<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Client Calls Controller: Signaling endpoints for WebRTC calls from client side
 * Allows clients to answer, decline calls and send ICE candidates back to staff
 */
class ClientCalls_Controller extends ClientsController
{
    /**
     * Stores the pusher options.
     *
     * @var array
     */
    protected $pusher_options = [];

    /**
     * Hold Pusher instance.
     *
     * @var object
     */
    protected $pusher;

    public function __construct()
    {
        parent::__construct();

        if (!$this->app_modules->is_active('prchat')) {
            show_error('Module not activated');
        }

        if (get_option('pusher_chat_enabled') != '1') {
            show_error(_l('chat_pusher_not_enabled'));
        }

        $this->pusher_options['app_key'] = get_option('pusher_app_key');
        $this->pusher_options['app_secret'] = get_option('pusher_app_secret');
        $this->pusher_options['app_id'] = get_option('pusher_app_id');

        if (!isset($this->pusher_options['cluster']) && get_option('pusher_cluster') != '') {
            $this->pusher_options['cluster'] = get_option('pusher_cluster');
        }

        // Create Guzzle client - SSL verification disabled only for development environments
        $verify_ssl = (ENVIRONMENT === 'production');
        $guzzleClient = new \GuzzleHttp\Client(['verify' => $verify_ssl]);

        $this->pusher = new Pusher\Pusher(
            $this->pusher_options['app_key'],
            $this->pusher_options['app_secret'],
            $this->pusher_options['app_id'],
            [
                'cluster' => $this->pusher_options['cluster'],
                'useTLS' => true,
            ],
            $guzzleClient
        );
    }

    /**
     * Get the appropriate channel based on recipient type
     * @param int $toId Recipient ID
     * @param string $recipientType 'staff' or 'client'
     * @return string Channel name
     */
    private function getChannel($toId, $recipientType = 'staff')
    {
        if ($recipientType === 'client') {
            return CHAT_CALLS_CLIENT_CHANNEL_PREFIX . $toId;
        }
        return CHAT_CALLS_STAFF_CHANNEL_PREFIX . $toId;
    }

    /**
     * Get current client info for payloads
     * @return array ['avatar' => ..., 'name' => ...]
     */
    private function getClientInfo()
    {
        $contactId = get_contact_user_id();
        $name = get_contact_full_name($contactId);
        $avatar = '';
        if (function_exists('contact_profile_image_url')) {
            $avatar = contact_profile_image_url($contactId, 'small');
        }
        return ['id' => $contactId, 'avatar' => $avatar, 'name' => $name];
    }

    /**
     * Answer call with SDP (client answering a call from staff)
     */
    public function answerCall()
    {
        $clientInfo = $this->getClientInfo();
        $fromId = $clientInfo['id']; // client (callee)
        $toId   = $this->input->post('to_id');   // staff (caller)
        $sdp    = $this->input->post('sdp');
        $isVideo = (bool)$this->input->post('is_video');

        // Send answer to staff channel
        $channel = $this->getChannel($toId, 'staff');

        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'sdp'     => $sdp,
            'is_video' => $isVideo,
            'from_avatar' => $clientInfo['avatar'],
            'from_name' => $clientInfo['name'],
            'caller_type' => 'client',
            'recipient_type' => 'staff',
        ];

        $this->pusher->trigger($channel, 'call-answer', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Relay ICE candidate from client to staff
     */
    public function iceCandidate()
    {
        $clientInfo = $this->getClientInfo();
        $fromId = $clientInfo['id'];
        $toId   = $this->input->post('to_id');
        $candidate = $this->input->post('candidate');

        // Send to staff channel
        $channel = $this->getChannel($toId, 'staff');
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'candidate' => $candidate,
        ];
        $this->pusher->trigger($channel, 'call-ice', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Hangup from client side
     */
    public function hangup()
    {
        $clientInfo = $this->getClientInfo();
        $fromId = $clientInfo['id'];
        $toId   = $this->input->post('to_id');

        // Send to staff channel
        $channel = $this->getChannel($toId, 'staff');
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
        ];
        $this->pusher->trigger($channel, 'call-hangup', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Decline call (client explicitly declines before answering)
     */
    public function decline()
    {
        $clientInfo = $this->getClientInfo();
        $fromId = $clientInfo['id']; // client (callee)
        $toId   = $this->input->post('to_id');   // staff (caller)

        // Send to staff channel
        $channel = $this->getChannel($toId, 'staff');
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
        ];
        $this->pusher->trigger($channel, 'call-declined', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Notify about mute status change from client
     */
    public function muteStatus()
    {
        $clientInfo = $this->getClientInfo();
        $fromId = $clientInfo['id'];
        $toId   = $this->input->post('to_id');
        $isMuted = (bool)$this->input->post('is_muted');

        // Send to staff channel
        $channel = $this->getChannel($toId, 'staff');
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'is_muted' => $isMuted,
        ];
        $this->pusher->trigger($channel, 'call-mute-status', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Pusher authentication for client calls channel
     */
    public function pusherAuth()
    {
        $contactId = get_contact_user_id();
        if (!$contactId) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $channelName = $this->input->post('channel_name');
        $socketId = $this->input->post('socket_id');

        if (!$channelName || !$socketId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing channel_name or socket_id']);
            return;
        }

        // Verify the channel is for this client
        $expectedChannel = CHAT_CALLS_CLIENT_CHANNEL_PREFIX . $contactId;
        if ($channelName !== $expectedChannel) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized channel']);
            return;
        }

        try {
            $auth = $this->pusher->authorizeChannel($channelName, $socketId);
            echo $auth;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Calls Controller: Signaling endpoints for WebRTC calls
 * Supports both staff-to-staff and staff-to-client calls
 */
class Calls_Controller extends AdminController
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->app_modules->is_active('prchat')) {
            redirect('admin');
        }

        if (get_option('pusher_chat_enabled') != '1') {
            show_error(_l('chat_pusher_not_enabled'));
        }
        $this->load->library('App_pusher');
    }

    /**
     * Staff call signaling trace (enable in application/config/config.php: log_threshold >= 2 for DEBUG).
     */
    private function logCallDebug($message, array $context = [])
    {
        $suffix = $context ? ' ' . json_encode($context) : '';
        log_message('DEV', 'prchat calls: ' . $message . $suffix);
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
     * Get caller info (avatar, name) based on caller type
     * @param int $fromId Caller ID
     * @param string $callerType 'staff' or 'client'
     * @return array ['avatar' => ..., 'name' => ...]
     */
    private function getCallerInfo($fromId, $callerType = 'staff')
    {
        if ($callerType === 'client') {
            // Get client/contact info
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact((int)$fromId);
            $avatar = '';
            $name = '';
            if ($contact) {
                $name = $contact->firstname . ' ' . $contact->lastname;
                // Contact profile image
                if (function_exists('contact_profile_image_url')) {
                    $avatar = contact_profile_image_url((int)$fromId, 'small');
                }
            }
            return ['avatar' => $avatar, 'name' => $name];
        }

        // Staff info
        $avatar = function_exists('staff_profile_image_url') ? staff_profile_image_url((int)$fromId, 'small') : '';
        $name = function_exists('get_staff_full_name') ? get_staff_full_name((int)$fromId) : '';
        return ['avatar' => $avatar, 'name' => $name];
    }

    /**
     * Use posted from_id only when it matches the logged-in staff (avoids spoofing if POST is tampered).
     */
    private function resolveStaffFromId()
    {
        $sid  = (int) get_staff_user_id();
        $post = (int) $this->input->post('from_id');
        $this->logCallDebug('resolveStaffFromId', ['post' => $post, 'sid' => $sid]);

        return ($post > 0 && $post === $sid) ? $post : $sid;
    }

    /**
     * Send call offer to callee
     */
    public function startCall()
    {
        $fromId = $this->resolveStaffFromId();
        $toId   = $this->input->post('to_id');
        $sdp    = $this->input->post('sdp');
        $isVideo = (bool)$this->input->post('is_video');
        $recipientType = $this->input->post('recipient_type') ?: 'staff';
        $callerType = $this->input->post('caller_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $callerInfo = $this->getCallerInfo($fromId, $callerType);

        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'sdp'     => $sdp,
            'is_video' => $isVideo,
            'from_avatar' => $callerInfo['avatar'],
            'from_name' => $callerInfo['name'],
            'caller_type' => $callerType,
            'recipient_type' => $recipientType,
        ];

        $sdpLen = is_string($sdp) ? strlen($sdp) : 0;
        $this->logCallDebug('startCall trigger call-offer', [
            'channel'         => $channel,
            'from_id'         => (int) $fromId,
            'to_id'           => (int) $toId,
            'is_video'        => $isVideo,
            'recipient_type'  => $recipientType,
            'sdp_chars'       => $sdpLen,
        ]);

        $this->app_pusher->trigger($channel, 'call-offer', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Answer call with SDP
     */
    public function answerCall()
    {
        $fromId = $this->resolveStaffFromId(); // callee
        $toId   = $this->input->post('to_id');   // caller
        $sdp    = $this->input->post('sdp');
        $isVideo = (bool)$this->input->post('is_video');
        $recipientType = $this->input->post('recipient_type') ?: 'staff';
        $callerType = $this->input->post('caller_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $callerInfo = $this->getCallerInfo($fromId, $callerType);

        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'sdp'     => $sdp,
            'is_video' => $isVideo,
            'from_avatar' => $callerInfo['avatar'],
            'from_name' => $callerInfo['name'],
            'caller_type' => $callerType,
            'recipient_type' => $recipientType,
        ];

        $sdpLen = is_string($sdp) ? strlen($sdp) : 0;
        $this->logCallDebug('answerCall trigger call-answer', [
            'channel'        => $channel,
            'from_id'        => (int) $fromId,
            'to_id'          => (int) $toId,
            'is_video'       => $isVideo,
            'recipient_type' => $recipientType,
            'sdp_chars'      => $sdpLen,
        ]);

        $this->app_pusher->trigger($channel, 'call-answer', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Relay ICE candidate
     */
    public function iceCandidate()
    {
        $fromId = $this->resolveStaffFromId();
        $toId   = $this->input->post('to_id');
        $candidate = $this->input->post('candidate');
        $recipientType = $this->input->post('recipient_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'candidate' => $candidate,
        ];
        $this->logCallDebug('iceCandidate call-ice', [
            'channel' => $channel,
            'from_id' => (int) $fromId,
            'to_id'   => (int) $toId,
        ]);
        $this->app_pusher->trigger($channel, 'call-ice', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Hangup
     */
    public function hangup()
    {
        $fromId = $this->resolveStaffFromId();
        $toId   = $this->input->post('to_id');
        $recipientType = $this->input->post('recipient_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
        ];
        $this->logCallDebug('hangup call-hangup', ['channel' => $channel, 'from_id' => (int) $fromId, 'to_id' => (int) $toId]);
        $this->app_pusher->trigger($channel, 'call-hangup', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Decline (callee explicitly declines before answering)
     */
    public function decline()
    {
        $fromId = $this->resolveStaffFromId(); // callee
        $toId   = $this->input->post('to_id');   // caller
        $recipientType = $this->input->post('recipient_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
        ];
        $this->logCallDebug('decline call-declined', ['channel' => $channel, 'from_id' => (int) $fromId, 'to_id' => (int) $toId]);
        $this->app_pusher->trigger($channel, 'call-declined', $payload);
        echo json_encode(['success' => true]);
    }

    /**
     * Notify about mute status change
     */
    public function muteStatus()
    {
        $fromId = $this->resolveStaffFromId();
        $toId   = $this->input->post('to_id');
        $isMuted = (bool)$this->input->post('is_muted');
        $recipientType = $this->input->post('recipient_type') ?: 'staff';

        $channel = $this->getChannel($toId, $recipientType);
        $payload = [
            'from_id' => (int)$fromId,
            'to_id'   => (int)$toId,
            'is_muted' => $isMuted,
        ];
        $this->logCallDebug('muteStatus call-mute-status', ['channel' => $channel, 'from_id' => (int) $fromId, 'to_id' => (int) $toId]);
        $this->app_pusher->trigger($channel, 'call-mute-status', $payload);
        echo json_encode(['success' => true]);
    }
}

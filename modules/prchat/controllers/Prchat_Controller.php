<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Perfex CRM Powerful Chat
Description: Chat Module for Perfex CRM
Author: iDev
Author URI: https://idevalex.com
*/

/**
 * @property Prchat_model $chat_model
 */
class Prchat_Controller extends AdminController
{

  /**
   * Class constructor
   */
  public function __construct()
  {
    parent::__construct();


    if (!staff_can('view', PR_CHAT_MODULE_NAME)) {
      redirect('admin');
    }

    if (!$this->app_modules->is_active('prchat')) {
      redirect('admin');
    }

    if (get_option('pusher_chat_enabled') != '1') {
      redirect('admin');
    }

    $this->load->model('prchat_model', 'chat_model');
    $this->load->library('App_pusher');


    if (
      get_option('pusher_app_key') == '' ||
      get_option('pusher_app_secret') == '' ||
      get_option('pusher_app_id') == ''
    ) {
      $error_message = '<div class="tw-border-l-4 tw-border-danger-500 tw-bg-danger-50 tw-rounded-lg tw-p-6 tw-mb-4">';
      $error_message .= '<div class="tw-flex tw-items-start tw-gap-3">';
      $error_message .= '<div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-rounded-full tw-bg-danger-500 tw-flex tw-items-center tw-justify-center tw-mt-0.5">';
      $error_message .= '<i class="fa fa-exclamation-circle tw-text-white tw-text-xs"></i>';
      $error_message .= '</div>';
      $error_message .= '<div class="tw-flex-1">';
      $error_message .= '<h4 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mb-3">' . _l('chat_pusher_setup_error') . '</h4>';
      $error_message .= '<p class="tw-text-sm tw-text-neutral-700 tw-mb-4">' . _l('chat_pusher_setup_link') . ': <a href="' . site_url('admin/settings?group=pusher') . '" class="tw-text-primary-600 tw-font-medium hover:tw-text-primary-700 hover:tw-underline">Perfex CRM Settings → Pusher.com</a></p>';
      $error_message .= '<div class="tw-flex tw-items-center tw-gap-2 tw-pt-3 tw-border-t tw-border-danger-200">';
      $error_message .= '<a target="_blank" href="https://help.perfexcrm.com/setup-realtime-notifications-with-pusher-com/" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-danger-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-danger-700 hover:tw-bg-danger-100 hover:tw-border-danger-400 tw-transition-colors">';
      $error_message .= '<i class="fa fa-external-link tw-text-xs"></i>';
      $error_message .= '<span>' . _l('chat_pusher_tutorial_link') . '</span>';
      $error_message .= '</a>';
      $error_message .= '</div>';
      $error_message .= '</div>';
      $error_message .= '</div>';
      $error_message .= '</div>';
      set_alert('danger', $error_message);
      redirect(admin_url('settings?group=pusher'));
    }

    // App_pusher library handles Pusher initialization automatically
  }

  /**
   * Messaging events
   *
   * @return void
   */
  public function initiateChat()
  {
    if ($this->input->post()) {
      if ($this->input->post('typing') == 'false') {
        $from = $this->input->post('from');
        $receiver = str_replace('#', '', $this->input->post('to') ?? '');
        $msg = trim($this->input->post('msg') ?? '');

        if ($msg === '') {
          $this->app_pusher->trigger(
            'presence-mychanel',
            'typing-event',
            [
              'message' => 'null',
              'from' => $from,
              'to' => $receiver,
            ]
          );
          return;
        }

        $imageData['sender_image'] = $this->chat_model->getUserImage($from);
        $imageData['receiver_image'] = $this->chat_model->getUserImage($receiver);

        if ($msg !== '') {
          $raw_message = $this->input->post('msg', false);

          $stored_message = $this->chat_model->process_message_for_storage($raw_message);

          $final_message = htmlentities($stored_message);

          $is_call_message = preg_match('/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/', $raw_message);
          $is_missed_call = preg_match('/^\[CALL:missed_(voice|video):\d+:\d+:\d+\]$/', $raw_message);

          // Server-side dedup for call messages
          if ($is_call_message) {
            $this->db->where('sender_id', $from);
            $this->db->where('reciever_id', $receiver);
            $this->db->where('message', $final_message);
            $this->db->where('time_sent >', date("Y-m-d H:i:s", strtotime('-10 seconds')));
            $dup = $this->db->get(db_prefix() . 'chatmessages')->num_rows();
            if ($dup > 0) {
              header('Content-Type: application/json');
              echo json_encode(['id' => 0, 'duplicate' => true]);
              return;
            }
          }

          $message_data = [
            'sender_id' => $this->input->post('from'),
            'reciever_id' => str_replace('#', '', $this->input->post('to') ?? ''),
            'message' => $final_message,
            'viewed' => ($is_call_message && !$is_missed_call) ? 1 : 0,
            'time_sent' => date("Y-m-d H:i:s"),
          ];

          $last_id = $this->chat_model->createMessage($message_data, db_prefix() . 'chatmessages');

          $display_message = $this->chat_model->process_message_for_display($stored_message);
          $display_message = htmlspecialchars($display_message, ENT_QUOTES, 'UTF-8');
          $safe_display = clickable(pr_chat_convertLinkImageToString($display_message));

          $this->app_pusher->trigger('presence-mychanel', 'send-event', [
            'message' => $safe_display,
            'from' => $this->input->post('from'),
            'to' => str_replace('#', '', $this->input->post('to') ?? ''),
            'from_name' => get_staff_full_name($from),
            'last_insert_id' => $last_id,
            'sender_image' => $imageData['sender_image'],
            'receiver_image' => $imageData['receiver_image'],
            'is_call' => $is_call_message ? true : false,
          ]);

          if (!$is_call_message || $is_missed_call) {
            $this->app_pusher->trigger(
              'presence-mychanel',
              'notify-event',
              [
                'from' => $this->input->post('from'),
                'to' => str_replace('#', '', $this->input->post('to') ?? ''),
                'from_name' => get_staff_full_name($from),
                'sender_image' => $imageData['sender_image'],
                'message' => $safe_display,
              ]
            );
          }

          header('Content-Type: application/json');
          echo json_encode(['id' => $last_id]);
          return;
        }
      } else if ($this->input->post('typing') == 'true') {
        $this->app_pusher->trigger(
          'presence-mychanel',
          'typing-event',
          [
            'message' => 'true',
            'from' => $this->input->post('from'),
            'to' => str_replace('#', '', $this->input->post('to') ?? ''),
          ]
        );
      } else {
        $this->app_pusher->trigger(
          'presence-mychanel',
          'typing-event',
          [
            'message' => 'null',
            'from' => $this->input->post('from'),
            'to' => str_replace('#', '', $this->input->post('to') ?? ''),
          ]
        );
      }
    }
  }


  /**
   * Main function that handles, sending messages, notify events, typing events and inserts message data in database.
   *
   * @return websocket event
   */
  public function initiateGroupChat()
  {
    if ($this->input->post()) {
      $from = $this->input->post('from');
      $group_id = $this->input->post('group_id');
      $group_name = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id])->row('group_name');

      if ($this->input->post('typing') == 'false') {
        $imageData['sender_image'] = $this->chat_model->getUserImage($from);

        $stored_message = $this->chat_model->process_message_for_storage($this->input->post('g_message', false));

        $message_data = [
          'sender_id' => $this->input->post('from'),
          'group_id' => $this->input->post('group_id'),
          'message' => htmlspecialchars($stored_message),
          'time_sent' => date("Y-m-d H:i:s")
        ];

        $last_id = $this->chat_model->createGroupMessage($message_data);

        $group_display_message = $this->chat_model->process_message_for_display($stored_message);

        $hasMention = strpos($group_display_message, 'user_mentioned') !== false;
        $hasEmoji = strpos($group_display_message, '"emoji"') !== false;
        $hasQuickMention = strpos($group_display_message, 'quickMentionLink') !== false;

        if (!$hasMention && !$hasEmoji && !$hasQuickMention) {
          $group_display_message = pr_chat_convertLinkImageToString($group_display_message);
        }
        $group_display_message = clickable($group_display_message);

        $this->app_pusher->trigger($group_name, 'group-send-event', [
          'message' => $group_display_message,
          'from' => $from,
          'to_group' => $group_id,
          'from_name' => get_staff_full_name($this->input->post('from')),
          'group_name' => $group_name,
          'last_insert_id' => $last_id,
          'sender_image' => $imageData['sender_image'],
        ]);

        $this->app_pusher->trigger($group_name, 'group-notify-event', [
          'from' => $this->input->post('from'),
          'from_name' => get_staff_full_name($this->input->post('from')),
          'to_group' => $group_id,
          'group_name' => $group_name,
          'sender_image' => $imageData['sender_image'],
          'message' => $group_display_message,
        ]);

        header('Content-Type: application/json');
        echo json_encode(['id' => $last_id]);
        return;
      } else if ($this->input->post('typing') == 'true') {
        $this->app_pusher->trigger(
          $group_name,
          'group-typing-event',
          [
            'message' => 'true',
            'from' => $this->input->post('from'),
            'from_name' => get_staff_full_name($this->input->post('from')),
            'to_group' => $group_id,
            'group_name' => $group_name,
          ]
        );
      } else {
        $this->app_pusher->trigger(
          $group_name,
          'group-typing-event',
          [
            'message' => 'null',
            'from' => $this->input->post('from'),
            'from_name' => get_staff_full_name($this->input->post('from')),
            'to_group' => $group_id,
            'group_name' => $group_name,
          ]
        );
      }
    }
  }

  /**
   * Get staff members for chat.
   *
   * @return void
   */
  public function users()
  {
    // Allow both AJAX (jQuery) and fetch (Vue.js) requests
    $users = $this->chat_model->getUsers();

    if ($users) {
      echo json_encode($users);
    } else {
      echo json_encode(['error' => _l('chat_error_table')]);
    }
  }


  /**
   * Get chat group members formatted for JSON response
   *
   * @return void
   */
  public function getChatGroupMembersAsJson()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $group_id = $this->input->get('group_id');

    if ($group_id) {
      $jsonFormattedUsers = $this->chat_model->getChatGroupMembersAsJson($group_id);
      header('Content-Type: application/json');

      if ($jsonFormattedUsers) {
        echo json_encode($jsonFormattedUsers, true);
      } else {
        echo json_encode(['error' => true, 'message' => _l('chat_error_table')]);
      }
    }
  }

  /**
   * Get pusher key
   *
   * @return mixed
   */
  public function getKey()
  {
    $app_key = get_option('pusher_app_key');
    header('Content-Type: application/json');
    if (!empty($app_key)) {
      echo json_encode(['key' => $app_key]);
    } else {
      echo json_encode(['error' => true, 'message' => _l('chat_app_key_not_found')]);
    }
  }

  /**
   * Get staff that will be used for the chat window.
   *
   * @return json|false
   */
  public function getStaffInfo()
  {
    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $response = $this->chat_model->getStaffInfo($id);

      if ($response) {
        echo json_encode($response);
      }
    }

    return false;
  }


  /**
   * Get logged in user messages sent to other user
   *
   * @return void
   */
  public function getMessages()
  {
    $limit = abs((int) $this->input->get('limit'));
    $from = (int) $this->input->get('from');
    $to = (int) $this->input->get('to');

    $limit = $limit > 0 ? min($limit, 100) : 10; // Default 10, max 100

    $offset = 0;
    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $currentUserId = (int) get_staff_user_id();
    if ($from !== $currentUserId && $to !== $currentUserId) {
      $from = $currentUserId;
    }

    $response = $this->chat_model->getMessages($from, $to, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }


  /**
   *  Get group messages.
   *
   * @return void
   */
  /**
   * Get last message previews for multiple groups in a single batch request.
   */
  public function getGroupPreviews()
  {
    $group_ids = $this->input->get('group_ids');
    if (!$group_ids) {
      header('Content-Type: application/json');
      echo json_encode([]);
      return;
    }

    $group_ids = array_filter(array_map('intval', explode(',', $group_ids)));
    $previews = $this->chat_model->getGroupPreviews($group_ids);

    header('Content-Type: application/json');
    echo json_encode($previews);
  }

  public function getGroupMessages()
  {
    $limit = abs((int) $this->input->get('limit'));
    $group_id = (int) $this->input->get('group_id');

    $limit = $limit > 0 ? min($limit, 100) : 10; // Default 10, max 100

    $offset = 0;
    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $response = $this->chat_model->getGroupMessages($group_id, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }


  /**
   * Get group messages history.
   *
   * @return void
   */
  public function getGroupMessagesHistory()
  {
    $limit = abs((int) $this->input->get('limit'));
    $group_id = (int) $this->input->get('group_id');

    $limit = $limit > 0 ? min($limit, 100) : 10;

    $offset = 0;
    $message = '';

    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }

    $response = $this->chat_model->getGroupMessagesHistory($group_id, $limit, $offset);

    if ($response) {
      echo json_encode($response);
    } else {
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }

  /**
   * Get unread messages, used when somebody sent a message while the user is offline.
   *
   * @param bool
   *
   * @return mixed
   */
  public function getUnread($return = false)
  {
    $result = $this->chat_model->getUnread();

    if ($result) {
      echo json_encode($result);
    } else {
      echo json_encode(['success' => false]);
    }

    return false;
  }

  /**
   * Get all unread counts (staff + client) for floating notifications.
   *
   * @return void
   */
  public function getUnreadCounts()
  {
    $data = ['staff' => [], 'clients' => []];

    // Staff unread
    $staffUnread = $this->chat_model->getUnread();
    if ($staffUnread && is_array($staffUnread)) {
      foreach ($staffUnread as $entry) {
        $sid = (int) $entry['sender_id'];
        if ($sid <= 0)
          continue;
        $name = get_staff_full_name($sid);
        if (empty(trim($name)))
          continue;
        $data['staff'][] = [
          'id' => $sid,
          'name' => $name,
          'count' => (int) $entry['count_messages'],
          'avatar' => staff_profile_image_url($sid, 'small'),
        ];
      }
    }

    // Client unread
    if (isClientsEnabled()) {
      $clientUnread = $this->chat_model->getClientUnreadMessages();
      if ($clientUnread && is_array($clientUnread) && !isset($clientUnread['result'])) {
        foreach ($clientUnread as $entry) {
          $contactId = str_replace('client_', '', $entry['sender_id']);
          $clientData = isset($entry['client_data']) ? $entry['client_data'] : null;
          $name = ($clientData && !empty($clientData['firstname']))
            ? trim($clientData['firstname'] . ' ' . ($clientData['lastname'] ?? ''))
            : _l('chatbot_client_prefix', [$contactId]);
          $data['clients'][] = [
            'id' => $contactId,
            'name' => $name,
            'count' => (int) $entry['count_messages'],
            'avatar' => contact_profile_image_url($contactId),
          ];
        }
      }
    }

    echo json_encode($data);
  }


  /**
   * Updated unread messages to read.
   *
   * @return void
   */
  public function updateUnread()
  {
    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $result = $this->chat_model->updateUnread($this->app_pusher, $id);

      echo json_encode($result);
    }
  }

  /**
   * Mark messages as read from floating notifications
   *
   * @return void
   */
  public function mark_messages_as_read()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $type = $this->input->post('type');

    if ($type === 'staff') {
      $staff_id = $this->input->post('staff_id');
      if ($staff_id) {
        $result = $this->chat_model->updateUnread($this->app_pusher, $staff_id);
        echo json_encode(['success' => $result]);
      }
    } elseif ($type === 'client') {
      $contact_id = $this->input->post('contact_id');
      if ($contact_id) {
        // For client messages, we need to mark them as read from staff perspective
        $result = $this->chat_model->updateClientUnreadMessages($contact_id, $this->app_pusher);
        echo json_encode(['success' => $result]);
      }
    } else {
      echo json_encode(['success' => false, 'error' => _l('chatbot_invalid_type')]);
    }
  }


  /**
   * Pusher authentication.
   *
   * @return mixed
   * @throws \Pusher\PusherException
   */
  public function pusher_auth()
  {
    if ($this->input->get() || $this->input->post()) {
      $name = get_staff_full_name();
      $user_id = get_staff_user_id();
      $channel_name = $this->input->post('channel_name') ?: $this->input->get('channel_name');
      $socket_id = $this->input->post('socket_id') ?: $this->input->get('socket_id');

      if (!$channel_name) {
        exit('channel_name must be supplied');
      }

      if (!$socket_id) {
        exit('socket_id must be supplied');
      }

      if (
        !empty(get_option('pusher_app_key'))
        && !empty(get_option('pusher_app_secret'))
        && !empty(get_option('pusher_app_id'))
      ) {
        if (
          strpos($channel_name, 'private-') === 0
          && preg_match('/^private-calls-staff-(\d+)$/', $channel_name, $m)
          && (int) $m[1] === (int) $user_id
        ) {
          $auth = $this->app_pusher->socket_auth($channel_name, $socket_id);
        } else {
          $justLoggedIn = ($channel_name === 'presence-mychanel' && $this->session->has_userdata('prchat_user_before_login'));

          if ($justLoggedIn) {
            $this->session->unset_userdata('prchat_user_before_login');
          }

          $presence_data = [
            'name' => $name,
            'justLoggedIn' => $justLoggedIn,
            'status' => '' . $this->chat_model->getChatStatus() . ''
          ];

          $auth = $this->app_pusher->presence_auth($channel_name, $socket_id, $user_id, $presence_data);
        }

        $callback = $this->input->get('callback');
        if (!empty($callback)) {
          $callback = preg_replace('/[^a-zA-Z0-9_.$\[\]\'"\\\\]/', '', $callback);
          header('Content-Type: application/javascript');
          echo $callback . '(' . $auth . ');';
        } else {
          header('Content-Type: application/json');
          echo $auth;
        }
      } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Appkey, secret or appid is missing']);
      }
    }
  }

  /**
   * ICE / WebRTC bootstrap JSON for staff clients.
   */
  public function get_call_token()
  {
    $ice = [
      ['urls' => ['stun:stun.l.google.com:19302']],
    ];
    if (defined('CHAT_CALLS_TURN_URL') && CHAT_CALLS_TURN_URL !== '') {
      $turn = ['urls' => [CHAT_CALLS_TURN_URL]];
      if (defined('CHAT_CALLS_TURN_USERNAME') && CHAT_CALLS_TURN_USERNAME !== '') {
        $turn['username'] = CHAT_CALLS_TURN_USERNAME;
      }
      if (defined('CHAT_CALLS_TURN_CREDENTIAL') && CHAT_CALLS_TURN_CREDENTIAL !== '') {
        $turn['credential'] = CHAT_CALLS_TURN_CREDENTIAL;
      }
      $ice[] = $turn;
    }

    $this->output
      ->set_status_header(200)
      ->set_content_type('application/json', 'utf-8')
      ->set_output(json_encode([
        'success' => true,
        'iceServers' => $ice,
        'staff_id' => (int) get_staff_user_id(),
      ]));
  }

  /**
   * Upload method for files
   *
   * @return json
   */
  public function uploadMethod()
  {

    $isVoiceUpload = $this->input->post('prchat_voice_upload') === '1'
      || $this->input->post('prchat_voice_upload') === 1;

    if ($isVoiceUpload) {
      pr_chat_patch_upload_mimes_for_voice();
      $allowedFiles = pr_chat_voice_upload_extensions_string();
    } else {
      $allowedFiles = get_option('allowed_files');
      $allowedFiles = str_replace([',', '.'], ['|', ''], $allowedFiles ?? '');
      $denyList = ['php', 'phar', 'phtml', 'cgi', 'pl', 'py', 'sh', 'bat', 'exe', 'htaccess', 'htpasswd'];
      $types = array_filter(explode('|', $allowedFiles));
      $types = array_diff($types, $denyList);
      $allowedFiles = implode('|', $types);
    }

    if ($isVoiceUpload) {
      $uploadPath = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff';
    } else {
      $uploadPath = PR_CHAT_MODULE_UPLOAD_FOLDER;
    }

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
      file_put_contents($uploadPath . '/index.html', '');
    }

    $config = [
      'upload_path' => $uploadPath,
      'allowed_types' => $allowedFiles,
      'max_size' => $isVoiceUpload ? '10240' : '9048000',
    ];

    $this->load->library('upload', $config);

    if ($this->upload->do_upload()) {
      $data = $this->upload->data();
      if ($isVoiceUpload) {
        $data['subfolder'] = 'staff';
      }
      echo json_encode(['upload_data' => $data]);
    } else {
      echo json_encode(['error' => strip_tags($this->upload->display_errors())]);
    }
  }


  /**
   * Uploads method for chat group files
   *
   * @return json
   */
  public function groupUploadMethod()
  {

    $isVoiceUpload = $this->input->post('prchat_voice_upload') === '1'
      || $this->input->post('prchat_voice_upload') === 1;

    if ($isVoiceUpload) {
      pr_chat_patch_upload_mimes_for_voice();
      $allowedFiles = pr_chat_voice_upload_extensions_string();
    } else {
      $allowedFiles = get_option('allowed_files');
      $allowedFiles = str_replace([',', '.'], ['|', ''], $allowedFiles ?? '');
      $denyList = ['php', 'phar', 'phtml', 'cgi', 'pl', 'py', 'sh', 'bat', 'exe', 'htaccess', 'htpasswd'];
      $types = array_filter(explode('|', $allowedFiles));
      $types = array_diff($types, $denyList);
      $allowedFiles = implode('|', $types);
    }

    if ($isVoiceUpload) {
      $uploadPath = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups';
    } else {
      $uploadPath = PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER;
    }

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
      file_put_contents($uploadPath . '/index.html', '');
    }

    $config = [
      'upload_path' => $uploadPath,
      'allowed_types' => $allowedFiles,
      'max_size' => $isVoiceUpload ? '10240' : '9048000',
    ];

    $this->load->library('upload', $config);
    if ($this->upload->do_upload()) {
      $from = $this->input->post()['send_from'];
      $to_group = $this->input->post()['to_group'];

      $this->db->insert(
        'tblchatgroupsharedfiles',
        [
          'sender_id' => $from,
          'group_id' => $to_group,
          'file_name' => $this->upload->data('file_name'),
        ]
      );

      $data = $this->upload->data();
      if ($isVoiceUpload) {
        $data['subfolder'] = 'groups';
      }
      echo json_encode(['upload_data' => $data]);
    } else {
      echo json_encode(['error' => strip_tags($this->upload->display_errors())]);
    }
  }





  /**
   * Change chat color for current user
   *
   * @return json
   */
  public function changeChatColor()
  {
    $id = get_staff_user_id();
    $color = trim($this->input->post('color') ?? '');

    if ($this->input->post('get_chat_color')) {
      echo json_encode(pr_get_chat_color($id));
    }

    if ($this->input->post('color')) {
      echo json_encode($this->chat_model->setChatColor($color));
    }
  }


  /**
   * Delete chat message
   *
   * @return json
   */
  public function deleteMessage()
  {
    // Must be AJAX and user must have delete capability
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $contact_id = (string) $this->input->post('contact_id');
    $contact_id = ltrim($contact_id, '#');
    $contact_id = preg_replace('/[^0-9]/', '', $contact_id);
    $currentUserId = (int) get_staff_user_id();

    if ($this->input->post('group_id')) {
      $group_id = (int) $this->input->post('group_id');

      // Ownership check: only allow deleting own group messages
      $row = $this->db->select('sender_id')
        ->where('id', $id)
        ->where('group_id', $group_id)
        ->get(db_prefix() . 'chatgroupmessages')
        ->row();

      if (!$row || (int) $row->sender_id !== $currentUserId) {
        access_denied();
      }

      $deleted = $this->chat_model->deleteMessage($id, 'group_id' . $group_id);

      if ($deleted && $group_id > 0 && isset($this->app_pusher)) {
        $group_name = $this->db->select('group_name')
          ->where('id', $group_id)
          ->get(db_prefix() . 'chatgroups')
          ->row('group_name');

        if (!empty($group_name)) {
          $this->app_pusher->trigger($group_name, 'group-message-deleted', [
            'message_id' => $id,
            'group_id' => $group_id,
            'sender_id' => $currentUserId,
          ]);
        }
      }

      echo json_encode($deleted);
    } else {
      // Ownership check: only allow deleting own direct messages
      $row = $this->db->select('sender_id')
        ->where('id', $id)
        ->get(db_prefix() . 'chatmessages')
        ->row();

      if (!$row || (int) $row->sender_id !== $currentUserId) {
        access_denied();
      }

      $deleted = $this->chat_model->deleteMessage($id, $contact_id);

      if ($deleted && !empty($contact_id) && isset($this->app_pusher)) {
        $this->app_pusher->trigger('presence-mychanel', 'message-deleted', [
          'message_id' => $id,
          'from' => $currentUserId,
          'to' => (int) $contact_id,
        ]);
      }

      echo json_encode($deleted);
    }
  }


  /**
   * Edit a staff or group message.
   */
  public function editMessage()
  {
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $new_text = $this->input->post('message', false);
    $group_id = $this->input->post('group_id');
    $staff_id = get_staff_user_id();

    if (!$id || !$new_text) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'changed' => false, 'error' => 'Missing parameters']);
      return;
    }

    $stored = $this->chat_model->process_message_for_storage($new_text);
    $final = htmlentities($stored);

    if ($group_id) {
      $table = db_prefix() . 'chatgroupmessages';
      $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', $staff_id, ['sender_type' => 'staff']);
    } else {
      $table = db_prefix() . 'chatmessages';
      $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', $staff_id);
    }

    $success = !empty($editResult['success']);
    $changed = !empty($editResult['changed']);

    $display = $this->chat_model->process_message_for_display($stored);
    $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
    $rendered_safe = clickable(pr_chat_convertLinkImageToString($display));

    if ($success && $changed) {
      if ($group_id) {
        $group_name = $this->db->select('group_name')
          ->where('id', (int) $group_id)
          ->get(TABLE_CHATGROUPS)
          ->row('group_name');
        if ($group_name) {
          $gSender = $this->db->select('sender_id')
            ->where('id', $id)
            ->get(db_prefix() . 'chatgroupmessages')
            ->row();
          $this->app_pusher->trigger($group_name, 'group-message-edited', [
            'message_id' => $id,
            'group_id' => (int) $group_id,
            'sender_id' => $gSender ? (int) $gSender->sender_id : (int) $staff_id,
            'rendered_message' => $rendered_safe,
            'edited_at' => date('Y-m-d H:i:s'),
          ]);
        }
      } else {
        $row = $this->db->where('id', $id)->get(db_prefix() . 'chatmessages')->row();
        if ($row) {
          $this->app_pusher->trigger('presence-mychanel', 'message-edited', [
            'message_id' => $id,
            'from' => $row->sender_id,
            'to' => $row->reciever_id,
            'rendered_message' => $rendered_safe,
            'edited_at' => date('Y-m-d H:i:s'),
          ]);
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'changed' => $changed,
      'rendered_message' => $rendered_safe,
    ]);
  }

  /**
   * Edit a client message.
   */
  public function editClientMessage()
  {
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    $id = (int) $this->input->post('id');
    $new_text = $this->input->post('message', false);
    $staff_id = get_staff_user_id();

    if (!$id || !$new_text) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'changed' => false, 'error' => 'Missing parameters']);
      return;
    }

    $stored = $this->chat_model->process_message_for_storage($new_text);
    $final = htmlentities($stored);
    $table = db_prefix() . 'chatclientmessages';
    $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', 'staff_' . $staff_id);

    $success = !empty($editResult['success']);
    $changed = !empty($editResult['changed']);

    $display = $this->chat_model->process_message_for_display($stored);
    $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
    $rendered_safe = clickable(pr_chat_convertLinkImageToString($display));

    if ($success && $changed) {
      $row = $this->db->where('id', $id)->get($table)->row();
      if ($row) {
        $this->app_pusher->trigger('presence-clients', 'message-edited', [
          'message_id' => $id,
          'from' => $row->sender_id,
          'to' => $row->reciever_id,
          'rendered_message' => $rendered_safe,
          'edited_at' => date('Y-m-d H:i:s'),
        ]);
      }
    }

    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'changed' => $changed,
      'rendered_message' => $rendered_safe,
    ]);
  }

  /**
   * Delete chat client message
   *
   * @return mixed
   */
  public function deleteClientMessage()
  {
    // Must be AJAX and user must have delete capability
    if (!$this->input->is_ajax_request()) {
      access_denied();
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $message_id = (int) $this->input->post('message_id');

    if ($message_id) {
      // Ownership check: only allow deleting own staff messages in client conversation
      $expectedSender = 'staff_' . get_staff_user_id();
      $row = $this->db->select('sender_id')
        ->where('id', $message_id)
        ->get(db_prefix() . 'chatclientmessages')
        ->row();

      if (!$row || (string) $row->sender_id !== $expectedSender) {
        access_denied();
      }

      echo json_encode($this->chat_model->deleteClientMessage($message_id));
    }
  }


  /**
   * Delete chat conversation
   *
   * @return mixed
   */
  public function deleteChatConversation()
  {
    if (!chatStaffCanDelete())
      access_denied();

    if ($this->input->post('id')) {
      $id = $this->input->post('id');
      $table = $this->input->post('table');
      $allowedTables = ['chatmessages', 'chatclientmessages'];
      if (!in_array($table, $allowedTables)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid table']);
        return;
      }
      header('Content-Type: application/json');
      echo json_encode($this->chat_model->deleteMutualConversation($id, $table));
    }
  }


  /**
   * Switch user theme
   * Light or Dark.
   *
   * @return json
   */
  public function switchTheme()
  {
    $id = get_staff_user_id();
    $theme_name = $this->input->post('theme_name');

    echo json_encode($this->chat_model->updateChatTheme($id, $theme_name));
  }


  /**
   * Loads user full chat browser view.
   *
   * @return view
   */
  public function chat_full_view()
  {
    $result = $this->chat_model->getUnread();
    $this->load->view('prchat/chat_full_view', ['unreadMessages' => $result]);
  }

  /**
   * Handles shared files between two users.
   *
   * @return json
   */
  public function getSharedFiles()
  {
    if ($this->input->post()) {
      $own_id = $this->input->post('own_id');
      $contact_id = $this->input->post('contact_id');

      $html = $this->chat_model->get_shared_files_and_create_template($own_id, $contact_id);
      if ($html) {
        echo json_encode($html);
      }
    }
  }


  /**
   * Handles shared files between users in group.
   *
   * @return json
   */
  public function getGroupSharedFiles()
  {
    if ($this->input->post()) {
      $group_id = $this->input->post('group_id');

      $html = $this->chat_model->get_group_shared_files_and_create_template($group_id);

      if ($html) {
        echo json_encode($html);
      }
    }
  }


  /**
   *  Handles staff announcement modal view.
   *
   * @return view modal
   */
  public function staff_announcement()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_announcement_modal_text');

    $this->load->view('prchat/includes/modal', $data);
  }


  /**
   *  Handles clients mass message modal view.
   *
   * @return view modal
   */
  public function clients_announcement_message()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_client_announcement_title');

    $this->load->view('prchat/includes/client_announcment_modal', $data);
  }


  /**
   * Handles data inserting for global message to selected clients.
   *
   * @return json
   */
  public function clients_announcement()
  {
    if ($this->input->post()) {
      $members = $this->input->post('clients');
      $message = $this->input->post('message');

      echo json_encode($this->chat_model->announcementToClients($members, $message, $this->app_pusher));
    }
  }


  /**
   *  Handles staff announcement modal view.
   *
   * @return view modal
   */
  public function quick_mentions($id = '')
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!staff_can('edit', 'tasks') && !staff_can('create', 'tasks')) {
      ajax_access_denied();
    }

    $data = [];

    $data['milestones'] = [];
    $data['checklistTemplates'] = [];
    $data['project_end_date_attrs'] = [];


    $this->load->view('prchat/includes/quick_mentions_modal', $data);
  }


  /**
   * Handles data inserting for global message to selected members.
   *
   * @return json
   */
  public function staff_get_selected_members()
  {
    if ($this->input->post()) {
      $members = $this->input->post('members');
      $message = $this->input->post('message');

      echo json_encode($this->chat_model->globalMessage($members, $message, $this->app_pusher));
    }
  }


  /**
   * Fetch chat groups
   *
   * @return view
   */
  public function chatGroups()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_group_modal_title');
    $data['association_only'] = false;
    $data['group_id'] = 0;
    $data['related_type'] = '';
    $data['related_id'] = 0;
    $data['related_name'] = '';

    if ($this->input->get('association_only') === '1') {
      $gid = (int) $this->input->get('group_id');
      if ($gid <= 0) {
        echo '<div class="alert alert-danger tw-m-3">' . _l('access_denied') . '</div>';
        return;
      }
      $row = $this->db->select('id, created_by_id, related_type, related_id')
        ->where('id', $gid)
        ->get(db_prefix() . 'chatgroups')
        ->row();
      $staffId = (int) get_staff_user_id();
      if (!$row || ((int) $row->created_by_id !== $staffId && !is_admin())) {
        echo '<div class="alert alert-danger tw-m-3">' . _l('access_denied') . '</div>';
        return;
      }
      $data['association_only'] = true;
      $data['group_id'] = $gid;
      $data['title'] = _l('chat_associate_with');
      $data['related_type'] = $row->related_type ?: '';
      $data['related_id'] = !empty($row->related_id) ? (int) $row->related_id : 0;
      if ($data['related_type'] && $data['related_id']) {
        $resolved = $this->chat_model->resolveRelatedItemPublic($data['related_type'], $data['related_id']);
        $data['related_name'] = $resolved['name'] ?? '';
      }
    }

    $this->load->view('prchat/includes/groups_modal', $data);
  }


  /**
   * Loads new modal for creating new chat group.
   *
   * @return view
   */
  public function addNewChatGroupMembersModal()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_group_modal_add_title');
    $group_id = (int) $this->input->get('group_id');
    $data['group_id'] = $group_id;

    $this->load->view('prchat/includes/add_modal', $data);
  }


  /**
   * Adds new chat members to specific group.
   *
   * @return json
   */
  public function addChatGroupMembers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!empty($this->input->post('group_name'))) {
      $group_name = $this->input->post('group_name');
      $members = $this->input->post('members');
      $group_id = $this->input->post('group_id');

      return $this->chat_model->addChatGroupMembers($group_name, $group_id, $members, $this->app_pusher);
    }
  }


  /**
   * Create new chat group
   *
   * @return mixed
   */
  public function addChatGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    // Check if staff can create groups (admins bypass)
    if (get_option('chat_members_can_create_groups') != 1 && !is_admin()) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    if ($this->input->post('group_name')) {
      $data = [];

      $data['group_name'] = CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($this->input->post('group_name'));

      $data['members'] = $this->input->post('members');

      $own_id = $this->session->userdata('staff_user_id');

      if (empty($data['members'])) {
        return false;
      }

      if (!in_array($own_id, $data['members'])) {
        $data['members'][] = $own_id;
      }

      $insertData = [
        'created_by_id' => $own_id,
        'group_name' => $data['group_name'],
      ];

      // Add association if provided
      $related_type = $this->input->post('related_type');
      $related_id = $this->input->post('related_id');
      if (!empty($related_type) && !empty($related_id)) {
        $insertData['related_type'] = $related_type;
        $insertData['related_id'] = (int) $related_id;
        // Include in Pusher payload so sidebar renders association immediately
        $data['related_type'] = $related_type;
        $data['related_id'] = (int) $related_id;
        $resolved = $this->chat_model->resolveRelatedItemPublic($related_type, (int) $related_id);
        $data['related_name'] = $resolved['name'];
        $data['related_url'] = $resolved['url'];
      }

      return $this->chat_model->addChatGroup($insertData, $data, $this->app_pusher);
    }
  }

  /**
   * Task search for group association (Perfex core get_relation_data does not search tasks by name).
   * Same JSON shape as admin/misc/get_relation_data for ajax bootstrap-select.
   */
  public function prchatRelationSearch()
  {
    if (!$this->input->is_ajax_request()) {
      echo json_encode([]);
      return;
    }

    $type = $this->input->post('type');
    $q = trim((string) $this->input->post('q'));
    if ($type !== 'task' || $q === '') {
      echo json_encode([]);
      return;
    }

    if (staff_cant('view', 'tasks')) {
      echo json_encode([]);
      return;
    }

    $this->load->helper('relation');
    $this->db->select('id, name');
    $this->db->from(db_prefix() . 'tasks');
    $this->db->group_start();
    $this->db->like('name', $q);
    $this->db->group_end();
    $this->db->limit(50);
    $rows = $this->db->get()->result_array();

    $out = [];
    foreach ($rows as $r) {
      $out[] = get_relation_values($r, 'task');
    }

    echo json_encode($out);
  }

  /**
   * Legacy: full list loader (unused by group modal; association uses ajax search like core CRM).
   */
  public function getRelatedItems()
  {
    if (!$this->input->is_ajax_request()) {
      echo json_encode([]);
      return;
    }

    echo json_encode([]);
  }

  public function renameChatGroup()
  {
    $groupId = $this->input->post('groupId');
    $newName = $this->input->post('groupName');

    try {
      if ($groupId) {
        $this->db->where('id', $groupId)->update(db_prefix() . 'chatgroups', ['group_name' => CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($newName)]);
        $this->db->where('group_id', $groupId)->update(db_prefix() . 'chatgroupmembers', ['group_name' => CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($newName)]);
      }

      $this->app_pusher->trigger(
        'group-chat',
        'group-renamed',
        [
          'group_id' => $groupId,
          'newName' => $newName,
        ]
      );
    } catch (Exception $e) {
      echo json_encode(['error' => $e->getMessage()]);
      die;
    }

    echo json_encode(['success' => true]);
  }

  /**
   * Link an existing group (no association yet) to a CRM record. Group creator or admin only.
   */
  public function updateChatGroupAssociation()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
      return;
    }

    header('Content-Type: application/json; charset=utf-8');

    $groupId = (int) $this->input->post('group_id');
    $relatedType = $this->input->post('related_type');
    $relatedId = (int) $this->input->post('related_id');

    if ($groupId <= 0 || $relatedId <= 0 || !is_string($relatedType) || $relatedType === '') {
      echo json_encode(['success' => false, 'message' => _l('chat_select_association')]);
      return;
    }

    $allowed = ['project', 'invoice', 'estimate', 'contract', 'ticket', 'lead', 'task'];
    if (!in_array($relatedType, $allowed, true)) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    $group = $this->db->where('id', $groupId)->get(db_prefix() . 'chatgroups')->row();
    if (!$group) {
      echo json_encode(['success' => false, 'message' => _l('chat_error_float')]);
      return;
    }

    $staffId = (int) get_staff_user_id();
    if ((int) $group->created_by_id !== $staffId && !is_admin()) {
      echo json_encode(['success' => false, 'message' => _l('access_denied')]);
      return;
    }

    $this->db->where('id', $groupId)->update(db_prefix() . 'chatgroups', [
      'related_type' => $relatedType,
      'related_id' => $relatedId,
    ]);

    $resolved = $this->chat_model->resolveRelatedItemPublic($relatedType, $relatedId);

    echo json_encode([
      'success' => true,
      'related_type' => $relatedType,
      'related_id' => $relatedId,
      'related_name' => $resolved['name'],
      'related_url' => $resolved['url'],
    ]);
  }


  /**
   * Fetches all groups linked to current logged in user
   *
   * @return json
   */
  public function getMyGroups()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    return $this->chat_model->getMyGroups();
  }


  /**
   * Delete chat group
   *
   * @return json
   */
  public function deleteGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    if ($this->input->post('group_id')) {
      $group_id = $this->input->post('group_id');
      $group_name = $this->input->post('group_name');

      return $this->chat_model->deleteGroup($group_id, $group_name, $this->app_pusher);
    }
  }


  /**
   * Get all group members
   *
   * @return json
   */
  public function getGroupUsers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id') !== '') {
      $group_id = $this->input->post('group_id');

      return $this->chat_model->getGroupUsers($group_id);
    }
  }


  /**
   * Backup function that fetches all group members.
   *
   * @return mixed
   */
  public function getCurrentGroupUsers()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id') !== '') {
      $group_id = $this->input->post('group_id');
      $users = $this->chat_model->getCurrentGroupUsers($group_id);
      if (is_array($users) && !empty($users)) {
        return $users;
      } else {
        return false;
      }
    }
  }


  /**
   * Remove user from group
   *
   * @return mixed
   */
  public function removeChatGroupUser()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if (!staff_can('delete', PR_CHAT_MODULE_NAME)) {
      access_denied();
    }

    $own_id = get_staff_user_id();

    if ($this->input->post('id')) {
      $group_name = $this->input->post('group_name');
      $user_id = $this->input->post('id');
      $group_id = $this->input->post('group_id');

      return $this->chat_model->removeChatGroupUser($group_name, $group_id, $user_id, $own_id, $this->app_pusher);
    } else {
      return false;
    }
  }


  /**
   * Chat members leaves group event
   *
   * @return mixed
   */
  public function chatMemberLeaveGroup()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    if ($this->input->post('group_id')) {
      $group_id = $this->input->post('group_id');
      $member_id = $this->input->post('member_id');

      return $this->chat_model->chatMemberLeaveGroup($group_id, $member_id, $this->app_pusher);
    }
  }


  /**
   * Downloads CSV file of exported messages from database between two users staff or clients
   *
   * @return void
   */
  public function exportCSV()
  {
    if (!is_admin()) {
      access_denied();
    }

    $to = $this->input->get('user');

    $this->chat_model->initiateExportToCSV($to);
  }


  /**
   * Conver to ticket load model view
   *
   * @return view
   */
  public function convertToTicket()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    // Check if ticket conversion is enabled (admins bypass)
    if (get_option('chat_allow_staff_to_create_tickets') != 1 && !is_admin()) {
      access_denied();
    }

    $id = $this->input->post('id');
    $table = 'chatclientmessages';

    $name = (strpos($id, 'client') !== false)
      ? get_contact_full_name(str_replace('client_', '', $id))
      : get_staff_full_name(get_staff_user_id());

    $data = [
      'id' => $id,
      'user_full_name' => $name,
      'messages' => $this->chat_model->getMessagesForTicketConversion($id, $table),
    ];

    $this->load->view('prchat/includes/convert_to_ticket_modal', $data);
  }


  /**
   * Create new support ticket
   *
   * @return string
   */
  public function createNewSupportTicket()
  {
    // Check if ticket conversion is enabled (admins bypass)
    if (get_option('chat_allow_staff_to_create_tickets') != 1 && !is_admin()) {
      access_denied();
    }

    $data = [];

    $data = $this->input->post('content');
    $assigned = $this->input->post('assigned');
    $subject = $this->input->post('subject');
    $department = $this->input->post('department');

    return $this->chat_model->chatHandleSupportTicketCreation($data, $subject, $department, $assigned);
  }


  /**
   * Chat status update
   *
   * @return mixed
   */
  public function handleChatStatus()
  {
    $status = $this->input->post('status');

    if (!$status || !$this->input->is_ajax_request()) {
      show_404();
    }

    $response = $this->chat_model->handleChatStatus($status);

    if (!empty($response)) {
      $this->app_pusher->trigger(
        'user_changed_chat_status',
        'status-changed-event',
        [
          'user_id' => $response['user_id'],
          'status' => $response['status'],
        ]
      );
      header('Content-Type: application/json');
      echo json_encode($response);
    }
  }


  /**
   * Toggle pin state for a conversation
   *
   * @return void
   */
  public function togglePin()
  {
    if (!$this->input->is_ajax_request() || !$this->input->post()) {
      show_404();
    }

    $type = $this->input->post('type'); // staff, group, client
    $target_id = $this->input->post('target_id');

    if (!$type || !$target_id) {
      show_404();
    }

    $settingName = 'pinned_' . $type;
    $allowedTypes = ['staff', 'groups', 'clients'];
    if (!in_array($type, $allowedTypes)) {
      show_404();
    }

    $result = $this->chat_model->togglePinChat($settingName, $target_id);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'pinned' => $result['added'],
      'ids' => $result['ids']
    ]);
  }

  /**
   * Toggle mute state for a conversation
   *
   * @return void
   */
  public function toggleMute()
  {
    if (!$this->input->is_ajax_request() || !$this->input->post()) {
      show_404();
    }

    $type = $this->input->post('type'); // staff, groups, clients
    $target_id = $this->input->post('target_id');

    if (!$type || !$target_id) {
      show_404();
    }

    $settingName = 'muted_' . $type;
    $allowedTypes = ['staff', 'groups', 'clients'];
    if (!in_array($type, $allowedTypes)) {
      show_404();
    }

    $result = $this->chat_model->toggleMuteChat($settingName, $target_id);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'muted' => $result['muted'],
      'ids' => $result['ids']
    ]);
  }

  /**
   * Get all pin/mute settings for the current user
   *
   * @return void
   */
  public function getPinMuteSettings()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $settings = $this->chat_model->getAllPinMuteSettings();

    header('Content-Type: application/json');
    echo json_encode($settings);
  }

  /**
   * Mentions
   *
   * @return void
   */
  public function pusherMentionEvent()
  {
    $data = $this->input->post();

    if (!$data || !$this->input->is_ajax_request()) {
      show_404();
    }
    if ($data) {
      $this->chat_model->handleMentionEvent($data, $this->app_pusher);
    }
  }


  /**
   * Show modal view with staff users and clients for message forwarding
   *
   * @return void
   */
  public function showForwardUsersModal()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $data['title'] = _l('chat_forward_message_title');
    $data['groups'] = $this->chat_model->getChatGroups();

    $this->load->view('prchat/includes/forward_to_modal', $data);
  }


  /**
   * Live Search staff.
   *
   * @return void
   */
  public function searchStaffForForward()
  {
    $search = $this->input->get('search');
    $staff = $this->chat_model->searchStaff($search);
    echo json_encode($staff);
  }


  /**
   * Load more staff members for pagination (AJAX)
   *
   * @return void
   */
  public function loadMoreStaffMembers()
  {
    $offset = abs((int) $this->input->get('offset'));
    echo json_encode($this->chat_model->loadMoreStaffMembers($offset));
  }


  /**
   * AJAX search endpoint for staff members.
   * Returns JSON [{id, name, subtext}] compatible with ajaxSelectPicker.
   */
  public function ajaxSearchStaff()
  {
    header('Content-Type: application/json');

    $q = $this->input->get('q') ?: '';
    $excludeIds = [];

    $excludeGroup = (int) $this->input->get('exclude_group');
    if ($excludeGroup > 0) {
      $members = $this->getCurrentGroupUsers($excludeGroup);
      foreach ($members as $m) {
        $excludeIds[] = (int) $m['member_id'];
      }
    }

    echo json_encode($this->chat_model->searchStaffAjax($q, $excludeIds));
  }

  /**
   * AJAX search endpoint for client contacts.
   * Returns JSON [{id, name, subtext}] compatible with ajaxSelectPicker.
   */
  public function ajaxSearchClients()
  {
    header('Content-Type: application/json');

    $q = $this->input->get('q') ?: '';
    echo json_encode($this->chat_model->searchClientsAjax($q));
  }

  /**
   * Live ajax search for chat messages for staff to staff and staff to client.
   *
   * @return void
   */
  public function searchMessages()
  {
    if (!$this->input->is_ajax_request()) {
      redirect('admin/prchat/Prchat_Controller/chat_full_view', 'refresh');
    }

    $id = $this->input->post('id');
    $table = $this->input->post('table');

    $name = (strpos($id, 'client') !== false)
      ? get_contact_full_name(str_replace('client_', '', $id))
      : get_staff_full_name($id);

    $data = [
      'id' => $id,
      'user_full_name' => $name,
      'messages' => json_encode($this->chat_model->getMessagesHistoryBetween($id, $table), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
    ];

    $this->load->view('prchat/includes/search_messages_modal', $data);
  }


  /**
   * Deletes conversation history from staff, clients or groups with all uploads
   */
  public function purgeConversations()
  {
    if (!chatStaffCanDelete()) {
      access_denied();
    }

    $type = $this->input->post('type');

    if ($type) {
      header('Content-Type: application/json');
      echo json_encode($this->chat_model->purgeConversations($type));
    }
  }

  /**
   * Get internal client notes for a contact (staff with access only).
   */
  public function get_client_notes()
  {
    $contact_id = (int) $this->input->get_post('contact_id');
    if ($contact_id <= 0 || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'notes' => []]);
      return;
    }
    $notes = $this->chat_model->get_client_notes($contact_id);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'notes' => $notes]);
  }

  /**
   * Save a new client note (staff with access only).
   */
  public function save_client_note()
  {
    $contact_id = (int) $this->input->post('contact_id');
    $content = $this->input->post('note_content');
    if ($contact_id <= 0 || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $id = $this->chat_model->add_client_note($contact_id, $content);
    header('Content-Type: application/json');
    echo json_encode(['success' => (bool) $id, 'id' => $id]);
  }

  /**
   * Update a client note (author only; staff must have access to contact).
   */
  public function update_client_note()
  {
    $note_id = (int) $this->input->post('note_id');
    $content = $this->input->post('note_content');
    if ($note_id <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $contact_id = $this->chat_model->get_contact_id_by_note_id($note_id);
    if ($contact_id === null || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $ok = $this->chat_model->update_client_note($note_id, $content);
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
  }

  /**
   * Toggle emoji reaction for a chat message (staff/admin context).
   *
   * POST: message_id, emoji, message_type (staff|client|group)
   */
  public function addReaction()
  {
    $messageId = (int) $this->input->post('message_id');
    $emoji = (string) $this->input->post('emoji');
    $messageType = (string) $this->input->post('message_type');

    $allowedEmojis = ['👍', '❤️', '😂', '😮', '😢', '😡', '🎉', '🔥'];
    if ($messageId <= 0 || empty($emoji) || !in_array($emoji, $allowedEmojis, true)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    if (!in_array($messageType, ['staff', 'client', 'group'], true)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    $staffId = get_staff_user_id();
    if ($staffId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    // Reaction identity key must match frontend expectations
    $userKey = $messageType === 'client' ? 'staff_' . $staffId : (string) $staffId;

    $updatedReactions = $this->chat_model->toggleMessageReaction(
      $messageId,
      $emoji,
      $userKey,
      $messageType
    );

    // Broadcast reaction update to participants (via Pusher).
    if ($messageType === 'staff' || $messageType === 'client') {
      $payload = [
        'message_id' => $messageId,
        'message_type' => $messageType,
        'emoji' => $emoji,
        'reactions' => $updatedReactions,
        'reactor_key' => $userKey,
      ];

      // Staff clients (full chat and toggled chat) listen on presence-mychanel.
      $this->app_pusher->trigger('presence-mychanel', 'message-reaction', $payload);

      // Client portal listens on presence-clients for staff<->client messages.
      if ($messageType === 'client') {
        $this->app_pusher->trigger('presence-clients', 'message-reaction', $payload);
      }
    } elseif ($messageType === 'group') {
      // Resolve group channel name for group message reactions.
      $groupIdRow = $this->db->select('group_id')->from(db_prefix() . 'chatgroupmessages')->where('id', $messageId)->limit(1)->get()->row();
      $groupId = $groupIdRow && isset($groupIdRow->group_id) ? (int) $groupIdRow->group_id : 0;
      $groupName = $groupId > 0 ? $this->db->get_where(TABLE_CHATGROUPS, ['id' => $groupId])->row('group_name') : null;

      if ($groupName) {
        $payload = [
          'message_id' => $messageId,
          'message_type' => $messageType,
          'emoji' => $emoji,
          'reactions' => $updatedReactions,
          'reactor_key' => $userKey,
        ];
        $this->app_pusher->trigger($groupName, 'message-reaction', $payload);
      }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'reactions' => $updatedReactions]);
  }

  /**
   * Delete a client note (author only; staff must have access to contact).
   */
  public function delete_client_note()
  {
    $note_id = (int) $this->input->post('note_id');
    if ($note_id <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $contact_id = $this->chat_model->get_contact_id_by_note_id($note_id);
    if ($contact_id === null || !staff_can_access_contact_for_chat($contact_id)) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }
    $ok = $this->chat_model->delete_client_note($note_id);
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
  }
}

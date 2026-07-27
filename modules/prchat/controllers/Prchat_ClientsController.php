<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Perfex CRM Powerful Chat
Description: Chat Module for Perfex CRM
Author: iDev
Author URI: https://idevalex.com
Requires at least: 2.3.2
*/

class Prchat_ClientsController extends ClientsController
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
   * @var [object]
   */
  protected $pusher;

  /**
   * Controler __construct function to initialize options.
   */
  public function __construct()
  {
    parent::__construct();

    if (!$this->app_modules->is_active('prchat')) {
      redirect('admin');
    }

    if (get_option('pusher_chat_enabled') != '1') {
      redirect('admin');
    }

    $this->load->model('prchat_model', 'chat_model');

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
   * Pusher authentication.
   *
   * @return void
   */
  public function pusherCustomersAuth()
  {
    if ($this->input->get() || $this->input->post()) {
      $user_id = 'client_' . get_contact_user_id();
      $name = get_contact_full_name(get_contact_user_id());
      $channel_name = 'presence-clients';
      $socket_id = $this->input->post('socket_id') ?: $this->input->get('socket_id');

      if (!$channel_name) {
        exit('channel_name must be supplied');
      }

      if (!$socket_id) {
        exit('socket_id must be supplied');
      }

      if (
        !empty($this->pusher_options['app_key'])
        && !empty($this->pusher_options['app_secret'])
        && !empty($this->pusher_options['app_id'])
      ) {
        $justLoggedIn = $this->session->has_userdata('prchat_client_before_login');

        if ($justLoggedIn) {
          $this->session->unset_userdata('prchat_client_before_login');
        }

        $presence_data = [
          'name' => $name,
          'justLoggedIn' => $justLoggedIn,
          'contact_id' => get_contact_user_id(),
          'client_id' => get_client_user_id(),
          'company' => get_company_name(get_client_user_id()),
        ];

        $auth = $this->pusher->presence_auth($channel_name, $socket_id, $user_id, $presence_data);
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
        exit('Appkey, secret or appid is missing');
      }
    }
  }

  /**
   * Main function that handles, sending messages, notify events, typing events and inserts message data in database.
   *
   * @throws \Pusher\PusherException
   */
  public function initClientChat()
  {
    if ($this->input->post()) {
      $from = $this->input->post('from');
      $receiver = $this->input->post('to');
      $client_id = $this->input->post('client_id');
      $contact_full_name = $this->input->post('contact_full_name');
      $contact_company_name = $this->input->post('company');

      if ($this->input->post('typing') == 'false') {
        // Server-side validation: Check if client has any staff available (only when CLIENT is sending)
        if (str_contains($from, 'client_') && (empty($receiver) || !str_contains($receiver, 'staff_'))) {
          echo json_encode(['error' => true, 'message' => 'No staff available']);
          return;
        }
        $message = $this->input->post('client_message', false); // Disable XSS cleaning for reply format

        // Process message for storage (convert Unicode emojis to shortcodes)
        $stored_message = $this->chat_model->process_message_for_storage($message);

        $is_call_message = preg_match('/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/', $message);
        $is_missed_call = preg_match('/^\[CALL:missed_(voice|video):\d+:\d+:\d+\]$/', $message);

        $message_data = [
          'sender_id' => $from,
          'reciever_id' => $receiver,
          'message' => htmlentities($stored_message),
          'viewed' => ($is_call_message && !$is_missed_call) ? 1 : 0,
          'time_sent' => date("Y-m-d H:i:s"),
        ];

        $lastInsertId = $this->chat_model->recordClientMessage($message_data);
        if (!$lastInsertId) {
          header('Content-Type: application/json');
          echo json_encode(['error' => true, 'message' => 'Could not save message']);
          return;
        }

        $display_message = $this->chat_model->process_message_for_display($stored_message);
        $display_message = htmlspecialchars($display_message, ENT_QUOTES, 'UTF-8');

        // Determine if sender is staff or client
        $is_staff_sender = str_contains($from, 'staff_');
        $is_client_sender = str_contains($from, 'client_');

        $from_staff_data = null;
        $staff_id = null;
        $from_name = '';

        if ($is_staff_sender) {
          // Staff sending to client
          $staff_id = str_replace('staff_', '', $from);
          $staff_data = $this->db->select('staffid, firstname, lastname, email, profile_image, admin, role')
            ->where('staffid', $staff_id)
            ->where('active', 1)
            ->get(db_prefix() . 'staff')
            ->row();

          if ($staff_data) {
            $from_staff_data = [
              'staffid' => $staff_data->staffid,
              'firstname' => $staff_data->firstname,
              'lastname' => $staff_data->lastname,
              'profile_image' => $staff_data->profile_image,
              'role' => $staff_data->admin ? ' ' . _l('chat_role_administrator') : (get_staff_userrole($staff_data->role) ?: ' ' . _l('chat_role_staff')),
            ];
            $from_name = get_staff_full_name($staff_id);
          }
        } else if ($is_client_sender) {
          // Client sending to staff - use contact_full_name from POST
          $from_name = $contact_full_name ?: 'Client';
        }

        $this->pusher->trigger(
          'presence-clients',
          'send-event',
          [
            'message' => clickable(pr_chat_convertLinkImageToString($display_message)),
            'from' => $from,
            'to' => $receiver,
            'client_id' => $client_id,
            'company' => $contact_company_name,
            'contact_full_name' => $contact_full_name,
            'client_image_path' => contact_profile_image_url(str_replace('client_', '', $is_client_sender ? $from : $receiver)),
            'from_name' => $from_name,
            'from_staff_data' => $from_staff_data,
            'last_insert_id' => $lastInsertId,
            'is_call' => $is_call_message ? true : false,
          ]
        );

        // Only send notify-event for staff→client messages (not client→staff)
        if ($is_staff_sender && (!$is_call_message || $is_missed_call)) {
          $this->pusher->trigger(
            'presence-clients',
            'notify-event',
            [
              'from' => $from,
              'to' => $receiver,
              'from_name' => $from_name,
            ]
          );
        }

        header('Content-Type: application/json');
        echo json_encode(['id' => $lastInsertId]);
        return;
      } else if ($this->input->post('typing') == 'true') {
        $this->pusher->trigger(
          'presence-clients',
          'typing-event',
          [
            'message' => 'true',
            'from' => $from,
            'to' => $receiver,
          ]
        );
      } else {
        $this->pusher->trigger(
          'presence-clients',
          'typing-event',
          [
            'message' => 'null',
            'from' => $from,
            'to' => $receiver,
          ]
        );
      }
    }
  }

  /**
   * Get logged in user messages sent to other user.
   */
  public function getMutualMessages()
  {
    $limit = abs((int) $this->input->get('limit'));
    $to = $this->input->get('sender_id');
    $from = $this->input->get('reciever_id');

    $limit = $limit > 0 ? min($limit, 100) : 10;

    $offset = 0;

    if ($this->input->get('offset')) {
      $offset = abs((int) $this->input->get('offset'));
    }
    $response = $this->chat_model->getMutualMessages($from, $to, $limit, $offset);

    if ($response) {
      header('Content-Type: application/json');
      echo json_encode($response);
    } else {
      header('Content-Type: application/json');
      $message = _l('chat_no_more_messages_in_database');
      echo json_encode($message);
    }
  }

  /**
   * Get last message previews for all client contacts in a single batch request.
   * Returns JSON object keyed by contact_id.
   */
  public function getClientContactPreviews()
  {
    $contact_ids = $this->input->get('contact_ids');
    if (!$contact_ids) {
      header('Content-Type: application/json');
      echo json_encode([]);
      return;
    }

    $contact_ids = array_filter(array_map('intval', explode(',', $contact_ids)));
    $staff_id = 'staff_' . get_staff_user_id();

    $previews = $this->chat_model->getClientContactPreviews($staff_id, $contact_ids);

    header('Content-Type: application/json');
    echo json_encode($previews);
  }

  /**
   * Get unread messages, used when somebody sent a message while the user is offline.
   */
  public function getClientUnreadMessages()
  {
    $result = $this->chat_model->getClientUnreadMessages();
    if ($result) {
      echo json_encode($result);
    } else {
      echo json_encode($result);
    }
  }

  /**
   * Get unread messages, used when somebody sent a message while the user is offline.
   */
  public function getStaffUnreadMessages()
  {
    $result = $this->chat_model->getStaffUnreadMessages($this->pusher);

    header('Content-Type: application/json');
    if ($result) {
      echo json_encode($result);
    } else {
      echo json_encode(['error' => false, 'data' => null]);
    }
  }

  /**
   *  Uploads chat files.
   */
  public function uploadMethod()
  {
    $this->load->helper('prchat/prchat');

    $isVoiceUpload = $this->input->post('prchat_voice_upload') === '1'
      || $this->input->post('prchat_voice_upload') === 1;

    if ($isVoiceUpload) {
      $from = (string) $this->input->post('from');
      // Staff messaging client from CRM uses staff_* — store under audio/staff, not audio/clients
      if ($from !== '' && stripos($from, 'staff_') === 0) {
        $upload_path = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff';
        $voiceSubfolder = 'staff';
      } else {
        $upload_path = PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/clients';
        $voiceSubfolder = 'clients';
      }
      pr_chat_patch_upload_mimes_for_voice();
      $allowedFiles = pr_chat_voice_upload_extensions_string();
    } else {
      $upload_path = PR_CHAT_MODULE_UPLOAD_FOLDER . '/clients';
      $allowedFiles = pr_chat_client_crm_attachment_types_string();
    }

    if (!is_dir($upload_path)) {
      mkdir($upload_path, 0755, true);
      file_put_contents($upload_path . '/index.html', '');
    }

    $config = [
      'upload_path' => $upload_path,
      'allowed_types' => $allowedFiles,
      'max_size' => $isVoiceUpload ? '10240' : '9048000',
    ];

    $this->load->library('upload', $config);

    if ($this->upload->do_upload()) {
      $data = $this->upload->data();
      if ($isVoiceUpload) {
        $data['subfolder'] = $voiceSubfolder;
      }
      echo json_encode(['upload_data' => $data]);
    } else {
      echo json_encode(['error' => strip_tags($this->upload->display_errors())]);
    }
  }


  /**
   * Update unread messages.
   */
  public function updateClientUnreadMessages()
  {
    $id = $this->input->post('id');
    $client = $this->input->post('client');
    $contact_id = $this->input->post('contact_id');
    $pusher = $this->pusher;

    echo json_encode($this->chat_model->updateClientUnreadMessages($id, $pusher, ($client) ? $client : null, $contact_id));
  }


  /**
   * Loading more clients from database on click Load more button.
   */
  public function loadMoreClients()
  {
    $limit = 30;
    $offset = 0;

    if ($this->input->get('offset')) {
      $offset = $this->input->get('offset');
    }

    $staffCanViewAllClients = staff_can('view', 'customers');

    $this->db->select('firstname, lastname, ' . db_prefix() . 'contacts.id as contact_id, ' . get_sql_select_client_company());
    $this->db->where(db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'contacts.active = 1');
    $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
    $this->db->select(db_prefix() . 'clients.userid as client_id');

    if (!$staffCanViewAllClients) {
      $this->db->where('(' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . '))');
    }

    $this->db->limit($limit, $offset);
    $result = $this->db->get(db_prefix() . 'contacts')->result_array();

    foreach ($result as $key => $contact) {
      $result[$key]['profile_image_url'] = contact_profile_image_url($contact['contact_id']);
    }

    if ($this->db->affected_rows() !== 0) {
      echo json_encode(['customers' => $result]);
    } else {
      echo json_encode(['customers' => []]);
    }
  }

  /**
   * Toggle emoji reaction for a chat message (client portal context).
   *
   * POST: message_id, emoji, message_type (expects "client")
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

    if ($messageType !== 'client') {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    $contactId = get_contact_user_id();
    if ($contactId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false]);
      return;
    }

    $userKey = 'client_' . $contactId;

    $updatedReactions = $this->chat_model->toggleMessageReaction(
      $messageId,
      $emoji,
      $userKey,
      $messageType
    );

    $payload = [
      'message_id' => $messageId,
      'message_type' => $messageType,
      'emoji' => $emoji,
      'reactions' => $updatedReactions,
      'reactor_key' => $userKey,
    ];

    try {
      // Update client portal UI
      $this->pusher->trigger('presence-clients', 'message-reaction', $payload);
      // Update staff UI (staff pages listen on presence-mychanel)
      $this->pusher->trigger('presence-mychanel', 'message-reaction', $payload);
    } catch (\Throwable $e) {
      // Fail silently; UI will reconcile on next load.
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'reactions' => $updatedReactions]);
  }

  /**
   * Live Search clients.
   *
   * @return void
   */
  public function searchClients()
  {
    $search = $this->input->post('search');
    $query = $this->chat_model->searchClients($search);
    echo json_encode($query);
  }


  /**
   * @throws \Pusher\PusherException
   */
  public function trigger_ticket_event()
  {
    $trigger = $this->pusher->trigger(
      'presence-clients',
      'chat-ticket-event',
      [
        'ticket_id' => $this->input->post('ticket_id'),
        'client_id' => $this->input->post('client_id'),
      ]
    );

    header('Content-Type: application/json');
    if ($trigger) {
      echo json_encode(
        [
          'result' => 'success',
          'ticket_id' => $this->input->post('ticket_id')
        ]
      );
    } else {
      echo json_encode(['result' => 'error']);
    }
  }

  /**
   * Edit a client-side message (client editing their own message).
   */
  public function editClientMessage()
  {
    $id = (int) $this->input->post('id');
    $new_text = $this->input->post('message', false);

    if (!$id || !$new_text) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'changed' => false]);
      return;
    }

    $contact_id = get_contact_user_id();
    $sender_val = 'client_' . $contact_id;

    $stored = $this->chat_model->process_message_for_storage($new_text);
    $final = htmlentities($stored);
    $table = db_prefix() . 'chatclientmessages';
    $editResult = $this->chat_model->editMessage($id, $final, $table, 'sender_id', $sender_val);

    $success = !empty($editResult['success']);
    $changed = !empty($editResult['changed']);

    $display = $this->chat_model->process_message_for_display($stored);
    $display = htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
    $rendered_safe = clickable(pr_chat_convertLinkImageToString($display));

    if ($success && $changed) {
      $row = $this->db->where('id', $id)->get($table)->row();
      if ($row) {
        $this->pusher->trigger('presence-clients', 'message-edited', [
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
}

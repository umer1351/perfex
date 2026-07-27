<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM Chat & AI Chatbot Module
Description: Chat & AI Chatbot Module for Perfex CRM
Author: iDev
Author URI: https://idevalex.com
*/

class Prchat_model extends App_Model
{
  /** @var int|null Cached current staff user ID to avoid repeated function calls */
  private $current_staff_id = null;

  /**
   * Get current staff user ID with caching
   *
   * @return int Current staff user ID
   */
  private function get_current_staff_id(): int
  {
    if ($this->current_staff_id === null) {
      $this->current_staff_id = get_staff_user_id();
    }
    return $this->current_staff_id;
  }

  /**
   * Process chat messages
   *
   * @param array $messages Array of message objects
   * @param bool $include_user_image Include user image processing
   * @param bool $include_client_data Include client-specific data processing
   * @return array Processed messages
   */
  private function process_chat_messages(array $messages, bool $include_user_image = true, bool $include_client_data = false): array
  {
    foreach ($messages as &$chat) {
      $chat->message = html_entity_decode($chat->message, ENT_QUOTES, 'UTF-8');

      $hasQuickMention = strpos($chat->message, 'quickMentionLink') !== false;
      if (!$hasQuickMention) {
        $chat->message = htmlspecialchars($chat->message, ENT_QUOTES, 'UTF-8');
      }

      $chat->message = $this->process_message_for_display($chat->message);

      if (!$hasQuickMention) {
        $chat->message = pr_chat_convertLinkImageToString($chat->message);
      }
      $chat->message = clickable($chat->message);
      $chat->time_sent_formatted = _dt($chat->time_sent);

      // Format viewed_at timestamp if message has been seen using Perfex CRM date/time settings
      if (isset($chat->viewed_at) && !empty($chat->viewed_at) && $chat->viewed_at !== '0000-00-00 00:00:00') {
        $chat->viewed_at_formatted = _dt($chat->viewed_at);
      } else {
        $chat->viewed_at_formatted = '';
      }

      // User image processing
      if ($include_user_image && isset($chat->sender_id)) {
        $sender_id = $include_client_data ? str_replace('staff_', '', $chat->sender_id) : $chat->sender_id;
        $chat->user_image = $this->getUserImage($sender_id);
      }

      // Client-specific data processing
      if ($include_client_data && isset($chat->sender_id)) {
        $chat->sender_fullname = get_staff_full_name(str_replace('staff_', '', $chat->sender_id));
        $chat->client_image_path = contact_profile_image_url(str_replace('client_', '', $chat->sender_id));
      }
    }

    unset($chat);
    return $messages;
  }
  /**
   * Get active staff members with chat permissions and latest messages
   *
   * @return array|false Array of users with chat data or false if none found
   */
  public function getUsers()
  {
    $users = $this->getActiveStaff();
    $users = $this->filterStaffByPermissions($users);

    foreach ($users as $key => $user) {
      $users[$key] = $this->enrichUserWithChatData($user);
    }

    return !empty($users) ? array_values($users) : false;
  }

  /**
   * Get active staff members from database
   *
   * @return array Active staff members
   */
  private function getActiveStaff(): array
  {
    $this->db->select('staffid, firstname, lastname, profile_image, last_login, last_activity, facebook, linkedin, skype, admin, role');
    $this->db->where('active', 1);
    return $this->db->get(db_prefix() . 'staff')->result_array();
  }

  /**
   * Filter staff by chat permissions
   *
   * @param array $users Staff users array
   * @return array Filtered users array
   */
  private function filterStaffByPermissions(array $users): array
  {
    return array_filter($users, function ($user) {
      return staff_can('view', PR_CHAT_MODULE_NAME, $user['staffid']);
    });
  }

  /**
   * Enrich user data with chat information (role, latest message, status)
   *
   * @param array $user User data array
   * @return array Enriched user data
   */
  private function enrichUserWithChatData(array $user): array
  {
    $user['role'] = $this->processUserRole($user);
    $user['status'] = ($this->getChatStatus($user['staffid'])) ? $this->getChatStatus($user['staffid']) : CHAT_DEFAULT_USER_STATUS;

    // Generate proper profile image URL with file existence validation
    $placeholder = base_url('assets/images/user-placeholder.jpg');
    if (!empty($user['profile_image'])) {
      $imagePath = FCPATH . 'uploads/staff_profile_images/' . $user['staffid'] . '/small_' . $user['profile_image'];
      if (file_exists($imagePath)) {
        $user['profile_image_url'] = base_url('uploads/staff_profile_images/' . $user['staffid'] . '/small_' . $user['profile_image']);
      } else {
        $user['profile_image_url'] = $placeholder;
        // Clear invalid profile_image to prevent frontend from trying to use it
        $user['profile_image'] = null;
      }
    } else {
      $user['profile_image_url'] = $placeholder;
    }

    $latestMessage = $this->getLatestMessageWithUser($user['staffid']);
    if ($latestMessage) {
      $user = array_merge($user, $this->processLatestMessage($latestMessage, $user['staffid']));
    }

    // Count unread messages from this user to current user
    $current_user_id = $this->get_current_staff_id();
    $user['unread_count'] = (int) $this->db
      ->where('sender_id', $user['staffid'])
      ->where('reciever_id', $current_user_id)
      ->where('viewed', 0)
      ->count_all_results(db_prefix() . 'chatmessages');

    return $user;
  }

  /**
   * Process user role information with admin/role hierarchy
   *
   * @param array $user User data array
   * @return string Processed role string
   */
  private function processUserRole(array $user): string
  {
    // If user is admin, they are ONLY Administrator (no role needed)
    if ($user['admin']) {
      return ' ' . _l('chat_role_administrator');
    }

    // If user is not admin but has a role, show the role
    if ($user['role']) {
      $role = get_staff_userrole($user['role']);
      if ($role) {
        return ' ' . $role;
      }
    }

    // Fallback: if no role and not admin, show as Staff
    return ' ' . _l('chat_role_staff');
  }

  /**
   * Get latest message between current user and specified user
   *
   * @param int $user_id Target user ID
   * @return object|null Latest message or null if none found
   */
  private function getLatestMessageWithUser(int $user_id): ?object
  {
    $current_user_id = $this->get_current_staff_id();

    $this->db->select('message, sender_id, time_sent');
    $this->db->from(db_prefix() . 'chatmessages');
    $this->db->group_start();
    $this->db->where('sender_id', $current_user_id);
    $this->db->where('reciever_id', $user_id);
    $this->db->group_end();
    $this->db->or_group_start();
    $this->db->where('sender_id', $user_id);
    $this->db->where('reciever_id', $current_user_id);
    $this->db->group_end();
    $this->db->order_by('id', 'DESC');
    $this->db->limit(1);

    $result = $this->db->get()->row();
    return $result ?: null;
  }

  /**
   * Process latest message for display
   *
   * @param object $message Message object
   * @param int $user_id Target user ID
   * @return array Processed message data
   */
  private function processLatestMessage(object $message, int $user_id): array
  {
    $data = [];
    $data['time_sent'] = $message->time_sent;
    $data['time_sent_formatted'] = time_ago($message->time_sent);
    $data['message_sender_id'] = (int) $message->sender_id;

    // Check if message contains audio content
    $is_audio = preg_match('~\b(src|audio|controls|ogg)\b~i', $message->message);
    $render_message = $is_audio ? _l('chat_new_audio_message_sent') : $message->message;

    // Determine message direction (sent by current user or received)
    if ((int) $user_id !== (int) $message->sender_id) {
      $data['message'] = _l('chat_message_you') . ' ' . $render_message;
    } else {
      $data['message'] = $render_message;
    }

    return $data;
  }


  /**
   * Get staff members available for message forwarding
   *
   * @return array|false Array of forwardable staff members or false if none found
   */
  public function getForwardableStaffMembers()
  {

    $this->db->select('staffid, firstname, lastname, profile_image, role, admin');
    $this->db->where('active', 1);
    $this->db->order_by('firstname', 'ASC');

    $users = $this->db->get(db_prefix() . 'staff')->result_array();

    foreach ($users as $key => $user) {
      if (!staff_can('view', PR_CHAT_MODULE_NAME, $user['staffid'])) {
        unset($users[$key]);
        continue;
      }
    }

    if ($users) {
      return $users;
    }

    return false;
  }

  /**
   * Get clients available for message forwarding
   *
   * @return array|false Array of forwardable clients or false if none found
   */
  public function getForwardableClients()
  {
    // Return contacts (not clients) since messages are sent to contacts
    $this->db->select('ct.id, c.company, ct.firstname, ct.lastname, ct.profile_image, c.userid as client_userid');
    $this->db->from(db_prefix() . 'contacts ct');
    $this->db->join(db_prefix() . 'clients c', 'c.userid = ct.userid', 'left');
    $this->db->where('c.active', 1);
    $this->db->where('ct.active', 1);
    $this->db->order_by('ct.firstname', 'ASC');

    $contacts = $this->db->get()->result_array();

    if ($contacts) {
      return $contacts;
    }

    return false;
  }

  /**
   * Get chat group members formatted for JSON response
   *
   * @param string $group_id The chat group ID
   *
   * @return array|false Array of group members or false if none found
   */
  public function getChatGroupMembersAsJson($group_id)
  {
    $this->db->select('staffid as id, CONCAT(firstname, " ", lastname) as name, profile_image as avatar');
    $this->db->where('active', 1);
    $this->db->where('staffid !=', get_staff_user_id());
    $this->db->where('staffid IN (SELECT member_id FROM ' . db_prefix() . 'chatgroupmembers WHERE member_id=' . db_prefix() . 'staff.staffid AND group_id = ' . (int) $group_id . ')');

    $users = $this->db->get(db_prefix() . 'staff')->result_array();

    foreach ($users as &$user) {
      $user['type'] = 'staff';
      $user['avatar'] = staff_profile_image_url($user['id']);
    }

    if ($users) {
      return $users;
    }

    return false;
  }

  /**
   * Get chat status for a specific user
   *
   * @param int|false $id User ID or false for current user
   *
   * @return string|null Chat status value or null if not found
   */
  public function getChatStatus($id = false)
  {
    $this->db->where('user_id', ($id) ? $id : $this->get_current_staff_id());
    $this->db->where('name', 'chat_status');
    $result = $this->db->get(db_prefix() . 'chatsettings')->row_array();
    if ($result !== null) {
      return $result['value'];
    }
  }

  /**
   * Get logged in staff profile image.
   *
   * @param string @id
   *
   * @return mixed
   */
  public function getUserImage($id)
  {
    $this->db->from(db_prefix() . 'staff');
    $this->db->where('staffid', $id);
    $profile_image = $this->db->get()->row('profile_image');

    // Validate that the image file actually exists before returning it
    if ($profile_image) {
      $imagePath = FCPATH . 'uploads/staff_profile_images/' . $id . '/thumb_' . $profile_image;
      if (file_exists($imagePath)) {
        return $profile_image;
      }
    }

    // Return empty/false to trigger placeholder on frontend
    return false;
  }

  /**
   * Create message.
   *
   * @param $data
   * @param $table
   *
   * @return mixed
   */
  public function createMessage($data, $table)
  {

    // because :p was being processed before :poop:

    if ($this->db->insert($table, $data)) {
      return $this->db->insert_id();
    }

    return false;
  }

  /**
   * Edit an existing message. Only the original sender may edit.
   *
   * @param int    $message_id
   * @param string $new_message  Already stored/escaped content
   * @param string $table        Full table name
   * @param string $sender_field Column name for sender ('sender_id' or 'member_id')
   * @param mixed  $sender_value Current user identifier
   * @return array{success:bool,changed:bool} changed=false when text equals DB (no update, no edited_at bump)
   */
  public function editMessage($message_id, $new_message, $table, $sender_field, $sender_value, $extra_where = [])
  {
    $this->db->select('id, ' . $sender_field . ', message')
      ->where('id', $message_id);

    foreach ($extra_where as $col => $val) {
      $this->db->where($col, $val);
    }

    $row = $this->db->get($table)->row();

    if (!$row || $row->{$sender_field} != $sender_value) {
      return ['success' => false, 'changed' => false];
    }

    if ((string) $row->message === (string) $new_message) {
      return ['success' => true, 'changed' => false];
    }

    $this->db->where('id', $message_id);
    $ok = $this->db->update($table, [
      'message' => $new_message,
      'edited_at' => date('Y-m-d H:i:s'),
    ]);

    return ['success' => (bool) $ok, 'changed' => (bool) $ok];
  }

  /**
   * Create group message.
   *
   * @param array @data
   *
   * @return mixed
   */
  public function createGroupMessage($data)
  {


    if ($this->db->insert(db_prefix() . 'chatgroupmessages', $data)) {
      return $this->db->insert_id();
    }

    return false;
  }

  /**
   * Get staff firstname and lastname.
   *
   * @param string @id
   *
   * @return mixed
   */
  public function getStaffInfo($id)
  {
    $this->db->select('firstname,lastname,profile_image');
    $this->db->where('staffid', $id);
    $result = $this->db->get(db_prefix() . 'staff')->row();
    if ($result) {
      return $result;
    }

    return false;
  }

  /**
   * Get messages
   *
   * @param string $from sender
   * @param string $to receiver
   * @param string $limit limit messages
   * @param string $offset offset
   *
   * @return mixed
   */
  public function getMessages($from, $to, $limit, $offset)
  {
    $this->db->select('id, sender_id, reciever_id, message, time_sent, viewed, viewed_at, reactions, edited_at');
    $this->db->from(db_prefix() . 'chatmessages');
    $this->db->group_start();
    $this->db->where('sender_id', $to)->where('reciever_id', $from);
    $this->db->or_where('sender_id', $from)->where('reciever_id', $to);
    $this->db->group_end();
    $this->db->order_by('id', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get()->result();

    // Use centralized message processing
    $query = $this->process_chat_messages($query, true, false);

    if ($query) {
      return $query;
    }

    return false;
  }

  /**
   * Get client messages
   *
   * @param string $from sender
   * @param string $to receiver
   * @param string $limit limit messages
   * @param string $offset offset
   *
   * @return mixed
   */
  public function getMutualMessages($from, $to, $limit, $offset)
  {
    $this->db->select('id, sender_id, reciever_id, message, time_sent, viewed, viewed_at, reactions, edited_at');
    $this->db->from(db_prefix() . 'chatclientmessages');
    $this->db->group_start();
    $this->db->where('sender_id', $to)->where('reciever_id', $from);
    $this->db->or_where('sender_id', $from)->where('reciever_id', $to);
    $this->db->group_end();
    $this->db->order_by('id', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get()->result();

    $query = $this->process_chat_messages($query, true, true);

    if ($query) {
      return $query;
    }

    return false;
  }

  /**
   * Get last message preview for multiple client contacts in a single query.
   *
   * @param string $staff_id   The staff identifier (e.g. "staff_1")
   * @param array  $contact_ids Array of contact IDs (numeric)
   *
   * @return array Associative array keyed by contact_id with last message data
   */
  public function getClientContactPreviews($staff_id, $contact_ids)
  {
    if (empty($contact_ids)) {
      return [];
    }

    $prefix = db_prefix();
    $results = [];
    $contact_ids = array_map('intval', $contact_ids);

    // Build the list of "client_X" identifiers
    $client_ids = array_map(function ($id) {
      return 'client_' . $id;
    }, $contact_ids);

    // Escape for IN clause
    $escaped = array_map(function ($cid) {
      return "'" . $this->db->escape_str($cid) . "'";
    }, $client_ids);
    $in_clause = implode(',', $escaped);

    $escaped_staff = "'" . $this->db->escape_str($staff_id) . "'";

    // Single query: get the latest message per contact pair
    $sql = "SELECT m.id, m.sender_id, m.reciever_id, m.message, m.time_sent
                FROM {$prefix}chatclientmessages m
                INNER JOIN (
                    SELECT
                        CASE
                            WHEN sender_id = {$escaped_staff} THEN reciever_id
                            ELSE sender_id
                        END AS contact_identifier,
                        MAX(id) AS max_id
                    FROM {$prefix}chatclientmessages
                    WHERE (sender_id = {$escaped_staff} OR reciever_id = {$escaped_staff})
                    GROUP BY contact_identifier
                ) latest ON m.id = latest.max_id
                WHERE latest.contact_identifier IN ({$in_clause})";

    $query = $this->db->query($sql);

    if ($query && $query->num_rows() > 0) {
      foreach ($query->result() as $row) {
        $contact_key = ($row->sender_id === $staff_id) ? $row->reciever_id : $row->sender_id;
        $numeric_id = str_replace('client_', '', $contact_key);
        $results[$numeric_id] = [
          'message' => $row->message,
          'time_sent' => $row->time_sent,
          'sender_id' => $row->sender_id,
          'time_sent_formatted' => _dt($row->time_sent),
        ];
      }
    }

    return $results;
  }

  /**
   * Get last message preview for multiple groups in a single query.
   *
   * @param array $group_ids Array of group IDs (numeric)
   *
   * @return array Associative array keyed by group_id with last message data
   */
  public function getGroupPreviews($group_ids)
  {
    if (empty($group_ids)) {
      return [];
    }

    $prefix = db_prefix();
    $group_ids = array_map('intval', $group_ids);
    $results = [];

    $escaped = implode(',', $group_ids);

    $sql = "SELECT m.group_id, m.sender_id, m.message, m.time_sent,
                       CONCAT(s.firstname, ' ', s.lastname) AS sender_fullname
                FROM {$prefix}chatgroupmessages m
                INNER JOIN (
                    SELECT group_id, MAX(id) AS max_id
                    FROM {$prefix}chatgroupmessages
                    WHERE group_id IN ({$escaped})
                    GROUP BY group_id
                ) latest ON m.id = latest.max_id
                LEFT JOIN {$prefix}staff s ON s.staffid = m.sender_id";

    $query = $this->db->query($sql);

    if ($query && $query->num_rows() > 0) {
      foreach ($query->result() as $row) {
        $results[$row->group_id] = [
          'message' => $row->message,
          'time_sent' => $row->time_sent,
          'sender_id' => $row->sender_id,
          'sender_fullname' => $row->sender_fullname ?? '',
          'time_sent_formatted' => _dt($row->time_sent),
        ];
      }
    }

    return $results;
  }

  /**
   * Get group messages
   *
   * @param string $group_id group_id
   * @param string $limit limit messages
   * @param string $offset offset
   *
   * @return mixed
   */
  public function getGroupMessages($group_id, $limit, $offset)
  {
    if ($group_id !== null) {
      // Optimized: Select only needed columns and use proper escaping
      $this->db->select('id, sender_id, group_id, message, time_sent, reactions, edited_at');
      $this->db->from(db_prefix() . 'chatgroupmessages');
      $this->db->where('group_id', $group_id);
      $this->db->order_by('id', 'DESC');
      $this->db->limit($limit, $offset);

      $query = $this->db->get()->result();
      $created_by = $this->db->get_where(db_prefix() . 'chatgroups', ['id' => $group_id])->row('created_by_id');

      foreach ($query as &$chat) {
        $chat->message = html_entity_decode($chat->message, ENT_QUOTES, 'UTF-8');

        $hasMention = strpos($chat->message, 'user_mentioned') !== false;
        $hasEmoji = strpos($chat->message, '"emoji"') !== false;
        $hasQuickMention = strpos($chat->message, 'quickMentionLink') !== false;

        if (!$hasMention && !$hasEmoji && !$hasQuickMention) {
          $chat->message = pr_chat_convertLinkImageToString($chat->message);
        }

        $chat->message = clickable($chat->message);

        $this->processAudioMessage($chat);

        $chat->user_image = $this->getUserImage($chat->sender_id);
        $chat->sender_fullname = get_staff_full_name($chat->sender_id);
        $chat->time_sent_formatted = _dt($chat->time_sent);
        $chat->created_by_id = $created_by;
      }

      $newQuery['messages'] = $query;

      $this->db->select('member_id, group_id, lastname, firstname, created_by_id, profile_image');
      $this->db->from(TABLE_CHATGROUPMEMBERS);
      $this->db->where('group_id', $group_id);
      $this->db->join(TABLE_STAFF, '' . TABLE_STAFF . '.staffid=' . TABLE_CHATGROUPMEMBERS . '.member_id');
      $this->db->join(TABLE_CHATGROUPS, '' . TABLE_CHATGROUPS . '.id=' . TABLE_CHATGROUPMEMBERS . '.group_id');
      $result = $this->db->get();
      $users = $result->result_array();

      // Generate proper avatar URLs (handles missing thumbnails gracefully)
      foreach ($users as &$u) {
        $u['avatar_url'] = staff_profile_image_url($u['member_id']);
      }
      $newQuery['users'] = $users;

      $groupRow = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id])->row();

      $newQuery['separete_group_id'] = $group_id;
      $newQuery['separete_group_name'] = $groupRow ? $groupRow->group_name : '';

      // Include association data
      if ($groupRow && !empty($groupRow->related_type) && !empty($groupRow->related_id)) {
        $resolved = $this->resolveRelatedItem($groupRow->related_type, (int) $groupRow->related_id);
        $newQuery['related_type'] = $groupRow->related_type;
        $newQuery['related_id'] = $groupRow->related_id;
        $newQuery['related_name'] = $resolved['name'];
        $newQuery['related_url'] = $resolved['url'];
      }

      if ($newQuery) {
        return $newQuery;
      }
    } else {
      return false;
    }

    return false;
  }

  /**
   * Process message content if it contains audio elements
   *
   * @param object $chat Chat message object
   */
  private function processAudioMessage($chat)
  {
    if (preg_match('~\b(src|audio|controls|ogg)\b~i', $chat->message)) {
      $chat->message = html_entity_decode($chat->message);
    }
  }


  /**
   * Toggle emoji reaction for a legacy chat message.
   *
   * Reactions stored as JSON in TEXT column `reactions`, shaped as:
   *   {
   *     "👍": ["1","5"],
   *     "❤️": ["client_3"]
   *   }
   *
   * @param int $messageId
   * @param string $emoji
   * @param string $userKey
   * @param string $type staff|client|group
   * @return string|null Updated reactions JSON, or null when empty
   */
  public function toggleMessageReaction(int $messageId, string $emoji, string $userKey, string $type): ?string
  {
    $tables = [
      'staff' => db_prefix() . 'chatmessages',
      'client' => db_prefix() . 'chatclientmessages',
      'group' => db_prefix() . 'chatgroupmessages',
    ];

    if (!isset($tables[$type])) {
      return null;
    }

    $table = $tables[$type];

    $row = $this->db
      ->select('reactions')
      ->from($table)
      ->where('id', $messageId)
      ->limit(1)
      ->get()
      ->row();

    $reactions = [];
    if ($row && !empty($row->reactions)) {
      $decoded = json_decode($row->reactions, true);
      if (is_array($decoded)) {
        $reactions = $decoded;
      }
    }

    // Check if user already reacted with this exact emoji (toggle off)
    $hadSameEmoji = isset($reactions[$emoji])
      && is_array($reactions[$emoji])
      && in_array($userKey, $reactions[$emoji], true);

    // Remove user from ALL emojis (one reaction per user per message)
    foreach ($reactions as $e => &$list) {
      if (!is_array($list)) {
        $list = [];
        continue;
      }
      $pos = array_search($userKey, $list, true);
      if ($pos !== false) {
        unset($list[$pos]);
        $list = array_values($list);
      }
      if (empty($list)) {
        unset($reactions[$e]);
      }
    }
    unset($list);

    // If clicking the same emoji → just remove (toggle off). Otherwise add new.
    if (!$hadSameEmoji) {
      if (!isset($reactions[$emoji])) {
        $reactions[$emoji] = [];
      }
      $reactions[$emoji][] = $userKey;
    }

    $newRaw = empty($reactions) ? null : json_encode($reactions);

    $this->db->where('id', $messageId)->update($table, ['reactions' => $newRaw]);

    return $newRaw;
  }

  /**
   * Groups history for messages
   *
   * @param string $group_id group_id
   * @param string $limit limit messages
   * @param string $offset offset
   *
   * @return mixed
   */
  public function getGroupMessagesHistory($group_id, $limit, $offset)
  {
    $group_id = (int) $group_id;
    $limit = (int) $limit;
    $offset = (int) $offset;

    $this->db->where('group_id', $group_id);
    $this->db->order_by('id', 'DESC');
    $this->db->limit($limit, $offset);
    $query = $this->db->get(db_prefix() . 'chatgroupmessages')->result();
    $created_by = $this->db->get_where(db_prefix() . 'chatgroups', ['id' => $group_id])->row('created_by_id');

    foreach ($query as &$chat) {
      $chat->message = html_entity_decode($chat->message, ENT_QUOTES, 'UTF-8');

      $hasMention = strpos($chat->message, 'user_mentioned') !== false;
      $hasEmoji = strpos($chat->message, '"emoji"') !== false;
      $hasQuickMention = strpos($chat->message, 'quickMentionLink') !== false;

      if (!$hasMention && !$hasEmoji && !$hasQuickMention) {
        $chat->message = pr_chat_convertLinkImageToString($chat->message);
      }

      $chat->message = clickable($chat->message);
      $chat->user_image = $this->getUserImage($chat->sender_id);
      $chat->sender_fullname = get_staff_full_name($chat->sender_id);
      $chat->time_sent_formatted = _dt($chat->time_sent);
      $chat->created_by_id = $created_by;
    }

    if ($query) {
      return $query;
    }

    return false;
  }

  /**
   * Get unread messages for the logged in user.
   *
   * @return mixed
   */
  public function getUnread()
  {
    $unreadMessages = [];

    $staff_id = $this->get_current_staff_id();

    $this->db->select('sender_id');
    $this->db->where('reciever_id', $staff_id);
    $this->db->where('viewed', 0);
    $this->db->where('sender_id >', 0);
    $query = $this->db->get(db_prefix() . 'chatmessages');

    if ($query) {
      $result = $query->result_array();
      foreach ($result as $sender) {
        $sender_id = 'sender_id_' . $sender['sender_id'];
        if (array_key_exists($sender_id, $unreadMessages)) {
          $unreadMessages['' . $sender_id . '']['count_messages'] = $unreadMessages['' . $sender_id . '']['count_messages'] + 1;
        } else {
          $unreadMessages['' . $sender_id . ''] = ['sender_id' => $sender['sender_id'], 'count_messages' => 1];
        }
      }
      if ($result) {
        return $unreadMessages;
      }
    }

    return false;
  }

  /**
   * Get client unread messages for the logged in user / admin.
   *
   * @return array
   */
  public function getClientUnreadMessages()
  {
    $unreadMessages = [];

    $staff_id = 'staff_' . get_staff_user_id();
    $current_staff_id = get_staff_user_id();

    $this->db->select('sender_id');
    $this->db->where('reciever_id', $staff_id);
    $this->db->where('viewed', 0);
    $query = $this->db->get(db_prefix() . 'chatclientmessages');

    if ($query) {
      $result = $query->result_array();

      // Get authorized clients for this staff member
      $authorizedClients = get_staff_customers(30, 0, true);

      // Build a quick lookup array of authorized contact IDs
      $authorizedContactIds = [];
      if (is_array($authorizedClients)) {
        foreach ($authorizedClients as $client) {
          if (isset($client['contact_id'])) {
            $authorizedContactIds[$client['contact_id']] = true;
          }
        }
      }

      foreach ($result as $sender) {
        $sender_id = '_' . $sender['sender_id'];
        $contact_id = str_replace('client_', '', $sender['sender_id']);

        // Check if staff has access to this client (admins see all)
        if (!is_admin($current_staff_id) && !isset($authorizedContactIds[$contact_id])) {
          continue; // Skip this unread message if staff doesn't have access
        }

        if (array_key_exists($sender_id, $unreadMessages)) {
          $unreadMessages['' . $sender_id . '']['count_messages'] = $unreadMessages['' . $sender_id . '']['count_messages'] + 1;
        } else {
          $unreadMessages['' . $sender_id . ''] = ['sender_id' => $sender['sender_id'], 'count_messages' => 1];
          $unreadMessages['' . $sender_id . '']['client_data'] = getOwnClient($contact_id);
        }
      }

      if ($result) {
        return $unreadMessages;
      } else {
        return ['result' => false];
      }
    }

    return false;
  }

  /**
   * Get client unread messages for the logged in user / admin.
   *
   * @return mixed
   */
  public function getStaffUnreadMessages($pusher)
  {
    $unreadMessages = [];

    $contact_id = 'client_' . get_contact_user_id();

    $this->db->select('sender_id, reciever_id, id AS msg_id');
    $this->db->where('reciever_id', $contact_id);
    $this->db->where('viewed', 0);
    $query = $this->db->get(db_prefix() . 'chatclientmessages');

    if ($query) {
      $result = $query->result_array();

      foreach ($result as $sender) {
        $sender_id = '_' . $sender['sender_id'];

        if (array_key_exists($sender_id, $unreadMessages)) {
          $unreadMessages['' . $sender_id . '']['count_messages'] = $unreadMessages['' . $sender_id . '']['count_messages'] + 1;
        } else {
          $unreadMessages['' . $sender_id . ''] = ['sender_id' => $sender['sender_id'], 'count_messages' => 1];
        }
      }
      if ($result) {
        return $unreadMessages;
      }
    }

    return false;
  }

  /**
   * Update unread for sender.
   *
   * @param string $id sender id
   *
   * @return mixed
   */
  public function updateUnread($pusher, $id)
  {
    $staff_id = $this->get_current_staff_id();

    // CRITICAL FIX: Only select unread messages where current user is the receiver
    $this->db->select('id as msg_id, sender_id, reciever_id, viewed_at');
    $this->db->from(db_prefix() . "chatmessages");
    $this->db->where('viewed', 0);
    $this->db->where('reciever_id', $staff_id);
    $this->db->where('sender_id', $id);
    $messages = $this->db->get()->result();

    if ($messages) {
      $viewed_at = date('Y-m-d H:i:s');
      $this->db->where('viewed', 0);
      $this->db->where('reciever_id', $staff_id);
      $this->db->where('sender_id', $id);
      $update_data = [
        'viewed' => 1,
        'viewed_at' => $viewed_at
      ];
      $query = $this->db->update(db_prefix() . 'chatmessages', $update_data);

      if ($query) {
        // Add viewed_at to each message for Pusher event using Perfex CRM date/time settings
        foreach ($messages as &$msg) {
          $msg->viewed_at = $viewed_at;
          $msg->viewed_at_formatted = _dt($viewed_at);
        }
        $this->setMessageAsViewed($pusher, $messages);
        return true;
      }
    }

    return false;
  }

  /**
   * Update unread for client sender.
   *
   * @param string $id sender id
   * @param        $pusher
   * @param null   $to_client
   *
   * @return mixed
   */
  public function updateClientUnreadMessages($id, $pusher, $to_client = null, $passed_contact_id = null)
  {
    // Use passed contact_id if available, otherwise try to get from session
    $contact_id = $passed_contact_id ? $passed_contact_id : get_contact_user_id();
    $query = '';
    $messages = '';

    $this->db->select('id as msg_id,sender_id, reciever_id, viewed_at, viewed');
    $this->db->from(db_prefix() . "chatclientmessages");

    if ($to_client && $contact_id) {
      $staff_id = str_replace('staff_', '', $id);
      $staff_id = (int) $staff_id;
      $contact_id = (int) $contact_id;

      $this->db->where('sender_id', 'staff_' . $staff_id);
      $this->db->where('reciever_id', 'client_' . $contact_id);
      $this->db->where('viewed', 0);
      $messages = $this->db->get()->result();
    } else {
      $staff_id = $this->get_current_staff_id();
      $contact_id = str_replace('client_', '', $id);
      $contact_id = (int) $contact_id;

      $this->db->where('sender_id', 'client_' . $contact_id);
      $this->db->where('reciever_id', 'staff_' . $staff_id);
      $this->db->where('viewed', 0);
      $messages = $this->db->get()->result();
    }

    if ($messages) {
      $viewed_at = date('Y-m-d H:i:s');
      foreach ($messages as $message) {
        $update_data = [
          'viewed' => 1,
          'viewed_at' => $viewed_at
        ];

        if ($to_client != null) {
          /** From staff to client */
          $this->db->where('sender_id', 'staff_' . $staff_id);
          $this->db->where('reciever_id', 'client_' . $contact_id);
          $this->db->where('id', $message->msg_id);
          $this->db->where('viewed', 0);
          $query = $this->db->update(db_prefix() . 'chatclientmessages', $update_data);
        } else {
          /** From client to staff */
          $this->db->where('reciever_id', 'staff_' . $staff_id);
          $this->db->where('sender_id', 'client_' . $contact_id);
          $this->db->where('id', $message->msg_id);
          $this->db->where('viewed', 0);
          $query = $this->db->update(db_prefix() . 'chatclientmessages', $update_data);
        }
      }


      if ($query) {
        // Add viewed_at to each message for Pusher event using Perfex CRM date/time settings
        foreach ($messages as &$msg) {
          $msg->viewed_at = $viewed_at;
          $msg->viewed_at_formatted = _dt($viewed_at);
        }
        $this->setMessageAsViewed($pusher, $messages);
        return true;
      }
    } else {
      return false;
    }


    return false;
  }

  /**
   * Set current chat theme.
   *
   * @param string $id the staff id
   * @param string $theme_name 1 or 0 light or dark
   */
  public function updateChatTheme($id, $theme_name)
  {
    $name = 'current_theme';
    $theme_name = $theme_name ?: 'chat_light';

    $exists = $this->db->where('user_id', $id)->where('name', $name)
      ->get(db_prefix() . 'chatsettings')->row();

    if ($exists) {
      $this->db->where('user_id', $id)->where('name', $name)
        ->update(db_prefix() . 'chatsettings', ['value' => $theme_name]);
    } else {
      $this->db->insert(db_prefix() . 'chatsettings', ['user_id' => $id, 'name' => $name, 'value' => $theme_name]);
    }

    return $theme_name;
  }



  /**
   * Set the chat color.
   *
   * @param string $id the staff id
   * @param string $color the color to set
   */
  public function setChatColor($color)
  {
    $id = get_staff_user_id();
    $name = 'chat_color';

    $color = validateChatColorBeforeApply($color, true);

    if ($color === 'unknownColor') {
      $message['success'] = $color;

      return $message;
    }

    if ($this->db->field_exists('value', db_prefix() . 'chatsettings')) {
      $exists = $this->db->where('user_id', $id)->where('name', $name)
        ->get(db_prefix() . 'chatsettings')->row();
      if ($exists) {
        $this->db->where('user_id', $id)->where('name', $name)
          ->update(db_prefix() . 'chatsettings', ['value' => $color]);
      } else {
        $this->db->insert(db_prefix() . 'chatsettings', ['user_id' => $id, 'name' => $name, 'value' => $color]);
      }
      if ($this->db->affected_rows() != 0) {
        $message['success'] = $color;

        return $message;
      }
      $message['success'] = false;

      return $message;
    } else {
      $this->db->where('user_id', $id);
      $this->db->where('name', $name);
      $exsists = $this->db->get(db_prefix() . 'chatsettings')->row();
      if (!$exsists == null) {
        $this->db->where('user_id', $id);
        $this->db->where('name', $name);
        $this->db->update(db_prefix() . 'chatsettings', ['chat_color' => $color]);
      } else {
        $this->db->insert(db_prefix() . 'chatsettings', ['chat_color' => $color, 'user_id' => $id]);
      }
      if ($this->db->affected_rows() != 0) {
        $message['success'] = $color;

        return $message;
      }
      $message['success'] = false;

      return $message;
    }
  }

  /**
   * Delete group shared files and audio from folder.
   *
   * @param string $group_id
   *
   * @return boolean
   */
  public function deleteGroupSharedFiles($group_id)
  {
    $group_id = (int) $group_id;
    $files = $this->db->select('file_name')->where('group_id', $group_id)->get(db_prefix() . 'chatgroupsharedfiles')->result_array();

    $this->db->select('message');
    $this->db->where('group_id', $group_id);
    $this->db->like('message', '.ogg');
    $audio_files = $this->db->get(db_prefix() . 'chatgroupmessages')->result_array();

    if (count($audio_files) > 0) {
      foreach ($audio_files as $file) {
        $this->handleAudioDeleteFile($file['message']);
      }
    }

    if (!empty($files) && is_array($files)) {
      foreach ($files as $file) {
        if (is_dir(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER)) {
          unlink(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . $file['file_name']);
        }
      }
      $this->db->where('group_id', $group_id);
      $result = $this->db->delete(TABLE_CHATGROUPSHAREDFILES);
      if ($result) {
        return true;
      }
    } else {
      return false;
    }

    return false;
  }


  public function deleteClientMessage($message_id)
  {
    $table = db_prefix() . 'chatclientmessages';
    $possible_file = $this->db->select('id, message')->where("id", $message_id)->get($table)->row();

    if (!$possible_file) {
      return false;
    }

    // Handle audio/voice file deletion (works for audio folder)
    $this->handleAudioDeleteFile($possible_file);

    // Handle regular file attachments (images, documents, etc.)
    if (prchat_checkMessageIfFileExists($possible_file->message)) {
      $file_name = getImageFullName($possible_file->message);
      // Only delete from /clients/ if it's not a voice file
      if ($file_name && stripos($file_name, 'voice_') === false) {
        $clientFilePath = PR_CHAT_MODULE_UPLOAD_FOLDER . '/clients/' . $file_name;
        if (file_exists($clientFilePath)) {
          @unlink($clientFilePath);
        }
      }
    }

    $this->db->where('id', $message_id);
    return $this->db->delete($table, ['id' => $message_id]);
  }


  /**
   * Delete chat message including associated files (handles both group and direct messages)
   *
   * @param string $id Message ID to delete
   * @param string $conversation_identifier Either 'group_id{ID}' for groups or user ID for direct messages
   *
   * @return boolean True if deleted successfully, false otherwise
   */
  public function deleteMessage($id, $conversation_identifier)
  {

    if ($conversation_identifier && strpos($conversation_identifier, 'group_id') !== false) {
      $group_id = str_replace('group_id', '', $conversation_identifier);
      $row = $this->db->select('message')->where('group_id', $group_id)->where('id', $id)->get(db_prefix() . 'chatgroupmessages')->row();

      if (!$row) {
        return false;
      }

      // Handle audio/voice file deletion
      $this->handleAudioDeleteFile($row);

      // Handle regular file attachments
      if (prchat_checkMessageIfFileExists($row->message)) {
        $file_name = getImageFullName($row->message);
        // Only delete from /groups/ if it's not a voice file
        if ($file_name && stripos($file_name, 'voice_') === false) {
          $groupFilePath = PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . $file_name;
          if (file_exists($groupFilePath)) {
            @unlink($groupFilePath);
          }
          $this->db->delete(db_prefix() . 'chatgroupsharedfiles', ['group_id' => $group_id, 'file_name' => $file_name]);
        }
      }

      $this->db->where('id', $id);
      return $this->db->delete(db_prefix() . 'chatgroupmessages');
    } else {
      $row = $this->db->select('message')->where('id', $id)->get(db_prefix() . 'chatmessages')->row();
      if (!$row) {
        return false;
      }

      $possible_file = $row->message;

      // Handle audio/voice file deletion
      $this->handleAudioDeleteFile($possible_file);

      // Handle regular file attachments
      if (prchat_checkMessageIfFileExists($possible_file)) {
        $file_name = getImageFullName($possible_file);
        // Only delete from main uploads if it's not a voice file
        if ($file_name && stripos($file_name, 'voice_') === false) {
          $mainFilePath = PR_CHAT_MODULE_UPLOAD_FOLDER . '/' . $file_name;
          if (file_exists($mainFilePath)) {
            @unlink($mainFilePath);
          }
        }
      }

      $this->db->where('id', $id);
      return $this->db->delete(db_prefix() . 'chatmessages');
    }

    return false;
  }

  /**
   * Handle deleting audio/voice files (supports URLs, HTML, and standalone filenames)
   *
   * @param object|string $possible_file
   *
   * @return string
   */
  private function handleAudioDeleteFile($possible_file)
  {
    if (isset($possible_file->message)) {
      $possible_file = $possible_file->message;
    }

    $finallyFileToDelete = '';
    $file_to_delete = html_entity_decode((string) $possible_file, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Full URL pointing at /modules/.../audio/...
    if (preg_match('/https?:\/\/[^"\'\s<>]+/i', $file_to_delete, $urlMatch)) {
      $parsedUrl = parse_url($urlMatch[0]);
      if (!empty($parsedUrl['path']) && preg_match('~/audio/(.+\.(?:webm|ogg|m4a))(?:\?.*)?$~i', $parsedUrl['path'], $am)) {
        $finallyFileToDelete = $am[1];
      }
    }

    // <audio> or <source src="...">
    if ($finallyFileToDelete === '' && preg_match('~<(?:audio|source)[^>]+src=["\']([^"\']+)~i', $file_to_delete, $srcMatch)) {
      $src = html_entity_decode($srcMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
      if (preg_match('~/audio/(.+\.(?:webm|ogg|m4a))(?:\?.*)?$~i', $src, $am)) {
        $finallyFileToDelete = $am[1];
      }
    }

    // DB stores: staff/9_voice_....webm or 9_voice_....webm (word boundary before "voice_" fails on "9_voice_")
    if ($finallyFileToDelete === '' && preg_match('~\b((?:staff|clients|groups)/)?([a-z0-9_]+_voice_[a-z0-9_]+\.(?:webm|ogg|m4a))\b~i', $file_to_delete, $m)) {
      $finallyFileToDelete = !empty($m[1]) ? $m[1] . $m[2] : $m[2];
    }

    if ($finallyFileToDelete !== '' && is_dir(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER)) {
      $safe = str_replace(['..', '\\'], '', $finallyFileToDelete);
      $audioPath = rtrim(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER, '/') . '/' . $safe;
      if (is_file($audioPath)) {
        @unlink($audioPath);
      }
    }

    return $finallyFileToDelete;
  }

  /**
   * Delete chat conversation including pictures and files (works for clients and staff members).
   *
   * @param string $id staff or client id
   * @param string $table name for deletion directions
   *
   * @return json|array
   */
  public function deleteMutualConversation($id, $table)
  {
    $response = [];

    $response['success'] = false;

    if ($table == 'chatmessages') {
      $staff_id = $this->get_current_staff_id();
      $sender_id = (int) $id;
      $receiver_id = $staff_id;
    } else {
      $sender_id = 'client_' . (int) $id;
      $receiver_id = 'staff_' . get_staff_user_id();
    }

    $this->db->select('message');
    $this->db->group_start();
    $this->db->where('sender_id', $sender_id);
    $this->db->where('reciever_id', $receiver_id);
    $this->db->group_end();
    $this->db->or_group_start();
    $this->db->where('reciever_id', $sender_id);
    $this->db->where('sender_id', $receiver_id);
    $this->db->group_end();

    $possible_files = $this->db->get(db_prefix() . $table)->result_array();

    if ($possible_files) {
      foreach ($possible_files as $file) {
        if (prchat_checkMessageIfFileExists($file['message'])) {
          $file_name = getImageFullName($file['message']);

          if ($file_name == 'audio&amp;gt;') {
            $file['message'] = str_replace('&amp;lt;audio controls src=&quot;', "", $file['message']);
            $file['message'] = str_replace("&quot; type=&quot;audio/ogg&quot;&amp;gt;&amp;lt;/audio&amp;gt;", "", $file['message']);
            $file_name = pathinfo($file['message'], PATHINFO_BASENAME);
            if (strlen($file_name)) {
              if (is_dir(PR_CHAT_MODULE_UPLOAD_FOLDER . '/audio')) {
                @unlink(PR_CHAT_MODULE_UPLOAD_FOLDER . '/audio/' . $file_name);
              }
            }
          }

          if (is_dir(PR_CHAT_MODULE_UPLOAD_FOLDER)) {
            @unlink(PR_CHAT_MODULE_UPLOAD_FOLDER . '/' . $file_name);
          }
        }
      }

      $this->db->group_start();
      $this->db->where('sender_id', $sender_id);
      $this->db->where('reciever_id', $receiver_id);
      $this->db->group_end();
      $this->db->or_group_start();
      $this->db->where('reciever_id', $sender_id);
      $this->db->where('sender_id', $receiver_id);
      $this->db->group_end();

      $files_deleted = $this->db->delete(db_prefix() . $table);
    } else {
      return $response;
    }

    if ($files_deleted) {
      $response['success'] = true;

      return $response;
    }

    return $response;
  }


  /**
   * Delete chat conversation including pictures and files (works for clients and staff members and groups).
   *
   * @param $type 'staff, clients, groups or all'
   *
   * @return bool
   */
  public function purgeConversations($type)
  {
    $response['success'] = false;

    if ($type == 'chatbot') {
      try {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->truncate(db_prefix() . 'chatbot_conversation_notes');
        $this->db->truncate(db_prefix() . 'chatbot_conversation_reads');
        $this->db->truncate(db_prefix() . 'chatbot_messages');
        $this->db->truncate(db_prefix() . 'chatbot_conversations');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        return ['success' => true];
      } catch (Exception $e) {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        return ['success' => false, 'error' => $e->getMessage()];
      }
    }

    if ($type == 'all') {
      try {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->truncate(db_prefix() . 'chatmessages');
        $this->db->truncate(db_prefix() . 'chatclientmessages');
        $this->db->truncate(db_prefix() . 'chatgroupmessages');
        $this->db->truncate(db_prefix() . 'chatgroupsharedfiles');
        $this->db->truncate(db_prefix() . 'chatbot_conversation_notes');
        $this->db->truncate(db_prefix() . 'chatbot_conversation_reads');
        $this->db->truncate(db_prefix() . 'chatbot_messages');
        $this->db->truncate(db_prefix() . 'chatbot_conversations');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $this->_purgeUploadedFiles(PR_CHAT_MODULE_UPLOAD_FOLDER);
        $this->_purgeUploadedFiles(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER);
        $this->_purgeUploadedFiles(PR_CHAT_MODULE_UPLOAD_FOLDER . '/clients');
        // Purge voice files from all subfolders
        $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff');
        $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/clients');
        $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups');

        return ['success' => true];
      } catch (Exception $e) {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        return ['success' => false, 'error' => $e->getMessage()];
      }
    }

    $typeConfig = [
      'staff' => [
        'table' => db_prefix() . 'chatmessages',
        'upload_dir' => PR_CHAT_MODULE_UPLOAD_FOLDER,
      ],
      'clients' => [
        'table' => db_prefix() . 'chatclientmessages',
        'upload_dir' => PR_CHAT_MODULE_UPLOAD_FOLDER . '/clients',
      ],
      'groups' => [
        'table' => db_prefix() . 'chatgroupmessages',
        'upload_dir' => PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER,
      ],
    ];

    if (!isset($typeConfig[$type])) {
      return $response;
    }

    $config = $typeConfig[$type];

    try {
      $this->_purgeUploadedFiles($config['upload_dir']);
      // Purge voice files from subfolders
      $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/staff');
      $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/clients');
      $this->_purgeUploadedFiles(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER . '/groups');

      if ($type == 'groups') {
        $this->db->truncate(db_prefix() . 'chatgroupsharedfiles');
      }

      $this->db->truncate($config['table']);
      $response['success'] = true;
    } catch (Exception $e) {
      $response['error'] = $e->getMessage();
    }

    return $response;
  }

  /**
   * Delete all uploaded files in a directory, preserving index.html and the folder itself.
   */
  private function _purgeUploadedFiles($dir)
  {
    if (!is_dir($dir)) {
      return;
    }

    $files = glob($dir . '/*');

    foreach ($files as $file) {
      if (is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.gitignore') {
        @unlink($file);
      }
    }
  }

  /**
   * Handles shared files between two users.
   *
   * @param string $own_id session id
   * @param        $contact_id
   *
   * @return string
   */
  public function get_shared_files_and_create_template($own_id, $contact_id)
  {
    $files = [];

    $allFiles = CHAT_ALL_FILE_EXTENSIONS;
    $photoExtensions = CHAT_PHOTO_EXTENSIONS;
    $docFiles = CHAT_DOCUMENT_EXTENSIONS;

    // Determine conversation type and get messages in a single optimized query
    $isClientConversation = $this->isClientConversation($own_id, $contact_id);
    $messages = $this->getFileMessages($own_id, $contact_id, $isClientConversation);

    if (empty($messages)) {
      return false;
    }

    // Extract filenames from message URLs
    foreach ($messages as $message) {
      $messageText = $message['message'];

      // Extract filenames from message URLs (handle both relative and absolute URLs)
      if (preg_match_all('/(?:https?:\/\/[^\/]+)?\/modules\/prchat\/uploads\/(?:clients\/)?([^\/\s<>"\']+\.[a-zA-Z0-9]+)/', $messageText, $matches)) {
        foreach ($matches[1] as $fileName) {
          // Only include files with valid extensions and avoid duplicates
          if (preg_match("/^[^\?]+\.($allFiles)$/", $fileName) && !in_array($fileName, $files, true)) {
            $files[] = $fileName;
          }
        }
      }
    }

    $html = '';
    $html .= '<ul class="nav nav-tabs" role="tablist">';
    $html .= '<li class="active"><a href="#photos" role="tab" data-toggle="tab"><i class="fa-regular fa-file-image icon_shared_files" aria-hidden="true"></i>' . _l('chat_photos_text') . '</a></li>';
    $html .= '<li><a href="#files" role="tab" data-toggle="tab"><i class="fa fa-file icon_shared_files" aria-hidden="true"></i>' . _l('chat_files_text') . '</a></li>';
    $html .= '</ul>';

    $html .= '<div class="tab-content">';
    $html .= '<div class="tab-pane active" id="photos">';
    $html .= '<span class="text-center shared_items_span">' . _l('chat_shared_photos_text') . '</span>';

    foreach ($files as $file) {
      if (preg_match("/^[^\?]+\.(" . $photoExtensions . ")$/", $file)) {
        $basePath = $isClientConversation ? 'modules/prchat/uploads/clients/' : 'modules/prchat/uploads/';
        $file_url = base_url($basePath . $file);
        $html .= "<a href='" . $file_url . "' onclick=\"openImageModal('" . $file_url . "', '" . htmlspecialchars($file) . "'); return false;\">
               <div class='col-xs-3 shared_files_ahref' style='background-image:url(" . $file_url . ");'></div></a>";
      }
    }
    $html .= '</div>';
    $html .= '<div class="tab-pane" id="files">';
    $html .= '<span class="text-center shared_items_span">' . _l('chat_shared_files_text') . '</span>';

    foreach ($files as $file) {
      if (preg_match("/^[^\?]+\.(" . $docFiles . ")$/", $file)) {
        $basePath = $isClientConversation ? 'modules/prchat/uploads/clients/' : 'modules/prchat/uploads/';
        $file_url = base_url($basePath . $file);
        $html .= "<div class='col-md-12'><a target='_blank' href ='" . $file_url . "'><i class='fa fa-file icon_shared_files' aria-hidden='true'></i>" . $file . '</a></div>';
      }
    }
    $html .= '</div></div>';

    return $html;
  }

  /**
   * Handles shared files between users in group.
   *
   * @param string $group_id
   *
   * @return string
   */
  public function get_group_shared_files_and_create_template($group_id)
  {
    $files = [];

    $allFiles = CHAT_ALL_FILE_EXTENSIONS;
    $photoExtensions = CHAT_PHOTO_EXTENSIONS;
    $docFiles = CHAT_DOCUMENT_EXTENSIONS;

    $dir = list_files(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER);

    $group_id = (int) $group_id;
    $this->db->select('file_name');
    $this->db->where('group_id', $group_id);
    $this->db->where("file_name REGEXP '^.*\\.(" . $allFiles . ")$'", null, false);
    $from_messages_table = $this->db->get(db_prefix() . 'chatgroupsharedfiles');

    if ($from_messages_table) {
      $from_messages_table = $from_messages_table->result_array();
    } else {
      return false;
    }
    foreach ($dir as $file_name) {
      foreach ($from_messages_table as $value) {
        if (strpos($file_name, $value['file_name']) !== false) {
          if (!in_array($file_name, $files)) {
            $files[] = $file_name;
          }
        }
      }
    }



    $html = '';
    $html .= '<ul class="nav nav-tabs" role="tablist">';
    $html .= '<li class="active"><a href="#group_photos" role="tab" data-toggle="tab"><i class="fa-regular fa-file-image icon_shared_files" aria-hidden="true"></i>' . _l('chat_photos_text') . '</a></li>';
    $html .= '<li><a href="#group_files" role="tab" data-toggle="tab"><i class="fa fa-file icon_shared_files" aria-hidden="true"></i>' . _l('chat_files_text') . '</a></li>';
    $html .= '</ul>';

    $html .= '<div class="tab-content">';
    $html .= '<div class="tab-pane active" id="group_photos">';
    $html .= '<span class="text-center shared_items_span">' . _l('chat_shared_photos_text') . '</span>';

    foreach ($files as $file) {
      if (preg_match("/^[^\?]+\.(" . $photoExtensions . ")$/", $file)) {
        $html .= "<a href='" . base_url('modules/prchat/uploads/groups/' . $file) . "' onclick=\"openImageModal('" . base_url('modules/prchat/uploads/groups/' . $file) . "', '" . htmlspecialchars($file) . "'); return false;\">
               <div class='col-md-3 shared_files_ahref' style='background-image:url(" . base_url('modules/prchat/uploads/groups/' . $file) . ");'></div></a>";
      }
    }
    $html .= '</div>';
    $html .= '<div class="tab-pane" id="group_files">';
    $html .= '<span class="text-center shared_items_span">' . _l('chat_shared_files_text') . '</span>';

    foreach ($files as $file) {
      if (preg_match("/^[^\?]+\.('" . $docFiles . "')$/", $file)) {
        $html .= "<div class='col-md-12'><a target='_blank' href ='" . base_url('modules/prchat/uploads/groups/' . $file) . "'><i class='fa fa-file icon_shared_files' aria-hidden='true'></i>" . $file . '</a></div>';
      }
    }
    $html .= '</div></div>';

    return $html;
  }

  /**
   * globalMessage Sends global message to selected members
   *
   * @param array    $members
   * @param string   $message
   * @param instance $pusher
   *
   * @return boolean
   */
  public function globalMessage($members, $message, $pusher)
  {
    if ($message == '' || empty($members)) {
      return false;
    }

    if (isset($members) && (is_array($members) && !empty($members))) {

      $message = _l('chat_message_announce') . $message;

      foreach ($members as $member_id) {

        $this->chat_model->createMessage(
          [
            'sender_id' => get_staff_user_id(),
            'reciever_id' => $member_id,
            'message' => htmlspecialchars($message),
            'time_sent' => date('Y-m-d H:i:s'),
            'viewed' => 0,
          ],
          db_prefix() . 'chatmessages'
        );

        $pusher->trigger(
          'presence-mychanel',
          'send-event',
          [
            'message' => pr_chat_convertLinkImageToString($message),
            'from' => get_staff_user_id(),
            'to' => $member_id,
            'global' => true,
          ]
        );
      }

      return true;
    }
  }


  /**
   * Announcement Event to clients
   *
   * @param array    $clients
   * @param string   $message
   * @param instance $pusher
   *
   * @return boolean
   */
  public function announcementToClients($clients, $message, $pusher)
  {
    if ($message == '' || empty($clients)) {
      return false;
    }

    $staffUserId = get_staff_user_id();

    if (isset($clients) && (is_array($clients) && !empty($clients))) {

      foreach ($clients as $client_id) {

        $this->chat_model->createMessage(
          [
            'sender_id' => 'staff_' . $staffUserId,
            'reciever_id' => 'client_' . $client_id,
            'message' => htmlspecialchars($message),
            'time_sent' => date('Y-m-d H:i:s')
          ],
          db_prefix() . 'chatclientmessages'
        );

        $pusher->trigger(
          'presence-clients',
          'send-event',
          [
            'message' => pr_chat_convertLinkImageToString($message),
            'from' => 'staff_' . $staffUserId,
            'from_name' => get_staff_full_name($staffUserId),
            'to' => 'client_' . $client_id,
            'massMessage' => true
          ]
        );
      }
      return true;
    }
  }


  /**
   * Function that handles new chat group creation.
   *
   * @param array    $insertData
   * @param array    $data
   * @param instance $pusher
   */
  public function addChatGroup($insertData, $data, $pusher)
  {
    $own_id = $this->session->userdata('staff_user_id');

    $this->db->insert(TABLE_CHATGROUPS, $insertData);

    $insert_id = $this->db->insert_id();

    $presence_data = $data;

    foreach ($data['members'] as $member_id) {
      $this->db->insert(TABLE_CHATGROUPMEMBERS, ['group_name' => $data['group_name'], 'member_id' => $member_id, 'group_id' => $insert_id]);
    }

    $presence_data['group_id'] = $insert_id;
    $presence_data['result'] = 'success';
    $presence_data['created_by_id'] = $own_id;
    $presence_data['message'] = _l('chat_new_group_created_text');

    if ($pusher->trigger('group-chat', 'group-chat', $presence_data)) {
      echo json_encode(['data' => $presence_data]);
    } else {
      echo json_encode(['result' => 'error']);
    }
  }

  /**
   * Function that fetches all logged in user chat groups
   *
   * @return json
   */
  public function getMyGroups()
  {
    $id = (int) get_staff_user_id();

    $this->db->order_by('id', 'ASC');
    $groups = $this->db->get(TABLE_CHATGROUPS)->result_array();

    $this->db->trans_start();

    foreach ($groups as $key => $group) {
      $this->db->select('member_id, firstname, lastname, group_id');
      $this->db->from(TABLE_CHATGROUPMEMBERS);
      $this->db->join(TABLE_STAFF, TABLE_STAFF . '.staffid = ' . TABLE_CHATGROUPMEMBERS . '.member_id');
      $this->db->where('group_id', (int) $group['id']);
      $this->db->where('member_id', $id);
      $groups[$key]['members'] = $this->db->get()->result_array();

      // Resolve related item name and URL
      if (!empty($group['related_type']) && !empty($group['related_id'])) {
        $resolved = $this->resolveRelatedItem($group['related_type'], (int) $group['related_id']);
        $groups[$key]['related_name'] = $resolved['name'];
        $groups[$key]['related_url'] = $resolved['url'];
      }
    }

    if ($this->db->trans_complete()) {
      if (!empty($groups)) {
        echo json_encode(['groups' => $groups]);
      } else {
        echo json_encode(['noChannels' => true]);
      }
    }
  }

  /**
   * Resolves a related item name and admin URL by type and id
   *
   * @param string $type
   * @param int    $id
   * @return array ['name' => string, 'url' => string]
   */
  private function resolveRelatedItem($type, $id)
  {
    $name = '';
    $url = '';

    switch ($type) {
      case 'project':
        $row = $this->db->select('name')->where('id', $id)->get(db_prefix() . 'projects')->row();
        if ($row) {
          $name = $row->name;
          $url = admin_url('projects/view/' . $id);
        }
        break;
      case 'invoice':
        $row = $this->db->select('id, number, prefix')->where('id', $id)->get(db_prefix() . 'invoices')->row();
        if ($row) {
          $name = ($row->prefix ?? '') . $row->number;
          $url = admin_url('invoices/list_invoices/' . $id);
        }
        break;
      case 'estimate':
        $row = $this->db->select('id, number, prefix')->where('id', $id)->get(db_prefix() . 'estimates')->row();
        if ($row) {
          $name = ($row->prefix ?? '') . $row->number;
          $url = admin_url('estimates/list_estimates/' . $id);
        }
        break;
      case 'contract':
        $row = $this->db->select('id, subject')->where('id', $id)->get(db_prefix() . 'contracts')->row();
        if ($row) {
          $name = $row->subject;
          $url = admin_url('contracts/contract/' . $id);
        }
        break;
      case 'ticket':
        $row = $this->db->select('ticketid, subject')->where('ticketid', $id)->get(db_prefix() . 'tickets')->row();
        if ($row) {
          $name = '#' . $row->ticketid . ' - ' . $row->subject;
          $url = admin_url('tickets/ticket/' . $id);
        }
        break;
      case 'lead':
        $row = $this->db->select('id, name')->where('id', $id)->get(db_prefix() . 'leads')->row();
        if ($row) {
          $name = $row->name;
          $url = admin_url('leads/index/' . $id);
        }
        break;
      case 'task':
        $row = $this->db->select('id, name')->where('id', $id)->get(db_prefix() . 'tasks')->row();
        if ($row) {
          $name = $row->name;
          $url = admin_url('tasks/view/' . $id);
        }
        break;
    }

    return ['name' => $name ?: ucfirst($type) . ' #' . $id, 'url' => $url];
  }

  /**
   * Public wrapper for resolveRelatedItem (used by controller)
   *
   * @param string $type
   * @param int    $id
   * @return array ['name' => string, 'url' => string]
   */
  public function resolveRelatedItemPublic($type, $id)
  {
    return $this->resolveRelatedItem($type, $id);
  }

  /**
   * Function that fetches all logged in user chat groups
   *
   * @return json
   */
  public function getChatGroups()
  {
    $id = get_staff_user_id();

    $this->db->where('member_id', $id);
    $groups = $this->db->get(db_prefix() . 'chatgroupmembers')->result_array();

    if ($groups) {
      foreach ($groups as &$group) {
        $group['group_name'] = str_replace(CHAT_GROUP_PRESENCE_PREFIX, '', $group['group_name']);
      }
    }

    return $groups;
  }

  /**
   * Function that is responsible when deleting a selected group.
   *
   * @param string   $group_id
   * @param string   $group_name
   * @param instance $pusher
   *
   * @return void
   */
  public function deleteGroup($group_id, $group_name, $pusher)
  {
    $group_members = $this->db->get(TABLE_CHATGROUPMEMBERS)->result_array();

    $this->db->trans_start();
    foreach ($group_members as $member) {
      if ($member['group_id'] == $group_id) {

        $this->chat_model->deleteGroupSharedFiles($group_id);

        $this->db->where('group_id', $group_id);
        $this->db->delete(TABLE_CHATGROUPMEMBERS);

        $this->db->where('group_id', $group_id);
        $this->db->delete(TABLE_CHATGROUPMESSAGES);
      }
    }

    if ($this->db->trans_complete()) {
      $this->db->where('id', $group_id);
      $this->db->delete(TABLE_CHATGROUPS);

      try {
        $channel = preg_replace('/[^a-zA-Z0-9_\-=@,.;]/', '-', $group_name);
        $presence_data = ['result' => 'true', 'group_name' => $group_name, 'group_id' => $group_id];
        $pusher->trigger($channel, 'group-deleted', $presence_data);
      } catch (Exception $e) {
        log_message('error', 'Prchat: Pusher trigger failed for group deletion - ' . $e->getMessage());
      }

      if ($this->db->affected_rows() !== 0) {
        echo json_encode(['result' => 'success']);
      } else {
        echo json_encode(['error' => 'no_more_groups_to_delete']);
      }
    } else {
      echo json_encode(['result' => 'failed']);
    }
  }

  /**
   * Function that handles when adding new members to chat groups.
   *
   * @param string   $group_name
   * @param string   $group_id
   * @param array    $members
   * @param instance $pusher
   */
  public function addChatGroupMembers($group_name, $group_id, $members, $pusher)
  {
    $member_ids = [];

    foreach ($members as $member_id) {
      $this->db->insert(TABLE_CHATGROUPMEMBERS, ['group_name' => $group_name, 'member_id' => $member_id, 'group_id' => $group_id]);
      $member_ids[] = $member_id;
    }

    $presence_data = [
      'group_name' => $group_name,
      'result' => 'success',
      'group_id' => $group_id,
      'user_ids' => $member_ids,
    ];

    if ($this->db->affected_rows() != 0) {
      if ($pusher->trigger('group-chat', 'added-to-channel', $presence_data)) {
        echo json_encode(['data' => $presence_data]);
      }
    } else {
      echo json_encode(['data' => 'failed']);
    }
  }

  /**
   * Function that fetches all chat group members.
   *
   * @param string @group_id
   *
   * @return void
   */
  public function getGroupUsers($group_id)
  {
    $group_name = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id])->row('group_name');

    $this->db->select('member_id, group_id, lastname, firstname, created_by_id, profile_image');
    $this->db->from(TABLE_CHATGROUPMEMBERS);
    $this->db->where('group_id', $group_id);
    $this->db->join(TABLE_STAFF, '' . TABLE_STAFF . '.staffid=' . TABLE_CHATGROUPMEMBERS . '.member_id');
    $this->db->join(TABLE_CHATGROUPS, '' . TABLE_CHATGROUPS . '.id=' . TABLE_CHATGROUPMEMBERS . '.group_id');
    $query = $this->db->get();
    $groupUsers = $query->result_array();

    // Generate proper avatar URLs via Perfex helper (handles missing thumbs)
    foreach ($groupUsers as &$user) {
      $user['avatar_url'] = staff_profile_image_url($user['member_id']);
    }

    echo json_encode(['result' => 'success', 'users' => $groupUsers]);
  }

  /**
   * Function that fetches all members connected with specific group.
   *
   * @param string $group_id
   *
   * @return array
   */
  public function getCurrentGroupUsers($group_id)
  {
    $group_id = $group_id ?: $this->input->post('group_id');
    $this->db->select('member_id, lastname, firstname, profile_image');
    $this->db->from(TABLE_CHATGROUPMEMBERS);
    $this->db->where('group_id', $group_id);
    $this->db->join(TABLE_STAFF, TABLE_STAFF . '.staffid=' . TABLE_CHATGROUPMEMBERS . '.member_id');
    $query = $this->db->get();
    $users = $query->result_array();

    if ($this->db->affected_rows() !== 0) {
      return $users;
    }

    return [];
  }

  /**
   * Private function that checks if specific group is created by logged in user.
   *
   * @param string $group_id
   * @param string $user_id
   *
   * @return mixed
   */
  private function isGroupCreatedBy($group_id, $user_id)
  {
    $result = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id, 'created_by_id' => $user_id])->row('created_by_id');

    if ($result !== null) {
      return $result;
    }

    return false;
  }

  /**
   * Function that removes user from specific group
   *
   * @param string   $group_name
   * @param string   $group_id
   * @param string   $user_id
   * @param string   $own_id
   * @param object $pusher
   *
   * @return void
   */
  public function removeChatGroupUser($group_name, $group_id, $user_id, $own_id, $pusher)
  {
    // First, check if the member exists before trying to delete
    $this->db->where('group_id', $group_id);
    $this->db->where('member_id', $user_id);
    $member_exists = $this->db->get(TABLE_CHATGROUPMEMBERS);

    if (!$member_exists || $member_exists->num_rows() == 0) {
      echo json_encode(['response' => 'error', 'message' => 'Member not found in group']);
      return;
    }

    $groupCreatedBy = $this->isGroupCreatedBy($group_id, $user_id);

    // This means that an admin has removed the creator of the group and group is assigned to this admin automatically
    if ($groupCreatedBy == $user_id && $groupCreatedBy != $own_id) {
      $this->db->where('id', $group_id);
      $this->db->update(TABLE_CHATGROUPS, ['created_by_id' => $own_id]);
    }

    // Now delete the member - only use group_id and member_id for precision
    $this->db->where('group_id', $group_id);
    $this->db->where('member_id', $user_id);
    $deleted = $this->db->delete(TABLE_CHATGROUPMEMBERS);

    $presence_data['group_id'] = $group_id;
    $presence_data['group_name'] = $group_name;
    $presence_data['user_id'] = $user_id;

    if ($deleted && $this->db->affected_rows() > 0) {
      $presence_data['created_by_me'] = $this->isGroupCreatedBy($group_id, $own_id);
      $pusher->trigger('group-chat', 'removed-from-channel', $presence_data);
      echo json_encode(['response' => 'success', 'data' => $presence_data]);
    } else {
      echo json_encode(['response' => 'error', 'message' => 'Failed to remove member from group - no rows affected', 'data' => $presence_data]);
    }
  }

  /**
   * Function that handles when user leaves chat group.
   *
   * @param string   $group_id
   * @param string   $member_id
   * @param instance $pusher
   *
   * @return void
   */
  public function chatMemberLeaveGroup($group_id, $member_id, $pusher)
  {
    $userFullName = get_staff_full_name();

    $group_name = $this->db->get_where(TABLE_CHATGROUPS, ['id' => $group_id])->row('group_name');

    $this->db->where('group_id', $group_id);
    $this->db->where('member_id', $member_id);
    $deleted = $this->db->delete(TABLE_CHATGROUPMEMBERS);

    $presence_data = [
      'group_name' => $group_name,
      'group_id' => $group_id,
      'member_id' => $member_id,
      'user_full_name' => $userFullName,
    ];

    if ($deleted) {
      $pusher->trigger('group-chat', 'member-left-channel', $presence_data);
      echo json_encode(['message' => 'deleted']);
    }
  }

  /**
   * Record clients and customer admins message into database.
   *
   * @param array @message_data
   *
   * @return bool
   */
  public function recordClientMessage($message_data)
  {


    if ($this->db->insert(db_prefix() . 'chatclientmessages', $message_data)) {
      return $this->db->insert_id();
    }

    return false;
  }

  /**
   * Live ajax search for clients
   *
   * @param string @search
   *
   * @return array
   */
  public function searchClients($search)
  {
    $search = mb_substr(trim($search), 0, 200);
    $escaped = $this->db->escape_like_str($search);

    $this->db->select(db_prefix() . 'clients.userid as client_id, ' . db_prefix() . 'contacts.id as contact_id, company, firstname, lastname, title');

    $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid = ' . db_prefix() . 'clients.userid');

    $this->db->group_start();
    $this->db->like('company', $escaped, 'both', false);
    $this->db->or_like('firstname', $escaped, 'both', false);
    $this->db->or_like('lastname', $escaped, 'both', false);
    $this->db->group_end();

    $this->db->from(db_prefix() . 'clients');

    $query = $this->db->get()->result_array();

    if (count($query) > 0) {
      foreach ($query as $key => $contact) {
        $query[$key]['profile_image_url'] = contact_profile_image_url($contact['contact_id']);
      }
    }

    return $query;
  }

  /**
   * Live ajax search for staff
   *
   * @param string @search
   *
   * @return array
   */
  public function searchStaff($search)
  {
    $search = mb_substr(trim($search), 0, 200);
    $escaped = $this->db->escape_like_str($search);

    $this->db->group_start();
    $this->db->like('firstname', $escaped, 'both', false);
    $this->db->or_like('lastname', $escaped, 'both', false);
    $this->db->group_end();
    $this->db->from(db_prefix() . 'staff');

    $query = $this->db->get()->result_array();

    if (count($query) > 0) {
      foreach ($query as $key => $staff) {
        $query[$key]['profile_image_url'] = staff_profile_image_url($staff['staffid']);
      }
    }

    return $query;
  }

  /**
   * Load more staff members for pagination
   *
   * @param int $offset Database offset for pagination
   * @return array Staff members array
   */
  public function loadMoreStaffMembers($offset)
  {
    $this->db->select('staffid, firstname, lastname, profile_image');

    return $this->db->get(db_prefix() . 'staff', CHAT_STAFF_PAGINATION_LIMIT, $offset)->result_array();
  }

  /**
   * AJAX search for staff members.
   * Returns [{id, name, subtext}] for ajaxSelectPicker compatibility.
   * When $search is empty returns the 10 most recently active staff.
   *
   * @param string $search
   * @param array  $excludeIds  Staff IDs to exclude (e.g. existing group members)
   * @param array  $limitToIds  If non-empty, only return staff in this list
   * @return array
   */
  public function searchStaffAjax($search = '', $excludeIds = [], $limitToIds = [])
  {
    $this->db->select('staffid, firstname, lastname, admin, role, last_activity, active');

    $currentStaffId = get_staff_user_id();
    if ($currentStaffId) {
      $this->db->where('staffid !=', $currentStaffId);
    }

    if (!empty($excludeIds)) {
      $this->db->where_not_in('staffid', array_map('intval', $excludeIds));
    }

    if (!empty($limitToIds)) {
      $this->db->where_in('staffid', array_map('intval', $limitToIds));
    }

    $search = trim($search);
    $isSearch = ($search !== '');

    if ($isSearch) {
      $escaped = $this->db->escape_like_str($search);
      $this->db->group_start();
      $this->db->like('firstname', $escaped, 'both', false);
      $this->db->or_like('lastname', $escaped, 'both', false);
      $this->db->or_like("CONCAT(firstname, ' ', lastname)", $escaped, 'both', false);
      $this->db->group_end();
      $this->db->order_by('firstname', 'ASC');
    } else {
      $this->db->where('active', 1);
      $this->db->order_by('last_activity', 'DESC');
    }

    $rows = $this->db->get(db_prefix() . 'staff')->result_array();

    $result = [];
    foreach ($rows as $row) {
      if (!defined('PRCHAT_AJAX_SKIP_PERM_CHECK')) {
        if (!staff_can('view', PR_CHAT_MODULE_NAME, $row['staffid'])) {
          continue;
        }
      }
      $subtext = '';
      if ($row['admin']) {
        $subtext = _l('chat_admin_role');
      } elseif (!empty($row['role'])) {
        $roleName = get_staff_userrole($row['role']);
        if ($roleName) {
          $subtext = $roleName;
        }
      }
      $result[] = [
        'id'      => $row['staffid'],
        'name'    => $row['firstname'] . ' ' . $row['lastname'],
        'subtext' => $subtext,
      ];
    }

    if (!$isSearch) {
      $result = array_slice($result, 0, 10);
    }

    return $result;
  }

  /**
   * AJAX search for client contacts.
   * Returns [{id, name, subtext}] for ajaxSelectPicker compatibility.
   * When $search is empty returns the 10 most recently active contacts.
   *
   * @param string $search
   * @return array
   */
  public function searchClientsAjax($search = '')
  {
    $this->db->select('ct.id, ct.firstname, ct.lastname, c.company, c.userid as client_userid');
    $this->db->from(db_prefix() . 'contacts ct');
    $this->db->join(db_prefix() . 'clients c', 'c.userid = ct.userid', 'left');

    $search = trim($search);
    if ($search !== '') {
      $escaped = $this->db->escape_like_str($search);
      $this->db->group_start();
      $this->db->like('ct.firstname', $escaped, 'both', false);
      $this->db->or_like('ct.lastname', $escaped, 'both', false);
      $this->db->or_like('c.company', $escaped, 'both', false);
      $this->db->or_like("CONCAT(ct.firstname, ' ', ct.lastname)", $escaped, 'both', false);
      $this->db->group_end();
      $this->db->order_by('ct.firstname', 'ASC');
    } else {
      $this->db->where('c.active', 1);
      $this->db->where('ct.active', 1);
      $this->db->order_by('ct.last_login', 'DESC');
      $this->db->limit(10);
    }
    $rows = $this->db->get()->result_array();

    $result = [];
    foreach ($rows as $row) {
      $result[] = [
        'id'      => $row['id'],
        'name'    => $row['firstname'] . ' ' . $row['lastname'],
        'subtext' => $row['company'] ?? '',
      ];
    }

    return $result;
  }

  /**
   * Get customer  company name
   *
   * @param string @client_id
   *
   * @return object
   */



  /**
   * Get staff message history (client ? staff )
   *
   * @param string @user_id
   * @param string @table
   *
   * @return array
   */
  public function getMessagesHistoryBetween($user_id, $table)
  {
    $to = get_staff_user_id();
    $contact_full_name = '';

    if ($table === 'chatclientmessages') {
      $to = 'staff_' . $to;
      $contact_full_name = get_contact_full_name(str_replace('client_', '', $user_id));
    }

    $allowedTables = ['chatmessages', 'chatclientmessages'];
    if (!in_array($table, $allowedTables)) {
      return [];
    }

    $to = $this->db->escape($to);
    $user_id_esc = $this->db->escape($user_id);
    $sql = 'SELECT * FROM ' . db_prefix() . $table . " WHERE (sender_id = {$to} AND reciever_id = {$user_id_esc}) OR (sender_id = {$user_id_esc} AND reciever_id = {$to}) ORDER BY id DESC";

    $query = $this->db->query($sql)->result_array();

    foreach ($query as &$chat) {
      $chat['message'] = pr_chat_convertLinkImageToString($chat['message']);
      $chat['message'] = stripslashes(htmlentities($chat['message'], ENT_QUOTES));

      $chat['message'] = str_replace("&amp;lt;a class=&amp;quot;chat_ticket_link&amp;quot; href=&amp;quot;", "", $chat['message']);
      $chat['message'] = str_replace("&amp;quot; target=&amp;quot;_blank&amp;quot;&amp;gt;", " - ", $chat['message']);
      $chat['message'] = str_replace("&amp;lt;/a&amp;gt;", "", $chat['message']);
      $chat['message'] = str_replace("&amp;amp;lt;audio controls src=&amp;quot;", "", $chat['message']);
      $chat['message'] = str_replace("&amp;quot; type=&amp;quot;audio/ogg&amp;quot;&amp;amp;gt;&amp;amp;lt;/audio&amp;amp;gt;", "", $chat['message']);


      /**
       * New line break blows return json so need to replace with spaces
       */
      if (strpos($chat['message'], "\n") !== false) {
        $chat['message'] = preg_replace('/\s+/', ' ', trim($chat['message']));
      }

      $chat['sender_fullname'] = get_staff_full_name(str_replace('staff_', '', $chat['sender_id']));
      ($contact_full_name !== '') ? $chat['contact_fullname'] = $contact_full_name : '';
      $chat['user_image_path'] = contact_profile_image_url(str_replace('staff_', '', $chat['sender_id']));
    }

    // Must put in session because it messes up everything down when searching, this way works perfect
    $this->session->set_userdata('chat_messages_count', sizeof($query));

    if ($query) {
      return $query;
    }
  }

  /**
   * Export messages to CSV file
   *
   * @param string $to
   *
   * @return void
   */
  public function initiateExportToCSV($to)
  {
    $table = 'chatmessages';

    $staff_id = $this->get_current_staff_id();

    if (strpos($to, 'client') !== false) {
      $staff_id = 'staff_' . get_staff_user_id() . '';
      $table = 'chatclientmessages';
    } else {
      $staff_id = $this->get_current_staff_id();
    }

    $filename = 'messages_' . date('Ymd') . '.csv';

    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Type: application/csv; ");


    $to_esc = $this->db->escape($to);
    $staff_esc = $this->db->escape($staff_id);
    $sql = 'SELECT * FROM ' . db_prefix() . $table . " WHERE (sender_id = {$to_esc} AND reciever_id = {$staff_esc}) OR (sender_id = {$staff_esc} AND reciever_id = {$to_esc}) ORDER BY id DESC";

    $user_messages = $this->db->query($sql)->result_array();


    foreach ($user_messages as &$user) {
      // Get system default time format
      $user['time_sent'] = _dt($user['time_sent']);

      if ($table == 'chatclientmessages') {
        (strpos($user['sender_id'], 'client') !== false)
          ? $user['sender_id'] = get_contact_full_name(str_replace('client_', '', $user['sender_id']))
          : $user['sender_id'] = get_staff_full_name(str_replace('staff_', '', $user['sender_id']));

        (strpos($user['reciever_id'], 'client') !== false)
          ? $user['reciever_id'] = get_contact_full_name(str_replace('client_', '', $user['reciever_id']))
          : $user['reciever_id'] = get_staff_full_name(str_replace('staff_', '', $user['reciever_id']));
      } else {
        $user['sender_id'] = get_staff_full_name($user['sender_id']);
        $user['reciever_id'] = get_staff_full_name($user['reciever_id']);
      }
    }

    // Create new csv file
    $file = fopen('php://output', 'w');

    $header = [
      "" . _l('chat_header_message_id') . "",
      "" . _l('chat_header_from') . "",
      "" . _l('chat_header_send_to') . "",
      "" . _l('chat_header_message') . "",
      "" . _l('chat_header_is_read') . "",
      "" . _l('chat_header_deleted') . "",
      "" . _l('chat_viewed_at_datetime') . "",
      "" . _l('chat_header_datetime') . "",
    ];


    if ($table == 'chatclientmessages') {
      unset($header[5]);
    }

    fputcsv($file, $header);

    foreach ($user_messages as $line) {
      $line['message'] = '[ ' . _l("chat_export_message") . $line["message"] . ' ]';
      $line['message'] = str_replace("&lt;a class=&quot;chat_ticket_link&quot; href=&quot;", "", $line['message']);
      $line['message'] = str_replace("&quot; target=&quot;_blank&quot;&gt;", " - ", $line['message']);
      $line['message'] = str_replace("&lt;/a&gt;", "", $line['message']);
      $line['message'] = str_replace("&amp;lt;audio controls src=&quot;", "", $line['message']);
      $line['message'] = str_replace("&quot; type=&quot;audio/ogg&quot;&amp;gt;&amp;lt;/audio&amp;gt;", "", $line['message']);
      fputcsv($file, $line);
    }

    fclose($file);
    exit;
  }


  /**
   * Handle Messages and get ready for support ticket conversation
   *
   * @param $user_id
   *
   * @return array
   */
  public function getMessagesForTicketConversion($user_id)
  {
    $to = get_staff_user_id();
    $contact_full_name = '';

    $table = 'chatclientmessages';
    $to = 'staff_' . $to;
    $contact_full_name = get_contact_full_name(str_replace('client_', '', $user_id));


    $to_esc = $this->db->escape($to);
    $uid_esc = $this->db->escape($user_id);
    $sql = 'SELECT id, message, sender_id, reciever_id, DATE_FORMAT(time_sent, "%Y-%m-%d") as time_sent FROM ' . db_prefix() . $table . " WHERE time_sent > NOW() - INTERVAL 48 HOUR AND ((sender_id = {$to_esc} AND reciever_id = {$uid_esc}) OR (sender_id = {$uid_esc} AND reciever_id = {$to_esc})) ORDER BY time_sent DESC";

    $query = $this->db->query($sql)->result_array();

    foreach ($query as &$chat) {
      $chat['message'] = stripslashes(htmlentities($chat['message'], ENT_QUOTES));
      $chat['sender_fullname'] = get_staff_full_name(str_replace('staff_', '', $chat['sender_id']));
      ($contact_full_name !== '') ? $chat['contact_fullname'] = $contact_full_name : '';

      if (strpos($chat['message'], "audio controls src=") != false) {
        $chat['message'] = str_replace("&amp;amp;lt;audio controls src=&amp;quot;", "", $chat['message']);
        $chat['message'] = str_replace("&amp;quot; type=&amp;quot;audio/ogg&amp;quot;&amp;amp;gt;&amp;amp;lt;/audio&amp;amp;gt;", "", $chat['message']);
      }
    }

    $this->session->set_userdata('chat_support_ticket_messages_count', sizeof($query));

    if ($query) {
      return $query;
    }
  }

  /**
   * Handle chat support ticket conversation
   *
   * @param array  $data
   * @param string $subject
   * @param string $department
   * @param string $assigned
   *
   * @return bool|\json
   */
  public function chatHandleSupportTicketCreation($data, $subject, $department, $assigned)
  {
    $html = '';

    if ($data === null) {
      echo json_encode(['message' => 'no_message']);
      return false;
    }

    foreach ($data as &$content) {
      $class = '';
      if (strpos($content['user_id'], 'client') !== false) {
        $class = 'chat_client_msg_style';
        $content['user_name'] = '<strong class="text-muted">' . _l('chat_lang_contact') . '</strong> ' . $content['user_name'];
      } else {
        $class = 'chat_staff_msg_style';
        $content['user_name'] = '<strong class="text-primary">' . _l('chat_staff_member') . '</strong> ' . $content['user_name'];
      }
      $html .= "<p class=" . $class . ">" . $content['user_name'] . " \n <strong>Message:</strong> " . $content['message'] . "</p>";
    }

    $client_id = str_replace('client_', '', $data[0]['client_id']);

    $update_data = [
      'subject' => $subject,
      'admin' => get_staff_user_id(),
      'message' => $html,
      'userid' => get_contact_customer_user_id($client_id),
      'contactid' => $client_id,
      'department' => $department,
    ];

    if ($assigned !== 0) {
      $update_data['assigned'] = get_staff_user_id();
    }

    $this->load->model('tickets_model');

    if ($this->tickets_model->add($update_data)) {
      echo json_encode(
        [
          'message' => 'success',
          'client_id' => $client_id,
          'ticket_id' => chat_get_tickets_last_inserted_row()
        ]
      );
    } else {
      echo json_encode(['success' => 'error']);
    }
  }

  /**
   * Update chat status
   *
   * @param string
   *
   * @return array
   */
  public function handleChatStatus($status)
  {
    $table = db_prefix() . 'chatsettings';
    $user_id = get_staff_user_id();
    $current_status = $this->db->get_where($table, ['user_id' => $user_id, 'name' => 'chat_status'])->result();

    if (empty($current_status)) {
      $this->db->insert($table, ['user_id' => $user_id, 'name' => 'chat_status', 'value' => $status]);
    } else {
      $this->db->where('user_id', $user_id);
      $this->db->where('name', 'chat_status');
      $this->db->update($table, ['value' => $status]);
    }
    if ($this->db->affected_rows() != 0) {
      return [
        'status' => $status,
        'user_id' => $user_id
      ];
    }

    return [
      'status' => 'same',
      'user_id' => $user_id
    ];
  }

  /**
   * Get pinned chat IDs for current user
   *
   * @param string $type Type: pinned_staff, pinned_groups, pinned_clients
   * @return array Array of pinned IDs
   */
  public function getPinnedChats(string $type): array
  {
    return $this->getChatSettingJsonArray($type);
  }

  /**
   * Toggle pin state for a chat
   *
   * @param string $type Type: pinned_staff, pinned_groups, pinned_clients
   * @param string $targetId The ID to pin/unpin
   * @return array ['pinned' => bool, 'ids' => array]
   */
  public function togglePinChat(string $type, string $targetId): array
  {
    return $this->toggleChatSettingJsonArray($type, $targetId);
  }

  /**
   * Get muted chat IDs for current user
   *
   * @param string $type Type: muted_staff, muted_groups, muted_clients
   * @return array Array of muted IDs
   */
  public function getMutedChats(string $type): array
  {
    return $this->getChatSettingJsonArray($type);
  }

  /**
   * Toggle mute state for a chat
   *
   * @param string $type Type: muted_staff, muted_groups, muted_clients
   * @param string $targetId The ID to mute/unmute
   * @return array ['muted' => bool, 'ids' => array]
   */
  public function toggleMuteChat(string $type, string $targetId): array
  {
    $result = $this->toggleChatSettingJsonArray($type, $targetId);
    return ['muted' => $result['added'], 'ids' => $result['ids']];
  }

  /**
   * Get all pin and mute settings for current user in one query
   *
   * @return array Associative array with pinned_staff, pinned_groups, pinned_clients, muted_staff, muted_groups, muted_clients
   */
  public function getAllPinMuteSettings(): array
  {
    $user_id = $this->get_current_staff_id();
    $names = ['pinned_staff', 'pinned_groups', 'pinned_clients', 'muted_staff', 'muted_groups', 'muted_clients'];

    $this->db->where('user_id', $user_id);
    $this->db->where_in('name', $names);
    $rows = $this->db->get(db_prefix() . 'chatsettings')->result_array();

    $settings = [];
    foreach ($names as $name) {
      $settings[$name] = [];
    }

    foreach ($rows as $row) {
      $decoded = json_decode($row['value'], true);
      $settings[$row['name']] = is_array($decoded) ? $decoded : [];
    }

    return $settings;
  }

  /**
   * Generic helper: get a JSON array setting from chatsettings
   */
  private function getChatSettingJsonArray(string $name): array
  {
    $user_id = $this->get_current_staff_id();
    $row = $this->db->get_where(db_prefix() . 'chatsettings', [
      'user_id' => $user_id,
      'name' => $name
    ])->row();

    if ($row && !empty($row->value)) {
      $decoded = json_decode($row->value, true);
      return is_array($decoded) ? $decoded : [];
    }

    return [];
  }

  /**
   * Generic helper: toggle an ID in a JSON array setting
   *
   * @return array ['added' => bool, 'ids' => array]
   */
  private function toggleChatSettingJsonArray(string $name, string $targetId): array
  {
    $user_id = $this->get_current_staff_id();
    $table = db_prefix() . 'chatsettings';
    $ids = $this->getChatSettingJsonArray($name);

    $key = array_search($targetId, $ids);
    if ($key !== false) {
      array_splice($ids, $key, 1);
      $added = false;
    } else {
      $ids[] = $targetId;
      $added = true;
    }

    $jsonValue = json_encode(array_values($ids));

    $existing = $this->db->get_where($table, ['user_id' => $user_id, 'name' => $name])->row();
    if ($existing) {
      $this->db->where('user_id', $user_id);
      $this->db->where('name', $name);
      $this->db->update($table, ['value' => $jsonValue]);
    } else {
      $this->db->insert($table, ['user_id' => $user_id, 'name' => $name, 'value' => $jsonValue]);
    }

    return ['added' => $added, 'ids' => $ids];
  }

  /**
   * Handles mention event in groups chat
   *
   * @param array @data
   * @param object Pusher
   *
   * @return void
   */
  public function handleMentionEvent($data, $pusher)
  {
    $userImage = $this->getUserImage($data['from']);
    $staff_fullname = get_staff_full_name($data['from']);

    $notify_users = [];
    $groupName = $data['channel'];
    $groupId = isset($data['group_id']) ? $data['group_id'] : '';

    foreach ($data['users'] as $user) {
      $notify_users[] = $user['user_id'];

      $link = 'prchat/Prchat_Controller/chat_full_view';
      if (!empty($groupId)) {
        $link .= '?tab=groups&group=' . $groupId;
      }

      add_notification([
        'description' => 'chat_mention_notification_desc',
        'touserid' => $user['user_id'],
        'fromuserid' => $data['from'],
        'fromcompany' => true,
        'link' => $link,
        'additional_data' => serialize([$staff_fullname, $groupName]),
      ]);
    }
    pusher_trigger_notification(array_unique($notify_users));

    // Slugify channel name for Pusher (spaces/special chars are invalid)
    $channelName = CHAT_GROUP_PRESENCE_PREFIX . slugifyGroupName($groupName);

    $pusher->trigger($channelName, 'mention-event', [
      'group_name' => $groupName,
      'from' => $data['from'],
      'users' => $data['users'],
      'userImage' => $userImage,
      'name' => $staff_fullname
    ]);
  }





  /**
   * Trigger new message seen event
   *
   * @param object $pusher
   * @param array  $messages
   *
   * @return void
   */
  public function setMessageAsViewed($pusher, $messages)
  {
    $pusher->trigger('user_messages', 'message_seen', $messages);
  }

  /**
   * Convert Unicode emojis to shortcodes for database storage
   * This ensures compatibility and prevents encoding issues
   *
   * @param string $message Message containing Unicode emojis
   * @return string Message with shortcodes
   */
  public function convert_emojis_to_shortcodes(string $message): string
  {
    if (empty($message)) {
      return $message;
    }

    // Comprehensive emoji to shortcode mapping
    $emoji_map = [
      // Smileys & Emotion
      '😀' => ':grinning:',
      '😃' => ':smiley:',
      '😄' => ':smile:',
      '😁' => ':grin:',
      '😆' => ':laughing:',
      '😅' => ':sweat_smile:',
      '🤣' => ':rofl:',
      '😂' => ':joy:',
      '🙂' => ':slightly_smiling_face:',
      '🙃' => ':upside_down_face:',
      '😉' => ':wink:',
      '😊' => ':blush:',
      '😇' => ':innocent:',
      '🥰' => ':smiling_face_with_hearts:',
      '😍' => ':heart_eyes:',
      '🤩' => ':star_struck:',
      '😘' => ':kissing_heart:',
      '😗' => ':kissing:',
      '😚' => ':kissing_closed_eyes:',
      '😙' => ':kissing_smiling_eyes:',
      '😋' => ':yum:',
      '😛' => ':stuck_out_tongue:',
      '😜' => ':stuck_out_tongue_winking_eye:',
      '🤪' => ':zany_face:',
      '😝' => ':stuck_out_tongue_closed_eyes:',
      '🤑' => ':money_mouth_face:',
      '🤗' => ':hugs:',
      '🤭' => ':hand_over_mouth:',
      '🤫' => ':shushing_face:',
      '🤔' => ':thinking:',
      '🤐' => ':zipper_mouth_face:',
      '🤨' => ':raised_eyebrow:',
      '😐' => ':neutral_face:',
      '😑' => ':expressionless:',
      '😶' => ':no_mouth:',
      '😏' => ':smirk:',
      '😒' => ':unamused:',
      '🙄' => ':roll_eyes:',
      '😬' => ':grimacing:',
      '🤥' => ':lying_face:',
      '😔' => ':pensive:',
      '😪' => ':sleepy:',
      '🤤' => ':drooling_face:',
      '😴' => ':sleeping:',
      '😷' => ':mask:',
      '🤒' => ':face_with_thermometer:',
      '🤕' => ':face_with_head_bandage:',
      '🤢' => ':nauseated_face:',
      '🤮' => ':vomiting_face:',
      '🤧' => ':sneezing_face:',
      '🥵' => ':hot_face:',
      '🥶' => ':cold_face:',
      '🥴' => ':woozy_face:',
      '😵' => ':dizzy_face:',
      '🤯' => ':exploding_head:',
      '🤠' => ':cowboy_hat_face:',
      '🥳' => ':partying_face:',
      '😎' => ':sunglasses:',
      '🤓' => ':nerd_face:',
      '🧐' => ':monocle_face:',
      '😕' => ':confused:',
      '😟' => ':worried:',
      '🙁' => ':slightly_frowning_face:',
      '☹️' => ':frowning_face:',
      '😮' => ':open_mouth:',
      '😯' => ':hushed:',
      '😲' => ':astonished:',
      '😳' => ':flushed:',
      '🥺' => ':pleading_face:',
      '😦' => ':frowning:',
      '😧' => ':anguished:',
      '😨' => ':fearful:',
      '😰' => ':cold_sweat:',
      '😥' => ':disappointed_relieved:',
      '😢' => ':cry:',
      '😭' => ':sob:',
      '😱' => ':scream:',
      '😖' => ':confounded:',
      '😣' => ':persevere:',
      '😞' => ':disappointed:',
      '😓' => ':sweat:',
      '😩' => ':weary:',
      '😫' => ':tired_face:',
      '🥱' => ':yawning_face:',
      '😤' => ':triumph:',
      '😡' => ':rage:',
      '😠' => ':angry:',
      '🤬' => ':cursing_face:',
      '😈' => ':smiling_imp:',
      '👿' => ':imp:',
      '💀' => ':skull:',
      '☠️' => ':skull_and_crossbones:',
      '💩' => ':poop:',
      '🤡' => ':clown_face:',
      '👹' => ':japanese_ogre:',
      '👺' => ':japanese_goblin:',
      '👻' => ':ghost:',
      '👽' => ':alien:',
      '👾' => ':space_invader:',
      '🤖' => ':robot:',

      // Hearts & Symbols
      '❤️' => ':heart:',
      '🧡' => ':orange_heart:',
      '💛' => ':yellow_heart:',
      '💚' => ':green_heart:',
      '💙' => ':blue_heart:',
      '💜' => ':purple_heart:',
      '🖤' => ':black_heart:',
      '🤍' => ':white_heart:',
      '🤎' => ':brown_heart:',
      '💔' => ':broken_heart:',
      '❣️' => ':heart_exclamation:',
      '💕' => ':two_hearts:',
      '💞' => ':revolving_hearts:',
      '💓' => ':heartbeat:',
      '💗' => ':heartpulse:',
      '💖' => ':sparkling_heart:',
      '💘' => ':cupid:',
      '💝' => ':gift_heart:',
      '💟' => ':heart_decoration:',

      // Common symbols
      '👍' => ':thumbs_up:',
      '👎' => ':thumbs_down:',
      '🔥' => ':fire:',
      '💯' => ':100:',
      '👌' => ':ok_hand:',
      '✌️' => ':victory_hand:',
      '🤞' => ':crossed_fingers:',
      '🤟' => ':love_you_gesture:',
      '🤘' => ':metal:',
      '🤙' => ':call_me_hand:',
      '👈' => ':point_left:',
      '👉' => ':point_right:',
      '👆' => ':point_up_2:',
      '🖕' => ':middle_finger:',
      '👇' => ':point_down:',
      '☝️' => ':point_up:',
      '👏' => ':clap:',
      '🙌' => ':raised_hands:',
      '👐' => ':open_hands:',
      '🤲' => ':palms_up_together:',
      '🙏' => ':pray:',
      '✍️' => ':writing_hand:',
      '💪' => ':muscle:',

      // Text shortcuts (common ones) - MUST match shortcode_map in convert_shortcodes_to_emojis
      '❤️' => '<3',
      '💔' => '</3',
      '🙂' => ':)',
      '🙁' => ':(',
      '😀' => ':D',
      '😉' => ';)',
      '😛' => ':P',
      '😐' => ':|',
      '😮' => ':o',

      // Missing mappings from frontend (26 total)
      '😌' => ':relieved:',
      '☮️' => ':peace_symbol:',
      '✝️' => ':latin_cross:',
      '☪️' => ':star_and_crescent:',
      '🕉️' => ':om:',
      '☸️' => ':wheel_of_dharma:',
      '✡️' => ':star_of_david:',
      '🔯' => ':six_pointed_star:',
      '🕎' => ':menorah:',
      '☯️' => ':yin_yang:',
      '☦️' => ':orthodox_cross:',
      '🛐' => ':place_of_worship:',
      '⛎' => ':ophiuchus:',
      '♈' => ':aries:',
      '♉' => ':taurus:',
      '♊' => ':gemini:',
      '♋' => ':cancer:',
      '♌' => ':leo:',
      '♍' => ':virgo:',
      '♎' => ':libra:',
      '♏' => ':scorpius:',
      '♐' => ':sagittarius:',
      '♑' => ':capricorn:',
      '♒' => ':aquarius:',
      '♓' => ':pisces:',
      '👋' => ':wave:',

      // Critical missing emojis that cause ???? corruption
      '🎉' => ':tada:',
      '🤳' => ':selfie:',
      '🤏' => ':pinching_hand:',
    ];

    // Protect URLs from emoji processing
    $urlMatches = [];
    $urlIndex = 0;

    // Replace URLs with placeholders
    $message = preg_replace_callback(
      '/(https?:\/\/[^\s<>"\']+)/i',
      function ($matches) use (&$urlMatches, &$urlIndex) {
        $placeholder = "__URL_PLACEHOLDER_{$urlIndex}__";
        $urlMatches[$urlIndex] = $matches[0];
        $urlIndex++;
        return $placeholder;
      },
      $message
    );

    // Convert emojis to shortcodes (process longer emojis first to avoid conflicts)
    uksort($emoji_map, function ($a, $b) {
      return strlen($b) - strlen($a);
    });
    $result = str_replace(array_keys($emoji_map), array_values($emoji_map), $message);

    // Restore URLs
    for ($i = 0; $i < count($urlMatches); $i++) {
      $placeholder = "__URL_PLACEHOLDER_{$i}__";
      $result = str_replace($placeholder, $urlMatches[$i], $result);
    }

    return $result;
  }

  /**
   * Convert shortcodes to Unicode emojis for display
   * This renders beautiful native emojis in the frontend
   *
   * @param string $message Message containing shortcodes
   * @return string Message with Unicode emojis
   */
  public function convert_shortcodes_to_emojis(string $message): string
  {
    if (empty($message)) {
      return $message;
    }

    // Comprehensive shortcode to emoji mapping (reverse of above)
    $shortcode_map = [
      // Smileys & Emotion
      ':grinning:' => '😀',
      ':smiley:' => '😃',
      ':smile:' => '😄',
      ':grin:' => '😁',
      ':laughing:' => '😆',
      ':sweat_smile:' => '😅',
      ':rofl:' => '🤣',
      ':joy:' => '😂',
      ':slightly_smiling_face:' => '🙂',
      ':upside_down_face:' => '🙃',
      ':wink:' => '😉',
      ':blush:' => '😊',
      ':innocent:' => '😇',
      ':smiling_face_with_hearts:' => '🥰',
      ':heart_eyes:' => '😍',
      ':star_struck:' => '🤩',
      ':kissing_heart:' => '😘',
      ':kissing:' => '😗',
      ':kissing_closed_eyes:' => '😚',
      ':kissing_smiling_eyes:' => '😙',
      ':yum:' => '😋',
      ':stuck_out_tongue:' => '😛',
      ':stuck_out_tongue_winking_eye:' => '😜',
      ':zany_face:' => '🤪',
      ':stuck_out_tongue_closed_eyes:' => '😝',
      ':money_mouth_face:' => '🤑',
      ':hugs:' => '🤗',
      ':hand_over_mouth:' => '🤭',
      ':shushing_face:' => '🤫',
      ':thinking:' => '🤔',
      ':zipper_mouth_face:' => '🤐',
      ':raised_eyebrow:' => '🤨',
      ':neutral_face:' => '😐',
      ':expressionless:' => '😑',
      ':no_mouth:' => '😶',
      ':smirk:' => '😏',
      ':unamused:' => '😒',
      ':roll_eyes:' => '🙄',
      ':grimacing:' => '😬',
      ':lying_face:' => '🤥',
      ':pensive:' => '😔',
      ':sleepy:' => '😪',
      ':drooling_face:' => '🤤',
      ':sleeping:' => '😴',
      ':mask:' => '😷',
      ':face_with_thermometer:' => '🤒',
      ':face_with_head_bandage:' => '🤕',
      ':nauseated_face:' => '🤢',
      ':vomiting_face:' => '🤮',
      ':sneezing_face:' => '🤧',
      ':hot_face:' => '🥵',
      ':cold_face:' => '🥶',
      ':woozy_face:' => '🥴',
      ':dizzy_face:' => '😵',
      ':exploding_head:' => '🤯',
      ':cowboy_hat_face:' => '🤠',
      ':partying_face:' => '🥳',
      ':sunglasses:' => '😎',
      ':nerd_face:' => '🤓',
      ':monocle_face:' => '🧐',
      ':confused:' => '😕',
      ':worried:' => '😟',
      ':slightly_frowning_face:' => '🙁',
      ':frowning_face:' => '☹️',
      ':open_mouth:' => '😮',
      ':hushed:' => '😯',
      ':astonished:' => '😲',
      ':flushed:' => '😳',
      ':pleading_face:' => '🥺',
      ':frowning:' => '😦',
      ':anguished:' => '😧',
      ':fearful:' => '😨',
      ':cold_sweat:' => '😰',
      ':disappointed_relieved:' => '😥',
      ':cry:' => '😢',
      ':sob:' => '😭',
      ':scream:' => '😱',
      ':confounded:' => '😖',
      ':persevere:' => '😣',
      ':disappointed:' => '😞',
      ':sweat:' => '😓',
      ':weary:' => '😩',
      ':tired_face:' => '😫',
      ':yawning_face:' => '🥱',
      ':triumph:' => '😤',
      ':rage:' => '😡',
      ':angry:' => '😠',
      ':cursing_face:' => '🤬',
      ':smiling_imp:' => '😈',
      ':imp:' => '👿',
      ':skull:' => '💀',
      ':skull_and_crossbones:' => '☠️',
      ':poop:' => '💩',
      ':clown_face:' => '🤡',
      ':japanese_ogre:' => '👹',
      ':japanese_goblin:' => '👺',
      ':ghost:' => '👻',
      ':alien:' => '👽',
      ':space_invader:' => '👾',
      ':robot:' => '🤖',

      // Hearts & Symbols
      ':heart:' => '❤️',
      ':orange_heart:' => '🧡',
      ':yellow_heart:' => '💛',
      ':green_heart:' => '💚',
      ':blue_heart:' => '💙',
      ':purple_heart:' => '💜',
      ':black_heart:' => '🖤',
      ':white_heart:' => '🤍',
      ':brown_heart:' => '🤎',
      ':broken_heart:' => '💔',
      ':heart_exclamation:' => '❣️',
      ':two_hearts:' => '💕',
      ':revolving_hearts:' => '💞',
      ':heartbeat:' => '💓',
      ':heartpulse:' => '💗',
      ':sparkling_heart:' => '💖',
      ':cupid:' => '💘',
      ':gift_heart:' => '💝',
      ':heart_decoration:' => '💟',

      // Common symbols
      ':thumbs_up:' => '👍',
      ':thumbsup:' => '👍',
      ':thumbs_down:' => '👎',
      ':fire:' => '🔥',
      ':100:' => '💯',
      ':ok_hand:' => '👌',
      ':victory_hand:' => '✌️',
      ':crossed_fingers:' => '🤞',
      ':love_you_gesture:' => '🤟',
      ':metal:' => '🤘',
      ':call_me_hand:' => '🤙',
      ':point_left:' => '👈',
      ':point_right:' => '👉',
      ':point_up_2:' => '👆',
      ':middle_finger:' => '🖕',
      ':point_down:' => '👇',
      ':point_up:' => '☝️',
      ':clap:' => '👏',
      ':raised_hands:' => '🙌',
      ':open_hands:' => '👐',
      ':palms_up_together:' => '🤲',
      ':pray:' => '🙏',
      ':writing_hand:' => '✍️',
      ':muscle:' => '💪',

      // Text shortcuts (common ones) - MUST match emoji_map in convert_emojis_to_shortcodes
      '<3' => '❤️',
      '</3' => '💔',
      ':)' => '🙂',
      ':(' => '🙁',
      ':D' => '😀',
      ';)' => '😉',
      ':P' => '😛',
      ':p' => '😛',
      ':|' => '😐',
      ':o' => '😮',
      ':O' => '😮',

      // Missing mappings from frontend (41 total)
      ':relieved:' => '😌',
      ':smiling_face_with_sunglasses:' => '😎',
      ':peace_symbol:' => '☮️',
      ':latin_cross:' => '✝️',
      ':star_and_crescent:' => '☪️',
      ':om:' => '🕉️',
      ':wheel_of_dharma:' => '☸️',
      ':star_of_david:' => '✡️',
      ':six_pointed_star:' => '🔯',
      ':menorah:' => '🕎',
      ':yin_yang:' => '☯️',
      ':orthodox_cross:' => '☦️',
      ':place_of_worship:' => '🛐',
      ':ophiuchus:' => '⛎',
      ':aries:' => '♈',
      ':taurus:' => '♉',
      ':gemini:' => '♊',
      ':cancer:' => '♋',
      ':leo:' => '♌',
      ':virgo:' => '♍',
      ':libra:' => '♎',
      ':scorpius:' => '♏',
      ':sagittarius:' => '♐',
      ':capricorn:' => '♑',
      ':aquarius:' => '♒',
      ':pisces:' => '♓',
      ':-)' => '🙂',
      ':-(' => '🙁',
      ':-D' => '😀',
      ':-P' => '😛',
      ':-p' => '😛',
      ';-)' => '😉',
      ':-o' => '😮',
      ':-O' => '😮',
      ':-|' => '😐',
      ':*' => '😘',
      ':-*' => '😘',
      ':thumbsdown:' => '👎',
      ':+1:' => '👍',
      ':-1:' => '👎',
      ':wave:' => '👋',

      // Critical missing emojis that cause ???? corruption
      ':tada:' => '🎉',
      ':party:' => '🎉',
      ':selfie:' => '🤳',
      ':pinching_hand:' => '🤏',
    ];

    // Protect URLs from emoji processing
    $urlMatches = [];
    $urlIndex = 0;

    // Replace URLs with placeholders
    $message = preg_replace_callback(
      '/(https?:\/\/[^\s<>"\']+)/i',
      function ($matches) use (&$urlMatches, &$urlIndex) {
        $placeholder = "__URL_PLACEHOLDER_{$urlIndex}__";
        $urlMatches[$urlIndex] = $matches[0];
        $urlIndex++;
        return $placeholder;
      },
      $message
    );

    // Convert shortcodes to emojis (process longer shortcodes first to avoid conflicts)
    uksort($shortcode_map, function ($a, $b) {
      return strlen($b) - strlen($a);
    });
    $result = str_replace(array_keys($shortcode_map), array_values($shortcode_map), $message);

    // Restore URLs
    for ($i = 0; $i < count($urlMatches); $i++) {
      $placeholder = "__URL_PLACEHOLDER_{$i}__";
      $result = str_replace($placeholder, $urlMatches[$i], $result);
    }

    return $result;
  }

  /**
   * Process message for storage: convert Unicode emojis to shortcodes
   * This is called before saving messages to the database
   *
   * @param string $message Raw message from frontend
   * @return string Processed message ready for database storage
   */
  public function process_message_for_storage(string $message): string
  {
    if (empty($message)) {
      return $message;
    }

    // Convert Unicode emojis to shortcodes for database storage
    $processed = $this->convert_emojis_to_shortcodes($message);

    return $processed;
  }

  /**
   * Process message for display: convert shortcodes to Unicode emojis
   * This is called when loading messages from the database for frontend display
   *
   * @param string $message Message from database
   * @return string Processed message ready for frontend display
   */
  public function process_message_for_display(string $message): string
  {
    if (empty($message)) {
      return $message;
    }

    // Convert shortcodes to Unicode emojis for display
    $processed = $this->convert_shortcodes_to_emojis($message);

    return $processed;
  }

  /**
   * Check if conversation involves a client (optimized single query)
   *
   * @param int $own_id
   * @param int $contact_id
   * @return bool
   */
  private function isClientConversation($own_id, $contact_id)
  {
    // Check if both users are staff members in a single query
    $staffCount = $this->db->query(
      "SELECT COUNT(*) as count FROM " . db_prefix() . "staff WHERE staffid IN (?, ?)",
      [$own_id, $contact_id]
    )->row()->count;

    // If both users are staff (count = 2), it's a staff conversation
    // If one or both are not staff (count < 2), it's a client conversation
    return $staffCount < 2;
  }

  /**
   * Get file messages for a conversation (optimized query)
   *
   * @param int $own_id
   * @param int $contact_id
   * @param bool $isClientConversation
   * @return array
   */
  private function getFileMessages($own_id, $contact_id, $isClientConversation)
  {
    if ($isClientConversation) {
      // Client conversation - search chatclientmessages table
      $query = $this->db->query(
        "SELECT message FROM " . db_prefix() . "chatclientmessages
                 WHERE ((sender_id = ? AND reciever_id = ?) OR (sender_id = ? AND reciever_id = ?))
                 AND message LIKE '%/modules/prchat/uploads/%'
                 ORDER BY id DESC",
        ["staff_$own_id", "client_$contact_id", "client_$contact_id", "staff_$own_id"]
      );
    } else {
      // Staff conversation - search chatmessages table
      $query = $this->db->query(
        "SELECT message FROM " . db_prefix() . "chatmessages
                 WHERE ((sender_id = ? AND reciever_id = ?) OR (sender_id = ? AND reciever_id = ?))
                 AND message LIKE '%/modules/prchat/uploads/%'
                 ORDER BY id DESC",
        [$own_id, $contact_id, $contact_id, $own_id]
      );
    }

    return $query ? $query->result_array() : [];
  }

  /**
   * Get internal notes for a client contact (staff-only).
   *
   * @param int $contact_id Contact ID
   * @return array List of notes (id, note_content, created_by, created_at, updated_at, author_name)
   */
  public function get_client_notes($contact_id)
  {
    $staff_id = $this->get_current_staff_id();

    $this->db->select('n.id, n.note_content, n.created_by, n.created_at, n.updated_at');
    $this->db->from(db_prefix() . 'chat_client_notes n');
    $this->db->where('n.contact_id', (int) $contact_id);
    $this->db->where('n.created_by', $staff_id);
    $this->db->order_by('n.created_at', 'DESC');
    $rows = $this->db->get()->result_array();
    if (empty($rows)) {
      return [];
    }
    $staff_ids = array_unique(array_column($rows, 'created_by'));
    $names = $this->get_staff_names_by_ids($staff_ids);
    foreach ($rows as &$row) {
      $row['author_name'] = isset($names[$row['created_by']]) ? $names[$row['created_by']] : '';
    }
    return $rows;
  }

  /**
   * Get staff display names by IDs.
   *
   * @param int[] $staff_ids
   * @return array [ staff_id => full_name ]
   */
  private function get_staff_names_by_ids(array $staff_ids)
  {
    if (empty($staff_ids)) {
      return [];
    }
    $this->db->select('staffid, CONCAT(firstname, " ", lastname) as full_name', false);
    $this->db->from(db_prefix() . 'staff');
    $this->db->where_in('staffid', $staff_ids);
    $result = $this->db->get()->result_array();
    $out = [];
    foreach ($result as $r) {
      $out[$r['staffid']] = $r['full_name'];
    }
    return $out;
  }

  /**
   * Add a client note. Caller must ensure staff is allowed to see this contact.
   *
   * @param int $contact_id Contact ID
   * @param string $note_content Note text
   * @return int|false New note ID or false
   */
  public function add_client_note($contact_id, $note_content)
  {
    $note_content = trim($note_content);
    if ($note_content === '') {
      return false;
    }
    $staff_id = $this->get_current_staff_id();
    $this->db->insert(db_prefix() . 'chat_client_notes', [
      'contact_id' => (int) $contact_id,
      'note_content' => $note_content,
      'created_by' => $staff_id,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => null,
    ]);
    return $this->db->insert_id() ?: false;
  }

  /**
   * Get contact_id for a note (for access checks).
   *
   * @param int $note_id Note ID
   * @return int|null Contact ID or null if not found
   */
  public function get_contact_id_by_note_id($note_id)
  {
    $row = $this->db->select('contact_id')
      ->from(db_prefix() . 'chat_client_notes')
      ->where('id', (int) $note_id)
      ->limit(1)
      ->get()
      ->row_array();
    return $row ? (int) $row['contact_id'] : null;
  }

  /**
   * Update a client note. Only the author can update.
   *
   * @param int $note_id Note ID
   * @param string $note_content New content
   * @return bool
   */
  public function update_client_note($note_id, $note_content)
  {
    $note_content = trim($note_content);
    if ($note_content === '') {
      return false;
    }
    $staff_id = $this->get_current_staff_id();
    $this->db->where('id', (int) $note_id);
    $this->db->where('created_by', $staff_id);
    $this->db->update(db_prefix() . 'chat_client_notes', [
      'note_content' => $note_content,
      'updated_at' => date('Y-m-d H:i:s'),
    ]);
    return $this->db->affected_rows() > 0;
  }

  /**
   * Delete a client note. Only the author can delete.
   *
   * @param int $note_id Note ID
   * @return bool
   */
  public function delete_client_note($note_id)
  {
    $staff_id = $this->get_current_staff_id();
    $this->db->where('id', (int) $note_id);
    $this->db->where('created_by', $staff_id);
    $this->db->delete(db_prefix() . 'chat_client_notes');
    return $this->db->affected_rows() > 0;
  }
}

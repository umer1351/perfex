<?php
/*
Module Name: CRM Chat & AI Chatbot Module
Description: Chat & AI Chatbot Module for Perfex CRM
Author: iDev
Author URI: https://idevalex.com
*/

defined('BASEPATH') or exit('No direct script access allowed');
define('CHAT_CURRENT_URI', isset($_SERVER['REQUEST_URI']) ? strtolower($_SERVER['REQUEST_URI']) : null);
define('VERSIONING', get_instance()->app_scripts->core_version());

if (get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('before_staff_login', 'prchat_set_session_variable_before_login_for_notification');
}

if (staff_can('view', PR_CHAT_MODULE_NAME) && get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('app_admin_head', 'pr_chat_add_head_components');
  hooks()->add_action('app_admin_footer', 'pr_chat_init_checkView');
  hooks()->add_action('app_admin_footer', 'pr_chat_load_js');
  hooks()->add_action('app_admin_head', 'pr_chat_add_js_before_admin_render');
  hooks()->add_filter('migration_tables_to_replace_old_links', 'pr_chat_migration_tables_to_replace_old_links');
  hooks()->add_action('staff_member_deleted', 'pr_chat_staff_member_data_transfer');
}

// Check if clients view is enabled in Setup->Settings->Chat Settings
if (isClientsEnabled() && get_option('pusher_chat_enabled') == '1') {
  hooks()->add_action('before_client_login', 'prchat_set_session_variable_before_login_for_notification_client');
  hooks()->add_action('app_customers_head', 'handle_clients_css_styles');
  hooks()->add_action('app_customers_footer', 'pr_chat_init_checkViewClients');
}

/**
 * Function that handles css files for customers view.
 *
 * @return void
 */
function handle_clients_css_styles()
{
  echo '<link href="' . base_url('modules/prchat/assets/clients/styles.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" >';
}

/*
 * Check if can have permissions then apply new tab in settings
 */
if (staff_can('view', 'settings')) {
  hooks()->add_action('admin_init', 'prchat_add_settings_tab');
}

/**
 * [prchat_add_settings_tab add new tab in setup->settings].
 *
 * @return void
 */
function prchat_add_settings_tab()
{
  $CI = &get_instance();
  $CI->app->add_settings_section('prchat-settings', [
    'title' => 'CRM Chat',
    'position' => 1,
    'children' => [
      [
        'position' => 1,
        'name' => _l('chat_settings_name'),
        'view' => 'prchat/perfex_chat_settings',
        'icon' => 'fa-regular fa-message',
      ],
    ],
  ]);
}

/**
 * [Set session variable before login > this is for html5 live desktop notifications].
 *
 * @return void
 */
function prchat_set_session_variable_before_login_for_notification()
{
  get_instance()->session->set_userdata('prchat_user_before_login', true);
}

/**
 * [Set session variable before login > this is for html5 live desktop notifications].
 *
 * @return void
 */
function prchat_set_session_variable_before_login_for_notification_client()
{
  get_instance()->session->set_userdata('prchat_client_before_login', true);
}

/**
 * [pr_chat_load_js inject javascript files].
 *
 * @return void
 */
function pr_chat_load_js()
{
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<script>var prchatLang = { lastSeenNever: "' . addslashes(_l('chat_last_seen_never')) . '" };</script>';
    echo '<script src="' . module_dir_url('prchat', 'assets/js/pr-chat.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
  /**
   * Mentions js (legacy + new PrchatMentions)
   */
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/underscore.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/jquery-elastic.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/mentions.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/mentions/prchat-mentions.js' . '?v=' . VERSIONING . '') . '"></script>';

  // New centralized sound management system
  echo '<script src="' . base_url('modules/prchat/assets/js/ChatSoundManager.js' . '?v=' . VERSIONING . '') . '"></script>';
}

/**
 * Function that will inject the chat messages tables when user changing domain and need to replace old links.
 *
 * @param array $tables
 *
 * @return array
 */
function pr_chat_migration_tables_to_replace_old_links($tables)
{
  $tables[] = [
    'table' => db_prefix() . 'chatmessages',
    'field' => 'message',
  ];

  return $tables;
}

/**
 * Injects chat CSS.
 *
 * @return null
 */
function pr_chat_add_head_components()
{
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<link href="' . base_url('modules/prchat/assets/css/chat_styles.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" >';
  } else {
    chat_check_theme_options();
  }
  // Mutual files for both chat views
  echo '<link href="' . base_url('modules/prchat/assets/css/inline-video-embed.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
  echo '<link href="' . base_url('modules/prchat/assets/css/mentions.css') . '" rel="stylesheet" type="text/css"/>';
}

/**
 * Inject chat JS plugins.
 */
function pr_chat_add_js_before_admin_render()
{
  if (strpos($_SERVER['REQUEST_URI'], 'chat_full_view') === false) {
    echo '<script src="' . base_url('modules/prchat/assets/js/jscolor.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
  echo '<script src="' . base_url('modules/prchat/assets/js/inline-video-embed.js' . '?v=' . VERSIONING . '') . '"></script>';
  echo '<script src="' . base_url('modules/prchat/assets/js/voice-recorder.js' . '?v=' . VERSIONING . '') . '"></script>';

  // Calls scaffolding (feature-flagged)
  if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1') {
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/signaling-client.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/media-manager.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/call-ui.js' . '?v=' . VERSIONING . '') . '"></script>';
    echo '<script src="' . base_url('modules/prchat/assets/js/calls/call-manager.js' . '?v=' . VERSIONING . '') . '"></script>';
  }
}

/**
 * Theme options.
 *
 * @return load css file
 */
function chat_check_theme_options()
{
  // Dark mode is handled via body.chat_dark class in the same CSS file
  echo '<link href="' . base_url('modules/prchat/assets/css/chat_full_view.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
  echo '<link href="' . base_url('modules/prchat/assets/css/inline-video-embed.css' . '?v=' . VERSIONING . '') . '"  rel="stylesheet" type="text/css" />';
}

/**
 * Loads the chat view.
 *
 * @return null
 */
function pr_chat_init_checkView()
{
  $CI = &get_instance();

  // Note: prchat_user_before_login is cleared in pusher_auth() after Pusher reads it.
  // Do NOT unset it here — the footer renders before the async Pusher auth AJAX fires.

  $CI->load->model('prchat/prchat_model', 'chat_model');
  $unreadMessages = $CI->chat_model->getUnread();

  echo $CI->load->view('prchat/initViewCheck', ['unreadMessages' => $unreadMessages], true);
}

/**
 * Loads the chat view.
 *
 * @return null
 */
function pr_chat_init_checkViewClients()
{
  $CI = &get_instance();

  // Note: prchat_client_before_login is cleared in pusherCustomersAuth() after Pusher reads it.
  // Do NOT unset it here — the footer renders before the async Pusher auth AJAX fires.

  echo $CI->load->view('prchat/initViewCheckClients');
}

/*
 * Function that will convert links to iamges if meets the regex
 */
function pr_chat_convertLinkImageToString($string)
{
  $regexImg = '~(http.*\.)(jpe?g|png|gif|[tg]iff?|svg)~i';

  if (preg_match_all($regexImg, $string)) {
    $string = preg_replace_callback($regexImg, function ($matches) {
      $fullUrl = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
      $filename = basename(parse_url($fullUrl, PHP_URL_PATH));
      $safeUrl = htmlspecialchars($fullUrl, ENT_QUOTES, 'UTF-8');
      $safeName = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
      return '<a href="' . $safeUrl . '" target="_blank" data-chat-file="image" data-file-url="' . $safeUrl . '" data-filename="' . $safeName . '"><img class="prchat_convertedImage" src="' . $safeUrl . '" alt="' . $safeName . '"/></a>';
    }, $string);
  }

  return $string;
}

/**
 * Merge audio/webm into CI mimes for .webm (MediaRecorder voice uses audio/webm).
 */
function pr_chat_patch_upload_mimes_for_voice()
{
  $mimes = &get_mimes();
  if (!is_array($mimes)) {
    return;
  }
  if (isset($mimes['webm'])) {
    if ($mimes['webm'] === 'video/webm') {
      $mimes['webm'] = ['video/webm', 'audio/webm'];
    } elseif (is_array($mimes['webm']) && !in_array('audio/webm', $mimes['webm'], true)) {
      $mimes['webm'][] = 'audio/webm';
    }
  }
  if (isset($mimes['m4a'])) {
    if (is_string($mimes['m4a'])) {
      $mimes['m4a'] = [$mimes['m4a'], 'audio/mp4', 'audio/m4a'];
    } elseif (is_array($mimes['m4a'])) {
      foreach (['audio/mp4', 'audio/m4a'] as $am) {
        if (!in_array($am, $mimes['m4a'], true)) {
          $mimes['m4a'][] = $am;
        }
      }
    }
  }
}

/**
 * Voice-note extensions only (stored under module uploads; not tied to CRM allowed_files).
 */
function pr_chat_voice_upload_extensions_string()
{
  return 'webm|ogg|oga|m4a|mp3|wav|aac';
}

/**
 * Attachment extensions from CRM settings (client portal file picker), minus deny list.
 */
function pr_chat_client_crm_attachment_types_string()
{
  $allowedFiles = get_option('allowed_files');
  $raw = is_string($allowedFiles) ? $allowedFiles : '';
  $allowedFiles = str_replace([',', '.'], ['|', ''], $raw);
  $denyList = ['php', 'phar', 'phtml', 'cgi', 'pl', 'py', 'sh', 'bat', 'exe', 'htaccess', 'htpasswd'];
  $types = array_filter(explode('|', $allowedFiles));
  $types = array_diff($types, $denyList);
  $out = implode('|', $types);

  return $out !== '' ? $out : 'jpg|jpeg|png|gif|webp|pdf|doc|docx|xls|xlsx|zip';
}

/**
 * Get chat color by user id.
 *
 * @param mixed $id
 * @param mixed $name
 *
 * @return mixed
 */
function pr_get_chat_color($id, $name = '')
{
  $CI = &get_instance();

  if ($CI->db->field_exists('value', db_prefix() . 'chatsettings')) {
    return pr_get_chat_option($id, $name);
  } else {
    $CI->db->select('chat_color');
    $CI->db->where('user_id', $id);
  }
  $result = $CI->db->get(db_prefix() . 'chatsettings')->row();

  if (!$result) {
    return '';
  }

  return $result->chat_color;
}

/**
 * Get chat get chat color on subscribe.
 *
 * @param mixed $id
 * @param mixed $name
 *
 * @return mixed
 */
function pr_get_chat_option($id, $name)
{
  $CI = &get_instance();
  $CI->db->select('value');
  $CI->db->where('name', $name);
  $CI->db->where('user_id', $id);

  $result = $CI->db->get(db_prefix() . 'chatsettings')->row();

  if (!$result) {
    return '';
  }

  return $result->value;
}

/**
 * Function that will check check if current message contains image.
 *
 * @param string $string
 *
 * @return string
 */
function prchat_checkMessageIfFileExists($message)
{
  $regexImg = '/^[^?]*\.(unknown|gif|jpg|jpeg|tiff|png|swf|rar|zip|mp3|ogg|mp4|mov|flv|wmv|avi|doc|docx|pdf|xls|xlsx|zip|rar|txt|php|html|css|PNG|JPG|JPEG)/';
  if (preg_match_all($regexImg, $message)) {
    return true;
  } else {
    return false;
  }
}

/**
 * Check if message has any images or files links containing.
 *
 * @param string $image
 *
 * @return string
 */
function getImageFullName($file)
{
  $url_arr = explode('/', $file);

  return $url_arr[count($url_arr) - 1];
}

/**
 * Get staff current role.
 *
 * @param [type] $role_id
 *
 * @return string
 */
function get_staff_userrole($role_id)
{
  $CI = &get_instance();
  $CI->db->select('name');
  $CI->db->where('roleid', $role_id);

  $result = $CI->db->get(db_prefix() . 'roles')->row_array();
  if ($result !== null) {
    return $result['name'];
  }
}

/**
 * Theme options.
 *
 * @return string
 */
function get_chat_theme_option()
{
  get_instance()->db->where('user_id', get_staff_user_id());
  get_instance()->db->where('name', 'current_theme');

  return get_instance()->db->get(db_prefix() . 'chatsettings')->row('value');
}

/**
 * Function that will check chat URL images and will convert to link.
 *
 * @param string $string
 *
 * @return string
 */
function make_url_clickable_cb($matches)
{
  $ret = '';
  $url = $matches[2];
  if (empty($url)) {
    return $matches[0];
  }

  if (in_array(substr($url, -1), ['.', ',', ';', ':']) === true) {
    $ret = substr($url, -1);
    $url = substr($url, 0, strlen($url) - 1);
  }

  $rawUrl = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
  if (preg_match('/^(javascript|vbscript|data):/i', trim($rawUrl))) {
    return $matches[0];
  }

  $hrefDest = str_replace('http://', '//', $rawUrl);
  $safeHref = htmlspecialchars($hrefDest, ENT_QUOTES, 'UTF-8');
  $safeDisplay = htmlspecialchars($rawUrl, ENT_QUOTES, 'UTF-8');

  $youtubeRegex = '/(youtube(-nocookie)?\.com|youtu\.be)/i';
  $vimeoRegex = '/(vimeo(pro)?.com)/i';
  $facebookVideoRegex = '/(facebook\.com)\/([a-z0-9_-]*)\/videos\//i';
  $googlemapsRegex = '/((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i';

  $isVideoUrl = preg_match($youtubeRegex, $rawUrl) || preg_match($vimeoRegex, $rawUrl) || preg_match($facebookVideoRegex, $rawUrl) || preg_match($googlemapsRegex, $rawUrl);
  $dataVideoEmbed = $isVideoUrl ? ' data-video-embed="true"' : '';

  if (strpos($rawUrl, '/modules/prchat/uploads/') !== false) {
    $filename = htmlspecialchars(basename($rawUrl), ENT_QUOTES, 'UTF-8');
    return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\" target=\"_blank\" data-chat-file=\"file\" data-file-url=\"$safeHref\" data-filename=\"$filename\">$filename</a>" . $ret;
  }

  return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\"$dataVideoEmbed target=\"_blank\">$safeDisplay</a>" . $ret;
}

/**
 * Callback for clickable.
 */
function make_web_ftp_clickable_cb($matches)
{
  $ret = '';
  $dest = $matches[2];
  $dest = 'http://' . $dest;
  if (empty($dest)) {
    return $matches[0];
  }

  if (in_array(substr($dest, -1), ['.', ',', ';', ':']) === true) {
    $ret = substr($dest, -1);
    $dest = substr($dest, 0, strlen($dest) - 1);
  }

  $rawDest = html_entity_decode($dest, ENT_QUOTES, 'UTF-8');
  if (preg_match('/^(javascript|vbscript|data):/i', trim($rawDest))) {
    return $matches[0];
  }

  $hrefDest = str_replace('http://', '//', $rawDest);
  $safeHref = htmlspecialchars($hrefDest, ENT_QUOTES, 'UTF-8');
  $safeDisplay = htmlspecialchars($rawDest, ENT_QUOTES, 'UTF-8');

  $youtubeRegex = '/(youtube(-nocookie)?\.com|youtu\.be)/i';
  $vimeoRegex = '/(vimeo(pro)?.com)/i';
  $facebookVideoRegex = '/(facebook\.com)\/([a-z0-9_-]*)\/videos\//i';
  $googlemapsRegex = '/((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i';
  $instagramRegex = '/(instagram\.com)/i';
  $tiktokRegex = '/(tiktok\.com)/i';

  $isVideoUrl = preg_match($youtubeRegex, $rawDest) || preg_match($vimeoRegex, $rawDest) || preg_match($facebookVideoRegex, $rawDest) || preg_match($googlemapsRegex, $rawDest) || preg_match($instagramRegex, $rawDest) || preg_match($tiktokRegex, $rawDest);
  $dataVideoEmbed = $isVideoUrl ? ' data-video-embed="true"' : '';

  if (strpos($rawDest, '/modules/prchat/uploads/') !== false) {
    $filename = htmlspecialchars(basename($rawDest), ENT_QUOTES, 'UTF-8');
    return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\" target=\"_blank\" data-chat-file=\"file\" data-file-url=\"$safeHref\" data-filename=\"$filename\">$filename</a>" . $ret;
  }

  return $matches[1] . "<a href=\"$safeHref\" rel=\"nofollow\"$dataVideoEmbed target=\"_blank\">$safeDisplay</a>" . $ret;
}

/**
 * Callback for clickable.
 */
function make_email_clickable_cb($matches)
{
  $email = htmlspecialchars($matches[2] . '@' . $matches[3], ENT_QUOTES, 'UTF-8');

  return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}

/**
 * Check for links/emails/ftp in string to wrap in href.
 *
 * @param string $ret
 *
 * @return string formatted string with href in any found
 */
function clickable($ret)
{
  $ret = ' ' . $ret;
  // in testing, using arrays here was found to be faster
  $ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', 'make_url_clickable_cb', $ret);
  $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', 'make_web_ftp_clickable_cb', $ret);
  $ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', 'make_email_clickable_cb', $ret);
  // this one is not in an array because we need it to run last, for cleanup of accidental links within links
  $ret = preg_replace('#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', '$1$3</a>', $ret);
  $ret = trim($ret);

  return $ret;
}


/**
 * Function that handles member data upon member deletion.
 *
 * @param [array] $data
 *
 * @return boolean
 */
function pr_chat_staff_member_data_transfer($data)
{
  $deleted = $data['id'];
  $transfer_data_to = $data['transfer_data_to'];
  $CI = &get_instance();

  $CI->db->trans_start();

  $CI->db->where('member_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPMEMBERS);

  $CI->db->where('sender_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPMESSAGES);

  $CI->db->group_start();
  $CI->db->where('sender_id', $deleted);
  $CI->db->or_where('reciever_id', $deleted);
  $CI->db->group_end();
  $CI->db->delete(db_prefix() . 'chatmessages');

  $CI->db->where('user_id', $deleted);
  $CI->db->delete(db_prefix() . 'chatsettings');

  $CI->db->where('created_by_id', $deleted);
  $CI->db->update(TABLE_CHATGROUPS, ['created_by_id' => $transfer_data_to]);

  $group_files = $CI->db->select('file_name')->where('sender_id', $deleted)->get(db_prefix() . 'chatgroupsharedfiles')->result_array();
  // File tracking removed - files tracked via message content

  foreach ($group_files as $group_file) {
    if (is_dir(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER)) {
      unlink(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER . '/' . $group_file['file_name']);
    }
  }


  $CI->db->where('sender_id', $deleted);
  $CI->db->delete(TABLE_CHATGROUPSHAREDFILES);

  $CI->db->where('sender_id', $deleted);
  // File tracking table removed - files tracked via message content

  if ($CI->db->trans_complete()) {
    return true;
  }

  return false;
}

// Helpers for gradient colors
function validateChatColorBeforeApply($color, $model_check = '')
{
  $validColor = '';
  if (
    colorStartsWith($color, '#') && !colorEndsWith($color, ';')
    || colorStartsWith($color, 'linear-gradient(') && colorEndsWith($color, ');')
  ) {
    $validColor = $color;
  } else {
    $validColor = '#546bf1';
  }
  if ($model_check == true && $validColor === '#546bf1') {
    return 'unknownColor';
  }

  return $validColor;
}

// Helpers for gradient colors
function colorStartsWith($haystack, $needle)
{
  $length = strlen($needle);

  return substr($haystack, 0, $length) === $needle;
}

function colorEndsWith($haystack, $needle)
{
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }

  return substr($haystack, -$length) === $needle;
}

/**
 * Returns the customer for a specific (mixed) contact_id.
 *
 * @return row_array
 */
function getOwnClient($id)
{
  $CI = &get_instance();

  $CI->db->select('clients.userid as client_id, ' . db_prefix() . 'contacts.id as contact_id, firstname, lastname, company');
  $CI->db->from(db_prefix() . 'clients as clients');
  $CI->db->where(db_prefix() . 'contacts.id', $id);
  $CI->db->where(db_prefix() . 'contacts.active', 1);
  $CI->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid = clients.userid');

  $result = $CI->db->get()->row_array();

  return $result;
}

/**
 * Fetches from database all staff assigned customers
 * If admin fetches all customers.
 *
 * @param int $limit
 * @param int $offset
 *
 * @return json
 */
function get_staff_customers($limit = 30, $offset = 0, $arrayData = false)
{
  $CI = &get_instance();

  $staff_can_access = get_option('chat_staff_can_access_clients') == '1';
  if (!$staff_can_access && !is_admin()) {
    if ($arrayData) {
      return [];
    }
    echo json_encode([]);
    return;
  }

  $staffCanViewAllClients = staff_can('view', 'customers');
  $current_staff_id = get_staff_user_id();

  $CI->db->select('firstname, lastname, ' . db_prefix() . 'contacts.id as contact_id, ' . get_sql_select_client_company());
  $CI->db->where(db_prefix() . 'clients.active = 1 AND ' . db_prefix() . 'contacts.active = 1');
  $CI->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
  $CI->db->select(db_prefix() . 'clients.userid as client_id, title, is_primary, ' . db_prefix() . 'contacts.last_login');

  if (!$staffCanViewAllClients) {
    $CI->db->where('(' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id="' . $current_staff_id . '"))');
  }

  $CI->db->limit($limit, $offset);

  $result = $CI->db->get(db_prefix() . 'contacts')->result_array();

  foreach ($result as $key => $contact) {
    $result[$key]['profile_image_url'] = contact_profile_image_url($contact['contact_id']);
  }

  if ($arrayData) {
    return $result;
  }

  if ($CI->db->affected_rows() !== 0) {
    echo json_encode(['customers' => $result]);
  } else {
    echo json_encode(['customers' => []]);
  }
}

/**
 * Fetches all customer assigned admins + any staff who have messaged this contact.
 * Includes staff from customer_admins table AND staff who have chat history with this contact.
 *
 * @return json
 */
function get_customer_admins()
{
  $CI = &get_instance();

  $customer_id = get_client_user_id();
  $contact_id = get_contact_user_id();

  $visibility = get_option('chat_client_staff_visibility') ?: 'assigned_and_responded';

  // Treat legacy 'assigned_only' as 'assigned_and_responded'
  if ($visibility === 'assigned_only') {
    $visibility = 'assigned_and_responded';
  }

  $staffIds = [];

  // Include all active staff with chat permission when visibility is 'all_staff'
  if ($visibility === 'all_staff') {
    $allStaff = $CI->db->select('staffid')
      ->where('active', 1)
      ->get(db_prefix() . 'staff')
      ->result_array();

    foreach ($allStaff as $row) {
      $staffIds[] = (int) $row['staffid'];
    }
  }

  // Always include staff assigned to this client
  $assignedStaff = $CI->db->select('staff_id')
    ->where('customer_id', (int) $customer_id)
    ->get(db_prefix() . 'customer_admins')
    ->result_array();

  foreach ($assignedStaff as $row) {
    if (!in_array((int) $row['staff_id'], $staffIds)) {
      $staffIds[] = (int) $row['staff_id'];
    }
  }

  // Always include staff who have communicated with this client
  // so the client can continue existing conversations
  $messages = $CI->db->select('sender_id, reciever_id')
    ->where('(sender_id = "client_' . (int) $contact_id . '" OR reciever_id = "client_' . (int) $contact_id . '")')
    ->where('(sender_id LIKE "staff_%" OR reciever_id LIKE "staff_%")')
    ->get(db_prefix() . 'chatclientmessages')
    ->result_array();

  foreach ($messages as $msg) {
    $staff_id_str = (strpos($msg['sender_id'], 'staff_') === 0) ? $msg['sender_id'] : $msg['reciever_id'];
    $sid = (int) str_replace('staff_', '', $staff_id_str);
    if ($sid > 0 && !in_array($sid, $staffIds)) {
      $staffIds[] = $sid;
    }
  }

  if (empty($staffIds)) {
    return [];
  }

  $CI->db->select('firstname, lastname, staffid, profile_image, admin, role');
  $CI->db->where_in('staffid', $staffIds);
  $CI->db->where('active', 1);
  $customer_admins = $CI->db->get(db_prefix() . 'staff')->result_array();

  foreach ($customer_admins as $key => &$admin) {
    if (!staff_can('view', PR_CHAT_MODULE_NAME, $admin['staffid'])) {
      unset($customer_admins[$key]);
      continue;
    }

    if ($admin['admin']) {
      $admin['role'] = ' ' . _l('chat_role_administrator');
    } else {
      $role_name = get_staff_userrole($admin['role']);
      if ($role_name) {
        $admin['role'] = ' ' . $role_name;
      } else {
        $admin['role'] = ' ' . _l('chat_role_staff');
      }
    }

    // Never expose admin flag or email to client side
    unset($admin['admin']);
    unset($admin['email']);
  }

  return array_values($customer_admins);
}

/**
 * Check if current staff can access a contact for chat (and thus for client notes).
 * Staff can access if they have view customers permission OR are assigned as customer admin.
 *
 * @param int $contact_id Contact ID
 * @return bool
 */
function staff_can_access_contact_for_chat($contact_id)
{
  $CI = &get_instance();

  if (staff_can('view', 'customers')) {
    $CI->db->select('id');
    $CI->db->where('id', (int) $contact_id);
    $CI->db->where('active', 1);
    $row = $CI->db->get(db_prefix() . 'contacts')->row();
    return $row !== null;
  }

  $staff_id = (int) get_staff_user_id();
  $customer_ids = $CI->db->select('customer_id')
    ->from(db_prefix() . 'customer_admins')
    ->where('staff_id', $staff_id)
    ->get()
    ->result_array();
  $userids = array_column($customer_ids, 'customer_id');
  if (empty($userids)) {
    return false;
  }

  $CI->db->select(db_prefix() . 'contacts.id');
  $CI->db->from(db_prefix() . 'contacts');
  $CI->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
  $CI->db->where(db_prefix() . 'contacts.id', (int) $contact_id);
  $CI->db->where(db_prefix() . 'clients.active', 1);
  $CI->db->where(db_prefix() . 'contacts.active', 1);
  $CI->db->where_in(db_prefix() . 'clients.userid', $userids);
  $row = $CI->db->get()->row();
  return $row !== null;
}

/**
 * Check if staff can delete messages
 *
 * @return bool
 */
function chatStaffCanDelete()
{
  if (is_admin()) {
    return true;
  }
  return staff_can('delete', PR_CHAT_MODULE_NAME);
}


/**
 * Get contact user id from contacts table
 * Used for when creating support ticket from messages
 *
 * @return string
 */
function get_contact_customer_user_id($contact_id)
{
  $CI = &get_instance();
  $CI->db->select('userid');
  $CI->db->where('id', $contact_id);
  $result = $CI->db->get(db_prefix() . 'contacts')->row_array();
  if ($result !== null) {
    return $result['userid'];
  }
}


/**
 * Get last ticket_id inserted
 *
 * @return string
 */
function chat_get_tickets_last_inserted_row()
{
  return get_instance()->db->select('ticketid')->order_by('ticketid', "desc")->limit(1)->get(db_prefix() . 'tickets')->row()->ticketid;
}

/**
 * Receives user active chat status
 *
 * @return string
 */
function get_user_chat_status()
{
  $CI = &get_instance();
  $CI->db->where('user_id', get_staff_user_id());
  $CI->db->where('name', 'chat_status');
  $result = $CI->db->get(db_prefix() . 'chatsettings')->row_array();
  if ($result !== null) {
    return $result['value'];
  }
}

/**
 * Check if clients are enabled.
 *
 * @return boolean
 */
function isClientsEnabled()
{
  return get_option('chat_client_enabled');
}

/**
 * Check if staff can access the clients tab.
 * Admins always have access. Non-admins need the setting enabled.
 *
 * @return bool
 */
function staffCanAccessClientsTab()
{
  if (is_admin()) {
    return true;
  }

  $option_value = get_option('chat_staff_can_access_clients');

  return $option_value == '1';
}

/**
 * Returns groups name with who capitals eg My_Group and underscore.
 *
 * @param $name
 *
 * @return string
 */
function slugifyGroupName($name)
{
  return rtrim(stripslashes(ucwords(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', ucwords($name))))), '-');
}

/**
 * Template components js loader
 *
 * @param       $name
 * @param array $params is user over all components do not remove in any case
 *
 * @return mixed
 */
function loadChatComponent($name, $params = [])
{    // Used in chat_full_view as ['prop' => 'class or something else']
  // Then reuse this in the $name.php $params['prop']
  require('modules/prchat/assets/module_includes/Components/' . $name . '.php');
}

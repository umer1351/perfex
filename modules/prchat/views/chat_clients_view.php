<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$isHttps = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? $isHttps = true : false);
?>
<!-- Emoji Picker Styles for Client Chat -->
<style>
  /* Emoji Picker for Client Chat - Override default styles */
  #emoji-picker.emoji-picker {
    position: fixed !important;
    width: 300px !important;
    max-height: 320px !important;
    background: #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important;
    z-index: 9999999 !important;
    display: none !important;
    overflow: hidden !important;
    border: 1px solid #e0e0e0 !important;
  }

  #emoji-picker.emoji-picker.show {
    display: block !important;
  }

  #emoji-picker .emoji-picker-header {
    padding: 12px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
  }

  #emoji-picker .emoji-categories {
    display: flex;
    gap: 4px;
    justify-content: center;
  }

  #emoji-picker .emoji-category-btn {
    padding: 8px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
  }

  #emoji-picker .emoji-category-btn:hover {
    background: #e9ecef;
  }

  #emoji-picker .emoji-category-btn.active {
    background: #4f46e5;
    color: white;
  }

  #emoji-picker .emoji-picker-body {
    padding: 12px;
    max-height: 250px;
    overflow-y: auto;
  }

  #emoji-picker .emoji-grid {
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    gap: 6px !important;
  }

  #emoji-picker .emoji-item {
    width: 34px !important;
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 22px !important;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.15s ease;
    background: transparent;
  }

  #emoji-picker .emoji-item:hover {
    background: #e9ecef !important;
    transform: scale(1.15);
  }

  #emoji-picker .emoji-empty {
    padding: 20px;
    text-align: center;
    color: #999;
    font-size: 13px;
  }
</style>
<!-- Client Chat Widget - Modern Design -->
<div class="ch_pointer">
  <div id="ch_pointer-main" class="ch_pointer-main" role="button" aria-label="<?= _l('chat_open_chat'); ?>"
    tabindex="0">
    <div class="chatNewNotification" data-count="0"></div>
    <span class="ch_pointer-main-first">
      <!-- Chat Icon -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path
          d="M4.913 2.658c2.075-.27 4.19-.408 6.337-.408 2.147 0 4.262.139 6.337.408 1.922.25 3.291 1.861 3.405 3.727a4.403 4.403 0 00-1.032-.211 50.89 50.89 0 00-8.42 0c-2.358.196-4.04 2.19-4.04 4.434v4.286a4.47 4.47 0 002.433 3.984L7.28 21.53A.75.75 0 016 21v-4.03a48.527 48.527 0 01-1.087-.128C2.905 16.58 1.5 14.833 1.5 12.862V6.638c0-1.97 1.405-3.718 3.413-3.979z" />
        <path
          d="M15.75 7.5c-1.376 0-2.739.057-4.086.169C10.124 7.797 9 9.103 9 10.609v4.285c0 1.507 1.128 2.814 2.67 2.94 1.243.102 2.5.157 3.768.165l2.782 2.781a.75.75 0 001.28-.53v-2.39l.33-.026c1.542-.125 2.67-1.433 2.67-2.94v-4.286c0-1.505-1.125-2.811-2.664-2.94A49.392 49.392 0 0015.75 7.5z" />
      </svg>
    </span>
    <span class="ch_pointer-main-under">
      <span class="ch_pointer-main-prefix">
        <!-- Close Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path fill-rule="evenodd"
            d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
            clip-rule="evenodd" />
        </svg>
      </span>
    </span>
  </div>
</div>
<?php
// Get client widget settings - use proper null checks since '0' is valid value
$widget_position = get_option('chat_client_widget_position') ?: 'right';
$widget_primary = get_option('chat_client_widget_primary_color') ?: '#4f46e5';
$show_logo_opt = get_option('chat_client_widget_show_logo');
$widget_show_logo = ($show_logo_opt === '0' || $show_logo_opt === '1') ? $show_logo_opt : '1';
$show_names_opt = get_option('chat_client_widget_show_staff_names');
$widget_show_names = ($show_names_opt === '0' || $show_names_opt === '1') ? $show_names_opt : '1';
$show_roles_opt = get_option('chat_client_widget_show_staff_roles');
$widget_show_roles = ($show_roles_opt === '0' || $show_roles_opt === '1') ? $show_roles_opt : '1';
?>
<!-- Dynamic Widget Styles -->
<style>
  :root {
    --chat-primary:
      <?= $widget_primary ?>;
    --chat-primary-hover:
      <?= $widget_primary ?> dd;
  }

  <?php if ($widget_position === 'left'): ?>.ch_pointer {
    right: auto;
    left: 24px;
  }

  #clientChat .firstDiv {
    right: auto;
    left: 24px;
  }

  <?php endif; ?>

  /* Header styling with primary color */
  .company_top_info,
  .ch_pointer .ch_pointer-main {
    background: var(--chat-primary) !important;
  }

  .company_top_info {
    min-height: auto;
    padding: 14px 16px !important;
  }

  /* When logo is hidden, ensure header still has proper spacing */
  .company_top_info:not(:has(.company_top_info_parent)) {
    padding: 12px 16px !important;
  }

  /* Client chat header: company name fallback when no logo uploaded */
  #clientChat .client-chat-header-company-name {
    color: #fff !important;
    font-size: 1.1rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    display: inline-block;
  }

  /* Sent messages (client) - use primary color; bubble right, text left-aligned inside (like WhatsApp/Telegram) */
  #clientChat .m-area ol.chat li.client .msg {
    background: var(--chat-primary) !important;
    color: #fff !important;
    text-align: left;
  }

  #clientChat .m-area ol.chat li.client .msg .client_name,
  #clientChat .m-area ol.chat li.client .msg .msg-time,
  #clientChat .m-area ol.chat li.client .msg p,
  #clientChat .m-area ol.chat li.client .msg p a {
    color: #fff !important;
  }

  #clientChat .m-area ol.chat li.client .msg .msg-status i {
    color: rgba(255, 255, 255, 0.8) !important;
  }

  #clientChat .m-area ol.chat li.client .msg .msg-status.read i {
    color: #90EE90 !important;
  }

  /* Send button styling */
  #clientChat .send_client_message {
    background: var(--chat-primary) !important;
    transition: all 0.2s ease;
  }

  #clientChat .send_client_message:hover {
    background: #477ae1 !important;
  }

  /* Disabled send button */
  #clientChat .send_client_message[style*="pointer-events: none"] {
    background: #9ca3af !important;
    cursor: not-allowed !important;
  }

  /* Call system message (centered bubble, matching staff side) */
  #clientChat .modern-call-system-message {
    display: flex;
    justify-content: center;
    padding: 8px 0;
  }

  #clientChat .modern-call-bubble {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f2f5;
    color: #54656f;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
  }

  #clientChat .modern-call-bubble i {
    font-size: 13px;
    color: #667781;
  }

  #clientChat .modern-call-time {
    color: #8696a0;
    font-size: 11px;
    margin-left: 4px;
  }

  #clientChat li.call-message-row {
    list-style: none;
    float: none !important;
    clear: both;
    text-align: center;
    margin: 0 auto;
  }

  /* Textarea focus border */
  #clientChat .clients_textarea:focus {
    outline: none !important;
  }

  /* Received message links (from staff) - blue on light bg */
  #clientChat .m-area ol.chat li.customer_admin .msg p a {
    color: #2563eb !important;
    text-decoration: underline !important;
  }

  #clientChat .m-area ol.chat li.customer_admin .msg p a:hover {
    color: #1d4ed8 !important;
  }

  /* Images inside chat messages – keep inside bubble */
  #clientChat .m-area ol.chat li .msg p {
    overflow: hidden;
    max-width: 100%;
  }

  #clientChat .m-area ol.chat li .msg p img,
  #clientChat .m-area ol.chat li .msg p .prchat_convertedImage {
    max-width: 100%;
    max-height: 280px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 6px;
    display: block;
    margin-top: 4px;
  }

  #clientChat .m-area ol.chat li .msg p a[data-chat-file="image"] img {
    max-width: 100%;
    max-height: 280px;
    object-fit: contain;
  }

  #clientChat .m-area ol.chat li .msg p audio.prchat-inline-audio {
    max-width: 100%;
    min-width: 200px;
    height: 36px;
    display: block;
    margin-top: 4px;
  }

  /* Message row: relative so options sit outside bubble on white background */
  #clientChat .m-area ol.chat li .msg-row {
    position: relative;
  }

  #clientChat .m-area ol.chat li.customer_admin {
    justify-content: flex-start;
  }

  #clientChat .m-area ol.chat li.client {
    justify-content: flex-end;
  }

  #clientChat .m-area ol.chat li .msg {
    position: relative;
    overflow: visible;
  }

  /* Options inside .msg so they position relative to bubble = same 8px distance for all messages (short/long/image). */
  #clientChat .m-area ol.chat li.customer_admin .msg .messageOptionsDiv {
    position: absolute;
    top: 8px;
    left: 100%;
    margin: 0 0 0 8px;
    z-index: 10;
  }

  #clientChat .m-area ol.chat li.client .msg .messageOptionsDiv {
    position: absolute;
    top: 8px;
    right: 100%;
    margin: 0 8px 0 0;
    z-index: 10;
  }

  #clientChat .m-area ol.chat li .messageOptionsDiv .chooseOption {
    display: none;
    cursor: pointer;
    padding: 2px;
    opacity: 0.8;
  }

  #clientChat .m-area ol.chat li:hover .messageOptionsDiv .chooseOption,
  #clientChat .m-area ol.chat li .messageOptionsDiv:hover .chooseOption {
    display: block;
  }

  /* Dropdown: open inward so it stays inside chat */
  #clientChat .m-area ol.chat li.customer_admin .messageOptionsDiv .optionsMore {
    right: 0;
    left: auto;
  }

  #clientChat .m-area ol.chat li.client .messageOptionsDiv .optionsMore {
    left: 0;
    right: auto;
  }

  #clientChat .m-area ol.chat li .messageOptionsDiv .optionsMore {
    display: none;
    position: absolute;
    top: 100%;
    margin-top: 4px;
    min-width: 120px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 4px 0;
    z-index: 20;
  }

  #clientChat .m-area ol.chat li .messageOptionsDiv .optionBtn {
    display: block;
    width: 100%;
    text-align: left;
    padding: 8px 12px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
  }

  #clientChat .m-area ol.chat li .messageOptionsDiv .optionBtn:hover {
    background: #f3f4f6;
  }

  #clientChat .m-area ol.chat li .messageOptionsDiv .optionBtn i {
    margin-right: 8px;
    width: 18px;
  }

  /* Reply context inside bubble – strip with reply icon */
  #clientChat .m-area ol.chat li .msg .message-reply-context {
    background: rgba(0, 0, 0, 0.06);
    border-left: 3px solid var(--chat-primary);
    border-radius: 6px;
    padding: 6px 10px 6px 28px;
    margin-bottom: 8px;
    font-size: 12px;
    color: var(--chat-text-muted);
    position: relative;
  }

  #clientChat .m-area ol.chat li .msg .message-reply-context::before {
    content: "\f3e5";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: var(--chat-primary);
  }

  #clientChat .m-area ol.chat li.client .msg .message-reply-context {
    background: rgba(255, 255, 255, 0.2);
    border-left-color: rgba(255, 255, 255, 0.8);
    color: rgba(255, 255, 255, 0.9);
  }

  #clientChat .m-area ol.chat li.client .msg .message-reply-context::before {
    color: rgba(255, 255, 255, 0.85);
  }

  #clientChat .m-area ol.chat li .msg .reply-context-text {
    color: inherit;
    font-size: 12px;
  }

  /* Tooltips must appear above chat widget */
  .tooltip {
    z-index: 10000000 !important;
  }

  .tooltip.in,
  .tooltip.show {
    z-index: 10000000 !important;
  }

  /* Date separator line between days */
  #clientChat .chat-date-separator {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    font-size: 12px;
    color: #8e8e93;
    margin: 4px 0;
    padding: 0;
    position: relative;
    list-style: none;
  }

  #clientChat .chat-date-separator::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e4e7ea;
    z-index: 0;
  }

  /* Hover color for input action icons (file upload, emoji, voice) */
  #clientChat .client-file-trigger:hover,
  #clientChat .client-emoji-trigger:hover,
  #clientChat .chat-action-voice-clientside:hover {
    color: var(--chat-primary);
    background: color-mix(in srgb, var(--chat-primary) 10%, transparent);
  }

  #clientChat .chat-date-separator span {
    background: var(--chat-bg-secondary, #f8fafc);
    padding: 0 12px;
    position: relative;
    z-index: 1;
    font-weight: 500;
  }
</style>
<div id="clientChat">
  <div class="firstDiv">
    <!-- Header -->
    <div class="company_top_info">
      <div class="top_close_icon" role="button" aria-label="<?= _l('close'); ?>" tabindex="0" style="top: 16px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
          <path
            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
        </svg>
      </div>
      <?php
      $company_logo = get_option('company_logo');
      $company_name = get_option('companyname');
      ?>
      <div class="company_top_info_parent">
        <div class="company_logo_placeholder">
          <?php if ($widget_show_logo == '1' && !empty($company_logo)): ?>
            <?= get_company_logo(); ?>
          <?php elseif (!empty($company_name)): ?>
            <span class="client-chat-header-company-name"><?= e($company_name); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Staff Header -->
    <div class="customer_admins_wrapper">
      <div class="customer_first_co_wrapper">
        <div>
          <div>
            <div class="customer_main_placeholder_top">
              <h2 class="staff_online_text"><?= _l('chat_clients_assigned_admins'); ?></h2>
              <div class="staff_info_wrapper_div">
                <div class="staff_muted_text_info"><?= _l('chat_clients_choose_and_start'); ?></div>
              </div>
              <div class="staff_image_parent">
                <div class="staff_image_wrapper">
                  <!-- Handled on the fly -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="clientwrapper">
      <div class="m-area" data-staffid="" id="">
        <svg class="message_client_loader" viewBox="0 0 50 50">
          <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
        </svg>
        <ol class="chat">
          <!-- Handled on the fly -->
        </ol>
        <div class="typing-indicator">
          <div class="typing-bubble">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
            <span class="typing-text"></span>
          </div>
        </div>
      </div>
      <div class="placeholder-messages">
        <form hidden enctype="multipart/form-data" name="staffMessagesFileForm" id="staffMessagesFileForm" method="post"
          onsubmit="uploadClientFileForm(this);return false;">
          <input type="file" class="file" name="userfile" multiple required />
          <input type="submit" name="submit" class="save" value="<?= _l('chat_save_button') ?>" />
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
            value="<?php echo $this->security->get_csrf_hash(); ?>">
        </form>
        <form method="post" enctype="multipart/form-data" id="staffMessagesForm" onsubmit="return false;">
          <div class="prchat-client-composer-row">
            <div class="input-actions-left">
              <i class="fa-solid fa-paperclip client-file-trigger fileUpload" data-toggle="tooltip"
                title="<?php echo _l('chat_file_upload'); ?>" aria-hidden="true"></i>
              <i class="fa-solid fa-microphone chat-action-voice chat-action-voice-clientside" data-toggle="tooltip"
                title="<?php echo _l('chat_voice_message'); ?>" aria-hidden="true"></i>
              <i class="fa-regular fa-face-smile client-emoji-trigger" data-toggle="tooltip"
                title="<?php echo _l('chat_emoji'); ?>" aria-hidden="true"></i>
            </div>
            <div class="input-textarea-wrap-client">
              <textarea class="clients_textarea ays-ignore" placeholder="<?= _l('chat_type_a_message'); ?>"
                autocomplete="off" rows="1"></textarea>
              <span class="send_client_message" role="button" aria-label="<?= _l('chat_send'); ?>" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                  <path
                    d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z" />
                </svg>
              </span>
            </div>
          </div>
          <input type="hidden" class="ays-ignore from" name="from" value="" />
          <input type="hidden" class="ays-ignore to" name="to" value="" />
          <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
          <input type="hidden" class="ays-ignore" name="<?php echo $this->security->get_csrf_token_name(); ?>"
            value="<?php echo $this->security->get_csrf_hash(); ?>">
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  window.prchatI18n = window.prchatI18n || {};
  window.prchatI18n.chat_voice_message = <?= json_encode(_l('chat_voice_message')); ?>;
  window.prchatI18n.chat_new_file_uploaded = <?= json_encode(_l('chat_new_file_uploaded')); ?>;
  window.prchatI18n.chat_new_photo_uploaded = <?= json_encode(_l('chat_new_photo_uploaded')); ?>;
</script>
<script
  src="<?= module_dir_url('prchat', 'assets/js/prchat-safe-renderer.js?v=' . (defined('VERSIONING') ? VERSIONING : time())); ?>"></script>
<?php require 'modules/prchat/assets/module_includes/mutual_and_helper_functions.php'; ?>
<?php
// Date/Time format settings for client-side date separators & seen tooltips
$phpDateFormat_client = get_current_date_format(true);
$is24Hour_client = get_option('time_format') == '24';
?>
<script>
  // Date/time format settings from Perfex CRM (for date separators & seen tooltips)
  var clientChatDateSettings = {
    is24Hour: <?= $is24Hour_client ? 'true' : 'false' ?>,
    phpDateFormat: "<?= $phpDateFormat_client ?>"
  };

  /**
   * Format a raw datetime string (YYYY-MM-DD HH:mm:ss) to date-only string for separator grouping.
   * Returns YYYY-MM-DD.
   */
  function getDateKey(rawDatetime) {
    if (!rawDatetime) return '';
    return String(rawDatetime).substring(0, 10);
  }

  /**
   * Format a raw datetime (YYYY-MM-DD HH:mm:ss) into a human-readable date separator label.
   * Uses the CRM date format. Shows "Today" / "Yesterday" for recent dates.
   */
  function formatDateSeparator(rawDatetime) {
    if (!rawDatetime) return '';
    var d = new Date(rawDatetime.replace(' ', 'T'));
    if (isNaN(d.getTime())) return rawDatetime;

    var now = new Date();
    var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    var msgDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    var diffDays = Math.round((today - msgDay) / 86400000);

    if (diffDays === 0) return '<?= _l("chatbot_today") ?: "Today" ?>';
    if (diffDays === 1) return '<?= _l("chatbot_yesterday") ?: "Yesterday" ?>';

    var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return days[d.getDay()] + ', ' + months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
  }

  /**
   * Format "Seen" tooltip: same day = "Seen - time", different day = "Seen - datetime".
   */
  var _seenPrefix = "<?= _l('chat_msg_seen'); ?>";

  function formatSeenTooltip(viewedAt, viewedAtFormatted) {
    if (!viewedAtFormatted) return _seenPrefix;
    if (viewedAt) {
      var d = new Date(String(viewedAt).replace(' ', 'T'));
      var now = new Date();
      if (!isNaN(d.getTime()) && d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate()) {
        var timeStr = clientChatDateSettings.is24Hour ?
          String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') :
          (function() {
            var h = d.getHours(),
              m = d.getMinutes(),
              ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            return h + ':' + String(m).padStart(2, '0') + ' ' + ampm;
          })();
        return _seenPrefix + ' - ' + timeStr;
      }
    }
    return _seenPrefix + ' - ' + viewedAtFormatted;
  }

  /**
   * Insert a date separator before appending a new real-time message
   * if the day changed since the last message in the chat.
   */
  function maybeInsertDateSeparator() {
    var lastMsg = $(".m-area ol.chat > li:not(.chat-date-separator)").last();
    if (lastMsg.length === 0) return;
    var lastDateKey = lastMsg.attr('data-date-key') || '';
    var todayKey = new Date().toISOString().substring(0, 10);
    if (lastDateKey && lastDateKey !== todayKey) {
      $(".m-area ol.chat").append('<li class="chat-date-separator"><span>' + formatDateSeparator(new Date().toISOString().replace('T', ' ').substring(0, 19)) + '</span></li>');
    }
  }
</script>
<script>
  /**
   * Detect [CALL:...] token or legacy "Voice call • 4s" format.
   */
  function isCallMessageStr(msg) {
    if (!msg) return false;
    if (/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/.test(msg)) return true;
    var d = msg.replace(/&bull;/g, '\u2022');
    return /^(Outgoing call|Incoming call|Voice call|Video call|Missed call)\s*[•·\u2022]/i.test(d);
  }

  /**
   * Render a call message as a centered system bubble (matching staff side).
   */
  function getCallStaffName(staffId) {
    if (typeof customerAdmins !== 'undefined') {
      for (var i = 0; i < customerAdmins.length; i++) {
        if (String(customerAdmins[i].staffid) === String(staffId)) {
          return (customerAdmins[i].firstname + ' ' + customerAdmins[i].lastname).trim();
        }
      }
    }
    return '';
  }

  function getCallDisplayName(staffId) {
    if (typeof showStaffNames !== 'undefined' && !showStaffNames) {
      return (typeof genericStaffLabel !== 'undefined') ? genericStaffLabel : 'Support Agent';
    }
    return getCallStaffName(staffId) || ((typeof genericStaffLabel !== 'undefined') ? genericStaffLabel : 'Support Agent');
  }

  function renderCallMessageHtml(msg, timeStr) {
    var m = msg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):(\d+):(\d+)\]$/);
    if (m) {
      var type = m[1],
        dur = parseInt(m[2]),
        callerId = m[3],
        calleeId = m[4];
      var staffId = callerId;
      var staffName = getCallDisplayName(staffId);

      if (type === 'missed_voice' || type === 'missed_video') {
        var missedIcon = type === 'missed_video' ? 'fa-video-camera' : 'fa-phone';
        var missedLabel = '<?= addslashes(_l("chat_call_missed_from")) ?> ' + staffName;
        return '<div class="modern-call-system-message"><div class="modern-call-bubble modern-call-missed"><i class="fa ' + missedIcon + '" style="color:#e74c3c"></i> <span style="color:#e74c3c">' + missedLabel + '</span><span class="modern-call-time">' + timeStr + '</span></div></div>';
      }
      var icon = type === 'video' ? 'fa-video-camera' : 'fa-phone';
      var mins = Math.floor(dur / 60),
        secs = dur % 60;
      var durStr = (mins > 0 ? mins + 'm ' : '') + secs + 's';
      var label = staffName + ' <?= addslashes(_l("chat_call_called_you")) ?>';
      return '<div class="modern-call-system-message"><div class="modern-call-bubble"><i class="fa ' + icon + '"></i> ' + label + ' &bull; ' + durStr + '<span class="modern-call-time">' + timeStr + '</span></div></div>';
    }
    var d = msg.replace(/&bull;/g, '\u2022');
    var legacy = d.match(/^(Outgoing call|Incoming call|Voice call|Video call|Missed call)\s*[•·\u2022]\s*(.+)$/i);
    if (legacy) {
      var legacyIcon = legacy[1].toLowerCase().indexOf('video') !== -1 ? 'fa-video-camera' : 'fa-phone';
      var safeType = legacy[1].replace(/</g, '&lt;');
      var safeDur = legacy[2].replace(/</g, '&lt;');
      return '<div class="modern-call-system-message"><div class="modern-call-bubble"><i class="fa ' + legacyIcon + '"></i> ' + safeType + ' &bull; ' + safeDur + '<span class="modern-call-time">' + timeStr + '</span></div></div>';
    }
    return '';
  }

  /**
   * Render message content for display: images, [REPLY:...] blocks, links, emojis.
   * Uses PrchatSafeRenderer when available. For raw text (e.g. pasted/uploaded image URL)
   * uses renderFromText so image URLs become <img>; for server HTML uses render().
   */
  function renderMessageContent(msg) {
    if (msg == null || msg === '') return '';
    if (typeof PrchatSafeRenderer !== 'undefined') {
      var isPlainText = (typeof msg === 'string' && msg.indexOf('<') === -1 && msg.indexOf('>') === -1);
      if (isPlainText) {
        return PrchatSafeRenderer.renderFromText(msg).display;
      }
      return PrchatSafeRenderer.render(msg).display;
    }
    return convertEmojis(createTextLinks_(escapeHtml(msg)));
  }

  /**
   * Client message options HTML: Copy for all, Reply only for staff messages.
   */
  function clientMessageOptions(messageId, showReply, messageContent) {
    var l_copy = "<?= _l('copy') ?>";
    var l_reply = "<?= _l('reply') ?>";
    var l_edit = "<?= _l('edit') ?>";

    // Check if message is voice/media - don't show edit button
    var isVoiceOrMedia = messageContent && (
      /prchat-inline-audio|<audio|<video|<img/.test(messageContent) ||
      /voice_[a-z0-9_]+\.(webm|ogg|m4a)/i.test(messageContent) ||
      /(staff|clients|groups)\/[a-z0-9_]+_voice_[a-z0-9_]+\.(webm|ogg|m4a)/i.test(messageContent)
    );

    var replyBtn = showReply ? '<button class="pointer optionBtn _replyMessage"><i class="fa fa-reply"></i>' + l_reply + '</button>' : '';
    var editBtn = (!showReply && !isVoiceOrMedia) ? '<button class="pointer optionBtn _editMessage"><i class="fa fa-pencil"></i>' + l_edit + '</button>' : '';
    return '<div class="messageOptionsDiv">' +
      '<svg height="22px" class="chooseOption" width="22px" viewBox="0 0 22 22"><circle fill="#777777" cx="11" cy="6" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="11" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="16" r="2" stroke-width="1px"></circle></svg>' +
      '<div class="optionsMore" data-id="' + (messageId || '') + '">' +
      '<button class="pointer optionBtn _reactMessage"><i class="fa fa-smile"></i>' + <?= json_encode(_l('chat_react')); ?> + '</button>' +
      replyBtn +
      '<button class="pointer optionBtn _copyMessage"><i class="fa fa-copy"></i>' + l_copy + '</button>' +
      editBtn +
      '</div></div>';
  }

  /** Ensure three-dots position is from CSS only (no inline overrides) */
  function clearClientChatOptionsPosition() {
    $("#clientChat .messageOptionsDiv").css({
      top: '',
      left: '',
      right: '',
      transform: ''
    });
  }

  /** Sync DOM id / options menu data-id so react & edit work after send (matches prependContactMessages after reload). */
  function assignClientChatMessageId($li, messageId) {
    if (!$li || !$li.length || messageId == null || messageId === '' || messageId === false) {
      return;
    }
    var idStr = String(messageId);
    $li.attr('id', idStr).attr('data-id', idStr);
    $li.find('.optionsMore').attr('data-id', idStr);
  }

  var clientsArea = $(".clients_textarea");
  var contact_id = "<?= get_contact_user_id(); ?>";
  var client_id = "<?= get_client_user_id(); ?>";
  var contact_company_name = document.getElementsByTagName("title")[0].innerHTML;
  var pusherKey = "<?= get_option('pusher_app_key') ?>";
  var appCluster = "<?= get_option('pusher_cluster') ?>";
  var customerAdmins = <?= json_encode(get_customer_admins()); ?>;
  var contact_name_id = "client_" + contact_id;
  var contact_full_name = $(".customers-nav-item-profile a img").data("title");
  var checkForStaffUnreadMessages = $.getJSON(customerSettings.getStaffUnreadMessages);
  var offsetPush = 0;
  var groupOffsetPush = 0;
  var endOfScroll = false;
  var currentStaff;
  var ntf = document.querySelector(".chatNewNotification");
  var chatPointer = document.querySelector(".ch_pointer");
  var showStaffNames = <?= $widget_show_names === '1' ? 'true' : 'false' ?>;
  var showStaffRoles = <?= $widget_show_roles === '1' ? 'true' : 'false' ?>;
  var genericStaffLabel = '<?= _l("chat_generic_staff_label") ?>';
  var placeholderImg = site_url + "/assets/images/user-placeholder.jpg";
  var contactAvatarUrl = <?= json_encode(contact_profile_image_url(get_contact_user_id())); ?> || placeholderImg;
  var _clientChatOpenTimeouts = {
    first: null,
    retry: null
  };

  function getStaffRole(staffId) {
    for (var i = 0; i < customerAdmins.length; i++) {
      if (String(customerAdmins[i].staffid) === String(staffId)) {
        return (customerAdmins[i].role || '').trim();
      }
    }
    return '<?= _l("chat_role_staff") ?>';
  }

  function buildStaffLabel(name, staffId) {
    var parts = [];
    if (showStaffNames && name) {
      parts.push('<span class="admin_name">' + name + '</span>');
    } else {
      parts.push('<span class="admin_name">' + genericStaffLabel + '</span>');
    }
    if (showStaffRoles) parts.push('<span class="admin_role">' + getStaffRole(staffId) + '</span>');
    return parts.join('');
  }

  /*---------------* Put customerSettings.debug for debug mode for Pusher *---------------*/
  if (customerSettings.debug) {
    try {
      Pusher.log = function(message) {
        console.log(message);
      };
    } catch (e) {
      if (e instanceof ReferenceError) {
        alert_float("danger", e);
      }
    }
  }

  /*---------------* Init pusher library, and register *---------------*/
  var pusher = new Pusher(pusherKey, {
    authEndpoint: customerSettings.clientPusherAuth,
    authTransport: "ajax",
    "cluster": appCluster,
    disableStats: true,
    auth: {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-Token": (typeof csrfData == "undefined") ? "" : csrfData.formatted.csrf_token_name
      }
    }
  });

  /*---------------* Pusher Trigger accessing channel *---------------*/
  var clientsChannel = pusher.subscribe("presence-clients");

  /*---------------* Pusher Trigger Message Seen / Unseen *---------------*/
  var user_messages_events = pusher.subscribe("user_messages");

  /*---------------* Calls Channel for receiving calls from staff *---------------*/
  <?php if (get_option('chat_client_calls_enabled') == '1'): ?>
    window.PRCHAT_CSRF = {
      name: '<?= $this->security->get_csrf_token_name(); ?>',
      hash: '<?= $this->security->get_csrf_hash(); ?>'
    };
    window.PRCHAT_CALLS = window.PRCHAT_CALLS || {};
    var clientCallsChannel = pusher.subscribe("<?= CHAT_CALLS_CLIENT_CHANNEL_PREFIX . get_contact_user_id(); ?>");
    var clientCallsChannelInitialized = false;

    // Initialize call manager and UI when channel is ready
    clientCallsChannel.bind('pusher:subscription_succeeded', function() {
      clientCallsChannelInitialized = true;
      initializeClientCallEventBindings();
    });

    // Handle incoming call events
    function initializeClientCallEventBindings() {
      clientCallsChannel.bind('call-offer', function(payload) {
        try {
          var nowTs = Date.now();
          if (nowTs < (window.__CLIENT_CALL_SUPPRESS_UNTIL || 0)) {
            return;
          }
          if (window.__CLIENT_CALL_STATE && window.__CLIENT_CALL_STATE !== 'idle') {
            return;
          }

          var offer = JSON.parse(payload.sdp);
          var isVideoCall = payload.is_video || false;
          var callerName = payload.from_name || 'Staff';
          var callerAvatar = payload.from_avatar || '';
          var callerId = payload.from_id;

          // Store call info
          window.__CLIENT_CALL_PEER = callerId;
          window.__CLIENT_CALL_STATE = 'ringing';
          window.__CLIENT_CALL_IS_VIDEO = isVideoCall;
          window.__CLIENT_CALL_OFFER = offer;

          // Show incoming call UI
          showClientIncomingCallUI(callerName, callerAvatar, isVideoCall, callerId);

          // Play ringtone (ChatCallUI handles its own ringtone)
          if (!window.ChatCallUI) {
            playClientRingtone();
          }

        } catch (e) {
          console.error('[Client Call] Error handling call-offer:', e);
        }
      });

      // ICE candidates from staff
      clientCallsChannel.bind('call-ice', function(payload) {
        try {
          if (payload.from_id != window.__CLIENT_CALL_PEER) return;
          var cand = JSON.parse(payload.candidate);
          if (window.__clientCallManager) {
            window.__clientCallManager.addIceCandidate(cand);
          }
        } catch (e) {
          console.error('[Client Call] Error handling ICE candidate:', e);
        }
      });

      // Staff hung up
      clientCallsChannel.bind('call-hangup', function(payload) {
        if (payload.from_id != window.__CLIENT_CALL_PEER) return;

        stopClientRingtone();
        hideClientCallUI();
        hideClientInCallModal();

        // Remove audio elements
        var remoteAudio = document.getElementById('client-call-remote-audio');
        if (remoteAudio) remoteAudio.remove();

        if (window.__clientCallManager) {
          if (window.__clientCallManager.endFromRemote) {
            window.__clientCallManager.endFromRemote();
          }
          window.__clientCallManager = null;
        }

        window.__CLIENT_CALL_STATE = 'idle';
        window.__CLIENT_CALL_PEER = null;
        clientIsMuted = false;

        alert_float('info', '<?= _l('chat_call_ended'); ?>');
      });

      // Mute status from staff
      clientCallsChannel.bind('call-mute-status', function(payload) {
        if (payload.from_id != window.__CLIENT_CALL_PEER) return;
        showClientRemoteMuteIndicator(payload.is_muted);
      });
    }

    // Inject call UI styles (matching staff side call-ui.js)
    function ensureClientCallStyles() {
      if (document.getElementById('client-call-ui-styles')) return;
      var style = document.createElement('style');
      style.id = 'client-call-ui-styles';
      style.textContent =
        '.chat-call-overlay{position:fixed;inset:0;background:rgba(17,24,39,.6);z-index:100000;display:flex;align-items:center;justify-content:center}' +
        '.chat-call-box{background:#fff;border-radius:16px;box-shadow:0 28px 80px rgba(0,0,0,.35);padding:22px 22px 18px;min-width:340px;max-width:92vw;text-align:center}' +
        '.chat-call-title{font-size:18px;font-weight:700;color:#111827;margin:4px 0 4px}' +
        '.chat-call-subtitle{font-size:13px;color:#6b7280;margin:0 0 8px}' +
        '.chat-call-actions{display:flex;gap:10px;align-items:center;justify-content:center;margin-top:12px}' +
        '.chat-call-btn{border:0;border-radius:999px;padding:10px 16px;font-weight:600;color:#fff;cursor:pointer;box-shadow:0 10px 20px rgba(0,0,0,.12);transition:transform .12s ease}' +
        '.chat-call-btn:active{transform:scale(.98)}' +
        '.chat-call-btn--accept{background:#10b981}' +
        '.chat-call-btn--decline{background:#ef4444}' +
        '.chat-call-btn--neutral{background:#6b7280}' +
        '.chat-call-avatar{width:64px;height:64px;border-radius:999px;background:linear-gradient(135deg,#60a5fa,#34d399);margin:0 auto 8px;position:relative;overflow:hidden}' +
        '.chat-call-wave:before,.chat-call-wave:after{content:"";position:absolute;inset:-6px;border-radius:50%;border:2px solid rgba(255,255,255,.55);animation:chat-wave 1.8s infinite ease-out}' +
        '.chat-call-wave:after{animation-delay:.9s}' +
        '@keyframes chat-wave{0%{transform:scale(.9);opacity:.8}70%{transform:scale(1.2);opacity:.15}100%{transform:scale(1.35);opacity:0}}' +
        '.chat-call-timer{font-size:12px;color:#374151;margin-top:6px}' +
        '.chat-call-mute-indicator{font-size:12px;color:#ef4444;margin-top:4px;display:none}' +
        '.chat-call-mute-indicator.show{display:block}';
      document.head.appendChild(style);
    }

    // Incoming call UI - uses ChatCallUI (shared with staff) when available
    function showClientIncomingCallUI(callerName, callerAvatar, isVideoCall, callerId) {
      if (window.ChatCallUI) {
        ChatCallUI.showIncomingCall(callerName, function() {
          acceptClientCall(callerId);
        }, function() {
          declineClientCall(callerId);
        }, {
          avatarUrl: callerAvatar,
          isVideo: isVideoCall
        });
        return;
      }
      // Fallback: basic modal if call-ui.js not loaded
      ensureClientCallStyles();
      $('#client-incoming-call-modal').remove();
      var modalRoot = document.createElement('div');
      modalRoot.id = 'client-incoming-call-modal';
      modalRoot.className = 'chat-call-overlay';
      var box = document.createElement('div');
      box.className = 'chat-call-box';
      var avatar = document.createElement('div');
      avatar.className = 'chat-call-avatar chat-call-wave';
      if (callerAvatar) {
        avatar.style.backgroundImage = 'url("' + callerAvatar + '")';
        avatar.style.backgroundSize = 'cover';
        avatar.style.backgroundPosition = 'center';
      }
      var title = document.createElement('div');
      title.className = 'chat-call-title';
      title.textContent = (isVideoCall ? 'Video' : 'Voice') + ' call from ' + callerName;
      var actions = document.createElement('div');
      actions.className = 'chat-call-actions';
      var acceptBtn = document.createElement('button');
      acceptBtn.textContent = '<?= _l('chat_accept'); ?>';
      acceptBtn.className = 'chat-call-btn chat-call-btn--accept';
      acceptBtn.onclick = function() {
        acceptClientCall(callerId);
      };
      var declineBtn = document.createElement('button');
      declineBtn.textContent = '<?= _l('chat_decline'); ?>';
      declineBtn.className = 'chat-call-btn chat-call-btn--decline';
      declineBtn.onclick = function() {
        declineClientCall(callerId);
      };
      actions.appendChild(acceptBtn);
      actions.appendChild(declineBtn);
      box.appendChild(avatar);
      box.appendChild(title);
      box.appendChild(actions);
      modalRoot.appendChild(box);
      document.body.appendChild(modalRoot);
    }

    function hideClientCallUI() {
      if (window.ChatCallUI) {
        ChatCallUI.stopRingtone();
        ChatCallUI.closeModal();
      }
      $('#client-incoming-call-modal').remove();
    }

    // Accept call - supports both voice and video
    function acceptClientCall(staffId) {
      stopClientRingtone();
      hideClientCallUI();

      window.__CLIENT_CALL_STATE = 'connecting';
      var isVideoCall = window.__CLIENT_CALL_IS_VIDEO || false;

      // Set up client-specific endpoints for call-manager.js
      window.PRCHAT_CALLS = {
        answer: site_url + 'prchat/ClientCalls_Controller/answerCall',
        ice: site_url + 'prchat/ClientCalls_Controller/iceCandidate',
        hangup: site_url + 'prchat/ClientCalls_Controller/hangup',
        mute_status: site_url + 'prchat/ClientCalls_Controller/muteStatus'
      };
      window.PRCHAT_CSRF = {
        name: '<?= $this->security->get_csrf_token_name(); ?>',
        hash: '<?= $this->security->get_csrf_hash(); ?>'
      };

      // Auto-attach CSRF token to every jQuery AJAX POST request
      $.ajaxSetup({
        beforeSend: function(xhr, settings) {
          if (settings.type && settings.type.toUpperCase() === 'POST' && window.PRCHAT_CSRF) {
            if (typeof settings.data === 'string') {
              if (settings.data.indexOf(PRCHAT_CSRF.name) === -1) {
                settings.data += '&' + encodeURIComponent(PRCHAT_CSRF.name) + '=' + encodeURIComponent(PRCHAT_CSRF.hash);
              }
            } else if (settings.data instanceof FormData) {
              if (!settings.data.has(PRCHAT_CSRF.name)) {
                settings.data.append(PRCHAT_CSRF.name, PRCHAT_CSRF.hash);
              }
            } else if (typeof settings.data === 'object' && settings.data !== null) {
              if (!settings.data[PRCHAT_CSRF.name]) {
                settings.data[PRCHAT_CSRF.name] = PRCHAT_CSRF.hash;
              }
            } else {
              settings.data = PRCHAT_CSRF.name + '=' + encodeURIComponent(PRCHAT_CSRF.hash);
            }
          }
        }
      });

      // Initialize call manager
      if (!window.__clientCallManager) {
        window.__clientCallManager = new ChatCallManager(new ChatSignalingClient({}));
      }

      // Bridge for ChatCallUI controls (mute/camera buttons reference __chatCallManager)
      window.__chatCallManager = window.__clientCallManager;

      // Handle remote track (voice or video)
      window.__clientCallManager.onRemoteTrack = function(stream) {
        // Show in-call controls first (creates PiP for video, floating bar for voice)
        showClientInCallControls(staffId);

        if (isVideoCall) {
          // Insert remote video into PiP container
          var pip = document.getElementById('chat-call-pip');
          if (pip) {
            var remoteVideo = document.getElementById('chat-call-remote-video');
            if (!remoteVideo) {
              remoteVideo = document.createElement('video');
              remoteVideo.id = 'chat-call-remote-video';
              remoteVideo.autoplay = true;
              remoteVideo.setAttribute('playsinline', '');
              pip.insertBefore(remoteVideo, pip.firstChild);
            }
            remoteVideo.srcObject = stream;
          }
        } else {
          // Voice call: audio element
          var audio = document.getElementById('client-call-remote-audio');
          if (!audio) {
            audio = document.createElement('audio');
            audio.id = 'client-call-remote-audio';
            audio.autoplay = true;
            document.body.appendChild(audio);
          }
          audio.srcObject = stream;
        }
      };

      // Handle local track (camera preview in PiP for video calls)
      window.__clientCallManager.onLocalTrack = function(stream) {
        if (isVideoCall && stream.getVideoTracks().length > 0) {
          var pip = document.getElementById('chat-call-pip');
          if (pip) {
            var localVideo = document.getElementById('chat-call-local-video');
            if (!localVideo) {
              localVideo = document.createElement('video');
              localVideo.id = 'chat-call-local-video';
              localVideo.autoplay = true;
              localVideo.muted = true;
              localVideo.setAttribute('playsinline', '');
              pip.appendChild(localVideo);
            }
            localVideo.srcObject = stream;
          }
        }
      };

      // Answer the call
      window.__clientCallManager.answer(contact_id, staffId, window.__CLIENT_CALL_OFFER, false).then(function() {
        window.__CLIENT_CALL_STATE = 'inCall';
      }).catch(function(err) {
        console.error('[Client Call] Error answering call:', err);
        alert_float('danger', '<?= _l('chat_call_failed'); ?>');
        cleanupClientCall();
      });
    }

    // Decline call
    function declineClientCall(staffId) {
      stopClientRingtone();
      hideClientCallUI();

      $.post(site_url + 'prchat/ClientCalls_Controller/decline', {
        to_id: staffId,
        <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
      });

      window.__CLIENT_CALL_STATE = 'idle';
      window.__CLIENT_CALL_PEER = null;
    }

    // End call from client side
    function endClientCall(staffId) {
      // cleanupClientCall calls hangup() which sends the hangup via PRCHAT_CALLS.hangup
      cleanupClientCall();
      alert_float('info', '<?= _l('chat_call_ended'); ?>');
    }

    function cleanupClientCall() {
      hideClientCallUI();
      hideClientInCallModal();

      // Remove audio elements
      $('#client-call-remote-audio').remove();
      $('#client-remote-mute-indicator').remove();

      // Reset mute state
      clientIsMuted = false;
      window.__chatCallManager = null;

      if (window.__clientCallManager) {
        window.__clientCallManager.hangup && window.__clientCallManager.hangup();
        window.__clientCallManager = null;
      }

      window.__CLIENT_CALL_STATE = 'idle';
      window.__CLIENT_CALL_PEER = null;
    }

    // In-call controls: video → ChatCallUI PiP (draggable), voice → floating compact bar
    var clientCallStartTime = null;
    var clientCallTimerInterval = null;

    function showClientInCallControls(staffId) {
      var isVideoCall = window.__CLIENT_CALL_IS_VIDEO || false;

      // Get staff name and avatar
      var staffName = $(".staff_container#staff_" + staffId + " .staff_children_parent_child_div img").attr('data-original-title');
      if (!staffName) {
        staffName = $(".staff_container#staff_" + staffId + " img").attr('data-original-title') || '<?= _l('chat_staff'); ?>';
      }
      if (staffName && staffName.indexOf('<br>') > -1) {
        staffName = staffName.split('<br>')[0].trim();
      }
      var staffAvatar = $(".staff_container#staff_" + staffId + " img").attr('src') || '';

      // Bridge call manager so ChatCallUI buttons work
      window.__chatCallManager = window.__clientCallManager;

      if (isVideoCall && window.ChatCallUI) {
        // Video call: use ChatCallUI PiP (draggable floating window, can chat during call)
        ChatCallUI.showInCall(staffName, function() {
          endClientCall(staffId);
        }, {
          isVideo: true,
          avatarUrl: staffAvatar
        });
      } else {
        // Voice call: floating compact bar (non-blocking, can chat during call)
        showClientFloatingVoiceBar(staffId, staffName, staffAvatar);
      }
    }

    // Floating voice call bar — compact, draggable, non-blocking
    function showClientFloatingVoiceBar(staffId, staffName, staffAvatar) {
      var existing = document.getElementById('client-floating-voice-bar');
      if (existing) existing.remove();

      var bar = document.createElement('div');
      bar.id = 'client-floating-voice-bar';
      bar.style.cssText =
        'position:fixed;bottom:20px;right:20px;z-index:99999;' +
        'background:rgba(17,24,39,0.95);backdrop-filter:blur(10px);' +
        'border-radius:50px;padding:8px 16px 8px 8px;' +
        'box-shadow:0 8px 32px rgba(0,0,0,0.3);' +
        'display:flex;align-items:center;gap:12px;cursor:grab;' +
        'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;';

      // Avatar
      var avatarEl = document.createElement('div');
      avatarEl.style.cssText =
        'width:40px;height:40px;border-radius:50%;' +
        'background:linear-gradient(135deg,#60a5fa,#34d399);flex-shrink:0;overflow:hidden;';
      if (staffAvatar) {
        avatarEl.style.backgroundImage = 'url("' + staffAvatar + '")';
        avatarEl.style.backgroundSize = 'cover';
        avatarEl.style.backgroundPosition = 'center';
      }

      // Info (name + timer)
      var info = document.createElement('div');
      info.style.cssText = 'color:#fff;min-width:0;';
      var nameEl = document.createElement('div');
      nameEl.style.cssText = 'font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;';
      nameEl.textContent = staffName;
      var timerEl = document.createElement('div');
      timerEl.style.cssText = 'font-size:11px;color:#9ca3af;';
      timerEl.id = 'client-voice-bar-timer';
      timerEl.textContent = '00:00';
      info.appendChild(nameEl);
      info.appendChild(timerEl);

      // Remote mute indicator
      var muteIndicator = document.createElement('div');
      muteIndicator.id = 'client-remote-mute-status';
      muteIndicator.style.cssText = 'display:none;color:#ef4444;font-size:12px;flex-shrink:0;';
      muteIndicator.innerHTML = '<i class="fa fa-microphone-slash"></i>';

      // Mute button
      var muteBtn = document.createElement('button');
      muteBtn.style.cssText =
        'width:36px;height:36px;border-radius:50%;border:none;background:#374151;' +
        'color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;' +
        'flex-shrink:0;transition:background 0.15s;';
      muteBtn.innerHTML = '<i class="fa fa-microphone" style="font-size:14px"></i>';
      muteBtn.title = '<?= _l('chat_mute'); ?>';
      muteBtn.onclick = function(e) {
        e.stopPropagation();
        toggleClientMute(staffId);
        var icon = muteBtn.querySelector('i');
        icon.className = clientIsMuted ? 'fa fa-microphone-slash' : 'fa fa-microphone';
        muteBtn.title = clientIsMuted ? '<?= _l('chat_unmute'); ?>' : '<?= _l('chat_mute'); ?>';
      };

      // End button
      var endBtn = document.createElement('button');
      endBtn.style.cssText =
        'width:36px;height:36px;border-radius:50%;border:none;background:#ef4444;' +
        'color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;' +
        'flex-shrink:0;transition:background 0.15s;';
      endBtn.innerHTML = '<i class="fa fa-phone" style="font-size:14px;transform:rotate(135deg)"></i>';
      endBtn.title = '<?= _l('chat_end'); ?>';
      endBtn.onclick = function(e) {
        e.stopPropagation();
        endClientCall(staffId);
      };

      bar.appendChild(avatarEl);
      bar.appendChild(info);
      bar.appendChild(muteIndicator);
      bar.appendChild(muteBtn);
      bar.appendChild(endBtn);
      document.body.appendChild(bar);

      // Drag logic
      (function() {
        var dragging = false,
          offsetX = 0,
          offsetY = 0;
        bar.addEventListener('mousedown', function(e) {
          if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I') return;
          dragging = true;
          var rect = bar.getBoundingClientRect();
          offsetX = e.clientX - rect.left;
          offsetY = e.clientY - rect.top;
          bar.style.cursor = 'grabbing';
          e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) {
          if (!dragging) return;
          bar.style.left = Math.max(0, Math.min(e.clientX - offsetX, window.innerWidth - bar.offsetWidth)) + 'px';
          bar.style.top = Math.max(0, Math.min(e.clientY - offsetY, window.innerHeight - bar.offsetHeight)) + 'px';
          bar.style.right = 'auto';
          bar.style.bottom = 'auto';
        });
        document.addEventListener('mouseup', function() {
          if (dragging) {
            dragging = false;
            bar.style.cursor = 'grab';
          }
        });
      })();

      // Timer
      clientCallStartTime = Date.now();
      if (clientCallTimerInterval) clearInterval(clientCallTimerInterval);
      clientCallTimerInterval = setInterval(function() {
        var elapsed = Math.floor((Date.now() - clientCallStartTime) / 1000);
        var mm = String(Math.floor(elapsed / 60)).padStart(2, '0');
        var ss = String(elapsed % 60).padStart(2, '0');
        var t = document.getElementById('client-voice-bar-timer');
        if (t) t.textContent = mm + ':' + ss;
      }, 1000);

      // Acquire microphone for voice call
      (function acquireVoiceMic() {
        var m = window.__clientCallManager;
        if (m && m.localStream) return;
        navigator.mediaDevices.getUserMedia({
            audio: true
          })
          .then(function(stream) {
            var mgr = window.__clientCallManager;
            if (!mgr || !mgr.pc) {
              stream.getTracks().forEach(function(t) {
                t.stop();
              });
              return;
            }
            mgr.localStream = stream;
            stream.getTracks().forEach(function(track) {
              var senders = mgr.pc.getSenders();
              var replaced = false;
              for (var i = 0; i < senders.length; i++) {
                if (!senders[i].track && mgr.pc.getTransceivers) {
                  var trs = mgr.pc.getTransceivers();
                  for (var j = 0; j < trs.length; j++) {
                    if (trs[j].sender === senders[i] && trs[j].receiver &&
                      trs[j].receiver.track && trs[j].receiver.track.kind === track.kind) {
                      senders[i].replaceTrack(track).catch(function() {});
                      replaced = true;
                      break;
                    }
                  }
                }
                if (replaced) break;
              }
              if (!replaced && mgr.pc) mgr.pc.addTrack(track, stream);
            });
            if (typeof mgr.onLocalTrack === 'function') {
              try {
                mgr.onLocalTrack(stream);
              } catch (_) {}
            }
          })
          .catch(function(err) {
            console.warn('[Client Call] voice getUserMedia failed:', err);
          });
      })();
    }

    function hideClientInCallModal() {
      // ChatCallUI elements (PiP for video, modal for voice)
      if (window.ChatCallUI) {
        ChatCallUI.closeModal();
        ChatCallUI.hideVideoCallControls();
      }
      // Floating voice bar
      var voiceBar = document.getElementById('client-floating-voice-bar');
      if (voiceBar) voiceBar.remove();
      // Legacy in-call modal
      var modal = document.getElementById('client-incall-modal');
      if (modal) modal.remove();
      // Timer cleanup
      if (clientCallTimerInterval) {
        clearInterval(clientCallTimerInterval);
        clientCallTimerInterval = null;
      }
      clientCallStartTime = null;
    }

    // Mute toggle - matching staff side design
    var clientIsMuted = false;

    function toggleClientMute(staffId) {
      clientIsMuted = !clientIsMuted;

      // setMuted also sends mute status to staff via PRCHAT_CALLS.mute_status
      if (window.__clientCallManager && window.__clientCallManager.setMuted) {
        window.__clientCallManager.setMuted(clientIsMuted);
      }
    }

    // Remote mute indicator - works with floating voice bar and ChatCallUI
    function showClientRemoteMuteIndicator(isMuted) {
      var indicator = document.getElementById('client-remote-mute-status');
      if (indicator) {
        indicator.style.display = isMuted ? 'inline-flex' : 'none';
      }
    }

    // Ringtone
    var clientRingtone = null;

    function playClientRingtone() {
      try {
        // Generate inline beep sound using Web Audio API
        function playBeep() {
          const audioContext = new(window.AudioContext || window.webkitAudioContext)();
          const oscillator = audioContext.createOscillator();
          const gainNode = audioContext.createGain();

          oscillator.connect(gainNode);
          gainNode.connect(audioContext.destination);

          oscillator.frequency.value = 800; // 800 Hz tone
          oscillator.type = 'sine';

          gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
          gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

          oscillator.start(audioContext.currentTime);
          oscillator.stop(audioContext.currentTime + 0.5);
        }

        // Play beep immediately
        playBeep();

        // Store reference for cleanup
        clientRingtone = {
          isPlaying: true
        };

        // Play beep in loop using interval
        if (window.clientRingtoneInterval) {
          clearInterval(window.clientRingtoneInterval);
        }
        window.clientRingtoneInterval = setInterval(function() {
          if (clientRingtone && clientRingtone.isPlaying) {
            playBeep();
          }
        }, 1000);
      } catch (e) {
        console.warn('[Client Call] Ringtone error:', e);
      }
    }

    function stopClientRingtone() {
      if (window.ChatCallUI) ChatCallUI.stopRingtone();
      if (clientRingtone) {
        clientRingtone.isPlaying = false;
        clientRingtone = null;
      }
      if (window.clientRingtoneInterval) {
        clearInterval(window.clientRingtoneInterval);
        window.clientRingtoneInterval = null;
      }
    }

    // Initialize call state
    window.__CLIENT_CALL_STATE = 'idle';
    window.__CLIENT_CALL_PEER = null;
  <?php endif; ?>

  /*---------------* Member array for online / offline activity *---------------*/
  var pendingRemoves = [];

  clientsChannel.bind("pusher:subscription_succeeded", function(member) {
    pushNewMember();
  });

  /*---------------* Pusher Trigger user logout *---------------*/
  clientsChannel.bind("pusher:member_removed", function(members) {
    removeChatMember(members);
  });

  /*---------------* Pusher Trigger user connected *---------------*/
  clientsChannel.bind("pusher:member_added", function(member) {
    addChatMember(member);
  });
  /*---------------* New staff member activity online / offline  *---------------*/
  function pushNewMember() {
    $.each(customerAdmins, function(i, admin) {
      var user = clientsChannel.members.get(admin.staffid);
      if (user !== null) {
        $(".staff_container#staff_" + user.id).find(".staff_children_parent_child_div").addClass("onlineStaff");
        $(".staff_container#staff_" + user.id).find(".staff_image_second_children_div").addClass("onlineStaff");
      }
    });
  }

  /*---------------* New chat members tracking / removing *---------------*/
  function addChatMember(member) {
    var pendingRemoveTimeout = pendingRemoves[member.id];
    $("div#staff_" + member.id).find(".staff_children_parent_child_div").addClass("onlineStaff");
    $("div#staff_" + member.id).find(".staff_image_second_children_div").addClass("onlineStaff");

    if (pendingRemoveTimeout) {
      clearTimeout(pendingRemoveTimeout);
    }
  }

  /*---------------* New chat members tracking / removing from channel and UX*---------------*/
  function removeChatMember(member) {
    pendingRemoves[member.id] = setTimeout(function() {
      $("div#staff_" + member.id).find(".staff_children_parent_child_div").removeClass("onlineStaff");
      $("div#staff_" + member.id).find(".staff_image_second_children_div").removeClass("onlineStaff");

    }, 5000);
  }


  /*---------------* Append customer admins in view *---------------*/
  function appendCustomerAdmins() {
    var dfd = jQuery.Deferred();
    var promises = [];
    var counter = 1;

    if (customerAdmins.length === 0) {
      // Disable chat functionality when no staff available
      disableChatUI();
      return false;
    }

    $.each(customerAdmins, function(i, admin) {
      var fullname = admin.firstname + " " + admin.lastname;
      var adminImage = "";

      var avatarUrl = fetchUserAvatar(admin.staffid, admin.profile_image);

      var tooltipParts = [];
      tooltipParts.push(showStaffNames ? fullname : genericStaffLabel);
      if (showStaffRoles) tooltipParts.push(admin.role.trim());
      var tooltipContent = tooltipParts.join('<br>');

      adminImage += "<div class =\"staff_container\" id=\"staff_" + admin.staffid + "\">";
      adminImage += "<div class =\"staff_image_second_children_div\">";
      adminImage += "<div class =\"staff_notification\" data-notification=\"0\"></div>";
      adminImage += "<div class = \"staff_children_parent_child_div\" ><img src=\"" + avatarUrl + "\" onerror=\"this.onerror=null;this.src='" + placeholderImg + "';\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" data-original-title=\"" + tooltipContent + "\"/></div>";
      adminImage += "</div></div>";

      $(".staff_image_wrapper").append(adminImage);

      counter++;
      promises.push(counter);
    });
    var topStaff = $(".staff_image_wrapper .staff_container:first").attr("id");
    $(".m-area").attr("data-staffid", topStaff.replace("staff_", ""));
    currentStaff = $(".m-area").data("staffid");

    if (counter === counter) {
      dfd.resolve(counter);
    }
  }

  /*---------------* Disable chat UI when no staff available *---------------*/
  function disableChatUI() {
    $(".clients_textarea").prop("disabled", true).attr("placeholder", "");
    $(".client-emoji-trigger, .client-file-trigger, .send_client_message").css("opacity", "0.5").css("pointer-events", "none");

    // Hide staff header section
    $(".customer_main_placeholder_top").hide();

    // Show message in chat area
    var noStaffMessage = `
            <div class="no-staff-message" style="
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100%;
                text-align: center;
                padding: 40px 20px;
                color: #6b7280;
            ">
                <svg style="width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.5;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #374151;"><?= _l('chat_no_staff_available'); ?></h3>
                <p style="margin: 0; font-size: 14px; line-height: 1.5;">Please contact support or wait for a staff member to be assigned to assist you.</p>
            </div>
        `;
    $(".m-area .chat").html(noStaffMessage);

    // Set a flag to prevent message sending
    window.chatDisabled = true;
  }

  /*---------------* Enable chat UI when staff becomes available *---------------*/
  function enableChatUI() {
    $(".clients_textarea").prop("disabled", false).attr("placeholder", "<?= _l('chat_type_a_message'); ?>");
    $(".client-emoji-trigger, .client-file-trigger, .send_client_message").css("opacity", "1").css("pointer-events", "auto");

    // Show staff header section
    $(".customer_main_placeholder_top").show();

    // Remove no staff message
    $(".m-area .chat .no-staff-message").remove();

    // Clear the flag
    window.chatDisabled = false;
  }

  /*---------------* Promise after admins are appended to view *---------------*/
  $.when(appendCustomerAdmins()).then(
    function() {
      // Initialize tooltips with container: body so they appear outside clipped containers
      $('.staff_children_parent_child_div img[data-toggle="tooltip"]').tooltip({
        container: 'body',
        placement: 'top'
      });

      // Select first staff
      var firstStaff = $(".staff_container").first();
      firstStaff.trigger("click");

      checkForStaffUnreadMessages.done(function(r) {
        // Check if r is valid and iterable
        if (r && typeof r === 'object' && !r.null) {
          var totalUnread = 0;
          $.each(r, function(i, sender) {
            if (sender && sender.count_messages !== undefined) {
              var messageCount = parseInt(sender.count_messages) || 0;
              totalUnread += messageCount;
              $("body").find("div#" + sender.sender_id)
                .find(".staff_notification")
                .attr("data-notification", messageCount)
                .show();
            }
          });
          // Show total unread count in main chat notification badge
          if (totalUnread > 0) {
            setInitialNotificationCount(totalUnread);
          }
        }
      });
    }
  );

  /*---------------* Function that handles updating unread messages  *---------------*/
  function updateUnreadNotifications(id) {
    $.post(customerSettings.updateStaffUnread, {
      id: id,
      client: "client",
      contact_id: contact_id
    });
    clearNotifications();
  }

  /*---------------* Paperplane button send event click  *---------------*/
  $(".send_client_message").on("click", function() {
    // Prevent sending if chat is disabled (no staff available)
    if (window.chatDisabled === true) {
      return false;
    }

    clientsArea.trigger($.Event("keypress", {
      which: 13
    }));
  });

  /*---------------* Event click and function handlers for textarea to check for unread messages *---------------*/
  $("body").on("click", ".clients_textarea", function(e) {
    // Only check when chat is open
    if ($(".firstDiv").hasClass("chat-open")) {
      checkForUnreadMessages();
    }
  });

  function checkForUnreadMessages() {
    var staff_id = $(".m-area").attr("data-staffid");
    var lastMessage = $(".m-area li:last");
    var notification = parseInt($(".active_staff").prev().data("notification")) || 0;

    // Only mark as read if there are actual unread messages (notification > 0)
    // and the last message is from staff (customer_admin)
    if (lastMessage.hasClass("customer_admin") && notification > 0 && staff_id) {
      updateUnreadNotifications(staff_id);
    }
  }

  /*---------------* Ul li customers click event *---------------*/

  $(".ch_pointer-main-first").click(function() {
    scrollBottom();
  });

  $(".ch_pointer-main, .top_close_icon").on("click", function() {
    // Use CSS class toggle for smooth animation
    var $chatWindow = $(".firstDiv");
    var $chatIcon = $(".ch_pointer .ch_pointer-main-first"); // Chat bubble icon
    var $closeIcon = $(".ch_pointer .ch_pointer-main-under"); // X close icon
    var isOpen = $chatWindow.hasClass("chat-open");

    if (isOpen) {
      if (_clientChatOpenTimeouts.first) clearTimeout(_clientChatOpenTimeouts.first);
      if (_clientChatOpenTimeouts.retry) clearTimeout(_clientChatOpenTimeouts.retry);
      _clientChatOpenTimeouts.first = _clientChatOpenTimeouts.retry = null;
      $chatWindow.removeClass("chat-open");
      $closeIcon.fadeOut(150, function() {
        $chatIcon.fadeIn(200);
      });
    } else {
      // Open the chat - show X icon, hide chat icon
      // Reset main floating icon notification when chat is opened
      // Individual staff badges will remain until textarea is focused
      if ($(".chatNewNotification").length > 0) {
        resetChatNotifications();
      }
      $chatWindow.addClass("chat-open");
      $chatIcon.fadeOut(150, function() {
        $closeIcon.fadeIn(200);
      });

      // Auto-select first staff and load messages after panel is open
      function triggerFirstStaffAndLoad() {
        var $first = $(".staff_container").first();
        if ($first.length && !$(".staff_children_parent_child_div.active_staff").length) {
          $first.trigger("click");
        }
      }
      if (!$(".staff_children_parent_child_div.active_staff").length) {
        if (_clientChatOpenTimeouts.first) clearTimeout(_clientChatOpenTimeouts.first);
        _clientChatOpenTimeouts.first = setTimeout(function() {
          _clientChatOpenTimeouts.first = null;
          triggerFirstStaffAndLoad();
        }, 300);
      } else if ($(".m-area ol.chat li").length === 0) {
        var activeStaffId = $(".m-area").attr("data-staffid");
        if (activeStaffId) {
          appendMutualMessages(activeStaffId, contact_id);
        }
      }

      // Focus on textarea after a short delay to allow DOM to update
      setTimeout(function() {
        $(".clients_textarea").focus();
        scrollBottom();
      }, 200);
    }
  });

  /*---------------* Function that handles message sending send and typing events *---------------*/
  var typingTimeout = null;
  var isTyping = false;
  var lastTypingForm = null;

  // Send typing indicator
  function sendTypingIndicator(form, isTypingNow) {
    var typingField = form.find('input[name="typing"]');
    typingField.val(isTypingNow ? "true" : "null");
    $.post(customerSettings.clientsMessagesPath, form.serializeArray());
  }

  // Stop typing indicator immediately (optionally send event)
  function stopTyping(sendEvent) {
    clearTimeout(typingTimeout);
    if (isTyping && lastTypingForm && sendEvent !== false) {
      sendTypingIndicator(lastTypingForm, false);
    }
    isTyping = false;
  }

  // Listen for input changes to detect when textarea is emptied (backspace/delete)
  clientsArea.on("input", function() {
    var form = $(this).parents("form#staffMessagesForm");
    lastTypingForm = form;

    if ($(this).val().trim() === "" && isTyping) {
      // User deleted all text - stop typing immediately
      stopTyping();
    }
  });

  clientsArea.on("keypress", function(e) {
    var form = $(this).parents("form#staffMessagesForm");
    lastTypingForm = form;

    if (e.which == 13 && !e.shiftKey) {
      e.preventDefault();

      if ($(this).hasClass('prchat-editing') && typeof prchatSubmitEdit === 'function') {
        prchatSubmitEdit($(this));
        return false;
      }

      if (window.chatDisabled === true) {
        return false;
      }

      var message = $.trim($(this).val());

      if ($.trim(message) == "" || internetConnectionCheck() === false) {
        return false;
      }

      // Apply reply prefix if client is replying to a staff message
      if (typeof getReplyFormattedMessage === 'function') {
        message = getReplyFormattedMessage(message);
      }

      // Clear typing state locally (don't send event - send-event will hide on receiver)
      stopTyping(false);

      var currentTime = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
      var clientAvatar = contactAvatarUrl || placeholderImg;
      var displayMessage = renderMessageContent(message);

      // Insert date separator if the day changed since the last message
      maybeInsertDateSeparator();

      var todayDateKey = new Date().toISOString().substring(0, 10);
      messageToAppend = `
                <li class="client has-status sending" data-date-key="${todayDateKey}">
                    <div class="msg-row">
                        <img class="msg-avatar" src="${clientAvatar}" onerror="this.onerror=null;this.src='${placeholderImg}';" alt="" />
                        <div class="msg">
                            <span class="client_name">${contact_full_name}</span>
                            <p>${displayMessage}</p>
                            <span class="msg-time">${currentTime}</span>
                            <span class="msg-status sending" data-toggle="tooltip" title="<?= _l('chat_sending'); ?>">
                                <i class="fa fa-clock"></i>
                            </span>
                            ${clientMessageOptions('', false, displayMessage)}
                        </div>
                    </div>
                </li>`;

      $(".m-area .chat").append(messageToAppend);
      clearClientChatOptionsPosition();

      form.find('input[name="typing"]').val("false");
      message = escapeHtml(message);

      var formData = form.serializeArray();

      formData.push({
        name: "client_message",
        value: message
      }, {
        name: "client_id",
        value: client_id
      }, {
        name: "contact_full_name",
        value: contact_full_name
      }, {
        name: "company",
        value: contact_company_name
      });

      // send event (expect JSON { id } from server for DB message row)
      $.post(customerSettings.clientsMessagesPath, formData, null, 'json')
        .done(function(resp) {
          var $li = $(".m-area ol.chat li.client.sending:last");
          if (resp && resp.error) {
            $li.find(".msg-status")
              .removeClass("sending")
              .addClass("failed")
              .attr("title", "<?= _l('chat_send_failed'); ?>")
              .html('<i class="fa fa-exclamation-circle"></i>');
            $li.removeClass("sending");
            return;
          }
          if (typeof clearReplyData === 'function') {
            clearReplyData();
          }
          if (resp && resp.id) {
            assignClientChatMessageId($li, resp.id);
          }
          $li.removeClass("sending")
            .find(".msg-status")
            .removeClass("sending")
            .addClass("delivered")
            .attr("title", "<?= _l('chat_msg_delivered'); ?>")
            .html('<i class="fa fa-check"></i>');
        })
        .fail(function() {
          // Mark as failed
          $(".m-area ol.chat li.sending:last")
            .find(".msg-status")
            .removeClass("sending")
            .addClass("failed")
            .attr("title", "<?= _l('chat_send_failed'); ?>")
            .html('<i class="fa fa-exclamation-circle"></i>');
          $(".m-area ol.chat li.sending:last").removeClass("sending");
        });
      $(this).val("").focus();
      scrollBottom(300);
      $(".clients_textarea").val("");

    } else {
      // User is typing - send typing indicator
      if (!isTyping) {
        isTyping = true;
        sendTypingIndicator(form, true);
      }

      // Reset the typing timeout (shorter - 1.5 seconds)
      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(function() {
        isTyping = false;
        sendTypingIndicator(form, false);
      }, 1500);
    }
  });

  /*---------------* Event that is binded to typing event with pusher webockets *---------------*/
  var clearTypingTimerId;
  clientsChannel.bind("typing-event", function(data) {
    var clearTypingInterval = 2500;
    var typingIndicator = $(".clientwrapper").find(".typing-indicator");

    // Only show typing indicator if:
    // 1. Message is from the currently selected staff
    // 2. Message is TO this specific client (not staff-to-staff)
    // 3. The "to" field must start with "client_" to ensure it's for a client
    if (data.from == currentStaff &&
      data.to == contact_name_id &&
      data.to && data.to.startsWith("client_") &&
      data.message == "true") {
      var staffId = data.from.replace("staff_", "");
      var staffName = genericStaffLabel;
      if (showStaffNames) {
        $.each(customerAdmins, function(i, admin) {
          if (admin.staffid == staffId) {
            staffName = admin.firstname + " " + admin.lastname;
            return false;
          }
        });
      }
      typingIndicator.find(".typing-text").text(staffName + " is typing...");
      typingIndicator.addClass("show");
      // Scroll to bottom to show typing indicator
      var mArea = $(".clientwrapper").find(".m-area");
      if (mArea.length) {
        mArea.scrollTop(mArea[0].scrollHeight);
      }
      clearTimeout(clearTypingTimerId);
      clearTypingTimerId = setTimeout(function() {
        typingIndicator.removeClass("show");
      }, clearTypingInterval);
    } else if (data.from == currentStaff && data.to == contact_name_id && data.message == "null") {
      typingIndicator.removeClass("show");
    }
  });

  /*---------------* Event that is binded to send event with pusher webockets *---------------*/
  clientsChannel.bind("send-event", function(data) {
    $(".clientwrapper").find(".typing-indicator").removeClass("show");

    // IMPORTANT: Only process messages that are meant for THIS client
    // Skip if the message is not TO this client (e.g., staff-to-staff messages)
    if (!data.to || data.to !== contact_name_id) {
      return;
    }

    var _isCallMsg = data.is_call || (typeof data.message === 'string' && /^\[CALL:(voice|video):\d/.test(data.message));

    var isChatMinimized = !$("body").find(".firstDiv").hasClass("chat-open");
    var staffId = data.from.replace('staff_', '');

    // Check if staff exists in the UI
    var staffExists = $("#staff_" + staffId).length > 0;

    // If staff doesn't exist (e.g., admin not in customer_admins), add them dynamically
    if (!staffExists && data.from_staff_data) {
      var staffData = data.from_staff_data;
      var fullname = staffData.firstname + " " + staffData.lastname;
      var role = staffData.role || '<?= _l('chat_role_staff'); ?>';
      var avatarUrl = fetchUserAvatar(staffData.staffid, staffData.profile_image);
      var tooltipParts = [];
      tooltipParts.push(showStaffNames ? fullname : genericStaffLabel);
      if (showStaffRoles) tooltipParts.push(role.trim());
      var tooltipContent = tooltipParts.join('<br>');

      var adminImage = "<div class=\"staff_container\" id=\"staff_" + staffData.staffid + "\">" +
        "<div class=\"staff_image_second_children_div\">" +
        "<div class=\"staff_notification\" data-notification=\"0\"></div>" +
        "<div class=\"staff_children_parent_child_div\"><img src=\"" + avatarUrl + "\" onerror=\"this.onerror=null;this.src='" + placeholderImg + "';\" data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" data-original-title=\"" + tooltipContent + "\"/></div>" +
        "</div></div>";

      $(".staff_image_wrapper").append(adminImage);

      // Initialize tooltip for new staff
      $('.staff_children_parent_child_div img[data-toggle="tooltip"]').tooltip({
        container: 'body',
        placement: 'top'
      });

      // Add to customerAdmins array
      customerAdmins.push({
        staffid: staffData.staffid,
        firstname: staffData.firstname,
        lastname: staffData.lastname,
        profile_image: staffData.profile_image,
        role: role
      });

      // If this is the first staff being added, enable chat UI
      if (customerAdmins.length === 1) {
        enableChatUI();
      }

      // If no staff is currently selected, select this new staff
      if (!currentStaff || currentStaff === "") {
        $("#staff_" + staffData.staffid).trigger("click");
        return;
      }
    }

    var isChatOpen = $(".firstDiv").hasClass("chat-open");

    var fromStaffNorm = String(data.from || '').replace('staff_', '');
    var currentStaffNorm = String(currentStaff || '').replace('staff_', '');
    if (fromStaffNorm == currentStaffNorm && data.to == contact_name_id) {

      if (isChatMinimized && !_isCallMsg) {
        showChatNotification();
        if (window.chatSoundManager) window.chatSoundManager.initClientSound(data);
        var notifyCurrentStaff = $("#" + data.from + " .staff_notification");
        var currentNotification = parseInt(notifyCurrentStaff.attr("data-notification")) || 0;
        notifyCurrentStaff.attr("data-notification", currentNotification + 1).show();
      }

      var renderedMessage = renderMessageContent(data.message);
      var currentTime = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });

      // Insert date separator if the day changed since the last message
      maybeInsertDateSeparator();

      // Call messages → centered system bubble
      if (isCallMessageStr(data.message)) {
        var todayKey = new Date().toISOString().substring(0, 10);
        var callHtml = '<li class="call-message-row" data-date-key="' + todayKey + '">' + renderCallMessageHtml(data.message, currentTime) + '</li>';
        $(".m-area ol.chat").append(callHtml);
        scrollBottom();
      } else {

        var staffId = (data.from || '').replace('staff_', '');
        var staffProfileImage = (data.from_staff_data && data.from_staff_data.profile_image) ? data.from_staff_data.profile_image : null;
        var staffAvatarUrl = fetchUserAvatar(staffId, staffProfileImage);
        var staffFullName = data.from_name ||
          (data.from_staff_data && data.from_staff_data.firstname ? (data.from_staff_data.firstname + ' ' + (data.from_staff_data.lastname || '')).trim() : null) ||
          'Staff Member';
        var adminNameHtml = buildStaffLabel(staffFullName, staffId);
        var msgId = (data.id != null && data.id !== '') ? data.id : (data.last_insert_id != null && data.last_insert_id !== '') ? String(data.last_insert_id) : '';
        var todayKey = new Date().toISOString().substring(0, 10);
        var msgHtml = `
                <li class="customer_admin" data-date-key="${todayKey}"${msgId ? ' id="' + msgId + '" data-id="' + msgId + '"' : ''}>
                    <div class="msg-row">
                        <img class="msg-avatar" src="${staffAvatarUrl}" onerror="this.onerror=null;this.src='${placeholderImg}';" alt="" />
                        <div class="msg">
                            ${adminNameHtml}
                            <p>${renderedMessage}</p>
                            <span class="msg-time">${currentTime}</span>
                            ${clientMessageOptions(msgId, true, renderedMessage)}
                        </div>
                    </div>
                </li>`;

        $(".m-area ol.chat").append(msgHtml);
        clearClientChatOptionsPosition();
        scrollBottom();

      } // end else (non-call message)

      if (isChatOpen && $(".clients_textarea").is(":focus")) {
        updateUnreadNotifications(data.from.replace('staff_', ''));
      } else if (isChatOpen && !_isCallMsg) {
        var notifyCurrentStaff = $("#" + data.from + " .staff_notification");
        var currentNotification = parseInt(notifyCurrentStaff.attr("data-notification")) || 0;
        notifyCurrentStaff.attr("data-notification", currentNotification + 1).show();
      }
    }

    if (fromStaffNorm !== currentStaffNorm && data.to == contact_name_id) {
      if (!_isCallMsg) {
        var notifyStaff = $("#" + data.from + " .staff_notification");
        if (notifyStaff.length > 0) {
          var notification = parseInt(notifyStaff.attr("data-notification")) || 0;
          notifyStaff.attr("data-notification", notification + 1).show();
        }

        if (!isChatOpen) {
          showChatNotification();
          if (window.chatSoundManager) window.chatSoundManager.initClientSound(data);
        }
      }
    }

  });

  clientsChannel.bind("message-edited", function(data) {
    if (!data || !data.message_id || !data.rendered_message) {
      return;
    }
    if (data.to !== contact_name_id && data.from !== contact_name_id) {
      return;
    }
    var $li = $("#clientChat .m-area ol.chat li#" + data.message_id);
    if (!$li.length) {
      return;
    }
    $li.find(".msg p").first().html(data.rendered_message);
    if (!$li.find(".prchat-edited-tag").length) {
      $li.find(".msg p").first().after('<span class="prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>');
    }
  });

  /*---------------* Init current chat loader synchronized with client messages append *---------------*/
  function activateClientsLoader(promise = null) {
    if (promise !== null) {
      var initLoader = $(".m-area");
      if (initLoader.find(".message_client_loader").show()) {
        promise.then(function() {
          initLoader.find(".message_client_loader").hide();
        });
      };
    }
  }

  /*---------------* Functions that handles staff information and messages in view *---------------*/
  $("body").on("click", ".staff_container", function() {
    endOfScroll = false;
    offsetPush = 0;
    var staff_id = $(this).attr("id");
    var clName = "active_staff";
    var staff_chpd = ".staff_children_parent_child_div";
    currentStaff = staff_id;

    $(".m-area").attr("data-staffid", staff_id);
    $(".m-area").attr("id", staff_id);

    $(".staff_container " + staff_chpd).removeClass("active_staff");

    $(this).find(staff_chpd).addClass("active_staff");

    $("#staffMessagesForm .to").val(staff_id);

    $("#staffMessagesForm .from").val("client_" + contact_id);

    appendMutualMessages(staff_id, contact_id);

    // Mark messages as read when opening a conversation (standard chat UX)
    var notification = parseInt($(this).find(".staff_notification").data("notification")) || 0;
    if (notification > 0) {
      $(this).find(".staff_notification").attr("data-notification", 0).hide();
      updateUnreadNotifications(staff_id);
    }
  });

  /**
   * Function that handles customer admins and contacts conversation messages
   */
  function prependContactMessages(value) {
    var element = $(".m-area ol.chat");
    var isViewed = value.viewed == 1;
    var statusIcon = "";
    var statusClass = "";

    // Extract only time from the formatted date (like staff side does)
    var timeOnly = value.time_sent_formatted;
    if (value.time_sent_formatted) {
      var parts = value.time_sent_formatted.trim().split(' ');
      if (parts.length >= 2) {
        // Get the time part (last part or last 2 parts for AM/PM)
        timeOnly = parts.slice(-1)[0];
        // If it's HH:mm:ss format, trim to HH:mm
        if (timeOnly.match(/^\d{2}:\d{2}:\d{2}$/)) {
          timeOnly = timeOnly.substring(0, 5);
        }
      }
    }

    // Status icons for outgoing messages (from client)
    if (value.reciever_id != contact_name_id) {
      if (isViewed) {
        var seenTime = formatSeenTooltip(value.viewed_at, value.viewed_at_formatted);
        statusIcon = '<span class="msg-status read" data-toggle="tooltip" title="' + seenTime + '"><i class="fa fa-check-double"></i></span>';
      } else {
        statusIcon = '<span class="msg-status delivered" data-toggle="tooltip" title="<?= _l('chat_msg_delivered'); ?>"><i class="fa fa-check"></i></span>';
        statusClass = " has-status";
      }
    }

    var messageContainer = "";

    // Call messages get a centered system bubble (same as staff side)
    if (isCallMessageStr(value.message)) {
      messageContainer = '<li class="call-message-row" id="' + value.id + '" data-id="' + value.id + '" data-date-key="' + getDateKey(value.time_sent) + '">' + renderCallMessageHtml(value.message, timeOnly) + '</li>';
      element.prepend(messageContainer);
      return;
    }

    // Alignment: sender_id === contact (client) = outgoing = right; else staff = incoming = left
    var isFromClient = String(value.sender_id || '') === String(contact_name_id);
    var rxPills = (typeof window.renderReactionPills === 'function') ? window.renderReactionPills(value.reactions, value.id) : '';
    var hasRxCls = rxPills ? ' has-reactions' : '';
    var editedTag = (value.edited_at && String(value.edited_at).indexOf('0000-00-00') !== 0 && String(value.edited_at).trim() !== '') ? '<span class="prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>' : '';
    var dateKeyAttr = ' data-date-key="' + getDateKey(value.time_sent) + '"';
    if (!isFromClient) {
      var staffFullName = (value.sender_fullname && String(value.sender_fullname).trim() !== '') ? value.sender_fullname : 'Staff Member';
      var adminNameHtml = buildStaffLabel(staffFullName, (value.sender_id || '').replace('staff_', ''));
      var staffAvatarUrl = fetchUserAvatar((value.sender_id || '').replace('staff_', ''), value.user_image);
      messageContainer = `
                <li class="customer_admin" id="${value.id}" data-id="${value.id}"${dateKeyAttr}>
                    <div class="msg-row">
                        <img class="msg-avatar" src="${staffAvatarUrl}" onerror="this.onerror=null;this.src='${placeholderImg}';" alt="" />
                        <div class="msg${hasRxCls}">
                            ${adminNameHtml}
                            <p>${renderMessageContent(value.message)}</p>
                            ${editedTag}
                            <span class="msg-time">${timeOnly}</span>
                            ${clientMessageOptions(value.id, true, value.message)}
                            ${rxPills}
                        </div>
                    </div>
                </li>`;
    } else {
      // Outgoing (client): right
      var clientAvatarUrl = value.client_image_path || contactAvatarUrl || placeholderImg;
      messageContainer = `
                <li class="client${statusClass}" id="${value.id}" data-id="${value.id}"${dateKeyAttr}>
                    <div class="msg-row">
                        <img class="msg-avatar" src="${clientAvatarUrl}" onerror="this.onerror=null;this.src='${placeholderImg}';" alt="" />
                        <div class="msg${hasRxCls}">
                            <span class="client_name">${contact_full_name}</span>
                            <p>${renderMessageContent(value.message)}</p>
                            ${editedTag}
                            <span class="msg-time">${timeOnly}</span>
                            ${statusIcon}
                            ${clientMessageOptions(value.id, false, value.message)}
                            ${rxPills}
                        </div>
                    </div>
                </li>`;
    }

    element.prepend(messageContainer);
    clearClientChatOptionsPosition();
  }


  /*---------------* Check for messages history and append to main chat window *---------------*/
  function loadMessages(el) {
    var pos = $(el).scrollTop();
    var staff_id = $(el).attr("data-staffid");

    $(".m-area").find(".message_loader").show();

    if (pos == 0 && offsetPush >= 10) {

      var mutualMessagesPromisse = $.get(customerSettings.getMutualMessages, {
          reciever_id: staff_id,
          sender_id: contact_name_id,
          offset: offsetPush,
        })
        .done(function(messages) {

          var isHostHttps = '<?= $isHttps; ?>';

          if (!isHostHttps) {
            $("body").find(".startMic").remove();
          }

          if (Array.isArray(messages) == false) {
            endOfScroll = true;
            $(".m-area .message_client_loader").hide();
            if ($(el).hasScrollBar() && endOfScroll == true) {
              prchat_setNoMoreStaffMessages();
            }
          } else {
            offsetPush += 10;
          }

          if (Array.isArray(messages)) {
            for (var mi = 0; mi < messages.length; mi++) {
              prependContactMessages(messages[mi]);

              // Insert date separator when day changes between adjacent messages
              if (messages[mi + 1] !== undefined) {
                var curDay = getDateKey(messages[mi].time_sent);
                var nxtDay = getDateKey(messages[mi + 1].time_sent);
                if (curDay !== nxtDay) {
                  $(".m-area ol.chat").prepend('<li class="chat-date-separator"><span>' + formatDateSeparator(messages[mi].time_sent) + '</span></li>');
                }
              }
            }
          }

          if (endOfScroll == false) {
            $(el).scrollTop(200);
          }
          if (window._pendingScrollToMessageId) {
            var pendingId = window._pendingScrollToMessageId;
            window._pendingScrollToMessageId = null;
            setTimeout(function() {
              if (typeof scrollToOriginalMessage === 'function') {
                scrollToOriginalMessage(pendingId);
              }
            }, 100);
          }
        });
      activateClientsLoader(mutualMessagesPromisse);
    }
  }
  // Bind scroll handler in JS (not inline onscroll) to avoid race condition
  document.querySelector('#clientChat .m-area').addEventListener('scroll', function() {
    loadMessages(this);
  });

  // Handles client file form upload
  function uploadClientFileForm(form) {
    var fileInput = $(form).children("input[type=file]")[0];
    var files = fileInput.files;
    var sentTo = $(".m-area").attr("data-staffid");
    var token_name = $(form).children("input[name=csrf_token_name]").val();

    if (files.length === 0) {
      return false;
    }

    // Upload files one by one in sequence
    uploadClientFilesSequentially(files, sentTo, token_name, 0);
  }

  // Upload client files one by one sequentially
  function uploadClientFilesSequentially(files, sentTo, token_name, currentIndex) {
    if (currentIndex >= files.length) {
      // All files uploaded, hide loader and reset form
      $(".m-area .chat-module-loader").fadeOut();
      $("form[name='staffMessagesFileForm']").trigger("reset");
      return;
    }

    var file = files[currentIndex];
    var formData = new FormData();
    formData.append("userfile", file);
    formData.append("send_to", sentTo);
    formData.append("send_from", contact_name_id);
    formData.append("csrf_token_name", token_name);

    $.ajax({
      type: "POST",
      url: customerSettings.uploadMethod,
      data: formData,
      dataType: "json",
      processData: false,
      contentType: false,
      beforeSend: function() {
        if (file != undefined) {
          // Show loading only for first file
          if (currentIndex === 0) {
            if ($(".chat-module-loader").length == 0) {
              $(".m-area").prepend("<div class=\"chat-module-loader\"><div></div><div></div><div></div></div>");
            } else {
              $(".m-area .chat-module-loader").fadeIn();
            }
          }
          var Regex = new RegExp("\[~%:\()@]");
          if (Regex.test(file.name)) {
            alert_float("warning", '<?php echo _l('chat_permitted_files') ?>');
            if (currentIndex === 0) {
              $(".m-area .chat-module-loader").remove();
            }
            return false;
          }
        } else {
          if (currentIndex === 0) {
            $(".m-area .chat-module-loader").remove();
          }
          return false;
        }
      },
      success: function(r) {
        if (!r.error) {
          var basePath = "<?php echo base_url('modules/prchat/uploads/clients/'); ?>";
          var clientTextArea = $("#clientChat textarea.clients_textarea");
          clientTextArea.val(basePath + r.upload_data.file_name);

          // Auto-send the file message (keypress handler will run and call scrollBottom(300))
          setTimeout(function() {
            $(".send_client_message").trigger("click");
            setTimeout(function() {
              uploadClientFilesSequentially(files, sentTo, token_name, currentIndex + 1);
            }, 500);
          }, 100);
        } else {
          alert_float("danger", r.error);
          // Continue with next file even if this one failed
          setTimeout(function() {
            uploadClientFilesSequentially(files, sentTo, token_name, currentIndex + 1);
          }, 100);
        }
      },
      error: function() {
        // Continue with next file even if this one failed
        setTimeout(function() {
          uploadClientFilesSequentially(files, sentTo, token_name, currentIndex + 1);
        }, 100);
      }
    });
  }

  clientsChannel.bind("chat-ticket-event", function(event) {
    if (event.client_id == contact_id) {
      alert_float("success", "<?= _l('chat_client_new_ticket_created'); ?>");
    }
  });

  var createMessagesRequest = null;

  function appendMutualMessages(staff_id, contact_id) {
    $(".m-area ol").html("");

    var deferred = $.Deferred();
    var promise = deferred.promise();

    // Check if staff is selected
    var hasActiveStaff = $("body").find(".staff_children_parent_child_div").hasClass("active_staff");

    if (hasActiveStaff) {
      if (createMessagesRequest) {
        createMessagesRequest.abort();
      }

      createMessagesRequest = $.get(customerSettings.getMutualMessages, {
          reciever_id: staff_id,
          sender_id: "client_" + contact_id,
          offset: 0,
          limit: 20
        })
        .done(function(messages) {
          offsetPush = 10;

          offsetPush += 10;
          deferred.resolve(messages);

        })
        .always(function() {
          if ($("#no_messages").length) {
            $("#no_messages").remove();
          }
          createMessagesRequest = null;
        });
    } else {
      deferred.resolve([]);
    }

    /*---------------* After users are fetched from database -> continue with loading *---------------*/
    promise.then(function(messages) {

      if (Array.isArray(messages)) {
        for (var i = 0; i < messages.length; i++) {
          prependContactMessages(messages[i]);

          // Insert date separator when day changes between adjacent messages (prepend = reverse order)
          if (messages[i + 1] !== undefined) {
            var currentDay = getDateKey(messages[i].time_sent);
            var nextDay = getDateKey(messages[i + 1].time_sent);
            if (currentDay !== nextDay) {
              $(".m-area ol.chat").prepend('<li class="chat-date-separator"><span>' + formatDateSeparator(messages[i].time_sent) + '</span></li>');
            }
          }
        }
      }

      // Don't auto-mark as read here - only mark as read when textarea is focused
      // The focus event handler will take care of marking messages as read
      scrollBottom();
    });

    // Loader is dependant of the promise... after messages are loaded loader dissapears
    activateClientsLoader(promise);
  }

  // File upload - click to open file dialog
  $("#clientChat").on("click", ".fileUpload", function() {
    $("#clientChat").find("form[name=staffMessagesFileForm] input:first").click();
  });

  // File upload - handle file selection
  $("#clientChat").on("change", "form[name=staffMessagesFileForm] input[type=file]", function() {
    var files = this.files;
    if (files.length > 0) {
      var sentTo = $(".m-area").attr("data-staffid");
      if (!sentTo) {
        alert_float("warning", "<?= _l('chat_select_staff_first'); ?>");
        return;
      }
      var form = $(this).closest("form");
      var csrfInput = form.find("input[type=hidden]");
      var token_name = csrfInput.val();
      uploadClientFilesSequentially(files, sentTo, token_name, 0);
    }
  });


  /*---------------* Helper function scroll bottom to messages div (smooth when duration given) *---------------*/
  function scrollBottom(duration) {
    var $area = $(".m-area");
    if (!$area.length) return;
    var target = $area[0].scrollHeight;
    if (duration && duration > 0) {
      $area.animate({
        scrollTop: target
      }, Math.min(duration, 400), 'swing');
    } else {
      $area.scrollTop(target);
    }
  }

  /*---------------* Helper functions that clears and shows all notifications *---------------*/
  function clearNotifications() {
    $("body").find(".active_staff").prev().attr("data-notification", "0").hide();
  }

  function resetChatNotifications() {
    ntf.setAttribute("data-count", 0);
    ntf.style.display = "none";
    // Remove pulse animation when no unread messages
    if (chatPointer) {
      chatPointer.classList.remove("has-unread");
    }
  }

  function showChatNotification() {
    var count = Number(ntf.getAttribute("data-count")) || 0;
    ntf.style.display = "block";
    ntf.setAttribute("data-count", count + 1);
    ntf.classList.remove("notify");
    ntf.offsetWidth = ntf.offsetWidth;
    ntf.classList.add("notify");
    if (count === 0) {
      ntf.classList.add("show-count");
    }
    // Add pulse animation when there are unread messages
    if (chatPointer) {
      chatPointer.classList.add("has-unread");
    }
  }

  // Set initial notification count (for page load with existing unread messages)
  function setInitialNotificationCount(count) {
    if (count > 0) {
      ntf.style.display = "block";
      ntf.setAttribute("data-count", count);
      ntf.classList.add("show-count");
      // Add pulse animation when there are unread messages
      if (chatPointer) {
        chatPointer.classList.add("has-unread");
      }
    }
  }

  /**
   * Mark messages as seen pusher event
   */
  user_messages_events.bind("message_seen", function(messages) {
    var l_seen = "<?= _l('chat_msg_seen'); ?>";

    if (Array.isArray(messages)) {
      for (var i = 0; i < messages.length; i++) {
        var msg = messages[i];
        var senderId = msg.sender_id;
        var recieverId = msg.reciever_id;

        // If current client sent the message and staff has seen it
        if (senderId == contact_name_id) {
          // Build tooltip with seen time using unified format
          var seenTooltip = formatSeenTooltip(msg.viewed_at, msg.viewed_at_formatted);

          // Update all delivered/sending messages to read status with seen time
          $(".m-area ol.chat li.client .msg-status.delivered, .m-area ol.chat li.client .msg-status.sending").each(function() {
            $(this)
              .removeClass("delivered sending")
              .addClass("read")
              .attr("title", seenTooltip)
              .attr("data-original-title", seenTooltip)
              .html('<i class="fa fa-check-double"></i>');
            // Reinitialize tooltip (destroy for Bootstrap 3, dispose for Bootstrap 4+)
            try {
              $(this).tooltip('destroy');
            } catch (e) {}
            try {
              $(this).tooltip('dispose');
            } catch (e) {}
            $(this).tooltip();
          });

          // Also update messages that were already marked as read but without timestamp
          $(".m-area ol.chat li.client .msg-status.read").each(function() {
            var currentTitle = $(this).attr('data-original-title') || $(this).attr('title') || '';
            // If just "Seen" without datetime, update with the new timestamp
            if (currentTitle && !currentTitle.match(/\d{2}[:\-\/]\d{2}/)) {
              $(this).attr("title", seenTooltip).attr("data-original-title", seenTooltip);
              try {
                $(this).tooltip('destroy');
              } catch (e) {}
              try {
                $(this).tooltip('dispose');
              } catch (e) {}
              $(this).tooltip();
            }
          });

          // Also handle old-style indicators
          $(".m-area ol.chat li.client i.circle-unseen").remove();
          $(".m-area ol.chat li.client").removeClass("isUnseenPadding");
        }
      }
    }
  });

  /**
   * Focus event to mark messages as read when user focuses on chat textarea
   * Always mark staff messages as read when focusing on textarea (chat must be open)
   */
  $(document).on('focus', '.clients_textarea', function() {
    var staff_id = $(".m-area").attr("data-staffid");

    // Mark staff messages as read when focusing on textarea
    if (staff_id && $(".firstDiv").hasClass("chat-open")) {
      // Strip "staff_" prefix if present
      var cleanStaffId = staff_id.replace("staff_", "");

      // Mark messages as read in database
      updateUnreadNotifications(cleanStaffId);

      // Clear THIS staff's notification badge
      $("#" + staff_id + " .staff_notification").attr("data-notification", "0").hide();

      // Main floating badge was already cleared when chat was opened
      // So no need to recalculate here
    }
  });

  /*===============================================================
   * Image Modal Function - for viewing images in chat
   *===============================================================*/
  window.openImageModal = function(imageUrl, filename) {
    // Create a simple image modal - z-index must be higher than chat widget (999999)
    var modal = document.createElement('div');
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:99999999;display:flex;align-items:center;justify-content:center;cursor:pointer;';

    var content = document.createElement('div');
    content.style.cssText = 'position:relative;max-width:90%;max-height:90%;background:white;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.3);';

    var img = document.createElement('img');
    img.src = imageUrl;
    img.alt = filename || 'Image';
    img.style.cssText = 'width:100%;height:auto;max-width:100%;max-height:80vh;display:block;';

    var toolbar = document.createElement('div');
    toolbar.style.cssText = 'position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(0,0,0,0.8),rgba(0,0,0,0.4));padding:15px;display:flex;justify-content:center;gap:15px;';

    var downloadBtn = document.createElement('a');
    downloadBtn.href = imageUrl;
    downloadBtn.download = filename || 'image';
    downloadBtn.innerHTML = '⬇️ Download';
    downloadBtn.style.cssText = 'color:white;text-decoration:none;padding:8px 16px;border-radius:6px;background:#007bff;font-size:14px;font-weight:500;';

    var openBtn = document.createElement('a');
    openBtn.href = imageUrl;
    openBtn.target = '_blank';
    openBtn.innerHTML = '🔗 Open';
    openBtn.style.cssText = 'color:white;text-decoration:none;padding:8px 16px;border-radius:6px;background:#28a745;font-size:14px;font-weight:500;';

    var closeBtn = document.createElement('button');
    closeBtn.innerHTML = '✕';
    closeBtn.style.cssText = 'color:white;background:#dc3545;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:500;';

    toolbar.appendChild(downloadBtn);
    toolbar.appendChild(openBtn);
    toolbar.appendChild(closeBtn);
    content.appendChild(img);
    content.appendChild(toolbar);
    modal.appendChild(content);

    // Close modal events
    var closeModal = function() {
      if (modal && modal.parentNode) {
        document.body.removeChild(modal);
      }
      document.removeEventListener('keydown', handleKeydown);
    };

    closeBtn.onclick = closeModal;
    modal.onclick = function(e) {
      if (e.target === modal) closeModal();
    };

    // ESC key to close
    var handleKeydown = function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    };
    document.addEventListener('keydown', handleKeydown);

    // Prevent clicks on content from closing modal
    content.onclick = function(e) {
      e.stopPropagation();
    };

    document.body.appendChild(modal);
  };

  /*===============================================================
   * Drag and Drop File Upload
   *===============================================================*/
  (function initDragDrop() {
    var clientWrapper = document.querySelector('.clientwrapper');
    if (!clientWrapper) return;

    // Create overlay
    var overlay = document.createElement('div');
    overlay.className = 'drag-drop-overlay';
    overlay.innerHTML = `
            <div class="drag-drop-content">
                <i class="fa fa-cloud-upload-alt"></i>
                <p><?= _l('drop_files_here_to_upload') ?></p>
            </div>
        `;
    clientWrapper.style.position = 'relative';
    clientWrapper.appendChild(overlay);

    // Prevent defaults
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
      clientWrapper.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
      }, false);
    });

    // Handle drag enter/over
    ['dragenter', 'dragover'].forEach(function(eventName) {
      clientWrapper.addEventListener(eventName, function(e) {
        if (e.dataTransfer.types.includes('Files')) {
          clientWrapper.classList.add('dragover');
          overlay.classList.add('active');
        }
      }, false);
    });

    // Handle drag leave
    clientWrapper.addEventListener('dragleave', function(e) {
      if (!clientWrapper.contains(e.relatedTarget)) {
        clientWrapper.classList.remove('dragover');
        overlay.classList.remove('active');
      }
    }, false);

    // Handle drop
    clientWrapper.addEventListener('drop', function(e) {
      clientWrapper.classList.remove('dragover');
      overlay.classList.remove('active');

      var files = e.dataTransfer.files;
      if (files.length > 0) {
        handleDroppedFiles(files);
      }
    }, false);

    function handleDroppedFiles(files) {
      var sentTo = $(".m-area").attr("data-staffid");
      if (!sentTo) {
        alert_float("warning", "<?= _l('chat_select_staff_first'); ?>");
        return;
      }

      var form = document.querySelector('form[name="staffMessagesFileForm"]');
      if (!form) return;

      var csrfInput = form.querySelector('input[type="hidden"]');
      var token_name = csrfInput ? csrfInput.value : '';

      // Process files
      uploadClientFilesSequentially(files, sentTo, token_name, 0);
    }
  })();
</script>
<!-- Chat Utilities -->
<script src="<?= base_url('modules/prchat/assets/js/chat-utils.js?v=' . VERSIONING); ?>"></script>
<!-- Emoji Picker Scripts -->
<script src="<?= base_url('modules/prchat/assets/js/custom-emoji-system.js?v=' . VERSIONING); ?>"></script>
<script src="<?= base_url('modules/prchat/assets/js/emoji-picker.js?v=' . VERSIONING); ?>"></script>
<script>
  // Initialize emoji converter for message rendering
  window.clientEmojiConverter = new CustomEmoji();

  // Helper function to convert emoji shortcodes in messages
  window.convertEmojis = function(text) {
    if (!text) return text;
    return window.clientEmojiConverter.shortcodesToEmojis(text);
  };

  // Initialize emoji picker for client chat
  $(document).ready(function() {
    var clientPickerVisible = false;

    // Click handler for emoji trigger
    $(document).on('click', '.client-emoji-trigger', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation(); // Prevent default emoji-picker handler from interfering

      // Initialize picker if needed
      if (!window.clientEmojiPickerInstance) {
        window.clientEmojiPickerInstance = new EmojiPicker();
        window.clientEmojiPickerInstance.init();
      }

      // Get the textarea
      var textarea = $('.clients_textarea');

      if (!textarea.length) {
        return;
      }

      // Get position for picker - pass raw trigger coordinates
      // The emoji picker will calculate proper position above the trigger
      var triggerRect = this.getBoundingClientRect();
      var pickerHeight = 320; // approximate picker height
      var pickerWidth = 300; // approximate picker width

      // Calculate position to place picker directly above the emoji icon
      var top = triggerRect.top - pickerHeight - 8;
      var left = triggerRect.left - (pickerWidth / 2) + (triggerRect.width / 2);

      // If not enough space above, show below
      if (top < 10) {
        top = triggerRect.bottom + 8;
      }

      // Keep picker within viewport bounds
      if (left < 10) {
        left = 10;
      }
      if (left + pickerWidth > window.innerWidth - 10) {
        left = window.innerWidth - pickerWidth - 10;
      }

      // Toggle the picker
      if (clientPickerVisible) {
        window.clientEmojiPickerInstance.hide();
        clientPickerVisible = false;
      } else {
        // Show picker and manually position it (bypass the show method's position calculation)
        window.clientEmojiPickerInstance.targetTextarea = textarea;
        if (!window.clientEmojiPickerInstance.picker) {
          window.clientEmojiPickerInstance.init();
        }
        window.clientEmojiPickerInstance.picker.addClass('show');
        window.clientEmojiPickerInstance.picker.css({
          position: 'fixed',
          top: top + 'px',
          left: left + 'px',
          zIndex: 9999999
        });
        window.clientEmojiPickerInstance.isVisible = true;
        clientPickerVisible = true;
      }
    });

    // Close picker when clicking outside (but not on the trigger)
    $(document).on('click', function(e) {
      if (clientPickerVisible &&
        !$(e.target).closest('#emoji-picker, .client-emoji-trigger').length) {
        window.clientEmojiPickerInstance.hide();
        clientPickerVisible = false;
      }
    });

    // Close on ESC key
    $(document).on('keydown', function(e) {
      if (e.key === 'Escape' && clientPickerVisible) {
        window.clientEmojiPickerInstance.hide();
        clientPickerVisible = false;
      }
    });
  });
</script>

<!-- Include image paste handler -->
<?php require('modules/prchat/assets/module_includes/image_paste.php'); ?>

<!-- Reaction Picker -->
<script src="<?= module_dir_url('prchat', 'assets/js/reaction-picker.js?v=' . time()); ?>"></script>
<script>
  (function() {
    "use strict";
    var addReactionUrl = "<?= site_url('prchat/Prchat_ClientsController/addReaction'); ?>";

    function parseReactions(raw) {
      if (!raw) return {};
      if (typeof raw === 'object') return raw || {};
      if (typeof raw === 'string') {
        try {
          var d = JSON.parse(raw);
          if (d && typeof d === 'object') return d;
        } catch (e) {}
      }
      return {};
    }

    function renderReactionPills(reactionsRaw, messageId) {
      var obj = parseReactions(reactionsRaw);
      var emojis = Object.keys(obj);
      if (!emojis.length) return '';
      var myKey = 'client_' + contact_id;
      var pills = emojis.map(function(emoji) {
        var users = obj[emoji] || [];
        var reacted = users.some(function(v) {
          return String(v) === myKey;
        });
        var names = users.map(function(u) {
          var s = String(u);
          if (s === myKey) return 'You';
          if (s.startsWith('staff_')) return 'Staff';
          return 'User';
        });
        var tooltip = names.join(', ');
        return '<button type="button" class="reaction-pill' + (reacted ? ' reacted' : '') + '" data-emoji="' +
          emoji + '" data-msg-id="' + messageId + '" title="' + tooltip + '">' +
          '<span class="reaction-emoji">' + emoji + '</span></button>';
      });
      return '<div class="message-reactions">' + pills.join('') + '</div>';
    }
    window.renderReactionPills = renderReactionPills;

    function sendClientReaction(messageId, emoji) {
      var payload = {
        message_id: messageId,
        emoji: emoji,
        message_type: 'client'
      };
      var csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
      var $form = $('#staffMessagesForm');
      if ($form.length && csrfName) {
        $form.find('input[type="hidden"]').each(function() {
          if (this.name === csrfName && this.value) {
            payload[this.name] = this.value;
            return false;
          }
        });
      }
      $.post(addReactionUrl, payload, function(resp) {
        if (resp && resp.reactions !== undefined) {
          updateClientReactionPills(messageId, resp.reactions);
        } else if (resp && resp.success === false && typeof alert_float === 'function') {
          alert_float('warning', '<?= _l('chat_error_float'); ?>');
        }
      }, 'json').fail(function() {
        if (typeof alert_float === 'function') {
          alert_float('danger', '<?= _l('chat_error_float'); ?>');
        }
      });
    }

    function updateClientReactionPills(messageId, reactionsRaw) {
      var $li = $('#clientChat .m-area ol.chat li[data-id="' + messageId + '"]');
      if (!$li.length) return;
      $li.find('.message-reactions').remove();
      var $msg = $li.find('.msg').first();
      var html = renderReactionPills(reactionsRaw, messageId);
      if (html) {
        $msg.addClass('has-reactions');
        $msg.append(html);
      } else {
        $msg.removeClass('has-reactions');
      }
    }

    // React button click
    $("body").on("click", "#clientChat ._reactMessage", function(e) {
      e.stopPropagation();
      e.preventDefault();
      var $btn = $(this);
      var $optionsMore = $btn.closest(".optionsMore");
      var $li = $btn.closest("li.client, li.customer_admin");
      var messageId = ($optionsMore.attr("data-id") || "").trim() ||
        ($li.attr("data-id") || "").trim() ||
        ($li.attr("id") || "").trim();
      if (!messageId) {
        if (typeof alert_float === 'function') {
          alert_float('warning', '<?= _l('chat_error_float'); ?>');
        }
        return;
      }

      var btnRect = $btn[0].getBoundingClientRect();
      var anchor = {
        getBoundingClientRect: function() {
          return btnRect;
        },
        _position: 'left'
      };

      if (typeof window.ReactionPicker === 'undefined') {
        if (typeof alert_float === 'function') {
          alert_float('danger', '<?= _l('chat_error_float'); ?>');
        }
        return;
      }
      if (!window.__clientReactionPicker) {
        window.__clientReactionPicker = new window.ReactionPicker({
          onPick: function(emoji) {
            var id = window.__clientReactionTarget;
            if (id) sendClientReaction(id, emoji);
            $optionsMore.hide();
          }
        });
      }
      window.__clientReactionTarget = messageId;
      window.__clientReactionPicker.openNear(anchor);
    });

    // Click on reaction pill - only remove own reaction
    $("body").on("click", "#clientChat .reaction-pill", function(e) {
      e.stopPropagation();
      if (!$(this).hasClass('reacted')) return;
      var messageId = $(this).attr("data-msg-id");
      var emoji = $(this).attr("data-emoji");
      if (messageId && emoji) sendClientReaction(messageId, emoji);
    });

    // Pusher: message-reaction event
    if (typeof clientsChannel !== 'undefined' && clientsChannel) {
      clientsChannel.bind('message-reaction', function(data) {
        if (data && data.message_id) {
          updateClientReactionPills(data.message_id, data.reactions);
        }
      });
    }
  })();
</script>
<style>
  #clientChat .msg {
    position: relative;
  }

  #clientChat .msg.has-reactions {
    margin-bottom: 22px;
  }

  #clientChat .message-reactions {
    position: absolute;
    bottom: -14px;
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    z-index: 1;
  }

  #clientChat .customer_admin .message-reactions {
    right: 4px;
  }

  #clientChat .client .message-reactions {
    left: 4px;
  }

  #clientChat .reaction-pill {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 4px;
    cursor: default;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    transition: all .15s ease;
  }

  #clientChat .reaction-pill:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
    transform: translateY(-1px);
  }

  #clientChat .reaction-pill.reacted {
    cursor: pointer;
    background: rgba(59, 130, 246, .08);
    border-color: rgba(59, 130, 246, .35);
  }

  #clientChat .reaction-pill.reacted:hover {
    background: rgba(59, 130, 246, .14);
  }

  #clientChat .reaction-emoji {
    font-size: 14px;
    line-height: 1;
  }

  #clientChat .reaction-count {
    font-weight: 600;
    font-size: 10px;
    color: #6b7280;
    min-width: 8px;
    text-align: center;
  }

  #clientChat .reaction-pill.reacted .reaction-count {
    color: #3b82f6;
  }
</style>

<script src="<?= module_dir_url('prchat', 'assets/js/voice-recorder.js?v=' . time()); ?>"></script>
<script>
  (function() {
    if (typeof PrchatVoiceRecorder === 'undefined' || !PrchatVoiceRecorder.isSupported()) return;

    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfVal = '<?= $this->security->get_csrf_hash(); ?>';
    var voiceBtn = document.querySelector('.chat-action-voice-clientside');
    if (!voiceBtn) return;

    PrchatVoiceRecorder.init({
      triggerBtn: voiceBtn,
      container: document.querySelector('#staffMessagesForm .prchat-client-composer-row') || document.querySelector('#staffMessagesForm'),
      uploadUrl: customerSettings.uploadMethod,
      csrfName: csrfName,
      csrfValue: csrfVal,
      extraFormData: function() {
        var form = $('#staffMessagesForm');
        var fromVal = form.find('input.from').val();
        return {
          from: fromVal || (typeof contact_name_id !== 'undefined' ? contact_name_id : '')
        };
      },
      onSend: function(filename) {
        var form = $('#staffMessagesForm');
        var from = form.find('input.from').val();
        var to = form.find('input.to').val();
        if (!from || !to) return;

        // Append optimistic message to UI
        var currentTime = new Date().toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit'
        });
        var clientAvatar = contactAvatarUrl || placeholderImg;
        var displayMessage = renderMessageContent(filename);
        var messageToAppend = `
                <li class="client has-status sending">
                    <div class="msg-row">
                        <img class="msg-avatar" src="${clientAvatar}" onerror="this.onerror=null;this.src='${placeholderImg}';" alt="" />
                        <div class="msg">
                            <span class="client_name">${contact_full_name}</span>
                            <p>${displayMessage}</p>
                            <span class="msg-time">${currentTime}</span>
                            <span class="msg-status sending" data-toggle="tooltip" title="<?= _l('chat_sending'); ?>">
                                <i class="fa fa-clock"></i>
                            </span>
                            ${clientMessageOptions('', false, filename)}
                        </div>
                    </div>
                </li>`;

        $(".m-area .chat").append(messageToAppend);
        clearClientChatOptionsPosition();
        scrollBottom(300);

        // Send message with all required data
        var fd = form.serializeArray();
        fd = fd.filter(function(item) {
          return item.name !== 'client_message';
        });
        fd.push({
          name: 'client_message',
          value: filename
        }, {
          name: 'typing',
          value: 'false'
        }, {
          name: 'client_id',
          value: client_id
        }, {
          name: 'contact_full_name',
          value: contact_full_name
        }, {
          name: 'company',
          value: contact_company_name
        });

        $.post(customerSettings.clientsMessagesPath, fd, null, 'json')
          .done(function(resp) {
            var $li = $(".m-area ol.chat li.client.sending:last");
            if (resp && resp.error) {
              $li.find(".msg-status")
                .removeClass("sending")
                .addClass("failed")
                .attr("title", "<?= _l('chat_send_failed'); ?>")
                .html('<i class="fa fa-exclamation-circle"></i>');
              $li.removeClass("sending");
              return;
            }
            if (resp && resp.id) {
              assignClientChatMessageId($li, resp.id);
            }
            $li.removeClass("sending")
              .find(".msg-status")
              .removeClass("sending")
              .addClass("delivered")
              .attr("title", "<?= _l('chat_msg_delivered'); ?>")
              .html('<i class="fa fa-check"></i>');
          })
          .fail(function() {
            $(".m-area ol.chat li.sending:last")
              .find(".msg-status")
              .removeClass("sending")
              .addClass("failed")
              .attr("title", "<?= _l('chat_send_failed'); ?>")
              .html('<i class="fa fa-exclamation-circle"></i>');
            $(".m-area ol.chat li.sending:last").removeClass("sending");
          });
      },
      onError: function(msg) {
        alert(msg);
      }
    });
  })();
</script>

<?php if (get_option('chat_client_calls_enabled') == '1'): ?>
  <script src="<?= module_dir_url('prchat', 'assets/js/calls/media-manager.js?v=' . time()); ?>"></script>
  <script src="<?= module_dir_url('prchat', 'assets/js/calls/call-ui.js?v=' . time()); ?>"></script>
  <script src="<?= module_dir_url('prchat', 'assets/js/calls/call-manager.js?v=' . time()); ?>"></script>
  <script src="<?= module_dir_url('prchat', 'assets/js/calls/signaling-client.js?v=' . time()); ?>"></script>
<?php endif; ?>

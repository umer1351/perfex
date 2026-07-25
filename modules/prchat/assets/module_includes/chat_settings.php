<?php
$color = pr_get_chat_color(get_staff_user_id(), 'chat_color');

$currentChatColor = !empty($color) ? $color : '#546bf1';
?>
<!-- Additional Styling -->
<style type="text/css" media="screen">
  body #pusherChat #mainChatId #membersContent a:hover {
    background:
      <?= $currentChatColor; ?>
    ;
    color: #fff;
  }

  #pusherChat .pusherChatBox .msgTxt p.you {
    background:
      <?= $currentChatColor; ?>
    ;
  }

  #pusherChat .chatBoxWrap #slideRight .fa-angle-double-right {
    color:
      <?= $currentChatColor; ?>
    ;
  }

  #pusherChat .chatBoxWrap #slideLeft .fa-angle-double-left {
    color:
      <?= $currentChatColor; ?>
    ;
  }
</style>

<!-- Check if user has permission to delete own messages enabled -->
<?php $chat_desktop_messages_notifications = get_option('chat_desktop_messages_notifications'); ?>

<script>
  window.prchatI18n = window.prchatI18n || {};
  window.prchatI18n.chat_voice_message = <?php echo json_encode(_l('chat_voice_message')); ?>;
  window.prchatI18n.chat_new_file_uploaded = <?php echo json_encode(_l('chat_new_file_uploaded')); ?>;
  window.prchatI18n.chat_new_photo_uploaded = <?php echo json_encode(_l('chat_new_photo_uploaded')); ?>;
  /*---------------* Start of main Chat helper function  *---------------*/
  var prchatSettings = {
    "getUnread": '<?php echo json_encode($unreadMessages); ?>',
    "pusherAuthentication": '<?php echo site_url('prchat/Prchat_Controller/pusher_auth'); ?>',
    "usersList": '<?php echo site_url('prchat/Prchat_Controller/users'); ?>',
    "getMessages": '<?php echo site_url('prchat/Prchat_Controller/getMessages'); ?>',
    "getSharedFiles": '<?php echo site_url('prchat/Prchat_Controller/getSharedFiles'); ?>',
    "getGroupSharedFiles": '<?php echo site_url('prchat/Prchat_Controller/getGroupSharedFiles'); ?>',
    "getGroupMessages": '<?php echo site_url('prchat/Prchat_Controller/getGroupMessages'); ?>',
    "getGroupPreviews": '<?php echo site_url('prchat/Prchat_Controller/getGroupPreviews'); ?>',
    "getRelatedItems": '<?php echo site_url('prchat/Prchat_Controller/getRelatedItems'); ?>',
    "prchatRelationSearch": '<?php echo admin_url('prchat/Prchat_Controller/prchatRelationSearch'); ?>',
    "getGroupMessagesHistory": '<?php echo site_url('prchat/Prchat_Controller/getGroupMessagesHistory'); ?>',
    "updateUnread": '<?php echo site_url('prchat/Prchat_Controller/updateUnread'); ?>',
    "updateClientUnread": '<?php echo site_url('prchat/Prchat_ClientsController/updateClientUnreadMessages'); ?>',
    "serverPath": '<?php echo site_url('prchat/Prchat_Controller/initiateChat'); ?>',
    "addReaction": '<?php echo site_url('prchat/Prchat_Controller/addReaction'); ?>',
    "uploadMethod": '<?php echo site_url('prchat/Prchat_Controller/uploadMethod'); ?>',
    "groupUploadMethod": '<?php echo site_url('prchat/Prchat_Controller/groupUploadMethod'); ?>',
    "groupMessagePath": '<?php echo site_url('prchat/Prchat_Controller/initiateGroupChat'); ?>',
    "deleteMessage": "<?php echo site_url('prchat/Prchat_Controller/deleteMessage'); ?>",
    "editMessagePath": "<?php echo site_url('prchat/Prchat_Controller/editMessage'); ?>",
    "editClientMessagePath": "<?php echo site_url('prchat/Prchat_Controller/editClientMessage'); ?>",
    "getClientUnreadMessages": '<?php echo site_url('prchat/Prchat_ClientsController/getClientUnreadMessages'); ?>',
    "addClientReaction": '<?php echo site_url('prchat/Prchat_ClientsController/addReaction'); ?>',
    "chatGroups": '<?php echo site_url('prchat/Prchat_Controller/chatGroups'); ?>',
    "addChatGroupMembers": '<?php echo site_url('prchat/Prchat_Controller/addChatGroupMembers'); ?>',
    "addChatGroup": '<?php echo site_url('prchat/Prchat_Controller/addChatGroup'); ?>',
    "addNewChatGroupMembersModal": '<?php echo site_url('prchat/Prchat_Controller/addNewChatGroupMembersModal'); ?>',
    "getMyGroups": '<?php echo site_url('prchat/Prchat_Controller/getMyGroups'); ?>',
    "addChatMembersToGroup": '<?php echo site_url('prchat/Prchat_Controller/addChatMembersToGroup'); ?>',
    "chatMemberLeaveGroup": '<?php echo site_url('prchat/Prchat_Controller/chatMemberLeaveGroup'); ?>',
    "removeChatGroupUser": '<?php echo site_url('prchat/Prchat_Controller/removeChatGroupUser'); ?>',
    "addNewChatGroup": '<?php echo site_url('prchat/Prchat_Controller/addNewChatGroup'); ?>',
    "chatRenameGroup": '<?php echo site_url('prchat/Prchat_Controller/renameChatGroup'); ?>',
    "updateChatGroupAssociation": '<?php echo site_url('prchat/Prchat_Controller/updateChatGroupAssociation'); ?>',
    "deleteGroup": '<?php echo site_url('prchat/Prchat_Controller/deleteGroup'); ?>',
    "switchTheme": '<?php echo site_url('prchat/Prchat_Controller/switchTheme'); ?>',
    "chatAnnouncement": '<?php echo site_url('prchat/Prchat_Controller/staff_announcement'); ?>',
    "quickMentions": '<?php echo site_url('prchat/Prchat_Controller/quick_mentions'); ?>',
    "sendStaffAnnouncement": '<?php echo site_url('prchat/Prchat_Controller/staff_get_selected_members'); ?>',
    "sendClientsAnnouncement": '<?php echo site_url('prchat/Prchat_Controller/clients_announcement_message'); ?>',
    "clientsAnnouncementPost": '<?php echo site_url('prchat/Prchat_Controller/clients_announcement'); ?>',
    "deleteClientMessage": '<?php echo site_url('prchat/Prchat_Controller/deleteClientMessage'); ?>',
    "searchMessagesView": '<?php echo site_url('prchat/Prchat_Controller/searchMessages'); ?>',
    "ajaxSearchStaff": '<?php echo site_url('prchat/Prchat_Controller/ajaxSearchStaff'); ?>',
    "ajaxSearchClients": '<?php echo site_url('prchat/Prchat_Controller/ajaxSearchClients'); ?>',

    "convertToTicket": '<?php echo site_url('prchat/Prchat_Controller/convertToTicket'); ?>',
    "togglePin": '<?php echo site_url('prchat/Prchat_Controller/togglePin'); ?>',
    "toggleMute": '<?php echo site_url('prchat/Prchat_Controller/toggleMute'); ?>',
    "getPinMuteSettings": '<?php echo site_url('prchat/Prchat_Controller/getPinMuteSettings'); ?>',
    // translations
    "noMoreMessagesText": "<?php echo _l('chat_no_more_messages_to_show'); ?>",
    "chatLastSeenText": "<?php echo _l('chat_last_seen'); ?>",
    "hasComeOnlineText": "<?php echo _l('chat_user_is_online'); ?>",
    "sayHiText": "<?php echo _l('chat_say_hi'); ?>",
    "deleteChatMessage": "<?php echo _l('chat_delete_message'); ?>",
    "onlineUsers": "<?php echo _l('chat_online_users'); ?>",
    "onlineUsersMenu": "<?php echo _l('chat_online_users_menu'); ?>",
    "newMessages": "<?php echo _l('chat_new_messages'); ?>",
    "messageIsDeleted": "<?php echo _l('chat_message_deleted'); ?>",
    "invalidColor": '<?php echo _l('chat_invalid_color_alert'); ?>',
    "areYouSure": '<?php echo _l('confirm_action_prompt'); ?>',
    "typeInToConfirm": '<?php echo _l('chat_type_in_delete'); ?>',
    "conversationDeleted": '<?php echo _l('chat_history_conversation_deleted'); ?>',
    "conversationAlreadyDeleted": '<?php echo _l('chat_history_already_deleted'); ?>',
    "actionCancelled": '<?php echo _l('chat_action_was_cancelled'); ?>',
    "handleChatStatus": '<?php echo site_url('prchat/Prchat_Controller/handleChatStatus'); ?>',
    "showForwardModal": '<?php echo site_url('prchat/Prchat_Controller/showForwardUsersModal'); ?>',
    // Clients
    "clientsMessagesPath": '<?php echo site_url('prchat/Prchat_ClientsController/initClientChat'); ?>',
    "getMutualMessages": '<?php echo site_url('prchat/Prchat_ClientsController/getMutualMessages'); ?>',
    "getClientContactPreviews": '<?php echo site_url('prchat/Prchat_ClientsController/getClientContactPreviews'); ?>',
    "getStaffInfo": '<?php echo site_url('prchat/Prchat_Controller/getStaffInfo'); ?>',
    "debug": <?php if (ENVIRONMENT != 'production') { ?> true <?php } else { ?> false <?php }
    ; ?>,
    // Date/Time format settings from Perfex CRM
    <?php
    $phpDateFormat = get_current_date_format(true);
    $is24Hour = get_option('time_format') == '24';

    // Convert PHP date format to moment.js format (use strtr to avoid cascading replacements)
    $momentDateFormat = strtr($phpDateFormat, [
      'Y' => 'YYYY',
      'y' => 'YY',
      'm' => 'MM',
      'n' => 'M',
      'd' => 'DD',
      'j' => 'D',
      'F' => 'MMMM',
      'M' => 'MMM',
      'D' => 'ddd',
      'l' => 'dddd',
    ]);

    $momentTimeFormat = $is24Hour ? 'HH:mm' : 'h:mm A';
    $momentTimeFormatWithSeconds = $is24Hour ? 'HH:mm:ss' : 'h:mm:ss A';
    ?>
        "dateFormat": "<?php echo $momentDateFormat; ?>",
    "timeFormat": "<?php echo $momentTimeFormat; ?>",
    "timeFormatWithSeconds": "<?php echo $momentTimeFormatWithSeconds; ?>",
    "dateTimeFormat": "<?php echo $momentDateFormat . ' ' . $momentTimeFormat; ?>",
    "dateTimeFormatFull": "<?php echo $momentDateFormat . ' ' . $momentTimeFormatWithSeconds; ?>",
    "dateSeparatorFormat": "ddd, MMM D, YYYY"
  };

  /**
   * Format a date separator label with Today/Yesterday support.
   * Uses moment.js (available on staff side).
   */
  function formatDateSeparatorLabel(timeSent) {
    var m = moment(timeSent);
    var today = moment().startOf('day');
    var yesterday = moment().subtract(1, 'day').startOf('day');
    if (m.isSameOrAfter(today)) {
      return '<?= _l("chatbot_today") ?>';
    } else if (m.isSameOrAfter(yesterday)) {
      return '<?= _l("chatbot_yesterday") ?>';
    }
    return m.format(prchatSettings.dateSeparatorFormat);
  }

  /** Helper Functions */
  /*---------------* Live internet connection tracking *---------------*/
  function handleConnectionChange(event) {
    var conn_tracker = $(".connection_field");
    if (event.type == "offline") {
      conn_tracker.fadeIn();
      conn_tracker.children("i.fa-wifi").addClass("blink");
      conn_tracker.css("background", "#f03d25");
      conn_tracker.children("i.fa-wifi").fadeIn();
    }
    if (event.type == "online") {
      conn_tracker.css("background", "#04cc04");
      conn_tracker.children("i.fa-wifi").fadeIn();
      conn_tracker.children("i.fa-wifi").removeClass("blink");
      conn_tracker.delay(4000).fadeOut(function () {
        conn_tracker.children("i.fa-wifi").fadeOut();
      });
    }
  }

  /*---------------* UI Track chat monitor current load and resize event activity for mobile and desktop version *---------------*/

  function monitorWindowActivity() {
    $(window).resize(function () {
      if ($(window).width() > 733) {
        $("body").removeClass("hide-sidebar").addClass("show-sidebar");
      } else {
        $("body").removeClass("show-sidebar").addClass("hide-sidebar");
      }
      if ($("#frame #sidepanel #contacts li").length > 10) {
        $("#frame #sidepanel #contacts").css({
          "overflow-y": "scroll"
        });
      }
    });
  }


  /**
   * Global Array where mentioned users are saved
   */
  var mentioned_users = [];

  /**
   * Global chat statuses, translations and value mixed
   */
  var chat_user_statuses = {
    online: "<?php echo _l('chat_status_online'); ?>",
    away: "<?php echo _l('chat_status_away'); ?>",
    busy: "<?php echo _l('chat_status_busy'); ?>",
    offline: "<?php echo _l('chat_status_offline'); ?>"
  };
</script>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper" class="desktop_chat">

  <?php

  $isHttps = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? $isHttps = true : false);

  ?>

  <div id="frame">

    <?php if (isset($current_user))
      loadChatComponent('Sidepanel', ['props' => $current_user]); ?>

    <?php loadChatComponent('Content'); ?>

  </div>
  <?php loadChatComponent('ChooseAnnouncement'); ?>
  <div class="modal_container"></div>

  <?php init_tail(); ?>

  <!-- Include chat settings file -->
  <?php require('modules/prchat/assets/module_includes/chat_settings.php'); ?>

  <!-- Include chat settings file -->
  <?php require('modules/prchat/assets/module_includes/chat_statuses.php'); ?>

  <!-- Include various mutual functions file -->
  <?php require('modules/prchat/assets/module_includes/mutual_and_helper_functions.php'); ?>

  <?php
  $prchat_calls_staff_signaling = (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1');
  ?>

  <?php
  if (!chatStaffCanDelete()): ?>
    <style>
      ._removeMessage {
        display: none;
      }
    </style>
  <?php endif; ?>
  <script>
    "use strict";
    if (localStorage.prChatThemeMode) {
      $("body").addClass("chat_" + localStorage.prChatThemeMode);
    }

    var isContentActive = false;

    window.addEventListener("online", handleConnectionChange);
    window.addEventListener("offline", handleConnectionChange);

    monitorWindowActivity();

    /*---------------* Generate initial skeletons *---------------*/
    generateContactSkeleton('#contacts .contacts-skeleton', 6);
    generateContactSkeleton('#groups_container .contacts-skeleton', 4);
    generateContactSkeleton('#clients_container .contacts-skeleton', 5);
    generateMessageSkeleton('.messages .messages-skeleton', 8);
    generateMessageSkeleton('.group_messages .messages-skeleton', 8);
    generateMessageSkeleton('.client_messages .messages-skeleton', 8);

    /*---------------* Main first thing get users/staff from database *---------------*/
    var users = $.get(prchatSettings.usersList);

    var offsetPush = 0;
    var groupOffsetPush = 0;
    var endOfScroll = false;
    var groupEndOfScroll = false;
    var friendUsername = "";
    var unreadMessages = "";
    var pusherKey = "<?= get_option('pusher_app_key') ?>";
    var appCluster = "<?= get_option('pusher_cluster') ?>";
    var staffFullName = "<?= get_staff_full_name(); ?>";
    var userSessionId = "<?= get_staff_user_id(); ?>";
    window.userSessionId = userSessionId;
    var isAdmin = app.user_is_admin;
    var staffCanCreateGroups = "<?= get_option('chat_members_can_create_groups'); ?>";
    var staffCanDelete = <?= chatStaffCanDelete() ? 'true' : 'false' ?>;

    // Pin & Mute settings (loaded async, used throughout)
    var pinnedStaff = [],
      pinnedGroups = [],
      pinnedClients = [];
    var mutedStaff = [],
      mutedGroups = [],
      mutedClients = [];

    // Load pin/mute settings
    var pinMuteLoaded = false;
    $.get(prchatSettings.getPinMuteSettings, function(settings) {
      if (settings) {
        if (typeof settings === 'string') {
          try {
            settings = JSON.parse(settings);
          } catch (e) {
            return;
          }
        }
        pinnedStaff = (settings.pinned_staff || []).map(String);
        pinnedGroups = (settings.pinned_groups || []).map(String);
        pinnedClients = (settings.pinned_clients || []).map(String);
        mutedStaff = (settings.muted_staff || []).map(String);
        mutedGroups = (settings.muted_groups || []).map(String);
        mutedClients = (settings.muted_clients || []).map(String);
      }
      pinMuteLoaded = true;

      // Apply pin/mute icons to already-rendered elements
      applyPinMuteIcons();
    });

    function applyPinMuteIcons() {
      // Staff
      $("#contacts li.contact").each(function() {
        var sid = $(this).attr("id");
        $(this).find(".contact-pin-icon, .contact-mute-icon").remove();
        if (pinnedStaff.indexOf(String(sid)) !== -1) {
          $(this).prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
          $(this).attr("data-pinned", "1");
        }
        if (mutedStaff.indexOf(String(sid)) !== -1) {
          $(this).prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
        }
      });
      sortPinnedToTop("#contacts ul", pinnedStaff);

      // Groups
      $(".group_selector").each(function() {
        var gid = $(this).attr("id");
        $(this).find(".contact-pin-icon, .contact-mute-icon").remove();
        if (pinnedGroups.indexOf(String(gid)) !== -1) {
          $(this).prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
          $(this).attr("data-pinned", "1");
        }
        if (mutedGroups.indexOf(String(gid)) !== -1) {
          $(this).prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
        }
      });

      // Clients
      $(".chat_clients_list > li.contact_name").each(function() {
        var cid = $(this).attr("id");
        $(this).find(".contact-pin-icon, .contact-mute-icon").remove();
        if (pinnedClients.indexOf(String(cid)) !== -1) {
          $(this).prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
          $(this).attr("data-pinned", "1");
        }
        if (mutedClients.indexOf(String(cid)) !== -1) {
          $(this).prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
        }
      });
    }


    var checkForNewMessages = prchatSettings.getUnread;
    var chat_desktop_notifications_enabled = "<?php echo get_option('chat_desktop_messages_notifications') ?>";
    chat_desktop_notifications_enabled = (chat_desktop_notifications_enabled == "0") ? false : true;
    var user_chat_status = "<?= get_user_chat_status(); ?>";

    if (staffCanCreateGroups === "0" && !isAdmin) {
      $("#add_group_btn").remove();
    }

    function isCallMessageStr(msg) {
      if (!msg) return false;
      if (/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/.test(msg)) return true;
      var d = msg.replace(/&bull;/g, '\u2022');
      return /^(Outgoing call|Incoming call|Voice call|Video call|Missed call)\s*[•·\u2022]/i.test(d);
    }

    function getStaffNameById(id) {
      var $el = $("#contacts li.contact a#" + id + ", #contacts li.contact a#user_" + id);
      if ($el.length) return ($el.find(".meta .name").text() || '').trim();
      return '';
    }

    function getClientNameById(id) {
      var $el = $(".chat_clients_list li.contact_name[id='" + id + "']");
      if ($el.length) return ($el.find(".contact_name_main").text() || '').trim();
      return '';
    }

    function getPeerName(id) {
      return getStaffNameById(id) || getClientNameById(id) || '<?= addslashes(_l("chat_staff_fallback")) ?>';
    }

    function renderCallMessageHtml(msg, timeStr) {
      var m = msg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):(\d+):(\d+)\]$/);
      if (m) {
        var type = m[1],
          dur = parseInt(m[2]),
          callerId = m[3],
          calleeId = m[4];
        var isCaller = String(callerId) === String(userSessionId);
        var peerId = isCaller ? calleeId : callerId;
        var peerName = getPeerName(peerId);

        if (type === 'missed_voice' || type === 'missed_video') {
          var missedIcon = type === 'missed_video' ? 'fa-video-camera' : 'fa-phone';
          var missedLabel = isCaller
            ? '<?= addslashes(_l("chat_call_no_answer")) ?> ' + peerName
            : '<?= addslashes(_l("chat_call_missed_from")) ?> ' + peerName;
          return '<div class="modern-call-system-message"><div class="modern-call-bubble modern-call-missed"><i class="fa ' + missedIcon + '" style="color:#e74c3c"></i> <span style="color:#e74c3c">' + missedLabel + '</span><span class="modern-call-time">' + timeStr + '</span></div></div>';
        }
        var icon = type === 'video' ? 'fa-video-camera' : 'fa-phone';
        var mins = Math.floor(dur / 60),
          secs = dur % 60;
        var durStr = (mins > 0 ? mins + 'm ' : '') + secs + 's';
        var label = isCaller
          ? '<?= addslashes(_l("chat_call_you_called")) ?> ' + peerName
          : peerName + ' <?= addslashes(_l("chat_call_called_you")) ?>';
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

    /*---------------* Format "Seen" tooltip: always show full datetime via moment *---------------*/
    var l_seen_prefix = "<?= _l('chat_msg_seen'); ?>";

    function formatSeenTooltip(viewedAt, viewedAtFormatted) {
      if (!viewedAt && !viewedAtFormatted) return l_seen_prefix;
      var viewedMoment = viewedAt ? moment(viewedAt) : null;
      if (viewedMoment && viewedMoment.isValid()) {
        return l_seen_prefix + ' - ' + viewedMoment.format(prchatSettings.dateTimeFormat);
      }
      return l_seen_prefix + ' - ' + viewedAtFormatted;
    }
    window.formatSeenTooltip = formatSeenTooltip;

    /*---------------* Modern Message HTML Architecture *---------------*/
    function createModernMessage(messageData) {
      const isOwn = messageData.sender_id == userSessionId;
      // Extract only the time portion for display (not full date)
      let timeOnly;
      if (messageData.time_sent) {
        // If we have the raw time_sent, parse and format it
        timeOnly = moment(messageData.time_sent).format(prchatSettings.timeFormat);
      } else if (messageData.time_sent_formatted) {
        // Try to extract just the time from formatted string
        // If it contains a space and looks like "date time", get the time part
        const parts = messageData.time_sent_formatted.trim().split(' ');
        if (parts.length >= 2) {
          // Get last 1-2 parts which should be time (handles "15:25" or "3:25 PM")
          timeOnly = parts.slice(-2).join(' ').replace(/^\d{2}-\d{2}-\d{4}\s*/, '');
          if (timeOnly.match(/^\d{2}:\d{2}(:\d{2})?$/)) {
            // It's just time like "15:25:54", format it properly
            timeOnly = timeOnly.substring(0, 5); // Get HH:mm
          }
        } else {
          timeOnly = messageData.time_sent_formatted;
        }
      } else {
        timeOnly = moment().format(prchatSettings.timeFormat);
      }

      if (isCallMessageStr(messageData.message)) {
        return renderCallMessageHtml(messageData.message, timeOnly);
      }

      let statusIcon = '';
      let statusClass = '';
      if (isOwn) {
        if (messageData.sending) {
          // Sending state - clock icon with animation
          statusIcon = '<i class="fa fa-clock modern-status-sending" aria-hidden="true" data-toggle="tooltip" title="<?= _l('chat_msg_sending'); ?>"></i>';
          statusClass = ' modern-message-sending';
        } else if (messageData.viewed) {
          // Read state - double check blue with seen time tooltip
          const seenTime = formatSeenTooltip(messageData.viewed_at, messageData.viewed_at_formatted);
          statusIcon = '<i class="fa fa-check-double text-primary modern-status-read" aria-hidden="true" data-toggle="tooltip" title="' + seenTime + '"></i>';
        } else {
          // Delivered state - single check
          statusIcon = '<i class="fa fa-check modern-status-delivered" aria-hidden="true" data-toggle="tooltip" title="<?= _l('chat_msg_delivered'); ?>"></i>';
        }
      }

      const optionsMore = deleteOrForward(messageData.id || userSessionId, null, isOwn, messageData.message || '');

      // For new messages without user_image, use current user's sidebar profile image
      let avatarSrc;
      if (!messageData.user_image && messageData.sender_id == userSessionId) {
        // Use current user's profile image from sidebar
        avatarSrc = $("#sidepanel #profile #profile-img").prop("currentSrc") || site_url + "/assets/images/user-placeholder.jpg";
      } else {
        // Use fetchUserAvatar for loaded messages from database
        avatarSrc = fetchUserAvatar(messageData.sender_id || userSessionId, messageData.user_image);
      }

      const processedMessage = PrchatSafeRenderer.sanitize(messageData.message);
      const reactionsHtml = window.prchatRenderReactionPills ? window.prchatRenderReactionPills(messageData.reactions, messageData.id, 'staff') : '';
      const hasRx = reactionsHtml ? ' has-reactions' : '';
      const editedAt = messageData.edited_at;
      const showEdited = editedAt && String(editedAt).indexOf('0000-00-00') !== 0 && String(editedAt).trim() !== '';
      const editedTagHtml = showEdited ? '<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>' : '';

      if (isOwn) {
        return `
                    <div class="modern-message-item modern-message-sent${statusClass}" data-message-id="${messageData.id || userSessionId}">
                        <div class="modern-message-content">
                            <div class="modern-message-bubble modern-sent-bubble${hasRx}">
                                <div class="modern-message-text">${processedMessage}</div>
                                <div class="modern-message-meta">
                                    ${editedTagHtml}
                                    <span class="modern-message-time">${timeOnly}</span>
                                    ${statusIcon}
                                </div>
                                ${reactionsHtml}
                            </div>
                            ${optionsMore}
                        </div>
                        <div class="modern-message-avatar">
                            <img src="${avatarSrc}" alt="<?= _l('chat_you_text'); ?>" class="modern-avatar-img">
                        </div>
                    </div>
                `;
      } else {
        return `
                    <div class="modern-message-item modern-message-received" data-message-id="${messageData.id || userSessionId}">
                        <div class="modern-message-avatar">
                            <img src="${avatarSrc}" alt="Contact" class="modern-avatar-img">
                        </div>
                        <div class="modern-message-content">
                            <div class="modern-message-bubble modern-received-bubble${hasRx}">
                                <div class="modern-message-text">${processedMessage}</div>
                                <div class="modern-message-meta">
                                    ${editedTagHtml}
                                    <span class="modern-message-time">${timeOnly}</span>
                                </div>
                                ${reactionsHtml}
                            </div>
                            ${optionsMore}
                        </div>
                    </div>
                `;
      }
    }

    window.createModernMessage = createModernMessage;

    // Add flag to prevent duplicate uploads
    var dragDropUploadInProgress = false;

    $("#frame").on("click", ".fileUpload", function() {
      $("#frame").find("form[name=\"fileForm\"] input:first").click();
    });

    $("#frame").on("click", ".clientFileUpload", function() {
      $("#frame").find("form[name=\"clientFileForm\"] input:first").click();
    });

    $("#frame").on("click", ".groupFileUpload", function() {
      $("#frame").find("form[name=\"groupFileForm\"] input:first").click();
    });

    $("#frame").on("change", "input[type=file]", function() {
      // Prevent duplicate uploads when drag & drop is in progress
      if (dragDropUploadInProgress) {
        dragDropUploadInProgress = false;
        return false;
      }
      $(this).parent("form").submit();
    });

    // Expose flag for drag & drop system
    window.setDragDropUploadInProgress = function(value) {
      dragDropUploadInProgress = value;
    };

    /*---------------* Handles input form sending *---------------*/

    // Handles file form upload  for staff to staff
    function uploadFileForm(form) {
      var fileInput = $(form).children("input[type=file]")[0];
      var files = fileInput.files;
      var sentTo = $("li.contact.active").attr("id");
      var token_name = $(form).children("input[name=csrf_token_name]").val();
      var formId = $(form).attr("id");

      if (files.length === 0) {
        return false;
      }

      // Upload files one by one in sequence
      uploadFilesSequentially(files, sentTo, token_name, 0);
    }

    // Upload files one by one sequentially
    function uploadFilesSequentially(files, sentTo, token_name, currentIndex) {
      if (currentIndex >= files.length) {
        // All files uploaded, hide loader and update shared files
        $(".content .chat-module-loader").fadeOut();
        getSharedFiles(userSessionId, sentTo);
        $("form[name='fileForm']").trigger("reset");
        return;
      }

      var file = files[currentIndex];
      var formData = new FormData();
      formData.append("userfile", file);
      formData.append("send_to", sentTo);
      formData.append("send_from", userSessionId);
      formData.append("csrf_token_name", token_name);

      $.ajax({
        type: "POST",
        url: prchatSettings.uploadMethod,
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        beforeSend: function() {
          if (file != undefined) {
            // Show loading only for first file
            if (currentIndex === 0) {
              if ($(".chat-module-loader").length == 0) {
                $(".content").prepend("<div class=\"chat-module-loader\"><div></div><div></div><div></div></div>");
              } else {
                $(".content .chat-module-loader").fadeIn();
              }
            }
            var Regex = new RegExp("\[~%:\()@]");
            if (Regex.test(file.name)) {
              alert_float("warning", "<?php echo _l('chat_permitted_files') ?>");
              if (currentIndex === 0) {
                $(".content .chat-module-loader").remove();
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
            var uploadSend = $.Event("keypress", {
              which: 13
            });

            var basePath = "<?php echo base_url('modules/prchat/uploads/'); ?>";
            $("#frame textarea.chatbox").val(basePath + r.upload_data.file_name);
            $("#frame textarea.chatbox").trigger(uploadSend);

            // Wait a bit then upload next file
            setTimeout(function() {
              uploadFilesSequentially(files, sentTo, token_name, currentIndex + 1);
            }, 500);
          } else {
            alert_float("danger", r.error);
            // Continue with next file even if this one failed
            setTimeout(function() {
              uploadFilesSequentially(files, sentTo, token_name, currentIndex + 1);
            }, 100);
          }
        },
        error: function() {
          // Continue with next file even if this one failed
          setTimeout(function() {
            uploadFilesSequentially(files, sentTo, token_name, currentIndex + 1);
          }, 100);
        }
      });
    }




    /*---------------* Check for messages history and append to main chat window *---------------*/
    function loadMessages(el) {
      var pos = $(el).scrollTop();
      var id = $(el).attr("id");
      var to = $("#contacts ul li.contact").children("a.active_chat").attr("id");
      var from = userSessionId;

      if (pos == 0 && offsetPush >= 10) {

        $.get(prchatSettings.getMessages, {
            from: from,
            to: to,
            offset: offsetPush,
          })
          .done(function(message) {
            message = JSON.parse(message);
            if (Array.isArray(message) == false) {
              endOfScroll = true;
              if ($(el).hasScrollBar() && endOfScroll == true) {
                prchat_setNoMoreMessages();
              }
            } else {
              offsetPush += 10;
            }
            $(message).each(function(i, value) {

              if (message[i].time_sent !== undefined && message[i].time_sent !== "undefined") {
                var previous_time = moment(message[i].time_sent).format("YYYY-MM-DD");

                if (message[i + 1] !== undefined) {
                  var current_time = moment(message[i + 1].time_sent).format("YYYY-MM-DD");
                }
              }

              var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(value.message);

              var modernMessage = createModernMessage({
                id: value.id,
                message: displayForRow,
                sender_id: value.sender_id,
                time_sent: value.time_sent,
                time_sent_formatted: value.time_sent_formatted,
                viewed: value.viewed == 1,
                viewed_at: value.viewed_at || '',
                viewed_at_formatted: value.viewed_at_formatted || '',
                user_image: value.user_image,
                reactions: value.reactions || null
              });
              $(".modern-messages-container").prepend(modernMessage);

              if (message[i + 1] !== undefined) {
                if (previous_time !== current_time) {
                  var dateHtml = '<div class="modern-date-separator"><span>' + formatDateSeparatorLabel(value.time_sent) + '</span></div>';
                  $(".modern-messages-container").prepend(dateHtml);
                }
              }
            });
            if (endOfScroll == false) {
              $(el).scrollTop(200);
            }
            $('.modern-messages-container .reaction-pill[data-toggle="tooltip"]').tooltip();
          });
      }
    }

    /*---------------* Put prchatSettings.debug for debug mode for Pusher *---------------*/
    prchatSettings.debug = false;
    if (prchatSettings.debug) {
      try {
        Pusher.log = function(message) {};
      } catch (e) {
        if (e instanceof ReferenceError) {
          alert_float("danger", e);
        }
      }
    }


    /*---------------* Init pusher library, and register *---------------*/
    var pusher = new Pusher(pusherKey, {
      authEndpoint: prchatSettings.pusherAuthentication,
      authTransport: "jsonp",
      "cluster": appCluster,
      disableStats: true,
      auth: {
        headers: {
          "<?php echo $this->security->get_csrf_token_name(); ?>": "<?php echo $this->security->get_csrf_hash(); ?>"
        }
      }
    });

    // Suppress pong timeout errors (4201) - Pusher handles reconnection automatically
    pusher.connection.bind('error', function(err) {
      if (err?.error?.data?.code !== 4201) {
        console.error('Pusher error:', err);
      }
    });

    /* Mobile vs desktop: slide panels fully off-screen */
    window.prchatIsChatMobileViewport = function() {
      return window.matchMedia && window.matchMedia('only screen and (max-width: 735px)').matches;
    };
    window.prchatSlidePanelClosedCss = function() {
      if (window.prchatIsChatMobileViewport()) {
        return {
          right: '-100%',
          width: '100%',
          maxWidth: '100vw',
          boxSizing: 'border-box'
        };
      }
      return {
        right: '-360px',
        width: '360px'
      };
    };

    /** Clear slide-panel inline styles mistakenly applied to the staff composer (see clients tab hide). */
    window.prchatResetStaffComposerFormCss = function() {
      var $f = $('#frame form[name="pusherMessagesForm"]');
      if (!$f.length) return;
      $f.css({
        width: '',
        right: '',
        left: '',
        maxWidth: '',
        minWidth: '',
        boxSizing: '',
        position: '',
        top: '',
        float: ''
      });
    };

    /*---------------* Video call: only hide button if browser lacks getUserMedia entirely *---------------*/
    <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        $('.chat-action-video-call').hide();
      }
    <?php endif; ?>

    /*---------------* Tab unread badge helpers *---------------*/
    function incrementTabBadge(tab) {
      var tabEl = $("#frame #sidepanel ." + tab + " a");
      var badge = tabEl.find(".tab-unread-badge");
      if (badge.length === 0) {
        tabEl.append('<span class="tab-unread-badge">1</span>');
      } else {
        var count = parseInt(badge.text()) || 0;
        badge.text(count + 1);
      }
    }

    function clearTabBadge(tab) {
      $("#frame #sidepanel ." + tab + " a .tab-unread-badge").remove();
    }

    /*---------------* Pusher Trigger accessing channel *---------------*/
    var presenceChannel = pusher.subscribe("presence-mychanel");
    var chat_status = pusher.subscribe("user_changed_chat_status");
    var user_messages_events = pusher.subscribe("user_messages");
    var groupChannels = pusher.subscribe("group-chat");

    // Subscribe to individual group channels immediately on page load
    // so group-notify-event and group-send-event are received before Groups tab is clicked
    <?php
    $CI = &get_instance();
    $CI->db->select('g.group_name');
    $CI->db->from(db_prefix() . 'chatgroups g');
    $CI->db->join(db_prefix() . 'chatgroupmembers m', 'm.group_id = g.id');
    $CI->db->where('m.member_id', get_staff_user_id());
    $_prchat_user_group_channels = array_column($CI->db->get()->result_array(), 'group_name');
    ?>
    var _myGroupChannels = <?= json_encode($_prchat_user_group_channels) ?>;
    _myGroupChannels.forEach(function(ch) {
      pusher.subscribe(ch);
    });

    // Listen for status changes
    chat_status.bind("status-changed-event", function(data) {
      if (data && data.user_id && data.status) {
        // Skip if this is the current user (they're not in their own contact list)
        if (data.user_id == userSessionId) {
          return;
        }

        // Update the staff contact status in real-time
        updateStaffContactStatus(data.user_id, (data.status !== 'offline'), data.status);

      }
    });

    // Calls endpoints (absolute URLs)
    window.PRCHAT_CALLS = {
      start: '<?= admin_url("prchat/Calls_Controller/startCall") ?>',
      answer: '<?= admin_url("prchat/Calls_Controller/answerCall") ?>',
      ice: '<?= admin_url("prchat/Calls_Controller/iceCandidate") ?>',
      hangup: '<?= admin_url("prchat/Calls_Controller/hangup") ?>',
      decline: '<?= admin_url("prchat/Calls_Controller/decline") ?>',
      mute_status: '<?= admin_url("prchat/Calls_Controller/muteStatus") ?>'
    };
    window.PRCHAT_GET_CALL_TOKEN_URL = '<?= admin_url("prchat/Prchat_Controller/get_call_token") ?>';
    // CSRF for POSTs
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

    // Calls channel for this staff (will be initialized when Pusher connects)
    <?php if ($prchat_calls_staff_signaling): ?>
      var callsChannel;
      window.prchatUiCallTrace = function() {
        if (typeof window.prchatCallTrace === 'function') {
          window.prchatCallTrace.apply(null, Array.prototype.slice.call(arguments));
        }
      };
    <?php endif; ?>

    pusher.config.unavailable_timeout = 5000;

    // Flag to track if call channel has been initialized
    <?php if ($prchat_calls_staff_signaling): ?>
      var callChannelInitialized = false;
    <?php endif; ?>

    pusher.connection.bind("state_change", function(states) {
      var prevState = states.previous;
      var currState = states.current;
      var conn_tracker = $(".connection_field");
      if (currState == "unavailable") {
        conn_tracker.fadeIn();
        conn_tracker.children("i.fa-wifi").fadeIn();
        conn_tracker.css("background", "#f03d25");
      } else if (currState == "connected") {
        if (conn_tracker.is(":visible")) {
          conn_tracker.children("i.fa-wifi").removeClass("blink");
          conn_tracker.css("background", "#04cc04", function() {
            conn_tracker.fadeOut(2000);
          });
        }

        <?php if ($prchat_calls_staff_signaling): ?>
          if (!callChannelInitialized) {
            var channelName = "<?= CHAT_CALLS_STAFF_CHANNEL_PREFIX ?>" + userSessionId;
            var existingChannel = pusher.channel(channelName);
            prchatUiCallTrace('Pusher connected: wiring calls channel', channelName, 'hadExisting', !!existingChannel, 'existingSubscribed', existingChannel ? existingChannel.subscribed : null);

            if (existingChannel) {
              existingChannel.unbind_all();

              callsChannel = existingChannel;
              callChannelInitialized = true;

              setTimeout(function() {
                prchatUiCallTrace('callsChannelReady trigger (existing channel, delayed 50ms)');
                $(document).trigger('callsChannelReady');
              }, 50);

              if (!existingChannel.subscribed) {
                callsChannel.bind('pusher:subscription_succeeded', function() {
                  prchatUiCallTrace('calls-staff subscription_succeeded (was not subscribed)');
                });
              }
            } else {
              callsChannel = pusher.subscribe(channelName);
              callChannelInitialized = true;
              prchatUiCallTrace('subscribe() issued', channelName);

              callsChannel.bind('pusher:subscription_succeeded', function() {
                prchatUiCallTrace('calls-staff subscription_succeeded (new subscribe)');
                $(document).trigger('callsChannelReady');
              });

              callsChannel.bind('pusher:subscription_error', function(status) {
                console.error('[prchat-call] calls-staff subscription_error', channelName, status);
              });
            }
          }
        <?php endif; ?>

        // When Pusher connects, clean up any active calls
        <?php if ($prchat_calls_staff_signaling): ?>
          try {
            const savedCallState = localStorage.getItem('prchat_call_state');
            if (savedCallState) {
              const callState = JSON.parse(savedCallState);

              // Check for all possible call states
              if ((callState.state === 'inCall' ||
                  callState.state === 'outgoing' ||
                  callState.state === 'ringingOutgoing' ||
                  callState.state === 'ringingIncoming') && callState.peer) {

                // Send hangup via backend API
                const hangupUrl = '<?= admin_url("prchat/Calls_Controller/hangup") ?>';
                const formData = new URLSearchParams({
                  from_id: userSessionId,
                  to_id: callState.peer,
                  <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                });

                fetch(hangupUrl, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                  },
                  body: formData,
                  keepalive: true
                }).catch(e => {
                  console.error('[Call Cleanup] Failed to send hangup signal:', e);
                });
              }

              // Clear all call state
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              window.__CHAT_CALL_START_TIME = null;
              window.__CHAT_CALL_IS_CALLER = false;
              localStorage.removeItem('prchat_call_state');
            }
          } catch (e) {
            console.error('[Call Cleanup] Error during cleanup:', e);
            localStorage.removeItem('prchat_call_state');
          }
        <?php endif; ?>
      }
    });

    // Suppress pong timeout errors (4201) - Pusher handles reconnection automatically
    pusher.connection.bind("error", function(err) {
      if (err?.error?.data?.code !== 4201) {
        console.error('Pusher error:', err);
      }
    });

    /*---------------* Pusher Trigger subscription succeeded *---------------*/
    presenceChannel.bind("pusher:subscription_succeeded", function(members) {
      chatMemberUpdate();
      updateOnlineCounter();
      var redirect_staff_id = localStorage.staff_to_redirect;
      users.then(function() {
        if (localStorage.touchClientsTab) {
          $("li.crm_clients a").click();
          localStorage.touchClientsTab = "";
        }
        if (redirect_staff_id != "") {
          $(".chat_nav li.staff a").trigger("click");
          $("#contacts a#" + redirect_staff_id).trigger("click");
          localStorage.staff_to_redirect = "";
        } else {
          if (!window.matchMedia("only screen and (max-width: 735px)").matches) {
            $("#frame #sidepanel ul.nav.nav-tabs li.staff.active a").click();
          }
        }
      });
    });

    <?php if ($prchat_calls_staff_signaling): ?>
      // Global call state
      // idle | ringingIncoming | ringingOutgoing | inCall
      window.__CHAT_CALL_STATE = window.__CHAT_CALL_STATE || 'idle';
      window.__CHAT_CALL_PEER = window.__CHAT_CALL_PEER || null;
      window.__CHAT_CALL_SUPPRESS_UNTIL = window.__CHAT_CALL_SUPPRESS_UNTIL || 0;
      window.__CHAT_CALL_START_TIME = null;
      window.__CHAT_CALL_IS_CALLER = false;

      // Function to save call state to localStorage
      function saveCallState() {
        const callState = {
          state: window.__CHAT_CALL_STATE,
          peer: window.__CHAT_CALL_PEER,
          startTime: window.__CHAT_CALL_START_TIME,
          isCaller: window.__CHAT_CALL_IS_CALLER
        };
        localStorage.setItem('prchat_call_state', JSON.stringify(callState));
      }

      // Send hangup signal before page unloads (reload/close)
      window.addEventListener('beforeunload', function(e) {
        const savedCallState = localStorage.getItem('prchat_call_state');
        if (savedCallState) {
          try {
            const callState = JSON.parse(savedCallState);

            if ((callState.state === 'inCall' ||
                callState.state === 'outgoing' ||
                callState.state === 'ringingOutgoing' ||
                callState.state === 'ringingIncoming') && callState.peer) {

              // Send hangup synchronously using sendBeacon
              const hangupUrl = '<?= admin_url("prchat/Calls_Controller/hangup") ?>';
              const formData = new URLSearchParams({
                from_id: userSessionId,
                to_id: callState.peer,
                <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
              });

              // Use sendBeacon for reliable delivery during page unload
              if (navigator.sendBeacon) {
                navigator.sendBeacon(hangupUrl, formData);
              } else {
                // Fallback: async fetch with keepalive for browsers without sendBeacon
                fetch(hangupUrl, {
                  method: 'POST',
                  body: formData,
                  keepalive: true
                }).catch(function() {});
              }

              localStorage.removeItem('prchat_call_state');
            }
          } catch (e) {
            console.error('[Before Unload] Error sending hangup:', e);
          }
        }
      });

      // Function to send call summary message
      function sendCallSummaryMessage(peerId, durationSeconds, callWasVideo) {
        if (!peerId) return;

        var dur = Math.max(1, parseInt(durationSeconds, 10) || 0);
        var isVideoCall = (arguments.length >= 3) ? !!callWasVideo : !!window.__CHAT_CALL_WAS_VIDEO;
        window.__CHAT_CALL_WAS_VIDEO = false;
        var callType = isVideoCall ? 'video' : 'voice';
        var message = '[CALL:' + callType + ':' + dur + ':' + userSessionId + ':' + peerId + ']';

        var isClientRecipient = window.__CHAT_CALL_RECIPIENT_TYPE === 'client';
        var endpoint = isClientRecipient ? prchatSettings.clientsMessagesPath : prchatSettings.serverPath;

        var postData;

        if (isClientRecipient) {
          postData = {
            to: 'client_' + peerId,
            from: 'staff_' + userSessionId,
            client_message: message,
            client_id: peerId,
            typing: 'false'
          };

          var selectedContact = $(".chat_clients_list > li.contact_name.selected");
          if (selectedContact.length) {
            postData.contact_full_name = selectedContact.find('.contact_name_main').text();
            postData.company = selectedContact.attr('data-company') || '';
          }
        } else {
          postData = {
            reciever: peerId,
            msg: message,
            from: userSessionId,
            to: peerId,
            typing: 'false'
          };
        }

        postData['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';

        $.post(endpoint, postData)
          .done(function(response) {
            // Manually append the message to the UI immediately
            if (isClientRecipient) {
              var messageData = {
                id: response.id || Date.now(),
                sender_id: "staff_" + userSessionId,
                message: message,
                time_sent_formatted: moment().format(prchatSettings.timeFormat),
                viewed: false,
                user_image: null
              };

              var $container = $(".client_messages .modern-messages-container");
              if ($container.length > 0 && typeof window.createModernClientMessage === 'function') {
                var messageHtml = window.createModernClientMessage(messageData);
                $container.append(messageHtml);
                scrollToBottom();
              }
            } else {
              // Staff message
              var messageData = {
                id: response.id || Date.now(),
                sender_id: userSessionId,
                reciever_id: peerId,
                message: message,
                time_sent_formatted: moment().format(prchatSettings.timeFormat),
                viewed: "0",
                user_image: null
              };

              var $container = $(".messages .modern-messages-container");
              if ($container.length > 0 && typeof window.createModernMessage === 'function') {
                $container.append(window.createModernMessage(messageData));
                scrollToBottom();
              }
            }
          })
          .fail(function(xhr, status, error) {
            console.error('[CALL SUMMARY] Failed:', {
              status,
              error,
              responseText: xhr.responseText
            });
          });
      }

      function sendMissedCallMessage(peerId) {
        if (!peerId) return;

        var callType = window.__CHAT_CALL_WAS_VIDEO ? 'missed_video' : 'missed_voice';
        window.__CHAT_CALL_WAS_VIDEO = false;
        var message = '[CALL:' + callType + ':0:' + userSessionId + ':' + peerId + ']';

        var isClientRecipient = window.__CHAT_CALL_RECIPIENT_TYPE === 'client';
        var endpoint = isClientRecipient ? prchatSettings.clientsMessagesPath : prchatSettings.serverPath;

        var postData;
        if (isClientRecipient) {
          postData = {
            to: 'client_' + peerId,
            from: 'staff_' + userSessionId,
            client_message: message,
            client_id: peerId,
            typing: 'false'
          };
        } else {
          postData = {
            reciever: peerId,
            msg: message,
            from: userSessionId,
            to: peerId,
            typing: 'false'
          };
        }

        postData['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
        $.post(endpoint, postData)
          .done(function(response) {
            if (isClientRecipient) {
              var messageData = {
                id: response && response.id ? response.id : Date.now(),
                sender_id: "staff_" + userSessionId,
                message: message,
                time_sent_formatted: moment().format(prchatSettings.timeFormat),
                viewed: false,
                user_image: null
              };
              var $container = $(".client_messages .modern-messages-container");
              if ($container.length > 0 && typeof window.createModernClientMessage === 'function') {
                $container.append(window.createModernClientMessage(messageData));
                scrollToBottom();
              }
            } else {
              var messageData = {
                id: response && response.id ? response.id : Date.now(),
                sender_id: userSessionId,
                reciever_id: peerId,
                message: message,
                time_sent_formatted: moment().format(prchatSettings.timeFormat),
                viewed: "0",
                user_image: null
              };
              var $container = $(".messages .modern-messages-container");
              if ($container.length > 0 && typeof window.createModernMessage === 'function') {
                $container.append(window.createModernMessage(messageData));
                scrollToBottom();
              }
            }
          })
          .fail(function(xhr, status, error) {
            console.error('[MISSED CALL] Failed:', error);
          });
      }

      function endCallWithSummary() {
        var peerId = window.__CHAT_CALL_PEER;
        var wasCaller = window.__CHAT_CALL_IS_CALLER;
        var callWasVideo = !!window.__CHAT_CALL_WAS_VIDEO;
        var durationSeconds = 0;

        if (window.__CHAT_CALL_START_TIME) {
          durationSeconds = Math.floor((Date.now() - window.__CHAT_CALL_START_TIME) / 1000);
        }

        window.__CHAT_CALL_STATE = 'idle';
        window.__CHAT_CALL_PEER = null;
        window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
        window.__CHAT_CALL_START_TIME = null;
        window.__CHAT_CALL_IS_CALLER = false;
        window.__CHAT_CALL_WAS_VIDEO = false;

        if (peerId && wasCaller) {
          var dur = Math.max(1, durationSeconds);
          var lockKey = 'prchat_call_summary_lock_' + String(peerId);
          var existing = localStorage.getItem(lockKey);
          if (existing && (Date.now() - parseInt(existing, 10)) < 5000) {
            return;
          }
          localStorage.setItem(lockKey, String(Date.now()));
          setTimeout(function() {
            localStorage.removeItem(lockKey);
          }, 5000);
          sendCallSummaryMessage(peerId, dur, callWasVideo);
        }
      }

      // Function to initialize call event bindings
      function initializeCallEventBindings() {

        if (!callsChannel) {
          console.error('[Full Chat] ERROR: callsChannel is not defined!');
          return;
        }

        prchatUiCallTrace('initializeCallEventBindings', callsChannel.name);

        // Verify this is the correct channel object Pusher knows about
        const pusherChannel = pusher.channel(callsChannel.name);
        if (pusherChannel !== callsChannel) {
          console.error('[Full Chat] CRITICAL: Channel object mismatch!');
          callsChannel = pusherChannel;
        }

        callsChannel.bind('call-offer', function(payload) {
          try {
            prchatUiCallTrace('Pusher call-offer received', {
              from_id: payload && payload.from_id,
              is_video: payload && payload.is_video,
              sdpType: payload && payload.sdp ? typeof payload.sdp : null,
              sdpChars: payload && typeof payload.sdp === 'string' ? payload.sdp.length : null,
              state: window.__CHAT_CALL_STATE
            });
            var nowTs = Date.now();
            if (nowTs < (window.__CHAT_CALL_SUPPRESS_UNTIL || 0)) {
              prchatUiCallTrace('call-offer skipped: suppress window');
              return;
            }
            if (window.__CHAT_CALL_STATE !== 'idle') {
              prchatUiCallTrace('call-offer skipped: state=', window.__CHAT_CALL_STATE);
              return;
            }
            var offer = JSON.parse(payload.sdp);
            var isVideoCall = !!(payload.is_video === true || payload.is_video === 1 || payload.is_video === '1');
            prchatUiCallTrace('call-offer parsed OK', 'isVideoCall', isVideoCall);

            if (!window.__chatCallManager) {
              window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
            }

            // Set up remote track handler based on call type
            if (isVideoCall) {
              // Local track handler for incoming video call
              window.__chatCallManager.onLocalTrack = function(stream) {
                var pip = document.getElementById('chat-call-pip');
                var localVideo = document.getElementById('chat-call-local-video');
                if (!localVideo) {
                  localVideo = document.createElement('video');
                  localVideo.id = 'chat-call-local-video';
                  localVideo.autoplay = true;
                  localVideo.playsinline = true;
                  localVideo.muted = true;
                  (pip || document.body).appendChild(localVideo);
                }
                localVideo.srcObject = stream;
                localVideo.style.display = 'block';
                localVideo.play().catch(function(err) {
                  console.error('[VIDEO CALL] Error playing local video:', err);
                });
              };

              window.__chatCallManager.onRemoteTrack = function(stream) {
                var pip = document.getElementById('chat-call-pip');
                var video = document.getElementById('chat-call-remote-video');
                if (!video) {
                  video = document.createElement('video');
                  video.id = 'chat-call-remote-video';
                  video.autoplay = false;
                  video.playsinline = true;
                  video.muted = false;
                  if (pip) pip.insertBefore(video, pip.firstChild);
                  else document.body.appendChild(video);
                }
                video.onloadedmetadata = null;
                if (video.srcObject) {
                  video.pause();
                  video.srcObject = null;
                }
                video.srcObject = stream;
                video.style.display = 'block';
                video.onloadedmetadata = function() {
                  video.play().catch(function(err) {
                    console.error('[VIDEO CALL] Error playing remote video:', err);
                  });
                };
              };
            } else {
              // Ensure remote audio hookup for callee
              window.__chatCallManager.onRemoteTrack = function(stream) {
                var audio = document.getElementById('chat-call-remote');
                if (!audio) {
                  audio = document.createElement('audio');
                  audio.id = 'chat-call-remote';
                  audio.autoplay = true;
                  document.body.appendChild(audio);
                }
                audio.srcObject = stream;
              };
            }
            if (window.ChatCallUI) {
              window.__CHAT_CALL_STATE = 'ringingIncoming';
              window.__CHAT_CALL_PEER = payload.from_id;
              saveCallState(); // Save state so beforeunload can send hangup
              var avatarUrlIn = (payload.from_avatar && payload.from_avatar.length) ? payload.from_avatar : ($("#contacts a#user_" + payload.from_id + " img").attr('src') || null);
              ChatCallUI.showIncomingCall((payload.from_name || ''), function(options) {
                // Accept (with or without mic)
                if (window.ChatCallUI.unlockAudio) {
                  window.ChatCallUI.unlockAudio();
                }
                if (window.__CHAT_CALL_STATE !== 'ringingIncoming') {
                  return;
                }
                window.__CHAT_CALL_STATE = 'inCall';
                window.__CHAT_CALL_PEER = payload.from_id;
                window.__CHAT_CALL_START_TIME = Date.now(); // Track call start
                window.__CHAT_CALL_IS_CALLER = false; // We received the call
                saveCallState(); // Save state so beforeunload can send hangup
                // Track call type for summary messages
                window.__CHAT_CALL_WAS_VIDEO = isVideoCall;

                var answerOpts = {};
                for (var k in (options || {})) {
                  answerOpts[k] = options[k];
                }
                answerOpts.isVideo = isVideoCall;
                answerOpts.recipientType = payload.caller_type || 'staff';
                answerOpts.callerType = 'staff';
                window.__chatCallManager.answer(userSessionId, payload.from_id, offer, answerOpts);

                ChatCallUI.showInCall((payload.from_name || ''), function() {
                  window.__chatCallManager.hangup();
                  endCallWithSummary();
                  if (isVideoCall) {
                    var pipEl = document.getElementById('chat-call-pip');
                    if (pipEl) pipEl.parentNode.removeChild(pipEl);
                  }
                }, {
                  avatarUrl: avatarUrlIn,
                  isVideo: isVideoCall
                });
              }, function() {
                // Decline
                // notify caller explicitly
                try {
                  var urlDecl = (window.PRCHAT_CALLS && PRCHAT_CALLS.decline) ? PRCHAT_CALLS.decline : '<?= admin_url("prchat/Calls_Controller/decline") ?>';
                  var bodyDecl = 'from_id=' + encodeURIComponent(userSessionId) + '&to_id=' + encodeURIComponent(payload.from_id) +
                    '&recipient_type=' + encodeURIComponent(payload.caller_type || 'staff');
                  if (window.PRCHAT_CSRF) {
                    bodyDecl += '&' + encodeURIComponent(PRCHAT_CSRF.name) + '=' + encodeURIComponent(PRCHAT_CSRF.hash);
                  }
                  fetch(urlDecl, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                      'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: bodyDecl
                  });
                } catch (e) {}
                window.__chatCallManager.hangup();
                window.__CHAT_CALL_STATE = 'idle';
                window.__CHAT_CALL_PEER = null;
                window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
              }, {
                avatarUrl: avatarUrlIn,
                isVideo: isVideoCall
              });
            } else {
              // Fallback: auto-accept
              window.__CHAT_CALL_STATE = 'inCall';
              window.__CHAT_CALL_PEER = payload.from_id;
              window.__CHAT_CALL_WAS_VIDEO = isVideoCall;
              saveCallState();
              window.__chatCallManager.answer(userSessionId, payload.from_id, offer, {
                isVideo: isVideoCall,
                recipientType: payload.caller_type || 'staff',
                callerType: 'staff'
              });
            }
          } catch (e) {
            console.error(e);
          }
        });
        // Answer to our offer (we are the caller)
        callsChannel.bind('call-answer', function(payload) {
          try {
            prchatUiCallTrace('Pusher call-answer received', {
              from_id: payload && payload.from_id,
              sdpChars: payload && typeof payload.sdp === 'string' ? payload.sdp.length : null
            });
            if (window.__chatNoAnswerTimer) {
              clearTimeout(window.__chatNoAnswerTimer);
              window.__chatNoAnswerTimer = null;
            }

            var answer = JSON.parse(payload.sdp);
            var isVideoCall = !!(window.__CHAT_CALL_WAS_VIDEO || payload.is_video === true || payload.is_video === 1 || payload.is_video === '1');
            window.__chatCallManager && window.__chatCallManager.handleRemoteAnswer(answer);
            if (window.ChatCallUI) {
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              ChatCallUI.closeModal && ChatCallUI.closeModal();
              window.__CHAT_CALL_STATE = 'inCall';
              window.__CHAT_CALL_PEER = payload.from_id;
              window.__CHAT_CALL_START_TIME = Date.now();
              window.__CHAT_CALL_IS_CALLER = true;
              saveCallState();
              var avatarUrlAns = (payload.from_avatar && payload.from_avatar.length) ? payload.from_avatar : null;

              ChatCallUI.showInCall((payload.from_name || ''), function() {
                window.__chatCallManager.hangup();
                endCallWithSummary();
                if (isVideoCall) {
                  var pipEl = document.getElementById('chat-call-pip');
                  if (pipEl) pipEl.parentNode.removeChild(pipEl);
                }
              }, {
                avatarUrl: avatarUrlAns,
                isVideo: isVideoCall
              });
            }
          } catch (e) {
            console.error('[prchat-call] call-answer handler error:', e);
          }
        });
        // Callee explicitly declined
        callsChannel.bind('call-declined', function(payload) {
          if (window.__chatNoAnswerTimer) {
            clearTimeout(window.__chatNoAnswerTimer);
            window.__chatNoAnswerTimer = null;
          }

          var declinedPeer = window.__CHAT_CALL_PEER;

          if (window.ChatCallUI) {
            ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
            ChatCallUI.closeModal && ChatCallUI.closeModal();
            var $contacts = $("#contacts");
            var $contactEl = $contacts.find('a#' + payload.from_id + ', a#' + payload.from_id + ', li.contact#' + payload.from_id).first();
            var avatarUrl = (payload.from_avatar && payload.from_avatar.length) ? payload.from_avatar : ($contactEl.find('img').first().attr('src') || null);
            ChatCallUI.showNotice && ChatCallUI.showNotice('Declined', {
              autoCloseMs: 1500,
              avatarUrl: avatarUrl
            });
          }
          if (window.__chatCallManager && window.__chatCallManager.endFromRemote) {
            window.__chatCallManager.endFromRemote();
          }
          if (declinedPeer) sendMissedCallMessage(declinedPeer);
          window.__CHAT_CALL_STATE = 'idle';
          window.__CHAT_CALL_PEER = null;
          window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
          localStorage.removeItem('prchat_call_state');
        });

        // ICE candidates
        callsChannel.bind('call-ice', function(payload) {
          try {
            prchatUiCallTrace('Pusher call-ice', 'from', payload.from_id, 'to', payload.to_id);
            var cand = JSON.parse(payload.candidate);
            window.__chatCallManager && window.__chatCallManager.addIceCandidate(cand);
          } catch (e) {
            prchatUiCallTrace('call-ice handler error', e);
          }
        });
        callsChannel.bind('call-hangup', function(payload) {
          prchatUiCallTrace('Pusher call-hangup', payload);
          // Send call summary FIRST (before resetting state)
          endCallWithSummary();

          // Clean up call manager
          if (window.__chatCallManager) {
            if (window.__chatCallManager.endFromRemote) {
              window.__chatCallManager.endFromRemote();
            } else {
              window.__chatCallManager.hangup();
            }
          }

          // Clean up UI and timers
          if (window.ChatCallUI) {
            ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
            ChatCallUI.closeModal && ChatCallUI.closeModal();

            if (window.__chatCallTimer) {
              clearInterval(window.__chatCallTimer);
              window.__chatCallTimer = null;
            }
          }

          // Clear no-answer timer (for outgoing calls)
          if (window.__chatNoAnswerTimer) {
            clearTimeout(window.__chatNoAnswerTimer);
            window.__chatNoAnswerTimer = null;
          }

          // Remove PiP container (contains all video elements)
          var pipEl = document.getElementById('chat-call-pip');
          if (pipEl) pipEl.parentNode.removeChild(pipEl);

          // Hide mute indicator
          var muteIndicator = document.getElementById('remote-mute-indicator');
          if (muteIndicator) muteIndicator.style.display = 'none';

          // Clear localStorage
          localStorage.removeItem('prchat_call_state');
        });

        // Remote user mute status changed
        callsChannel.bind('call-mute-status', function(payload) {
          if (payload.from_id != window.__CHAT_CALL_PEER) return;

          var isMuted = payload.is_muted;
          showRemoteMuteIndicator(isMuted);
        });
      }

      var callBindingsInitialized = false;
      $(document).on('callsChannelReady', function() {
        if (!callBindingsInitialized) {
          callBindingsInitialized = true;
          prchatUiCallTrace('document callsChannelReady: invoking initializeCallEventBindings');
          initializeCallEventBindings();
        } else {
          prchatUiCallTrace('document callsChannelReady: ignored (already bound)');
        }
      });

      // Show/hide mute indicator on remote video
      function showRemoteMuteIndicator(isMuted) {
        var indicator = document.getElementById('remote-mute-indicator');

        if (isMuted) {
          // Create indicator if it doesn't exist
          if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'remote-mute-indicator';
            indicator.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);' +
              'background:rgba(0,0,0,0.8);color:#fff;padding:12px 20px;' +
              'border-radius:25px;font-size:14px;font-weight:600;z-index:10000001;' +
              'display:flex;align-items:center;gap:8px;' +
              'box-shadow:0 4px 12px rgba(0,0,0,0.3);backdrop-filter:blur(10px);';
            indicator.innerHTML = '<i class="fa fa-microphone-slash" style="color:#ef4444;"></i> <span>They muted their microphone</span>';
            document.body.appendChild(indicator);
          }
          indicator.style.display = 'flex';
        } else {
          // Hide indicator
          if (indicator) {
            indicator.style.display = 'none';
          }
        }
      }

    <?php endif; ?>

    // Calls: start a voice call from staff chat settings button (full view only)
    $(document).on('click', '.chat-settings-option[data-action="call-user"]', function() {
      try {
        if (typeof ChatCallManager === 'undefined') {
          return;
        }
        var activeAnchor = $("#contacts a.active_chat");
        if (!activeAnchor.length) {
          return;
        }
        var peerId = activeAnchor.attr('id');
        if (!peerId) {
          return;
        }
        peerId = peerId.replace('user_', '');
        peerId = parseInt(peerId, 10);
        if (!peerId || peerId < 1) {
          return;
        }
        if (!window.__chatCallManager) {
          window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
        }
        window.__chatCallManager.onRemoteTrack = function(stream) {
          var audio = document.getElementById('chat-call-remote');
          if (!audio) {
            audio = document.createElement('audio');
            audio.id = 'chat-call-remote';
            audio.autoplay = true;
            document.body.appendChild(audio);
          }
          audio.srcObject = stream;
        };
        window.__CHAT_CALL_STATE = 'ringingOutgoing';
        window.__CHAT_CALL_PEER = peerId;
        window.__CHAT_CALL_WAS_VIDEO = false;
        saveCallState();

        if (window.ChatCallUI) {
          var avatarUrl = $("#contacts a#user_" + peerId + " img").attr('src') || null;
          ChatCallUI.showOutgoingCall('', function() {
            if (window.__chatNoAnswerTimer) {
              clearTimeout(window.__chatNoAnswerTimer);
              window.__chatNoAnswerTimer = null;
            }
            var cancelledPeer = window.__CHAT_CALL_PEER;
            window.__chatCallManager && window.__chatCallManager.hangup();
            window.__CHAT_CALL_STATE = 'idle';
            window.__CHAT_CALL_PEER = null;
            localStorage.removeItem('prchat_call_state');
            if (cancelledPeer) sendMissedCallMessage(cancelledPeer);
          }, {
            avatarUrl: avatarUrl,
            isVideo: false
          });
        }
        window.__chatCallManager.call(userSessionId, peerId, false, {
          recipientType: 'staff',
          callerType: 'staff'
        });
        if (window.__chatNoAnswerTimer) clearTimeout(window.__chatNoAnswerTimer);
        window.__chatNoAnswerTimer = setTimeout(function() {
          try {
            if (window.__CHAT_CALL_STATE !== 'inCall') {
              var missedPeer = window.__CHAT_CALL_PEER;
              window.__chatCallManager && window.__chatCallManager.hangup();
              if (window.ChatCallUI) {
                ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                ChatCallUI.closeModal && ChatCallUI.closeModal();
                ChatCallUI.showNotice && ChatCallUI.showNotice('No answer', {
                  autoCloseMs: 1500
                });
              }
              if (missedPeer) sendMissedCallMessage(missedPeer);
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
              localStorage.removeItem('prchat_call_state');
            }
          } catch (e) {}
        }, 15000);
      } catch (e) {
        if (window.console && console.error) console.error(e);
      }
    });

    /*---------------* Pusher Trigger user connected *---------------*/
    presenceChannel.bind("pusher:member_added", function(member) {
      if (member.info.status == "") {
        member.info.status = "online";
      }
      if (member.info.status != "") {
        updateStaffContactStatus(member.id, true, member.info.status);
      }

      // Only use the server-side justLoggedIn flag (set during actual login via before_staff_login hook)
      // Do NOT use wasOffline — page reloads can exceed the 5s offline delay and cause false positives
      var isActuallyComingOnline = member.info.justLoggedIn === true;

      if (isActuallyComingOnline) {
        // Use native browser Notification API like Perfex CRM
        if ("Notification" in window && Notification.permission === "granted") {
          var notification = new Notification(app.lang.new_notification, {
            body: member.info.name + " " + prchatSettings.hasComeOnlineText,
            icon: $("#header").find("img").attr("src"),
            tag: "user-join-" + member.id,
            requireInteraction: true
          });

          notification.onclick = function(e) {
            window.focus();
            this.close();
          };

          // Auto close after specified time
          if (app.options.dismiss_desktop_not_after != "0") {
            setTimeout(function() {
              notification.close();
            }, app.options.dismiss_desktop_not_after * 1000);
          } else {
            // Default 2 second timeout
            setTimeout(function() {
              notification.close();
            }, 2000);
          }
        }
      }

      // Now add the chat member (pass whether to move to top)
      addChatMember(member, isActuallyComingOnline);
    });

    /*---------------* Pusher Trigger user logout *---------------*/
    presenceChannel.bind("pusher:member_removed", function(members) {
      removeChatMember(members);
    });

    var pendingRemoves = [];

    // Function to update staff contact online/offline status
    function updateStaffContactStatus(staffId, isOnline, status) {
      var contactElement = $("#contacts .contact a#" + staffId);
      var imgElement = contactElement.find("img.imgFriend");
      var dotElement = contactElement.find(".status-dot");

      if (!imgElement.length) {
        return;
      }

      if (isOnline) {
        // Update to online status (could be online, away, busy)
        var actualStatus = (status && status !== "") ? status : "online";
        imgElement.removeClass("offline away busy online").addClass(actualStatus);
        dotElement.removeClass("offline away busy online").addClass(actualStatus);
        imgElement.attr("title", actualStatus);
        imgElement.attr("data-original-title", actualStatus);
      } else {
        // Update to offline status
        imgElement.removeClass("online away busy").addClass("offline");
        dotElement.removeClass("online away busy").addClass("offline");
        imgElement.attr("title", "offline");
        imgElement.attr("data-original-title", "offline");
      }
    }

    /*---------------* Update online users counter in sidebar header *---------------*/
    window._onlineClientCount = 0;

    function updateOnlineCounter() {
      var staffCount = 0;
      if (presenceChannel && presenceChannel.members) {
        staffCount = Math.max(0, presenceChannel.members.count - 1);
      }

      var clientCount = window._onlineClientCount || 0;
      var hasAnything = staffCount > 0 || clientCount > 0;

      var $counter = $(".online-users-counter");
      if ($counter.length) {
        $counter.find(".staff-counter .counter-val").text(staffCount);
        $counter.find(".client-counter .counter-val").text(clientCount);
        $counter.toggle(hasAnything);
      }
    }

    /*---------------* New chat members tracking / removing *---------------*/
    function addChatMember(member, shouldMoveToTop) {
      var pendingRemoveTimeout = pendingRemoves[member.id];

      // Update staff contact status to online with actual status
      var memberStatus = (member.info && member.info.status) ? member.info.status : 'online';
      updateStaffContactStatus(member.id, true, memberStatus);

      // Update online users count in sidebar header
      updateOnlineCounter();

      // Move to top if user actually came online (not just reconnecting)
      if (shouldMoveToTop) {
        appendMemberToTop(member.id);
      }

      if (pendingRemoveTimeout) {
        clearTimeout(pendingRemoveTimeout);
      }
    }

    /*---------------* New chat members tracking / removing *---------------*/
    function removeChatMember(members) {
      pendingRemoves[members.id] = setTimeout(function() {
        // Update online counter in sidebar header
        updateOnlineCounter();
        // Update staff contact status to offline
        updateStaffContactStatus(members.id, false);
        chatMemberUpdate();
      }, 5000);
    }

    /*---------------* Append member to top of sidebar after logged in *---------------*/
    function appendMemberToTop(member) {
      var contactMember = $("#contacts li.contact#" + member);
      if (contactMember.length) {
        // Use detach (preserves events/data) instead of clone+remove
        contactMember.detach().prependTo("#contacts ul");
      }
    }

    /*---------------* Bind the 'send-event' & update the chat box message log *---------------*/
    presenceChannel.bind("send-event", function(data) {
      if (data.global) {
        data.message = "<?= '<strong>' . _l('chat_message_announce') . '</strong>'; ?>" + data.message;
      }

      if (data.from) {
        clearStaffTypingFor(data.from);
      }

      if (data.last_insert_id) {
        $(".modern-messages-container").find('[data-message-id="' + data.last_insert_id + '"]').find(".optionsMore").attr("data-id", data.last_insert_id);
      }

      if (String(presenceChannel.members.me.id) === String(data.to) && String(data.from) !== String(presenceChannel.members.me.id)) {
        $(".has_newmessages").val("true").attr("id", data.from);

        var rendered = PrchatSafeRenderer.render(data.message);
        var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(data.message);

        // Only append to messages if the sender is currently selected (visible in chat)
        if (String($("li.contact.active").attr("id")) === String(data.from)) {
          const realtimeMessageData = {
            id: data.message_id || data.from + '_' + Date.now(),
            sender_id: data.from,
            message: displayForRow,
            time_sent_formatted: moment().format(prchatSettings.timeFormat),
            viewed: false,
            user_image: data.sender_image
          };

          $(".messages#id_" + data.from + " .modern-messages-container").append(createModernMessage(realtimeMessageData));

          var messageContainer = $(".messages#id_" + data.from);
          if (messageContainer.length > 0) {
            messageContainer.scrollTop(messageContainer[0].scrollHeight);
          }
        }

        $("#contacts .contact a#" + data.from).find(".wrap .meta .preview").text(rendered.sidebar);

        // Update sidebar time (server may send time_ago or time_sent_formatted; fallback to moment relative time)
        var timeDisplay = data.time_ago || data.time_sent_formatted || moment().fromNow();
        var timeAgoElement = $("#contacts .contact a#" + data.from).find(".wrap .meta .pull-right.time_ago");
        if (timeAgoElement.length === 0) {
          $("#contacts .contact a#" + data.from).find(".wrap .meta .meta-row-preview").append("<p class=\"pull-right time_ago\">" + timeDisplay + "</p>");
        } else {
          timeAgoElement.html(timeDisplay);
        }

        // Skip unread badge + sound for completed call messages (not missed)
        var _isCallMsg = data.is_call || (typeof data.message === 'string' && /^\[CALL:(voice|video):\d/.test(data.message));

        // Add unread notification badge — skip for completed call messages
        if (!_isCallMsg && String($("li.contact.active").attr("id")) !== String(data.from)) {
          var unreadNotificationBadge = $("#contacts .contact a#" + data.from).find(".unread-notifications");
          if (unreadNotificationBadge.length === 0) {
            var newBadge = $("<span class=\"unread-notifications\" data-badge=\"1\"></span>");
            newBadge.data("client-updated", Date.now());
            $("#contacts .contact a#" + data.from).append(newBadge);
          } else {
            var currentCount = parseInt(unreadNotificationBadge.attr("data-badge")) || 0;
            var newCount = currentCount + 1;
            unreadNotificationBadge.attr("data-badge", newCount);
            unreadNotificationBadge.data("client-updated", Date.now());
          }
        }

        // Tab unread badge - only for messages sent TO current user
        if (data.from && data.from != userSessionId && data.to == userSessionId) {
          // Only show badge if not currently on staff tab
          if (!$("#frame #sidepanel .staff a").parent().hasClass("active")) {
            incrementTabBadge('staff');
          }
        }

        // Check if this contact is muted
        var isStaffMuted = mutedStaff.indexOf(String(data.from)) !== -1;

        // Sound notification — skip for completed call messages
        if (!_isCallMsg && !isStaffMuted && data.from && data.from != userSessionId && user_chat_status != "offline") {
          if (window.chatSoundManager) {
            window.chatSoundManager.setUserStatus(user_chat_status);
            window.chatSoundManager.initUserSound(data);
          }
        }

        // Floating notification — skip for completed call messages
        var staffTabActive = $("#frame #sidepanel .staff a").parent().hasClass("active");
        if (!_isCallMsg && !isStaffMuted && typeof FloatingChatNotifications !== 'undefined' && !staffTabActive) {
          // Render message first for proper preview
          var rendered = PrchatSafeRenderer.render(data.message);
          var fvNotifMsg =
            (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.formatCallPreview(data.message)) ||
            rendered.sidebar ||
            (data.message || '').replace(/<[^>]*>/g, '').substring(0, 50) + '...';
          FloatingChatNotifications.add({
            from: data.from,
            fromName: data.from_name || '<?= addslashes(_l("chat_staff_member_fallback")) ?>',
            type: 'staff',
            message: fvNotifMsg,
            avatar: fetchUserAvatar(data.from, data.sender_image)
          });
        }

        // Move this contact to top of sidebar (most recent conversation first)
        appendMemberToTop(data.from);

        // Scroll to bottom if current chat is open
        if (String($("li.contact.active").attr("id")) === String(data.from)) {
          setTimeout(function() {
            $(".messages#id_" + data.from).scrollTop($(".messages#id_" + data.from)[0].scrollHeight);
          }, 100);
        }
      }
    });

    presenceChannel.bind("message-edited", function(data) {
      var mid = data.message_id || data.id;
      if (!mid) return;
      var $msg = $('.messages .modern-messages-container').find('[data-message-id="' + mid + '"]');
      if (!$msg.length) return;
      if (data.rendered_message) {
        $msg.find('.modern-message-text').html(data.rendered_message);
      }
      if (!$msg.find('.prchat-edited-tag').length) {
        $msg.find('.modern-message-text').after('<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>');
      }
      // Sidebar preview: only if this row is still the latest message in the thread
      var $c = $msg.closest('.modern-messages-container');
      var $last = $c.find('[data-message-id]').last();
      if (!$msg.is($last) || typeof PrchatSafeRenderer === 'undefined') {
        return;
      }
      var side = PrchatSafeRenderer.render(data.rendered_message).sidebar;
      var youLbl = '<?= _l('chat_message_you'); ?> ';
      var me = String(typeof window.userSessionId !== 'undefined' ? window.userSessionId : '');
      var fromS = String(data.from);
      var toS = String(data.to);
      var activeId = String($('li.contact.active').attr('id') || '');
      if (!activeId || (activeId !== fromS && activeId !== toS)) {
        return;
      }
      if (fromS === me) {
        $('#contacts .contact.active').find('.wrap .meta .preview').text(youLbl + side);
      } else {
        $('#contacts .contact a#' + fromS).find('.wrap .meta .preview').text(side);
      }
      var rowKey = fromS === me ? activeId : fromS;
      var sendTimeLabel = moment().fromNow();
      var $ta = $('#contacts .contact a#' + rowKey).find('.wrap .meta .pull-right.time_ago');
      if ($ta.length) {
        $ta.html(sendTimeLabel);
      } else {
        $('#contacts .contact a#' + rowKey).find('.wrap .meta .meta-row-preview').append(
          '<p class="pull-right time_ago">' + sendTimeLabel + '</p>'
        );
      }
      if (typeof appendMemberToTop === 'function') {
        appendMemberToTop(rowKey);
      }
    });

    presenceChannel.bind("message-deleted", function(data) {
      var mid = data && (data.message_id || data.id);
      if (!mid) return;

      var me = String(typeof userSessionId !== 'undefined' ? userSessionId : '');
      var fromS = String(data.from || '');
      var toS = String(data.to || '');
      if (fromS !== me && toS !== me) {
        return;
      }

      var otherId = fromS === me ? toS : fromS;
      if (!otherId) {
        return;
      }

      var $conv = $('.messages#id_' + otherId + ' .modern-messages-container');
      if ($conv.length) {
        $conv.find('[data-message-id="' + mid + '"]').remove();
      }

      if (String($('li.contact.active').attr('id') || '') === String(otherId)) {
        updateSidebarAfterDelete('staff', otherId);
        getSharedFiles(userSessionId, otherId);
      }
    });

    /*---------------* Detect when a user is typing a message (mirror clients tab logic) *---------------*/
    function clearStaffTypingFor(fromId) {
      if (staffTypingTimerId) {
        clearTimeout(staffTypingTimerId);
        staffTypingTimerId = null;
      }
      var sid = String(fromId).replace(/'/g, "\\'");
      $(".modern-typing-indicator[data-user-id='" + sid + "']").fadeOut(500);
      var $contactEl = $("#frame #contacts ul li.contact[id='" + sid + "']");
      var $sidebarPreview = $contactEl.find(".wrap .meta .preview");
      if ($sidebarPreview.length && $sidebarPreview.data("original-preview")) {
        $sidebarPreview.html($sidebarPreview.data("original-preview"));
        $sidebarPreview.removeData("original-preview");
      }
    }
    presenceChannel.bind("typing-event", function(data) {
      if (
        presenceChannel.members.me.id != data.to ||
        data.from == presenceChannel.members.me.id
      ) return;

      if (data.message == "true") {
        clearTimeout(staffTypingTimerId);
        var activeContactId = $("li.contact.active").attr("id");
        var isInThisChat = (activeContactId === String(data.from));
        var sid = String(data.from).replace(/'/g, "\\'");
        var $contactEl = $("#frame #contacts ul li.contact[id='" + sid + "']");

        var $typingIndicator = $(".modern-typing-indicator");
        if (isInThisChat && $typingIndicator.length) {
          $typingIndicator.attr("data-user-id", data.from);
          var userName = $contactEl.find(".meta .name").text() || 'User';
          $typingIndicator.find('.modern-typing-text').text(userName + ' is typing...');
          $typingIndicator.fadeIn(500);
          setTimeout(function() {
            scrollToBottom(true);
          }, 200);
        }

        var $sidebarPreview = $contactEl.find(".wrap .meta .preview");
        if ($sidebarPreview.length && !$sidebarPreview.data("original-preview")) {
          $sidebarPreview.data("original-preview", $sidebarPreview.html());
          $sidebarPreview.html('<span class="typing-preview"><?php echo _l("chat_typing"); ?></span>');
        }

        staffTypingTimerId = setTimeout(function() {
          staffTypingTimerId = null;
          clearStaffTypingFor(data.from);
        }, STAFF_TYPING_CLEAR_MS);
      } else if (data.message == "null" || data.message == "false") {
        clearTimeout(staffTypingTimerId);
        staffTypingTimerId = null;
        clearStaffTypingFor(data.from);
      }
    });

    /*---------------* Trigger notification popup increment and live notification *---------------*/
    presenceChannel.bind("notify-event", function(data) {
      if (chat_desktop_notifications_enabled) {
        if (data.from !== userSessionId && data.to == userSessionId) {
          if (user_chat_status != "busy" && user_chat_status != "offline") {

            // Normalize call summary notifications ([CALL:...]) to the same preview text as the chat UI
            if (
              typeof PrchatSafeRenderer !== "undefined" &&
              PrchatSafeRenderer &&
              typeof PrchatSafeRenderer.formatCallPreview === "function"
            ) {
              var callPreview = PrchatSafeRenderer.formatCallPreview(data.message);
              if (!callPreview && typeof data.message === "string") {
                var callMatch = data.message.match(
                  /^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/
                );
                if (callMatch) {
                  callPreview = PrchatSafeRenderer.formatCallPreview(callMatch[0]);
                }
              }
              if (callPreview) data.message = callPreview;
            }

            setTimeout(() => {
              // Use native browser Notification API like Perfex CRM
              if ("Notification" in window && Notification.permission === "granted") {
                // Use cleanNotificationText for all message types — it handles uploads, audio, HTML stripping
                var notifBodyRaw = (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.cleanNotificationText) ?
                  PrchatSafeRenderer.cleanNotificationText(data.message) :
                  String(data.message || '').replace(/<[^>]*>/g, '').substring(0, 100);
                var notifBody = notifBodyRaw;
                if (typeof notifBodyRaw === 'string') {
                  var compactBody = notifBodyRaw.trim().toLowerCase();
                  var i18n = (typeof window !== 'undefined' && window.prchatI18n) ? window.prchatI18n : {};
                  var upPhoto = String(i18n.chat_new_photo_uploaded || '').trim().toLowerCase();
                  var upFile = String(i18n.chat_new_file_uploaded || '').trim().toLowerCase();
                  if ((upPhoto && compactBody === upPhoto) || (upFile && compactBody === upFile)) {
                    notifBody = data.from_name + ' ' + notifBodyRaw;
                  }
                }

                var notification = new Notification(data.from_name, {
                  body: notifBody,
                  icon: fetchUserAvatar(data.from, data.sender_image),
                  tag: "user-message-" + data.from,
                  requireInteraction: true
                });

                notification.onclick = function(e) {
                  window.focus();
                  this.close();
                };

                // Auto close after specified time
                if (app.options.dismiss_desktop_not_after != "0") {
                  setTimeout(function() {
                    notification.close();
                  }, app.options.dismiss_desktop_not_after * 1000);
                }
              }
            }, 1000);
          }
        }
      }

      // Handle staff tab badge only (badge increment is handled by first handler above)
      if (data.from !== userSessionId && data.to == userSessionId) {
        var notiBox = $("body").find("li.contact#" + data.from + " a#" + data.from);
        if (!$(notiBox).hasClass("active_chat")) {
          // Add staff tab unread badge if not on staff tab
          if (!$("#frame #sidepanel .staff a").parent().hasClass("active")) {
            incrementTabBadge('staff');
          }
        }
      }
    });

    /*---------------* On click send message button trigger post message *---------------*/
    $("body").on("click", ".enterBtn, .enterGroupBtn, .enterClientBtn, .fa-paper-plane", function(e) {
      // Close emoji picker if open
      if (window.emojiPickerInstance && window.emojiPickerInstance.isVisible) {
        window.emojiPickerInstance.hide();
      }
      var eventEnter = $.Event("keypress", {
        which: 13
      });

      var targetName = "";
      if (e.currentTarget.getAttribute("name") !== null) {
        targetName = e.currentTarget.getAttribute("name");
      }
      // Groups
      if (targetName == "") {
        targetName = e.currentTarget.parentNode.getAttribute("name");
      }
      if (targetName == "enterBtn") {
        $("#frame").find(".chatbox").trigger(eventEnter);
        $(".chatbox").focus();
      } else if (targetName == "enterGroupBtn") {
        $("#frame").find(".group_chatbox").trigger(eventEnter);
        $(".group_chatbox").focus();
      } else if (targetName == "enterClientBtn") {
        $("#frame").find(".client_chatbox").trigger(eventEnter);
        $(".client_chatbox").focus();
      } else {
        // Fallback: try to trigger the visible chatbox
        var $visibleChatbox = $("#frame").find("textarea.chatbox:visible");
        if ($visibleChatbox.length > 0) {
          $visibleChatbox.trigger(eventEnter);
          $visibleChatbox.focus();
        }
      }
    });

    /*---------------* chatMemberUpdate() place & update users on user page, unread messages notifications *---------------*/
    function chatMemberUpdate() {
      users.then(function(data) {
        var offlineUser = "";
        var onlineUser = "";
        data = JSON.parse(data);
        if (Object.keys(data).length > 1) {
          $("#frame .nav.nav-tabs li.groups, #frame .nav.nav-tabs li.groups a, #frame .nav.nav-tabs li.crm_clients a").removeClass("events_disabled");

          // Sort users by time_sent (most recent conversations first)
          var dataArr = Object.values(data);
          dataArr.sort(function(a, b) {
            var aTime = a.time_sent ? new Date(a.time_sent).getTime() : 0;
            var bTime = b.time_sent ? new Date(b.time_sent).getTime() : 0;
            return bTime - aTime; // Descending (newest first)
          });
          // Rebuild data as object with sorted order
          data = {};
          dataArr.forEach(function(user, idx) {
            data[idx] = user;
          });
        } else {
          $(".message-input, .contact-profile").remove();

          $(".content .messages").html(
            '<div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-min-h-[400px] tw-p-8">' +
            '<div class="tw-max-w-2xl tw-w-full">' +
            '<div class="tw-border-l-4 tw-border-primary-500 tw-bg-primary-50 tw-rounded-lg tw-p-6 tw-mb-4">' +
            '<div class="tw-flex tw-items-start tw-gap-3">' +
            '<div class="tw-flex-shrink-0 tw-w-6 tw-h-6 tw-rounded-full tw-bg-primary-500 tw-flex tw-items-center tw-justify-center tw-mt-0.5">' +
            '<i class="fa fa-info tw-text-white tw-text-xs"></i>' +
            '</div>' +
            '<div class="tw-flex-1">' +
            '<h4 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mb-3">No staff users with valid permissions found</h4>' +
            '<p class="tw-text-sm tw-text-neutral-700 tw-mb-3">Try adding <strong>new staff users</strong> or grant Chat Access to existing staff users.</p>' +
            '<div class="tw-text-sm tw-text-neutral-600 tw-space-y-2">' +
            '<p><strong>To manage staff permissions:</strong></p>' +
            '<ol class="tw-list-decimal tw-list-inside tw-space-y-1 tw-ml-2">' +
            '<li>Navigate to <strong>Setup → Staff</strong></li>' +
            '<li>Select a staff member and click <strong>Permissions</strong></li>' +
            '<li>Scroll down to <strong>Chat Module</strong> and assign chat access</li>' +
            '<li>Or disable "Show only users with chat permissions" in <strong>Setup → Settings → Chat Settings</strong></li>' +
            '</ol>' +
            '<p class="tw-mt-3 tw-text-xs tw-text-neutral-500"><i class="fa fa-info-circle tw-mr-1"></i> Administrators do not need any permissions.</p>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="tw-border-l-4 tw-border-warning-500 tw-bg-warning-50 tw-rounded-lg tw-p-4">' +
            '<div class="tw-flex tw-items-start tw-gap-3">' +
            '<div class="tw-flex-shrink-0 tw-w-5 tw-h-5 tw-rounded-full tw-bg-warning-500 tw-flex tw-items-center tw-justify-center tw-mt-0.5">' +
            '<i class="fa fa-exclamation-triangle tw-text-white tw-text-xs"></i>' +
            '</div>' +
            '<p class="tw-text-sm tw-font-medium tw-text-neutral-800"><strong>Important:</strong> You must create at least one staff member (including yourself)!</p>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
          );
          return false;
        }
        $(".chatbox").prop("disabled", "");
        $.each(data, function(user_id, value) {
          if (value.role == null) {
            value.role = "<?= _l("als_staff"); ?>";
          }
          if (value.staffid != presenceChannel.members.me.id) {
            var user = presenceChannel.members.get(value.staffid);

            if (value.message == undefined) value.message = prchatSettings.sayHiText + " " + strCapitalize(value.firstname + " " + value.lastname);

            if (value.time_sent_formatted == undefined) value.time_sent_formatted = "";

            var user_status = (value.status != undefined || value.status == "") ? value.status : "online";

            var translated_status = "";
            for (var status in chat_user_statuses) {
              if (status == user_status) {
                translated_status = chat_user_statuses[status];
              }
            }

            var preview_message = PrchatSafeRenderer.getSidebarPreview(value.message, value.message);

            // Build pin/mute icon HTML
            var pinMuteIcons = "";
            var isPinned = pinnedStaff.indexOf(String(value.staffid)) !== -1;
            var isMuted = mutedStaff.indexOf(String(value.staffid)) !== -1;
            if (isPinned) pinMuteIcons += '<i class="fa fa-thumb-tack contact-pin-icon"></i>';
            if (isMuted) pinMuteIcons += '<i class="fa fa-bell-slash contact-mute-icon"></i>';

            if (user != null) {
              var avatarUrl = value.profile_image_url || fetchUserAvatar(value.staffid, value.profile_image);
              onlineUser += "<li class=\"contact\" id=\"" + value.staffid + "\" data-pinned=\"" + (isPinned ? "1" : "0") + "\">";
              onlineUser += pinMuteIcons;
              onlineUser += "<a href=\"#" + value.staffid + "\" id=\"" + value.staffid + "\" class=\"on\"><div class=\"wrap\">";
              onlineUser += "<span class=\"avatar-wrap\">";
              onlineUser += "<img src=\"" + avatarUrl + "\" onerror=\"this.onerror=null;this.src='<?= base_url('assets/images/user-placeholder.jpg') ?>';\" class=\"imgFriend " + user_status + "\" />";
              onlineUser += "<span class=\"status-dot " + user_status + "\"></span>";
              onlineUser += "</span>";
              onlineUser += "<div class=\"meta\"><p role=\"" + value.role + "\" class=\"name\">" + strCapitalize(value.firstname + " " + value.lastname) + "</p>";
              if (value.role && value.role !== "0" && value.role !== "undefined" && value.role !== "null") {
                onlineUser += "<p class=\"contact-title\">" + value.role + "</p>";
              } else if (value.role === "0") {
                onlineUser += "<p class=\"contact-title\"><?= _l('chat_role_administrator'); ?></p>";
              }
              onlineUser += "<social_info skype=\"" + value.skype + "\" facebook=\"" + value.facebook + "\" linkedin=\"" + value.linkedin + "\"></social_info>";
              onlineUser += "<div class=\"meta-row-preview\"><p class=\"preview\">" + preview_message + "</p>";
              if (value.time_sent_formatted && value.time_sent_formatted.trim() !== "") {
                onlineUser += "<p class=\"pull-right time_ago\">" + value.time_sent_formatted + "</p>";
              }
              onlineUser += "</div></div></div>";
              onlineUser += "<span class=\"unread-notifications\" id=\"" + value.staffid + "\" data-badge=\"0\"></span></a></li>";

            } else {
              var lastSeenText = "";
              if (value.last_activity) {
                lastSeenText = "<?php echo _l('chat_last_seen'); ?>: " + moment(value.last_activity, "YYYYMMDD h:mm:ss").fromNow();
              } else {
                lastSeenText = "<?php echo _l('chat_last_seen'); ?>: Never";
              }

              offlineUser += "<li class=\"contact\" id=\"" + value.staffid + "\" data-pinned=\"" + (isPinned ? "1" : "0") + "\"";
              offlineUser += ' data-toggle="tooltip" data-container="body" title="' + lastSeenText + '">';
              offlineUser += pinMuteIcons;
              offlineUser += "<a href=\"#" + value.staffid + "\" id=\"" + value.staffid + "\" class=\"off\"><div class=\"wrap\">";
              var offlineAvatarUrl = value.profile_image_url || fetchUserAvatar(value.staffid, value.profile_image);
              offlineUser += "<span class=\"avatar-wrap\">";
              offlineUser += "<img src=\"" + offlineAvatarUrl + "\" onerror=\"this.onerror=null;this.src='<?= base_url('assets/images/user-placeholder.jpg') ?>';\" class=\"imgFriend offline\" />";
              offlineUser += "<span class=\"status-dot offline\"></span>";
              offlineUser += "</span>";
              offlineUser += "<div class=\"meta\"><p role=\"" + value.role + "\" class=\"name\">" + strCapitalize(value.firstname + " " + value.lastname) + "</p>";
              if (value.role && value.role !== "0" && value.role !== "undefined" && value.role !== "null") {
                offlineUser += "<p class=\"contact-title\">" + value.role + "</p>";
              } else if (value.role === "0") {
                offlineUser += "<p class=\"contact-title\"><?= _l('chat_role_administrator'); ?></p>";
              }
              offlineUser += "<div class=\"meta-row-preview\"><p class=\"preview\">" + preview_message + "</p>";
              if (value.time_sent_formatted && value.time_sent_formatted.trim() !== "") {
                offlineUser += "<p class=\"pull-right time_ago\">" + value.time_sent_formatted + "</p>";
              }
              offlineUser += "</div></div></div><span class=\"unread-notifications\" id=\"" + value.staffid + "\" data-badge=\"0\"></span></a></li>";
            }
          }
        });
        $("#frame #contacts ul").html("");
        $("#frame #contacts ul").prepend(onlineUser + offlineUser);

        // Move pinned contacts to top of list
        sortPinnedToTop("#contacts ul", pinnedStaff);

        // Hide contact list skeleton after contacts are loaded
        $("#contacts .contacts-skeleton").addClass("hidden");
        var newUnreadMessages = JSON.parse(checkForNewMessages);

        if (!checkForNewMessages.includes("false")) {
          var notifications;
          $.each(newUnreadMessages, function(i, sender) {
            notifications = $("#contacts li a#" + sender.sender_id).find(".unread-notifications");
            if (notifications.length) {
              var messageCount = sender.count_messages || 0;
              var currentBadge = parseInt(notifications.attr("data-badge")) || 0;
              var lastClientUpdate = notifications.data("client-updated") || 0;
              var timeSinceUpdate = Date.now() - lastClientUpdate;

              // Only update if:
              // 1. Server count is higher than current client count, OR
              // 2. It's been more than 5 seconds since last client update
              if (messageCount > currentBadge || timeSinceUpdate > 5000) {
                notifications.attr("data-badge", messageCount);
                // Clear the client-updated flag since we're syncing with server
                if (timeSinceUpdate > 5000) {
                  notifications.removeData("client-updated");
                }
              }
              // Remove inline display style to let CSS control visibility
              notifications.css("display", "");
            }
          });
        }
        return false;
      });
    }

    /*---------------* Trigger click on user & create chat box and check for messages *---------------*/
    $("#frame #contacts .chat_contacts_list").on("click", "li.contact a", function(event) {
      animateContent();
      var obj = $(this);
      var id = obj.attr("id").replace("id_", "");
      var contact_selector = $("#contacts a#" + id);

      // Clear staff tab unread badge when user clicks on any staff contact
      clearTabBadge('staff');

      // Remove floating notification for this staff member
      if (typeof FloatingChatNotifications !== 'undefined' && typeof FloatingChatNotifications.removeOne === 'function') {
        FloatingChatNotifications.removeOne(id);
      }

      // Handle unread messages
      if ($(".has_newmessages").attr("id") == id && $(".has_newmessages").val() == "true" ||
        $(this).find(".unread-notifications").attr("data-badge") > 0) {
        updateLatestMessages(id);
      }


      $(".has_newmessages").val("false").attr("id", "");

      $(".group_members_inline").remove();

      var contact_image = $("#frame .contact-profile .profile-wrapper img.staff-profile-image-small");
      if (contact_image.is(":hidden")) {
        contact_image.show();
      }
      endOfScroll = false;
      offsetPush = 0;

      $("#frame .chatbox").val("");

      $("#contacts li a").removeClass("active_chat");

      $("#contacts .contact").removeClass("active");

      contact_selector.parent(".contact").addClass("active");

      $(this).addClass("active_chat");

      if (contact_selector.parent(".contact").find(".tb")) {
        contact_selector.parent(".contact").find(".tb").css({
          "font-weight": "normal",
          "color": "rgba(153, 153, 153, 1)"
        });
      }

      createChatBox(obj);

      if ($("#search_field").val() !== "") {
        clearSearchValues();
      }

      if ($(this).find(".unread-notifications").attr("data-badge") > 0) {
        updateUnreadMessages($(this));
        obj.find(".unread-notifications").attr("data-badge", "0");
        obj.find(".unread-notifications").css("display", "");
      }

      return false; // DO NOT REMOVE THIS LINE
    });

    /*---------------* Creating chat box from the html template to the DOM *---------------*/
    var createChatBoxRequest = null;

    function createChatBox(obj) {
      // Generate dynamic skeleton while messages load, clear old messages
      $(".messages ul").empty();
      generateMessageSkeleton('.messages .messages-skeleton', 12);
      var id = obj.attr("href");
      var fullName = obj.find(".meta").children("p:first-child").text();
      var contactRole = obj.find(".meta").children("p:first-child").attr("role");

      // Ensure contactRole is never empty/undefined
      if (!contactRole || contactRole === "" || contactRole === "undefined" || contactRole === "null") {
        contactRole = "<?= _l('chat_role_staff'); ?>";
      }

      var optionsMore = "";

      var contact_id = id.replace("#", "");
      id = id.replace("#", "id_");
      $(".modern-typing-indicator").attr("data-user-id", contact_id);



      // Check if contact-profile exists, if not, recreate it
      var $existingProfile = $("#frame .content .contact-profile");

      if ($("#frame .content .contact-profile").length === 0) {
        // Contact profile was removed, recreate it with correct structure
        var contactProfileHtml = `
                    <div class="contact-profile tw-flex tw-items-center">
                        <svg onclick="chatBackMobile()" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_back'); ?>" class="chat_back_mobile" viewBox="0 0 24 24">
                            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" />
                        </svg>
                        <div class="profile-wrapper">
                            <img src="" class="img img-responsive staff-profile-image-small pull-left" alt="" />
                            <p class="staff-name-dropdown">
                                <span class="staff-name-text"></span>
                                <i class="fa fa-chevron-down dropdown-arrow" onclick="toggleProfileDropdown(this)"></i>
                            </p>
                            <div class="profile-dropdown" id="profile-dropdown">
                                <ul>
                                    <li><a href="#" class="chatActionMembers-option" data-action="view-profile" id="dropdown-view-profile"><i class="fa fa-user"></i> <?= _l('chat_view_profile'); ?></a></li>
                                    <li><a href="#" class="chatActionMembers-option" data-action="add-task" onclick="chatNewTask(this); return false;"><i class="fa fa-tasks"></i> <?= _l('chat_associate_task'); ?></a></li>
                                </ul>
                            </div>
                            <div class="profile-actions">
                                <?php if (get_option('chat_staff_calls_enabled') == '1'): ?>
                                <button type="button" class="profile-action-btn chat-action-voice-call" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_start_voice_call'); ?>"><i class="fa fa-phone" aria-hidden="true"></i></button>
                                <?php endif; ?>
                                <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
                                <button type="button" class="profile-action-btn chat-action-video-call" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_start_video_call'); ?>"><i class="fa fa-video-camera" aria-hidden="true"></i></button>
                                <?php endif; ?>
                                <button type="button" class="profile-action-btn chat-header-search" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_search_msg_txt'); ?>"><i class="fa fa-search" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                `;

        $("#frame .content .chat_group_options").after(contactProfileHtml);
      }

      // Find the staff name element and update it directly
      var $staffNameElement = $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown .staff-name-text");
      var profileUrl = site_url + 'admin/profile/' + contact_id;

      if ($staffNameElement.length === 0) {
        // Element doesn't exist, create it directly in the staff-name-dropdown
        $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").html('<span class="staff-name-text"><a href="' + profileUrl + '" target="_blank" class="staff-profile-link">' + fullName + '</a><br><a target="_blank" href="' + profileUrl + '"><small class="contact_role">' + contactRole + '</small></a></span><i class="fa fa-chevron-down dropdown-arrow"></i>');
      } else {
        // Element exists, update it
        $staffNameElement.html('<a href="' + profileUrl + '" target="_blank" class="staff-profile-link">' + fullName + '</a><br><a target="_blank" href="' + profileUrl + '"><small class="contact_role">' + contactRole + '</small></a>');
      }

      // Ensure the arrow is always present after updating content
      if ($("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown .dropdown-arrow").length === 0) {
        $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").append('<i class="fa fa-chevron-down dropdown-arrow"></i>');
      }

      // Update the "View Profile" link in the dropdown
      $("#dropdown-view-profile").attr("href", profileUrl).attr("target", "_blank");

      // Make sure dropdown is closed
      $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").removeClass("dropdown-open");
      var taskName, userId;
      userId = $("li.contact.active").attr("id");
      taskName = $("li.contact.active a").find("p.name").text();



      $(".group_members_inline").remove();

      checkContactRole(contactRole);

      var currentActiveChatWindow = obj.hasClass("active");

      var dfd = $.Deferred();
      var promise = dfd.promise();

      if (!currentActiveChatWindow) {
        // Show skeleton only when switching contacts
        $(".messages ul").empty();
        generateMessageSkeleton('.messages .messages-skeleton', 12);

        if (createChatBoxRequest) {
          createChatBoxRequest.abort();
        }

        createChatBoxRequest = $.get(prchatSettings.getMessages, {
            from: userSessionId,
            to: contact_id,
            offset: 0,
            limit: 20
          })
          .done(function(r) {
            offsetPush = 10;
            r = JSON.parse(r);
            offsetPush += 10;
            dfd.resolve(r);
          })
          .fail(function() {
            dfd.resolve([]);
          })
          .always(function() {
            if ($("#no_messages").length) {
              $("#no_messages").remove();
            }
            createChatBoxRequest = null;
          });
      } else {
        dfd.resolve([]);
      }

      /*---------------* After users are fetched from database -> continue with loading *---------------*/
      promise.then(function(message) {
        var sliderimg = obj.find("img").prop("currentSrc");
        $("#frame .content .contact-profile img").prop("src", sliderimg);

        // Keep form id="pusherMessagesForm" so voice recorder / other code can find it; peer id is numeric contact_id
        $("#pusherMessagesForm input.to").val(contact_id);
        $("#pusherMessagesForm input.from").val(userSessionId);

        var batchHtml = '';
        $(message).each(function(i, value) {

          var previous_time = moment(message[i].time_sent).format("YYYY-MM-DD");
          if (message[i + 1] !== undefined) {
            var current_time = moment(message[i + 1].time_sent).format("YYYY-MM-DD");
          }

          if (value.message.startsWith("<?= _l('chat_message_announce'); ?>")) {
            value.message = "<strong class=\"italic\">" + value.message + "</strong>";
          }

          var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(value.message);
          var isViewed = value.viewed == 1;
          var msgHtml = '';

          if (value.sender_id == userSessionId) {
            msgHtml = createModernMessage({
              id: value.id,
              sender_id: value.sender_id,
              message: displayForRow,
              time_sent: value.time_sent,
              time_sent_formatted: value.time_sent_formatted,
              viewed: isViewed,
              viewed_at: value.viewed_at || '',
              viewed_at_formatted: value.viewed_at_formatted || '',
              user_image: value.user_image,
              reactions: value.reactions || null,
              edited_at: value.edited_at || null
            });
            if (previous_time !== current_time) {
              msgHtml = "<div class=\"modern-date-separator\">" + formatDateSeparatorLabel(value.time_sent) + "</div>" + msgHtml;
            }
          } else {
            if (!isViewed) {
              $(".has_newmessages").val("true").attr("id", value.sender_id);
            }
            msgHtml = createModernMessage({
              id: value.id,
              sender_id: value.sender_id,
              message: displayForRow,
              time_sent: value.time_sent,
              time_sent_formatted: value.time_sent_formatted,
              viewed: isViewed,
              user_image: value.user_image,
              reactions: value.reactions || null,
              edited_at: value.edited_at || null
            });
          }
          batchHtml = msgHtml + batchHtml;
        });
        // Hide skeleton, create container, inject messages
        $(".messages .messages-skeleton").addClass("hidden");
        $(".messages ul").html('<div class="modern-messages-container"></div><div class="modern-typing-indicator" style="display: none;" data-user-id=""><div class="modern-typing-bubble"><div class="modern-typing-dots"><span></span><span></span><span></span></div><div class="modern-typing-text">Typing...</div></div></div>');
        $(".messages .modern-messages-container").prepend(batchHtml);
        $(".group_members_inline").remove();
      });

      $(".messages").attr("id", id);

      $.when(promise.then())
        .then(function() {
          if ($(".messages").hasScrollBar() && $(window).width() > 733) {
            scrollToBottom();
            $(".message-input textarea.chatbox").focus();
          } else if ($(window).width() < 733) {
            // Due to mobile devices bug and loading time
            scrollToBottom();
          } else {
            // One last check for mobile devices
            scrollToBottom();
          }
        });

      $("#pusherMessagesForm input.from").val(userSessionId);
      $("#pusherMessagesForm input.to").val(contact_id);

      getSharedFiles(userSessionId, contact_id);

      $("#frame .nav.nav-tabs li.groups, #frame .nav.nav-tabs li.groups a, #frame .nav.nav-tabs li.crm_clients a").removeClass("events_disabled");

      return false;
    }

    /*--------------------  * send message & typing event to server  * ------------------- */

    // Typing indicator management (staff tab, mirror clients logic)
    var typingTimer = null;
    var isTyping = false;
    var TYPING_TIMER_LENGTH = 1500;
    var staffTypingTimerId = null;
    var STAFF_TYPING_CLEAR_MS = 2500;

    function sendTypingEvent(isTypingNow) {
      var activeContactId = $("li.contact.active").attr("id");

      if (activeContactId) {
        var formData = {
          msg: isTypingNow ? 'true' : '',
          from: userSessionId,
          to: activeContactId,
          typing: isTypingNow ? 'true' : 'false',
          ci_csrf_token: $('[name="ci_csrf_token"]').val() || ''
        };

        $.post(prchatSettings.serverPath, formData);
      }
    }

    $("#frame").on("keypress", "textarea.chatbox", function(e) {
      var form = $(this).parents("form");
      var imgPath = $("#sidepanel #profile #profile-img").prop("currentSrc");

      var input_from = form.find('input.from');

      if (e.which == 13 && !e.shiftKey) {

        e.preventDefault();

        // Intercept edit mode
        if ($(this).hasClass('prchat-editing') && typeof prchatSubmitEdit === 'function') {
          prchatSubmitEdit($(this));
          return false;
        }

        if (window.emojiPickerInstance && window.emojiPickerInstance.isVisible) {
          window.emojiPickerInstance.hide();
        }

        var message = $.trim($(this).val());

        if (message == "" || internetConnectionCheck() === false) {
          return false;
        }

        if (window.currentReplyData) {
          message = getReplyFormattedMessage(message);
          // Update the actual textarea value with formatted message
          $(this).val(message);
          clearReplyData(); // Clear reply after sending
        }

        var isQuickMention = (message.indexOf('quickMentionLink') !== -1 && message.indexOf('<a') !== -1);
        var rendered = isQuickMention ? PrchatSafeRenderer.render(message) : PrchatSafeRenderer.renderFromText(message);
        var chat_display_message = rendered.display;
        var sidebar_preview = rendered.sidebar;

        $("#contacts .contact.active").find(".wrap .meta .preview").text('<?php echo _l('chat_message_you'); ?>' + " " + sidebar_preview);

        // Update sidebar time when you send (moment relative time)
        var activeContactId = $("li.contact.active").attr("id");
        var sendTimeLabel = moment().fromNow();
        var $activeTimeAgo = $("#contacts .contact a#" + activeContactId).find(".wrap .meta .pull-right.time_ago");
        if ($activeTimeAgo.length === 0) {
          $("#contacts .contact a#" + activeContactId).find(".wrap .meta .meta-row-preview").append('<p class="pull-right time_ago">' + sendTimeLabel + '</p>');
        } else {
          $activeTimeAgo.html(sendTimeLabel);
        }

        // Move this contact to top of sidebar (most recent conversation)
        appendMemberToTop(activeContactId);

        // Generate unique message ID for this message
        var tempMessageId = 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Create modern message structure for new sent message - starts in "sending" state
        const newMessageData = {
          id: tempMessageId,
          sender_id: userSessionId,
          message: chat_display_message,
          time_sent_formatted: moment().format(prchatSettings.timeFormat),
          viewed: false,
          sending: true, // Start in sending state with clock icon animation
          user_image: null
        };

        // Ensure modern container exists with typing indicator
        $(".messages .messages-skeleton").addClass("hidden");
        if ($(".messages .modern-messages-container").length === 0) {
          $(".messages ul").html('<div class="modern-messages-container"></div><div class="modern-typing-indicator" style="display: none;" data-user-id=""><div class="modern-typing-bubble"><div class="modern-typing-dots"><span></span><span></span><span></span></div><div class="modern-typing-text">Typing...</div></div></div>');
        }
        $(".messages .modern-messages-container").append(createModernMessage(newMessageData));

        // Sender must stay the logged-in staff id; recipient is the active contact (never overwrite "from" with peer id)
        var recipientId = $("li.contact.active").attr("id");
        if (recipientId) {
          form.find('input.to').val(recipientId);
        } else {
          console.error('No active contact found for recipient ID');
          form.find('input.to').val("false");
        }
        input_from.val(userSessionId);

        // Stop typing state and ensure backend/recipient get typing=false
        clearTimeout(typingTimer);
        isTyping = false;
        sendTypingEvent(false);
        form.find("input[name=typing]").val("false");

        var formData = form.serializeArray();

        $.post(prchatSettings.serverPath, formData)
          .done(function(response) {
            var $sentMessage = $(".modern-messages-container").find('[data-message-id="' + tempMessageId + '"]');
            $sentMessage.removeClass('modern-message-sending');

            try {
              var result = typeof response === 'string' ? JSON.parse(response) : response;
              if (result && result.id) {
                $sentMessage.attr('data-message-id', result.id);
                $sentMessage.find('.optionsMore').attr('data-id', result.id);
              }
            } catch (e) {}

            var $statusIcon = $sentMessage.find('.modern-status-sending');
            $statusIcon
              .removeClass('modern-status-sending fa-clock')
              .addClass('modern-status-delivered fa-check')
              .attr('title', '<?= _l('chat_msg_delivered'); ?>')
              .attr('data-original-title', '<?= _l('chat_msg_delivered'); ?>');

            try {
              $statusIcon.tooltip('destroy');
            } catch (e) {}
            try {
              $statusIcon.tooltip('dispose');
            } catch (e) {}
            $statusIcon.tooltip();
          })
          .fail(function(xhr, status, error) {
            console.error('Failed to send message:', error, xhr.responseText);
            // Update message status to "failed"
            var $sentMessage = $(".modern-messages-container").find('[data-message-id="' + tempMessageId + '"]');
            $sentMessage.addClass('modern-message-failed');
            var $statusIcon = $sentMessage.find('.modern-status-sending');

            $statusIcon
              .removeClass('modern-status-sending fa-clock')
              .addClass('modern-status-failed fa-exclamation-circle text-danger');
          });
        $(this).val("");
        $(this).focus();
        scrollToBottom();

      } else {
        // Handle typing indicator
        if (!isTyping) {
          isTyping = true;
          sendTypingEvent(true);
        }

        // Reset the typing timer
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() {
          isTyping = false;
          sendTypingEvent(false);
        }, TYPING_TIMER_LENGTH);
      }
    });

    // Also handle keyup to stop typing when user stops
    $("#frame").on("keyup", "textarea.chatbox", function(e) {
      var textareaValue = $(this).val().trim();

      // If textarea is empty, immediately stop typing
      if (textareaValue === '' && isTyping) {
        clearTimeout(typingTimer);
        isTyping = false;
        sendTypingEvent(false);
        return;
      }

      // Reset the typing timer on keyup as well
      clearTimeout(typingTimer);
      typingTimer = setTimeout(function() {
        if (isTyping) {
          isTyping = false;
          sendTypingEvent(false);
        }
      }, TYPING_TIMER_LENGTH);
    });

    /*---------------* Update user latest message into database *---------------*/
    function updateLatestMessages(id) {
      $.post(prchatSettings.updateUnread, {
        id: id
      }).done(function(r) {
        if (r != "true") {
          return false;
        }

        // Check if there are any remaining unread staff messages
        var hasUnreadStaff = $("#contacts .contact .unread-notifications").filter(function() {
          var badge = $(this).attr("data-badge");
          return badge && badge !== "0" && parseInt(badge) > 0;
        }).length > 0;

        if (!hasUnreadStaff) {
          clearTabBadge('staff');
        }

        return true;
      });
    }

    /*---------------* Updating unread messages trigger and notification trigger *---------------*/
    function updateUnreadMessages(member_id) {
      member_id = $(member_id).attr("id");
      if (member_id) {
        updateLatestMessages(member_id);
        $("#contacts .contact a#" + member_id).find(".unread-notifications").attr("data-badge", "0");
      }
    }

    /*---------------* Additional checks for chatbox and unread message update control *---------------*/
    $("#frame").on("click", "textarea.chatbox, div.messages", function() {
      updateMessageToSeen();
    });

    /*---------------* Focus: scroll and mark seen (do not hide remote typing indicator here) *---------------*/
    $("#frame").on("focus", ".chatbox, .messages", function(e) {

      if (e.currentTarget.tagName == "TEXTAREA") {
        $(".messages").scrollTop($(".messages")[0].scrollHeight);
      }

      updateMessageToSeen();
      if ($(".tb")) {
        $(".tb").css({
          "font-weight": "normal",
          "color": "rgba(153, 153, 153, 1)"
        });
      }
    });

    function updateMessageToSeen() {
      var member_id = $("#sidepanel li.active a.active_chat").attr("id");

      if ($(".has_newmessages").attr("id") == member_id && $(".has_newmessages").val() == "true") {
        updateLatestMessages(member_id);
        $(".has_newmessages").val("false");
      }
    }

    /*---------------* Switch user chat theme *---------------*/
    $("body").on("click", "#_light", function() {
      chatSwitchTheme("light");
    });
    $("body").on("click", "#_dark", function() {
      chatSwitchTheme("dark");
    });

    function chatSwitchTheme(theme_name) {
      $.post(prchatSettings.switchTheme, {
        theme_name: theme_name
      }).done(function(r) {
        if (r.success !== false) {
          localStorage.prChatThemeMode = theme_name;
          location.reload();
        }
      });
    }

    /*---------------* Search members *---------------*/
    $("#frame #search").on("keyup", "#search_field", function() {
      var value = $(this).val().toLowerCase();
      $("#frame #contacts ul li").filter(function() {
        $(this).toggle($(this).children("a").find("p.name").text().toLowerCase().indexOf(value) > -1);
      });
    });

    /*---------------* Debug: Log all search field interactions *---------------*/


    /*---------------* On focus out clear out input field and show all members if not found in searchbox *---------------*/
    function clearSearchValues() {
      $("#frame #search_field").val("");
      $("#contacts ul li").filter(function() {
        $(this).css("display", "block");
        $("#profile").click();
      });
    }

    $("#frame").keyup("#search_field", function(e) {
      if (e.keyCode === 27) {
        clearSearchValues();
      }
    });

    /*---------------* Groups search functionality using main search field *---------------*/
    $("body").on("input", "#search_groups_field", function() {
      var searchTerm = $(this).val().toLowerCase().trim();
      var $groupsList = $(".chat_groups_list li");

      if (searchTerm === "") {
        // Show all groups when search is empty
        $groupsList.show();
      } else {
        // Filter groups based on search term
        $groupsList.each(function() {
          var groupName = $(this).find(".name").text().toLowerCase();
          var groupMembers = $(this).find(".preview").text().toLowerCase();

          if (groupName.includes(searchTerm) || groupMembers.includes(searchTerm)) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }
    });

    // Clear groups search on ESC key
    $("body").on("keydown", "#search_groups_field", function(e) {
      if (e.keyCode === 27) {
        $(this).val("");
        $(".chat_groups_list li").show();
      }
    });

    // Clear groups search on focus out
    $("body").on("focusout", "#search_groups_field", function() {
      $(this).val("");
      $(".chat_groups_list li").show();
    });

    (jQuery);



    /*---------------* Delete own messages function - Updated for modern structure *---------------*/
    function delete_chat_message(msg_id) {
      var contact_id = $("#contacts ul li").children("a.active_chat").attr("id");
      var selector = $(".modern-messages-container").find('[data-message-id="' + msg_id + '"]');

      $.post(prchatSettings.deleteMessage, {
        id: msg_id,
        contact_id: contact_id
      }).done(function(response) {
        if (response) {
          selector.remove();
          // Check if last element is a date separator and remove if needed
          let lastChildren = $(".modern-messages-container").children().last();
          if (lastChildren.hasClass("modern-date-separator")) {
            lastChildren.remove();
          }

          getSharedFiles(userSessionId, contact_id);

          // Update sidebar preview with last message
          updateSidebarAfterDelete('staff', contact_id);
        } else {
          selector.remove();
          alert_float("danger", "<?php echo _l('chat_error_float'); ?>");
        }
      });
    }

    /*---------------* Update sidebar preview after delete *---------------*/
    function updateSidebarAfterDelete(type, id) {
      var lastMessage = null;
      var lastMessageText = "";
      var sidebarSelector = "";

      if (type === 'staff') {
        // Get last message from THIS SPECIFIC staff conversation
        lastMessage = $(".messages#id_" + id + " .modern-messages-container .modern-message-item").last();
        sidebarSelector = ".chat_contacts_list li.contact a#" + id + " .wrap .meta .preview";
      } else if (type === 'group') {
        // Get last message from group conversation
        lastMessage = $(".chat_group_messages .modern-messages-container .modern-message-item").last();
        sidebarSelector = ".chat_groups_list li.group_selector#" + id + " .group_preview";
      } else if (type === 'client') {
        // Get last message from client conversation
        lastMessage = $(".chat_client_messages .modern-messages-container .modern-message-item").last();
        sidebarSelector = ".chat_clients_list li.contact_name#" + id + " .contact_preview";
      }

      if (lastMessage && lastMessage.length > 0) {
        var messageContent = lastMessage.find(".modern-message-text").html();

        if (messageContent) {
          var sidebar = PrchatSafeRenderer.getSidebarPreview(messageContent, messageContent);

          // Add "You: " prefix for own messages
          if (lastMessage.hasClass("modern-message-sent")) {
            sidebar = "<?= _l('chat_message_you'); ?> " + sidebar;
          }

          lastMessageText = sidebar;
        }
      } else {
        // No messages left
        if (type === 'group') {
          lastMessageText = "";
        } else {
          lastMessageText = "<?= _l('chat_say_hi'); ?>";
        }
      }

      // Update the sidebar
      if (sidebarSelector) {
        $(sidebarSelector).text(lastMessageText);
      }
    }

    /*---------------* Check contact/staff role and append *---------------*/
    function checkContactRole(contactRole) {
      var roleText = "";

      // Handle undefined, null, empty string, or "0" cases
      if (!contactRole || contactRole === "" || contactRole === "undefined" || contactRole === "null") {
        roleText = "<?= _l('chat_role_staff'); ?>";
      } else if (contactRole == "0") {
        roleText = "<?= _l('chat_role_administrator'); ?>";
      } else {
        roleText = contactRole;
      }

      // Update the role in the small tag
      $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown .staff-name-text small.contact_role").html(roleText);

      // Ensure arrow is still present after role update
      if ($("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown .dropdown-arrow").length === 0) {
        $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").append('<i class="fa fa-chevron-down dropdown-arrow"></i>');
      }
    }

    /*---------------* Init current chat loader synchronized with messages append *---------------*/
    /*---------------* Get current chat shared files *---------------*/
    window.getSharedFiles = function(own_id, contact_id) {
      $.post(prchatSettings.getSharedFiles, {
        own_id: own_id,
        contact_id: contact_id
      }).done(function(data) {
        $(".history_slider").html("");
        var html = "";
        try {
          html = JSON.parse(data);
        } catch (e) {
          html = data || "";
        }
        $(".history_slider").html(html);
      });
    }


    /*---------------* Truncate text message to user view left sidebar *---------------*/
    String.prototype.trunc = String.prototype.trunc ||
      function(n) {
        return (this.length > n) ? this.substr(0, n - 1) + "&hellip;" : this;
      };

    /*---------------* Scroll to bottom with improved reliability *---------------*/
    var scrollTimeout;

    window.scrollToBottom = function(forceScroll = false) {
      // Clear any pending scroll to prevent multiple rapid scrolls
      clearTimeout(scrollTimeout);

      scrollTimeout = setTimeout(function() {
        var m = $(".messages"),
          gm = $(".group_messages"),
          cm = $(".client_messages");

        // Only scroll if container is visible and has content
        if (m.is(":visible") && m.length && m[0].scrollHeight > m[0].clientHeight) {
          m.scrollTop(m[0].scrollHeight);
        }
        if (gm.is(":visible") && gm.length && gm[0].scrollHeight > gm[0].clientHeight) {
          gm.scrollTop(gm[0].scrollHeight);
        }
        if (cm.is(":visible") && cm.length && cm[0].scrollHeight > cm[0].clientHeight) {
          cm.scrollTop(cm[0].scrollHeight);
        }
      }, forceScroll ? 0 : 100); // Small delay to let content render, unless forced
    }

    /*---------------* For mobile devices vh ports adjust for better UX *---------------*/
    var vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", vh + "px");

    window.addEventListener("resize", function() {
      var vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty("--vh", vh + "px");
    });

    /*---------------* Theme events  *---------------*/
    $("#bottom-bar").on("click", "#switchTheme", function() {
      $(".dropdown-content").toggle(10);
    });

    /*---------------* Check if staff has permissions for settings  *---------------*/
    $("#settings").on("click", function() {
      <?php if (staff_can('view', 'settings')): ?>
        window.location = "<?php echo admin_url('settings?group=perfex_chat_settings'); ?>";
      <?php else: ?>
        alert_float("warning", "<?php echo _l('chat_settings_permission'); ?>");
      <?php endif; ?>
    });

    var isUserMobile = window.matchMedia("only screen and (max-width: 735px)").matches;
    /*---------------* Shared files on lick icon hide div with shared items  *---------------*/
    $("#sharedFiles").on("click", "i.fa-times-circle", function() {
      $("#sharedFiles").css(window.prchatSlidePanelClosedCss ? window.prchatSlidePanelClosedCss() : {
        "right": "-360px",
        "width": "360px"
      }, 10, "linear").hide(1000);
    });

    /*---------------* Event click tracker for options   *---------------*/
    $(".messages, .group_messages, .chat_client_messages, #contacts, textarea, #header, #menu").on("click", function(e) {
      // Don't interfere with audio/video controls
      if ($(e.target).closest("audio, video").length) return;
      $(".chat_group_options, #status-options").removeClass("active");
      $(".chat_group_options").css(window.prchatSlidePanelClosedCss ? window.prchatSlidePanelClosedCss() : {
        "right": "-360px",
        "width": "360px"
      }, 10, "linear").hide(500);
    });

    /*---------------* Modal create announcement handler  *---------------*/
    $(function() {
      $("#frame .dropdown .i_broadcast").on("click touchstart", function() {
        $("#chooseAnnouncement").modal({
          show: true
        });
      });

      $("body #staffAnnouncementModal").on("click touchstart", function() {
        $(".modal_container").load(prchatSettings.chatAnnouncement, function(res) {
          $("#_staffAnnouncementModal").modal({
            show: true
          });
          $("#chooseAnnouncement").modal("hide");
        });
      });

      $("body #clientsAnnouncementModal").on("click touchstart", function() {
        $(".modal_container").load(prchatSettings.sendClientsAnnouncement, function(res) {
          $("#_clientsAnnouncementModal").modal({
            show: true
          });
          $("#chooseAnnouncement").modal("hide");
        });
      });

      $("#frame .dropdown .quickMentions").on("click touchstart", function() {
        $(".modal_container").load(prchatSettings.quickMentions, function(res) {
          $("#quickMentionsModal").modal({
            show: true
          });
        });
      });
    });
    /*---------------* Modal create group handler  *---------------*/
    $("#frame .dropdown .i_groups, #frame #sidepanel #add_group_btn").on("click touchstart", function() {
      $(".modal_container").load(prchatSettings.chatGroups, function(res) {
        if ($("#chat_groups_custom_modal").is(":hidden")) {
          $("#chat_groups_custom_modal").modal({
            show: true
          });
        }
      });
    });


    /*---------------* Some cached variables for group chat  *---------------*/
    var chat_group_messages = $("#frame .content .group_messages .chat_group_messages");
    var chat_client_messages = $("#frame .content .client_messages .chat_client_messages");
    var chat_contact_profile_img = $("#frame .content .contact-profile img");
    var chat_social_media = $("#frame .content .social-media");
    var chat_content_messages = $("#frame .content .messages");
    var chat_content_client_messages = $("#frame .content .client_messages");

    var changeSearchField = function() {
      $("#search #search_clients_field").attr("id", "search_field");
      $("#search #search_groups_field").attr("id", "search_field");
      var $searchField = $("#search #search_field");

      $searchField.attr("placeholder", "<?= _l('chat_search_chat_members'); ?>").prop("disabled", false).prop("readonly", false).css({
        "opacity": "1",
        "cursor": "text",
        "background-color": ""
      });

      // Re-enable all interactions with the search field
      $searchField.off('focus blur click keyup keydown input');
      $(".chat_contacts_list li").show();

    };

    /*---------------* Click event for staff users sidebar  *---------------*/
    $("#frame #sidepanel .staff a").click(function() {
      // Show chat-add-btn when switching to staff tab
      $("#chat-add-btn").show();

      // Clear staff tab unread badge
      clearTabBadge('staff');

      $("#mainFilterDiv").show();

      // Set staff tab body class and ensure profile elements are visible
      $("body").removeClass("clients-tab groups-tab").addClass("staff-tab");

      $(".chat-action-voice-call, .chat-action-video-call").show();

      // Force show profile wrapper on staff tab
      $("#frame .content .contact-profile .profile-wrapper").show();

      // Make sure dropdown is closed
      $("#profile-dropdown").removeClass("show");
      $(".staff-name-dropdown").removeClass("dropdown-open");

      $(".chat_contacts_list li a").show();
      // hide groups form
      $("#frame form[name=groupMessagesForm],#frame form[name=clientMessagesForm], #frame .groupOptions").hide();

      $("#frame .chat_group_options.active").hide().removeClass("active").css(window.prchatSlidePanelClosedCss ? window.prchatSlidePanelClosedCss() : {
        "right": "-360px",
        "width": "360px"
      });
      if (typeof window.prchatResetStaffComposerFormCss === 'function') {
        window.prchatResetStaffComposerFormCss();
      }
      $("#frame form[name=pusherMessagesForm], #frame .startMic").show();
      $(".client_data").remove();

      // Hide other message containers and show staff messages
      $("#frame .content .group_messages, #frame .content .client_messages").hide();
      chat_content_messages.show();

      if ($(".group_members_inline").remove()) {

        chat_contact_profile_img.show();
        chat_contact_profile_img.next().show();
        chat_contact_profile_img.next().next().show();
        chat_social_media.show();

      }
      if (!optionsSelector.hasClass("active")) {
        optionsSelector.css("display", "");
      }

      clientsListCheck();

      changeSearchField();

      $(".group_members_inline").remove();

      // Skip auto-selecting first contact when deep-linking to a specific staff (floating notification)
      var urlParams = new URLSearchParams(window.location.search);
      var deepContact = urlParams.get('contact');
      var deepTab = urlParams.get('tab');
      if (deepTab === 'staff' && deepContact) {
        // Let deep-link interval select the correct contact; do not click first
      } else if (!window.matchMedia("only screen and (max-width: 735px)").matches) {
        var $firstContact = $("#frame #contacts ul li").first().children("a").first();
        $firstContact.click();
      }
    });
    /*---------------* Click event for groups sidebar  *---------------*/
    $("#frame #sidepanel .groups a").click(function() {
      // Hide chat-add-btn if user doesn't have permission to create groups
      if (staffCanCreateGroups === "0" && !isAdmin) {
        $("#chat-add-btn").hide();
      } else {
        $("#chat-add-btn").show();
      }

      // Fetch groups on first activation (deferred from page load)
      if (typeof window.fetchMyGroups === 'function') {
        if (!window._groupsLoaded) {
          // Show sidebar skeleton while groups are loading
          $("#groups_container .contacts-skeleton").removeClass("hidden");
          // Also show messages skeleton while loading
          $(".group_messages .messages-skeleton").removeClass("hidden");
        }
        window.fetchMyGroups();
      }

      // Clear tab badges when groups tab is clicked
      clearTabBadge('staff');
      clearTabBadge('groups');

      $("#mainFilterDiv").hide();

      // Set groups tab body class to hide profile elements
      $("body").removeClass("staff-tab clients-tab").addClass("groups-tab");

      $(".chat-action-voice-call").hide();
      <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
        $(".chat-action-video-call").show();
      <?php else: ?>
        $(".chat-action-video-call").hide();
      <?php endif; ?>

      // Force hide profile wrapper on groups tab
      $("#frame .content .contact-profile .profile-wrapper").hide();

      // Make sure dropdown is closed
      $("#profile-dropdown").removeClass("show");
      $(".staff-name-dropdown").removeClass("dropdown-open");

      // Disable search field for groups
      $("#search #search_field").attr("id", "search_groups_field");
      $("#search #search_clients_field").attr("id", "search_groups_field");
      var $groupsField = $("#search #search_groups_field");
      var isDarkMode = $("body").hasClass("chat_dark");
      $groupsField.attr("placeholder", "<?= _l('chat_search_disabled_on_groups'); ?>").prop("disabled", true).prop("readonly", true);
      $groupsField.addClass("tw-opacity-60 tw-cursor-not-allowed");
      if (isDarkMode) {
        $groupsField.css({
          "background-color": "#374151",
          "color": "#6b7280"
        });
      } else {
        $groupsField.css({
          "background-color": "#f5f5f5",
          "color": "#999"
        });
      }

      // Prevent all interactions with the search field on groups tab
      $groupsField.off('keyup keydown input focus blur click');
      $groupsField.on('focus blur click keyup keydown input', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).blur();
        return false;
      });

      // Hide staff chatbox form
      $("#frame form[name=pusherMessagesForm], #frame form[name=clientMessagesForm]").hide();

      $("#sharedFiles").hide().css(window.prchatSlidePanelClosedCss ? window.prchatSlidePanelClosedCss() : {
        "right": "-360px",
        "width": "360px"
      });
      $(".client_data").remove();

      // show groups form
      $("#frame .content .group_messages, #frame form[name=groupMessagesForm], #frame .startMic").show();

      // Initialize PrchatMentions for groups textarea
      if ($("textarea.group_chatbox").length && !$("textarea.group_chatbox").hasClass("mentions-initialized")) {
        if (window.PrchatMentions) {
          window.groupMentions = new PrchatMentions({
            input: 'textarea.group_chatbox',
            dropdown: '#group-mention-dropdown',
            members: [],
            labels: {
              staff: '<?= _l("chat_staff"); ?>',
              contact: '<?= _l("chat_lang_contact"); ?>'
            },
            placeholder: site_url + '/assets/images/user-placeholder.jpg'
          });
        }
        $("textarea.group_chatbox").addClass("mentions-initialized");
      }

      chat_content_messages.hide();
      chat_content_client_messages.hide();
      chat_contact_profile_img.hide();
      chat_contact_profile_img.next().hide();
      chat_contact_profile_img.next().next().hide();

      // If groups are already loaded, select active group and load its messages
      // If not yet loaded, fetchMyGroups() handles first group selection on completion
      if (window._groupsLoaded) {
        if ($(".chat_groups_list li").length == 0 || window.hasNoGroups) {
          chat_group_messages.html("");
          $(".contact-profile .groupOptions, .contact-profile .social-media").hide();
          $(".group_messages .messages-skeleton").addClass("hidden");
          $("#frame form[name=groupMessagesForm]").hide();
          showEmptyGroupsMessage();
        } else {
          var $activeGroup = $("#frame ul.chat_groups_list li.active");
          if ($activeGroup.length) {
            $("#frame form[name=groupMessagesForm]").show();
          } else if (!window.matchMedia("only screen and (max-width: 735px)").matches) {
            chat_group_messages.html("");
            $activeGroup = $(".chat_groups_list li.group_selector").first();
            if ($activeGroup.length) {
              $("#frame form[name=groupMessagesForm]").show();
              $activeGroup.click();
            }
          }
        }
      }
      // else: fetchMyGroups is loading and will handle first group selection

      clientsListCheck();
    });

    /**
     * Mobile device back button pressed on phone
     */
    window.addEventListener("hashchange", function(event) {
      if (window.matchMedia("only screen and (max-width: 735px)").matches && isContentActive) {
        $("body").find("#frame #sidepanel").fadeIn(50);
        $("body").find("#frame .content").hide(1);
        isContentActive = false;
      }
    });

    (function initSwipeBack() {
      var startX = 0, startY = 0, tracking = false;
      var frame = document.getElementById('frame');
      if (!frame) return;
      frame.addEventListener('touchstart', function(e) {
        if (!window.matchMedia('(max-width: 735px)').matches) return;
        if (!isContentActive) return;
        var t = e.touches[0];
        if (t.clientX < 40) { startX = t.clientX; startY = t.clientY; tracking = true; }
      }, { passive: true });
      frame.addEventListener('touchend', function(e) {
        if (!tracking) return;
        tracking = false;
        var t = e.changedTouches[0];
        var dx = t.clientX - startX;
        var dy = Math.abs(t.clientY - startY);
        if (dx > 60 && dy < 100) chatBackMobile();
      }, { passive: true });
    })();

    // Fix for height on the wrapper
    function chatPinnedProjectFix() {
      var pinned = $("li.pinned_project");
      if (pinned.length > 3 && !is_mobile()) {
        // Get and set current height
        var topHeaderHeight = 63;
        var navigationH = side_bar.height();
        var bodyContentHeight = $("#wrapper").find(".content").height();
        var content_wrapper = $("#sidepanel, #frame .content, body .main_loader_init, body #audio-wrapper");
        content_wrapper.css("min-height", $(document).outerHeight(true) - topHeaderHeight + "px");
        // Set new height when content height is less then navigation
        if (bodyContentHeight < navigationH) {
          content_wrapper.css("min-height", navigationH + "px");
        }

        // Set new height when content height is less then navigation and navigation is less then window
        if (bodyContentHeight < navigationH && navigationH < $(window).height()) {
          content_wrapper.css("min-height", $(window).height() - topHeaderHeight + "px");
        }
        // Set new height when content is higher then navigation but less then window
        if (bodyContentHeight > navigationH && bodyContentHeight < $(window).height()) {
          content_wrapper.css("min-height", $(window).height() - topHeaderHeight + "px");
        }
      }
    }

    chatPinnedProjectFix();

    var isHostHttps = '<?= $isHttps; ?>';

    if (!isHostHttps) {
      $(".startMic").remove();
    } else {
      $(".startMic").css("display", "block");
    }

    /*---------------* Search messages modal functionality *---------------*/

    // Skeleton HTML for instant modal display while content loads
    var historySkeletonHtml = '<div class="modal right fade" id="search_messages_modal" tabindex="-1" role="dialog">' +
      '<div class="modal-dialog modal-xl side-modal-dialog" role="any">' +
      '<div class="modal-content side-modal-content">' +
      '<div class="history-modal-header">' +
      '<div class="history-header-left"><div class="history-header-icon"><i class="fa fa-comments"></i></div>' +
      '<h4><?= _l("chat_messages_history_title"); ?></h4></div>' +
      '<button type="button" class="history-close-btn" data-dismiss="modal" aria-label="<?= _l("chat_close_button"); ?>"><i class="fa fa-times"></i></button></div>' +
      '<div class="history-modal-body">' +
      '<div class="history-skeleton-loading" style="padding:20px;">' +
      '<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">' +
      '<div style="width:48px;height:48px;border-radius:50%;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
      '<div><div style="width:140px;height:14px;border-radius:4px;background:#e5e7eb;margin-bottom:6px;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
      '<div style="width:80px;height:12px;border-radius:4px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div></div></div>' +
      '<div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:1px solid #e5e7eb;padding-bottom:12px;">' +
      '<div style="width:100px;height:32px;border-radius:6px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
      '<div style="width:80px;height:32px;border-radius:6px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
      '<div style="width:80px;height:32px;border-radius:6px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div></div>' +
      '<div style="width:100%;height:40px;border-radius:8px;background:#e5e7eb;margin-bottom:20px;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
      Array(6).fill(0).map(function(_, i) {
        var isRight = i % 3 === 0;
        var w = [65, 80, 50, 90, 70, 55][i];
        return '<div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;' + (isRight ? 'flex-direction:row-reverse;' : '') + '">' +
          '<div style="width:36px;height:36px;border-radius:50%;background:#e5e7eb;flex-shrink:0;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
          '<div style="flex:1;' + (isRight ? 'display:flex;flex-direction:column;align-items:flex-end;' : '') + '">' +
          '<div style="display:flex;gap:8px;margin-bottom:4px;' + (isRight ? 'flex-direction:row-reverse;' : '') + '">' +
          '<div style="width:80px;height:12px;border-radius:4px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
          '<div style="width:50px;height:12px;border-radius:4px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div></div>' +
          '<div style="width:' + w + '%;height:16px;border-radius:4px;background:#e5e7eb;animation:skeleton-pulse 1.5s ease-in-out infinite;"></div>' +
          '</div></div>';
      }).join('') +
      '</div></div></div></div></div>';

    function loadHistoryModal(userId, table) {
      if (!userId) {
        alert_float('warning', '<?= _l("chat_select_contact_first"); ?>');
        return;
      }

      // Show skeleton modal immediately
      $(".modal_container").html(historySkeletonHtml);
      $("#search_messages_modal").modal({
        show: true,
        backdrop: true,
        keyboard: true
      });

      // Load actual content in background, replace only the inner content
      $.post(prchatSettings.searchMessagesView, {
        id: userId,
        table: table
      }, function(res) {
        // Extract inner content from response and swap it in
        var $response = $('<div>').html(res);
        var $newModal = $response.find('#search_messages_modal');
        if ($newModal.length) {
          // Replace only modal-content to avoid backdrop issues
          $("#search_messages_modal .modal-content").replaceWith($newModal.find('.modal-content'));
          $response.find('script').each(function() {
            try {
              $.globalEval(this.textContent || this.innerText);
            } catch (e) {}
          });
        } else {
          // Fallback: response is the full page, close skeleton and re-render
          $("#search_messages_modal").modal('hide');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
          $(".modal_container").html(res);
          $("#search_messages_modal").modal({
            show: true,
            backdrop: true,
            keyboard: true
          });
        }
      });
    }

    function openSearchMessagesModal(element) {
      var isClientConversation = $('.client_messages').hasClass('isFocused');
      var userId, table;

      if (isClientConversation) {
        var activeClient = $('.client_messages').attr('id');
        if (activeClient) {
          userId = activeClient;
          table = 'chatclientmessages';
        }
      } else {
        var activeContact = $('li.contact.active').attr('id');
        if (activeContact) {
          userId = activeContact;
          table = 'chatmessages';
        }
      }

      loadHistoryModal(userId, table);
    }

    $("#frame .message-input .wrap .search_messages").on("click", function() {
      var id = $(".tab-pane.fade.active.in .contact.active a").attr("id");
      loadHistoryModal(id, "chatmessages");
    });

    $("body").on("hidden.bs.modal", "#search_messages_modal .modal", function() {
      $(this).removeData();
    });

    $("#frame .message-input .wrap .search_client_messages").on("click", function() {
      var id = $(".chat_clients_list > li.contact_name.selected").attr("id");
      loadHistoryModal("client_" + id, "chatclientmessages");
    });

    /**
     * Mark messages as seen pusher event
     */
    user_messages_events.bind("message_seen", function(messages) {

      var recieverId, senderId;

      if (Array.isArray(messages)) {
        for (var i = 0; i < messages.length; i++) {

          recieverId = messages[i].reciever_id;
          senderId = messages[i].sender_id;

          // Build tooltip with seen time using unified format
          var seenTooltip = formatSeenTooltip(messages[i].viewed_at, messages[i].viewed_at_formatted);

          /** Staff to Client messages - when client reads staff's message */
          if (senderId && senderId.startsWith("staff_")) {
            // Only process if current staff is the sender
            var staffSenderId = senderId.replace("staff_", "");
            if (staffSenderId == userSessionId) {
              // Get the currently active client conversation
              var activeClientId = $(".chat_clients_list > li.contact_name.selected").attr("id");
              var messageReceiverId = recieverId ? recieverId.replace("client_", "") : "";

              // Only update if viewing the same client conversation
              if (activeClientId && (activeClientId == messageReceiverId || !messageReceiverId)) {
                var $clientMessageContainer = $(".client_messages");
                var $modernClientMessages = $clientMessageContainer.find('.modern-message-sent');

                $modernClientMessages.each(function() {
                  var $statusIcon = $(this).find('.modern-status-delivered, .modern-status-sending');
                  if ($statusIcon.length > 0) {
                    $statusIcon.removeClass('modern-status-delivered modern-status-sending fa-check fa-clock')
                      .addClass('modern-status-read fa-check-double text-primary')
                      .attr('data-toggle', 'tooltip')
                      .attr('title', seenTooltip)
                      .attr('data-original-title', seenTooltip);
                    // Reinitialize tooltip (destroy for Bootstrap 3, dispose for Bootstrap 4+)
                    try {
                      $statusIcon.tooltip('destroy');
                    } catch (e) {}
                    try {
                      $statusIcon.tooltip('dispose');
                    } catch (e) {}
                    $statusIcon.tooltip();
                  }
                });

                // Also update messages that were already marked as read but without timestamp
                $clientMessageContainer.find('.modern-status-read').each(function() {
                  var $el = $(this);
                  var currentTitle = $el.attr('data-original-title') || $el.attr('title') || '';
                  if (currentTitle && !currentTitle.match(/\d{2}[:\-\/]\d{2}/)) {
                    $el.attr('title', seenTooltip).attr('data-original-title', seenTooltip);
                    try {
                      $el.tooltip('destroy');
                    } catch (e) {}
                    try {
                      $el.tooltip('dispose');
                    } catch (e) {}
                    $el.tooltip();
                  }
                });

                // Remove old-style indicators
                $clientMessageContainer.find("i.circle-unseen").remove();
              }
              userSeenNotify(userSessionId);
            }
            continue;
          }


          /** Staff to Staff - Update modern message status */
          if (messages[i].reciever_id == $("li.contact a.active_chat").attr("id") && messages[i].sender_id == userSessionId) {
            // Update modern message structure
            var $messageContainer = $(".messages#id_" + messages[i].reciever_id);
            var $modernMessages = $messageContainer.find('.modern-message-sent');

            $modernMessages.each(function() {
              var $statusIcon = $(this).find('.modern-status-delivered, .modern-status-sending');
              if ($statusIcon.length > 0) {
                $statusIcon.removeClass('modern-status-delivered modern-status-sending fa-check fa-clock')
                  .addClass('modern-status-read fa-check-double text-primary')
                  .attr('data-toggle', 'tooltip')
                  .attr('title', seenTooltip)
                  .attr('data-original-title', seenTooltip);
                // Reinitialize tooltip (destroy for Bootstrap 3, dispose for Bootstrap 4+)
                try {
                  $statusIcon.tooltip('destroy');
                } catch (e) {}
                try {
                  $statusIcon.tooltip('dispose');
                } catch (e) {}
                $statusIcon.tooltip();
              }
            });

            // Also update messages that were already marked as read but without timestamp
            $messageContainer.find('.modern-status-read').each(function() {
              var $el = $(this);
              var currentTitle = $el.attr('data-original-title') || $el.attr('title') || '';
              if (currentTitle && !currentTitle.match(/\d{2}[:\-\/]\d{2}/)) {
                $el.attr('title', seenTooltip).attr('data-original-title', seenTooltip);
                try {
                  $el.tooltip('destroy');
                } catch (e) {}
                try {
                  $el.tooltip('dispose');
                } catch (e) {}
                $el.tooltip();
              }
            });
          }
        };

        if (senderId && senderId.startsWith("staff")) {
          if (senderId.replace("staff_", "") == userSessionId) {
            userSeenNotify(userSessionId);
          }
        } else if (senderId) {
          if (senderId == userSessionId) {
            userSeenNotify(userSessionId);
          }
        }
      }
    });



    // Create new task from chat related to staff
    function chatNewTask(userInfo) {
      var user_name = $("body").find("a.active_chat p.name").text();
      var rel_id = $("body").find("a.active_chat").attr("id");
      var rel_type = "staff";

      if ($("body").find(".crm_clients").hasClass("active")) {
        user_name = $("body").find(".contact_name.selected .contact_name_main").text();
        rel_id = $("body").find(".chat_clients_list > li.contact_name.selected").attr("id");
        rel_type = "customer";

        // Debug and validate rel_id
        if (!rel_id || rel_id === undefined || rel_id === "") {
          rel_id = "0"; // Fallback to prevent syntax errors
        }
      }

      var url = admin_url + "tasks/task?rel_id=" + rel_id + "&rel_type=" + rel_type;

      new_task(url);

      var infoMessage = `<div class="alert alert-info" id="_infoMessage"><span><?= _l("chat_task_info_message") ?></span></div>`;

      $("#_task").on("show.bs.modal", function(e) {
        $("body").find("#_infoMessage").remove();

        if (rel_type == "customer") {
          $("body").find("#_task div[app-field-wrapper=\"name\"]").prepend(infoMessage);
        }

        $("body").find("#_task #rel_id_wrapper .rel_id_label").text("checked");
        $("body").find("#_task #name").val("<?= _l("chat_task_assign"); ?>" + "" + user_name + "");
        $("body").find("#_task #task_is_billable").attr("checked", false);
      });

      // Hide dropdown after action
      $("#profile-dropdown").removeClass("show");
      $(".staff-name-dropdown").removeClass("dropdown-open");
    }

    // Toggle profile dropdown
    function toggleProfileDropdown(element) {
      var dropdown = $("#profile-dropdown");
      var staffNameElement = $(element).closest('.staff-name-dropdown');

      dropdown.toggleClass("show");
      staffNameElement.toggleClass("dropdown-open");

      $(document).off('click.profile-dropdown').on('click.profile-dropdown', function(e) {
        if (!$(e.target).closest('.profile-dropdown').length && !$(e.target).closest('.dropdown-arrow').length) {
          dropdown.removeClass("show");
          staffNameElement.removeClass("dropdown-open");
          $(document).off('click.profile-dropdown');
        }
      });
    }

    // Close dropdown after clicking any item
    $(document).on('click', '.profile-dropdown ul li a', function() {
      $("#profile-dropdown").removeClass("show");
      $(".staff-name-dropdown").removeClass("dropdown-open");
    });

    // Expose functions globally for inline onclick handlers
    window.chatNewTask = chatNewTask;
    window.toggleProfileDropdown = toggleProfileDropdown;

    // Initialize proper body classes on page load
    $(document).ready(function() {
      // Force all tooltips inside #frame to render on body so they are not clipped by overflow:hidden
      $.fn.tooltip.Constructor.DEFAULTS ?
        $.fn.tooltip.Constructor.DEFAULTS.container = 'body' // Bootstrap 3
        :
        ($.fn.tooltip.Constructor.Default && ($.fn.tooltip.Constructor.Default.container = 'body')); // Bootstrap 4/5

      // Check which tab is currently active and set appropriate body class + container visibility
      if ($("#frame #sidepanel .staff a").parent().hasClass("active")) {
        $("body").addClass("staff-tab");
        $(".chat-action-voice-call, .chat-action-video-call").show();
        $("#frame .content .group_messages, #frame .content .client_messages").hide();
        $("#frame .content .messages").show();
      } else if ($("#frame #sidepanel .groups a").parent().hasClass("active")) {
        $("body").addClass("groups-tab");
        $(".chat-action-voice-call").hide();
        <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
          $(".chat-action-video-call").show();
        <?php else: ?>
          $(".chat-action-video-call").hide();
        <?php endif; ?>
        $("#frame .content .messages, #frame .content .client_messages").hide();
        $("#frame .content .group_messages").show();
      } else if ($("#frame #sidepanel .crm_clients a").parent().hasClass("active")) {
        $("body").addClass("clients-tab");
        <?php if (get_option('chat_client_calls_enabled') == '1'): ?>
          $(".chat-action-voice-call").show();
        <?php else: ?>
          $(".chat-action-voice-call").hide();
        <?php endif; ?>
        <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
          $(".chat-action-video-call").show();
        <?php else: ?>
          $(".chat-action-video-call").hide();
        <?php endif; ?>
        $("#frame .content .messages, #frame .content .group_messages").hide();
        $("#frame .content .client_messages").show();
      }

      // Make sure dropdown is closed by default
      $("#profile-dropdown").removeClass("show");
      $(".staff-name-dropdown").removeClass("dropdown-open");

      // Handle deep-link from floating notification (e.g. ?tab=crm_clients&contact=5 or ?tab=groups&group=123)
      var urlParams = new URLSearchParams(window.location.search);
      var deepTab = urlParams.get('tab');
      var deepContact = urlParams.get('contact');
      var deepGroup = urlParams.get('group');
      var allowedTabs = ['staff', 'crm_clients', 'groups'];
      if (deepTab && allowedTabs.indexOf(deepTab) !== -1) {
        setTimeout(function() {
          $("#frame #sidepanel ." + deepTab + " a").click();
          if (deepContact) {
            // Retry clicking the contact until it appears (async load)
            var attempts = 0;
            var maxAttempts = 20;
            var trySelectContact = setInterval(function() {
              attempts++;
              var contactEl;
              if (deepTab === 'crm_clients') {
                contactEl = $(".chat_clients_list li.contact_name[id='" + deepContact + "']");
              } else if (deepTab === 'staff') {
                // Use attribute selector (numeric IDs break #id in jQuery); same container as staff list
                contactEl = $("#frame #contacts ul li.contact[id='" + deepContact + "'] a");
              } else {
                contactEl = $("li.contact a#" + deepContact);
              }
              if (contactEl.length > 0) {
                clearInterval(trySelectContact);
                contactEl.first().trigger('click');
              } else if (attempts >= maxAttempts) {
                clearInterval(trySelectContact);
              }
            }, 300);
          }
          if (deepGroup && deepTab === 'groups') {
            var attempts = 0;
            var maxAttempts = 20;
            var trySelectGroup = setInterval(function() {
              attempts++;
              var groupEl = $(".chat_groups_list li.group_selector#" + deepGroup);
              if (groupEl.length > 0) {
                clearInterval(trySelectGroup);
                groupEl.click();
              } else if (attempts >= maxAttempts) {
                clearInterval(trySelectGroup);
              }
            }, 300);
          }
        }, 300);
      }
    });
  </script>



  <!-- XSS Sanitizer + Unified Message Renderer (must load before modules that use it) -->
  <script src="<?= base_url('modules/prchat/assets/js/vendor/purify.min.js?v=' . VERSIONING); ?>"></script>
  <script src="<?= base_url('modules/prchat/assets/js/prchat-safe-renderer.js?v=' . VERSIONING . '.3'); ?>"></script>

  <!-- Include drag and drop file -->
  <?php require('modules/prchat/assets/module_includes/chat_drag_drop.php'); ?>

  <!-- Include image paste handler -->
  <?php require('modules/prchat/assets/module_includes/image_paste.php'); ?>

  <!-- Include chat groups file -->
  <?php require('modules/prchat/assets/module_includes/groups.php'); ?>



  <?php
  $clients_enabled = isClientsEnabled();
  $staff_can_access = staffCanAccessClientsTab();

  if ($clients_enabled && $staff_can_access) {
    require('modules/prchat/assets/module_includes/crm_clients.php');
  }
  ?>

  <script>
    $(document).ready(function() {
      // Composer inline actions
      $(document).on('click', '.chat-action-upload', function() {
        $('#frame').find('form[name="fileForm"] input:first').click();
      });

      $(document).on('click', '.chat-action-upload-client', function() {
        $('#frame').find('form[name="clientFileForm"] input:first').click();
      });

      $(document).on('click', '.chat-action-upload-group', function() {
        $('#frame').find('form[name="groupFileForm"] input:first').click();
      });

      $(document).on('click', '.chat-action-emoji', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleEmojiPicker(this);
      });

      $(document).on('click', '.chat-action-group-settings', function() {
        if (typeof showGroupChatOptions === 'function') {
          showGroupChatOptions();
        }
      });

      // Header actions
      $(document).on('click', '.chat-header-search', function() {
        openSearchMessagesModal(this);
      });

      // Voice call handler
      $(document).on('click', '.chat-action-voice-call', function(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
          if (typeof ChatCallManager === 'undefined') {
            console.error('ChatCallManager not loaded');
            alert_float('danger', 'Call system not initialized');
            return;
          }

          // Determine if we're calling a client or staff member
          var isClientCall = $("body").hasClass("clients-tab");
          var peerId, displayName, avatarUrl;

          if (isClientCall) {
            // Get selected client from clients tab
            var activeClient = $(".chat_clients_list > li.contact_name.selected");
            if (!activeClient.length) {
              alert_float('warning', '<?= _l('chat_select_contact_first'); ?>');
              return;
            }
            peerId = activeClient.attr('id');
            displayName = activeClient.find('.contact_name_main').text().trim();
            avatarUrl = activeClient.find('.chatContactImage').first().attr('src') || null;
          } else {
            // Get selected staff member
            var activeAnchor = $("#contacts a.active_chat");
            if (!activeAnchor.length) {
              alert_float('warning', 'Please select a contact first');
              return;
            }
            peerId = activeAnchor.attr('id');
            if (!peerId) {
              alert_float('warning', 'Cannot identify contact');
              return;
            }
            peerId = peerId.replace('user_', '');
            peerId = parseInt(peerId, 10);
            var $contactEl = $("#contacts").find('a#user_' + peerId + ', a#' + peerId + ', li.contact#' + peerId).first();
            avatarUrl = $contactEl.find('img').first().attr('src') || null;
            displayName = ($contactEl.find('.name').first().text() || '').trim();
          }

          if (isClientCall) {
            peerId = parseInt(String(peerId).replace(/^client_/i, ''), 10);
          }

          if (!peerId || peerId < 1) {
            alert_float('warning', 'Cannot identify contact');
            return;
          }

          // Initialize call manager if not exists
          if (!window.__chatCallManager) {
            window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
          }

          // Set up remote audio handler
          window.__chatCallManager.onRemoteTrack = function(stream) {
            var audio = document.getElementById('chat-call-remote');
            if (!audio) {
              audio = document.createElement('audio');
              audio.id = 'chat-call-remote';
              audio.autoplay = true;
              document.body.appendChild(audio);
            }
            audio.srcObject = stream;
          };

          if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert_float('danger', 'Your browser does not support audio calls');
            return;
          }

          window.__CHAT_CALL_STATE = 'outgoing';
          window.__CHAT_CALL_PEER = peerId;
          window.__CHAT_CALL_IS_CALLER = true;
          window.__CHAT_CALL_RECIPIENT_TYPE = isClientCall ? 'client' : 'staff';
          window.__CHAT_CALL_WAS_VIDEO = false;

          var callOpts = {
            recipientType: isClientCall ? 'client' : 'staff',
            callerType: 'staff'
          };
          window.__chatCallManager.call(userSessionId, peerId, false, callOpts).then(function() {
            window.__chatNoAnswerTimer = setTimeout(function() {
              if (window.__CHAT_CALL_STATE === 'outgoing') {
                var missedPeer = window.__CHAT_CALL_PEER;
                window.__CHAT_CALL_STATE = 'idle';
                window.__CHAT_CALL_PEER = null;
                if (window.__chatCallManager) window.__chatCallManager.hangup();
                if (window.ChatCallUI) {
                  ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                  ChatCallUI.closeModal && ChatCallUI.closeModal();
                  ChatCallUI.showNotice && ChatCallUI.showNotice('No answer', {
                    autoCloseMs: 2000
                  });
                }
                if (missedPeer) sendMissedCallMessage(missedPeer);
              }
            }, 30000);
          }).catch(function(err) {
            console.error('Failed to start call:', err);
            window.__CHAT_CALL_STATE = 'idle';
            window.__CHAT_CALL_PEER = null;
            if (window.__chatCallManager) window.__chatCallManager.hangup();
            if (window.ChatCallUI) {
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              ChatCallUI.closeModal && ChatCallUI.closeModal();
            }
            alert_float('danger', 'Failed to start call');
          });

          if (window.ChatCallUI) {
            ChatCallUI.showOutgoingCall(displayName || 'User', function() {
              if (window.__chatNoAnswerTimer) {
                clearTimeout(window.__chatNoAnswerTimer);
                window.__chatNoAnswerTimer = null;
              }
              var cancelledPeer = window.__CHAT_CALL_PEER;
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              localStorage.removeItem('prchat_call_state');
              if (window.__chatCallManager) window.__chatCallManager.hangup();
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              if (cancelledPeer) sendMissedCallMessage(cancelledPeer);
            }, {
              avatarUrl: avatarUrl,
              isVideo: false
            });
          }
        } catch (err) {
          console.error('Call error:', err);
          window.__CHAT_CALL_STATE = 'idle';
        }
      });

      // Video call handler — mirrors voice call handler; no getUserMedia pre-test
      $(document).on('click', '.chat-action-video-call', function(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
          if (typeof ChatCallManager === 'undefined') {
            console.error('ChatCallManager not loaded');
            alert_float('danger', 'Call system not initialized');
            return;
          }

          var isClientCall = $("body").hasClass("clients-tab");
          var peerId, displayName, avatarUrl;

          if (isClientCall) {
            var activeClient = $(".chat_clients_list > li.contact_name.selected");
            if (!activeClient.length) {
              alert_float('warning', '<?= _l('chat_select_contact_first'); ?>');
              return;
            }
            peerId = activeClient.attr('id');
            displayName = activeClient.find('.contact_name_main').text().trim();
            avatarUrl = activeClient.find('.chatContactImage').first().attr('src') || null;
          } else {
            var activeAnchor = $("#contacts a.active_chat");
            if (!activeAnchor.length) {
              alert_float('warning', 'Please select a contact first');
              return;
            }
            peerId = activeAnchor.attr('id');
            if (!peerId) {
              alert_float('warning', 'Cannot identify contact');
              return;
            }
            peerId = peerId.replace('user_', '');
            peerId = parseInt(peerId, 10);
            var $contactEl = $("#contacts").find('a#user_' + peerId + ', a#' + peerId + ', li.contact#' + peerId).first();
            avatarUrl = $contactEl.find('img').first().attr('src') || null;
            displayName = ($contactEl.find('.name').first().text() || '').trim();
          }

          if (isClientCall) {
            peerId = parseInt(String(peerId).replace(/^client_/i, ''), 10);
          }

          if (!peerId || peerId < 1) {
            alert_float('warning', 'Cannot identify contact');
            return;
          }

          if (!window.__chatCallManager) {
            window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
          }

          window.__chatCallManager.onLocalTrack = function(stream) {
            var pip = document.getElementById('chat-call-pip');
            var localVideo = document.getElementById('chat-call-local-video');
            if (!localVideo) {
              localVideo = document.createElement('video');
              localVideo.id = 'chat-call-local-video';
              localVideo.autoplay = true;
              localVideo.playsinline = true;
              localVideo.muted = true;
              (pip || document.body).appendChild(localVideo);
            }
            localVideo.srcObject = stream;
            localVideo.style.display = 'block';
            localVideo.play().catch(function(err) {
              console.error('[VIDEO CALL] Error playing local video:', err);
            });
          };

          window.__chatCallManager.onRemoteTrack = function(stream) {
            var pip = document.getElementById('chat-call-pip');
            var video = document.getElementById('chat-call-remote-video');
            if (!video) {
              video = document.createElement('video');
              video.id = 'chat-call-remote-video';
              video.autoplay = false;
              video.playsinline = true;
              video.muted = false;
              if (pip) pip.insertBefore(video, pip.firstChild);
              else document.body.appendChild(video);
            }
            video.onloadedmetadata = null;
            if (video.srcObject) {
              video.pause();
              video.srcObject = null;
            }
            video.srcObject = stream;
            video.style.display = 'block';
            video.onloadedmetadata = function() {
              video.play().catch(function(err) {
                console.error('[VIDEO CALL] Error playing remote video:', err);
              });
            };
          };

          window.__CHAT_CALL_STATE = 'outgoing';
          window.__CHAT_CALL_PEER = peerId;
          window.__CHAT_CALL_IS_CALLER = true;
          window.__CHAT_CALL_RECIPIENT_TYPE = isClientCall ? 'client' : 'staff';
          window.__CHAT_CALL_WAS_VIDEO = true;

          var callOpts = {
            recipientType: isClientCall ? 'client' : 'staff',
            callerType: 'staff'
          };
          window.__chatCallManager.call(userSessionId, peerId, true, callOpts).then(function() {
            window.__chatNoAnswerTimer = setTimeout(function() {
              if (window.__CHAT_CALL_STATE === 'outgoing') {
                var missedPeer = window.__CHAT_CALL_PEER;
                window.__CHAT_CALL_STATE = 'idle';
                window.__CHAT_CALL_PEER = null;
                if (window.__chatCallManager) window.__chatCallManager.hangup();
                if (window.ChatCallUI) {
                  ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                  ChatCallUI.closeModal && ChatCallUI.closeModal();
                  ChatCallUI.showNotice && ChatCallUI.showNotice('No answer', {
                    autoCloseMs: 2000
                  });
                }
                var pipEl = document.getElementById('chat-call-pip');
                if (pipEl) pipEl.parentNode.removeChild(pipEl);
                if (missedPeer) sendMissedCallMessage(missedPeer);
              }
            }, 30000);
          }).catch(function(err) {
            console.error('Failed to start video call:', err);
            window.__CHAT_CALL_STATE = 'idle';
            window.__CHAT_CALL_PEER = null;
            if (window.__chatCallManager) window.__chatCallManager.hangup();
            if (window.ChatCallUI) {
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              ChatCallUI.closeModal && ChatCallUI.closeModal();
            }
            alert_float('danger', 'Failed to start video call');
          });

          if (window.ChatCallUI) {
            ChatCallUI.showOutgoingCall(displayName || 'User', function() {
              if (window.__chatNoAnswerTimer) {
                clearTimeout(window.__chatNoAnswerTimer);
                window.__chatNoAnswerTimer = null;
              }
              var cancelledPeer = window.__CHAT_CALL_PEER;
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              localStorage.removeItem('prchat_call_state');
              if (window.__chatCallManager) window.__chatCallManager.hangup();
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              var pipEl = document.getElementById('chat-call-pip');
              if (pipEl) pipEl.parentNode.removeChild(pipEl);
              if (cancelledPeer) sendMissedCallMessage(cancelledPeer);
            }, {
              avatarUrl: avatarUrl,
              isVideo: true
            });
          }
        } catch (err) {
          console.error('Video call error:', err);
          window.__CHAT_CALL_STATE = 'idle';
        }
      });

      // GLOBAL FUNCTIONS FOR COMPATIBILITY WITH GENERATED HTML
      window.openImageModal = function(imageUrl, filename) {
        // Create a simple image modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            position: relative;
            max-width: 90%;
            max-height: 90%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        `;

        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = filename || 'Image';
        img.style.cssText = `
            width: 100%;
            height: auto;
            max-width: 100%;
            max-height: 80vh;
            display: block;
        `;

        const toolbar = document.createElement('div');
        toolbar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.4));
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 15px;
        `;

        const downloadBtn = document.createElement('a');
        downloadBtn.href = imageUrl;
        downloadBtn.download = filename || 'image';
        downloadBtn.innerHTML = '⬇️ Download';
        downloadBtn.style.cssText = `
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: #007bff;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        `;
        downloadBtn.onmouseover = () => downloadBtn.style.background = '#0056b3';
        downloadBtn.onmouseout = () => downloadBtn.style.background = '#007bff';

        const openBtn = document.createElement('a');
        openBtn.href = imageUrl;
        openBtn.target = '_blank';
        openBtn.innerHTML = '🔗 Open';
        openBtn.style.cssText = `
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: #28a745;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        `;
        openBtn.onmouseover = () => openBtn.style.background = '#1e7e34';
        openBtn.onmouseout = () => openBtn.style.background = '#28a745';

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '✕';
        closeBtn.style.cssText = `
            color: white;
            background: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        `;
        closeBtn.onmouseover = () => closeBtn.style.background = '#c82333';
        closeBtn.onmouseout = () => closeBtn.style.background = '#dc3545';

        toolbar.appendChild(downloadBtn);
        toolbar.appendChild(openBtn);
        toolbar.appendChild(closeBtn);
        content.appendChild(img);
        content.appendChild(toolbar);
        modal.appendChild(content);

        // Close modal events
        const closeModal = () => {
          if (modal && modal.parentNode) {
            document.body.removeChild(modal);
          }
          document.removeEventListener('keydown', handleKeydown);
        };

        closeBtn.onclick = closeModal;
        modal.onclick = (e) => {
          if (e.target === modal) closeModal();
        };

        // ESC key to close
        const handleKeydown = (e) => {
          if (e.key === 'Escape') {
            closeModal();
          }
        };
        document.addEventListener('keydown', handleKeydown);

        // Prevent clicks on content from closing modal
        content.onclick = (e) => e.stopPropagation();

        document.body.appendChild(modal);
      };
    });
  </script>

  <!-- Include Custom Emoji System -->
  <script src="<?= base_url('modules/prchat/assets/js/custom-emoji-system.js?v=' . VERSIONING); ?>"></script>
  <script src="<?= base_url('modules/prchat/assets/js/emoji-picker.js?v=' . VERSIONING); ?>"></script>

  <script>
    // Initialize video embed processing when messages are loaded
    $(document).ready(function() {
      // Process existing messages for video embeds
      if (typeof PrChatVideoEmbed !== 'undefined') {
        // Try multiple selectors to find the messages container
        const messagesContainer = document.getElementById('messages') ||
          document.querySelector('.messages') ||
          document.querySelector('#frame .content .contact-profile') ||
          document.querySelector('#frame .content');

        if (messagesContainer) {
          PrChatVideoEmbed.convertVideoLinks(messagesContainer);
        } else {
          // Process the entire chat frame as fallback
          const chatFrame = document.getElementById('frame');
          if (chatFrame) {
            PrChatVideoEmbed.convertVideoLinks(chatFrame);
          }
        }
      }
    });

    // Hook into message processing to convert video links
    if (typeof MutationObserver !== 'undefined') {
      const chatContentObserver = new MutationObserver(function(mutations) {
        if (typeof PrChatVideoEmbed !== 'undefined') {
          mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
              mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                  setTimeout(function() {
                    PrChatVideoEmbed.convertVideoLinks(node);
                  }, 100);
                }
              });
            }
          });
        }
      });

      // Start observing the chat content area
      const chatContent = document.querySelector('#frame .content');
      if (chatContent) {
        chatContentObserver.observe(chatContent, {
          childList: true,
          subtree: true
        });
      }
    }

    // Process video links when messages are loaded via AJAX
    $(document).ajaxComplete(function(event, xhr, settings) {
      if (settings.url && settings.url.includes('getMessages')) {
        setTimeout(function() {
          if (typeof PrChatVideoEmbed !== 'undefined') {
            const chatContent = document.querySelector('#frame .content .contact-profile') ||
              document.querySelector('#frame .content');
            if (chatContent) {
              PrChatVideoEmbed.convertVideoLinks(chatContent);
            }
          }
        }, 500);
      }
    });

    /*---------------* Sort pinned contacts to top of a list *---------------*/
    function sortPinnedToTop(containerSelector, pinnedIds) {
      if (!pinnedIds || pinnedIds.length === 0) return;
      var $container = $(containerSelector);
      for (var i = pinnedIds.length - 1; i >= 0; i--) {
        var $el = $container.children("[id='" + pinnedIds[i] + "']");
        if ($el.length) {
          $el.attr("data-pinned", "1").detach().prependTo($container);
        }
      }
    }

    /*---------------* Context menu for contacts (staff, groups, clients) *---------------*/
    var $contextMenu = $('<div class="chat-context-menu" id="chatContextMenu"></div>').appendTo('body');

    function buildContextMenuHtml(targetId, type, pinnedList, mutedList) {
      var isPinned = pinnedList.indexOf(String(targetId)) !== -1;
      var isMuted = mutedList.indexOf(String(targetId)) !== -1;
      var html = '';
      html += '<div class="ctx-item" data-action="pin" data-id="' + targetId + '" data-type="' + type + '">';
      html += '<i class="fa fa-thumb-tack"></i>';
      html += isPinned ? '<?php echo _l("chat_unpin_conversation"); ?>' : '<?php echo _l("chat_pin_conversation"); ?>';
      html += '</div>';
      html += '<div class="ctx-item" data-action="mute" data-id="' + targetId + '" data-type="' + type + '">';
      html += '<i class="fa ' + (isMuted ? 'fa-bell' : 'fa-bell-slash') + '"></i>';
      html += isMuted ? '<?php echo _l("chat_unmute_conversation"); ?>' : '<?php echo _l("chat_mute_conversation"); ?>';
      html += '</div>';
      return html;
    }

    $(document).on("contextmenu", "#contacts li.contact", function(e) {
      e.preventDefault();
      var contactId = $(this).attr("id");
      if (!contactId) return;
      $contextMenu.html(buildContextMenuHtml(contactId, "staff", pinnedStaff, mutedStaff))
        .addClass("visible").css({
          top: Math.min(e.pageY, $(window).height() - 100) + "px",
          left: Math.min(e.pageX, $(window).width() - 200) + "px"
        });
    });

    $(document).on("contextmenu", ".group_selector", function(e) {
      e.preventDefault();
      var groupId = $(this).attr("id");
      if (!groupId) return;
      $contextMenu.html(buildContextMenuHtml(groupId, "groups", pinnedGroups, mutedGroups))
        .addClass("visible").css({
          top: Math.min(e.pageY, $(window).height() - 100) + "px",
          left: Math.min(e.pageX, $(window).width() - 200) + "px"
        });
    });

    $(document).on("contextmenu", ".chat_clients_list > li.contact_name", function(e) {
      e.preventDefault();
      var contactId = $(this).attr("id");
      if (!contactId) return;
      $contextMenu.html(buildContextMenuHtml(contactId, "clients", pinnedClients, mutedClients))
        .addClass("visible").css({
          top: Math.min(e.pageY, $(window).height() - 100) + "px",
          left: Math.min(e.pageX, $(window).width() - 200) + "px"
        });
    });

    $(document).on("click", function() {
      $contextMenu.removeClass("visible");
    });

    $(document).on("click", ".chat-context-menu .ctx-item", function(e) {
      e.stopPropagation();
      var action = $(this).data("action");
      var targetId = String($(this).data("id"));
      var type = $(this).data("type");
      $contextMenu.removeClass("visible");

      var url = action === "pin" ? prchatSettings.togglePin : prchatSettings.toggleMute;
      $.post(url, {
        type: type,
        target_id: targetId
      }, function(res) {
        if (!res || !res.success) return;
        var ids = (res.ids || []).map(String);
        var isActive = action === "pin" ? res.pinned : res.muted;

        // Update local arrays
        if (type === "staff") {
          if (action === "pin") pinnedStaff = ids;
          else mutedStaff = ids;
        } else if (type === "groups") {
          if (action === "pin") pinnedGroups = ids;
          else mutedGroups = ids;
        } else if (type === "clients") {
          if (action === "pin") pinnedClients = ids;
          else mutedClients = ids;
        }

        // Update DOM icons
        refreshPinMuteIcons(type, targetId, isActive, action);

        if (action === "mute") {
          alert_float('success', isActive ? '<?php echo _l("chat_muted"); ?>' : '<?php echo _l("chat_unmute_conversation"); ?>');
        }
      }, 'json');
    });

    function refreshPinMuteIcons(type, targetId, isActive, actionType) {
      var $el;
      // Use attribute selector for reliable matching (numeric IDs break #id syntax)
      if (type === "staff") $el = $("#contacts li.contact[id='" + targetId + "']");
      else if (type === "groups") $el = $(".group_selector[id='" + targetId + "']");
      else if (type === "clients") $el = $(".chat_clients_list > li.contact_name[id='" + targetId + "']");
      if (!$el || !$el.length) return;

      if (actionType === 'pin') {
        $el.find(".contact-pin-icon").remove();
        if (isActive) {
          $el.prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
          $el.attr("data-pinned", "1");
          if (type === "staff") $el.detach().prependTo("#contacts ul");
          else if (type === "groups") $el.detach().prependTo(".chat_groups_list");
          else if (type === "clients") $el.detach().prependTo(".chat_clients_list");
        } else {
          $el.removeAttr("data-pinned");
        }
      } else if (actionType === 'mute') {
        $el.find(".contact-mute-icon").remove();
        if (isActive) {
          $el.prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
        }
      }
    }

    /*--------------------------------* Menu active in sidebar *--------------------------------*/
    document.addEventListener('DOMContentLoaded', function() {
      var menuItem = document.querySelector('a[href*="Prchat_Controller/chat_full_view"]');
      menuItem.closest('li').classList.add('active');
    });

    $(document).on('click', '.messages [data-chat-file="image"]', function(e) {
      e.preventDefault();
      var imageUrl = $(this).attr('data-file-url') || $(this).attr('href');
      var filename = $(this).attr('data-filename') || '';
      if (imageUrl && typeof window.openImageModal === 'function') {
        window.openImageModal(imageUrl, filename);
      }
    });

    $(document).on('click', '.messages .message-reply-context', function(e) {
      e.preventDefault();
      var originalId = $(this).attr('data-original-message-id');
      if (!originalId || originalId === '0') return;
      var targetMsg = $('.messages').find('[data-message-id="' + originalId + '"]');
      if (targetMsg.length) {
        targetMsg[0].scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        targetMsg.css({
          transition: 'transform 0.3s ease',
          transform: 'scale(1.02)'
        });
        var bubble = targetMsg.find('.modern-message-bubble');
        if (bubble.length) {
          bubble.css({
            transition: 'border-color 0.3s, box-shadow 0.3s',
            borderColor: '#f59e0b',
            boxShadow: '0 0 0 2px rgba(245,158,11,0.3)'
          });
        }
        setTimeout(function() {
          targetMsg.css({
            transform: ''
          });
          if (bubble.length) bubble.css({
            borderColor: '',
            boxShadow: ''
          });
        }, 1800);
      }
    });
  </script>
  <script src="<?= base_url('modules/prchat/assets/js/reaction-picker.js?v=' . time()); ?>"></script>
  <script>
    (function() {
      "use strict";

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

      function resolveReactorName(key) {
        var k = String(key);
        var myId = String(userSessionId);
        if (k === myId || k === 'staff_' + myId) return '<?= addslashes(get_staff_full_name()); ?> (You)';
        if (k.startsWith('client_')) {
          var cId = k.replace('client_', '');
          var $c = $(".chat_clients_list li.contact_name#" + cId);
          if ($c.length) return ($c.find('.contact_name_main').text() || '').trim() || 'Client';
          return 'Client';
        }
        var staffKey = k.startsWith('staff_') ? k.replace('staff_', '') : k;
        var $el = $("#contacts li.contact#" + staffKey + ", #contacts a#user_" + staffKey);
        if ($el.length) {
          var n = ($el.find('.name').first().text() || '').trim();
          if (n) return n;
        }
        return staffKey;
      }

      /**
       * @param {string|object} reactionsRaw
       * @param {string|number} messageId
       * @param {string} chatType - 'staff', 'client', or 'group'
       */
      window.prchatRenderReactionPills = function(reactionsRaw, messageId, chatType) {
        var obj = parseReactions(reactionsRaw);
        var emojis = Object.keys(obj);
        if (!emojis.length) return '';
        var myId = String(userSessionId);
        var isGroup = chatType === 'group';
        var pills = emojis.map(function(emoji) {
          var users = obj[emoji] || [];
          var reacted = users.some(function(v) {
            var s = String(v);
            return s === myId || s === 'staff_' + myId;
          });
          var names = users.map(function(u) {
            return resolveReactorName(u);
          });
          var tooltip = names.join(', ');
          var countHtml = isGroup && users.length > 1 ? '<span class="reaction-count">' + users.length + '</span>' : '';
          return '<button type="button" class="reaction-pill' + (reacted ? ' reacted' : '') +
            '" data-emoji="' + emoji + '" data-msg-id="' + messageId +
            '" data-toggle="tooltip" data-placement="top" title="' + tooltip + '">' +
            '<span class="reaction-emoji">' + emoji + '</span>' + countHtml + '</button>';
        });
        return '<div class="message-reactions">' + pills.join('') + '</div>';
      };

      function sendReactionAjax(messageId, emoji, messageType) {
        $.post(prchatSettings.addReaction, {
          message_id: messageId,
          emoji: emoji,
          message_type: messageType || 'staff'
        }, function(resp) {
          if (resp && resp.reactions !== undefined) {
            updateReactionPillsOnDom(messageId, resp.reactions, messageType);
          }
        }, 'json');
      }

      function updateReactionPillsOnDom(messageId, reactionsRaw, chatType) {
        var $msg = $('.modern-message-item[data-message-id="' + messageId + '"]');
        if (!$msg.length) return;
        var $item = $msg.first();
        $item.find('.message-reactions').remove();
        var $bubble = $item.find('.modern-message-bubble').first();
        var html = window.prchatRenderReactionPills(reactionsRaw, messageId, chatType || 'staff');
        if (html) {
          $bubble.addClass('has-reactions');
          $bubble.append(html);
          $bubble.find('[data-toggle="tooltip"]').tooltip();
        } else {
          $bubble.removeClass('has-reactions');
        }
      }

      // React button click (opens picker)
      $("body").on("click", "._reactMessage", function(e) {
        e.stopPropagation();
        e.preventDefault();
        var $btn = $(this);
        var $optionsMore = $btn.closest(".optionsMore");
        var messageId = $optionsMore.attr("data-id");
        if (!messageId) return;

        var messageType = 'staff';
        if ($btn.closest('.group_messages').length || $btn.closest('.chat_group_messages').length) {
          messageType = 'group';
        } else if ($btn.closest('.client_messages').length || $btn.closest('#clientChat').length) {
          messageType = 'client';
        }

        var btnRect = $btn[0].getBoundingClientRect();
        var anchor = {
          getBoundingClientRect: function() {
            return btnRect;
          },
          _position: 'left'
        };

        if (typeof window.ReactionPicker === 'undefined') return;
        if (!window.__fullChatReactionPicker) {
          window.__fullChatReactionPicker = new window.ReactionPicker({
            onPick: function(emoji) {
              var id = window.__fullChatReactionTarget;
              var type = window.__fullChatReactionType || 'staff';
              if (id) sendReactionAjax(id, emoji, type);
              $(".optionsMore").hide();
            }
          });
        }
        window.__fullChatReactionTarget = messageId;
        window.__fullChatReactionType = messageType;
        window.__fullChatReactionPicker.openNear(anchor);
      });

      // Click on reaction pill to remove own reaction only
      $("body").on("click", ".reaction-pill", function(e) {
        e.stopPropagation();
        var $pill = $(this);
        if (!$pill.hasClass('reacted')) return;
        var messageId = $pill.attr("data-msg-id");
        var emoji = $pill.attr("data-emoji");
        if (!messageId || !emoji) return;

        var messageType = 'staff';
        if ($pill.closest('.group_messages').length || $pill.closest('.chat_group_messages').length) {
          messageType = 'group';
        } else if ($pill.closest('.client_messages').length || $pill.closest('#clientChat').length) {
          messageType = 'client';
        }

        sendReactionAjax(messageId, emoji, messageType);
      });

      // Pusher: message-reaction event for staff messages
      if (typeof presenceChannel !== 'undefined' && presenceChannel) {
        presenceChannel.bind('message-reaction', function(data) {
          if (data && data.message_id) {
            updateReactionPillsOnDom(data.message_id, data.reactions);
          }
        });
      }
    })();

    // Voice recorder initialization for Staff, Clients, Groups
    (function() {
      if (typeof PrchatVoiceRecorder === 'undefined' || !PrchatVoiceRecorder.isSupported()) return;

      var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
      var csrfVal = '<?= $this->security->get_csrf_hash(); ?>';

      function sendAsMessage(fileUrl, target) {
        if (typeof PrchatSafeRenderer === 'undefined') return;

        var rendered = PrchatSafeRenderer.renderFromText(fileUrl);
        var displayHtml = rendered.display;
        var sidebarPreview = rendered.sidebar;

        function voiceCsrfPayload(obj) {
          obj = obj || {};
          if (csrfName) {
            obj[csrfName] = csrfVal;
          }
          return obj;
        }

        function markVoiceSent($root, tempId, responseText) {
          var $sentMessage = $root.find('[data-message-id="' + tempId + '"]');
          $sentMessage.removeClass('modern-message-sending');
          try {
            var result = typeof responseText === 'string' ? JSON.parse(responseText) : responseText;
            if (result && result.id) {
              $sentMessage.attr('data-message-id', result.id);
              $sentMessage.find('.optionsMore').attr('data-id', result.id);
            }
          } catch (e) {}
          var $statusIcon = $sentMessage.find('.modern-status-sending');
          if ($statusIcon.length) {
            $statusIcon
              .removeClass('modern-status-sending fa-clock')
              .addClass('modern-status-delivered fa-check')
              .attr('title', '<?= _l('chat_msg_delivered'); ?>')
              .attr('data-original-title', '<?= _l('chat_msg_delivered'); ?>');
            try {
              $statusIcon.tooltip('destroy');
            } catch (e) {}
            try {
              $statusIcon.tooltip('dispose');
            } catch (e) {}
            $statusIcon.tooltip();
          }
        }

        if (target === 'staff') {
          var to = $('li.contact.active').attr('id');
          if (!to) return;
          var tempMessageId = 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
          var $pane = $(".messages#id_" + to);
          $pane.find(".messages-skeleton").addClass("hidden");
          var $mc = $pane.find(".modern-messages-container");
          if (!$mc.length) {
            $pane.find("ul").html('<div class="modern-messages-container"></div><div class="modern-typing-indicator" style="display: none;" data-user-id=""><div class="modern-typing-bubble"><div class="modern-typing-dots"><span></span><span></span><span></span></div><div class="modern-typing-text">Typing...</div></div></div>');
            $mc = $pane.find(".modern-messages-container");
          }
          if (typeof window.createModernMessage === 'function') {
            $mc.append(window.createModernMessage({
              id: tempMessageId,
              sender_id: userSessionId,
              message: displayHtml,
              time_sent_formatted: moment().format(prchatSettings.timeFormat),
              viewed: false,
              sending: true,
              user_image: null
            }));
          }
          if (typeof window.scrollToBottom === 'function') {
            window.scrollToBottom(true);
          }
          var activeContactId = to;
          $("#contacts .contact.active").find(".wrap .meta .preview").text('<?php echo _l('chat_message_you'); ?>' + ' ' + sidebarPreview);
          var sendTimeLabel = moment().fromNow();
          var $activeTimeAgo = $("#contacts .contact a#" + activeContactId).find(".wrap .meta .pull-right.time_ago");
          if ($activeTimeAgo.length === 0) {
            $("#contacts .contact a#" + activeContactId).find(".wrap .meta .meta-row-preview").append('<p class="pull-right time_ago">' + sendTimeLabel + '</p>');
          } else {
            $activeTimeAgo.html(sendTimeLabel);
          }
          var contactMember = $("#contacts li.contact#" + activeContactId);
          if (contactMember.length) {
            contactMember.detach().prependTo("#contacts ul");
          }
          $.post(prchatSettings.serverPath, voiceCsrfPayload({
            from: userSessionId,
            to: to,
            msg: fileUrl,
            typing: false
          })).done(function(response) {
            markVoiceSent($mc, tempMessageId, response);
          }).fail(function() {
            var $failed = $mc.find('[data-message-id="' + tempMessageId + '"]');
            $failed.addClass('modern-message-failed');
            var $ic = $failed.find('.modern-status-sending');
            if ($ic.length) {
              $ic.removeClass('modern-status-sending fa-clock').addClass('modern-status-failed fa-exclamation-circle text-danger');
            }
          });
        } else if (target === 'client') {
          var form = $("form[name=clientMessagesForm]");
          var clientTo = form.find('input.to').val();
          if (!clientTo) return;
          var tempMessageId = 'client_msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
          if ($(".client_messages .modern-messages-container").length === 0) {
            $(".client_messages .chat_client_messages ul").html('<div class="modern-messages-container"></div>');
          }
          var $cmc = $(".client_messages .modern-messages-container");
          if (typeof window.createModernClientMessage === 'function') {
            $cmc.append(window.createModernClientMessage({
              id: tempMessageId,
              sender_id: 'staff_' + userSessionId,
              message: displayHtml,
              time_sent_formatted: moment().format(prchatSettings.timeFormat),
              viewed: false,
              sending: true,
              user_image: null
            }));
          }
          var activeContact = $(".chat_clients_list > li.contact_name.selected");
          if (activeContact.length > 0 && typeof updateContactPreview === 'function') {
            updateContactPreview(activeContact.attr("id"), sidebarPreview, true, moment().format("YYYY-MM-DD HH:mm:ss"));
          }
          var formData = form.serializeArray();
          var clientId = '';
          var contactFullName = '';
          var companyName = '';
          if (activeContact.length > 0) {
            clientId = activeContact.attr("id");
            contactFullName = activeContact.find(".contact_name_main").text();
            companyName = activeContact.attr("data-company") || "";
          }
          var toFieldIndex = formData.findIndex(function(field) {
            return field.name === 'to';
          });
          if (toFieldIndex !== -1) {
            formData[toFieldIndex].value = "client_" + clientId;
          }
          formData = formData.filter(function(field) {
            return field.name !== 'client_message';
          });
          formData.push({
            name: "client_message",
            value: fileUrl
          }, {
            name: "client_id",
            value: clientId
          }, {
            name: "contact_full_name",
            value: contactFullName
          }, {
            name: "company",
            value: companyName
          });
          $.post(prchatSettings.clientsMessagesPath, formData).done(function(response) {
            markVoiceSent($cmc, tempMessageId, response);
          }).fail(function() {
            var $failed = $cmc.find('[data-message-id="' + tempMessageId + '"]');
            $failed.addClass('modern-message-failed');
            var $ic = $failed.find('.modern-status-sending');
            if ($ic.length) {
              $ic.removeClass('modern-status-sending fa-clock').addClass('modern-status-failed fa-exclamation-circle text-danger');
            }
          });
          if (typeof window.scrollToBottom === 'function') {
            window.scrollToBottom(true);
          }
        } else if (target === 'group') {
          var gid = localStorage.getItem('prchat_active_group');
          var $gm = $('#frame .group_messages .chat_group_messages:visible').first();
          if ($gm.length) {
            gid = $gm.attr('id') || gid;
          }
          if (!gid) return;
          var groupTempId = 'gmsg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
          var member_full_name = $("#frame #sidepanel #profile .profile-bar-name").text();
          if ($("#frame .group_messages .chat_group_messages#" + gid + " .modern-messages-container").length === 0) {
            $("#frame .group_messages .chat_group_messages#" + gid + " ul").html('<div class="modern-messages-container"></div>');
          }
          var $gmc = $("#frame .group_messages .chat_group_messages#" + gid + " .modern-messages-container");
          if (typeof window.createModernGroupMessage === 'function') {
            $gmc.append(window.createModernGroupMessage({
              id: groupTempId,
              sender_id: userSessionId,
              message: displayHtml,
              time_sent_formatted: moment().format(prchatSettings.timeFormat),
              user_image: null,
              sender_fullname: member_full_name
            }));
          }
          if (typeof updateGroupPreview === 'function') {
            updateGroupPreview(gid, fileUrl, true, moment().format("YYYY-MM-DD HH:mm:ss"));
          }
          if (typeof window.scrollToBottom === 'function') {
            window.scrollToBottom();
          }
          $.post(prchatSettings.groupMessagePath, voiceCsrfPayload({
            from: userSessionId,
            group_id: gid,
            g_message: fileUrl,
            typing: false
          })).done(function(response) {
            try {
              var result = typeof response === 'string' ? JSON.parse(response) : response;
              if (result && result.id) {
                var $message = $gmc.find('[data-message-id="' + groupTempId + '"]');
                $message.attr('data-message-id', result.id);
                $message.find('.optionsMore').attr('data-id', result.id);
              }
            } catch (e) {}
          });
        }
      }

      // Staff voice recorder
      var staffBtn = document.querySelector('#pusherMessagesForm .chat-action-voice');
      if (staffBtn) {
        PrchatVoiceRecorder.init({
          triggerBtn: staffBtn,
          container: document.querySelector('#pusherMessagesForm .prchat-composer-row') || document.querySelector('#pusherMessagesForm .message-input'),
          uploadUrl: prchatSettings.uploadMethod,
          csrfName: csrfName,
          csrfValue: csrfVal,
          extraFormData: function() {
            return {
              from: userSessionId
            };
          },
          onSend: function(filename) {
            sendAsMessage(filename, 'staff');
          },
          onError: function(msg) {
            alert_float('danger', msg);
          }
        });
      }

      // Client voice recorder (staff→client from admin)
      var clientBtn = document.querySelector('#clientMessagesForm .chat-action-voice-client');
      if (clientBtn) {
        PrchatVoiceRecorder.init({
          triggerBtn: clientBtn,
          container: document.querySelector('#clientMessagesForm .prchat-composer-row') || document.querySelector('#clientMessagesForm .message-input'),
          uploadUrl: "<?php echo site_url('prchat/Prchat_ClientsController/uploadMethod'); ?>",
          csrfName: csrfName,
          csrfValue: csrfVal,
          extraFormData: function() {
            return {
              from: 'staff_' + userSessionId
            };
          },
          onSend: function(filename) {
            sendAsMessage(filename, 'client');
          },
          onError: function(msg) {
            alert_float('danger', msg);
          }
        });
      }

      // Group voice recorder
      var groupBtn = document.querySelector('#groupMessagesForm .chat-action-voice-group');
      if (groupBtn) {
        PrchatVoiceRecorder.init({
          triggerBtn: groupBtn,
          container: document.querySelector('#groupMessagesForm .prchat-composer-row') || document.querySelector('#groupMessagesForm .message-input'),
          uploadUrl: prchatSettings.groupUploadMethod,
          csrfName: csrfName,
          csrfValue: csrfVal,
          extraFormData: function() {
            var gid = document.querySelector('.group_messages .chat_group_messages') && document.querySelector('.group_messages .chat_group_messages').getAttribute('id');
            if (!gid && typeof localStorage !== 'undefined') {
              gid = localStorage.getItem('prchat_active_group');
            }
            return {
              to_group: gid || '',
              send_from: String(typeof userSessionId !== 'undefined' ? userSessionId : '')
            };
          },
          onSend: function(filename) {
            sendAsMessage(filename, 'group');
          },
          onError: function(msg) {
            alert_float('danger', msg);
          }
        });
      }
    })();
  </script>
  <style>
    .modern-message-bubble {
      position: relative;
      overflow: visible;
    }

    .modern-message-bubble.has-reactions {
      margin-bottom: 22px;
    }

    .message-reactions {
      position: absolute;
      bottom: -14px;
      display: flex;
      flex-wrap: wrap;
      gap: 3px;
      z-index: 1;
    }

    .modern-message-sent .message-reactions {
      left: 4px;
    }

    .modern-message-received .message-reactions {
      right: 4px;
    }

    .reaction-pill {
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

    .reaction-pill:hover {
      box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
      transform: translateY(-1px);
    }

    .reaction-pill.reacted {
      cursor: pointer;
      background: rgba(59, 130, 246, .08);
      border-color: rgba(59, 130, 246, .35);
    }

    .reaction-pill.reacted:hover {
      background: rgba(59, 130, 246, .14);
    }

    .reaction-emoji {
      font-size: 14px;
      line-height: 1;
    }

    .reaction-count {
      font-weight: 600;
      font-size: 10px;
      color: #6b7280;
      min-width: 8px;
      text-align: center;
    }

    .reaction-pill.reacted .reaction-count {
      color: #3b82f6;
    }

    .alert-info {
      z-index: unset !important;
    }

    .modern-message-text audio.prchat-inline-audio,
    #clientChat .msg p audio.prchat-inline-audio {
      max-width: 100%;
      min-width: 200px;
      height: 36px;
      vertical-align: middle;
    }

    body.chat_dark .reaction-pill {
      background: #1f2937;
      border-color: #374151;
      box-shadow: 0 1px 4px rgba(0, 0, 0, .25);
    }

    body.chat_dark .reaction-pill:hover {
      box-shadow: 0 2px 8px rgba(0, 0, 0, .35);
    }

    body.chat_dark .reaction-pill.reacted {
      background: rgba(59, 130, 246, .15);
      border-color: rgba(59, 130, 246, .4);
    }

    body.chat_dark .reaction-pill.reacted:hover {
      background: rgba(59, 130, 246, .25);
    }

    body.chat_dark .reaction-count {
      color: #9ca3af;
    }

    body.chat_dark .reaction-pill.reacted .reaction-count {
      color: #60a5fa;
    }
  </style>

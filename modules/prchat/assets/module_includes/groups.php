<script>
  $(document).ready(function () {
    "use strict";

    /*---------------* Add new user to chat group that already exists  *---------------*/
    $("#addNewUserForm").submit(function (e) {
      var action = prchatSettings.addChatGroup;
      var selected_user = $("#usersSelect").val();

      e.preventDefault();
      $.ajax({
        type: "POST",
        url: action,
        data: {
          users: selected_user,
          group_name: chname_modal,
          group_id: $(this).data("group-id"),
        },
        success: function (response) {
          if (response !== "") {
            response = JSON.parse(response);
            if (typeof getInactiveChatUsers === 'function') {
              getInactiveChatUsers();
            }
          }
        },
        error: function () { }
      });
    });

    /*---------------* Pusher bind group send event  *---------------*/
    pusher.bind("group-send-event", function (data) {
      var groupMessages = $(".chat_group_messages");
      /**
       * Replace user id with last insert id for group message  (used for deleting messages after message is recieved or sent in current view)
       */
      if (data.last_insert_id) {
        groupMessages.find("li.own_group_message_li .own_group_message#" + userSessionId).attr("id", "gmsg_" + data.last_insert_id);
        groupMessages.find("li.own_group_message_li#" + userSessionId).find(".optionsMore").attr("data-id", data.last_insert_id);
        groupMessages.find("li.own_group_message_li#" + userSessionId).attr("id", data.last_insert_id);
        groupMessages.find('.modern-message-item[data-message-id="' + userSessionId + '"]').first().attr("data-message-id", data.last_insert_id);
      }

      if (String(data.from) !== String(userSessionId)) {
        removeGroupTyper(data.to_group, data.from);

        var rendered = PrchatSafeRenderer.render(data.message);

        if ($("#frame .group_messages .chat_group_messages#" + data.to_group + " .modern-messages-container").length === 0) {
          $("#frame .group_messages .chat_group_messages#" + data.to_group + " ul").html('<div class="modern-messages-container"></div>');
        }

        const newMessageData = {
          id: data.last_insert_id || data.message_id || ('grp_' + Date.now()),
          sender_id: data.from,
          message: rendered.display,
          time_sent_formatted: moment().format(prchatSettings.timeFormat),
          viewed: false,
          user_image: data.sender_image,
          sender_fullname: data.from_name
        };

        if (typeof window.createModernGroupMessage === 'function') {
          $("#frame .group_messages .chat_group_messages#" + data.to_group + " .modern-messages-container").append(window.createModernGroupMessage(newMessageData));
        }
      }

      if ($("#frame .group_messages").is(":visible")) {
        scrollToBottom();
      }


      if (data.to_group && data.message) {
        updateGroupPreview(data.to_group, data.message, data.from === userSessionId, moment().format("YYYY-MM-DD HH:mm:ss"), data.from_name || '');
      }
    });

    pusher.bind("group-message-edited", function (data) {
      var mid = data.message_id || data.id;
      if (!mid || data.group_id == null) {
        return;
      }
      var $container = $("#frame .group_messages .chat_group_messages#" + data.group_id + " .modern-messages-container");
      if (!$container.length) {
        return;
      }
      var $msg = $container.find('[data-message-id="' + mid + '"]');
      if (!$msg.length) {
        return;
      }
      if (data.rendered_message) {
        var editedHtml = data.rendered_message;
        if (window.PrchatMentions) {
          var _members = window.groupAllMentionMembers ||
            (window.groupMentions && window.groupMentions.members ? window.groupMentions.members : []);
          if (_members.length > 0 && editedHtml.indexOf('data-mention-id') === -1) {
            editedHtml = PrchatMentions.highlightMentions(editedHtml, _members);
          }
        }
        $msg.find(".modern-message-text").html(editedHtml);
      }
      if (!$msg.find(".prchat-edited-tag").length) {
        $msg.find(".modern-message-text").after('<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>');
      }
      var $last = $container.find("[data-message-id]").last();
      if (!$msg.is($last) || typeof updateGroupPreview !== "function" || typeof PrchatSafeRenderer === "undefined") {
        return;
      }
      var sid = data.sender_id != null ? String(data.sender_id) : "";
      var me = String(typeof window.userSessionId !== "undefined" ? window.userSessionId : "");
      var isMe = sid !== "" && sid === me;
      var ts = moment().format("YYYY-MM-DD HH:mm:ss");
      updateGroupPreview(String(data.group_id), data.rendered_message, isMe, ts, null);
    });

    pusher.bind("group-message-deleted", function (data) {
      var mid = data && (data.message_id || data.id);
      var gid = data && data.group_id != null ? String(data.group_id) : "";
      if (!mid || !gid) {
        return;
      }

      var $container = $("#frame .group_messages .chat_group_messages#" + gid + " .modern-messages-container");
      if ($container.length) {
        $container.find('[data-message-id="' + mid + '"]').remove();

        var $last = $container.children().last();
        if ($last.hasClass("modern-date-separator")) {
          $last.remove();
        }
      }

      if (typeof loadGroupPreviews === "function") {
        loadGroupPreviews();
      }

      if (typeof getGroupSharedFiles === "function" && $(".chat_group_messages").attr("id") == gid) {
        getGroupSharedFiles(gid);
      }
    });


    pusher.bind("group-renamed", function (data) {
      let oldGroupName = $("body").find("li.group_selector#" + data.group_id + " .group_name_main").text();
      pusher.unsubscribe("presence-" + oldGroupName.replace(/\s+/g, "-").replace(/\b\w/g, l => l.toUpperCase()));
      pusher.subscribe("presence-" + data.newName.replace(/\s+/g, "-").replace(/\b\w/g, l => l.toUpperCase()));

      // Update sidebar group name
      $("body").find("li.group_selector#" + data.group_id + " .group_name_main").text(data.newName);

      // Update group panel header if this group is currently active
      if ($("#frame .chat_groups_list li.active").attr("id") == data.group_id) {
        $(".modern-group-header h4").html('<i class="fa-solid fa-user-group"></i> ' + data.newName);
      }
    });

    /*---------------* Pending group badge store (for groups not yet loaded) *---------------*/
    window._pendingGroupBadges = window._pendingGroupBadges || {};

    /*---------------* Pusher bind group notify event  *---------------*/
    pusher.bind("group-notify-event", function (data) {
      // Check if this group is muted
      var isGroupMuted = (typeof mutedGroups !== 'undefined') && mutedGroups.indexOf(String(data.group_id)) !== -1;

      if (String(data.from) !== String(userSessionId) && !isGroupMuted) {
        <?php if (isset($chat_desktop_messages_notifications) && $chat_desktop_messages_notifications): ?>
          if (user_chat_status != "busy" && user_chat_status != "offline") {
            // Build notification body based on message type
            var notifBody = data.message;
            var rawMsg = String(data.message || '');
            if (rawMsg.indexOf('/modules/prchat/uploads/') !== -1) {
              if (/\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(rawMsg)) {
                notifBody = (window.prchatI18n && window.prchatI18n.chat_new_photo_uploaded) || 'uploaded a new photo';
              } else {
                notifBody = (window.prchatI18n && window.prchatI18n.chat_new_file_uploaded) || 'uploaded a new file';
              }
            } else if (/type=["']?audio/i.test(rawMsg) || rawMsg.indexOf('/uploads/prchat_audio/') !== -1) {
              notifBody = "<?= _l('chat_new_audio_group_message'); ?>";
            } else if (rawMsg.includes("data-mention-id")) {
              // Mentions: show as-is (they'll be cleaned below)
              notifBody = rawMsg.replace(/<[^>]*>/g, '').substring(0, 100);
            } else {
              // Plain text: strip any HTML and show preview
              notifBody = rawMsg.replace(/<[^>]*>/g, '').substring(0, 100) || rawMsg;
            }

            $.notify("", {
              "title": "<?= _l('chat_group_text'); ?>" + strCapitalize(data.group_name.replace("presence-", "")),
              "body": data.from_name + ": " + notifBody,
              "requireInteraction": false,
              "icon": fetchUserAvatar(data.from, data.sender_image),
              "tag": "group-message-" + data.from + data.group_id,
              "closeTime": app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : null
            });
          }
        <?php endif; ?>
      }


      if (String(data.from) !== String(userSessionId)) {
        // Always increment the Groups tab badge first (works even if groups not loaded)
        if (!$("li.groups").hasClass("active")) {
          incrementTabBadge('groups');
        }

        // Try to update individual group badge in sidebar
        var group_selector = $("#frame .chat_groups_list .group_selector#" + data.to_group);

        if (group_selector.length > 0) {
          if (!group_selector.hasClass("active")) {
            // Group DOM exists, update its badge
            var groupBadge = group_selector.find(".group-unread-badge");
            if (groupBadge.length > 0) {
              var gCount = parseInt(groupBadge.attr("data-badge")) || 0;
              groupBadge.attr("data-badge", gCount + 1).text(gCount + 1).show();
            } else {
              group_selector.find(".group_row_top").append('<span class="group-unread-badge" data-badge="1">1</span>');
            }
          }
        } else {
          // Groups not loaded yet — store pending badge count
          var gid = String(data.to_group);
          window._pendingGroupBadges[gid] = (window._pendingGroupBadges[gid] || 0) + 1;
        }
      }

    });

    groupChannels.bind("pusher:subscription_succeeded", function () {
      <?php if (!isClientsEnabled() || !staffCanAccessClientsTab()) { ?>
        $("#main_loader_init").fadeOut(500);
      <?php } ?>
    });

    /*---------------* Groupchannels event if member leaves a channel/group  *---------------*/
    groupChannels.bind("member-left-channel", function (data) {
      var group_name = data.group_name.replace("presence-", "");
      pusher.unsubscribe(data.group_name);

      if (data.member_id == userSessionId) {
        var selector = $("#frame .chat_groups_list li#" + data.group_id);
        if (selector.remove()) {
          $(".group_messages").hide();
          $("#frame .group_members_inline").remove();
          alert_float("info", '<?= _l("chat_removed_from_group"); ?>'.replace('%s', group_name));
        }
      } else if (data.member_id !== userSessionId || isAdmin) {
        var memberName = data.user_full_name || '<?= _l("chat_unknown_user"); ?>';
        alert_float("info", memberName + " <?= _l('chat_group_left_text'); ?> " + group_name);
        $("#frame .chat_group_options .group_members p#member_" + data.member_id).fadeOut().remove();
      }
    });

    /*---------------* Pusher bind if member is added to new group/channel  *---------------*/
    pusher.bind("added-to-channel", function (data) {
      var group_name = fixChatGroupName(data.group_name);
      if (data.result == "success") {
        $.each(data.user_ids, function (i, id) {
          if (id == userSessionId) {
            pusher.subscribe(data.group_name);
            appendNewChatGroup(data);
            alert_float("info", '<?php echo _l("chat_added_to_group"); ?>' + group_name);
          } else {
            pusher.subscribe(data.group_name);
            var activeGroupId = $("#frame .chat_groups_list li.active").attr("id");
            if (activeGroupId == data.group_id) {
              $.post(prchatSettings.getStaffInfo, {
                id: id
              }, function (response) {
                try {
                  var memberInfo = JSON.parse(response);
                  if (memberInfo && memberInfo.firstname && memberInfo.lastname) {
                    var memberName = memberInfo.firstname + ' ' + memberInfo.lastname;
                    var profileImage = memberInfo.profile_image || null;
                    addMemberToDOM(memberName, id, data.group_id, profileImage);
                  }
                } catch (e) {
                  console.error('Error parsing member info:', e);
                }
              });
            }
          }
        });
        $("#frame .group_selector.active a").click();
        $(".message-input.group_msg_input").show();
      }
    });


    /*---------------* Pusher bind if member is removed from group/channel  *---------------*/
    pusher.bind("removed-from-channel", function (data) {
      if (data.created_by_me == userSessionId) {
        return false;
      }

      var group_name = fixChatGroupName(data.group_name);

      var selector = $("#frame .chat_groups_list li#" + data.group_id);
      if (data.user_id == userSessionId) {

        if (selector.hasClass("active")) {
          $("#frame #sidepanel .nav.nav-tabs .staff a").click();
          $(".group_messages").hide();
        }
        selector.remove();
        pusher.unsubscribe(data.group_name);

        group_name = group_name.replace("presence-", "");

        alert_float("info", '<?php echo _l("chat_removed_from_group"); ?>'.replace('%s', group_name));
      }
    });


    /*---------------* Pusher bind event if group is deleted/closed channel  *---------------*/
    pusher.bind("group-deleted", function (data) {
      if (data.result == "true") {
        data.group_name = data.group_name.replace("-", "_");
        if ($("#frame #sidepanel li.group_selector#" + data.group_id).remove()) {
          if ($("#frame #sidepanel li.group_selector:first").length !== 0) {
            $("#frame #sidepanel li.group_selector:first").click();
          } else {
            $("#frame li.staff a").click();
          }
        }
      }
    });


    /*---------------* Pusher event when new group/channel is created *---------------*/
    pusher.bind("group-chat", function (data) {
      $(data.members).each(function (index, user_id) {
        if (user_id == userSessionId) {
          alert_float("success", '<?php echo _l("chat_added_to_group"); ?>' + fixChatGroupName(data.group_name));
          pusher.subscribe(data.group_name);
          appendNewChatGroup(data);
          $(".message-input.group_msg_input").show();
        }
      });
    });

    var groupTypingTimeout = 3000; // 3 seconds before clearing a typer
    var groupTypers = {}; // { groupId: { userId: { name, timer } } }

    function updateGroupTypingIndicator(groupId) {
      var typerData = groupTypers[groupId] || {};
      var names = [];
      for (var uid in typerData) {
        if (typerData.hasOwnProperty(uid)) {
          names.push(typerData[uid].name.split(' ')[0]); // first name only
        }
      }
      var $container = $("#frame .group_messages#" + groupId);
      var $indicator = $container.find(".user_is_typing");

      if (names.length === 0) {
        $indicator.fadeOut(200, function () {
          $(this).remove();
        });
        return;
      }

      var typingText = '';
      if (names.length === 1) {
        typingText = names[0] + ' <?= _l("chat_is_typing"); ?>...';
      } else if (names.length === 2) {
        typingText = names[0] + ' & ' + names[1] + ' <?= _l("chat_are_typing"); ?>...';
      } else {
        typingText = '<?= _l("chat_several_typing"); ?>...';
      }

      if ($indicator.length === 0) {
        $container.append('<span class="user_is_typing">' + typingText + '</span>');
        $container.find(".user_is_typing").fadeIn(500);
        scrollToBottom();
      } else {
        $indicator.text(typingText);
      }
    }

    function removeGroupTyper(groupId, userId) {
      if (groupTypers[groupId] && groupTypers[groupId][userId]) {
        clearTimeout(groupTypers[groupId][userId].timer);
        delete groupTypers[groupId][userId];
        if (Object.keys(groupTypers[groupId]).length === 0) {
          delete groupTypers[groupId];
        }
      }
      updateGroupTypingIndicator(groupId);
    }

    pusher.bind("group-typing-event", function (data) {
      var to_group = data.to_group;
      if (data.from !== userSessionId && data.message == "true") {
        if ($("#frame .group_messages").is(":visible")) {
          if (!groupTypers[to_group]) groupTypers[to_group] = {};

          // Clear existing timer for this user
          if (groupTypers[to_group][data.from]) {
            clearTimeout(groupTypers[to_group][data.from].timer);
          }

          // Add/update typer with auto-remove timer
          groupTypers[to_group][data.from] = {
            name: data.from_name || 'User',
            timer: setTimeout(function () {
              removeGroupTyper(to_group, data.from);
            }, groupTypingTimeout)
          };

          updateGroupTypingIndicator(to_group);
        }
      } else if (data.from !== userSessionId && data.message == "null") {
        removeGroupTyper(to_group, data.from);
      }
    });

    /*---------------* Handle mention event notifications  *---------------*/
    pusher.bind("mention-event", function (data) {

      if (data.users) {
        var currentUserMentioned = false;
        data.users.forEach(function (user) {
          if (user.user_id == userSessionId) {
            currentUserMentioned = true;
          }
        });

        if (currentUserMentioned) {

          if (typeof fetch_notifications === 'function') {
            fetch_notifications();
          }


          if (app.options.desktop_notifications && chat_desktop_notifications_enabled) {
            if (user_chat_status != "busy" && user_chat_status != "offline") {
              var mentionMessage = data.name + " <?= _l('chat_mentioned_you'); ?> <?= _l('chat_in_group'); ?> " + data.group_name;

              $.notify("", {
                "title": "<?= _l('chat_you_were_mentioned'); ?>",
                "body": mentionMessage,
                "requireInteraction": true,
                "icon": data.userImage ? admin_url + "staff/profile_image/" + data.from + "?s=32" : $("#header").find("img").attr("src"),
                "tag": "mention-" + data.from + "-" + Date.now(),
                "closeTime": app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : 5000
              }).show(function (e) {
                window.focus();
                setTimeout(function () {
                  e.target.close();
                }, app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : 5000);
              });
            }
          }
        }
      }
    });

    /*---------------* Defer group loading until groups tab is first activated *---------------*/
    var inChannels = [],
      members, data, result, channels, resp;

    window._groupsLoaded = false;
    window._groupsFetching = false;
    window._groupPreviewsLoaded = false;

    function fetchMyGroups() {
      if (window._groupsFetching || window._groupsLoaded) {
        return;
      }

      window._groupsFetching = true;

      // Suppress auto-click during bulk load
      window._bulkLoadingGroups = true;

      $.post(prchatSettings.getMyGroups).done(function (r) {
        // Always hide skeletons first when AJAX completes
        $("#groups_container .contacts-skeleton").addClass("hidden");
        $(".group_messages .messages-skeleton").addClass("hidden");

        var response = null;
        if (r !== "") {
          response = JSON.parse(r);
        }

        if (response && response.noChannels) {
          window.hasNoGroups = true;
          $("#frame form[name=groupMessagesForm]").hide();
          showEmptyGroupsMessage();
        } else {
          if (response && response.groups) {
            // Append groups to sidebar (only if user is a member)
            $.each(response.groups, function (key, data) {
              if ($(".chat_groups_list li a#" + data.id).length == 0) {
                appendCurrentGroups(data);
              }
            });

            // Check if any groups were actually added to the sidebar
            var $actualGroups = $(".chat_groups_list li.group_selector");

            if ($actualGroups.length === 0) {
              // User is not a member of any groups - show empty message
              window.hasNoGroups = true;
              $("#frame form[name=groupMessagesForm]").hide();
              showEmptyGroupsMessage();
            } else {
              // User has groups - show content
              window.hasNoGroups = false;
              $("#frame .content .group_messages, #frame form[name=groupMessagesForm]").show();
              $("#add_group_btn").show();

              window._bulkLoadingGroups = false;
              if (!window.matchMedia('only screen and (max-width: 735px)').matches) {
                var $firstGroup = $actualGroups.first();
                $firstGroup.click();
              }

              // Load previews (single batch request)
              loadGroupPreviews();
              window._groupPreviewsLoaded = true;
            }
          }
        }

        // Apply any pending group badges that arrived before groups were loaded
        if (window._pendingGroupBadges) {
          $.each(window._pendingGroupBadges, function (gid, count) {
            var grpEl = $("#frame .chat_groups_list .group_selector#" + gid);
            if (grpEl.length > 0 && !grpEl.hasClass("active") && count > 0) {
              var existingBadge = grpEl.find(".group-unread-badge");
              if (existingBadge.length > 0) {
                var cur = parseInt(existingBadge.attr("data-badge")) || 0;
                existingBadge.attr("data-badge", cur + count).text(cur + count).show();
              } else {
                grpEl.find(".group_row_top").append('<span class="group-unread-badge" data-badge="' + count + '">' + count + '</span>');
              }
            }
          });
          window._pendingGroupBadges = {};
        }

        // Mark as loaded AFTER async response is processed
        window._groupsLoaded = true;
        window._groupsFetching = false;
      })
        .fail(function () {
          // Hide skeletons on error
          $("#groups_container .contacts-skeleton").addClass("hidden");
          $(".group_messages .messages-skeleton").addClass("hidden");
          window._groupsFetching = false;
        });
    }

    // Hide skeleton immediately since groups tab is not active on load
    $("#groups_container .contacts-skeleton").addClass("hidden");

    // Expose for use from chat_full_view.php
    window.fetchMyGroups = fetchMyGroups;
    window.renderChatGroupMessages = renderChatGroupMessages;
    window.showEmptyGroupsMessage = showEmptyGroupsMessage;


    /*---------------* Click event for adding new member to group  *---------------*/
    $("body").on("click", "#frame .add_chat_member", function () {
      var group_id = $("#frame .chat_groups_list li.active").attr("id");
      $(".modal_container").load(prchatSettings.addNewChatGroupMembersModal, {
        group_id: group_id
      }, function (res) {
        if ($("#add_members_modal").is(":hidden")) {
          $("#add_members_modal").modal({
            show: true
          });
        }
      });
    });
  });

  /*---------------* Function to show empty groups message *---------------*/
  function showEmptyGroupsMessage() {
    // Force hide ALL skeletons first
    $(".messages-skeleton").addClass("hidden");
    $(".contacts-skeleton").addClass("hidden");

    var noGroupsMessage = '<div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-min-h-[400px] tw-p-8 tw-mt-12">';
    noGroupsMessage += '<div class="tw-max-w-md tw-w-full">';
    noGroupsMessage += '<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-8 tw-border tw-border-neutral-200">';
    noGroupsMessage += '<div class="tw-flex tw-flex-col tw-items-center tw-text-center">';
    noGroupsMessage += '<div class="tw-w-16 tw-h-16 tw-rounded-full tw-bg-primary-100 tw-flex tw-items-center tw-justify-center tw-mb-4">';
    noGroupsMessage += '<i class="fa-solid fa-user-group tw-text-primary-600 tw-text-2xl"></i>';
    noGroupsMessage += '</div>';

    // Show different message based on permission
    if (staffCanCreateGroups === "1" || isAdmin) {
      noGroupsMessage += '<h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mb-2"><?= _l('chat_no_groups_yet_title'); ?></h3>';
      noGroupsMessage += '<p class="tw-text-sm tw-text-neutral-600 tw-mb-6"><?= _l('chat_no_groups_yet_desc'); ?></p>';
      noGroupsMessage += '<button class="tw-inline-flex tw-items-center tw-gap-2 tw-px-6 tw-border-0 tw-py-3 tw-bg-primary-600 tw-text-white tw-font-medium tw-rounded-lg hover:tw-bg-primary-700 tw-transition-colors tw-shadow-sm hover:tw-shadow-md" onclick="loadCreateGroupModal()">';
      noGroupsMessage += '<i class="fa fa-plus tw-text-sm"></i>';
      noGroupsMessage += '<span><?php echo _l('chat_create_first_group'); ?></span>';
      noGroupsMessage += '</button>';
    } else {
      noGroupsMessage += '<h3 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mb-2"><?= _l('chat_no_groups_available_title'); ?></h3>';
      noGroupsMessage += '<p class="tw-text-sm tw-text-neutral-600 tw-mb-6"><?= _l('chat_no_groups_available_desc'); ?></p>';
    }

    noGroupsMessage += '</div>';
    noGroupsMessage += '</div>';
    noGroupsMessage += '</div>';
    noGroupsMessage += '</div>';

    // Clear and set the empty message
    var $chatGroupMessages = $("#frame .group_messages .chat_group_messages");
    $chatGroupMessages.empty().html(noGroupsMessage);

    // Ensure parent container is visible
    $("#frame .group_messages").show();

    // Don't hide the add_group_btn in sidebar - let permission check handle it
  }

  /*---------------* Function to load create group modal *---------------*/
  function loadCreateGroupModal() {
    $(".modal_container").load(prchatSettings.chatGroups, function (res) {
      if ($("#chat_groups_custom_modal").is(":hidden")) {
        $("#chat_groups_custom_modal").modal({
          show: true
        });
      }
    });
  }


  /*---------------* Check for messages history and append to main chat window in group messages *---------------*/
  function loadGroupMessages(el) {
    var pos = $(el).scrollTop();
    var messagesScrollbar = $(el).find(".chat_group_messages");
    var to_group = $(el).find(".chat_group_messages").attr("id");
    var messages;
    var from = userSessionId;

    if ($(messagesScrollbar).children().length !== 0) {
      if (pos == 0 && groupOffsetPush >= 10) {

        $.get(prchatSettings.getGroupMessagesHistory, {
          group_id: to_group,
          offset: groupOffsetPush,
        })
          .done(function (message) {
            messages = JSON.parse(message);
            if (Array.isArray(messages) == false) {
              groupEndOfScroll = true;
              if ($(".group_messages").hasScrollBar() && groupEndOfScroll == true) {
                prchat_setNoMoreGroupMessages();
              }
            } else {
              groupOffsetPush += 10;
            }

            $(messages).each(function (i, value) {

              var previous_time = moment(messages[i].time_sent).format("YYYY-MM-DD");

              if (messages[i + 1] !== undefined) {
                var current_time = moment(messages[i + 1].time_sent).format("YYYY-MM-DD");
              }

              var rendered = PrchatSafeRenderer.render(value.message);

              if ($(".chat_group_messages#" + to_group + " .modern-messages-container").length === 0) {
                $(".chat_group_messages#" + to_group + " ul").html('<div class="modern-messages-container"></div>');
              }

              const scrollMessageData = {
                id: value.id,
                sender_id: value.sender_id,
                message: rendered.display,
                time_sent: value.time_sent,
                time_sent_formatted: value.time_sent_formatted,
                viewed: value.viewed == 1,
                user_image: value.user_image,
                sender_fullname: value.sender_fullname,
                reactions: value.reactions || null
              };

              $(".chat_group_messages#" + to_group + " .modern-messages-container").prepend(window.createModernGroupMessage(scrollMessageData));

              if (previous_time !== current_time) {
                $("<div class=\"modern-date-separator\">" + formatDateSeparatorLabel(value.time_sent) + "</div>").prependTo($(".chat_group_messages#" + to_group + " .modern-messages-container"));
              }
            });
            if (groupEndOfScroll == false) {
              $(el).scrollTop(200);
            }
          });
      }
    }
  }

  /*---------------* Main function that renders the messages and created view for group chat messages  *---------------*/
  function renderChatGroupMessages(data) {
    groupEndOfScroll = false;
    groupOffsetPush = 0;
    var group_id = $(data).attr("id");
    var group_name = $(data).attr("data-channel");
    chat_group_messages.html("<ul><div class=\"modern-messages-container\"></div></ul>");
    chat_group_messages.attr("id", group_id);
    $("#frame .content .group_messages").attr("id", group_id);
    $("#frame .content .messages, #frame .content a[target=_blank], #frame .content .staff-profile-image-small").hide();
    $(chat_social_media).hide();
    $(chat_contact_profile_img).hide();
    $("#frame .content p").text("");
    var group_created_by = $(data).data("created-by");
    $(".leave_chat_group").remove();

    $("#frame .content .group_messages#" + group_id).show();

    getGroupMessages(group_id);
  }




  /*---------------* Main function for fetching messages for specific group and appends into view  *---------------*/
  var createGroupBoxRequest = null;

  function getGroupMessages(group_id) {

    var groupMessages;
    chat_group_messages.html("");
    // Generate dynamic skeleton while loading
    generateMessageSkeleton('.group_messages .messages-skeleton', 10);
    var dfd = $.Deferred();
    var groupMessagesPromises = dfd.promise();

    if (createGroupBoxRequest !== null) {
      createGroupBoxRequest.abort();
    }

    createGroupBoxRequest = $.get(prchatSettings.getGroupMessages, {
      group_id: group_id,
      offset: 0,
      limit: 20
    })
      .done(function (r) {
        groupOffsetPush = 10;
        r = JSON.parse(r);
        groupMessages = r;
        groupOffsetPush += 10;
        dfd.resolve(groupMessages);
      })
      .fail(function () {
        dfd.resolve([]);
      })
      .always(function () {
        if ($("#no_messages").length) {
          $("#no_messages").remove();
        }
        createGroupBoxRequest = null;
      });


    /*---------------* Modern Group Message HTML Architecture *---------------*/
    window.createModernGroupMessage = function (messageData) {
      const isOwn = messageData.sender_id == userSessionId;
      // Extract only the time portion for display (not full date)
      let timeOnly;
      if (messageData.time_sent) {
        timeOnly = moment(messageData.time_sent).format(prchatSettings.timeFormat);
      } else if (messageData.time_sent_formatted) {
        const parts = messageData.time_sent_formatted.trim().split(' ');
        if (parts.length >= 2) {
          timeOnly = parts.slice(-2).join(' ').replace(/^\d{2}-\d{2}-\d{4}\s*/, '');
          if (timeOnly.match(/^\d{2}:\d{2}(:\d{2})?$/)) {
            timeOnly = timeOnly.substring(0, 5);
          }
        } else {
          timeOnly = messageData.time_sent_formatted;
        }
      } else {
        timeOnly = moment().format(prchatSettings.timeFormat);
      }

      // Groups don't have read/unread status - no status icons needed

      const optionsMore = deleteOrForward(messageData.id || userSessionId, "group", isOwn, messageData.message || '');

      // For group messages, we need to handle both own and other users' avatars
      let avatarSrc;
      if (isOwn) {
        // For own messages, use current user's sidebar profile image or fetchUserAvatar
        if (!messageData.user_image) {
          avatarSrc = $("#sidepanel #profile #profile-img").prop("currentSrc") || site_url + "/assets/images/user-placeholder.jpg";
        } else {
          avatarSrc = fetchUserAvatar(userSessionId, messageData.user_image);
        }
      } else {
        // For other users' messages, use fetchUserAvatar with their sender_id
        avatarSrc = fetchUserAvatar(messageData.sender_id, messageData.user_image);
      }

      let processedMessage = PrchatSafeRenderer.sanitize(messageData.message);

      if (window.PrchatMentions) {
        var _highlightMembers = window.groupAllMentionMembers ||
          (window.groupMentions && window.groupMentions.members ? window.groupMentions.members : []);
        // Skip if message already has mention HTML (sender path - already converted)
        var _alreadyHasMentions = processedMessage.indexOf('data-mention-id') !== -1;
        if (!_alreadyHasMentions && _highlightMembers.length > 0) {
          processedMessage = PrchatMentions.highlightMentions(processedMessage, _highlightMembers);
        }
      }

      if (typeof processedMessage === 'string') {
        // Keep @everyone visually consistent across sender/receiver and reload paths.
        processedMessage = processedMessage.replace(
          /(^|\s)@everyone(?=\s|$|<)/g,
          '$1<span class="mention-highlight mention-everyone" data-mention-id="everyone">@everyone</span>'
        );

        // Normalize mention anchors so receiver side keeps highlight classes.
        processedMessage = processedMessage.replace(/class="([^"]*\b(?:quickMentionLink|user_mentioned)\b[^"]*)"/gi, function (_, cls) {
          var classes = cls.split(/\s+/).filter(Boolean);
          if (classes.indexOf('mention-highlight') === -1) {
            classes.push('mention-highlight');
          }
          return 'class="' + classes.join(' ') + '"';
        });

        processedMessage = processedMessage.replace(/<a(?![^>]*class=)([^>]*\bdata-mention-id="[^"]+"[^>]*)>/gi, '<a class="mention-highlight user_mentioned"$1>');
      }

      // Get sender name for group messages
      const senderName = messageData.sender_fullname ? messageData.sender_fullname.split(" ")[0] : "User";
      const reactionsHtml = window.prchatRenderReactionPills ? window.prchatRenderReactionPills(messageData.reactions, messageData.id, 'group') : '';
      const hasRx = reactionsHtml ? ' has-reactions' : '';
      const editedAt = messageData.edited_at;
      const showEdited = editedAt && String(editedAt).indexOf('0000-00-00') !== 0 && String(editedAt).trim() !== '';
      const editedTagHtml = showEdited ? '<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>' : '';

      if (isOwn) {
        return `
                    <div class="modern-message-item modern-message-sent" data-message-id="${messageData.id}">
                        <div class="modern-message-content">
                            <div class="modern-message-bubble modern-sent-bubble${hasRx}">
                                <div class="modern-message-text">${processedMessage}</div>
                                <div class="modern-message-meta">
                                    ${editedTagHtml}
                                    <span class="modern-message-time">${timeOnly}</span>
                                </div>
                                ${reactionsHtml}
                            </div>
                            ${optionsMore}
                        </div>
                        <div class="modern-message-avatar">
                            <img src="${avatarSrc}" alt="${senderName}" class="modern-avatar-img" />
                        </div>
                    </div>
                `;
      } else {
        return `
                    <div class="modern-message-item modern-message-received" data-message-id="${messageData.id}" data-sender-name="${senderName}">
                        <div class="modern-message-avatar" data-toggle="tooltip" data-placement="left" title="${senderName}">
                            <img src="${avatarSrc}" alt="${senderName}" class="modern-avatar-img" />
                        </div>
                        <div class="modern-message-content">
                            <div class="modern-message-bubble modern-received-bubble${hasRx}">
                                <div class="modern-message-sender" style="font-size:11px;font-weight:600;opacity:.8;margin-bottom:4px;">${senderName}</div>
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
    };

    /*---------------* After users are fetched from database -> continue with loading *---------------*/
    groupMessagesPromises.then(function (data) {
      // Handle empty/failed response
      if (!data || !data.users) {
        $(".group_messages .messages-skeleton").addClass("hidden");
        $(".message-input.group_msg_input").hide();
        return false;
      }

      if (!window.matchMedia("only screen and (max-width: 735px)").matches) {
        if (!Array.isArray(data.users)) {
          $(".group_messages .messages-skeleton").addClass("hidden");
          $(".message-input.group_msg_input").hide();
          return false;
        }
      }

      // Pass association data from server response
      var groupAssociation = {
        related_type: data.related_type || '',
        related_id: data.related_id || '',
        related_name: data.related_name || '',
        related_url: data.related_url || ''
      };

      // Also update the <li> data attributes so they stay in sync
      var $grpLi = $("#frame .chat_groups_list li#" + data.separete_group_id);
      if ($grpLi.length) {
        $grpLi.attr("data-related-type", groupAssociation.related_type);
        $grpLi.attr("data-related-id", groupAssociation.related_id);
        $grpLi.attr("data-related-name", groupAssociation.related_name);
        $grpLi.attr("data-related-url", groupAssociation.related_url);

        // Sidebar avatar: CRM type when linked, else default group icon
        var _sideIcon = groupAssociation.related_type ? getRelatedTypeIcon(groupAssociation.related_type) : "fa-solid fa-user-group";
        $grpLi.find(".groups_image i").attr("class", _sideIcon);

        $grpLi.find(".group_association_tag").remove();
        if (groupAssociation.related_type && groupAssociation.related_name) {
          var _tagIcon = getRelatedTypeIcon(groupAssociation.related_type);
          $grpLi.find(".group_row_top").after('<div class="group_association_tag"><i class="' + _tagIcon + '"></i> ' + groupAssociation.related_name + '</div>');
        }
      }

      getGroupUsers(data.separete_group_id, data.separete_group_name, data.users, groupAssociation);

      $(data.messages).each(function (i, value) {

        var previous_time = moment(value.time_sent).format("YYYY-MM-DD");

        if (data.messages[i + 1] !== undefined) {
          var current_time = moment(data.messages[i + 1].time_sent).format("YYYY-MM-DD");
        }

        var rendered = PrchatSafeRenderer.render(value.message);

        if ($(".group_messages .modern-messages-container").length === 0) {
          $(".group_messages .messages-skeleton").addClass("hidden");
          $(".group_messages .chat_group_messages").html("<ul><div class='modern-messages-container'></div></ul>");
        }

        const messageData = {
          id: value.id,
          sender_id: value.sender_id,
          message: rendered.display,
          time_sent: value.time_sent,
          time_sent_formatted: value.time_sent_formatted,
          viewed: value.viewed == 1,
          user_image: value.user_image,
          sender_fullname: value.sender_fullname,
          reactions: value.reactions || null,
          edited_at: value.edited_at || null
        };

        $(".group_messages .modern-messages-container").prepend(window.createModernGroupMessage(messageData));

        if (previous_time !== current_time) {
          $("<div class=\"modern-date-separator\">" + formatDateSeparatorLabel(value.time_sent) + "</div>").prependTo($(".group_messages .modern-messages-container"));
        }

      });

      // Always hide skeleton and ensure container exists after processing
      $(".group_messages .messages-skeleton").addClass("hidden");
      if ($(".group_messages .modern-messages-container").length === 0) {
        $(".group_messages .chat_group_messages").html("<ul><div class='modern-messages-container'></div></ul>");
      }
    });

    $.when(groupMessagesPromises.then())
      .then(function () {
        if ($(".group_messages").hasScrollBar() && $(window).width() > 733) {
          scrollToBottom();
          $(".message-input.group_msg_input textarea.group_chatbox").focus();
        } else if ($(window).width() < 733) {
          // Due to mobile devices bug and loading time
          scrollToBottom();
        } else {
          // One last check for mobile devices
          scrollToBottom();
        }
      });

    return false;
  }

  /*---------------* Functions that handles member events after someone have created group chat  *---------------*/
  function appendCurrentGroups(data) {
    appendChatGroup(data);
  }

  function appendChatGroup(data) {
    var group_name = data.group_name;
    var group_id = data.group_id;

    $(data.members).each(function (index, member_data) {
      if (member_data.member_id == userSessionId) {
        pusher.subscribe(group_name);
        appendNewChatGroup(data);
      }
    });
  }

  /*---------------* Fixes channel (group name) for UI purposes *---------------*/
  function fixChatGroupName(name) {
    return name.replace("presence-", "").replace(/-/g, " ");
  }

  /*---------------* Returns FA6 icon class for a related type *---------------*/
  function getRelatedTypeIcon(type) {
    var icons = {
      'project': 'fa-solid fa-briefcase',
      'invoice': 'fa-solid fa-file-invoice-dollar',
      'estimate': 'fa-solid fa-file-invoice',
      'contract': 'fa-solid fa-file-signature',
      'ticket': 'fa-solid fa-life-ring',
      'lead': 'fa-solid fa-user-tie',
      'task': 'fa-solid fa-list-check'
    };
    return icons[type] || 'fa-solid fa-link';
  }

  /*---------------* Returns human-readable label for a related type *---------------*/
  function getRelatedTypeLabel(type) {
    var labels = {
      'project': '<?= _l("chat_project"); ?>',
      'invoice': '<?= _l("chat_invoice"); ?>',
      'estimate': '<?= _l("chat_estimate"); ?>',
      'contract': '<?= _l("chat_contract"); ?>',
      'ticket': '<?= _l("chat_ticket"); ?>',
      'lead': '<?= _l("chat_lead"); ?>',
      'task': '<?= _l("chat_task"); ?>'
    };
    return labels[type] || type;
  }

  /*---------------* Rename Group (inline in panel) *---------------*/
  function toggleGroupRenameInput(groupId) {
    $("#grp-panel-name-display, .grp-panel-rename-btn").hide();
    $("#grp-panel-rename-input").show();
    $("#grp-rename-field").focus().select();
  }

  function cancelGroupRename() {
    $("#grp-panel-rename-input").hide();
    $("#grp-panel-name-display, .grp-panel-rename-btn").show();
  }

  function saveGroupRename(groupId) {
    var groupName = $.trim($("#grp-rename-field").val());
    if (!groupName || groupName.length === 0) {
      cancelGroupRename();
      return;
    }
    groupName = escapeHtml(groupName);
    $.post(prchatSettings.chatRenameGroup, {
      groupId: groupId,
      groupName: groupName
    }).done(function (r) {
      r = JSON.parse(r);
      if (r.success) {
        // Update sidebar group name
        $("body").find("li.group_selector#" + groupId + " .group_name_main").text(groupName);
        // Update panel header
        $("#grp-panel-name-display").text(groupName);
        cancelGroupRename();
        alert_float("success", customerSettings.groupNameChanged + groupName);
      }
      if (r.error) {
        alert_float("error", r.error);
      }
    });
  }

  // Keep old function name for backward compat
  function renameGroup(groupId) {
    toggleGroupRenameInput(groupId);
  }

  window.prchatRefreshGroupAssociationUI = function (groupId, r) {
    if (!groupId || !r || !r.success) {
      return;
    }
    window.currentGroupAssociation = {
      related_type: r.related_type,
      related_id: r.related_id,
      related_name: r.related_name,
      related_url: r.related_url
    };
    var $li = $("li.group_selector#" + groupId);
    $li.attr("data-related-type", r.related_type);
    $li.attr("data-related-id", r.related_id);
    $li.attr("data-related-name", r.related_name);
    $li.attr("data-related-url", r.related_url);
    $li.find(".group_association_tag").remove();
    if (r.related_type && r.related_name) {
      var _tagIcon = getRelatedTypeIcon(r.related_type);
      var $top = $li.find(".group_row_top");
      if ($top.length) {
        $top.after('<div class="group_association_tag"><i class="' + _tagIcon + '"></i> ' + $("<div>").text(r.related_name).html() + "</div>");
      }
    }
    var _refreshIcon = r.related_type ? getRelatedTypeIcon(r.related_type) : "fa-solid fa-user-group";
    $li.find(".groups_image i").attr("class", _refreshIcon);
    appendGroupOptions();
  };

  function loadGroupAssociationModal(groupId) {
    if (!groupId || !prchatSettings.chatGroups) {
      return;
    }
    var url = prchatSettings.chatGroups + "?association_only=1&group_id=" + encodeURIComponent(groupId);
    $(".modal_container").load(url, function () {
      if ($("#chat_groups_custom_modal").length) {
        $("#chat_groups_custom_modal").modal({ show: true });
      }
    });
  }

  window.loadGroupAssociationModal = loadGroupAssociationModal;

  /*---------------* Renders the new chat group in sidebar  *---------------*/
  function appendNewChatGroup(data) {
    var data_group_id = "";
    if (data.group_id) {
      data_group_id = data.group_id;
    } else {
      data_group_id = data.id;
    }

    var group_name = fixChatGroupName(data.group_name);
    var main_selector = $("#frame #sidepanel .tab-content #groups .chat_groups_list");
    var group = "";

    var relatedType = data.related_type || '';
    var relatedId = data.related_id || '';
    var relatedName = data.related_name || '';
    var relatedUrl = data.related_url || '';

    group += "<li class=\"group_selector\" data-created-by=\"" + data.created_by_id + "\" id=\"" + data_group_id + "\" data-channel=\"" + data.group_name + "\" data-related-type=\"" + relatedType + "\" data-related-id=\"" + relatedId + "\" data-related-name=\"" + relatedName + "\" data-related-url=\"" + relatedUrl + "\" onClick=\"renderChatGroupMessages(this)\">";
    group += "<div class=\"group_wrapper\">";
    var _newGroupIcon = relatedType ? getRelatedTypeIcon(relatedType) : "fa-solid fa-user-group";
    group += '<div class="groups_image"><i class="' + _newGroupIcon + '"></i></div>';
    group += "<div class=\"group_info\">";
    group += "<div class=\"group_row_top\"><span class=\"group_name_main\">" + strCapitalize(group_name) + "</span></div>";
    if (relatedType && relatedName) {
      var _tagIcon = getRelatedTypeIcon(relatedType);
      group += "<div class=\"group_association_tag\"><i class=\"" + _tagIcon + "\"></i> " + relatedName + "</div>";
    }
    group += "<div class=\"group_row_preview\"><div class=\"group_preview\"></div><span class=\"group_time\"></span></div>";
    group += "</div>";
    group += "</div>";

    if (main_selector.prepend(group)) {
      // Reset the no groups flag since we now have a group
      window.hasNoGroups = false;
      // Show the add_group_btn since we have groups
      $("#add_group_btn").show();
      // Remove empty state message if it exists
      $(".no-groups-message").remove();

      // Only auto-select when a single group is added (not during bulk load)
      if (!window._bulkLoadingGroups && !window.matchMedia("only screen and (max-width: 735px)").matches) {
        $("#frame #sidepanel #groups .chat_groups_list li#" + data_group_id).click();
      }
    }
  }

  /*---------------* Handles sidebar groups click also handles active classes and notifications  *---------------*/
  $("body").on("click", "#frame .group_selector", function () {
    animateContent();
    $(".group_chatbox").val("");
    var groupOptions = $("#frame .groupOptions");
    if (groupOptions.is(":hidden")) {
      groupOptions.show();
    }
    // Clear unread count badge
    $(this).find(".group-unread-badge").remove();
    $(this).parent().find("li.group_selector.active").removeClass("active");
    $(this).addClass("active");
  });

  /*---------------* Function that gets all users connected with a specifix group chat and renders to view  *---------------*/
  function getGroupUsers(group_id, group_name, users, groupAssociation) {

    var active_members = "";

    // Store users globally for the loadGroupMembers function
    window.currentGroupMembers = users;

    // Store association data globally for appendGroupOptions
    window.currentGroupAssociation = groupAssociation || {};

    // Update PrchatMentions with current group members + @everyone
    if (window.groupMentions && users) {
      var allMentionMembers = users.map(function (u) {
        return {
          id: u.member_id,
          name: u.firstname + ' ' + u.lastname,
          avatar: u.avatar_url || fetchUserAvatar(u.member_id, u.profile_image),
          type: 'staff'
        };
      });

      // Store ALL members (including self) for highlight rendering
      window.groupAllMentionMembers = allMentionMembers;

      // Dropdown excludes self so you don't @mention yourself
      var mentionMembers = allMentionMembers.filter(function (m) {
        return m.id != userSessionId;
      });

      // Add @everyone option at the top
      mentionMembers.unshift({
        id: 'everyone',
        name: 'everyone',
        avatar: null,
        type: 'everyone'
      });

      window.groupMentions.setMembers(mentionMembers);
    }

    appendGroupOptions();

    if ($("#frame ul li.groups").hasClass("active")) {

      $("#frame .contact-profile div.group_members_inline").remove();

      // Clean the group name (remove presence- prefix and fix formatting)
      var cleanGroupName = group_name.replace(/^presence-/, '').replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

      // Create modern group profile with stacked avatars and settings button
      var groupProfileHtml = `
                <div class="group_members_inline">
                    <div class="group-layout">
                        <div class="stacked-avatars" id="group-stacked-avatars"></div>
                        <i class="fa-solid fa-bars group-settings-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?php echo _l('chat_group_settings'); ?>" onclick="showGroupChatOptions()" aria-hidden="true"></i>
                    </div>
                </div>
            `;

      $("#frame .contact-profile").append(groupProfileHtml);

      // Generate stacked avatars
      generateStackedAvatars(users, group_id);
    }
    var group_selector_options = ".chat_group_options #group_options";
    $.each(users, function (i, user) {
      active_members += user.firstname + " " + user.lastname + ", ";

      // Old button logic removed - buttons are now in the modern footer

      // Leave group button moved to modern group panel footer

      // Rename button moved to group info panel

      // Members are rendered by loadGroupMembers() in the modern panel
    });
  }


  /*---------------* Function that handles users if wants to leave group on its own  *---------------*/
  function leaveGroup(group_id) {
    var member_id = userSessionId;
    $.post(prchatSettings.chatMemberLeaveGroup, {
      group_id: group_id,
      member_id: member_id
    }).done(function (r) {
      if (r) {
        r = JSON.parse(r);
        if (r.message == "deleted") {
          if ($("#frame .chat_groups_list").children().length == 0) {
            $("#frame .staff a").click();
          } else {
            $("#frame .chat_groups_list li:first a").click();
          }
        }
      }
    });
  }


  /*---------------* Function that handles removing a member from group chat  *---------------*/
  function removeChatGroupUser(user) {
    var group_name = $(user).attr("data-group");
    var group_id = $(user).attr("data-group-id");
    var member_id = $(user).attr("id");
    member_id = member_id.replace("member_", "");

    if (member_id !== "") {
      $.post(prchatSettings.removeChatGroupUser, {
        id: member_id,
        group_id: group_id,
        group_name: group_name
      }).done(function (r) {
        r = JSON.parse(r);
        if (r.response == "success") {
          getGroupMessages(group_id);
        }
      });
    }
  }


  /*---------------* Function that handles closing/deleting an existing group  *---------------*/
  function deleteGroup(el) {

    var group_name = $(el).data("group-name");
    var group_id = $(el).data("group-id");

    if (confirm("<?php echo _l('chat_are_you_sure_delete_group'); ?>")) {
      $.post(prchatSettings.deleteGroup, {
        "group_name": group_name,
        "group_id": group_id
      }).done(function (r) {

        if (r !== "") {
          r = JSON.parse(r);
        }

        if (r.error == "nomore") {
          alert_float("warning", "<?php echo _l('chat_no_more_groups_to_delete'); ?>");
          $("#frame .chat_group_options, #frame .groupOptions, #frame .group_members_inline").remove();
          return false;
        }

        if (r.result == "success") {
          // Close the group settings modal
          if (optionsSelector.hasClass("active")) {
            showGroupChatOptions();
          }

          if ($("#frame #sidepanel li.group_selector#" + group_id).remove()) {
            if ($("#frame #sidepanel li.group_selector:first").length > 0) {
              // Still have groups, click the first one
              $("#frame #sidepanel li.group_selector:first").click();
            } else {
              // No more groups left - click Groups tab to show empty state
              window.hasNoGroups = true;
              window._groupsLoaded = false;
              $("#frame #sidepanel .groups a").click();
            }
          }
          alert_float("success", "<?php echo _l('chat_group_deleted'); ?>");
        }

      });
    } else {
      return false;
    }
  }

  var optionsSelector = $("#frame .content .chat_group_options");

  /*---------------* Toggle Settings sidebar for group chat  *---------------*/
  function showGroupChatOptions() {
    var g_messages = $("#frame .group_messages");
    if (g_messages.is(":hidden")) {
      g_messages.show();
    }
    if (!optionsSelector.hasClass("active")) {
      optionsSelector.css({
        "right": "-100%",
        "width": (isUserMobile) ? "100%" : "500px"
      }).show().animate({
        "right": "0"
      }, 300, "linear", function () {
        $(this).addClass("active");
      });
    } else if (!optionsSelector.is(":hidden")) {
      optionsSelector.animate({
        "right": "-100%"
      }, 300, "linear", function () {
        $(this).hide().removeClass("active");
      });
    }
  }

  $(document).on("keydown", function (e) {
    if (e.key === "Escape" || e.keyCode === 27) {
      if (optionsSelector.hasClass("active") && optionsSelector.is(":visible")) {
        showGroupChatOptions();
      }
    }
  });

  /*---------------* Get groups shared items id -> mixed and append to group option settings *---------------*/
  function getGroupSharedFiles(group_id, callback) {
    var $mainDivSharedFiles = $(".main_div_shared_files");

    $.post(prchatSettings.getGroupSharedFiles, {
      group_id: group_id
    }).done(function (data) {
      if (data) {
        data = JSON.parse(data);
        $mainDivSharedFiles.html("");
        $mainDivSharedFiles.html(data);

        // Execute callback if provided
        if (typeof callback === 'function') {
          callback();
        }
      }
    });
  }

  /*---------------* Delete group own messages function *---------------*/
  function delete_group_chat_message(grp_msg_id) {
    var group_id = $(".chat_group_messages").attr("id");

    $.post(prchatSettings.deleteMessage, {
      id: grp_msg_id,
      group_id: group_id
    }).done(function (response) {
      // Parse response if string
      if (typeof response === 'string') {
        try {
          response = JSON.parse(response);
        } catch (e) {
          // Invalid JSON, treat as false
          response = false;
        }
      }

      if (response) {
        // Try modern message structure first
        var modernSelector = $('.modern-messages-container').find('[data-message-id="' + grp_msg_id + '"]');

        if (modernSelector.length > 0) {
          modernSelector.remove();
        } else {
          // Fallback to legacy structure
          $("body").find("li.own_group_message_li#" + grp_msg_id).remove();
        }

        // Check and remove trailing date separator
        let lastChildren = $(".modern-messages-container").children().last();
        if (lastChildren.hasClass("modern-date-separator")) {
          lastChildren.remove();
        }

        // Also check legacy structure
        lastChildren = $("body").find(".chat_group_messages ul").children().last();
        if (lastChildren.hasClass("middleDateTime")) {
          lastChildren.remove();
        }

        getGroupSharedFiles(group_id);

        // Update sidebar preview with last message
        if (typeof updateSidebarAfterDelete === 'function') {
          updateSidebarAfterDelete('group', group_id);
        }
      } else {
        $('.modern-messages-container').find('[data-message-id="' + grp_msg_id + '"]').remove();
        $("body").find("li.own_group_message_li#" + grp_msg_id).remove();
        alert_float("danger", '<?php echo _l('chat_error_float'); ?>');
      }
    }).fail(function (xhr, status, error) {
      alert_float("danger", '<?php echo _l('chat_error_float'); ?>');
    });
  }

  /*--------------------  * send group message & typing event to server  * ------------------- */
  $("#frame").on("keypress", "textarea.group_chatbox", function (e) {

    var form = $(this).parents("form");
    var group_id = $("#frame .group_selector.active").attr("id");
    var isUserTyping = $(this).parents(".wrap").find("input.typing");

    $(this).parents(".wrap").find("input.from").val(userSessionId);

    var message = $.trim($(this).val());

    if (e.which == 13 && !e.shiftKey) {

      e.preventDefault();

      if ($(this).hasClass('prchat-editing') && typeof prchatSubmitEdit === 'function') {
        prchatSubmitEdit($(this));
        return false;
      }

      if (window.emojiPickerInstance && window.emojiPickerInstance.isVisible) {
        window.emojiPickerInstance.hide();
      }

      var ownImagePath = $("#sidepanel #profile #profile-img").prop("currentSrc");
      var member_full_name = $("#frame #sidepanel #profile .profile-bar-name").text();
      var member_first_name = $.trim(member_full_name);

      member_first_name = member_first_name.split(" ")[0];
      if (message == "" || internetConnectionCheck() === false) {
        return false;
      }

      // Handle reply functionality
      if (window.currentReplyData) {
        message = getReplyFormattedMessage(message);
        // Update the actual textarea value with formatted message
        $(this).val(message);
        clearReplyData(); // Clear reply after sending
      }

      // Detect @mentions BEFORE any HTML processing (on raw text)
      var notify_users = [];
      var mentionMembers = (window.groupMentions && window.groupMentions.members) ? window.groupMentions.members : [];
      var isEveryone = message.indexOf('@everyone') !== -1;

      if (isEveryone) {
        mentionMembers.forEach(function (member) {
          if (member.id !== 'everyone' && member.id != userSessionId) {
            notify_users.push({
              user_id: member.id,
              name: member.name
            });
          }
        });
        message = message.replace(
          /@everyone/g,
          '<span class="mention-highlight mention-everyone" data-mention-id="everyone">@everyone</span>'
        );
      } else if (mentionMembers.length > 0) {
        var notified = {};
        var candidates = mentionMembers
          .filter(function (member) {
            return member && member.id !== 'everyone' && member.name;
          })
          .map(function (member) {
            member.name = String(member.name).trim().replace(/\s+/g, ' ');
            return member;
          })
          .sort(function (a, b) {
            return b.name.length - a.name.length;
          });

        candidates.forEach(function (member) {
          var variants = [member.name];
          var firstName = member.name.split(' ')[0];
          if (firstName && firstName.toLowerCase() !== member.name.toLowerCase()) {
            variants.push(firstName);
          }

          variants.forEach(function (variant) {
            var escapedVariant = variant.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var mentionRegex = new RegExp('(^|\\s)@(' + escapedVariant + ')(?=\\s|$|[.,;!?])', 'gi');
            var didReplace = false;

            message = message.replace(mentionRegex, function (_, prefix, capturedName) {
              didReplace = true;
              return prefix + '<a href="' + site_url + 'admin/profile/' + member.id + '" class="user_mentioned mention-highlight" data-chatmentioned="true" data-toggle="tooltip" title="' + PrchatSafeRenderer.escapeHtml(member.name) + '" target="_blank" data-mention-id="' + member.id + '">@' + PrchatSafeRenderer.escapeHtml(capturedName) + '</a>';
            });

            if (didReplace && !notified[member.id] && member.id != userSessionId) {
              notified[member.id] = true;
              notify_users.push({
                user_id: member.id,
                name: member.name
              });
            }
          });
        });
      }

      if (notify_users.length > 0) {
        var group_name = $("body").find(".group_selector.active .group_wrapper .group_name_main").text();
        var group_id = $("#frame .group_selector.active").attr("id");
        sendMentionNotifications(userSessionId, group_name, notify_users, group_id);
      }

      var hasMentionHtml = message.indexOf('data-mention-id') !== -1 || message.indexOf('quickMentionLink') !== -1;
      var rendered = hasMentionHtml ?
        PrchatSafeRenderer.render(message) :
        PrchatSafeRenderer.renderFromText(message);

      if ($(".group_messages .modern-messages-container").length === 0) {
        $(".group_messages .chat_group_messages ul").html('<div class="modern-messages-container"></div>');
      }

      var groupTempId = 'gmsg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

      const newMessageData = {
        id: groupTempId,
        sender_id: userSessionId,
        message: rendered.display,
        time_sent_formatted: moment().format(prchatSettings.timeFormat),
        user_image: null,
        sender_fullname: member_full_name
      };

      $(".group_messages .modern-messages-container").append(window.createModernGroupMessage(newMessageData));

      isUserTyping.val("false");

      // send event
      var formData = form.serializeArray();

      formData.push({
        name: "group_id",
        value: group_id
      });

      $.post(prchatSettings.groupMessagePath, formData).done(function (response) {
        try {
          var result = typeof response === 'string' ? JSON.parse(response) : response;
          if (result && result.id) {
            // Update the message data-message-id
            var $message = $(".group_messages .modern-messages-container").find('[data-message-id="' + groupTempId + '"]');
            $message.attr('data-message-id', result.id);

            // CRITICAL: Also update the optionsMore data-id attribute (used for delete)
            $message.find('.optionsMore').attr('data-id', result.id);
          }
        } catch (e) { }
      });
      $(this).val("");
      $(this).focus();
      scrollToBottom();

      // Update group preview and move to top of list
      updateGroupPreview(group_id, message, true, new Date().toISOString());
    } else if (!$(this).val() || (isUserTyping.val() == "false")) {
      // typing event
      isUserTyping.val("true");
      var formTyping = form.serializeArray();

      formTyping.push({
        name: "group_id",
        value: group_id
      });

      $.post(prchatSettings.groupMessagePath, formTyping);
    }
  });

  // Handles group file form uploads
  function uploadGroupFileForm(form) {
    var fileInput = $(form).children("input[type=file]")[0];
    var files = fileInput.files;
    var to_group = $("#frame .group_messages .chat_group_messages").attr("id");
    var token_name = $(form).children("input:nth-child(3)").val();

    if (files.length === 0) {
      return false;
    }

    // Upload files one by one in sequence
    uploadGroupFilesSequentially(files, to_group, token_name, 0);
  }

  // Upload group files one by one sequentially
  function uploadGroupFilesSequentially(files, to_group, token_name, currentIndex) {
    if (currentIndex >= files.length) {
      // All files uploaded, hide loader and reset form
      $(".content .chat-module-loader").fadeOut();
      $("form[name='groupFileForm']").trigger("reset");
      return;
    }

    var file = files[currentIndex];
    var formData = new FormData();
    formData.append("userfile", file);
    formData.append("to_group", to_group);
    formData.append("send_from", userSessionId);
    formData.append("csrf_token_name", token_name);

    $.ajax({
      type: "POST",
      url: prchatSettings.groupUploadMethod,
      data: formData,
      dataType: "json",
      processData: false,
      contentType: false,
      beforeSend: function () {
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
            alert_float("warning", '<?php echo _l('chat_permitted_files') ?>');
            if (currentIndex === 0) {
              $(".content .chat-module-loader").remove();
            }
            return false;
          }
        } else {
          if (currentIndex === 0) {
            $(".content .chat-module-loader").remove();
          }
          return false;
        }
      },
      success: function (r) {
        if (!r.error) {
          var uploadSend = $.Event("keypress", {
            which: 13
          });

          var basePath = "<?php echo base_url('modules/prchat/uploads/groups/'); ?>";
          $("#frame textarea.group_chatbox").val(basePath + r.upload_data.file_name);

          setTimeout(function () {
            if ($("#frame textarea.group_chatbox").trigger(uploadSend)) {
              // Update shared files after message is sent
              getGroupSharedFiles(to_group);
            }

            // Wait a bit then upload next file
            setTimeout(function () {
              uploadGroupFilesSequentially(files, to_group, token_name, currentIndex + 1);
            }, 300);
          }, 100);
        } else {
          alert_float("danger", r.error);
          // Continue with next file even if this one failed
          setTimeout(function () {
            uploadGroupFilesSequentially(files, to_group, token_name, currentIndex + 1);
          }, 100);
        }
      },
      error: function () {
        // Continue with next file even if this one failed
        setTimeout(function () {
          uploadGroupFilesSequentially(files, to_group, token_name, currentIndex + 1);
        }, 100);
      }
    });
  }


  function appendGroupOptions() {
    var group_messages_active_selector = $(".content .chat_group_options");
    var $activeGroup = $("#frame .chat_groups_list li.active");
    var activeGroupName = $activeGroup.find(".group_name_main").text() || "Group";
    var activeGroupId = $activeGroup.attr("id");
    var createdBy = $activeGroup.attr("data-created-by") || '';
    var isCreator = (createdBy == userSessionId);
    var canEditAssociation = isCreator || (typeof isAdmin !== "undefined" && isAdmin);
    // Use server response data first, fall back to <li> data attributes
    var assoc = window.currentGroupAssociation || {};
    var relatedType = assoc.related_type || $activeGroup.attr("data-related-type") || '';
    var relatedName = assoc.related_name || $activeGroup.attr("data-related-name") || '';
    var relatedUrl = assoc.related_url || $activeGroup.attr("data-related-url") || '';

    // Build association section HTML
    var associationHtml = '';
    if (relatedType && relatedName) {
      var typeLabel = getRelatedTypeLabel(relatedType);
      var iconClass = getRelatedTypeIcon(relatedType);
      var editHeadBtn = canEditAssociation ? `<button type="button" class="grp-assoc-head-edit" onclick="event.stopPropagation(); loadGroupAssociationModal('${activeGroupId}')" title="<?= htmlspecialchars(_l('chat_group_update_association')); ?>"><i class="fa fa-pencil"></i></button>` : '';
      associationHtml = `
                    <!-- Association Section -->
                    <div class="grp-opt-section">
                        <div class="grp-opt-section-head" onclick="toggleSection('association')">
                            <span><i class="${iconClass}"></i> <?= _l('chat_associate_with'); ?></span>
                            <span class="grp-opt-section-head-actions">
                                ${editHeadBtn}
                                <i class="fa fa-chevron-down toggle-icon" id="association-toggle"></i>
                            </span>
                        </div>
                        <div class="grp-opt-section-body" id="association-content">
                            <div class="group-association-info">
                                <div class="association-badge-row"><span class="association-type-badge">${typeLabel}</span></div>
                                <a href="${relatedUrl}" target="_blank" class="association-link">
                                    <i class="${iconClass}"></i> ${relatedName}
                                    <i class="fa fa-external-link-alt fa-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
    } else if (canEditAssociation) {
      associationHtml = `
                    <div class="grp-opt-section">
                        <div class="grp-opt-section-head" onclick="toggleSection('association')">
                            <span><i class="fa fa-link"></i> <?= _l('chat_associate_with'); ?> <small class="text-muted">(<?= _l('chat_optional'); ?>)</small></span>
                            <i class="fa fa-chevron-down toggle-icon" id="association-toggle"></i>
                        </div>
                        <div class="grp-opt-section-body" id="association-content">
                            <p class="text-muted small tw-mb-2"><?= _l('chat_group_no_crm_record_linked'); ?></p>
                            <button type="button" class="btn btn-default btn-sm" onclick="loadGroupAssociationModal('${activeGroupId}')">
                                <i class="fa fa-link tw-mr-1"></i><?= _l('chat_associate_with'); ?>
                            </button>
                        </div>
                    </div>`;
    } else {
      associationHtml = `
                    <div class="grp-opt-section">
                        <div class="grp-opt-section-head" onclick="toggleSection('association')">
                            <span><i class="fa fa-link"></i> <?= _l('chat_associate_with'); ?></span>
                            <i class="fa fa-chevron-down toggle-icon" id="association-toggle"></i>
                        </div>
                        <div class="grp-opt-section-body" id="association-content">
                            <p class="text-muted small"><?= _l('chat_group_no_crm_record_linked'); ?></p>
                        </div>
                    </div>`;
    }

    var headerIconClass = relatedType ? getRelatedTypeIcon(relatedType) : "fa-solid fa-user-group";

    var modernPanel = `
            <div class="modern-group-panel">
                <!-- Header (same style as history modal) -->
                <div class="history-modal-header">
                    <div class="history-header-left">
                        <div class="history-header-icon"><i class="${headerIconClass}"></i></div>
                        <div class="grp-panel-name-wrap">
                            <h4 class="grp-panel-name" id="grp-panel-name-display">${activeGroupName}</h4>
                            ${isCreator ? '<button class="grp-panel-rename-btn" onclick="toggleGroupRenameInput(\'' + activeGroupId + '\')" data-toggle="tooltip" title="<?= _l("chat_rename_label"); ?>"><i class="fa-regular fa-pen-to-square"></i></button>' : ''}
                        </div>
                        ${isCreator ? `<div class="grp-panel-rename-input" id="grp-panel-rename-input" style="display:none;">
                            <input type="text" id="grp-rename-field" class="form-control" value="${activeGroupName}" maxlength="100" />
                            <button class="grp-rename-save" onclick="saveGroupRename('${activeGroupId}')"><i class="fa fa-check"></i></button>
                            <button class="grp-rename-cancel" onclick="cancelGroupRename()"><i class="fa fa-times"></i></button>
                        </div>` : ''}
                    </div>
                    <button type="button" class="history-close-btn" onclick="showGroupChatOptions()">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div class="modern-group-body">
                    <!-- Search -->
                    <div class="grp-opt-search-row" onclick="openGroupSearchModal('${activeGroupId}', '${activeGroupName}')">
                        <i class="fa fa-search"></i>
                        <span><?= _l('chat_search_msg_txt'); ?></span>
                    </div>

                    ${associationHtml}

                    <!-- Members Section -->
                    <div class="grp-opt-section">
                        <div class="grp-opt-section-head" onclick="toggleSection('members')">
                            <span><i class="fa fa-users"></i> <?php echo _l("chat_group_members_text"); ?></span>
                            <i class="fa fa-chevron-down toggle-icon" id="members-toggle"></i>
                        </div>
                        <div class="grp-opt-section-body" id="members-content">
                            <div class="group_members" id="modern-group-members"></div>
                        </div>
                    </div>

                    <!-- Shared Files Section -->
                    <div class="grp-opt-section">
                        <div class="grp-opt-section-head" onclick="toggleSection('shared')">
                            <span><i class="fa fa-share-alt"></i> <?php echo _l("chat_group_shared_items_text"); ?></span>
                            <i class="fa fa-chevron-down toggle-icon" id="shared-toggle"></i>
                        </div>
                        <div class="grp-opt-section-body" id="shared-content">
                            <div class="history-tabs grp-opt-tabs">
                                <button class="history-tab-btn active" data-tab="photos">
                                    <i class="fa fa-image"></i> <?= _l('chat_photos_text'); ?>
                                </button>
                                <button class="history-tab-btn" data-tab="files">
                                    <i class="fa fa-file"></i> <?= _l('chat_files_text'); ?>
                                </button>
                            </div>
                            <div class="grp-opt-tab-content">
                                <div class="tab-pane active" id="photos-tab">
                                    <div class="shared-photos-grid"></div>
                                </div>
                                <div class="tab-pane" id="files-tab">
                                    <div class="shared-files-list"></div>
                                </div>
                            </div>
                            <div class="main_div_shared_files" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="grp-opt-footer">
                    <div class="group_options" style="display: none;"></div>
                    <button class="history-action-btn primary" onclick="openAddMemberModal('${activeGroupId}')">
                        <i class="fa fa-user-plus"></i> <?= _l("chat_add_member_text"); ?>
                    </button>
                    ${!isCreator ? `<button class="history-action-btn" style="background:#f39c12;color:#fff;" onclick="leaveGroup('${activeGroupId}')">
                        <i class="fa fa-sign-out"></i> <?= _l("chat_group_leave"); ?>
                    </button>` : ''}
                    ${staffCanDelete ? `<button class="history-action-btn danger" onclick="deleteGroup(this)" data-group-id="${activeGroupId}" data-group-name="${activeGroupName}">
                        <i class="fa fa-trash"></i> <?= _l("chat_delete_group_text"); ?>
                    </button>` : ''}
                </div>
            </div>
        `;

    group_messages_active_selector.html(modernPanel);

    // Enter key to save rename, Escape to cancel
    $("#grp-rename-field").on("keydown", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        saveGroupRename(activeGroupId);
      }
      if (e.which === 27) {
        e.preventDefault();
        cancelGroupRename();
      }
    });

    // Initialize tab functionality
    initializeGroupTabs();

    // Load group members with remove functionality
    loadGroupMembers(activeGroupId);

    // Initialize collapsible sections
    initializeCollapsibleSections();

    // Ensure openImageModal is available globally for groups
    if (typeof window.openImageModal === 'undefined') {
      window.openImageModal = function (imageUrl, filename) {
        // Create modal HTML
        var modalHtml = `
                    <div id="global-image-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                        <div style="position: relative; max-width: 90%; max-height: 90%; background: white; border-radius: 8px; overflow: hidden;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                                <h5 style="margin: 0; font-size: 16px; color: #333;">${filename || '<?= _l('chat_image_preview'); ?>'}</h5>
                                <button onclick="closeGlobalImageModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #666;">&times;</button>
                            </div>
                            <div style="padding: 20px; text-align: center; max-height: 70vh; overflow: auto;">
                                <img src="${imageUrl}" alt="${filename || 'Image'}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <div style="padding: 15px; background: #f8f9fa; border-top: 1px solid #dee2e6; display: flex; gap: 10px; justify-content: flex-end;">
                                <a href="${imageUrl}" download="${filename || 'image'}" style="padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">
                                    <i class="fa fa-download"></i> Download
                                </a>
                                <a href="${imageUrl}" target="_blank" style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">
                                    <i class="fa fa-external-link"></i> Open in New Tab
                                </a>
                            </div>
                        </div>
                    </div>
                `;

        // Remove existing modal
        $('#global-image-modal').remove();

        // Add modal to body
        $('body').append(modalHtml);

        // Close on ESC key
        $(document).on('keydown.imageModal', function (e) {
          if (e.key === 'Escape') {
            closeGlobalImageModal();
          }
        });

        // Close on click outside
        $('#global-image-modal').on('click', function (e) {
          if (e.target === this) {
            closeGlobalImageModal();
          }
        });
      };

      window.closeGlobalImageModal = function () {
        $('#global-image-modal').remove();
        $(document).off('keydown.imageModal');
      };
    }
  }

  /*---------------* Initialize collapsible sections *---------------*/
  function initializeCollapsibleSections() {
    // Set initial state - both sections collapsed to save space
    $('#members-content').hide();
    $('#shared-content').hide();
    $('#members-toggle').addClass('fa-chevron-right').removeClass('fa-chevron-down');
    $('#shared-toggle').addClass('fa-chevron-right').removeClass('fa-chevron-down');
  }

  /*---------------* Toggle section visibility *---------------*/
  function toggleSection(sectionName) {
    var content = $('#' + sectionName + '-content');
    var toggle = $('#' + sectionName + '-toggle');

    if (content.is(':visible')) {
      content.slideUp(300);
      toggle.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
      content.slideDown(300);
      toggle.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
  }

  // Make toggleSection globally available
  window.toggleSection = toggleSection;

  /*---------------* Load group members with remove functionality *---------------*/
  function loadGroupMembers(groupId) {
    if (!groupId) return;

    var $membersContainer = $('#modern-group-members');
    var members = [];
    var currentUserName = "<?php echo trim(get_staff_full_name()); ?>";

    // Preferred: use global members data with full info (avatar, id, name)
    if (window.currentGroupMembers && window.currentGroupMembers.length > 0) {
      window.currentGroupMembers.forEach(function (user) {
        // Use backend-generated avatar_url (handles missing thumbnails) or fallback
        var avatarUrl = user.avatar_url || fetchUserAvatar(user.member_id, user.profile_image);
        members.push({
          name: user.firstname + ' ' + user.lastname,
          id: user.member_id,
          avatar: avatarUrl,
          initials: (user.firstname.charAt(0) + user.lastname.charAt(0)).toUpperCase()
        });
      });
    }

    // Fallback: try old group_members structure
    if (members.length === 0) {
      $('.chat_group_options .group_members .members_list').each(function () {
        var memberName = $(this).find('a').text().trim();
        var memberId = $(this).attr('id').replace('member_', '');
        if (memberName) {
          var parts = memberName.split(' ');
          members.push({
            name: memberName,
            id: memberId,
            avatar: "<?php echo base_url(); ?>assets/images/user-placeholder.jpg",
            initials: ((parts[0] || '').charAt(0) + (parts[1] || '').charAt(0)).toUpperCase()
          });
        }
      });
    }

    // Add current user if missing
    var currentUserExists = members.some(function (m) {
      return m.name === currentUserName || m.id == userSessionId;
    });
    if (!currentUserExists) {
      var nameParts = currentUserName.split(' ');
      var currentAvatar = $("#sidepanel #profile #profile-img").attr("src") || "<?php echo base_url(); ?>assets/images/user-placeholder.jpg";
      members.unshift({
        name: currentUserName,
        id: userSessionId,
        avatar: currentAvatar,
        initials: ((nameParts[0] || '').charAt(0) + (nameParts[1] || '').charAt(0)).toUpperCase()
      });
    }

    if (members.length === 0) {
      $membersContainer.html('<div class="no-content"><?= _l("chat_group_empty"); ?></div>');
      return;
    }

    var membersHtml = '<div class="members-grid">';
    members.forEach(function (member) {
      var isCurrentUser = member.id == userSessionId;
      var profileUrl = "<?php echo admin_url('profile/'); ?>" + member.id;
      var displayName = isCurrentUser ? member.name + ' (<?= _l("chat_you_text"); ?>)' : member.name;

      membersHtml += `
                    <div class="member-item">
                        <a href="${profileUrl}" target="_blank" class="member-avatar-link">
                            <div class="member-avatar">
                                <img src="${member.avatar}" alt="${member.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="member-avatar-fallback" style="display:none;">${member.initials}</div>
                            </div>
                        </a>
                        <div class="member-info">
                            <a href="${profileUrl}" target="_blank" class="member-name-link">
                                <div class="member-name">${displayName}</div>
                            </a>
                            ${isCurrentUser ? '<div class="member-role"><?= _l("chat_group_admin_text"); ?></div>' : ''}
                        </div>
                        ${!isCurrentUser ? `
                        <div class="member-actions">
                            <button class="remove-member-btn" onclick="removeMemberFromGroup('${groupId}', '${member.name}', '${member.id || ''}')" data-toggle="tooltip" title="<?= _l('chat_group_remove_member'); ?>">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
    });
    membersHtml += '</div>';

    $membersContainer.html(membersHtml);
    // Re-init tooltips
    $membersContainer.find('[data-toggle="tooltip"]').tooltip();
  }

  /*---------------* Add member to DOM *---------------*/
  function addMemberToDOM(memberName, memberId, groupId, profileImage) {
    var $membersContainer = $('#modern-group-members');

    // Remove "no members" message if it exists
    $membersContainer.find('.no-content').remove();

    // Check if member already exists
    var memberExists = $('.member-item').filter(function () {
      return $(this).find('.member-name').text().trim() === memberName;
    }).length > 0;

    if (memberExists) {
      return;
    }

    // Add to members grid or create it if it doesn't exist
    var $membersGrid = $membersContainer.find('.members-grid');
    if ($membersGrid.length === 0) {
      $membersContainer.html('<div class="members-grid"></div>');
      $membersGrid = $membersContainer.find('.members-grid');
    }

    var avatarUrl = "<?php echo base_url(); ?>assets/images/user-placeholder.jpg";
    if (profileImage && profileImage !== 'null' && profileImage.trim() !== '') {
      avatarUrl = "<?php echo base_url(); ?>uploads/staff_profile_images/" + memberId + "/thumb_" + profileImage;
    }
    var nameParts = memberName.split(' ');
    var initials = ((nameParts[0] || '').charAt(0) + (nameParts[1] || '').charAt(0)).toUpperCase();
    var profileUrl = "<?php echo admin_url('profile/'); ?>" + memberId;

    var memberHtml = `
            <div class="member-item" style="display: none;">
                <a href="${profileUrl}" target="_blank" class="member-avatar-link">
                    <div class="member-avatar">
                        <img src="${avatarUrl}" alt="${memberName}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="member-avatar-fallback" style="display:none;">${initials}</div>
                    </div>
                </a>
                <div class="member-info">
                    <a href="${profileUrl}" target="_blank" class="member-name-link">
                        <div class="member-name">${memberName}</div>
                    </a>
                </div>
                <div class="member-actions">
                    <button class="remove-member-btn" onclick="removeMemberFromGroup('${groupId}', '${memberName}', '${memberId}')" data-toggle="tooltip" title="<?= _l('chat_group_remove_member'); ?>">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        `;

    $membersGrid.append(memberHtml);
    $membersGrid.find('.member-item:last').fadeIn(300);

    // Update the header avatars dynamically
    if (window.currentGroupMembers) {
      // Add new member to global members array
      window.currentGroupMembers.push({
        firstname: memberName.split(' ')[0] || memberName,
        lastname: memberName.split(' ')[1] || '',
        member_id: memberId,
        profile_image: profileImage
      });

      // Refresh the stacked avatars
      generateStackedAvatars(window.currentGroupMembers, groupId);
    }
  }

  // Make addMemberToDOM globally available
  window.addMemberToDOM = addMemberToDOM;

  /*---------------* Generate stacked avatars for group members *---------------*/
  function generateStackedAvatars(users, groupId) {
    const maxVisible = 5; // Show max 5 avatars
    const container = $("#group-stacked-avatars");

    if (!container.length) return;

    container.empty();

    // Add current user first if not already in list
    var currentUserName = "<?php echo trim(get_staff_full_name()); ?>";
    var currentUserExists = users.some(user =>
      (user.firstname + ' ' + user.lastname) === currentUserName ||
      user.member_id == userSessionId
    );

    var allUsers = [...users];
    if (!currentUserExists) {
      allUsers.unshift({
        firstname: currentUserName.split(' ')[0] || '<?= _l("chat_you_text"); ?>',
        lastname: currentUserName.split(' ')[1] || '',
        member_id: userSessionId,
        profile_image: null
      });
    }

    // Show first 5 members
    const visibleUsers = allUsers.slice(0, maxVisible);
    const remainingCount = Math.max(0, allUsers.length - maxVisible);

    // Generate avatar elements
    visibleUsers.forEach((user, index) => {
      const isCurrentUser = user.member_id == userSessionId;
      const userName = user.firstname + ' ' + user.lastname;
      const initials = (user.firstname.charAt(0) + user.lastname.charAt(0)).toUpperCase();

      // Use backend-generated avatar_url or fetchUserAvatar fallback
      let avatarUrl = user.avatar_url || fetchUserAvatar(user.member_id, user.profile_image);

      const profileUrl = "<?php echo admin_url('profile/'); ?>" + user.member_id;
      const avatarHtml = `
                <a href="${profileUrl}" target="_blank" class="stacked-avatar-link" data-toggle="tooltip" title="${userName}${isCurrentUser ? ' (<?= _l("chat_you_text"); ?>)' : ''}" style="z-index: ${maxVisible - index}">
                    <div class="stacked-avatar">
                        <img src="${avatarUrl}" alt="${userName}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-initials" style="display:none;">${initials}</div>
                    </div>
                </a>
            `;

      container.append(avatarHtml);
    });

    // Add "+X more" indicator if there are more users
    if (remainingCount > 0) {
      container.append(`
                <div class="stacked-avatar more-members" data-toggle="tooltip" title="<?= _l('chat_view_all_members'); ?>" onclick="showAllGroupMembers('${groupId}')">
                    <div class="avatar-initials">+${remainingCount}</div>
                </div>
            `);
    }

    // Add "+" button for adding new members
    container.append(`
            <div class="stacked-avatar add-member" data-toggle="tooltip" title="<?= _l('chat_add_new_member'); ?>" onclick="openAddMemberModal('${groupId}')">
                <div class="avatar-initials add-icon">
                    <i class="fa fa-plus"></i>
                </div>
            </div>
        `);

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
  }

  /*---------------* Show all group members modal/dropdown *---------------*/
  function showAllGroupMembers(groupId) {
    // This can open the group settings or a members list modal
    // For now, let's just trigger the group settings
    if (typeof showGroupChatOptions === 'function') {
      showGroupChatOptions();
    }
  }

  // Expose functions globally for inline onclick handlers
  window.generateStackedAvatars = generateStackedAvatars;
  window.showAllGroupMembers = showAllGroupMembers;
  window.loadGroupPreviews = loadGroupPreviews;
  window.removeMemberFromGroup = removeMemberFromGroup;
  window.showGroupChatOptions = showGroupChatOptions;
  window.openGroupSearchModal = openGroupSearchModal;
  window.openAddMemberModal = openAddMemberModal;
  window.deleteGroup = deleteGroup;
  window.leaveGroup = leaveGroup;
  window.renameGroup = renameGroup;
  window.toggleGroupRenameInput = toggleGroupRenameInput;
  window.saveGroupRename = saveGroupRename;
  window.cancelGroupRename = cancelGroupRename;

  /*---------------* Remove member from group *---------------*/
  function removeMemberFromGroup(groupId, memberName, memberId) {
    if (!memberId) {
      alert_float("danger", "<?= _l('chat_error_float'); ?>");
      return;
    }

    if (confirm('<?= _l("chat_remove_member_confirm"); ?>'.replace('%s', memberName))) {
      var activeGroupName = $("#frame .chat_groups_list li.active .group_name_main").text();

      // Sanitize group name for Pusher channel
      var sanitizedGroupName = activeGroupName
        .replace(/[^a-zA-Z0-9_\-=@,.;]/g, '_')
        .replace(/_{2,}/g, '_')
        .substring(0, 50);

      $.post(prchatSettings.removeChatGroupUser, {
        id: memberId,
        group_id: groupId,
        group_name: sanitizedGroupName
      }).done(function (response) {
        try {
          var result = JSON.parse(response);
          if (result.response === 'success') {
            // Remove the member from DOM
            $('.remove-member-btn').filter(function () {
              return $(this).attr('onclick').includes("'" + memberId + "'");
            }).closest('.member-item').fadeOut(300, function () {
              $(this).remove();

              if ($('#modern-group-members .member-item').length === 0) {
                $('#modern-group-members').html('<div class="no-content"><?= _l("chat_group_empty"); ?></div>');
              }
            });

            // Update the header avatars dynamically
            if (window.currentGroupMembers) {
              window.currentGroupMembers = window.currentGroupMembers.filter(function (user) {
                return user.member_id != memberId;
              });
              generateStackedAvatars(window.currentGroupMembers, groupId);
            }

            alert_float("success", '<?= _l("chat_member_removed"); ?>'.replace('%s', memberName));
          } else {
            alert_float("danger", '<?= _l("chat_remove_member_failed"); ?>: ' + (result.message || result.error || ''));
          }
        } catch (e) {
          alert_float("danger", "<?= _l('chat_error_float'); ?>");
        }
      });
    }
  }

  /*---------------* Open group search modal *---------------*/
  function openGroupSearchModal(groupId, groupName) {
    var modalHtml = `
            <div class="modal right fade" id="group_search_modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-xl side-modal-dialog" role="any">
                    <div class="modal-content side-modal-content">
                        <div class="history-modal-header">
                            <div class="history-header-left">
                                <div class="history-header-icon"><i class="fa-solid fa-user-group"></i></div>
                                <h4>${groupName}</h4>
                            </div>
                            <button type="button" class="history-close-btn" data-dismiss="modal" aria-label="<?= _l('chat_close_button'); ?>">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="history-modal-body">
                            <div class="history-search-bar">
                                <i class="fa fa-search"></i>
                                <input type="text" id="group_search_input" placeholder="<?= _l('chat_messages_search_here'); ?>" autocomplete="off">
                            </div>
                            <div id="group_search_results" class="history-messages" style="flex:1; overflow-y:auto;">
                                <div class="loading-state history-loading" style="display: none;">
                                    <i class="fa fa-spinner fa-spin"></i> <?= _l('loading'); ?>...
                                </div>
                                <div class="no-results history-empty-tab" style="display: none;">
                                    <i class="fa fa-search"></i>
                                    <p><?= _l('chat_sorry_no_data'); ?></p>
                                </div>
                                <div class="results-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

    // Remove existing modal and add new one
    $('#group_search_modal').remove();
    $('body').append(modalHtml);

    // Show the modal
    $('#group_search_modal').modal('show');

    // Focus on search input
    $('#group_search_modal').on('shown.bs.modal', function () {
      $('#group_search_input').focus();
    });

    // Handle search input
    var searchTimeout;
    $('#group_search_input').on('input', function () {
      var searchTerm = $(this).val().trim();

      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        if (searchTerm.length >= 2) {
          searchGroupMessages(groupId, searchTerm);
        } else {
          $('#group_search_results .results-container').empty();
          $('#group_search_results .no-results').hide();
        }
      }, 300);
    });

    // Clean up on modal close
    $('#group_search_modal').on('hidden.bs.modal', function () {
      $(this).remove();
    });
  }

  /*---------------* Search group messages *---------------*/
  function searchGroupMessages(groupId, searchTerm) {
    var $loadingState = $('#group_search_results .loading-state');
    var $noResults = $('#group_search_results .no-results');
    var $resultsContainer = $('#group_search_results .results-container');

    $loadingState.show();
    $noResults.hide();
    $resultsContainer.empty();

    // Get all messages from the current group chat
    var messages = [];

    // Search in modern message structure
    $('#frame .group_messages .chat_group_messages .modern-messages-container .modern-message-item').each(function () {
      var $message = $(this);
      var messageText = '';
      var messageHtml = '';
      var sender = '';
      var timestamp = '';

      var $textEl = $message.find('.modern-message-text');
      if ($textEl.length) {
        messageText = $textEl.text().trim();
        messageHtml = $textEl.html();
      }

      timestamp = $message.find('.modern-message-time').text().trim();

      if ($message.hasClass('modern-message-sent')) {
        sender = '<?= _l('chat_you_text'); ?>';
      } else {
        sender = $message.attr('data-sender-name') || 'Unknown';
      }

      if (messageText && messageText.toLowerCase().includes(searchTerm.toLowerCase())) {
        messages.push({
          text: messageText,
          html: messageHtml,
          sender: sender,
          timestamp: timestamp,
          element: $message
        });
      }
    });

    $loadingState.hide();

    if (messages.length === 0) {
      $noResults.show();
    } else {
      var resultsHtml = '';
      messages.forEach(function (msg) {
        var highlightedText = msg.html.replace(
          new RegExp('(' + searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'),
          '<mark>$1</mark>'
        );

        var isOwn = (msg.sender === '<?= _l('chat_you_text'); ?>' || msg.sender === '<?= trim(get_staff_full_name()); ?>');
        resultsHtml += `
                    <div class="history-msg${isOwn ? ' own' : ''}">
                        <div class="history-msg-body">
                            <div class="history-msg-meta">
                                <span class="history-msg-sender">${msg.sender}</span>
                                <span class="history-msg-time">${msg.timestamp}</span>
                            </div>
                            <div class="history-msg-text">${highlightedText}</div>
                        </div>
                    </div>
                `;
      });

      $resultsContainer.html(resultsHtml);

      // Add click handlers to scroll to original message
      $resultsContainer.find('.history-msg').on('click', function () {
        var index = $(this).index();
        var targetMessage = messages[index].element;

        // Close modal
        $('#group_search_modal').modal('hide');

        // Scroll to message in the group messages container
        if (targetMessage.length) {
          var $container = $('#frame .group_messages');
          var scrollTop = $container.scrollTop() + targetMessage.position().top - 100;
          $container.animate({
            scrollTop: scrollTop
          }, 500);

          // Highlight the message temporarily
          targetMessage.addClass('highlighted-message');
          setTimeout(function () {
            targetMessage.removeClass('highlighted-message');
          }, 3000);
        }
      });
    }
  }

  /*---------------* Open add member modal *---------------*/
  function openAddMemberModal(groupId) {
    $(".modal_container").load(prchatSettings.addNewChatGroupMembersModal, {
      group_id: groupId
    }, function (res) {
      if ($("#add_members_modal").is(":hidden")) {
        $("#add_members_modal").modal({
          show: true
        });
      }
    });
  }

  /*---------------* Initialize tab functionality for group panel *---------------*/
  function initializeGroupTabs() {
    // Tab switching functionality
    $(document).on('click', '.grp-opt-tabs .history-tab-btn', function (e) {
      e.preventDefault();

      var targetTab = $(this).data('tab');
      var $tabContent = $(this).closest('.grp-opt-section-body').find('.grp-opt-tab-content');

      // Update active button
      $(this).siblings('.history-tab-btn').removeClass('active');
      $(this).addClass('active');

      // Update active tab pane
      $tabContent.find('.tab-pane').removeClass('active');
      $tabContent.find('#' + targetTab + '-tab').addClass('active');

      // Load content based on tab
      if (targetTab === 'photos') {
        loadGroupPhotos();
      } else if (targetTab === 'files') {
        loadGroupFiles();
      }
    });
  }

  /*---------------* Load group photos for modern panel *---------------*/
  function loadGroupPhotos() {
    var group_id = $("#frame .chat_groups_list li.active").attr("id");
    if (!group_id) return;

    var $photosContainer = $('.shared-photos-grid');
    $photosContainer.html('<div class="loading-state"><i class="fa fa-spinner fa-spin"></i> <?= _l("loading"); ?>...</div>');

    getGroupSharedFiles(group_id, function () {
      // Parse photos from the main_div_shared_files
      var $mainDiv = $('.main_div_shared_files');
      var photos = [];

      // Try different selectors to find photos
      $mainDiv.find('a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".webp"]').each(function () {
        var href = $(this).attr('href');
        if (href && isImageFile(href.split('/').pop())) {
          var filename = href.split('/').pop();
          photos.push({
            url: href,
            filename: filename
          });
        }
      });

      // Also check for img tags directly
      $mainDiv.find('img').each(function () {
        var src = $(this).attr('src');
        if (src && isImageFile(src.split('/').pop())) {
          var filename = src.split('/').pop();
          photos.push({
            url: src,
            filename: filename
          });
        }
      });

      // Remove duplicates
      photos = photos.filter((photo, index, self) =>
        index === self.findIndex((p) => p.url === photo.url)
      );

      if (photos.length === 0) {
        $photosContainer.html('<div class="no-content"><i class="fa fa-image"></i><p><?= _l("chat_no_photos_shared"); ?></p></div>');
      } else {
        var html = '';
        photos.forEach(function (photo) {
          html += `
                        <div class="photo-item" onclick="openImageModal('${photo.url}', '${photo.filename}')">
                            <img src="${photo.url}" alt="${photo.filename}" loading="lazy">
                            <div class="photo-overlay">
                                <i class="fa fa-search-plus"></i>
                            </div>
                        </div>
                    `;
        });
        $photosContainer.html(html);
      }
    });
  }

  /*---------------* Load group files for modern panel *---------------*/
  function loadGroupFiles() {
    var group_id = $("#frame .chat_groups_list li.active").attr("id");
    if (!group_id) return;

    var $filesContainer = $('.shared-files-list');
    $filesContainer.html('<div class="loading-state"><i class="fa fa-spinner fa-spin"></i> <?= _l("loading"); ?>...</div>');

    getGroupSharedFiles(group_id, function () {
      // Parse files from the main_div_shared_files
      var $mainDiv = $('.main_div_shared_files');
      var files = [];

      // Try different selectors to find files
      $mainDiv.find('a[href*="/modules/prchat/uploads/"]').each(function () {
        var href = $(this).attr('href');
        var filename = href.split('/').pop(); // Always use filename from URL, not text content

        // Skip if it's an image file (those go in photos tab)
        if (href && filename && !isImageFile(filename)) {
          files.push({
            url: href,
            filename: filename
          });
        }
      });

      // Also check for direct file links
      $mainDiv.find('a').each(function () {
        var href = $(this).attr('href');
        if (href && href.includes('/modules/prchat/uploads/')) {
          var filename = href.split('/').pop();
          if (filename && !isImageFile(filename)) {
            var existingFile = files.find(f => f.url === href);
            if (!existingFile) {
              files.push({
                url: href,
                filename: filename
              });
            }
          }
        }
      });



      if (files.length === 0) {
        $filesContainer.html('<div class="no-content"><i class="fa fa-file"></i><p><?= _l("chat_no_files_shared"); ?></p></div>');
      } else {
        var html = '';
        files.forEach(function (file) {
          var fileExt = file.filename.split('.').pop().toUpperCase();
          var fileIcon = getFileIcon(file.filename);

          html += `
                        <div class="file-item">
                            <div class="file-icon">
                                <i class="${fileIcon}"></i>
                            </div>
                            <div class="file-details">
                                <div class="file-name" title="${file.filename}">${file.filename}</div>
                                <div class="file-meta">${fileExt}</div>
                            </div>
                            <div class="file-actions">
                                <a href="${file.url}" target="_blank" class="file-action-btn" data-toggle="tooltip" title="<?= _l('chat_open_text'); ?>">
                                    <i class="fa fa-external-link"></i>
                                </a>
                                <a href="${file.url}" download="${file.filename}" class="file-action-btn" data-toggle="tooltip" title="<?= _l('chat_download_text'); ?>">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    `;
        });
        $filesContainer.html(html);
      }
    });
  }

  /*---------------* Helper function to check if file is image *---------------*/
  function isImageFile(filename) {
    var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    var extension = filename.split('.').pop().toLowerCase();
    return imageExtensions.includes(extension);
  }

  /*---------------* Helper: decode HTML entities *---------------*/
  function decodeHtmlEntities(str) {
    var div = document.createElement('div');
    div.innerHTML = str;
    return div.textContent || div.innerText || '';
  }

  /*---------------* Helper: clean file/image URLs for preview *---------------*/
  function cleanPreviewForFiles(text) {
    if (!text) return text;
    // If text is just a URL (file/image upload)
    if (text.match(/^https?:\/\/.*\.(gif|jpg|jpeg|png|svg|webp)$/i)) {
      return '<?= _l("chat_photos_text"); ?>';
    }
    if (text.match(/^https?:\/\/.*\/modules\/prchat\/uploads\//i)) {
      return '<?= _l("chat_file_text"); ?>';
    }
    return text;
  }

  /*---------------* Load group previews (last messages) - single batch request *---------------*/
  function loadGroupPreviews() {
    var groupIds = [];
    $(".group_selector").each(function () {
      var gid = $(this).attr("id");
      if (gid) groupIds.push(gid);
    });

    if (groupIds.length === 0) return;

    $.ajax({
      url: prchatSettings.getGroupPreviews,
      type: "GET",
      data: {
        group_ids: groupIds.join(",")
      },
      success: function (previews) {
        if (typeof previews === "string") {
          try {
            previews = JSON.parse(previews);
          } catch (e) {
            previews = {};
          }
        }

        // First, store time_sent as data attribute on each group for sorting
        $(".group_selector").each(function () {
          var groupId = $(this).attr("id");
          var previewElement = $(this).find(".group_preview");
          var timeElement = $(this).find(".group_time");
          if (!groupId || previewElement.length === 0) return;

          var data = previews[groupId];
          if (data && data.message) {
            var isMine = (data.sender_id == userSessionId);
            // Store raw time_sent for sorting, but don't move to top yet
            $(this).attr("data-last-message-time", data.time_sent || "");
            var groupElement = $(this);
            var gPreviewEl = groupElement.find(".group_preview");
            var gTimeEl = groupElement.find(".group_time");

            var messageText = PrchatSafeRenderer.getSidebarPreview(data.message, data.message);
            var previewText = "";
            if (isMine) {
              previewText = "<?= _l('chat_message_you'); ?> " + messageText;
            } else {
              var senderFirst = data.sender_fullname ? data.sender_fullname.split(' ')[0] : '';
              previewText = senderFirst ? senderFirst + ': ' + messageText : messageText;
            }
            if (previewText.length > 50) {
              previewText = previewText.substring(0, 50) + "...";
            }
            gPreviewEl.text(previewText).show();
            if (data.time_sent) {
              gTimeEl.text(moment(data.time_sent).fromNow()).show();
            } else {
              gTimeEl.hide();
            }
          } else {
            $(this).attr("data-last-message-time", "");
            previewElement.text("<?= _l('chat_no_messages_yet'); ?>");
            timeElement.hide();
          }
        });

        // Add pin/mute icons to groups
        if (typeof pinnedGroups !== 'undefined' || typeof mutedGroups !== 'undefined') {
          $(".group_selector").each(function () {
            var gid = $(this).attr("id");
            $(this).find(".contact-pin-icon, .contact-mute-icon").remove();
            if (typeof pinnedGroups !== 'undefined' && pinnedGroups.indexOf(String(gid)) !== -1) {
              $(this).prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
              $(this).attr("data-pinned", "1");
            }
            if (typeof mutedGroups !== 'undefined' && mutedGroups.indexOf(String(gid)) !== -1) {
              $(this).prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
            }
          });
        }

        // Sort groups by most recent message time (newest first), pinned first
        var $groupsList = $(".chat_groups_list");
        var $groups = $groupsList.children(".group_selector").toArray();
        $groups.sort(function (a, b) {
          // Pinned groups first
          var aPinned = $(a).attr("data-pinned") === "1" ? 1 : 0;
          var bPinned = $(b).attr("data-pinned") === "1" ? 1 : 0;
          if (aPinned !== bPinned) return bPinned - aPinned;

          var aTime = $(a).attr("data-last-message-time");
          var bTime = $(b).attr("data-last-message-time");
          var aTs = aTime ? new Date(aTime).getTime() : 0;
          var bTs = bTime ? new Date(bTime).getTime() : 0;
          return bTs - aTs;
        });
        $groupsList.append($groups);
      },
      error: function () {
        $(".group_selector").each(function () {
          $(this).find(".group_preview").text("<?= _l('chat_no_messages_yet'); ?>");
          $(this).find(".group_time").hide();
        });
      }
    });
  }

  /*---------------* Update group preview with last message *---------------*/
  function updateGroupPreview(groupId, message, isSentByMe, timeString, senderName) {
    var groupElement = $(".group_selector#" + groupId);
    var previewElement = groupElement.find(".group_preview");
    var timeElement = groupElement.find(".group_time");

    if (previewElement.length > 0) {
      var messageText = PrchatSafeRenderer.getSidebarPreview(message, message);

      var previewText = "";
      if (isSentByMe) {
        previewText = "<?= _l('chat_message_you'); ?> " + messageText;
      } else {
        var senderFirst = senderName ? senderName.split(' ')[0] : '';
        previewText = senderFirst ? senderFirst + ': ' + messageText : messageText;
      }

      if (previewText.length > 50) {
        previewText = previewText.substring(0, 50) + "...";
      }

      previewElement.text(previewText).show();

      if (timeString) {
        var timeDiff = moment().diff(moment(timeString), 'seconds');
        // Show "now" only if the message was FROM another user (not from current user)
        if (timeDiff < 30 && !isSentByMe) {
          timeElement.text("now").show();
        } else {
          timeElement.text(moment(timeString).fromNow()).show();
        }
      } else {
        timeElement.hide();
      }

      // Update data attribute for sorting
      groupElement.attr("data-last-message-time", timeString || new Date().toISOString());

      // Move group to top of list (most recent conversation first)
      if (!groupElement.hasClass("active")) {
        groupElement.prependTo(".chat_groups_list");
      }
    }
  }

  /*---------------* Helper function to get file icon *---------------*/
  function getFileIcon(filename) {
    var extension = filename.split('.').pop().toLowerCase();
    var iconMap = {
      'pdf': 'fa fa-file-pdf',
      'doc': 'fa fa-file-word',
      'docx': 'fa fa-file-word',
      'xls': 'fa fa-file-excel',
      'xlsx': 'fa fa-file-excel',
      'ppt': 'fa fa-file-powerpoint',
      'pptx': 'fa fa-file-powerpoint',
      'zip': 'fa fa-file-archive',
      'rar': 'fa fa-file-archive',
      'txt': 'fa fa-file-text',
      'mp3': 'fa fa-file-audio',
      'mp4': 'fa fa-file-video',
      'avi': 'fa fa-file-video'
    };
    return iconMap[extension] || 'fa fa-file';
  }

  function sendMentionNotifications(from, channel, users, groupId) {
    var chatBoxValues = $("body").find(".group_chatbox").val();
    var isEveryoneMention = chatBoxValues.indexOf('@everyone') !== -1;

    // If not @everyone, filter out users not actually in the chatbox
    if (!isEveryoneMention) {
      users = users.filter(function (user) {
        return chatBoxValues.includes("@" + user.name);
      });
    }

    $.post(admin_url + "prchat/Prchat_Controller/pusherMentionEvent", {
      from: from,
      channel: channel,
      users: users,
      group_id: groupId || ''
    });
  }

  $(document).on('click', '.group_messages [data-chat-file="image"]', function (e) {
    e.preventDefault();
    var imageUrl = $(this).attr('data-file-url') || $(this).attr('href');
    var filename = $(this).attr('data-filename') || '';
    if (imageUrl && typeof window.openImageModal === 'function') {
      window.openImageModal(imageUrl, filename);
    }
  });

  $(document).on('click', '.group_messages .message-reply-context', function (e) {
    e.preventDefault();
    var originalId = $(this).attr('data-original-message-id');
    if (!originalId || originalId === '0') return;
    var targetMsg = $('.group_messages').find('[data-message-id="' + originalId + '"]');
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
      targetMsg.find('.modern-message-content').css({
        transition: 'background 0.3s',
        background: 'rgba(245,158,11,0.08)',
        borderRadius: '8px'
      });
      setTimeout(function () {
        targetMsg.css({
          transform: ''
        });
        if (bubble.length) bubble.css({
          borderColor: '',
          boxShadow: ''
        });
        targetMsg.find('.modern-message-content').css({
          background: ''
        });
      }, 1800);
    }
  });

  pusher.bind("message-reaction", function (data) {
    if (data && data.message_id && data.message_type === 'group') {
      if (typeof window.prchatRenderReactionPills === 'function') {
        var $msg = $('.chat_group_messages .modern-message-item[data-message-id="' + data.message_id + '"]');
        if ($msg.length) {
          $msg.find('.message-reactions').remove();
          var $bubble = $msg.find('.modern-message-bubble').first();
          var html = window.prchatRenderReactionPills(data.reactions, data.message_id, 'group');
          if (html) {
            $bubble.addClass('has-reactions');
            $bubble.append(html);
            $bubble.find('[data-toggle="tooltip"]').tooltip();
          } else {
            $bubble.removeClass('has-reactions');
          }
        }
      }
    }
  });
</script>

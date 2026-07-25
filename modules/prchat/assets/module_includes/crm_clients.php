<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
  (function () {

    "use strict";

    /*---------------* Pusher Trigger accessing channel *---------------*/
    var clientsChannel = pusher.subscribe("presence-clients");
    var customersUlFirst = "";
    var hiddenUnreadMessages = $(".invisibleUnread");
    var own_image_url = $(".icon.header-user-profile a img").prop("src");
    var clientOffsetPush = 0;
    var clientEndOfScroll = false;
    var lang_customer = "<?= _l('chat_lang_customer'); ?>";
    var lang_contact = "<?= _l('chat_lang_contact'); ?>";
    var l_say_hi = "<?= _l('chat_say_hi'); ?>";
    var checkForNewClientUnreadMessages = prchatSettings.getClientUnreadMessages;
    var myCustomers = "";

    var f = {
      fetchClients: function () {
        var customers = JSON.stringify(<?php get_staff_customers(); ?>);
        myCustomers = JSON.parse(customers);
        return myCustomers;
      },
      chatAppendCustomers: function (customers) {
        var dfd = new $.Deferred();
        var promise = dfd.promise();

        // Flatten: pass raw array (no groupBy needed)
        var flat = Array.isArray(customers) ? customers : [];
        if (typeof customers === 'object' && !Array.isArray(customers)) {
          flat = [];
          for (var k in customers) {
            flat = flat.concat(customers[k]);
          }
        }

        if (flat.length === 0) {
          $("form[name=clientMessagesForm] .message-input").hide();
          $(".chat_clients_list").append("<li class='text-center m-t-5 cp-5 bg-chat-primary'><h5><?= _l('chat_assigned_contacts'); ?></h5></li>");
          $("#clients_container .contacts-skeleton").addClass("hidden");
          $(".client_messages .messages-skeleton").addClass("hidden");
          // Still check for unread messages even with no assigned clients
          f.checkForUnreadMessages();
          return false;
        }

        // Wrap in a dummy group so initCustomersAppending iterates properly
        initCustomersAppending({
          all: flat
        });
        dfd.resolve(flat);

        promise.then(function () {
          $("#clients_container .contacts-skeleton").addClass("hidden");
          f.initAfterAppendClients();
        });
      },
      initAfterAppendClients: function () {
        if ($(".chat_clients_list > li.contact_name").length >= 30) {
          $(".chat_clients_list").after('<button type="button" onClick="fetchMoreClients(this)" class="btn btn-primary btn-block chat_load_more_clients"><?= _l('chat_load_more_clients'); ?></button');
        }

        f.checkForUnreadMessages();

        // Deep-link from floating notification: select contact and load messages (after tab switch at 300ms)
        var urlParams = new URLSearchParams(window.location.search);
        var deepTab = urlParams.get('tab');
        var deepContact = urlParams.get('contact');
        if (deepTab === 'crm_clients' && deepContact) {
          setTimeout(function () {
            var $contact = $(".chat_clients_list > li.contact_name#" + deepContact);
            if ($contact.length) {
              // Clear unread badge and notify server before/with the click
              $contact.find(".unread-notifications").remove();
              $contact.removeClass("has-unread-messages");
              f.updateUnreadMessages(deepContact);
              if (typeof clearTabBadge === 'function') {
                clearTabBadge('crm_clients');
              }
              $contact[0].click();
            }
          }, 2000);
        }
      },
      updateUnreadMessages: function (id) {
        $.post(prchatSettings.updateClientUnread, {
          id: id
        });
      },
      checkForUnreadMessages: function () {
        $.post(checkForNewClientUnreadMessages).done(function (r) {
          var contacts = "";
          r = JSON.parse(r);
          if (r.result !== false) {
            var totalUnreadClients = 0;
            $.each(r, function (i, sender) {
              var sender_id = sender.sender_id.replace("client_", "");
              var contactName = $("body").find(".contact_name#" + sender_id);
              totalUnreadClients += parseInt(sender.count_messages) || 0;

              if (contactName.length === 0 && sender.client_data !== null) {
                var contact_fullname = sender.client_data.firstname + " " + sender.client_data.lastname;

                // Create client contact structure for unread messages - match staff side
                var profile_image_url = sender.client_data.profile_image_url || contact_profile_image_url(sender.client_data.contact_id) || site_url + "/assets/images/user-placeholder.jpg";
                var unreadContactHtml = '<li class="contact_name" id="' + sender.client_data.contact_id + '" data-client-id="' + sender.client_data.client_id + '" data-company="' + (sender.client_data.company || '').replace(/"/g, '&quot;') + '">' +
                  '<div class="contact_name_content">' +
                  '<img class="chatContactImage" src="' + profile_image_url + '"/>' +
                  '<div class="contact_info">' +
                  '<div class="contact_name_main">' + contact_fullname + '</div>';

                if (sender.client_data.company) {
                  unreadContactHtml += '<small class="contact_company">' + sender.client_data.company + '</small>';
                }
                if (sender.client_data.title && sender.client_data.title.trim() !== "") {
                  unreadContactHtml += '<small class="contact_title">' + sender.client_data.title + '</small>';
                }

                unreadContactHtml += '<div class="contact_preview">' + l_say_hi + ' ' + contact_fullname + '</div>' +
                  '</div>' +
                  '<div class="contact_meta">' +
                  '<div class="contact_time"></div>' +
                  '</div>' +
                  '<span class="unread-notifications" data-badge="' + (sender.count_messages || 0) + '"></span>' +
                  '</div>' +
                  '</li>';

                $(unreadContactHtml).prependTo("#crm_clients .chat_clients_list");

                // Reorder contacts after adding new contact
                reorderContactList();

                clientsChannel.bind("pusher:subscription_succeeded", function (members) {
                  if (members.get(sender.sender_id)) {
                    updateClientContactStatus(sender.client_data.contact_id, true);
                  }
                });
              }

              // Only add notifications if this contact doesn't already have them
              var messageCount = sender.count_messages || 0;
              if (!contactName.find(".unread-notifications").length) {
                contactName.find(".contact_name_content").append("<span class=\"unread-notifications\" data-badge=\"" + messageCount + "\"></span>");
              } else {
                // Update existing notification count
                contactName.find(".unread-notifications").attr("data-badge", messageCount);
              }

              // Add has-unread-messages class for browsers without :has() support
              if (messageCount > 0) {
                contactName.addClass("has-unread-messages");
              }

              contactName.prependTo(".chat_clients_list");
            });
            // Set the tab badge to total unread count (not just +1)
            if (totalUnreadClients > 0) {
              var tabEl = $("#frame #sidepanel .crm_clients a");
              var badge = tabEl.find(".tab-unread-badge");
              if (badge.length === 0) {
                tabEl.append('<span class="tab-unread-badge">' + totalUnreadClients + '</span>');
              } else {
                badge.text(totalUnreadClients);
              }
            }
          }
        });
      }
    };


    // Get clients and contacts list
    var customerData = f.fetchClients();
    if (customerData.customers !== "none") f.chatAppendCustomers(customerData.customers);


    // DOM Functions — flat contact list (no company accordion)

    /*---------------* Handle Clients DOM *---------------*/
    var _clientPreviewsLoaded = false;

    $("#frame li.crm_clients a").on("click", function () {
      // Show chat-add-btn when switching to clients tab
      $("#chat-add-btn").show();

      // Clear tab badges when clients tab is clicked
      clearTabBadge('staff');
      clearTabBadge('crm_clients');

      // Lazy-load client contact previews on first tab activation
      var isFirstLoad = !_clientPreviewsLoaded;
      if (!_clientPreviewsLoaded) {
        _clientPreviewsLoaded = true;
        loadContactPreviews();
      }

      $("#mainFilterDiv").hide();

      // Set clients tab body class and ensure profile elements are visible
      $("body").removeClass("staff-tab groups-tab").addClass("clients-tab");

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

      // Force show profile wrapper on clients tab
      $("#frame .content .contact-profile .profile-wrapper").show();

      // Make sure dropdown is closed
      $("#profile-dropdown").removeClass("show");
      $(".dropdown-arrow").css("transform", "rotate(0deg)");

      if (!isFirstLoad && !window.matchMedia("only screen and (max-width: 735px)").matches && !window.__prchatOpenClientFromNotification) {
        selectBestClientContact();
      }

      $("#frame form[name=groupMessagesForm],#frame form[name=pusherMessagesForm],#frame .content .group_messages, #frame .groupOptions").hide();
      $("#frame .chat_group_options.active")
        .hide()
        .removeClass("active")
        .css(typeof window.prchatSlidePanelClosedCss === "function" ? window.prchatSlidePanelClosedCss() : {
          "right": "-360px",
          "width": "360px"
        });


      $("#search #search_field").attr("id", "search_clients_field");
      $("#search #search_groups_field").attr("id", "search_clients_field");
      var $clientsField = $("#search #search_clients_field");


      $clientsField.attr("placeholder", "<?= _l('chat_search_customers'); ?>").prop("disabled", false).prop("readonly", false).css({
        "opacity": "1",
        "cursor": "text",
        "background-color": ""
      });


      // Re-enable all interactions with the search field
      $clientsField.off('focus blur click keyup keydown input');

      chat_contact_profile_img.hide();
      chat_contact_profile_img.next().hide();
      chat_contact_profile_img.next().next().hide();
      chat_social_media.hide();
      chat_content_messages.hide();
      $(".client_messages").show();

      $(".group_members_inline").remove();

      // Hide staff composer (do NOT apply shared-files slide CSS — that fixed 360px stuck on the form)
      $("#frame #pusherMessagesForm").hide();
      $("#frame #sharedFiles").hide().css(typeof window.prchatSlidePanelClosedCss === "function" ? window.prchatSlidePanelClosedCss() : {
        "right": "-360px",
        "width": "360px"
      });
      clearTabBadge('crm_clients');

      $("#frame .content .client_messages, #frame form[name=clientMessagesForm]").show();

    });

    /*---------------* Search customers *---------------*/
    var crmClientsParent = $("#crm_clients ul:not(.dropdown-menu)");
    var crmClientsChildren = $("#crm_clients ul:not(.dropdown-menu) li");

    $("body").on("focusout", "#search_clients_field", function () {
      var $field = $(this);
      setTimeout(function () {
        $field.val("");
        $(".chat_clients_list > li.contact_name").show();
      }, 200);
    });

    $("body").on("keyup", "#search_clients_field", _debounce(function (e) {
      var value = $.trim($(this).val().toLowerCase());
      var searchInDatabase = false;

      if (value === "" || e.keyCode === 8 || e.keyCode === 46) {
        $(".chat_clients_list > li.contact_name").show();
        if (value === "") return;
      }

      if (e.keyCode === 27) {
        $("#search_clients_field").val("");
        $(".chat_clients_list > li.contact_name").show();
        return false;
      }

      if (value.length >= 3) {
        $(".chat_clients_list > li.contact_name").each(function () {
          var contactEl = $(this);
          var contactName = contactEl.find(".contact_name_main").text().toLowerCase();
          var companyName = (contactEl.attr("data-company") || "").toLowerCase();
          if (contactName.indexOf(value) > -1 || companyName.indexOf(value) > -1) {
            contactEl.show();
          } else {
            contactEl.hide();
          }
        });

        if ($(".chat_clients_list > li.contact_name:visible").length === 0) {
          searchInDatabase = true;
        }

        if (searchInDatabase) {
          $.ajax({
            type: "POST",
            url: "<?php echo site_url('prchat/Prchat_ClientsController/searchClients'); ?>",
            cache: false,
            data: {
              "search": $("#search_clients_field").val()
            },
            success: function (response) {
              var obj = JSON.parse(response);
              if (obj.length > 0) {
                try {
                  var items = [];
                  $.each(obj, function (i, val) {
                    var contact_id = val.contact_id,
                      client_id = val.client_id,
                      company = val.company,
                      contact_fullname = val.firstname + " " + val.lastname;

                    if ($(".chat_clients_list > li.contact_name#" + contact_id).length) return;

                    var isOnline = clientsChannel.members.get("client_" + contact_id) ? "contactActive" : "";

                    var html = '<li class="contact_name ' + isOnline + '" id="' + contact_id + '" data-client-id="' + client_id + '" data-company="' + (company || '').replace(/"/g, '&quot;') + '">' +
                      '<div class="contact_name_content">' +
                      '<img class="chatContactImage" src="' + val.profile_image_url + '"/>' +
                      '<div class="contact_info">' +
                      '<div class="contact_name_main">' + contact_fullname + '</div>';

                    if (company) {
                      html += '<small class="contact_company">' + company + '</small>';
                    }
                    if (val.title && val.title.trim() !== "") {
                      html += '<small class="contact_title">' + val.title + '</small>';
                    }

                    html += '<div class="contact_preview">' + l_say_hi + ' ' + contact_fullname + '</div>' +
                      '</div>' +
                      '<div class="contact_meta"><div class="contact_time"></div></div>' +
                      '<span class="unread-notifications" data-badge="0"></span>' +
                      '</div></li>';

                    items.push(html);
                  });

                  $(".chat_clients_list").prepend(items.join(''));
                  reorderContactList();
                } catch (e) {
                  alert_float("warning", e);
                }
              }
            },
            error: function (err) {
              alert_float("danger", err);
            }
          });
          return false;
        }
      }
    }, 500));


    /*--------------------  * send message & typing event to server  * ------------------- */
    $("#frame").on("keypress", "textarea.client_chatbox", function (e) {
      var form = $("form[name=clientMessagesForm]");
      var imgPath = $("#sidepanel #profile #profile-img").prop("currentSrc");
      var sent_at = "<?= _l('chat_sent_at'); ?>";

      if (e.which == 13 && !e.shiftKey) {
        e.preventDefault();

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

        // Handle reply functionality
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

        // Generate unique message ID for this message
        var tempMessageId = 'client_msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Create modern message structure for new sent message - starts in "sending" state
        const newMessageData = {
          id: tempMessageId,
          sender_id: "staff_" + userSessionId,
          message: chat_display_message,
          time_sent_formatted: moment().format(prchatSettings.timeFormat),
          viewed: false,
          sending: true, // Start in sending state with clock icon animation
          user_image: null
        };

        // Ensure modern container exists
        if ($(".client_messages .modern-messages-container").length === 0) {
          $(".client_messages .chat_client_messages ul").html('<div class="modern-messages-container"></div>');
        }

        $(".client_messages .modern-messages-container").append(window.createModernClientMessage(newMessageData));

        // Store tempMessageId for later status update
        window._lastClientMessageId = tempMessageId;

        // Update sidebar preview (SAME AS STAFF)
        var activeContact = $(".chat_clients_list > li.contact_name.selected");
        if (activeContact.length > 0) {
          updateContactPreview(activeContact.attr("id"), sidebar_preview, true, moment().format("YYYY-MM-DD HH:mm:ss"));
        }

        form.find('input.typing').val("false");

        // send event (don't escape message here - it's already processed)
        var formData = form.serializeArray();

        // Add the missing fields manually (like widget view does)
        var activeContact = $(".chat_clients_list > li.contact_name.selected");
        var clientId = "";
        var contactFullName = "";
        var companyName = "";

        if (activeContact.length > 0) {
          // Use the contact's own ID instead of parent ID (like the old logic)
          clientId = activeContact.attr("id");
          contactFullName = activeContact.find(".contact_name_main").text();
          companyName = activeContact.attr("data-company") || "";
        }

        // Override the 'to' field with correct client ID
        var toFieldIndex = formData.findIndex(field => field.name === 'to');
        if (toFieldIndex !== -1) {
          formData[toFieldIndex].value = "client_" + clientId;
        }

        // Remove any existing client_message field to avoid duplicates
        formData = formData.filter(field => field.name !== 'client_message');

        formData.push({
          name: "client_message",
          value: message
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

        // Capture tempMessageId for closure
        var currentTempMessageId = window._lastClientMessageId;

        $.post(prchatSettings.clientsMessagesPath, formData)
          .done(function (response) {
            var $sentMessage = $(".client_messages .modern-messages-container").find('[data-message-id="' + currentTempMessageId + '"]');
            $sentMessage.removeClass('modern-message-sending');

            try {
              var result = typeof response === 'string' ? JSON.parse(response) : response;
              if (result && result.id) {
                $sentMessage.attr('data-message-id', result.id);
                $sentMessage.find('.optionsMore').attr('data-id', result.id);
              }
            } catch (e) { }

            var $statusIcon = $sentMessage.find('.modern-status-sending');
            $statusIcon
              .removeClass('modern-status-sending fa-clock')
              .addClass('modern-status-delivered fa-check')
              .attr('title', '<?= _l('chat_msg_delivered'); ?>')
              .attr('data-original-title', '<?= _l('chat_msg_delivered'); ?>');

            try {
              $statusIcon.tooltip('destroy');
            } catch (e) { }
            try {
              $statusIcon.tooltip('dispose');
            } catch (e) { }
            $statusIcon.tooltip();
          })
          .fail(function () {
            // Update message status to "failed"
            var $sentMessage = $(".client_messages .modern-messages-container").find('[data-message-id="' + currentTempMessageId + '"]');
            $sentMessage.addClass('modern-message-failed');
            var $statusIcon = $sentMessage.find('.modern-status-sending');

            $statusIcon
              .removeClass('modern-status-sending fa-clock')
              .addClass('modern-status-failed fa-exclamation-circle text-danger')
              .attr('title', '<?= _l('chat_msg_failed'); ?>')
              .attr('data-original-title', '<?= _l('chat_msg_failed'); ?>');

            // Reinitialize tooltip
            try {
              $statusIcon.tooltip('destroy');
            } catch (e) { }
            try {
              $statusIcon.tooltip('dispose');
            } catch (e) { }
            $statusIcon.tooltip();
          });

        // Legacy code - keep for compatibility
        var activeContactId = $(".chat_clients_list > li.contact_name.selected").attr("id");
        if (activeContactId) {
          updateContactPreview(activeContactId, message, true, new Date().toISOString());
          reorderContactList();
        }

        $(this).val("").focus();
        scrollToBottom();
      } else if (!$(this).val() || (form.find('input.typing').val() == "null" && $(this).val())) {
        // typing event
        form.find('input.typing').val("true");

        // Use same client ID logic as message sending
        var activeContact = $(".chat_clients_list > li.contact_name.selected");
        var clientId = "";
        if (activeContact.length > 0) {
          clientId = activeContact.attr("id");
        }

        // Create proper form data for typing indicator
        var typingData = {
          from: "staff_" + userSessionId,
          to: "client_" + clientId,
          typing: "true",
          ci_csrf_token: $("input[name='ci_csrf_token']").val() || ""
        };

        $.post(prchatSettings.clientsMessagesPath, typingData);
      }
    });

    /*---------------* Check for unread frontend - mark client messages as read when staff interacts *---------------*/
    $("#frame").on("focus", ".client_chatbox", function () {
      var contactId = $(".chat_clients_list > li.contact_name.selected").attr("id");
      if (contactId && hiddenUnreadMessages.val() > 0) {
        f.updateUnreadMessages(contactId);
        hiddenUnreadMessages.val("0");
        $(".contact_name#" + contactId).find(".unread-notifications").remove();
      }
      if (contactId && typeof FloatingChatNotifications !== 'undefined') {
        FloatingChatNotifications.removeOne(String(contactId));
      }
    });

    $(function () {
      $("body").on("click", ".chat_clients_list > li.contact_name", function () {
        $(".chat_clients_list > li.contact_name.selected").removeClass("selected");
        $(this).addClass("selected");
        var cid = $(this).attr('id');
        if (cid && typeof FloatingChatNotifications !== 'undefined') {
          FloatingChatNotifications.removeOne(String(cid));
        }
      });
    });


    var createChatBoxClientRequest = null;

    function getClientMessages(staff_id, client_id, contact) {
      var not_seen_icon = "";
      var notificationDiv = contact.children(".unread-notifications");
      var notificationLength = contact.children(".unread-notifications");

      if (notificationLength.length > 0) {
        notificationDiv.remove();
      }

      $("#no_messages, .group_members_inline").remove();

      var deferred = $.Deferred();
      var promise = deferred.promise();

      if (createChatBoxClientRequest) {
        createChatBoxClientRequest.abort();
        deferred.resolve([]);
      } else {
        createChatBoxClientRequest = $.get(prchatSettings.getMutualMessages, {
          reciever_id: "staff_" + staff_id,
          sender_id: "client_" + client_id,
          offset: 0,
          limit: 20
        })
          .done(function (messages) {
            clientOffsetPush = 10;
            clientOffsetPush += 10;
            deferred.resolve(messages);
          })
          .fail(function () {
            deferred.resolve([]);
          })
          .always(function () {
            if ($("#no_messages").length > 0) {
              $("#no_messages").remove();
            }
            createChatBoxClientRequest = null;
          });
      }

      /*---------------* Modern Client Message HTML Architecture *---------------*/
      window.createModernClientMessage = function (messageData) {
        const isOwn = messageData.sender_id.startsWith("staff_");
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

        if (typeof isCallMessageStr === 'function' && isCallMessageStr(messageData.message)) {
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

        const optionsMore = deleteOrForward(messageData.id || userSessionId, "ticket", isOwn, messageData.message || '');

        // Use proper avatar fetching for staff, client image path for clients
        let avatarSrc;
        if (isOwn) {
          // For new staff messages without user_image, use current user's sidebar profile image
          if (!messageData.user_image && messageData.sender_id.startsWith("staff_")) {
            // Use current user's profile image from sidebar
            avatarSrc = $("#sidepanel #profile #profile-img").prop("currentSrc") || site_url + "/assets/images/user-placeholder.jpg";
          } else {
            // Use fetchUserAvatar for loaded messages from database
            avatarSrc = fetchUserAvatar(messageData.sender_id.replace("staff_", "") || userSessionId, messageData.user_image);
          }
        } else {
          avatarSrc = messageData.client_image_path || '';
        }

        const processedMessage = PrchatSafeRenderer.sanitize(messageData.message);
        const reactionsHtml = window.prchatRenderReactionPills ? window.prchatRenderReactionPills(messageData.reactions, messageData.id, 'client') : '';
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
                                ${statusIcon}
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
      promise.then(function (messages) {
        var clientUl = $(".client_messages .chat_client_messages ul");

        // Hide skeleton, create modern container
        $(".client_messages .messages-skeleton").addClass("hidden");
        clientUl.html('<div class="modern-messages-container"></div>');
        var modernContainer = clientUl.find('.modern-messages-container');

        $(messages).each(function (i, value) {
          var previous_time = moment(messages[i].time_sent).format("YYYY-MM-DD");
          if (messages[i + 1]) {
            var current_time = moment(messages[i + 1].time_sent).format("YYYY-MM-DD");
          }

          var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(value.message);

          const messageData = {
            id: value.id,
            sender_id: value.sender_id,
            message: displayForRow,
            time_sent: value.time_sent,
            time_sent_formatted: value.time_sent_formatted,
            viewed: value.viewed == 1,
            viewed_at: value.viewed_at || '',
            viewed_at_formatted: value.viewed_at_formatted || '',
            user_image: value.user_image,
            client_image_path: value.client_image_path,
            reactions: value.reactions || null,
            edited_at: value.edited_at || null
          };

          modernContainer.prepend(window.createModernClientMessage(messageData));

          if (previous_time !== current_time) {
            $("<div class=\"modern-date-separator\">" + formatDateSeparatorLabel(value.time_sent) + "</div>").prependTo(modernContainer);
          }
        });
        $(".group_members_inline").remove();
      });

      $.when(promise)
        .then(function () {
          ($(".client_messages").hasScrollBar());
          scrollToBottom();
          if ($(window).width() > 760) {
            $(".client_chatbox").focus();
          }
        });
      return false;
    }


    /*---------------* Check for messages history and append to main chat window *---------------*/
    $(".client_messages").on("scroll", function () {

      var pos = $(this).scrollTop();
      var client_id = $(this).attr("id");
      var to = $("#clients_container li.contact_name.selected").attr("id");
      var not_seen_icon = "";

      var defScroll = $.Deferred();
      var promise = defScroll.promise();

      if (pos == 0 && clientOffsetPush >= 10) {

        $.get(prchatSettings.getMutualMessages, {
          reciever_id: "staff_" + userSessionId,
          sender_id: client_id,
          offset: clientOffsetPush,
        })
          .done(function (messages) {
            if (Array.isArray(messages) === false) {
              clientEndOfScroll = true;
              if ($(".client_messages").hasScrollBar() && clientEndOfScroll == true) {
                prchat_setNoMoreMessages();
              }
            } else {
              clientOffsetPush += 10;
              defScroll.resolve(messages);
            }
            $(messages).each(function (i, value) {

              var previous_time = moment(messages[i].time_sent).format("YYYY-MM-DD");
              if (messages[i + 1] !== undefined) {
                var current_time = moment(messages[i + 1].time_sent).format("YYYY-MM-DD");
              }

              var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(value.message);

              const scrollMessageData = {
                id: value.id,
                sender_id: value.sender_id,
                message: displayForRow,
                time_sent: value.time_sent,
                time_sent_formatted: value.time_sent_formatted,
                viewed: value.viewed == 1,
                viewed_at: value.viewed_at || '',
                viewed_at_formatted: value.viewed_at_formatted || '',
                user_image: value.user_image,
                client_image_path: value.client_image_path,
                reactions: value.reactions || null
              };

              $(".client_messages .modern-messages-container").prepend(window.createModernClientMessage(scrollMessageData));

              if (previous_time !== current_time) {
                $("<div class=\"modern-date-separator\">" + formatDateSeparatorLabel(value.time_sent) + "</div>").prependTo($(".client_messages .modern-messages-container"));
              }

            });
            if (clientEndOfScroll === false) {
              $(".client_messages").scrollTop(200);
            }
          });
      }
    });

    // Show All Contacts (no-op for flat list)
    $("#frame").on("click", "#clients_show", function () {
      $(".chat_clients_list > li.contact_name").show();
    });

    /*---------------* Event that handles when clicked on a specific contact in sidebar *---------------*/
    $("#clients_container").on("click", ".contact_name", function (e) {
      e.preventDefault();
      animateContent();

      // Clear notifications for this specific contact (like staff side)
      var contactId = $(this).attr("id");
      var notificationBadge = $(this).find(".unread-notifications");

      if (notificationBadge.length > 0) {
        // Update server-side unread count
        f.updateUnreadMessages(contactId);

        // Remove notification badge and classes
        notificationBadge.remove();
        $(this).removeClass("has-unread-messages");
      }

      // Show skeleton while messages load
      $(".client_messages .chat_client_messages ul").empty();
      generateMessageSkeleton('.client_messages .messages-skeleton', 10);

      // Reset offset and end of scroll
      clientOffsetPush = 0;
      clientEndOfScroll = false;

      var contactId = $(this).attr("id"),
        clientName = $(this).find(".contact_name_main").text(),
        customerName = $(this).attr("data-company") || "",
        contactProfile = $("#frame .contact-profile"),
        companyId = $(this).attr("data-client-id"),
        isPrimary = $(this).attr("data-is-primary") === "true",
        clientData = "";

      $(".client_data").remove();
      $("body").find(".client_messages").attr("id", "client_" + contactId);
      $("body").find(".client_messages").addClass("isFocused");

      $("body").find("form#clientMessagesForm .to").val("client_" + contactId);
      $("body").find("form#clientMessagesForm .from").val("staff_" + userSessionId);

      getClientMessages(userSessionId, contactId, $(this));

      var extraClass = $(this).find(".contact_title").length ? "" : "notitle";

      // Remove any existing client_data
      $(".client_data").remove();

      // Update staff profile elements with client data (same structure as staff side)
      var $staffNameElement = $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown .staff-name-text");
      var $staffImage = $("#frame .content .contact-profile .profile-wrapper img.staff-profile-image-small");

      // Update staff name with client data and proper links
      var contactLink = '<a href="' + site_url + 'admin/clients/client/' + companyId + '?group=contacts&contactid=' + contactId + '" target="_blank"><strong>' + clientName + '</strong></a>';
      var companyLink = '<br><a href="' + site_url + 'admin/clients/client/' + companyId + '" target="_blank"><small class="contact_role">' + customerName + '</small></a>';

      if ($staffNameElement.length === 0) {
        $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").html('<span class="staff-name-text">' + contactLink + companyLink + '</span><i class="fa fa-chevron-down dropdown-arrow"></i>');
      } else {
        $staffNameElement.html(contactLink + companyLink);
      }

      // Make sure the profile wrapper and elements are visible
      $("#frame .content .contact-profile .profile-wrapper").show();
      $("#frame .content .contact-profile .profile-wrapper img.staff-profile-image-small").show();
      $("#frame .content .contact-profile .profile-wrapper p.staff-name-dropdown").show();

      // Ensure the image element has the correct src attribute set
      if ($("#frame .content .contact-profile .profile-wrapper img.staff-profile-image-small").attr("src") === "") {
        $("#frame .content .contact-profile .profile-wrapper img.staff-profile-image-small").attr("src", site_url + "assets/images/user-placeholder.jpg");
      }

      // Update staff image with client profile image
      if ($staffImage.length > 0) {
        // Get the profile image from the chatContactImage element
        var clientAvatarUrl = $(this).find(".chatContactImage").attr("src") || site_url + "assets/images/user-placeholder.jpg";
        $staffImage.attr("src", clientAvatarUrl);
      }
    });

    /*---------------* Member array for online / offline activity *---------------*/
    var pendingRemoves = [];

    /*---------------* Pusher Trigger user subscribed successfully *---------------*/
    clientsChannel.bind("pusher:subscription_succeeded", function (member) {
      if (typeof loaderTimeout !== 'undefined') {
        clearTimeout(loaderTimeout);
      }
      checkIfContactIsActive(member);
      $("#main_loader_init").fadeOut(500);
      ifContactOnlineAddToClientsList(member);
      appendClientIfNotInOnlineClients();
    });

    function appendClientIfNotInOnlineClients() {
      // Count online clients and update global counter
      var count = 0;
      if (clientsChannel && clientsChannel.members && clientsChannel.members.members) {
        for (var key in clientsChannel.members.members) {
          if (key.toString().indexOf("client") === 0) {
            var m = clientsChannel.members.members[key];
            if (m && m.contact_id) {
              count++;
            }
          }
        }
      }
      window._onlineClientCount = count;
      if (typeof updateOnlineCounter === 'function') {
        updateOnlineCounter();
      }
    }

    /*---------------* Pusher Trigger user logout *---------------*/
    clientsChannel.bind("pusher:member_removed", function (member) {
      removeClientMember(member);
    });


    /*---------------* Pusher Trigger user connected *---------------*/
    clientsChannel.bind("pusher:member_added", function (member) {
      /**
       * Check if clients is in my own list clients
       */
      var ownClient = myCustomers.customers.find(function (client) {
        if (client.client_id !== undefined) {
          return client.client_id == member.info.client_id;
        }
      });

      if (ownClient == undefined) {
        return false;
      }

      var contact = member.info;

      // Use attribute selector for reliable matching (numeric IDs break #id syntax)
      var contactIsLoggedIn = $(".chat_clients_list > li.contact_name[id='" + contact.contact_id + "']");

      if (member.info.justLoggedIn == true) {
        appendClientIfNotInOnlineClients();
      }

      if (typeof contact.company !== "undefined" && contact.contact_id !== "undefined") {
        if (contactIsLoggedIn.length === 0) {
          var companyName = (typeof contact.company === 'object') ? contact.company.name : contact.company;
          var rtContactHtml = '<li class="contact_name contactActive" id="' + contact.contact_id + '" data-client-id="' + contact.client_id + '" data-company="' + (companyName || '').replace(/"/g, '&quot;') + '">' +
            '<div class="contact_name_content">' +
            '<img class="chatContactImage online" src="' + (contact.profile_image_url || site_url + '/assets/images/user-placeholder.jpg') + '"/>' +
            '<div class="contact_info">' +
            '<div class="contact_name_main">' + contact.name + '</div>';

          if (companyName) {
            rtContactHtml += '<small class="contact_company">' + companyName + '</small>';
          }

          rtContactHtml += '<div class="contact_preview" style="display: none;"></div>' +
            '</div>' +
            '<div class="contact_meta"><div class="contact_time"></div></div>' +
            '</div></li>';

          $(rtContactHtml).prependTo(".chat_clients_list");
        }
        addClientMember(member);
      }
    });

    /*---------------* New staff member activity online / offline  *---------------*/
    function ifContactOnlineAddToClientsList() {
      var c = f.fetchClients();

      for (var i = 0; i < c.customers.length; i++) {
        var memberId = "client_" + c.customers[i].contact_id;
        var contact = clientsChannel.members.get(memberId);
        var cid = c.customers[i].client_id;

        if (contact !== null) {
          // Use attribute selector for company element (IDs starting with "client_" work fine)
          updateClientContactStatus(c.customers[i].contact_id, true);
        }
      };
    }

    /*---------------* New chat members tracking / removing *---------------*/
    function addClientMember(member) {
      var contactId = member.id.replace("client_", "");
      var pendingRemoveTimeout = pendingRemoves[contactId];

      // Update contact status to online
      updateClientContactStatus(contactId, true);

      // Always update the combined online counter
      appendClientIfNotInOnlineClients();

      // Use attribute selector for numeric IDs
      var activeContact = $("body").find(".chat_clients_list > li.contact_name[id='" + contactId + "']");

      var c = f.fetchClients();
      for (var i = 0; i < c.customers.length; i++) {
        var contact = clientsChannel.members.get("client_" + c.customers[i].contact_id);
        if (contact !== null) {
          if (contact.info.justLoggedIn && "client_" + contactId == contact.id) {
            pushDesktopOnlineNotification(contact);
          }
        }
      };
      if (pendingRemoveTimeout) {
        clearTimeout(pendingRemoveTimeout);
      }
    }

    /*---------------* New chat members tracking / removing from channel and view *---------------*/
    function removeClientMember(member) {
      var contactId = member.id.replace("client_", "");

      pendingRemoves[contactId] = setTimeout(function () {
        // Use attribute selector for numeric IDs
        if ($("body").find(".chat_clients_list > li.contact_name[id='" + contactId + "']").length) {
          // Update contact status to offline
          updateClientContactStatus(contactId, false);
        }
        // Recalculate client count and update combined counter
        appendClientIfNotInOnlineClients();
      }, 5000);
    }

    /*---------------* Bind the 'send-event' & update the chat box message log *---------------*/
    var invisibleCounter = 1;

    clientsChannel.bind("send-event", function (data) {
      $("#frame .client_messages").find(".typing-indicator").removeClass("show");
      var clientMessages = $(".chat_client_messages");
      var selectedContact = $(".chat_clients_list > li.contact_name.selected");

      selectedContact = "client_" + selectedContact.attr("id");

      if (selectedContact == data.from && data.to == "staff_" + userSessionId) {

        var rendered = PrchatSafeRenderer.render(data.message);
        var displayForRow = PrchatSafeRenderer.displayForModernMessageRow(data.message);

        const realtimeMessageData = {
          id: data.last_insert_id || data.message_id || data.from + '_' + Date.now(),
          sender_id: data.from,
          message: displayForRow,
          time_sent_formatted: moment().format(prchatSettings.timeFormat),
          viewed: false,
          client_image_path: data.client_image_path,
          sender_image: data.sender_image
        };

        if ($(".client_messages#" + data.from + " .modern-messages-container").length === 0) {
          $(".client_messages#" + data.from + " .chat_client_messages ul").html('<div class="modern-messages-container"></div>');
        }
        $(".client_messages#" + data.from + " .modern-messages-container").append(window.createModernClientMessage(realtimeMessageData));

        parseInt(hiddenUnreadMessages.val(invisibleCounter++));
        scrollToBottom();

        var contactId = data.from.replace("client_", "");
        updateContactPreview(contactId, rendered.sidebar, false, moment().format("YYYY-MM-DD HH:mm:ss"));

      } else if (data.to == "staff_" + userSessionId) {
        data.from = data.from.replace("client_", "");
        var contactNotification = $("body").find(".contact_name#" + data.from);
        var contactNotificationParent = $("body").find(".contact_name#" + data.from).parent();
        var contactNotificationClass = ".unread-notifications";
        var topLi = $(".chat_clients_list ul li").first();

        var isClientLoaded = $(".chat_clients_list > li.contact_name#" + data.from);

        if (isClientLoaded.length === 0) {
          var profile_image_url = data.client_image_path || contact_profile_image_url(data.from) || site_url + "/assets/images/user-placeholder.jpg";
          var nmContactHtml = '<li class="contact_name" id="' + data.from + '" data-client-id="' + data.client_id + '" data-company="' + (data.company || '').replace(/"/g, '&quot;') + '">' +
            '<div class="contact_name_content">' +
            '<img class="chatContactImage" src="' + profile_image_url + '"/>' +
            '<div class="contact_info">' +
            '<div class="contact_name_main">' + data.contact_full_name + '</div>';

          if (data.company) {
            nmContactHtml += '<small class="contact_company">' + data.company + '</small>';
          }
          if (data.contact_title && data.contact_title.trim() !== "") {
            nmContactHtml += '<small class="contact_title">' + data.contact_title + '</small>';
          }

          nmContactHtml += '<div class="contact_preview"><?= _l('chat_new_message_received'); ?></div>' +
            '</div>' +
            '<div class="contact_meta">' +
            '<div class="contact_time">now</div>' +
            '</div>' +
            '<span class="unread-notifications" data-badge="' + (data.count_messages || 1) + '"></span>' +
            '</div>' +
            '</li>';

          $(nmContactHtml).prependTo("#crm_clients .chat_clients_list");
          updateClientContactStatus(data.from, true);

          // Update preview with actual message content (rendered)
          var rendered = PrchatSafeRenderer.render(data.message);
          updateContactPreview(data.from, rendered.sidebar, false, moment().format("YYYY-MM-DD HH:mm:ss"));
        }

        if (topLi.attr("id") !== data.from) {
          contactNotification.prependTo(".chat_clients_list");
        }

        $("#clients_container").scrollTop(0);

        let l_newChatFile = "<?= _l('chat_new_file_uploaded'); ?>";
        let l_newChatLink = "<?= _l('chat_new_link_shared'); ?>";
        let l_newAudioMsg = "<?= _l('chat_new_audio_message'); ?>";
        let l_Contact = "<?= _l('chat_lang_contact'); ?>";

        if (app.options.desktop_notifications && chat_desktop_notifications_enabled) {
          if (user_chat_status != "busy" && user_chat_status != "offline") {

            if (data.message.includes("/modules/prchat/uploads/")) {
              var filename = data.message.replace(/^.*[\\\/]/, '');
              if (filename.match(/\.(gif|jpg|jpeg|png|swf|PNG|JPG|JPEG)$/i)) {
                data.message = data.contact_full_name + " " + "<?= _l('chat_photos_text') ?>";
              } else {
                data.message = data.contact_full_name + " " + filename;
              }
            } else if (data.message.includes("class=\"prchat_convertedImage\"")) {
              data.message = data.contact_full_name + " " + "<?= _l('chat_photos_text') ?>";
            } else if (isVideoUrl(data.message)) {
              data.message = data.contact_full_name + " " + "Video";
            }

            if (data.message.includes("audio/ogg")) {
              data.message = data.contact_full_name + " " + l_newAudioMsg;
            }

            // Normalize call summary notifications ([CALL:...]) to friendly preview text
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
                } else {
                  // If message contains any prefix, try extracting only the [CALL:...] token
                  var callMatch2 = data.message.match(
                    /\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]/
                  );
                  if (callMatch2) {
                    callPreview = PrchatSafeRenderer.formatCallPreview(callMatch2[0]);
                  }
                }
              }
              if (callPreview) data.message = callPreview;
            }

            $.notify("", {
              "title": l_Contact + " " + data.contact_full_name,
              "body": PrchatSafeRenderer.stripTags(data.message),
              "requireInteraction": true,
              "tag": "contact-message-client_" + data.from,
              "icon": data.client_image_path,
              "closeTime": app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : null
            }).show(function (e) {
              window.focus();
              setTimeout(function () {
                e.target.close();
              }, app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : 3000);
            });
          }
        }

        var clientContactId = String((data.from || '').replace(/^client_/, ''));
        var isClientMuted = typeof mutedClients !== 'undefined' && mutedClients.indexOf(clientContactId) !== -1;
        if (!isClientMuted && user_chat_status != "busy" && user_chat_status != "offline") {
          window.chatSoundManager.setUserStatus(user_chat_status);
          window.chatSoundManager.initClientSound(data);
        }

        if (data.from && data.to == "staff_" + userSessionId) {
          var isClientsTabActive = $("#frame #sidepanel .crm_clients a").parent().hasClass("active");
          if (!isClientsTabActive) {
            incrementTabBadge('crm_clients');
          }
          if (typeof FloatingChatNotifications !== 'undefined' && !isClientsTabActive && !isClientMuted) {
            // Render the message properly first to get clean sidebar preview
            var rendered = PrchatSafeRenderer.render(data.message);
            var clNotifMsg =
              (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.formatCallPreview(data.message)) ||
              rendered.sidebar ||
              (data.message || '').replace(/<[^>]*>/g, '').substring(0, 50) + '...';
            FloatingChatNotifications.add({
              from: data.from,
              fromName: data.from_name || data.contact_full_name || 'Client',
              type: 'client',
              message: clNotifMsg,
              avatar: data.client_image_path || site_url + 'assets/images/user-placeholder.jpg'
            });
          }
        }

        // Find the specific contact that sent the message (not any other contact)
        var messageSender = $("body").find(".contact_name#" + data.from);

        if (messageSender.length > 0) {
          var existingBadge = messageSender.find(".unread-notifications");

          if (!existingBadge.length) {
            // Create new notification badge - positioned inside contact_name_content
            var initialCount = data.count_messages || 1;
            messageSender.find(".contact_name_content").append("<span class=\"unread-notifications\" data-badge=\"" + initialCount + "\"></span>");
          } else {
            // Update existing notification count
            var currentNotifications = parseInt(existingBadge.attr("data-badge")) || 0;
            var newCount = (isNaN(currentNotifications) ? 0 : currentNotifications) + 1;
            existingBadge.attr("data-badge", newCount);
          }

          // Add has-unread-messages class for browsers without :has() support
          messageSender.addClass("has-unread-messages");
        }

        // Update sidebar preview and time for the contact that sent the message (use rendered preview)
        var rendered = PrchatSafeRenderer.render(data.message);
        updateContactPreview(data.from, rendered.sidebar, false, moment().format("YYYY-MM-DD HH:mm:ss"));

        // Update raw timestamp for accurate sorting
        var senderContact = $(".contact_name#" + data.from.replace("client_", ""));
        if (senderContact.length) {
          senderContact.attr("data-last-message-time", new Date().toISOString());
        }

        // Reorder contacts list (most recent conversation first)
        reorderContactList();
      }
    });

    clientsChannel.bind("message-edited", function (data) {
      if (!data || !data.message_id || !data.rendered_message) {
        return;
      }
      var selectedEl = $(".chat_clients_list > li.contact_name.selected");
      if (!selectedEl.length) {
        return;
      }
      var threadId = "client_" + selectedEl.attr("id");
      if (data.to !== threadId && data.from !== threadId) {
        return;
      }
      var $msg = $('.client_messages .modern-message-item[data-message-id="' + data.message_id + '"]');
      if (!$msg.length) {
        return;
      }
      $msg.find(".modern-message-text").html(data.rendered_message);
      if (!$msg.find(".prchat-edited-tag").length) {
        $msg.find(".modern-message-text").after('<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l('chat_edited'); ?>)</span>');
      }
      var $c = $(".client_messages .modern-messages-container");
      if (!$c.length || typeof PrchatSafeRenderer === "undefined" || typeof updateContactPreview !== "function") {
        return;
      }
      var $last = $c.find("[data-message-id]").last();
      if (!$msg.is($last)) {
        return;
      }
      var side = PrchatSafeRenderer.render(data.rendered_message).sidebar;
      var sentByMe = data.from === "staff_" + userSessionId;
      var cid = selectedEl.attr("id");
      updateContactPreview(cid, side, sentByMe, moment().format("YYYY-MM-DD HH:mm:ss"));
      reorderContactList();
    });

    /*---------------* Pusher: message-reaction for client messages *---------------*/
    clientsChannel.bind("message-reaction", function (data) {
      if (data && data.message_id && typeof window.prchatRenderReactionPills === 'function') {
        var $msg = $('.client_messages .modern-message-item[data-message-id="' + data.message_id + '"]');
        if ($msg.length) {
          $msg.find('.message-reactions').remove();
          var $bubble = $msg.find('.modern-message-bubble').first();
          var html = window.prchatRenderReactionPills(data.reactions, data.message_id, 'client');
          if (html) {
            $bubble.addClass('has-reactions');
            $bubble.append(html);
            $bubble.find('[data-toggle="tooltip"]').tooltip();
          } else {
            $bubble.removeClass('has-reactions');
          }
        }
      }
    });

    /*---------------* Detect when a user is typing a message *---------------*/
    var clientTypingTimerId;
    clientsChannel.bind("typing-event", function (data) {
      var clearTypingInterval = 2500;
      var clientMessages = $("#frame .client_messages");
      var selectedContact = $(".chat_clients_list > li.contact_name.selected");
      var selectedContactId = "client_" + selectedContact.attr("id");
      var typingIndicator = clientMessages.find(".typing-indicator");

      if (data.from && data.from.startsWith("client_") &&
        data.to == "staff_" + userSessionId &&
        data.message == "true") {

        // Show typing in chat area if this contact is selected
        if (clientMessages.hasClass("isFocused") && data.from === selectedContactId) {
          var clientName = data.from_name || selectedContact.find(".contact_name_content .contact_name_main").text() || 'Client';
          typingIndicator.find(".typing-text").text(clientName + " <?= _l('chat_is_typing'); ?>...");
          typingIndicator.addClass("show");
          var chatClientMessages = clientMessages.find(".chat_client_messages");
          if (chatClientMessages.length) {
            chatClientMessages.scrollTop(chatClientMessages[0].scrollHeight);
          }
        }

        // Show typing indicator in client sidebar preview
        var clientContactId = data.from.replace("client_", "");
        var $clientPreview = $(".contact_name#" + clientContactId).find(".contact_preview");
        if ($clientPreview.length && !$clientPreview.data("original-preview")) {
          $clientPreview.data("original-preview", $clientPreview.html());
          $clientPreview.html('<span class="typing-preview"><?php echo _l("chat_typing"); ?></span>');
        }

        clearTimeout(clientTypingTimerId);
        clientTypingTimerId = setTimeout(function () {
          typingIndicator.removeClass("show");
          // Restore sidebar preview after timeout
          var $cp = $(".contact_name#" + clientContactId).find(".contact_preview");
          if ($cp.length && $cp.data("original-preview")) {
            $cp.html($cp.data("original-preview"));
            $cp.removeData("original-preview");
          }
        }, clearTypingInterval);
      } else if (
        data.from && data.from.startsWith("client_") &&
        data.to == "staff_" + userSessionId &&
        data.message == "null") {
        typingIndicator.removeClass("show");

        // Restore client sidebar preview
        var clientContactId = data.from.replace("client_", "");
        var $clientPreview = $(".contact_name#" + clientContactId).find(".contact_preview");
        if ($clientPreview.length && $clientPreview.data("original-preview")) {
          $clientPreview.html($clientPreview.data("original-preview"));
          $clientPreview.removeData("original-preview");
        }
      }
    });

    function checkIfContactIsActive(member) {
      member.each(function (m) {

        // Only process client members (skip staff members)
        if (!m.id.startsWith("client")) {
          return;
        }
        var contact = m.info;

        // Check if this client is in the authorized list for this staff member
        var isAuthorized = myCustomers.customers.find(function (client) {
          if (client.client_id !== undefined) {
            return client.client_id == contact.client_id;
          }
        });

        // If staff doesn't have access to this client, skip it
        if (isAuthorized == undefined) {
          return;
        }

        // Use attribute selector for reliable matching (numeric IDs break #id syntax)
        var contactIsLoggedIn = $(".chat_clients_list > li.contact_name[id='" + contact.contact_id + "']");
        if (typeof contact.company !== "undefined" && contact.company != null) {
          if (contactIsLoggedIn.length === 0) {
            var companyName = (typeof contact.company === 'object') ? contact.company.name : contact.company;
            var memberContactHtml = '<li class="contact_name contactActive" id="' + contact.contact_id + '" data-client-id="' + contact.client_id + '" data-company="' + (companyName || '').replace(/"/g, '&quot;') + '">' +
              '<div class="contact_name_content">' +
              '<img class="chatContactImage online" src="' + (contact.profile_image_url || site_url + '/assets/images/user-placeholder.jpg') + '"/>' +
              '<div class="contact_info">' +
              '<div class="contact_name_main">' + contact.name + '</div>';

            if (companyName) {
              memberContactHtml += '<small class="contact_company">' + companyName + '</small>';
            }
            if (contact.title && contact.title.trim() !== "") {
              memberContactHtml += '<small class="contact_title">' + contact.title + '</small>';
            }

            memberContactHtml += '<div class="contact_preview">' + l_say_hi + ' ' + contact.name + '</div>' +
              '</div>' +
              '<div class="contact_meta"><div class="contact_time"></div></div>' +
              '<span class="unread-notifications" data-badge="0"></span>' +
              '</div></li>';

            $(memberContactHtml).prependTo(".chat_clients_list");
          }
        }
      });
    }

    function pushDesktopOnlineNotification(contact) {
      return $.notify("", {
        "title": app.lang.new_notification,
        "body": lang_contact + " " + contact.info.name + " " + prchatSettings.hasComeOnlineText,
        "requireInteraction": true,
        "tag": "contact-join-" + contact.id,
        "closeTime": app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : null
      }).show(function (e) {
        window.focus();
        setTimeout(function () {
          e.target.close();
        }, app.options.dismiss_desktop_not_after != "0" ? app.options.dismiss_desktop_not_after * 1000 : 3000);
      });
    }

    var onlineToggled = false;
    var mainClientsContainer = $(".chat_clients_list");

    $("body").on("click touchstart", "#resetContacts", function (e) {
      e.stopPropagation();
      $.each(mainClientsContainer.children("ul"), function (i, el) {
        el = $(el);
        if (el.is(":hidden")) {
          el.show();
        }
      });
      if (onlineToggled === false) {
        alert_float("warning", "<?= _l('chat_already_seeing_all_clients'); ?>");
      }
      onlineToggled = false;

    });
    $("body").on("click touchstart", "#showOnlineContacts", function (e) {
      e.stopPropagation();
      if (!onlineToggled) {
        onlineToggled = true;
        $(".chat_clients_list > li.contact_name").each(function () {
          if (!$(this).hasClass("contactActive")) {
            $(this).hide();
          }
        });

        clientsChannel.members.each(function (m) {
          var contact = m.info;
          var contactIsLoggedIn = $(".chat_clients_list > li.contact_name#" + contact.contact_id);

          if (typeof contact.company !== "undefined") {
            if (contactIsLoggedIn.length === 0) {
              var companyName = (typeof contact.company === 'object') ? contact.company.name : contact.company;
              var onlineContactHtml = '<li class="contact_name contactActive" id="' + contact.contact_id + '" data-client-id="' + contact.client_id + '" data-company="' + (companyName || '').replace(/"/g, '&quot;') + '">' +
                '<div class="contact_name_content">' +
                '<img class="chatContactImage online" src="' + (contact.profile_image_url || site_url + '/assets/images/user-placeholder.jpg') + '"/>' +
                '<div class="contact_info">' +
                '<div class="contact_name_main">' + contact.name + '</div>';

              if (companyName) {
                onlineContactHtml += '<small class="contact_company">' + companyName + '</small>';
              }

              onlineContactHtml += '<div class="contact_preview" style="display: none;"></div>' +
                '</div>' +
                '<div class="contact_meta"><div class="contact_time"></div></div>' +
                '</div></li>';

              $(onlineContactHtml).prependTo(".chat_clients_list");
            }
          }
        });
      }
    });
  }());

  function initCustomersAppending(customers) {
    var l_say_hi = "<?= _l('chat_say_hi'); ?>";
    for (var index in customers) {
      customers[index].forEach(function (contact) {
        var firstname = contact.firstname,
          lastname = contact.lastname,
          contact_id = contact.contact_id,
          client_id = contact.client_id,
          company = contact.company,
          image_path = contact.profile_image_url;

        if ($(".chat_clients_list > li.contact_name#" + contact_id).length) return;

        var tooltipAttr = '';
        if (contact.last_login) {
          tooltipAttr = ' data-toggle="tooltip" data-container="body" title="<?= _l('chat_last_seen'); ?>: ' + moment(contact.last_login, "YYYY-MM-DD HH:mm:ss").fromNow() + '"';
        }

        var contactHtml = '<li class="contact_name" id="' + contact_id + '" data-client-id="' + client_id + '" data-company="' + (company || '').replace(/"/g, '&quot;') + '" data-is-primary="' + (contact.is_primary == 1 ? 'true' : 'false') + '"' + tooltipAttr + '>' +
          '<div class="contact_name_content">' +
          '<img class="chatContactImage" src="' + image_path + '"/>' +
          '<div class="contact_info">' +
          '<div class="contact_name_main">' + firstname + ' ' + lastname + '</div>';

        if (company) {
          contactHtml += '<small class="contact_company">' + company + '</small>';
        }
        if (contact.title && contact.title.trim() !== "") {
          contactHtml += '<small class="contact_title">' + contact.title + '</small>';
        }

        var previewMessage = contact.message || l_say_hi + ' ' + firstname + ' ' + lastname;
        contactHtml += '<div class="contact_preview">' + previewMessage + '</div>';

        contactHtml += '</div><div class="contact_meta">';

        var timeDisplay = '';
        if (contact.time_sent_formatted && contact.time_sent_formatted.trim() !== '') {
          timeDisplay = contact.time_sent_formatted;
        }

        contactHtml += '<div class="contact_time">' + timeDisplay + '</div>' +
          '</div>' +
          '<span class="unread-notifications" data-badge="0"></span>' +
          '</div>' +
          '</li>';

        $("#crm_clients .chat_clients_list").append(contactHtml);
      });
    }
  }

  // Handles client file form upload - updated for multiple files
  function uploadClientFileForm(form) {
    var fileInput = $(form).children("input[type=file]")[0];
    var files = fileInput.files;
    var sentTo = $(".chat_clients_list > li.contact_name.selected").attr("id");
    var token_name = $(form).children("input[name=csrf_token_name]").val();
    var formId = $(form).attr("id");

    if (files.length === 0) {
      return false;
    }

    // Upload files one by one in sequence
    uploadClientFilesSequentially(files, sentTo, token_name, formId, 0);
  }

  // Upload client files one by one sequentially
  function uploadClientFilesSequentially(files, sentTo, token_name, formId, currentIndex) {
    if (currentIndex >= files.length) {
      // All files uploaded, hide loader and reset form
      $(".content .chat-module-loader").fadeOut();
      $("form#" + formId).trigger("reset");
      return;
    }

    var file = files[currentIndex];
    var formData = new FormData();
    formData.append("userfile", file);
    formData.append("send_to", sentTo);
    formData.append("send_from", "staff_" + userSessionId);
    formData.append("csrf_token_name", token_name);

    // Use client controller for client uploads
    var clientUploadUrl = "<?php echo site_url('prchat/Prchat_ClientsController/uploadMethod'); ?>";

    $.ajax({
      type: "POST",
      url: clientUploadUrl,
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
          var basePath = "<?php echo base_url('modules/prchat/uploads/clients/'); ?>";
          $("#frame textarea.client_chatbox").val(basePath + r.upload_data.file_name);

          setTimeout(function () {
            if ($("#frame textarea.client_chatbox").trigger(uploadSend)) {
              // Get the selected contact ID for shared files update
              var contact_name_id = $("#clients_container li.contact_name.selected").attr("id");
              if (contact_name_id) {
                getSharedFiles(contact_name_id, sentTo);
              }
            }

            // Wait a bit then upload next file
            setTimeout(function () {
              uploadClientFilesSequentially(files, sentTo, token_name, formId, currentIndex + 1);
            }, 300);
          }, 100);
        } else {
          alert_float("danger", r.error);
          // Continue with next file even if this one failed
          setTimeout(function () {
            uploadClientFilesSequentially(files, sentTo, token_name, formId, currentIndex + 1);
          }, 100);
        }
      },
      error: function () {
        // Continue with next file even if this one failed
        setTimeout(function () {
          uploadClientFilesSequentially(files, sentTo, token_name, formId, currentIndex + 1);
        }, 100);
      }
    });
  }

  /*!
   * Group items from an array together by some criteria or value.
   * @param  {Array}           arr      The array to group items from
   * @param  {String|Function} criteria The criteria to group by
   * @return {Object}                   The grouped object
   */

  function chatGroupBy(arr, criteria) {
    return arr.reduce(function (obj, item) {
      // Check if the criteria is a function to run on the item or a property of it
      var key = typeof criteria === "function" ? criteria(item) : item[criteria];

      // If the key doesn't exist yet, create it
      if (!obj.hasOwnProperty(key)) {
        obj[key] = [];
      }

      // Push the value to the object
      obj[key].push(item);

      // Return the object to the next item in the loop
      return obj;

    }, {});
  };

  var clientsOffset = 0;

  function fetchMoreClients(el) {
    clientsOffset += 30;
    $(el).html('<div class="fa-1x chat_loading_more_clients"><?= _l('chat_load_more_clients'); ?><i class="fa fa-spinner fa-pulse"></i><i class="fa fa-stroopwafel fa-spin"></i></div>');
    $.getJSON("<?php echo site_url('prchat/Prchat_ClientsController/loadMoreClients'); ?>", {
      offset: clientsOffset
    })
      .done(function (clients) {
        var customers = clients.customers || [];

        if (customers.length === 0) {
          var content = "<li class='text-center m-t-5 cp-5 no_more_clients bg-chat-primary'><h6><?= _l('chat_no_more_contacts_found'); ?></h6></li>";
          $(el).html("<?= _l('chat_load_more_clients'); ?>");
          if ($(".chat_clients_list li.no_more_clients").length) {
            $(".chat_clients_list li.no_more_clients").html(content);
            if ($("body").hasClass("chat_dark")) {
              $(".chat_load_more_clients").hide();
            }
          } else {
            $(".chat_clients_list").append(content);
            $("#clients_container").scrollTop($("#clients_container")[0].scrollHeight);
          }
          return false;
        }

        initCustomersAppending({
          all: customers
        });

        $(el).html("<?= _l('chat_load_more_clients'); ?>");
      });
  }

  $("body").on("click", ".chat_client_messages .btn_convert_conversation_to_ticket", function (e) {
    var msgTxt = $(this).parents("li").find("p").text();

    var msgId, user_id;
    var id = $(".chat_clients_list > li.contact_name.selected").attr("id");
    var current_message = $(this).prev().text();

    $(".modal_container").load(prchatSettings.convertToTicket, {
      id: "client_" + id,
    },
      function (res) {

        var poppedUp = $("#convert_to_ticket_modal").modal({
          show: true
        });

        if (poppedUp.length) {
          setTimeout(function () {
            $("#convert_to_ticket_modal label").filter(function () {
              if ($(this).text() === msgTxt) {
                msgId = $(this).prev().attr("id").replace("message_", "");;
                return $(this).prev().prop("checked", true);
              }
            }).focus();

            user_id = "client_" + id;

            var client_id = $(".client_messages").attr("id");
            var ticket_message = msgTxt;
            var user_name = $("#converToTicketModal .mbot20").children("strong").text();

            filteredTicketMessages.push({
              "user_id": user_id,
              "message_id": msgId,
              "message": ticket_message,
              "client_id": client_id,
              "user_name": user_name
            });

          }, 500);
        }
      });
  });


  jQuery.fn.extend({
    chevronRemoveRightAddDefault: function () {
      return $(this).removeClass("chevron-right").addClass("chevron-default");
    }
  });

  // Helper functions for file type detection
  function isImageFile(url) {
    return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(url);
  }

  function isAudioFile(url) {
    return /\.(mp3|wav|ogg|aac|m4a)$/i.test(url);
  }

  function isVideoFile(url) {
    return /\.(mp4|webm|ogg|avi|mov|wmv|flv|mkv)$/i.test(url);
  }



  // Create ticket modal
  <?php if (get_option('chat_allow_staff_to_create_tickets') == 1 || is_admin() && is_staff_member()): ?>

    $("body").on("mouseover", ".chat_client_messages .replies", function () {
      var message_date = $(this).data("sqldate");

      var newFormat = "YYYY-MM-DD HH:mm:ss";
      var today = moment().format(newFormat);

      var hours = moment(today, newFormat).diff(moment(message_date, newFormat), "hours");

      // Only allow to convert messages send in last 48 hours
      if (hours >= 48) {
        return;
      }

      $(this).find(".btn_convert_conversation_to_ticket").show();
    }).mouseout(function () {
      $(this).find(".btn_convert_conversation_to_ticket").hide();
    });
  <?php endif; ?>

  /**
   * Remove message click event - Updated for modern structure
   * Note: stopImmediatePropagation prevents the generic handler in mutual_and_helper_functions.php from also firing
   */
  $("body").on("click", ".chat_client_messages ._removeMessage", function (e) {
    e.stopImmediatePropagation(); // Prevent double confirmation from generic handler

    if (!confirm("<?= _l('chat_confirm_delete_message') ?>")) {
      return false;
    }

    $(this).attr("disabled", true);
    var messageId = $(this).closest(".optionsMore").attr("data-id");

    // Hide the options menu
    $(this).closest(".optionsMore").hide();
    $(this).closest(".modern-message-item").find(".chooseOption").hide();

    delete_chat_client_message(messageId);
  });

  /*---------------* Delete clients messages - Updated for modern structure *---------------*/
  function delete_chat_client_message(msg_id) {
    var selector = $(".client_messages .modern-messages-container").find('[data-message-id="' + msg_id + '"]');
    var client_id = $(".chat_clients_list > li.contact_name.selected").attr("id");

    $.post(prchatSettings.deleteClientMessage, {
      message_id: msg_id
    }).done(function (response) {
      if (response) {
        selector.remove();
        // Check if last element is a date separator and remove if needed
        let lastChildren = $(".client_messages .modern-messages-container").children().last();
        if (lastChildren.hasClass("modern-date-separator")) {
          lastChildren.remove();
        }

        // Update sidebar preview with last message
        if (typeof updateSidebarAfterDelete === 'function' && client_id) {
          updateSidebarAfterDelete('client', client_id);
        }

        // Get the active contact ID for shared files
        var activeContactId = $(".chat_clients_list > li.contact_name.selected").attr("id");
        if (activeContactId) {
          getSharedFiles(userSessionId, activeContactId);
        }
      } else {
        selector.remove();
        alert_float("danger", "<?php echo _l('chat_error_float'); ?>");
      }
    });
  }

  function loadContactPreviews() {
    var l_say_hi = "<?= _l('chat_say_hi'); ?>";

    // Collect all contact IDs in one pass
    var contactIds = [];
    $(".contact_name").each(function () {
      var cid = $(this).attr("id");
      if (cid) contactIds.push(cid);
    });

    if (contactIds.length === 0) return;

    // Single batch request instead of N individual requests
    $.ajax({
      url: prchatSettings.getClientContactPreviews,
      type: "GET",
      data: {
        contact_ids: contactIds.join(",")
      },
      success: function (previews) {
        if (typeof previews === "string") {
          try {
            previews = JSON.parse(previews);
          } catch (e) {
            previews = {};
          }
        }

        $(".contact_name").each(function () {
          var contactId = $(this).attr("id");
          var contactElement = $(this);
          var previewElement = contactElement.find(".contact_preview");
          if (!contactId || previewElement.length === 0) return;

          var data = previews[contactId];
          if (data && data.message) {
            // Render the message first to convert filenames to HTML
            var rendered = PrchatSafeRenderer.render(data.message);
            var previewText = rendered.sidebar;

            if (data.sender_id === "staff_" + userSessionId) {
              previewText = "<?= _l('chat_message_you'); ?> " + previewText;
            }

            if (previewText.length > 50) {
              previewText = previewText.substring(0, 50) + "...";
            }

            previewElement.html(previewText).show();

            if (data.time_sent) {
              contactElement.find(".contact_time").text(moment(data.time_sent).fromNow()).show();
              // Store raw timestamp for sorting
              contactElement.attr("data-last-message-time", data.time_sent);
            } else {
              contactElement.find(".contact_time").hide();
            }
          } else {
            var contactName = contactElement.find(".contact_name_main").text();
            previewElement.html(l_say_hi + " " + contactName).show();
            contactElement.find(".contact_time").hide();
          }
        });

        // Add pin/mute icons to client companies
        if (typeof pinnedClients !== 'undefined' || typeof mutedClients !== 'undefined') {
          $(".chat_clients_list > li.contact_name").each(function () {
            var cid = $(this).attr("id");
            $(this).find(".contact-pin-icon, .contact-mute-icon").remove();
            if (typeof pinnedClients !== 'undefined' && pinnedClients.indexOf(String(cid)) !== -1) {
              $(this).prepend('<i class="fa fa-thumb-tack contact-pin-icon"></i>');
              $(this).attr("data-pinned", "1");
            }
            if (typeof mutedClients !== 'undefined' && mutedClients.indexOf(String(cid)) !== -1) {
              $(this).prepend('<i class="fa fa-bell-slash contact-mute-icon"></i>');
            }
          });
        }

        // Reorder contacts by most recent conversation after previews are loaded
        reorderContactList();

        if (!window.matchMedia("only screen and (max-width: 735px)").matches && !window.__prchatOpenClientFromNotification) {
          selectBestClientContact();
        }
      },
      error: function () {
        // Fallback: show default text for all contacts
        $(".contact_name").each(function () {
          var contactElement = $(this);
          var previewElement = contactElement.find(".contact_preview");
          var contactName = contactElement.find(".contact_name_main").text();
          previewElement.html(l_say_hi + " " + contactName).show();
          contactElement.find(".contact_time").hide();
        });
      }
    });
  }

  function selectBestClientContact() {
    if ($(".contact_name.selected").length > 0) return;
    var first = $(".chat_clients_list > li.contact_name:first");
    if (first.length) {
      first.click();
    }
  }

  function updateContactPreview(contactId, message, isSentByMe, timeString) {
    var contactElement = $(".contact_name#" + contactId);
    var previewElement = contactElement.find(".contact_preview");
    var timeElement = contactElement.find(".contact_time");

    if (previewElement.length > 0) {
      var previewText = "";

      // Render the message first to convert filenames to HTML, then extract sidebar preview
      var rendered = PrchatSafeRenderer.render(message);
      var messageText = rendered.sidebar;

      if (isSentByMe) {
        previewText = "<?= _l('chat_message_you'); ?> " + messageText;
      } else {
        previewText = messageText;
      }

      previewElement.html(previewText).show();

      if (timeString) {
        var timeDiff = moment().diff(moment(timeString), 'seconds');
        if (timeDiff < 30 && !isSentByMe) {
          timeElement.text("now").show();
        } else {
          timeElement.text(moment(timeString).fromNow()).show();
        }
      } else {
        timeElement.hide();
      }

      if (!contactElement.hasClass("selected")) {
        contactElement.prependTo(".chat_clients_list");
      }
    }
  }

  // Function to update client contact online/offline status
  function updateClientContactStatus(contactId, isOnline, status) {
    var l_say_hi = "<?= _l('chat_say_hi'); ?>";
    // Use attribute selector - numeric IDs don't work with #id syntax
    var contactElement = $(".contact_name[id='" + contactId + "']");
    var imgElement = contactElement.find(".chatContactImage");

    var timeElement = contactElement.find(".contact_time");

    if (isOnline) {
      // Update to online status (could be online, away, busy)
      var actualStatus = (status && status !== "") ? status : "online";
      contactElement.addClass("contactActive");
      imgElement.removeClass("offline away busy online").addClass(actualStatus);

      // Check if this contact has recent messages before showing time
      var previewElement = contactElement.find(".contact_preview");
      if (previewElement.text().includes(l_say_hi)) {
        // No recent messages, hide time element (like staff side)
        timeElement.hide();
      } else {
        // Has recent messages, show "now"
        timeElement.text("now").show();
      }
    } else {
      // Update to offline status
      contactElement.removeClass("contactActive");
      imgElement.removeClass("online away busy").addClass("offline");

      // Check if this contact has recent messages before setting time
      var previewElement = contactElement.find(".contact_preview");
      if (previewElement.text().includes(l_say_hi)) {
        // No recent messages, hide time element (like staff side)
        timeElement.hide();
      } else {
        // Has recent messages, leave empty or show last seen
        timeElement.text("").hide();
      }
    }
  }

  // Function to handle contact ordering similar to staff side
  function reorderContactList() {
    var contacts = $(".chat_clients_list > li.contact_name").toArray();

    contacts.sort(function (a, b) {
      var aPinned = $(a).attr("data-pinned") === "1" ? 1 : 0;
      var bPinned = $(b).attr("data-pinned") === "1" ? 1 : 0;
      if (aPinned !== bPinned) return bPinned - aPinned;

      var aUnread = parseInt($(a).find(".unread-notifications").attr("data-badge")) || 0;
      var bUnread = parseInt($(b).find(".unread-notifications").attr("data-badge")) || 0;
      if (aUnread > 0 && bUnread === 0) return -1;
      if (aUnread === 0 && bUnread > 0) return 1;

      var aOnline = $(a).hasClass("contactActive") ? 1 : 0;
      var bOnline = $(b).hasClass("contactActive") ? 1 : 0;
      var aHasMessages = $(a).attr("data-last-message-time") ? 1 : 0;
      var bHasMessages = $(b).attr("data-last-message-time") ? 1 : 0;

      var aScore = (aOnline && aHasMessages) ? 3 : aHasMessages ? 2 : aOnline ? 1 : 0;
      var bScore = (bOnline && bHasMessages) ? 3 : bHasMessages ? 2 : bOnline ? 1 : 0;
      if (aScore !== bScore) return bScore - aScore;

      var aRawTime = $(a).attr("data-last-message-time");
      var bRawTime = $(b).attr("data-last-message-time");
      if (aRawTime || bRawTime) {
        var aTs = aRawTime ? new Date(aRawTime).getTime() : 0;
        var bTs = bRawTime ? new Date(bRawTime).getTime() : 0;
        return bTs - aTs;
      }

      return 0;
    });

    $(".chat_clients_list").empty().append(contacts);
  }

  // Function to update client contact time displays periodically
  function updateClientContactTimes() {
    var l_say_hi = "<?= _l('chat_say_hi'); ?>";
    $(".contact_name").each(function () {
      var contactElement = $(this);
      var timeElement = contactElement.find(".contact_time");
      var currentTime = timeElement.text();



      // Update online contacts to show appropriate time
      if (contactElement.hasClass("contactActive")) {
        var previewElement = contactElement.find(".contact_preview");
        if (previewElement.text().includes(l_say_hi)) {
          // No recent messages, hide time element (like staff side)
          timeElement.hide();
        } else {
          // Has recent messages, show "now"
          timeElement.text("now").show();
        }
      }
    });
  }

  // Update client contact times every 30 seconds
  setInterval(updateClientContactTimes, 30000);

  // Helper function to get time order value for sorting
  function getTimeOrderValue(timeText) {
    if (!timeText) return 999999;
    if (timeText === "now") return 0;

    var match = timeText.match(/(\d+)([mhd])/);
    if (match) {
      var value = parseInt(match[1]);
      var unit = match[2];

      if (unit === 'm') return value;
      if (unit === 'h') return value * 60;
      if (unit === 'd') return value * 1440;
    }

    return 999999;
  }

  // Function to update has-unread-messages class for browsers without :has() support
  function updateUnreadMessagesClass() {
    $(".contact_name").each(function () {
      var $contact = $(this);
      var $notification = $contact.find(".unread-notifications");
      var badgeCount = $notification.attr("data-badge") || "0";

      if (badgeCount !== "0" && parseInt(badgeCount) > 0) {
        $contact.addClass("has-unread-messages");
      } else {
        $contact.removeClass("has-unread-messages");
      }
    });
  }

  // Call updateUnreadMessagesClass when DOM is ready
  $(document).ready(function () {
    updateUnreadMessagesClass();
  });

  $(document).on('click', '.client_messages [data-chat-file="image"]', function (e) {
    e.preventDefault();
    var imageUrl = $(this).attr('data-file-url') || $(this).attr('href');
    var filename = $(this).attr('data-filename') || '';
    if (imageUrl && typeof window.openImageModal === 'function') {
      window.openImageModal(imageUrl, filename);
    }
  });

  $(document).on('click', '.client_messages .message-reply-context', function (e) {
    e.preventDefault();
    var originalId = $(this).attr('data-original-message-id');
    if (!originalId || originalId === '0') return;
    var targetMsg = $('.client_messages').find('[data-message-id="' + originalId + '"]');
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
      setTimeout(function () {
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

  var clientNotesBaseUrl = "<?= admin_url('prchat/Prchat_Controller/'); ?>";
  $(document).on('click', '.chat-action-client-notes', function () {
    var contactId = $(".chat_clients_list > li.contact_name.selected").attr("id");
    if (!contactId) return;
    var $modal = $("#client_notes_modal");
    if (!$modal.length) return;
    $modal.find(".client_notes_contact_name").text($(".chat_clients_list > li.contact_name.selected .contact_name_main").text().trim() || contactId);
    $modal.data("contact-id", contactId);
    $modal.find(".client_notes_list").empty();
    $modal.find(".client_notes_new_content").val("");
    $modal.find(".client_notes_edit_id").val("");
    $modal.find(".client_notes_edit_content").val("").closest(".client_notes_edit_row").hide();
    $modal.modal("show");
    $.get(clientNotesBaseUrl + "get_client_notes", {
      contact_id: contactId
    })
      .done(function (res) {
        if (res && res.success && res.notes && res.notes.length) {
          res.notes.forEach(function (n) {
            var html = '<div class="client_note_item tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 tw-mb-2" data-note-id="' + n.id + '">' +
              '<div class="tw-flex tw-justify-between tw-items-start tw-gap-2">' +
              '<div class="tw-flex-1"><div class="tw-text-sm tw-text-neutral-600">' + (n.author_name || '') + ' &middot; ' + (n.created_at || '') + '</div>' +
              '<div class="client_note_text tw-mt-1">' + (n.note_content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div></div>' +
              '<div class="tw-flex tw-gap-1"><button type="button" class="btn btn-default btn-xs client_note_edit" data-id="' + n.id + '" data-content="' + (n.note_content || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '"><?= _l('chat_client_notes_edit'); ?></button>' +
              '<button type="button" class="btn btn-danger btn-xs client_note_delete" data-id="' + n.id + '"><?= _l('chat_client_notes_delete'); ?></button></div></div></div>';
            $modal.find(".client_notes_list").append(html);
          });
        } else {
          $modal.find(".client_notes_list").html('<p class="tw-text-neutral-500 tw-p-2"><?= _l('chat_client_notes_empty'); ?></p>');
        }
      })
      .fail(function () {
        $modal.find(".client_notes_list").html('<p class="tw-text-danger tw-p-2"><?= _l('chat_client_notes_error'); ?></p>');
      });
  });
  $(document).on('click', '#client_notes_modal .client_note_edit', function () {
    var id = $(this).data("id");
    var content = $(this).data("content");
    var $modal = $("#client_notes_modal");
    $modal.find(".client_notes_edit_id").val(id);
    $modal.find(".client_notes_edit_content").val(content).closest(".client_notes_edit_row").show();
  });
  $(document).on('click', '#client_notes_modal .client_note_delete', function () {
    var id = $(this).data("id");
    if (!confirm("<?= _l('chat_client_notes_delete_confirm'); ?>")) return;
    var $modal = $("#client_notes_modal");
    var deleteNoteData = {
      note_id: id
    };
    if (typeof csrfData !== "undefined") {
      deleteNoteData[csrfData.token_name] = csrfData.hash;
    }
    $.post(clientNotesBaseUrl + "delete_client_note", deleteNoteData)
      .done(function (res) {
        if (res && res.success) {
          $modal.find(".client_note_item[data-note-id='" + id + "']").remove();
          alert_float("success", "<?= _l('chat_client_notes_deleted'); ?>");
        } else {
          alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
        }
      })
      .fail(function () {
        alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
      });
  });
  $(document).on('click', '#client_notes_modal .client_notes_save_new', function () {
    var content = $("#client_notes_modal .client_notes_new_content").val().trim();
    if (!content) return;
    var contactId = $("#client_notes_modal").data("contact-id");
    if (!contactId) return;
    var $modal = $("#client_notes_modal");
    var saveNoteData = {
      contact_id: contactId,
      note_content: content
    };
    if (typeof csrfData !== "undefined") {
      saveNoteData[csrfData.token_name] = csrfData.hash;
    }
    $.post(clientNotesBaseUrl + "save_client_note", saveNoteData)
      .done(function (res) {
        if (res && res.success && res.id) {
          $modal.find(".client_notes_new_content").val("");
          var escContent = (content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          var html = '<div class="client_note_item tw-border tw-border-neutral-200 tw-rounded-lg tw-p-3 tw-mb-2" data-note-id="' + res.id + '">' +
            '<div class="tw-flex tw-justify-between tw-items-start"><div class="tw-flex-1"><div class="tw-text-sm tw-text-neutral-600"> &middot; </div><div class="client_note_text tw-mt-1">' + (content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div></div>' +
            '<div class="tw-flex tw-gap-1"><button type="button" class="btn btn-default btn-xs client_note_edit" data-id="' + res.id + '" data-content="' + escContent + '"><?= _l('chat_client_notes_edit'); ?></button><button type="button" class="btn btn-danger btn-xs client_note_delete" data-id="' + res.id + '"><?= _l('chat_client_notes_delete'); ?></button></div></div></div>';
          if ($modal.find(".client_notes_list p").length) {
            $modal.find(".client_notes_list").html(html);
          } else {
            $modal.find(".client_notes_list").prepend(html);
          }
          alert_float("success", "<?= _l('chat_client_notes_saved'); ?>");
        } else {
          alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
        }
      })
      .fail(function () {
        alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
      });
  });
  $(document).on('click', '#client_notes_modal .client_notes_save_edit', function () {
    var id = $("#client_notes_modal .client_notes_edit_id").val();
    var content = $("#client_notes_modal .client_notes_edit_content").val().trim();
    if (!id || !content) return;
    var $modal = $("#client_notes_modal");
    var updateNoteData = {
      note_id: id,
      note_content: content
    };
    if (typeof csrfData !== "undefined") {
      updateNoteData[csrfData.token_name] = csrfData.hash;
    }
    $.post(clientNotesBaseUrl + "update_client_note", updateNoteData)
      .done(function (res) {
        if (res && res.success) {
          var $item = $modal.find(".client_note_item[data-note-id='" + id + "']");
          $item.find(".client_note_text").text(content);
          $modal.find(".client_notes_edit_row").hide().find(".client_notes_edit_content").val("");
          alert_float("success", "<?= _l('chat_client_notes_saved'); ?>");
        } else {
          alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
        }
      })
      .fail(function () {
        alert_float("danger", "<?= _l('chat_client_notes_error'); ?>");
      });
  });
</script>

<div class="modal fade" id="client_notes_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><?= _l('chat_client_notes'); ?>: <span class="client_notes_contact_name"></span></h4>
      </div>
      <div class="modal-body">
        <div class="client_notes_list tw-mb-3"></div>
        <div class="tw-mb-2">
          <label class="control-label"><?= _l('chat_client_notes_add'); ?></label>
          <textarea class="form-control client_notes_new_content tw-mb-2" rows="2"
            placeholder="<?= _l('chat_client_notes_add_placeholder'); ?>"></textarea>
          <button type="button" class="btn btn-primary client_notes_save_new"><?= _l('chat_save_button'); ?></button>
        </div>
        <div class="client_notes_edit_row tw-mb-2" style="display:none;">
          <label class="control-label"><?= _l('chat_client_notes_edit'); ?></label>
          <input type="hidden" class="client_notes_edit_id" value="" />
          <textarea class="form-control client_notes_edit_content tw-mb-2" rows="2"></textarea>
          <button type="button" class="btn btn-primary client_notes_save_edit"><?= _l('chat_save_button'); ?></button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
if (get_option('chat_allow_staff_to_create_tickets') == 1 || is_admin() && is_staff_member()) {
  // Include convert to ticket modal javascript code
  require('modules/prchat/assets/module_includes/modal_additional_files_includes/convert_to_ticket_inc.php');
} ?>


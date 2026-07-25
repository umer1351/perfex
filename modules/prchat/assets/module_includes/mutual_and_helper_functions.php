<script>
  var PRCHAT_CSRF_NAME = '<?php echo get_instance()->security->get_csrf_token_name(); ?>';
  window.prchatI18n = window.prchatI18n || {};
  window.prchatI18n.chat_voice_message = <?php echo json_encode(_l('chat_voice_message')); ?>;
  window.prchatI18n.chat_new_file_uploaded = <?php echo json_encode(_l('chat_new_file_uploaded')); ?>;
  window.prchatI18n.chat_new_photo_uploaded = <?php echo json_encode(_l('chat_new_photo_uploaded')); ?>;

  /**
   * Main Settings for clients chat
   * Used in chat_clients_view.php
   */
  var customerSettings = {
    "clientPusherAuth": '<?php echo site_url('prchat/Prchat_ClientsController/pusherCustomersAuth'); ?>',
    "getMutualMessages": '<?php echo site_url('prchat/Prchat_ClientsController/getMutualMessages'); ?>',
    "clientsMessagesPath": '<?php echo site_url('prchat/Prchat_ClientsController/initClientChat'); ?>',
    "getStaffUnreadMessages": '<?php echo site_url('prchat/Prchat_ClientsController/getStaffUnreadMessages'); ?>',
    "updateStaffUnread": '<?php echo site_url('prchat/Prchat_ClientsController/updateClientUnreadMessages'); ?>',
    "hasComeOnlineText": "<?php echo _l('chat_user_is_online'); ?>",
    "groupNameChanged": "<?php echo _l('chat_group_renamed'); ?>",
    "groupNewName": "<?php echo _l('chat_group_newname'); ?>",
    "chatRenameLabel": "<?php echo _l('chat_rename_label'); ?>",
    "noMoreMessagesText": "<?php echo _l('chat_no_more_messages_to_show'); ?>",
    "uploadMethod": '<?php echo site_url('prchat/Prchat_ClientsController/uploadMethod'); ?>',
    "editClientMessage": '<?php echo site_url('prchat/Prchat_ClientsController/editClientMessage'); ?>',
    "clientUploadsBase": '<?php echo rtrim(base_url('modules/prchat/uploads/clients/'), '/') . '/'; ?>',
    "debug": false,
  };

  // Allow delete only if setting enabled AND user has role capability (do not special-case admins)
  var CHAT_STAFF_CAN_DELETE_MESSAGES = <?= (is_admin() || staff_can('delete', PR_CHAT_MODULE_NAME)) ? 'true' : 'false'; ?>;
  var _scrollEvent = true;
  $(function () {
    $(window).on("load resize", function (e) {
      if (is_mobile()) {
        // Wait until metsiMenu, collapse and other effect finish and set wrapper height
        setTimeout(function () {
          $("#wrapper").css("min-height", "100%");
        }, 500);

      };
    });
  });

  /*-------* Simple enter key function *-------*/
  $.fn.enterKey = function (fnc) {
    return this.each(function () {
      $(this).keypress(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if (keycode == "13") {
          fnc.call(this, e);
        }
      });
    });
  };

  /*-------* Live internet connection tracker *-------*/
  function internetConnectionCheck() {
    return navigator.onLine ? true : false;
  }

  /*---------------* Security escaping html in chatboxes prevent database injection *---------------*/
  function escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/'/g, "&#039;");
  }

  /*---------------* Check if URL is a video platform supported by inline video embeds *---------------*/
  function isVideoUrl(url) {
    var youtubeRegex = /(youtube(-nocookie)?\.com|youtu\.be)/i;
    var vimeoRegex = /(vimeo(pro)?.com)/i;
    var facebookVideoRegex = /(facebook\.com)\/([a-z0-9_-]*)\/videos\//i;
    var googlemapsRegex = /((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i;

    return youtubeRegex.test(url) || vimeoRegex.test(url) || facebookVideoRegex.test(url) || googlemapsRegex.test(url);
  }

  /*---------------* Capitalize first string of letter *---------------*/
  function strCapitalize(string) {
    if (string != undefined) {
      var firstCP = string.codePointAt(0);
      var index = firstCP > 0xFFFF ? 2 : 1;

      return String.fromCodePoint(firstCP).toUpperCase() + string.slice(index);
    }
  }


  /*---------------* Dynamic skeleton generators *---------------*/

  /**
   * Generate message skeleton rows inside a container.
   * @param {string|jQuery} container  CSS selector or jQuery object of .messages-skeleton
   * @param {number}        count      Number of skeleton rows to generate (default 10)
   */
  window.generateMessageSkeleton = function (container, count) {
    count = count || 10;
    var $el = $(container);
    var html = '';
    var widths = [35, 40, 42, 45, 48, 50, 52, 55, 58, 60, 65, 70];

    for (var i = 0; i < count; i++) {
      // Alternate left/right with slight randomness
      var isRight = (i % 3 === 1) || (i % 5 === 3);
      var lineCount = (i % 3 === 0) ? 2 : 1;
      var w1 = widths[Math.floor(Math.random() * widths.length)];
      var w2 = widths[Math.floor(Math.random() * widths.length)];

      if (isRight) {
        html += '<div class="skel-row skel-right"><div class="skel-lines">';
        html += '<div class="skel-line" style="width:' + w1 + '%"></div>';
        if (lineCount === 2) html += '<div class="skel-line" style="width:' + w2 + '%"></div>';
        html += '</div><div class="skel-avatar"></div></div>';
      } else {
        html += '<div class="skel-row skel-left"><div class="skel-avatar"></div><div class="skel-lines">';
        html += '<div class="skel-line" style="width:' + w1 + '%"></div>';
        if (lineCount === 2) html += '<div class="skel-line" style="width:' + w2 + '%"></div>';
        html += '</div></div>';
      }
    }

    $el.html(html).removeClass('hidden');
  };

  /**
   * Generate sidebar contact skeleton rows inside a container.
   * @param {string|jQuery} container  CSS selector or jQuery object of .contacts-skeleton
   * @param {number}        count      Number of skeleton contact rows (default 6)
   */
  window.generateContactSkeleton = function (container, count) {
    count = count || 6;
    var $el = $(container);
    var html = '';
    var widths = [45, 50, 55, 60, 65, 70];
    var shortWidths = [30, 35, 38, 40, 44];

    for (var i = 0; i < count; i++) {
      var w1 = widths[Math.floor(Math.random() * widths.length)];
      var w2 = shortWidths[Math.floor(Math.random() * shortWidths.length)];
      html += '<div class="skel-contact">';
      html += '<div class="skel-contact-avatar"></div>';
      html += '<div class="skel-contact-lines">';
      html += '<div class="skel-line" style="width:' + w1 + '%"></div>';
      html += '<div class="skel-line short" style="width:' + w2 + '%"></div>';
      html += '</div></div>';
    }

    $el.html(html).removeClass('hidden');
  };

  /**
   * Hide a skeleton container.
   * @param {string|jQuery} container
   */
  window.hideSkeleton = function (container) {
    $(container).addClass('hidden');
  };


  /*---------------* Function that handles clients events after clicked back to .crm_clients in tabs *---------------*/
  function clientsListCheck() {
    $(".client_messages").removeClass("isFocused");
    $("body").find(".contact_name.selected").removeClass("selected");
  }

  /*---------------* Get user avatar picture *---------------*/
  function fetchUserAvatar(id, image_name) {
    var baseUrl = site_url.replace(/\/+$/, ''); // Remove trailing slashes
    var placeholder = baseUrl + "/assets/images/user-placeholder.jpg";

    // Check if id is undefined or null
    if (id == undefined || id == null || id === "") {
      return placeholder;
    }

    // Check if image_name is valid (backend now validates file existence)
    if (image_name == false || image_name == null || image_name == undefined || image_name === "" || image_name === "null") {
      return placeholder;
    }

    // If image_name is already a full URL, use it directly
    if (typeof image_name === 'string' && (image_name.indexOf('http') === 0 || image_name.indexOf('/assets/') === 0)) {
      return image_name;
    }

    return baseUrl + "/uploads/staff_profile_images/" + id + "/thumb_" + image_name;
  }

  /*---------------* Global handler for broken profile images *---------------*/
  var placeholderImage = site_url.replace(/\/+$/, '') + "/assets/images/user-placeholder.jpg";

  // Handle broken images globally for staff profile images
  $(document).on('error', 'img.imgFriend, img.staff-profile-image, img.staff-profile-image-small, .staff_children_parent_child_div img, .contact-profile img, .wrap img', function () {
    if (this.src !== placeholderImage) {
      // Extract id and image_name from URL to cache the failure
      var match = this.src.match(/staff_profile_images\/(\d+)\/thumb_(.+)$/);
      if (match && window.markAvatarFailed) {
        window.markAvatarFailed(match[1], match[2]);
      }
      this.onerror = null;
      this.src = placeholderImage;
    }
  });

  // Also use MutationObserver to catch dynamically added images
  if (typeof MutationObserver !== 'undefined') {
    var imgObserver = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            var images = node.querySelectorAll ? node.querySelectorAll('img') : [];
            images.forEach(function (img) {
              if (!img.onerror && img.src && img.src.indexOf('staff_profile_images') !== -1) {
                img.onerror = function () {
                  this.onerror = null;
                  this.src = placeholderImage;
                };
              }
            });
          }
        });
      });
    });
    imgObserver.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  /*---------------* Check if element has scrollbar *---------------*/
  (function ($) {
    $.fn.hasScrollBar = function () {
      return this.get(0).scrollHeight > this.get(0).clientHeight;
    };
  })(jQuery);

  /*---------------* Set no more messages if not found in database *---------------*/
  // Used in chat_full_view.php
  function prchat_setNoMoreMessages() {
    if ($("#no_messages").length == 0) {

      $(".client_messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");

      $(".messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");
    }
  }

  // Used in chat_full_view.php
  function prchat_setNoMoreGroupMessages() {
    if ($("#no_messages").length == 0) {
      $(".group_messages .chat_group_messages").prepend("<div class=\"text-center mtop5\" id=\"no_messages\">" + prchatSettings.noMoreMessagesText + "</div>");
    }
  }

  // Used in chat_clients_view.php
  function prchat_setNoMoreStaffMessages() {
    if ($("#no_messages").length == 0) {
      $(".m-area").prepend("<div class=\"text-center mtopbottomfixed\" id=\"no_messages\">" + customerSettings.noMoreMessagesText + "</div>");
    }
  }

  /*---------------* Chat shortcuts for better user experience Ctrl+Alt+Z and Ctrl+Alt+S *---------------*/
  $(document).keydown(function (e) {
    if ((e.which === 90 || e.keyCode == 90) && e.ctrlKey && e.altKey) {
      $(".messages").animate({
        scrollTop: $(".messages").prop("scrollHeight")
      }, 500);
    }

    if ((e.which === 83 || e.keyCode == 83) && e.ctrlKey && e.altKey) {
      $("#frame").find("#search_field").focus();
    }

    if ((e.which === 81 || e.keyCode == 81) && e.ctrlKey && e.shiftKey) {
      $(".quickMentions a").trigger("click");
    }


  });

  var decodeChatMessageHtml = function (html) {
    var text = document.createElement("textarea");
    text.innerHTML = html;
    return text.value;
  };

  if ($(window).width() > 733) {
    $("body").removeClass("hide-sidebar").addClass("show-sidebar");
  } else {
    $("body").removeClass("show-sidebar").addClass("hide-sidebar");
  }

  function animateContent() {
    /**
     * After all notifications are read show all staff
     */
    $(".chat_contacts_list li a:visible").filter(function (i, el) {
      const user = $(el);
      if (user.find("span.unread-notifications").is(":hidden")) {
        $(".chat_contacts_list li a:hidden").show();
      }
    });

    // Only run animation on actual mobile devices, not desktop
    if (window.matchMedia("only screen and (max-width: 735px)").matches && window.innerWidth <= 735) {
      var contentWidth = $("#frame .content");
      setTimeout(function () {
        isContentActive = true;
      }, 1000);
      contentWidth.css("display", "flex").animate({
        "left": "0",
        "opacity": "1"
      }, 50, "linear");
      $("#frame #sidepanel").fadeOut(50);

      // Ensure skeleton is hidden after messages load (fallback)
      setTimeout(function () {
        $(".messages .messages-skeleton").addClass("hidden");
        $(".group_messages .messages-skeleton").addClass("hidden");
        $(".client_messages .messages-skeleton").addClass("hidden");
      }, 2000);
    }
  }

  function chatBackMobile() {
    if (window.matchMedia("only screen and (max-width: 735px)").matches) {
      $("#frame #sidepanel").fadeIn(50);
      $("#frame .content").animate({
        "left": "+=100%",
        "opacity": "0"
      }, 200, "linear", function () {
        $(this).hide();
      });
      isContentActive = false;
    }
  }
  window.chatBackMobile = chatBackMobile;

  function _debounce(func, wait, immediate) {
    var timeout;
    return function () {
      var context = this,
        args = arguments;
      var later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  };

  $("#frame textarea").on("focus click", function () {
    if (window.matchMedia("only screen and (max-width: 735px)").matches) {
      setTimeout(function () {
        scrollToBottom();
      }, 500);
    }
  });

  /**
   * In case internet connection returns back and field stays happends on mobile
   */
  $("body").on("click", ".connection_field", function () {
    $(this).fadeOut();
  });

  /**
   * Position message options for regular staff/client messages
   */
  function positionRegularMessageOptions($li) {
    var $options = $li.find('.messageOptionsDiv');
    if (!$options.length) return;

    // Client portal: positioning is 100% CSS. Clear any inline position so stylesheet rules win (consistent three-dots).
    if ($li.closest('#clientChat').length) {
      $options.css({
        top: '',
        left: '',
        right: '',
        transform: ''
      });
      return;
    }

    // Staff/group chat (admin frame): position options by bubble position – only runs outside #clientChat
    var $bubble = $li.find('p');
    if ($bubble.length) {
      var bubblePos = $bubble.position();
      var bubbleWidth = $bubble.outerWidth();

      $options.css('top', (bubblePos.top + 8) + 'px');

      if ($li.hasClass('sent')) {
        $options.css('left', (bubblePos.left - 25) + 'px');
      } else {
        $options.css('left', (bubblePos.left + bubbleWidth + 8) + 'px');
      }
    }
  }

  /**
   * Regular staff/client message hover events
   */
  // Modern message hover events - pure CSS positioning (staff, groups, clients)
  var isTouchDevice = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
  if (!isTouchDevice) {
    $("body").on("mouseenter", ".modern-message-item", function () {
      $(this).find(".chooseOption").show();
    }).on("mouseleave", ".modern-message-item", function () {
      var pickerOpen = document.querySelector('.reaction-picker-popup');
      if (pickerOpen && pickerOpen.style.display !== 'none') return;
      $(this).find(".chooseOption, .optionsMore").hide();
    });
  } else {
    var _lastTapTarget = null, _lastTapTime = 0;

    $(document).on("touchstart", function(e) {
      if (!$(e.target).closest(".messageOptionsDiv").length) {
        $(".optionsMore").hide();
      }
    });

    var _msgSelector = ".modern-message-item, li.own_group_message_li, li.from_other, li.customer_admin, li.client";
    $("body").on("click", _msgSelector, function(e) {
      if ($(e.target).closest(".messageOptionsDiv, a, button, .reaction-pill, audio, video").length) return;
      var now = Date.now();
      var $el = $(this);
      var elNode = $el[0];
      if (_lastTapTarget === elNode && (now - _lastTapTime) < 400) {
        _lastTapTarget = null;
        _lastTapTime = 0;
        $(".optionsMore").hide();
        var $choose = $el.find(".chooseOption");
        if ($choose.length) $choose.trigger("click");
      } else {
        _lastTapTarget = elNode;
        _lastTapTime = now;
      }
    });
  }

  // Legacy message hover events (for groups only - staff and clients use modern architecture)
  $("body").on("mouseenter", "li.customer_admin, li.client", function () {
    var $li = $(this);
    positionRegularMessageOptions($li);
    $li.find(".chooseOption").show();
  }).on("mouseleave", "li.customer_admin, li.client", function () {
    var pickerOpen = document.querySelector('.reaction-picker-popup');
    if (pickerOpen && pickerOpen.style.display !== 'none') return;
    $(this).find(".chooseOption, .optionsMore").hide();
  });

  /**
   * Show options for Remove or Forward
   */
  $("body").on("click", ".chooseOption", function () {
    var $dropdown = $(this).next();
    var $container = $(this).closest('.messages, .group_messages, .client_messages, .m-area, .modern-messages-container');

    // Remove any existing flip class
    $dropdown.removeClass('flip-up');

    // Hide copy button if message contains an image or file attachment (client portal can copy image URL)
    var $messageItem = $(this).closest('.modern-message-item, li');
    var hasImage = $messageItem.find('img.prchat_convertedImage, a[data-chat-file="image"], a[data-chat-file="file"]').length > 0;
    var isClientPortal = $(this).closest('#clientChat').length > 0;
    if (hasImage && !isClientPortal) {
      $dropdown.find('._copyMessage').hide();
    } else {
      $dropdown.find('._copyMessage').show();
    }

    // Show dropdown first to get its dimensions
    $dropdown.css("display", "initial");

    // Check if dropdown would overflow the container or viewport
    var dropdownRect = $dropdown[0].getBoundingClientRect();
    var containerRect = $container.length ? $container[0].getBoundingClientRect() : {
      top: 0,
      bottom: window.innerHeight
    };
    var viewportHeight = window.innerHeight;

    // Check bottom overflow: if dropdown would go below container/viewport bottom, flip it up
    var wouldOverflowBottom = dropdownRect.bottom > Math.min(containerRect.bottom, viewportHeight - 60);

    // Check top overflow: if dropdown (when flipped up) would go above container/viewport top
    var dropdownHeight = dropdownRect.height;
    var triggerTop = dropdownRect.top;
    var wouldOverflowTop = (triggerTop - dropdownHeight - 30) < Math.max(containerRect.top || 0, 60);

    // Flip up only if it would overflow bottom AND wouldn't overflow top
    if (wouldOverflowBottom && !wouldOverflowTop) {
      $dropdown.addClass('flip-up');
    }
    // If it would overflow both, keep it down (default) as it's better to scroll down than up
  });

  /**
   * Remove message click event
   * Note: Client messages are handled by specific handler in crm_clients.php
   */
  $("body").on("click", "._removeMessage", function () {
    // Skip client messages - handled by specific handler in crm_clients.php
    if ($(this).closest('.chat_client_messages').length) {
      return;
    }

    // Check if this is a group message
    var isGroupMessage = $(this).closest('.chat_group_messages').length > 0;

    if (!confirm("<?= _l('chat_confirm_delete_message') ?>")) {
      return false;
    }

    $(this).attr("disabled", true);
    var messageId = $(this).closest(".optionsMore").attr("data-id");

    // Hide the options menu
    $(this).closest(".optionsMore").hide();
    $(this).closest("li").find(".chooseOption").hide();

    if (isGroupMessage) {
      delete_group_chat_message(messageId);
    } else {
      delete_chat_message(messageId);
    }
  });


  /**
   * Copy message click event
   */
  $("body").on("click", "._copyMessage", function () {
    const cantCopy = "<?= _l('chat_cant_copy') ?>";
    const messgeCopied = "<?= _l('chat_message_copied') ?>";
    let copyText;

    // Check if it's a modern message structure
    const $modernMessage = $(this).closest(".modern-message-item");
    if ($modernMessage.length > 0) {
      // Modern message structure - get text without reply context
      const $messageText = $modernMessage.find(".modern-message-text");
      const $replyContext = $messageText.find(".message-reply-context");

      if ($replyContext.length > 0) {
        // Clone the element and remove reply context to get clean text
        const $clone = $messageText.clone();
        $clone.find(".message-reply-context").remove();
        copyText = $clone.text().trim();
      } else {
        copyText = $messageText.text();
      }
    } else {
      // Legacy message structure (staff/client chat: li > .msg-row > .msg > p, or li > p)
      var $li = $(this).parents("li");
      copyText = $li.children("p:first").text();
      if (!copyText || !copyText.trim()) {
        copyText = $li.find(".msg p").first().text();
      }
      if (!copyText || !copyText.trim()) {
        var $img = $li.find('img.prchat_convertedImage, a[data-chat-file="image"]').first();
        if ($img.length) {
          copyText = $img.is('img') ? $img.attr('src') : $img.attr('href');
        }
      }
      copyText = copyText ? copyText.trim() : '';
    }

    if (!copyText) {
      alert_float("warning", cantCopy);
      return false;
    }

    // Modern clipboard API with fallback
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(copyText).then(() => {
        alert_float("info", messgeCopied);
      }).catch(() => {
        // Fallback to old method
        const el = document.createElement("textarea");
        el.value = copyText;
        document.body.appendChild(el);
        el.select();
        document.execCommand("copy");
        document.body.removeChild(el);
        alert_float("info", messgeCopied);
      });
    } else {
      // Fallback for older browsers
      const el = document.createElement("textarea");
      el.value = copyText;
      document.body.appendChild(el);
      el.select();
      document.execCommand("copy");
      document.body.removeChild(el);
      alert_float("info", messgeCopied);
    }

    // Hide the options menu
    $(this).closest(".optionsMore").hide();
    $(this).closest("li").find(".chooseOption").hide();
  });

  /**
   * Forward message click event
   */
  $("body").on("click", "._forwardMessage", function () {
    let message, messageEscaped;

    // Check if it's a modern message structure
    const $modernMessage = $(this).closest(".modern-message-item");
    if ($modernMessage.length > 0) {
      // Modern message structure - try to get original text before video processing
      const $messageText = $modernMessage.find(".modern-message-text");

      // Check if it has video embeds, try to get original URL
      const $videoEmbed = $messageText.find(".prchat-video-embed");
      if ($videoEmbed.length > 0) {
        // Get original video URL from data attribute
        const originalUrl = $videoEmbed.attr("data-video-url");
        if (originalUrl) {
          message = originalUrl;
          messageEscaped = originalUrl;
        } else {
          // Fallback to text content
          message = $messageText.html();
          messageEscaped = $messageText.text();
        }
      } else {
        message = $messageText.html();
        messageEscaped = $messageText.text();
      }
    } else {
      // Legacy message structure - check for video embeds
      const $messageP = $(this).parents("li").find("p");
      const $videoEmbed = $messageP.find(".prchat-video-embed");

      if ($videoEmbed.length > 0) {
        // Get original video URL from data attribute
        const originalUrl = $videoEmbed.attr("data-video-url");
        if (originalUrl) {
          message = originalUrl;
          messageEscaped = originalUrl;
        } else {
          message = $messageP.html();
          messageEscaped = $messageP.text();
        }
      } else {
        message = $messageP.html();
        messageEscaped = $messageP.text();
      }
    }

    // Hide the options menu
    $(this).closest(".optionsMore").hide();
    $(this).closest(".chooseOption").hide();

    $(".modal_container").load(prchatSettings.showForwardModal, function () {
      $("#forwardToModal").modal({
        show: true
      });

      // Use jQuery .data() to avoid HTML attribute parsing issues
      $("#forwardToModal").append(`
            <input class="_dataMessage" hidden />
            <input class="_dataMessage escaped" hidden />
            `);

      // Set the data using jQuery to avoid quote/HTML parsing issues
      $("#forwardToModal").find('._dataMessage').first().data('message', message);
      $("#forwardToModal").find('._dataMessage.escaped').data('message-escaped', messageEscaped);
    });
  });

  /**
   * Delete or forward html for message
   */
  function deleteOrForward(messageId, ticket = null, isOwn = null, messageContent = '') {
    const l_ticket = "<?= _l('ticket') ?>";
    const l_copy = "<?= _l('copy') ?>";
    const l_forward = "<?= _l('chat_forward_message_btn') ?>";
    const l_remove = "<?= _l('chat_delete_message') ?>";
    const l_reply = "<?= _l('reply') ?>";
    const isOwnFlag = (typeof isOwn === 'boolean') ? isOwn : null;
    const showDeleteButton = CHAT_STAFF_CAN_DELETE_MESSAGES && (isOwnFlag === null ? true : isOwnFlag);

    const l_edit = "<?= _l('edit') ?>";
    // Don't show edit for voice, media, file attachments, quick mentions, video embeds
    const isNonEditable = messageContent && (
      /prchat-inline-audio|prchat-video-embed|quickMentionLink|data-chat-file/.test(messageContent) ||
      /<(audio|video|img)\b/.test(messageContent) ||
      /voice_[a-z0-9_]+\.(webm|ogg|m4a)/i.test(messageContent) ||
      /(staff|clients|groups)\/[a-z0-9_]+_voice_[a-z0-9_]+\.(webm|ogg|m4a)/i.test(messageContent)
    );
    const showEditButton = (isOwnFlag === null ? true : isOwnFlag) && !isNonEditable;

    return `
            <div class="messageOptionsDiv">
            <svg height="22px" class="chooseOption" width="22px" viewBox="0 0 22 22"><circle fill="#777777" cx="11" cy="6" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="11" r="2" stroke-width="1px"></circle><circle fill="#777777" cx="11" cy="16" r="2" stroke-width="1px"></circle></svg>
            <div class="optionsMore" data-id="${messageId}">
            <button class="pointer optionBtn _reactMessage"><i class="fa fa-smile"></i><?= _l('chat_react'); ?></button>
            <button class="pointer optionBtn _replyMessage"><i class="fa fa-reply"></i>${l_reply}</button>
            <button class="pointer optionBtn _forwardMessage"><i class="fa fa-share"></i>${l_forward}</button>
            <button class="pointer optionBtn _copyMessage"><i class="fa fa-copy"></i>${l_copy}</button>
            ${showEditButton ? `<button class="pointer optionBtn _editMessage"><i class="fa fa-pencil"></i>${l_edit}</button>` : ""}
            ${showDeleteButton ? `<button class="pointer optionBtn _removeMessage"><i class="fa fa-trash"></i>${l_remove}</button>` : ""}
            ${(ticket) ? "<button class=\"pointer optionBtn btn_convert_conversation_to_ticket\">" + l_ticket + "</button>" : ""}
            </div>
            </div>
            `;
  }

  /**
   * Reply to message functionality
   */
  $("body").on("click", "._replyMessage", function () {
    var $messageElement, $messageContainer, originalMessageId;

    // Check if it's a modern message structure
    const $modernMessage = $(this).closest(".modern-message-item");
    if ($modernMessage.length > 0) {
      // Modern message structure
      $messageElement = $modernMessage.find(".modern-message-text");
      $messageContainer = $modernMessage;
      originalMessageId = $modernMessage.attr('data-message-id') || $modernMessage.attr('id');
    } else {
      // Legacy message structure
      $messageElement = $(this).parents("li").find("p").first();
      $messageContainer = $(this).parents("li");
      originalMessageId = $messageContainer.attr('id') || $messageElement.attr('id');
    }

    var messageData = extractMessageForReply($messageElement, $messageContainer);

    // Store original message ID for scroll-to functionality
    if (originalMessageId) {
      messageData.originalMessageId = originalMessageId;
    }

    var chatbox = null;

    // Try different selectors based on chat type - in order of priority
    // Staff-to-staff chat
    if (!chatbox || !chatbox.length) {
      chatbox = $("#frame textarea.chatbox:visible");
    }
    // Groups
    if (!chatbox || !chatbox.length) {
      chatbox = $("#frame textarea.group_chatbox:visible");
    }
    // Staff-to-client chat
    if (!chatbox || !chatbox.length) {
      chatbox = $("#frame textarea.client_chatbox:visible");
    }
    // Client portal chat (client replying to staff)
    if (!chatbox || !chatbox.length) {
      chatbox = $("#clientChat .clients_textarea:visible");
    }
    // Generic fallback
    if (!chatbox || !chatbox.length) {
      chatbox = $(this).closest("#frame").find("textarea:visible");
    }

    if (chatbox.length && chatbox.is(':visible')) {
      showReplyPreview(messageData, chatbox);
      chatbox.focus();
    }

    $(this).closest(".optionsMore").hide();
    $(this).closest("li").find(".chooseOption").hide();
  });

  /**
   * Edit message functionality
   */
  $("body").on("click", "._editMessage", function () {
    var $btn = $(this);
    var $modernMessage = $btn.closest(".modern-message-item");
    var messageId, $textEl, rawText;

    if ($modernMessage.length) {
      messageId = $modernMessage.attr('data-message-id');
      $textEl = $modernMessage.find(".modern-message-text");
    } else {
      var $li = $btn.closest("li");
      var $optMore = $btn.closest(".optionsMore");
      messageId = $li.attr('id') || $li.attr('data-id') || ($optMore.length ? $optMore.attr('data-id') : '');
      $textEl = $li.find("p").first();
    }

    if (!messageId || !$textEl.length) return;

    rawText = $textEl.text().trim();
    if ($textEl.find('audio, video, img, a[data-chat-file]').length) {
      alert_float('warning', '<?= _l("chat_cannot_edit_media"); ?>');
      $btn.closest(".optionsMore").hide();
      return;
    }

    // Determine edit URL and group context
    var editUrl = '', groupId = null;
    var $frame = $btn.closest("#frame");
    var $clientChat = $btn.closest("#clientChat");

    if ($frame.length) {
      if ($btn.closest('.group_messages, .chat_group_messages').length) {
        editUrl = prchatSettings.editMessagePath || (site_url + 'admin/prchat/Prchat_Controller/editMessage');
        var $gmsg = $frame.find('.group_messages .chat_group_messages:visible').first();
        groupId = ($gmsg.length && $gmsg.attr('id')) ? $gmsg.attr('id') : localStorage.getItem('prchat_active_group');
      } else if ($btn.closest('.client_messages, .chat_client_messages').length) {
        editUrl = prchatSettings.editClientMessagePath || (site_url + 'admin/prchat/Prchat_Controller/editClientMessage');
      } else {
        editUrl = prchatSettings.editMessagePath || (site_url + 'admin/prchat/Prchat_Controller/editMessage');
      }
    } else if ($clientChat.length) {
      editUrl = (typeof customerSettings !== 'undefined' && customerSettings.editClientMessage) ? customerSettings.editClientMessage : '';
    }

    if (!editUrl) {
      if (typeof alert_float === 'function') {
        alert_float('danger', '<?= _l("chat_edit_failed"); ?>');
      }
      $btn.closest(".optionsMore").hide();
      return;
    }

    // Inline edit: make the text element editable
    $textEl.data('prchat-original-text', rawText);
    $textEl.data('prchat-edit-id', messageId);
    $textEl.data('prchat-edit-url', editUrl);
    $textEl.data('prchat-edit-group', groupId);
    $textEl.attr('contenteditable', 'true').addClass('prchat-inline-editing').text(rawText).focus();

    // Select all text for easy replacement
    var range = document.createRange();
    range.selectNodeContents($textEl[0]);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);

    // Handle Enter to save, Escape to cancel
    $textEl.off('keydown.prchatEdit').on('keydown.prchatEdit', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        submitInlineEdit($(this));
      } else if (e.key === 'Escape') {
        e.preventDefault();
        cancelInlineEdit($(this));
      }
    });

    // Save on focus out
    $textEl.off('blur.prchatEdit').on('blur.prchatEdit', function() {
      var $el = $(this);
      setTimeout(function() {
        if ($el.hasClass('prchat-inline-editing')) {
          submitInlineEdit($el);
        }
      }, 150);
    });

    $btn.closest(".optionsMore").hide();
  });

  function submitInlineEdit($textEl) {
    var newText = $textEl.text().trim();
    var originalText = $textEl.data('prchat-original-text');
    var messageId = $textEl.data('prchat-edit-id');
    var editUrl = $textEl.data('prchat-edit-url');
    var groupId = $textEl.data('prchat-edit-group');

    // Clean up inline editing state
    $textEl.attr('contenteditable', 'false').removeClass('prchat-inline-editing');
    $textEl.off('keydown.prchatEdit blur.prchatEdit');

    if (!newText || newText === originalText) {
      $textEl.text(originalText);
      $textEl.removeData('prchat-original-text prchat-edit-id prchat-edit-url prchat-edit-group');
      return;
    }

    var postData = { id: messageId, message: newText };
    if (groupId) postData.group_id = groupId;

    $.post(editUrl, postData, function(res) {
      if (typeof res === 'string') { try { res = JSON.parse(res); } catch(e) { res = {}; } }
      if (res.success) {
        if (res.rendered_message && typeof PrchatSafeRenderer !== 'undefined') {
          $textEl.html(PrchatSafeRenderer.sanitize(res.rendered_message));
        } else {
          $textEl.text(newText);
        }
        var $tag = $textEl.closest('.modern-message-item, li').find('.prchat-edited-tag');
        if (!$tag.length) {
          $textEl.closest('.modern-message-meta, .modern-message-bubble').find('.modern-message-meta').prepend('<span class="modern-message-edited-tag prchat-edited-tag">(<?= _l("chat_edited"); ?>)</span>');
        }
        prchatSyncSidebarAfterEdit(null, res, groupId);
      } else {
        $textEl.text(originalText);
        if (typeof alert_float === 'function') {
          alert_float('warning', (res.error || '<?= _l("chat_edit_failed"); ?>'));
        }
      }
    }).fail(function() {
      $textEl.text(originalText);
      if (typeof alert_float === 'function') {
        alert_float('danger', '<?= _l("chat_edit_failed"); ?>');
      }
    });

    $textEl.removeData('prchat-original-text prchat-edit-id prchat-edit-url prchat-edit-group');
  }

  function cancelInlineEdit($textEl) {
    var originalText = $textEl.data('prchat-original-text');
    $textEl.attr('contenteditable', 'false').removeClass('prchat-inline-editing').text(originalText);
    $textEl.off('keydown.prchatEdit blur.prchatEdit');
    $textEl.removeData('prchat-original-text prchat-edit-id prchat-edit-url prchat-edit-group');
  }

  function cancelEditMode($chatbox) {
    $chatbox.val('').removeData('prchat-edit-id prchat-edit-url prchat-edit-group prchat-edit-text-el').removeClass('prchat-editing');
    var $composer = $chatbox.closest('.prchat-client-composer-row, .prchat-composer-row');
    if ($composer.length) {
      $composer.find('.prchat-edit-indicator').remove();
    } else {
      $chatbox.closest('.wrap, form, .chat-input-container').find('.prchat-edit-indicator').remove();
    }
  }

  window.prchatCancelEditMode = cancelEditMode;

  /**
   * After a successful message edit, refresh the sidebar preview (staff / groups / CRM clients).
   */
  function prchatSyncSidebarAfterEdit($chatbox, res, groupIdFromEdit) {
    if (!res || res.changed === false || !res.rendered_message) {
      return;
    }
    if (typeof PrchatSafeRenderer === 'undefined') {
      return;
    }
    var side = PrchatSafeRenderer.render(res.rendered_message).sidebar;
    var youLbl = '<?= _l("chat_message_you"); ?> ';

    var isStaff = $chatbox ? $chatbox.hasClass('chatbox') : $('#frame #sidepanel .staff a').parent().hasClass('active');
    var isGroup = $chatbox ? $chatbox.hasClass('group_chatbox') : !!groupIdFromEdit;
    var isClient = $chatbox ? $chatbox.hasClass('client_chatbox') : $('#frame #sidepanel .crm_clients a').parent().hasClass('active');

    if (isStaff && $('#frame #sidepanel .staff a').parent().hasClass('active')) {
      var activeId = $('li.contact.active').attr('id');
      if (!activeId) {
        return;
      }
      $('#contacts .contact.active').find('.wrap .meta .preview').text(youLbl + side);
      var sendTimeLabel = (typeof moment !== 'undefined') ? moment().fromNow() : '';
      var $ta = $('#contacts .contact a#' + activeId).find('.wrap .meta .pull-right.time_ago');
      if ($ta.length) {
        $ta.html(sendTimeLabel);
      } else {
        $('#contacts .contact a#' + activeId).find('.wrap .meta .meta-row-preview').append(
          '<p class="pull-right time_ago">' + sendTimeLabel + '</p>'
        );
      }
      if (typeof appendMemberToTop === 'function') {
        appendMemberToTop(activeId);
      }
    } else if (isGroup && $('#frame #sidepanel .groups a').parent().hasClass('active')) {
      var g = groupIdFromEdit || $('#frame .chat_groups_list li.group_selector.active').attr('id');
      if (g && typeof updateGroupPreview === 'function') {
        var ts = (typeof moment !== 'undefined') ? moment().format('YYYY-MM-DD HH:mm:ss') : '';
        updateGroupPreview(g, res.rendered_message, true, ts, null);
      }
    } else if (isClient && $('#frame #sidepanel .crm_clients a').parent().hasClass('active')) {
      var cid = $('.chat_clients_list > li.contact_name.selected').attr('id');
      if (cid && typeof updateContactPreview === 'function') {
        var ts2 = (typeof moment !== 'undefined') ? moment().format('YYYY-MM-DD HH:mm:ss') : '';
        updateContactPreview(cid, side, true, ts2);
      }
    }
  }

  function submitEdit($chatbox) {
    var msgId = $chatbox.data('prchat-edit-id');
    var editUrl = $chatbox.data('prchat-edit-url');
    var groupId = $chatbox.data('prchat-edit-group');
    var $textEl = $chatbox.data('prchat-edit-text-el');
    var newText = $chatbox.val().trim();

    if (!msgId || !editUrl || !newText) return false;

    var postData = { id: msgId, message: newText };
    if (groupId) postData.group_id = groupId;

    if (typeof PRCHAT_CSRF_NAME !== 'undefined' && PRCHAT_CSRF_NAME) {
      var $csrfIn = $chatbox.closest('form').find('input[type="hidden"]').filter(function () {
        return $(this).prop('name') === PRCHAT_CSRF_NAME;
      }).first();
      if ($csrfIn.length) {
        postData[PRCHAT_CSRF_NAME] = $csrfIn.val();
      }
    }

    $.post(editUrl, postData, function (res) {
      try { if (typeof res === 'string') res = JSON.parse(res); } catch (e) { return; }
      if (res.success && $textEl && $textEl.length) {
        var editedContent = res.rendered_message;
        if (window.PrchatMentions && groupId) {
          var _members = window.groupAllMentionMembers ||
            (window.groupMentions && window.groupMentions.members ? window.groupMentions.members : []);
          if (_members.length > 0 && editedContent.indexOf('data-mention-id') === -1) {
            editedContent = PrchatMentions.highlightMentions(editedContent, _members);
          }
        }
        $textEl.html(editedContent);
        var $bubble = $textEl.closest('.modern-message-bubble, li');
        if (res.changed !== false && !$bubble.find('.prchat-edited-tag').length) {
          $textEl.after('<span class="prchat-edited-tag">(<?= _l("chat_edited"); ?>)</span>');
        }
        prchatSyncSidebarAfterEdit($chatbox, res, groupId);
        cancelEditMode($chatbox);
      } else if (!res.success) {
        if (typeof alert_float === 'function') {
          alert_float('warning', (res.error || '<?= _l("chat_edit_failed"); ?>'));
        }
      }
    }).fail(function () {
      if (typeof alert_float === 'function') {
        alert_float('danger', '<?= _l("chat_edit_failed"); ?>');
      }
    });

    return true;
  }

  window.prchatSubmitEdit = submitEdit;

  /**
   * Extract message content and type for reply preview
   */
  function extractMessageForReply($messageElement, $messageContainer) {
    var messageHtml = $messageElement.html();
    var messageText = $messageElement.text().trim();

    // Define selectors for different media types
    var mediaSelectors = {
      images: 'img.prchat_convertedImage, a[data-chat-file="image"]',
      videos: 'video',
      audios: 'audio',
      files: 'a[data-chat-file="file"]',
      links: 'a[href*="http"]'
    };

    // Helper function to find valid image source
    function getValidImageSrc($images) {
      var validSrc = null;
      $images.each(function () {
        var src = $(this).is('img') ? $(this).attr('src') : $(this).attr('href');

        if (src && src !== '#' && src.includes('/uploads/') &&
          !src.match(/(staff_profile|profile|avatar)/)) {
          validSrc = src;
          return false; // break
        }
      });
      return validSrc;
    }

    // Helper function to create image preview HTML
    function createImagePreview(imageSrc) {
      return imageSrc ?
        `<img src="${imageSrc}" alt="Image" style="max-width: 40px; max-height: 40px; border-radius: 4px; margin-right: 8px; object-fit: cover;">Image` :
        'Photo';
    }

    // Check for images
    var $images = $messageElement.find(mediaSelectors.images);
    if ($images.length === 0 && $messageContainer) {
      $images = $messageContainer.find('img[src*="/uploads/"], a[href*="/uploads/"][data-chat-file="image"]')
        .not('img.staff-profile-image, img.modern-avatar-img, img[src*="staff_profile"], img[src*="profile"], img[src="#"], img[alt*="avatar"], img[alt*="profile"]');
    }

    if ($images.length > 0) {
      var imageSrc = getValidImageSrc($images);
      return {
        type: 'image',
        preview: createImagePreview(imageSrc)
      };
    }

    // Check for video
    if ($messageElement.find(mediaSelectors.videos).length) {
      return {
        type: 'video',
        preview: 'Video'
      };
    }

    // Check for audio
    if ($messageElement.find(mediaSelectors.audios).length) {
      return {
        type: 'audio',
        preview: 'Audio'
      };
    }

    // Check for file attachments
    var $fileElement = $messageElement.find(mediaSelectors.files);
    if ($fileElement.length) {
      var fileName = $fileElement.text().trim();
      return {
        type: 'file',
        preview: fileName
      };
    }

    // Check for links
    if ($messageElement.find(mediaSelectors.links).length && messageHtml.includes('<a')) {
      var linkText = $messageElement.find('a').first().text() || 'Link';
      var truncatedText = linkText.length > 30 ? linkText.substring(0, 30) + '...' : linkText;
      return {
        type: 'link',
        preview: truncatedText
      };
    }

    // Default to text message
    var cleanText = messageText.replace(/\s+/g, ' ').trim();
    var truncatedText = cleanText.length > 50 ? cleanText.substring(0, 50) + '...' : cleanText;

    return {
      type: 'text',
      preview: truncatedText
    };
  }

  // Global reply data storage
  window.currentReplyData = null;

  /**
   * Show modern reply preview above chat input (WhatsApp/Telegram style)
   */
  function showReplyPreview(messageData, chatbox) {
    // Store reply data globally for message sending
    window.currentReplyData = messageData;

    // Remove any existing reply preview
    chatbox.closest('#clientChat').find('.reply-preview-container').remove();

    // Create modern reply preview HTML with click-to-scroll functionality
    var clickableClass = messageData.originalMessageId ? 'reply-preview-clickable' : '';
    var clickableStyle = messageData.originalMessageId ? 'cursor: pointer;' : '';
    var clickableTitle = messageData.originalMessageId ? '<?= _l('chat_click_to_scroll'); ?>' : '';
    // Strip HTML from preview, truncate and escape so reply strip is clean text only
    var previewRaw = (messageData.preview || '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
    if (previewRaw.length > 60) previewRaw = previewRaw.substring(0, 57) + '...';
    var previewText = escapeHtml(previewRaw);

    var replyPreviewHtml = `
            <div class="reply-preview-container ${clickableClass}"
                 data-original-message-id="${messageData.originalMessageId || ''}"
                 title="${clickableTitle}"
                 style="
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                margin: 0;
                padding: 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                position: relative;
                overflow: hidden;
                transition: all 0.2s ease;
                ${clickableStyle}
            ">
                <div style="
                    padding: 12px 16px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                ">
                    <div class="reply-content-clickable" style="flex: 1; min-width: 0;">
                        <div style="
                            font-size: 11px;
                            font-weight: 600;
                            text-transform: uppercase;
                            color: #007bff;
                            margin-bottom: 4px;
                            letter-spacing: 0.5px;
                            display: flex;
                            align-items: center;
                            gap: 5px;
                        ">
                            <i class="fa fa-reply" style="font-size: 10px;"></i> <?= _l('chat_replying_to'); ?>
                        </div>
                        <div class="reply-preview-text" style="
                            color: #495057;
                            font-size: 14px;
                            line-height: 1.3;
                            word-break: break-word;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        ">
                            ${previewText || '...'}
                        </div>
                    </div>
                    <button type="button" class="cancel-reply-btn" style="
                        background: rgba(108, 117, 125, 0.1);
                        border: none;
                        color: #6c757d;
                        cursor: pointer;
                        font-size: 18px;
                        padding: 8px;
                        margin-left: 12px;
                        border-radius: 50%;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s ease;
                    " title="<?= _l('chat_cancel_reply'); ?>" onmouseover="this.style.background='rgba(108, 117, 125, 0.2)'" onmouseout="this.style.background='rgba(108, 117, 125, 0.1)'">
                        ×
                    </button>
                </div>
            </div>
        `;

    // Client chat: append reply preview under m-area, no extra wrapper padding
    var $mArea = chatbox.closest('#clientChat').find('.m-area');
    if ($mArea.length) {
      $mArea.after(replyPreviewHtml);
    } else {
      var $form = chatbox.closest('form');
      if ($form.length) {
        $form.prepend(replyPreviewHtml);
      } else {
        chatbox.before(replyPreviewHtml);
      }
    }

    // Add cancel reply functionality
    $('.cancel-reply-btn').on('click', function (e) {
      e.stopPropagation(); // Prevent triggering the scroll-to functionality
      clearReplyData();
    });

    // Add click-to-scroll functionality
    $('.reply-preview-clickable').on('click', function (e) {
      // Don't trigger if clicking the cancel button
      if ($(e.target).hasClass('cancel-reply-btn') || $(e.target).closest('.cancel-reply-btn').length) {
        return;
      }

      var originalMessageId = $(this).data('original-message-id');
      if (originalMessageId) {
        scrollToOriginalMessage(originalMessageId);
      }
    });

    // Add hover effects for clickable reply previews
    $('.reply-preview-clickable').hover(
      function () {
        $(this).css({
          'background': 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)',
          'transform': 'translateY(-1px)',
          'box-shadow': '0 4px 8px rgba(0,0,0,0.15)'
        });
      },
      function () {
        $(this).css({
          'background': 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)',
          'transform': 'translateY(0)',
          'box-shadow': '0 2px 4px rgba(0,0,0,0.1)'
        });
      }
    );

    // Auto-hide after 30 seconds
    setTimeout(function () {
      if (window.currentReplyData) {
        clearReplyData();
      }
    }, 30000);
  }

  /**
   * Clear reply data and preview
   */
  function clearReplyData() {
    window.currentReplyData = null;
    $('.reply-preview-container').fadeOut(200, function () {
      $(this).remove();
    });
  }

  /**
   * Extract clean text from reply format for sidebar previews
   */
  function extractCleanTextFromReply(message) {
    // Check if message has reply format
    var replyMatch = message.match(/\[REPLY:[^:]+:[^:]+:([^\]]+)\]\s*(.*)/);
    if (replyMatch) {
      // Return the actual message part (after the reply context)
      return replyMatch[2] || replyMatch[1];
    }
    // No reply format, return original message
    return message;
  }

  /**
   * Get reply formatted message
   */
  function getReplyFormattedMessage(originalMessage) {
    if (!window.currentReplyData) {
      return originalMessage;
    }

    var cleanPreview = window.currentReplyData.preview
      .replace(/<[^>]*>/g, '')
      .replace(/^[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]\s*/u, '')
      .trim();
    var replyPrefix = `[REPLY:${window.currentReplyData.originalMessageId}:${window.currentReplyData.type}:${cleanPreview}] `;
    return replyPrefix + originalMessage;
  }

  /**
   * Convert reply text format to HTML for display.
   * Now escapes preview text to prevent XSS.
   */
  function convertReplyToHTML(messageText) {
    var replyRegex = /\[REPLY:([^:]+):([^:]+):([^\]]+)\]\s*/;
    var match = messageText.match(replyRegex);

    if (!match) {
      return messageText;
    }

    var originalMessageId = escapeHtml(match[1]);
    var type = match[2];
    var preview = escapeHtml(match[3]);
    var actualMessage = messageText.replace(replyRegex, '');

    var replyHTML = `
            <div class="message-reply-context" data-original-message-id="${originalMessageId}">
                <div class="reply-context-content">
                    <div class="reply-context-text">${preview}</div>
                </div>
            </div>`;

    return replyHTML + actualMessage;
  }

  /**
   * Scroll to and highlight original message when reply is clicked
   */
  function scrollToOriginalMessage(messageId) {
    if (!messageId) return;
    messageId = String(messageId).trim();

    // Build list of ids to try (reply to image may store composite id; client view uses numeric li id)
    var idsToTry = [messageId];
    var parts = messageId.split(/\s+/);
    if (parts.length > 1) idsToTry.push(parts[parts.length - 1]);
    var numericMatches = messageId.match(/\d+/g);
    if (numericMatches) idsToTry.push(numericMatches[numericMatches.length - 1]);

    var $targetMessage = null;
    var i, id, $candidate;

    for (i = 0; i < idsToTry.length && (!$targetMessage || !$targetMessage.length); i++) {
      id = idsToTry[i];
      if (!id) continue;
      $candidate = $('li#' + id);
      if ($candidate.length) $targetMessage = $candidate;
      if (!$targetMessage || !$targetMessage.length) $candidate = $('p#' + id).closest('li');
      if ($candidate.length) $targetMessage = $candidate;
      if (!$targetMessage || !$targetMessage.length) $candidate = $('li[data-id="' + id + '"]');
      if ($candidate.length) $targetMessage = $candidate;
      if (!$targetMessage || !$targetMessage.length) $candidate = $('li[data-message-id="' + id + '"]');
      if ($candidate.length) $targetMessage = $candidate;
      if (!$targetMessage || !$targetMessage.length) $candidate = $('.modern-message-item[data-message-id="' + id + '"]');
      if ($candidate.length) $targetMessage = $candidate;
    }

    if ($targetMessage && $targetMessage.length) {
      var $messagesContainer = $targetMessage.closest('.messages, .chat_messages, .chat_client_messages, .chat_group_messages, .m-area');

      // Client portal (.m-area): use scrollIntoView for a single smooth scroll (avoids stutter)
      if ($messagesContainer.length && $messagesContainer.hasClass('m-area')) {
        var el = $targetMessage[0];
        if (el && el.scrollIntoView) {
          el.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
          setTimeout(function () {
            highlightMessage($targetMessage);
          }, 400);
        } else {
          var containerTop = $messagesContainer.offset().top;
          var messageTop = $targetMessage.offset().top;
          var targetScrollTop = $messagesContainer.scrollTop() + (messageTop - containerTop) - 80;
          $messagesContainer.animate({
            scrollTop: targetScrollTop
          }, 350, function () {
            highlightMessage($targetMessage);
          });
        }
        return;
      }

      if ($messagesContainer.length) {
        var containerTop = $messagesContainer.offset().top;
        var messageTop = $targetMessage.offset().top;
        var targetScrollTop = $messagesContainer.scrollTop() + (messageTop - containerTop) - 100;
        $messagesContainer.animate({
          scrollTop: targetScrollTop
        }, 350, 'swing', function () {
          highlightMessage($targetMessage);
        });
      } else {
        $('html, body').animate({
          scrollTop: $targetMessage.offset().top - 100
        }, 350, 'swing', function () {
          highlightMessage($targetMessage);
        });
      }
    } else {
      // Client portal: try loading older messages once then retry (e.g. reply to image in history)
      var $clientArea = $('#clientChat .m-area');
      if ($clientArea.length && !window._pendingScrollToMessageId) {
        window._pendingScrollToMessageId = messageId;
        $clientArea.scrollTop(0);
        alert_float('info', '<?= _l('chat_loading_older_messages'); ?>');
      } else {
        if (window._pendingScrollToMessageId) window._pendingScrollToMessageId = null;
        alert_float('warning', '<?= _l('chat_original_message_not_found'); ?>');
      }
    }
  }

  /**
   * Highlight a message temporarily
   */
  function highlightMessage($messageElement) {
    var $bubble = $messageElement.find('.modern-message-bubble, .msg');
    if (!$bubble.length) $bubble = $messageElement;
    $bubble.css({
      transition: 'border-color 0.3s, box-shadow 0.3s',
      borderColor: '#f59e0b',
      boxShadow: '0 0 0 2px rgba(245,158,11,0.3)'
    });
    setTimeout(function () {
      $bubble.css({
        borderColor: '',
        boxShadow: '',
        transition: ''
      });
    }, 1800);
  }

  $("body").on("click", ".message-reply-context", function () {
    if ($(this).closest('.chat-window').length) return;
    var originalMessageId = $(this).data('original-message-id');
    if (originalMessageId) {
      scrollToOriginalMessage(originalMessageId);
    }
  });
</script>

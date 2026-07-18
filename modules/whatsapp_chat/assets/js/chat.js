/**
 * chat.js - WhatsApp Chat Module
 *
 * Handles:
 *  - Loading conversations on click
 *  - Loading messages into the right panel
 *  - AJAX polling: conversation list every 10s, active chat every 5s
 *  - Marking conversations as read on open
 *  - Client-side search/filter on the conversation list
 *  - Auto-scroll to the latest message
 */

/* global WAC_BASE_URL, WAC_CSRF_TOKEN */

;(function ($) {
    'use strict';

    // -----------------------------------------------------------------------
    // State
    // -----------------------------------------------------------------------
    var activeConvId     = null;
    var convPollTimer    = null;
    var msgPollTimer     = null;
    var CONV_POLL_MS     = 10000;   // poll conversation list every 10s
    var MSG_POLL_MS      = 5000;    // poll active messages every 5s
    var isLoadingMsgs    = false;
    var isLoadingConvs   = false;
    var lastMsgCount     = 0;       // track message count to detect new messages

    // -----------------------------------------------------------------------
    // INIT
    // -----------------------------------------------------------------------
    $(document).ready(function () {
        // Prevent duplicate initialization
        if (window.wacInitialized) return;
        window.wacInitialized = true;

        // Kick off conversation list polling
        startConvPolling();

        // Live search with debounce
        var searchTimeout;
        $('#wac-search').on('input', function () {
            var $input = $(this);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                var q = $input.val().toLowerCase().trim();
                filterConversations(q);
            }, 150);
        });

        // Handle visibility change to pause/resume polling
        $(document).on('visibilitychange', function () {
            if (document.hidden) {
                stopConvPolling();
                stopMsgPolling();
            } else {
                startConvPolling();
                if (activeConvId) {
                    startMsgPolling(activeConvId);
                }
            }
        });
    });

    // -----------------------------------------------------------------------
    // CONVERSATION LOADING
    // -----------------------------------------------------------------------

    /**
     * Called from onclick on each conversation item.
     * @param {number} convId  ID of the conversation
     * @param {HTMLElement} el  The clicked .wac-conv-item element
     */
    window.wacLoadConversation = function (convId, el) {
        // Prevent double-clicks
        if (activeConvId === convId && $('#wac-chat-panel').is(':visible')) {
            return;
        }

        // Highlight active item
        $('.wac-conv-item').removeClass('wac-conv-active');
        $(el).addClass('wac-conv-active');

        // Reset unread badge in the list item immediately (optimistic UI)
        $(el).find('.wac-unread-badge').remove();
        $(el).removeClass('wac-conv-unread');

        // Update state
        activeConvId = convId;
        lastMsgCount = 0;

        // Show chat panel with loading state
        $('#wac-empty-state').hide();
        $('#wac-chat-panel').show();
        $('#wac-chat-header').html('<div class="wac-loading"><i class="fa fa-spinner fa-spin"></i></div>');
        $('#wac-messages-wrap').html('<div class="wac-loading"><i class="fa fa-spinner fa-spin"></i> Loading messages...</div>');

        // Clear any existing msg poll
        stopMsgPolling();

        // Load messages immediately, then start polling
        loadMessages(convId, true);
        startMsgPolling(convId);

        // Tell server to mark read
        markRead(convId);
    };

    // -----------------------------------------------------------------------
    // MESSAGES
    // -----------------------------------------------------------------------

    /**
     * Load messages for a conversation
     * @param {number} convId
     * @param {boolean} forceScroll - scroll to bottom even if not new messages
     */
    function loadMessages(convId, forceScroll) {
        if (isLoadingMsgs) return;
        if (activeConvId !== convId) return; // conversation changed

        isLoadingMsgs = true;

        $.ajax({
            url:      WAC_BASE_URL + '/get_messages/' + convId,
            type:     'GET',
            dataType: 'html',
            headers:  { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                if (activeConvId !== convId) {
                    // User switched conversations while loading
                    return;
                }

                // Parse the HTML response
                var $tmp = $('<div>').html(html);

                // Extract header and messages
                var $header   = $tmp.find('.wac-chat-header-inner');
                var $messages = $tmp.find('#wac-messages-inner');

                // Update header
                if ($header.length) {
                    $('#wac-chat-header').html($header);
                }

                // Update messages
                if ($messages.length) {
                    var msgCount = $messages.find('.wac-message-row').length;
                    var hasNewMessages = (msgCount > lastMsgCount);
                    lastMsgCount = msgCount;

                    $('#wac-messages-wrap').html($messages);

                    // Scroll to bottom if new messages or forced
                    if (forceScroll || hasNewMessages) {
                        scrollToBottom();
                    }
                } else {
                    // Fallback: render full HTML
                    $('#wac-messages-wrap').html(html);
                    if (forceScroll) {
                        scrollToBottom();
                    }
                }
            },
            error: function () {
                if (activeConvId === convId) {
                    $('#wac-messages-wrap').html(
                        '<div class="wac-no-messages">' +
                        '<i class="fa fa-exclamation-circle"></i>' +
                        '<p>Failed to load messages. Please try again.</p>' +
                        '</div>'
                    );
                }
            },
            complete: function () {
                isLoadingMsgs = false;
            }
        });
    }

    /**
     * Scroll messages container to bottom
     */
    function scrollToBottom() {
        var $wrap = $('#wac-messages-wrap');
        if ($wrap.length && $wrap[0].scrollHeight) {
            // Use requestAnimationFrame for smoother scrolling
            requestAnimationFrame(function () {
                $wrap.scrollTop($wrap[0].scrollHeight);
            });
        }
    }

    // -----------------------------------------------------------------------
    // CONVERSATION LIST POLLING
    // -----------------------------------------------------------------------

    function startConvPolling() {
        // Clear existing timer first
        stopConvPolling();
        convPollTimer = setInterval(pollConversations, CONV_POLL_MS);
    }

    function stopConvPolling() {
        if (convPollTimer) {
            clearInterval(convPollTimer);
            convPollTimer = null;
        }
    }

    function pollConversations() {
        if (isLoadingConvs) return;
        isLoadingConvs = true;

        $.ajax({
            url:      WAC_BASE_URL + '/get_conversations',
            type:     'GET',
            dataType: 'html',
            headers:  { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                // Preserve scroll position
                var $list = $('#wac-conversation-list');
                var scrollTop = $list.scrollTop();

                // Update HTML
                $list.html(html);

                // Re-apply search filter if active
                var q = $('#wac-search').val();
                if (q) {
                    filterConversations(q.toLowerCase().trim());
                }

                // Re-apply active class
                if (activeConvId) {
                    $list.find('[data-conv-id="' + activeConvId + '"]').addClass('wac-conv-active');
                }

                // Restore scroll position
                $list.scrollTop(scrollTop);

                // Update total count in header
                var convCount = $list.find('.wac-conv-item').length;
                $('#wac-total-count').text(convCount + ' conversations');
            },
            complete: function () {
                isLoadingConvs = false;
            }
        });
    }

    // -----------------------------------------------------------------------
    // MESSAGE POLLING (active conversation only)
    // -----------------------------------------------------------------------

    function startMsgPolling(convId) {
        stopMsgPolling();
        msgPollTimer = setInterval(function () {
            if (activeConvId === convId && !document.hidden) {
                loadMessages(convId, false);
            }
        }, MSG_POLL_MS);
    }

    function stopMsgPolling() {
        if (msgPollTimer) {
            clearInterval(msgPollTimer);
            msgPollTimer = null;
        }
    }

    // -----------------------------------------------------------------------
    // MARK READ
    // -----------------------------------------------------------------------

    function markRead(convId) {
        $.ajax({
            url:      WAC_BASE_URL + '/mark_read/' + convId,
            type:     'POST',
            headers:  { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            data: {
                csrf_token_name: typeof WAC_CSRF_TOKEN !== 'undefined' ? WAC_CSRF_TOKEN : ''
            }
        });
    }

    // -----------------------------------------------------------------------
    // CLIENT-SIDE SEARCH / FILTER
    // -----------------------------------------------------------------------

    function filterConversations(query) {
        var $items = $('#wac-conversation-list .wac-conv-item');

        if (!query) {
            $items.show();
            return;
        }

        $items.each(function () {
            var $item = $(this);
            var name  = String($item.data('display-name') || '').toLowerCase();
            var phone = String($item.data('phone') || '').toLowerCase();
            var searchText = name + ' ' + phone;

            if (searchText.indexOf(query) !== -1) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }

    // -----------------------------------------------------------------------
    // PUBLIC API (for external integration)
    // -----------------------------------------------------------------------
    window.WACChat = {
        refresh: function () {
            pollConversations();
            if (activeConvId) {
                loadMessages(activeConvId, false);
            }
        },
        getActiveConversation: function () {
            return activeConvId;
        },
        selectConversation: function (convId) {
            var $item = $('[data-conv-id="' + convId + '"]');
            if ($item.length) {
                window.wacLoadConversation(convId, $item[0]);
            }
        }
    };

})(jQuery);

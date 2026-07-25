<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<!-- Floating Chat Notification Widget -->
<div id="floating-chat-notifications" class="floating-chat-widget" style="display: none;">
    <div class="widget-header">
        <div class="widget-title">
            <i class="fa fa-comments"></i>
            <span class="title-text"><?php echo _l('chat'); ?></span>
            <span class="notification-count" id="notification-count">(0)</span>
        </div>
        <div class="widget-controls">
            <button id="mark-all-read" class="widget-btn" data-tip="<?php echo _l('chat_mark_all_read'); ?>">
                <i class="fa fa-check"></i>
            </button>
            <button id="clear-all" class="widget-btn" data-tip="<?php echo _l('chat_clear_all'); ?>">
                <i class="fa fa-trash"></i>
            </button>
            <button id="minimize-widget" class="widget-btn" data-tip="<?php echo _l('minimize'); ?>">
                <i class="fa fa-minus"></i>
            </button>
            <button id="close-widget" class="widget-btn" data-tip="<?php echo _l('chat_close_widget'); ?>">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="widget-body" id="notification-list">
        <div class="no-notifications">
            <i class="fa fa-bell-slash"></i>
            <p><?php echo _l('chat_no_new_messages'); ?></p>
        </div>
    </div>
</div>
<script>
    // Floating Chat Notification System
    // No localStorage for notification data — purely session-based.
    // State comes from: real-time Pusher events + actual DOM unread badges.
    (function () {
        'use strict';

        if (typeof site_url === 'undefined') {
            window.site_url = '<?= site_url(); ?>';
        }

        if (typeof fetchUserAvatar === 'undefined') {
            window.fetchUserAvatar = function (id, image_name) {
                var url = site_url + "assets/images/user-placeholder.jpg";
                if (!id) return url;
                if (!image_name) return url;
                return site_url + "uploads/staff_profile_images/" + id + "/thumb_" + image_name;
            };
        }

        // Normalize sender IDs — strip client_/staff_ prefix so "94" and "client_94" match
        function nid(id) {
            return id ? String(id).replace(/^(client_|staff_)/, '') : '';
        }

        let notifications = [];
        let widget = null;
        let isMinimized = false;
        let isVisible = false;

        // Timeout IDs for goToChat so we can clear them and avoid leaks / double runs
        let _goToChatTimeouts = {
            staff: null,
            clientPoll: null,
            clientClearFlag: null
        };

        // ── Init ──────────────────────────────────────────────────
        function initWidget() {
            widget = document.getElementById('floating-chat-notifications');
            loadWidgetPosition();
            makeDraggable();
            bindEvents();
        }

        function loadWidgetPosition() {
            try {
                var pos = JSON.parse(localStorage.getItem('chatWidgetPosition')) || {
                    top: 100,
                    right: 20
                };
                if (pos.left !== undefined) {
                    widget.style.left = pos.left + 'px';
                    widget.style.top = pos.top + 'px';
                    widget.style.right = 'auto';
                } else {
                    widget.style.top = pos.top + 'px';
                    widget.style.right = pos.right + 'px';
                }
            } catch (e) { }

            isMinimized = localStorage.getItem('chatWidgetMinimized') === 'true';
            if (isMinimized) {
                widget.classList.add('minimized');
                var icon = document.querySelector('#minimize-widget i');
                if (icon) icon.className = 'fa fa-plus';
            }
        }

        // ── Draggable ─────────────────────────────────────────────
        function makeDraggable() {
            var isDragging = false,
                dragStartX, dragStartY, elStartX, elStartY;
            var header = widget.querySelector('.widget-header');
            header.style.cursor = 'move';

            header.addEventListener('mousedown', function (e) {
                if (e.target.classList.contains('widget-btn') || e.target.closest('.widget-btn')) return;
                isDragging = true;
                header.style.cursor = 'grabbing';
                document.body.style.userSelect = 'none';
                var rect = widget.getBoundingClientRect();
                elStartX = rect.left;
                elStartY = rect.top;
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                document.addEventListener('mousemove', onDrag);
                document.addEventListener('mouseup', stopDrag);
                e.preventDefault();
            });

            function onDrag(e) {
                if (!isDragging) return;
                requestAnimationFrame(function () {
                    var pad = 10;
                    var x = Math.max(pad, Math.min(elStartX + e.clientX - dragStartX, window.innerWidth - widget.offsetWidth - pad));
                    var y = Math.max(pad, Math.min(elStartY + e.clientY - dragStartY, window.innerHeight - widget.offsetHeight - pad));
                    widget.style.left = x + 'px';
                    widget.style.top = y + 'px';
                    widget.style.right = 'auto';
                });
            }

            function stopDrag() {
                if (!isDragging) return;
                isDragging = false;
                header.style.cursor = 'move';
                document.body.style.userSelect = '';
                var rect = widget.getBoundingClientRect();
                localStorage.setItem('chatWidgetPosition', JSON.stringify({
                    top: rect.top,
                    left: rect.left
                }));
                document.removeEventListener('mousemove', onDrag);
                document.removeEventListener('mouseup', stopDrag);
            }
        }

        // ── Events ────────────────────────────────────────────────
        function bindEvents() {
            document.getElementById('mark-all-read').addEventListener('click', markAllRead);
            document.getElementById('clear-all').addEventListener('click', clearAll);
            document.getElementById('minimize-widget').addEventListener('click', toggleMinimize);
            document.getElementById('close-widget').addEventListener('click', function () {
                hideWidget();
            });

            widget.querySelector('.widget-header').addEventListener('dblclick', function (e) {
                if (e.target.classList.contains('widget-btn') || e.target.closest('.widget-btn')) return;
                toggleMinimize();
            });
        }

        // ── Notify change (Vue badge listens to this) ─────────────
        function notifyChange() {
            window.dispatchEvent(new CustomEvent('floatingNotificationsChanged'));
        }

        // ── Core: add / remove / clear ────────────────────────────
        function addNotification(data, forceShow) {
            var fromId = nid(data.from);

            // Find existing from same sender
            var existing = notifications.find(function (n) {
                return nid(n.from) === fromId;
            });
            var prevCount = existing ? (existing.messageCount || 1) : 0;

            // Remove old entry for this sender
            notifications = notifications.filter(function (n) {
                return nid(n.from) !== fromId;
            });

            // When data.messageCount is provided (from DOM badges), use it as-is.
            // When it's a real-time event (no messageCount), increment from previous.
            var newCount = data.messageCount ? data.messageCount : (prevCount + 1);

            var notification = {
                id: Date.now(),
                from: fromId,
                fromName: data.fromName,
                type: data.type,
                message: data.message,
                avatar: data.avatar,
                timestamp: new Date(),
                isNew: true,
                messageCount: newCount
            };

            notifications.unshift(notification);
            if (notifications.length > 10) notifications = notifications.slice(0, 10);

            updateWidget();
            showWidget();
            notifyChange();

            if (!forceShow && isMinimized) expandWidget();

            // Auto-dim after 10 seconds
            setTimeout(function () {
                notification.isNew = false;
                updateWidget();
            }, 10000);
        }

        function removeNotification(fromId) {
            fromId = nid(fromId);
            notifications = notifications.filter(function (n) {
                return nid(n.from) !== fromId;
            });
            updateWidget();
            notifyChange();
            if (notifications.length === 0) hideWidget();
        }

        function clearNotifications() {
            notifications = [];
            updateWidget();
            notifyChange();
        }

        // ── Widget display ────────────────────────────────────────
        function getTotalUnreadCount() {
            return notifications.reduce(function (sum, n) {
                return sum + (n.messageCount || 1);
            }, 0);
        }

        function updateWidget() {
            var total = getTotalUnreadCount();
            document.getElementById('notification-count').textContent = total > 0 ? '(' + total + ')' : '(0)';

            var list = document.getElementById('notification-list');
            if (notifications.length === 0) {
                list.innerHTML = '<div class="no-notifications"><i class="fa fa-bell-slash"></i><p><?php echo _l('chat_no_new_messages'); ?></p></div>';
            } else {
                var typeLabels = {
                    staff: '<?php echo _l('chat_staff'); ?>',
                    client: '<?php echo _l('chat_client'); ?>'
                };
                list.innerHTML = notifications.map(function (n) {
                    return '<div class="notification-item ' + (n.isNew ? 'new' : '') + '" data-from="' + n.from + '" data-type="' + n.type + '">' +
                        '<div class="notification-main" onclick="goToChat(\'' + n.from + '\', \'' + n.type + '\')">' +
                        '<div class="notification-avatar-wrapper">' +
                        '<img src="' + n.avatar + '" alt="' + escapeHtml(n.fromName) + '" class="notification-avatar" onerror="this.src=\'' + site_url + 'assets/images/user-placeholder.jpg\'">' +
                        ((n.messageCount || 1) > 1 ? '<span class="notification-msg-count">' + n.messageCount + '</span>' : '') +
                        '</div>' +
                        '<div class="notification-content">' +
                        '<div class="notification-sender">' + escapeHtml(n.fromName) + ' <span class="sender-type ' + n.type + '">' + (typeLabels[n.type] || n.type) + '</span></div>' +
                        '<div class="notification-message">' + escapeHtml(n.message) + '</div>' +
                        '<div class="notification-time">' + timeAgo(n.timestamp) + '</div>' +
                        '</div></div>' +
                        '<div class="notification-actions">' +
                        '<button type="button" class="notif-action-btn" data-action="reply" data-tip="<?php echo _l('chat_reply'); ?>"><i class="fa fa-reply"></i></button>' +
                        '<button type="button" class="notif-action-btn" data-action="mark-read" data-tip="<?php echo _l('chat_mark_as_read'); ?>"><i class="fa fa-check"></i></button>' +
                        '<button type="button" class="notif-action-btn notif-action-remove" data-action="remove" data-tip="<?php echo _l('chat_remove'); ?>"><i class="fa fa-times"></i></button>' +
                        '</div></div>';
                }).join('');
            }
        }

        // ── Show / Hide / Minimize ────────────────────────────────
        function showWidget(force) {
            if (!widget) return;
            if (force || !isVisible) {
                widget.style.display = 'block';
                isVisible = true;
                ensureInViewport();
            }
        }

        function hideWidget() {
            if (widget) widget.style.display = 'none';
            isVisible = false;
            clearNotifications();
        }

        function ensureInViewport() {
            if (!widget) return;
            var rect = widget.getBoundingClientRect();
            var vw = window.innerWidth,
                vh = window.innerHeight,
                moved = false;
            if (rect.left + rect.width > vw - 10) {
                widget.style.left = Math.max(10, vw - rect.width - 20) + 'px';
                widget.style.right = 'auto';
                moved = true;
            }
            if (rect.left < 0) {
                widget.style.left = '10px';
                widget.style.right = 'auto';
                moved = true;
            }
            if (rect.top + rect.height > vh - 10) {
                widget.style.top = Math.max(10, vh - rect.height - 20) + 'px';
                moved = true;
            }
            if (rect.top < 0) {
                widget.style.top = '10px';
                moved = true;
            }
            if (moved) {
                var r = widget.getBoundingClientRect();
                localStorage.setItem('chatWidgetPosition', JSON.stringify({
                    top: r.top,
                    left: r.left
                }));
            }
        }

        function toggleMinimize() {
            isMinimized = !isMinimized;
            widget.classList.toggle('minimized', isMinimized);
            localStorage.setItem('chatWidgetMinimized', isMinimized);
            var icon = document.querySelector('#minimize-widget i');
            icon.className = isMinimized ? 'fa fa-plus' : 'fa fa-minus';
        }

        function expandWidget() {
            if (!isMinimized) return;
            isMinimized = false;
            widget.classList.remove('minimized');
            localStorage.setItem('chatWidgetMinimized', 'false');
            var icon = document.querySelector('#minimize-widget i');
            icon.className = 'fa fa-minus';
            widget.style.animation = 'notificationPulse 0.6s ease-out';
            setTimeout(function () {
                widget.style.animation = '';
            }, 600);
        }

        // ── Mark as read ──────────────────────────────────────────
        function markAllRead() {
            var staffIds = [],
                clientIds = [];
            notifications.forEach(function (n) {
                if (n.type === 'staff') staffIds.push(n.from);
                else if (n.type === 'client') clientIds.push(n.from);
            });
            if (staffIds.length > 0) markSidebarMessagesAsRead('staff', staffIds);
            if (clientIds.length > 0) markSidebarMessagesAsRead('client', clientIds);
            hideWidget();
        }

        function clearAll() {
            hideWidget();
        }

        function markOneRead(fromId) {
            fromId = nid(fromId);
            var notif = notifications.find(function (n) {
                return nid(n.from) === fromId;
            });
            if (notif) {
                markSidebarMessagesAsRead(notif.type, [fromId]);
                removeNotification(fromId);
            }
        }

        function markNotificationsByType(type) {
            if (!isVisible || notifications.length === 0) return;
            var contactIds = [];
            notifications.forEach(function (n) {
                if (n.type === type) contactIds.push(n.from);
            });
            if (contactIds.length > 0) {
                notifications = notifications.filter(function (n) {
                    return n.type !== type;
                });
                updateWidget();
                notifyChange();
                markSidebarMessagesAsRead(type, contactIds);
                if (notifications.length === 0) hideWidget();
            }
        }

        function markSidebarMessagesAsRead(type, contactIds) {
            if (type === 'staff') {
                contactIds.forEach(function (staffId) {
                    $('#contacts .contact a#' + staffId + ' .unread-notifications').remove();
                    $.post('<?php echo admin_url('prchat/Prchat_Controller/mark_messages_as_read'); ?>', {
                        staff_id: staffId,
                        type: 'staff'
                    });
                    if (window.toggledChatApp && typeof window.toggledChatApp.markMessagesAsRead === 'function') {
                        window.toggledChatApp.markMessagesAsRead(staffId);
                    }
                });
                if (typeof clearTabBadge === 'function') clearTabBadge('staff');
            } else if (type === 'client') {
                contactIds.forEach(function (clientId) {
                    $('.chat_clients_list .contact_name#' + clientId + ' .unread-notifications').remove();
                    $.post('<?php echo admin_url('prchat/Prchat_Controller/mark_messages_as_read'); ?>', {
                        contact_id: clientId,
                        type: 'client'
                    });
                });
                if (typeof clearTabBadge === 'function') clearTabBadge('crm_clients');
            }
        }

        // ── Load unread from server API (respects muted staff/clients) ─────
        var getPinMuteSettingsUrl = '<?= admin_url("prchat/Prchat_Controller/getPinMuteSettings"); ?>';

        function loadUnreadMessages() {
            $.ajax({
                url: getPinMuteSettingsUrl,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (settings) {
                    var mutedStaff = [];
                    var mutedClients = [];
                    if (settings) {
                        try {
                            var s = typeof settings === 'string' ? JSON.parse(settings) : settings;
                            mutedStaff = (s.muted_staff || []).map(String);
                            mutedClients = (s.muted_clients || []).map(String);
                        } catch (e) { }
                    }
                    $.ajax({
                        url: '<?= admin_url("prchat/Prchat_Controller/getUnreadCounts"); ?>',
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            var unreadLabel = '<?= _l("chat_unread_messages"); ?>';
                            var placeholder = site_url + 'assets/images/user-placeholder.jpg';

                            // On full chat view, don't add notification for the conversation currently open
                            var activeStaffId = '';
                            var activeClientId = '';
                            if (document.getElementById('frame')) {
                                activeStaffId = ($("#frame #contacts li.contact.active").attr("id") || $("#frame #contacts a.active_chat").attr("id") || '').replace(/^id_/, '');
                                activeClientId = $("#frame .chat_clients_list li.contact_name.selected").attr("id") || '';
                            }

                            if (data.staff && data.staff.length) {
                                data.staff.forEach(function (s) {
                                    if (mutedStaff.indexOf(String(s.id)) !== -1) return;
                                    if (nid(s.id) === nid(activeStaffId)) return;
                                    addNotification({
                                        from: s.id,
                                        fromName: s.name,
                                        type: 'staff',
                                        message: unreadLabel.replace('%s', s.count),
                                        avatar: s.avatar || placeholder,
                                        messageCount: s.count
                                    }, true);
                                });
                            }

                            if (data.clients && data.clients.length) {
                                data.clients.forEach(function (c) {
                                    var cid = String((c.id || '').replace(/^client_/, ''));
                                    if (mutedClients.indexOf(cid) !== -1) return;
                                    if (nid(c.id) === nid(activeClientId)) return;
                                    addNotification({
                                        from: c.id,
                                        fromName: c.name,
                                        type: 'client',
                                        message: unreadLabel.replace('%s', c.count),
                                        avatar: c.avatar || placeholder,
                                        messageCount: c.count
                                    }, true);
                                });
                            }
                        },
                        error: function () { }
                    });
                }
            });
        }

        // ── Go to chat ────────────────────────────────────────────
        window.goToChat = function (fromId, type) {
            fromId = nid(fromId);
            var isFullChatView = document.getElementById('frame') !== null;

            if (isFullChatView) {
                if (type === 'staff') {
                    if (_goToChatTimeouts.staff) clearTimeout(_goToChatTimeouts.staff);
                    var params = new URLSearchParams(window.location.search);
                    params.set('tab', 'staff');
                    params.set('contact', fromId);
                    if (history.replaceState) history.replaceState(null, '', window.location.pathname + '?' + params.toString());
                    $("#frame #sidepanel .staff a").click();
                    var escapedId = String(fromId).replace(/'/g, "\\'");
                    _goToChatTimeouts.staff = setTimeout(function () {
                        _goToChatTimeouts.staff = null;
                        var contactEl = $("#frame #contacts ul li.contact[id='" + escapedId + "'] a");
                        if (contactEl.length) contactEl.first().trigger('click');
                    }, 2000);
                } else if (type === 'client') {
                    if (_goToChatTimeouts.clientPoll) clearTimeout(_goToChatTimeouts.clientPoll);
                    if (_goToChatTimeouts.clientClearFlag) clearTimeout(_goToChatTimeouts.clientClearFlag);
                    _goToChatTimeouts.clientPoll = null;
                    _goToChatTimeouts.clientClearFlag = null;

                    window.__prchatOpenClientFromNotification = fromId;
                    $("#frame #sidepanel .crm_clients a").click();
                    var contactId = fromId;
                    var attempts = 0;
                    var maxAttempts = 25;

                    function tryClickContact() {
                        var $el = $(".chat_clients_list > li.contact_name#" + contactId);
                        if ($el.length) {
                            _goToChatTimeouts.clientPoll = null;
                            if ($el.hasClass("selected")) {
                                $(".chat_clients_list > li.contact_name.selected").removeClass("selected");
                            }
                            $el.trigger("click");
                            if (_goToChatTimeouts.clientClearFlag) clearTimeout(_goToChatTimeouts.clientClearFlag);
                            _goToChatTimeouts.clientClearFlag = setTimeout(function () {
                                _goToChatTimeouts.clientClearFlag = null;
                                window.__prchatOpenClientFromNotification = null;
                            }, 1500);
                            return;
                        }
                        attempts++;
                        if (attempts < maxAttempts) {
                            _goToChatTimeouts.clientPoll = setTimeout(tryClickContact, 100);
                        } else {
                            _goToChatTimeouts.clientPoll = null;
                            window.__prchatOpenClientFromNotification = null;
                        }
                    }
                    _goToChatTimeouts.clientPoll = setTimeout(tryClickContact, 150);
                }
            } else {
                if (type === 'staff' && typeof window.openChatWithStaff === 'function') {
                    window.openChatWithStaff(fromId);
                } else {
                    var tab = type === 'client' ? 'crm_clients' : 'staff';
                    // Remove before navigating
                    removeNotification(fromId);
                    window.location.href = site_url + 'admin/prchat/Prchat_Controller/chat_full_view?tab=' + tab + '&contact=' + encodeURIComponent(fromId);
                    return;
                }
            }
            removeNotification(fromId);
        };

        // ── Utilities ─────────────────────────────────────────────
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function timeAgo(date) {
            return moment(date).fromNow();
        }

        // ── Click handlers for sidebar contacts ───────────────────
        function setupClickHandlers() {
            $(document).on('click', '#contacts .contact a', function () {
                var id = $(this).attr('id');
                if (id) removeNotification(nid(id));
            });
            $(document).on('click', '.chat_clients_list .contact_name', function () {
                var id = $(this).attr('id');
                if (id) removeNotification(nid(id));
            });
            $(document).on('focus click', '#frame textarea.chatbox, #frame div.messages', function () {
                var activeId = $('#sidepanel li.active a.active_chat').attr('id');
                if (!activeId) {
                    activeId = $('.chat_clients_list > li.contact_name.selected').attr('id');
                }
                if (activeId) removeNotification(nid(activeId));
            });
        }

        // ── Delegated handler for notification action buttons (reply, mark read, remove) ───────────────────
        $(document).on('click', '.notif-action-btn[data-action]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
            var action = btn.attr('data-action');
            var item = btn.closest('.notification-item');
            if (!item.length) return;
            var fromId = item.attr('data-from');
            var type = item.attr('data-type');
            if (action === 'reply' && fromId && type) {
                if (typeof goToChat === 'function') goToChat(fromId, type);
            } else if (action === 'mark-read' && typeof window.FloatingChatNotifications !== 'undefined') {
                window.FloatingChatNotifications.markRead(fromId);
            } else if (action === 'remove' && typeof window.FloatingChatNotifications !== 'undefined') {
                window.FloatingChatNotifications.removeOne(fromId);
            }
        });

        // ── Public API ────────────────────────────────────────────
        window.FloatingChatNotifications = {
            add: addNotification,
            show: function () {
                showWidget(true);
            },
            hide: hideWidget,
            clear: clearAll,
            loadUnread: loadUnreadMessages,
            markAllRead: markAllRead,
            markByType: markNotificationsByType,
            markRead: markOneRead,
            removeOne: function (id) {
                removeNotification(id);
            },
            expand: expandWidget,
            minimize: function () {
                if (!isMinimized) toggleMinimize();
            },
            getCount: getTotalUnreadCount,
            isVisible: function () {
                return isVisible;
            }
        };

        // ── Boot ──────────────────────────────────────────────────
        $(document).ready(function () {
            // Clear any stale localStorage notification data from old versions
            localStorage.removeItem('chatFloatingNotifications');
            localStorage.removeItem('chatFloatingNotifications_visible');

            initWidget();
            setupClickHandlers();

            // Load actual unread counts from DOM badges (only finds badges on full chat view)
            setTimeout(loadUnreadMessages, 1000);
        });

    })();
</script>


<style>
    /* Floating Chat Notification Widget Styles */
    .floating-chat-widget {
        position: fixed;
        top: 100px;
        right: 20px;
        width: 320px;
        max-height: 400px;
        background: #ffffff;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        user-select: none;
        opacity: 0.95;
        transition: all 0.3s ease;
    }

    .floating-chat-widget:hover {
        opacity: 1;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .floating-chat-widget.minimized {
        height: 50px;
        overflow: visible;
    }

    .floating-chat-widget.minimized .widget-body {
        display: none;
    }

    .widget-header {
        background: linear-gradient(135deg, #017cff 0%, #354dc1 100%);
        color: white;
        padding: 12px 15px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: move;
    }

    .widget-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
    }

    .notification-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .widget-controls {
        display: flex;
        gap: 5px;
    }

    .widget-controls > i{
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
        transition: color 0.2s ease;
    }

    .widget-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 5px 8px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        transition: background 0.2s ease;
    }

    .widget-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .widget-body {
        max-height: 350px;
        overflow-y: auto;
        padding: 0;
    }

    .notification-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0;
        position: relative;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
        pointer-events: auto;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-main {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
        cursor: pointer;
        padding: 4px 0;
    }

    .notification-actions {
        display: flex;
        gap: 2px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s ease;
        flex-shrink: 0;
        margin-left: 4px;
    }

    .notif-action-btn {
        background: none;
        border: 1px solid #e0e0e0;
        color: #888;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        padding: 0;
    }

    .notif-action-btn:hover {
        background: #e3f2fd;
        color: #1976d2;
        border-color: #90caf9;
    }

    .notif-action-btn.notif-action-remove:hover {
        background: #ffebee;
        color: #c62828;
        border-color: #ef9a9a;
    }

    .notification-avatar-wrapper {
        position: relative;
        flex-shrink: 0;
    }

    .notification-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e1e5e9;
    }

    .notification-msg-count {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        background: #e53935;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        line-height: 1;
        border: 1.5px solid #fff;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-sender {
        font-weight: 600;
        font-size: 13px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 2px;
    }

    .sender-type {
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 500;
    }

    .sender-type.staff {
        background: #e3f2fd;
        color: #1976d2;
    }

    .sender-type.client {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .notification-message {
        font-size: 12px;
        color: #666;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .notification-time {
        font-size: 11px;
        color: #999;
    }

    .no-notifications {
        text-align: center;
        padding: 30px 20px;
        color: #999;
    }

    .no-notifications i {
        font-size: 24px;
        margin-bottom: 8px;
        display: block;
    }

    .no-notifications p {
        margin: 0;
        font-size: 13px;
    }

    /* Mobile Responsive */
    @media screen and (max-width: 768px) {
        .floating-chat-widget {
            width: 280px;
            right: 10px;
            top: 80px;
        }
    }

    /* Animation for new notifications */
    @keyframes notificationPulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .notification-item.new {
        animation: notificationPulse 0.5s ease;
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    /* CSS-only tooltips for floating widget */
    .floating-chat-widget [data-tip] {
        position: relative;
    }

    .floating-chat-widget [data-tip]::after {
        content: attr(data-tip);
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 8px;
        background: #1e293b;
        color: #fff;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
        border-radius: 4px;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s ease;
        z-index: 99999;
        line-height: 1.3;
        /* Default: above the element */
        bottom: calc(100% + 6px);
    }

    .floating-chat-widget [data-tip]:hover::after {
        opacity: 1;
    }

    /* Notification action buttons: tooltip above */
    .notif-action-btn[data-tip]::after {
        bottom: calc(100% + 6px);
    }

    /* ===== DARK MODE ===== */
    body.dark-mode .floating-chat-widget {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    body.dark-mode .floating-chat-widget:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
    }

    body.dark-mode .widget-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    body.dark-mode .widget-body {
        background: #1e293b;
    }

    body.dark-mode .notification-item {
        border-bottom-color: #334155;
    }

    body.dark-mode .notification-item:hover {
        background: #334155;
    }

    body.dark-mode .notification-item.new {
        background: #1e3a5f;
        border-left-color: #3b82f6;
    }

    body.dark-mode .notification-sender {
        color: #e2e8f0;
    }

    body.dark-mode .notification-message {
        color: #94a3b8;
    }

    body.dark-mode .notification-time {
        color: #64748b;
    }

    body.dark-mode .notification-avatar {
        border-color: #475569;
    }

    body.dark-mode .notification-msg-count {
        border-color: #1e293b;
    }

    body.dark-mode .sender-type.staff {
        background: #1e3a5f;
        color: #60a5fa;
    }

    body.dark-mode .sender-type.client {
        background: #2d1b4e;
        color: #c084fc;
    }

    body.dark-mode .no-notifications {
        color: #64748b;
    }

    body.dark-mode .notif-action-btn {
        border-color: #475569;
        color: #94a3b8;
    }

    body.dark-mode .notif-action-btn:hover {
        background: #1e3a5f;
        color: #60a5fa;
        border-color: #3b82f6;
    }

    body.dark-mode .notif-action-btn.notif-action-remove:hover {
        background: #451a1a;
        color: #f87171;
        border-color: #ef4444;
    }

    body.dark-mode .widget-body::-webkit-scrollbar-track {
        background: #1e293b;
    }

    body.dark-mode .widget-body::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 4px;
    }

    body.dark-mode .widget-body::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
</style>

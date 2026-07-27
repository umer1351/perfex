<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // Minimal Pusher presence subscription (for when toggled chat is disabled)
    // This ensures staff maintain online/offline status across all pages
    (function() {
        'use strict';

        // Only initialize if Pusher is available and not already initialized
        if (typeof Pusher === 'undefined') {
            console.error('[Chat Presence] Pusher not loaded');
            return;
        }

        // Check if pusher is already initialized (by chat_full_view or other scripts)
        if (typeof window.pusher !== 'undefined') {
            console.log('[Chat Presence] Pusher already initialized, skipping');
            return;
        }

        var pusherKey = '<?= get_option("pusher_app_key"); ?>';
        var pusherCluster = '<?= get_option("pusher_cluster"); ?>';
        var currentUserId = '<?= get_staff_user_id(); ?>';

        if (!pusherKey || !pusherCluster || !currentUserId) {
            console.error('[Chat Presence] Missing required configuration');
            return;
        }

        // Initialize Pusher
        var _presAuthHeaders = { 'X-Requested-With': 'XMLHttpRequest' };
        if (window.PRCHAT_CSRF && window.PRCHAT_CSRF.name) {
            _presAuthHeaders[window.PRCHAT_CSRF.name] = window.PRCHAT_CSRF.hash;
        }
        window.pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            authEndpoint: '<?= admin_url("prchat/Prchat_Controller/pusher_auth"); ?>',
            authTransport: 'ajax',
            auth: { headers: _presAuthHeaders },
            disableStats: true
        });

        // Subscribe to same presence channel as full/toggled chat (staff online status)
        var presenceChannel = window.pusher.subscribe('presence-mychanel');

        presenceChannel.bind('pusher:subscription_succeeded', function() {});

        presenceChannel.bind('pusher:subscription_error', function() {});

        // Load mute settings so we don't show floating notifications for muted staff/clients
        var presenceMutedStaff = [];
        var presenceMutedClients = [];
        (function() {
            var req = new XMLHttpRequest();
            req.open('GET', '<?= admin_url("prchat/Prchat_Controller/getPinMuteSettings"); ?>', true);
            req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            req.onreadystatechange = function() {
                if (req.readyState === 4 && req.responseText) {
                    try {
                        var s = JSON.parse(req.responseText);
                        presenceMutedStaff = (s.muted_staff || []).map(String);
                        presenceMutedClients = (s.muted_clients || []).map(String);
                    } catch (e) {}
                }
            };
            req.send();
        })();

        // Staff-to-staff messages: show floating notification when not on conversations page (skip if sender muted)
        presenceChannel.bind('send-event', function(data) {
            if (!data || String(data.to) !== String(currentUserId) || String(data.from) === String(currentUserId)) return;
            if (presenceMutedStaff.indexOf(String(data.from)) !== -1) return;
            if (typeof FloatingChatNotifications === 'undefined') return;
            var siteUrl = (typeof site_url !== 'undefined') ? site_url : '<?= base_url(); ?>';
            var avatar = (data.sender_image) ? siteUrl + 'uploads/staff_profile_images/' + data.from + '/thumb_' + data.sender_image : siteUrl + 'assets/images/user-placeholder.jpg';
            var _rawMsg = data.message || '';
            var _callM = _rawMsg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):\d+:\d+\]$/);
            var presNotifMsg;
            if (_callM) {
                if (_callM[1] === 'missed_voice' || _callM[1] === 'missed_video') {
                    presNotifMsg = '\uD83D\uDCF5 Missed call';
                } else {
                    presNotifMsg = ((_callM[1] === 'video' ? '\uD83D\uDCF9' : '\uD83D\uDCDE') + ' ' + (_callM[1] === 'video' ? 'Video call' : 'Voice call') + ' \u2022 ' + (Math.floor(parseInt(_callM[2]) / 60) > 0 ? Math.floor(parseInt(_callM[2]) / 60) + 'm ' : '') + (parseInt(_callM[2]) % 60) + 's');
                }
            } else {
                presNotifMsg = (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.cleanNotificationText) ? PrchatSafeRenderer.cleanNotificationText(_rawMsg) : _rawMsg.replace(/\[REPLY:[^\]]*\]\s*/, '').replace(/<[^>]*>/g, '').substring(0, 50) + (_rawMsg.length > 50 ? '...' : '');
            }
            FloatingChatNotifications.add({
                from: data.from,
                fromName: data.from_name || '<?= _l("chat_staff_member_fallback"); ?>',
                type: 'staff',
                message: presNotifMsg,
                avatar: avatar
            });
        });

        // Subscribe to clients channel for notifications (if clients enabled and staff has access)
        <?php if (isClientsEnabled() && staffCanAccessClientsTab()) : ?>
            var clientsChannel = window.pusher.subscribe('presence-clients');

            clientsChannel.bind('send-event', function(data) {
                // Client-to-staff message
                if (data.to !== 'staff_' + currentUserId) return;
                var clientContactId = String((data.from || '').replace(/^client_/, ''));
                if (presenceMutedClients.indexOf(clientContactId) !== -1) return;

                // Show floating notification
                if (typeof FloatingChatNotifications !== 'undefined') {
                    var msg = (data.message != null && data.message !== undefined) ? String(data.message) : '';
                    var _cm = msg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):\d+:\d+\]$/);
                    if (_cm) {
                        if (_cm[1] === 'missed_voice' || _cm[1] === 'missed_video') {
                            msg = '\uD83D\uDCF5 Missed call';
                        } else {
                            msg = (_cm[1] === 'video' ? '\uD83D\uDCF9' : '\uD83D\uDCDE') + ' ' + (_cm[1] === 'video' ? 'Video call' : 'Voice call') + ' \u2022 ' + (Math.floor(parseInt(_cm[2]) / 60) > 0 ? Math.floor(parseInt(_cm[2]) / 60) + 'm ' : '') + (parseInt(_cm[2]) % 60) + 's';
                        }
                    } else {
                        msg = (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.cleanNotificationText) ? PrchatSafeRenderer.cleanNotificationText(msg) : msg.replace(/\[REPLY:[^\]]*\]\s*/, '').replace(/<[^>]*>/g, '').substring(0, 50);
                        if (msg.length >= 50) msg += '...';
                    }
                    FloatingChatNotifications.add({
                        from: data.from,
                        fromName: data.contact_full_name || '<?= _l("chat_client"); ?>',
                        type: 'client',
                        message: msg,
                        avatar: data.client_image_path || '<?= base_url("assets/images/user-placeholder.jpg"); ?>'
                    });
                }
            });
        <?php endif; ?>
    })();
</script>

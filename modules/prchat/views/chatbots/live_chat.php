<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<link rel="stylesheet" href="<?= module_dir_url('prchat', 'assets/css/chatbot-live-chat.css'); ?>?v=<?= filemtime(module_dir_path('prchat', 'assets/css/chatbot-live-chat.css')); ?>">

<div id="wrapper">
    <div class="chatbot-live-chat" style="position:relative;">
        <div id="cb-connection-status" style="display:none;position:absolute;top:0;left:0;right:0;z-index:999;background:#f39c12;color:#fff;text-align:center;padding:6px 10px;font-size:12px;font-weight:600;"></div>

        <!-- ===================== SIDEBAR ===================== -->
        <div class="cb-sidebar">
            <div class="cb-sidebar-header">
                <h4>
                    <span><span class="cb-live-dot"></span> <?= _l('chatbot_live_chat_support') ?></span>
                    <div class="cb-notification-controls">
                        <button class="cb-notif-toggle" id="cb-theme-toggle" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chat_dark_mode') ?>" aria-label="<?= _l('chat_dark_mode'); ?>">
                            <i class="fa fa-moon" id="cb-theme-icon"></i>
                        </button>
                        <button class="cb-notif-toggle active" id="cb-sound-toggle" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chat_sound_notifications') ?>" aria-label="<?= _l('chat_toggle_sound'); ?>">
                            <i class="fa fa-volume-up" id="cb-sound-icon"></i>
                        </button>
                        <button class="cb-notif-toggle <?= get_staff_meta(get_staff_user_id(), 'chatbot_desktop_notifications_enabled') != '0' ? 'active' : '' ?>" id="cb-desktop-toggle" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chat_desktop_notifications') ?>" aria-label="<?= _l('chat_toggle_desktop_notifications'); ?>">
                            <i class="fa fa-desktop" id="cb-desktop-icon"></i>
                        </button>
                    </div>
                </h4>
                <div class="cb-search-wrap">
                    <i class="fa fa-search"></i>
                    <input type="text" id="cb-search-input" placeholder="<?= _l('chatbot_search_conversations') ?>" aria-label="<?= _l('chatbot_search_conversations') ?>">
                </div>
            </div>

            <div class="cb-filter-bar">
                <div class="cb-filter-row cb-filter-pills">
                    <div class="cb-filter-pill-wrap" data-filter="status">
                        <button type="button" class="cb-filter-pill" id="cb-filter-status-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="cb-filter-pill-text"><?= _l('chatbot_filter_all') ?></span><span class="cb-filter-pill-chevron"></span></button>
                        <div class="cb-filter-dropdown cb-filter-pill-dd" id="cb-filter-status-dropdown" role="listbox">
                            <div class="cb-filter-option" data-value="all"><?= _l('chatbot_filter_all') ?></div>
                            <div class="cb-filter-option" data-value="open"><?= _l('chatbot_filter_open') ?></div>
                            <div class="cb-filter-option" data-value="escalated"><?= _l('chatbot_filter_escalated') ?></div>
                            <div class="cb-filter-option" data-value="closed"><?= _l('chatbot_filter_closed') ?></div>
                        </div>
                    </div>
                    <div class="cb-filter-pill-wrap" data-filter="effort">
                        <button type="button" class="cb-filter-pill" id="cb-filter-effort-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="cb-filter-pill-text"><?= _l('chatbot_effort') ?></span><span class="cb-filter-pill-chevron"></span></button>
                        <div class="cb-filter-dropdown cb-filter-pill-dd cb-filter-effort-dropdown" id="cb-filter-effort-dropdown" role="listbox">
                            <div class="cb-filter-section" data-filter="assignee">
                                <div class="cb-filter-section-title"><?= _l('chatbot_filter_assignee') ?></div>
                                <div class="cb-filter-options">
                                    <div class="cb-filter-option" data-value="all"><?= _l('chatbot_filter_anyone') ?></div>
                                    <div class="cb-filter-option" data-value="mine"><?= _l('chatbot_filter_mine') ?></div>
                                    <div class="cb-filter-option" data-value="unassigned"><?= _l('chatbot_filter_unassigned') ?></div>
                                </div>
                            </div>
                            <div class="cb-filter-section" data-filter="priority">
                                <div class="cb-filter-section-title"><?= _l('chatbot_filter_priority') ?></div>
                                <div class="cb-filter-options">
                                    <div class="cb-filter-option" data-value="all"><?= _l('chatbot_filter_priority_all') ?></div>
                                    <div class="cb-filter-option" data-value="high"><?= _l('chatbot_priority_high') ?></div>
                                    <div class="cb-filter-option" data-value="medium"><?= _l('chatbot_priority_medium') ?></div>
                                    <div class="cb-filter-option" data-value="low"><?= _l('chatbot_priority_low') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cb-filter-pill-wrap" data-filter="sort">
                        <button type="button" class="cb-filter-pill" id="cb-filter-sort-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="cb-filter-pill-text"><?= _l('chatbot_sort_newest') ?></span><span class="cb-filter-pill-chevron"></span></button>
                        <div class="cb-filter-dropdown cb-filter-pill-dd" id="cb-filter-sort-dropdown" role="listbox">
                            <div class="cb-filter-option" data-value="newest"><?= _l('chatbot_sort_newest') ?></div>
                            <div class="cb-filter-option" data-value="oldest"><?= _l('chatbot_sort_oldest') ?></div>
                            <div class="cb-filter-option" data-value="unread"><?= _l('chatbot_sort_unread') ?></div>
                        </div>
                    </div>
                    <div class="cb-filter-pill-wrap" data-filter="tag">
                        <button type="button" class="cb-filter-pill" id="cb-filter-tag-trigger" aria-haspopup="listbox" aria-expanded="false"><span class="cb-filter-pill-text"><?= _l('chatbot_filter_tag_all') ?></span><span class="cb-filter-pill-chevron"></span></button>
                        <div class="cb-filter-dropdown cb-filter-pill-dd cb-filter-tag-dropdown" id="cb-filter-tag-dropdown" role="listbox">
                            <div class="cb-filter-option" data-value=""><?= _l('chatbot_filter_tag_all') ?></div>
                            <div id="cb-filter-tag-options"></div>
                        </div>
                    </div>
                    <div class="cb-filter-counts">
                        <span class="cb-filter-count" id="cb-count-all">0</span> <?= _l('chatbot_filter_total') ?>
                        <span class="cb-filter-sep">&bull;</span>
                        <span class="cb-filter-count has-items" id="cb-count-escalated">0</span> <?= _l('chatbot_filter_escalated') ?>
                    </div>
                </div>
            </div>

            <!-- Context menu -->
            <div class="cb-context-menu" id="cb-context-menu">
                <div class="cb-ctx-item" data-action="assign"><i class="fa fa-user-plus"></i> <?= _l('chatbot_take_over') ?></div>
                <div class="cb-ctx-item" data-action="transfer"><i class="fa fa-exchange"></i> <?= _l('chatbot_transfer_to_staff') ?></div>
                <div class="cb-ctx-divider" id="cb-ctx-divider-top"></div>
                <div class="cb-ctx-item cb-ctx-has-sub" data-action="priority">
                    <i class="fa fa-flag"></i> <?= _l('chatbot_priority') ?>
                    <i class="fa fa-caret-right cb-ctx-arrow"></i>
                    <div class="cb-ctx-submenu">
                        <div class="cb-ctx-item" data-priority="low"><i class="fa fa-flag" style="color:#22c55e"></i> <?= _l('chatbot_priority_low') ?></div>
                        <div class="cb-ctx-item" data-priority="medium"><i class="fa fa-flag" style="color:#f59e0b"></i> <?= _l('chatbot_priority_medium') ?></div>
                        <div class="cb-ctx-item" data-priority="high"><i class="fa fa-flag" style="color:#ef4444"></i> <?= _l('chatbot_priority_high') ?></div>
                        <div class="cb-ctx-item" data-priority=""><i class="fa fa-flag" style="color:#9ca3af"></i> <?= _l('chatbot_priority_none') ?></div>
                    </div>
                </div>
                <div class="cb-ctx-item cb-ctx-has-sub" data-action="tags">
                    <i class="fa fa-tags"></i> <?= _l('chatbot_manage_tags') ?>
                    <i class="fa fa-caret-right cb-ctx-arrow"></i>
                    <div class="cb-ctx-submenu" id="cb-ctx-tags-submenu"></div>
                </div>
                <div class="cb-ctx-item cb-ctx-has-sub" data-action="export">
                    <i class="fa fa-download"></i> <?= _l('chatbot_export_conversation') ?>
                    <i class="fa fa-caret-right cb-ctx-arrow"></i>
                    <div class="cb-ctx-submenu">
                        <div class="cb-ctx-item" data-action="export-csv"><i class="fa fa-file-text"></i> CSV</div>
                        <div class="cb-ctx-item" data-action="export-pdf"><i class="fa fa-print"></i> Print / PDF</div>
                    </div>
                </div>
                <?php if ($can_delete): ?>
                    <div class="cb-ctx-divider" id="cb-ctx-divider-danger"></div>
                    <div class="cb-ctx-item" data-action="end"><i class="fa fa-stop-circle"></i> <?= _l('chatbot_end_conversation') ?></div>
                    <div class="cb-ctx-item cb-ctx-danger" data-action="delete"><i class="fa fa-trash"></i> <?= _l('chatbot_delete') ?></div>
                <?php endif; ?>
            </div>

            <!-- Transfer to staff modal -->
            <div class="cb-modal-overlay" id="cb-transfer-modal" style="display:none">
                <div class="cb-modal-box">
                    <div class="cb-modal-header">
                        <h5><?= _l('chatbot_transfer_to_staff') ?></h5>
                        <button type="button" class="cb-modal-close" id="cb-transfer-close">&times;</button>
                    </div>
                    <div class="cb-modal-body">
                        <select class="selectpicker ajax-search" id="cb-transfer-staff-select" data-live-search="true" data-width="100%" data-none-selected-text="<?= _l('chatbot_select_staff') ?>">
                        </select>
                    </div>
                    <div class="cb-modal-footer">
                        <button type="button" class="btn btn-default btn-sm" id="cb-transfer-cancel"><?= _l('chatbot_cancel') ?></button>
                        <button type="button" class="btn btn-primary btn-sm" id="cb-transfer-confirm"><?= _l('chatbot_confirm_transfer') ?></button>
                    </div>
                </div>
            </div>

            <div class="cb-conv-list" id="cb-conv-list" role="listbox" aria-label="<?= _l('chat_conversations'); ?>">
                <div class="cb-loading-state" id="cb-loading-state">
                    <div class="cb-loading-spinner"></div>
                    <p><?= _l('chatbot_loading') ?></p>
                </div>
            </div>
        </div>

        <!-- ===================== MAIN CHAT AREA ===================== -->
        <div class="cb-main">

            <!-- Empty State -->
            <div class="cb-empty-state" id="cb-empty-state">
                <i class="fa fa-comments"></i>
                <h5><?= _l('chatbot_select_conversation') ?></h5>
                <p><?= _l('chatbot_select_conversation_desc') ?></p>
            </div>

            <!-- Chat Container (shown when conversation selected) -->
            <div class="cb-chat-container" id="cb-chat-container">
                <!-- Header -->
                <div class="cb-chat-header">
                    <button type="button" class="cb-mobile-back-btn" onclick="mobileBack()" aria-label="Back to conversations">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                        </svg>
                    </button>
                    <div class="cb-header-avatar" id="cb-header-avatar" aria-hidden="true">V</div>
                    <div class="cb-header-info">
                        <div class="cb-header-name" id="cb-header-name"></div>
                        <div class="cb-header-meta" id="cb-header-meta"></div>
                    </div>
                    <div class="cb-chat-actions" id="cb-chat-actions"></div>
                    <button type="button" class="cb-header-btn cb-search-in-conv-btn" id="cb-search-in-conv-btn" aria-label="<?= _l('chatbot_search_in_conversation') ?>" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chatbot_search_in_conversation') ?>">
                        <i class="fa fa-search"></i>
                    </button>
                    <button type="button" class="cb-toggle-info-panel" id="cb-toggle-info-panel" aria-label="<?= _l('chatbot_toggle_info_panel') ?>" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chatbot_toggle_info_panel') ?>">
                        <i class="fa fa-chevron-right" id="cb-toggle-icon"></i>
                    </button>
                </div>

                <!-- Search in conversation -->
                <div class="cb-conv-search-wrap" id="cb-conv-search-wrap" style="display:none">
                    <input type="text" class="cb-conv-search-input" id="cb-conv-search-input" placeholder="<?= _l('chatbot_search_in_conversation') ?>" aria-label="<?= _l('chatbot_search_in_conversation') ?>">
                    <span class="cb-conv-search-count" id="cb-conv-search-count"></span>
                    <button type="button" class="cb-conv-search-prev" id="cb-conv-search-prev" data-toggle="tooltip" title="<?= _l('chatbot_search_prev') ?>" aria-label="<?= _l('chatbot_search_prev') ?>"><i class="fa fa-chevron-up"></i></button>
                    <button type="button" class="cb-conv-search-next" id="cb-conv-search-next" data-toggle="tooltip" title="<?= _l('chatbot_search_next') ?>" aria-label="<?= _l('chatbot_search_next') ?>"><i class="fa fa-chevron-down"></i></button>
                    <button type="button" class="cb-conv-search-clear" id="cb-conv-search-clear" data-toggle="tooltip" title="<?= _l('chatbot_search_clear') ?>" aria-label="<?= _l('chatbot_search_clear') ?>"><i class="fa fa-times"></i></button>
                </div>
                <!-- Messages (scroll button is inside so it stays bottom-right of this area) -->
                <div class="cb-messages-wrap">
                    <div class="cb-messages" id="cb-messages" role="log" aria-live="polite" aria-label="<?= _l('chat_messages_log'); ?>">
                    </div>
                    <div class="cb-scroll-bottom" id="cb-scroll-bottom" data-toggle="tooltip" data-placement="top" title="<?= _l('chat_scroll_to_bottom'); ?>" aria-label="<?= _l('chat_scroll_to_latest'); ?>">
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <!-- Typing indicator (positioned inside messages container) -->
                    <div class="cb-typing-indicator" id="cb-typing-indicator" aria-live="polite">
                        <div class="cb-typing-dots"><span></span><span></span><span></span></div>
                        <span class="cb-typing-text" id="cb-typing-text"><?= _l('chat_visitor_typing'); ?></span>
                    </div>
                </div>

                <!-- Input -->
                <div class="cb-input-area">
                    <div class="cb-canned-dropdown" id="cb-canned-dropdown" role="listbox"></div>
                    <div class="cb-input-wrapper" id="cb-input-wrapper">
                        <button type="button" class="cb-attach-btn" id="cb-attach-btn" title="Attach file" disabled>
                            <i class="fa fa-paperclip"></i>
                        </button>
                        <input type="file" id="cb-file-input" style="display:none" />
                        <textarea id="cb-message-input"
                            placeholder="<?= _l('chatbot_type_reply') ?>"
                            rows="1"
                            aria-label="<?= _l('chatbot_type_reply') ?>"
                            disabled></textarea>
                        <button class="cb-send-btn" id="cb-send-btn" aria-label="<?= _l('chatbot_send') ?>" disabled>
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="cb-input-disabled" id="cb-input-disabled">
                        <?= _l('chatbot_take_over_prompt') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== INFO PANEL ===================== -->
        <div class="cb-info-panel cb-info-panel-hidden" id="cb-info-panel">
            <div class="cb-info-panel-header">
                <button type="button" class="cb-mobile-back-btn" onclick="mobileBack()" aria-label="Back to chat">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                    </svg>
                </button>
                <span class="cb-info-panel-header-title"><?= _l('chatbot_conversation_details') ?? 'Details' ?></span>
            </div>
            <div class="cb-panel-empty" id="cb-panel-empty">
                <i class="fa fa-info-circle"></i>
                <p><?= _l('chatbot_select_for_details') ?></p>
            </div>
            <div class="cb-panel-content" id="cb-panel-content">
                <!-- Contact Info -->
                <div class="cb-panel-section">
                    <div class="cb-panel-section-title"><i class="fa fa-user"></i> <?= _l('chatbot_contact') ?></div>
                    <div id="cb-contact-info"></div>
                </div>
                <!-- Session Info -->
                <div class="cb-panel-section">
                    <div class="cb-panel-section-title"><i class="fa fa-globe"></i> <?= _l('chatbot_session') ?></div>
                    <div id="cb-session-info"></div>
                </div>
                <!-- CRM Actions -->
                <div class="cb-panel-section">
                    <div class="cb-panel-section-title"><i class="fa fa-briefcase"></i> CRM</div>
                    <div id="cb-crm-actions" class="cb-crm-actions"></div>
                </div>
                <!-- CSAT Rating -->
                <div class="cb-panel-section" id="cb-csat-section" style="display: none;">
                    <div class="cb-panel-section-title"><i class="fa fa-star"></i> <?= _l('chatbot_csat_rating') ?></div>
                    <div id="cb-csat-info"></div>
                </div>
                <!-- Internal Notes -->
                <div class="cb-panel-section">
                    <div class="cb-panel-section-title"><i class="fa fa-sticky-note"></i> <?= _l('chatbot_internal_notes') ?></div>
                    <div id="cb-notes-list"></div>
                    <div class="cb-note-input-wrap">
                        <textarea id="cb-note-input" placeholder="<?= _l('chatbot_add_note_placeholder') ?>" rows="1"></textarea>
                        <button id="cb-add-note-btn"><?= _l('chatbot_add') ?></button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php init_tail(); ?>

<script>
    function mobileBack() {
        var container = document.querySelector('.chatbot-live-chat');
        if (!container) return;
        if (container.classList.contains('cb-mobile-info-active')) {
            container.classList.remove('cb-mobile-info-active');
            container.classList.add('cb-mobile-chat-active');
        } else {
            container.classList.remove('cb-mobile-chat-active');
        }
    }

    (function() {
        'use strict';

        // ===========================================
        // CONFIGURATION
        // ===========================================
        const CONFIG = {
            staffId: <?= (int) get_staff_user_id() ?>,
            staffName: <?= json_encode(get_staff_full_name()) ?>,
            staffImage: <?= json_encode(staff_profile_image_url(get_staff_user_id(), 'small')) ?>,
            csrfName: <?= json_encode($this->security->get_csrf_token_name()) ?>,
            csrfHash: <?= json_encode($this->security->get_csrf_hash()) ?>,
            baseUrl: <?= json_encode(admin_url('prchat/Chatbot_Admin/')) ?>,
            pusherKey: <?= json_encode(get_option('pusher_app_key')) ?>,
            pusherCluster: <?= json_encode(get_option('pusher_cluster')) ?>,
            pusherEnabled: <?= get_option('pusher_chat_enabled') == '1' ? 'true' : 'false' ?>,
            refreshInterval: 30000,
            canDelete: <?= $can_delete ? 'true' : 'false' ?>,
            i18n: {
                visitor: <?= json_encode(_l('chatbot_visitor_label')) ?>,
                visitorHash: <?= json_encode(_l('chatbot_visitor_hash')) ?>,
                staff: <?= json_encode(_l('chat_staff')) ?>,
                staffLabel: <?= json_encode(_l('chatbot_staff_label')) ?>,
                aiAssistant: <?= json_encode(_l('chatbot_ai_assistant')) ?>,
                aiLabel: <?= json_encode(_l('chatbot_ai_label')) ?>,
                noConversations: <?= json_encode(_l('chatbot_no_conversations_found')) ?>,
                noMessages: <?= json_encode(_l('chatbot_no_messages_preview')) ?>,
                helpLabel: <?= json_encode(_l('chatbot_help_label')) ?>,
                activeLabel: <?= json_encode(_l('chatbot_status_active')) ?>,
                newLabel: <?= json_encode(_l('chatbot_new_label')) ?>,
                closedLabel: <?= json_encode(_l('chatbot_closed_label')) ?>,
                badgeWaitingAgent: <?= json_encode(_l('chatbot_badge_waiting_agent')) ?>,
                badgeAgentResponding: <?= json_encode(_l('chatbot_badge_agent_responding')) ?>,
                badgeYouHandling: <?= json_encode(_l('chatbot_badge_you_handling')) ?>,
                badgeResolved: <?= json_encode(_l('chatbot_badge_resolved')) ?>,
                handledBy: <?= json_encode(_l('chatbot_handled_by')) ?>,
                assignedToYou: <?= json_encode(_l('chatbot_assigned_to_you')) ?>,
                needsHelp: <?= json_encode(_l('chatbot_needs_help')) ?>,
                takeOver: <?= json_encode(_l('chatbot_take_over')) ?>,
                aiHandling: <?= json_encode(_l('chatbot_ai_handling')) ?>,
                convertVisitor: <?= json_encode(_l('chatbot_convert_visitor')) ?>,
                convertToLead: <?= json_encode(_l('chatbot_convert_to_lead')) ?>,
                convertToClient: <?= json_encode(_l('chatbot_convert_to_client')) ?>,
                createFollowUp: <?= json_encode(_l('chatbot_create_follow_up')) ?>,
                endConversation: <?= json_encode(_l('chatbot_end_conversation')) ?>,
                deleteLabel: <?= json_encode(_l('chatbot_delete')) ?>,
                viewContact: <?= json_encode(_l('chatbot_view_contact')) ?>,
                viewClient: <?= json_encode(_l('chatbot_view_client')) ?>,
                viewLead: <?= json_encode(_l('chatbot_view_lead')) ?>,
                createAsLead: <?= json_encode(_l('chatbot_create_as_lead')) ?>,
                noContactForLead: <?= json_encode(_l('chatbot_no_contact_for_lead')) ?>,
                noContactInfo: <?= json_encode(_l('chatbot_no_contact_info')) ?>,
                noNotesYet: <?= json_encode(_l('chatbot_no_notes_yet')) ?>,
                conversationClosed: <?= json_encode(_l('chatbot_conversation_is_closed')) ?>,
                takeOverPrompt: <?= json_encode(_l('chatbot_take_over_prompt')) ?>,
                failedSend: <?= json_encode(_l('chatbot_failed_send_message')) ?>,
                handlingConv: <?= json_encode(_l('chatbot_handling_conversation')) ?>,
                takenOver: <?= json_encode(_l('chatbot_taken_over')) ?>,
                failedTakeOver: <?= json_encode(_l('chatbot_failed_take_over')) ?>,
                deleteConfirm: <?= json_encode(_l('chatbot_delete_confirm')) ?>,
                conversationDeleted: <?= json_encode(_l('chatbot_conversation_deleted')) ?>,
                endConfirm: <?= json_encode(_l('chatbot_end_confirm')) ?>,
                conversationEnded: <?= json_encode(_l('chatbot_conversation_ended')) ?>,
                endedByStaff: <?= json_encode(_l('chatbot_ended_by_staff')) ?>,
                failedEnd: <?= json_encode(_l('chatbot_failed_end')) ?>,
                alreadyLead: <?= json_encode(_l('chatbot_already_lead')) ?>,
                leadCreated: <?= json_encode(_l('chatbot_lead_created')) ?>,
                failedLead: <?= json_encode(_l('chatbot_failed_lead')) ?>,
                alreadyClient: <?= json_encode(_l('chatbot_already_client')) ?>,
                clientCreated: <?= json_encode(_l('chatbot_client_created')) ?>,
                confirmConvertClient: <?= json_encode(_l('chatbot_confirm_convert_client')) ?>,
                failedClient: <?= json_encode(_l('chatbot_failed_client')) ?>,
                enterTaskDesc: <?= json_encode(_l('chatbot_enter_task_desc')) ?>,
                ratedOn: <?= json_encode(_l('chatbot_csat_rated_on')) ?>,
                followUpDefault: <?= json_encode(_l('chatbot_follow_up_default')) ?>,
                taskCreated: <?= json_encode(_l('chatbot_task_created')) ?>,
                failedTask: <?= json_encode(_l('chatbot_failed_task')) ?>,
                supportRequest: <?= json_encode(_l('chatbot_support_request')) ?>,
                contactName: <?= json_encode(_l('chatbot_contact_name')) ?>,
                contactEmail: <?= json_encode(_l('chatbot_contact_email')) ?>,
                conversationSummary: <?= json_encode(_l('chatbot_conversation_summary')) ?>,
                newSupportRequest: <?= json_encode(_l('chatbot_new_support_request')) ?>,
                newMessage: <?= json_encode(_l('chatbot_new_message')) ?>,
                leadModalNA: <?= json_encode(_l('chatbot_lead_modal_not_available')) ?>,
                leadFromConv: <?= json_encode(_l('chatbot_lead_from_conversation')) ?>,
                nameLabel: <?= json_encode(_l('chatbot_name_label')) ?>,
                emailLabel: <?= json_encode(_l('chatbot_email')) ?>,
                phoneLabel: <?= json_encode(_l('chatbot_phone_label')) ?>,
                ipLabel: <?= json_encode(_l('chatbot_ip_label')) ?>,
                locationLabel: <?= json_encode(_l('chatbot_location_label')) ?>,
                openInMaps: <?= json_encode(_l('chatbot_open_in_maps')) ?>,
                browserLabel: <?= json_encode(_l('chatbot_browser_label')) ?>,
                referrerLabel: <?= json_encode(_l('chatbot_referrer_label')) ?>,
                startedLabel: <?= json_encode(_l('chatbot_started_label')) ?>,
                crmLabel: <?= json_encode(_l('chatbot_crm_label')) ?>,
                networkError: <?= json_encode(_l('chatbot_network_error')) ?>,
                justNow: <?= json_encode(_l('chatbot_just_now')) ?>,
                today: <?= json_encode(_l('chatbot_today')) ?>,
                yesterday: <?= json_encode(_l('chatbot_yesterday')) ?>,
                browserUnknown: <?= json_encode(_l('chatbot_browser_unknown')) ?>,
                browserOther: <?= json_encode(_l('chatbot_browser_other')) ?>,
                reconnecting: <?= json_encode(_l('chatbot_reconnecting')) ?>,
                connectionLost: <?= json_encode(_l('chatbot_connection_lost')) ?>,
                loadConversationsFailed: <?= json_encode(_l('chatbot_load_conversations_failed')) ?>,
                error: <?= json_encode(_l('chatbot_error')) ?>,
                chatbot_filter_tag_all: <?= json_encode(_l('chatbot_filter_tag_all')) ?>,
                chatbot_remove_tag: <?= json_encode(_l('chatbot_remove_tag')) ?>,
                chatbot_remove_all_tags: <?= json_encode(_l('chatbot_remove_all_tags')) ?>
            },
            urls: {
                contact: <?= json_encode(admin_url('clients/contact/')) ?>,
                client: <?= json_encode(admin_url('clients/client/')) ?>,
                lead: <?= json_encode(admin_url('leads/index/')) ?>,
                staffProfile: <?= json_encode(admin_url('staff/profile/')) ?>
            },
            aiIconSvg: '<i class="fa fa-brain"></i>'
        };

        // ===========================================
        // STATE
        // ===========================================
        const state = {
            conversations: [],
            activeConversationId: null,
            activeConversation: null,
            messages: [],
            cannedResponses: [],
            notes: [],
            filterStatus: 'all',
            filterAssignee: 'all',
            filterPriority: 'all',
            filterTags: '',
            availableTags: [],
            sortBy: 'newest',
            searchQuery: '',
            canReply: false,
            isLoading: true,
            pusher: null,
            staffChannel: null,
            convChannel: null,
            soundEnabled: (function() {
                try {
                    return localStorage.getItem('prchat_sound_enabled') !== '0';
                } catch (e) {
                    return true;
                }
            })(),
            desktopNotifEnabled: false,
            selectedCannedIndex: -1,
            refreshTimer: null,
            typingTimeout: null,
            isAtBottom: true,
            unreadTotal: 0,
            contextConvId: null,
            transferConvId: null,
            convSearchQuery: '',
            convSearchIndex: 0
        };

        // ===========================================
        // DOM REFERENCES
        // ===========================================
        const DOM = {};

        function cacheDom() {
            DOM.convList = document.getElementById('cb-conv-list');
            DOM.chatContainer = document.getElementById('cb-chat-container');
            DOM.emptyState = document.getElementById('cb-empty-state');
            DOM.messages = document.getElementById('cb-messages');
            DOM.messageInput = document.getElementById('cb-message-input');
            DOM.sendBtn = document.getElementById('cb-send-btn');
            DOM.inputWrapper = document.getElementById('cb-input-wrapper');
            DOM.inputDisabled = document.getElementById('cb-input-disabled');
            DOM.chatActions = document.getElementById('cb-chat-actions');
            DOM.headerName = document.getElementById('cb-header-name');
            DOM.headerMeta = document.getElementById('cb-header-meta');
            DOM.headerAvatar = document.getElementById('cb-header-avatar');
            DOM.panelEmpty = document.getElementById('cb-panel-empty');
            DOM.panelContent = document.getElementById('cb-panel-content');
            DOM.contactInfo = document.getElementById('cb-contact-info');
            DOM.sessionInfo = document.getElementById('cb-session-info');
            DOM.notesList = document.getElementById('cb-notes-list');
            DOM.noteInput = document.getElementById('cb-note-input');
            DOM.addNoteBtn = document.getElementById('cb-add-note-btn');
            DOM.crmActions = document.getElementById('cb-crm-actions');
            DOM.cannedDropdown = document.getElementById('cb-canned-dropdown');
            DOM.searchInput = document.getElementById('cb-search-input');
            DOM.countAll = document.getElementById('cb-count-all');
            DOM.countEscalated = document.getElementById('cb-count-escalated');
            DOM.filterStatusTrigger = document.getElementById('cb-filter-status-trigger');
            DOM.filterStatusDropdown = document.getElementById('cb-filter-status-dropdown');
            DOM.filterEffortTrigger = document.getElementById('cb-filter-effort-trigger');
            DOM.filterEffortDropdown = document.getElementById('cb-filter-effort-dropdown');
            DOM.filterSortTrigger = document.getElementById('cb-filter-sort-trigger');
            DOM.filterSortDropdown = document.getElementById('cb-filter-sort-dropdown');
            DOM.filterTagTrigger = document.getElementById('cb-filter-tag-trigger');
            DOM.filterTagDropdown = document.getElementById('cb-filter-tag-dropdown');
            DOM.filterTagOptions = document.getElementById('cb-filter-tag-options');
            DOM.contextMenu = document.getElementById('cb-context-menu');
            DOM.ctxTagsSubmenu = document.getElementById('cb-ctx-tags-submenu');
            DOM.scrollBottom = document.getElementById('cb-scroll-bottom');
            DOM.typingIndicator = document.getElementById('cb-typing-indicator');
            DOM.typingText = document.getElementById('cb-typing-text');
            DOM.themeToggle = document.getElementById('cb-theme-toggle');
            DOM.soundToggle = document.getElementById('cb-sound-toggle');
            DOM.desktopToggle = document.getElementById('cb-desktop-toggle');
            DOM.infoPanel = document.getElementById('cb-info-panel');
            DOM.toggleInfoPanel = document.getElementById('cb-toggle-info-panel');
            DOM.convSearchWrap = document.getElementById('cb-conv-search-wrap');
            DOM.convSearchInput = document.getElementById('cb-conv-search-input');
            DOM.convSearchCount = document.getElementById('cb-conv-search-count');
            DOM.convSearchPrev = document.getElementById('cb-conv-search-prev');
            DOM.convSearchNext = document.getElementById('cb-conv-search-next');
            DOM.convSearchClear = document.getElementById('cb-conv-search-clear');
            DOM.searchInConvBtn = document.getElementById('cb-search-in-conv-btn');
        }

        function closeAllFilterDropdowns() {
            var dropdowns = [DOM.filterStatusDropdown, DOM.filterEffortDropdown, DOM.filterSortDropdown, DOM.filterTagDropdown];
            var triggers = [DOM.filterStatusTrigger, DOM.filterEffortTrigger, DOM.filterSortTrigger, DOM.filterTagTrigger];
            dropdowns.forEach(function(d, i) {
                if (d) {
                    d.classList.remove('show');
                    d.setAttribute('aria-hidden', 'true');
                    if (d === DOM.filterTagDropdown) {
                        d.style.position = d.style.left = d.style.top = d.style.minWidth = '';
                    }
                }
                if (triggers[i]) triggers[i].setAttribute('aria-expanded', 'false');
            });
        }

        function fillTagsSubmenu(conv) {
            if (!DOM.ctxTagsSubmenu || !conv) return;
            var removeAllLabel = CONFIG.i18n.chatbot_remove_all_tags || 'Remove all tags';
            var convTagIds = (conv.tags || []).map(function(t) {
                return String(t.id);
            });
            var parts = [];
            if (convTagIds.length > 0) {
                parts.push('<div class="cb-ctx-item cb-ctx-remove-all" data-action="remove-all-tags"><i class="fa fa-times-circle cb-ctx-icon-remove"></i> ' + esc(removeAllLabel) + '</div><div class="cb-ctx-divider"></div>');
            }
            state.availableTags.forEach(function(t) {
                var has = convTagIds.indexOf(String(t.id)) !== -1;
                var tagColor = t.color || '#6c757d';
                var iconClass = has ? 'fa fa-minus-circle cb-ctx-icon-remove' : 'fa fa-plus-circle cb-ctx-icon-add';
                var iconStyle = has ? '' : ' style="color:' + tagColor + '"';
                parts.push('<div class="cb-ctx-item" data-tag-id="' + t.id + '" data-tag-action="' + (has ? 'remove' : 'add') + '"><i class="' + iconClass + '"' + iconStyle + '></i> <span style="color:' + tagColor + '">' + esc(t.name) + '</span></div>');
            });
            DOM.ctxTagsSubmenu.innerHTML = parts.join('');
        }

        // ===========================================
        // API CLIENT
        // ===========================================
        async function api(endpoint, data = {}) {
            const formData = new FormData();
            formData.append(CONFIG.csrfName, CONFIG.csrfHash);
            Object.keys(data).forEach(k => formData.append(k, data[k]));
            try {
                const res = await fetch(CONFIG.baseUrl + endpoint, {
                    method: 'POST',
                    body: formData
                });
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    if (res.status === 403 || res.redirected || res.status === 401) {
                        window.location.reload();
                        return {
                            success: false,
                            error: 'Session expired'
                        };
                    }
                    return {
                        success: false,
                        error: CONFIG.i18n.networkError
                    };
                }
                const json = await res.json();
                if (json[CONFIG.csrfName]) {
                    CONFIG.csrfHash = json[CONFIG.csrfName];
                }
                return json;
            } catch (err) {
                console.error('API error [' + endpoint + ']:', err);
                return {
                    success: false,
                    error: CONFIG.i18n.networkError
                };
            }
        }

        // ===========================================
        // DATA LOADING
        // ===========================================
        async function loadConversations() {
            const params = {};
            if (state.filterTags) params.tag_id = state.filterTags;
            const result = await api('get_conversations', params);
            state.isLoading = false;
            const loadingEl = document.getElementById('cb-loading-state');
            if (loadingEl) loadingEl.style.display = 'none';
            if (result && result.success) {
                state.conversations = result.conversations;
                renderConversations();
                updateCounts();
            } else {
                notify(CONFIG.i18n.error || 'Error', CONFIG.i18n.loadConversationsFailed || 'Failed to load conversations', 'danger');
            }
        }

        async function loadAvailableTags() {
            const result = await api('get_available_tags');
            if (result && result.success) state.availableTags = result.tags || [];
        }

        function renderTagFilterOptions() {
            if (!DOM.filterTagOptions) return;
            DOM.filterTagOptions.innerHTML = state.availableTags.map(function(t) {
                return '<div class="cb-filter-option" data-value="' + t.id + '">' + esc(t.name) + '</div>';
            }).join('');
        }

        async function loadConversation(id) {
            const result = await api('get_conversation', {
                conversation_id: id
            });
            if (!result.success) return;

            state.activeConversationId = id;
            state.activeConversation = result.conversation;
            state.messages = result.messages;
            state.notes = result.notes || [];
            state.canReply = result.can_reply;

            // Update the conversation in the list to clear unread flag (since get_conversation marks it as read)
            const conv = state.conversations.find(c => c.id == id);
            if (conv) {
                conv.has_unread = false;
                conv.unread_count = 0;
            }

            renderChat();
            renderInfoPanel();
            updateActiveInList();
            subscribeToPusherConversation(id);

            if (window.innerWidth <= 768) {
                var container = document.querySelector('.chatbot-live-chat');
                container.classList.add('cb-mobile-chat-active');
                container.classList.remove('cb-mobile-info-active');
            }

            // If loaded from analytics (conv parameter), highlight the conversation
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('conv')) {
                highlightConversationFromAnalytics();
                // Remove the parameter from URL without reloading
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }

        async function loadCannedResponses() {
            const result = await api('get_canned_responses');
            if (result.success) state.cannedResponses = result.responses;
        }

        // ===========================================
        // ACTIONS
        // ===========================================
        async function sendMessage(content) {
            if (!content.trim() || !state.canReply) return;
            if (state._sendingStaffReply) return;
            state._sendingStaffReply = true;
            DOM.sendBtn.disabled = true;

            appendMessage({
                role: 'assistant',
                content: content,
                is_staff: true,
                staff_name: CONFIG.staffName,
                staff_image: CONFIG.staffImage,
                created_at: new Date().toISOString()
            });
            DOM.messageInput.value = '';
            autoResizeTextarea(DOM.messageInput);
            scrollToBottom();

            const result = await api('send_staff_reply', {
                conversation_id: state.activeConversationId,
                message: content
            });
            if (!result.success) {
                alert_float('danger', result.error || CONFIG.i18n.failedSend);
            }
            state._sendingStaffReply = false;
            if (state.canReply) {
                DOM.sendBtn.disabled = false;
                DOM.messageInput.focus();
            }
        }

        async function takeOver() {
            const result = await api('assign_conversation', {
                conversation_id: state.activeConversationId
            });
            if (result.success) {
                alert_float('success', CONFIG.i18n.handlingConv);
                state.canReply = true;
                state.activeConversation.status = 'human_handling';
                state.activeConversation.assigned_staff_id = CONFIG.staffId;
                state.activeConversation.assigned_staff_name = CONFIG.staffName;
                renderChatActions();
                renderInputArea();
                appendSystemMessage(CONFIG.i18n.takenOver);
                updateConversationStatus(state.activeConversationId, {
                    status: 'human_handling',
                    assigned_staff_id: CONFIG.staffId,
                    assigned_staff_name: CONFIG.staffName,
                    is_escalated: false
                });
            } else {
                alert_float('danger', result.error || CONFIG.i18n.failedTakeOver);
            }
        }

        async function deleteConversation() {
            if (!confirm(CONFIG.i18n.deleteConfirm)) return;
            const result = await api('delete_conversation', {
                conversation_id: state.activeConversationId
            });
            if (result.success) {
                alert_float('success', CONFIG.i18n.conversationDeleted);
                removeConversationFromList(state.activeConversationId);
                state.activeConversationId = null;
                state.activeConversation = null;
                showEmptyState();
            }
        }

        async function endConversation() {
            if (!confirm(CONFIG.i18n.endConfirm)) return;
            const result = await api('end_conversation', {
                conversation_id: state.activeConversationId
            });
            if (result.success) {
                alert_float('success', CONFIG.i18n.conversationEnded);
                state.canReply = false;
                state.activeConversation.status = 'closed';
                renderChatActions();
                renderInputArea();
                appendSystemMessage(CONFIG.i18n.endedByStaff);
                updateConversationStatus(state.activeConversationId, {
                    status: 'closed'
                });
            } else {
                alert_float('danger', result.error || CONFIG.i18n.failedEnd);
            }
        }

        async function convertToLead() {
            const result = await api('convert_to_lead', {
                conversation_id: state.activeConversationId
            });
            if (result.success) {
                alert_float('success', CONFIG.i18n.leadCreated);
                if (result.lead_url) window.open(result.lead_url, '_blank');
                state.activeConversation.crm_lead_id = result.lead_id;
                renderInfoPanel();
            } else {
                alert_float('danger', result.error || CONFIG.i18n.failedLead);
            }
        }

        async function convertToClient() {
            if (!confirm(CONFIG.i18n.confirmConvertClient)) return;
            const result = await api('convert_to_client', {
                conversation_id: state.activeConversationId
            });
            if (result.success) {
                alert_float('success', CONFIG.i18n.clientCreated);
                if (result.client_url) window.open(result.client_url, '_blank');
                loadConversation(state.activeConversationId);
            } else {
                alert_float('danger', result.error || CONFIG.i18n.failedClient);
            }
        }

        function createFollowupTask() {
            const conv = state.activeConversation;
            if (!conv) return;

            // Build task URL with relation to lead if available
            let url = admin_url + 'tasks/task?';
            if (conv.crm_lead_id) {
                url += 'rel_id=' + conv.crm_lead_id + '&rel_type=lead';
            }

            // Build task info from conversation
            const visitorName = conv.visitor_name || conv.visitor_email || CONFIG.i18n.visitor;
            const taskName = CONFIG.i18n.supportRequest + ': ' + visitorName;

            // Build description from conversation messages
            let desc = '<strong>' + CONFIG.i18n.supportRequest + '</strong><br>';
            desc += '<b>' + CONFIG.i18n.contactName + ':</b> ' + esc(visitorName) + '<br>';
            if (conv.visitor_email) {
                desc += '<b>' + CONFIG.i18n.contactEmail + ':</b> ' + esc(conv.visitor_email) + '<br>';
            }
            desc += '<br><b>' + CONFIG.i18n.conversationSummary + ':</b><br>';
            const msgs = state.messages || [];
            const recentMsgs = msgs.slice(-10);
            recentMsgs.forEach(function(m) {
                if (m.role === 'system') return;
                const sender = m.role === 'user' ? visitorName : (m.is_staff ? (m.staff_name || CONFIG.i18n.staff) : 'AI');
                desc += '<b>' + esc(sender) + ':</b> ' + esc(m.content).substring(0, 200) + '<br>';
            });

            // Open the Perfex CRM task modal
            if (typeof new_task === 'function') {
                new_task(url);
                // Pre-fill fields after modal loads
                $('body').one('shown.bs.modal', '#_task_modal', function() {
                    var $modal = $('#_task_modal');
                    $modal.find('input[name="name"]').val(taskName);
                    $modal.find('input[name="startdate"]').val(moment().format(app.options.date_format || 'YYYY-MM-DD'));
                    // Set description via TinyMCE if available, otherwise textarea
                    var descEditor = tinymce.get('description');
                    if (descEditor) {
                        descEditor.setContent(desc);
                    } else {
                        $modal.find('textarea[name="description"]').val(desc.replace(/<br>/g, '\n').replace(/<[^>]+>/g, ''));
                    }
                    // Assign to current staff
                    var $assignees = $modal.find('select[name="assignees[]"]');
                    if ($assignees.length && app.staff_id) {
                        $assignees.val([app.staff_id]).selectpicker('refresh');
                    }
                });
            } else {
                window.location.href = url;
            }
        }

        async function addNote() {
            const content = DOM.noteInput.value.trim();
            if (!content) return;
            const result = await api('add_conversation_note', {
                conversation_id: state.activeConversationId,
                content: content
            });
            if (result.success) {
                state.notes.push(result.note);
                renderNotes();
                DOM.noteInput.value = '';
            }
        }

        // ===========================================
        // RENDER: CONVERSATION LIST
        // ===========================================
        function renderConversations() {
            let filtered = state.conversations.filter(conv => {
                // When user is searching, include all statuses so closed conversations can be found
                if (!state.searchQuery) {
                    if (state.filterStatus === 'open' && conv.status === 'closed') return false;
                    if (state.filterStatus === 'closed' && conv.status !== 'closed') return false;
                    if (state.filterStatus === 'escalated' && !conv.is_escalated) return false;
                }

                // Assignee filter
                if (state.filterAssignee === 'mine') {
                    if (parseInt(conv.assigned_staff_id) !== parseInt(CONFIG.staffId)) return false;
                }
                if (state.filterAssignee === 'unassigned') {
                    if (conv.assigned_staff_id) return false;
                }

                if (state.filterPriority !== 'all') {
                    if (!conv.priority || conv.priority !== state.filterPriority) return false;
                }

                // Tag filter (client-side when not using server filter, e.g. after clearing tag filter without reload)
                if (state.filterTags && (conv.tags || []).length) {
                    const hasTag = (conv.tags || []).some(function(t) {
                        return String(t.id) === String(state.filterTags);
                    });
                    if (!hasTag) return false;
                } else if (state.filterTags) {
                    return false;
                }

                // Search: name, email, and conversation id (e.g. "87" or "Visitor #87")
                if (state.searchQuery) {
                    const q = state.searchQuery.toLowerCase().trim();
                    const name = (conv.visitor_name || '').toLowerCase();
                    const email = (conv.visitor_email || '').toLowerCase();
                    const visitorLabel = (CONFIG.i18n.visitorHash + conv.id).toLowerCase();
                    const idMatch = q === String(conv.id) || visitorLabel.includes(q);
                    const nameEmailMatch = name.includes(q) || email.includes(q);
                    if (!idMatch && !nameEmailMatch) return false;
                }
                return true;
            });

            // Sort
            filtered.sort((a, b) => {
                if (state.sortBy === 'unread') {
                    const aUnread = a.has_unread ? 1 : 0;
                    const bUnread = b.has_unread ? 1 : 0;
                    if (aUnread !== bUnread) return bUnread - aUnread;
                }

                const aTime = new Date(a.updated_at || a.created_at || 0).getTime();
                const bTime = new Date(b.updated_at || b.created_at || 0).getTime();
                return state.sortBy === 'oldest' ? aTime - bTime : bTime - aTime;
            });

            if (filtered.length === 0) {
                DOM.convList.innerHTML = '<div class="cb-empty-list"><i class="fa fa-inbox"></i><p>' + CONFIG.i18n.noConversations + '</p></div>';
                return;
            }

            DOM.convList.innerHTML = filtered.map(conv => {
                const isActive = conv.id == state.activeConversationId;
                const cls = ['cb-conv-item', isActive ? 'active' : '', conv.is_escalated ? 'escalated' : '', conv.has_unread ? 'has-unread' : ''].filter(Boolean).join(' ');
                const initial = (conv.visitor_name || 'V').charAt(0).toUpperCase();
                let badges = '';
                if (conv.status === 'closed') {
                    badges += '<span class="label label-default">' + CONFIG.i18n.badgeResolved + '</span>';
                } else if (conv.status === 'human_handling') {
                    var isMineConv = parseInt(conv.assigned_staff_id) === parseInt(CONFIG.staffId);
                    badges += '<span class="label label-success">' + (isMineConv ? CONFIG.i18n.badgeYouHandling : CONFIG.i18n.badgeAgentResponding) + '</span>';
                } else if (conv.is_escalated) {
                    badges += '<span class="label label-warning">' + CONFIG.i18n.badgeWaitingAgent + '</span>';
                }

                const unreadCount = conv.unread_count || 0;
                const priorityColors = {
                    high: '#ef4444',
                    medium: '#f59e0b',
                    low: '#22c55e'
                };
                const priorityFlag = conv.priority && priorityColors[conv.priority] ?
                    '<i class="fa fa-flag cb-priority-flag" style="color:' + priorityColors[conv.priority] + '"></i> ' :
                    '';

                // CSAT indicator
                const csatIndicator = conv.csat_score ?
                    '<div class="cb-csat-mini" data-toggle="tooltip" title="Rated ' + conv.csat_score + '/5 stars">' + '★'.repeat(conv.csat_score) + '</div>' :
                    '';

                const tagBadges = (conv.tags || []).map(function(t) {
                    return '<span class="cb-tag-badge" style="background:' + (t.color || '#6c757d') + ';color:#fff">' + esc(t.name || '') + '</span>';
                }).join('');

                return '<div class="' + cls + '" data-id="' + conv.id + '" role="option" aria-selected="' + isActive + '">' +
                    '<div class="cb-conv-avatar ' + (conv.is_escalated ? 'escalated' : '') + '">' +
                    (conv.contact_image ? '<img src="' + esc(conv.contact_image) + '" alt="">' : initial) +
                    '</div>' +
                    '<div class="cb-conv-details">' +
                    '<div class="cb-conv-name"><span class="cb-conv-name-text">' + priorityFlag + esc(conv.visitor_name || CONFIG.i18n.visitorHash + conv.id) + '</span> ' + badges + (tagBadges ? ' <span class="cb-conv-tags">' + tagBadges + '</span>' : '') + '</div>' +
                    '<div class="cb-conv-preview">' + esc(conv.last_message || CONFIG.i18n.noMessages) + '</div>' +
                    '</div>' +
                    '<div class="cb-conv-meta">' +
                    '<div class="cb-conv-time">' + (conv.time_ago || '') + '</div>' +
                    csatIndicator +
                    (conv.has_unread ? '<div class="cb-conv-unread">' + (unreadCount > 0 ? unreadCount : '1') + '</div>' : '') +
                    '</div>' +
                    '</div>';
            }).join('');

            $(DOM.convList).find('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
        }

        // ===========================================
        // RENDER: CHAT
        // ===========================================
        function renderChat() {
            if (!state.activeConversation) return;
            DOM.emptyState.style.display = 'none';
            DOM.chatContainer.style.display = 'flex';
            if (DOM.convSearchWrap) DOM.convSearchWrap.style.display = 'none';
            state.convSearchQuery = '';
            state.convSearchIndex = 0;
            if (DOM.convSearchInput) DOM.convSearchInput.value = '';
            if (DOM.searchInConvBtn) DOM.searchInConvBtn.classList.remove('active');

            const conv = state.activeConversation;
            const initial = (conv.visitor_name || 'V').charAt(0).toUpperCase();

            DOM.headerAvatar.innerHTML = conv.contact_image ?
                '<img src="' + esc(conv.contact_image) + '" alt="">' :
                initial;
            DOM.headerName.textContent = conv.visitor_name || CONFIG.i18n.visitorHash + conv.id;

            const meta = [];
            if (conv.visitor_email) meta.push(conv.visitor_email);
            if (conv.time_ago) meta.push(conv.time_ago);
            DOM.headerMeta.innerHTML = meta.join(' &bull; ');

            renderChatActions();
            renderMessages();
            renderInputArea();
        }

        function renderChatActions() {
            const conv = state.activeConversation;
            if (!conv) return;
            const status = conv.status;
            const isMine = conv.assigned_staff_id == CONFIG.staffId;
            let html = '';

            const tip = ' data-toggle="tooltip" data-placement="bottom" ';
            if (status === 'closed') {
                html = '<span class="cb-status-badge badge-closed"><i class="fa fa-lock"></i> ' + CONFIG.i18n.closedLabel + '</span>' +
                    '<button class="cb-action-btn btn-task" onclick="ChatApp.createFollowupTask()"' + tip + 'title="' + CONFIG.i18n.createFollowUp + '"><i class="fa fa-tasks"></i></button>';
                if (CONFIG.canDelete) {
                    html += '<button class="cb-action-btn btn-delete" onclick="ChatApp.deleteConversation()"' + tip + 'title="' + CONFIG.i18n.deleteLabel + '"><i class="fa fa-trash"></i></button>';
                }
            } else if (status === 'human_handling' && isMine) {
                var hasContactInfo = !!(conv.visitor_email || conv.visitor_name);
                html = '<span class="cb-status-badge badge-assigned"><i class="fa fa-check-circle"></i> ' + CONFIG.i18n.assignedToYou + '</span>';
                var showConvertLead = hasContactInfo && !conv.crm_lead_id;
                var showConvertClient = hasContactInfo && !conv.crm_client_id;
                if (showConvertLead || showConvertClient) {
                    var menuItems = '';
                    if (showConvertLead) {
                        menuItems += '<a href="#" onclick="ChatApp.convertToLead(); return false;"><i class="fa fa-user-plus"></i> ' + CONFIG.i18n.convertToLead + '</a>';
                    }
                    if (showConvertClient) {
                        menuItems += '<a href="#" onclick="ChatApp.convertToClient(); return false;"><i class="fa fa-building"></i> ' + CONFIG.i18n.convertToClient + '</a>';
                    }
                    html += '<div class="cb-convert-dropdown">' +
                        '<button class="cb-action-btn btn-convert" onclick="ChatApp.toggleConvertMenu()"' + tip + 'title="' + CONFIG.i18n.convertVisitor + '"><i class="fa fa-exchange"></i></button>' +
                        '<div class="cb-convert-menu" id="cb-convert-menu">' +
                        menuItems +
                        '</div></div>';
                }
                html += '<button class="cb-action-btn btn-task" onclick="ChatApp.createFollowupTask()"' + tip + 'title="' + CONFIG.i18n.createFollowUp + '"><i class="fa fa-tasks"></i></button>';
                if (CONFIG.canDelete) {
                    html += '<button class="cb-action-btn btn-end" onclick="ChatApp.endConversation()"' + tip + 'title="' + CONFIG.i18n.endConversation + '"><i class="fa fa-stop-circle"></i></button>';
                }
            } else if (status === 'human_handling') {
                var handledName = esc(conv.assigned_staff_name || CONFIG.i18n.staff);
                var handledPart = conv.assigned_staff_id ?
                    '<a href="' + CONFIG.urls.staffProfile + conv.assigned_staff_id + '" target="_blank" class="cb-handled-link">' + handledName + '</a>' :
                    handledName;
                html = '<span class="cb-status-badge badge-other"><i class="fa fa-user"></i> ' + CONFIG.i18n.handledBy + ' ' + handledPart + '</span>';
            } else if (conv.is_escalated || status === 'pending_escalation') {
                html = '<span class="cb-status-badge badge-escalated"><i class="fa fa-exclamation-triangle"></i> ' + CONFIG.i18n.needsHelp + '</span>' +
                    '<button class="cb-action-btn btn-take-over" onclick="ChatApp.takeOver()"' + tip + 'title="' + CONFIG.i18n.takeOver + '"><i class="fa fa-user-plus"></i> ' + CONFIG.i18n.takeOver + '</button>';
            } else {
                html = '<span class="cb-status-badge badge-ai">' + CONFIG.aiIconSvg + ' ' + CONFIG.i18n.aiHandling + '</span>' +
                    '<button class="cb-action-btn btn-take-over" onclick="ChatApp.takeOver()"' + tip + 'title="' + CONFIG.i18n.takeOver + '"><i class="fa fa-user-plus"></i> ' + CONFIG.i18n.takeOver + '</button>';
            }
            DOM.chatActions.innerHTML = html;
            // Initialize Bootstrap tooltips on newly inserted elements
            $(DOM.chatActions).find('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
        }

        function renderMessages() {
            let lastDate = '';
            const html = [];

            state.messages.forEach(msg => {
                if (msg.role === 'tool' || msg.role === 'tool_result') return;
                const content = (msg.content || '').trim();
                if (!content || content === '[empty]' || content === '[]') return;

                // System messages (staff joined, conversation closed, etc.)
                if (msg.role === 'system') {
                    html.push('<div class="cb-system-msg"><span>' + esc(content) + '</span></div>');
                    return;
                }

                // Date separator
                const msgDate = formatDate(msg.created_at);
                if (msgDate && msgDate !== lastDate) {
                    html.push('<div class="cb-date-separator"><span>' + msgDate + '</span></div>');
                    lastDate = msgDate;
                }

                html.push(buildMessageHtml(msg));
            });

            DOM.messages.innerHTML = html.join('');
            if (state.convSearchQuery) highlightConvSearch(state.convSearchQuery);
            else scrollToBottom();
            updateConvSearchUI();
        }

        function highlightConvSearch(query) {
            const q = (query || '').trim();
            if (!q) return;
            const re = new RegExp(escRegex(q), 'gi');
            const walker = document.createTreeWalker(DOM.messages, NodeFilter.SHOW_TEXT, null, false);
            const toReplace = [];
            let n;
            while ((n = walker.nextNode())) {
                const text = n.textContent;
                if (!re.test(text)) continue;
                re.lastIndex = 0;
                const matches = [];
                let m;
                while ((m = re.exec(text)) !== null) matches.push({
                    index: m.index,
                    len: m[0].length,
                    text: m[0]
                });
                toReplace.push({
                    node: n,
                    matches: matches
                });
            }
            toReplace.forEach(function(item) {
                const node = item.node;
                const matches = item.matches;
                const text = node.textContent;
                const frag = document.createDocumentFragment();
                let last = 0;
                matches.forEach(function(match) {
                    if (match.index > last) frag.appendChild(document.createTextNode(text.slice(last, match.index)));
                    const span = document.createElement('span');
                    span.className = 'cb-search-highlight';
                    span.textContent = match.text;
                    frag.appendChild(span);
                    last = match.index + match.len;
                });
                if (last < text.length) frag.appendChild(document.createTextNode(text.slice(last)));
                node.parentNode.replaceChild(frag, node);
            });
        }

        function escRegex(s) {
            return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function updateConvSearchUI() {
            if (!DOM.convSearchCount) return;
            const highlights = DOM.messages.querySelectorAll('.cb-search-highlight');
            const total = highlights.length;
            DOM.convSearchCount.textContent = total ? (state.convSearchIndex + 1) + ' / ' + total : '';
            if (DOM.convSearchPrev) DOM.convSearchPrev.disabled = total === 0 || state.convSearchIndex <= 0;
            if (DOM.convSearchNext) DOM.convSearchNext.disabled = total === 0 || state.convSearchIndex >= total - 1;
            DOM.messages.querySelectorAll('.cb-search-highlight.cb-search-current').forEach(function(el) {
                el.classList.remove('cb-search-current');
            });
            if (highlights[state.convSearchIndex]) {
                highlights[state.convSearchIndex].classList.add('cb-search-current');
                highlights[state.convSearchIndex].scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
            }
        }

        function buildMessageHtml(msg) {
            const hasStaffFlag = msg.hasOwnProperty('is_staff') && (msg.is_staff === true || msg.is_staff === 1 || msg.is_staff === '1');
            const hasStaffName = !!(msg.staff_name && String(msg.staff_name).trim());
            const isStaff = hasStaffFlag || (msg.role === 'assistant' && hasStaffName);
            const isUser = msg.role === 'user';
            const isAI = msg.role === 'assistant' && !isStaff;

            let msgClass = 'cb-msg';
            let rowClass = 'cb-msg-row ';
            let avatarIcon, labelClass, senderLabel;

            if (isUser) {
                msgClass += ' incoming';
                rowClass += 'cb-msg-row-left';
                avatarIcon = '<i class="fa fa-user"></i>';
                labelClass = '';
                senderLabel = '';
            } else if (isStaff) {
                msgClass += ' outgoing staff-msg';
                rowClass += 'cb-msg-row-right';
                const imgUrl = msg.staff_image || CONFIG.staffImage;
                avatarIcon = imgUrl ?
                    '<img src="' + esc(imgUrl) + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">' :
                    '<i class="fa fa-user-tie"></i>';
                labelClass = 'staff';
                senderLabel = '';
            } else {
                msgClass += ' ai';
                rowClass += 'cb-msg-row-left';
                avatarIcon = CONFIG.aiIconSvg;
                labelClass = 'ai';
                senderLabel = '<span class="cb-sender-label ai-label">' + CONFIG.i18n.aiLabel + '</span> ';
            }

            const timeOnly = formatTimeOnly(msg.created_at);

            return '<div class="' + rowClass + '">' +
                '<div class="' + msgClass + '">' +
                '<div class="cb-msg-avatar ' + labelClass + '">' + avatarIcon + '</div>' +
                '<div class="cb-msg-bubble">' +
                '<div class="cb-msg-text">' + formatMessageContent(msg.content) + '</div>' +
                '<div class="cb-msg-meta">' + senderLabel + timeOnly + '</div>' +
                '</div>' +
                '</div></div>';
        }

        function appendMessage(msg) {
            state.messages.push(msg);
            DOM.messages.insertAdjacentHTML('beforeend', buildMessageHtml(msg));
            scrollToBottom();
        }

        function appendSystemMessage(text) {
            DOM.messages.insertAdjacentHTML('beforeend', '<div class="cb-system-msg"><span>' + esc(text) + '</span></div>');
            scrollToBottom();
        }

        function renderInputArea() {
            const status = state.activeConversation?.status;

            var cbAttachBtnEl = document.getElementById('cb-attach-btn');
            if (status === 'closed') {
                DOM.inputWrapper.style.display = 'none';
                DOM.inputDisabled.style.display = 'block';
                DOM.inputDisabled.innerHTML = '<i class="fa fa-lock"></i> ' + CONFIG.i18n.conversationClosed;
                if (cbAttachBtnEl) cbAttachBtnEl.disabled = true;
            } else if (state.canReply) {
                DOM.inputWrapper.style.display = 'flex';
                DOM.inputDisabled.style.display = 'none';
                DOM.messageInput.disabled = false;
                DOM.sendBtn.disabled = false;
                if (cbAttachBtnEl) cbAttachBtnEl.disabled = false;
                DOM.messageInput.focus();
            } else {
                DOM.inputWrapper.style.display = 'none';
                DOM.inputDisabled.style.display = 'block';
                DOM.inputDisabled.innerHTML = CONFIG.i18n.takeOverPrompt;
                if (cbAttachBtnEl) cbAttachBtnEl.disabled = true;
            }
        }

        // ===========================================
        // RENDER: INFO PANEL
        // ===========================================
        function renderInfoPanel() {
            if (!state.activeConversation) return;
            DOM.panelEmpty.style.display = 'none';
            DOM.panelContent.style.display = 'flex';

            const conv = state.activeConversation;
            const info = conv.visitor_info || {};

            // Contact
            let ch = '';
            if (conv.visitor_name) ch += infoRow(CONFIG.i18n.nameLabel, esc(conv.visitor_name));
            if (conv.visitor_email) ch += infoRow(CONFIG.i18n.emailLabel, '<a href="mailto:' + esc(conv.visitor_email) + '" target="_blank" rel="noopener noreferrer">' + esc(conv.visitor_email) + '</a>');
            if (info.phone) ch += infoRow(CONFIG.i18n.phoneLabel, esc(info.phone));
            if (conv.contact_id) ch += infoRow(CONFIG.i18n.crmLabel, '<a href="' + CONFIG.urls.contact + conv.contact_id + '" target="_blank" rel="noopener noreferrer">' + CONFIG.i18n.viewContact + ' <i class="fa fa-external-link"></i></a>');
            DOM.contactInfo.innerHTML = ch || '<div class="text-muted">' + CONFIG.i18n.noContactInfo + '</div>';

            // Session
            let sh = '';
            if (info.ip) sh += infoRow(CONFIG.i18n.ipLabel, esc(info.ip));

            // Location with Google Maps link
            const loc = [];
            if (info.city) loc.push(esc(info.city));
            if (info.country) loc.push(esc(info.country));
            if (loc.length > 0) {
                const locationText = loc.join(', ');
                let mapsUrl;
                if (info.latitude && info.longitude) {
                    mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(info.latitude + ',' + info.longitude);
                } else {
                    mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(locationText);
                }
                sh += infoRow(CONFIG.i18n.locationLabel,
                    '<a href="' + mapsUrl + '" target="_blank" rel="noopener noreferrer" style="color:#64748b; text-decoration:none;" data-toggle="tooltip" title="' + CONFIG.i18n.openInMaps + '">' +
                    '<i class="fa fa-globe" style="color:#0ea5e9;"></i> ' + locationText +
                    '</a>');
            }

            if (info.user_agent) sh += infoRow(CONFIG.i18n.browserLabel, esc(parseBrowser(info.user_agent)));
            if (info.referer) sh += infoRow(CONFIG.i18n.referrerLabel, '<a href="' + esc(info.referer) + '" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" title="' + esc(info.referer) + '">' + esc(truncate(info.referer, 30)) + '</a>');
            sh += infoRow(CONFIG.i18n.startedLabel, conv.time_ago);
            DOM.sessionInfo.innerHTML = sh;

            renderNotes();
            renderCrmActions();
            renderCSATInfo();

            $(DOM.panelContent).find('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
        }

        function renderCrmActions() {
            const conv = state.activeConversation;
            if (!conv) return;
            let h = '';

            // Priority: 1) Converted client  2) Converted lead / CRM lead  3) Matched contact  4) Create lead  5) No info
            if (conv.crm_client_id) {
                h = '<a href="' + CONFIG.urls.client + conv.crm_client_id + '" target="_blank" class="btn btn-info btn-sm btn-block"><i class="fa fa-building"></i> ' + CONFIG.i18n.viewClient + '</a>';
            } else if (conv.crm_lead_id) {
                h = '<a href="' + CONFIG.urls.lead + conv.crm_lead_id + '" target="_blank" class="btn btn-default btn-sm btn-block"><i class="fa fa-user"></i> ' + CONFIG.i18n.viewLead + '</a>';
            } else if (conv.contact_id) {
                h = '<a href="' + CONFIG.urls.contact + conv.contact_id + '" target="_blank" class="btn btn-info btn-sm btn-block"><i class="fa fa-user"></i> ' + CONFIG.i18n.viewContact + '</a>';
            } else if (conv.visitor_email || conv.visitor_name) {
                h = '<button class="btn btn-success btn-sm btn-block" onclick="ChatApp.createLead()"><i class="fa fa-user-plus"></i> ' + CONFIG.i18n.createAsLead + '</button>';
            } else {
                h = '<div class="text-muted text-center" style="font-size:12px;">' + CONFIG.i18n.noContactForLead + '</div>';
            }
            DOM.crmActions.innerHTML = h;
        }

        function renderCSATInfo() {
            const conv = state.activeConversation;
            if (!conv) return;
            const csatSection = document.getElementById('cb-csat-section');
            const csatInfo = document.getElementById('cb-csat-info');

            if (conv.csat_score) {
                csatSection.style.display = 'block';
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += i <= conv.csat_score ? '★' : '☆';
                }
                let html = '<div class="cb-csat-rating"><span class="cb-csat-stars">' + stars + '</span> <span class="cb-csat-score">(' + conv.csat_score + '/5)</span></div>';
                if (conv.csat_comment) {
                    html += '<div class="cb-csat-comment">' + esc(conv.csat_comment) + '</div>';
                }
                html += '<div class="cb-csat-date">' + CONFIG.i18n.ratedOn + ': ' + formatTime(conv.csat_at) + '</div>';
                csatInfo.innerHTML = html;
            } else {
                csatSection.style.display = 'none';
            }
        }

        function renderNotes() {
            if (state.notes.length === 0) {
                DOM.notesList.innerHTML = '<div class="text-muted" style="font-size:12px;">' + CONFIG.i18n.noNotesYet + '</div>';
                return;
            }
            DOM.notesList.innerHTML = state.notes.map(n =>
                '<div class="cb-note-item"><div class="cb-note-content">' + esc(n.content) + '</div>' +
                '<div class="cb-note-meta">' + esc(n.staff_name) + ' &bull; ' + formatTime(n.created_at) + '</div></div>'
            ).join('');
        }

        function infoRow(label, value) {
            return '<div class="cb-info-row"><span class="cb-info-label">' + label + '</span><span class="cb-info-value">' + value + '</span></div>';
        }

        // ===========================================
        // RENDER: HELPERS
        // ===========================================
        function showEmptyState() {
            DOM.chatContainer.style.display = 'none';
            DOM.emptyState.style.display = 'flex';
            if (DOM.convSearchWrap) DOM.convSearchWrap.style.display = 'none';
            DOM.panelContent.style.display = 'none';
            DOM.panelEmpty.style.display = 'flex';
            var container = document.querySelector('.chatbot-live-chat');
            if (container) {
                container.classList.remove('cb-mobile-chat-active', 'cb-mobile-info-active');
            }
        }

        function updateCounts() {
            const all = state.conversations.length;
            const esc_ = state.conversations.filter(c => c.is_escalated).length;

            DOM.countAll.textContent = all;
            DOM.countEscalated.textContent = esc_;

            if (esc_ > 0) DOM.countEscalated.classList.add('has-items');
            else DOM.countEscalated.classList.remove('has-items');
        }

        function updateActiveInList() {
            document.querySelectorAll('.cb-conv-item').forEach(el => {
                el.classList.toggle('active', el.dataset.id == state.activeConversationId);
                if (el.dataset.id == state.activeConversationId) {
                    el.classList.remove('has-unread');
                    const badge = el.querySelector('.cb-conv-unread');
                    if (badge) badge.remove();
                }
            });
        }

        function updateConversationStatus(id, updates) {
            const conv = state.conversations.find(c => c.id == id);
            if (conv) {
                Object.assign(conv, updates);
                renderConversations();
                updateCounts();
            }
        }

        function removeConversationFromList(id) {
            state.conversations = state.conversations.filter(c => c.id != id);
            renderConversations();
            updateCounts();
        }

        function markConversationAsUnread(id) {
            const conv = state.conversations.find(c => c.id == id);
            if (conv) {
                conv.unread_count = (conv.unread_count || 0) + 1;
                conv.has_unread = true;
            }
            const item = document.querySelector('.cb-conv-item[data-id="' + id + '"]');
            if (item && !item.classList.contains('active')) {
                item.classList.add('has-unread');
                const badge = item.querySelector('.cb-conv-unread');
                const count = conv ? conv.unread_count : 1;
                if (badge) {
                    badge.textContent = count;
                } else {
                    item.querySelector('.cb-conv-meta').insertAdjacentHTML('beforeend', '<div class="cb-conv-unread">' + count + '</div>');
                }
            }
        }

        function updateConversationInList(id, preview) {
            const idx = state.conversations.findIndex(c => c.id == id);
            if (idx === -1) {
                loadConversations();
                return;
            }

            const conv = state.conversations[idx];
            if (preview) {
                conv.last_message = typeof preview === 'string' ? preview : (preview.content || preview.preview || preview.message || String(preview));
            }
            conv.time_ago = CONFIG.i18n.justNow;
            if (id != state.activeConversationId) {
                conv.has_unread = true;
            }

            state.conversations.splice(idx, 1);
            state.conversations.unshift(conv);
            renderConversations();
        }

        // ===========================================
        // CANNED RESPONSES
        // ===========================================
        function showCannedDropdown(query) {
            const filtered = state.cannedResponses.filter(r => {
                if (!query) return true;
                return (r.shortcut || '').toLowerCase().includes(query.toLowerCase()) ||
                    r.title.toLowerCase().includes(query.toLowerCase());
            });
            if (filtered.length === 0) {
                hideCannedDropdown();
                return;
            }

            state.selectedCannedIndex = 0;
            DOM.cannedDropdown.innerHTML = filtered.map((r, i) =>
                '<div class="cb-canned-item ' + (i === 0 ? 'selected' : '') + '" data-index="' + i + '">' +
                '<div><span class="cb-canned-title">' + esc(r.title) + '</span>' +
                (r.shortcut ? '<span class="cb-canned-shortcut">' + esc(r.shortcut) + '</span>' : '') + '</div>' +
                '<div class="cb-canned-preview">' + esc(truncate(r.content, 60)) + '</div></div>'
            ).join('');

            DOM.cannedDropdown.classList.add('show');
            DOM.cannedDropdown._filtered = filtered;
        }

        function hideCannedDropdown() {
            DOM.cannedDropdown.classList.remove('show');
            state.selectedCannedIndex = -1;
        }

        function selectCannedResponse(index) {
            const filtered = DOM.cannedDropdown._filtered;
            if (!filtered || !filtered[index]) return;
            let content = filtered[index].content;
            const conv = state.activeConversation;
            content = content.replace(/\{\{visitor_name\}\}/gi, conv?.visitor_name || 'there');
            content = content.replace(/\{\{staff_name\}\}/gi, CONFIG.staffName);
            DOM.messageInput.value = content;
            hideCannedDropdown();
            DOM.messageInput.focus();
        }

        function updateCannedSelection() {
            DOM.cannedDropdown.querySelectorAll('.cb-canned-item').forEach((el, i) => {
                el.classList.toggle('selected', i === state.selectedCannedIndex);
            });
        }

        // ===========================================
        // PUSHER REAL-TIME
        // ===========================================
        function initPusher() {
            if (!CONFIG.pusherEnabled || typeof Pusher === 'undefined') return;

            state.pusher = new Pusher(CONFIG.pusherKey, {
                cluster: CONFIG.pusherCluster
            });

            var connectionTimer = null;
            state.pusher.connection.bind('state_change', function(states) {
                var indicator = document.getElementById('cb-connection-status');
                if (!indicator) return;
                if (connectionTimer) {
                    clearTimeout(connectionTimer);
                    connectionTimer = null;
                }
                if (states.current === 'connected') {
                    indicator.style.display = 'none';
                } else if (states.current === 'connecting' || states.current === 'unavailable' || states.current === 'disconnected') {
                    connectionTimer = setTimeout(function() {
                        indicator.style.display = 'block';
                        indicator.textContent = states.current === 'connecting' ?
                            (CONFIG.i18n.reconnecting || 'Reconnecting...') :
                            (CONFIG.i18n.connectionLost || 'Connection lost. Updates paused.');
                    }, 2000);
                }
            });

            state.staffChannel = state.pusher.subscribe('chatbot-staff');

            // Listen for CSAT updates
            state.staffChannel.bind('csat-updated', data => {
                if (state.activeConversation && state.activeConversation.id == data.conversation_id) {
                    state.activeConversation.csat_score = data.csat_score;
                    state.activeConversation.csat_comment = data.csat_comment;
                    state.activeConversation.csat_at = data.csat_at;
                    renderCSATInfo();
                }
                const conv = state.conversations.find(c => c.id == data.conversation_id);
                if (conv) {
                    conv.csat_score = data.csat_score;
                    renderConversations();
                }
            });

            // Escalation notification
            state.staffChannel.bind('new-escalation', data => {
                const notificationTitle = 'Chatbot - New Notification';
                notify(notificationTitle, CONFIG.i18n.newSupportRequest, 'warning');
                if (data.conversation_id) {
                    updateConversationInList(data.conversation_id, data.preview);
                    markConversationAsUnread(data.conversation_id);
                } else {
                    loadConversations();
                }
            });

            // New message notification
            state.staffChannel.bind('new-message', data => {
                updateConversationInList(data.conversation_id, data.preview);
                if (data.conversation_id != state.activeConversationId) {
                    markConversationAsUnread(data.conversation_id);
                    const notificationTitle = 'Chatbot - New Notification';
                    // Get visitor name from conversation (if available)
                    const conv = state.conversations.find(c => c.id == data.conversation_id);
                    const visitorName = conv?.visitor_info?.name || conv?.visitor_info?.email || 'Visitor';
                    const notificationBody = visitorName + ': ' + ((data.preview || '').substring(0, 100));
                    notify(notificationTitle, notificationBody, 'info');
                }
            });
        }

        function subscribeToPusherConversation(id) {
            if (!state.pusher) return;
            if (state.convChannel) state.pusher.unsubscribe(state.convChannel.name);

            state.convChannel = state.pusher.subscribe('chatbot-conversation-' + id);

            state.convChannel.bind('visitor-message', data => {
                appendMessage({
                    role: 'user',
                    content: data.content,
                    created_at: new Date().toISOString()
                });
                updateConversationInList(id, data.content);
                showTypingIndicator(false);

                // Notify staff if this conversation is not in focus
                if (id != state.activeConversationId || document.hidden) {
                    const visitorName = state.activeConversation?.visitor_info?.name || CONFIG.i18n.visitor || 'Visitor';
                    const preview = data.content?.substring(0, 100) || CONFIG.i18n.newMessage;
                    const notificationTitle = 'Chatbot - New Notification';
                    const notificationBody = preview;
                    notify(notificationTitle, notificationBody, 'info');
                }
            });

            state.convChannel.bind('ai-message', data => {
                appendMessage({
                    role: 'assistant',
                    content: data.content,
                    is_staff: false,
                    created_at: new Date().toISOString()
                });
            });

            state.convChannel.bind('staff-message', data => {
                const sid = data.staff_id != null && data.staff_id !== '' ?
                    parseInt(String(data.staff_id), 10) :
                    NaN;
                if (Number.isFinite(sid) && sid === parseInt(String(CONFIG.staffId), 10)) {
                    return;
                }
                appendMessage({
                    role: 'assistant',
                    content: data.content,
                    is_staff: true,
                    staff_name: data.staff_name,
                    staff_image: data.staff_image || null,
                    created_at: new Date().toISOString()
                });
            });

            state.convChannel.bind('visitor-typing', data => {
                if (!state.activeConversation) return;
                if (data.typing) {
                    showTypingIndicator(true);
                    clearTimeout(state.typingTimeout);
                    state.typingTimeout = setTimeout(() => showTypingIndicator(false), 5000);
                } else {
                    clearTimeout(state.typingTimeout);
                    showTypingIndicator(false);
                }
            });

            state.convChannel.bind('human-takeover', data => {
                if (!state.activeConversation) return;
                if (parseInt(data.staff_id) !== CONFIG.staffId) {
                    appendSystemMessage(data.message || data.staff_name);
                }
                state.activeConversation.status = 'human_handling';
                state.activeConversation.is_escalated = false;
                state.activeConversation.assigned_staff_id = data.staff_id || state.activeConversation.assigned_staff_id;
                state.activeConversation.assigned_staff_name = data.staff_name;
                renderChatActions();
                renderInputArea();
                // Update conversation in list
                updateConversationStatus(state.activeConversationId, {
                    status: 'human_handling',
                    is_escalated: false,
                    assigned_staff_id: data.staff_id,
                    assigned_staff_name: data.staff_name
                });
            });

            state.convChannel.bind('conversation-closed', () => {
                if (!state.activeConversation) return;
                state.activeConversation.status = 'closed';
                state.canReply = false;
                renderChatActions();
                renderInputArea();
                appendSystemMessage(CONFIG.i18n.conversationClosed);
            });
        }

        // ===========================================
        // NOTIFICATIONS
        // ===========================================
        let audioCtx = null;
        const originalTitle = document.title;
        let titleFlashInterval = null;

        function notify(title, body, type) {
            // Sound
            if (state.soundEnabled) playNotificationSound();

            // Title flash
            if (!document.hasFocus()) flashTitle(title);

            if (state.desktopNotifEnabled && 'Notification' in window && Notification.permission === 'granted') {
                try { var dn = new Notification(title, {body: body, tag: 'cb-' + Date.now()}); setTimeout(function(){ dn.close(); }, 6000); } catch(e) {}
            }
        }

        function playNotificationSound() {
            try {
                if (!audioCtx) audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.frequency.value = 800;
                osc.type = 'sine';
                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.3);
            } catch (e) {}
        }

        function flashTitle(message) {
            if (document.hasFocus()) return;
            if (titleFlashInterval) clearInterval(titleFlashInterval);
            let isOriginal = true;
            titleFlashInterval = setInterval(() => {
                document.title = isOriginal ? message : originalTitle;
                isOriginal = !isOriginal;
            }, 1000);
            window.addEventListener('focus', stopFlashTitle, {
                once: true
            });
        }

        function stopFlashTitle() {
            if (titleFlashInterval) {
                clearInterval(titleFlashInterval);
                titleFlashInterval = null;
                document.title = originalTitle;
            }
        }

        function showTypingIndicator(show) {
            DOM.typingIndicator.classList.toggle('visible', show);
            if (show) scrollToBottom();
        }

        // ===========================================
        // UTILITIES
        // ===========================================
        function esc(text) {
            if (!text) return '';
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        function formatMessageContent(text) {
            if (!text) return '';
            var trimmed = text.trim();
            var imgMatch = trimmed.match(/^(https?:\/\/\S+\.(?:png|jpe?g|gif|webp|bmp))$/i);
            if (imgMatch) {
                return '<a href="' + esc(imgMatch[1]) + '" target="_blank" rel="noopener"><img class="cb-chat-image" src="' + esc(imgMatch[1]) + '" alt="Shared image" loading="lazy" style="max-width:280px;max-height:200px;border-radius:8px;margin:4px 0;display:block;cursor:pointer;"></a>';
            }
            text = text.replace(/<a\s[^>]*href=['"]([^'"]+)['"][^>]*>([^<]*)<\/a>/gi, '$2 ( $1 )');
            let s = esc(text);

            // Code blocks: ```...```
            s = s.replace(/```([\s\S]*?)```/g, function(_, code) {
                return '<pre><code>' + code.replace(/^\n|\n$/g, '') + '</code></pre>';
            });

            // Inline code: `...`
            s = s.replace(/`([^`\n]+)`/g, '<code>$1</code>');

            // Bold: **...**
            s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

            // Italic: *...* (not preceded/followed by *)
            s = s.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');

            // Headings: ### ... → h5, ## ... → h4, # ... → h3
            s = s.replace(/^### (.+)$/gm, '<h5 class="cb-md-heading">$1</h5>');
            s = s.replace(/^## (.+)$/gm, '<h4 class="cb-md-heading">$1</h4>');
            s = s.replace(/^# (.+)$/gm, '<h3 class="cb-md-heading">$1</h3>');

            // Unordered list items: - item or * item
            s = s.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');

            // Ordered list items: 1. item
            s = s.replace(/^\d+\.\s(.+)$/gm, '<li>$1</li>');

            // Wrap consecutive <li> into <ul>
            s = s.replace(/((?:<li>.*?<\/li>\n?)+)/g, '<ul class="cb-md-list">$1</ul>');

            s = s.replace(/(https?:\/\/[^\s<>"\']+?)([.,;:!?)]*)(?=\s|$|<\/)/g, function(_, url, trail) {
                if (/\.(png|jpe?g|gif|webp|bmp)$/i.test(url)) {
                    return '<a href="' + url + '" target="_blank" rel="noopener"><img class="cb-chat-image" src="' + url + '" alt="Image" loading="lazy" style="max-width:280px;max-height:200px;border-radius:8px;margin:4px 0;display:block;cursor:pointer;"></a>' + (trail || '');
                }
                return '<a class="cb-msg-link" href="' + url + '" target="_blank" rel="noopener">' + url + '</a>' + (trail || '');
            });

            // Newlines (skip inside <pre>)
            var parts = s.split(/(<pre[\s\S]*?<\/pre>)/);
            for (var i = 0; i < parts.length; i++) {
                if (!parts[i].match(/^<pre/)) {
                    parts[i] = parts[i].replace(/\n/g, '<br>');
                }
            }
            s = parts.join('');

            // Clean up: remove <br> between list items
            s = s.replace(/<\/li><br>/g, '</li>');
            s = s.replace(/<br><li>/g, '<li>');
            s = s.replace(/<\/ul><br>/g, '</ul>');
            s = s.replace(/<br><ul/g, '<ul');
            // Clean up: remove <br> around headings
            s = s.replace(/<br><h([345])/g, '<h$1');
            s = s.replace(/<\/h([345])><br>/g, '</h$1>');
            // Clean up: remove <br> around pre blocks
            s = s.replace(/<br><pre>/g, '<pre>');
            s = s.replace(/<\/pre><br>/g, '</pre>');

            return s;
        }

        function truncate(str, len) {
            if (!str) return '';
            return str.length > len ? str.substring(0, len) + '...' : str;
        }

        function formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        function formatTimeOnly(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleTimeString(undefined, {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            const today = new Date();
            if (d.toDateString() === today.toDateString()) return CONFIG.i18n.today;
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            if (d.toDateString() === yesterday.toDateString()) return CONFIG.i18n.yesterday;
            return d.toLocaleDateString(undefined, {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function parseBrowser(ua) {
            if (!ua) return CONFIG.i18n.browserUnknown;
            if (ua.includes('Chrome') && !ua.includes('Edg')) return 'Chrome';
            if (ua.includes('Firefox')) return 'Firefox';
            if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
            if (ua.includes('Edg')) return 'Edge';
            return CONFIG.i18n.browserOther;
        }

        function scrollToBottom() {
            setTimeout(() => {
                DOM.messages.scrollTop = DOM.messages.scrollHeight;
            }, 50);
        }

        function autoResizeTextarea(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 100) + 'px';
        }

        function highlightConversationFromAnalytics() {
            // Highlight the conversation item in the sidebar when coming from analytics
            const activeConvItem = document.querySelector('.cb-conv-item.active');
            if (activeConvItem) {
                // Add temporary highlight effect
                activeConvItem.style.background = 'linear-gradient(90deg, rgba(33, 88, 224, 0.1), rgba(33, 88, 224, 0.05))';
                activeConvItem.style.transform = 'scale(1.02)';
                activeConvItem.style.transition = 'all 0.3s ease';
                activeConvItem.style.boxShadow = '0 2px 8px rgba(33, 88, 224, 0.2)';

                // Scroll the conversation into view
                activeConvItem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Remove highlight after 3 seconds
                setTimeout(() => {
                    activeConvItem.style.background = '';
                    activeConvItem.style.transform = '';
                    activeConvItem.style.boxShadow = '';
                    activeConvItem.style.transition = '';
                }, 3000);
            }
        }

        let searchDebounceTimer = null;

        // ===========================================
        // EVENT HANDLERS
        // ===========================================
        function initEventHandlers() {
            // Conversation click
            DOM.convList.addEventListener('click', e => {
                const item = e.target.closest('.cb-conv-item');
                if (item) loadConversation(item.dataset.id);
            });

            // Filter pills: one dropdown per pill, close others when opening
            function openFilterPill(trigger, dropdown) {
                closeAllFilterDropdowns();
                if (dropdown === DOM.filterTagDropdown) {
                    renderTagFilterOptions();
                    var r = trigger.getBoundingClientRect();
                    dropdown.style.position = 'fixed';
                    dropdown.style.left = r.left + 'px';
                    dropdown.style.top = (r.bottom + 4) + 'px';
                    dropdown.style.minWidth = Math.max(r.width, 140) + 'px';
                } else {
                    dropdown.style.position = '';
                    dropdown.style.left = '';
                    dropdown.style.top = '';
                    dropdown.style.minWidth = '';
                }
                dropdown.classList.add('show');
                dropdown.setAttribute('aria-hidden', 'false');
                trigger.setAttribute('aria-expanded', 'true');
                syncFilterUI();
            }

            function bindFilterPill(trigger, dropdown, stateKey, setState, onSelect) {
                if (!trigger || !dropdown) return;
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (dropdown.classList.contains('show')) closeAllFilterDropdowns();
                    else openFilterPill(trigger, dropdown);
                });
                dropdown.querySelectorAll('.cb-filter-option').forEach(function(opt) {
                    opt.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var value = opt.getAttribute('data-value') || '';
                        setState(value);
                        var labelEl = trigger.querySelector('.cb-filter-pill-text');
                        if (labelEl) labelEl.textContent = opt.textContent.trim();
                        dropdown.querySelectorAll('.cb-filter-option').forEach(function(o) {
                            o.classList.remove('selected');
                        });
                        opt.classList.add('selected');
                        closeAllFilterDropdowns();
                        onSelect();
                    });
                });
            }
            bindFilterPill(DOM.filterStatusTrigger, DOM.filterStatusDropdown, 'filterStatus', function(v) {
                state.filterStatus = v;
            }, renderConversations);
            if (DOM.filterEffortTrigger && DOM.filterEffortDropdown) {
                DOM.filterEffortTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (DOM.filterEffortDropdown.classList.contains('show')) closeAllFilterDropdowns();
                    else openFilterPill(DOM.filterEffortTrigger, DOM.filterEffortDropdown);
                });
                DOM.filterEffortDropdown.addEventListener('click', function(e) {
                    var opt = e.target.closest('.cb-filter-option');
                    if (!opt) return;
                    e.stopPropagation();
                    var section = opt.closest('.cb-filter-section');
                    var filterName = section ? section.getAttribute('data-filter') : '';
                    var value = opt.getAttribute('data-value') || '';
                    if (filterName === 'assignee') {
                        state.filterAssignee = value;
                    } else if (filterName === 'priority') {
                        state.filterPriority = value;
                    }
                    section.querySelectorAll('.cb-filter-option').forEach(function(o) {
                        o.classList.remove('selected');
                    });
                    opt.classList.add('selected');
                    syncFilterUI();
                    renderConversations();
                });
            }
            bindFilterPill(DOM.filterSortTrigger, DOM.filterSortDropdown, 'sortBy', function(v) {
                state.sortBy = v;
            }, renderConversations);
            bindFilterPill(DOM.filterTagTrigger, DOM.filterTagDropdown, 'filterTags', function(v) {
                state.filterTags = v;
            }, loadConversations);

            // Tag dropdown: "All tags" option + dynamic options (dynamic ones get click in renderTagFilterOptions or we delegate)
            if (DOM.filterTagDropdown && DOM.filterTagOptions) {
                DOM.filterTagDropdown.addEventListener('click', function(e) {
                    var opt = e.target.closest('.cb-filter-option');
                    if (!opt || !opt.closest('#cb-filter-tag-options')) return;
                    e.stopPropagation();
                    var value = opt.getAttribute('data-value') || '';
                    state.filterTags = value;
                    if (DOM.filterTagTrigger) {
                        var labelEl = DOM.filterTagTrigger.querySelector('.cb-filter-pill-text');
                        if (labelEl) labelEl.textContent = opt.textContent.trim();
                    }
                    DOM.filterTagDropdown.querySelectorAll('.cb-filter-option').forEach(function(o) {
                        o.classList.remove('selected');
                    });
                    opt.classList.add('selected');
                    closeAllFilterDropdowns();
                    loadConversations();
                });
            }
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.cb-filter-bar')) closeAllFilterDropdowns();
            });

            function syncFilterUI() {
                function syncPill(trigger, dropdown, currentValue) {
                    if (!trigger || !dropdown) return;
                    var opt = dropdown.querySelector('.cb-filter-option[data-value="' + currentValue + '"]');
                    if (opt) {
                        var labelEl = trigger.querySelector('.cb-filter-pill-text');
                        if (labelEl) labelEl.textContent = opt.textContent.trim();
                        dropdown.querySelectorAll('.cb-filter-option').forEach(function(o) {
                            o.classList.remove('selected');
                        });
                        opt.classList.add('selected');
                    }
                }
                syncPill(DOM.filterStatusTrigger, DOM.filterStatusDropdown, state.filterStatus);
                if (DOM.filterEffortDropdown) {
                    DOM.filterEffortDropdown.querySelectorAll('.cb-filter-section').forEach(function(section) {
                        var key = section.getAttribute('data-filter');
                        var current = key === 'assignee' ? state.filterAssignee : key === 'priority' ? state.filterPriority : '';
                        if (!current) return;
                        section.querySelectorAll('.cb-filter-option').forEach(function(o) {
                            var v = o.getAttribute('data-value') || '';
                            o.classList.toggle('selected', v === current);
                        });
                    });
                }
                syncPill(DOM.filterSortTrigger, DOM.filterSortDropdown, state.sortBy);
                syncPill(DOM.filterTagTrigger, DOM.filterTagDropdown, state.filterTags);
            }
            syncFilterUI();

            // Search with debounce
            DOM.searchInput.addEventListener('input', e => {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    state.searchQuery = e.target.value;
                    renderConversations();
                }, 250);
            });

            // Right-click context menu
            DOM.convList.addEventListener('contextmenu', e => {
                const item = e.target.closest('.cb-conv-item');
                if (!item) return;
                e.preventDefault();
                state.contextConvId = parseInt(item.dataset.id);
                const conv = state.conversations.find(c => c.id == state.contextConvId);
                if (!conv) return;

                const menu = DOM.contextMenu;
                const isMine = parseInt(conv.assigned_staff_id) === parseInt(CONFIG.staffId) && conv.status === 'human_handling';
                const isHuman = conv.status === 'human_handling';
                const isClosed = conv.status === 'closed';

                const showAssign = !isMine && !isClosed;
                const showTransfer = isHuman && isMine;
                menu.querySelector('[data-action="assign"]').style.display = showAssign ? '' : 'none';
                menu.querySelector('[data-action="transfer"]').style.display = showTransfer ? '' : 'none';
                document.getElementById('cb-ctx-divider-top').style.display = (showAssign || showTransfer) ? '' : 'none';
                const showEnd = isMine && CONFIG.canDelete;
                const showDelete = isClosed && CONFIG.canDelete;
                menu.querySelector('[data-action="end"]').style.display = showEnd ? '' : 'none';
                menu.querySelector('[data-action="delete"]').style.display = showDelete ? '' : 'none';
                document.getElementById('cb-ctx-divider-danger').style.display = (showEnd || showDelete) ? '' : 'none';

                const currentPriority = conv.priority || '';
                menu.querySelectorAll('[data-priority]').forEach(el => {
                    el.classList.toggle('active', el.dataset.priority === currentPriority);
                });

                fillTagsSubmenu(conv);
                menu.querySelectorAll('.cb-ctx-has-sub').forEach(function(el) {
                    el.classList.remove('cb-ctx-sub-open');
                });

                menu.style.left = e.clientX + 'px';
                menu.style.top = e.clientY + 'px';
                menu.classList.add('show');

                // Keep menu within viewport
                const rect = menu.getBoundingClientRect();
                if (rect.right > window.innerWidth) menu.style.left = (window.innerWidth - rect.width - 8) + 'px';
                if (rect.bottom > window.innerHeight) menu.style.top = (window.innerHeight - rect.height - 8) + 'px';
            });

            // Context menu actions
            DOM.contextMenu.addEventListener('click', e => {
                const tagsRow = e.target.closest('.cb-ctx-item.cb-ctx-has-sub[data-action="tags"]');
                if (tagsRow && !e.target.closest('.cb-ctx-submenu')) {
                    e.stopPropagation();
                    DOM.contextMenu.querySelectorAll('.cb-ctx-has-sub').forEach(function(el) {
                        el.classList.remove('cb-ctx-sub-open');
                    });
                    tagsRow.classList.toggle('cb-ctx-sub-open');
                    return;
                }

                const removeAllEl = e.target.closest('.cb-ctx-remove-all[data-action="remove-all-tags"]');
                if (removeAllEl) {
                    const convId = state.contextConvId;
                    const conv = state.conversations.find(c => c.id == convId);
                    if (!conv || !(conv.tags && conv.tags.length)) return;
                    DOM.contextMenu.classList.remove('show');
                    DOM.contextMenu.querySelectorAll('.cb-ctx-has-sub').forEach(function(el) {
                        el.classList.remove('cb-ctx-sub-open');
                    });
                    const tagIds = conv.tags.map(function(t) {
                        return t.id;
                    });
                    Promise.all(tagIds.map(function(tagId) {
                            return api('remove_conversation_tag', {
                                conversation_id: convId,
                                tag_id: tagId
                            });
                        }))
                        .then(function() {
                            conv.tags = [];
                            renderConversations();
                            if (state.activeConversationId == convId && state.activeConversation) state.activeConversation.tags = [];
                        });
                    return;
                }

                const tagEl = e.target.closest('[data-tag-id]');
                if (tagEl) {
                    const convId = state.contextConvId;
                    const conv = state.conversations.find(c => c.id == convId);
                    if (!conv) return;
                    const tagId = parseInt(tagEl.dataset.tagId);
                    const action = tagEl.dataset.tagAction;
                    (action === 'add' ? api('add_conversation_tag', {
                        conversation_id: convId,
                        tag_id: tagId
                    }) : api('remove_conversation_tag', {
                        conversation_id: convId,
                        tag_id: tagId
                    }))
                    .then(function(result) {
                        if (result && result.success) {
                            const tags = conv.tags || [];
                            const tagInfo = state.availableTags.find(function(t) {
                                return t.id === tagId;
                            });
                            if (action === 'add' && tagInfo) {
                                tags.push(tagInfo);
                                conv.tags = tags;
                            } else {
                                conv.tags = tags.filter(function(t) {
                                    return t.id !== tagId;
                                });
                            }
                            renderConversations();
                            if (state.activeConversationId == convId && state.activeConversation) {
                                state.activeConversation.tags = conv.tags;
                            }
                            fillTagsSubmenu(conv);
                            DOM.contextMenu.querySelector('.cb-ctx-has-sub[data-action="tags"]').classList.add('cb-ctx-sub-open');
                        }
                    });
                    return;
                }

                const priorityEl = e.target.closest('[data-priority]');
                if (priorityEl) {
                    const convId = state.contextConvId;
                    const conv = state.conversations.find(c => c.id == convId);
                    if (!conv) return;
                    const priority = priorityEl.dataset.priority;
                    DOM.contextMenu.classList.remove('show');
                    conv.priority = priority || null;
                    api('set_priority', {
                        conversation_id: convId,
                        priority: priority
                    });
                    renderConversations();
                    if (state.activeConversationId == convId && state.activeConversation) {
                        state.activeConversation.priority = priority || null;
                    }
                    return;
                }

                const actionEl = e.target.closest('[data-action]');
                if (!actionEl) return;
                const action = actionEl.dataset.action;
                if (action === 'priority' || action === 'tags') return;
                const convId = state.contextConvId;
                DOM.contextMenu.classList.remove('show');
                if (!convId) return;

                const conv = state.conversations.find(c => c.id == convId);
                if (!conv) return;

                switch (action) {
                    case 'assign':
                        loadConversation(convId).then(() => ChatApp.takeOver());
                        break;
                    case 'transfer':
                        state.transferConvId = convId;
                        jQuery('#cb-transfer-staff-select').val('').selectpicker('refresh');
                        document.getElementById('cb-transfer-modal').style.display = '';
                        break;
                    case 'export-csv':
                        window.location.href = CONFIG.baseUrl + 'export_conversation?conversation_id=' + convId + '&format=csv';
                        break;
                    case 'export-pdf':
                        window.open(CONFIG.baseUrl + 'export_conversation?conversation_id=' + convId + '&format=pdf', '_blank');
                        break;
                    case 'end':
                        if (confirm(<?= json_encode(_l('chatbot_confirm_end')) ?>)) {
                            loadConversation(convId).then(() => ChatApp.endConversation());
                        }
                        break;
                    case 'delete':
                        if (confirm(<?= json_encode(_l('chatbot_confirm_delete_conv')) ?>)) {
                            loadConversation(convId).then(() => ChatApp.deleteConversation());
                        }
                        break;
                }
            });

            // Transfer modal - init AJAX staff search
            init_ajax_search('prchat_staff', '#cb-transfer-staff-select.ajax-search', undefined, '<?= admin_url("prchat/Chatbot_Admin/ajaxSearchChatbotStaff") ?>');
            document.getElementById('cb-transfer-close').addEventListener('click', () => {
                document.getElementById('cb-transfer-modal').style.display = 'none';
            });
            document.getElementById('cb-transfer-cancel').addEventListener('click', () => {
                document.getElementById('cb-transfer-modal').style.display = 'none';
            });
            document.getElementById('cb-transfer-confirm').addEventListener('click', async () => {
                const staffId = jQuery('#cb-transfer-staff-select').val();
                if (!staffId) {
                    alert_float('warning', 'Please select a staff member');
                    return;
                }
                const convId = state.transferConvId;
                const result = await api('transfer_conversation', {
                    conversation_id: convId,
                    staff_id: staffId
                });
                document.getElementById('cb-transfer-modal').style.display = 'none';
                if (result.success) {
                    alert_float('success', result.message || 'Transferred');
                    const conv = state.conversations.find(c => c.id == convId);
                    if (conv) {
                        conv.status = 'human_handling';
                        conv.is_escalated = false;
                        conv.assigned_staff_id = staffId;
                        conv.assigned_staff_name = result.staff_name;
                    }
                    if (state.activeConversationId == convId) {
                        state.activeConversation.status = 'human_handling';
                        state.activeConversation.is_escalated = false;
                        state.activeConversation.assigned_staff_id = staffId;
                        state.activeConversation.assigned_staff_name = result.staff_name;
                        state.canReply = false;
                        renderChatActions();
                        renderInputArea();
                    }
                    renderConversations();
                } else {
                    alert_float('danger', result.error || 'Transfer failed');
                }
            });

            // Close context menu on click outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.cb-context-menu')) {
                    DOM.contextMenu.classList.remove('show');
                    DOM.contextMenu.querySelectorAll('.cb-ctx-has-sub').forEach(function(el) {
                        el.classList.remove('cb-ctx-sub-open');
                    });
                }
            });
            document.addEventListener('scroll', () => {
                DOM.contextMenu.classList.remove('show');
                DOM.contextMenu.querySelectorAll('.cb-ctx-has-sub').forEach(function(el) {
                    el.classList.remove('cb-ctx-sub-open');
                });
            }, true);

            // Send message
            DOM.sendBtn.addEventListener('click', () => sendMessage(DOM.messageInput.value));

            DOM.messageInput.addEventListener('keydown', e => {
                if (DOM.cannedDropdown.classList.contains('show')) {
                    const filtered = DOM.cannedDropdown._filtered || [];
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        state.selectedCannedIndex = Math.min(state.selectedCannedIndex + 1, filtered.length - 1);
                        updateCannedSelection();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        state.selectedCannedIndex = Math.max(state.selectedCannedIndex - 1, 0);
                        updateCannedSelection();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        selectCannedResponse(state.selectedCannedIndex);
                    } else if (e.key === 'Escape') {
                        hideCannedDropdown();
                    }
                    return;
                }
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage(DOM.messageInput.value);
                }
            });

            let _staffTypingTimer = null;
            let _lastStaffTypingSent = 0;
            DOM.messageInput.addEventListener('input', e => {
                autoResizeTextarea(e.target);
                const value = e.target.value;
                if (value.startsWith('/')) showCannedDropdown(value.substring(1));
                else hideCannedDropdown();

                if (state.activeConversation && value.trim()) {
                    clearTimeout(_staffTypingTimer);
                    const now = Date.now();
                    if (now - _lastStaffTypingSent > 3000) {
                        _lastStaffTypingSent = now;
                        api('staff_typing', {
                            conversation_id: state.activeConversation.id,
                            typing: '1'
                        });
                    }
                    _staffTypingTimer = setTimeout(() => {
                        api('staff_typing', {
                            conversation_id: state.activeConversation.id,
                            typing: '0'
                        });
                    }, 3000);
                }
            });

            // File attach + paste
            const cbAttachBtn = document.getElementById('cb-attach-btn');
            const cbFileInput = document.getElementById('cb-file-input');
            if (cbAttachBtn && cbFileInput) {
                cbAttachBtn.addEventListener('click', function() {
                    cbFileInput.click();
                });
                cbFileInput.addEventListener('change', function(ev) {
                    if (ev.target.files && ev.target.files.length > 0) {
                        uploadChatbotFile(ev.target.files[0]);
                        ev.target.value = '';
                    }
                });
            }

            DOM.messageInput.addEventListener('paste', function(ev) {
                var cd = ev.clipboardData || window.clipboardData;
                if (!cd) return;
                var imgFile = null;
                if (cd.items) {
                    for (var i = 0; i < cd.items.length; i++) {
                        if (cd.items[i].type.indexOf('image') !== -1) {
                            imgFile = cd.items[i].getAsFile();
                            break;
                        }
                    }
                }
                if (!imgFile && cd.files) {
                    for (var i = 0; i < cd.files.length; i++) {
                        if (cd.files[i].type.indexOf('image') !== -1) {
                            imgFile = cd.files[i];
                            break;
                        }
                    }
                }
                if (imgFile) {
                    ev.preventDefault();
                    var ext = {
                        'image/png': 'png',
                        'image/jpeg': 'jpg',
                        'image/gif': 'gif',
                        'image/webp': 'webp'
                    } [imgFile.type] || 'png';
                    var file = new File([imgFile], 'pasted-image-' + Date.now() + '.' + ext, {
                        type: imgFile.type
                    });
                    uploadChatbotFile(file);
                }
            });

            async function uploadChatbotFile(file) {
                if (!state.activeConversation || !state.canReply) {
                    alert_float('warning', 'Select a conversation first');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert_float('danger', 'File too large (max 5MB)');
                    return;
                }
                alert_float('info', '<?= _l("chat_msg_sending"); ?>');
                var formData = new FormData();
                formData.append('userfile', file);
                formData.append('staff_upload', '1');
                formData.append('conversation_id', state.activeConversation.id);
                formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
                try {
                    var resp = await fetch(admin_url + 'prchat/Chatbot_Controller/upload', {
                        method: 'POST',
                        body: formData
                    });
                    var data = await resp.json();
                    if (data.success && data.file_url) {
                        sendMessage(data.file_url);
                    } else {
                        alert_float('danger', data.error || 'Upload failed');
                    }
                } catch (e) {
                    alert_float('danger', 'Upload failed');
                }
            }

            // Canned item click
            DOM.cannedDropdown.addEventListener('click', e => {
                const item = e.target.closest('.cb-canned-item');
                if (item) selectCannedResponse(parseInt(item.dataset.index));
            });

            // Add note
            DOM.addNoteBtn.addEventListener('click', addNote);
            DOM.noteInput.addEventListener('keydown', e => {
                if (e.key === 'Enter' && e.ctrlKey) {
                    e.preventDefault();
                    addNote();
                }
            });

            // Close canned on outside click
            document.addEventListener('click', e => {
                if (!DOM.cannedDropdown.contains(e.target) && e.target !== DOM.messageInput) hideCannedDropdown();
            });

            // Scroll to bottom button
            DOM.messages.addEventListener('scroll', () => {
                const atBottom = DOM.messages.scrollHeight - DOM.messages.scrollTop - DOM.messages.clientHeight < 50;
                state.isAtBottom = atBottom;
                DOM.scrollBottom.classList.toggle('visible', !atBottom);
            });
            DOM.scrollBottom.addEventListener('click', scrollToBottom);

            // Search in conversation
            DOM.convSearchInput.addEventListener('input', function() {
                state.convSearchQuery = this.value.trim();
                state.convSearchIndex = 0;
                renderMessages();
            });

            DOM.convSearchPrev.addEventListener('click', function() {
                if (state.convSearchIndex > 0) {
                    state.convSearchIndex--;
                    updateConvSearchUI();
                }
            });
            DOM.convSearchNext.addEventListener('click', function() {
                const highlights = DOM.messages.querySelectorAll('.cb-search-highlight');
                if (state.convSearchIndex < highlights.length - 1) {
                    state.convSearchIndex++;
                    updateConvSearchUI();
                }
            });

            function closeConvSearchBar() {
                if (DOM.convSearchWrap) DOM.convSearchWrap.style.display = 'none';
                if (DOM.searchInConvBtn) DOM.searchInConvBtn.classList.remove('active');
                state.convSearchQuery = '';
                state.convSearchIndex = 0;
                if (DOM.convSearchInput) DOM.convSearchInput.value = '';
                renderMessages();
            }
            DOM.convSearchClear.addEventListener('click', closeConvSearchBar);
            DOM.convSearchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeConvSearchBar();
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const highlights = DOM.messages.querySelectorAll('.cb-search-highlight');
                    if (state.convSearchIndex < highlights.length - 1) {
                        state.convSearchIndex++;
                        updateConvSearchUI();
                    } else if (highlights.length > 0) {
                        state.convSearchIndex = 0;
                        updateConvSearchUI();
                    }
                }
            });
            if (DOM.searchInConvBtn) {
                DOM.searchInConvBtn.addEventListener('click', function() {
                    const wrap = DOM.convSearchWrap;
                    const isShown = wrap && wrap.style.display !== 'none';
                    if (wrap) wrap.style.display = isShown ? 'none' : 'flex';
                    DOM.searchInConvBtn.classList.toggle('active', !isShown);
                    if (!isShown && DOM.convSearchInput) setTimeout(function() {
                        DOM.convSearchInput.focus();
                    }, 50);
                });
            }

            // Info panel toggle (panel-open = panel is visible; icon reflects current state after toggle)
            if (DOM.toggleInfoPanel && DOM.infoPanel) {
                const toggleIcon = document.getElementById('cb-toggle-icon');
                DOM.toggleInfoPanel.addEventListener('click', function() {
                    DOM.infoPanel.classList.toggle('cb-info-panel-hidden');
                    var isPanelNowHidden = DOM.infoPanel.classList.contains('cb-info-panel-hidden');
                    DOM.toggleInfoPanel.classList.toggle('panel-open', !isPanelNowHidden);
                    if (toggleIcon) {
                        toggleIcon.className = isPanelNowHidden ? 'fa fa-chevron-right' : 'fa fa-chevron-left';
                    }
                    if (window.innerWidth <= 768) {
                        var container = document.querySelector('.chatbot-live-chat');
                        if (!isPanelNowHidden) {
                            container.classList.add('cb-mobile-info-active');
                            container.classList.remove('cb-mobile-chat-active');
                        } else {
                            container.classList.remove('cb-mobile-info-active');
                            container.classList.add('cb-mobile-chat-active');
                        }
                    }
                });
            }

            // Dark mode toggle (persisted in localStorage)
            var cbDark = localStorage.getItem('prChatThemeSupportMode') === 'dark';

            function applyCbDarkMode() {
                var container = document.querySelector('.chatbot-live-chat');
                if (container) {
                    container.classList.toggle('cb-dark', cbDark);
                }
                if (DOM.themeToggle) {
                    DOM.themeToggle.classList.toggle('active', cbDark);
                    var icon = DOM.themeToggle.querySelector('i');
                    if (icon) icon.className = cbDark ? 'fa fa-sun' : 'fa fa-moon';
                }
            }
            applyCbDarkMode();
            if (DOM.themeToggle) {
                DOM.themeToggle.addEventListener('click', function() {
                    cbDark = !cbDark;
                    try {
                        localStorage.setItem('prChatThemeSupportMode', cbDark ? 'dark' : 'light');
                    } catch (e) {}
                    applyCbDarkMode();
                });
            }

            // Sound toggle (persisted in localStorage)
            function applySoundToggleUI() {
                DOM.soundToggle.classList.toggle('active', state.soundEnabled);
                DOM.soundToggle.classList.toggle('muted', !state.soundEnabled);
                DOM.soundToggle.querySelector('i').className = state.soundEnabled ? 'fa fa-volume-up' : 'fa fa-volume-off';
            }
            applySoundToggleUI();
            DOM.soundToggle.addEventListener('click', () => {
                state.soundEnabled = !state.soundEnabled;
                try {
                    localStorage.setItem('prchat_sound_enabled', state.soundEnabled ? '1' : '0');
                } catch (e) {}
                applySoundToggleUI();
            });

            // Desktop notification toggle
            DOM.desktopToggle.addEventListener('click', async () => {
                if (!state.desktopNotifEnabled) {
                    if ('Notification' in window) {
                        const perm = await Notification.requestPermission();
                        if (perm === 'granted') {
                            state.desktopNotifEnabled = true;
                            DOM.desktopToggle.classList.add('active');
                            // Save preference to database
                            fetch('<?= admin_url("prchat/Chatbot_Admin/save_desktop_notification_preference") ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    [CONFIG.csrfName]: CONFIG.csrfHash,
                                    enabled: '1'
                                })
                            });
                        } else if (perm === 'denied') {
                            alert_float('warning', <?= json_encode(_l('chatbot_desktop_notif_blocked')) ?>);
                        }
                    }
                } else {
                    state.desktopNotifEnabled = false;
                    DOM.desktopToggle.classList.remove('active');
                    // Save preference to database
                    fetch('<?= admin_url("prchat/Chatbot_Admin/save_desktop_notification_preference") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            [CONFIG.csrfName]: CONFIG.csrfHash,
                            enabled: '0'
                        })
                    });
                }
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', cleanup);
        }

        function cleanup() {
            // Clear timers
            if (state.refreshTimer) clearInterval(state.refreshTimer);
            if (titleFlashInterval) clearInterval(titleFlashInterval);

            // Don't disconnect Pusher during page unload - browser handles WebSocket closure
            // Attempting to unsubscribe/disconnect during beforeunload causes errors in pusher.min.js
            // because the connection is already closing
        }

        // ===========================================
        // CREATE LEAD (Perfex CRM Modal)
        // ===========================================
        function createLead() {
            if (!state.activeConversation) return;
            const conv = state.activeConversation;
            const info = conv.visitor_info || {};

            if (typeof init_lead_modal_data === 'function') {
                init_lead_modal_data(undefined, undefined, false);
            } else {
                alert_float('warning', CONFIG.i18n.leadModalNA);
                return;
            }

            $('#lead-modal').off('shown.bs.modal.chatbot').on('shown.bs.modal.chatbot', function() {
                var $m = $(this);
                if (conv.visitor_name) $m.find('input[name="name"]').val(conv.visitor_name);
                if (conv.visitor_email) $m.find('input[name="email"]').val(conv.visitor_email);
                if (info.phone) $m.find('input[name="phonenumber"]').val(info.phone);

                var desc = CONFIG.i18n.leadFromConv + conv.id + '\n\n';
                if (info.ip) desc += 'IP: ' + info.ip + '\n';
                if (info.referer) desc += 'Referrer: ' + info.referer + '\n';

                if (typeof tinymce !== 'undefined') {
                    var editor = tinymce.get('description');
                    if (editor) editor.setContent(desc.replace(/\n/g, '<br>'));
                } else {
                    $m.find('textarea[name="description"]').val(desc);
                }
                $m.find('select').trigger('change');
            });
        }

        // ===========================================
        // PUBLIC API
        // ===========================================
        function toggleConvertMenu() {
            const menu = document.getElementById('cb-convert-menu');
            if (menu) menu.classList.toggle('show');
        }

        // Close convert menu on outside click
        document.addEventListener('click', e => {
            const menu = document.getElementById('cb-convert-menu');
            if (menu && menu.classList.contains('show') && !e.target.closest('.cb-convert-dropdown')) {
                menu.classList.remove('show');
            }
        });

        window.ChatApp = {
            takeOver,
            deleteConversation,
            endConversation,
            convertToLead,
            convertToClient,
            createFollowupTask,
            createLead,
            toggleConvertMenu
        };

        // ===========================================
        // INIT
        // ===========================================
        async function init() {
            cacheDom();
            initEventHandlers();
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            });
            await Promise.all([loadAvailableTags(), loadConversations(), loadCannedResponses()]);
            renderTagFilterOptions();
            initPusher();

            // Check URL for conversation ID, otherwise auto-select first
            const urlMatch = window.location.pathname.match(/live_chat\/(\d+)/);
            const urlParams = new URLSearchParams(window.location.search);
            const convParam = urlParams.get('conv');

            if (urlMatch) {
                loadConversation(urlMatch[1]);
            } else if (convParam) {
                loadConversation(convParam);
            }
            // uncomment this if you want to auto-select the first conversation
            // else if (state.conversations.length > 0) {
            //     loadConversation(state.conversations[0].id);
            // }

            // Auto-refresh conversation list
            state.refreshTimer = setInterval(loadConversations, CONFIG.refreshInterval);

            // Load desktop notification preference from database (staff meta)
            if ('Notification' in window && Notification.permission === 'granted') {
                // Check staff meta value (default to enabled if not set)
                var staffPreference = '<?= get_staff_meta(get_staff_user_id(), "chatbot_desktop_notifications_enabled") ?>';
                if (staffPreference !== '0') {
                    state.desktopNotifEnabled = true;
                    DOM.desktopToggle.classList.add('active');
                }
            }
        }

        init();
    })();
</script>

</body>

</html>

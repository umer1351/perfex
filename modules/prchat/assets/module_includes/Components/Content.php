<div class="content">
    <div id="sharedFiles">
        <i class="fa fa-times-circle" aria-hidden="true"></i>
        <div class="history_slider">
            <!-- Message and files history -->
        </div>
    </div>
    <div class="chat_group_options">
        <!-- Group options  -->
    </div>
    <div class="contact-profile">

        <svg onclick="chatBackMobile()" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_back'); ?>" class="chat_back_mobile" viewBox="0 0 24 24">
            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" />
        </svg>
        <div class="profile-wrapper">
            <img src="" class="img img-responsive staff-profile-image-small" alt="" />
            <p onclick="toggleProfileDropdown(this)" class="staff-name-dropdown">
                <span class="staff-name-text"></span>
                <i class="fa fa-chevron-down dropdown-arrow"></i>
            </p>
            <div class="profile-dropdown" id="profile-dropdown">
                <ul>
                    <li><a href="#" class="chatActionMembers-option" data-action="view-profile" id="dropdown-view-profile"><i class="fa fa-user"></i> <?= _l('chat_view_profile'); ?></a></li>
                    <li><a href="#" class="chatActionMembers-option" data-action="add-task" onclick="chatNewTask(this); return false;"><i class="fa fa-tasks"></i> <?= _l('chat_associate_task'); ?></a></li>
                </ul>
            </div>
            <div class="profile-actions">
                <?php if (get_option('chat_calls_video_enabled') == '1') : ?>
                <button type="button" class="profile-action-btn chat-action-video-call" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?php echo _l('chat_start_video_call'); ?>">
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                </button>
                <?php endif; ?>
                <?php if (get_option('chat_staff_calls_enabled') == '1') : ?>
                <button type="button" class="profile-action-btn chat-action-voice-call" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?php echo _l('chat_start_voice_call'); ?>">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                </button>
                <?php endif; ?>
                <button type="button" class="profile-action-btn chat-header-search" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_search_msg_txt'); ?>">
                    <i class="fa fa-search" aria-hidden="true"></i>
                </button>
                <button type="button" class="profile-action-btn chat-action-client-notes chat-action-client-notes-header" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_client_notes'); ?>">
                    <i class="fa fa-sticky-note" aria-hidden="true"></i>
                </button>
            </div>
        </div>

    </div>
    <div class="messages" onscroll="loadMessages(this)">
        <div class="messages-skeleton"></div>
        <div class="userIsTyping">
            <div class="typing-dots"><span></span><span></span><span></span></div>
            <span class="typing-text"></span>
        </div>
        <ul>
        </ul>
    </div>
    <div class="group_messages" onscroll="loadGroupMessages(this)">
        <div class="messages-skeleton"></div>
        <div class="chat_group_messages">
            <ul>
            </ul>
        </div>
    </div>
    <?php if (isClientsEnabled()) : ?>
        <div class="client_messages" id="">
            <div class="messages-skeleton"></div>
            <div class="chat_client_messages">
                <!-- Client messages -->
                <ul>
                </ul>
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
        </div>
    <?php endif; ?>
    <!-- Staff -->
    <?php loadChatComponent('StaffForm'); ?>
    <!-- Groups -->
    <?php loadChatComponent('GroupsForm'); ?>
    <!-- Clients -->
    <?php loadChatComponent('ClientsForm'); ?>

</div>

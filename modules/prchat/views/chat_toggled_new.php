<?php

/**
 * Vue.js implementation with full chat functionality
 * Uses existing backend methods with modern frontend
 */

defined('BASEPATH') or exit('No direct script access allowed');
?>

<div id="chatApp" class="perfex-chat-system" v-cloak>

  <!-- FLOATING CIRCLE BUTTON (when minimized) -->
  <div v-if="showMainPanel && isMinimized" class="chat-fab"
    :style="{ right: mainPanelPos.right + 'px', bottom: mainPanelPos.bottom + 'px' }" @mousedown="startFabDrag"
    @touchstart.prevent="startFabDragTouch">
    <i class="fa fa-comments"></i>
    <span v-if="totalUnreadCount > 0" class="chat-fab-badge">{{ totalUnreadCount }}</span>
  </div>

  <!-- MAIN CHAT PANEL -->
  <div v-if="showMainPanel" class="main-chat-panel" :class="{ 'minimized': isMinimized }" :style="mainPanelStyle">

    <!-- HEADER SECTION (draggable) -->
    <div class="chat-header" @mousedown="startMainPanelDrag" @dblclick="toggleMinimize">
      <div class="header-content">
        <div class="header-left">
          <i class="fa fa-comments header-icon"></i>
          <div class="header-title-wrap">
            <span class="company-name"><?php echo get_option('companyname'); ?></span>
            <span class="header-online-text" v-if="otherOnlineUsers > 0">
              <span class="header-online-dot"></span>
              {{ otherOnlineUsers }} <?php echo _l('chat_online_users'); ?>
            </span>
          </div>
        </div>
        <div class="header-actions">
          <div class="filter-dropdown-wrap" @click.stop>
            <button @click.stop="showFilterDropdown = !showFilterDropdown" class="header-btn filter-btn"
              data-tip="<?php echo _l('chat_filter_all'); ?>">
              <i class="fa fa-filter"></i>
              <span v-if="activeFilter !== 'all'" class="filter-active-dot"></span>
            </button>
            <div v-if="showFilterDropdown" class="filter-dropdown">
              <div v-for="f in filterOptions" :key="f.value"
                @click="setStatusFilter(f.value); showFilterDropdown = false" class="filter-dropdown-item"
                :class="{ active: activeFilter === f.value }">
                <span v-if="f.dot" class="filter-dot" :class="f.value"></span>
                <span v-else class="filter-dot-placeholder"></span>
                <span class="filter-label">{{ f.label }}</span>
                <span class="filter-count">{{ getFilterCount(f.value) }}</span>
                <i v-if="activeFilter === f.value" class="fa fa-check filter-check"></i>
              </div>
            </div>
          </div>
          <button @click.stop="toggleMinimize" class="header-btn minimize-btn" data-tip="<?php echo _l('minimize'); ?>">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- SEARCH BAR -->
    <div v-if="searchVisible && !isMinimized" class="search-section">
      <input v-model="searchQuery" @input="debouncedFilterUsers" type="text" class="search-input"
        placeholder="<?php echo _l('chat_search_chat_members'); ?>">
    </div>

    <!-- USER LIST -->
    <div v-if="!isMinimized" class="users-section">
      <div class="users-list">
        <div v-for="user in filteredUsers" :key="user.staffid" @click="openChat(user)"
          @contextmenu="showContextMenu($event, user)" class="user-item" :class="{
                         'online': user.status === 'online',
                         'away': user.status === 'away',
                         'busy': user.status === 'busy',
                         'offline': user.status === 'offline',
                         'has-unread': user.unreadCount > 0
                     }">
          <div class="user-avatar-wrap">
            <img :src="getUserAvatar(user)" :alt="sanitizeAttr(user.firstname + ' ' + user.lastname)"
              class="user-avatar">
            <span class="status-indicator avatar-status" :class="user.status"></span>
            <span v-if="user.unreadCount > 0" class="unread-badge">{{ user.unreadCount }}</span>
          </div>
          <div class="user-info">
            <span class="user-name">{{ user.firstname }} {{ user.lastname }}</span>
            <span class="user-role">{{ user.role }}</span>
            <div class="user-activity">
              <span v-if="user.isTyping" class="typing-preview"><?php echo _l('chat_typing'); ?></span>
              <span v-else-if="user.lastMessage" class="last-message">{{ getLastMessagePreview(user.lastMessage,
                user.lastMessageSenderId)
                }}</span>
              <span v-else-if="user.lastSeen" class="last-seen"><?php echo _l('chat_last_seen'); ?> {{
                formatTimeAgo(user.lastSeen) }}</span>
              <span v-else class="no-activity"><?php echo _l('chat_no_recent_activity'); ?></span>
            </div>
          </div>
          <div class="user-meta">
            <i v-if="isUserPinned(user)" class="fa fa-thumb-tack user-pin-icon"
              title="<?php echo _l('chat_pinned'); ?>"></i>
            <i v-if="isUserMuted(user)" class="fa fa-bell-slash user-mute-icon"
              title="<?php echo _l('chat_muted'); ?>"></i>
          </div>
        </div>
        <!-- Empty state when filter has no results -->
        <div v-if="filteredUsers.length === 0" class="filter-empty-state">
          <i class="fa fa-user-times"></i>
          <span><?php echo _l('chat_no_users_found'); ?></span>
        </div>
      </div>
    </div>

    <!-- PANEL FOOTER -->
    <div v-if="!isMinimized" class="panel-footer">
      <button @click.stop="toggleSearch" class="footer-btn" :class="{ active: searchVisible }"
        data-tip="<?php echo _l('search'); ?>">
        <i class="fa fa-search"></i>
      </button>
      <button @click.stop="openHistoryModal" class="footer-btn"
        data-tip="<?php echo _l('chat_messages_history_title'); ?>">
        <i class="fa fa-history"></i>
      </button>
      <button @click.stop="toggleNotifications" class="footer-btn" :class="{ active: notificationsEnabled }"
        data-tip="<?php echo _l('chat_sound_notifications'); ?>">
        <i class="fa" :class="notificationsEnabled ? 'fa-bell' : 'fa-bell-slash'"></i>
      </button>
      <button @click.stop="toggleFloatingNotifications" class="footer-btn"
        :class="{ 'has-floating-unread': floatingUnreadCount > 0 }" data-tip="<?php echo _l('chat_notifications'); ?>">
        <i class="fa fa-envelope"></i>
        <span v-if="floatingUnreadCount > 0" class="footer-badge">{{ floatingUnreadCount }}</span>
      </button>
      <button @click.stop="toggleDarkMode" class="footer-btn" :class="{ active: isDarkMode }"
        data-tip="<?php echo _l('chat_dark_mode'); ?>">
        <i class="fa" :class="isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
      </button>
      <a :href="fullChatViewUrl" class="footer-btn" data-tip="<?php echo _l('chat_full_chat_view'); ?>">
        <i class="fa fa-expand"></i>
      </a>
    </div>

  </div>

  <!-- INDIVIDUAL CHAT WINDOWS -->
  <div class="chat-windows-container">

    <div v-for="chat in activeChats" :key="'chat-' + chat.userId" class="chat-window prchat-toggled-chat-window"
      :data-user-id="String(chat.userId)" :class="{
                 'minimized': chat.minimized,
                 'focused': chat.focused,
                 'has-unread': chat.unreadCount > 0
             }" :style="getChatWindowStyle(chat)" @dragenter.prevent="handleDragEnter($event, chat)"
      @dragover.prevent="handleDragOver($event, chat)" @dragleave.prevent="handleDragLeave($event, chat)"
      @drop.prevent="handleDrop($event, chat)">
      <!-- UNREAD BADGE (visible when minimized) -->
      <span v-if="chat.minimized && chat.unreadCount > 0" class="chat-window-badge">{{ chat.unreadCount }}</span>
      <!-- CHAT HEADER -->
      <div class="chat-window-header" @mousedown="startDrag($event, chat); bringToFront(chat)"
        @dblclick="toggleChatWindow(chat)" @click="markMessagesAsRead(chat.userId); bringToFront(chat)">
        <div class="chat-user-info">
          <div class="chat-avatar-wrapper" :class="'status-' + chat.user.status">
            <img :src="getUserAvatar(chat.user)" :alt="sanitizeAttr(chat.user.firstname + ' ' + chat.user.lastname)"
              class="chat-avatar">
          </div>
          <div class="chat-user-details">
            <span class="chat-user-name">{{ chat.user.firstname }} {{ chat.user.lastname }}</span>
          </div>
        </div>
        <div class="chat-window-actions">
          <?php if (get_option('chat_staff_calls_enabled') == '1'): ?>
            <button @click.stop="startCall(chat.userId)" class="chat-action-btn call-btn"
              data-tip="<?php echo _l('chat_start_voice_call'); ?>">
              <i class="fa fa-phone"></i>
            </button>
          <?php endif; ?>
          <?php if (get_option('chat_calls_video_enabled') == '1'): ?>
            <button v-if="hasVideoSupport" @click.stop="startVideoCall(chat.userId)"
              class="chat-action-btn video-call-btn" data-tip="<?php echo _l('chat_start_video_call'); ?>">
              <i class="fa fa-video-camera"></i>
            </button>
          <?php endif; ?>
          <button @click.stop="toggleChatWindow(chat)" class="chat-action-btn"
            :data-tip="chat.minimized ? '<?php echo _l('chat_expand'); ?>' : '<?php echo _l('minimize'); ?>'">
            <i class="fa" :class="chat.minimized ? 'fa-plus' : 'fa-minus'"></i>
          </button>
          <button @click.stop="closeChat(chat)" class="chat-action-btn close-btn" data-tip="<?php echo _l('close'); ?>">
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>

      <!-- DROP OVERLAY -->
      <div v-if="chat.isDragOver" class="chat-drop-overlay">
        <div class="chat-drop-overlay-content">
          <i class="fa fa-cloud-upload"></i>
          <span><?php echo _l('chat_drop_files_here'); ?></span>
        </div>
      </div>

      <!-- CHAT MESSAGES -->
      <div v-if="!chat.minimized" class="chat-messages" :id="'chat-messages-' + chat.userId"
        @scroll="handleScroll($event, chat)" @click="markMessagesAsRead(chat.userId); bringToFront(chat)"
        @mousedown="bringToFront(chat)" ref="chatMessages">

        <!-- INITIAL LOADING STATE -->
        <div v-if="chat.isLoading" class="chat-initial-loading">
          <div class="chat-loading-dots">
            <span></span><span></span><span></span>
          </div>
        </div>

        <!-- LOADING MORE MESSAGES (scroll up) -->
        <div v-if="chat.loadingMore && !chat.isLoading" class="loading-more">
          <i class="fa fa-spinner fa-spin"></i> <?php echo _l('loading'); ?>...
        </div>

        <!-- MESSAGES -->
        <template v-for="(message, msgIndex) in chat.messages" :key="'msg-' + chat.userId + '-' + message.id">
          <!-- Date separator -->
          <div v-if="shouldShowDateSeparator(chat.messages, msgIndex)" class="toggle-date-separator">
            <span>{{ formatDateSeparator(message.time_sent) }}</span>
          </div>
          <div v-if="isCallMessage(message.message)" class="message call-system-message">
            <div class="call-summary-bubble">
              <i :class="getCallIcon(message.message)"></i>
              <span v-html="renderCallLabel(message.message)"></span>
              <span class="call-summary-time">{{ formatTime(message.time_sent) }}</span>
            </div>
          </div>
          <div v-else class="message" :class="{
                         'own-message': message.sender_id == currentUserId,
                         'other-message': message.sender_id != currentUserId
                     }">
            <div class="message-content">
              <img :src="getMessageAvatar(message)" :alt="sanitizeAttr(message.sender_name)" class="message-avatar">
              <div class="message-bubble" :class="{ 'has-reactions': hasMessageReactions(message) }">
                <div class="message-text" v-html="formatMessage(message.message)" @click="handleContentClick($event)">
                </div>
                <div class="message-meta">
                  <span v-if="message.edited || message.edited_at"
                    class="prchat-edited-tag">(<?php echo _l('chat_edited'); ?>)</span>
                  <span class="message-time">{{ formatTime(message.time_sent) }}</span>
                  <i v-if="message.sender_id == currentUserId" class="message-status fa" :class="{
                                        'fa-clock text-muted': message.status === 'pending',
                                        'fa-exclamation-triangle text-danger': message.status === 'failed',
                                        'fa-check': (message.status === 'sent' && !message.viewed) || (!message.status && !message.viewed),
                                        'fa-check-double text-primary': (message.status === 'sent' && message.viewed) || (!message.status && message.viewed)
                                    }" data-toggle="tooltip" data-placement="top"
                    :title="getMessageStatusTitle(message)"
                    @click="message.status === 'failed' ? retryMessage(chat, message) : null"></i>
                </div>
                <div class="message-reactions" v-if="hasMessageReactions(message)">
                  <button type="button" class="reaction-pill" v-for="emoji in getReactionEmojis(message)"
                    :key="'rx-' + message.id + '-' + emoji" :class="{ reacted: isMessageReactedByMe(message, emoji) }"
                    data-toggle="tooltip" data-placement="top" :data-original-title="getReactionTooltip(message, emoji)"
                    :title="getReactionTooltip(message, emoji)"
                    @click.stop="isMessageReactedByMe(message, emoji) ? sendReaction(message, emoji) : null">
                    <span class="reaction-emoji">{{ emoji }}</span>
                  </button>
                </div>
              </div>
              <div class="message-options" :class="{ 'active': activeMessageOptions === message.id }"
                @mouseenter="clearHideTimeout" @mouseleave="scheduleHide">
                <button @click.stop="toggleMessageOptions(message, $event)" class="options-btn">
                  <i class="fa fa-ellipsis-v"></i>
                </button>

                <!-- OPTIONS DROPDOWN -->
                <div v-if="activeMessageOptions === message.id" class="message-options-dropdown"
                  @mouseenter="clearHideTimeout" @mouseleave="scheduleHide">
                  <button @click.stop="openReactionPicker(message, $event)" class="option-item react">
                    <i class="fa fa-smile"></i> <?php echo _l('chat_react'); ?>
                  </button>
                  <button @click.stop="replyToMessage(message)" class="option-item">
                    <i class="fa fa-reply"></i> <?php echo _l('reply'); ?>
                  </button>
                  <button @click.stop="forwardMessage(message)" class="option-item">
                    <i class="fa fa-share"></i> <?php echo _l('chat_forward'); ?>
                  </button>
                  <button @click.stop="copyMessage(message)" class="option-item">
                    <i class="fa fa-copy"></i> <?php echo _l('copy'); ?>
                  </button>
                  <button v-if="message.sender_id == currentUserId" @click.stop="editMessage(message, chat)"
                    class="option-item">
                    <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                  </button>
                  <button v-if="canDeleteMessages && message.sender_id == currentUserId"
                    @click.stop="deleteMessage(message, chat)" class="option-item delete">
                    <i class="fa fa-trash"></i> <?php echo _l('delete'); ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- TYPING INDICATOR -->
        <div v-if="chat.isTyping" class="typing-indicator">
          <img :src="getUserAvatar(chat.user)" :alt="sanitizeAttr(chat.user.firstname)" class="typing-avatar">
          <div class="typing-bubble">
            <div class="typing-dots">
              <span></span><span></span><span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- CHAT INPUT -->
      <div v-if="!chat.minimized" class="chat-input-section" @mousedown="bringToFront(chat)">
        <!-- EDIT INDICATOR -->
        <div v-if="chat.editingMessageId" class="edit-indicator">
          <div class="edit-info">
            <i class="fa fa-pencil"></i>
            <span><?php echo _l('chat_editing_message'); ?></span>
            <button @click="cancelEdit(chat)" class="cancel-edit-btn">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
        <!-- REPLY CONTEXT -->
        <div v-if="chat.replyingTo" class="reply-context">
          <div class="reply-info">
            <i class="fa fa-reply"></i>
            <span><?php echo _l('chat_replying_to'); ?>: {{ getReplyPreview(chat.replyingTo.message) }}</span>
            <button @click="cancelReply(chat)" class="cancel-reply-btn">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>

        <div class="chat-input-container">
          <div class="input-actions-left">
            <button @click="triggerFileUpload(chat)" class="input-action-btn" title="<?php echo _l('attach_file'); ?>">
              <i class="fa fa-paperclip"></i>
            </button>
            <button type="button" class="input-action-btn" @click="startVoiceRecord(chat)"
              title="<?php echo _l('chat_voice_message'); ?>">
              <i class="fa fa-microphone"></i>
            </button>
            <button type="button" class="input-action-btn emoji-trigger" @click="openEmojiPicker(chat, $event)"
              title="<?php echo _l('chat_emoji'); ?>">
              <i class="fa fa-smile"></i>
            </button>
          </div>
          <div class="input-textarea-wrap">
            <textarea v-model="chat.currentMessage" @keypress="handleKeyPress($event, chat)" @input="handleTyping(chat)"
              @focus="handleTextareaFocus(chat)" @paste="handlePaste($event, chat)"
              :placeholder="chat.replyingTo ? '<?php echo _l('chat_reply_to_message'); ?>' : '<?php echo _l('chat_type_a_message'); ?>...'"
              class="message-input" :ref="'messageInput' + chat.userId" rows="1"></textarea>
            <button @click="sendMessage(chat)" :disabled="!chat.currentMessage.trim()" class="send-btn-inline"
              title="<?php echo _l('send'); ?>">
              <i class="fa fa-paper-plane" style="margin-right:2px;"></i>
            </button>
          </div>
        </div>

        <!-- FILE UPLOAD (HIDDEN) - MULTIPLE FILES SUPPORT -->
        <input @change="handleFileUpload($event, chat)" type="file" class="file-input" :ref="'fileInput' + chat.userId"
          multiple style="display: none;">
      </div>
    </div>
  </div>

  <!-- FLOATING CHAT TOGGLE -->
  <div v-if="!showMainPanel && !isInitializing" @click="showMainPanel = true" class="chat-toggle-float">
    <i class="fa fa-comments"></i>
    <span v-if="totalUnreadCount > 0" class="unread-count">{{ totalUnreadCount }}</span>
  </div>

  <!-- CHAT HISTORY MODAL -->
  <div v-if="showHistoryModal" class="history-modal-overlay" @click.self="closeHistoryModal"
    @keydown.esc="closeHistoryModal" tabindex="-1">
    <div class="history-modal">
      <!-- HEADER -->
      <div class="history-modal-header">
        <div class="history-header-left">
          <i class="fa fa-history history-header-icon"></i>
          <h4><?php echo _l('chat_messages_history_title'); ?></h4>
        </div>
        <button @click="closeHistoryModal" class="history-close-btn" title="<?php echo _l('close'); ?>">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <!-- SIDEBAR + CONTENT LAYOUT -->
      <div class="history-layout">
        <!-- LEFT SIDEBAR: user list -->
        <div class="history-sidebar">
          <div class="history-sidebar-search">
            <i class="fa fa-search"></i>
            <input v-model="historyUserFilter" type="text" placeholder="<?php echo _l('chat_search_chat_members'); ?>">
          </div>
          <div class="history-user-list">
            <div v-for="user in historyFilteredUsers" :key="'hu-' + user.staffid" class="history-user-item"
              :class="{ active: selectedHistoryUserId == user.staffid }"
              @click="selectedHistoryUserId = user.staffid; onHistoryUserChange()">
              <img :src="getUserAvatar(user)" class="history-user-avatar" :alt="user.firstname">
              <div class="history-user-meta">
                <span class="history-user-name">{{ user.firstname }} {{ user.lastname }}</span>
                <span class="history-user-role">{{ user.role }}</span>
              </div>
            </div>
            <div v-if="historyFilteredUsers.length === 0" class="history-no-users">
              <?php echo _l('chat_sorry_no_data'); ?>
            </div>
          </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="history-content">
          <!-- No user selected -->
          <div v-if="!selectedHistoryUserId" class="history-empty-state">
            <i class="fa fa-comments"></i>
            <h5><?php echo _l('chat_select_user_to_view_history'); ?></h5>
            <p><?php echo _l('chat_select_user_history_description'); ?></p>
          </div>

          <!-- User selected -->
          <div v-else class="history-content-inner">
            <!-- Conversation header -->
            <div class="history-convo-header">
              <img :src="getUserAvatar(getSelectedHistoryUser())" class="history-convo-avatar">
              <div>
                <strong>{{ getSelectedUserName() }}</strong>
                <span class="history-convo-role">{{ getSelectedHistoryUser()?.role }}</span>
              </div>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="history-tabs">
              <button @click="activeHistoryTab = 'messages'"
                :class="['history-tab-btn', { active: activeHistoryTab === 'messages' }]">
                <i class="fa fa-comments"></i> <?php echo _l('chat_messages'); ?>
              </button>
              <button @click="activeHistoryTab = 'files'"
                :class="['history-tab-btn', { active: activeHistoryTab === 'files' }]">
                <i class="fa fa-file"></i> <?php echo _l('chat_files_text'); ?>
              </button>
              <button @click="activeHistoryTab = 'photos'"
                :class="['history-tab-btn', { active: activeHistoryTab === 'photos' }]">
                <i class="fa fa-image"></i> <?php echo _l('chat_photos_text'); ?>
              </button>
            </div>

            <!-- SEARCH -->
            <div class="history-search-bar">
              <i class="fa fa-search"></i>
              <input v-if="activeHistoryTab === 'messages'" v-model="historySearchQuery" @input="filterHistoryMessages"
                type="text" :placeholder="'<?php echo _l('chat_messages_search_here'); ?>'">
              <input v-if="activeHistoryTab === 'files'" v-model="fileSearchQuery" @input="filterSharedFiles"
                type="text" placeholder="<?php echo _l('chat_messages_search_here'); ?>">
              <input v-if="activeHistoryTab === 'photos'" v-model="photoSearchQuery" @input="filterSharedPhotos"
                type="text" placeholder="<?php echo _l('chat_messages_search_here'); ?>">
            </div>

            <!-- MESSAGES TAB -->
            <div v-if="activeHistoryTab === 'messages'" class="history-tab-content">
              <div v-if="historyLoading" class="history-loading">
                <i class="fa fa-spinner fa-spin"></i> <?php echo _l('loading'); ?>...
              </div>

              <div v-else-if="Array.isArray(filteredHistoryMessages) && filteredHistoryMessages.length > 0"
                class="history-messages">
                <div class="history-msg-count">
                  <span>{{ totalHistoryCount }} <?php echo _l('chat_messages'); ?></span>
                </div>
                <div v-for="message in filteredHistoryMessages" :key="'hist-' + message.id" class="history-msg"
                  :class="{ 'own': message.sender_id == currentUserId }">
                  <div class="history-msg-avatar">
                    <img
                      :src="getUserAvatar(users.find(u => u.staffid == message.sender_id) || getSelectedHistoryUser())"
                      alt="">
                  </div>
                  <div class="history-msg-body">
                    <div class="history-msg-meta">
                      <span class="history-msg-sender">{{ message.sender_fullname }}</span>
                      <span class="history-msg-time">{{ formatHistoryTime(message.time_sent) }}</span>
                    </div>
                    <div class="history-msg-text" v-html="processHistoryMessage(message.message)"></div>
                  </div>
                </div>
              </div>

              <div v-else-if="!historyLoading && Array.isArray(historyMessages) && historyMessages.length === 0"
                class="history-empty-tab">
                <i class="fa fa-comments"></i>
                <p><?php echo _l('chat_sorry_no_data'); ?></p>
              </div>

              <div v-else-if="!historyLoading && historySearchQuery" class="history-empty-tab">
                <i class="fa fa-search"></i>
                <p><?php echo _l('chat_no_messages_found_matching'); ?> "{{ historySearchQuery }}"</p>
              </div>
            </div>

            <!-- FILES TAB -->
            <div v-if="activeHistoryTab === 'files'" class="history-tab-content">
              <div v-if="filesLoading" class="history-loading">
                <i class="fa fa-spinner fa-spin"></i> <?php echo _l('loading'); ?>...
              </div>

              <div v-else-if="Array.isArray(filteredSharedFiles) && filteredSharedFiles.length > 0"
                class="shared-files-list">
                <div class="history-msg-count">
                  <span>{{ filteredSharedFiles.length }} <?php echo _l('chat_files_text'); ?></span>
                </div>
                <div v-for="file in filteredSharedFiles" :key="'file-' + file.file_name" class="shared-file-item">
                  <div class="file-icon">
                    <i :class="getFileIcon(file.file_name)"></i>
                  </div>
                  <div class="file-details">
                    <div class="file-name">{{ file.file_name }}</div>
                    <div class="file-meta">{{ getFileExtension(file.file_name).toUpperCase() }}</div>
                  </div>
                  <div class="file-actions">
                    <a :href="getFileUrl(file.file_name)" target="_blank" class="file-action-btn"
                      title="<?php echo _l('chat_open_file'); ?>">
                      <i class="fa fa-external-link"></i>
                    </a>
                    <a :href="getFileUrl(file.file_name)" :download="file.file_name" class="file-action-btn"
                      title="<?php echo _l('chat_download_file'); ?>">
                      <i class="fa fa-download"></i>
                    </a>
                  </div>
                </div>
              </div>

              <div v-else-if="!filesLoading && Array.isArray(sharedFiles) && sharedFiles.length === 0"
                class="history-empty-tab">
                <i class="fa fa-file"></i>
                <p><?php echo _l('chat_no_files_shared'); ?></p>
              </div>

              <div v-else-if="!filesLoading && fileSearchQuery" class="history-empty-tab">
                <i class="fa fa-search"></i>
                <p><?php echo _l('chat_no_files_found_matching'); ?> "{{ fileSearchQuery }}"</p>
              </div>
            </div>

            <!-- PHOTOS TAB -->
            <div v-if="activeHistoryTab === 'photos'" class="history-tab-content">
              <div v-if="photosLoading" class="history-loading">
                <i class="fa fa-spinner fa-spin"></i> <?php echo _l('loading'); ?>...
              </div>

              <div v-else-if="Array.isArray(filteredSharedPhotos) && filteredSharedPhotos.length > 0"
                class="shared-photos-grid">
                <div class="history-msg-count">
                  <span>{{ filteredSharedPhotos.length }} <?php echo _l('chat_photos_text'); ?></span>
                </div>
                <div class="photos-grid">
                  <div v-for="photo in filteredSharedPhotos" :key="'photo-' + photo.file_name" class="photo-item"
                    @click="openImagePreview(getFileUrl(photo.file_name), photo.file_name)">
                    <div class="photo-thumbnail"
                      :style="{ backgroundImage: 'url(' + getFileUrl(photo.file_name) + ')' }">
                    </div>
                    <div class="photo-overlay">
                      <i class="fa fa-search-plus"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="!photosLoading && Array.isArray(sharedPhotos) && sharedPhotos.length === 0"
                class="history-empty-tab">
                <i class="fa fa-image"></i>
                <p><?php echo _l('chat_no_photos_shared'); ?></p>
              </div>

              <div v-else-if="!photosLoading && photoSearchQuery" class="history-empty-tab">
                <i class="fa fa-search"></i>
                <p><?php echo _l('chat_no_photos_found_matching'); ?> "{{ photoSearchQuery }}"</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FORWARD MESSAGE MODAL -->
  <div v-if="showForwardModal" class="forward-modal" @click.self="closeForwardModal">
    <div class="forward-modal-content">
      <div class="forward-modal-header">
        <h3 class="forward-modal-title"><?php echo _l('chat_forward_message'); ?></h3>
        <button @click="closeForwardModal" class="btn btn-secondary">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="forward-modal-body">
        <div class="forward-message-preview">
          <strong><?php echo _l('chat_message_to_forward'); ?>:</strong>
          <div class="forward-preview-text">{{ getForwardPreview(messageToForward?.message || '') }}</div>
        </div>
        <div class="forward-users-list">
          <h6><?php echo _l('chat_select_users_to_forward_to'); ?>:</h6>
          <input type="text" v-model="forwardSearchQuery" class="form-control" style="margin-bottom:10px;" placeholder="<?php echo _l('kb_search'); ?>...">
          <div v-for="user in forwardFilteredUsers" :key="user.staffid"
            class="forward-user-item" @click="toggleUserSelection(user.staffid)">
            <input type="checkbox" :checked="selectedUsersForForward.includes(user.staffid)"
              class="forward-user-checkbox">
            <img :src="getUserAvatar(user)" :alt="user.firstname" class="forward-user-avatar">
            <span>{{ user.firstname }} {{ user.lastname }}</span>
          </div>
        </div>
      </div>
      <div class="forward-modal-footer">
        <button @click="closeForwardModal" class="btn btn-secondary"><?php echo _l('cancel'); ?></button>
        <button @click="executeForward" :disabled="selectedUsersForForward.length === 0" class="btn btn-primary">
          <?php echo _l('chat_forward_message'); ?>
        </button>
      </div>
    </div>
  </div>

  <!-- CONTEXT MENU (Pin/Mute) -->
  <div v-if="contextMenu.visible" class="toggled-context-menu"
    :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }" @click.stop>
    <div class="ctx-item" @click="togglePin(contextMenu.user)">
      <i class="fa fa-thumb-tack"></i>
      <span v-if="isUserPinned(contextMenu.user)"><?php echo _l('chat_unpin_conversation'); ?></span>
      <span v-else><?php echo _l('chat_pin_conversation'); ?></span>
    </div>
    <div class="ctx-divider"></div>
    <div class="ctx-item" @click="toggleMute(contextMenu.user)">
      <i :class="isUserMuted(contextMenu.user) ? 'fa fa-bell' : 'fa fa-bell-slash'"></i>
      <span v-if="isUserMuted(contextMenu.user)"><?php echo _l('chat_unmute_conversation'); ?></span>
      <span v-else><?php echo _l('chat_mute_conversation'); ?></span>
    </div>
  </div>

  <!-- IMAGE PREVIEW MODAL -->
  <div v-if="showImagePreview" class="image-preview-modal" @click.self="closeImagePreview">
    <div class="image-preview-content">
      <div class="image-preview-header">
        <h4 class="image-preview-title">{{ previewImageTitle }}</h4>
        <button @click="closeImagePreview" class="image-preview-close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="image-preview-body">
        <img :src="previewImageUrl" :alt="previewImageTitle" class="preview-image">
      </div>
      <div class="image-preview-footer">
        <button @click="downloadImage" class="btn btn-secondary">
          <i class="fa fa-download"></i> <?php echo _l('download'); ?>
        </button>
        <button @click="openImageInNewTab" class="btn btn-primary">
          <i class="fa fa-external-link"></i> <?php echo _l('open_in_new_tab'); ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- CSS STYLES -->
<link rel="stylesheet" href="<?php echo base_url('modules/prchat/assets/css/new_toggle_chat.css?v=' . time()); ?>">
<link rel="stylesheet" href="<?php echo base_url('modules/prchat/assets/css/toggled_chat_emoji.css?v=' . time()); ?>">
<style>
  .chat-header {
    background: linear-gradient(135deg, #2663eb 0%, #1e4fd6 100%);
    padding: 10px 14px;
    color: white;
    border-radius: 12px 12px 0 0;
    cursor: grab;
    user-select: none;
  }

  .chat-header:active {
    cursor: grabbing;
  }

  .message-bubble.has-reactions {
    position: relative;
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

  .own-message .message-reactions {
    left: 4px;
  }

  .other-message .message-reactions {
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

  .dark-mode .reaction-pill {
    background: #1f2937;
    border-color: #374151;
    box-shadow: 0 1px 4px rgba(0, 0, 0, .25);
  }

  .dark-mode .reaction-pill:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, .35);
  }

  .dark-mode .reaction-pill.reacted {
    background: rgba(59, 130, 246, .15);
    border-color: rgba(59, 130, 246, .4);
  }

  .dark-mode .reaction-pill.reacted:hover {
    background: rgba(59, 130, 246, .25);
  }

  .dark-mode .reaction-count {
    color: #9ca3af;
  }

  .dark-mode .reaction-pill.reacted .reaction-count {
    color: #60a5fa;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }

  .header-icon {
    font-size: 16px;
    opacity: 0.9;
    flex-shrink: 0;
  }

  .company-name {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 157px;
    line-height: 1.2;
  }

  .header-separator {
    opacity: 0.5;
    font-size: 14px;
    flex-shrink: 0;
  }

  .online-info {
    display: none;
  }

  .footer-online-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-right: -6px;
    padding: 0 4px;
  }

  .footer-online-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #94a3b8;
    flex-shrink: 0;
  }

  .footer-online-indicator.has-online .footer-online-dot {
    background: #22c55e;
    box-shadow: 0 0 4px rgba(34, 197, 94, 0.5);
  }

  .footer-online-text {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
  }

  .footer-online-indicator.has-online .footer-online-text {
    color: #64748b;
  }

  .dark-mode .footer-online-text {
    color: #64748b;
  }

  .dark-mode .footer-online-indicator.has-online .footer-online-text {
    color: #94a3b8;
  }

  .main-chat-panel.minimized .chat-header {
    display: none;
  }

  /* PANEL FOOTER */
  .panel-footer {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    padding: 6px 8px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 0 0 12px 12px;
  }

  .footer-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
    text-decoration: none;
  }

  .footer-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
  }

  .footer-btn.active {
    color: #2663eb;
    background: #e1e6ed;
  }

  .footer-btn.has-floating-unread {
    color: #ef4444;
  }

  .footer-btn.has-floating-unread:hover {
    background: #fef2f2;
    color: #dc2626;
  }

  .footer-badge {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #ef4444;
    color: #fff;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 600;
    line-height: 16px;
    text-align: center;
  }

  /* Dark mode footer */
  .dark-mode .panel-footer {
    background: #0f172a;
    border-top-color: #334155;
  }

  .dark-mode .footer-btn {
    color: #94a3b8;
  }

  .dark-mode .footer-btn:hover {
    background: #1e293b;
    color: #f1f5f9;
  }

  .dark-mode .footer-btn.active {
    color: #60a5fa;
    background: #1e3a5f;
  }

  /* CSS-only tooltips (no JS needed) */
  [data-tip] {
    position: relative;
  }

  [data-tip]::after {
    content: attr(data-tip);
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 10px;
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
  }

  [data-tip]:hover::after {
    opacity: 1;
  }

  /* Downward tooltip only for main panel header buttons */
  .main-chat-panel .header-btn[data-tip]::after {
    bottom: auto;
    top: calc(100% + 6px);
  }

  .user-avatar-wrap {
    position: relative;
    flex-shrink: 0;
  }

  .unread-badge {
    position: absolute;
    top: -4px;
    left: -4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    background: #ef4444;
    color: white;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 600;
    border: 2px solid #fff;
    z-index: 1;
    animation: pulse-badge 1.5s ease-in-out infinite;
  }

  @keyframes pulse-badge {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }
  }

  /* FLOATING TOGGLE */
  .chat-toggle-float {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: #2663eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-size: 24px;
  }

  /* REPLY CONTEXT STYLES */
  .reply-context {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 10px 14px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    animation: slideInDownSmall 0.2s ease-out;
  }

  .reply-info i {
    color: #2663eb;
    font-size: 12px;
  }

  .message-reply-context {
    cursor: pointer;
    background: rgba(59, 130, 246, 0.06);
    border-radius: 6px;
    padding: 5px 8px 5px 24px;
    margin-bottom: 6px;
    position: relative;
    max-width: 100%;
    overflow: hidden;
  }

  .message-reply-context::before {
    content: '\f3e5';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    color: #93a3b8;
    pointer-events: none;
  }

  .message-reply-context:hover {
    background: rgba(59, 130, 246, 0.12);
  }

  .reply-context-bar {
    display: none;
  }

  .reply-context-content {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #2563eb;
    font-weight: 500;
    min-width: 0;
  }

  .reply-context-icon {
    display: none;
  }

  .reply-context-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #374151;
    font-weight: 400;
    font-size: 11px;
    min-width: 0;
  }

  .reply-context-text img {
    max-width: 24px !important;
    max-height: 24px !important;
    border-radius: 4px;
    margin-right: 4px;
    object-fit: cover;
  }

  .own-message .message-reply-context {
    background: rgba(255, 255, 255, 0.13);
  }

  .own-message .message-reply-context::before {
    color: rgba(255, 255, 255, 0.4);
  }

  .own-message .reply-context-content {
    color: rgba(255, 255, 255, 0.85);
  }

  .own-message .reply-context-text {
    color: rgba(255, 255, 255, 0.7);
  }

  /* Reply context - dark mode */
  .dark-mode .message-reply-context {
    background: rgba(255, 255, 255, 0.08);
  }

  .dark-mode .message-reply-context::before {
    color: #60a5fa;
  }

  .dark-mode .reply-context-content {
    color: #60a5fa;
  }

  .dark-mode .reply-context-text {
    color: #cbd5e1;
  }

  .dark-mode .reply-context:not(.own-message .reply-context) {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  }

  .dark-mode .reply-context .reply-info i {
    color: #60a5fa;
  }

  .dark-mode .reply-context .reply-info span {
    color: #cbd5e1;
  }

  /* Call summary centered bubble */
  .call-system-message {
    display: flex !important;
    justify-content: center !important;
    padding: 4px 0 !important;
  }

  .call-summary-bubble {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f2f5;
    color: #54656f;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    margin: 0 auto;
  }

  .call-summary-bubble i {
    font-size: 13px;
    color: #667781;
  }

  .call-summary-time {
    color: #8696a0;
    font-size: 11px;
    margin-left: 4px;
  }

  /* Drag-and-drop overlay */
  .chat-drop-overlay {
    position: absolute;
    inset: 0;
    background: rgba(59, 130, 246, 0.12);
    backdrop-filter: blur(4px);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px dashed #3b82f6;
    border-radius: 12px;
    pointer-events: none;
  }

  .chat-drop-overlay-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #2563eb;
    font-size: 14px;
    font-weight: 600;
  }

  .chat-drop-overlay-content i {
    font-size: 32px;
    opacity: 0.8;
  }

  .forward-message-preview {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid #2663eb;
  }

  .chat-link {
    color: #2663eb;
    text-decoration: underline;
  }

  .chat-link:hover {
    text-decoration: none;
    opacity: 0.8;
  }

  .chat-uploaded-file .file-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #2663eb;
    text-decoration: none;
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #f8f9fa;
    transition: background-color 0.2s;
  }

  .chat-uploaded-file .file-link:hover {
    background: #e9ecef;
    text-decoration: none;
  }

  .chat-file {
    margin: 8px 0;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #2663eb;
  }

  .file-link {
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .file-link:hover {
    color: #2663eb;
  }

  .file-link i {
    color: #2663eb;
  }

  /* Date separator */
  .toggle-date-separator {
    text-align: center;
    font-size: 11px;
    color: #8e8e93;
    margin: 12px 0;
    position: relative;
  }

  .toggle-date-separator::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e4e7ea;
    z-index: 0;
  }

  .toggle-date-separator span {
    background: #fff;
    padding: 0 10px;
    position: relative;
    z-index: 1;
  }
</style>

<!-- VUE.JS LIBRARY -->
<script src="<?= base_url('modules/prchat/assets/js/vendor/vue.global.prod.js?v=' . time()); ?>"></script>

<!-- CHAT SETTINGS -->
<script>
  // Chat configuration from PHP
  window.chatConfig = {
    currentUserId: <?php echo get_staff_user_id(); ?>,
    currentUserName: '<?php echo get_staff_full_name(); ?>',
    // chatColor removed - using light/dark theme system only
    siteUrl: '<?php echo site_url(); ?>',
    apiUrls: {
      users: '<?php echo admin_url("prchat/Prchat_Controller/users"); ?>',
      getKey: '<?php echo admin_url("prchat/Prchat_Controller/getKey"); ?>',
      getMessages: '<?php echo admin_url("prchat/Prchat_Controller/getMessages"); ?>',
      sendMessage: '<?php echo admin_url("prchat/Prchat_Controller/initiateChat"); ?>',
      addReaction: '<?php echo admin_url("prchat/Prchat_Controller/addReaction"); ?>',
      updateUnread: '<?php echo admin_url("prchat/Prchat_Controller/updateUnread"); ?>',
      handleChatStatus: '<?php echo admin_url("prchat/Prchat_Controller/handleChatStatus"); ?>',
      pusherAuth: '<?php echo admin_url("prchat/Prchat_Controller/pusher_auth"); ?>',
      uploadFile: '<?php echo admin_url("prchat/Prchat_Controller/uploadMethod"); ?>',
      getPinMuteSettings: '<?php echo admin_url("prchat/Prchat_Controller/getPinMuteSettings"); ?>',
      togglePin: '<?php echo admin_url("prchat/Prchat_Controller/togglePin"); ?>',
      toggleMute: '<?php echo admin_url("prchat/Prchat_Controller/toggleMute"); ?>',
    },
    pusherCluster: '<?php echo get_option('pusher_cluster'); ?>',
    notificationsEnabled: <?php echo get_option('chat_desktop_messages_notifications') ? 'true' : 'false'; ?>,
    canDeleteMessages: <?php echo (is_admin() || staff_can('delete', PR_CHAT_MODULE_NAME)) ? 'true' : 'false'; ?>
  };

  <?php if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1'): ?>
    window.prchatSetCallDebug = function(on) {
      window.PRCHAT_DEBUG_CALLS = (on !== false);
    };
  <?php endif; ?>

  // CSRF token for AJAX requests
  window.PRCHAT_CSRF = {
    name: '<?= $this->security->get_csrf_token_name(); ?>',
    hash: '<?= $this->security->get_csrf_hash(); ?>'
  };

  // Auto-attach CSRF token to every fetch() POST request
  (function() {
    var originalFetch = window.fetch;
    window.fetch = function(url, options) {
      options = options || {};
      if (options.method && options.method.toUpperCase() === 'POST' && window.PRCHAT_CSRF) {
        if (options.body instanceof FormData) {
          var _fdToken = options.body.get(PRCHAT_CSRF.name);
          if (!_fdToken) {
            options.body.set(PRCHAT_CSRF.name, PRCHAT_CSRF.hash);
          }
        } else if (options.body instanceof URLSearchParams) {
          var _spToken = options.body.get(PRCHAT_CSRF.name);
          if (!_spToken) {
            options.body.set(PRCHAT_CSRF.name, PRCHAT_CSRF.hash);
          }
        } else if (typeof options.body === 'string') {
          var _csrfPair = encodeURIComponent(PRCHAT_CSRF.name) + '=' + encodeURIComponent(PRCHAT_CSRF.hash);
          if (options.body.indexOf(PRCHAT_CSRF.name + '=') === -1 && options.body.indexOf(encodeURIComponent(PRCHAT_CSRF.name) + '=') === -1) {
            options.body += (options.body.length ? '&' : '') + _csrfPair;
          }
        }
      }
      return originalFetch.call(this, url, options);
    };
  })();
</script>

<!-- DOMPurify + Safe Renderer (loaded before Vue app) -->
<script src="<?= base_url('modules/prchat/assets/js/vendor/purify.min.js?v=' . VERSIONING); ?>"></script>
<script src="<?= base_url('modules/prchat/assets/js/prchat-safe-renderer.js?v=' . VERSIONING . '.3'); ?>"></script>
<script src="<?= base_url('modules/prchat/assets/js/voice-recorder.js?v=' . VERSIONING); ?>"></script>

<!-- MAIN VUE.JS APPLICATION -->
<script>
  const {
    createApp
  } = Vue;

  createApp({
    beforeUnmount() {
      document.removeEventListener('click', this.handleClickOutside);
      document.removeEventListener('keydown', this.handleEscapeKey);

      // Cleanup floating count listener
      if (this._floatingChangeHandler) {
        window.removeEventListener('floatingNotificationsChanged', this._floatingChangeHandler);
      }

      // Cleanup network connection listeners
      window.removeEventListener('online', this.handleConnectionChange);
      window.removeEventListener('offline', this.handleConnectionChange);

      // Cleanup Pusher channels to prevent memory leaks
      if (this.presenceChannel) {
        this.presenceChannel.unbind_all();
        this.pusher.unsubscribe('presence-mychanel');
      }
      if (this.chatStatusChannel) {
        this.chatStatusChannel.unbind_all();
        this.pusher.unsubscribe('user_changed_chat_status');
      }
      if (this.userMessagesChannel) {
        this.userMessagesChannel.unbind_all();
        this.pusher.unsubscribe('user_messages');
      }
      if (this.clientsChannel) {
        this.clientsChannel.unbind_all();
        this.pusher.unsubscribe('presence-clients');
      }
      <?php if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1'): ?>
        if (this.callsStaffChannel && this.pusher) {
          try {
            var _prCn = '<?= CHAT_CALLS_STAFF_CHANNEL_PREFIX ?>' + window.chatConfig.currentUserId;
            this.callsStaffChannel.unbind();
            this.pusher.unsubscribe(_prCn);
          } catch (e) {}
        }
      <?php endif; ?>
      if (this.pusher) {
        this.pusher.disconnect();
      }

      // Close BroadcastChannel for call sync
      if (this.callSyncChannel) {
        this.callSyncChannel.close();
      }

      // Clear pending removal timeouts
      Object.values(this.pendingRemoves).forEach(timeout => clearTimeout(timeout));
    },
    data() {
      return {
        // UI State - Start hidden, will show after data loads
        showMainPanel: false,
        isInitializing: true, // Prevents floating button from showing during load
        isMinimized: (() => {
          try {
            const saved = JSON.parse(localStorage.getItem('vueChatStates'));
            return saved?.mainPanelMinimized || false;
          } catch {
            return false;
          }
        })(),
        searchVisible: false,

        // Filter
        showFilterDropdown: false,
        activeFilter: localStorage.getItem('prchat_toggled_filter') || 'all',
        filterOptions: [{
            value: 'all',
            label: '<?= _l("chat_filter_all"); ?>',
            dot: false
          },
          {
            value: 'online',
            label: '<?= _l("chat_filter_online"); ?>',
            dot: true
          },
          {
            value: 'away',
            label: '<?= _l("chat_filter_away"); ?>',
            dot: true
          },
          {
            value: 'busy',
            label: '<?= _l("chat_filter_busy"); ?>',
            dot: true
          },
          {
            value: 'offline',
            label: '<?= _l("chat_filter_offline"); ?>',
            dot: true
          },
          {
            value: 'unread',
            label: '<?= _l("chat_filter_unread"); ?>',
            dot: false
          },
        ],

        // Chat Data
        users: [],
        filteredUsers: [],
        activeChats: [],
        onlineUsers: [],
        searchQuery: '',

        notificationsEnabled: window.chatConfig.notificationsEnabled,
        canDeleteMessages: !!window.chatConfig.canDeleteMessages,

        // Current User
        currentUserId: window.chatConfig.currentUserId,
        currentUserData: null, // Store current user data for profile images

        // Theme
        isDarkMode: localStorage.getItem('prChatThemeMode') === 'dark',

        // Video Support
        hasVideoSupport: false,

        // Pusher
        pusher: null,
        presenceChannel: null,
        chatStatusChannel: null,
        userMessagesChannel: null,
        clientsChannel: null, // Pusher channel for client messages
        callSyncChannel: null, // BroadcastChannel for cross-tab call sync
        audioContext: null,
        audioContextReady: false,

        // Intervals
        typingTimeouts: {},
        pendingRemoves: {}, // Track pending offline updates (5-second delay)

        // Status
        connectionStatus: 'connected',

        // Message Options
        activeMessageOptions: null,
        hideTimeout: null,
        reactionTargetMessage: null,

        // Forward Modal
        showForwardModal: false,
        messageToForward: null,
        selectedUsersForForward: [],
        forwardSearchQuery: '',

        // Image Preview Modal
        showImagePreview: false,
        previewImageUrl: '',
        previewImageTitle: '',

        // Chat History Modal
        showHistoryModal: false,
        selectedHistoryUserId: '',
        activeHistoryTab: 'messages',
        historyMessages: [],
        filteredHistoryMessages: [],
        historySearchQuery: '',
        historyUserFilter: '',
        historyLoading: false,
        totalHistoryCount: 0,
        // Files Tab
        sharedFiles: [],
        filteredSharedFiles: [],
        fileSearchQuery: '',
        filesLoading: false,
        // Photos Tab
        sharedPhotos: [],
        filteredSharedPhotos: [],
        photoSearchQuery: '',
        photosLoading: false,

        // Drag State
        dragState: {
          isDragging: false,
          draggedChat: null,
          startX: 0,
          startY: 0,
          startLeft: 0,
          startTop: 0
        },

        // Main panel drag
        mainPanelPos: {
          right: 20,
          bottom: 16
        },
        mainPanelDrag: {
          isDragging: false,
          startX: 0,
          startY: 0,
          startRight: 20,
          startBottom: 16
        },

        // Floating notifications count
        floatingUnreadCount: 0,

        // Full chat view URL
        fullChatViewUrl: (window.chatConfig.siteUrl || '') + 'admin/prchat/Prchat_Controller/chat_full_view',

        // Pin & Mute
        pinnedUsers: [],
        mutedUsers: [],
        mutedClients: [],
        contextMenu: {
          visible: false,
          x: 0,
          y: 0,
          userId: null
        }
      }
    },

    computed: {

      mainPanelStyle() {
        return {
          right: this.mainPanelPos.right + 'px',
          bottom: this.mainPanelPos.bottom + 'px'
        };
      },

      totalUnreadCount() {
        return this.users.reduce((total, user) => total + (user.unreadCount || 0), 0);
      },

      otherOnlineUsers() {
        return this.onlineUsers.filter(user => user.id != this.currentUserId).length;
      },

      forwardFilteredUsers() {
        var list = this.users.filter(u => parseInt(u.staffid) !== parseInt(this.currentUserId));
        var q = (this.forwardSearchQuery || '').trim().toLowerCase();
        if (!q) return list;
        return list.filter(u => {
          var name = ((u.firstname || '') + ' ' + (u.lastname || '')).toLowerCase();
          return name.indexOf(q) > -1;
        });
      },

      historyFilteredUsers() {
        if (!this.historyUserFilter) return this.users;
        const q = this.historyUserFilter.toLowerCase();
        return this.users.filter(u =>
          (u.firstname + ' ' + u.lastname).toLowerCase().includes(q) ||
          (u.role || '').toLowerCase().includes(q)
        );
      }
    },

    async mounted() {
      // Store reference for global access
      window.toggledChatApp = this;

      // Add body class for toggled chat view (used for CSS isolation)
      document.body.classList.add('toggled-chat-view');

      // Add click outside handler to close message options
      document.addEventListener('click', this.handleClickOutside);
      // Add escape key handler for closing chats and modals
      document.addEventListener('keydown', this.handleEscapeKey);

      await this.initializeChat();
      this.restoreChatState();
      this.initializeDarkMode();

      window.addEventListener('resize', () => this.clampMainPanelPos());

      // Track floating notification count via custom event
      this.updateFloatingUnreadCount();
      this._floatingChangeHandler = () => this.updateFloatingUnreadCount();
      window.addEventListener('floatingNotificationsChanged', this._floatingChangeHandler);

      // Process existing video embeds after chat is initialized
      this.$nextTick(() => {
        if (typeof PrChatVideoEmbed !== 'undefined' && this.$el) {
          PrChatVideoEmbed.convertVideoLinks(this.$el);
        }
      });

      // Initialize audio context on first user interaction
      const initAudio = () => {
        this.initializeAudioContext();
        // Remove all listeners after first interaction
        document.removeEventListener('click', initAudio);
        document.removeEventListener('keydown', initAudio);
        document.removeEventListener('touchstart', initAudio);
        document.removeEventListener('mousedown', initAudio);
      };

      // Listen for multiple user interaction events
      document.addEventListener('click', initAudio);
      document.addEventListener('keydown', initAudio);
      document.addEventListener('touchstart', initAudio);
      document.addEventListener('mousedown', initAudio);

      // Listen for network connection changes
      window.addEventListener('online', this.handleConnectionChange);
      window.addEventListener('offline', this.handleConnectionChange);

    },

    methods: {
      getChatWindowStyle(chat) {
        const windowWidth = window.innerWidth || 1920;
        const windowHeight = window.innerHeight || 800;
        const chatIndex = this.activeChats.indexOf(chat);
        const chatWidth = chat.minimized ? 260 : 330;
        const chatHeight = chat.minimized ? 48 : 400;

        // Default position: stack left of main panel, flush to bottom
        const mainRight = this.mainPanelPos.right || 20;
        const calculatedLeft = windowWidth - mainRight - 340 - 330 - (chatIndex * 340);
        const calculatedTop = windowHeight - chatHeight;

        // Clamp to viewport: must stay fully within
        const maxLeft = windowWidth - chatWidth;
        const maxTop = windowHeight - chatHeight;
        const rawLeft = (chat.position?.left != null) ? chat.position.left : calculatedLeft;
        const rawTop = (chat.position?.top != null) ? chat.position.top : calculatedTop;
        const finalLeft = Math.max(0, Math.min(rawLeft, maxLeft));
        const finalTop = Math.max(0, Math.min(rawTop, maxTop));

        return {
          left: finalLeft + 'px',
          top: finalTop + 'px',
          position: 'fixed',
          zIndex: chat.zIndex || 9999
        };
      },

      bringToFront(chat) {
        this._zCounter = (this._zCounter || 10000) + 1;
        chat.zIndex = this._zCounter;
        // Unfocus all others
        this.activeChats.forEach(c => {
          c.focused = (c === chat);
        });
      },

      openEmojiPicker(chat, event) {
        // Get the textarea for this chat using the ref
        const textareaRef = this.$refs['messageInput' + chat.userId];
        const textarea = Array.isArray(textareaRef) ? textareaRef[0] : textareaRef;

        if (!textarea) {
          return;
        }

        // Initialize picker if needed
        if (!window.emojiPickerInstance) {
          window.emojiPickerInstance = new EmojiPicker();
          window.emojiPickerInstance.init();
        }

        const picker = window.emojiPickerInstance;
        const $picker = $('#emoji-picker');

        // If picker is visible, just hide it
        if (picker.isVisible) {
          picker.hide();
          return;
        }

        // Store reference to chat for Vue reactivity
        window.currentEmojiChat = chat;

        // Override the selectEmoji method to work with Vue
        const originalSelectEmoji = picker.selectEmoji.bind(picker);
        picker.selectEmoji = (emoji) => {
          // Update Vue's reactive data directly
          if (window.currentEmojiChat) {
            window.currentEmojiChat.currentMessage += emoji;
          }
          // Add to recent emojis
          picker.addToRecent(emoji);
        };

        // Set target textarea and show picker
        picker.targetTextarea = $(textarea);

        // Show off-screen first to get real dimensions
        $picker.css({
          position: 'fixed',
          visibility: 'hidden',
          top: '-9999px',
          left: '-9999px'
        });
        $picker.addClass('show');
        picker.isVisible = true;
        void $picker[0].offsetHeight; // force reflow

        // Calculate proper position for toggled chat
        const buttonRect = event.currentTarget.getBoundingClientRect();
        const pickerHeight = $picker.outerHeight() || 300;
        const pickerWidth = $picker.outerWidth() || 280;
        const windowHeight = window.innerHeight;
        const windowWidth = window.innerWidth;

        // Position above the button by default
        let top = buttonRect.top - pickerHeight - 10;
        let left = buttonRect.left - (pickerWidth / 2) + 15; // Center on button

        // Clamp to viewport top edge (always stay above)
        if (top < 10) {
          top = 10;
        }

        // If would go below viewport, adjust
        if (top + pickerHeight > windowHeight - 10) {
          top = windowHeight - pickerHeight - 10;
        }

        // Adjust if would go outside right edge
        if (left + pickerWidth > windowWidth - 10) {
          left = windowWidth - pickerWidth - 10;
        }

        // Adjust if would go outside left edge
        if (left < 10) {
          left = 10;
        }

        // Apply position directly
        $picker.css({
          position: 'fixed',
          top: top + 'px',
          left: left + 'px',
          visibility: 'visible',
          zIndex: 10001
        });
      },

      async initializeChat() {
        try {
          await this.loadUsers();
        } catch (error) {
          if (error?.name !== 'AbortError') console.warn('Failed to load users:', error);
        }

        try {
          const saved = JSON.parse(localStorage.getItem('vueChatStates'));
          this.showMainPanel = saved?.showMainPanel !== false;
        } catch {
          this.showMainPanel = true;
        }
        this.isInitializing = false;

        this.initializePusher().catch(e => {
          if (e?.name !== 'AbortError') console.warn('Pusher init issue:', e);
        });
        this.checkVideoSupport();
      },


      saveChatState() {
        const chatStates = {
          activeChats: this.activeChats.map(chat => ({
            userId: chat.userId,
            user: chat.user,
            minimized: chat.minimized,
            position: chat.position,
            unreadCount: chat.unreadCount,
            focused: chat.focused
          })),
          mainPanelMinimized: this.isMinimized,
          mainPanelPos: this.mainPanelPos,
          showMainPanel: this.showMainPanel
        };

        localStorage.setItem('vueChatStates', JSON.stringify(chatStates));

      },


      clampMainPanelPos() {
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const panelW = this.isMinimized ? 56 : 340;
        const panelH = this.isMinimized ? 56 : 500;
        this.mainPanelPos.right = Math.max(0, Math.min(this.mainPanelPos.right, vw - panelW));
        this.mainPanelPos.bottom = Math.max(0, Math.min(this.mainPanelPos.bottom, vh - panelH));
      },

      restoreChatState() {
        try {
          const savedStates = localStorage.getItem('vueChatStates');
          if (!savedStates) return;

          const chatStates = JSON.parse(savedStates);

          // Restore main panel state
          this.isMinimized = chatStates.mainPanelMinimized || false;
          if (chatStates.mainPanelPos) {
            this.mainPanelPos = chatStates.mainPanelPos;
          }
          this.clampMainPanelPos();
          this.showMainPanel = chatStates.showMainPanel !== false; // Default to true

          // Restore active chats with corrected positions
          if (chatStates.activeChats && chatStates.activeChats.length > 0) {
            this.activeChats = chatStates.activeChats.map((savedChat, index) => {
              // Recalculate position if it seems incorrect (not next to main chat panel)
              const windowWidth = window.innerWidth || 1920;
              const windowHeight = window.innerHeight || 800;
              const chatWidth = 330;
              const chatHeight = 400;
              const mainChatPanelWidth = 350;

              let position = savedChat.position;
              const correctLeft = windowWidth - mainChatPanelWidth - chatWidth - 20 - (index * (chatWidth + 10));
              const minLeft = windowWidth - mainChatPanelWidth - (chatWidth * 3) - 60;

              if (!position || position.left < minLeft || position.left > windowWidth - chatWidth) {
                position = {
                  left: correctLeft,
                  top: Math.max(0, windowHeight - chatHeight)
                };
              }

              return {
                ...savedChat,
                position,
                messages: [],
                currentMessage: '',
                isTyping: false,
                isLoading: true,
                loadingMore: false,
                showFileUpload: false,
                messageOffset: 0
              };
            });

            this.activeChats.forEach(chat => {
              this.loadChatMessages(chat);
            });
          }


        } catch (error) {
          console.error('Failed to restore chat state:', error);
        }
      },

      async loadUsers() {
        try {
          const response = await fetch(window.chatConfig.apiUrls.users, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          // Handle response as text first, then parse as JSON (like jQuery does)
          const text = await response.text();
          const data = JSON.parse(text);

          // Handle both array and object responses
          let usersArray = Array.isArray(data) ? data : (data.users || []);

          // Store current user data for profile image purposes (before filtering)
          this.currentUserData = usersArray.find(user => user.staffid == this.currentUserId) || null;

          // Filter out current logged-in user - they shouldn't chat with themselves
          usersArray = usersArray.filter(user => user.staffid != this.currentUserId);

          this.users = usersArray.map(user => ({
            ...user,
            status: 'offline',
            isTyping: false,
            unreadCount: parseInt(user.unread_count) || 0,
            lastMessage: user.message || null,
            lastMessageSenderId: user.message_sender_id || null,
            lastMessageTime: user.time_sent || null,
            lastSeen: user.last_activity || user.last_login || null
          }));

          // Load pin/mute settings
          await this.loadPinMuteSettings();

          this.filterUsers(); // Apply sorting from initial load

          // Feed existing unread counts to floating notification widget
          this.loadFloatingUnread();
        } catch (error) {
          console.error('Failed to load users:', error);
        }
      },

      async initializePusher() {
        try {
          // Get Pusher key
          const keyResponse = await fetch(window.chatConfig.apiUrls.getKey, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          });

          if (!keyResponse.ok) {
            throw new Error(`Failed to get Pusher key: ${keyResponse.status}`);
          }

          const pusherKeyData = await keyResponse.json();

          // Extract the actual key string from the response
          const pusherKey = typeof pusherKeyData === 'object' ? pusherKeyData.key : pusherKeyData;

          // Initialize Pusher
          var authHeaders = {};
          if (window.PRCHAT_CSRF && window.PRCHAT_CSRF.name) {
            authHeaders[window.PRCHAT_CSRF.name] = window.PRCHAT_CSRF.hash;
          }
          this.pusher = new Pusher(pusherKey, {
            authEndpoint: window.chatConfig.apiUrls.pusherAuth,
            authTransport: "jsonp",
            auth: {
              headers: authHeaders
            },
            cluster: window.chatConfig.pusherCluster || 'us2',
            disableStats: true
          });

          // Suppress pong timeout errors (4201) - Pusher handles reconnection automatically
          this.pusher.connection.bind('error', (err) => {
            const code = err?.error?.data?.code || err?.data?.code;
            if (code !== 4201 && code !== 1006) {
              console.warn('Pusher connection error:', code);
            }
          });

          // Track Pusher connection state changes
          this.pusher.connection.bind('state_change', (states) => {
            const prevState = states.previous;
            const currState = states.current;

            if (currState === 'unavailable' || currState === 'disconnected') {
              this.connectionStatus = 'disconnected';
            } else if (currState === 'connecting') {
              this.connectionStatus = 'connecting';
            } else if (currState === 'connected') {
              this.connectionStatus = 'connected';
              // Only show success message if we were previously disconnected
              if (prevState === 'unavailable' || prevState === 'disconnected') {
                this.showNotification('<?php echo _l('chat_connection_restored'); ?>', 'success');
              }
            }
          });

          // Subscribe to presence channel
          this.presenceChannel = this.pusher.subscribe('presence-mychanel');
          this.chatStatusChannel = this.pusher.subscribe('user_changed_chat_status');
          this.userMessagesChannel = this.pusher.subscribe('user_messages');

          // Staff call signaling must NOT wait on presence/member sync (same idea as full chat:
          // audio/video rings use calls-staff-{id} only). Presence can be slow; calls would never wire in time.
          <?php if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1'): ?>
            this.initGlobalCalls();
          <?php endif; ?>

          // Presence member list (async; never blocks call subscription above)
          this.presenceChannel.bind('pusher:subscription_succeeded', async (members) => {
            try {
              await this.handlePresenceUpdate(members);
            } catch (e) {
              console.error('[prchat] handlePresenceUpdate failed:', e);
            }
          });
          if (this.presenceChannel.subscribed === true) {
            this.handlePresenceUpdate(this.presenceChannel.members).catch((e) => {
              console.error('[prchat] handlePresenceUpdate failed:', e);
            });
          }

          this.presenceChannel.bind('pusher:member_added', (member) => {
            this.handleUserOnline(member);
          });

          this.presenceChannel.bind('pusher:member_removed', (member) => {
            this.handleUserOffline(member);
          });

          // Message events
          this.presenceChannel.bind('send-event', (data) => {
            this.handleIncomingMessage(data);
          });

          this.presenceChannel.bind('message-edited', (data) => {
            this.handleStaffMessageEdited(data);
          });

          this.presenceChannel.bind('message-deleted', (data) => {
            this.handleStaffMessageDeleted(data);
          });

          // Message reactions (emoji toggles)
          this.presenceChannel.bind('message-reaction', (data) => {
            this.handleMessageReaction(data);
          });

          this.presenceChannel.bind('typing-event', (data) => {
            this.handleTypingEvent(data);
          });

          // Status events
          this.chatStatusChannel.bind('status-changed-event', (data) => {
            this.handleStatusChange(data);
          });

          // Read receipts events
          this.userMessagesChannel.bind('message_seen', (data) => {
            this.handleMessageSeen(data);
          });

          // Subscribe to client channel for client-to-staff message notifications
          <?php if (isClientsEnabled()): ?>
            this.clientsChannel = this.pusher.subscribe('presence-clients');
            this.clientsChannel.bind('send-event', (data) => {
              this.handleClientMessage(data);
            });

            this.clientsChannel.bind('pusher:member_added', (member) => {
              this.handleClientOnline(member);
            });

            this.clientsChannel.bind('pusher:member_removed', (member) => {
              this.handleClientOffline(member);
            });
          <?php endif; ?>
        } catch (error) {
          console.error('Failed to initialize Pusher:', error);
        }
      },

      <?php if (get_option('chat_staff_calls_enabled') == '1' || get_option('chat_calls_video_enabled') == '1'): ?>
        // Initialize global call receiving
        initGlobalCalls() {
          const prchatToggledTrace = (...args) => {
            if (typeof window.prchatCallTrace === 'function') {
              window.prchatCallTrace.apply(null, args);
            }
          };

          // Set calls endpoints (required by CallManager)
          if (!window.PRCHAT_CALLS) {
            window.PRCHAT_CALLS = {};
          }
          Object.assign(window.PRCHAT_CALLS, {
            start: '<?= admin_url("prchat/Calls_Controller/startCall") ?>',
            answer: '<?= admin_url("prchat/Calls_Controller/answerCall") ?>',
            ice: '<?= admin_url("prchat/Calls_Controller/iceCandidate") ?>',
            hangup: '<?= admin_url("prchat/Calls_Controller/hangup") ?>',
            decline: '<?= admin_url("prchat/Calls_Controller/decline") ?>',
            mute_status: '<?= admin_url("prchat/Calls_Controller/muteStatus") ?>'
          });
          window.PRCHAT_GET_CALL_TOKEN_URL = '<?= admin_url("prchat/Prchat_Controller/get_call_token") ?>';
          window.userSessionId = String(window.chatConfig.currentUserId);
          if (!window.PRCHAT_CSRF) {
            window.PRCHAT_CSRF = {
              name: '<?= $this->security->get_csrf_token_name(); ?>',
              hash: '<?= $this->security->get_csrf_hash(); ?>'
            };
          }

          const channelName = '<?= CHAT_CALLS_STAFF_CHANNEL_PREFIX ?>' + window.chatConfig.currentUserId;
          let callsChannel = this.pusher.channel(channelName);
          prchatToggledTrace('toggled calls channel lookup', channelName, 'existing', !!callsChannel);
          if (!callsChannel) {
            callsChannel = this.pusher.subscribe(channelName);
            prchatToggledTrace('toggled subscribe() issued', channelName);
          }
          this.callsStaffChannel = callsChannel;
          callsChannel.bind('pusher:subscription_succeeded', () => {
            prchatToggledTrace('toggled calls-staff subscription_succeeded', channelName);
          });
          if (callsChannel.subscribed === true) {
            prchatToggledTrace('toggled calls channel already subscribed at init', channelName);
          }
          callsChannel.bind('pusher:subscription_error', (status) => {
            console.error('[prchat-call] toggled: calls channel subscription_error', channelName, status);
          });

          // Global call state
          window.__CHAT_CALL_STATE = window.__CHAT_CALL_STATE || 'idle';
          window.__CHAT_CALL_PEER = window.__CHAT_CALL_PEER || null;
          window.__CHAT_CALL_SUPPRESS_UNTIL = window.__CHAT_CALL_SUPPRESS_UNTIL || 0;
          window.__CHAT_CALL_START_TIME = null;
          window.__CHAT_CALL_IS_CALLER = false;

          // Send hangup signal before page unloads (reload/close)
          window.addEventListener('beforeunload', () => {
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
                    from_id: window.chatConfig.currentUserId,
                    to_id: callState.peer,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                  });

                  // Use sendBeacon for reliable delivery during page unload
                  if (navigator.sendBeacon) {
                    navigator.sendBeacon(hangupUrl, formData);
                  } else {
                    // Fallback to synchronous XHR
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', hangupUrl, false); // false = synchronous
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(formData);
                  }

                  localStorage.removeItem('prchat_call_state');
                }
              } catch (e) {
                console.error('[Before Unload] Error sending hangup:', e);
              }
            }
          });

          // Initialize call manager
          if (!window.__chatCallManager && typeof ChatCallManager !== 'undefined') {
            window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
            window.__chatCallManager.onRemoteTrack = (stream) => {
              let audio = document.getElementById('chat-call-remote');
              if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'chat-call-remote';
                audio.autoplay = true;
                document.body.appendChild(audio);
              }
              audio.srcObject = stream;
            };
          }

          callsChannel.bind('call-offer', (payload) => {
            try {
              prchatToggledTrace('toggled Pusher call-offer', {
                from_id: payload && payload.from_id,
                is_video: payload && payload.is_video,
                sdpChars: payload && typeof payload.sdp === 'string' ? payload.sdp.length : null,
                state: window.__CHAT_CALL_STATE
              });
              const nowTs = Date.now();
              if (nowTs < (window.__CHAT_CALL_SUPPRESS_UNTIL || 0)) return;
              if (window.__CHAT_CALL_STATE !== 'idle') return;

              const offer = JSON.parse(payload.sdp);
              const isVideoCall = !!(payload.is_video === true || payload.is_video === 1 || payload.is_video === '1');

              // Set up remote track handler for video calls
              if (isVideoCall) {
                // Set up local track handler
                window.__chatCallManager.onLocalTrack = (stream) => {
                  const pip = document.getElementById('chat-call-pip');
                  let localVideo = document.getElementById('chat-call-local-video');
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
                };

                window.__chatCallManager.onRemoteTrack = (stream) => {
                  const pip = document.getElementById('chat-call-pip');
                  let remoteVideo = document.getElementById('chat-call-remote-video');
                  if (!remoteVideo) {
                    remoteVideo = document.createElement('video');
                    remoteVideo.id = 'chat-call-remote-video';
                    remoteVideo.autoplay = false;
                    remoteVideo.playsinline = true;
                    remoteVideo.muted = false;
                    if (pip) pip.insertBefore(remoteVideo, pip.firstChild);
                    else document.body.appendChild(remoteVideo);
                  }
                  remoteVideo.onloadedmetadata = null;
                  if (remoteVideo.srcObject) {
                    remoteVideo.pause();
                    remoteVideo.srcObject = null;
                  }
                  remoteVideo.srcObject = stream;
                  remoteVideo.style.display = 'block';
                };
              }

              if (window.ChatCallUI) {
                window.__CHAT_CALL_STATE = 'ringingIncoming';
                window.__CHAT_CALL_PEER = payload.from_id;

                // Save call state so beforeunload can send hangup if page reloads while ringing
                this.saveCallState();

                const avatarUrl = payload.from_avatar || null;

                ChatCallUI.showIncomingCall((payload.from_name || '<?php echo _l('chatbot_unknown'); ?>'), (options) => {
                  // Accept
                  if (window.__CHAT_CALL_STATE !== 'ringingIncoming') return;
                  window.__CHAT_CALL_STATE = 'inCall';
                  window.__CHAT_CALL_PEER = payload.from_id;
                  window.__CHAT_CALL_START_TIME = Date.now();
                  window.__CHAT_CALL_IS_CALLER = false;

                  // Save minimal call state (for cross-tab sync)
                  this.saveCallState();

                  // Notify other tabs that call was answered
                  if (this.callSyncChannel) {
                    this.callSyncChannel.postMessage({
                      type: 'call-answered',
                      data: {
                        peerId: payload.from_id
                      }
                    });
                  }

                  // Track call type for summary messages
                  window.__CHAT_CALL_WAS_VIDEO = isVideoCall;

                  // Pass isVideo from Pusher payload so answer() knows the intended call type
                  var answerOpts = Object.assign({}, options || {}, {
                    isVideo: isVideoCall,
                    recipientType: payload.caller_type || 'staff',
                    callerType: 'staff'
                  });
                  window.__chatCallManager.answer(window.chatConfig.currentUserId, payload.from_id, offer, answerOpts);

                  ChatCallUI.showInCall((payload.from_name || ''), () => {
                    window.__chatCallManager.hangup();
                    this.endCallWithSummary();
                    if (isVideoCall) {
                      const pipEl = document.getElementById('chat-call-pip');
                      if (pipEl) pipEl.parentNode.removeChild(pipEl);
                    }
                  }, {
                    avatarUrl,
                    isVideo: isVideoCall
                  });
                }, () => {
                  // Decline
                  this.declineCall(payload.from_id, payload);
                  window.__chatCallManager.hangup();
                  window.__CHAT_CALL_STATE = 'idle';
                  window.__CHAT_CALL_PEER = null;
                  window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;

                  // Clear call state from localStorage
                  localStorage.removeItem('prchat_call_state');

                  // Notify other tabs that call was declined
                  if (this.callSyncChannel) {
                    this.callSyncChannel.postMessage({
                      type: 'call-declined',
                      data: {
                        peerId: payload.from_id
                      }
                    });
                  }
                }, {
                  avatarUrl,
                  isVideo: isVideoCall
                });
              }
            } catch (e) {
              console.error('Call offer error:', e);
            }
          });

          // Answer to our offer (we are caller)
          callsChannel.bind('call-answer', (payload) => {
            try {
              prchatToggledTrace('toggled Pusher call-answer', {
                from_id: payload && payload.from_id,
                sdpChars: payload && typeof payload.sdp === 'string' ? payload.sdp.length : null
              });
              if (window.__chatNoAnswerTimer) {
                clearTimeout(window.__chatNoAnswerTimer);
                window.__chatNoAnswerTimer = null;
              }

              const answer = JSON.parse(payload.sdp);
              const isVideoCall = !!(window.__CHAT_CALL_WAS_VIDEO || payload.is_video === true || payload.is_video === 1 || payload.is_video === '1');
              window.__chatCallManager && window.__chatCallManager.handleRemoteAnswer(answer);

              if (window.ChatCallUI) {
                ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                ChatCallUI.closeModal && ChatCallUI.closeModal();
                window.__CHAT_CALL_STATE = 'inCall';
                window.__CHAT_CALL_PEER = payload.from_id;
                window.__CHAT_CALL_START_TIME = Date.now();
                window.__CHAT_CALL_IS_CALLER = true;

                const avatarUrl = payload.from_avatar || null;
                ChatCallUI.showInCall((payload.from_name || ''), () => {
                  window.__chatCallManager.hangup();
                  this.endCallWithSummary();
                  if (isVideoCall) {
                    const pipEl = document.getElementById('chat-call-pip');
                    if (pipEl) pipEl.parentNode.removeChild(pipEl);
                  }
                }, {
                  avatarUrl,
                  isVideo: isVideoCall
                });
              }
            } catch (e) {
              console.error('[prchat-call] toggled call-answer handler error:', e);
            }
          });

          // Call declined
          callsChannel.bind('call-declined', (payload) => {
            if (window.__chatNoAnswerTimer) {
              clearTimeout(window.__chatNoAnswerTimer);
              window.__chatNoAnswerTimer = null;
            }

            const declinedPeer = window.__CHAT_CALL_PEER;

            if (window.ChatCallUI) {
              ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
              ChatCallUI.closeModal && ChatCallUI.closeModal();
              ChatCallUI.showNotice && ChatCallUI.showNotice('<?php echo _l('chat_decline'); ?>', {
                autoCloseMs: 1500
              });
            }
            if (window.__chatCallManager && window.__chatCallManager.endFromRemote) {
              window.__chatCallManager.endFromRemote();
            }
            if (declinedPeer) {
              this.sendMissedCallMessage(declinedPeer);
            }
            window.__CHAT_CALL_STATE = 'idle';
            window.__CHAT_CALL_PEER = null;
            window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
            localStorage.removeItem('prchat_call_state');
          });

          // ICE candidates
          callsChannel.bind('call-ice', (payload) => {
            try {
              prchatToggledTrace('toggled Pusher call-ice', payload.from_id, '->', payload.to_id);
              const cand = JSON.parse(payload.candidate);
              window.__chatCallManager && window.__chatCallManager.addIceCandidate(cand);
            } catch (e) {
              prchatToggledTrace('toggled call-ice error', e);
            }
          });

          // Call hangup
          callsChannel.bind('call-hangup', (payload) => {
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

            // Remove PiP container
            const pipEl = document.getElementById('chat-call-pip');
            if (pipEl) pipEl.parentNode.removeChild(pipEl);

            // Hide mute indicator
            const muteIndicator = document.getElementById('remote-mute-indicator');
            if (muteIndicator) muteIndicator.style.display = 'none';

            // Send call summary BEFORE resetting state (captures wasCaller/peerId/duration)
            this.endCallWithSummary();

            // Reset remaining call state
            window.__CHAT_CALL_STATE = 'idle';
            window.__CHAT_CALL_PEER = null;
            window.__CHAT_CALL_START_TIME = null;
            window.__CHAT_CALL_IS_CALLER = false;

            // Clear localStorage
            localStorage.removeItem('prchat_call_state');
          });

          // Remote user mute status changed
          callsChannel.bind('call-mute-status', (payload) => {
            if (payload.from_id != window.__CHAT_CALL_PEER) return;

            const isMuted = payload.is_muted;
            this.showRemoteMuteIndicator(isMuted);
          });

          // Initialize cross-tab call synchronization
          this.initCallSync();

          // Clean up any call state on page load (end calls on reload)
          this.cleanupCallStateOnReload();
        },

        // Initialize BroadcastChannel for cross-tab call synchronization
        initCallSync() {
          if (typeof BroadcastChannel === 'undefined') return;

          this.callSyncChannel = new BroadcastChannel('prchat-call-sync');

          // Listen for call state changes from other tabs
          this.callSyncChannel.onmessage = (event) => {
            const {
              type,
              data
            } = event.data;

            switch (type) {
              case 'call-answered':
                // Another tab answered the call, close incoming call UI in this tab
                if (window.__CHAT_CALL_STATE === 'ringingIncoming') {
                  if (window.ChatCallUI) {
                    window.ChatCallUI.stopRingtone && window.ChatCallUI.stopRingtone();
                    window.ChatCallUI.closeModal && window.ChatCallUI.closeModal();
                  }
                  window.__CHAT_CALL_STATE = 'idle';
                  window.__CHAT_CALL_PEER = null;
                  window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 2000;
                }
                break;

              case 'call-declined':
                // Another tab declined the call, close incoming call UI in this tab
                if (window.__CHAT_CALL_STATE === 'ringingIncoming') {
                  if (window.ChatCallUI) {
                    window.ChatCallUI.stopRingtone && window.ChatCallUI.stopRingtone();
                    window.ChatCallUI.closeModal && window.ChatCallUI.closeModal();
                  }
                  window.__CHAT_CALL_STATE = 'idle';
                  window.__CHAT_CALL_PEER = null;
                }
                break;

              case 'call-started':
                // Another tab started a call, suppress incoming calls in this tab
                window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 60000; // Suppress for 1 minute
                break;
            }
          };
        },

        // Save minimal call state to localStorage (used for cleanup on reload)
        saveCallState() {
          const callState = {
            state: window.__CHAT_CALL_STATE,
            peer: window.__CHAT_CALL_PEER,
            startTime: window.__CHAT_CALL_START_TIME,
            isCaller: window.__CHAT_CALL_IS_CALLER,
            timestamp: Date.now()
          };
          localStorage.setItem('prchat_call_state', JSON.stringify(callState));
        },

        // Clean up call state on reload - end any active calls
        cleanupCallStateOnReload() {
          try {
            const savedState = localStorage.getItem('prchat_call_state');
            if (!savedState) return;

            const callState = JSON.parse(savedState);

            // If there was an active call, send hangup signal via backend API
            // Check for all possible call states: inCall, outgoing, ringingOutgoing, ringingIncoming
            if ((callState.state === 'inCall' ||
                callState.state === 'outgoing' ||
                callState.state === 'ringingOutgoing' ||
                callState.state === 'ringingIncoming') && callState.peer) {

              // Send hangup via backend API (synchronously using fetch with keepalive)
              const hangupUrl = '<?= admin_url("prchat/Calls_Controller/hangup") ?>';
              const formData = new URLSearchParams({
                from_id: window.chatConfig.currentUserId,
                to_id: callState.peer,
                <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
              });

              // Use keepalive to ensure request completes even if page unloads
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
          } catch (e) {
            console.error('Failed to cleanup call state:', e);
            localStorage.removeItem('prchat_call_state');
          }
        },

        // Decline an incoming call
        declineCall(callerId, offerPayload) {
          try {
            const url = '<?= admin_url("prchat/Calls_Controller/decline") ?>';
            const body = new URLSearchParams({
              from_id: window.chatConfig.currentUserId,
              to_id: callerId,
              recipient_type: (offerPayload && offerPayload.caller_type) ? offerPayload.caller_type : 'staff',
              <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
            });
            fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: body.toString()
            });
          } catch (e) {}
        },

        endCallWithSummary() {
          const peerId = window.__CHAT_CALL_PEER;
          const wasCaller = window.__CHAT_CALL_IS_CALLER;
          const callWasVideo = !!window.__CHAT_CALL_WAS_VIDEO;
          let durationSeconds = 0;

          if (window.__CHAT_CALL_START_TIME) {
            durationSeconds = Math.floor((Date.now() - window.__CHAT_CALL_START_TIME) / 1000);
          }

          window.__CHAT_CALL_STATE = 'idle';
          window.__CHAT_CALL_PEER = null;
          window.__CHAT_CALL_SUPPRESS_UNTIL = Date.now() + 1500;
          window.__CHAT_CALL_START_TIME = null;
          window.__CHAT_CALL_IS_CALLER = false;
          window.__CHAT_CALL_WAS_VIDEO = false;

          localStorage.removeItem('prchat_call_state');

          if (peerId && wasCaller) {
            const dur = Math.max(1, durationSeconds);
            const lockKey = 'prchat_call_summary_lock_' + String(peerId);
            const existing = localStorage.getItem(lockKey);
            if (existing && (Date.now() - parseInt(existing, 10)) < 5000) return;
            localStorage.setItem(lockKey, String(Date.now()));
            setTimeout(() => localStorage.removeItem(lockKey), 5000);
            this.sendCallSummaryMessage(peerId, dur, callWasVideo);
          }
        },

        sendCallSummaryMessage(peerId, durationSeconds, callWasVideo) {
          const dur = Math.max(1, parseInt(durationSeconds, 10) || 0);
          const isVideoCall = (arguments.length >= 3) ? !!callWasVideo : !!window.__CHAT_CALL_WAS_VIDEO;
          window.__CHAT_CALL_WAS_VIDEO = false;
          const callType = isVideoCall ? 'video' : 'voice';
          const callerId = window.chatConfig.currentUserId;
          const message = '[CALL:' + callType + ':' + dur + ':' + callerId + ':' + peerId + ']';

          const params = new URLSearchParams({
            reciever: peerId,
            to: peerId,
            msg: message,
            from: callerId,
            typing: 'false'
          });
          params.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

          const self = this;
          fetch(window.chatConfig.apiUrls.sendMessage, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
          }).then(() => {
            if (self.selectedUser && String(self.selectedUser.staffid) === String(peerId)) {
              self.chatMessages.push({
                id: Date.now(),
                sender_id: callerId,
                message: message,
                time_sent: new Date().toISOString()
              });
              self.$nextTick(() => {
                self.scrollToBottom();
              });
            }
          }).catch(err => {
            console.error('Failed to send call summary:', err);
          });
        },

        sendMissedCallMessage(peerId) {
          const callType = window.__CHAT_CALL_WAS_VIDEO ? 'missed_video' : 'missed_voice';
          window.__CHAT_CALL_WAS_VIDEO = false;
          const callerId = window.chatConfig.currentUserId;
          const message = '[CALL:' + callType + ':0:' + callerId + ':' + peerId + ']';

          const params = new URLSearchParams({
            reciever: peerId,
            to: peerId,
            msg: message,
            from: callerId,
            typing: 'false'
          });
          params.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

          const self = this;
          fetch(window.chatConfig.apiUrls.sendMessage, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
          }).then(() => {
            if (self.selectedUser && String(self.selectedUser.staffid) === String(peerId)) {
              self.chatMessages.push({
                id: Date.now(),
                sender_id: callerId,
                message: message,
                time_sent: new Date().toISOString()
              });
              self.$nextTick(() => {
                self.scrollToBottom();
              });
            }
          }).catch(err => {
            console.error('Failed to send missed call message:', err);
          });
        },

        // Show/hide mute indicator on remote video
        showRemoteMuteIndicator(isMuted) {
          let indicator = document.getElementById('remote-mute-indicator');

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
              indicator.innerHTML = '<i class="fa fa-microphone-slash" style="color:#ef4444;"></i> <span><?php echo _l('chat_they_muted'); ?></span>';
              document.body.appendChild(indicator);
            }
            indicator.style.display = 'flex';
          } else {
            // Hide indicator
            if (indicator) {
              indicator.style.display = 'none';
            }
          }
        },

        // Start a voice call with a user
        startCall(peerId) {
          try {
            if (typeof ChatCallManager === 'undefined') {
              console.error('ChatCallManager not available');
              return;
            }
            peerId = parseInt(peerId, 10);
            if (!peerId || peerId < 1) {
              console.error('Invalid peer ID for call', peerId);
              return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
              this.showNotification('<?php echo _l('chat_audio_not_supported'); ?>', 'error');
              return;
            }
            if (!window.__chatCallManager) {
              window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
            }
            window.__chatCallManager.onRemoteTrack = (stream) => {
              let audio = document.getElementById('chat-call-remote');
              if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'chat-call-remote';
                audio.autoplay = true;
                document.body.appendChild(audio);
              }
              audio.srcObject = stream;
            };

            const user = this.users.find(u => u.staffid == peerId);
            const userName = user ? `${user.firstname} ${user.lastname}` : '';
            const avatarUrl = user ? this.getUserAvatar(user) : null;
            const self = this;

            window.__CHAT_CALL_STATE = 'ringingOutgoing';
            window.__CHAT_CALL_PEER = peerId;
            window.__CHAT_CALL_WAS_VIDEO = false;
            self.saveCallState();

            if (self.callSyncChannel) {
              self.callSyncChannel.postMessage({
                type: 'call-started',
                data: {
                  peerId
                }
              });
            }

            window.__chatCallManager.call(window.chatConfig.currentUserId, peerId, false, {
              recipientType: 'staff',
              callerType: 'staff'
            }).catch((err) => {
              console.error('Failed to start voice call:', err);
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              if (window.__chatCallManager) window.__chatCallManager.hangup();
              if (window.ChatCallUI) {
                ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                ChatCallUI.closeModal && ChatCallUI.closeModal();
              }
              self.showNotification('<?php echo _l('chat_call_error'); ?>', 'error');
            });

            if (window.ChatCallUI) {
              ChatCallUI.showOutgoingCall(userName, () => {
                if (window.__chatNoAnswerTimer) {
                  clearTimeout(window.__chatNoAnswerTimer);
                  window.__chatNoAnswerTimer = null;
                }
                const cancelledPeer = window.__CHAT_CALL_PEER;
                window.__chatCallManager && window.__chatCallManager.hangup();
                window.__CHAT_CALL_STATE = 'idle';
                window.__CHAT_CALL_PEER = null;
                localStorage.removeItem('prchat_call_state');
                if (cancelledPeer) self.sendMissedCallMessage(cancelledPeer);
              }, {
                avatarUrl: avatarUrl,
                isVideo: false
              });
            }

            if (window.__chatNoAnswerTimer) clearTimeout(window.__chatNoAnswerTimer);
            window.__chatNoAnswerTimer = setTimeout(() => {
              try {
                if (window.__CHAT_CALL_STATE !== 'inCall') {
                  const missedPeer = window.__CHAT_CALL_PEER;
                  window.__chatCallManager && window.__chatCallManager.hangup();
                  if (window.ChatCallUI) {
                    ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                    ChatCallUI.closeModal && ChatCallUI.closeModal();
                    ChatCallUI.showNotice && ChatCallUI.showNotice('<?php echo _l('chat_no_answer'); ?>', {
                      autoCloseMs: 2500
                    });
                  }
                  if (missedPeer) self.sendMissedCallMessage(missedPeer);
                  window.__CHAT_CALL_STATE = 'idle';
                  window.__CHAT_CALL_PEER = null;
                  localStorage.removeItem('prchat_call_state');
                }
              } catch (e) {
                console.error('No-answer timeout error:', e);
              }
            }, 15000);
          } catch (error) {
            console.error('Error starting call:', error);
            if (window.ChatCallUI) {
              ChatCallUI.showNotice && ChatCallUI.showNotice('<?php echo _l('chat_call_error'); ?>', {
                autoCloseMs: 2500
              });
            }
          }
        },

        startVideoCall(peerId) {
          try {
            if (typeof ChatCallManager === 'undefined') {
              console.error('ChatCallManager not available');
              return;
            }
            peerId = parseInt(peerId, 10);
            if (!peerId || peerId < 1) {
              console.error('Invalid peer ID for video call', peerId);
              return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
              this.showNotification('<?php echo _l('chat_video_not_supported'); ?>', 'error');
              return;
            }
            if (!window.__chatCallManager) {
              window.__chatCallManager = new ChatCallManager(new ChatSignalingClient({}));
            }

            window.__chatCallManager.onLocalTrack = (stream) => {
              const pip = document.getElementById('chat-call-pip');
              let localVideo = document.getElementById('chat-call-local-video');
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
              localVideo.play().catch(err => {
                console.error('[VIDEO CALL] local play error:', err);
              });
            };

            window.__chatCallManager.onRemoteTrack = (stream) => {
              const pip = document.getElementById('chat-call-pip');
              let remoteVideo = document.getElementById('chat-call-remote-video');
              if (!remoteVideo) {
                remoteVideo = document.createElement('video');
                remoteVideo.id = 'chat-call-remote-video';
                remoteVideo.autoplay = false;
                remoteVideo.playsinline = true;
                remoteVideo.muted = false;
                if (pip) pip.insertBefore(remoteVideo, pip.firstChild);
                else document.body.appendChild(remoteVideo);
              }
              remoteVideo.onloadedmetadata = null;
              if (remoteVideo.srcObject) {
                remoteVideo.pause();
                remoteVideo.srcObject = null;
              }
              remoteVideo.srcObject = stream;
              remoteVideo.style.display = 'block';
              remoteVideo.onloadedmetadata = () => {
                remoteVideo.play().catch(err => {
                  console.error('[VIDEO CALL] remote play error:', err);
                });
              };
            };

            const user = this.users.find(u => u.staffid == peerId);
            const userName = user ? `${user.firstname} ${user.lastname}` : '';
            const avatarUrl = user ? this.getUserAvatar(user) : null;
            const self = this;

            window.__CHAT_CALL_STATE = 'ringingOutgoing';
            window.__CHAT_CALL_PEER = peerId;
            window.__CHAT_CALL_WAS_VIDEO = true;
            self.saveCallState();

            if (self.callSyncChannel) {
              self.callSyncChannel.postMessage({
                type: 'call-started',
                data: {
                  peerId,
                  isVideo: true
                }
              });
            }

            window.__chatCallManager.call(window.chatConfig.currentUserId, peerId, true, {
              recipientType: 'staff',
              callerType: 'staff'
            }).catch((err) => {
              console.error('Failed to start video call:', err);
              window.__CHAT_CALL_STATE = 'idle';
              window.__CHAT_CALL_PEER = null;
              if (window.__chatCallManager) window.__chatCallManager.hangup();
              if (window.ChatCallUI) {
                ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                ChatCallUI.closeModal && ChatCallUI.closeModal();
              }
              const pipErr = document.getElementById('chat-call-pip');
              if (pipErr) pipErr.parentNode.removeChild(pipErr);
              self.showNotification('<?php echo _l('chat_call_error'); ?>', 'error');
            });

            if (window.ChatCallUI) {
              ChatCallUI.showOutgoingCall(userName, () => {
                if (window.__chatNoAnswerTimer) {
                  clearTimeout(window.__chatNoAnswerTimer);
                  window.__chatNoAnswerTimer = null;
                }
                const cancelledPeer = window.__CHAT_CALL_PEER;
                window.__chatCallManager && window.__chatCallManager.hangup();
                window.__CHAT_CALL_STATE = 'idle';
                window.__CHAT_CALL_PEER = null;
                localStorage.removeItem('prchat_call_state');
                const pipCancel = document.getElementById('chat-call-pip');
                if (pipCancel) pipCancel.parentNode.removeChild(pipCancel);
                if (cancelledPeer) self.sendMissedCallMessage(cancelledPeer);
              }, {
                avatarUrl: avatarUrl,
                isVideo: true
              });
            }

            if (window.__chatNoAnswerTimer) clearTimeout(window.__chatNoAnswerTimer);
            window.__chatNoAnswerTimer = setTimeout(() => {
              try {
                if (window.__CHAT_CALL_STATE !== 'inCall') {
                  const missedPeer = window.__CHAT_CALL_PEER;
                  window.__chatCallManager && window.__chatCallManager.hangup();
                  if (window.ChatCallUI) {
                    ChatCallUI.stopRingtone && ChatCallUI.stopRingtone();
                    ChatCallUI.closeModal && ChatCallUI.closeModal();
                    ChatCallUI.showNotice && ChatCallUI.showNotice('<?php echo _l('chat_no_answer'); ?>', {
                      autoCloseMs: 2500
                    });
                  }
                  if (missedPeer) self.sendMissedCallMessage(missedPeer);
                  window.__CHAT_CALL_STATE = 'idle';
                  window.__CHAT_CALL_PEER = null;
                  const pipTimeout = document.getElementById('chat-call-pip');
                  if (pipTimeout) pipTimeout.parentNode.removeChild(pipTimeout);
                }
              } catch (e) {
                console.error('No-answer timeout error:', e);
              }
            }, 15000);
          } catch (error) {
            console.error('Error starting video call:', error);
            if (window.ChatCallUI) {
              ChatCallUI.showNotice && ChatCallUI.showNotice('<?php echo _l('chat_call_error'); ?>', {
                autoCloseMs: 2500
              });
            }
          }
        },
      <?php endif; ?>

      async handlePresenceUpdate(members) {
        // First, mark all users as offline
        this.users.forEach(user => {
          user.status = 'offline';
        });

        // Collect online user IDs
        this.onlineUsers = [];
        const onlineUserIds = [];

        members.each(member => {
          this.onlineUsers.push(member);
          if (member.id != this.currentUserId) {
            onlineUserIds.push(member.id);
          }
        });

        // Sync current statuses from backend for online users
        // This ensures we get the CURRENT status, not stale Pusher presence info
        // Pass skipSort=true to prevent individual sorts during bulk update
        await this.syncUserStatuses(onlineUserIds, true);

        // NOW sort the list once after all statuses are updated
        // This puts online users at the top without moving them during the sync
        this.filterUsers();
      },

      async syncUserStatuses(userIds, skipSort = false) {
        if (userIds.length === 0) return;

        try {
          const response = await fetch(window.chatConfig.apiUrls.users, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          });

          if (!response.ok) return;

          const text = await response.text();
          const data = JSON.parse(text);
          const usersArray = Array.isArray(data) ? data : (data.users || []);

          // Update status for each online user from backend data
          userIds.forEach(userId => {
            const backendUser = usersArray.find(u => u.staffid == userId);
            if (backendUser && backendUser.status) {
              this.updateUserStatus(userId, backendUser.status, skipSort);
            } else {
              // Fallback to online if no status found
              this.updateUserStatus(userId, 'online', skipSort);
            }
          });
        } catch (error) {
          console.error('Failed to sync user statuses:', error);
        }
      },

      async handleUserOnline(member) {
        if (this.pendingRemoves[member.id]) {
          clearTimeout(this.pendingRemoves[member.id]);
          delete this.pendingRemoves[member.id];
        }

        if (!this.onlineUsers.find(u => u.id === member.id)) {
          this.onlineUsers.push(member);
        }

        // Only use server-side justLoggedIn flag (set during actual login)
        const isActuallyComingOnline = member.info?.justLoggedIn === true;

        if (member.id != this.currentUserId) {
          await this.syncUserStatuses([member.id], !isActuallyComingOnline);
        }
      },

      handleUserOffline(member) {
        // Add 5-second delay before marking user offline
        // This prevents flickering when users reload/reconnect quickly
        this.pendingRemoves[member.id] = setTimeout(() => {
          this.onlineUsers = this.onlineUsers.filter(u => u.id !== member.id);
          this.updateUserStatus(member.id, 'offline');
          delete this.pendingRemoves[member.id];
        }, 5000);
      },

      updateUserStatus(userId, status, skipSort = false) {
        // Skip if this is the current user (they're not in their own contact list)
        if (userId == this.currentUserId) {
          return;
        }

        const userIndex = this.users.findIndex(u => u.staffid == userId);

        if (userIndex !== -1) {
          const oldStatus = this.users[userIndex].status;

          // Vue 3: Direct assignment is reactive
          this.users[userIndex].status = status;

          // Also update in active chats if user has open chat
          const chat = this.activeChats.find(c => c.userId == userId);
          if (chat && chat.user) {
            chat.user.status = status;
          }

          // Re-sort users when status changes (especially when coming online/offline)
          // Skip sorting during bulk updates (e.g., initial presence sync)
          if (oldStatus !== status && !skipSort) {
            this.filterUsers();
          }
        }
      },

      handleIncomingMessage(data) {
        // Skip if message is from self or not addressed to us
        if (String(data.from) === String(this.currentUserId)) return;
        if (data.to && String(data.to) !== String(this.currentUserId)) return;

        const chat = this.activeChats.find(c => String(c.userId) === String(data.from));

        // Determine if we should play sound and show notifications
        // Sound should play if: notifications enabled AND (no chat open OR chat minimized OR main panel minimized)
        const chatIsOpen = chat && !chat.minimized;
        const shouldNotify = !chatIsOpen || this.isMinimized;

        if (chat) {
          chat.isTyping = false;
          if (this.typingTimeouts[`chat_typing_${data.from}`]) {
            clearTimeout(this.typingTimeouts[`chat_typing_${data.from}`]);
            delete this.typingTimeouts[`chat_typing_${data.from}`];
          }

          const newMessage = {
            id: data.last_insert_id,
            sender_id: data.from,
            message: data.message,
            time_sent: new Date(),
            viewed: false,
            sender_image: null
          };

          chat.messages.push(newMessage);

          this.$nextTick(() => {
            this.scrollToBottom(chat);
          });
        }

        const _isCallMsg = data.is_call || (typeof data.message === 'string' && /^\[CALL:(voice|video):\d/.test(data.message));

        const user = this.users.find(u => u.staffid == data.from);
        if (user) {
          user.isTyping = false;
          user.lastMessage = data.message;
          user.lastMessageSenderId = data.from;
          user.lastMessageTime = new Date().toISOString();
          user.lastSeen = new Date().toISOString();
          if (!_isCallMsg) {
            user.unreadCount = (user.unreadCount || 0) + 1;
          }
        }

        // Re-sort users to move this conversation to top
        this.filterUsers();

        // Check if sender is muted
        const isSenderMuted = this.mutedUsers.includes(String(data.from));

        // Play notification sound if needed — skip for completed call messages
        if (!_isCallMsg && !isSenderMuted && shouldNotify) {
          if (this.notificationsEnabled) {
            this.playNotificationSound();
          }

          if (this.isMinimized && !chatIsOpen && typeof FloatingChatNotifications !== 'undefined') {
            var notifMsg = (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.cleanNotificationText) ? PrchatSafeRenderer.cleanNotificationText(data.message) : data.message.replace(/\[REPLY:[^\]]*\]\s*/, '').replace(/<[^>]*>/g, '').substring(0, 50) + '...';
            FloatingChatNotifications.add({
              from: data.from,
              fromName: data.from_name || '<?php echo _l('chat_staff_member'); ?>',
              type: 'staff',
              message: notifMsg,
              avatar: fetchUserAvatar(data.from, data.sender_image) || site_url + 'assets/images/user-placeholder.jpg'
            });
          }
        } else if (!_isCallMsg && !isSenderMuted && this.notificationsEnabled && chat) {
          this.playNotificationSound();
        }
      },

      handleStaffMessageEdited(data) {
        const mid = data.message_id || data.id;
        if (!mid || !data.rendered_message) {
          return;
        }
        const otherId = String(data.from) === String(this.currentUserId) ? data.to : data.from;
        const chat = this.activeChats.find(c => String(c.userId) === String(otherId));
        if (!chat) {
          return;
        }
        const msg = chat.messages.find(m => String(m.id) === String(mid));
        if (msg) {
          msg.message = data.rendered_message;
          msg.edited_at = data.edited_at || new Date().toISOString();
        }
        const last = chat.messages.length ? chat.messages[chat.messages.length - 1] : null;
        const isLast = last && String(last.id) === String(mid);
        if (isLast) {
          const user = this.users.find(u => String(u.staffid) === String(otherId));
          if (user) {
            user.lastMessage = data.rendered_message;
            user.lastMessageSenderId = data.from;
            user.lastMessageTime = data.edited_at || new Date().toISOString();
          }
          this.filterUsers();
        }
      },

      async refreshUserPreview(userId) {
        try {
          const response = await fetch(window.chatConfig.apiUrls.users, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          });

          if (!response.ok) return;

          const text = await response.text();
          const data = JSON.parse(text);
          const usersArray = Array.isArray(data) ? data : (data.users || []);
          const backendUser = usersArray.find(u => String(u.staffid) === String(userId));
          if (!backendUser) return;

          const user = this.users.find(u => String(u.staffid) === String(userId));
          if (!user) return;

          user.lastMessage = backendUser.message || null;
          user.lastMessageSenderId = backendUser.message_sender_id || null;
          user.lastMessageTime = backendUser.time_sent || null;
          user.unreadCount = parseInt(backendUser.unread_count) || 0;

          this.filterUsers();
        } catch (error) {
          console.error('Failed to refresh user preview:', error);
        }
      },

      handleStaffMessageDeleted(data) {
        const mid = data && (data.message_id || data.id);
        if (!mid) return;

        const fromId = String(data.from || '');
        const toId = String(data.to || '');
        const me = String(this.currentUserId);

        if (fromId !== me && toId !== me) return;

        const otherId = fromId === me ? toId : fromId;
        if (!otherId) return;

        const chat = this.activeChats.find(c => String(c.userId) === String(otherId));
        if (chat) {
          const idx = chat.messages.findIndex(m => String(m.id) === String(mid));
          if (idx !== -1) {
            const wasLast = idx === chat.messages.length - 1;
            chat.messages.splice(idx, 1);

            if (wasLast) {
              const last = chat.messages.length ? chat.messages[chat.messages.length - 1] : null;
              const user = this.users.find(u => String(u.staffid) === String(otherId));
              if (user) {
                if (last) {
                  user.lastMessage = last.message || null;
                  user.lastMessageSenderId = last.sender_id || null;
                  user.lastMessageTime = last.time_sent || null;
                } else {
                  user.lastMessage = null;
                  user.lastMessageSenderId = null;
                  user.lastMessageTime = null;
                }
              }
            }
          }
        }

        this.refreshUserPreview(otherId);
      },

      handleClientMessage(data) {
        // Client-to-staff message: show floating notification popup
        if (data.to !== 'staff_' + this.currentUserId) return;

        var clientContactId = String((data.from || '').replace(/^client_/, ''));
        var isClientMuted = this.mutedClients && this.mutedClients.indexOf(clientContactId) !== -1;

        // Play notification sound (skip if client muted)
        if (!isClientMuted && this.notificationsEnabled) {
          this.playNotificationSound();
        }

        // Show floating notification popup (skip if client muted)
        if (!isClientMuted && typeof FloatingChatNotifications !== 'undefined') {
          var clientName = data.contact_full_name || '<?= _l("chat_client"); ?>';
          var clientNotifMsg = (typeof PrchatSafeRenderer !== 'undefined' && PrchatSafeRenderer.cleanNotificationText) ? PrchatSafeRenderer.cleanNotificationText(data.message) : data.message.replace(/\[REPLY:[^\]]*\]\s*/, '').replace(/<[^>]*>/g, '').substring(0, 50) + '...';
          FloatingChatNotifications.add({
            from: data.from,
            fromName: clientName,
            type: 'client',
            message: clientNotifMsg,
            avatar: data.client_image_path || site_url + 'assets/images/user-placeholder.jpg'
          });
        }
      },

      handleClientOnline(member) {},

      handleClientOffline(member) {
        // No client list UI in toggled view — nothing to update
      },

      handleTypingEvent(data) {
        const chat = this.activeChats.find(c => c.userId == data.from);
        const user = this.users.find(u => u.staffid == data.from);
        const tKey = `chat_typing_${data.from}`;

        if (this.typingTimeouts[tKey]) {
          clearTimeout(this.typingTimeouts[tKey]);
          delete this.typingTimeouts[tKey];
        }

        if (data.message === 'true') {
          if (chat) chat.isTyping = true;
          if (user) user.isTyping = true;

          this.typingTimeouts[tKey] = setTimeout(() => {
            if (chat) chat.isTyping = false;
            if (user) user.isTyping = false;
            delete this.typingTimeouts[tKey];
          }, 3500);
        } else {
          if (chat) chat.isTyping = false;
          if (user) user.isTyping = false;
        }
      },

      handleStatusChange(data) {
        if (data && data.user_id && data.status) {
          this.updateUserStatus(data.user_id, data.status);
        }
      },

      handleConnectionChange(event) {
        // Track connection status changes (online/offline)
        if (event.type === 'offline') {
          this.connectionStatus = 'offline';
          this.showNotification('<?php echo _l('chat_connection_lost'); ?>', 'warning');
        } else if (event.type === 'online') {
          this.connectionStatus = 'connected';
          this.showNotification('<?php echo _l('chat_connection_restored'); ?>', 'success');
        }
      },

      handleMessageSeen(data) {
        // data is an array of message objects that were marked as read
        if (Array.isArray(data)) {
          data.forEach(messageData => {
            // When User B reads messages from User A, User A should see double checkmarks
            const chat = this.activeChats.find(c => c.userId == messageData.reciever_id);
            if (chat) {
              let messageUpdated = false;

              // First try exact ID match
              for (let i = 0; i < chat.messages.length; i++) {
                if (chat.messages[i].id == messageData.msg_id) {
                  chat.messages.splice(i, 1, {
                    ...chat.messages[i],
                    viewed: true,
                    viewed_at: messageData.viewed_at || new Date().toISOString(),
                    viewed_at_formatted: messageData.viewed_at_formatted || ''
                  });
                  messageUpdated = true;
                  break;
                }
              }

              // Fallback: If no exact match, find most recent unread message sent by current user
              if (!messageUpdated) {
                for (let i = chat.messages.length - 1; i >= 0; i--) {
                  if (chat.messages[i].sender_id == this.currentUserId && !chat.messages[i].viewed) {
                    chat.messages.splice(i, 1, {
                      ...chat.messages[i],
                      viewed: true,
                      viewed_at: messageData.viewed_at || new Date().toISOString(),
                      viewed_at_formatted: messageData.viewed_at_formatted || ''
                    });
                    break;
                  }
                }
              }
            }
          });
        } else {
          // Handle single object format
          if (data && data.msg_id && data.reciever_id) {
            this.handleMessageSeen([data]);
          }
        }
      },



      debouncedFilterUsers() {
        clearTimeout(this._filterDebounce);
        this._filterDebounce = setTimeout(() => this.filterUsers(), 300);
      },

      setStatusFilter(filter) {
        this.activeFilter = filter;
        localStorage.setItem('prchat_toggled_filter', filter);
        this.filterUsers();
      },

      getFilterCount(filter) {
        if (filter === 'all') return this.users.length;
        if (filter === 'unread') return this.users.filter(u => u.unreadCount > 0).length;
        return this.users.filter(u => u.status === filter).length;
      },

      filterUsers() {
        let usersToFilter = [...this.users];

        // Apply status filter
        if (this.activeFilter !== 'all') {
          if (this.activeFilter === 'unread') {
            usersToFilter = usersToFilter.filter(u => u.unreadCount > 0);
          } else {
            usersToFilter = usersToFilter.filter(u => u.status === this.activeFilter);
          }
        }

        // Apply search filter if search query exists
        if (this.searchQuery.trim()) {
          usersToFilter = usersToFilter.filter(user =>
            `${user.firstname} ${user.lastname}`.toLowerCase()
            .includes(this.searchQuery.toLowerCase())
          );
        }

        // Sort users: Online first, then alphabetically
        this.filteredUsers = this.sortUsers(usersToFilter);
      },

      sortUsers(users) {
        return users.sort((a, b) => {
          // Pinned contacts always first
          const aPinned = this.pinnedUsers.includes(String(a.staffid));
          const bPinned = this.pinnedUsers.includes(String(b.staffid));
          if (aPinned && !bPinned) return -1;
          if (!aPinned && bPinned) return 1;

          // Unread messages next
          const aUnread = a.unreadCount || 0;
          const bUnread = b.unreadCount || 0;
          if (aUnread > 0 && bUnread === 0) return -1;
          if (aUnread === 0 && bUnread > 0) return 1;

          // Then by most recent message time (newest first)
          const aTime = a.lastMessageTime ? new Date(a.lastMessageTime).getTime() : 0;
          const bTime = b.lastMessageTime ? new Date(b.lastMessageTime).getTime() : 0;
          if (aTime !== bTime) {
            return bTime - aTime;
          }

          // Then by online status
          const statusPriority = {
            online: 1,
            away: 2,
            busy: 3,
            offline: 4
          };
          const aStatus = statusPriority[a.status] || 4;
          const bStatus = statusPriority[b.status] || 4;
          if (aStatus !== bStatus) {
            return aStatus - bStatus;
          }

          // Finally alphabetically
          const aName = `${a.firstname} ${a.lastname}`.toLowerCase();
          const bName = `${b.firstname} ${b.lastname}`.toLowerCase();
          return aName.localeCompare(bName);
        });
      },

      async loadPinMuteSettings() {
        try {
          const response = await fetch(window.chatConfig.apiUrls.getPinMuteSettings || (window.chatConfig.siteUrl + 'prchat/Prchat_Controller/getPinMuteSettings'), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          if (response.ok) {
            const settings = await response.json();
            this.pinnedUsers = (settings.pinned_staff || []).map(String);
            this.mutedUsers = (settings.muted_staff || []).map(String);
            this.mutedClients = (settings.muted_clients || []).map(String);
          }
        } catch (e) {
          // Silently fail - pin/mute are non-critical
        }
      },

      isUserPinned(user) {
        return this.pinnedUsers.includes(String(user.staffid));
      },

      isUserMuted(user) {
        return this.mutedUsers.includes(String(user.staffid));
      },

      async togglePin(user) {
        this.contextMenu.visible = false;
        try {
          const formData = new FormData();
          formData.append('type', 'staff');
          formData.append('target_id', String(user.staffid));
          const response = await fetch(window.chatConfig.apiUrls.togglePin || (window.chatConfig.siteUrl + 'prchat/Prchat_Controller/togglePin'), {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
          });
          if (response.ok) {
            const res = await response.json();
            if (res.success) {
              this.pinnedUsers = (res.ids || []).map(String);
              this.filterUsers();
            }
          }
        } catch (e) {
          console.error('Failed to toggle pin:', e);
        }
      },

      async toggleMute(user) {
        this.contextMenu.visible = false;
        try {
          const formData = new FormData();
          formData.append('type', 'staff');
          formData.append('target_id', String(user.staffid));
          const response = await fetch(window.chatConfig.apiUrls.toggleMute || (window.chatConfig.siteUrl + 'prchat/Prchat_Controller/toggleMute'), {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
          });
          if (response.ok) {
            const res = await response.json();
            if (res.success) {
              this.mutedUsers = (res.ids || []).map(String);
              const msg = res.muted ? '<?php echo _l("chat_muted"); ?>' : '<?php echo _l("chat_unmute_conversation"); ?>';
              if (typeof alert_float !== 'undefined') alert_float('success', msg);
            }
          }
        } catch (e) {
          console.error('Failed to toggle mute:', e);
        }
      },

      showContextMenu(event, user) {
        event.preventDefault();
        this.contextMenu = {
          visible: true,
          x: Math.min(event.clientX, window.innerWidth - 180),
          y: Math.min(event.clientY, window.innerHeight - 100),
          userId: user.staffid,
          user: user
        };
      },

      hideContextMenu() {
        this.contextMenu.visible = false;
      },

      async openChat(user) {
        // Remove floating notification for this user when chat is opened
        if (typeof FloatingChatNotifications !== 'undefined' && typeof FloatingChatNotifications.removeOne === 'function') {
          FloatingChatNotifications.removeOne(String(user.staffid));
        }

        let chat = this.activeChats.find(c => c.userId == user.staffid);

        if (!chat) {

          // Calculate correct position for new chat next to main chat panel
          const windowWidth = window.innerWidth || 1920;
          const windowHeight = window.innerHeight || 800;
          const chatIndex = this.activeChats.length;
          const chatWidth = 330;
          const chatHeight = 400;
          const mainRight = this.mainPanelPos.right || 20;
          const mainBottom = this.mainPanelPos.bottom || 16;

          // Position next to main chat panel, stacking to the left, flush to bottom
          const left = Math.max(0, windowWidth - mainRight - 340 - chatWidth - (chatIndex * (chatWidth + 10)));
          const top = Math.max(0, windowHeight - chatHeight);

          // Create chat object with explicit reactivity
          this._zCounter = (this._zCounter || 10000) + 1;
          chat = {
            userId: user.staffid,
            user: user,
            messages: [], // This will be replaced entirely when loaded
            currentMessage: '',
            minimized: false,
            focused: true,
            isTyping: false,
            isTypingIndicatorSent: false, // Track if typing indicator was sent to prevent spam
            unreadCount: 0,
            isLoading: true, // Show loading state until messages arrive
            loadingMore: false,
            showFileUpload: false,
            isDragOver: false,
            messageOffset: 0,
            _renderKey: 0,
            editingMessageId: null,
            zIndex: this._zCounter,
            position: {
              left,
              top
            }
          };

          this.activeChats.push(chat);
          // Get the reactive proxy reference from the array
          const reactiveChat = this.activeChats[this.activeChats.length - 1];

          // Load messages with better error handling
          setTimeout(async () => {
            try {
              await this.loadChatMessages(reactiveChat);
            } catch (error) {
              if (error?.name !== 'AbortError') console.error(`Failed to load messages for new chat ${user.staffid}:`, error);
            }
          }, 50); // Small delay to let Vue render the DOM

        } else {
          chat.focused = true;
          chat.minimized = false;
          this.bringToFront(chat);
        }

        // Reset user's unread count
        user.unreadCount = 0;

        // Mark messages as read
        await this.markMessagesAsRead(user.staffid);

        // Scroll to bottom after opening chat
        this.$nextTick(() => {
          this.scrollToBottom({
            userId: user.staffid
          });
        });

        this.saveChatState();
      },


      // EMOJI PROCESSING FUNCTION
      processMessageForDisplay(message) {
        if (!message) return message;
        var el = document.createElement('textarea');
        el.innerHTML = message;
        message = el.value;
        if (window.CustomEmoji) {
          const customEmoji = new window.CustomEmoji();
          return customEmoji.shortcodesToEmojis(message);
        }
        return message;
      },

      startFabDrag(event) {
        event.preventDefault();
        event.stopPropagation();
        const fab = event.currentTarget;
        const startX = event.clientX;
        const startY = event.clientY;
        const startRight = this.mainPanelPos.right;
        const startBottom = this.mainPanelPos.bottom;
        let moved = false;
        const fabSize = 56;

        const onMove = (e) => {
          const dx = startX - e.clientX;
          const dy = startY - e.clientY;
          if (!moved && Math.abs(dx) < 4 && Math.abs(dy) < 4) return;
          if (!moved) {
            moved = true;
            fab.classList.add('is-dragging');
          }
          const maxRight = window.innerWidth - fabSize;
          const maxBottom = window.innerHeight - fabSize;
          this.mainPanelPos.right = Math.max(0, Math.min(startRight + dx, maxRight));
          this.mainPanelPos.bottom = Math.max(0, Math.min(startBottom + dy, maxBottom));
        };
        const onUp = () => {
          document.removeEventListener('mousemove', onMove);
          document.removeEventListener('mouseup', onUp);
          document.body.style.cursor = '';
          document.body.style.userSelect = '';
          fab.classList.remove('is-dragging');
          if (!moved) {
            this.toggleMinimize();
          } else {
            this.saveChatState();
          }
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
        document.body.style.cursor = 'grabbing';
        document.body.style.userSelect = 'none';
      },

      startFabDragTouch(event) {
        const fab = event.currentTarget;
        const touch = event.touches[0];
        const startX = touch.clientX;
        const startY = touch.clientY;
        const startRight = this.mainPanelPos.right;
        const startBottom = this.mainPanelPos.bottom;
        let moved = false;
        const fabSize = 56;

        const onMove = (e) => {
          e.preventDefault();
          const t = e.touches[0];
          const dx = startX - t.clientX;
          const dy = startY - t.clientY;
          if (!moved && Math.abs(dx) < 4 && Math.abs(dy) < 4) return;
          if (!moved) {
            moved = true;
            fab.classList.add('is-dragging');
          }
          const maxRight = window.innerWidth - fabSize;
          const maxBottom = window.innerHeight - fabSize;
          this.mainPanelPos.right = Math.max(0, Math.min(startRight + dx, maxRight));
          this.mainPanelPos.bottom = Math.max(0, Math.min(startBottom + dy, maxBottom));
        };
        const onEnd = () => {
          document.removeEventListener('touchmove', onMove);
          document.removeEventListener('touchend', onEnd);
          fab.classList.remove('is-dragging');
          if (!moved) {
            this.toggleMinimize();
          } else {
            this.saveChatState();
          }
        };
        document.addEventListener('touchmove', onMove, {
          passive: false
        });
        document.addEventListener('touchend', onEnd);
      },

      startMainPanelDrag(event) {
        if (event.target.closest('.header-btn') || event.target.closest('.header-actions')) return;
        event.preventDefault();
        this.mainPanelDrag.isDragging = true;
        this.mainPanelDrag.startX = event.clientX;
        this.mainPanelDrag.startY = event.clientY;
        this.mainPanelDrag.startRight = this.mainPanelPos.right;
        this.mainPanelDrag.startBottom = this.mainPanelPos.bottom;

        const onMove = (e) => {
          if (!this.mainPanelDrag.isDragging) return;
          const dx = this.mainPanelDrag.startX - e.clientX;
          const dy = this.mainPanelDrag.startY - e.clientY;
          const panelWidth = 340;
          const panelHeight = this.isMinimized ? 56 : 500;
          const maxRight = window.innerWidth - panelWidth;
          const maxBottom = window.innerHeight - panelHeight;
          const newRight = Math.max(0, Math.min(this.mainPanelDrag.startRight + dx, maxRight));
          const newBottom = Math.max(0, Math.min(this.mainPanelDrag.startBottom + dy, maxBottom));
          this.mainPanelPos.right = newRight;
          this.mainPanelPos.bottom = newBottom;
        };
        const onUp = () => {
          this.mainPanelDrag.isDragging = false;
          document.removeEventListener('mousemove', onMove);
          document.removeEventListener('mouseup', onUp);
          document.body.style.cursor = '';
          document.body.style.userSelect = '';
          this.saveChatState();
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
        document.body.style.cursor = 'grabbing';
        document.body.style.userSelect = 'none';
      },

      startDrag(event, chat) {
        // Prevent default to avoid text selection
        event.preventDefault();

        // Don't start drag on button clicks
        if (event.target.closest('.chat-action-btn')) {
          return;
        }

        this.dragState.isDragging = true;
        this.dragState.draggedChat = chat;
        this.dragState.startX = event.clientX;
        this.dragState.startY = event.clientY;

        // Get current position and actual element size
        const chatEl = event.currentTarget.parentElement;
        const rect = chatEl.getBoundingClientRect();
        this.dragState.startLeft = rect.left;
        this.dragState.startTop = rect.top;
        this.dragState.elWidth = chatEl.offsetWidth;
        this.dragState.elHeight = chatEl.offsetHeight;

        // Add event listeners
        document.addEventListener('mousemove', this.onMouseMove);
        document.addEventListener('mouseup', this.onMouseUp);

        // Add cursor style
        document.body.style.cursor = 'grabbing';
        document.body.style.userSelect = 'none';
      },

      onMouseMove(event) {
        if (!this.dragState.isDragging || !this.dragState.draggedChat) return;

        const deltaX = event.clientX - this.dragState.startX;
        const deltaY = event.clientY - this.dragState.startY;

        const newLeft = this.dragState.startLeft + deltaX;
        const newTop = this.dragState.startTop + deltaY;

        // Keep within viewport — use actual element size captured at drag start
        const chatWidth = this.dragState.elWidth || (this.dragState.draggedChat.minimized ? 260 : 330);
        const chatHeight = this.dragState.elHeight || (this.dragState.draggedChat.minimized ? 48 : 400);
        const maxLeft = window.innerWidth - chatWidth;
        const maxTop = window.innerHeight - chatHeight;

        this.dragState.draggedChat.position = {
          left: Math.max(0, Math.min(newLeft, maxLeft)),
          top: Math.max(0, Math.min(newTop, maxTop))
        };
      },

      onMouseUp() {
        if (this.dragState.isDragging) {
          this.dragState.isDragging = false;
          this.dragState.draggedChat = null;

          // Remove event listeners
          document.removeEventListener('mousemove', this.onMouseMove);
          document.removeEventListener('mouseup', this.onMouseUp);

          // Restore cursor
          document.body.style.cursor = '';
          document.body.style.userSelect = '';

          this.saveChatState();
        }
      },

      async loadChatMessages(chat, loadMore = false) {
        try {
          if (!loadMore) {
            chat.messages = [];
            chat.messageOffset = 0;
            chat.isLoading = true;
          }

          const url = `${window.chatConfig.apiUrls.getMessages}?from=${this.currentUserId}&to=${chat.userId}&limit=20&offset=${chat.messageOffset}`;

          const response = await fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const messages = await response.json();

          if (Array.isArray(messages) && messages.length > 0) {
            // Process messages to normalize data types and convert shortcodes to emojis
            const processedMessages = messages.map(msg => ({
              ...msg,
              message: this.processMessageForDisplay(msg.message), // Convert shortcodes to emojis
              viewed: msg.viewed == 1 || msg.viewed === true,
              viewed_at: msg.viewed_at || null,
              viewed_at_formatted: msg.viewed_at_formatted || '',
              edited_at: msg.edited_at || null,
              sender_id: parseInt(msg.sender_id),
              status: msg.status || 'sent'
            }));

            if (loadMore) {
              chat.messages.unshift(...processedMessages.reverse());
            } else {
              chat.messages = processedMessages.reverse();
              chat._renderKey = Date.now();

              this.$nextTick(() => {
                setTimeout(() => {
                  this.scrollToBottom(chat);
                }, 250);
              });
            }

            chat.messageOffset += messages.length;
          }

          chat.isLoading = false;
          chat.loadingMore = false;
        } catch (error) {
          if (error?.name !== 'AbortError') console.error('Failed to load messages:', error);
          chat.isLoading = false;
          chat.loadingMore = false;
        }
      },

      async sendMessage(chat) {
        if (!chat.currentMessage.trim()) return;

        // Handle edit mode
        if (chat.editingMessageId) {
          var editId = chat.editingMessageId;
          var newText = chat.currentMessage.trim();
          chat.editingMessageId = null;
          chat.currentMessage = '';

          var self = this;
          $.post(site_url + 'admin/prchat/Prchat_Controller/editMessage', {
            id: editId,
            message: newText,
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
          }, function(res) {
            try {
              if (typeof res === 'string') res = JSON.parse(res);
            } catch (e) {
              return;
            }
            if (res.success) {
              var msg = chat.messages.find(function(m) {
                return m.id == editId;
              });
              if (msg) {
                msg.message = res.rendered_message;
                if (res.changed !== false) {
                  msg.edited = true;
                  msg.edited_at = new Date().toISOString().slice(0, 19).replace('T', ' ');
                }
              }
            }
          });
          return;
        }

        if (window.emojiPickerInstance && window.emojiPickerInstance.isVisible) {
          window.emojiPickerInstance.hide();
        }

        let message = chat.currentMessage;

        if (chat.replyingTo) {
          var replyMsg = chat.replyingTo.message || '';
          var replyId = chat.replyingTo.id || '0';
          var type = 'text';
          if (replyMsg.match(/\.(gif|jpg|jpeg|png|swf)$/i) || replyMsg.indexOf('data-chat-file="image"') !== -1 || replyMsg.indexOf('chat-image-preview') !== -1) {
            type = 'image';
          } else if (replyMsg.indexOf('data-video-embed') !== -1 || this.isVideoUrl(replyMsg)) {
            type = 'video';
          } else if (replyMsg.indexOf('data-chat-file="file"') !== -1 || (replyMsg.indexOf('/modules/prchat/uploads/') !== -1 && !replyMsg.match(/\.(gif|jpg|jpeg|png)$/i))) {
            type = 'file';
          } else if (replyMsg.match(/^https?:\/\//i)) {
            type = 'link';
          }
          var preview = this.getCleanMessageText(replyMsg);
          if (preview.length > 80) preview = preview.substring(0, 80);
          if (!preview) {
            var fallbacks = {
              image: 'Photo',
              video: 'Video',
              file: 'File',
              audio: 'Audio',
              link: 'Link'
            };
            preview = fallbacks[type] || '...';
          }
          message = '[REPLY:' + replyId + ':' + type + ':' + preview + '] ' + message;
        }

        // Process emojis: Convert Unicode emojis to shortcodes for storage
        let messageForStorage = message;
        if (window.CustomEmoji) {
          const customEmoji = new window.CustomEmoji();
          messageForStorage = customEmoji.emojisToShortcodes(message);
        }

        let messageForDisplay = message;
        if (window.CustomEmoji) {
          const customEmoji = new window.CustomEmoji();
          messageForDisplay = customEmoji.shortcodesToEmojis(message);
        }

        const originalMessage = chat.currentMessage;
        const tempId = `temp_${Date.now()}_${Math.random()}`;

        const optimisticMessage = {
          id: tempId,
          sender_id: this.currentUserId,
          message: messageForDisplay,
          time_sent: new Date().toISOString(),
          viewed: false,
          status: 'pending' // Track message status
        };

        chat.messages.push(optimisticMessage);
        chat.currentMessage = '';

        // Clear reply context
        if (chat.replyingTo) {
          chat.replyingTo = null;
        }

        // Scroll to bottom immediately
        this.$nextTick(() => {
          this.scrollToBottom(chat);
        });

        if (chat.isTypingIndicatorSent) {
          chat.isTypingIndicatorSent = false;
          this.sendTypingIndicator(chat, false);
        }
        if (this.typingTimeouts[`debounce_${chat.userId}`]) {
          clearTimeout(this.typingTimeouts[`debounce_${chat.userId}`]);
        }
        if (this.typingTimeouts[`typing_${chat.userId}`]) {
          clearTimeout(this.typingTimeouts[`typing_${chat.userId}`]);
        }

        // Send message to server
        try {
          const formData = new FormData();
          formData.append('from', this.currentUserId);
          formData.append('to', '#' + chat.userId);
          formData.append('msg', messageForStorage); // Send shortcodes to server
          formData.append('typing', 'false');

          const response = await fetch(window.chatConfig.apiUrls.sendMessage, {
            method: 'POST',
            body: formData
          });

          if (response.ok) {
            // Message sent successfully - update status
            const messageIndex = chat.messages.findIndex(m => m.id === tempId);
            if (messageIndex !== -1) {
              chat.messages[messageIndex].status = 'sent';

              // Try to get server response, but don't fail if there's no JSON
              const responseText = await response.text();
              if (responseText) {
                const result = JSON.parse(responseText);
                if (result && result.id) {
                  // Update message with real server ID
                  chat.messages[messageIndex].id = result.id;
                }
              }
            }

            // Update user's last message and timestamp when we send a message
            const user = this.users.find(u => u.staffid == chat.userId);
            if (user) {
              user.lastMessage = originalMessage;
              user.lastMessageSenderId = this.currentUserId;
              user.lastMessageTime = new Date().toISOString();
              user.lastSeen = new Date().toISOString();
            }

            // Re-sort users to move this conversation to top
            this.filterUsers();
          } else {
            throw new Error(`Server responded with ${response.status}`);
          }

        } catch (error) {
          console.error('Failed to send message:', error);

          // Mark message as failed instead of removing it
          const messageIndex = chat.messages.findIndex(m => m.id === tempId);
          if (messageIndex !== -1) {
            chat.messages[messageIndex].status = 'failed';
          }

          // Show error notification using Perfex CRM's notification system
          if (typeof alert_float !== 'undefined') {
            alert_float('danger', '<?php echo _l('chat_send_failed'); ?>');
          }
        }
      },

      handleTextareaFocus(chat) {
        // Mark messages as read when user focuses to reply
        this.markMessagesAsRead(chat.userId);

        // Scroll to bottom
        this.scrollToBottom(chat);
      },

      handleKeyPress(event, chat) {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          this.sendMessage(chat);
        }
      },

      handleTyping(chat) {
        if (this.typingTimeouts[`debounce_${chat.userId}`]) {
          clearTimeout(this.typingTimeouts[`debounce_${chat.userId}`]);
        }

        if (!chat.isTypingIndicatorSent) {
          chat.isTypingIndicatorSent = true;
          this.sendTypingIndicator(chat, true);
        }

        this.typingTimeouts[`debounce_${chat.userId}`] = setTimeout(() => {
          chat.isTypingIndicatorSent = false;
          this.sendTypingIndicator(chat, false);
        }, 1500);
      },

      async sendTypingIndicator(chat, isTyping) {
        // THROTTLE: Prevent rapid-fire typing indicator calls
        const now = Date.now();
        const lastCall = this.typingTimeouts[`lastCall_${chat.userId}`] || 0;

        // Don't send typing indicator more than once every 500ms
        if (isTyping && (now - lastCall) < 500) {
          return;
        }

        this.typingTimeouts[`lastCall_${chat.userId}`] = now;

        try {
          const formData = new FormData();
          formData.append('from', this.currentUserId);
          formData.append('to', '#' + chat.userId);
          formData.append('typing', isTyping ? 'true' : 'false');

          await fetch(window.chatConfig.apiUrls.sendMessage, {
            method: 'POST',
            body: formData
          });
        } catch (error) {
          console.error('Failed to send typing indicator:', error);
        }
      },

      handleScroll(event, chat) {
        if (event.target.scrollTop === 0 && !chat.loadingMore) {
          chat.loadingMore = true;
          this.loadChatMessages(chat, true);
        }
      },

      scrollToBottom(chat) {
        const messagesEl = document.getElementById(`chat-messages-${chat.userId}`);
        if (messagesEl) {
          messagesEl.scrollTop = messagesEl.scrollHeight;
          // Process video embeds after scrolling
          if (typeof PrChatVideoEmbed !== 'undefined') {
            PrChatVideoEmbed.convertVideoLinks(messagesEl);
          }
        }
      },

      toggleChatWindow(chat) {
        chat.minimized = !chat.minimized;
        if (!chat.minimized) {
          chat.focused = true;
          this.bringToFront(chat);

          // Ensure messages are visible and scrolled when reopening
          setTimeout(() => {
            this.$nextTick(() => {
              this.scrollToBottom(chat);
            });
          }, 100);
        }
        this.saveChatState();
      },

      closeChat(chat) {
        const index = this.activeChats.indexOf(chat);
        if (index > -1) {
          this.activeChats.splice(index, 1);
          this.saveChatState();
        }
      },

      toggleMinimize() {
        this.isMinimized = !this.isMinimized;
        // Reset all users' unread counts when expanding
        if (!this.isMinimized) {
          this.users.forEach(user => {
            user.unreadCount = 0;
          });
          this.clampMainPanelPos();
        }
        this.saveChatState();
      },

      toggleNotifications() {
        this.notificationsEnabled = !this.notificationsEnabled;
      },

      toggleSearch() {
        // If main panel is hidden, show it
        if (!this.showMainPanel) {
          this.showMainPanel = true;
        }

        // If main panel is minimized, expand it
        if (this.isMinimized) {
          this.isMinimized = false;
        }

        // Toggle search visibility
        this.searchVisible = !this.searchVisible;

        // Focus search input after it's shown
        if (this.searchVisible) {
          this.$nextTick(() => {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
              searchInput.focus();
            }
          });
        }
      },

      // DARK MODE FUNCTIONALITY
      initializeDarkMode() {
        const savedMode = localStorage.getItem('prChatThemeMode');
        if (savedMode !== null) {
          this.isDarkMode = savedMode === 'dark';
        }
        this.applyDarkMode();
      },

      toggleFloatingNotifications() {
        if (typeof FloatingChatNotifications === 'undefined') return;
        if (FloatingChatNotifications.isVisible()) {
          FloatingChatNotifications.hide();
        } else {
          FloatingChatNotifications.show();
          FloatingChatNotifications.expand();
        }
        this.updateFloatingUnreadCount();
      },

      updateFloatingUnreadCount() {
        try {
          this.floatingUnreadCount = (typeof FloatingChatNotifications !== 'undefined') ?
            FloatingChatNotifications.getCount() :
            0;
        } catch (e) {
          this.floatingUnreadCount = 0;
        }
      },

      // Feed unread counts (staff + client) into the floating notification widget
      async loadFloatingUnread() {
        if (typeof FloatingChatNotifications === 'undefined') return;
        // Only on toggled view (full chat uses its own DOM badges)
        if (document.getElementById('frame')) return;

        try {
          var placeholder = window.chatConfig.siteUrl + 'assets/images/user-placeholder.jpg';
          var unreadLabel = '<?= _l("chat_unread_messages"); ?>';

          var resp = await fetch('<?= admin_url("prchat/Prchat_Controller/getUnreadCounts"); ?>', {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          if (!resp.ok) return;
          var data = await resp.json();

          var mutedStaff = this.mutedUsers || [];
          var mutedClients = this.mutedClients || [];

          // Staff unread (skip muted)
          if (data.staff && data.staff.length) {
            data.staff.forEach(function(s) {
              if (mutedStaff.indexOf(String(s.id)) !== -1) return;
              FloatingChatNotifications.add({
                from: s.id,
                fromName: s.name,
                type: 'staff',
                message: unreadLabel.replace('%s', s.count),
                avatar: s.avatar || placeholder,
                messageCount: s.count
              }, true);
            });
          }

          // Client unread (skip muted)
          if (data.clients && data.clients.length) {
            data.clients.forEach(function(c) {
              var cid = String((c.id || '').replace(/^client_/, ''));
              if (mutedClients.indexOf(cid) !== -1) return;
              FloatingChatNotifications.add({
                from: c.id,
                fromName: c.name,
                type: 'client',
                message: unreadLabel.replace('%s', c.count),
                avatar: c.avatar || placeholder,
                messageCount: c.count
              }, true);
            });
          }

          this.updateFloatingUnreadCount();
        } catch (e) {
          if (e?.name !== 'AbortError') console.warn('loadFloatingUnread error:', e);
        }
      },

      toggleDarkMode() {
        this.isDarkMode = !this.isDarkMode;
        localStorage.setItem('prChatThemeMode', this.isDarkMode ? 'dark' : 'light');
        this.applyDarkMode();
      },

      applyDarkMode() {
        const chatApp = document.getElementById('chatApp');
        if (chatApp) {
          if (this.isDarkMode) {
            chatApp.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
          } else {
            chatApp.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
          }
        }
      },

      async markMessagesAsRead(userId) {
        try {
          const formData = new FormData();
          formData.append('id', userId);

          const response = await fetch(window.chatConfig.apiUrls.updateUnread, {
            method: 'POST',
            body: formData
          });

          const chat = this.activeChats.find(c => c.userId == userId);

          if (chat) {
            const now = new Date().toISOString();
            const nowFormatted = this.formatSeenDate(now);
            for (let i = 0; i < chat.messages.length; i++) {
              if (chat.messages[i].sender_id != this.currentUserId && !chat.messages[i].viewed) {
                chat.messages.splice(i, 1, {
                  ...chat.messages[i],
                  viewed: true,
                  viewed_at: now,
                  viewed_at_formatted: nowFormatted
                });
              }
            }
          }

          const user = this.users.find(u => u.staffid == userId);
          if (user) {
            user.unreadCount = 0;
          }

          if (typeof FloatingChatNotifications !== 'undefined') {
            FloatingChatNotifications.removeOne(String(userId));
          }

        } catch (error) {
          console.error('Failed to mark messages as read:', error);
        }
      },

      // Use the existing fetchUserAvatar function from full chat view
      fetchUserAvatar(id, image_name) {
        const type = "thumb";
        const siteUrl = window.chatConfig.siteUrl;
        let url = siteUrl + "/assets/images/user-placeholder.jpg";

        // Check if id is undefined or null
        if (id == undefined || id == null || id === "") {
          return url;
        }

        if (image_name == false || image_name == null || image_name == undefined || image_name === "") {
          return url;
        }

        if (image_name != null && image_name != undefined) {
          url = siteUrl + "/uploads/staff_profile_images/" + id + "/" + type + "_" + image_name;
        } else {
          url = siteUrl + "/assets/images/user-placeholder.jpg";
        }
        return url;
      },

      getUserAvatar(user) {
        if (!user) {
          return this.fetchUserAvatar(null, null);
        }

        // If we have a profile_image_url from backend, use that directly
        if (user.profile_image_url) {
          return user.profile_image_url;
        }

        // Fallback to the existing fetchUserAvatar function
        const userId = user.staffid || user.id;
        const imageName = user.profile_image || user.image_name;

        return this.fetchUserAvatar(userId, imageName);
      },

      getMessageAvatar(message) {
        let user = this.users.find(u => u.staffid == message.sender_id);

        if (!user) {
          // Check if this is the current user (filtered out of users list)
          if (message.sender_id == this.currentUserId && this.currentUserData) {
            user = this.currentUserData;
          } else {
            // Create fallback user - use placeholder
            user = {
              firstname: 'User',
              lastname: '',
              staffid: message.sender_id,
              profile_image: null // Will force fetchUserAvatar to use placeholder
            };
          }
        }

        return message.sender_image || this.getUserAvatar(user);
      },

      // Safe attribute value sanitization
      sanitizeAttr(value) {
        if (!value) return '';
        // Remove HTML comments and other invalid characters for attributes
        return String(value).replace(/<!--[\s\S]*?-->/g, '').replace(/[<>"']/g, '').trim();
      },

      showNotification(message, type = 'info') {
        // Use Perfex's built-in notification system
        if (typeof alert_float === 'function') {
          alert_float(type === 'error' ? 'danger' : type, message);
        }
      },

      checkVideoSupport() {
        this.hasVideoSupport = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
      },

      getUserStatusText(status) {
        const statusMap = {
          online: '<?php echo _l("chat_status_online"); ?>',
          away: '<?php echo _l("chat_status_away"); ?>',
          busy: '<?php echo _l("chat_status_busy"); ?>',
          offline: '<?php echo _l("chat_status_offline"); ?>'
        };
        return statusMap[status] || status;
      },

      processMessageContent(message) {
        if (!message) return '';

        if (typeof PrchatSafeRenderer === 'undefined') {
          return message.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        var oldReply = message.match(/^@reply:\s*"([^"]*)"\s*\n?([\s\S]*)$/);
        if (oldReply) {
          var preview = oldReply[1] || '';
          var body = oldReply[2] || '';
          message = '[REPLY:0:text:' + preview.substring(0, 80) + '] ' + body;
        }

        var hasHtml = message.indexOf('<a') !== -1 ||
          message.indexOf('<img') !== -1 ||
          message.indexOf('&lt;') !== -1 ||
          message.indexOf('data-chat-file') !== -1 ||
          message.indexOf('quickMentionLink') !== -1;

        if (hasHtml) {
          return PrchatSafeRenderer.render(message).display;
        }

        return PrchatSafeRenderer.renderFromText(message).display;
      },

      isVideoUrl(url) {
        // Note: .webm and .ogg are excluded — they are audio formats for voice recordings
        return /\.(mp4|avi|mov|wmv|flv|mkv|m4v|3gp)$/i.test(url) ||
          url.includes('youtube.com') ||
          url.includes('youtu.be') ||
          url.includes('vimeo.com');
      },

      isCallMessage(msg) {
        if (!msg) return false;
        if (/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/.test(msg)) return true;
        var decoded = msg.replace(/&bull;/g, '\u2022');
        if (/^(Outgoing call|Incoming call|Voice call|Video call|Missed call)\s*[•·\u2022]/i.test(decoded)) return true;
        return false;
      },

      parseCallMessage(msg) {
        var m = msg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):(\d+):(\d+)\]$/);
        if (m) return {
          type: m[1],
          duration: parseInt(m[2]),
          callerId: m[3],
          calleeId: m[4]
        };
        return null;
      },

      getCallIcon(msg) {
        var info = this.parseCallMessage(msg);
        if (info) {
          if (info.type === 'missed_voice') return 'fa fa-phone tw-text-red-500';
          if (info.type === 'missed_video') return 'fa fa-video-camera tw-text-red-500';
          return info.type === 'video' ? 'fa fa-video-camera' : 'fa fa-phone';
        }
        if (/video/i.test(msg)) return 'fa fa-video-camera';
        return 'fa fa-phone';
      },

      getStaffNameById(id) {
        var u = this.users.find(function(u) {
          return String(u.staffid) === String(id);
        });
        return u ? (u.firstname + ' ' + u.lastname).trim() : '';
      },

      renderCallLabel(msg) {
        var info = this.parseCallMessage(msg);
        if (info) {
          var isCaller = String(info.callerId) === String(this.currentUserId);
          var peerId = isCaller ? info.calleeId : info.callerId;
          var peerName = this.getStaffNameById(peerId) || '<?= addslashes(_l("chat_staff_fallback")) ?>';

          if (info.type === 'missed_voice' || info.type === 'missed_video') {
            var missedLabel = isCaller ?
              '<?= addslashes(_l("chat_call_no_answer")) ?> ' + peerName :
              '<?= addslashes(_l("chat_call_missed_from")) ?> ' + peerName;
            return '<span style="color:#e74c3c">' + missedLabel + '</span>';
          }
          var label = isCaller ?
            '<?= addslashes(_l("chat_call_you_called")) ?> ' + peerName :
            peerName + ' <?= addslashes(_l("chat_call_called_you")) ?>';
          var mins = Math.floor(info.duration / 60);
          var secs = info.duration % 60;
          var dur = (mins > 0 ? mins + 'm ' : '') + secs + 's';
          return label + ' &bull; ' + dur;
        }
        var decoded = msg.replace(/&bull;/g, '\u2022').replace(/&amp;/g, '&');
        var legacy = decoded.match(/^(Outgoing call|Incoming call|Voice call|Video call|Missed call)\s*[•·\u2022]\s*(.+)$/i);
        if (legacy) {
          var safeType = legacy[1].replace(/</g, '&lt;');
          var safeDur = legacy[2].replace(/</g, '&lt;');
          return safeType + ' &bull; ' + safeDur;
        }
        return msg;
      },

      // Wrapper method for template usage
      formatMessage(message) {
        if (this.isCallMessage(message)) {
          return this.renderCallLabel(message);
        }
        return this.processMessageContent(message);
      },

      formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit'
        });
      },

      formatTimeAgo(timestamp) {
        if (!timestamp) return '';
        return moment(timestamp).fromNow();
      },

      shouldShowDateSeparator(messages, index) {
        if (index === 0) return true;
        var cur = moment(messages[index].time_sent);
        var prev = moment(messages[index - 1].time_sent);
        return !cur.isSame(prev, 'day');
      },

      formatDateSeparator(timestamp) {
        if (!timestamp) return '';
        var m = moment(timestamp);
        if (m.isSame(moment(), 'day')) return '<?php echo _l("chatbot_today"); ?>';
        if (m.isSame(moment().subtract(1, 'day'), 'day')) return '<?php echo _l("chatbot_yesterday"); ?>';
        return m.format(prchatSettings.dateSeparatorFormat);
      },

      // Format date as "Fri, Feb 6, 2026 01:08" for seen timestamps
      formatSeenDate(timestamp) {
        if (!timestamp) return '';
        var m = moment(timestamp);
        if (!m.isValid()) return '';
        return m.format(prchatSettings.dateTimeFormat);
      },

      getLastMessagePreview(lastMessage, lastMessageSenderId) {
        if (!lastMessage) return '';

        var msg = lastMessage;

        // Strip "You: " prefix (added by server) before checking call format
        var youPrefix = '<?= addslashes(_l("chat_message_you")) ?> ';
        var hadYouPrefix = false;
        if (msg.indexOf(youPrefix) === 0) {
          msg = msg.substring(youPrefix.length);
          hadYouPrefix = true;
        }

        var hasSenderMeta = lastMessageSenderId !== null && lastMessageSenderId !== undefined && String(lastMessageSenderId) !== '';
        if (hasSenderMeta) {
          hadYouPrefix = String(lastMessageSenderId) === String(this.currentUserId);
        }

        if (typeof PrchatSafeRenderer !== 'undefined') {
          var callPrev = PrchatSafeRenderer.formatCallPreview(msg);
          if (callPrev) return (hadYouPrefix ? youPrefix : '') + callPrev;
        }

        var replyMatch = msg.match(/\[REPLY:[^\]]+\]\s*/);
        if (replyMatch) {
          msg = msg.replace(replyMatch[0], '');
          var replyText = this.getCleanMessageText(msg);
          if (!replyText) replyText = '...';
          var maxLen = 25;
          return '↩ ' + (replyText.length > maxLen ? replyText.substring(0, maxLen) + '...' : replyText);
        }

        var oldReplyMatch = msg.match(/^@reply:\s*"[^"]*"\s*/);
        if (oldReplyMatch) {
          msg = msg.replace(oldReplyMatch[0], '');
          var oldReplyText = this.getCleanMessageText(msg);
          if (!oldReplyText) oldReplyText = '...';
          return '↩ ' + (oldReplyText.length > 25 ? oldReplyText.substring(0, 25) + '...' : oldReplyText);
        }

        var preview = this.getMessagePreview(msg, 30);
        if (!preview) {
          if (window.CustomEmoji) {
            var customEmoji = new window.CustomEmoji();
            msg = customEmoji.shortcodesToEmojis(msg);
          }
          var cleanText = this.getCleanMessageText(msg);
          preview = cleanText.length > 30 ? cleanText.substring(0, 30) + '...' : cleanText;
        }
        return hadYouPrefix ? (youPrefix + ' ' + preview) : preview;
      },

      playNotificationSound() {
        // Only play sound if audio context is ready after user interaction
        if (this.audioContextReady && this.audioContext && this.audioContext.state === 'running') {
          this.playBeep();
        } else if (this.audioContext && this.audioContext.state === 'suspended') {
          // Try to resume if suspended
          this.audioContext.resume().then(() => {
            this.audioContextReady = true;
            this.playBeep();
          });
        }
      },

      playBeep() {
        try {
          const oscillator = this.audioContext.createOscillator();
          const gainNode = this.audioContext.createGain();

          oscillator.connect(gainNode);
          gainNode.connect(this.audioContext.destination);

          oscillator.frequency.value = 800;
          oscillator.type = 'sine';

          gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
          gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.1);

          oscillator.start(this.audioContext.currentTime);
          oscillator.stop(this.audioContext.currentTime + 0.1);
        } catch (error) {}
      },
      initializeAudioContext() {
        // Initialize audio context ONLY after user interaction
        if (!this.audioContext) {
          try {
            this.audioContext = new(window.AudioContext || window.webkitAudioContext)();

            // Resume if suspended
            if (this.audioContext.state === 'suspended') {
              this.audioContext.resume().then(() => {
                this.audioContextReady = true;
              }).catch(error => {
                console.error('Failed to resume AudioContext:', error);
              });
            } else {
              this.audioContextReady = true;
            }
          } catch (error) {
            console.error('AudioContext initialization failed:', error);
            this.audioContextReady = false;
          }
        }
      },

      handleDragEnter(event, chat) {
        if (event.dataTransfer && event.dataTransfer.types && event.dataTransfer.types.indexOf('Files') !== -1) {
          chat.isDragOver = true;
        }
      },
      handleDragOver(event, chat) {
        if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
      },
      handleDragLeave(event, chat) {
        var rect = event.currentTarget.getBoundingClientRect();
        var x = event.clientX;
        var y = event.clientY;
        if (x <= rect.left || x >= rect.right || y <= rect.top || y >= rect.bottom) {
          chat.isDragOver = false;
        }
      },
      handleDrop(event, chat) {
        chat.isDragOver = false;
        if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
          this.processDroppedFiles(event.dataTransfer.files, chat);
        }
      },
      async processDroppedFiles(files, chat) {
        for (var i = 0; i < files.length; i++) {
          var file = files[i];
          if (file.size > 25 * 1024 * 1024) {
            if (typeof alert_float !== 'undefined') alert_float('danger', file.name + ': File too large (max 25MB)');
            continue;
          }
          var formData = new FormData();
          formData.append('userfile', file);
          formData.append('send_from', this.currentUserId);
          formData.append('send_to', chat.userId);
          try {
            var response = await fetch(window.chatConfig.apiUrls.uploadFile, {
              method: 'POST',
              body: formData
            });
            var result = await response.json();
            if (result.error) {
              if (typeof alert_float !== 'undefined') alert_float('danger', file.name + ': ' + result.error);
              continue;
            }
            if (result.upload_data) {
              var basePath = window.chatConfig.siteUrl + '/modules/prchat/uploads/';
              var filePath = basePath + result.upload_data.file_name;
              var newMessage = {
                id: Date.now() + i,
                sender_id: this.currentUserId,
                message: filePath,
                time_sent: new Date().toISOString(),
                viewed: 0
              };
              chat.messages.push(newMessage);
              var messageData = new FormData();
              messageData.append('from', this.currentUserId);
              messageData.append('to', '#' + chat.userId);
              messageData.append('msg', filePath);
              messageData.append('typing', 'false');
              fetch(window.chatConfig.apiUrls.sendMessage, {
                method: 'POST',
                body: messageData
              });

              var user = this.users.find(u => u.staffid == chat.userId);
              if (user) {
                user.lastMessage = filePath;
                user.lastMessageSenderId = this.currentUserId;
                user.lastMessageTime = new Date().toISOString();
                user.lastSeen = new Date().toISOString();
              }
            }
          } catch (err) {
            console.error('Upload failed:', err);
            if (typeof alert_float !== 'undefined') alert_float('danger', file.name + ': Upload failed');
          }
        }
        this.filterUsers();
        this.$nextTick(function() {
          this.scrollToBottom(chat);
        }.bind(this));
        if (typeof alert_float !== 'undefined' && files.length > 0) alert_float('success', '<?php echo _l("chat_upload_success"); ?>');
      },

      triggerFileUpload(chat) {
        const fileInput = this.$refs['fileInput' + chat.userId];
        if (fileInput) {
          const input = Array.isArray(fileInput) ? fileInput[0] : fileInput;
          if (input && typeof input.click === 'function') {
            input.click();
          }
        }
      },

      startVoiceRecord(chat) {
        if (typeof PrchatVoiceRecorder === 'undefined' || !PrchatVoiceRecorder.isSupported()) {
          if (typeof alert_float === 'function') {
            alert_float('warning', 'Voice recording not supported in this browser.');
          }
          return;
        }
        var self = this;
        var uid = String(chat.userId);
        var chatEl = document.querySelector('.prchat-toggled-chat-window[data-user-id="' + uid.replace(/"/g, '') + '"]');
        if (!chatEl) {
          if (typeof alert_float === 'function') {
            alert_float('danger', 'Chat window not found.');
          }
          return;
        }
        var container = chatEl.querySelector('.chat-input-container');
        if (!container) return;

        var rec = PrchatVoiceRecorder.init({
          triggerBtn: null,
          container: container,
          uploadUrl: '<?= admin_url('prchat/Prchat_Controller/uploadMethod'); ?>',
          basePath: site_url + 'modules/prchat/uploads/',
          csrfName: '<?= $this->security->get_csrf_token_name(); ?>',
          csrfValue: '<?= $this->security->get_csrf_hash(); ?>',
          onSend: function(url) {
            chat.currentMessage = url;
            self.sendMessage(chat);
          },
          onError: function(msg) {
            alert_float('danger', msg);
          }
        });
        rec.start();
      },

      handlePaste(event, chat) {
        const clipboardData = event.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        var imageFile = null;

        // Try clipboardData.items first (Chrome, Safari, Edge)
        if (clipboardData.items) {
          for (let i = 0; i < clipboardData.items.length; i++) {
            if (clipboardData.items[i].type.indexOf('image') !== -1) {
              imageFile = clipboardData.items[i].getAsFile();
              break;
            }
          }
        }

        // Fallback: clipboardData.files (Firefox)
        if (!imageFile && clipboardData.files && clipboardData.files.length > 0) {
          for (let i = 0; i < clipboardData.files.length; i++) {
            if (clipboardData.files[i].type.indexOf('image') !== -1) {
              imageFile = clipboardData.files[i];
              break;
            }
          }
        }

        if (!imageFile) return;
        event.preventDefault();

        const ext = {
          'image/png': 'png',
          'image/jpeg': 'jpg',
          'image/gif': 'gif',
          'image/webp': 'webp'
        } [imageFile.type] || 'png';
        const file = new File([imageFile], 'pasted-image-' + Date.now() + '.' + ext, {
          type: imageFile.type
        });
        this.uploadPastedFile(file, chat);
      },

      async uploadPastedFile(file, chat) {
        alert_float('info', '<?php echo _l("chat_msg_sending"); ?>');

        const formData = new FormData();
        formData.append('userfile', file);
        formData.append('send_from', this.currentUserId);
        formData.append('send_to', chat.userId);

        try {
          const response = await fetch(window.chatConfig.apiUrls.uploadFile, {
            method: 'POST',
            body: formData
          });
          const result = await response.json();

          if (result.error) {
            alert_float('danger', result.error);
            return;
          }

          if (result.upload_data) {
            const basePath = window.chatConfig.siteUrl + '/modules/prchat/uploads/';
            const filePath = basePath + result.upload_data.file_name;
            chat.currentMessage = filePath;
            this.sendMessage(chat);
          }
        } catch (err) {
          alert_float('danger', '<?php echo _l("chat_error_float"); ?>');
        }
      },

      async handleFileUpload(event, chat) {
        const files = Array.from(event.target.files);
        if (!files.length) return;

        alert_float('info', '<?php echo _l('chat_msg_sending'); ?>');

        try {
          // Upload each file separately
          for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Check for invalid characters
            const invalidCharsRegex = /[~%:()@]/;
            if (invalidCharsRegex.test(file.name)) {
              alert_float('warning', '<?php echo _l('chat_permitted_files'); ?>');
              continue;
            }

            const formData = new FormData();
            formData.append('userfile', file);
            formData.append('send_from', this.currentUserId);
            formData.append('send_to', chat.userId);

            const response = await fetch(window.chatConfig.apiUrls.uploadFile, {
              method: 'POST',
              body: formData
            });

            const result = await response.json();

            if (result.error) {
              alert_float('danger', `${file.name}: ${result.error}`);
              continue;
            }

            if (result.upload_data) {
              // Construct file path like original chat
              const basePath = window.chatConfig.siteUrl + '/modules/prchat/uploads/';
              const filePath = basePath + result.upload_data.file_name;

              // Add message to UI immediately
              const newMessage = {
                id: Date.now() + i, // Unique ID for each file
                sender_id: this.currentUserId,
                message: filePath,
                time_sent: new Date().toISOString(),
                viewed: 0
              };

              chat.messages.push(newMessage);

              // Send message to backend
              const messageData = new FormData();
              messageData.append('from', this.currentUserId);
              messageData.append('to', '#' + chat.userId);
              messageData.append('msg', filePath);
              messageData.append('typing', 'false');

              fetch(window.chatConfig.apiUrls.sendMessage, {
                method: 'POST',
                body: messageData
              });

              const user = this.users.find(u => u.staffid == chat.userId);
              if (user) {
                user.lastMessage = filePath;
                user.lastMessageSenderId = this.currentUserId;
                user.lastMessageTime = new Date().toISOString();
                user.lastSeen = new Date().toISOString();
              }
            }
          }

          this.filterUsers();

          // Scroll to bottom after all files
          this.$nextTick(() => {
            const messagesContainer = document.getElementById(`chat-messages-${chat.userId}`);
            if (messagesContainer) {
              messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
          });

          alert_float('success', '<?php echo _l('chat_upload_success'); ?>');

          // Reset file input
          event.target.value = '';
        } catch (error) {
          console.error('Failed to upload files:', error);
          alert_float('danger', '<?php echo _l('chat_upload_failed'); ?>');
          event.target.value = '';
        }
      },

      // MESSAGE OPTIONS FUNCTIONALITY
      toggleMessageOptions(message, event) {
        this.clearHideTimeout();

        if (this.activeMessageOptions === message.id) {
          this.activeMessageOptions = null;
        } else {
          this.activeMessageOptions = message.id;

          this.$nextTick(() => {
            const dropdown = document.querySelector('.message-options-dropdown');
            const btn = event.target.closest('.options-btn') || event.target;

            if (!dropdown || !btn) return;

            const btnRect = btn.getBoundingClientRect();
            const dropW = 150;
            const dropH = dropdown.offsetHeight || 120;
            const isOwn = message.sender_id == this.currentUserId;

            var left;
            if (isOwn) {
              left = btnRect.right - dropW;
            } else {
              left = btnRect.left;
            }

            if (left + dropW > window.innerWidth - 8) {
              left = window.innerWidth - dropW - 8;
            }
            if (left < 8) left = 8;

            var top = btnRect.bottom + 4;
            if (top + dropH > window.innerHeight - 8) {
              top = btnRect.top - dropH - 4;
            }

            dropdown.style.top = top + 'px';
            dropdown.style.left = left + 'px';
          });
        }
      },

      // MESSAGE REACTIONS (quick emoji popup)
      openReactionPicker(message, event) {
        if (!message || !message.id) return;
        if (typeof window.ReactionPicker === "undefined") return;

        const btn = event.currentTarget || event.target;
        const rect = btn.getBoundingClientRect();
        const anchor = {
          getBoundingClientRect: () => rect,
          _position: 'left'
        };

        const self = this;
        if (!window.reactionPickerInstance) {
          window.reactionPickerInstance = new window.ReactionPicker({
            onPick: function(emoji) {
              if (!window.currentReactionMessage) return;
              self.sendReaction(window.currentReactionMessage, emoji);
              self.activeMessageOptions = null;
            }
          });
        }

        window.currentReactionMessage = message;
        this.$nextTick(() => {
          try {
            window.reactionPickerInstance.openNear(anchor);
          } catch (e) {}
        });
      },

      async sendReaction(message, emoji) {
        if (!message || !message.id || !emoji) return;
        if (String(message.id).startsWith('temp_')) return;

        // Optimistic: close message dropdown to avoid overlap
        this.activeMessageOptions = null;

        try {
          const formData = new FormData();
          formData.append('message_id', message.id);
          formData.append('emoji', emoji);
          formData.append('message_type', 'staff');

          const response = await fetch(window.chatConfig.apiUrls.addReaction, {
            method: 'POST',
            body: formData
          });

          if (response.ok) {
            const result = await response.json().catch(() => null);
            if (result && result.success) {
              // If backend returned null, clear reactions to avoid stale UI
              message.reactions = result.reactions;
            }
          }
        } catch (e) {
          // Ignore; reaction sync will arrive via Pusher.
        }
      },

      parseReactions(reactionsRaw) {
        if (!reactionsRaw) return {};
        if (typeof reactionsRaw === 'object') return reactionsRaw || {};
        if (typeof reactionsRaw === 'string') {
          try {
            const decoded = JSON.parse(reactionsRaw);
            if (decoded && typeof decoded === 'object') return decoded;
          } catch (e) {}
        }
        return {};
      },

      getReactionEmojis(message) {
        const obj = this.parseReactions(message && message.reactions);
        return Object.keys(obj);
      },

      hasMessageReactions(message) {
        const obj = this.parseReactions(message && message.reactions);
        return obj && Object.keys(obj).length > 0;
      },

      getReactionCount(message, emoji) {
        const obj = this.parseReactions(message && message.reactions);
        const arr = obj ? obj[emoji] : null;
        if (!Array.isArray(arr)) return 0;
        return arr.length;
      },

      isMessageReactedByMe(message, emoji) {
        const obj = this.parseReactions(message && message.reactions);
        const arr = obj ? obj[emoji] : null;
        if (!Array.isArray(arr)) return false;
        const myKey = String(this.currentUserId);
        return arr.some(v => String(v) === myKey);
      },

      resolveReactorName(key) {
        const k = String(key);
        if (k === String(this.currentUserId)) return '<?= addslashes(get_staff_full_name()); ?> (You)';
        if (k.startsWith('client_')) {
          return 'Client';
        }
        let staffKey = k.startsWith('staff_') ? k.replace('staff_', '') : k;
        const staff = (this.users || []).find(u => String(u.staffid) === staffKey);
        if (staff) {
          return [staff.firstname, staff.lastname].filter(Boolean).join(' ') || staffKey;
        }
        return staffKey;
      },

      getReactionTooltip(message, emoji) {
        const obj = this.parseReactions(message && message.reactions);
        const arr = obj ? obj[emoji] : null;
        if (!Array.isArray(arr) || !arr.length) return '';
        return arr.map(u => this.resolveReactorName(u)).join(', ');
      },

      handleMessageReaction(data) {
        if (!data || !data.message_id) return;
        if (data.message_type !== 'staff') return;
        const msgIdStr = String(data.message_id);

        this.activeChats.forEach(chat => {
          const idx = chat.messages.findIndex(m => String(m.id) === msgIdStr);
          if (idx !== -1) {
            chat.messages[idx].reactions = data.reactions;
          }
        });
      },

      scheduleHide() {
        // Clear any existing timeout
        this.clearHideTimeout();

        // Set a new timeout to hide the dropdown after 500ms
        this.hideTimeout = setTimeout(() => {
          this.activeMessageOptions = null;
        }, 500);
      },

      clearHideTimeout() {
        if (this.hideTimeout) {
          clearTimeout(this.hideTimeout);
          this.hideTimeout = null;
        }
      },

      handleClickOutside(event) {
        // Close context menu if clicking outside
        if (this.contextMenu.visible && !event.target.closest('.toggled-context-menu')) {
          this.contextMenu.visible = false;
        }
        // Close filter dropdown if clicking outside
        if (this.showFilterDropdown && !event.target.closest('.filter-dropdown-wrap')) {
          this.showFilterDropdown = false;
        }
        // Close message options if clicking outside
        if (!event.target.closest('.message-options-dropdown') &&
          !event.target.closest('.options-btn') &&
          !event.target.closest('.message-options')) {
          this.clearHideTimeout();
          this.activeMessageOptions = null;
        }
      },

      handleEscapeKey(event) {
        if (event.key === 'Escape') {
          // Close modals in order of priority
          if (this.showHistoryModal) {
            this.closeHistoryModal();
          } else if (this.showForwardModal) {
            this.closeForwardModal();
          } else if (this.showImagePreview) {
            this.closeImagePreview();
          } else if (this.activeChats.length > 0) {
            // Find the topmost (highest z-index) chat
            let topChat = null;
            let topZ = -1;
            for (const c of this.activeChats) {
              const z = c.zIndex || 0;
              if (z > topZ) {
                topZ = z;
                topChat = c;
              }
            }
            if (topChat) {
              // Cancel edit/reply first before closing
              if (topChat.editingMessageId) {
                this.cancelEdit(topChat);
              } else if (topChat.replyingTo) {
                this.cancelReply(topChat);
              } else {
                this.closeChat(topChat);
              }
            }
          } else if (!this.isMinimized) {
            // No chats open, minimize the main panel
            this.toggleMinimize();
          }
        }
      },

      handleContentClick(event) {
        if (event.target.classList.contains('chat-image-preview')) {
          var imageUrl = event.target.getAttribute('data-image-url');
          var imageTitle = event.target.getAttribute('data-image-title');
          if (imageUrl) {
            this.openImagePreview(imageUrl, imageTitle);
          }
          return;
        }

        var replyEl = event.target.closest('.message-reply-context');
        if (replyEl) {
          var originalId = replyEl.getAttribute('data-original-message-id');
          if (originalId && originalId !== '0') {
            var chat = this.activeChats.find(function(c) {
              return c.messages.some(function(m) {
                return String(m.id) === String(originalId);
              });
            });
            if (chat) {
              var msgContainer = document.getElementById('chat-messages-' + chat.userId);
              if (msgContainer) {
                var allMsgs = msgContainer.querySelectorAll('.message');
                var msgIndex = chat.messages.findIndex(function(m) {
                  return String(m.id) === String(originalId);
                });
                if (msgIndex !== -1 && allMsgs[msgIndex]) {
                  allMsgs[msgIndex].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                  });
                  var msgEl = allMsgs[msgIndex];
                  var bubble = msgEl.querySelector('.message-bubble');
                  msgEl.style.transition = 'transform 0.3s ease';
                  msgEl.style.transform = 'scale(1.03)';
                  if (bubble) {
                    bubble.style.transition = 'border-color 0.3s, box-shadow 0.3s';
                    bubble.style.borderColor = '#f59e0b';
                    bubble.style.boxShadow = '0 0 0 2px rgba(245,158,11,0.3)';
                  }
                  setTimeout(function() {
                    msgEl.style.transform = '';
                    if (bubble) {
                      bubble.style.borderColor = '';
                      bubble.style.boxShadow = '';
                    }
                  }, 1800);
                }
              }
            }
          }
        }
      },

      copyMessage(message) {
        // Copy message text to clipboard - use clean text without HTML
        const cleanText = this.getCleanMessageText(message.message);

        navigator.clipboard.writeText(cleanText).then(() => {
          alert_float('success', '<?php echo _l('copied_to_clipboard'); ?>');
          this.activeMessageOptions = null;
        }).catch(() => {
          // Fallback for older browsers
          const textArea = document.createElement('textarea');
          textArea.value = cleanText;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
          alert_float('success', '<?php echo _l('copied_to_clipboard'); ?>');
          this.activeMessageOptions = null;
        });
      },

      editMessage(message, chat) {
        this.activeMessageOptions = null;
        if (!message || !message.id) return;

        var cleanText = this.getCleanMessageText(message.message);
        if (!cleanText) return;

        chat.editingMessageId = message.id;
        chat.currentMessage = cleanText;

        this.$nextTick(() => {
          var input = this.$refs['messageInput' + chat.userId];
          if (input) {
            var el = Array.isArray(input) ? input[0] : input;
            if (el) el.focus();
          }
        });
      },

      getCleanMessageText(message) {
        if (!message) return '';
        var sanitized = (typeof DOMPurify !== 'undefined') ?
          DOMPurify.sanitize(message) :
          message.replace(/<[^>]*>/g, '');
        var text = sanitized.replace(/<[^>]*>/g, '');
        text = text.replace(/^\[Forwarded\]:\s*/, '')
          .replace(/^@reply:\s*"[^"]*"\s*\n?/, '')
          .replace(/^\[REPLY:[^\]]*\]\s*/, '');
        return text.trim();
      },

      getOriginalMessageText(message) {
        if (!message) return '';

        if (message.indexOf('/modules/prchat/uploads/') !== -1) {
          var urlMatch = message.match(/https?:\/\/[^\s<>"']+\/modules\/prchat\/uploads\/[^\s<>"']+/);
          if (urlMatch) return urlMatch[0];

          var imgMatch = message.match(/src="([^"]*\/modules\/prchat\/uploads\/[^"]*)/);
          if (imgMatch) return imgMatch[1];
        }

        var sanitized = (typeof DOMPurify !== 'undefined') ?
          DOMPurify.sanitize(message) :
          message.replace(/<[^>]*>/g, '');
        var text = sanitized.replace(/<[^>]*>/g, '');
        text = text.replace(/^\[Forwarded\]:\s*/, '').replace(/^@reply:\s*/, '');
        return text.trim();
      },

      async deleteMessage(message, chat) {
        if (!this.canDeleteMessages) {
          this.activeMessageOptions = null;
          return;
        }

        if (!confirm('<?php echo _l('confirm_delete_message'); ?>')) {
          this.activeMessageOptions = null;
          return;
        }

        const targetChat = chat || this.activeChats.find(c => c.messages.some(m => m.id === message.id));
        if (!targetChat) {
          this.showNotification('<?php echo _l('error_deleting_message'); ?>', 'error');
          this.activeMessageOptions = null;
          return;
        }

        try {
          const response = await fetch(window.chatConfig.apiUrls.deleteMessage || '<?php echo admin_url("prchat/Prchat_Controller/deleteMessage"); ?>', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
              id: String(message.id),
              contact_id: String(targetChat.userId)
            })
          });

          let ok = false;
          try {
            const data = await response.json();
            ok = response.ok && (data === true || data === 'true' || data === 1);
          } catch (e) {
            ok = false;
          }

          if (ok) {
            const index = targetChat.messages.findIndex(m => m.id === message.id);
            if (index !== -1) {
              targetChat.messages.splice(index, 1);
            }
            this.showNotification('<?php echo _l('message_deleted'); ?>');
          } else {
            throw new Error('Delete failed');
          }
        } catch (error) {
          console.error('Error deleting message:', error);
          this.showNotification('<?php echo _l('error_deleting_message'); ?>', 'error');
        }

        this.activeMessageOptions = null;
      },

      forwardMessage(message) {
        this.messageToForward = message;
        this.selectedUsersForForward = [];
        this.showForwardModal = true;
        this.activeMessageOptions = null;
      },

      toggleUserSelection(userId) {
        const index = this.selectedUsersForForward.indexOf(userId);
        if (index > -1) {
          this.selectedUsersForForward.splice(index, 1);
        } else {
          this.selectedUsersForForward.push(userId);
        }
      },

      async executeForward() {
        if (this.selectedUsersForForward.length === 0) {
          alert_float('warning', '<?php echo _l('chat_select_members_info'); ?>');
          return;
        }

        try {
          // Forward message to each selected user
          for (const userId of this.selectedUsersForForward) {
            const formData = new FormData();
            formData.append('from', this.currentUserId);
            formData.append('to', '#' + userId);
            // Use original message text to preserve image URLs
            const originalForwardText = this.getOriginalMessageText(this.messageToForward.message);
            formData.append('msg', `[Forwarded]: ${originalForwardText}`);
            formData.append('typing', 'false');

            await fetch(window.chatConfig.apiUrls.sendMessage, {
              method: 'POST',
              body: formData
            });

            // Add forwarded message to local UI immediately for each user
            const targetChat = this.activeChats.find(c => c.userId == userId);
            if (targetChat) {
              const newMessage = {
                id: Date.now() + userId, // Unique temporary ID
                sender_id: this.currentUserId,
                message: `[Forwarded]: ${this.messageToForward.message}`,
                time_sent: new Date().toISOString(),
                viewed: 0
              };
              targetChat.messages.push(newMessage);

              // Scroll to bottom
              this.$nextTick(() => {
                const messagesContainer = document.getElementById(`chat-messages-${userId}`);
                if (messagesContainer) {
                  messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
              });
            }
          }

          alert_float('success', '<?php echo _l('chat_forward_success'); ?>');
          this.closeForwardModal();
        } catch (error) {
          console.error('Error forwarding message:', error);
          alert_float('danger', '<?php echo _l('chat_forward_failed'); ?>');
        }
      },

      closeForwardModal() {
        this.showForwardModal = false;
        this.messageToForward = null;
        this.selectedUsersForForward = [];
        this.forwardSearchQuery = '';
      },

      replyToMessage(message) {
        // Set message as reply target
        const chat = this.activeChats.find(c => c.messages.includes(message));
        if (chat) {
          chat.replyingTo = message;
          // Focus the input
          this.$nextTick(() => {
            const input = document.querySelector(`#chat-messages-${chat.userId}`).parentElement.querySelector('.message-input');
            if (input) input.focus();
          });
        }
        this.activeMessageOptions = null;
      },

      cancelReply(chat) {
        chat.replyingTo = null;
      },

      cancelEdit(chat) {
        chat.editingMessageId = null;
        chat.currentMessage = '';
      },

      getMessagePreview(message, maxLength = 50) {
        if (!message) return '';

        if (this.isCallMessage(message)) {
          var info = this.parseCallMessage(message);
          if (info) {
            if (info.type === 'missed_voice' || info.type === 'missed_video') {
              return '<i class="fa fa-phone-slash"></i> <?= addslashes(_l("chat_missed_call")) ?>';
            }
            var icon = info.type === 'video' ? '📹' : '📞';
            var label = info.type === 'video' ? '<?= addslashes(_l("chat_video_call_label")) ?>' : '<?= addslashes(_l("chat_voice_call_label")) ?>';
            var mins = Math.floor(info.duration / 60),
              secs = info.duration % 60;
            var dur = (mins > 0 ? mins + 'm ' : '') + secs + 's';
            return icon + ' ' + label + ' \u2022 ' + dur;
          }
          return '📞 <?= addslashes(_l("chat_voice_call_label")) ?>';
        }

        // Handle image URLs from prchat uploads
        if (message.includes('/modules/prchat/uploads/') && message.match(/\.(gif|jpg|jpeg|png|swf|PNG|JPG|JPEG)$/i)) {
          return '📷 <?php echo _l("chat_photos_text"); ?>';
        }

        // Handle image messages that contain HTML
        if (message.includes('data-chat-file="image"') || message.includes('class="prchat_convertedImage"')) {
          return '📷 <?php echo _l("chat_photos_text"); ?>';
        }

        // Handle file messages - show filename or generic file text
        if (message.includes('data-chat-file="file"')) {
          const linkMatch = message.match(/>([^<]+)<\/a>/);
          return linkMatch ? '📄 ' + linkMatch[1] : '📄 <?php echo _l("chat_file_text"); ?>';
        }

        // Handle voice messages (before video check — .webm/.ogg are voice formats)
        if (/voice_\d+\.(webm|ogg|m4a)$/i.test(message) || message.includes('prchat-inline-audio')) {
          return '🎤 <?php echo _l("chat_voice_message"); ?>';
        }

        // Handle audio messages
        if (message.includes('<audio') || message.includes('audio/ogg') || /\.(mp3|wav|ogg|aac|m4a|webm)$/i.test(message)) {
          return '🎵 <?php echo _l("chat_new_audio_message"); ?>';
        }

        // Handle prchat file uploads (non-images)
        if (message.includes('/modules/prchat/uploads/') && !message.match(/\.(gif|jpg|jpeg|png|swf|PNG|JPG|JPEG)$/i)) {
          const filename = message.replace(/^.*[\\\/]/, '');
          return '📄 ' + filename;
        }

        // Handle video messages
        if (message.includes('<video') || this.isVideoUrl(message)) {
          return '🎬 <?php echo _l("chat_video_text"); ?>';
        }

        // Handle link messages
        if (message.includes('data-video-embed="true"') || (message.includes('<a') && !message.includes('/modules/prchat/uploads/'))) {
          return '🔗 <?php echo _l("chat_new_link_shared"); ?>';
        }

        // Regular text message - clean HTML and truncate
        const cleanText = this.getCleanMessageText(message);
        return cleanText.length > maxLength ? cleanText.substring(0, maxLength) + '...' : cleanText;
      },

      getReplyPreview(message) {
        return this.getMessagePreview(message, 50);
      },

      getForwardPreview(message) {
        return this.getMessagePreview(message, 100);
      },

      // IMAGE PREVIEW METHODS
      openImagePreview(imageUrl, title) {
        this.previewImageUrl = imageUrl;
        this.previewImageTitle = title || '<?php echo _l('chat_image_preview'); ?>';
        this.showImagePreview = true;
      },

      closeImagePreview() {
        this.showImagePreview = false;
        this.previewImageUrl = '';
        this.previewImageTitle = '';
      },



      // CHAT HISTORY MODAL METHODS
      openHistoryModal() {
        // Open modal without requiring a focused chat
        this.showHistoryModal = true;
        this.activeHistoryTab = 'messages';
        this.selectedHistoryUserId = '';
        this.historyUserFilter = '';
        this.historyMessages = [];
        this.filteredHistoryMessages = [];
        this.historySearchQuery = '';
        this.historyLoading = false;
        this.totalHistoryCount = 0;
        // Clear files data
        this.sharedFiles = [];
        this.filteredSharedFiles = [];
        this.fileSearchQuery = '';
        this.filesLoading = false;
        // Clear photos data
        this.sharedPhotos = [];
        this.filteredSharedPhotos = [];
        this.photoSearchQuery = '';
        this.photosLoading = false;

        // Pre-select user if there's a focused chat
        const activeChat = this.activeChats.find(chat => chat.focused);
        if (activeChat) {
          this.selectedHistoryUserId = activeChat.userId;
          // Load history for the pre-selected user
          this.onHistoryUserChange();
        }
      },

      closeHistoryModal() {
        this.showHistoryModal = false;
        this.selectedHistoryUserId = '';
        this.historyUserFilter = '';
        this.activeHistoryTab = 'messages';
        // Clear messages data
        this.historyMessages = [];
        this.filteredHistoryMessages = [];
        this.historySearchQuery = '';
        this.historyLoading = false;
        this.totalHistoryCount = 0;
        // Clear files data
        this.sharedFiles = [];
        this.filteredSharedFiles = [];
        this.fileSearchQuery = '';
        this.filesLoading = false;
        // Clear photos data
        this.sharedPhotos = [];
        this.filteredSharedPhotos = [];
        this.photoSearchQuery = '';
        this.photosLoading = false;
      },

      async onHistoryUserChange() {
        if (!this.selectedHistoryUserId) {
          // Clear all data
          this.historyMessages = [];
          this.filteredHistoryMessages = [];
          this.totalHistoryCount = 0;
          this.historyLoading = false;
          this.sharedFiles = [];
          this.filteredSharedFiles = [];
          this.filesLoading = false;
          this.sharedPhotos = [];
          this.filteredSharedPhotos = [];
          this.photosLoading = false;
          return;
        }

        // Load messages by default when user changes
        this.historyLoading = true;
        this.historySearchQuery = '';

        try {
          await this.loadChatHistory(this.selectedHistoryUserId);
          // Also load shared files in background
          await this.loadSharedFiles(this.selectedHistoryUserId);
        } catch (error) {
          console.error('Failed to load chat history:', error);
          // Ensure we have valid empty arrays on error
          this.historyMessages = [];
          this.filteredHistoryMessages = [];
          this.totalHistoryCount = 0;
          this.historyLoading = false;

          // Show user-friendly error message
          alert_float('info', '<?php echo _l('chat_sorry_no_data'); ?>');
        }
      },

      getSelectedUserName() {
        if (!this.selectedHistoryUserId) return '';
        const user = this.users.find(u => u.staffid == this.selectedHistoryUserId);
        return user ? `${user.firstname} ${user.lastname}` : '';
      },

      getSelectedHistoryUser() {
        if (!this.selectedHistoryUserId) return null;
        return this.users.find(u => u.staffid == this.selectedHistoryUserId) || null;
      },

      async loadChatHistory(userId) {
        try {
          const formData = new FormData();
          formData.append('id', userId);
          formData.append('table', 'chatmessages');

          const response = await fetch(window.chatConfig.siteUrl + '/admin/prchat/Prchat_Controller/searchMessages', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const html = await response.text();

          // Initialize with empty arrays first
          this.historyMessages = [];
          this.filteredHistoryMessages = [];
          this.totalHistoryCount = 0;

          // Extract messages from the response HTML (parse the JSON from the script)
          const messagesMatch = html.match(/var messages_history = JSON\.parse\('(.+?)'\);/);
          if (messagesMatch && messagesMatch[1]) {
            try {
              const messagesJson = messagesMatch[1].replace(/\\'/g, "'").replace(/\\\\/g, "\\");
              const parsedMessages = JSON.parse(messagesJson);

              // Ensure we have a valid array
              if (Array.isArray(parsedMessages)) {
                this.historyMessages = parsedMessages;
                this.filteredHistoryMessages = [...parsedMessages];
                this.totalHistoryCount = parsedMessages.length;
              } else if (parsedMessages && typeof parsedMessages === 'object') {
                // Handle case where single message is returned as object
                this.historyMessages = [parsedMessages];
                this.filteredHistoryMessages = [parsedMessages];
                this.totalHistoryCount = 1;
              }
            } catch (parseError) {
              // Keep empty arrays as initialized above
            }
          }

          // Check if the response indicates no data
          if (html.includes('chat_sorry_no_data') || html.includes('No messages found')) {
            // Already initialized as empty arrays above
          }

        } catch (error) {
          console.error('Error loading chat history:', error);
          // Ensure we have valid empty arrays even on error
          this.historyMessages = [];
          this.filteredHistoryMessages = [];
          this.totalHistoryCount = 0;
          throw error;
        } finally {
          this.historyLoading = false;
        }
      },

      filterHistoryMessages() {
        // Ensure historyMessages is always an array
        if (!Array.isArray(this.historyMessages)) {
          this.historyMessages = [];
          this.filteredHistoryMessages = [];
          return;
        }

        if (!this.historySearchQuery.trim()) {
          this.filteredHistoryMessages = [...this.historyMessages];
          return;
        }

        const query = this.historySearchQuery.toLowerCase();
        this.filteredHistoryMessages = this.historyMessages.filter(message =>
          message && message.message && message.message.toLowerCase().includes(query)
        );
      },

      formatHistoryTime(timeString) {
        // Use moment.js if available, otherwise fallback to basic formatting
        if (window.moment) {
          return window.moment(timeString, "YYYY-MM-DD HH:mm:ss").fromNow();
        } else {
          const date = new Date(timeString);
          return date.toLocaleString();
        }
      },

      processHistoryMessage(message) {
        if (!message) return '';

        var decoded = message;

        var newCallMatch = decoded.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):(\d+):(\d+)\]$/);
        if (newCallMatch) {
          var type = newCallMatch[1];
          var dur = parseInt(newCallMatch[2]);
          var hCallerId = newCallMatch[3];
          var hCalleeId = newCallMatch[4];
          var hIsCaller = String(hCallerId) === String(this.currentUserId);
          var hPeerId = hIsCaller ? hCalleeId : hCallerId;
          var hPeerName = this.getStaffNameById(hPeerId) || '<?= addslashes(_l("chat_staff_fallback")) ?>';
          if (type === 'missed_voice' || type === 'missed_video') {
            var missedIcon = type === 'missed_video' ? 'fa-video-camera' : 'fa-phone';
            var hMissedLabel = hIsCaller ?
              '<?= addslashes(_l("chat_call_no_answer")) ?> ' + hPeerName :
              '<?= addslashes(_l("chat_call_missed_from")) ?> ' + hPeerName;
            return '<span class="history-call-msg" style="color:#e74c3c"><i class="fa ' + missedIcon + '"></i> ' + hMissedLabel + '</span>';
          }
          var icon = type === 'video' ? 'fa-video-camera' : 'fa-phone';
          var mins = Math.floor(dur / 60);
          var secs = dur % 60;
          var durStr = (mins > 0 ? mins + 'm ' : '') + secs + 's';
          var hLabel = hIsCaller ?
            '<?= addslashes(_l("chat_call_you_called")) ?> ' + hPeerName :
            hPeerName + ' <?= addslashes(_l("chat_call_called_you")) ?>';
          return '<span class="history-call-msg"><i class="fa ' + icon + '"></i> ' + hLabel + ' &bull; ' + durStr + '</span>';
        }

        decoded = decoded.replace(/&bull;/g, '\u2022');

        // Legacy format: "Outgoing call • Xs" / "Incoming call • Xs" / "Voice call • Xs"
        var callMatch = decoded.match(/^(Outgoing call|Incoming call|Voice call|Video call)\s*[•·\u2022]\s*(.+)$/i);
        if (!callMatch) {
          var decodedForCall = decoded;
          if (decodedForCall.indexOf('&amp;') !== -1 || decodedForCall.indexOf('&lt;') !== -1) {
            decodedForCall = decodedForCall.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#039;/g, "'").replace(/&quot;/g, '"');
          }
          callMatch = decodedForCall.match(/^(Outgoing call|Incoming call|Voice call|Video call)\s*[•·\u2022]\s*(.+)$/i);
        }

        if (callMatch) {
          var callType = callMatch[1];
          var duration = callMatch[2];
          var callIcon = callType.toLowerCase().indexOf('video') !== -1 ? 'fa-video-camera' : 'fa-phone';
          var safeType = callType.replace(/</g, '&lt;').replace(/>/g, '&gt;');
          var safeDuration = duration.replace(/</g, '&lt;').replace(/>/g, '&gt;');
          return '<span class="history-call-msg"><i class="fa ' + callIcon + '"></i> ' + safeType + ' &bull; ' + safeDuration + '</span>';
        }

        var oldReply = decoded.match(/^@reply:\s*"([^"]*)"\s*\n?([\s\S]*)$/);
        if (oldReply) {
          decoded = '[REPLY:0:text:' + (oldReply[1] || '').substring(0, 80) + '] ' + (oldReply[2] || '');
        }

        if (typeof PrchatSafeRenderer !== 'undefined') {
          return PrchatSafeRenderer.render(decoded).display;
        }

        return decoded.replace(/</g, '&lt;').replace(/>/g, '&gt;');
      },

      // SHARED FILES METHODS
      async loadSharedFiles(userId) {
        this.filesLoading = true;
        this.photosLoading = true;

        try {
          const formData = new FormData();
          formData.append('own_id', this.currentUserId);
          formData.append('contact_id', userId);

          const response = await fetch(window.chatConfig.siteUrl + '/admin/prchat/Prchat_Controller/getSharedFiles', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const jsonResponse = await response.text();

          // The response is JSON-encoded HTML
          let htmlString = '';
          try {
            htmlString = JSON.parse(jsonResponse);
          } catch (e) {
            // If not JSON, treat as plain HTML
            htmlString = jsonResponse;
          }

          // Parse the HTML string to extract file information
          const parser = new DOMParser();
          const doc = parser.parseFromString(htmlString, 'text/html');

          const allFiles = [];
          const allPhotos = [];

          // Parse photos from #photos tab
          const photosTab = doc.querySelector('#photos');
          if (photosTab) {
            const photoLinks = photosTab.querySelectorAll('a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".webp"]');
            photoLinks.forEach(link => {
              const href = link.getAttribute('href');
              if (href && href.includes('/modules/prchat/uploads/')) {
                const fileName = href.split('/').pop();
                if (fileName && this.isImageFile(fileName)) {
                  allPhotos.push({
                    file_name: fileName,
                    url: href
                  });
                }
              }
            });
          }

          // Parse files from #files tab
          const filesTab = doc.querySelector('#files');
          if (filesTab) {
            const fileLinks = filesTab.querySelectorAll('a[href*="/modules/prchat/uploads/"]');
            fileLinks.forEach(link => {
              const href = link.getAttribute('href');
              if (href && href.includes('/modules/prchat/uploads/')) {
                const fileName = href.split('/').pop();
                if (fileName && !this.isImageFile(fileName)) {
                  allFiles.push({
                    file_name: fileName,
                    url: href
                  });
                }
              }
            });
          }


          this.sharedFiles = allFiles;
          this.filteredSharedFiles = [...allFiles];
          this.sharedPhotos = allPhotos;
          this.filteredSharedPhotos = [...allPhotos];

        } catch (error) {
          console.error('Error loading shared files:', error);
          this.sharedFiles = [];
          this.filteredSharedFiles = [];
          this.sharedPhotos = [];
          this.filteredSharedPhotos = [];
        } finally {
          this.filesLoading = false;
          this.photosLoading = false;
        }
      },

      filterSharedFiles() {
        if (!Array.isArray(this.sharedFiles)) {
          this.sharedFiles = [];
          this.filteredSharedFiles = [];
          return;
        }

        if (!this.fileSearchQuery.trim()) {
          this.filteredSharedFiles = [...this.sharedFiles];
          return;
        }

        const query = this.fileSearchQuery.toLowerCase();
        this.filteredSharedFiles = this.sharedFiles.filter(file =>
          file && file.file_name && file.file_name.toLowerCase().includes(query)
        );
      },

      filterSharedPhotos() {
        if (!Array.isArray(this.sharedPhotos)) {
          this.sharedPhotos = [];
          this.filteredSharedPhotos = [];
          return;
        }

        if (!this.photoSearchQuery.trim()) {
          this.filteredSharedPhotos = [...this.sharedPhotos];
          return;
        }

        const query = this.photoSearchQuery.toLowerCase();
        this.filteredSharedPhotos = this.sharedPhotos.filter(photo =>
          photo && photo.file_name && photo.file_name.toLowerCase().includes(query)
        );
      },

      // FILE UTILITY METHODS
      isImageFile(fileName) {
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        const extension = fileName.split('.').pop().toLowerCase();
        return imageExtensions.includes(extension);
      },

      getFileExtension(fileName) {
        return fileName.split('.').pop() || '';
      },

      getFileIcon(fileName) {
        const extension = this.getFileExtension(fileName).toLowerCase();

        const iconMap = {
          pdf: 'fa fa-file-pdf',
          doc: 'fa fa-file-word',
          docx: 'fa fa-file-word',
          xls: 'fa fa-file-excel',
          xlsx: 'fa fa-file-excel',
          ppt: 'fa fa-file-powerpoint',
          pptx: 'fa fa-file-powerpoint',
          txt: 'fa fa-file-text',
          zip: 'fa fa-file-archive',
          rar: 'fa fa-file-archive',
          mp3: 'fa fa-file-audio',
          mp4: 'fa fa-file-video',
          avi: 'fa fa-file-video'
        };

        return iconMap[extension] || 'fa fa-file';
      },

      getFileUrl(fileName) {
        return window.chatConfig.siteUrl + '/modules/prchat/uploads/' + fileName;
      },

      downloadImage() {
        if (this.previewImageUrl) {
          const link = document.createElement('a');
          link.href = this.previewImageUrl;
          link.download = this.previewImageTitle || 'image';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }
      },

      openImageInNewTab() {
        if (this.previewImageUrl) {
          window.open(this.previewImageUrl, '_blank');
        }
      },

      // MESSAGE STATUS HELPERS
      getMessageStatusTitle(message) {
        if (message.status === 'pending') {
          return '<?php echo _l('chat_msg_sending') ?: "Sending..."; ?>';
        } else if (message.status === 'failed') {
          return '<?php echo _l('chat_msg_failed') ?: "Failed"; ?>';
        } else if (message.status === 'sent' || !message.status) {
          if (message.viewed) {
            var ts = message.viewed_at || message.viewed_at_formatted;
            if (ts) {
              var formatted = this.formatSeenDate(ts);
              if (formatted) return formatted;
            }
            return '<?php echo _l('chat_msg_seen') ?: "Seen"; ?>';
          }
          return '<?php echo _l('chat_msg_delivered') ?: "Delivered"; ?>';
        }
        return '';
      },

      async retryMessage(chat, message) {
        if (message.status !== 'failed') return;

        // Reset message status to pending
        message.status = 'pending';

        try {
          const formData = new FormData();
          formData.append('from', this.currentUserId);
          formData.append('to', '#' + chat.userId);
          formData.append('msg', message.message);
          formData.append('typing', 'false');

          const response = await fetch(window.chatConfig.apiUrls.sendMessage, {
            method: 'POST',
            body: formData
          });

          if (response.ok) {
            message.status = 'sent';

            // Try to get server response, but don't fail if there's no JSON
            const responseText = await response.text();
            if (responseText) {
              const result = JSON.parse(responseText);
              if (result && result.id) {
                message.id = result.id;
              }
            }
          } else {
            throw new Error(`Server responded with ${response.status}`);
          }

        } catch (error) {
          console.error('Failed to retry message:', error);
          message.status = 'failed';
        }
      }

      // Removed custom notification system - now using Perfex CRM's alert_float()
    }
  }).mount('#chatApp');

  // Global function for floating notifications to open a chat
  window.openChatWithStaff = function(staffId) {
    if (window.toggledChatApp) {
      const user = window.toggledChatApp.users.find(u => u.staffid == staffId);
      if (user) {
        // Expand main panel if minimized
        if (window.toggledChatApp.isMinimized) {
          window.toggledChatApp.isMinimized = false;
        }
        // Reset user's unread count (user clicked the notification)
        if (user) {
          user.unreadCount = 0;
        }
        // Open the chat
        window.toggledChatApp.openChat(user);
      }
    }
  };

  // GLOBAL FUNCTIONS FOR COMPATIBILITY WITH GENERATED HTML
  window.openImageModal = function(imageUrl, filename) {
    // Create a simple image modal since we can't easily access Vue instance
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        `;

    // Create container for image and controls
    const container = document.createElement('div');
    container.style.cssText = `
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 95%;
            max-height: 95%;
        `;

    // Create image element
    const img = document.createElement('img');
    img.src = imageUrl;
    img.alt = filename || '<?php echo _l('chat_image_preview'); ?>';
    img.style.cssText = `
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            margin-bottom: 15px;
        `;

    // Create action buttons container
    const actionsContainer = document.createElement('div');
    actionsContainer.style.cssText = `
            display: flex;
            gap: 10px;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        `;

    // Create download button
    const downloadBtn = document.createElement('a');
    downloadBtn.href = imageUrl;
    downloadBtn.download = filename || 'image';
    downloadBtn.innerHTML = '<i class="fa fa-download"></i> Download';
    downloadBtn.style.cssText = `
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(0, 123, 255, 0.8);
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            cursor: pointer;
        `;
    downloadBtn.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(0, 123, 255, 1)';
    });
    downloadBtn.addEventListener('mouseleave', function() {
      this.style.background = 'rgba(0, 123, 255, 0.8)';
    });

    // Create open in new tab button
    const openBtn = document.createElement('a');
    openBtn.href = imageUrl;
    openBtn.target = '_blank';
    openBtn.innerHTML = '<i class="fa fa-external-link"></i> Open';
    openBtn.style.cssText = `
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(40, 167, 69, 0.8);
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            cursor: pointer;
        `;
    openBtn.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(40, 167, 69, 1)';
    });
    openBtn.addEventListener('mouseleave', function() {
      this.style.background = 'rgba(40, 167, 69, 0.8)';
    });

    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '<i class="fa fa-times"></i> Close';
    closeBtn.style.cssText = `
            color: white;
            background: rgba(220, 53, 69, 0.8);
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            cursor: pointer;
        `;
    closeBtn.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(220, 53, 69, 1)';
    });
    closeBtn.addEventListener('mouseleave', function() {
      this.style.background = 'rgba(220, 53, 69, 0.8)';
    });

    // Assemble the modal
    actionsContainer.appendChild(downloadBtn);
    actionsContainer.appendChild(openBtn);
    actionsContainer.appendChild(closeBtn);

    container.appendChild(img);
    container.appendChild(actionsContainer);
    modal.appendChild(container);
    document.body.appendChild(modal);

    // Close modal function
    const closeModal = function() {
      if (document.body.contains(modal)) {
        document.body.removeChild(modal);
      }
      document.removeEventListener('keydown', escapeHandler);
    };

    // Close modal on background click (not on container)
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    // Close button click
    closeBtn.addEventListener('click', closeModal);

    // Close modal on escape key
    const escapeHandler = function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    };
    document.addEventListener('keydown', escapeHandler);

    // Prevent actions container clicks from closing modal
    actionsContainer.addEventListener('click', function(e) {
      e.stopPropagation();
    });
    container.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  };
</script>

<!-- Include Custom Emoji System -->
<script src="<?= base_url('modules/prchat/assets/js/custom-emoji-system.js?v=' . VERSIONING); ?>"></script>
<script src="<?= base_url('modules/prchat/assets/js/emoji-picker.js?v=' . VERSIONING); ?>"></script>
<script src="<?= base_url('modules/prchat/assets/js/reaction-picker.js?v=' . VERSIONING); ?>"></script>

<?php
// Include chat settings for compatibility
require(__DIR__ . '/../assets/module_includes/chat_settings.php');

// Include floating notifications widget
if (get_option('chat_floating_notifications_enabled') == '1') {
  require(__DIR__ . '/../assets/module_includes/floating_notifications.php');
}
?>

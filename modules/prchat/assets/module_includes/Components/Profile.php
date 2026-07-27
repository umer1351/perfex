<div id="profile">
     <div class="profile-bar">
          <!-- Left: Avatar + Name/Status -->
          <div class="profile-bar-left">
               <div class="profile-bar-avatar">
                    <?php echo staff_profile_image($params['props']->staffid, ['img', 'img-responsive', 'profile-avatar'], 'small', ['id' => 'profile-img']); ?>
               </div>
               <div class="profile-bar-info">
                    <span class="profile-bar-name"><?= get_staff_full_name(); ?></span>
                    <!-- Status Dropdown -->
                    <div class="status-dropdown" id="status-dropdown-group">
                         <button type="button" class="btn-status" id="status-dropdown-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_status'); ?>">
                              <span class="status-dot online" id="current-status-dot"></span>
                              <span class="status-text" id="current-status-text"><?= _l('chat_status_online'); ?></span>
                              <i class="fa fa-chevron-down"></i>
                         </button>
                         <ul class="dropdown-menu" id="status-dropdown-menu">
                              <li data-status="online" class="status-option active">
                                   <a href="#"><span class="status-dot online"></span> <?= _l('chat_status_online'); ?></a>
                              </li>
                              <li data-status="away" class="status-option">
                                   <a href="#"><span class="status-dot away"></span> <?= _l('chat_status_away'); ?></a>
                              </li>
                              <li data-status="busy" class="status-option">
                                   <a href="#"><span class="status-dot busy"></span> <?= _l('chat_status_busy'); ?></a>
                              </li>
                              <li data-status="offline" class="status-option">
                                   <a href="#"><span class="status-dot offline"></span> <?= _l('chat_status_offline'); ?></a>
                              </li>
                         </ul>
                    </div>
               </div>
          </div>

          <!-- Right: Actions -->
          <div class="profile-bar-actions">
               <!-- Settings Button -->
               <div class="btn-group" id="settings-dropdown-group">
                    <button type="button" class="btn-icon" id="settings-dropdown-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('settings'); ?>">
                         <i class="fa fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" id="settings-dropdown-menu">
                         <li>
                              <a href="#" id="toggle-theme-btn">
                                   <i class="fa fa-adjust"></i>
                                   <span class="theme-label"><?= _l('chat_theme_options_dark'); ?></span>
                              </a>
                         </li>
                         <?php if (staff_can('view', 'settings')) : ?>
                              <li class="divider"></li>
                              <li>
                                   <a href="<?= admin_url('settings?group=perfex_chat_settings'); ?>">
                                        <i class="fa fa-sliders"></i> <?= _l('settings'); ?>
                                   </a>
                              </li>
                         <?php endif; ?>
                    </ul>
               </div>


               <!-- Plus Button -->
               <button type="button" class="btn-icon" id="chat-add-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_new_conversation'); ?>">
                    <i class="fa fa-plus"></i>
               </button>

               <!-- Three Dots Menu -->
               <div class="btn-group" id="options-dropdown-group">
                    <button type="button" class="btn-icon" id="options-dropdown-btn" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?= _l('chat_options'); ?>">
                         <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" id="options-dropdown-menu">
                         <!-- Staff Filter Options (default visible) -->
                         <li class="dropdown-header filter-header staff-filter"><?= _l('filter_by'); ?></li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="all">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="online">
                                   <i class="fa fa-circle" style="color: #22c55e;"></i> <?= _l('chat_online_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="offline">
                                   <i class="fa fa-circle" style="color: #9ca3af;"></i> <?= _l('chat_offline_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option staff-filter">
                              <a href="#" data-filter="unread">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>

                         <!-- Client Filter Options (hidden by default) -->
                         <li class="dropdown-header filter-header client-filter" style="display:none;"><?= _l('filter_by'); ?></li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showAllClientsBtn">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showOnlineContactsBtn">
                                   <i class="fa fa-circle" style="color: #22c55e;"></i> <?= _l('chat_only_online_clients'); ?>
                              </a>
                         </li>
                         <li class="filter-option client-filter" style="display:none;">
                              <a href="#" id="showUnreadClientsBtn">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>

                         <!-- Groups Filter Options (hidden by default) -->
                         <li class="dropdown-header filter-header groups-filter" style="display:none;"><?= _l('filter_by'); ?></li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="all">
                                   <i class="fa fa-users" style="color: #6366f1;"></i> <?= _l('all'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="unread">
                                   <i class="fa fa-envelope" style="color: #f59e0b;"></i> <?= _l('chat_unread_filter'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="my_groups">
                                   <i class="fa fa-user" style="color: #22c55e;"></i> <?= _l('chat_my_groups'); ?>
                              </a>
                         </li>
                         <li class="divider groups-filter" style="display:none;"></li>
                         <li class="dropdown-header groups-filter" style="display:none;"><?= _l('chat_filter_by_association'); ?></li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="project">
                                   <i class="fa-solid fa-briefcase" style="color: #3b82f6;"></i> <?= _l('chat_project'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="ticket">
                                   <i class="fa-solid fa-life-ring" style="color: #ef4444;"></i> <?= _l('chat_ticket'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="invoice">
                                   <i class="fa-solid fa-file-invoice-dollar" style="color: #10b981;"></i> <?= _l('chat_invoice'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="task">
                                   <i class="fa-solid fa-list-check" style="color: #8b5cf6;"></i> <?= _l('chat_task'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="contract">
                                   <i class="fa-solid fa-file-signature" style="color: #f97316;"></i> <?= _l('chat_contract'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="lead">
                                   <i class="fa-solid fa-user-tie" style="color: #ec4899;"></i> <?= _l('chat_lead'); ?>
                              </a>
                         </li>
                         <li class="filter-option groups-filter" style="display:none;">
                              <a href="#" data-group-filter="estimate">
                                   <i class="fa-solid fa-file-invoice" style="color: #06b6d4;"></i> <?= _l('chat_estimate'); ?>
                              </a>
                         </li>

                         <?php if (is_admin()) : ?>
                              <!-- Staff Actions -->
                              <li class="divider actions-divider staff-filter"></li>
                              <li class="dropdown-header staff-filter"><?= _l('chat_actions_menu'); ?></li>
                              <li class="staff-filter action-item">
                                   <a href="#" id="broadcastBtn">
                                        <i class="fa fa-bullhorn" style="color: #ec4899;"></i> <?= _l('chat_message_announcement_text'); ?>
                                   </a>
                              </li>
                              <li class="staff-filter action-item">
                                   <a href="#" id="mentionsBtn">
                                        <i class="fa fa-at" style="color: #8b5cf6;"></i> <?= _l('chat_quick_mention_title'); ?>
                                   </a>
                              </li>

                              <!-- Client Actions -->
                              <li class="divider actions-divider client-filter" style="display:none;"></li>
                              <li class="dropdown-header client-filter" style="display:none;"><?= _l('chat_actions_menu'); ?></li>
                              <li class="client-filter action-item" style="display:none;">
                                   <a href="#" id="clientBroadcastBtn">
                                        <i class="fa fa-bullhorn" style="color: #ec4899;"></i> <?= _l('chat_message_announcement_text'); ?>
                                   </a>
                              </li>
                              <li class="client-filter action-item" style="display:none;">
                                   <a href="#" id="clientMentionsBtn">
                                        <i class="fa fa-at" style="color: #8b5cf6;"></i> <?= _l('chat_quick_mention_title'); ?>
                                   </a>
                              </li>
                         <?php endif; ?>
                    </ul>
               </div>
          </div>
     </div>
</div>

<script>
     // Wait for document ready and jQuery to be available
     document.addEventListener('DOMContentLoaded', function() {
          // Check if jQuery is available, if not wait for it
          var initProfileDropdowns = function() {
               if (typeof jQuery === 'undefined') {
                    setTimeout(initProfileDropdowns, 100);
                    return;
               }

               var $ = jQuery;

               // ========== DROPDOWN TOGGLE HANDLERS ==========
               function closeAllDropdowns() {
                    $('#settings-dropdown-group, #options-dropdown-group, #status-dropdown-group').removeClass('open');
               }

               // Settings dropdown
               $(document).on('click', '#settings-dropdown-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $('#settings-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#settings-dropdown-group').addClass('open');
                    }
               });

               // Options dropdown (three dots)
               $(document).on('click', '#options-dropdown-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $('#options-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#options-dropdown-group').addClass('open');
                    }
               });

               // Status dropdown
               $(document).on('click', '#status-dropdown-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = $('#status-dropdown-group').hasClass('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                         $('#status-dropdown-group').addClass('open');
                    }
               });

               // Close dropdowns when clicking outside
               $(document).on('click', function(e) {
                    if (!$(e.target).closest('#profile .btn-group, #profile .status-dropdown').length) {
                         closeAllDropdowns();
                    }
               });

               // ========== THEME TOGGLE ==========
               $(document).on('click', '#toggle-theme-btn', function(e) {
                    e.preventDefault();
                    var currentTheme = localStorage.getItem("prChatThemeMode") || "light";
                    var newTheme = currentTheme === "dark" ? "light" : "dark";

                    // Apply theme without reload
                    $("body").removeClass("chat_dark chat_light").addClass("chat_" + newTheme);
                    localStorage.setItem("prChatThemeMode", newTheme);

                    // Save to server without reload
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.switchTheme) {
                         $.post(prchatSettings.switchTheme, {
                              theme_name: newTheme
                         });
                    }

                    updateThemeLabel(newTheme);
                    closeAllDropdowns();
               });

               function updateThemeLabel(theme) {
                    var label = theme === "dark" ? "<?= addslashes(_l('chat_theme_options_light')); ?>" : "<?= addslashes(_l('chat_theme_options_dark')); ?>";
                    $('.theme-label').text(label);
               }

               // ========== STATUS CHANGE ==========
               $(document).on('click', '#status-dropdown-menu .status-option a', function(e) {
                    e.preventDefault();
                    var $li = $(this).parent();
                    var status = $li.data('status');
                    var statusText = $(this).text().trim();

                    // Update UI
                    $('#status-dropdown-menu .status-option').removeClass('active');
                    $li.addClass('active');
                    $('#current-status-text').text(statusText);
                    $('#current-status-dot').attr('class', 'status-dot ' + status);
                    $('#profile-img').removeClass('online away busy offline').addClass(status);

                    // Update global status variable
                    if (typeof user_chat_status !== 'undefined') {
                         user_chat_status = status;
                    }

                    // Call the actual status change endpoint
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.handleChatStatus) {
                         $.post(prchatSettings.handleChatStatus, {
                              status: status
                         });
                    }

                    closeAllDropdowns();
               });

               // ========== + BUTTON HANDLER ==========
               $(document).on('click', '#chat-add-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var activeTab = $('.nav-tabs.chat_nav li.active a').attr('href') || '#staff';

                    if (activeTab === '#groups') {
                         // Open create group modal
                         if (typeof prchatSettings !== 'undefined' && prchatSettings.chatGroups) {
                              $(".modal_container").load(prchatSettings.chatGroups, function() {
                                   $("#chat_groups_custom_modal").modal('show');
                              });
                         } else {
                              // Fallback - try to find add group button
                              $('.add_group_btn, #add_group_btn').trigger('click');
                         }
                    } else if (activeTab === '#crm_clients') {
                         // Focus search to find clients - field ID changes dynamically
                         var $search = $('#search_clients_field');
                         if (!$search.length) $search = $('#search_field');
                         if (!$search.length) $search = $('#search input[type="text"]');
                         if ($search.length) {
                              $search.focus();
                         }
                         // Show all client contacts (flat list structure)
                         $(".chat_clients_list > li.contact_name").show("fast");
                    } else {
                         // Staff tab - focus search
                         $('#search_field').focus();
                    }
               });

               // ========== FILTER HANDLERS ==========
               // Helper function to check if element has unread messages
               function hasUnreadMessages(el) {
                    var $el = $(el);
                    var unreadBadge = $el.find('span.unread-notifications, span.badge, .unread-count');
                    if (unreadBadge.length === 0) return false;

                    // Check data-badge attribute first
                    var dataBadge = unreadBadge.attr('data-badge');
                    if (dataBadge && parseInt(dataBadge) > 0) return true;

                    // Check text content
                    var badgeText = unreadBadge.text().trim();
                    if (badgeText && parseInt(badgeText) > 0) return true;

                    // Check if visible and has content
                    return unreadBadge.is(':visible') && unreadBadge.text().trim() !== '';
               }

               // Staff filters
               $(document).on('click', '#options-dropdown-menu .staff-filter a', function(e) {
                    e.preventDefault();
                    var filterName = $(this).data('filter');
                    var staffList = $("#staff .chat_contacts_list, #contacts .chat_contacts_list");

                    // First show all
                    staffList.find("li").show();
                    staffList.find("li a").css('display', '');

                    if (filterName === 'all') {
                         // Already shown all above
                    } else if (filterName === 'online') {
                         staffList.find("li").each(function() {
                              var link = $(this).find('a');
                              var img = $(this).find('img.imgFriend');
                              var isOnline = link.hasClass('on') || img.hasClass('online') || img.hasClass('away') || img.hasClass('busy');
                              $(this).toggle(isOnline);
                         });
                    } else if (filterName === 'offline') {
                         staffList.find("li").each(function() {
                              var link = $(this).find('a');
                              var img = $(this).find('img.imgFriend');
                              var isOffline = link.hasClass('off') || img.hasClass('offline');
                              $(this).toggle(isOffline);
                         });
                    } else if (filterName === 'unread') {
                         staffList.find("li").each(function() {
                              $(this).toggle(hasUnreadMessages(this));
                         });
                    }
                    closeAllDropdowns();
               });

               // Client filters - Show All
               $(document).on('click', '#showAllClientsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").show();
                    closeAllDropdowns();
               });

               // Client filters - Online only
               $(document).on('click', '#showOnlineContactsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").each(function() {
                         var link = $(this).find('a, .contact_name');
                         var img = $(this).find('img');
                         var isOnline = link.hasClass('on') || img.hasClass('online');
                         $(this).toggle(isOnline);
                    });
                    closeAllDropdowns();
               });

               // Client filters - Unread
               $(document).on('click', '#showUnreadClientsBtn', function(e) {
                    e.preventDefault();
                    var clientList = $("#crm_clients .chat_contacts_list, #crm_clients .chat_clients_list, #clients_container .chat_clients_list");
                    clientList.find("li").each(function() {
                         $(this).toggle(hasUnreadMessages(this));
                    });
                    closeAllDropdowns();
               });

               // Group filters
               $(document).on('click', '#options-dropdown-menu [data-group-filter]', function(e) {
                    e.preventDefault();
                    var filter = $(this).data('group-filter');
                    var groupList = $("#groups .chat_groups_list");
                    var userId = typeof userSessionId !== 'undefined' ? userSessionId : '';

                    groupList.find("li.group_selector").each(function() {
                         var $li = $(this);
                         var show = true;

                         if (filter === 'unread') {
                              show = $li.find('.group-unread-badge').length > 0;
                         } else if (filter === 'my_groups') {
                              show = $li.attr('data-created-by') == userId;
                         } else if (filter !== 'all') {
                              // Association type filter
                              show = $li.attr('data-related-type') === filter;
                         }

                         $li.toggle(show);
                    });
                    closeAllDropdowns();
               });

               // Admin actions - Announcement
               $(document).on('click', '#broadcastBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    // Open announcement selection modal
                    $("#chooseAnnouncement").modal('show');
               });

               // Admin actions - Quick Mentions
               $(document).on('click', '#mentionsBtn, #clientMentionsBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    // Load quick mentions modal
                    if (typeof prchatSettings !== 'undefined' && prchatSettings.quickMentions) {
                         $(".modal_container").load(prchatSettings.quickMentions, function() {
                              $("#quickMentionsModal").modal('show');
                         });
                    }
               });

               // Client actions - Announcement
               $(document).on('click', '#clientBroadcastBtn', function(e) {
                    e.preventDefault();
                    closeAllDropdowns();
                    // Open announcement selection modal
                    $("#chooseAnnouncement").modal('show');
               });

               // ========== UPDATE FILTERS FOR TAB CHANGE ==========
               function updateFiltersForTab(activeTab) {
                    $('.staff-filter, .client-filter, .groups-filter').hide();
                    $('.actions-divider').hide();

                    if (activeTab === '#crm_clients') {
                         $('.client-filter').show();
                         $('.actions-divider.client-filter').show();
                    } else if (activeTab === '#staff') {
                         $('.staff-filter').show();
                         $('.actions-divider.staff-filter').show();
                    } else if (activeTab === '#groups') {
                         $('.groups-filter').show();
                    }
               }

               $(document).on('shown.bs.tab', '.nav-tabs.chat_nav a', function(e) {
                    updateFiltersForTab($(e.target).attr('href'));
               });

               $(document).on('click', '.nav-tabs.chat_nav a', function() {
                    var self = this;
                    setTimeout(function() {
                         var activeTab = $('.nav-tabs.chat_nav li.active a').attr('href') || '#staff';
                         updateFiltersForTab(activeTab);
                    }, 100);
               });

               // ========== INITIALIZATION ==========
               // Set theme label
               var savedTheme = localStorage.getItem("prChatThemeMode") || "light";
               updateThemeLabel(savedTheme);

               // Set initial filters
               var activeTab = $('.nav-tabs.chat_nav li.active a').attr('href') || '#staff';
               updateFiltersForTab(activeTab);

               // Set initial status
               var userStatus = '<?= get_user_chat_status() ?>' || 'online';
               var $statusOption = $('#status-dropdown-menu .status-option[data-status="' + userStatus + '"]');
               if ($statusOption.length) {
                    $('#status-dropdown-menu .status-option').removeClass('active');
                    $statusOption.addClass('active');
                    $('#current-status-text').text($statusOption.find('a').text().trim());
                    $('#current-status-dot').attr('class', 'status-dot ' + userStatus);
                    $('#profile-img').addClass(userStatus);
               }
          };

          initProfileDropdowns();
     });
</script>

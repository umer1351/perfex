<!-- Main Loader -->
<div class="main_loader_init" id="main_loader_init">
     <div class="main-loader-content">
          <div class="main-loader-spinner">
               <svg class="main-loader-svg" viewBox="0 0 50 50">
                    <circle class="main-loader-circle" cx="25" cy="25" r="20" fill="none" stroke-width="3"></circle>
               </svg>
          </div>
          <p class="main-loader-text"><?= _l("chat_accessing_channels"); ?></p>
     </div>
</div>
<!-- Sidepanel -->
<div id="sidepanel">
     <?php loadChatComponent('Profile', ['props' => $params['props']]);  ?>
     <div class="connection_field">
          <i class="fa fa-wifi blink"></i>
     </div>
     <div id="search">
          <label for="">
               <svg class="fa-search" viewBox="0 0 24 24">
                    <path d="M10,13C9.65,13.59 9.36,14.24 9.19,14.93C6.5,15.16 3.9,16.42 3.9,17V18.1H9.2C9.37,18.78 9.65,19.42 10,20H2V17C2,14.34 7.33,13 10,13M10,4A4,4 0 0,1 14,8C14,8.91 13.69,9.75 13.18,10.43C12.32,10.75 11.55,11.26 10.91,11.9L10,12A4,4 0 0,1 6,8A4,4 0 0,1 10,4M10,5.9A2.1,2.1 0 0,0 7.9,8A2.1,2.1 0 0,0 10,10.1A2.1,2.1 0 0,0 12.1,8A2.1,2.1 0 0,0 10,5.9M15.5,12C18,12 20,14 20,16.5C20,17.38 19.75,18.21 19.31,18.9L22.39,22L21,23.39L17.88,20.32C17.19,20.75 16.37,21 15.5,21C13,21 11,19 11,16.5C11,14 13,12 15.5,12M15.5,14A2.5,2.5 0 0,0 13,16.5A2.5,2.5 0 0,0 15.5,19A2.5,2.5 0 0,0 18,16.5A2.5,2.5 0 0,0 15.5,14Z" />
               </svg>
          </label>
          <input type="text" id="search_field" placeholder="<?= _l('chat_search_chat_members'); ?>" />
     </div>
     <div class="online-users-counter" style="display:none;">
          <span class="counter-item staff-counter"><span class="online-counter-dot"></span> <span class="counter-val">0</span> <?= _l('chat_staff'); ?></span>
          <span class="counter-sep">&middot;</span>
          <span class="counter-item client-counter"><span class="online-counter-dot client-dot"></span> <span class="counter-val">0</span> <?= _l('chat_clients'); ?></span>
     </div>
     <?php loadChatComponent('ChatNav');  ?>
     <?php loadChatComponent('TabContent');  ?>
</div>

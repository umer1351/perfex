<ul class="nav nav-tabs chat_nav">
  <?php $tabCount = (isClientsEnabled() && staffCanAccessClientsTab()) ? 3 : 2; ?>
  <li class="active staff">
    <a data-toggle="tab" href="#staff">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
      <span><?= _l('chat_staff_text'); ?></span>
    </a>
  </li>
  <li class="groups events_disabled">
    <a data-toggle="tab" class="events_disabled" href="#groups">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 21a8 8 0 0 0-16 0" />
        <circle cx="10" cy="8" r="5" />
        <path d="M22 20c0-3.37-2.69-6.29-6.44-7.4" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
      <span><?= _l('chat_groups_text'); ?></span>
    </a>
  </li>
  <?php if (isClientsEnabled() && staffCanAccessClientsTab()): ?>
    <li class="crm_clients">
      <a data-toggle="tab" class="events_disabled" href="#crm_clients">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <rect x="16" y="3" width="6" height="5" rx="1" />
          <path d="M19 8v3" />
          <path d="M19 14v.01" />
        </svg>
        <span><?= _l('chat_lang_clients'); ?></span>
      </a>
    </li>
  <?php endif; ?>
</ul>

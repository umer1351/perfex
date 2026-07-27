<div class="tab-content">
     <div id="staff" class="tab-pane fade in active">
          <div id="contacts">
               <div class="contacts-skeleton"></div>
               <ul class="chat_contacts_list">
                    <li class="contact">
                         <!-- Contacts list -->
                    </li>
               </ul>
          </div>
     </div>
     <div id="groups" class="tab-pane fade">
          <div id="groups_container">
               <div class="contacts-skeleton"></div>
               <ul class="chat_groups_list">
               </ul>
          </div>
     </div>
     <?php if (isClientsEnabled() && staffCanAccessClientsTab()) : ?>
          <div id="crm_clients" class="tab-pane fade">
               <div id="clients_container">
                    <div class="contacts-skeleton"></div>
                    <ul class="chat_clients_list">
                    </ul>
               </div>
          </div>
     <?php endif; ?>
</div>

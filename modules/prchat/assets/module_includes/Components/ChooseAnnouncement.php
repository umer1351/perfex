<div class="modal fade" id="chooseAnnouncement" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content chat-modal">
            <div class="chat-modal-header">
                <h4><i class="fa fa-bullhorn"></i> <?= _l("chat_choose_group") ?></h4>
                <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="chat-modal-body">
                <p class="modal-subtitle">Select who should receive your announcement</p>
                <div class="selection-buttons">
                    <button type="button" class="selection-btn" id="staffAnnouncementModal">
                        <i class="fa fa-users"></i>
                        <span><?= _l("staff") ?></span>
                    </button>
                    <button type="button" class="selection-btn" id="clientsAnnouncementModal">
                        <i class="fa fa-briefcase"></i>
                        <span><?= _l("clients") ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

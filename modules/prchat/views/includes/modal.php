<div class="modal fade" id="_staffAnnouncementModal" tabindex="-1" role="dialog">
    <form method="POST" id="members_form">
        <div class="modal-dialog" role="document">
            <div class="modal-content chat-modal">
                <div class="chat-modal-header">
                    <h4><i class="fa fa-bullhorn"></i> <?= isset($title) ? $title : _l('chat_staff_announcements') ?></h4>
                    <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="chat-modal-body">
                    <p class="modal-subtitle"><?= _l('chat_message_announcement'); ?></p>
                    <input type="hidden" class="ays-ignore" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    
                    <div class="form-group">
                        <label class="control-label"><?= _l('chat_select_members_info'); ?></label>
                        <select data-none-selected-text="<?= _l('chat_non_selected_member_text'); ?>" data-actions-box="true" id="members" name="members[]" multiple class="selectpicker ajax-search" data-width="100%" data-live-search="true">
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="control-label"><?= _l('chat_your_message_announce'); ?></label>
                        <textarea class="form-control" name="message" rows="5" placeholder="<?= _l('chat_announcement_placeholder') ?>"></textarea>
                    </div>
                </div>
                <div class="chat-modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary submit"><?= _l('chat_send_button'); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    (function($) {
        "use strict";

        appValidateForm($("#members_form"), {
            message: "required",
            'members[]': {
                required: true,
                minlength: 1
            }
        });

        init_ajax_search('prchat_staff', '#members.ajax-search', undefined, prchatSettings.ajaxSearchStaff);

        // On submit event
        $('#members_form').on('submit', function(event) {
            event.preventDefault();
            var btn = $('#members_form .submit');
            var formData = $(this).serialize();
            // Send data to controller
            $.ajax({
                url: prchatSettings.sendStaffAnnouncement,
                method: "POST",
                data: formData,
                beforeSend: function() {
                    $(btn).attr('disabled', true);
                },
                success: function(response) {
                    if (response === 'true') {

                        $('#members option:selected').each(function() {
                            $(this).prop('selected', false);
                        });

                        $('#members').selectpicker('refresh');

                        $('#_staffAnnouncementModal').modal('hide');
                        // If success hide modal and send alert message
                        $('#_staffAnnouncementModal').on('hidden.bs.modal', function() {
                            $('#frame #contacts ul li:first').find('a').click();
                            alert_float('success', "<?= _l('chat_announcement_success'); ?>")
                        });
                    }
                }
            });
        });
    })(jQuery);
</script>

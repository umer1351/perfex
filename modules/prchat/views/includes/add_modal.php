  <div class="modal fade" id="add_members_modal" tabindex="-1" role="dialog">
    <form method="POST" id="members_form_add">
      <div class="modal-dialog" role="any">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= isset($title) ? $title : ''  ?></h4>
          </div>
          <div class="modal-body">
            <h4 align="center" class="mbot20"><?= _l('chat_select_members_info'); ?></h4>
            <input type="hidden" class="ays-ignore" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="form-group">
              <label class="mtop20"><?= _l('chat_group_modal_add_title'); ?></label>
              <select data-none-selected-text="<?= _l('chat_non_selected_member_text'); ?>" data-actions-box="true" id="members" name="members[]" multiple class="selectpicker ajax-search" data-width="100%" data-live-search="true">
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?= _l('chat_add_members'); ?></button>
          </div>
        </div>
      </div>
    </form>
  </div>

<script>
  (function($) {
    "use strict";
      var groupId = <?= json_encode(isset($group_id) ? (int)$group_id : 0); ?>;
      var extraData = groupId ? { exclude_group: groupId } : undefined;
      init_ajax_search('prchat_staff', '#add_members_modal #members.ajax-search', extraData, prchatSettings.ajaxSearchStaff);

      $('#members_form_add').on('submit', function(event) {
        event.preventDefault();
        var group_name = '';
        var group_id = '';

        group_name = $('#frame .chat_groups_list li.group_selector.active').data('channel');
        group_id = $('#frame .chat_groups_list li.group_selector.active').attr('id');

        var select = $('#members_form_add select#members').val();

        if (!select || select.length == 0) {
          alert_float('warning',"<?= _l('chat_group_member_is_required'); ?>");
          return false;
        }

        var formData = $(this).serializeArray();
        formData.push({
          name:'group_name',
          value:group_name
        },
        {
          name:'group_id',
          value:group_id
        });

        var $submitBtn = $('#members_form_add .btn-info');
        $.ajax({
          url: prchatSettings.addChatGroupMembers,
          method: "POST",
          data: formData,
          beforeSend: function() {
            $submitBtn.prop('disabled', true);
          },
          success: function(response) {
            if(response !== ''){
              try {
                response = JSON.parse(response);
              } catch(e) {
                alert_float('danger', "<?= _l('chat_error_float'); ?>");
                $submitBtn.prop('disabled', false);
                return;
              }

              if(!response.data || response.data.result !== 'success'){
                alert_float('warning', "<?= _l('chat_error_float'); ?>");
                $submitBtn.prop('disabled', false);
                return;
              }

              $('#members option:selected').each(function() {
                $(this).prop('selected', false);
              });
              $('#members').selectpicker('refresh');
              $('#add_members_modal').modal('hide');

              var activeGroupId = $('#frame .chat_groups_list li.active').attr('id');
              if (activeGroupId && typeof window.currentGroupMembers !== 'undefined') {
                if (typeof window.renderChatGroupMessages === 'function') {
                    var $activeGroup = $('#frame .chat_groups_list li.active');
                    if ($activeGroup.length) {
                        window.renderChatGroupMessages($activeGroup[0]);
                    }
                }
              }

              alert_float('success', "<?= _l('chat_members_added_successfully'); ?>");
            }
          },
          error: function() {
            alert_float('danger', "<?= _l('chat_error_float'); ?>");
          },
          complete: function() {
            $submitBtn.prop('disabled', false);
          }
        });
      });
      $('#add_members_modal input').keydown(function(e) {
        if (e.keyCode == 32) {
          return false;
        }
      });
    })(jQuery);
  </script>

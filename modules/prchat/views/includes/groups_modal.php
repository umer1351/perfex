<?php defined('BASEPATH') or exit('No direct script access allowed');
$association_only = !empty($association_only);
?>
<div class="modal fade" id="chat_groups_custom_modal" tabindex="-1" role="dialog">
  <form method="POST" id="members_form">
    <div class="modal-dialog" role="any" style="max-width: 480px;">
      <div class="modal-content chat-modal">
        <div class="chat-modal-header">
          <h4><i class="fa <?= $association_only ? 'fa-link' : 'fa-users'; ?>"></i> <?= htmlspecialchars(isset($title) ? $title : _l('chat_group_modal_title'), ENT_QUOTES, 'UTF-8'); ?></h4>
          <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
        </div>
        <div class="chat-modal-body">
          <p class="modal-subtitle"><?= $association_only ? _l('chat_group_update_association') : _l('chat_message_groups'); ?></p>
          <input type="hidden" class="ays-ignore" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <?php if ($association_only) { ?>
            <input type="hidden" id="prchat_group_association_edit_id" value="<?= (int) $group_id; ?>">
          <?php } ?>
          <?php if (!$association_only) { ?>
          <div class="form-group">
            <label for="group_name"><?= _l('chat_groups_channel_text'); ?></label>
            <input type="text" name="group_name" class="form-control group_name" placeholder="<?= _l('chat_groups_channel_text'); ?>">
          </div>
          <div class="form-group">
            <label><?= _l('chat_select_members_info'); ?></label>
            <select data-none-selected-text="<?= _l('chat_non_selected_member_text'); ?>" data-actions-box="true" id="members" name="members[]" multiple class="selectpicker ajax-search" data-width="100%" data-live-search="true">
            </select>
          </div>
          <?php } ?>
          <div class="form-group">
            <label><?= _l('chat_associate_with'); ?> <small class="text-muted">(<?= _l('chat_optional') ?>)</small></label>
            <select id="group_related_type" name="related_type" class="form-control selectpicker" data-none-selected-text="<?= htmlspecialchars(_l('dropdown_non_selected_tex')); ?>">
              <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
              <option value="project" data-icon="fa fa-briefcase"><?= _l('chat_project'); ?></option>
              <option value="invoice" data-icon="fa-solid fa-file-invoice-dollar"><?= _l('chat_invoice'); ?></option>
              <option value="estimate" data-icon="fa-solid fa-file-invoice"><?= _l('chat_estimate'); ?></option>
              <option value="contract" data-icon="fa-solid fa-file-signature"><?= _l('chat_contract'); ?></option>
              <option value="ticket" data-icon="fa-solid fa-life-ring"><?= _l('chat_ticket'); ?></option>
              <option value="lead" data-icon="fa-solid fa-user-tie"><?= _l('chat_lead'); ?></option>
              <option value="task" data-icon="fa-solid fa-list-check"><?= _l('chat_task'); ?></option>
            </select>
          </div>
          <div class="form-group" id="group_related_id_wrapper" style="display:none;">
            <label><?= _l('chat_select_association'); ?></label>
            <select id="group_related_id" name="related_id" class="form-control selectpicker ajax-search" data-live-search="true" data-none-selected-text="<?= htmlspecialchars(_l('chat_select_association')); ?>">
            </select>
          </div>
        </div>
        <div class="chat-modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
          <button type="submit" class="btn btn-primary"><?= $association_only ? _l('submit') : _l('chat_message_groups_text'); ?></button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  (function($) {
    "use strict";
    var associationOnly = <?= $association_only ? 'true' : 'false'; ?>;
    var preRelatedType = <?= json_encode((string) ($related_type ?? '')); ?>;
    var preRelatedId = <?= json_encode((string) ($related_id ?? '')); ?>;
    var preRelatedName = <?= json_encode((string) ($related_name ?? '')); ?>;
    var relatedSelectNone = <?= json_encode(_l('chat_select_association')); ?>;

    if (!associationOnly) {
      init_ajax_search('prchat_staff', '#members.ajax-search', undefined, prchatSettings.ajaxSearchStaff);
    }

    $('#group_related_type').selectpicker();

    function prchatPerfexRelationType(prchatType) {
      var map = {
        project: 'project',
        invoice: 'invoice',
        estimate: 'estimate',
        contract: 'contract',
        ticket: 'ticket',
        lead: 'lead',
        task: 'task'
      };
      return map[prchatType] || '';
    }

    function prchatRebuildRelatedIdSelect(presetId, presetName) {
      var $w = $('#group_related_id_wrapper');
      var $old = $('#group_related_id');
      if ($old.length) {
        try {
          if ($old.data('selectpicker')) {
            $old.selectpicker('destroy');
          }
        } catch (e) {}
        $old.next('.bootstrap-select').remove();
        $old.remove();
      }
      var optHtml = '';
      if (presetId !== undefined && presetId !== null && String(presetId) !== '' && presetName) {
        optHtml = '<option value="' + $('<div>').text(String(presetId)).html() + '" selected>' + $('<div>').text(presetName).html() + '</option>';
      }
      var $sel = $('<select id="group_related_id" name="related_id" class="form-control selectpicker ajax-search" data-live-search="true"></select>');
      $sel.attr('data-none-selected-text', relatedSelectNone);
      $sel.html(optHtml);
      $w.find('label').first().after($sel);
      return $sel;
    }

    function prchatInitRelatedIdAjax(perfexType) {
      var $sel = $('#group_related_id');
      if (!$sel.length || !perfexType) {
        return;
      }
      if (typeof $.fn.ajaxSelectPicker !== 'function') {
        $sel.selectpicker({ liveSearch: true });
        return;
      }
      var ajaxUrl = admin_url + 'misc/get_relation_data';
      if (perfexType === 'task' && prchatSettings.prchatRelationSearch) {
        ajaxUrl = prchatSettings.prchatRelationSearch;
      }
      var lang = (typeof app !== 'undefined' && app.lang) ? app.lang : {};
      var options = {
        ajax: {
          url: ajaxUrl,
          data: function() {
            return {
              type: perfexType,
              rel_id: '',
              q: '{{{q}}}'
            };
          }
        },
        locale: {
          emptyTitle: lang.search_ajax_empty || '',
          statusInitialized: lang.search_ajax_initialized || '',
          statusSearching: lang.search_ajax_searching || '',
          statusNoResults: lang.not_results_found || '',
          searchPlaceholder: lang.search_ajax_placeholder || '',
          currentlySelected: lang.currently_selected || ''
        },
        requestDelay: 500,
        cache: false,
        preprocessData: function(processData) {
          var bs_data = [];
          if (!processData || !processData.length) {
            return bs_data;
          }
          for (var i = 0; i < processData.length; i++) {
            var row = {
              value: processData[i].id,
              text: processData[i].name
            };
            if (processData[i].subtext) {
              row.data = {
                subtext: processData[i].subtext
              };
            }
            bs_data.push(row);
          }
          return bs_data;
        },
        preserveSelectedPosition: 'after',
        preserveSelected: true
      };
      $sel.selectpicker({
        liveSearch: true
      }).ajaxSelectPicker(options);
    }

    function prchatOnRelatedTypeChanged() {
      var raw = $('#group_related_type').val();
      var perfexT = prchatPerfexRelationType(raw);
      var $wrapper = $('#group_related_id_wrapper');
      if (!raw || !perfexT) {
        $wrapper.slideUp(200);
        prchatRebuildRelatedIdSelect();
        return;
      }
      $wrapper.slideDown(200);
      prchatRebuildRelatedIdSelect();
      prchatInitRelatedIdAjax(perfexT);
    }

    $('#group_related_type').on('changed.bs.select', function() {
      prchatOnRelatedTypeChanged();
    });

    if (associationOnly && preRelatedType) {
      $('#group_related_type').val(preRelatedType).selectpicker('refresh');
      $('#group_related_id_wrapper').show();
      prchatRebuildRelatedIdSelect(preRelatedId, preRelatedName);
      prchatInitRelatedIdAjax(prchatPerfexRelationType(preRelatedType));
    }

    $('#members_form').on('submit', function(e) {
      e.preventDefault();
      var editGid = $('#prchat_group_association_edit_id').val();

      if (editGid) {
        var type = $('#group_related_type').val();
        var rid = $('#group_related_id').val();
        if (!type || !rid) {
          alert_float('warning', "<?php echo addslashes(_l('chat_select_association')); ?>");
          return false;
        }
        if (!prchatSettings.updateChatGroupAssociation) {
          alert_float('danger', "<?php echo addslashes(_l('chat_error_float')); ?>");
          return false;
        }
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);
        $.post(prchatSettings.updateChatGroupAssociation, {
          group_id: editGid,
          related_type: type,
          related_id: rid
        }).done(function(r) {
          try { if (typeof r === 'string') r = JSON.parse(r); } catch (ex) { r = {}; }
          if (r.success) {
            $('#chat_groups_custom_modal').modal('hide');
            if (typeof window.prchatRefreshGroupAssociationUI === 'function') {
              window.prchatRefreshGroupAssociationUI(String(editGid), r);
            }
            alert_float('success', "<?php echo addslashes(_l('chat_group_association_saved')); ?>");
          } else {
            alert_float('warning', r.message || "<?php echo addslashes(_l('chat_error_float')); ?>");
          }
        }).fail(function() {
          alert_float('danger', "<?php echo addslashes(_l('chat_error_float')); ?>");
        }).always(function() {
          $btn.prop('disabled', false);
        });
        return false;
      }

      if (staffCanCreateGroups === '0' && !isAdmin) {
        alert_float('warning', "<?php echo addslashes(_l('access_denied')); ?>");
        setTimeout(function() {
          window.location = site_url + 'admin/prchat/Prchat_Controller/chat_full_view';
        }, 1600);
        return false;
      }

      appValidateForm($("#members_form"), {
        group_name: "required",
        'members[]': {
          required: true,
          minlength: 1
        }
      });

      var formData = $(this).serializeArray();
      var group_name = $.trim($('#members_form input[name=group_name]').val());
      var select = $('#members_form select#members').val();

      if (group_name == '') {
        alert_float('warning', "<?php echo addslashes(_l('chat_group_name_required')); ?>");
        return false;
      }
      if (!select || select.length == 0) {
        alert_float('warning', "<?php echo addslashes(_l('chat_group_member_is_required')); ?>");
        return false;
      }

      $.ajax({
        url: prchatSettings.addChatGroup,
        method: "POST",
        data: formData,
        beforeSend: function() {
          $('.btn-primary').attr('disabled', 'disabled');
        },
        success: function(r) {
          if (r !== '') {
            r = JSON.parse(r)
            if (r.message) {
              alert_float('warning', r.message);
              return false;
            }
            if (r.data.result === 'success') {
              $('#members option:selected').each(function() {
                $(this).prop('selected', false);
              });

              $('#members').selectpicker('refresh');
              $('#group_related_type').val('').selectpicker('refresh');
              $('#group_related_id_wrapper').hide();
              prchatRebuildRelatedIdSelect();

              $('#chat_groups_custom_modal').modal('hide');
              $('#chat_groups_custom_modal').on('hidden.bs.modal', function() {
                alert_float('warning', r.data.message);
                $('#frame #sidepanel .groups a').click();
                $('.message-input.group_msg_input').show();
              });
              appendChatGroup(r.data);
            }
          }
        }
      });
    });
  })(jQuery);
</script>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row mbot15"> 
	<div class="col-md-12">
              
		<a href="<?php echo admin_url('logistic/driver'); ?>" class="btn btn-primary pull-left"><?php echo _l('lg_add_drivers'); ?></a>

              <div class="col-md-3">
                     <?php $office_group = '';
                                echo render_select('office_group', $office_groups, array('id', 'office_name'), '', $office_group, ['data-none-selected-text' => _l('lg_filter_by_office_group')], [], 'mbot5') ?>
              </div>

	</div>

</div>

<hr class="hr-panel-heading" />

<div class="clearfix"></div>
<?php

$table_data = [];
        

       $table_data = array_merge($table_data, [
        _l('lg_office_group'),
        _l('lg_full_name'),
        _l('staff_dt_email'),
        _l('lg_active'),
        _l('lg_last_login'),
        _l('lg_vehicle_license_plate'),
        _l('lg_vehicle_code'),
      ]);

   echo render_datatable($table_data, 'drivers'); ?>


<div class="modal fade" id="delete_staff" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('logistic/delete_driver', ['delete_staff_form'])); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="delete_id">
                    <?php echo form_hidden('id'); ?>
                </div>
                <p><?php echo _l('delete_staff_info'); ?></p>
                <?php
                echo render_select('transfer_data_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'staff_member', get_staff_user_id(), [], [], '', '', false);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
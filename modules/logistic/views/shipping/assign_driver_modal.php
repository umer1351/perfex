<div class="modal fade" id="addsign_driver_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/logistic/shipping_assign_driver'); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('lg_assign_driver'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_hidden('shipping_id'); ?>
                        <?php echo form_hidden('redirect_url'); ?>
                        <?php $assign_driver =  '';
                                    echo render_select('assign_driver', $drivers, array('staffid', 'full_name'), '', $assign_driver); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('lg_update'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<div class="modal fade" id="approval_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/logistic/approve_pickup',array('id'=>'approval_modal-form')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('lg_approve_pickup'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_hidden('shipping_id'); ?>
                        <?php echo form_hidden('redirect_url'); ?>
                        <?php echo form_hidden('approval_status'); ?>
                        <?php $approve_note =  '';
                                    echo render_textarea('approve_note', 'approve_note', ''); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" onclick="approve_pickup('rejected'); return false;" class="btn btn-danger"><?php echo _l('lg_reject'); ?></button>
                <button type="button" onclick="approve_pickup('approved'); return false;" class="btn btn-success"><?php echo _l('lg_accept'); ?></button>

            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
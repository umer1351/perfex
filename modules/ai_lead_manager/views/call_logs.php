<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('call_logs', '', 'create')) : ?>
                            <!-- <div class="_buttons">
                                <a href="<?= admin_url('ringcentral/sync_call_logs') ?>" class="text-uppercase btn btn-success text-capitalize pull-right"><?php echo _l('Sync call logs'); ?></a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" /> -->
                            <h4 class="mbot15"><?= _l('alm_call_logs') ?> <?= _l('alm_call_log_summary'); ?></h4>
                            <div class="row">
                                <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold no-mtop"><?php echo get_alm_call_logs_counts('direction', 0); ?></h3>
                                    <p style="color:#989898" class="font-medium no-mbot"><?php echo _l('alm_call_log_inbound'); ?> <i class="fa fa-arrow-down " title="<?php echo _l('alm_call_log_inbound'); ?>" style="font-size: 20px; color: #ed143d"></i></p>
                                </div>
                                <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold no-mtop"><?php echo get_alm_call_logs_counts('direction', 1); ?></h3>
                                    <p style="color:#989898" class="font-medium no-mbot"><?php echo _l('alm_call_log_outbound'); ?> <i class="fa fa-arrow-up " style="font-size: 20px; color: #84c529" title="<?php echo _l('alm_call_log_outbound'); ?>"></i></p>
                                </div>
                                <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold no-mtop"><?php echo get_alm_call_logs_total(); ?></h3>
                                    <p style="color:#989898" class="font-medium no-mbot"><?php echo _l('alm_call_log_total_calls_today'); ?></p>
                                </div>
                                <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold no-mtop"><?php echo get_alm_call_logs_total(true); ?></h3>
                                    <p style="color:#989898" class="font-medium no-mbot"><?php echo _l('alm_call_log_total_calls_yesterday'); ?></p>
                                </div>
                            </div>
                            <hr class="hr-panel-heading" />
                        <?php endif ?>
                        <?php
                        $alm_voice_assistant = get_option('alm_voice_assistant');

                        $table_data = [
                            '#',
                            _l('alm_call_log_call_id'),
                            _l('alm_call_log_created_at'),
                            _l('alm_call_log_direction'),
                            _l('alm_call_log_to_number'),
                            _l('alm_call_log_from_number'),
                            _l('alm_call_log_recording'),
                            ($alm_voice_assistant == 'vapi_ai' ? _l('alm_call_log_ended_reason') : _l('alm_call_log_status')),
                            _l('alm_call_log_duration'),
                            _l('alm_call_log_price'),
                            _l('alm_call_log_lead') . ' #',
                        ];

                        $table_data = hooks()->apply_filters('alm_call_logs_table_columns', $table_data);
                        render_datatable($table_data, 'alm_call_logs', [], [
                            'data-last-order-identifier' => 'alm_call_logs',
                            'data-default-order'         => get_table_last_order('alm_call_logs'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>
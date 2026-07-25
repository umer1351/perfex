<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">
                    <?php if (has_permission('assetcentral_audits', '', 'create')) { ?>
                        <a href="<?php echo admin_url('assetcentral/create_audit'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('assetcentral_create_audit'); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <br>
            <br>
            <br>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <div class="display-block">
                            <hr>
                            <div class="col-md-4 col-xs-6 border-right">
                                <?php echo render_select('assets', $assets, ['id', 'asset_name'], 'assetcentral', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                            </div>
                            <div class="col-md-4 col-xs-6">
                                <?php echo render_select('audit_by', $members, array('staffid', array('firstname', 'lastname')), 'assetcentral_audit_by', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('#'),
                            _l('assetcentral_audit_name'),
                            _l('assetcentral_audit_by'),
                            _l('assetcentral_audit_date'),
                            _l('assetcentral_audit_status'),
                            _l('assetcentral_created_at'),
                            _l('options')
                        ];
                        render_datatable($table_data, 'assetcentral-assets'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";

    var fnServerParams;

    $(function () {

        generate_table();

        fnServerParams = {
            "assets": '[name="assets"]',
            "audit_by": '[name="audit_by"]'
        }

        $('select[name="assets"]').on('change', function () {
            generate_table();
        });

        $('select[name="audit_by"]').on('change', function () {
            generate_table();
        });

    });

    function generate_table() {

        const tableClass = $('.table-assetcentral-assets');

        if ($.fn.DataTable.isDataTable(tableClass)) {
            tableClass.DataTable().destroy();
        }
        initDataTable(tableClass, window.location.href, [0], [0], fnServerParams, [0, 'desc']);

    }
</script>
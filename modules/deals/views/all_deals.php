<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$summary = $dashboard_summary ?? [];
$sla = $sla_summary ?? [];
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Sales Operating System</div>
                    <h3 class="deal-page-hero__title">Deals Dashboard</h3>
                    <p class="deal-page-hero__description">Pipeline execution, governance, follow-up pressure, and forecast coverage in one place.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/new_deal'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('new_deal'); ?>
                    </a>
                    <a href="<?php echo admin_url('deals/reports'); ?>" class="btn btn-default">Reports</a>
                    <a href="<?php echo admin_url('deals/follow_ups'); ?>" class="btn btn-default">Follow-Ups</a>
                    <a href="<?php echo admin_url('deals/diagnostics'); ?>" class="btn btn-default">Diagnostics</a>
                    <a href="<?php echo admin_url('deals/switch_kanban/' . $switch_kanban); ?>"
                       class="btn btn-default"
                       data-toggle="tooltip"
                       data-placement="top"
                       data-title="<?php echo $switch_kanban == 1 ? _l('deals_switch_to_kanban') : _l('switch_to_list_view'); ?>">
                        <?php if ($switch_kanban == 1) { ?>
                            <i class="fa-solid fa-grip-vertical"></i> Kanban
                        <?php } else { ?>
                            <i class="fa-solid fa-table-list"></i> List
                        <?php } ?>
                    </a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Open Pipeline</span>
                    <strong><?php echo app_format_money((float) ($summary['total_pipeline'] ?? 0), get_base_currency()); ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($summary['open_deals'] ?? 0); ?> open deals</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Weighted Pipeline</span>
                    <strong><?php echo app_format_money((float) ($summary['weighted_pipeline'] ?? 0), get_base_currency()); ?></strong>
                    <span class="deal-stat-card__meta">Win rate <?php echo (float) ($summary['win_rate'] ?? 0); ?>%</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Overdue Follow-Ups</span>
                    <strong><?php echo (int) ($summary['overdue_followups'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($sla['inactive_7_plus'] ?? 0); ?> inactive 7+ days</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Closing Soon</span>
                    <strong><?php echo (int) ($summary['closing_next_30_days'] ?? 0); ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($sla['no_followup_open'] ?? 0); ?> without next step</span>
                </div>
            </div>

            <?php if ($this->session->userdata('deals_kanban_view') == 'true') { ?>
                <div class="deal-filter-card">
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div data-toggle="tooltip" data-placement="top" data-title="<?php echo _l('search_by_tags'); ?>">
                                <?php echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'deals_kanban();', 'placeholder' => _l('deals_search')], [], 'no-margin'); ?>
                            </div>
                        </div>
                        <div id="kanban-params">
                            <?php echo render_input('pipeline_id', '', $default_pipeline, 'hidden', ['data-name' => 'pipeline', 'id' => 'pipeline'], [], 'no-margin'); ?>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div data-toggle="tooltip" data-placement="top" data-title="<?php echo _l('pipeline'); ?>">
                                <?php echo render_select('pipeline_id', $piplines, ['pipeline_id', 'pipeline_name'], '', $default_pipeline, ['data-name' => 'select', 'onchange' => 'deals_pipeline_kanban();', 'data-width' => '100%', 'data-none-selected-text' => _l('pipeline')], [], 'no-mbot'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php echo form_hidden('sort_type'); ?>
            <?php echo form_hidden('sort', (get_option('default_deals_kanban_sort') != '' ? get_option('default_deals_kanban_sort_type') : '')); ?>
                <div class="<?php echo $isKanBan ? '' : 'panel_s'; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body'; ?>">
                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="kanban-leads-sort">
                                        <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                                        <a href="#" onclick="deals_kanban_sort('created_at'); return false"
                                           class="dateadded">
                                            <?php if (get_option('default_leads_kanban_sort') == 'created_at') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_datecreated'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="deals_kanban_sort('dealorder');return false;"
                                           class="leadorder">
                                            <?php if (get_option('default_leads_kanban_sort') == 'dealorder') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="deals_kanban_sort('deal_value');return false;"
                                           class="lastcontact">
                                            <?php if (get_option('default_leads_kanban_sort') == 'deal_value') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_deal_value'); ?>
                                        </a>
                                    </div>
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row" id="deals-table">
                                    <div class="col-md-12">
                                        <div class="row deal-filter-card">
                                            <div class="col-md-12">
                                                <p class="bold"><?php echo _l('filter_by'); ?></p>
                                            </div>

                                            <div class="col-md-2 leads-filter-column">
                                                <?php echo render_select('deal_owner', $staff, ['staffid', ['firstname', 'lastname']], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('deal_owner')], [], 'no-mbot'); ?>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <?php
                                                echo '<div id="leads-filter-status">';
                                                echo render_select('view_pipeline', $piplines, ['pipeline_id', 'pipeline_name'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('pipelines')], [], 'no-mbot');
                                                echo '</div>';
                                                ?>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <?php
                                                echo render_select('view_source', $sources, ['source_id', 'source_name'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('deal_source')], [], 'no-mbot');
                                                ?>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <div class="select-placeholder">
                                                    <select name="custom_view"
                                                            title="<?php echo _l('additional_filters'); ?>"
                                                            id="custom_view"
                                                            class="selectpicker" data-width="100%">
                                                        <option value=""></option>
                                                        <option value="customer"><?php echo _l('customer'); ?></option>
                                                        <option value="contract"><?php echo _l('contract'); ?></option>
                                                        <option value="lead"><?php echo _l('lead'); ?></option>
                                                        <option value="proposal"><?php echo _l('proposal'); ?></option>
                                                        <option value="open"><?php echo _l('open'); ?></option>
                                                        <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                                        <option value="won"><?php echo _l('won'); ?></option>
                                                        <option value="created_today"><?php echo _l('created_today'); ?></option>
                                                        <option value="created_this_week"><?php echo _l('created_this_week'); ?></option>
                                                        <option value="created_last_week"><?php echo _l('created_last_week'); ?></option>
                                                        <option value="created_this_month"><?php echo _l('created_this_month'); ?></option>
                                                        <option value="created_last_month"><?php echo _l('created_last_month'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="hr-panel-separator"/>
                                    </div>
                                    <div class="clearfix"></div>

                                    <div class="col-md-12">
                                        <a href="#" data-toggle="modal" data-table=".table-leads"
                                           data-target="#leads_bulk_actions"
                                           class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                                        <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1"
                                             role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (has_permission('leads', '', 'delete')) { ?>
                                                            <div class="checkbox checkbox-danger">
                                                                <input type="checkbox" name="mass_delete"
                                                                       id="mass_delete">
                                                                <label
                                                                        for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                                            </div>
                                                            <hr class="mass_delete_separator"/>
                                                        <?php } ?>
                                                        <div id="bulk_change">
                                                            <div class="form-group">
                                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                                    <input type="checkbox" name="leads_bulk_mark_lost"
                                                                           id="leads_bulk_mark_lost" value="1">
                                                                    <label for="leads_bulk_mark_lost">
                                                                        <?php echo _l('lead_mark_as_lost'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <?php echo render_select('move_to_status_leads_bulk', $statuses, ['id', 'name'], 'ticket_single_change_status'); ?>
                                                            <?php
                                                            echo render_select('move_to_source_leads_bulk', $sources, ['source_id', 'source_name'], 'lead_source');
                                                            echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                                            echo render_select('assign_to_leads_bulk', $staff, ['staffid', ['firstname', 'lastname']], 'leads_dt_assigned');
                                                            ?>
                                                            <div class="form-group">
                                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                                <input type="text" class="tagsinput" id="tags_bulk"
                                                                       name="tags_bulk" value="" data-role="tagsinput">
                                                            </div>
                                                            <hr/>
                                                            <div class="form-group no-mbot">
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                           id="leads_bulk_public" value="public">
                                                                    <label for="leads_bulk_public">
                                                                        <?php echo _l('lead_public'); ?>
                                                                    </label>
                                                                </div>
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                           id="leads_bulk_private" value="private">
                                                                    <label for="leads_bulk_private">
                                                                        <?php echo _l('private'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                                data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                        <a href="#" class="btn btn-primary"
                                                           onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-striped DataTables " id="DataTables" width="100%">
                                                <thead>
                                                <tr>
                                                    <th><?= _l('title') ?></th>
                                                    <th><?= _l('deal_value') ?></th>
                                                    <th><?= _l('tags') ?></th>
                                                    <th><?= _l('stage') ?></th>
                                                    <th><?= _l('close') . ' ' . _l('date') ?></th>
                                                    <th><?= _l('status') ?></th>
                                                    <?php
                                                    $custom_fields = get_table_custom_fields('deals');
                                                    foreach ($custom_fields as $field) {
                                                        echo '<th>' . $field['name'] . '</th>';
                                                    }
                                                    ?>
                                                    <th><?= _l('action') ?></th>
                                                </tr>
                                                </thead>
                                                <tbody id="deals_table">

                                                </tbody>
                                            </table>
                                            <script type="text/javascript">
                                                list = "<?= admin_url() ?>deals/dealsList";
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>

    deals_kanban();

    function deals_kanban_sort(type) {
        kan_ban_sort(type, deals_kanban);
    }

    function deals_pipeline_kanban() {
        let pipeline_id = $('select[name="pipeline_id"]').val();
        // update the value of pipeline id
        $('input[name="pipeline_id"]').val(pipeline_id);
        deals_kanban(pipeline_id);
    }

    // Init the leads kanban
    function deals_kanban(search) {
        init_kanban(
            "deals/kanban",
            deals_kanban_update,
            ".leads-status",
            290,
            360,
            init_deals_stages_sortable
        );
    }

    function deals_kanban_update(ui, object) {
        if (object !== ui.item.parent()[0]) {
            return;
        }

        var data = {
            status: $(ui.item.parent()[0]).attr("data-lead-status-id"),
            leadid: $(ui.item).attr("data-lead-id"),
            order: [],
        };

        $.each($(ui.item).parents(".leads-status").find("li"), function (idx, el) {
            var id = $(el).attr("data-lead-id");
            if (id) {
                data.order.push([id, idx + 1]);
            }
        });

        setTimeout(function () {
            $.post(admin_url + "deals/update_deal_satges", data).done(function (
                response
            ) {
                update_kan_ban_total_when_moving(ui, data.status);
                deals_kanban();
            });
        }, 200);
    }

    function init_deals_stages_sortable() {
        $("#kan-ban").sortable({
            helper: "clone",
            item: ".kan-ban-col",
            update: function (event, ui) {
                data = {
                    order: [],
                };

                $.each($(".kan-ban-col"), function (idx, el) {
                    data.order.push([$(el).attr("data-col-status-id"), idx + 1]);
                });

                $.post(admin_url + "deals/update_stage_order", data);
            },
        });
    }

    // check list is have veriable or not
    if (typeof list !== 'undefined') {

        const DealsServerParams = {
            custom_view: "[name='custom_view']",
            deal_owner: "[name='deal_owner']",
            pipeline: "[name='view_pipeline']",
            source: "[name='view_source']",
            company: "[name='company_id']",
        };
        $(function () {
            'use strict';
            initDataTable('#DataTables', list, undefined, undefined, DealsServerParams);
        });
        const table_deals = $("table#DataTables");

        $.each(DealsServerParams, function (i, obj) {
            let params = {};
            $("select" + obj).on("change", function () {
                $("[name='view_status[]']")
                    .prop("disabled", $(this).val() == "lost" || $(this).val() == "junk")
                    .selectpicker("refresh");
                params[i] = $(this).val();
            });
            $("select" + obj).on("change", function () {
                table_deals.DataTable().ajax.reload();
            });
        });
    }
</script>

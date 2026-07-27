<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <i class="fa fa-history"></i>
                                    <?php echo _l('fb_leads_sync_history'); ?>
                                </h4>
                                <p class="text-muted mtop5"><?php echo _l('fb_leads_sync_history_description'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('facebookleadsintegration'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('fb_leads_back_to_settings'); ?>
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Statistics Row -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box success">
                                    <div class="stat-number"><?php echo $stats['success'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_successful_syncs'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box danger">
                                    <div class="stat-number"><?php echo $stats['failed'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_failed_syncs'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box warning">
                                    <div class="stat-number"><?php echo $stats['skipped'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_skipped_duplicates'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fb-leads-stat-box info">
                                    <div class="stat-number"><?php echo $stats['pending_retry'] ?? 0; ?></div>
                                    <div class="stat-label"><?php echo _l('fb_leads_pending_retry'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Retry Queue Action -->
                        <?php if (($stats['pending_retry'] ?? 0) > 0): ?>
                        <div class="alert alert-warning mtop20">
                            <div class="row">
                                <div class="col-md-8">
                                    <strong><?php echo _l('fb_leads_pending_items_notice'); ?></strong>
                                    <br>
                                    <?php echo _l('fb_leads_pending_items_description'); ?>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button type="button" class="btn btn-warning" id="btn-process-retry">
                                        <i class="fa fa-refresh"></i> <?php echo _l('fb_leads_process_retry_queue'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Filters -->
                        <div class="row mtop20">
                            <div class="col-md-12">
                                <form method="GET" action="<?php echo admin_url('facebookleadsintegration/sync_history'); ?>" class="form-inline">
                                    <div class="form-group">
                                        <label><?php echo _l('fb_leads_filter_status'); ?>:</label>
                                        <select name="status" class="form-control">
                                            <option value=""><?php echo _l('fb_leads_all_statuses'); ?></option>
                                            <option value="success" <?php echo ($filters['status'] ?? '') == 'success' ? 'selected' : ''; ?>><?php echo _l('fb_leads_status_success'); ?></option>
                                            <option value="failed" <?php echo ($filters['status'] ?? '') == 'failed' ? 'selected' : ''; ?>><?php echo _l('fb_leads_status_failed'); ?></option>
                                            <option value="skipped" <?php echo ($filters['status'] ?? '') == 'skipped' ? 'selected' : ''; ?>><?php echo _l('fb_leads_status_skipped'); ?></option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo _l('fb_leads_date_from'); ?>:</label>
                                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo _l('fb_leads_date_to'); ?>:</label>
                                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-filter"></i> <?php echo _l('fb_leads_apply_filter'); ?>
                                    </button>
                                    <a href="<?php echo admin_url('facebookleadsintegration/sync_history'); ?>" class="btn btn-default">
                                        <i class="fa fa-times"></i> <?php echo _l('fb_leads_clear_filter'); ?>
                                    </a>
                                </form>
                            </div>
                        </div>

                        <!-- History Table -->
                        <div class="table-responsive mtop20">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fb_leads_datetime'); ?></th>
                                        <th><?php echo _l('fb_leads_facebook_id'); ?></th>
                                        <th><?php echo _l('fb_leads_perfex_lead'); ?></th>
                                        <th><?php echo _l('fb_leads_status'); ?></th>
                                        <th><?php echo _l('fb_leads_message'); ?></th>
                                        <th><?php echo _l('fb_leads_actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($history['items'])): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <?php echo _l('fb_leads_no_sync_history'); ?>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($history['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('M j, Y H:i', strtotime($item['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($item['facebook_lead_id']); ?></code>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['perfex_lead_id'])): ?>
                                                <a href="<?php echo admin_url('leads/index/' . $item['perfex_lead_id']); ?>" target="_blank">
                                                    #<?php echo $item['perfex_lead_id']; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'success' => 'success',
                                                'failed' => 'danger',
                                                'skipped' => 'warning',
                                                'retry' => 'info'
                                            ];
                                            $class = $status_class[$item['status']] ?? 'default';
                                            ?>
                                            <span class="label label-<?php echo $class; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($item['message']); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['details'])): ?>
                                            <button type="button" class="btn btn-xs btn-default" 
                                                    onclick="showDetails(<?php echo htmlspecialchars(json_encode($item['details'])); ?>)">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($history['pages'] > 1): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <nav>
                                    <ul class="pagination">
                                        <?php for ($i = 1; $i <= $history['pages']; $i++): ?>
                                        <li class="<?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a href="<?php echo admin_url('facebookleadsintegration/sync_history?page=' . $i . 
                                                (!empty($filters['status']) ? '&status=' . $filters['status'] : '') .
                                                (!empty($filters['date_from']) ? '&date_from=' . $filters['date_from'] : '') .
                                                (!empty($filters['date_to']) ? '&date_to=' . $filters['date_to'] : '')); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Last Sync Info -->
                        <?php if (!empty($stats['last_sync'])): ?>
                        <div class="text-muted text-right mtop10">
                            <small>
                                <?php echo _l('fb_leads_last_sync'); ?>: 
                                <?php echo date('M j, Y H:i:s', strtotime($stats['last_sync'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fb_leads_sync_details'); ?></h4>
            </div>
            <div class="modal-body">
                <pre id="details-content" style="max-height: 400px; overflow: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
function showDetails(details) {
    try {
        var parsed = typeof details === 'string' ? JSON.parse(details) : details;
        $('#details-content').text(JSON.stringify(parsed, null, 2));
    } catch (e) {
        $('#details-content').text(details);
    }
    $('#detailsModal').modal('show');
}

$(function() {
    // Process retry queue
    $('#btn-process-retry').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('fb_leads_processing'); ?>');
        
        $.ajax({
            url: admin_url + 'facebookleadsintegration/process_retry_queue',
            type: 'POST',
            dataType: 'json',
            data: { <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>' },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> <?php echo _l('fb_leads_process_retry_queue'); ?>');
                
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert_float('danger', response.message || 'Failed to process retry queue');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> <?php echo _l('fb_leads_process_retry_queue'); ?>');
                alert_float('danger', 'Failed to process retry queue');
            }
        });
    });
});
</script>

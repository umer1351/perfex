<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$summary = $qa_result['summary'] ?? ['passed' => 0, 'warnings' => 0, 'failed' => 0];
$checksPagination = $qa_checks_pagination ?? ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1];
$logsPagination = $qa_logs_pagination ?? ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1];
$encodeMeta = function ($value) {
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $json = '{}';
    }

    return base64_encode($json);
};
$buildPageUrl = function ($key, $page) {
    $query = $_GET;
    $query[$key] = max(1, (int) $page);
    unset($query['run']);

    return current_url() . '?' . http_build_query($query);
};
$runAgainUrl = function () {
    $query = $_GET;
    $query['run'] = 1;
    $query['checks_page'] = 1;
    $query['logs_page'] = 1;

    return current_url() . '?' . http_build_query($query);
};
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Runtime QA</div>
                    <h3 class="deal-page-hero__title">Legacy Flow and Provider Fixture Tests</h3>
                    <p class="deal-page-hero__description">Executes non-destructive runtime checks across older deal tabs, enterprise extensions, and provider-specific fixtures for Mailgun, SendGrid, Postmark, Slack, and Teams.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo html_escape($runAgainUrl()); ?>" class="btn btn-primary">Run Again</a>
                    <a href="<?php echo admin_url('deals/diagnostics'); ?>" class="btn btn-default">Diagnostics</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Passed</span>
                    <strong><?php echo (int) $summary['passed']; ?></strong>
                    <span class="deal-stat-card__meta">Healthy checks</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Warnings</span>
                    <strong><?php echo (int) $summary['warnings']; ?></strong>
                    <span class="deal-stat-card__meta">Attention required</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Failed</span>
                    <strong><?php echo (int) $summary['failed']; ?></strong>
                    <span class="deal-stat-card__meta">Broken or inaccessible flows</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Sample Deal</span>
                    <strong><?php echo !empty($qa_result['deal_id']) ? '#' . (int) $qa_result['deal_id'] : '-'; ?></strong>
                    <span class="deal-stat-card__meta">Runtime context</span>
                </div>
            </div>

            <div class="deal-panel">
                <h4 class="deal-panel__title">Current QA Run</h4>
                <div class="table-responsive">
                    <table class="table table-hover deal-data-table">
                        <thead>
                        <tr>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th class="text-right">View</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($qa_result['checks'] ?? []) as $check) { ?>
                            <tr>
                                <td><?php echo ucwords(str_replace('_', ' ', $check['area'])); ?></td>
                                <td>
                                    <span class="deal-pill <?php echo $check['status'] === 'success' ? 'deal-pill--status-won' : ($check['status'] === 'failed' ? 'deal-pill--status-lost' : 'deal-pill--approval'); ?>">
                                        <?php echo ucfirst($check['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo html_escape($check['message']); ?></td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="btn btn-default btn-icon deal-qa-view-meta"
                                        data-toggle="modal"
                                        data-target="#dealQaMetaModal"
                                        data-title="<?php echo html_escape(ucwords(str_replace('_', ' ', $check['area']))); ?>"
                                        data-status="<?php echo html_escape(ucfirst($check['status'])); ?>"
                                        data-message="<?php echo html_escape($check['message']); ?>"
                                        data-meta="<?php echo html_escape($encodeMeta($check['meta'] ?? [])); ?>"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($qa_result['checks'])) { ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No QA checks were executed.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($checksPagination['total_pages'] ?? 1) > 1) { ?>
                    <div class="deal-table-pagination">
                        <div class="deal-table-pagination__summary">
                            Showing <?php echo (int) (((($checksPagination['page'] ?? 1) - 1) * ($checksPagination['per_page'] ?? 10)) + 1); ?>
                            -
                            <?php echo (int) min(($checksPagination['page'] ?? 1) * ($checksPagination['per_page'] ?? 10), ($checksPagination['total'] ?? 0)); ?>
                            of <?php echo (int) ($checksPagination['total'] ?? 0); ?>
                        </div>
                        <div class="deal-table-pagination__links">
                            <?php if (($checksPagination['page'] ?? 1) > 1) { ?>
                                <a href="<?php echo html_escape($buildPageUrl('checks_page', ($checksPagination['page'] ?? 1) - 1)); ?>" class="btn btn-default btn-sm">Previous</a>
                            <?php } ?>
                            <span class="deal-table-pagination__current">Page <?php echo (int) ($checksPagination['page'] ?? 1); ?> / <?php echo (int) ($checksPagination['total_pages'] ?? 1); ?></span>
                            <?php if (($checksPagination['page'] ?? 1) < ($checksPagination['total_pages'] ?? 1)) { ?>
                                <a href="<?php echo html_escape($buildPageUrl('checks_page', ($checksPagination['page'] ?? 1) + 1)); ?>" class="btn btn-default btn-sm">Next</a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="deal-panel tw-mt-4">
                <h4 class="deal-panel__title">Recent QA Log</h4>
                <div class="table-responsive">
                    <table class="table table-hover deal-data-table">
                        <thead>
                        <tr>
                            <th>Created</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Deal</th>
                            <th class="text-right">View</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($qa_logs as $log) { ?>
                            <?php $details = json_decode($log['details_json'] ?? '[]', true) ?: []; ?>
                            <tr>
                                <td><?php echo _dt($log['created_at']); ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $log['area'])); ?></td>
                                <td>
                                    <span class="deal-pill <?php echo $log['status'] === 'success' ? 'deal-pill--status-won' : ($log['status'] === 'failed' ? 'deal-pill--status-lost' : 'deal-pill--approval'); ?>">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($log['deal_id']) ? '#' . (int) $log['deal_id'] : '-'; ?></td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="btn btn-default btn-icon deal-qa-view-meta"
                                        data-toggle="modal"
                                        data-target="#dealQaMetaModal"
                                        data-title="<?php echo html_escape(ucwords(str_replace('_', ' ', $log['area']))); ?>"
                                        data-status="<?php echo html_escape(ucfirst($log['status'])); ?>"
                                        data-message="<?php echo html_escape('Runtime QA log captured on ' . _dt($log['created_at'])); ?>"
                                        data-meta="<?php echo html_escape($encodeMeta($details)); ?>"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($qa_logs)) { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No runtime QA logs have been recorded yet.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($logsPagination['total_pages'] ?? 1) > 1) { ?>
                    <div class="deal-table-pagination">
                        <div class="deal-table-pagination__summary">
                            Showing <?php echo (int) (((($logsPagination['page'] ?? 1) - 1) * ($logsPagination['per_page'] ?? 10)) + 1); ?>
                            -
                            <?php echo (int) min(($logsPagination['page'] ?? 1) * ($logsPagination['per_page'] ?? 10), ($logsPagination['total'] ?? 0)); ?>
                            of <?php echo (int) ($logsPagination['total'] ?? 0); ?>
                        </div>
                        <div class="deal-table-pagination__links">
                            <?php if (($logsPagination['page'] ?? 1) > 1) { ?>
                                <a href="<?php echo html_escape($buildPageUrl('logs_page', ($logsPagination['page'] ?? 1) - 1)); ?>" class="btn btn-default btn-sm">Previous</a>
                            <?php } ?>
                            <span class="deal-table-pagination__current">Page <?php echo (int) ($logsPagination['page'] ?? 1); ?> / <?php echo (int) ($logsPagination['total_pages'] ?? 1); ?></span>
                            <?php if (($logsPagination['page'] ?? 1) < ($logsPagination['total_pages'] ?? 1)) { ?>
                                <a href="<?php echo html_escape($buildPageUrl('logs_page', ($logsPagination['page'] ?? 1) + 1)); ?>" class="btn btn-default btn-sm">Next</a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="dealQaMetaModal" tabindex="-1" role="dialog" aria-labelledby="dealQaMetaModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content deal-qa-modal">
            <div class="modal-header deal-qa-modal__header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div>
                    <div class="deal-page-hero__eyebrow">QA Metadata</div>
                    <h4 class="modal-title deal-qa-modal__title" id="dealQaMetaModalLabel">QA Metadata</h4>
                </div>
            </div>
            <div class="modal-body deal-qa-modal__body">
                <div class="deal-qa-modal__summary">
                    <span class="deal-pill deal-pill--approval" id="dealQaMetaStatus">Status</span>
                    <p class="deal-qa-modal__message" id="dealQaMetaMessage">Metadata preview</p>
                </div>
                <div class="deal-qa-modal__code-wrap">
                    <pre class="deal-qa-modal__code" id="dealQaMetaContent">{}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    (function ($) {
        function decodeMeta(value) {
            if (!value) {
                return '{}';
            }

            try {
                return atob(value);
            } catch (error) {
                return '{}';
            }
        }

        function resolveStatusClass(status) {
            status = (status || '').toLowerCase();

            if (status === 'success') {
                return 'deal-pill deal-pill--status-won';
            }

            if (status === 'failed') {
                return 'deal-pill deal-pill--status-lost';
            }

            return 'deal-pill deal-pill--approval';
        }

        $(document).on('click', '.deal-qa-view-meta', function () {
            var $button = $(this);
            var title = $button.data('title') || 'QA Metadata';
            var status = $button.data('status') || 'Info';
            var message = $button.data('message') || '';
            var meta = decodeMeta($button.data('meta'));

            $('#dealQaMetaModalLabel').text(title);
            $('#dealQaMetaStatus').attr('class', resolveStatusClass(status)).text(status);
            $('#dealQaMetaMessage').text(message);
            $('#dealQaMetaContent').text(meta);
        });
    })(jQuery);
</script>

<?php init_head(); ?>
<link rel="stylesheet" id="deals-style" href="<?php echo module_dir_url(DEALS_MODULE); ?>assets/css/style.css">
<?php
$sla = $diagnostics['sla_summary'] ?? [];
?>
<div id="wrapper">
    <div class="content">
        <div class="deal-page-shell">
            <div class="deal-page-hero">
                <div>
                    <div class="deal-page-hero__eyebrow">Quality Assurance</div>
                    <h3 class="deal-page-hero__title">Deal Diagnostics</h3>
                    <p class="deal-page-hero__description">Operational QA for schema readiness, SLA pressure, approval backlog, and outbound integration health.</p>
                </div>
                <div class="deal-page-hero__actions">
                    <a href="<?php echo admin_url('deals/runtime_qa'); ?>" class="btn btn-default">Runtime QA</a>
                    <a href="<?php echo admin_url('deals/approval_policies'); ?>" class="btn btn-default">Governance</a>
                </div>
            </div>

            <div class="deal-stat-strip">
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Schema Gaps</span>
                    <strong><?php echo count($diagnostics['missing_tables']) + count($diagnostics['missing_columns']); ?></strong>
                    <span class="deal-stat-card__meta">Missing tables or columns</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Pending Approvals</span>
                    <strong><?php echo (int) $diagnostics['pending_approvals']; ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) ($diagnostics['approval_policies'] ?? 0); ?> active policies</span>
                </div>
                <div class="deal-stat-card">
                    <span class="deal-stat-card__label">Webhook Failures</span>
                    <strong><?php echo (int) $diagnostics['failed_webhook_logs']; ?></strong>
                    <span class="deal-stat-card__meta"><?php echo (int) $diagnostics['active_webhooks']; ?> active endpoints</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title">Schema Readiness</h4>
                        <?php if (empty($diagnostics['missing_tables']) && empty($diagnostics['missing_columns'])) { ?>
                            <div class="alert alert-success mbot0">All required enterprise schema components are present.</div>
                        <?php } else { ?>
                            <?php if (!empty($diagnostics['missing_tables'])) { ?>
                                <div class="alert alert-warning">
                                    <strong>Missing tables:</strong> <?php echo implode(', ', $diagnostics['missing_tables']); ?>
                                </div>
                            <?php } ?>
                            <?php if (!empty($diagnostics['missing_columns'])) { ?>
                                <div class="alert alert-warning mbot0">
                                    <strong>Missing `tbl_deals` columns:</strong> <?php echo implode(', ', $diagnostics['missing_columns']); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="deal-panel">
                        <h4 class="deal-panel__title">SLA Pressure</h4>
                        <div class="deal-kpi-grid">
                            <div class="deal-kpi-box">
                                <span>Overdue follow-ups</span>
                                <strong><?php echo (int) ($sla['overdue_followups'] ?? 0); ?></strong>
                            </div>
                            <div class="deal-kpi-box">
                                <span>No owner</span>
                                <strong><?php echo (int) ($sla['no_owner_open'] ?? 0); ?></strong>
                            </div>
                            <div class="deal-kpi-box">
                                <span>No next follow-up</span>
                                <strong><?php echo (int) ($sla['no_followup_open'] ?? 0); ?></strong>
                            </div>
                            <div class="deal-kpi-box">
                                <span>Inactive 7+ days</span>
                                <strong><?php echo (int) ($sla['inactive_7_plus'] ?? 0); ?></strong>
                            </div>
                            <div class="deal-kpi-box">
                                <span>Inactive 14+ days</span>
                                <strong><?php echo (int) ($sla['inactive_14_plus'] ?? 0); ?></strong>
                            </div>
                            <div class="deal-kpi-box">
                                <span>At risk or off track</span>
                                <strong><?php echo (int) ($sla['at_risk_open'] ?? 0); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="deal-panel tw-mt-4">
                <h4 class="deal-panel__title">Attention Queue</h4>
                <div class="table-responsive">
                    <table class="table table-hover deal-data-table">
                        <thead>
                        <tr>
                            <th>Deal</th>
                            <th>Owner</th>
                            <th>Signals</th>
                            <th>Attention</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($diagnostics['attention_queue'] as $deal) { ?>
                            <tr>
                                <td>
                                    <a href="<?php echo admin_url('deals/details/' . $deal['id']); ?>" class="deal-table-title"><?php echo html_escape($deal['title']); ?></a>
                                    <div class="text-muted"><?php echo $deal['stage_name'] ?: 'Unassigned stage'; ?></div>
                                </td>
                                <td><?php echo $deal['owner_name'] ?: 'Unassigned'; ?></td>
                                <td class="text-muted">
                                    <?php echo (int) $deal['inactive_days']; ?> inactive days
                                    <?php if (!empty($deal['pending_approvals'])) { ?>
                                        <br><?php echo (int) $deal['pending_approvals']; ?> pending approvals
                                    <?php } ?>
                                    <?php if (!empty($deal['next_follow_up_at'])) { ?>
                                        <br>Next follow-up <?php echo _dt($deal['next_follow_up_at']); ?>
                                    <?php } else { ?>
                                        <br>No follow-up scheduled
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="deal-pill <?php echo (int) $deal['attention_score'] >= 50 ? 'deal-pill--status-lost' : 'deal-pill--approval'; ?>">
                                        <?php echo (int) $deal['attention_score']; ?> score
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($diagnostics['attention_queue'])) { ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No deals currently require elevated attention.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="deal-panel tw-mt-4">
                <h4 class="deal-panel__title">Recent Webhook Log</h4>
                <div class="table-responsive">
                    <table class="table table-hover deal-data-table">
                        <thead>
                        <tr>
                            <th>Attempted</th>
                            <th>Webhook</th>
                            <th>Event</th>
                            <th>Deal</th>
                            <th>Status</th>
                            <th>Latency</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($diagnostics['recent_logs'] as $log) { ?>
                            <tr>
                                <td><?php echo _dt($log['attempted_at']); ?></td>
                                <td><?php echo $log['webhook_name'] ?: '-'; ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $log['event_type'])); ?></td>
                                <td><?php echo $log['deal_title'] ?: ('#' . $log['deal_id']); ?></td>
                                <td>
                                    <span class="deal-pill <?php echo ($log['status'] ?? '') === 'success' ? 'deal-pill--status-won' : (($log['status'] ?? '') === 'failed' ? 'deal-pill--status-lost' : ''); ?>">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($log['processing_ms']) ? (int) $log['processing_ms'] . ' ms' : '-'; ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($diagnostics['recent_logs'])) { ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No webhook activity recorded yet.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

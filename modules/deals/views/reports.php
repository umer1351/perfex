<?php
$pipelineOptions = array_merge([['pipeline_id' => '', 'pipeline_name' => 'All Pipelines']], $pipelines);
$sourceOptions = array_merge([['source_id' => '', 'source_name' => 'All Sources']], $sources);
$staffOptions = [['staffid' => '', 'firstname' => 'All', 'lastname' => 'Owners']];
foreach ($staff as $member) {
    $staffOptions[] = is_array($member) ? $member : [
        'staffid' => $member->staffid,
        'firstname' => $member->firstname,
        'lastname' => $member->lastname,
    ];
}

$stageLabels = array_map(function ($row) {
    return $row['stage_name'] ?: 'Unassigned';
}, $stage_report);
$stagePipelineValues = array_map(function ($row) {
    return (float) $row['pipeline_value'];
}, $stage_report);
$stageWeightedValues = array_map(function ($row) {
    return (float) $row['weighted_value'];
}, $stage_report);

$monthlyLabels = array_map(function ($row) {
    return $row['month_label'];
}, $monthly_revenue_report);
$monthlyValues = array_map(function ($row) {
    return (float) $row['won_value'];
}, $monthly_revenue_report);
$monthlyDeals = array_map(function ($row) {
    return (int) $row['won_deals'];
}, $monthly_revenue_report);

$statusLabels = array_map(function ($row) {
    return ucfirst($row['status'] ?: 'unknown');
}, $status_report);
$statusCounts = array_map(function ($row) {
    return (int) $row['total_deals'];
}, $status_report);

$forecastLabels = array_map(function ($row) {
    return ucwords(str_replace('_', ' ', $row['forecast_category'] ?: 'unassigned'));
}, $forecast_report);
$forecastValues = array_map(function ($row) {
    return (float) $row['weighted_value'];
}, $forecast_report);

$healthLabels = array_map(function ($row) {
    return ucwords(str_replace('_', ' ', $row['health_status'] ?: 'unassigned'));
}, $health_report);
$healthCounts = array_map(function ($row) {
    return (int) $row['total_deals'];
}, $health_report);
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Deal Reports</h4>
                <p class="text-muted">Pipeline, forecast, follow-up, and revenue reporting from live deal activity.</p>
            </div>
        </div>

        <?php echo form_open(admin_url('deals/reports'), ['method' => 'get']); ?>
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">
                        <?= render_date_input('from_date', 'From', !empty($filters['from_date']) ? _d($filters['from_date']) : ''); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_date_input('to_date', 'To', !empty($filters['to_date']) ? _d($filters['to_date']) : ''); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_select('pipeline_id', $pipelineOptions, ['pipeline_id', 'pipeline_name'], 'Pipeline', $filters['pipeline_id']); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_select('owner_id', $staffOptions, ['staffid', ['firstname', 'lastname']], 'Owner', $filters['owner_id']); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_select('source_id', $sourceOptions, ['source_id', 'source_name'], 'Source', $filters['source_id']); ?>
                    </div>
                    <div class="col-md-2">
                        <?= render_select('status', [
                            ['id' => '', 'name' => 'All Statuses'],
                            ['id' => 'open', 'name' => 'Open'],
                            ['id' => 'won', 'name' => 'Won'],
                            ['id' => 'lost', 'name' => 'Lost'],
                        ], ['id', 'name'], 'Status', $filters['status']); ?>
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="<?= admin_url('deals/reports') ?>" class="btn btn-default">Reset</a>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>

        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= app_format_money($summary['total_pipeline'], get_base_currency()) ?></h3>
                        <p class="text-muted tw-mb-0">Open Pipeline</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= app_format_money($summary['weighted_pipeline'], get_base_currency()) ?></h3>
                        <p class="text-muted tw-mb-0">Weighted Forecast</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= $summary['win_rate'] ?>%</h3>
                        <p class="text-muted tw-mb-0">Win Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= app_format_money($summary['avg_deal_size'], get_base_currency()) ?></h3>
                        <p class="text-muted tw-mb-0">Average Deal Size</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= (int) $summary['open_deals'] ?></h3>
                        <p class="text-muted tw-mb-0">Active Deals</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= (float) $summary['avg_cycle_days'] ?></h3>
                        <p class="text-muted tw-mb-0">Avg Cycle Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= (int) $summary['overdue_followups'] ?></h3>
                        <p class="text-muted tw-mb-0">Overdue Follow-Ups</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="tw-mb-1"><?= (int) $summary['closing_next_30_days'] ?></h3>
                        <p class="text-muted tw-mb-0">Closing In 30 Days</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Pipeline Funnel</h4>
                        <div style="height: 320px;">
                            <canvas id="deal-stage-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Won Revenue Trend</h4>
                        <div style="height: 320px;">
                            <canvas id="deal-revenue-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Status Mix</h4>
                        <div style="height: 280px;">
                            <canvas id="deal-status-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Forecast Categories</h4>
                        <div style="height: 280px;">
                            <canvas id="deal-forecast-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Health Distribution</h4>
                        <div style="height: 280px;">
                            <canvas id="deal-health-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Source Performance</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Deals</th>
                                    <th>Pipeline</th>
                                    <th>Won Value</th>
                                    <th>Win Rate</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($source_report as $row) { ?>
                                    <tr>
                                        <td><?= $row['source_name'] ?: 'Unknown' ?></td>
                                        <td><?= (int) $row['total_deals'] ?></td>
                                        <td><?= app_format_money($row['pipeline_value'], get_base_currency()) ?></td>
                                        <td><?= app_format_money($row['won_value'], get_base_currency()) ?></td>
                                        <td><?= $row['win_rate'] !== null ? (float) $row['win_rate'] . '%' : '0%' ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Owner Performance</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Owner</th>
                                    <th>Deals</th>
                                    <th>Pipeline</th>
                                    <th>Weighted</th>
                                    <th>Won</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($owner_report as $row) { ?>
                                    <tr>
                                        <td><?= $row['owner_name'] ?: 'Unassigned' ?></td>
                                        <td><?= (int) $row['total_deals'] ?></td>
                                        <td><?= app_format_money($row['pipeline_value'], get_base_currency()) ?></td>
                                        <td><?= app_format_money($row['weighted_value'], get_base_currency()) ?></td>
                                        <td><?= app_format_money($row['won_value'], get_base_currency()) ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Stage Funnel Detail</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Stage</th>
                                    <th>Deals</th>
                                    <th>Pipeline</th>
                                    <th>Weighted</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($stage_report as $row) { ?>
                                    <tr>
                                        <td><?= $row['stage_name'] ?: 'Unassigned' ?></td>
                                        <td><?= (int) $row['total_deals'] ?></td>
                                        <td><?= app_format_money($row['pipeline_value'], get_base_currency()) ?></td>
                                        <td><?= app_format_money($row['weighted_value'], get_base_currency()) ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Follow-Up Performance</h4>
                        <div class="row text-center">
                            <div class="col-xs-6 col-md-6">
                                <h3 class="tw-mb-1"><?= (int) $followup_report['pending'] ?></h3>
                                <p class="text-muted">Pending</p>
                            </div>
                            <div class="col-xs-6 col-md-6">
                                <h3 class="tw-mb-1"><?= (int) $followup_report['completed'] ?></h3>
                                <p class="text-muted">Completed</p>
                            </div>
                            <div class="col-xs-6 col-md-6">
                                <h3 class="tw-mb-1"><?= (int) $followup_report['cancelled'] ?></h3>
                                <p class="text-muted">Cancelled</p>
                            </div>
                            <div class="col-xs-6 col-md-6">
                                <h3 class="tw-mb-1 text-danger"><?= (int) $followup_report['overdue'] ?></h3>
                                <p class="text-muted">Overdue</p>
                            </div>
                        </div>
                        <hr />
                        <h5 class="tw-mt-0">Forecast Breakdown</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Deals</th>
                                    <th>Weighted</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($forecast_report as $row) { ?>
                                    <tr>
                                        <td><?= ucwords(str_replace('_', ' ', $row['forecast_category'] ?: 'Unassigned')) ?></td>
                                        <td><?= (int) $row['total_deals'] ?></td>
                                        <td><?= app_format_money($row['weighted_value'], get_base_currency()) ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Longest-Aging Open Deals</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Deal</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Last Activity</th>
                                    <th>Aging Days</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($stage_aging_report as $row) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('deals/details/' . $row['id']) ?>"><?= $row['title'] ?></a></td>
                                        <td><?= $row['stage_name'] ?: 'Unassigned' ?></td>
                                        <td><?= ucfirst($row['status']) ?></td>
                                        <td><?= app_format_money($row['deal_value'], get_base_currency()) ?></td>
                                        <td><?= !empty($row['last_activity_at']) ? _dt($row['last_activity_at']) : _dt($row['created_at']) ?></td>
                                        <td><?= (int) $row['aging_days'] ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($stage_aging_report)) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No open deals match the current filters.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    (function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        var currencySymbol = <?= json_encode($currency_symbol) ?>;
        var chartFontColor = '#6b7280';
        var gridColor = 'rgba(107, 114, 128, 0.15)';

        function formatCurrency(value) {
            return currencySymbol + Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function buildBarChart(canvasId, config) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) {
                return;
            }

            new Chart(canvas, config);
        }

        buildBarChart('deal-stage-chart', {
            type: 'bar',
            data: {
                labels: <?= json_encode($stageLabels) ?>,
                datasets: [{
                    label: 'Pipeline Value',
                    backgroundColor: '#0f766e',
                    borderColor: '#0f766e',
                    data: <?= json_encode($stagePipelineValues) ?>
                }, {
                    label: 'Weighted Value',
                    backgroundColor: '#94a3b8',
                    borderColor: '#94a3b8',
                    data: <?= json_encode($stageWeightedValues) ?>
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: chartFontColor
                        },
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: chartFontColor,
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        },
                        gridLines: {
                            color: gridColor
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + formatCurrency(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        buildBarChart('deal-revenue-chart', {
            type: 'line',
            data: {
                labels: <?= json_encode($monthlyLabels) ?>,
                datasets: [{
                    label: 'Won Revenue',
                    data: <?= json_encode($monthlyValues) ?>,
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29, 78, 216, 0.12)',
                    fill: true,
                    lineTension: 0.25
                }, {
                    label: 'Won Deals',
                    data: <?= json_encode($monthlyDeals) ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0)',
                    fill: false,
                    lineTension: 0.2,
                    yAxisID: 'deal-count-axis'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: chartFontColor
                        },
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        id: 'value-axis',
                        position: 'left',
                        ticks: {
                            beginAtZero: true,
                            fontColor: chartFontColor,
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        },
                        gridLines: {
                            color: gridColor
                        }
                    }, {
                        id: 'deal-count-axis',
                        position: 'right',
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#f59e0b',
                            precision: 0
                        },
                        gridLines: {
                            drawOnChartArea: false
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            if (tooltipItem.datasetIndex === 0) {
                                return data.datasets[tooltipItem.datasetIndex].label + ': ' + formatCurrency(tooltipItem.yLabel);
                            }

                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel;
                        }
                    }
                }
            }
        });

        buildBarChart('deal-status-chart', {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($statusCounts) ?>,
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626', '#7c3aed', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });

        buildBarChart('deal-forecast-chart', {
            type: 'bar',
            data: {
                labels: <?= json_encode($forecastLabels) ?>,
                datasets: [{
                    label: 'Weighted Value',
                    data: <?= json_encode($forecastValues) ?>,
                    backgroundColor: ['#0ea5e9', '#14b8a6', '#f97316', '#8b5cf6', '#64748b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: chartFontColor
                        },
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: chartFontColor,
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        },
                        gridLines: {
                            color: gridColor
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem) {
                            return 'Weighted Value: ' + formatCurrency(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        buildBarChart('deal-health-chart', {
            type: 'pie',
            data: {
                labels: <?= json_encode($healthLabels) ?>,
                datasets: [{
                    data: <?= json_encode($healthCounts) ?>,
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444', '#64748b', '#06b6d4']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
    })();
</script>

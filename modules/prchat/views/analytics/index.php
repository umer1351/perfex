<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head(); 
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Filters: Date range & Tag -->
                        <div class="row pull-right">
                            <div class="col-md-12">
                                <div class="form-inline tw-flex tw-flex-wrap tw-gap-3 tw-items-center">
                                    <div class="form-group">
                                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="<?= _l('chatbot_analytics_filters_help') ?>"></i>
                                        <label class="tw-mr-2"><?= _l('chatbot_filter_by_date') ?></label>
                                        <select id="date-range" class="form-control" style="min-width: 160px;">
                                            <option value="all" <?= (isset($current_date_range) && $current_date_range === 'all') ? 'selected' : '' ?>><?= _l('chatbot_all_time') ?></option>
                                            <option value="7" <?= (isset($current_date_range) && $current_date_range === '7') ? 'selected' : '' ?>><?= _l('chatbot_last_7_days') ?></option>
                                            <option value="30" <?= (isset($current_date_range) && $current_date_range === '30') ? 'selected' : '' ?>><?= _l('chatbot_last_30_days') ?></option>
                                            <option value="90" <?= (isset($current_date_range) && $current_date_range === '90') ? 'selected' : '' ?>><?= _l('chatbot_last_3_months') ?></option>
                                            <option value="180" <?= (isset($current_date_range) && $current_date_range === '180') ? 'selected' : '' ?>><?= _l('chatbot_last_6_months') ?></option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="tw-mr-2"><?= _l('chatbot_tags_tab') ?></label>
                                        <select id="tag-filter" class="form-control" style="min-width: 180px;">
                                            <option value="all" <?= (empty($current_tag_id) || $current_tag_id === 'all') ? 'selected' : '' ?>><?= _l('chatbot_filter_tag_all') ?></option>
                                            <?php foreach (isset($chatbot_tags) ? $chatbot_tags : [] as $t): ?>
                                                <option value="<?= (int)$t['id'] ?>" <?= (isset($current_tag_id) && (string)$current_tag_id === (string)$t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="analytics-apply-filters" class="btn btn-primary"><?= _l('chatbot_apply_filters') ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <!-- Overview Cards -->
                        <div class="row tw-mb-5">
                            <div class="col-md-3 col-sm-6">
                                <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#e2e8f0; background:#f8fafc;">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb;">
                                            <i class="fa fa-comments"></i>
                                        </div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $total_conversations ?></div>
                                            <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_total_conversations') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#d1fae5; background:#f0fdf4;">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#bbf7d0; color:#16a34a;">
                                            <i class="fa fa-star"></i>
                                        </div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $rated_conversations ?></div>
                                            <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_rated_conversations') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#e9d5ff; background:#faf5ff;">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#e9d5ff; color:#7c3aed;">
                                            <i class="fa fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $average_rating > 0 ? number_format($average_rating, 1) . ' ★' : 'N/A' ?></div>
                                            <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_average_rating') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#fde68a; background:#fffbeb;">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#fde68a; color:#d97706;">
                                            <i class="fa fa-percentage"></i>
                                        </div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= number_format($rating_percentage, 1) ?>%</div>
                                            <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_rating_rate') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Trends Chart -->
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 style="margin-top: 0;"><?= _l('chatbot_performance_trends') ?></h5>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <select id="chart-period-filter" class="form-control" style="width: 100%;">
                                                        <?php
                                                        $chart_days = isset($chart_days) ? (int)$chart_days : 30;
                                                        foreach ([7 => 'chatbot_last_7_days', 30 => 'chatbot_last_30_days', 90 => 'chatbot_last_3_months', 180 => 'chatbot_last_6_months'] as $val => $lang_key):
                                                        ?>
                                                        <option value="<?= $val ?>" <?= $chart_days === $val ? 'selected' : '' ?>><?= _l($lang_key) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div style="position: relative; height: 300px; margin-top: 20px;">
                                            <canvas id="trendsChart" width="400" height="300"></canvas>
                                        </div>
                                        
                                        <?php if (array_sum($chart_counts) == 0): ?>
                                            <div class="text-center" style="margin-top: 30px; color: #666;">
                                                <i class="fa fa-line-chart" style="font-size: 24px; margin-bottom: 10px;"></i>
                                                <p><?= _l('chatbot_no_trend_data') ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Staff, Quick Stats, and Tags (3 in a row) -->
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-4">
                                <div class="panel_s" style="height: 450px;">
                                    <div class="panel-body">
                                        <h5 style="margin-top: 0;"><?= _l('chatbot_top_staff_ratings') ?></h5>
                                        
                                        <?php if (!empty($top_staff)): ?>
                                            <div style="margin-top: 15px;">
                                                <?php foreach ($top_staff as $index => $staff): 
                                                    $rank = $index + 1;
                                                    $stars = str_repeat('★', floor($staff->avg_rating)) . str_repeat('☆', 5 - floor($staff->avg_rating));
                                                    $badge_class = $rank == 1 ? 'warning' : ($rank <= 3 ? 'success' : 'info');
                                                ?>
                                                <div style="display: flex; align-items: center; margin-bottom: 12px; padding: 8px; background: #f9f9f9; border-radius: 4px;">
                                                    <div style="width: 30px;">
                                                        <span class="label label-<?= $badge_class ?>" style="font-size: 11px;">#<?= $rank ?></span>
                                                    </div>
                                                    <div style="flex: 1; margin-left: 10px;">
                                                        <div style="font-weight: 600; color: #374151;"><?= htmlspecialchars($staff->firstname . ' ' . $staff->lastname) ?></div>
                                                        <div style="font-size: 12px; color: #666;">
                                                            <?= $staff->rated_conversations ?> ratings • <?= number_format($staff->avg_rating, 1) ?> avg
                                                        </div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <div style="color: #ff9800; font-size: 14px;"><?= $stars ?></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center" style="margin-top: 30px; color: #666;">
                                                <i class="fa fa-star" style="font-size: 24px; margin-bottom: 10px;"></i>
                                                <p><?= _l('chatbot_no_staff_ratings_yet') ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel_s" style="height: 450px;">
                                    <div class="panel-body">
                                        <h5 style="margin-top: 0;"><?= _l('chatbot_quick_stats') ?></h5>
                                        
                                        <div style="margin-top: 15px;">
                                            <!-- Today vs Yesterday -->
                                            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 15px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <div style="font-size: 24px; font-weight: 600; color: #2563eb;"><?= $today_ratings ?></div>
                                                        <div style="font-size: 12px; color: #666;"><?= _l('chatbot_ratings_today') ?></div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <?php 
                                                        $change = $today_ratings - $yesterday_ratings;
                                                        $change_color = $change >= 0 ? '#10b981' : '#ef4444';
                                                        $change_icon = $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                                        ?>
                                                        <div style="color: <?= $change_color ?>; font-size: 12px;">
                                                            <i class="fa <?= $change_icon ?>"></i> <?= abs($change) ?>
                                                        </div>
                                                        <div style="font-size: 11px; color: #666;"><?= _l('chatbot_vs_yesterday') ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Weekly Average Change -->
                                            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 15px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <div style="font-size: 24px; font-weight: 600; color: #7c3aed;"><?= $this_week_avg ? number_format($this_week_avg, 1) : 'N/A' ?></div>
                                                        <div style="font-size: 12px; color: #666;"><?= _l('chatbot_this_week_avg') ?></div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <?php if ($this_week_avg && $last_week_avg): 
                                                            $rating_change = $this_week_avg - $last_week_avg;
                                                        ?>
                                                        <div style="color: <?= $rating_change >= 0 ? '#10b981' : '#ef4444' ?>; font-size: 12px;">
                                                            <i class="fa <?= $rating_change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i> <?= number_format(abs($rating_change), 1) ?>
                                                        </div>
                                                        <div style="font-size: 11px; color: #666;"><?= _l('chatbot_vs_last_week') ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Peak Performance Day -->
                                            <?php if ($best_day): ?>
                                            <div style="padding: 15px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 6px; margin-bottom: 15px;">
                                                <div style="display: flex; align-items: center;">
                                                    <div style="margin-right: 10px;">
                                                        <i class="fa fa-trophy" style="color: #d97706; font-size: 20px;"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600; color: #92400e;"><?= _l('chatbot_best_day') ?></div>
                                                        <div style="font-size: 12px; color: #a16207;">
                                                            <?= _d($best_day->date) ?> - <?= number_format($best_day->avg_rating, 1) ?> ★ (<?= $best_day->count ?> ratings)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Monthly Escalations -->
                                            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <div style="font-size: 24px; font-weight: 600; color: #dc3545;"><?= $month_escalations ?></div>
                                                        <div style="font-size: 12px; color: #666;"><?= _l('chatbot_escalations_this_month') ?></div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <i class="fa fa-arrow-up" style="color: #dc3545; font-size: 16px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel_s" style="height: 450px;">
                                    <div class="panel-body">
                                        <h5 style="margin-top: 0;"><?= _l('chatbot_conversations_by_tag') ?></h5>
                                        <?php $tag_breakdown = isset($tag_breakdown) ? $tag_breakdown : []; ?>
                                        <?php if (!empty($tag_breakdown)): ?>
                                        <div class="table-responsive" style="margin-top: 15px;">
                                            <table class="table table-striped table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th><?= _l('chatbot_tag_name') ?></th>
                                                        <th><?= _l('chatbot_total_conversations') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tag_breakdown as $row): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="label" style="background:<?= htmlspecialchars($row['color']) ?>; color: #fff; padding: 4px 10px; border-radius: 4px;"><?= htmlspecialchars($row['name']) ?></span>
                                                        </td>
                                                        <td><?= (int) $row['count'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center" style="margin-top: 30px; color: #666;">
                                            <i class="fa fa-tags" style="font-size: 24px; margin-bottom: 10px;"></i>
                                            <p><?= _l('chatbot_no_tags') ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Performance Table -->
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 style="margin-top: 0;"><?= _l('chatbot_staff_performance') ?></h5>
                                            </div>
                                            <div class="pull-right col-md-2">
                                                <div class="form-group">
                                                    <select id="staff-filter" class="form-control">
                                                        <option value="all"><?= _l('chatbot_all_staff') ?></option>
                                                        <option value="high-rating"><?= _l('chatbot_high_rating') ?></option>
                                                        <option value="low-rating"><?= _l('chatbot_low_rating') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped dt-table" id="staff-performance-table" data-default-length="10">
                                                <thead>
                                                    <tr>
                                                        <th><?= _l('chatbot_staff_member') ?></th>
                                                        <th><?= _l('chatbot_total_conversations') ?></th>
                                                        <th><?= _l('chatbot_rated_conversations') ?></th>
                                                        <th><?= _l('chatbot_average_rating') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($staff_performance as $staff): 
                                                        $stars = $staff->avg_rating ? str_repeat('★', floor($staff->avg_rating)) . str_repeat('☆', 5 - floor($staff->avg_rating)) : '-';
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?= admin_url('staff/member/' . $staff->staffid) ?>" target="_blank" style="text-decoration: none;">
                                                                    <strong style="color: #3b82f6;"><?= htmlspecialchars($staff->firstname . ' ' . $staff->lastname) ?></strong>
                                                                    <i class="fa fa-external-link" style="font-size: 10px; margin-left: 5px; color: #9ca3af;"></i>
                                                                </a>
                                                            </td>
                                                            <td><?= $staff->total_conversations ?></td>
                                                            <td><?= $staff->rated_conversations ?></td>
                                                            <td>
                                                                <?php if ($staff->avg_rating): ?>
                                                                    <span style="color: #ff9800; font-size: 14px;"><?= $stars ?></span>
                                                                    <small class="text-muted">(<?= number_format($staff->avg_rating, 1) ?>)</small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conversations Table -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 style="margin-top: 0;"><?= _l('chatbot_conversation_details') ?></h5>
                                            </div>
                                            <div class="pull-right col-md-2">
                                                <div class="form-group">
                                                    <select id="rating-filter" class="form-control">
                                                        <option value="all"><?= _l('chatbot_all_ratings') ?></option>
                                                        <option value="5">5 ★</option>
                                                        <option value="4">4 ★</option>
                                                        <option value="3">3 ★</option>
                                                        <option value="2">2 ★</option>
                                                        <option value="1">1 ★</option>
                                                        <option value="unrated"><?= _l('chatbot_unrated') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped dt-table" id="conversations-table" data-default-length="10">
                                                <thead>
                                                    <tr>
                                                        <th><?= _l('chatbot_conversation_id') ?></th>
                                                        <th><?= _l('chatbot_visitor') ?></th>
                                                        <th><?= _l('chatbot_staff_member') ?></th>
                                                        <th><?= _l('chatbot_rating') ?></th>
                                                        <th><?= _l('chatbot_comment') ?></th>
                                                        <th><?= _l('chatbot_date') ?></th>
                                                        <th><?= _l('chatbot_status') ?></th>
                                                        <th><?= _l('chatbot_tags_tab') ?></th>
                                                        <th><?= _l('chatbot_actions') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($conversations as $conv):
                                                        $cid = $conv['id'];
                                                        $visitorName = $conv['visitor_name'] ?: 'Visitor #' . $cid;
                                                        $score = $conv['csat_score'] ? (int) $conv['csat_score'] : 0;
                                                        $stars = $score ? str_repeat('★', $score) . str_repeat('☆', 5 - $score) : '-';
                                                        $staffName = $conv['staff_name'] ?: '-';

                                                        $statusClass = '';
                                                        $statusLabel = '';
                                                        switch ($conv['status']) {
                                                            case 'closed':
                                                                $statusClass = 'label-default';
                                                                $statusLabel = _l('chatbot_closed');
                                                                break;
                                                            case 'human_handling':
                                                                $statusClass = 'label-success';
                                                                $statusLabel = _l('chatbot_active');
                                                                break;
                                                            default:
                                                                $statusClass = 'label-info';
                                                                $statusLabel = _l('chatbot_ai_handling');
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td>#<?= $cid ?></td>
                                                            <td>
                                                                <strong style="color: #374151;"><?= htmlspecialchars($visitorName) ?></strong>
                                                            </td>
                                                            <td>
                                                                <?php if ($staffName !== '-'): ?>
                                                                    <span style="color: #374151; font-weight: 500;"><?= htmlspecialchars($staffName) ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($score): ?>
                                                                    <span style="color: #ff9800; font-size: 16px;"><?= $stars ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($conv['csat_comment'])): ?>
                                                                    <i class="fa fa-comment text-info" title="<?= htmlspecialchars($conv['csat_comment']) ?>" data-toggle="tooltip" style="cursor: pointer;"></i>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?= $conv['created_at'] ?>
                                                            </td>
                                                            <td>
                                                                <span class="label <?= $statusClass ?>"><?= $statusLabel ?></span>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $tags_display = isset($conv['tags_display']) ? $conv['tags_display'] : [];
                                                                if (!empty($tags_display)): ?>
                                                                    <?= htmlspecialchars(implode(', ', $tags_display)) ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a data-toggle="tooltip" title="<?= _l('chatbot_view_conversation') ?>" href="<?= admin_url('prchat/Chatbot_Admin/live_chat?conv=' . $cid) ?>" 
                                                                   class="btn btn-default btn-xs">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Chart.js trends chart
    <?php if (array_sum($chart_counts) > 0): ?>
    const ctx = document.getElementById('trendsChart').getContext('2d');
    const trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_dates) ?>,
            datasets: [{
                label: '<?= _l('chatbot_average_rating') ?>',
                data: <?= json_encode($chart_ratings) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value + ' ★';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 8
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: '#3b82f6'
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Chart period filter handler
    $('#chart-period-filter').on('change', function() {
        var period = $(this).val();
        
        // Show loading state
        $('#trendsChart').parent().append('<div class="chart-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10; background: rgba(255,255,255,0.8); padding: 10px; border-radius: 4px;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        
        var tagId = $('#tag-filter').length ? $('#tag-filter').val() : 'all';
        $.ajax({
            url: '<?= admin_url('prchat/Chatbot_Admin/get_chart_data') ?>',
            type: 'POST',
            data: { period: period, tag_id: tagId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update chart data
                    trendsChart.data.labels = response.data.dates;
                    trendsChart.data.datasets[0].data = response.data.ratings;
                    trendsChart.update();
                }
                $('.chart-loading').remove();
            },
            error: function() {
                $('.chart-loading').remove();
                alert('Error loading chart data');
            }
        });
    });
    <?php endif; ?>
    
    // Wait for DataTables to be automatically initialized by Perfex, then modify settings
    setTimeout(function() {
        var staffTable, conversationsTable;
        
        // Get existing DataTables instances and modify their settings
        if ($.fn.DataTable.isDataTable('#staff-performance-table')) {
            staffTable = $('#staff-performance-table').DataTable();
            staffTable.page.len(10).draw(); // Set page length to 10
        }
        
        if ($.fn.DataTable.isDataTable('#conversations-table')) {
            conversationsTable = $('#conversations-table').DataTable();
            conversationsTable.page.len(10).draw(); // Set page length to 10
        }
        
        // Set up filters after tables are ready
        setupFilters(staffTable, conversationsTable);
    }, 500);
    
    function setupFilters(staffTable, conversationsTable) {

    var analyticsBaseUrl = '<?= admin_url('prchat/Chatbot_Admin/analytics') ?>';
    function applyAnalyticsFilters() {
        var dateRange = $('#date-range').val() || 'all';
        var tagId = $('#tag-filter').val() || 'all';
        window.location.href = analyticsBaseUrl + '?date_range=' + encodeURIComponent(dateRange) + '&tag_id=' + encodeURIComponent(tagId);
    }
    $('#analytics-apply-filters').on('click', applyAnalyticsFilters);

        // Filter conversations table by rating
        $('#rating-filter').on('change', function() {
            var filterValue = $(this).val();
            
            if (!conversationsTable) return;
            
            if (filterValue === 'all') {
                conversationsTable.column(3).search('').draw(); // Clear rating column filter
            } else if (filterValue === 'unrated') {
                conversationsTable.column(3).search('^-$', true, false).draw(); // Search for exact '-' (no rating)
            } else {
                // Search for specific star rating
                var rating = parseInt(filterValue);
                var searchPattern = '';
                
                // Build the exact pattern: ★★★☆☆ for 3 stars, ★★★★★ for 5 stars, etc.
                for (var i = 1; i <= 5; i++) {
                    if (i <= rating) {
                        searchPattern += '★';
                    } else {
                        searchPattern += '☆';
                    }
                }
                
                // Use exact match
                conversationsTable.column(3).search('^' + searchPattern + '$', true, false).draw();
            }
        });

        // Filter staff table by rating performance
        $('#staff-filter').on('change', function() {
            var filterValue = $(this).val();
            
            if (!staffTable) return;
            
            if (filterValue === 'all') {
                staffTable.column(3).search('').draw(); // Clear average rating column filter
            } else if (filterValue === 'high-rating') {
                // Filter for 4+ star ratings (★★★★ or ★★★★★)
                staffTable.column(3).search('★★★★', false, false).draw();
            } else if (filterValue === 'low-rating') {
                // Filter for 1-2 star ratings (★ or ★★)
                staffTable.column(3).search('^(★|★★)(?!★)', true, false).draw();
            }
        });
    }


});
</script>

<style>
    #staff-performance-table_filter {
    margin-top:1px !important;
}
</style>

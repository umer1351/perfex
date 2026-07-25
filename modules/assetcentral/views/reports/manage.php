<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h2>
                    <?php echo isset($type_data) ? $type_data->category_name : ''; ?>
                </h2>
                <p><?php echo isset($type_data) ? $type_data->category_description : ''; ?></p>
            </div>

            <div class="col-md-12 mtop20 mbot20">
                <div class="widget relative" id="widget-top_stats" data-name="Quick Statistics">
                    <div class="widget-dragger ui-sortable-handle"></div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6 col-sm-6 col-lg-3 tw-mb-2 sm:tw-mb-0">
                            <div class="top_stats_wrapper">
                                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor"
                                             class="tw-w-6 tw-h-6 tw-mr-3 rtl:tw-ml-3 tw-text-neutral-600">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"></path>
                                        </svg>
                                        <?php echo _l('assetcentral_asset_base_value_chart'); ?>
                                    </div>
                                </div>
                                <div style="color: cornflowerblue"
                                     class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mt-4">
                                    <?php echo $asset_summary_data['base_assets_value']; ?>
                                </div>
                                <div class="progress tw-mb-0 tw-mt-5 progress-bar-mini">
                                    <div class="progress-bar no-percent-text not-dynamic"
                                         style="background: rgb(37, 99, 235); width: 100%;" role="progressbar"
                                         aria-valuenow="100.00" aria-valuemin="0" aria-valuemax="100"
                                         data-percent="100.00">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6 col-sm-6 col-lg-3 tw-mb-2 sm:tw-mb-0">
                            <div class="top_stats_wrapper">
                                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor"
                                             class="tw-w-6 tw-h-6 tw-mr-3 rtl:tw-ml-3 tw-text-neutral-600">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"></path>
                                        </svg>
                                        <?php echo _l('assetcentral_asset_appreciation_chart'); ?>
                                    </div>
                                </div>
                                <div style="color: seagreen" class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mt-4">
                                    <?php echo $asset_summary_data['total_appreciation']; ?>
                                </div>
                                <div class="progress tw-mb-0 tw-mt-4 progress-bar-mini">
                                    <div class="progress-bar progress-bar-success no-percent-text not-dynamic"
                                         role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                         style="width: 100%;" data-percent="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6 col-sm-6 col-lg-3 tw-mb-2 sm:tw-mb-0">
                            <div class="top_stats_wrapper">
                                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-font-medium tw-inline-flex tw-items-center text-neutral-500">
                                        <svg fill="#475569" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                             xmlns:xlink="http://www.w3.org/1999/xlink"
                                             class="tw-w-6 tw-h-6 tw-mr-3 rtl:tw-ml-3 tw-text-neutral-600"
                                             viewBox="0 0 973.8 973.8" xml:space="preserve" stroke="#475569"><g
                                                    id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                               stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g>
                                                    <path d="M923.9,758.25H50c-27.6,0-50,22.4-50,50c0,27.602,22.4,50,50,50h873.8c27.601,0,50-22.398,50-50 C973.8,780.65,951.5,758.25,923.9,758.25z"></path>
                                                    <path d="M911.101,726.25H712.7c-31.8,0-57.5-25.699-57.5-57.5c0-31.799,25.7-57.5,57.5-57.5h72.4L634.8,484.45 c-2.3-2-5.3-3.1-8.399-3.1h-239.8c-26.9,0-53-9.601-73.4-27.2L20.7,203.55c-21-18-23.4-49.5-5.4-70.5c9.9-11.5,23.9-17.5,38-17.5 c11.5,0,23.1,4,32.5,12l292.4,250.7c2.3,2,5.3,3.1,8.4,3.1h239.8c26.899,0,53,9.6,73.399,27.2l153.7,129.6v-68.799 c0-31.801,25.7-57.5,57.5-57.5s57.5,25.699,57.5,57.5V668.65C968.601,700.55,942.8,726.25,911.101,726.25z"></path>
                                                </g>
                                            </g></svg>
                                        <?php echo _l('assetcentral_asset_depreciation_chart'); ?>
                                    </div>
                                </div>
                                <div style="color: indianred" class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mt-4">
                                    <?php echo $asset_summary_data['total_depreciation']; ?>
                                </div>
                                <div class="progress tw-mb-0 tw-mt-4 progress-bar-mini">
                                    <div class="progress-bar progress-bar-danger no-percent-text not-dynamic"
                                         role="progressbar" aria-valuenow="100.00" aria-valuemin="0" aria-valuemax="100"
                                         style="width: 100%;" data-percent="100.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6 col-sm-6 col-lg-3">
                            <div class="top_stats_wrapper">
                                <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor"
                                             class="tw-w-6 tw-h-6 tw-mr-3 rtl:tw-ml-3 tw-text-neutral-600">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12"></path>
                                        </svg>
                                        <?php echo _l('assetcentral_asset_final_value_chart'); ?>
                                    </div>
                                </div>
                                <div class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mt-4">
                                    <?php echo $asset_summary_data['final_asset_value']; ?>
                                </div>
                                <div class="progress tw-mb-0 tw-mt-4 progress-bar-mini">
                                    <div class="progress-bar progress-bar-default no-percent-text not-dynamic"
                                         role="progressbar" aria-valuenow="96.88" aria-valuemin="0" aria-valuemax="100"
                                         style="width: 96.88%;" data-percent="96.88">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">
                                <span class="tw-text-neutral-700">
                                    <?php echo _l('assetcentral_asset_locations_chart'); ?>
                                </span>
                            </p>
                            <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">
                            <div style="height: 320px;width: 100%" id="loadAssetsLocationByMapChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <canvas id="assetStatusPieChart" width="350" height="350"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <canvas id="assetsBoughtByYearLineChart" width="350" height="350"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <canvas id="assetLocationBarChart" width="350" height="350"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <canvas id="loadAssetsAddedByYearLineChart" width="350" height="350"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <canvas id="loadAssetsAssignedByBarChart" width="350" height="350"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    'use strict';

    $(document).ready(function () {
        loadAssetStatusPieChart();
        loadAssetLocationBarChart();
        loadAssetsBoughtByYearLineChart();
        loadAssetsAddedByYearLineChart();
        loadAssetsAssignedByBarChart();
        loadAssetsLocationByMapChart();
    });

    function loadAssetStatusPieChart() {
        'use strict';

        var ctx = document.getElementById('assetStatusPieChart').getContext('2d');

        var labels = <?php echo json_encode(array_column($asset_status_data, 'status_name')); ?>;
        var data = <?php echo json_encode(array_column($asset_status_data, 'count')); ?>;

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels.map(status => `${status}`),
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: '<?php echo _l("assetcentral_asset_status_distribution_chart"); ?>'
                }
            }
        });
    }

    function loadAssetLocationBarChart() {
        'use strict';

        var ctx = document.getElementById('assetLocationBarChart').getContext('2d');

        var labels = <?php echo json_encode(array_column($asset_location_data, 'location_name')); ?>;
        var data = <?php echo json_encode(array_column($asset_location_data, 'count')); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php echo _l("assetcentral_asset_by_location_chart"); ?>',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            },
                            stepSize: 1
                        }
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo _l("assetcentral_asset_by_location_chart"); ?>'
                }
            }
        });
    }

    function loadAssetsBoughtByYearLineChart() {
        'use strict';

        var ctx = document.getElementById('assetsBoughtByYearLineChart').getContext('2d');
        var data = <?php echo json_encode($asset_year_data); ?>;

        var months = [];
        var counts = [];

        for (var i = 1; i <= 12; i++) {
            months.push(getMonthName(i));
            counts.push(0);
        }

        data.forEach(function (item) {
            counts[item.month - 1] = item.count;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: '<?php echo _l("assetcentral_asset_bought_chart"); ?>',
                    data: counts,
                    borderColor: 'rgb(100,75,192)',
                    backgroundColor: 'rgba(104,75,192,0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo _l("assetcentral_asset_bought_this_year_chart"); ?>'
                }
            }
        });
    }

    function loadAssetsAddedByYearLineChart() {
        'use strict';

        var ctx = document.getElementById('loadAssetsAddedByYearLineChart').getContext('2d');

        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var assetsAdded = <?= json_encode($assets_added_year_chart); ?>;
        var assetCounts = new Array(12).fill(0);

        assetsAdded.forEach(function (item) {
            assetCounts[item.month - 1] = item.asset_count;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Assets Added',
                    data: assetCounts,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Assets'
                        },
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo _l("assetcentral_asset_count_year_chart"); ?>'
                }
            }
        });
    }

    function loadAssetsAssignedByBarChart() {
        'use strict';

        var ctx = document.getElementById('loadAssetsAssignedByBarChart').getContext('2d');
        var data = {
            labels: ['<?php echo _l('staff'); ?>', '<?php echo _l('project'); ?>', '<?php echo ucfirst(_l('customer')); ?>'],
            datasets: [{
                label: '<?php echo _l('assetcentral_asset_assignments_chart'); ?>',
                data: [
                    <?= isset($asset_assigned_chart_data[0]['total']) ? $asset_assigned_chart_data[0]['total'] : 0 ?>,
                    <?= isset($asset_assigned_chart_data[1]['total']) ? $asset_assigned_chart_data[1]['total'] : 0 ?>,
                    <?= isset($asset_assigned_chart_data[2]['total']) ? $asset_assigned_chart_data[2]['total'] : 0 ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        };
        var options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            title: {
                display: true,
                text: '<?php echo _l("assetcentral_asset_assigned_to_chart"); ?>'
            }
        };
        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
    }

    function loadAssetsLocationByMapChart() {
        'use strict';

        var map = L.map('loadAssetsLocationByMapChart').setView([20.0, 0.0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var assetLocations = <?= json_encode($asset_location_map_chart); ?>;

        assetLocations.forEach(function (location) {
            if (location.lat && location.lng) {
                L.marker([location.lat, location.lng]).addTo(map)
                    .bindPopup('<strong>' + location.location_name + '</strong><br><?php echo _l('assetcentral'); ?>: ' + location.asset_count);
            }
        });
    }

    function getMonthName(month) {
        'use strict';

        var monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        return monthNames[month - 1];
    }
</script>


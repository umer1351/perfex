<script>    
(function($) {
    "use strict";
    rec_chart_by_status('package_by_status',<?php echo html_entity_decode($package_by_status); ?>, <?php echo json_encode(_l('package_by_status')); ?>);
    line_chart_init('package_sales_graph', <?php echo html_entity_decode($package_sales_graph); ?>, <?php echo json_encode(_l('package_sales_graph')); ?>);
    rec_chart_by_status('shipping_by_status',<?php echo html_entity_decode($shipping_by_status); ?>, <?php echo json_encode(_l('shipping_by_status')); ?>);
    column_chart_init('shipping_sales_graph', <?php echo html_entity_decode($shipping_sales_graph); ?>, <?php echo json_encode(_l('shipping_sales_graph')); ?>);

    rec_chart_by_status('consolidated_by_status',<?php echo html_entity_decode($consolidated_by_status); ?>, <?php echo json_encode(_l('consolidated_by_status')); ?>);
    line_chart_init('consolidated_sales_graph', <?php echo html_entity_decode($consolidated_sales_graph); ?>, <?php echo json_encode(_l('consolidated_sales_graph')); ?>);
    function rec_chart_by_status(id, value, title_c){
     
        "use strict";
        Highcharts.setOptions({
          chart: {
              style: {
                  fontFamily: 'inherit !important',
                  fontWeight:'normal',
                  fill: 'black'
              }
          },
          colors: [ '#119EFA','#ef370dc7','#15f34f','#791db2d1', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B']
         });

        Highcharts.chart(id, {
            chart: {
                backgroundcolor: '#fcfcfc8a',
                type: 'variablepie'
            },
            accessibility: {
                description: null
            },
            title: {
                text: title_c
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">'+<?php echo json_encode(_l('invoice_table_quantity_heading')); ?>+'</span>: <b>{point.y}</b> <br/> <span>'+<?php echo json_encode(_l('ratio')); ?>+'</span>: <b>{point.percentage:.0f}%</b><br/>',
                shared: true
            },
             plotOptions: {
                variablepie: {
                    dataLabels: {
                        enabled: false,
                        },
                    showInLegend: true        
                }
            },
            series: [{
                minPointSize: 10,
                innerSize: '20%',
                zMin: 0,
                name: <?php echo json_encode(_l('invoice_table_quantity_heading')); ?>,
                data: value,
                point:{
                      events:{
                          click: function (event) {
                             if(this.statusLink !== undefined)
                             { 
                               window.location.href = this.statusLink;

                             }
                          }
                      }
                  }
            }]
        });
    }



    function line_chart_init(id, value, title_c){
        "use strict";
        Highcharts.setOptions({
          chart: {
              style: {
                  fontFamily: 'inherit !important',
                  fontWeight:'normal',
                  fill: 'black'
              }
          },
          colors: [ '#119EFA','#ef370dc7','#15f34f','#791db2d1', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B']
        });


        Highcharts.chart(id, {
         chart: {
             type: 'line'
         },
         title: {
             text: title_c
         },
         subtitle: {
             text: ''
         },
         credits: {
            enabled: false
          },
         xAxis: {
             categories: ['<?php echo _l('month_1') ?>',
                '<?php echo _l('month_2') ?>',
                '<?php echo _l('month_3') ?>',
                '<?php echo _l('month_4') ?>',
                '<?php echo _l('month_5') ?>',
                '<?php echo _l('month_6') ?>',
                '<?php echo _l('month_7') ?>',
                '<?php echo _l('month_8') ?>',
                '<?php echo _l('month_9') ?>',
                '<?php echo _l('month_10') ?>',
                '<?php echo _l('month_11') ?>',
                '<?php echo _l('month_12') ?>'],
             crosshair: true,
         },
         yAxis: {
             min: 0,
             title: {
              text: value.name
             }
         },
         tooltip: {
             headerFormat: '<span >{point.key}</span><table>',
             pointFormat: '<tr>' +
                 '<td><b>{point.y:.0f}'+value.unit +' {series.name}</b></td></tr>',
             footerFormat: '</table>',
             shared: true,
             useHTML: true
         },
         plotOptions: {
             column: {
                 pointPadding: 0.2,
                 borderWidth: 0
             }
         },

         series: [
            {
                name: value.name,
                data: value.data
            },
      
    ]
     });

    }


    function column_chart_init(id, value, title_c){
        "use strict";
        Highcharts.setOptions({
          chart: {
              style: {
                  fontFamily: 'inherit !important',
                  fontWeight:'normal',
                  fill: 'black'
              }
          },
          colors: [ '#ef370dc7','#15f34f','#791db2d1', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B']
        });


        Highcharts.chart(id, {
         chart: {
             type: 'column'
         },
         title: {
             text: title_c
         },
         subtitle: {
             text: ''
         },
         credits: {
            enabled: false
          },
         xAxis: {
             categories: ['<?php echo _l('month_1') ?>',
                '<?php echo _l('month_2') ?>',
                '<?php echo _l('month_3') ?>',
                '<?php echo _l('month_4') ?>',
                '<?php echo _l('month_5') ?>',
                '<?php echo _l('month_6') ?>',
                '<?php echo _l('month_7') ?>',
                '<?php echo _l('month_8') ?>',
                '<?php echo _l('month_9') ?>',
                '<?php echo _l('month_10') ?>',
                '<?php echo _l('month_11') ?>',
                '<?php echo _l('month_12') ?>'],
             crosshair: true,
         },
         yAxis: {
             min: 0,
             title: {
              text: value.name
             }
         },
         tooltip: {
             headerFormat: '<span >{point.key}</span><table>',
             pointFormat: '<tr>' +
                 '<td><b>{point.y:.0f}'+value.unit +' {series.name}</b></td></tr>',
             footerFormat: '</table>',
             shared: true,
             useHTML: true
         },
         plotOptions: {
             column: {
                 pointPadding: 0.2,
                 borderWidth: 0
             }
         },

         series: [
            {
                name: value.name,
                data: value.data
            },
      
    ]
     });
    }
})(jQuery);
</script>
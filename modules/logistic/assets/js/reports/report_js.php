<script>
var general_package_log, general_shipments,
general_pickups_and_shipments, general_consolidated_shipment, general_consolidated_package,  report_from_choose,
fnServerParams;
 var report_from = $('input[name="report-from"]');
 var report_to = $('input[name="report-to"]');
  var date_range = $('#date-range');
(function($) {
  "use strict";

  general_package_log = $('#general_package_log');
  general_shipments = $('#general_shipments');
  general_pickups_and_shipments = $('#general_pickups_and_shipments');
  general_consolidated_shipment = $('#general_consolidated_shipment');
   general_consolidated_package = $('#general_consolidated_package');
  report_from_choose = $('#report-time');
  fnServerParams = {
    "products_services": '[name="products_services"]',
    "report_months": '[name="months-report"]',
    "report_from": '[name="report-from"]',
    "report_to": '[name="report-to"]',
    "year_requisition": "[name='year_requisition']",
    "report_currency": '[name="currency"]',
    "payment_status": '[name="payment_status"]',
  }
  
  $('select[name="currency"]').on('change', function() {
    gen_reports();
  });



  $('select[name="months-report"]').on('change', function() {
    if($(this).val() != 'custom'){
     gen_reports();
    }
   });

   $('select[name="year_requisition"]').on('change', function() {
     gen_reports();
   });

   report_from.on('change', function() {
     var val = $(this).val();
     var report_to_val = report_to.val();
     if (val != '') {
       report_to.attr('disabled', false);
       if (report_to_val != '') {
         gen_reports();
       }
     } else {
       report_to.attr('disabled', true);
     }
   });

   report_to.on('change', function() {
     var val = $(this).val();
     if (val != '') {
       gen_reports();
     }
   });

   $('.table-general_package_log').on('draw.dt', function() {
     var general_package_logTable = $(this).DataTable();
     var sums = general_package_logTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
     $(this).find('tfoot td.total').html(sums.total);
   });

   $('.table-general_shipments').on('draw.dt', function() {
     var general_shipmentsTable = $(this).DataTable();
     var sums = general_shipmentsTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
     $(this).find('tfoot td.total').html(sums.total);
   });

   $('.table-general_pickups_and_shipments').on('draw.dt', function() {
     var general_pickups_and_shipmentsTable = $(this).DataTable();
     var sums = general_pickups_and_shipmentsTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
     $(this).find('tfoot td.total').html(sums.total);
   });

   $('.table-general_consolidated_shipment').on('draw.dt', function() {
     var general_consolidated_shipmentTable = $(this).DataTable();
     var sums = general_consolidated_shipmentTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
     $(this).find('tfoot td.total').html(sums.total);
   });

  $('.table-general_consolidated_package').on('draw.dt', function() {
     var general_consolidated_packageTable = $(this).DataTable();
     var sums = general_consolidated_packageTable.ajax.json().sums;
     $(this).find('tfoot').addClass('bold');
     $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
     $(this).find('tfoot td.total').html(sums.total);
   });

   $('select[name="months-report"]').on('change', function() {
     var val = $(this).val();
     report_to.attr('disabled', true);
     report_to.val('');
     report_from.val('');
     if (val == 'custom') {
       date_range.addClass('fadeIn').removeClass('hide');
       return;
     } else {
       if (!date_range.hasClass('hide')) {
         date_range.removeClass('fadeIn').addClass('hide');
       }
     }
     gen_reports();
   });
})(jQuery);


 function init_report(e, type) {
  "use strict";

   var report_wrapper = $('#report');

   if (report_wrapper.hasClass('hide')) {
        report_wrapper.removeClass('hide');
   }

   $('head title').html($(e).text());
   

   report_from_choose.addClass('hide');

   $('#year_requisition').addClass('hide');


   general_package_log.addClass('hide');
  general_shipments.addClass('hide');
  general_pickups_and_shipments.addClass('hide');
  general_consolidated_shipment.addClass('hide');
  general_consolidated_package.addClass('hide');
  $('#payment_status_f').addClass('hide');

  $('select[name="months-report"]').selectpicker('val', 'this_month');
    // Clear custom date picker
      $('#currency').removeClass('hide');

      if (type == 'general_package_log') {
        general_package_log.removeClass('hide');
        report_from_choose.removeClass('hide');
   
      }else if(type == 'general_shipments'){
       
        general_shipments.removeClass('hide');
        report_from_choose.removeClass('hide');
      }else if(type == 'general_pickups_and_shipments'){
        general_pickups_and_shipments.removeClass('hide');
        report_from_choose.removeClass('hide');
      }else if(type == 'general_consolidated_shipment'){
        general_consolidated_shipment.removeClass('hide');
        report_from_choose.removeClass('hide');
      }else if(type == 'general_consolidated_package'){
        general_consolidated_package.removeClass('hide');
        report_from_choose.removeClass('hide');
      }



      gen_reports();
}


function general_package_log_rp() {
  "use strict";

 if ($.fn.DataTable.isDataTable('.table-general_package_log')) {
   $('.table-general_package_log').DataTable().destroy();
 }
 initDataTable('.table-general_package_log', admin_url + 'logistic/general_package_log_report', false, false, fnServerParams);
}

function general_shipments_rp() {
  "use strict";

 if ($.fn.DataTable.isDataTable('.table-general_shipments')) {
   $('.table-general_shipments').DataTable().destroy();
 }
 initDataTable('.table-general_shipments', admin_url + 'logistic/general_shipments_reports', false, false, fnServerParams);
}

function general_pickups_and_shipments_rp() {
  "use strict";

 if ($.fn.DataTable.isDataTable('.table-general_pickups_and_shipments')) {
   $('.table-general_pickups_and_shipments').DataTable().destroy();
 }
 initDataTable('.table-general_pickups_and_shipments', admin_url + 'logistic/general_pickups_and_shipments_reports', false, false, fnServerParams);
}

function general_consolidated_shipment_rp() {
  "use strict";

 if ($.fn.DataTable.isDataTable('.table-general_consolidated_shipment')) {
   $('.table-general_consolidated_shipment').DataTable().destroy();
 }
 initDataTable('.table-general_consolidated_shipment', admin_url + 'logistic/general_consolidated_shipment_reports', false, false, fnServerParams);
}

function general_consolidated_package_rp() {
  "use strict";

 if ($.fn.DataTable.isDataTable('.table-general_consolidated_package')) {
   $('.table-general_consolidated_package').DataTable().destroy();
 }
 initDataTable('.table-general_consolidated_package', admin_url + 'logistic/general_consolidated_package_reports', false, false, fnServerParams);
}

function gen_reports() {
  "use strict";

 if (!general_package_log.hasClass('hide')) {
   general_package_log_rp();
 }else if (!general_shipments.hasClass('hide')) {
    general_shipments_rp();
 }else if (!general_pickups_and_shipments.hasClass('hide')) {
    general_pickups_and_shipments_rp();
 }else if(!general_consolidated_shipment.hasClass('hide')){
    general_consolidated_shipment_rp();
 }else if(!general_consolidated_package.hasClass('hide')){
    general_consolidated_package_rp();
 }
}
</script>



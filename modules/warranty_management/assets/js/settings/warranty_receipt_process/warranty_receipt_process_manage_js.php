
<script>

	(function($) {
		"use strict";

		var InvoiceServerParams={};
		var warranty_receipt_process_table = $('.table-warranty_receipt_process_table');
		initDataTable(warranty_receipt_process_table, admin_url+'warranty_management/warranty_receipt_process_table',[0],[0], InvoiceServerParams, [0 ,'desc']);

		$('#date_add').on('change', function() {
			warranty_receipt_process_table.DataTable().ajax.reload().columns.adjust().responsive.recalc();
		});

		var hidden_columns = [0];
		$('.table-warranty_receipt_process_table').DataTable().columns(hidden_columns).visible(false, false);
	})(jQuery); 

/**
* add routing
* @param {[type]} staff_id 
* @param {[type]} role_id  
* @param {[type]} add_new  
*/
function add_warranty_receipt_process(staff_id, role_id, add_new) {
	"use strict";

	$("#modal_wrapper").load("<?php echo admin_url('warranty_management/warranty_management/warranty_receipt_process_modal'); ?>", {
		slug: 'add',
	}, function() {
		if ($('.modal-backdrop.fade').hasClass('in')) {
			$('.modal-backdrop.fade').remove();
		}
		if ($('#appointmentModal').is(':hidden')) {
			$('#appointmentModal').modal({
				show: true
			});
		}
	});

	init_selectpicker();
	$(".selectpicker").selectpicker('refresh');
}

</script>
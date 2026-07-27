
<script>

	(function($) {
		"use strict";

		var InvoiceServerParams={
			"warranty_process_id": "[name='warranty_process_id']",
		};
		var process_table = $('.table-process_table');
		initDataTable(process_table, admin_url+'warranty_management/process_table',[0],[0], InvoiceServerParams, [1 ,'asc']);

		$('#date_add').on('change', function() {
			process_table.DataTable().ajax.reload().columns.adjust().responsive.recalc();
		});

		var hidden_columns = [0];
		$('.table-process_table').DataTable().columns(hidden_columns).visible(false, false);

	})(jQuery);

	function add_process(warranty_process_id, process_id, type) {
		"use strict";

		$("#modal_wrapper").load("<?php echo admin_url('warranty_management/warranty_management/process_modal'); ?>", {
			warranty_process_id: warranty_process_id,
			process_id: process_id,
			type: type
		}, function() {

			$("body").find('#appointmentModal').modal({ show: true, backdrop: 'static' });
		});

		init_selectpicker();
		$(".selectpicker").selectpicker('refresh');

	}


</script>
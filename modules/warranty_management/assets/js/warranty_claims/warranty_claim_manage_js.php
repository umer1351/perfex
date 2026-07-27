<script>
	$(function(){
		'use strict';
		var ProposalServerParams = {
			"client_filter": "[name='client_filter[]']",
			"order_filter": "[name='order_filter[]']",
			"product_filter": "[name='product_filter[]']",
			"delivery_note_filter": "[name='delivery_note_filter[]']",
			"claim_status_filter": "[name='claim_status_filter[]']",
		};

		var warranty_claim_table = $('table.table-warranty_claim_table');
		var _table_api = initDataTable(warranty_claim_table, admin_url+'warranty_management/warranty_claim_table', [0], [0], ProposalServerParams,  [0, 'desc']);
		$.each(ProposalServerParams, function(i, obj) {
			$('select' + obj).on('change', function() {  
				warranty_claim_table.DataTable().ajax.reload();
			});
		});

		var hidden_columns = [0];
		warranty_claim_table.DataTable().columns(hidden_columns).visible(false, false);

	});

	function warranty_status_mark_as(status, task_id, type) {
		"use strict"; 
		
		var url = 'warranty_management/warranty_status_mark_as/' + status + '/' + task_id + '/' + type;
		var taskModalVisible = $('#task-modal').is(':visible');
		url += '?single_task=' + taskModalVisible;
		$("body").append('<div class="dt-loader"></div>');

		requestGetJSON(url).done(function (response) {
			$("body").find('.dt-loader').remove();
			if (response.success === true || response.success == 'true') {

				var av_tasks_tables = ['.table-warranty_claim_table'];
				$.each(av_tasks_tables, function (i, selector) {
					if ($.fn.DataTable.isDataTable(selector)) {
						$(selector).DataTable().ajax.reload(null, false);
					}
				});
				alert_float('success', response.message);
			}
		});
	}

</script>
<script type="text/javascript">
	$(function(){
		'use strict';

		appValidateForm($("body").find('#add_warranty_claim'), {
			'client_id': 'required',
			'item_id': 'required',
			'warranty_receipt_process_id': 'required',
			'invoice_id': 'required',
		}); 

		$('select[name="client_id"]').on('change', function() {
			"use strict";  

			var client_id = $('select[name="client_id"]').val();

			if(client_id != '' && client_id != undefined){

				$.post(admin_url + 'warranty_management/get_invoice_by_client/'+client_id).done(function(response){
					response = JSON.parse(response);

					$('select[name="invoice_id"]').html('');
					$('select[name="invoice_id"]').append(response.invoice_option);
					init_selectpicker();
					$(".selectpicker").selectpicker('refresh');
					
				});

			}else{
				$('select[name="invoice_id"]').html('');
				$('select[name="warranty_receipt_process_id"]').html('');
				$('select[name="item_id"]').html('');
				$('.invoice-item table.invoice-items-table.items tbody').html('');

				init_selectpicker();
				$(".selectpicker").selectpicker('refresh');
			}


		});

		$('select[name="invoice_id"]').on('change', function() {
			"use strict";  

			var invoice_id = $('select[name="invoice_id"]').val();

			if(invoice_id != '' && invoice_id != undefined){

				$.post(admin_url + 'warranty_management/get_list_item_warranty_by_invoice/'+invoice_id).done(function(response){
					response = JSON.parse(response);

					$('select[name="item_id"]').html('');
					$('select[name="item_id"]').append(response.item_warranty_option);
					init_selectpicker();
					$(".selectpicker").selectpicker('refresh');
					
				});

			}else{
				$('select[name="item_id"]').html('');
				$('select[name="warranty_receipt_process_id"]').html('');
				$('.invoice-item table.invoice-items-table.items tbody').html('');

				init_selectpicker();
				$(".selectpicker").selectpicker('refresh');
			}

		});

		$('select[name="item_id"]').on('change', function() {
			"use strict";  

			var item_id = $('select[name="item_id"]').val();

			if(item_id != '' && item_id != undefined){

				$.post(admin_url + 'warranty_management/get_list_warranty_receipt_process/'+item_id).done(function(response){
					response = JSON.parse(response);

					$('select[name="warranty_receipt_process_id"]').html('');
					$('select[name="warranty_receipt_process_id"]').append(response.warranty_receipt_process_option);
					init_selectpicker();
					$(".selectpicker").selectpicker('refresh');
					
				});

			}else{
				$('select[name="warranty_receipt_process_id"]').html('');
				init_selectpicker();
				$(".selectpicker").selectpicker('refresh');
			}

		});

		$('select[name="warranty_receipt_process_id"]').on('change', function() {
			"use strict";  

			var warranty_receipt_process_id = $('select[name="warranty_receipt_process_id"]').val();

			if(warranty_receipt_process_id != '' && warranty_receipt_process_id != undefined){

				$.post(admin_url + 'warranty_management/get_list_warranty_receipt_process_detail/'+warranty_receipt_process_id).done(function(response){
					response = JSON.parse(response);

					$('.invoice-item table.invoice-items-table.items tbody').html('');
					$('.invoice-item table.invoice-items-table.items tbody').append(response.warranty_receipt_process_detail_option);
					init_selectpicker();
					$(".selectpicker").selectpicker('refresh');
					
				});

			}else{
				$('.invoice-item table.invoice-items-table.items tbody').html('');
				init_selectpicker();
				$(".selectpicker").selectpicker('refresh');
			}

		});

		

	});
</script>
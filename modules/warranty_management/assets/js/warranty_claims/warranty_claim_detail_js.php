<script type="text/javascript">
	function warranty_status_mark_as(status, task_id, type) {
		"use strict"; 
		
		var url = 'warranty_management/warranty_status_mark_as/' + status + '/' + task_id + '/' + type;
		var taskModalVisible = $('#task-modal').is(':visible');
		url += '?single_task=' + taskModalVisible;
		$("body").append('<div class="dt-loader"></div>');

		requestGetJSON(url).done(function (response) {
			$("body").find('.dt-loader').remove();
			if (response.success === true || response.success == 'true') {
				location.reload();
			}
		});
	}

	function delete_wh_activitylog(wrapper, id) {
		"use strict"; 

		if (confirm_delete()) {
			requestGetJSON('warranty_management/delete_activitylog/' + id).done(function(response) {
				if (response.success === true || response.success == 'true') {
					$(wrapper).parents('.feed-item').remove();
					alert_float('success', '<?php echo  _l('wm_warranty_log_deleted') ?>');
				}
			}).fail(function(data) {
				alert_float('danger', data.responseText);
			});
		}
	}

	function warranty_claim_activity_log_modal(slug, id, warranty_claim_id, cart_id) {
		"use strict";
		var data={};
		data.slug = slug;
		data.warranty_claim_id = warranty_claim_id;
		data.id = id;
		data.cart_id = cart_id;

		$.get(site_url+'warranty_management/warranty_claim_activity_log_modal',data , function (response) {

			$("#modal_wrapper").html(response.data);
			$("body").find('#add_activity_log').modal({ show: true, backdrop: 'static' });
			init_datepicker();
			
		}, 'json');
	}


	$('.mark_start_working').on('click', function() {
		"use strict";

		var warranty_process_detail_id = $("input[name='warranty_process_detail_id']").val();
		var warranty_claim_id = $("input[name='warranty_claim_id']").val();

		$.get(admin_url + 'warranty_management/warranty_process_detail_mark_as_start_working/' + warranty_process_detail_id+'/'+warranty_claim_id, function (response) {
			alert_float(response.status, response.message);

			location.reload();
		}, 'json');

	});

	$('.mark_pause').on('click', function() {
		"use strict";

		var warranty_process_detail_id = $("input[name='warranty_process_detail_id']").val();

		$.get(admin_url + 'warranty_management/warranty_process_detail_mark_as_mark_pause/' + warranty_process_detail_id, function (response) {
			alert_float(response.status, response.message);

			location.reload();
		}, 'json');

	});

	$('.mark_done').on('click', function() {
		"use strict";

		var warranty_process_detail_id = $("input[name='warranty_process_detail_id']").val();
		var warranty_claim_id = $("input[name='warranty_claim_id']").val();

		$.get(admin_url + 'warranty_management/warranty_process_detail_mark_as_mark_done/' + warranty_process_detail_id+'/'+ warranty_claim_id, function (response) {
			alert_float(response.status, response.message);

			location.reload();
		}, 'json');

	});

		var time_trackings;
		(function($) {
		"use strict";  


		<?php if(isset($time_tracking_details)){ ?>
			var dataObject_pu = <?php echo json_encode($time_tracking_details) ; ?>;
		<?php }else{?>
			var dataObject_pu = [];
		<?php } ?>

		var hotElement1 = document.getElementById('time_tracking_hs');

		time_trackings = new Handsontable(hotElement1, {
			licenseKey: 'non-commercial-and-evaluation',

			contextMenu: true,
			manualRowMove: true,
			manualColumnMove: true,
			stretchH: 'all',
			autoWrapRow: true,
			rowHeights: 30,
			defaultRowHeight: 100,
			minRows: 10,
			maxRows: <?php echo new_html_entity_decode($rows); ?>,
			width: '100%',

			rowHeaders: true,
			colHeaders: true,
			autoColumnSize: {
				samplingRatio: 23
			},

			filters: true,
			manualRowResize: true,
			manualColumnResize: true,
			allowInsertRow: true,
			allowRemoveRow: true,
			columnHeaderHeight: 40,
			// colWidths:  [20, 20, 20,20],
			rowHeights: 30,
			rowHeaderWidth: [44],
			minSpareRows: 1,
			hiddenColumns: {
				columns: [0],
				indicators: true
			},

			columns: [
			{
				type: 'text',
				data: 'id',
			},
			
			
			{
				data: 'from_date',
				type: 'text',
				
			},
			{
				data: 'to_date',
				type: 'text',
			},
			{
				data: 'duration',
				type: 'numeric',
				numericFormat: {
					pattern: '0,0.00',
				},
			},
			{
				data: 'full_name',
				type: 'text',
			},

			],

			colHeaders: [

			'<?php echo _l('id'); ?>',
			'<?php echo _l('wm_start_date'); ?>',
			'<?php echo _l('wm_end_date'); ?>',
			'<?php echo _l('wm_duration'); ?>',
			'<?php echo _l('staff'); ?>',
			],

			data: dataObject_pu,
		});


	})(jQuery);
	
</script>
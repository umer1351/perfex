<script type="text/javascript">
	$(function(){
		'use strict';

		appValidateForm($('#form_activity_log'),{
			description:'required',
		},expenseSubmitHandler);


		if($('#dropzoneDragArea').length > 0){
			expenseDropzone = new Dropzone("#form_activity_log", appCreateDropzoneOptions({
				autoProcessQueue: false,
				clickable: '#dropzoneDragArea',
				previewsContainer: '.dropzone-previews',
				addRemoveLinks: true,
				maxFiles: 10,

				success:function(file,response){
					response = JSON.parse(response);
					if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
						window.location.assign(response.url);
					}else{
						expenseDropzone.processQueue();
					}
				},

			}));
		}

	});


	function expenseSubmitHandler(form){

		'use strict';

		$.post(form.action, $(form).serialize()).done(function(response) {
			response = JSON.parse(response);
			if (response.shipment_log_id) {
				if(typeof(expenseDropzone) !== 'undefined'){
					if (expenseDropzone.getQueuedFiles().length > 0) {
						expenseDropzone.options.url = admin_url + 'warehouse/add_shipment_attachment/' + response.shipment_log_id+'/'+response. cart_id;
						expenseDropzone.processQueue();
					} else {
						window.location.assign(response.url);
					}
				} else {
					window.location.assign(response.url);
				}
			} else {
				window.location.assign(response.url);
			}
		});

		return false;
	}


</script>
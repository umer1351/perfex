<script>

(function($) {
  "use strict"; 
    $("body").on("click", ".package-send-to-client", function (e) {
      e.preventDefault();
      $("#package_send_to_client_modal").modal("show");
    });
})(jQuery);

function assign_driver(invoker){
	"use strict";

	var assign_driver = $(invoker).data('assign_driver');
	var package_id = $(invoker).data('package_id');

	$('#addsign_driver_modal input[name="redirect_url"]').val(admin_url+'logistic/package_detail/'+package_id);
	$('#addsign_driver_modal input[name="package_id"]').val(package_id);
	$('#addsign_driver_modal select[name="assign_driver"]').val(assign_driver).change();


	$('#addsign_driver_modal').modal('show');
}


//preview package attachment
function preview_package_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_package_file(id, rel_id);
}

function view_package_file(id, rel_id) {
  "use strict"; 
      $('#package_file_data').empty();
      $("#package_file_data").load(admin_url + 'logistic/file_package/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}

function delete_package_attachment(id) {
  "use strict"; 
    if (confirm_delete()) {
        requestGet('logistic/delete_package_attachment/' + id).done(function(success) {
            
                $("#package_pv_file").find('[data-attachment-id="' + id + '"]').remove();
            
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
  }


  //preview package attachment
function preview_package_shipment_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_package_shipment_file(id, rel_id);
}

function view_package_shipment_file(id, rel_id) {
  "use strict"; 
      $('#shipment_file_data').empty();
      $("#shipment_file_data").load(admin_url + 'logistic/file_shipment_package/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
</script>
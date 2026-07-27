<script>

(function($) {
  "use strict"; 
    $("body").on("click", ".consolidation-send-to-client", function (e) {
      e.preventDefault();
      $("#consolidation_send_to_client_modal").modal("show");
    });
})(jQuery);

function assign_driver(invoker){
	"use strict";

	var assign_driver = $(invoker).data('assign_driver');
	var consolidation_id = $(invoker).data('consolidation_id');

	$('#addsign_driver_modal input[name="redirect_url"]').val(admin_url+'logistic/consolidated_detail/'+consolidation_id);
	$('#addsign_driver_modal input[name="consolidation_id"]').val(consolidation_id);
	$('#addsign_driver_modal select[name="assign_driver"]').val(assign_driver).change();


	$('#addsign_driver_modal').modal('show');
}


//preview consolidation attachment
function preview_consolidation_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_consolidation_file(id, rel_id);
}

function view_consolidation_file(id, rel_id) {
  "use strict"; 
      $('#consolidation_file_data').empty();
      $("#consolidation_file_data").load(admin_url + 'logistic/file_consolidated/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}

function delete_consolidation_attachment(id) {
  "use strict"; 
    if (confirm_delete()) {
        requestGet('logistic/delete_consolidation_attachment/' + id).done(function(success) {
            
                $("#consolidation_pv_file").find('[data-attachment-id="' + id + '"]').remove();
            
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
  }


  //preview consolidation attachment
function preview_consolidation_shipment_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_consolidation_shipment_file(id, rel_id);
}

function view_consolidation_shipment_file(id, rel_id) {
  "use strict"; 
      $('#shipment_file_data').empty();
      $("#shipment_file_data").load(admin_url + 'logistic/file_shipment_consolidation/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
</script>
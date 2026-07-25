<script>

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
  	$("#consolidation_file_data").load(site_url + 'logistic/client/file_consolidation/' + id + '/' + rel_id, function(response, status, xhr) {
	    if (status == "error") {
	        alert_float('danger', xhr.statusText);
	    }
  	});
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
      $("#shipment_file_data").load(site_url + 'logistic/client/file_shipment_consolidation/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}	


function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}
</script>
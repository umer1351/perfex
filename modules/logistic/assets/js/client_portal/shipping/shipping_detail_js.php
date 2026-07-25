<script>

//preview shipping attachment
function preview_shipping_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_shipping_file(id, rel_id);
}

function view_shipping_file(id, rel_id) {
  "use strict"; 
  	$('#shipping_file_data').empty();
  	$("#shipping_file_data").load(site_url + 'logistic/client/file_shipping/' + id + '/' + rel_id, function(response, status, xhr) {
	    if (status == "error") {
	        alert_float('danger', xhr.statusText);
	    }
  	});
}



  //preview shipping attachment
function preview_shipping_shipment_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_shipping_shipment_file(id, rel_id);
}

function view_shipping_shipment_file(id, rel_id) {
  "use strict"; 
      $('#shipment_file_data').empty();
      $("#shipment_file_data").load(site_url + 'logistic/client/file_shipment_shipping/' + id + '/' + rel_id, function(response, status, xhr) {
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
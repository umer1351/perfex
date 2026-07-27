<script>

function preview_package_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_package_file(id, rel_id);
}

function view_package_file(id, rel_id) {
  "use strict"; 
      $('#package_file_data').empty();
      $("#package_file_data").load(site_url + 'logistic/client/file_package/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
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
      $("#shipment_file_data").load(site_url + 'logistic/client/file_shipment_package/' + id + '/' + rel_id, function(response, status, xhr) {
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
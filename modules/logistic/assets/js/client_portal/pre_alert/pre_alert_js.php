<script>
	
//preview pre_alert attachment
function preview_pre_alert_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_pre_alert_file(id, rel_id);
}

function view_pre_alert_file(id, rel_id) {
  "use strict"; 
      $('#pre_alert_file_data').empty();
      $("#pre_alert_file_data").load(site_url + 'logistic/client/file_pre_alert/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}

function delete_pre_alert_attachment(pre_alert_id, id) {
  "use strict"; 
    if (confirm_delete()) {
        $.post(site_url+'logistic/client/delete_pre_alert_attachment/' + pre_alert_id+'/'+id).done(function(success) {
            
                window.location.reload();
            
        })
    }
  }


</script>
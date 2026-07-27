<script>
(function($){
"use strict";

var driverServerParams = {
        "office_group": "[name='office_group']",
    }

initDataTable('.table-drivers', window.location.href, [], [],
        driverServerParams, [0, 'asc']);


$.each(driverServerParams, function(i, obj) {
$('select' + obj).on('change', function() {  
    $('.table-drivers').DataTable().ajax.reload()
        .columns.adjust()
        .responsive.recalc();
});
});

})(jQuery);
function delete_staff_member(id) {
    "use strict";
    $('#delete_staff').modal('show');
    $('#transfer_data_to').find('option').prop('disabled', false);
    $('#transfer_data_to').find('option[value="' + id + '"]').prop('disabled', true);
    $('#delete_staff .delete_id input').val(id);
    $('#transfer_data_to').selectpicker('refresh');
}

</script>

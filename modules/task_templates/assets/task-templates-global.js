
// New task template function, various actions performed
function new_task_from_template(id) {
    var url = admin_url + 'task_templates/create_task';
    if(typeof (id) != 'undefined'){
        url += '?id=' + id;
    }

    var $taskSingleModal = $('#task-modal');
    if ($taskSingleModal.is(':visible')) {
        $taskSingleModal.modal('hide');
    }

    var $taskEditModal = $('#_task_modal');
    if ($taskEditModal.is(':visible')) {
        $taskEditModal.modal('hide');
    }

    requestGet(url).done(function (response) {
        $('#_task').html(response);
        $("body").find('#_task_modal').modal({
            show: true,
            backdrop: 'static'
        });
    }).fail(function (error) {
        alert_float('danger', error.responseText);
    })
}
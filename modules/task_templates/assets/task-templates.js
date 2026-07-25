var _table_api;
$(document).ready(function(){
    "use strict";

    var table_task_templates = $('.table-task-templates');
    if (table_task_templates.length) {

        // Tasks not sortable
        var tasksTableNotSortable = [0]; // bulk actions
        var tasksTableURL = admin_url + 'task_templates/table?';

        if ($("body").hasClass('tasks-page')) {
            tasksTableURL += 'bulk_actions=true&';
        }
        if ($("body [name=project_id]").length) {
            var project_id = $("body [name=project_id]").val();
            tasksTableURL += 'project_id=' + project_id;
        }

        _table_api = initDataTable(table_task_templates, tasksTableURL, tasksTableNotSortable, tasksTableNotSortable, {}, [1, 'asc']);
    }

});

function init_new_task_template_form(){
    var inner_popover_template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>';

    $('#_task_modal .task-menu-options .trigger').popover({
        html: true,
        placement: "bottom",
        trigger: 'click',
        title: "<?php echo _l('actions'); ?>",
        content: function () {
            return $('body').find('#_task_modal .task-menu-options .content-menu').html();
        },
        template: inner_popover_template
    });

    custom_fields_hyperlink();

    appValidateForm($('#task-form'), {
        name: 'required',
        startdate: {
            required: function(){
                return !$('[app-field-wrapper="startdate"]').hasClass('hide');
            },
            number: true,
            min: 0
        },
        after_task_id: {
            required: function(){
                return !$('[app-field-wrapper="after_task_id"]').hasClass('hide');
            },
        },
        duration_value: {
            number: true,
            min: 0
        },
        repeat_every_custom: {min: 1},
    }, task_template_form_handler);

    init_datepicker();
    init_color_pickers();
    init_selectpicker();
    updateStartTypeFieldLabel();
    toggleStartTypeMilestoneRadio();

    $("[name=start_type]").on("change", function(){
        var val = $(this).val();
        if(val == "project" || val == "milestone"){
            $('[app-field-wrapper="startdate"]').removeClass('hide');
            $('[app-field-wrapper="after_task_id"]').addClass('hide');
        }
        else{
            $('[app-field-wrapper="startdate"]').addClass('hide');
            $('[app-field-wrapper="after_task_id"]').removeClass('hide');
        }
        updateStartTypeFieldLabel();
    })
    $("#milestone").on("change", function(){
        toggleStartTypeMilestoneRadio();
    })
    $(".dropdown-input-group .dropdown-menu li a").click(function(){
        var duration = $(this).text();
        const $container = $(this).closest(".dropdown-input-group");
        $container.find("button .dropdown-display-text").text(duration);
        $container.find("[type=hidden]").val(duration.toLowerCase());
        const $li = $(this).closest("li");
        $li.siblings().removeClass("hide");
        $li.addClass("hide");
    })
}

function updateStartTypeFieldLabel(){
    let initStartType = $("[name=start_type]:checked").val();
    if(initStartType == "project"){
        $("label.project_start").removeClass("hide");
        $("label.milestone_start").addClass("hide");
    }
    else if(initStartType == "milestone"){
        $("label.project_start").addClass("hide");
        $("label.milestone_start").removeClass("hide");
    }
    else{
        $("label.project_start").addClass("hide");
        $("label.milestone_start").addClass("hide");
    }
}

function toggleStartTypeMilestoneRadio(){
    let milestone = $("#milestone").val();
    if(milestone != ""){
        $(".start_type_milestone_wrapper").removeClass("hide");
    }
    else{
        $(".start_type_milestone_wrapper").addClass("hide");
        $("#start_type_date").trigger("click");
    }
}

// New task template function, various actions performed
function new_task_template(url) {
    url = typeof (url) != 'undefined' ? url : admin_url + 'task_templates/task';

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

// Go to edit view
function edit_task_template(template_id) {
    requestGet('task_templates/task/' + template_id).done(function (response) {
        $('#_task').html(response);
        $('#task-modal').modal('hide');
        $("body").find('#_task_modal').modal({
            show: true,
            backdrop: 'static'
        });
    });
}

// Handles task add/edit form modal.
function task_template_form_handler(form) {
    tinymce.triggerSave();

    // Disable the save button in cases od duplicate clicks
    $('#_task_modal').find('button[type="submit"]').prop('disabled', true);

    $("#_task_modal input[type=file]").each(function () {
        if ($(this).val() === "") {
            $(this).prop('disabled', true);
        }
    });

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: $(form).attr('method'),
        data: formData,
        mimeType: $(form).attr('enctype'),
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function (response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
        }
        $('#_task_modal').modal('hide');
        if($("[name=project_id]").length > 0){
            window.location.reload();
        }
        if(_table_api !== undefined){
            _table_api.ajax.reload();
        }

    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.responseText));
    });

    return false;
}

// Removes task single attachment
function remove_task_template_attachment(link, id) {
    if (confirm_delete()) {
        requestGetJSON('task_templates/remove_task_attachment/' + id).done(function (response) {
            if (response.success === true || response.success == 'true') {
                $('[data-task-attachment-id="' + id + '"]').remove();
            }
            _task_attachments_more_and_less_checks();
            if (response.comment_removed) {
                $('#comment_' + response.comment_removed).remove();
            }
        });
    }
}
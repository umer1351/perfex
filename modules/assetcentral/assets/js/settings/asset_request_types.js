var fnServerParams;
(function ($) {
    "use strict";

    fnServerParams = {};

    appValidateForm($('#new-asset-request-type-form'), {
        type_name: 'required'
    }, asset_request_type_form_handler);

    init_asset_request_type_table();

    $('.add-new-asset-request-type').on('click', function () {
        $('#new-asset-request-type-modal').find('button[type="submit"]').prop('disabled', false);

        $('input[name="id"]').val('');

        $('input[name="type_name"]').val('');
        $('textarea[name="type_description"]').val('');

        $('#new-asset-request-type-modal').modal('show');
    })

})(jQuery);

function init_asset_request_type_table() {
    "use strict";

    if ($.fn.DataTable.isDataTable('.table-asset-request-type')) {
        $('.table-asset-request-type').DataTable().destroy();
    }
    initDataTable('.table-asset-request-type', window.location.href, false, false, fnServerParams);
}


function edit_asset_request_type(id) {
    "use strict";
    $('#new-asset-request-type-modal').find('button[type="submit"]').prop('disabled', false);

    requestGetJSON(admin_url + 'assetcentral/settings/get_data_asset_request_type/' + id).done(function (response) {

        $('#new-asset-request-type-modal').modal('show');

        $('input[name="id"]').val(id);

        $('input[name="type_name"]').val(response.type_name);
        $('textarea[name="type_description"]').val(response.type_description);

    });
}

function asset_request_type_form_handler(form) {
    "use strict";

    $('#new-asset-request-type-modal').find('button[type="submit"]').prop('disabled', true);

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
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
            alert_float('success', response.message);

            init_asset_request_type_table();
        } else {
            alert_float('danger', response.message);
        }
        $('#new-asset-request-type-modal').modal('hide');
    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}
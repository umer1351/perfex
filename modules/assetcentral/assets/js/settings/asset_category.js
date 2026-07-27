var fnServerParams;
(function ($) {
    "use strict";

    fnServerParams = {};

    appValidateForm($('#new-asset-category-form'), {
        category_name: 'required'
    }, asset_category_form_handler);

    init_asset_category_table();

    $('.add-new-asset-category').on('click', function () {
        $('#new-asset-category-modal').find('button[type="submit"]').prop('disabled', false);

        $('input[name="category_name"]').val('');
        $('textarea[name="category_description"]').val('');
        $('input[name="id"]').val('');

        $('#new-asset-category-modal').modal('show');
    })

})(jQuery);

function init_asset_category_table() {
    "use strict";

    if ($.fn.DataTable.isDataTable('.table-asset-categories')) {
        $('.table-asset-categories').DataTable().destroy();
    }
    initDataTable('.table-asset-categories', window.location.href, false, false, fnServerParams);
}


function edit_asset_category(id) {
    "use strict";
    $('#new-asset-category-modal').find('button[type="submit"]').prop('disabled', false);

    requestGetJSON(admin_url + 'assetcentral/settings/get_data_asset_category/' + id).done(function (response) {

        $('#new-asset-category-modal').modal('show');

        $('input[name="id"]').val(id);

        $('input[name="category_name"]').val(response.category_name);
        $('textarea[name="category_description"]').val(response.category_description);

    });
}

function asset_category_form_handler(form) {
    "use strict";

    $('#new-asset-category-modal').find('button[type="submit"]').prop('disabled', true);

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

            init_asset_category_table();
        } else {
            alert_float('danger', response.message);
        }
        $('#new-asset-category-modal').modal('hide');
    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}
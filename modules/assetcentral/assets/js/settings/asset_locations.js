var fnServerParams;
(function ($) {
    "use strict";

    fnServerParams = {};
    var map;

    appValidateForm($('#new-location-form'), {
        location_name: 'required',
        manager_id: 'required',
        country: 'required'
    }, asset_location_form_handler);

    init_asset_locations_table();

    $('.add-new-location').on('click', function (map) {

        $('#new-asset-location-modal').find('button[type="submit"]').prop('disabled', false);

        $('input[name="id"]').val('');

        $('input[name="location_name"]').val('');
        $('input[name="address"]').val('');
        $('input[name="city"]').val('');
        $('input[name="state"]').val('');
        $('input[name="zip_code"]').val('');
        $('input[name="lat"]').val('');
        $('input[name="lng"]').val('');
        $('select[name="manager_id"]').val('').change();
        $('select[name="country"]').val('').change();

        setTimeout(function() {
            initMap();
        }, 300);

        $('#new-asset-location-modal').modal('show');
    })

})(jQuery);

function init_asset_locations_table() {
    "use strict";

    if ($.fn.DataTable.isDataTable('.table-asset-locations')) {
        $('.table-asset-locations').DataTable().destroy();
    }
    initDataTable('.table-asset-locations', window.location.href, false, false, fnServerParams);
}


function edit_asset_location(id) {
    "use strict";
    $('#new-asset-location-modal').find('button[type="submit"]').prop('disabled', false);

    requestGetJSON(admin_url + 'assetcentral/settings/get_data_asset_location/' + id).done(function (response) {

        setTimeout(function() {
            initMap(response.lat, response.lng);
        }, 300);

        $('#new-asset-location-modal').modal('show');

        $('input[name="id"]').val(id);

        $('input[name="location_name"]').val(response.location_name);
        $('input[name="address"]').val(response.address);
        $('input[name="city"]').val(response.city);
        $('input[name="state"]').val(response.state);
        $('input[name="zip_code"]').val(response.zip_code);
        $('input[name="lat"]').val(response.lat);
        $('input[name="lng"]').val(response.lng);
        $('select[name="manager_id"]').val(response.manager_id).change();
        $('select[name="country"]').val(response.country).change();


    });
}

function asset_location_form_handler(form) {
    "use strict";

    $('#new-asset-location-modal').find('button[type="submit"]').prop('disabled', true);

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

            init_asset_locations_table();
        } else {
            alert_float('danger', response.message);
        }
        $('#new-asset-location-modal').modal('hide');
    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}

function updateLatLngInputs(lat, lng) {
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
}

function initMap(lat = '51.505', lng = ' -0.09') {
    var defaultLocation = [lat, lng];

    map = L.map('map', {
        center: defaultLocation,
        zoom: 13
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker(defaultLocation, {
        draggable: true
    }).addTo(map);

    marker.on('dragend', function(e) {
        var latLng = marker.getLatLng();
        updateLatLngInputs(latLng.lat, latLng.lng);
    });

    map.on('click', function(e) {
        var latLng = e.latlng;
        marker.setLatLng(latLng);
        updateLatLngInputs(latLng.lat, latLng.lng);
    });
}
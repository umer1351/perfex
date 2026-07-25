<script>
init_color_pickers();

var state_select = '';
var state_rate_select = '';
var city_select = '';


$('#city_modal select[name="country"]').on('change', function(){

    var country = $(this).val();
    if(country != '' && country != null && country != undefined){
        $.post(admin_url + "logistic/get_state_by_country/"+country).done(function (response) {
            response = JSON.parse(response);
            $('#city_modal select[name="state"]').html(response.html);

            $('#city_modal select[name="state"]').selectpicker("refresh");
            init_selectpicker();

            $('#city_modal select[name="state"]').val(state_select).change();

        });
    }

});

$('#shipping_rates_list_modal select[name="country"]').on('change', function(){

    var country = $(this).val();
    if(country != '' && country != null && country != undefined){
        $.post(admin_url + "logistic/get_state_by_country/"+country).done(function (response) {
            response = JSON.parse(response);
            $('#shipping_rates_list_modal select[name="state"]').html(response.html);

            $('#shipping_rates_list_modal select[name="state"]').selectpicker("refresh");
            init_selectpicker();

            $('#shipping_rates_list_modal select[name="state"]').val(state_rate_select).change();

        });
    }

});

$('#shipping_rates_list_modal select[name="state"]').on('change', function(){

    var state = $(this).val();
    if(state != '' && state != null && state != undefined){
        $.post(admin_url + "logistic/get_city_by_state/"+state).done(function (response) {
            response = JSON.parse(response);
            $('#shipping_rates_list_modal select[name="city"]').html(response.html);

            $('#shipping_rates_list_modal select[name="city"]').selectpicker("refresh");
            init_selectpicker();

            $('#shipping_rates_list_modal select[name="city"]').val(city_select).change();

        });
    }

});



function new_office_group() {
    "use strict";
	$('#office_group_modal').modal('show');	

	$('#office_group_modal input[name="office_group_id"]').val('');

     $('#office_group_modal input[name="office_name"]').val('');
    $('#office_group_modal input[name="office_code"]').val('');
    $('#office_group_modal input[name="city"]').val('');
    $('#office_group_modal input[name="phone"]').val('');
    $('#office_group_modal textarea[name="address"]').val('');

	$('.add-title').removeClass('hide');
	$('.edit-title').addClass('hide');
}	

function edit_office_group(invoker,id) {

    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#office_group_modal input[name="office_group_id"]').val(id);

    $('#office_group_modal input[name="office_name"]').val($(invoker).data('office_name'));
    $('#office_group_modal input[name="office_code"]').val($(invoker).data('office_code'));
    $('#office_group_modal input[name="city"]').val($(invoker).data('city'));
    $('#office_group_modal input[name="phone"]').val($(invoker).data('phone'));
    $('#office_group_modal textarea[name="address"]').val($(invoker).data('address'));

    $('#office_group_modal').modal('show');

}


function new_agency_group() {
    "use strict";
	$('#agency_group_modal').modal('show');	

	$('#agency_group_modal input[name="agency_group_id"]').val('');

    $('#agency_group_modal input[name="agency_name"]').val('');
    $('#agency_group_modal input[name="city"]').val('');
    $('#agency_group_modal input[name="phone"]').val('');
    $('#agency_group_modal textarea[name="address"]').val('');

	$('.add-title').removeClass('hide');
	$('.edit-title').addClass('hide');
}	

function edit_agency_group(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#agency_group_modal input[name="agency_group_id"]').val(id);

    $('#agency_group_modal input[name="agency_name"]').val($(invoker).data('agency_name'));
    $('#agency_group_modal input[name="city"]').val($(invoker).data('city'));
    $('#agency_group_modal input[name="phone"]').val($(invoker).data('phone'));
    $('#agency_group_modal textarea[name="address"]').val($(invoker).data('address'));

    $('#agency_group_modal').modal('show');

}

function new_shipping_company() {
    "use strict";
    $('#shipping_company_modal').modal('show'); 

    $('#shipping_company_modal input[name="shipping_company_id"]').val('');

    $('#shipping_company_modal input[name="shipping_company_name"]').val('');
    $('#shipping_company_modal input[name="city"]').val('');

    $('#shipping_company_modal select[name="country"]').val('').change();
    $('#shipping_company_modal input[name="postcode"]').val('');
    $('#shipping_company_modal input[name="phone"]').val('');
    $('#shipping_company_modal textarea[name="address"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_shipping_company(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#shipping_company_modal input[name="shipping_company_id"]').val(id);

    $('#shipping_company_modal input[name="shipping_company_name"]').val($(invoker).data('shipping_company_name'));
    $('#shipping_company_modal input[name="city"]').val($(invoker).data('city'));

    $('#shipping_company_modal select[name="country"]').val($(invoker).data('country')).change();
    $('#shipping_company_modal input[name="postcode"]').val($(invoker).data('postcode'));
    $('#shipping_company_modal input[name="phone"]').val($(invoker).data('phone'));
    $('#shipping_company_modal textarea[name="address"]').val($(invoker).data('address'));

    $('#shipping_company_modal').modal('show');

}

function new_type_of_package() {
    "use strict";
    $('#type_of_package_modal').modal('show'); 

    $('#type_of_package_modal input[name="type_of_package_id"]').val('');

    $('#type_of_package_modal input[name="type_of_package_name"]').val('');
    $('#type_of_package_modal textarea[name="package_type_details"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_type_of_package(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#type_of_package_modal input[name="type_of_package_id"]').val(id);

    $('#type_of_package_modal input[name="type_of_package_name"]').val($(invoker).data('type_of_package_name'));
    $('#type_of_package_modal textarea[name="package_type_details"]').val($(invoker).data('package_type_details'));

    $('#type_of_package_modal').modal('show');

}

function new_shipping_mode() {
    "use strict";
    $('#shipping_mode_modal').modal('show'); 

    $('#shipping_mode_modal input[name="shipping_mode_id"]').val('');

    $('#shipping_mode_modal input[name="shipping_mode_name"]').val('');
    $('#shipping_mode_modal input[name="service_price_details"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_shipping_mode(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#shipping_mode_modal input[name="shipping_mode_id"]').val(id);

    $('#shipping_mode_modal input[name="shipping_mode_name"]').val($(invoker).data('shipping_mode_name'));
    $('#shipping_mode_modal input[name="service_price_details"]').val($(invoker).data('service_price_details'));

    $('#shipping_mode_modal').modal('show');

}

function new_shipping_time() {
    $('#shipping_time_modal').modal('show'); 

    $('#shipping_time_modal input[name="shipping_time_id"]').val('');

    $('#shipping_time_modal input[name="shipping_time_name"]').val('');
    $('#shipping_time_modal input[name="service_price_details"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_shipping_time(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#shipping_time_modal input[name="shipping_time_id"]').val(id);

    $('#shipping_time_modal input[name="shipping_time_name"]').val($(invoker).data('shipping_time_name'));
    $('#shipping_time_modal input[name="service_price_details"]').val($(invoker).data('service_price_details'));

    $('#shipping_time_modal').modal('show');

}

function new_style_and_state() {
    "use strict";
    $('#style_and_state_modal').modal('show'); 

    $('#style_and_state_modal input[name="style_and_state_id"]').val('');

    $('#style_and_state_modal input[name="style_name"]').val('');
    $('#style_and_state_modal input[name="button_color"]').val('');
    $('#style_and_state_modal textarea[name="description"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_style_and_state(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#style_and_state_modal input[name="style_and_state_id"]').val(id);

    $('#style_and_state_modal input[name="style_name"]').val($(invoker).data('style_name'));
    $('#style_and_state_modal input[name="button_color"]').val($(invoker).data('button_color'));

    $('#style_and_state_modal textarea[name="description"]').val($(invoker).data('description'));

    $('#style_and_state_modal').modal('show');

}

function new_logistics_service() {
    "use strict";
    $('#logistics_service_modal').modal('show'); 

    $('#logistics_service_modal input[name="logistics_service_id"]').val('');

    $('#logistics_service_modal input[name="logistics_service_name"]').val('');
    $('#logistics_service_modal input[name="description"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_logistics_service(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#logistics_service_modal input[name="logistics_service_id"]').val(id);

    $('#logistics_service_modal input[name="logistics_service_name"]').val($(invoker).data('logistics_service_name'));
    $('#logistics_service_modal textarea[name="description"]').val($(invoker).data('description'));

    $('#logistics_service_modal').modal('show');

}

function new_country() {
    "use strict";
    $('#country_modal').modal('show'); 

    $('#country_modal input[name="country_id"]').val('');

    $('#country_modal input[name="country_name"]').val('');
    $('#country_modal input[name="iso_code"]').val('');
    $('#country_modal input[name="phone_code"]').val('');
    $('#country_modal input[name="capital"]').val('');
    $('#country_modal input[name="region"]').val('');
    $('#country_modal select[name="currency_id"]').val('').change();
    $('#country_modal textarea[name="address"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_country(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#country_modal input[name="country_id"]').val(id);

    $('#country_modal input[name="country_name"]').val($(invoker).data('country_name'));
    $('#country_modal input[name="iso_code"]').val($(invoker).data('iso_code'));
    $('#country_modal input[name="phone_code"]').val($(invoker).data('phone_code'));
    $('#country_modal input[name="capital"]').val($(invoker).data('capital'));
    $('#country_modal input[name="region"]').val($(invoker).data('region'));

    $('#country_modal select[name="currency_id"]').val($(invoker).data('currency_id')).change();
    $('#country_modal textarea[name="address"]').val($(invoker).data('address'));

    $('#country_modal').modal('show');

}

function new_state() {
    "use strict";
    $('#state_modal').modal('show'); 

    $('#state_modal input[name="state_id"]').val('');

    $('#state_modal input[name="state_name"]').val('');
    $('#state_modal input[name="iso_code"]').val('');
    $('#state_modal select[name="country"]').val('').change();
    $('#state_modal textarea[name="address"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_state(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#state_modal input[name="state_id"]').val(id);

    $('#state_modal input[name="state_name"]').val($(invoker).data('state_name'));
    $('#state_modal input[name="iso_code"]').val($(invoker).data('iso_code'));
    $('#state_modal select[name="country"]').val($(invoker).data('country')).change();


    $('#state_modal').modal('show');

}

function new_city() {
    $('#city_modal').modal('show'); 

    $('#city_modal input[name="city_id"]').val('');

    $('#city_modal input[name="city_name"]').val('');
    $('#city_modal input[name="iso_code"]').val('');
    $('#city_modal select[name="country"]').val('').change();
    $('#city_modal select[name="state"]').val('').change();
    $('#city_modal textarea[name="address"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_city(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');

    state_select = $(invoker).data('state');
    $('#city_modal input[name="city_id"]').val(id);

    $('#city_modal input[name="city_name"]').val($(invoker).data('city_name'));
    $('#city_modal input[name="iso_code"]').val($(invoker).data('iso_code'));
    $('#city_modal select[name="country"]').val($(invoker).data('country')).change();

    

    $('#city_modal').modal('show');

}

function new_shipping_rates_list() {
    "use strict";
    $('#shipping_rates_list_modal').modal('show'); 

    $('#shipping_rates_list_modal input[name="shipping_rates_list_id"]').val('');

    $('#shipping_rates_list_modal input[name="start_weight_range"]').val('');
    $('#shipping_rates_list_modal input[name="end_weight_range"]').val('');
    $('#shipping_rates_list_modal input[name="rate_price"]').val('');
    $('#shipping_rates_list_modal select[name="country"]').val('').change();
    $('#shipping_rates_list_modal select[name="origin"]').val('').change();

     $('#shipping_rates_list_modal select[name="city"]').val('').change();
    $('#shipping_rates_list_modal select[name="state"]').val('').change();

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_shipping_rates_list(invoker,id) {

    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');

    state_rate_select = $(invoker).data('state');
    city_select = $(invoker).data('city');
    $('#shipping_rates_list_modal input[name="shipping_rates_list_id"]').val(id);

    $('#shipping_rates_list_modal input[name="start_weight_range"]').val($(invoker).data('start_weight_range'));
    $('#shipping_rates_list_modal input[name="end_weight_range"]').val($(invoker).data('end_weight_range'));
     $('#shipping_rates_list_modal input[name="rate_price"]').val($(invoker).data('rate_price'));
    $('#shipping_rates_list_modal select[name="country"]').val($(invoker).data('country')).change();
    $('#shipping_rates_list_modal select[name="origin"]').val($(invoker).data('origin')).change();
    

    $('#shipping_rates_list_modal').modal('show');

}

function new_payment_term() {
    "use strict";
    $('#payment_term_modal').modal('show'); 

    $('#payment_term_modal input[name="payment_term_id"]').val('');

    $('#payment_term_modal input[name="name"]').val('');
    $('#payment_term_modal input[name="days"]').val('');

    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');
}   

function edit_payment_term(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');


    $('#payment_term_modal input[name="payment_term_id"]').val(id);

    $('#payment_term_modal input[name="name"]').val($(invoker).data('name'));
    $('#payment_term_modal input[name="days"]').val($(invoker).data('days'));

    $('#payment_term_modal').modal('show');

}
</script>
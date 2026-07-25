<script>

var state_select = '';
var state_rate_select = '';
var city_select = '';



(function($) {
"use strict";


$('#address_book_modal select[name="country"]').on('change', function(){

    var country = $(this).val();
    if(country != '' && country != null && country != undefined){
        $.post(admin_url + "logistic/get_state_by_country/"+country).done(function (response) {
            response = JSON.parse(response);
            $('#address_book_modal select[name="state"]').html(response.html);

            $('#address_book_modal select[name="state"]').selectpicker("refresh");
            init_selectpicker();

            $('#address_book_modal select[name="state"]').val(state_rate_select).change();

        });
    }

});

$('#address_book_modal select[name="state"]').on('change', function(){

    var state = $(this).val();
    if(state != '' && state != null && state != undefined){
        $.post(admin_url + "logistic/get_city_by_state/"+state).done(function (response) {
            response = JSON.parse(response);
            $('#address_book_modal select[name="city"]').html(response.html);

            $('#address_book_modal select[name="city"]').selectpicker("refresh");
            init_selectpicker();

            $('#address_book_modal select[name="city"]').val(city_select).change();

        });
    }

});


})(jQuery);

function add_address_book(){
	"use strict";

	$('#address_book_modal .edit-title').addClass('hide');
	$('#address_book_modal .add-title').removeClass('hide');
	$('#address_book_modal').modal('show');

	$('#address_book_modal input[name="address_book_id"]').val('');
	$('#address_book_modal input[name="zip_code"]').val('');
	$('#address_book_modal textarea[name="address"]').val('');
	$('#address_book_modal select[name="country"]').val('').change();
	$('#address_book_modal select[name="state"]').val('').change();
	$('#address_book_modal select[name="city"]').val('').change();
}


function edit_address(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');

    state_rate_select = $(invoker).data('state');
    city_select = $(invoker).data('city');
    $('#address_book_modal input[name="address_book_id"]').val(id);

    $('#address_book_modal textarea[name="address"]').val($(invoker).data('address'));
    $('#address_book_modal input[name="zip_code"]').val($(invoker).data('zip_code'));
    $('#address_book_modal select[name="country"]').val($(invoker).data('country')).change();
    

    $('#address_book_modal').modal('show');

}	

</script>
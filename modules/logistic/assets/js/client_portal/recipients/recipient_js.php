<script>
var address_key = $('#address_list').children().length;

function country_change(invoker) {
    "use strict";
    var key = $(invoker).data('key');
    var country = $(invoker).val();
    if(country != '' && country != null && country != undefined){
        $.post(site_url + "logistic/client/get_state_by_country/"+country).done(function (response) {
            response = JSON.parse(response);
            $('select[name="address['+key+'][state]"]').html(response.html);
            $('select[name="address_update['+key+'][state]"]').html(response.html);


            $('select[name="address['+key+'][state]"]').selectpicker("refresh");
            $('select[name="address_update['+key+'][state]"]').selectpicker("refresh");
            init_selectpicker();

         

        });
    }

}

function state_change(invoker){
    "use strict";
    var state = $(invoker).val();
     var key = $(invoker).data('key');
    if(state != '' && state != null && state != undefined){
        $.post(site_url + "logistic/client/get_city_by_state/"+state).done(function (response) {
            response = JSON.parse(response);
            $('select[name="address['+key+'][city]"]').html(response.html);

            $('select[name="address['+key+'][city]"]').selectpicker("refresh");
            init_selectpicker();

       

        });
    }

}


function add_address() {
	"use strict";
	$.post(site_url + 'logistic/client/add_address_row/'+ address_key).done(function (response) {
        response = JSON.parse(response);

        $('#address_list').append(response.html);

        init_selectpicker();
        address_key++;
    });
}

function remove_address(key, invoker){
	"use strict";
	var id_remove = $(invoker).data('address_id');

	$('#address_row_'+key).remove();
	if(id_remove != ''){
		 $('#remove_address_ids').append(hidden_input('removed_address_ids[]', id_remove));
	}

	calculate_package();
}	


// Init select picker
function init_selectpicker() {
	"use strict";
  appSelectPicker();
}

function appSelectPicker(element) {
	"use strict";
  if (typeof element == "undefined") {
    element = $("body").find("select.selectpicker");
  }

  if (element.length) {
    element.selectpicker({
      showSubtext: true,
    });
  }
}


function remove_address(key, invoker){
    "use strict";
	var id_remove = $(invoker).data('address_id');

	$('#address_row_'+key).remove();
	if(id_remove != ''){
		 $('#remove_address_ids').append(hidden_input('remove_recipient_address_ids[]', id_remove));
	}

	calculate_package();
}

</script>
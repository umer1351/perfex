<script>
var consolidation_key = $('#consolidation_list_info').children().length;
var customer_address = '<?php echo (isset($consolidation) ? $consolidation->customer_address : ''); ?>';
var recipient_id = '<?php echo (isset($consolidation) ? $consolidation->recipient_id : ''); ?>';
var recipient_address_id = '<?php echo (isset($consolidation) ? $consolidation->recipient_address_id : ''); ?>';
(function($) {
"use strict";

$('select[name="rel_type"]').on('change', function(){
	var rel_type = $(this).val();
	
	load_packages();
	

});

$('#prefix_by_country_code').on('click', function(){
	if ($('#prefix_by_country_code').is(':checked')) {

		$('input[name="shipping_prefix"]').addClass('hide');
		$('#country_code_select').removeClass('hide');
		$('select[name="country_code"]').attr('required', 'true');

		$('input[name="shipping_prefix"]').val($('select[name="country_code"]').val());
	}else{
		$('input[name="shipping_prefix"]').val('<?php echo e(get_option('lg_internet_shopping_prefix')); ?>')

		$('input[name="shipping_prefix"]').removeClass('hide');
		$('#country_code_select').addClass('hide');

	}

});


$('select[name="country_code"]').on('change', function(){
	if($(this).val() != ''){
		$('input[name="shipping_prefix"]').val($(this).val());
	}

});

$('select[name="customer_id"]').on('change', function(){

	var client_id = $(this).val();
	if(client_id != ''){
		$.post(admin_url + 'logistic/get_client_address_option/'+ client_id).done(function (response) { 
			response = JSON.parse(response);
			$('select[name="customer_address"]').html(response.html);
			$('select[name="customer_address"]').selectpicker('refresh');

			$('select[name="recipient_id"]').html(response.recipient_html);
			$('select[name="recipient_id"]').selectpicker('refresh');

			$('select[name="invoice_id"]').html(response.invoice_html);
			$('select[name="invoice_id"]').selectpicker('refresh');

		});
	}else{
		$('select[name="customer_address"]').html('');
		$('select[name="customer_address"]').selectpicker('refresh');

		$('select[name="invoice_id"]').html('');
		$('select[name="invoice_id"]').selectpicker('refresh');
	}

	load_packages();

	
});

$('select[name="rel_id[]"]').on('change', function(){
	var rel_ids = $(this).val();

	if(rel_ids.length <= 1){
		$('#submit_consolidation').attr('disabled', 'true');
		alert_float('warning', '<?php echo _l('select_at_least_2_packages_to_consolidate'); ?>')
	}else{
		$('#submit_consolidation').removeAttr('disabled', 'true');
		load_package_row();
	}
	
});


$('select[name="recipient_id"]').on('change', function(){

	var recipient_id = $(this).val();
	if(recipient_id != ''){
		$.post(admin_url + 'logistic/get_client_recipient_address/'+ recipient_id).done(function (response) { 
			response = JSON.parse(response);
			$('select[name="recipient_address_id"]').html(response.html);
			$('select[name="recipient_address_id"]').selectpicker('refresh');
			$('select[name="recipient_address_id"]').val(recipient_address_id).change();
			

		});
	}else{
		$('select[name="recipient_address_id"]').html('');
		$('select[name="recipient_address_id"]').selectpicker('refresh');

	}

	
});


$("body").on('change', 'select[name="currency"]', function () {
  var currency_id = $(this).val();
  var client_id = $('select[name="customer_id"]').val();
  if(currency_id != ''){
    $.post(admin_url + 'logistic/get_currency_rate/'+currency_id).done(function(response){
      response = JSON.parse(response);
      if(response.currency_rate != 1){
       
        $('input[name="currency_rate"]').val(response.currency_rate).change();
        $('#convert_str').html(response.convert_str);
       
      }else{
        $('input[name="currency_rate"]').val(response.currency_rate).change();

        $('#convert_str').html(response.convert_str);
      }

    });

    if(client_id != ''){
	    $.post(admin_url + 'logistic/get_client_address_option/'+ client_id+'/'+currency_id).done(function (response) { 
				response = JSON.parse(response);
				$('select[name="invoice_id"]').html(response.invoice_html);
				$('select[name="invoice_id"]').selectpicker('refresh');
			});
	  }
  }else{
    alert_float('warning', "<?php echo _l('please_select_currency'); ?>" )
  }
  init_consolidation_currency();
  load_packages();
});


var client_id = $('select[name="customer_id"]').val();
var currency = $('select[name="currency"]').val();

if(client_id != ''){
	$.post(admin_url + 'logistic/get_client_address_option/'+ client_id+'/'+currency).done(function (response) { 
		response = JSON.parse(response);
		$('select[name="customer_address"]').html(response.html);
		$('select[name="customer_address"]').selectpicker('refresh');
		$('select[name="customer_address"]').val(customer_address).change();


		$('select[name="recipient_id"]').html(response.recipient_html);
		$('select[name="recipient_id"]').selectpicker('refresh');
		$('select[name="recipient_id"]').val(recipient_id).change();
	});
}else{
	$('select[name="customer_address"]').html('');
	$('select[name="customer_address"]').selectpicker('refresh');

	$('select[name="recipient_id"]').html('');
	$('select[name="recipient_id"]').selectpicker('refresh');
}



$('input[name="currency_rate"]').on('change', function(){
	var price_kg = $('input[name="price_kg"]').val();
	var rate = $(this).val();

	price_kg = price_kg*rate;

	$('input[name="price_kg"]').val(price_kg);
	calculate_consolidation();
});


calculate_consolidation();


})(jQuery);

function add_consolidation(){

	"use strict";
	$.post(admin_url + 'logistic/add_consolidation_row/'+ consolidation_key).done(function (response) {
        response = JSON.parse(response);

        $('#consolidation_list_info').append(response.html);
        consolidation_key++;
    });
}


function remove_consolidation(key, invoker){
	"use strict";
	var id_remove = $(invoker).data('package_id');

	$('#package_row_'+key).remove();
	if(id_remove != ''){
		 $('#remove_consolidation_ids').append(hidden_input('removed_package_detail_ids[]', id_remove));
	}

	calculate_consolidation();
}

function calculate_consolidation(){
	"use strict";
	var total_weight = 0;
	var weight_vol_total = 0;
	var fixed_charge_total = 0;
	var decvalue_total = 0;
	var subtotal = 0;
	var discount = 0;
	var shipping_insurance = 0;
	var custom_duties = 0;
	var tax = 0;
	var declared_value = 0;
	var total = 0;
	var price_kg = 0;
	var total_final_weight = 0;

	var rows = $("#consolidation_list_info div.consolidation_info");
	var volume_percentage = $('input[name="volume_percentage_setting"]').val();
	
	var discount_percent = $('input[name="discount_percent"]').val();
	var value_assured = $('input[name="value_assured"]').val();
	var shipping_insurance_percent = $('input[name="shipping_insurance_percent"]').val();
	var custom_duties_percent = $('input[name="custom_duties_percent"]').val();

	var tax_percent = $('input[name="tax_percent"]').val();
	var min_total_apply_tax = $('input[name="minium_cost_to_apply_the_tax_setting"]').val();

	var min_total_apply_declared_tax = $('input[name="minium_cost_to_apply_declared_tax_setting"]').val();
	var declared_value_percent = $('input[name="declared_value_percent"]').val();
	var currency_rate = $('input[name="currency_rate"]').val();

	var weight_value_setting = $('input[name="price_kg"]').val();

	var reissue = $('input[name="reissue"]').val();

	currency_rate = parseFloat(currency_rate);

	min_total_apply_tax = parseFloat(min_total_apply_tax) * currency_rate;
	min_total_apply_declared_tax = parseFloat(min_total_apply_declared_tax) * currency_rate;
	price_kg = parseFloat(weight_value_setting);
		
	$('input[name="minium_cost_to_apply_declared_tax_setting"]').val(min_total_apply_declared_tax);

	var weight_and_weight_vol_total = 0;

	$.each(rows, function () {

		var weight = $(this).find(".weight").val();
		var fixed_charge = $(this).find(".fixed_charge").val();
		var decvalue = $(this).find(".decvalue").val();
		var width = $(this).find(".width").val();
		var length = $(this).find(".length").val();
		var height = $(this).find(".height").val();

		var weight_vol = 0;
		var final_row_weight = 0;

		if(width != '' && length != '' && height != ''){
			weight_vol = (parseFloat(width) * parseFloat(length) * parseFloat(height) )/parseFloat(volume_percentage);			
		}

		$(this).find(".weight_vol").val(accounting.toFixed(parseFloat(weight_vol), app.options.decimal_places));

		if(weight != ''){
			total_weight += parseFloat(weight);
		}else{
			weight = 0;
		}

		if(fixed_charge != ''){
			fixed_charge_total += parseFloat(fixed_charge);
		}

		if(decvalue != ''){
			decvalue_total += parseFloat(decvalue);
		}

		weight_vol_total += weight_vol;

		if(weight_vol >= parseFloat(weight)){
			final_row_weight = weight_vol;
		}else{
			final_row_weight = parseFloat(weight);
		}

		total_final_weight += final_row_weight;

	});

	weight_and_weight_vol_total = parseFloat(total_weight)+parseFloat(weight_vol_total);

	subtotal = parseFloat(total_final_weight)*parseFloat(price_kg);
	if(discount_percent == ''){
	 	discount_percent = 0;
	}
	discount = (parseFloat(discount_percent) * subtotal)/100;

	if(value_assured == ''){
		value_assured = 0;
	}

	if(shipping_insurance_percent == ''){
		shipping_insurance_percent = 0;
	}

	if(custom_duties_percent == ''){
		custom_duties_percent = 0;
	}

	if(tax_percent == ''){
		tax_percent = 0;
	}

	if(reissue == ''){
		reissue = 0;
	}

	if(declared_value_percent == ''){
		declared_value_percent = 0;
	}

	if(subtotal > min_total_apply_tax){
		tax = tax_percent*subtotal/100;
	}

	if(decvalue_total > min_total_apply_declared_tax){
		declared_value = declared_value_percent*decvalue_total/100;
	}

	custom_duties = (weight_and_weight_vol_total * custom_duties_percent)/100;

	shipping_insurance = (parseFloat(value_assured) * parseFloat(shipping_insurance_percent))/100;

	total = subtotal + parseFloat(shipping_insurance) + parseFloat(custom_duties) + parseFloat(tax) + declared_value + parseFloat(reissue) + parseFloat(fixed_charge_total) - parseFloat(discount);

	$('input[name="price_kg"]').val(accounting.toFixed(price_kg, app.options.decimal_places));

	$('#fixed_charge_label').html(format_money(fixed_charge_total));
	$('input[name="fixed_charge"]').val(accounting.toFixed(fixed_charge_total, app.options.decimal_places));

	$('#total_label').html(format_money(total));
	$('input[name="total"]').val(accounting.toFixed(total, app.options.decimal_places));

	$('#tax_value').html(format_money(tax));
	$('input[name="tax"]').val(accounting.toFixed(tax, app.options.decimal_places));

	$('#declared_value_label').html(format_money(declared_value));
	$('input[name="declared_value"]').val(accounting.toFixed(declared_value, app.options.decimal_places));

	$('input[name="price_kg"]').val(accounting.toFixed(price_kg, app.options.decimal_places));

	$('#custom_duties_value').html(format_money(custom_duties));
	$('input[name="custom_duties"]').val(accounting.toFixed(custom_duties, app.options.decimal_places));
	$('#shipping_insurance_value').html(format_money(shipping_insurance));
	$('input[name="shipping_insurance"]').val(accounting.toFixed(shipping_insurance, app.options.decimal_places));
	$('#discount_value').html(format_money(discount));
	$('input[name="discount"]').val(accounting.toFixed(discount, app.options.decimal_places));
	$('input[name="subtotal"]').val(accounting.toFixed(subtotal, app.options.decimal_places));
	$('#subtotal_consolidation').html(format_money(subtotal));
	$('#total_weight').html( accounting.toFixed(total_weight, app.options.decimal_places));
	$('#weight_vol_total').html(accounting.toFixed(weight_vol_total, app.options.decimal_places) );
	$('#fixed_charge_total').html(accounting.toFixed(fixed_charge_total, app.options.decimal_places));
	$('#decvalue_total').html(accounting.toFixed(decvalue_total, app.options.decimal_places));
}

function init_consolidation_currency(id, callback) {
	"use strict";
	
    var $accountingTemplate = $("body").find('.accounting-template');

    if ($accountingTemplate.length || id) {
        var selectedCurrencyId = !id ? $accountingTemplate.find('select[name="currency"]').val() : id;

        requestGetJSON('misc/get_currency/' + selectedCurrencyId)
            .done(function (currency) {
                // Used for formatting money
                accounting.settings.currency.decimal = currency.decimal_separator;
                accounting.settings.currency.thousand = currency.thousand_separator;
                accounting.settings.currency.symbol = currency.symbol;
                accounting.settings.currency.format = currency.placement == 'after' ? '%v %s' : '%s%v';

                calculate_consolidation();

                if(callback) {
                    callback();
                }
            });
    }
}


//preview consolidation attachment
function preview_consolidation_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_consolidation_file(id, rel_id);
}

function view_consolidation_file(id, rel_id) {
  "use strict"; 
      $('#consolidation_file_data').empty();
      $("#consolidation_file_data").load(admin_url + 'logistic/file_consolidation/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}

function delete_consolidation_attachment(id) {
  "use strict"; 
    if (confirm_delete()) {
        requestGet('logistic/delete_consolidation_attachment/' + id).done(function(success) {
            
                $("#consolidation_pv_file").find('[data-attachment-id="' + id + '"]').remove();
            
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
  }

function load_packages(){
	"use strict";
		var rel_type = $('select[name="rel_type"]').val();
		var customer_id = $('select[name="customer_id"]').val();
		var currency_id = $('select[name="currency"]').val();
		var data = {};
		data.rel_type = rel_type;
		data.customer_id = customer_id;
		data.currency_id = currency_id;

		$.post(admin_url + 'logistic/load_packages_for_consolidation', data).done(function (response) { 
			response = JSON.parse(response);
			$('select[name="rel_id[]"]').html(response.html);
			$('select[name="rel_id[]"]').selectpicker('refresh');

		});

}

/**
 * [load_package_row description]
 * @return {[type]} [description]
 */
function load_package_row(){
	"use strict";
	var data = {};
	var rel_ids = $('select[name="rel_id[]"]').val();
	var rel_type = $('select[name="rel_type"]').val();
	data.rel_ids = rel_ids;
	data.rel_type = rel_type;
	$.post(admin_url + 'logistic/load_package_row_for_consolidation', data).done(function (response) { 
		response = JSON.parse(response);
		$('#consolidation_list_info').html(response.html);
		setTimeout(function () {
          calculate_consolidation();
        }, 300);
			
	});

}

</script>
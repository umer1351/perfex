<script>
var shipment_key = $('#shipment_list_info').children().length;
var customer_address = '<?php echo (isset($shipment) ? $shipment->customer_address : ''); ?>';
var recipient_id = '<?php echo (isset($shipment) ? $shipment->recipient_id : ''); ?>';
var recipient_address_id = '<?php echo (isset($shipment) ? $shipment->recipient_address_id : ''); ?>';

var addMoreAttachmentsInputKey = 1;
(function($) {
"use strict";

  $("body").on("click", ".add_more_attachments_shipping", function () {
    if ($(this).hasClass("disabled")) {
      return false;
    }

    var total_attachments = $('.attachments input[name*="attachments"]').length;
    if ($(this).data("max") && total_attachments >= $(this).data("max")) {
      return false;
    }

    var newattachment = $(".attachments")
      .find(".attachment")
      .eq(0)
      .clone()
      .appendTo(".attachments");
    newattachment.find("input").removeAttr("aria-describedby aria-invalid");
    newattachment
      .find("input")
      .attr("name", "attachments[" + addMoreAttachmentsInputKey + "]")
      .val("");
    newattachment
      .find(
        $.fn.appFormValidator.internal_options.error_element + '[id*="error"]'
      )
      .remove();
    newattachment
      .find("." + $.fn.appFormValidator.internal_options.field_wrapper_class)
      .removeClass(
        $.fn.appFormValidator.internal_options.field_wrapper_error_class
      );
    newattachment.find("i").removeClass("fa-plus").addClass("fa-minus");
    newattachment
      .find("button")
      .removeClass("add_more_attachments_shipping")
      .addClass("remove_attachment")
      .removeClass("btn-success")
      .removeClass("btn-default")
      .addClass("btn-danger");
    addMoreAttachmentsInputKey++;
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
		$.post(site_url + 'logistic/client/get_client_address_option/'+ client_id).done(function (response) { 
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

	
});

$('select[name="recipient_id"]').on('change', function(){

	var recipient_id = $(this).val();
	if(recipient_id != ''){
		$.post(site_url + 'logistic/client/get_client_recipient_address/'+ recipient_id).done(function (response) { 
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


$('select[name="invoice_id"]').on('change', function(){
	 var invoice_id = $(this).val();
	 if(invoice_id != ''){
	 	$.post(site_url + 'logistic/client/get_package_info_by_invoice/'+ invoice_id).done(function (response) { 
			response = JSON.parse(response);

	 		$('#shipment_list_info').html(response.html);

	 		shipment_key = $('#shipment_list_info').children().length;
	 	});
	 }

});

$("body").on('change', 'select[name="currency"]', function () {
  var currency_id = $(this).val();
  var client_id = $('select[name="customer_id"]').val();
  if(currency_id != ''){
    $.post(site_url + 'logistic/client/get_currency_rate/'+currency_id).done(function(response){
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
	    $.post(site_url + 'logistic/client/get_client_address_option/'+ client_id+'/'+currency_id).done(function (response) { 
				response = JSON.parse(response);
				$('select[name="invoice_id"]').html(response.invoice_html);
				$('select[name="invoice_id"]').selectpicker('refresh');
			});
	  }
  }else{
    alert_float('warning', "<?php echo _l('please_select_currency'); ?>" )
  }
  init_shipment_currency();
});


var client_id = $('select[name="customer_id"]').val();
var currency = $('select[name="currency"]').val();

if(client_id != ''){
	$.post(site_url + 'logistic/client/get_client_address_option/'+ client_id+'/'+currency).done(function (response) { 
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
	calculate_shipment();
});


calculate_shipment();


})(jQuery);

function add_shipment(){

	
	$.post(site_url + 'logistic/client/add_shipment_row/'+ shipment_key).done(function (response) {
        response = JSON.parse(response);

        $('#shipment_list_info').append(response.html);
        shipment_key++;
    });
}


function remove_shipment(key, invoker){

	var id_remove = $(invoker).data('package_id');

	$('#package_row_'+key).remove();
	if(id_remove != ''){
		 $('#remove_shipment_ids').append(hidden_input('removed_package_detail_ids[]', id_remove));
	}

	calculate_shipment();
}

function calculate_shipment(){
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

	var rows = $("#shipment_list_info div.shipment_info");
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
	$('#subtotal_shipment').html(format_money(subtotal));
	$('#total_weight').html( accounting.toFixed(total_weight, app.options.decimal_places));
	$('#weight_vol_total').html(accounting.toFixed(weight_vol_total, app.options.decimal_places) );
	$('#fixed_charge_total').html(accounting.toFixed(fixed_charge_total, app.options.decimal_places));
	$('#decvalue_total').html(accounting.toFixed(decvalue_total, app.options.decimal_places));
}

function init_shipment_currency(id, callback) {
	"use strict";
	
    var $accountingTemplate = $("body").find('.accounting-template');

    if ($accountingTemplate.length || id) {
        var selectedCurrencyId = !id ? $accountingTemplate.find('select[name="currency"]').val() : id;

        $.get(site_url+'logistic/client/get_currency/' + selectedCurrencyId)
            .done(function (currency) {
                // Used for formatting money
                accounting.settings.currency.decimal = currency.decimal_separator;
                accounting.settings.currency.thousand = currency.thousand_separator;
                accounting.settings.currency.symbol = currency.symbol;
                accounting.settings.currency.format = currency.placement == 'after' ? '%v %s' : '%s%v';

                calculate_shipment();

                if(callback) {
                    callback();
                }
            });
    }
}


//preview shipment attachment
function preview_shipment_btn(invoker){
  "use strict"; 
    var id = $(invoker).attr('id');
    var rel_id = $(invoker).attr('rel_id');
    view_shipment_file(id, rel_id);
}

function view_shipment_file(id, rel_id) {
  "use strict"; 
      $('#shipment_file_data').empty();
      $("#shipment_file_data").load(site_url + 'logistic/client/file_shipment/' + id + '/' + rel_id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
}
function close_modal_preview(){
  "use strict"; 
 $('._project_file').modal('hide');
}

function delete_shipment_attachment(id) {
  "use strict"; 
    if (confirm_delete()) {
        $.get(site_url+'logistic/client/delete_shipment_attachment/' + id).done(function(success) {
            
                $("#shipment_pv_file").find('[data-attachment-id="' + id + '"]').remove();
            
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
  }

 // Format money function
function format_money(total, excludeSymbol) {
  if (typeof excludeSymbol != "undefined" && excludeSymbol) {
    return accounting.formatMoney(total, {
      symbol: "",
    });
  }

  return accounting.formatMoney(total);
}

</script>
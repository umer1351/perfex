<script type="text/javascript">
	(function($) {
		"use strict"; 

		init_datepicker();
		appValidateForm($("body").find('#add_goods_delivery_warranty'), {
			'warranty_period': 'required',
		}); 

	})(jQuery);
</script>
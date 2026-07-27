<div class="consolidation_info" id="package_row_<?php echo e($key+1); ?>">
	<div class="col-md-1 pad_right_0">
		<?php $package_amount = (isset($amount) ? $amount : '' );
		echo render_input('package_information['.($key+1).'][amount]', 'lg_amount', $package_amount, 'number' , ['onchange' => 'calculate_consolidation(); return false;'], [], '', 'amount'); ?>
	</div>

	<div class="col-md-3 pad_right_0">
		<?php $description = (isset($package_description) ? $package_description : '' );
		echo render_input('package_information['.($key+1).'][package_description]', 'lg_package_description', $description, 'text'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_weight = (isset($weight) ? $weight : '' );
		echo render_input('package_information['.($key+1).'][weight]', 'lg_weight', $package_weight, 'number', ['onchange' => 'calculate_consolidation(); return false;'], [], '', 'weight'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_length = (isset($length) ? $length : '' );
		echo render_input('package_information['.($key+1).'][length]', 'lg_length', $package_length, 'number', ['onchange' => 'calculate_consolidation(); return false;'], [], '', 'length'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_width = (isset($width) ? $width : '' );
		echo render_input('package_information['.($key+1).'][width]', 'lg_width', $package_width, 'number', ['onchange' => 'calculate_consolidation(); return false;'], [], '', 'width'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_height = (isset($height) ? $height : '' );
		echo render_input('package_information['.($key+1).'][height]', 'lg_height', $package_height, 'number', ['onchange' => 'calculate_consolidation(); return false;'], [], '', 'height'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_weight_vol = (isset($weight_vol) ? $weight_vol : '' );
		echo render_input('package_information['.($key+1).'][weight_vol]', 'lg_weight_vol', $package_weight_vol, 'number', ['onchange' => 'calculate_consolidation(); return false;', 'readonly' => 'true'], [], '', 'weight_vol'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_fixed_charge = (isset($fixed_charge) ? $fixed_charge : '' );
		echo render_input('package_information['.($key+1).'][fixed_charge]', 'lg_fixed_charge', $package_fixed_charge, 'number', ['onchange' => 'calculate_consolidation(); return false;'] , [], '', 'fixed_charge'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php $package_decvalue = (isset($dec_value) ? $dec_value : '' );
		echo render_input('package_information['.($key+1).'][dec_value]', 'lg_dec_value', $package_decvalue, 'number', ['onchange' => 'calculate_consolidation(); return false;'] , [], '', 'decvalue'); ?>
	</div>


	<div class="col-md-12"><hr class="mtop5"></div>

</div>
<div class="package_info" id="package_row_<?php echo e($key+1); ?>">
	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][amount]', 'lg_amount', '', 'number' , ['onchange' => 'calculate_package(); return false;'], [], '', 'amount'); ?>
	</div>

	<div class="col-md-3 pad_right_0">
		<?php $description = (isset($default_description) ? $default_description : '' );
		echo render_input('package_information['.($key+1).'][package_description]', 'lg_package_description', $description, 'text'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][weight]', 'lg_weight', '', 'number', ['onchange' => 'calculate_package(); return false;'], [], '', 'weight'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][length]', 'lg_length', '', 'number', ['onchange' => 'calculate_package(); return false;'], [], '', 'length'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][width]', 'lg_width', '', 'number', ['onchange' => 'calculate_package(); return false;'], [], '', 'width'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][height]', 'lg_height', '', 'number', ['onchange' => 'calculate_package(); return false;'], [], '', 'height'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][weight_vol]', 'lg_weight_vol', '', 'number', ['onchange' => 'calculate_package(); return false;', 'readonly' => 'true'], [], '', 'weight_vol'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][fixed_charge]', 'lg_fixed_charge', '', 'number', ['onchange' => 'calculate_package(); return false;'] , [], '', 'fixed_charge'); ?>
	</div>

	<div class="col-md-1 pad_right_0">
		<?php
		echo render_input('package_information['.($key+1).'][dec_value]', 'lg_dec_value', '', 'number', ['onchange' => 'calculate_package(); return false;'] , [], '', 'decvalue'); ?>
	</div>

	<div class="col-md-1">
		<a onclick="remove_package(<?php echo e($key+1); ?>, this);" class="btn btn-danger mtop25 pull-right" data-package_id=""><i class="fa fa-trash"></i></a>
	</div>

	<div class="col-md-12"><hr class="mtop5"></div>

</div>
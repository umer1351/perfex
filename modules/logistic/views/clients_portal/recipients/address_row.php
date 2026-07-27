 
<div class="address_info" id="address_row_<?php echo e($key+1); ?>">
    <div class="col-md-12">
        <hr>
    </div>

     <div class="col-md-4">
        <label for="name"><span class="text-danger">* </span><?php echo _l('lg_country'); ?></label>        
        <?php
             $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => ($key+1), 'onchange' => 'country_change(this); return false;'];
             $selected = '';

             echo render_select('address['.($key+1).'][country]', $countries, ['id', 'country_name'], '', $selected, $s_attrs ,[], '', 'country_select');
             ?>  
    </div>   

    <div class="col-md-4">
        <label for="name"><span class="text-danger">* </span><?php echo _l('lg_state'); ?></label>         
        <?php
            $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => ($key+1), 'onchange' => 'state_change(this); return false;'];
             echo render_select('address['.($key+1).'][state]', $states, ['id', 'state_name'], '', $selected, $s_attrs ,[], '', 'state_select');
             ?>
    </div>

    <div class="col-md-4">
        <label for="name"><span class="text-danger">* </span><?php echo _l('lg_city'); ?></label>       
        <?php
            $s_attrs  = ['data-none-selected-text' => _l('system_default_string'), 'required' => 'true', 'data-key' => ($key+1)];
             echo render_select('address['.($key+1).'][city]', $cities, ['id', 'city_name'], '', $selected, $s_attrs ,[], '', 'city_select');
             ?>
    </div>
    <div class="col-md-5">
        <?php echo render_input('address['.($key+1).'][zip_code]', 'lg_zip_code', '', 'text'); ?>
    </div>
    <div class="col-md-6">
        <?php echo render_input('address['.($key+1).'][address]', 'lg_address', '', 'text'); ?>
    </div>

    <div class="col-md-1">
        <a onclick="remove_address(<?php echo e($key+1); ?>, this);" class="btn btn-danger mtop25 pull-right" data-address_id=""><i class="fa fa-trash"></i></a>
    </div>

    <div class="col-md-12"><hr></div>

</div>
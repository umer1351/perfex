<?php
defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('after_email_templates', 'add_logistic_email_templates');

if(!class_exists('qrstr')){
    include_once('modules/logistic/assets/plugins/phpqrcode/qrlib.php');
}

/**
 * [lg_get_country_name_by_id description]
 * @return [type] [description]
 */
function lg_get_country_name_by_id($id){
	$CI = & get_instance();

	$CI->db->where('id', $id);
   	$country = $CI->db->get(db_prefix().'lg_countries')->row();
   	if(isset($country->country_name)){
   		return $country->country_name;
   	}
   	return '';
}

/**
 * [lg_get_state_name_by_id description]
 * @return [type] [description]
 */
function lg_get_state_name_by_id($id){
	$CI = & get_instance();

	$CI->db->where('id', $id);
   	$state = $CI->db->get(db_prefix().'lg_states')->row();
   	if(isset($state->state_name)){
   		return $state->state_name;
   	}
   	return '';
}

/**
 * [lg_get_city_name_by_id description]
 * @return [type] [description]
 */
function lg_get_city_name_by_id($id){
	$CI = & get_instance();

	$CI->db->where('id', $id);
   	$city = $CI->db->get(db_prefix().'lg_cities')->row();
   	if(isset($city->city_name)){
   		return $city->city_name;
   	}
   	return '';
}

/**
 * get_office_group_name_by_id
 */
function get_office_group_name_by_id($id){
	$CI = & get_instance();

	$CI->db->where('id', $id);
	$office_group = $CI->db->get(db_prefix().'lg_office_group')->row();
	if(isset($office_group->office_name)){
		return $office_group->office_name;
	}
	return _l('no_entries_found');

}

/**
 * { lg_html_entity_decode }
 *
 * @param      string  $str    The string
 *
 * @return     <string>  
 */
function lg_html_entity_decode($str){
    return html_entity_decode($str ?? '');
}

/**
 * [lg_get_countries description]
 * @return [type] [description]
 */
function lg_get_countries(){

	$CI = & get_instance();
	$CI->load->model('logistic/logistic_model');

	return $CI->logistic_model->get_logistics_countries('active = 1');
}

/**
 * 
 */

function lg_get_client_address_list($client_id){
	$CI = & get_instance();

	$CI->db->where('client_id', $client_id);
	return $CI->db->get(db_prefix().'lg_client_address')->result_array();
}

/**
 * [get_package_next_number description]
 * @return [type] [description]
 */
function get_package_next_number(){
	$CI = & get_instance();

	$number = '';
	$random_digits = get_option('lg_number_of_random_digits');
	$number_type = get_option('lg_tracking_number_type');

	if($number_type == 'auto_increment'){
		$CI->db->where('number_type', 'auto_increment');
		$CI->db->select('MAX(number) as number');
		$package = $CI->db->get(db_prefix().'lg_packages')->row();
		if(isset($package->number)){
			return ($package->number+1); 
		}
		return 1;

	}else if($number_type == 'random'){

		$number = lg_generateRandomString($random_digits);

		$CI->db->where('number_code', $number);
        $check_exist_number = $CI->db->get(db_prefix().'lg_packages')->row();

        while($check_exist_number) {
          
        	$number = lg_generateRandomString($random_digits);
	        $this->db->where('number_code',$number);
	        $check_exist_number = $CI->db->get(db_prefix().'lg_packages')->row();
        }
	}

	return $number;
}


/**
 * [generateRandomString description]
 * @param  [type] $n [description]
 * @return [type]    [description]
 */
function lg_generateRandomString($n) {
    
    if ($n < 1) {
        return null;
    }

    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $randomString .= rand(0, 9);
    }

    return $randomString;
}

/**
 * Purchase get currency name symbol
 * @param  [type] $id     
 * @param  string $column 
 * @return [type]         
 */
function lg_get_currency_name_symbol($id, $column='')
{
    $CI   = & get_instance();
    $currency_value='';

    if($column == ''){
        $column = 'name';
    }

    $CI->db->select($column);
    $CI->db->from(db_prefix() . 'currencies');
    $CI->db->where('id', $id);
    $currency = $CI->db->get()->row();
    if($currency){
        $currency_value = $currency->$column;
    }

    return $currency_value;
}

/**
 * get currency rate
 * @param  [type] $from
 * @param  [type] $to
 * @return [type]           
 */
function lg_get_currency_rate($from, $to)
{
    $CI   = & get_instance();
    if($from == $to){
        return 1;
    }

    $amount_after_convertion = 1;

    $CI->db->where('from_currency_name', strtoupper($from));
    $CI->db->where('to_currency_name', strtoupper($to));
    $currency_rates = $CI->db->get(db_prefix().'currency_rates')->row();
    
    if($currency_rates){
        $amount_after_convertion = $currency_rates->to_currency_rate;
    }

    return $amount_after_convertion;
}


/**
 * { pur get currency by id }
 *
 * @param        $id     The identifier
 */
function lg_get_currency_by_id($id){
    $CI   = & get_instance();

    $CI->db->where('id', $id);
    return  $CI->db->get(db_prefix().'currencies')->row();
}

/**
 * [handle_upload_lg_package_files description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function handle_upload_lg_package_files($id){
	$type = 'packages';
    
    $CI = &get_instance();
    $totalUploaded = 0;

    if(is_array($id)){
        
            if (isset($_FILES['attachments']['name'])
                && ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
                if (!is_array($_FILES['attachments']['name'])) {
                    $_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
                    $_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
                    $_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
                    $_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
                    $_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
                }

                _file_attachments_index_fix('attachments');
                for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

                    // Get the temp file path
                    $tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
                    // Make sure we have a filepath
                    if (!empty($tmpFilePath) && $tmpFilePath != '') {
                        if (_perfex_upload_error($_FILES['attachments']['error'][$i])
                            || !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
                            continue;
                        }

                        $pk_first_key_id = 0;
                        foreach($id as $key => $pk_id){
                            if($key == 0){
                                $pk_first_key_id = $pk_id;
                                $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_id . '/';
                                _maybe_create_upload_path($path);
                                $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                                $newFilePath = $path . $filename;
                                // Upload the file into the temp dir
              
                                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                                    $attachment = [];
                                    $attachment[] = [
                                        'file_name' => $filename,
                                        'filetype' => $_FILES['attachments']['type'][$i],
                                    ];

                                    $CI->misc_model->add_attachment_to_database($pk_id, 'lg_packages', $attachment);
                                    $totalUploaded++;
                                }
                            }else{
                                
                                $source_path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_first_key_id . '/';
                                $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_id . '/';
                                _maybe_create_upload_path($path);
                                $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                                if(copy($source_path.$filename, $path.$filename)){
                                    $attachment = [];
                                    $attachment[] = [
                                        'file_name' => $filename,
                                        'filetype' => $_FILES['attachments']['type'][$i],
                                    ];

                                    $CI->misc_model->add_attachment_to_database($pk_id, 'lg_packages', $attachment);
                                    $totalUploaded++;
                                }
                            }
                        }
                    }
                }
            }
        


    }else{
        $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $id . '/';
        if (isset($_FILES['attachments']['name'])
            && ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
            if (!is_array($_FILES['attachments']['name'])) {
                $_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
                $_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
                $_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
                $_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
                $_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
            }

            _file_attachments_index_fix('attachments');
            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

                // Get the temp file path
                $tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES['attachments']['error'][$i])
                        || !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
                        continue;
                    }

                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $attachment = [];
                        $attachment[] = [
                            'file_name' => $filename,
                            'filetype' => $_FILES['attachments']['type'][$i],
                        ];

                        $CI->misc_model->add_attachment_to_database($id, 'lg_packages', $attachment);
                        $totalUploaded++;
                    }
                }
            }
        }
    }

    return (bool) $totalUploaded;
}

/**
 * [lg_get_shipping_company_name ]
 * @return [type] []
 */
function lg_get_shipping_company_name($id){
    $CI = & get_instance();

    $CI->db->where('id', $id);
    $city = $CI->db->get(db_prefix().'lg_shipping_companies')->row();
    if(isset($city->shipping_company_name)){
        return $city->shipping_company_name;
    }
    return _l('no_entries_found');
}

/**
 * [lg_get_customer_address_str description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function lg_get_customer_address_str($id, $return_address = ''){
    $CI = & get_instance();

    $CI->db->where('id', $id);
    $address = $CI->db->get(db_prefix().'lg_client_address')->row();

    if($return_address == 1){
        if(isset($address->address)){
            return $address->address;
        }

        return '';
    }

    $str = '';
    if(isset($address->country)){
        $str .= lg_get_country_name_by_id($address->country);
    }

    if(isset($address->city)){
        $str .= ' - '.lg_get_city_name_by_id($address->city);
    }
    return $str;
}

/**
 * [lg_create_default_style_and_states description]
 * @return [type] [description]
 */
function lg_create_default_style_and_states(){
    $CI = & get_instance();

    $data = [];

    $status_data = [
        'rejected',
        'quotation',
        'picked_up',
        'pending',
        'no_picked_up',
        'invoiced',
        'delivered',
        'consolidate',
        'cancelled',
        'approved',
    ];

    $color_data = [
        '#FB8C00',
        '#00FFC4',
        '#3CB7F3',
        '#FFBC34',
        '#A3A3A3',
        '#68C251',
        '#7561EE',
        '#2962FF',
        '#EA4335',
        '#65993A',
    ];


    if(total_rows(db_prefix().'lg_style_and_states', ['is_default_status' => 1]) == 0){
        foreach($status_data as $key => $status){
            $data['style_name'] = $status;
            $data['button_color'] = $color_data[$key];
            $data['description'] = $status;
            $data['is_default_status'] = 1;

            $CI->db->insert(db_prefix().'lg_style_and_states', $data);
        }
    }
}

/**
 * [format_lg_package_status description]
 * @return [type] [description]
 */
function format_lg_package_status($status_id, $status_str = ''){
    if(!is_numeric($status_id)){
        return '';
    }

    $CI = & get_instance();

    $CI->db->where('id', $status_id);
    $status = $CI->db->get(db_prefix().'lg_style_and_states')->row();
    if($status){
        if($status_str == 1){
             if($status->is_default_status == 1){
                return _l('lg_'.$status->style_name);
            }else{
                return $status->style_name;
            }        
        }


        if($status->is_default_status == 1){
            return '<span class="label" style="background-color: '.$status->button_color.'">'._l('lg_'.$status->style_name).'</span>';
        }else{
            return '<span class="label" style="background-color: '.$status->button_color.'">'.$status->style_name.'</span>';
        }
    }   
    return ''; 

}

/**
 * [lg_format_invoice_status description]
 * @return [type] [description]
 */
function lg_format_invoice_status($invoice_id, $return_status = ''){

    $CI = & get_instance();

    if(!is_numeric($invoice_id) || $invoice_id == 0){
        return '';
    }

    $CI->db->select('status');
    $CI->db->where('id', $invoice_id);
    $invoice = $CI->db->get(db_prefix().'invoices')->row();
    if(isset($invoice->status)){

        if($return_status == 1){
            return $invoice->status;
        }

        return format_invoice_status($invoice->status);
    }
    return '';
}

/**
 * [lg_get_agency_name_by_id description]
 * @return [type] [description]
 */
function lg_get_agency_name_by_id($agency_id){
    $CI = & get_instance();
    $CI->db->where('id', $agency_id);
    $agency = $CI->db->get(db_prefix().'lg_agency_group')->row();

    if(isset($agency->agency_name)){
        return $agency->agency_name;
    }
    return _l('no_entries_found');
}


/**
 * [lg_get_agency_name_by_id description]
 * @return [type] [description]
 */
function lg_get_service_name_by_id($service_id){
    $CI = & get_instance();
    $CI->db->where('id', $service_id);
    $shipping = $CI->db->get(db_prefix().'lg_shipping_modes')->row();

    if(isset($shipping->shipping_mode_name)){
        return $shipping->shipping_mode_name;
    }
    return _l('no_entries_found');
}

/**
 * [lg_get_agency_name_by_id description]
 * @return [type] [description]
 */
function lg_get_package_type_name_by_id($type_id){
    $CI = & get_instance();
    $CI->db->where('id', $type_id);
    $type = $CI->db->get(db_prefix().'lg_type_of_packages')->row();

    if(isset($type->type_of_package_name)){
        return $type->type_of_package_name;
    }
    return _l('no_entries_found');
}

/**
 * [lg_get_delivery_time_name_by_id ]
 * @return [type] []
 */
function lg_get_delivery_time_name_by_id($id){
    $CI = & get_instance();

    $CI->db->where('id', $id);
    $time = $CI->db->get(db_prefix().'lg_shipping_times')->row();
    if(isset($time->shipping_time_name)){
        return $time->shipping_time_name;
    }
    return _l('no_entries_found');
}

/**
 * [lg_get_logistic_service_name_by_id ]
 * @return [type] []
 */
function lg_get_logistic_service_name_by_id($id){
    $CI = & get_instance();

    $CI->db->where('id', $id);
    $service = $CI->db->get(db_prefix().'lg_logistics_services')->row();
    if(isset($service->logistics_service_name)){
        return $service->logistics_service_name;
    }
    return _l('no_entries_found');
}

/**
 * [lg_get_user_role_str description]
 * @return [type] [description]
 */
function lg_get_user_role_str($staff_id){
    $CI = & get_instance();

    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get(db_prefix().'staff')->row();


    if(isset($staff->staffid)){

        if($staff->admin == 1){
            return _l('system_administrator');
        }

        if($staff->staff_type == 'driver'){ 
            return _l('lg_driver');
        }


        if(is_numeric($staff->role) && $staff->role > 0){
            return lg_get_role_name_by_id($staff->role);
        }

    }

    return '';
}

/**
 * [lg_get_role_name_by_id description]
 * @return [type] [description]
 */
function lg_get_role_name_by_id($role_id){
    $CI = & get_instance();

    $CI->db->where('roleid', $role_id);
    $role = $CI->db->get(db_prefix().'roles')->row();
    if(isset($role->name)){
        return $role->name;
    }
    return '';
}

/**
 * [lg_get_contact_primary_email description]
 * @return [type] [description]
 */
function lg_get_contact_primary_email($client_id, $return_str_null = ''){
    $contact_id = get_primary_contact_user_id($client_id);
    if($contact_id){
        $CI = & get_instance();

        $CI->db->select('email');
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->get(db_prefix().'contacts')->row();

        if(isset($contact->email)){
            return $contact->email;
        }
    }

    if($return_str_null != ''){
        return '';
    }
    return _l('no_entries_found');
}

/**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_getBarcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_BARCODE.$id);

        $barcodeobj = new TCPDFBarcode($sample ?? '', 'C128');
        $code = $barcodeobj->getBarcodeSVGcode(4, 150, 'black');
        file_put_contents(LOGISTIC_PRINT_BARCODE.$id.'/'.md5($sample).'.svg', $code);

        return true;
    }
}


/**
 * [get_client_next_locker_number description]
 * @return [type] [description]
 */
function get_client_next_locker_number(){
    $CI = & get_instance();

    $number = '';
    $random_digits = get_option('lg_locker_number_of_random_digits');
    $number_type = get_option('lg_virtual_locker_number_type');

    if($number_type == 'auto_increment'){
        $CI->db->where('locker_code_type', 'auto_increment');
        $CI->db->select('MAX(locker_code_number) as locker_code_number');
        $client = $CI->db->get(db_prefix().'clients')->row();
        if(isset($client->locker_code_number)){
            return ($client->locker_code_number+1); 
        }
        return 1;

    }else if($number_type == 'random'){

        $number = lg_generateRandomString($random_digits);

        $CI->db->where('locker_code', $number);
        $check_exist_number = $CI->db->get(db_prefix().'clients')->row();

        while($check_exist_number) {
          
            $number = lg_generateRandomString($random_digits);
            $this->db->where('locker_code',$number);
            $check_exist_number = $CI->db->get(db_prefix().'clients')->row();
        }
    }

    return $number;
}

/**
 * [update_locker_code_for_client description]
 * @return [type] [description]
 */
function update_locker_code_for_client($client_id){
    $CI = & get_instance();

    $CI->db->select('locker_code, locker_code_prefix, locker_code_number, locker_code_type');
    $CI->db->where('userid', $client_id);

    $client = $CI->db->get(db_prefix().'clients')->row();

    if($client->locker_code == '' || $client->locker_code == null ){
        $random_digits = get_option('lg_locker_number_of_random_digits');
        $number_type = get_option('lg_virtual_locker_number_type');

            $number = get_client_next_locker_number();
            $number_code = $number;
                  
            if($number_type == 'auto_increment'){
                $number_code = str_pad($number,get_option('lg_number_digits_to_track_locker_packages'),'0',STR_PAD_LEFT);
            }else{
                $number = 0;
            }

        $data['locker_code'] = $number_code;
        $data['locker_code_number'] = $number;
        $data['locker_code_prefix'] = get_option('lg_locker_prefix');
        $data['locker_code_type'] = $number_type;


        $CI->db->where('userid', $client_id);
        $CI->db->update(db_prefix().'clients', $data);
    }

}

/**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_getQrcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_QRCODE.$id);

        $codeContents = $sample;

        $tempDir = FCPATH.'modules/logistic/uploads/package_code/qr/';
        $file_name = md5($sample).'.png';
        $rs_path = $tempDir.$id.'/'.$file_name;
        if(!file_exists($rs_path)){
            QRcode::png($codeContents, $rs_path, "L", 6, 1);
        }

        return true;
    }
}

/**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_shipping_getQrcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_SHIPPING_QRCODE.$id);

        $codeContents = $sample;

        $tempDir = FCPATH.'modules/logistic/uploads/shipping_code/qr/';
        $file_name = md5($sample).'.png';
        $rs_path = $tempDir.$id.'/'.$file_name;
        if(!file_exists($rs_path)){
            QRcode::png($codeContents, $rs_path, "L", 6, 1);
        }

        return true;
    }
}

/**
 * { lg process digital signature image }
 *
 * @param      <type>   $partBase64  The part base 64
 * @param      <type>   $path        The path
 * @param      string   $image_name  The image name
 *
 * @return     boolean  
 */
function lg_process_digital_signature_image($id, $partBase64, $path, $image_name)
{

    $CI = & get_instance();

    if (empty($partBase64)) {
        return false;
    }

    _maybe_create_upload_path($path);
    $filename = unique_filename($path, $image_name.'.png');

    $decoded_image = base64_decode($partBase64);

    $retval = false;

    $path = rtrim($path, '/') . '/' . $filename;

    $fp = fopen($path, 'w+');

    if (fwrite($fp, $decoded_image)) {
        $retval                                 = true;
        //$GLOBALS['processed_digital_signature'] = $filename;
    }

    fclose($fp);

    if($retval){
        $attachment = [];
        $attachment[] = [
            'file_name' => $filename,
            'filetype' => 'image/png',
        ];

        $CI->misc_model->add_attachment_to_database($id, 'shipment_sign', $attachment);
    }

    return $retval;
}

/**
 * [lg_handle_delivery_shipment_attachment description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function lg_handle_delivery_shipment_attachment($id){
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

       

        $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/attachments/'.$id.'/';
        
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
      
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();
                $attachment = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype' => $_FILES['file']['type'],
                ];

                $CI->misc_model->add_attachment_to_database($id, 'shipment_attach', $attachment);

                return true;
            }
        }
    }
}

if (!function_exists('add_logistic_email_templates')) {
    /**
     * Init appointly email templates and assign languages
     * @return void
     */
    function add_logistic_email_templates() {
        $CI = &get_instance();

        $data['logistic_templates'] = $CI->emails_model->get(['type' => 'logistic', 'language' => 'english']);

        $CI->load->view('logistic/email_templates', $data);
    }
}

/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function lg_prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->app_mail_template->prepare($email, $template, $mailClassParams);
    $slug             = $CI->app_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}


/**
 * [handle_upload_pre_alert_invoice description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function handle_upload_pre_alert_invoice($id){
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

       

        $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/pre_alert/'.$id.'/';
        
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
      
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();
                $attachment = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype' => $_FILES['file']['type'],
                    'contact_id' => get_contact_user_id(),
                ];

                $CI->misc_model->add_attachment_to_database($id, 'pre_alert', $attachment);

                return true;
            }
        }
    }
}

/**
 * [lg_get_state_of_country description]
 * @return [type] [description]
 */
function lg_get_state_of_country($country_id){
    $CI = &get_instance();

    $CI->db->where('country', $country_id);
    return $CI->db->get(db_prefix().'lg_states')->result_array();
}


/**
 * [lg_get_state_of_country description]
 * @return [type] [description]
 */
function lg_get_city_of_state($country_id, $state_id){
    $CI = &get_instance();

    $CI->db->where('state', $state_id);
    $CI->db->where('country', $country_id);
    return $CI->db->get(db_prefix().'lg_cities')->result_array();
}

/**
 * [get_shipment_next_number description]
 * @return [type] [description]
 */
function get_shipment_next_number(){
    $CI = & get_instance();

    $number = '';
    $random_digits = get_option('lg_number_of_random_digits');
    $number_type = get_option('lg_tracking_number_type');

    if($number_type == 'auto_increment'){
        $CI->db->where('number_type', 'auto_increment');
        $CI->db->select('MAX(number) as number');
        $package = $CI->db->get(db_prefix().'lg_shippings')->row();
        if(isset($package->number)){
            return ($package->number+1); 
        }
        return 1;

    }else if($number_type == 'random'){

        $number = lg_generateRandomString($random_digits);

        $CI->db->where('number_code', $number);
        $check_exist_number = $CI->db->get(db_prefix().'lg_shippings')->row();

        while($check_exist_number) {
          
            $number = lg_generateRandomString($random_digits);
            $this->db->where('number_code',$number);
            $check_exist_number = $CI->db->get(db_prefix().'lg_shippings')->row();
        }
    }

    return $number;
}


/**
 * [handle_upload_lg_shipping_files description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function handle_upload_lg_shipping_files($id){
    $type = 'shippings';
    
    $CI = &get_instance();
    $totalUploaded = 0;

    if(is_array($id)){
        
            if (isset($_FILES['attachments']['name'])
                && ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
                if (!is_array($_FILES['attachments']['name'])) {
                    $_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
                    $_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
                    $_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
                    $_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
                    $_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
                }

                _file_attachments_index_fix('attachments');
                for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

                    // Get the temp file path
                    $tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
                    // Make sure we have a filepath
                    if (!empty($tmpFilePath) && $tmpFilePath != '') {
                        if (_perfex_upload_error($_FILES['attachments']['error'][$i])
                            || !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
                            continue;
                        }

                        $pk_first_key_id = 0;
                        foreach($id as $key => $pk_id){
                            if($key == 0){
                                $pk_first_key_id = $pk_id;
                                $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_id . '/';
                                _maybe_create_upload_path($path);
                                $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                                $newFilePath = $path . $filename;
                                // Upload the file into the temp dir
              
                                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                                    $attachment = [];
                                    $attachment[] = [
                                        'file_name' => $filename,
                                        'filetype' => $_FILES['attachments']['type'][$i],
                                    ];

                                    $CI->misc_model->add_attachment_to_database($pk_id, 'lg_shipping', $attachment);
                                    $totalUploaded++;
                                }
                            }else{
                                
                                $source_path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_first_key_id . '/';
                                $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $pk_id . '/';
                                _maybe_create_upload_path($path);
                                $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                                if(copy($source_path.$filename, $path.$filename)){
                                    $attachment = [];
                                    $attachment[] = [
                                        'file_name' => $filename,
                                        'filetype' => $_FILES['attachments']['type'][$i],
                                    ];

                                    $CI->misc_model->add_attachment_to_database($pk_id, 'lg_shipping', $attachment);
                                    $totalUploaded++;
                                }
                            }
                        }
                    }
                }
            }
        


    }else{
        $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $id . '/';
        if (isset($_FILES['attachments']['name'])
            && ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
            if (!is_array($_FILES['attachments']['name'])) {
                $_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
                $_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
                $_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
                $_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
                $_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
            }

            _file_attachments_index_fix('attachments');
            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

                // Get the temp file path
                $tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (_perfex_upload_error($_FILES['attachments']['error'][$i])
                        || !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
                        continue;
                    }

                    _maybe_create_upload_path($path);
                    $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $attachment = [];
                        $attachment[] = [
                            'file_name' => $filename,
                            'filetype' => $_FILES['attachments']['type'][$i],
                        ];

                        $CI->misc_model->add_attachment_to_database($id, 'lg_shipping', $attachment);
                        $totalUploaded++;
                    }
                }
            }
        }
    }

    return (bool) $totalUploaded;
}

/**
 * [lg_get_recipient_name description]
 * @return [type] [description]
 */
function lg_get_recipient_name($recipient_id){
    $CI = &get_instance();

    $CI->db->where('id', $recipient_id);
    $recipient = $CI->db->get(db_prefix().'lg_recipients')->row();

    if(isset($recipient->first_name) && isset($recipient->last_name)){
        return $recipient->first_name .' '.$recipient->last_name;
    }

    return '';
}


/**
 * [lg_get_recipient_address_str description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function lg_get_recipient_address_str($id, $return_address = ''){
    $CI = & get_instance();

    $CI->db->where('id', $id);
    $address = $CI->db->get(db_prefix().'lg_recipient_address')->row();

    if($return_address == 1){
        if(isset($address->address)){
            return $address->address;
        }

        return '';
    }

    $str = '';
    if(isset($address->country)){
        $str .= lg_get_country_name_by_id($address->country);
    }

    if(isset($address->city)){
        $str .= ' - '.lg_get_city_name_by_id($address->city);
    }
    return $str;
}

/**
 * [lg_get_payment_term_str description]
 * @return [type] [description]
 */
function lg_get_payment_term_str($payment_term_id){
    $CI = & get_instance();

    $CI->db->where('id', $payment_term_id);

    $payment = $CI->db->get(db_prefix().'lg_payment_terms')->row();

    if(isset($payment->name)){
        return $payment->name;
    }
    return '';
}

/**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_shipping_getBarcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_SHIPPING_BARCODE.$id);

        $barcodeobj = new TCPDFBarcode($sample ?? '', 'C128');
        $code = $barcodeobj->getBarcodeSVGcode(4, 150, 'black');
        file_put_contents(LOGISTIC_PRINT_SHIPPING_BARCODE.$id.'/'.md5($sample).'.svg', $code);

        return true;
    }
}

/**
 * { lg process digital signature image }
 *
 * @param      <type>   $partBase64  The part base 64
 * @param      <type>   $path        The path
 * @param      string   $image_name  The image name
 *
 * @return     boolean  
 */
function lg_process_digital_signature_image_shipping($id, $partBase64, $path, $image_name)
{

    $CI = & get_instance();

    if (empty($partBase64)) {
        return false;
    }

    _maybe_create_upload_path($path);
    $filename = unique_filename($path, $image_name.'.png');

    $decoded_image = base64_decode($partBase64);

    $retval = false;

    $path = rtrim($path, '/') . '/' . $filename;

    $fp = fopen($path, 'w+');

    if (fwrite($fp, $decoded_image)) {
        $retval                                 = true;
        //$GLOBALS['processed_digital_signature'] = $filename;
    }

    fclose($fp);

    if($retval){
        $attachment = [];
        $attachment[] = [
            'file_name' => $filename,
            'filetype' => 'image/png',
        ];

        $CI->misc_model->add_attachment_to_database($id, 'shipping_sm_sign', $attachment);
    }

    return $retval;
}

/**
 * [lg_handle_delivery_shipment_attachment description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function lg_handle_shipping_delivery_shipment_attachment($id){
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

        $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/attachments/'.$id.'/';
        
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
      
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();
                $attachment = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype' => $_FILES['file']['type'],
                ];

                $CI->misc_model->add_attachment_to_database($id, 'shipping_attach', $attachment);

                return true;
            }
        }
    }
}

/**
 * [handle_upload_lg_consolidation_files description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function handle_upload_lg_consolidation_files($id){
    $type = 'consolidated';
    
    $CI = &get_instance();
    $totalUploaded = 0;

  
    $path = LOGISTIC_MODULE_UPLOAD_FOLDER . '/' . $type . '/' . $id . '/';
    if (isset($_FILES['attachments']['name'])
        && ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
        if (!is_array($_FILES['attachments']['name'])) {
            $_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
            $_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
            $_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
            $_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
            $_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
        }

        _file_attachments_index_fix('attachments');
        for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

            // Get the temp file path
            $tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES['attachments']['error'][$i])
                    || !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename = unique_filename($path, $_FILES['attachments']['name'][$i]);
                $newFilePath = $path . $filename;
                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $attachment = [];
                    $attachment[] = [
                        'file_name' => $filename,
                        'filetype' => $_FILES['attachments']['type'][$i],
                    ];

                    $CI->misc_model->add_attachment_to_database($id, 'lg_consolidated', $attachment);
                    $totalUploaded++;
                }
            }
        }
    }
    

    return (bool) $totalUploaded;
}



/**
 * [get_consolidation_next_number description]
 * @return [type] [description]
 */
function get_consolidation_next_number(){
    $CI = & get_instance();

    $number = '';
    $random_digits = get_option('lg_number_of_random_digits');
    $number_type = get_option('lg_tracking_number_type');

    if($number_type == 'auto_increment'){
        $CI->db->where('number_type', 'auto_increment');
        $CI->db->select('MAX(number) as number');
        $package = $CI->db->get(db_prefix().'lg_consolidated')->row();
        if(isset($package->number)){
            return ($package->number+1); 
        }
        return 1;

    }else if($number_type == 'random'){

        $number = lg_generateRandomString($random_digits);

        $CI->db->where('number_code', $number);
        $check_exist_number = $CI->db->get(db_prefix().'lg_consolidated')->row();

        while($check_exist_number) {
          
            $number = lg_generateRandomString($random_digits);
            $this->db->where('number_code',$number);
            $check_exist_number = $CI->db->get(db_prefix().'lg_consolidated')->row();
        }
    }

    return $number;
}

/**
 * [lg_get_tracking_number_by_type description]
 * @param  [type] $rel_type [description]
 * @param  [type] $rel_id   [description]
 * @return [type]           [description]
 */
function lg_get_tracking_number_by_type($rel_type, $rel_id){
    $CI = & get_instance();

    $table = '';
    if($rel_type == 'locker_packages'){
        $table = 'lg_packages';

    }else if($rel_type == 'shipping'){
        $table = 'lg_shippings';
    }

    if($table != ''){
        $CI->db->where('id', $rel_id);
        $data = $CI->db->get(db_prefix().$table)->row();

        if($data){
            return $data->shipping_prefix.$data->number_code;
        }
    }

    return '';
}


/**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_consolidation_getQrcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_CONSOLIDATION_QRCODE.$id);

        $codeContents = $sample;

        $tempDir = FCPATH.'modules/logistic/uploads/consolidation_code/qr/';
        $file_name = md5($sample).'.png';
        $rs_path = $tempDir.$id.'/'.$file_name;
        if(!file_exists($rs_path)){
            QRcode::png($codeContents, $rs_path, "L", 6, 1);
        }

        return true;
    }
}

    /**
 * getBarcode
 * @param  [type] $sample 
 * @return [type]         
 */
function lg_consolidation_getBarcode($sample, $id)
{
    if (!$sample) {
        echo "";
    } else {
        _maybe_create_upload_path(LOGISTIC_PRINT_CONSOLIDATION_BARCODE.$id);

        $barcodeobj = new TCPDFBarcode($sample ?? '', 'C128');
        $code = $barcodeobj->getBarcodeSVGcode(4, 150, 'black');
        file_put_contents(LOGISTIC_PRINT_CONSOLIDATION_BARCODE.$id.'/'.md5($sample).'.svg', $code);

        return true;
    }
}

/**
 * { lg process digital signature image }
 *
 * @param      <type>   $partBase64  The part base 64
 * @param      <type>   $path        The path
 * @param      string   $image_name  The image name
 *
 * @return     boolean  
 */
function lg_process_digital_signature_image_consolidated($id, $partBase64, $path, $image_name)
{

    $CI = & get_instance();

    if (empty($partBase64)) {
        return false;
    }

    _maybe_create_upload_path($path);
    $filename = unique_filename($path, $image_name.'.png');

    $decoded_image = base64_decode($partBase64);

    $retval = false;

    $path = rtrim($path, '/') . '/' . $filename;

    $fp = fopen($path, 'w+');

    if (fwrite($fp, $decoded_image)) {
        $retval                                 = true;
        //$GLOBALS['processed_digital_signature'] = $filename;
    }

    fclose($fp);

    if($retval){
        $attachment = [];
        $attachment[] = [
            'file_name' => $filename,
            'filetype' => 'image/png',
        ];

        $CI->misc_model->add_attachment_to_database($id, 'consolidated_sm_sign', $attachment);
    }

    return $retval;
}

/**
 * [lg_handle_delivery_shipment_attachment description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function lg_handle_consolidation_delivery_shipment_attachment($id){
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

        $path = LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/attachments/'.$id.'/';
        
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = unique_filename($path, $_FILES['file']['name']);
      
            $newFilePath = $path . $filename;
            // Upload the file into the company uploads dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI = & get_instance();
                $attachment = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype' => $_FILES['file']['type'],
                ];

                $CI->misc_model->add_attachment_to_database($id, 'consolidated_attach', $attachment);

                return true;
            }
        }
    }
}

/**
 * [is_driver_staff description]
 * @return boolean [description]
 */
function is_driver_staff($staffid = ''){

    /**
     * Checking for current user?
     */
    if (!is_numeric($staffid)) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->staff_type === 'driver';
        }

        $staffid = get_staff_user_id();
    }

    $CI = & get_instance();

    $CI->db->select('1')
    ->where('staff_type','driver')
    ->where('staffid', $staffid);
    $result = $CI->db->count_all_results(db_prefix() . 'staff') > 0 ? true : false;

    return $result;
}


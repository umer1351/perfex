<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Team password client controller
 */
class Client extends ClientsController
{

  /**
   * [__construct ]
   */
  public function __construct()
  {
    parent::__construct();
    $this->load->model('logistic_model');
  }

  /**
   * [packages description]
   * @return [type] [description]
   */
  public function packages(){
    $data['title'] = _l('lg_packages');
    $client_id = get_client_user_id();

    $data['packages'] = $this->logistic_model->get_client_packages($client_id);

    $this->data($data);
    $this->view('clients_portal/packages/manage');
    $this->layout();
  }


  /**
     * [package_detail description]
     * @return [type] [description]
     */
    public function package_detail($id){


        $package = $this->logistic_model->get_package($id);
        if($package->customer_id != get_client_user_id()){
            access_denied('packages');
        }

        $this->load->model('clients_model');

        
        $data = [];
       
        $data['package'] = $package;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver"');
        $data['package_attachments'] = $this->logistic_model->get_package_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_package_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['package']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['package']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_package($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_package($id);

        

        $data['title'] = _l('lg_package_details');

        $this->data($data);
        $this->view('clients_portal/packages/package_detail');
        $this->layout();
    }

    /**
     * [export_package_shipment description]
     * @return [type] [description]
     */
    public function export_package_shipment($id){
         if (!$id) {
            redirect(site_url('logistic/client/packages'));
        }

        $package_data = $this->logistic_model->get_package($id);
        if($package_data->customer_id != get_client_user_id()){
          access_denied('package_label');
        }

        $package = $this->logistic_model->get_package_pdf_html($package_data);

        try {
            $pdf = $this->logistic_model->package_pdf($package);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $package_data->shipping_prefix.$package_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_package_label description]
     * @return [type] [description]
     */
    public function export_package_label($id){
         if (!$id) {
            redirect(site_url('logistic/client/packages'));
        }

        $package_data = $this->logistic_model->get_package($id);

        if($package_data->customer_id != get_client_user_id()){
          access_denied('package_label');
        }

        $package = $this->logistic_model->get_package_label_pdf_html($package_data);

        try {
            $pdf = $this->logistic_model->package_label_pdf($package);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $package_data->shipping_prefix.$package_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_package($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('packages/_file', $data);
    }


    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_package($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = false;
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('packages/_file_sm', $data);
    }
      
    /**
     * [pre_alert_list description]
     * @return [type] [description]
     */
    public function pre_alert_list(){
      $data['title'] = _l('lg_pre_alert');
      $client_id = get_client_user_id();

      $data['pre_alert_list'] = $this->logistic_model->get_pre_alert_list('client_id = '.$client_id);

      $this->data($data);
      $this->view('clients_portal/pre_alert/manage');
      $this->layout();
    }

    /**
     * [pre_alert description]
     * @return [type] [description]
     */
    public function pre_alert($id = ''){
      $client_id = get_client_user_id();

      if($this->input->post()){
        $data_alert = $this->input->post();
        $data_alert['package_description'] = $this->input->post('package_description', false);
        if($id == ''){

          $pre_alert_id = $this->logistic_model->add_pre_alert($data_alert);
          if($pre_alert_id){
            handle_upload_pre_alert_invoice($pre_alert_id);

            set_alert('success', _l('added_successfully'));
          }

        }else{
          $success = $this->logistic_model->update_pre_alert($data_alert, $id);
          $success_upload =  handle_upload_pre_alert_invoice($id);
          if($success || $success_upload){
            set_alert('success', _l('updated_successfully'));
          }
        }

        redirect(site_url('logistic/client/pre_alert_list'));
      }

      if($id == ''){
        $data['title'] = _l('lg_create_pre_alert'); 
      }else{
        $data['title'] = _l('lg_edit_pre_alert');
        $data['pre_alert'] = $this->logistic_model->get_pre_alert($id);
        if($data['pre_alert']->status == 2){
          redirect(site_url('logistic/client/pre_alert_list'));
        }


        $data['pre_alert_attachment'] = $this->logistic_model->get_pre_alert_attachment($id);
      }

      $this->load->model('clients_model');
      $client = $this->clients_model->get($client_id);

      if($client->default_currency != 0){
        $data['currency_id'] = $client->default_currency;
      }else{

        $data['currency_id'] = get_base_currency()->id;
      }

      $data['shipping_companies'] = $this->logistic_model->get_shipping_companies();
      $data['client_id'] = $client_id;

      $this->data($data);
      $this->view('clients_portal/pre_alert/pre_alert');
      $this->layout();
    }


    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_pre_alert($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = '';
        $data['current_user_is_admin']             = false;
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('clients_portal/pre_alert/_file', $data);
    }

    /**
     * [delete_pre_alert_attachment description]
     * @return [type] [description]
     */
    public function delete_pre_alert_attachment($pre_alert_id, $id){
      $this->load->model('misc_model');
        $file = $this->misc_model->get_file($id);
        if ($file->contact_id == get_contact_user_id() || is_primary_contact(get_contact_user_id())) {
            $this->logistic_model->delete_pre_alert_attachment($id);


        } 

        redirect(site_url('logistic/client/pre_alert/'.$pre_alert_id));
    }

    public function delete_pre_alert($id){

        $success = $this->logistic_model->delete_pre_alert($id);

        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(site_url('logistic/client/pre_alert_list'));
    }

    /**
     * [recipients description]
     * @return [type] [description]
     */
    public function recipients(){

        $data['title'] = _l('lg_recipients');

        $client_id = get_client_user_id();

        $data['recipients'] = $this->logistic_model->get_client_recipients($client_id);


        $this->data($data);
        $this->view('clients_portal/recipients/recipients');
        $this->layout();
    }

    /**
     * [recipient description]
     * @return [type] [description]
     */
    public function recipient($id = ''){

        $client_id = get_client_user_id();


        if($this->input->post()){
            $reci_data = $this->input->post();

            if($reci_data['recipient_id'] == ''){
                unset($reci_data['recipient_id']);
                $recipient_id = $this->logistic_model->add_recipient($reci_data);

                if($recipient_id){
                    set_alert('success', _l('added_successfully'));
                }
            }else{
                unset($reci_data['recipient_id']);
                $success = $this->logistic_model->update_recipient($reci_data, $id);

                if($success){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(site_url('logistic/client/recipients'));

        }

        $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
        $data['states'] = [];
        $data['cities'] = [];

        if($id == ''){
            $data['title'] = _l('lg_create_recipient'); 
        }else{
            $data['title'] = _l('lg_edit_recipient');
            $data['recipient'] = $this->logistic_model->get_recipient($id);
           
        }

        $this->load->model('clients_model');
        $client = $this->clients_model->get($client_id);

        if($client->default_currency != 0){
            $data['currency_id'] = $client->default_currency;
        }else{

            $data['currency_id'] = get_base_currency()->id;
        }
        $data['client_id'] = $client_id;


        $this->data($data);
        $this->view('clients_portal/recipients/recipient');
        $this->layout();

    }

    /**
     * [add_address_row description]
     */
    public function add_address_row($key){

        $html = '';

        $data['countries'] = $this->logistic_model->get_logistics_countries('active = 1');
        $data['states'] = [];
        $data['cities'] = [];

        $data['key'] = $key;

        $html .= $this->load->view('clients_portal/recipients/address_row', $data, true);

        echo json_encode([
            'html' => $html,
        ]);

    }

    /**
     * [delete_recipient description]
     * @return [type] [description]
     */
    public function delete_recipient($id){

        $success = $this->logistic_model->delete_recipient($id);

        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(site_url('logistic/client/recipients'));
    }

     /**
     * [get_state_by_country description]
     * @return [type] [description]
     */
    public function get_state_by_country($country_id){
        $html = '';
        
        $this->db->where('country', $country_id);
        $states_list = $this->db->get(db_prefix().'lg_states')->result_array();
        $html = '<option value=""></option>';
        foreach($states_list as $state){
            $html .= '<option value="'.$state['id'].'">'.$state['state_name'].'</option>';
        }
        

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_city_by_state description]
     * @return [type] [description]
     */
    public function get_city_by_state($state_id){
        $html = '';
        
        $this->db->where('state', $state_id);
        $cities_list = $this->db->get(db_prefix().'lg_cities')->result_array();
        $html = '<option value=""></option>';
        foreach($cities_list as $city){
            $html .= '<option value="'.$city['id'].'">'.$city['city_name'].'</option>';
        }
        

        echo json_encode([
            'html' => $html,
        ]);
    }

        /**
       * [shippings description]
       * @return [type] [description]
       */
    public function shipping(){
        $data['title'] = _l('lg_shippings');
        $client_id = get_client_user_id();

        $data['shippings'] = $this->logistic_model->get_client_shippings($client_id);

        $this->data($data);
        $this->view('clients_portal/shippings/manage');
        $this->layout();
    }

    /**
     * [shipment description]
     * @return [type] [description]
     */
    public function shipment($multiple, $id = ''){

        if($id == ''){
            if($multiple == 0){
                $data['title'] = _l('lg_create_shipment');
            }else{
                $data['title'] = _l('lg_create_multiple_shipment');
            }

        }else{

            $data['shipment'] = $this->logistic_model->get_shipping($id);

            $data['shipment_attachments'] = $this->logistic_model->get_shipping_attachments($id);
            $data['title'] = _l('lg_update_shipment');
        }

        if($this->input->post()){
            $shipping_data = $this->input->post();
            if($shipping_data['id'] == ''){

                unset($shipping_data['id']);
                $shipping_data['created_from'] = 'client';
                $shipping_data['shipping_type'] = 'pickup';
                $shipping_data['approve_status'] = 'waiting_approval';

                $this->db->where('style_name', 'picked_up');
                $this->db->where('is_default_status', 1);
                $picked_up_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

                $shipping_data['delivery_status'] = (isset($picked_up_status->id) ? $picked_up_status->id : null);


                $shipping_id = $this->logistic_model->add_shipping($multiple, $shipping_data);

                if($shipping_id){
                    

                    handle_upload_lg_shipping_files($shipping_id);
                  
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                $shipping_id = $shipping_data['id'];
                unset($shipping_data['id']);
                $shipping_data['created_from'] = 'client';
                $success = $this->logistic_model->update_shipping($shipping_data, $shipping_id);

                $success_upload = handle_upload_lg_shipping_files($shipping_id);

                if($success || $success_upload){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(site_url('logistic/client/shipping'));
        }


        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get('', [db_prefix().'clients.active' => 1]);

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['statuses'] = $this->logistic_model->get_style_and_states_for_options();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['multiple'] = $multiple;
        $data['countries'] = $this->logistic_model->get_logistics_countries('iso_code IS NOT NULL AND active = 1');
        $data['agencies'] = $this->logistic_model->get_agencys();
        $data['office_groups'] = $this->logistic_model->get_offices();
        $data['logistics_services'] = $this->logistic_model->get_logistics_services();
        $data['type_of_packages'] = $this->logistic_model->get_type_of_packages();
        $data['shipping_companies'] = $this->logistic_model->get_shipping_companies();
        $data['shipping_modes'] = $this->logistic_model->get_shipping_modes();
        $data['shipping_times'] = $this->logistic_model->get_shipping_times();
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver"');
        $data['payment_terms'] = $this->logistic_model->get_logistics_payment_terms();

        $this->data($data);
        $this->view('clients_portal/shippings/shipment');
        $this->layout();
    }

    /**
     * Gets the currency rate.
     *
     * @param        $currency_id  The currency identifier
     */
    public function get_currency_rate($currency_id){
        $base_currency = get_base_currency();

        $pr_currency = lg_get_currency_by_id($currency_id);

        $currency_rate = 1;
        $convert_str = ' ('.$base_currency->name.' => '.$base_currency->name.')'; 
        $currency_name = '('.$base_currency->name.')';
        if($base_currency->id != $pr_currency->id){
            $currency_rate = lg_get_currency_rate($base_currency->name, $pr_currency->name);
            $convert_str = ' ('.$base_currency->name.' => '.$pr_currency->name.')'; 
            $currency_name = '('.$pr_currency->name.')';
        }

        echo json_encode([
            'currency_rate' => $currency_rate,
            'convert_str' => $convert_str,
            'currency_name' => $currency_name,
        ]);

    }

    /**
     * [get_client_address_option description]
     * @return [type] [description]
     */
    public function get_client_address_option($client_id, $currency = ''){

        $html = '<option value=""></option>';
        $invoice_html = '<option value=""></option>';
        $recipient_html = '<option value=""></option>';

        $list_address = lg_get_client_address_list($client_id);

        $list_invoice = $this->logistic_model->get_invoices_for_package($client_id, $currency);

        $list_recipients = $this->logistic_model->get_client_recipients($client_id);


        foreach($list_address as $address){
            $html .= '<option value="'.$address['id'].'">'.$address['address'].'</option>';
        }

        foreach($list_invoice as $inv){
            if(total_rows(db_prefix().'lg_packages', ['invoice_id' => $inv['id']]) == 0){
                $invoice_html .= '<option value="'.$inv['id'].'">'.format_invoice_number($inv['id']).'</option>';
            }
        }


        foreach($list_recipients as $recipient){
            $recipient_html .= '<option value="'.$recipient['id'].'">'.$recipient['first_name'].' '.$recipient['last_name'].'</option>';
        }


        echo json_encode([
            'html' => $html,
            'invoice_html' => $invoice_html,
            'recipient_html' => $recipient_html,
        ]);
    }

    /**
     * [get_client_recipient_address description]
     * @return [type] [description]
     */
    public function get_client_recipient_address($recipient_id){
        $html = '<option value=""></option>';

        $recipient_address = $this->logistic_model->get_recipient($recipient_id);

        
        if(isset($recipient_address->address)){
            foreach($recipient_address->address as $address){
                $html .= '<option value="'.$address['id'].'">'.$address['address'].'</option>';
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
        
    }

    /**
     * [get_currency description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_currency($id)
    {
        echo json_encode(get_currency($id));
    }

    /**
     * [add_shipment_row description]
     */
    public function add_shipment_row($key){

        $html = '';

        $data['key'] = $key;

        $html .= $this->load->view('shipping/package_row', $data, true);

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [shipping_detail description]
     * @return [type] [description]
     */
    public function shipping_detail($id){

        $this->load->model('clients_model');

        $template_name = 'logistic_shipping_send_to_customer';
        $shipping = $this->logistic_model->get_shipping($id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $shipping->customer_id, ['logistic'] );

        $data['shipping'] = $shipping;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver"');
        $data['shipping_attachments'] = $this->logistic_model->get_shipping_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_shipping_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_shipping_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['shipping']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['shipping']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_shipping($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_shipping($id);

        $data['recipient'] = $this->logistic_model->get_recipient($shipping->recipient_id);
        $data['recipient_address'] = $this->logistic_model->get_recipient_address($shipping->recipient_address_id);
        

        $data['title'] = _l('lg_shipping_details');


        $this->data($data);
        $this->view('clients_portal/shippings/shipping_detail');
        $this->layout();
    }


    /**
     * [export_shipping_shipment description]
     * @return [type] [description]
     */
    public function export_shipping_shipment($id){
         if (!$id) {
            redirect(admin_url('logistic/shipping'));
        }

        $shipping_data = $this->logistic_model->get_shipping($id);

        $shipping = $this->logistic_model->get_shipping_pdf_html($shipping_data);

        try {
            $pdf = $this->logistic_model->shipping_pdf($shipping);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_shipping_label description]
     * @return [type] [description]
     */
    public function export_shipping_label($id){
         if (!$id) {
            redirect(admin_url('logistic/shipping'));
        }

        $shipping_data = $this->logistic_model->get_shipping($id);

        $shipping = $this->logistic_model->get_shipping_label_pdf_html($shipping_data);

        try {
            $pdf = $this->logistic_model->shipping_label_pdf($shipping);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

     /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_shipping($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('clients_portal/shippings/_file_sm', $data);
    }

    /**
     * { preview shipping file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipping($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('clients_portal/shippings/_file', $data);
    }
        
     /**
       * [consolidateds description]
       * @return [type] [description]
       */
    public function consolidated(){
        $data['title'] = _l('lg_consolidated');
        $client_id = get_client_user_id();

        $data['consolidated'] = $this->logistic_model->get_client_consolidated($client_id);

        $this->data($data);
        $this->view('clients_portal/consolidated/manage');
        $this->layout();
    }

    /**
     * [consolidated_detail description]
     * @return [type] [description]
     */
    public function consolidated_detail($id){

        $this->load->model('clients_model');

        $template_name = 'logistic_consolidation_send_to_customer';
        $consolidation = $this->logistic_model->get_consolidation($id);
        $data = [];
        $data = lg_prepare_mail_preview_data($template_name, $consolidation->customer_id, ['logistic'] );

        $data['consolidation'] = $consolidation;
        $data['drivers'] = $this->staff_model->get('', 'staff_type = "driver"');
        $data['consolidation_attachments'] = $this->logistic_model->get_consolidation_attachments($id);
        $data['delivery_shipments'] = $this->logistic_model->get_consolidation_delivery_shipments($id);
        $data['shipment_attachments'] = $this->logistic_model->get_consolidation_shipment_attachments($id);

        $data['client'] = $this->clients_model->get($data['consolidation']->customer_id);

        $data['client_address'] = $this->logistic_model->get_client_address($data['consolidation']->customer_address);

        $data['tracking_histories'] = $this->logistic_model->get_tracking_histories_consolidation($id);
        $data['action_histories'] = $this->logistic_model->get_action_histories_consolidation($id);

        $data['recipient'] = $this->logistic_model->get_recipient($consolidation->recipient_id);
        $data['recipient_address'] = $this->logistic_model->get_recipient_address($consolidation->recipient_address_id);
        

        $data['title'] = _l('lg_consolidation_details');


        $this->data($data);
        $this->view('clients_portal/consolidated/consolidated_detail');
        $this->layout();
    }


    /**
     * { preview package file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_shipment_consolidation($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }

        $this->load->view('clients_portal/consolidated/_file_sm', $data);
    }

    /**
     * { preview consolidation file }
     *
     * @param      <type>  $id      The identifier
     * @param      <type>  $rel_id  The relative identifier
     * @return  view
     */
    public function file_consolidation($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->logistic_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('clients_portal/consolidated/_file', $data);
    }


     /**
     * [export_consolidation_shipment description]
     * @return [type] [description]
     */
    public function export_consolidation_shipment($id){
         if (!$id) {
            redirect(site_url('logistic/client/consolidated'));
        }

        $consolidation_data = $this->logistic_model->get_consolidation($id);

        $consolidation = $this->logistic_model->get_consolidation_pdf_html($consolidation_data);

        try {
            $pdf = $this->logistic_model->consolidation_pdf($consolidation);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

    /**
     * [export_consolidation_label description]
     * @return [type] [description]
     */
    public function export_consolidation_label($id){
         if (!$id) {
            redirect(site_url('logistic/client/consolidated'));
        }

        $consolidation_data = $this->logistic_model->get_consolidation($id);

        $consolidation = $this->logistic_model->get_consolidation_label_pdf_html($consolidation_data);

        try {
            $pdf = $this->logistic_model->consolidation_label_pdf($consolidation);
        } catch (Exception $e) {
            echo lg_html_entity_decode($e->getMessage());
            die;
        }
        
        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $pdf->Output($number.'.pdf', $type);
    }

}
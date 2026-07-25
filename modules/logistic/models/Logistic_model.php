<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Logistic model
 */
class Logistic_model extends App_Model {

	/**
	 * [__construct description]
	 */
	public function __construct()
    {
        parent::__construct();
        
    }

    /**
     * [add_office description]
     * @param [type] $data [description]
     */
    public function add_office($data)
    {
    	$data['created_at'] = date('Y-m-d H:i:s');
    	$data['created_by'] = get_staff_user_id();

    	$this->db->insert(db_prefix().'lg_office_group', $data);
    	$insert_id = $this->db->insert_id();
    	if($insert_id){

    		return $insert_id;
    	}

    	return false;
    }

    /**
     * [update_office description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_office($data, $id)
    {
    	$this->db->where('id', $id);
    	$this->db->update(db_prefix().'lg_office_group', $data);
    	if($this->db->affected_rows() > 0){
    		return true;
    	}

    	return false;
    }

    /**
     * [get_offices description]
     * @return [type] [description]
     */
    public function get_offices(){
    	return $this->db->get(db_prefix().'lg_office_group')->result_array();
    }

    /**
     * [delete_office description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_office($id){
    	$this->db->where('id', $id);
    	$this->db->delete(db_prefix().'lg_office_group');
    	if($this->db->affected_rows() > 0){
    		return true;
    	}

    	return false;
    }

    /**
     * [add_agency description]
     * @param [type] $data [description]
     */
    public function add_agency($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_agency_group', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_agency description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_agency($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_agency_group', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_agencys description]
     * @return [type] [description]
     */
    public function get_agencys(){
        return $this->db->get(db_prefix().'lg_agency_group')->result_array();
    }

    /**
     * [delete_agency description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_agency($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_agency_group');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [add_shipping_company description]
     * @param [type] $data [description]
     */
    public function add_shipping_company($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_shipping_companies', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_shipping_company description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_shipping_company($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_shipping_companies', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_shipping_companies description]
     * @return [type] [description]
     */
    public function get_shipping_companies(){
        return $this->db->get(db_prefix().'lg_shipping_companies')->result_array();
    }

    /**
     * [delete_shipping_company description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_shipping_company($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_shipping_companies');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }


     /**
     * [add_type_of_package description]
     * @param [type] $data [description]
     */
    public function add_type_of_package($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_type_of_packages', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_type_of_package description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_type_of_package($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_type_of_packages', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_type_of_packages description]
     * @return [type] [description]
     */
    public function get_type_of_packages(){
        return $this->db->get(db_prefix().'lg_type_of_packages')->result_array();
    }

    /**
     * [delete_type_of_package description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_type_of_package($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_type_of_packages');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [shipping_mode description]
     * @param [type] $data [description]
     */
    public function add_shipping_mode($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_shipping_modes', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_shipping_mode description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_shipping_mode($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_shipping_modes', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_shipping_modes description]
     * @return [type] [description]
     */
    public function get_shipping_modes(){
        return $this->db->get(db_prefix().'lg_shipping_modes')->result_array();
    }

    /**
     * [delete_shipping_mode description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_shipping_mode($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_shipping_modes');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [shipping_time description]
     * @param [type] $data [description]
     */
    public function add_shipping_time($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_shipping_times', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_shipping_time description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_shipping_time($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_shipping_times', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_shipping_times description]
     * @return [type] [description]
     */
    public function get_shipping_times(){
        return $this->db->get(db_prefix().'lg_shipping_times')->result_array();
    }

    /**
     * [delete_shipping_time description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_shipping_time($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_shipping_times');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }


    /**
     * [style_and_state description]
     * @param [type] $data [description]
     */
    public function add_style_and_state($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_style_and_states', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_style_and_state description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_style_and_state($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_style_and_states', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_style_and_states description]
     * @return [type] [description]
     */
    public function get_style_and_states($where = ''){
        if($where != ''){
            $this->db->where($where);
        }
        $statuses = $this->db->get(db_prefix().'lg_style_and_states')->result_array();


        return $statuses;
    }

    /**
     * [get_style_and_states description]
     * @return [type] [description]
     */
    public function get_style_and_states_for_options(){
        $statuses = $this->db->get(db_prefix().'lg_style_and_states')->result_array();
        foreach($statuses as $key => $status){
            if($status['is_default_status'] == 1){
                $statuses[$key]['style_name'] = _l('lg_'.$status['style_name']);
            }
        }

        return $statuses;
    }


    /**
     * [delete_style_and_state description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_style_and_state($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_style_and_states');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [logistics_service description]
     * @param [type] $data [description]
     */
    public function add_logistics_service($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_logistics_services', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_logistics_service description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_logistics_service($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_logistics_services', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_services description]
     * @return [type] [description]
     */
    public function get_logistics_services(){
        return $this->db->get(db_prefix().'lg_logistics_services')->result_array();
    }

    /**
     * [delete_logistics_service description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_logistics_service($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_logistics_services');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [update_taxes_setting description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function update_taxes_setting($data){

        $affected_rows = 0;
        if(is_array($data)){
            foreach($data as $key => $value){
                if(update_option($key, $value)){
                    $affected_rows++;
                }
            }
        }
        

        if($affected_rows > 0){
            return true;
        }

        return false;
    }

    /**
     * [country description]
     * @param [type] $data [description]
     */
    public function add_country($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['active'] = 1;

        $this->db->insert(db_prefix().'lg_countries', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_country description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_country($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_countries', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_countries description]
     * @return [type] [description]
     */
    public function get_logistics_countries($where = ''){
        if($where != ''){
            $this->db->where($where);
        }
        return $this->db->get(db_prefix().'lg_countries')->result_array();
    }

    /**
     * [delete_country description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_country($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_countries');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * change estimate status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_logistic_country_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'lg_countries', [
            'active' => $status,
        ]);
    }

    /**
     * [state description]
     * @param [type] $data [description]
     */
    public function add_state($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_states', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_state description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_state($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_states', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_states description]
     * @return [type] [description]
     */
    public function get_logistics_states(){
        return $this->db->get(db_prefix().'lg_states')->result_array();
    }

    /**
     * [delete_state description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_state($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_states');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [city description]
     * @param [type] $data [description]
     */
    public function add_city($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_cities', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_city description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_city($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_cities', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_citys description]
     * @return [type] [description]
     */
    public function get_logistics_cities(){
        return $this->db->get(db_prefix().'lg_cities')->result_array();
    }

    /**
     * [delete_city description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_city($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_cities');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }


    /**
     * [shipping_rates_list description]
     * @param [type] $data [description]
     */
    public function add_shipping_rates_list($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['active'] = 1;

        $this->db->insert(db_prefix().'lg_shipping_rates_list', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_shipping_rates_list description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_shipping_rates_list($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_shipping_rates_list', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_citys description]
     * @return [type] [description]
     */
    public function get_shipping_rates_lists(){
        return $this->db->get(db_prefix().'lg_shipping_rates_list')->result_array();
    }

    /**
     * [delete_city description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_shipping_rates_list($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_shipping_rates_list');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * change estimate status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_logistic_rate_list_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'lg_shipping_rates_list', [
            'active' => $status,
        ]);
    }

    /**
     * [update_taxes_setting description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function update_tracking_and_invoice_setting($data){

        $affected_rows = 0;
        if(is_array($data)){
            foreach($data as $key => $value){
                if(update_option($key, $value)){
                    $affected_rows++;
                }
            }
        }
        

        if($affected_rows > 0){
            return true;
        }

        return false;
    }

    /**
     * [payment_term description]
     * @param [type] $data [description]
     */
    public function add_payment_term($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_payment_terms', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_payment_term description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_payment_term($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_payment_terms', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_logistics_payment_terms description]
     * @return [type] [description]
     */
    public function get_logistics_payment_terms(){
        return $this->db->get(db_prefix().'lg_payment_terms')->result_array();
    }

    /**
     * [delete_payment_term description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_payment_term($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_payment_terms');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }
 

    /**
     * 
     */
    public function add_client_address($data){
        $data['created_at'] = date('Y-m-d H:i:s');
        

        $this->db->insert(db_prefix().'lg_client_address', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return $insert_id;
        }
        return false;
    }

    /**
     * [update_country description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_client_address($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_client_address', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [delete_address description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_address($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_client_address');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [add_package description]
     */
    public function add_package($multiple, $data){
        $this->load->model('clients_model');

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['pre_alert_id'])){
            $pre_alert_id = $data['pre_alert_id'];
            unset($data['pre_alert_id']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        $this->db->where('style_name', 'pending');
        $this->db->where('is_default_status', 1);
        $pending_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

        $data['delivery_status'] = (isset($pending_status->id) ? $pending_status->id : null);

        if($multiple == 1){
            $rs_ids = [];

            if(isset($package_information)){
                foreach($package_information as $detail_data){


                    $data['number'] = get_package_next_number();
                    $data['number_code'] = $data['number'];
                    $data['number_type'] = get_option('lg_tracking_number_type');     


                    if($data['number_type'] == 'auto_increment'){
                        $data['number_code'] = str_pad($data['number'],get_option('lg_number_digits_to_track_locker_packages'),'0',STR_PAD_LEFT);
                    }

                    $final_row_weight = 0;
                    if($detail_data['weight'] >= $detail_data['weight_vol']){
                        $final_row_weight = $detail_data['weight'];
                    }else{
                        $final_row_weight = $detail_data['weight_vol'];
                    }

                    $data['subtotal'] = $final_row_weight*$data['price_kg'];
                    $data['discount'] = 0;
                    if(is_numeric($data['discount_percent'])){
                        $data['discount'] = $data['subtotal']*$data['discount_percent']/100;
                    }

                    $data['custom_duties'] = ($detail_data['weight'] + $detail_data['weight_vol'])*$data['custom_duties_percent']/100;

                    $data['tax'] = 0;
                    if($data['subtotal'] > ($data['minium_cost_to_apply_the_tax_setting'] * $data['currency_rate'])){
                        $data['tax'] = $data['subtotal']*$data['tax_percent']/100;
                    }

                    $data['declared_value'] = 0;
                    if($detail_data['dec_value'] > $data['minium_cost_to_apply_declared_tax_setting']){
                        $data['declared_value'] = $detail_data['dec_value'] * $data['declared_value_percent']/100;
                    }

                    $data['fixed_charge'] = $detail_data['fixed_charge'];

                    
                    if(!is_numeric($data['reissue'])){
                        $data['reissue'] = 0;
                    }

                    if(!is_numeric($data['shipping_insurance'])){
                        $data['shipping_insurance'] = 0;
                    }

                    if(!is_numeric($data['fixed_charge'])){
                        $data['fixed_charge'] = 0;
                    }



                    $data['total'] = $data['subtotal'] + $data['shipping_insurance'] + $data['custom_duties'] + $data['tax'] + $data['declared_value'] + $data['reissue'] + $data['fixed_charge'] - $data['discount']; 


                    $this->db->insert(db_prefix().'lg_packages', $data);
                    $package_id = $this->db->insert_id();

                    if($package_id){
                        $detail_data['package_id'] = $package_id;
                        $detail_data['created_at'] = $data['created_at'];
                        $detail_data['created_by'] = $data['created_by'];
                        $this->db->insert(db_prefix().'lg_package_detail', $detail_data);

                        $rs_ids[] = $package_id;

                        $action_data = [];
                        $action_data['rel_id'] = $package_id;
                        $action_data['rel_type'] = 'package';
                        $action_data['time_update'] = date('Y-m-d H:i:s');
                        $action_data['user'] = get_staff_user_id();
                        $action_data['action'] = _l('lg_package_created');
                        $action_data['created_at'] = date('Y-m-d H:i:s');
                        $action_data['created_by'] = get_staff_user_id();

                        $this->db->insert(db_prefix().'lg_action_history', $action_data);


                        $contact_id = get_primary_contact_user_id($data['customer_id']);
                        $_package = $this->get_package($package_id);
                        if(is_numeric($contact_id) && $contact_id > 0){
                            $contact = $this->clients_model->get_contact($contact_id);
                            $template = mail_template('Logistic_package_created_send_to_customer', 'logistic', $_package, $contact);
                            $template->send();
                        }
                    }

                }
            }

            return $rs_ids;

        }else{

            $this->db->insert(db_prefix().'lg_packages', $data);
            $insert_id = $this->db->insert_id();

            if($insert_id){

                if(isset($package_information)){
                    foreach($package_information as $detail_data){

                        $detail_data['package_id'] = $insert_id;
                        $detail_data['created_at'] = $data['created_at'];
                        $detail_data['created_by'] = $data['created_by'];
                        $this->db->insert(db_prefix().'lg_package_detail', $detail_data);
                    }
                }


                $action_data = [];
                $action_data['rel_id'] = $insert_id;
                $action_data['rel_type'] = 'package';
                $action_data['time_update'] = date('Y-m-d H:i:s');
                $action_data['user'] = get_staff_user_id();
                $action_data['action'] = _l('lg_package_created');
                $action_data['created_at'] = date('Y-m-d H:i:s');
                $action_data['created_by'] = get_staff_user_id();

                $this->db->insert(db_prefix().'lg_action_history', $action_data);


                if(isset($pre_alert_id) && $pre_alert_id > 0){
                    $this->db->where('id', $pre_alert_id);
                    $this->db->update(db_prefix().'lg_pre_alert', [
                        'package_id' => $insert_id,
                        'status' => 2,
                    ]);

                }


                $contact_id = get_primary_contact_user_id($data['customer_id']);
                $_package = $this->get_package($insert_id);
                if(is_numeric($contact_id) && $contact_id > 0){
                    $contact = $this->clients_model->get_contact($contact_id);
                    $template = mail_template('Logistic_package_created_send_to_customer', 'logistic', $_package, $contact);
                    $template->send();
                }

                return $insert_id;
            }
        }
        return false;

    }

    /**
     * [update_package description]
     * @return [type] [description]
     */
    public function update_package($data, $id){

        $update_rs = 0;

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        if(isset($data['package_information_update'])){
            $package_information_update = $data['package_information_update'];
            unset($data['package_information_update']);
        }

        if(isset($data['removed_package_detail_ids'])){
            $removed_package_detail_ids = $data['removed_package_detail_ids'];
            unset($data['removed_package_detail_ids']);
        }

        if(isset($data['pre_alert_id'])){
            unset($data['pre_alert_id']);
        }

        if(isset($package_information)){
            foreach($package_information as $detail_data){

                $detail_data['package_id'] = $id;
                $this->db->insert(db_prefix().'lg_package_detail', $detail_data);
                $detail_inser_id = $this->db->insert_id();
                if($detail_inser_id){
                    $update_rs++; 
                }

            }
        }   

        if(isset($package_information_update)){
            foreach($package_information_update as $update_detail_data){

                $this->db->where('id', $update_detail_data['id']);
                $this->db->update(db_prefix().'lg_package_detail', $update_detail_data);
                if($this->db->affected_rows() > 0){
                    $update_rs++;
                }

            }
        }

        if(isset($removed_package_detail_ids)){
            foreach($removed_package_detail_ids as $detail_id){
                $this->db->where('id', $detail_id);
                $this->db->delete(db_prefix().'lg_package_detail');
                if($this->db->affected_rows() > 0){
                    $update_rs++;
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_packages', $data);
        if($this->db->affected_rows() > 0){
            $update_rs++;
        }

        if($update_rs > 0){

            $action_data = [];
            $action_data['rel_id'] = $id;
            $action_data['rel_type'] = 'package';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = _l('lg_package_updated');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            return true;
        }
        return false;
    }

    /**
     * [get_package description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_package($id){

        $this->db->where('id', $id);
        $package = $this->db->get(db_prefix().'lg_packages')->row();

        if($package){
            $this->db->where('package_id', $id);
            $package->package_detail = $this->db->get(db_prefix().'lg_package_detail')->result_array();

            return $package;
        }
        return false;
    }


     /**
     * check auto create currency rate
     * @return [type]
     */
    public function check_auto_create_currency_rate() {
        $this->load->model('currencies_model');
        $currency_rates = $this->get_currency_rate();
        $currencies = $this->currencies_model->get();
        $currency_generator = $this->currency_generator($currencies);

        foreach ($currency_rates as $key => $currency_rate) {
            if (isset($currency_generator[$currency_rate['from_currency_id'] . '_' . $currency_rate['to_currency_id']])) {
                unset($currency_generator[$currency_rate['from_currency_id'] . '_' . $currency_rate['to_currency_id']]);
            }
        }

        //if have API, will get currency rate from here
        if (count($currency_generator) > 0) {
            $this->db->insert_batch(db_prefix() . 'currency_rates', $currency_generator);
        }

        return true;
    }

    /**
     * currency generator
     * @param  $variants
     * @param  integer $i
     * @return 
     */
    public function currency_generator($currencies) {

        $currency_rates = [];

        foreach ($currencies as $key_1 => $value_1) {
            foreach ($currencies as $key_2 => $value_2) {
                if ($value_1['id'] != $value_2['id']) {
                    $currency_rates[$value_1['id'] . '_' . $value_2['id']] = [
                        'from_currency_id' => $value_1['id'],
                        'from_currency_name' => $value_1['name'],
                        'from_currency_rate' => 1,
                        'to_currency_id' => $value_2['id'],
                        'to_currency_name' => $value_2['name'],
                        'to_currency_rate' => 0,
                        'date_updated' => date('Y-m-d H:i:s'),
                    ];
                }

            }
        }

        return $currency_rates;
    }

    /**
     * get currency rate
     * @param  boolean $id
     * @return [type]
     */
    public function get_currency_rate($id = false) {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'currency_rates')->row();
        }

        if ($id == false) {
            return $this->db->query('select * from ' . db_prefix() . 'currency_rates')->result_array();
        }
    }

    /**
     * update currency rate setting
     *
     * @param      array   $data   The data
     *
     * @return     boolean
     */
    public function update_setting_currency_rate($data) {
        $affectedRows = 0;
        if (!isset($data['cr_automatically_get_currency_rate'])) {
            $data['cr_automatically_get_currency_rate'] = 0;
        }

        foreach ($data as $key => $value) {
            $this->db->where('name', $key);
            $this->db->update(db_prefix() . 'options', [
                'value' => $value,
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
     * Gets the currency rate online.
     *
     * @param        $id     The identifier
     *
     * @return     bool    The currency rate online.
     */
    public function get_currency_rate_online($id) {
        $currency_rate = $this->get_currency_rate($id);

        if ($currency_rate) {
            return $this->currency_converter($currency_rate->from_currency_name, $currency_rate->to_currency_name);
        }

        return false;
    }

    /**
     * Gets all currency rate online.
     *
     * @return     bool  All currency rate online.
     */
    public function get_all_currency_rate_online() {
        $currency_rates = $this->get_currency_rate();
        $affectedRows = 0;
        foreach ($currency_rates as $currency_rate) {
            $rate = $this->currency_converter($currency_rate['from_currency_name'], $currency_rate['to_currency_name']);

            $data_update = ['to_currency_rate' => $rate];
            $success = $this->update_currency_rate($data_update, $currency_rate['id']);

            if ($success) {
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }

        return true;
    }

    /**
     * update currency rate
     * @param  [type] $data
     * @return [type]
     */
    public function update_currency_rate($data, $id) {

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'currency_rates', ['to_currency_rate' => $data['to_currency_rate'], 'date_updated' => date('Y-m-d H:i:s')]);
        if ($this->db->affected_rows() > 0) {
            $this->db->where('id', $id);
            $current_rate = $this->db->get(db_prefix() . 'currency_rates')->row();

            $data_log['from_currency_id'] = $current_rate->from_currency_id;
            $data_log['from_currency_name'] = $current_rate->from_currency_name;
            $data_log['to_currency_id'] = $current_rate->to_currency_id;
            $data_log['to_currency_name'] = $current_rate->to_currency_name;
            $data_log['from_currency_rate'] = isset($data['from_currency_rate']) ? $data['from_currency_rate'] : '';
            $data_log['to_currency_rate'] = isset($data['to_currency_rate']) ? $data['to_currency_rate'] : '';
            $data_log['date'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'currency_rate_logs', $data_log);
            return true;
        }
        return false;
    }

     /**
     * [currency_converter description]
     * @param  string $from   Currency Code
     * @param  string $to     Currency Code
     * @param  float $amount
     * @return float        
     */
    public function currency_converter($from,$to,$amount = 1)
    {   
        $url = "https://www.google.com/finance/quote/$from-$to";
        $response = $this->api_get($url);
        $string1 = explode('class="YMlKec fxKbKc">', $response);

        if(isset($string1[1])){

            $rate = explode('</div>', $string1[1]);

            if(isset($rate[0])){
                $result = $rate[0] * $amount;
                
                return $result;
            }
        }

        return false;
    }

    /**
     * api get
     * @param  string $url
     * @return string
     */
    public function api_get($url) {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);

        return curl_exec($curl);
    }

    /**
     * delete currency rate
     * @param  [type] $id
     * @return [type]
     */
    public function delete_currency_rate($id) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'currency_rates');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * { cronjob currency rates }
     *
     * @param        $manually  The manually
     *
     * @return     bool    
     */
    public function cronjob_currency_rates($manually) {
        $currency_rates = $this->get_currency_rate();
        foreach ($currency_rates as $currency_rate) {
            $data_insert = $currency_rate;
            $data_insert['date'] = date('Y-m-d');
            unset($data_insert['date_updated']);
            unset($data_insert['id']);

            $this->db->insert(db_prefix() . 'currency_rate_logs', $data_insert);
        }

        if (get_option('cr_automatically_get_currency_rate') == 1) {
            $this->get_all_currency_rate_online();
        }

        $asm_global_amount_expiration = get_option('cr_global_amount_expiration');
        if ($asm_global_amount_expiration != 0 && $asm_global_amount_expiration != '') {
            $this->db->where('date < "' . date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $asm_global_amount_expiration . ' days')).'"');
            $this->db->delete(db_prefix() . 'currency_rate_logs');
        }
        update_option('cr_date_cronjob_currency_rates', date('Y-m-d'));

        return true;
    }

    /**
     * [create_invoice_for_package description]
     * @return [type] [description]
     */
    public function create_invoice_for_package($package_id){
        $this->load->model('invoices_model');
        $this->load->model('clients_model');

        $order = $this->get_package($package_id);
        

        $agent = $this->clients_model->get($order->customer_id);

        $newitems = [];

     
        $unit_name = '';
        $tax_arr = [];

        array_push($newitems, array('order' => 1, 'description' => $order->shipping_prefix.$order->number_code, 'long_description' => '', 'qty' => 1, 'unit' => $unit_name, 'rate'=> $order->total, 'taxname' => $tax_arr));
    

        $this->db->where('selected_by_default', 1);
        $payment_mode_arr = $this->db->get(db_prefix().'payment_modes')->result_array();
        $pm_arr = [];
        foreach($payment_mode_arr as $pm){
            $pm_arr[] = $pm['id'];
        }

        $data['clientid'] = $order->customer_id;
        $data['billing_street'] = $agent->billing_street;
        $data['billing_city'] = $agent->billing_city;
        $data['billing_state'] = $agent->state;
        $data['billing_zip'] = $agent->billing_zip;
        $data['billing_country'] = $agent->billing_country;
        $data['include_shipping'] = 1;
        $data['show_shipping_on_invoice'] = 1;
        $data['shipping_street'] = $agent->shipping_street;
        $data['shipping_city'] = $agent->shipping_city;
        $data['shipping_state'] = $agent->state;
        $data['shipping_zip'] = $agent->shipping_zip;
        $data['allowed_payment_modes'] = $pm_arr;
        $date_format   = get_option('dateformat');
        $date_format   = explode('|', $date_format ?? '');
        $date_format   = $date_format[0];       
        $data['date'] = date($date_format);

        $data['duedate'] = _d(date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d")))));
        $data['terms'] = get_option('predefined_terms_invoice');

        $__number = get_option('next_invoice_number');
        $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);

        $data['currency'] = $order->currency;
        $data['newitems'] = $newitems;
        $data['number'] = $_invoice_number;
        $data['total'] = $order->total;
        $data['subtotal'] = $order->total;      
        $data['total_tax'] = 0;
        $data['discount_total'] = 0;
       
        $data['discount_type'] = 'after_tax';
        $data['sale_agent'] = '';
        $data['adjustment'] = 0;

        $id = $this->invoices_model->add($data);
        if($id){

            $this->db->where('id', $package_id);
            $this->db->update(db_prefix().'lg_packages', ['invoice_id' => $id]);

            return $id;
        }

        return false;
    }

    /**
     * [get_package_attachments description]
     * @return [type] [description]
     */
    public function get_package_attachments($package_id){
        $this->db->where('rel_type', 'lg_packages');
        $this->db->where('rel_id', $package_id);

        return $this->db->get(db_prefix().'files')->result_array();

    }

    /**
     * Gets the file.
     *
     * @param      <type>   $id      The file id
     * @param      boolean  $rel_id  The relative identifier
     *
     * @return     boolean  The file.
     */
    public function get_file($id, $rel_id = false)
    {
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix().'files')->row();

        if ($file && $rel_id) {
            if ($file->rel_id != $rel_id) {
                return false;
            }
        }
        return $file;
    }

    /**
     * Gets the part attachments.
     *
     * @param      <type>  $surope  The surope
     * @param      string  $id      The identifier
     *
     * @return     <type>  The part attachments.
     */
    public function get_package_dl_attachments($surope, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $assets);
        }
        $this->db->where('rel_type', 'lg_packages');
        $result = $this->db->get(db_prefix().'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     * { delete package attachment }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean 
     */
    public function delete_package_attachment($id)
    {
        $attachment = $this->get_package_dl_attachments('', $id);
        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'files');        
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Gets the part attachments.
     *
     * @param      <type>  $surope  The surope
     * @param      string  $id      The identifier
     *
     * @return     <type>  The part attachments.
     */
    public function get_shipping_dl_attachments($surope, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $assets);
        }
        $this->db->where('rel_type', 'lg_shipping');
        $result = $this->db->get(db_prefix().'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     * { delete shipping attachment }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean 
     */
    public function delete_shipment_attachment($id)
    {
        $attachment = $this->get_shipping_dl_attachments('', $id);
        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'files');        
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * [get_delivery_shipments description]
     * @return [type] [description]
     */
    public function get_delivery_shipments($package_id){
        $this->db->where('package_id', $package_id);
        return $this->db->get(db_prefix().'lg_packages_delivery_shipment')->row();
    }

    /**
     * [get_tracking_histories_package description]
     * @param  [type] $package_id [description]
     * @return [type]             [description]
     */
    public function get_tracking_histories_package($package_id){
        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'package');

        return $this->db->get(db_prefix().'lg_tracking_history')->result_array();


    }

    /**
     * [get_action_histories_package description]
     * @param  [type] $package_id [description]
     * @return [type]             [description]
     */
    public function get_action_histories_package($package_id){
        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'package');

        return $this->db->get(db_prefix().'lg_action_history')->result_array();


    }

    /**
     * [get_client_address description]
     * @return [type] [description]
     */
    public function get_client_address($id){

        $this->db->where('id', $id);
        return $this->db->get(db_prefix().'lg_client_address')->row();

    }

    /**
     * { package pdf }
     *
     * @param      <type>  $package  The pur package
     *
     * @return     <type>  ( package pdf )
     */
    public function package_pdf($package)
    {
        return app_pdf('lg_package', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Packages_pdf'), $package);
    }

    /**
     * [get_package_pdf_html description]
     * @return [type] [description]
     */
    public function get_package_pdf_html($package_data){

        if(!isset($package_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($package_data->customer_id);

        $package_number = $package_data->shipping_prefix.$package_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/package_code/barcode/'.$package_data->id.'/' . md5($package_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_getBarcode($package_number, $package_data->id);
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            <td width="40%" class="fs13 bold  text-center">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                            </td>
                            <td width="30%" class="text-right"><img class="images_w_table" src="' . $barcode_path . '" alt="' . $package_number . '" ><span class="fs11 print-item-code">#'.$package_number.format_lg_package_status($package_data->delivery_status).'</span></td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';

        // Customer, company information
        $html .= '<table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="header-height">
                            <td width="33%">
                                <strong class="text-uppercase">'._l('lg_bill_to').'</strong><br>
                                '.get_company_name($package_data->customer_id).'
                                <br><br>
                                '.$client->address.' '.$client->city.'<br>
                                '.get_country_name($client->country).'<br>
                                '.$client->phonenumber.'<br>
                                '.lg_get_contact_primary_email($package_data->customer_id).'

                            </td>

                            <td width="33%" class="text-center">
                                 <strong class="text-uppercase fs21">'._l('lg_locker_package').'</strong><br>
                                <strong>#'.$package_data->tracking_purchase.'</strong><br>
                                '.$package_data->store_supplier.'<br>
                            </td>

                            <td width="33%" class="text-right">
                                <strong class="text-uppercase">'._l('lg_package_information').'</strong><br><br>
                                <strong>'._l('lg_service_mode').': </strong>'.lg_get_service_name_by_id($package_data->service_mode).'<br>
                                <strong>'._l('lg_courrier_company').': </strong>'.lg_get_shipping_company_name($package_data->courrier_company).'<br>
                                <strong>'._l('lg_logistic_service').': </strong>'.lg_get_logistic_service_name_by_id($package_data->logistic_service_id).'<br>
                                <strong>'._l('lg_shipping_date').': </strong>'._dt($package_data->created_at).'<br>
                                <strong>'._l('lg_invoice').': </strong>#'.format_invoice_number($package_data->invoice_id).'<br>

                            </td>
                        </tr>
                    </tbody>
                </table><br><br>';

        $html .= '<table class="table border" border="0.1" data-type="estimate">
                                        <thead >
                                            <tr>
                                                <th class="thead-dark">'._l('lg_amount').'</th>
                                                <th class="thead-dark width20">'._l('lg_package_description').'</th>
                                                <th class="thead-dark">'._l('lg_weight').'</th>
                                                <th class="thead-dark">'._l('lg_length').'</th>
                                                <th class="thead-dark">'._l('lg_width').'</th>
                                                <th class="thead-dark">'._l('lg_height').'</th>
                                                <th class="thead-dark">'._l('lg_weight_vol').'</th>
                                                <th class="text-right thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="text-right thead-dark">'._l('lg_dec_value').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                                            $weight = 0;
                                            $volumetric_weight = 0;
                                            $total_dec = 0;
                                            foreach($package_data->package_detail as $key => $detail){ 
                                                if($detail['weight_vol'] >= $detail['weight']){
                                                    $volumetric_weight += $detail['weight_vol'];
                                                }else{
                                                    $weight += $detail['weight'];
                                                }

                                                $total_dec += $detail['dec_value'];

                                            
                                             $html .= '<tr>

                                                    <td>'.e($detail['amount']).'</td>
                                                    <td>'.e($detail['package_description']).'</td>
                                                    <td>'.e($detail['weight']).'</td>
                                                    <td>'.e($detail['length']).'</td>
                                                    <td>'.e($detail['width']).'</td>
                                                    <td>'.e($detail['height']).'</td>
                                                    <td>'.e($detail['weight_vol']).'</td>
                                                    <td class="text-right">'.e($detail['fixed_charge']).'</td>
                                                    <td class="text-right">'.e($detail['dec_value']).'</td>
                                                </tr>';

                                            }

                                            $html .= '<tr>

                                                <td colspan="6"><span class="bold">'._l('lg_price').' '.$package_data->weight_units_setting.': '.'</span>'.e($package_data->price_kg).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_subtotal').'</td>
                                                <td class="text-right">'.app_format_money($package_data->subtotal, $package_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="6"><span class="bold">'._l('lg_weight').': '.'</span>'.e($weight).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_discount'). ' '.$package_data->discount_percent.'%'.'</td>
                                                <td class="text-right">'.app_format_money($package_data->discount, $package_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_volumetric_weight').': '.'</span>'.e($volumetric_weight).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_total_weight_calculation').': '.'</span>'.e($volumetric_weight + $weight).'</td>
                                               
                                              
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<br><br><br><table class="table" border="0.1">
                                        <thead>
                                            <tr>
                                                <th class="thead-dark">'._l('lg_value_assured').'</th>
                                                <th class="thead-dark">'._l('lg_shipping_insurance').' '.app_format_number($package_data->shipping_insurance_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_custom_duties').' '.app_format_number($package_data->custom_duties_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_declared_total_value').'</th>
                                                <th class="thead-dark">'._l('lg_declared_value'). ' '.app_format_number($package_data->declared_value_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_tax'). ' '.app_format_number($package_data->tax_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="thead-dark">'._l('lg_reissue').'</th>
                                                <th class="thead-dark">'._l('lg_total').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>'.app_format_number($package_data->value_assured).'</td>
                                                <td>'.app_format_number($package_data->shipping_insurance).'</td>
                                                <td>'.app_format_number($package_data->custom_duties).'</td>
                                                <td>'.app_format_number($total_dec).'</td>
                                                <td>'.app_format_number($package_data->declared_value).'</td>
                                                <td>'.app_format_number($package_data->tax).'</td>
                                                <td>'.app_format_number($package_data->fixed_charge).'</td>
                                                <td>'.app_format_number($package_data->reissue).'</td>
                                                <td>'.app_format_money($package_data->total, $package_data->currency).'</td>
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<p class="fs21 text-uppercase text-center">'._l('lg_terms').'</p><hr>';
         $html .= '<table><thead><tr><td></td></tr></thead></table>';
        $html .= '<span>'.get_option('lg_default_invoice_terms').'</span><br><br><hr>';

        $html .= '<table>
                    <thead>
                        <tr>
                            <td class="text-center text-uppercase fs20"></td>
                             <td class="text-center text-uppercase fs20"></td>
                        </tr>
                        <tr>
                            <td class="text-center text-uppercase fs20">'.get_option('lg_invoice_company_signature').'</td>
                             <td class="text-center text-uppercase fs20">'.get_option('lg_customer_signature_billing').'</td>
                        </tr>
                    </thead>
                </table>';

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }

     /**
     * { package pdf }
     *
     * @param      <type>  $package  The pur package
     *
     * @return     <type>  ( package pdf )
     */
    public function package_label_pdf($package)
    {
        return app_pdf('lg_package', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Package_label_pdf'), $package);
    }

    /**
     * [get_package_label_pdf_html description]
     * @return [type] [description]
     */
    public function get_package_label_pdf_html($package_data){

        if(!isset($package_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($package_data->customer_id);

        $package_number = $package_data->shipping_prefix.$package_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/package_code/barcode/'.$package_data->id.'/' . md5($package_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_getBarcode($package_number, $package_data->id);
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            
                            <td width="30%" class="text-right fs13">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                                
                            </td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';

        // Customer, company information
        $html .= '<table class="table">
        
                    <tbody>
                        <tr class="header-height text-center">
                         
                            <td width="33%" >
                               <img class="" src="' . $barcode_path . '" alt="' . $package_number . '" ><span class="fs11 print-item-code">#'.$package_number.'</span>
                            </td>

                        </tr>
                    </tbody>
                </table>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="header-height">
                            <td width="33%" class="text-center" >
                                <strong class="text-uppercase fs24">'.$package_data->shipping_prefix.$package_data->number_code.'</strong><br>
                            </td>
                        </tr>
                    </tbody>
                </table>';

        $weight = 0;
        $volumetric_weight = 0;
        $total_dec = 0;
        $amount = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        foreach($package_data->package_detail as $key => $detail){ 
            $amount++;
            $length += $detail['length'];
            $width += $detail['width'];
            $height += $detail['height'];
            if($detail['weight_vol'] >= $detail['weight']){
                $volumetric_weight += $detail['weight_vol'];
            }else{
                $weight += $detail['weight'];
            }

            $total_dec += $detail['dec_value'];

        }

        $weight_units_setting = ($package_data->weight_units_setting != '' ) ? $package_data->weight_units_setting : get_option('lg_weight_units');
        $length_units_setting = ($package_data->length_units_setting != '' ) ? $package_data->length_units_setting : get_option('lg_length_units');

        $html .= '<table class="table">
                    <tbody>
                        <tr class="">
                            <td width="33%" colspan="2" >
                                <strong class="text-uppercase">'._l('lg_package_reference').'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_time').': </strong>'._d($package_data->created_at).'
                            </td>
                             <td>
                                <strong>'._l('lg_amount').': </strong>'.$amount.'
                            </td>
                        </tr>
                        <tr>
                           <td>
                                <strong>'._l('lg_weight').': </strong>'.$weight.' '.$weight_units_setting .'
                            </td>
                             <td>
                                <strong>'._l('lg_cost').': </strong>'.app_format_money($package_data->total, $package_data->currency).'
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_length').': </strong>'.$length.' '.$length_units_setting .'
                            </td>
                            <td>
                                <strong>'._l('lg_width').': </strong>'.$width.' '.$length_units_setting .'
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong>'._l('lg_height').': </strong>'.$height.' '.$length_units_setting .'
                            </td>
                            <td>
                                
                            </td>
                        </tr>
                
                    </tbody>
                </table><br><br>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="">
                            <td width="33%" >
                                <strong class="text-uppercase">'._l('lg_service_reference').'</strong>
                            </td>
                        </tr>

                        <tr class="">
                            <td width="33%" >
                                '.lg_get_service_name_by_id($package_data->service_mode).' | '.lg_get_shipping_company_name($package_data->courrier_company).'
                            </td>
                        </tr>
                
                    </tbody>
                </table><br><br>';

        $invoice_status_str = '';
        $invoice_status = '';
        if(is_numeric($package_data->invoice_id) && $package_data->invoice_id > 0){
            $invoice_status_str = format_invoice_number($package_data->invoice_id). '&nbsp;<span style="color:rgb(' . invoice_status_color_pdf(lg_format_invoice_status($package_data->invoice_id, 1)) . ');text-transform:uppercase;">' . format_invoice_status(lg_format_invoice_status($package_data->invoice_id, 1), '', false) . '</span>';
        }else{
            $invoice_status = 'pending';
            $invoice_status_str = '<span class="label label-warning">'._l('lg_pending').'</span>';
        }        

        $html .= '<table class="table">
            <tbody>
                <tr class="">
                    <td width="33%" class="text-center">
                        <strong class="text-uppercase fs21">'._l('lg_invoice_status').'</strong>
                    </td>
                </tr>

                <tr class="">
                    <td width="33%" class="text-center">
                       '.$invoice_status_str.'
                    </td>
                </tr>
        
            </tbody>
        </table>';

        $html .= '<br><br><table class="table">
            <tbody>
                <tr class="">
                    <td width="33%" class="text-center">
                        <strong class="text-uppercase fs21">'.lg_get_customer_address_str($package_data->customer_address).'</strong>
                    </td>
                </tr>
            </tbody>
        </table><br><br>';

        if($client->locker_code == '' ||  $client->locker_code == null){
            update_locker_code_for_client($client->userid);
        }

        $qrcode_path = FCPATH.'modules/logistic/uploads/package_code/qr/'.$package_data->id.'/' . md5($package_number ?? '').'.png';
        if(!file_exists($qrcode_path)){
            lg_getQrcode($package_number, $package_data->id);
        }


        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%"><img class="" src="' . $qrcode_path . '" alt="' . $package_number . '" ></td>
                            
                            <td width="30%" class="text-right ">
                                '._l('lg_sender').'<br>
                                <strong class="fs15">'.get_company_name($package_data->customer_id).'</strong><br>
                                '.lg_get_customer_address_str($package_data->customer_address, 1).'<br>
                                '.$client->phonenumber.'<br>
                                <strong>'._l('lg_locker').': '.$client->locker_code_prefix.$client->locker_code.'</strong>
                            </td>
                        </tr>
                    </tbody>
                </table><br><br>';
        

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }

    /**
     * [add_shipment_tracking description]
     */
    public function add_shipment_tracking($data){
        if(isset($data['package_id'])){
            unset($data['package_id']);
        }
        $data['time_update'] = to_sql_date($data['time_update'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_tracking_history', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            if($data['rel_type'] == 'package'){
                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'lg_packages', ['delivery_status' => $data['delivery_status']]);
            }

            $action_data = [];
            $action_data['rel_id'] = $data['rel_id'];
            $action_data['rel_type'] = 'package';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = _l('lg_new_shipment_tracking');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            $_package = $this->get_package( $data['rel_id']);
            $contact_id = get_primary_contact_user_id($_package->customer_id);
            if(is_numeric($contact_id) && $contact_id > 0){
                $contact = $this->clients_model->get_contact($contact_id);
                $template = mail_template('Logistic_package_shipment_tracking', 'logistic', $_package, $contact);
                $template->send();
            }

            return $insert_id;
        }
        return false;
    }

    /**
     * [get_delivery_shipment description]
     * @return [type] [description]
     */
    public function get_delivery_shipment($package_id){
        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'shipment_sign');

        return $this->db->get(db_prefix().'files')->row();
    }

    /**
     * [remove_package_shipment_sign description]
     * @return [type] [description]
     */
    public function remove_package_shipment_sign($package_id){

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'shipment_sign');
        $this->db->delete(db_prefix().'files');



        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/sign/'. $package_id)) {
           
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/sign/'. $package_id);
            
        }

        return true;

    }

    /**
     * [delivery_shipment description]
     * @return [type] [description]
     */
    public function delivery_shipment($data){

        $data['delivery_date'] = to_sql_date($data['delivery_date'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_packages_delivery_shipment', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            $this->db->where('style_name', 'delivered');
            $this->db->where('is_default_status', 1);
            $delivered_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

            $status = [];
            $status['delivery_status'] = (isset($delivered_status->id) ? $delivered_status->id : null);

            $this->db->where('id', $data['package_id']);
            $this->db->update(db_prefix().'lg_packages', $status);


            $action_data = [];
            $action_data['rel_id'] = $data['package_id'];
            $action_data['rel_type'] = 'package';
            $action_data['time_update'] =  $data['delivery_date'];
            $action_data['user'] = $data['created_by'];
            $action_data['action'] = _l('lg_delivery_shipment');
            $action_data['created_at'] = $data['created_at'];
            $action_data['created_by'] = $data['created_by'];

            $this->db->insert(db_prefix().'lg_action_history', $action_data);


            $tracking_history = [];
            $tracking_history['rel_id'] = $data['package_id'];
            $tracking_history['rel_type'] = 'package';
            $tracking_history['new_location'] = null;
            $tracking_history['delivery_status'] = $status['delivery_status'];
            $tracking_history['time_update'] =  $data['delivery_date'];
            $tracking_history['remark'] = ($data['note'] != '') ? $data['note'] : _l('lg_the_shipment_has_been_delivered');
            $tracking_history['created_at'] = $data['created_at'];
            $tracking_history['created_by'] = $data['created_by'];
            $this->db->insert(db_prefix().'lg_tracking_history', $tracking_history);


            $package = $this->get_package($data['package_id']);
            if($package){
                $contact_id = get_primary_contact_user_id($package->customer_id);
                if(is_numeric($contact_id) && $contact_id > 0){
                    $contact = $this->clients_model->get_contact($contact_id);
                    $template = mail_template('Logistic_package_delivered', 'logistic', $package, $contact);
                    $template->send();
                }


                if(is_numeric($package->created_by) && $package->created_by > 0){
                    $notified = add_notification([
                    'description'     => _l('lg_the_shipment_has_been_delivered'),
                    'link'            => 'logistic/package_detail/'.$data['package_id'],
                    'touserid'  => $package->created_by,
                    'fromcompany' => '',
                    'additional_data' => serialize([
                        $package->shipping_prefix.$package->number_code,
                    ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$package->created_by]);
                    }
                }
            }


            return $insert_id;
        }
        return false;
    }

    /**
     * [get_package_attachments description]
     * @return [type] [description]
     */
    public function get_package_shipment_attachments($package_id){
        $this->db->where('rel_type', 'shipment_attach');
        $this->db->where('rel_id', $package_id);

        return $this->db->get(db_prefix().'files')->result_array();

    }


     /**
     * Send package to client
     * @param  mixed  $id        invoiceid
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach package pdf or not
     * @return boolean
     */
    public function send_package_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false, $attachStatement = [])
    {
      
        $package = $this->get_package($id);
        

        if ($template_name == '') {
            $template_name = 'logistic_package_send_to_customer';
            
        }

        $emails_sent = [];
        $send_to     = [];

        
        $send_to = $this->input->post('sent_to');
        

        if (is_array($send_to) && count($send_to) > 0) {
     

            if ($attachpdf) {

                $package_html = $this->get_package_pdf_html($package);

                set_mailing_constant();
                $pdf    = $this->package_pdf($package_html);
                $attach = $pdf->Output($package->shipping_prefix.$package->number_code . '.pdf', 'S');
            }

            $i = 0;
            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, 'logistic', $package, $contact, $cc);

                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => str_replace('/', '-', $package->shipping_prefix.$package->number_code . '.pdf'),
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        $sent = true;
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } 

        if (count($emails_sent) > 0) {
        
            return true;
        }

        return false;
    }


    /**
     * [get_client_packages description]
     * @return [type] [description]
     */
    public function get_client_packages($client_id){
        $this->db->where('customer_id', $client_id);

        return $this->db->get(db_prefix().'lg_packages')->result_array();
    }

    /**
     * [get_pre_alert_list description]
     * @return [type] [description]
     */
    public function get_pre_alert_list($where = ''){

        if($where != ''){
            $this->db->where($where);
        }

        return $this->db->get(db_prefix().'lg_pre_alert')->result_array();
    }

    /**
     * [get_pre_alert description]
     * @return [type] [description]
     */
    public function get_pre_alert($id){
        $this->db->where('id', $id);
        return $this->db->get(db_prefix().'lg_pre_alert')->row();
    }

    /**
     * [add_pre_alert description]
     */
    public function add_pre_alert($data){

        $data['delivery_date'] = to_sql_date($data['delivery_date'], true);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_contact_user_id();

        $data['status'] = 1;

        $this->db->insert(db_prefix().'lg_pre_alert', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            $this->load->model('clients_model');
            $admins = $this->clients_model->get_admins($data['client_id']);

            $link = 'logistic/register_package/0/0/' . $insert_id;
            $additional_data = '#'.$data['tracking_purchase'];

            foreach($admins as $admin){
                $staff_id = $admin['staff_id'];

                $staff = $this->staff_model->get($staff_id);
                $notified = add_notification([
                'description'     => _l('lg_new_pre_alert_created_click_here_to_convert_package', $additional_data),
                'touserid'        => $staff->staffid,
                'link'            => $link,
                'additional_data' => serialize([
                    $additional_data,
                ]),
                ]);
                if ($notified) {
                    pusher_trigger_notification([$staff->staffid]);
                }

                $data_sm = [];
                $data_sm['email'] = $staff->email;
                $data_sm['link'] = admin_url($link);
                $data_sm['staff_name'] = $staff->full_name;
                $data_sm['pre_alert_id'] = $insert_id;
               
                $template = mail_template('new_pre_alert_created', 'logistic', array_to_object($data_sm));
                $template->send();
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * [update_pre_alert description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_pre_alert($data, $id){

        $data['delivery_date'] = to_sql_date($data['delivery_date'], true);

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_pre_alert', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }
        return false;

    }

    /**
     * [get_pre_alert_attachment description]
     * @return [type] [description]
     */
    public function get_pre_alert_attachment($id){
        $this->db->where('rel_type', 'pre_alert');
        $this->db->where('rel_id', $id);

        return $this->db->get(db_prefix().'files')->result_array();
    }

    /**
     * { delete package attachment }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean 
     */
    public function delete_pre_alert_attachment($id)
    {
        $attachment = $this->get_pre_alert_dl_attachments('', $id);
        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'files');        
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(LOGISTIC_MODULE_UPLOAD_FOLDER .'/pre_alert/'. $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix().'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/pre_alert/'. $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(LOGISTIC_MODULE_UPLOAD_FOLDER .'/pre_alert/'. $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/pre_alert/'. $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * [delete_pre_alert description]
     * @return [type] [description]
     */
    public function delete_pre_alert($id){

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_pre_alert');
        if($this->db->affected_rows()){

            $this->delete_pre_alert_attachment($id);

            return true;
        }
        return false;

    }

    /**
     * Gets the part attachments.
     *
     * @param      <type>  $surope  The surope
     * @param      string  $id      The identifier
     *
     * @return     <type>  The part attachments.
     */
    public function get_pre_alert_dl_attachments($surope, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $assets);
        }
        $this->db->where('rel_type', 'pre_alert');
        $result = $this->db->get(db_prefix().'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     * [get_invoices_for_package description]
     * @return [type] [description]
     */
    public function get_invoices_for_package($client_id, $currency = ''){
        $this->load->model('invoices_model');

        $invoices = $this->invoices_model->get('', ['clientid' => $client_id]);
        if($currency != ''){
            $invoices = $this->invoices_model->get('', ['clientid' => $client_id, 'currency' => $currency]);
        }


        return $invoices;

    }

    /**
     * [get_client_recipients description]
     * @return [type] [description]
     */
    public function get_client_recipients($client_id = ''){

        if(is_numeric($client_id)){
            $this->db->where('client_id', $client_id);
        }

        return $this->db->get(db_prefix().'lg_recipients')->result_array();
    }

    /**
     * [get_recipients_of_shipping description]
     * @return [type] [description]
     */
    public function get_recipients_of_shipping($recipient_id){

        $this->db->where('id', $recipient_id);
 
        return $this->db->get(db_prefix().'lg_recipients')->result_array();
    }

    /**
     * [add_recipient description]
     */
    public function add_recipient($data){

        $data['created_at'] = date("Y-m-d H:i:s");
        $data['created_by'] = get_contact_user_id();
        $data['created_by_type'] = 'client';

        if(isset($data['address'])){
            $address = $data['address'];
            unset($data['address']);
        }

        $this->db->insert(db_prefix().'lg_recipients', $data);
        $insert_id = $this->db->insert_id();

        if($insert_id){
            if(isset($address)){
                foreach($address as $ad){

                    $ad['recipient_id'] = $insert_id;

                    $this->db->insert(db_prefix().'lg_recipient_address', $ad);
                    $address_id = $this->db->insert_id();
                }

            }

            return $insert_id;
        }

        return false;

    }

    /**
     * [update_recipient description]
     * @return [type] [description]
     */
    public function update_recipient($data, $id){

        $updated = 0;

        if(isset($data['address_update'])){

            $address_update = $data['address_update'];
            unset($data['address_update']);
        }

        if(isset($data['removed_address_ids'])){

            $removed_address_ids = $data['removed_address_ids'];
            unset($data['removed_address_ids']);
        }

        if(isset($data['address'])){
            $address = $data['address'];
            unset($data['address']);
        }


        if(isset($removed_address_ids)){
            foreach($removed_address_ids as $address_id){
                $this->db->where('id', $address_id);
                $this->db->delete(db_prefix().'lg_recipient_address');
                if($this->db->affected_rows() > 0){
                    $updated++;
                }
            }
        }

        if(isset($address_update)){
            foreach($address_update as $ad){
                $this->db->where('id', $ad['id']);
                $this->db->update(db_prefix().'lg_recipient_address', $ad);
                if($this->db->affected_rows() > 0){
                    $updated++;
                }
            }
        }

        if(isset($address)){
            foreach($address as $ad){

                $ad['recipient_id'] = $id;

                $this->db->insert(db_prefix().'lg_recipient_address', $ad);
                $address_id = $this->db->insert_id();
                if($address_id){
                    $updated++;
                }
            }

        }


        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_recipients', $data);
        if($this->db->affected_rows() > 0){
            $updated++;
        }

        if($updated > 0){
            return true;
        }

        return false;

    }

    /**
     * [delete_recipient description]
     * @return [type] [description]
     */
    public function delete_recipient($id){

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'lg_recipients');
        if($this->db->affected_rows() > 0){

            $this->db->where('recipient_id', $id);
            $this->db->delete(db_prefix().'lg_recipient_address');

            return true;
        }

    }

    /**
     * [get_recipient description]
     * @return [type] [description]
     */
    public function get_recipient($id){
        $this->db->where('id', $id);
        $recipient = $this->db->get(db_prefix().'lg_recipients')->row();

        $this->db->where('recipient_id', $id);
        $recipient->address = $this->db->get(db_prefix().'lg_recipient_address')->result_array();

        return $recipient;
    }

    /**
     * [add_shipping description]
     */
    public function add_shipping($multiple, $data){
        $this->load->model('clients_model');

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = (isset($data['created_from']) && $data['created_from'] == 'admin') ? get_staff_user_id() : get_contact_user_id();

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['pre_alert_id'])){
            $pre_alert_id = $data['pre_alert_id'];
            unset($data['pre_alert_id']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        $this->db->where('style_name', 'pending');
        $this->db->where('is_default_status', 1);
        $pending_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

        if(!isset($data['delivery_status']) || $data['delivery_status'] == null){
            $data['delivery_status'] = (isset($pending_status->id) ? $pending_status->id : null);
        }

        if($multiple == 1){
            $rs_ids = [];

            if(isset($package_information)){
                foreach($package_information as $detail_data){


                    $data['number'] = get_shipment_next_number();
                    $data['number_code'] = $data['number'];
                    $data['number_type'] = get_option('lg_tracking_number_type');     


                    if($data['number_type'] == 'auto_increment'){
                        $data['number_code'] = str_pad($data['number'],get_option('lg_number_digits_to_track_locker_packages'),'0',STR_PAD_LEFT);
                    }

                    $final_row_weight = 0;
                    if($detail_data['weight'] >= $detail_data['weight_vol']){
                        $final_row_weight = $detail_data['weight'];
                    }else{
                        $final_row_weight = $detail_data['weight_vol'];
                    }

                    $data['subtotal'] = $final_row_weight*$data['price_kg'];
                    $data['discount'] = 0;
                    if(is_numeric($data['discount_percent'])){
                        $data['discount'] = $data['subtotal']*$data['discount_percent']/100;
                    }

                    $data['custom_duties'] = ($detail_data['weight'] + $detail_data['weight_vol'])*$data['custom_duties_percent']/100;

                    $data['tax'] = 0;
                    if($data['subtotal'] > ($data['minium_cost_to_apply_the_tax_setting'] * $data['currency_rate'])){
                        $data['tax'] = $data['subtotal']*$data['tax_percent']/100;
                    }

                    $data['declared_value'] = 0;
                    if($detail_data['dec_value'] > $data['minium_cost_to_apply_declared_tax_setting']){
                        $data['declared_value'] = $detail_data['dec_value'] * $data['declared_value_percent']/100;
                    }

                    $data['fixed_charge'] = $detail_data['fixed_charge'];

                    
                    if(!is_numeric($data['reissue'])){
                        $data['reissue'] = 0;
                    }

                    if(!is_numeric($data['shipping_insurance'])){
                        $data['shipping_insurance'] = 0;
                    }

                    if(!is_numeric($data['fixed_charge'])){
                        $data['fixed_charge'] = 0;
                    }



                    $data['total'] = $data['subtotal'] + $data['shipping_insurance'] + $data['custom_duties'] + $data['tax'] + $data['declared_value'] + $data['reissue'] + $data['fixed_charge'] - $data['discount']; 


                    $this->db->insert(db_prefix().'lg_shippings', $data);
                    $package_id = $this->db->insert_id();

                    if($package_id){
                        $detail_data['shipping_id'] = $package_id;
                        $detail_data['created_at'] = $data['created_at'];
                        $detail_data['created_by'] = $data['created_by'];
                        $this->db->insert(db_prefix().'lg_shipping_detail', $detail_data);

                        $rs_ids[] = $package_id;

                        $action_data = [];
                        $action_data['rel_id'] = $package_id;
                        $action_data['rel_type'] = 'shipping';
                        $action_data['time_update'] = date('Y-m-d H:i:s');
                        $action_data['user'] = (isset($data['created_from']) && $data['created_from'] == 'admin') ? get_staff_user_id() : 0;
                        $action_data['action'] = _l('lg_shipping_created');
                        $action_data['created_at'] = date('Y-m-d H:i:s');
                        $action_data['created_by'] = $data['created_by'];

                        $this->db->insert(db_prefix().'lg_action_history', $action_data);

                        if(isset($data['created_from']) && $data['created_from'] == 'admin'){
                            $contact_id = get_primary_contact_user_id($data['customer_id']);
                            $_shipping = $this->get_shipping($package_id);
                            if(is_numeric($contact_id) && $contact_id > 0){
                                $contact = $this->clients_model->get_contact($contact_id);
                                $template = mail_template('Logistic_shipping_created_send_to_customer', 'logistic', $_shipping, $contact);
                                $template->send();
                            }
                        }else if(isset($data['created_from']) && $data['created_from'] == 'client'){

                            $_shipping = $this->logistic_model->get_shipping($package_id);

                            $admins = $this->clients_model->get_admins($data['customer_id']);
                            foreach($admins as $admin){
                                $this->load->model('staff_model');
                                $staff = $this->staff_model->get($admin['staff_id']);


                                $template = mail_template('Logistic_pickup_created', 'logistic', $_shipping, $staff);
                                $template->send();
                                

                                $notified = add_notification([
                                'description'     => _l('lg_pickup_created'),
                                'link'            => 'logistic/shipping_detail/'.$package_id,
                                'touserid'  => $data['assign_driver'],
                                'fromcompany' => '',
                                'additional_data' => serialize([
                                    $_shipping->shipping_prefix.$_shipping->number_code,
                                ]),
                                ]);
                                if ($notified) {
                                    pusher_trigger_notification([$data['assign_driver']]);
                                }
                            }

                        }
                    }

                }
            }

            return $rs_ids;

        }else{

            $this->db->insert(db_prefix().'lg_shippings', $data);
            $insert_id = $this->db->insert_id();

            if($insert_id){

                if(isset($package_information)){
                    foreach($package_information as $detail_data){

                        $detail_data['shipping_id'] = $insert_id;
                        $detail_data['created_at'] = $data['created_at'];
                        $detail_data['created_by'] = $data['created_by'];
                        $this->db->insert(db_prefix().'lg_shipping_detail', $detail_data);
                    }
                }


                $action_data = [];
                $action_data['rel_id'] = $insert_id;
                $action_data['rel_type'] = 'shipping';
                $action_data['time_update'] = date('Y-m-d H:i:s');
                $action_data['user'] = (isset($data['created_from']) && $data['created_from'] == 'admin') ? get_staff_user_id() : 0;
                $action_data['action'] = _l('lg_shipping_created');
                $action_data['created_at'] = date('Y-m-d H:i:s');
                $action_data['created_by'] = $data['created_by'];

                $this->db->insert(db_prefix().'lg_action_history', $action_data);

                if(isset($data['created_from']) && $data['created_from'] == 'admin'){
                    $contact_id = get_primary_contact_user_id($data['customer_id']);
                    $_shipping = $this->get_shipping($insert_id);
                    if(is_numeric($contact_id) && $contact_id > 0){
                        $contact = $this->clients_model->get_contact($contact_id);
                        $template = mail_template('Logistic_shipping_created_send_to_customer', 'logistic', $_shipping, $contact);
                        $template->send();
                    }
                }else if(isset($data['created_from']) && $data['created_from'] == 'client'){

                    $_shipping = $this->logistic_model->get_shipping($insert_id);

                    $admins = $this->clients_model->get_admins($data['customer_id']);
                    foreach($admins as $admin){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get($admin['staff_id']);


                        $template = mail_template('Logistic_pickup_created', 'logistic', $_shipping, $staff);
                        $template->send();
                        

                        $notified = add_notification([
                        'description'     => _l('lg_pickup_created'),
                        'link'            => 'logistic/shipping_detail/'.$insert_id,
                        'touserid'  => $data['assign_driver'],
                        'fromcompany' => '',
                        'additional_data' => serialize([
                            $_shipping->shipping_prefix.$_shipping->number_code,
                        ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['assign_driver']]);
                        }
                    }

                }

                return $insert_id;
            }
        }
        return false;

    }


    /**
     * [update_shipping description]
     * @return [type] [description]
     */
    public function update_shipping($data, $id){

        $update_rs = 0;

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        if(isset($data['package_information_update'])){
            $package_information_update = $data['package_information_update'];
            unset($data['package_information_update']);
        }

        if(isset($data['removed_package_detail_ids'])){
            $removed_package_detail_ids = $data['removed_package_detail_ids'];
            unset($data['removed_package_detail_ids']);
        }

        if(isset($package_information)){
            foreach($package_information as $detail_data){

                $detail_data['shipping_id'] = $id;
                $this->db->insert(db_prefix().'lg_shipping_detail', $detail_data);
                $detail_inser_id = $this->db->insert_id();
                if($detail_inser_id){
                    $update_rs++; 
                }

            }
        }   

        if(isset($package_information_update)){
            foreach($package_information_update as $update_detail_data){

                $this->db->where('id', $update_detail_data['id']);
                $this->db->update(db_prefix().'lg_shipping_detail', $update_detail_data);
                if($this->db->affected_rows() > 0){
                    $update_rs++;
                }

            }
        }

        if(isset($removed_package_detail_ids)){
            foreach($removed_package_detail_ids as $detail_id){
                $this->db->where('id', $detail_id);
                $this->db->delete(db_prefix().'lg_shipping_detail');
                if($this->db->affected_rows() > 0){
                    $update_rs++;
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_shippings', $data);
        if($this->db->affected_rows() > 0){
            $update_rs++;
        }

        if($update_rs > 0){

            $action_data = [];
            $action_data['rel_id'] = $id;
            $action_data['rel_type'] = 'shipping';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = ($data['created_from'] == 'client') ? get_contact_user_id() : get_staff_user_id();
            $action_data['action'] = _l('lg_shipping_updated');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = (isset($data['created_from']) && $data['created_from'] == 'client') ? get_contact_user_id() : get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            return true;
        }
        return false;
    }

     /**
     * [get_shipping description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_shipping($id){

        $this->db->where('id', $id);
        $shipping = $this->db->get(db_prefix().'lg_shippings')->row();

        if($shipping){
            $this->db->where('shipping_id', $id);
            $shipping->shipment_detail = $this->db->get(db_prefix().'lg_shipping_detail')->result_array();

            return $shipping;
        }
        return false;
    }

    /**
     * [get_shipping_attachments description]
     * @return [type] [description]
     */
    public function get_shipping_attachments($shipping_id){
        $this->db->where('rel_type', 'lg_shipping');
        $this->db->where('rel_id', $shipping_id);

        return $this->db->get(db_prefix().'files')->result_array();
    }

    /**
     * [delete_package description]
     * @return [type] [description]
     */
    public function delete_package($package_id){
        $rs = 0;

        $this->db->where('id', $package_id);
        $this->db->delete(db_prefix().'lg_packages');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'lg_packages');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $package_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/packages/'. $package_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/package_code/barcode/'. $package_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/package_code/barcode/'. $package_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/package_code/qr/'. $package_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/package_code/qr/'. $package_id);
        }

        $this->db->where('package_id', $package_id);
        $this->db->delete(db_prefix().'lg_package_detail');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'package');
        $this->db->delete(db_prefix().'lg_action_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'package');
        $this->db->delete(db_prefix().'lg_tracking_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('package_id', $package_id);
        $this->db->delete(db_prefix().'lg_packages_delivery_shipment');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'shipment_sign');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'shipment_attach');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/sign/'. $package_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/sign/'. $package_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/attachments/'. $package_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/delivery_shipment/attachments/'. $package_id);
        }

        $this->db->where('package_id', $package_id);
        $this->db->delete(db_prefix().'lg_pre_alert');
        if($this->db->affected_rows() > 0){
            $rs++;
        }


        if($rs > 0){
            return true;
        }

        return false;
        

    }


    /**
     * [get_client_shippings description]
     * @return [type] [description]
     */
    public function get_client_shippings($client_id){
        $this->db->where('customer_id', $client_id);

        return $this->db->get(db_prefix().'lg_shippings')->result_array();
    }


    /**
     * { shipping pdf }
     *
     * @param      <type>  $shipping  The pur shipping
     *
     * @return     <type>  ( shipping pdf )
     */
    public function shipping_pdf($shipping)
    {
        return app_pdf('lg_shipping', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Shippings_pdf'), $shipping);
    }

    /**
     * [get_shipping_pdf_html description]
     * @return [type] [description]
     */
    public function get_shipping_pdf_html($shipping_data){

        if(!isset($shipping_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($shipping_data->customer_id);

        $shipping_number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/shipping_code/barcode/'.$shipping_data->id.'/' . md5($shipping_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_shipping_getBarcode($shipping_number, $shipping_data->id);
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            <td width="40%" class="fs13 bold  text-center">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                            </td>
                            <td width="30%" class="text-right"><img class="images_w_table" src="' . $barcode_path . '" alt="' . $shipping_number . '" ><span class="fs11 print-item-code">#'.$shipping_number.format_lg_package_status($shipping_data->delivery_status).'</span></td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';

        // Customer, company information
        $html .= '<table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="header-height">
                            <td width="33%">
                                <strong class="text-uppercase">'._l('lg_bill_to').'</strong><br>
                                '.get_company_name($shipping_data->customer_id).'
                                <br><br>
                                '.$client->address.' '.$client->city.'<br>
                                '.get_country_name($client->country).'<br>
                                '.$client->phonenumber.'<br>
                                '.lg_get_contact_primary_email($shipping_data->customer_id).'

                            </td>

                            <td width="33%" class="text-center">
                                 <strong class="text-uppercase fs21">'._l('lg_shipment').'</strong><br>
                                
                            </td>

                            <td width="33%" class="text-right">
                                <strong class="text-uppercase">'._l('lg_shipping_information').'</strong><br><br>
                                <strong>'._l('lg_service_mode').': </strong>'.lg_get_service_name_by_id($shipping_data->service_mode).'<br>
                                <strong>'._l('lg_courrier_company').': </strong>'.lg_get_shipping_company_name($shipping_data->courrier_company).'<br>
                                <strong>'._l('lg_logistic_service').': </strong>'.lg_get_logistic_service_name_by_id($shipping_data->logistic_service_id).'<br>
                                <strong>'._l('lg_shipping_date').': </strong>'._dt($shipping_data->created_at).'<br>
                                <strong>'._l('lg_invoice').': </strong>#'.format_invoice_number($shipping_data->invoice_id).'<br>

                            </td>
                        </tr>
                    </tbody>
                </table><br><br>';

        $html .= '<table class="table border" border="0.1" data-type="estimate">
                                        <thead >
                                            <tr>
                                                <th class="thead-dark">'._l('lg_amount').'</th>
                                                <th class="thead-dark width20">'._l('lg_package_description').'</th>
                                                <th class="thead-dark">'._l('lg_weight').'</th>
                                                <th class="thead-dark">'._l('lg_length').'</th>
                                                <th class="thead-dark">'._l('lg_width').'</th>
                                                <th class="thead-dark">'._l('lg_height').'</th>
                                                <th class="thead-dark">'._l('lg_weight_vol').'</th>
                                                <th class="text-right thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="text-right thead-dark">'._l('lg_dec_value').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                                            $weight = 0;
                                            $volumetric_weight = 0;
                                            $total_dec = 0;
                                            foreach($shipping_data->shipment_detail as $key => $detail){ 
                                                if($detail['weight_vol'] >= $detail['weight']){
                                                    $volumetric_weight += $detail['weight_vol'];
                                                }else{
                                                    $weight += $detail['weight'];
                                                }

                                                $total_dec += $detail['dec_value'];

                                            
                                             $html .= '<tr>

                                                    <td>'.e($detail['amount']).'</td>
                                                    <td>'.e($detail['package_description']).'</td>
                                                    <td>'.e($detail['weight']).'</td>
                                                    <td>'.e($detail['length']).'</td>
                                                    <td>'.e($detail['width']).'</td>
                                                    <td>'.e($detail['height']).'</td>
                                                    <td>'.e($detail['weight_vol']).'</td>
                                                    <td class="text-right">'.e($detail['fixed_charge']).'</td>
                                                    <td class="text-right">'.e($detail['dec_value']).'</td>
                                                </tr>';

                                            }

                                            $html .= '<tr>

                                                <td colspan="6"><span class="bold">'._l('lg_price').' '.$shipping_data->weight_units_setting.': '.'</span>'.e($shipping_data->price_kg).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_subtotal').'</td>
                                                <td class="text-right">'.app_format_money($shipping_data->subtotal, $shipping_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="6"><span class="bold">'._l('lg_weight').': '.'</span>'.e($weight).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_discount'). ' '.$shipping_data->discount_percent.'%'.'</td>
                                                <td class="text-right">'.app_format_money($shipping_data->discount, $shipping_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_volumetric_weight').': '.'</span>'.e($volumetric_weight).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_total_weight_calculation').': '.'</span>'.e($volumetric_weight + $weight).'</td>
                                               
                                              
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<br><br><br><table class="table" border="0.1">
                                        <thead>
                                            <tr>
                                                <th class="thead-dark">'._l('lg_value_assured').'</th>
                                                <th class="thead-dark">'._l('lg_shipping_insurance').' '.app_format_number($shipping_data->shipping_insurance_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_custom_duties').' '.app_format_number($shipping_data->custom_duties_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_declared_total_value').'</th>
                                                <th class="thead-dark">'._l('lg_declared_value'). ' '.app_format_number($shipping_data->declared_value_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_tax'). ' '.app_format_number($shipping_data->tax_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="thead-dark">'._l('lg_reissue').'</th>
                                                <th class="thead-dark">'._l('lg_total').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>'.app_format_number($shipping_data->value_assured).'</td>
                                                <td>'.app_format_number($shipping_data->shipping_insurance).'</td>
                                                <td>'.app_format_number($shipping_data->custom_duties).'</td>
                                                <td>'.app_format_number($total_dec).'</td>
                                                <td>'.app_format_number($shipping_data->declared_value).'</td>
                                                <td>'.app_format_number($shipping_data->tax).'</td>
                                                <td>'.app_format_number($shipping_data->fixed_charge).'</td>
                                                <td>'.app_format_number($shipping_data->reissue).'</td>
                                                <td>'.app_format_money($shipping_data->total, $shipping_data->currency).'</td>
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<p class="fs21 text-uppercase text-center">'._l('lg_terms').'</p><hr>';
         $html .= '<table><thead><tr><td></td></tr></thead></table>';
        $html .= '<span>'.get_option('lg_default_invoice_terms').'</span><br><br><hr>';

        $html .= '<table>
                    <thead>
                        <tr>
                            <td class="text-center text-uppercase fs20"></td>
                             <td class="text-center text-uppercase fs20"></td>
                        </tr>
                        <tr>
                            <td class="text-center text-uppercase fs20">'.get_option('lg_invoice_company_signature').'</td>
                             <td class="text-center text-uppercase fs20">'.get_option('lg_customer_signature_billing').'</td>
                        </tr>
                    </thead>
                </table>';

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }


    /**
     * { shipping pdf }
     *
     * @param      <type>  $shipping  The pur shipping
     *
     * @return     <type>  ( shipping pdf )
     */
    public function shipping_label_pdf($shipping)
    {
        return app_pdf('lg_shipping', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Shipping_label_pdf'), $shipping);
    }

    /**
     * [get_shipping_label_pdf_html description]
     * @return [type] [description]
     */
    public function get_shipping_label_pdf_html($shipping_data){

        if(!isset($shipping_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($shipping_data->customer_id);

        $shipping_number = $shipping_data->shipping_prefix.$shipping_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/shipping_code/barcode/'.$shipping_data->id.'/' . md5($shipping_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_shipping_getBarcode($shipping_number, $shipping_data->id);
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            
                            <td width="30%" class="text-right fs13">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                                
                            </td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';

        // Customer, company information
        $html .= '<table class="table">
        
                    <tbody>
                        <tr class="header-height text-center">
                         
                            <td width="33%" >
                               <img class="" src="' . $barcode_path . '" alt="' . $shipping_number . '" ><span class="fs11 print-item-code">#'.$shipping_number.'</span>
                            </td>

                        </tr>
                    </tbody>
                </table>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="header-height">
                            <td width="33%" class="text-center" >
                                <strong class="text-uppercase fs24">'.$shipping_data->shipping_prefix.$shipping_data->number_code.'</strong><br>
                            </td>
                        </tr>
                    </tbody>
                </table>';

        $weight = 0;
        $volumetric_weight = 0;
        $total_dec = 0;
        $amount = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        foreach($shipping_data->shipment_detail as $key => $detail){ 
            $amount++;
            $length += $detail['length'];
            $width += $detail['width'];
            $height += $detail['height'];
            if($detail['weight_vol'] >= $detail['weight']){
                $volumetric_weight += $detail['weight_vol'];
            }else{
                $weight += $detail['weight'];
            }

            $total_dec += $detail['dec_value'];

        }

        $weight_units_setting = ($shipping_data->weight_units_setting != '' ) ? $shipping_data->weight_units_setting : get_option('lg_weight_units');
        $length_units_setting = ($shipping_data->length_units_setting != '' ) ? $shipping_data->length_units_setting : get_option('lg_length_units');

        $html .= '<table class="table">
                    <tbody>
                        <tr class="">
                            <td width="33%" colspan="2" >
                                <strong class="text-uppercase">'._l('lg_package_reference').'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_time').': </strong>'._d($shipping_data->created_at).'
                            </td>
                             <td>
                                <strong>'._l('lg_amount').': </strong>'.$amount.'
                            </td>
                        </tr>
                        <tr>
                           <td>
                                <strong>'._l('lg_weight').': </strong>'.$weight.' '.$weight_units_setting.'
                            </td>
                             <td>
                                <strong>'._l('lg_cost').': </strong>'.app_format_money($shipping_data->total, $shipping_data->currency).'
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_length').': </strong>'.$length.' '.$length_units_setting.'
                            </td>
                            <td>
                                <strong>'._l('lg_width').': </strong>'.$width.' '.$length_units_setting.'
                            </td>
                        </tr>

                        <tr>
                            <td>
                            </td>
                            <td>
                                <strong>'._l('lg_height').': </strong>'.$height.' '.$length_units_setting.'
                            </td>
                        </tr>
                
                    </tbody>
                </table><br><br>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="">
                            <td width="33%" >
                                <strong class="text-uppercase">'._l('lg_service_reference').'</strong>
                            </td>
                        </tr>

                        <tr class="">
                            <td width="33%" >
                                '.lg_get_service_name_by_id($shipping_data->service_mode).' | '.lg_get_shipping_company_name($shipping_data->courrier_company).'
                            </td>
                        </tr>
                
                    </tbody>
                </table><br><br>';

        $invoice_status_str = '';
        $invoice_status = '';
        if(is_numeric($shipping_data->invoice_id) && $shipping_data->invoice_id > 0){
            $invoice_status_str = format_invoice_number($shipping_data->invoice_id). '&nbsp;<span style="color:rgb(' . invoice_status_color_pdf(lg_format_invoice_status($shipping_data->invoice_id, 1)) . ');text-transform:uppercase;">' . format_invoice_status(lg_format_invoice_status($shipping_data->invoice_id, 1), '', false) . '</span>';
        }else{
            $invoice_status = 'pending';
            $invoice_status_str = '<span class="label label-warning">'._l('lg_pending').'</span>';
        }        

        $html .= '<table class="table">
            <tbody>
                <tr class="">
                    <td width="33%" class="text-center">
                        <strong class="text-uppercase fs21">'._l('lg_invoice_status').'</strong>
                    </td>
                </tr>

                <tr class="">
                    <td width="33%" class="text-center">
                       '.$invoice_status_str.'
                    </td>
                </tr>
        
            </tbody>
        </table>';

        $html .= '<br><br><table class="table">
            <tbody>
                <tr class="">
                    <td width="33%" class="text-center">
                        <strong class="text-uppercase fs21">'.lg_get_recipient_address_str($shipping_data->recipient_address_id).'</strong>
                    </td>
                </tr>
            </tbody>
        </table><br><br>';

        if($client->locker_code == '' ||  $client->locker_code == null){
            update_locker_code_for_client($client->userid);
        }

        $qrcode_path = FCPATH.'modules/logistic/uploads/shipping_code/qr/'.$shipping_data->id.'/' . md5($shipping_number ?? '').'.png';
        if(!file_exists($qrcode_path)){
            lg_shipping_getQrcode($shipping_number, $shipping_data->id);
        }

        $recipient = $this->get_recipient($shipping_data->recipient_id);
        $recipient_address = $this->get_recipient_address($shipping_data->recipient_address_id);

        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                           
                            
                            <td width="30%" class="text-left ">
                                '._l('lg_sender').'<br>
                                <strong class="fs15">'.get_company_name($shipping_data->customer_id).'</strong><br>
                                '.lg_get_customer_address_str($shipping_data->customer_address, 1).'<br>
                                '.$client->phonenumber.'<br>
                               
                            </td>
                             <td width="30%" class="text-right">
                             '._l('lg_recipient').'<br>
                             <strong class="fs15">'.$recipient->first_name.' '.$recipient->last_name.'</strong><br>
                             '.$recipient_address->address.'<br>
                             '.$recipient->phone.'
                             </td>

                        </tr>
                        <tr>
                        <td width="30%"><img class="" src="' . $qrcode_path . '" alt="' . $shipping_number . '" ></td>
                            
                            <td width="30%" class="text-right ">
                               
                            </td>

                        </tr>
                    </tbody>
                </table><br><br>';
        

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }

    /**
     * Send shipping to client
     * @param  mixed  $id        invoiceid
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach shipping pdf or not
     * @return boolean
     */
    public function send_shipping_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false, $attachStatement = [])
    {
      
        $shipping = $this->get_shipping($id);
        

        if ($template_name == '') {
            $template_name = 'Logistic_shipping_send_to_customer';
            
        }

        $emails_sent = [];
        $send_to     = [];

        
        $send_to = $this->input->post('sent_to');
        

        if (is_array($send_to) && count($send_to) > 0) {
     

            if ($attachpdf) {

                $shipping_html = $this->get_shipping_pdf_html($shipping);

                set_mailing_constant();
                $pdf    = $this->shipping_pdf($shipping_html);
                $attach = $pdf->Output($shipping->shipping_prefix.$shipping->number_code . '.pdf', 'S');
            }

            $i = 0;
            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->get_recipient($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, 'logistic', $shipping, $contact, $cc);

                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => str_replace('/', '-', $shipping->shipping_prefix.$shipping->number_code . '.pdf'),
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        $sent = true;
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } 

        if (count($emails_sent) > 0) {
        
            return true;
        }

        return false;
    }

    /**
     * [create_invoice_for_shipping description]
     * @return [type] [description]
     */
    public function create_invoice_for_shipping($shipping_id){
        $this->load->model('invoices_model');
        $this->load->model('clients_model');

        $order = $this->get_shipping($shipping_id);
        

        $agent = $this->clients_model->get($order->customer_id);

        $newitems = [];

     
        $unit_name = '';
        $tax_arr = [];

        array_push($newitems, array('order' => 1, 'description' => $order->shipping_prefix.$order->number_code, 'long_description' => '', 'qty' => 1, 'unit' => $unit_name, 'rate'=> $order->total, 'taxname' => $tax_arr));
    

        $this->db->where('selected_by_default', 1);
        $payment_mode_arr = $this->db->get(db_prefix().'payment_modes')->result_array();
        $pm_arr = [];
        foreach($payment_mode_arr as $pm){
            $pm_arr[] = $pm['id'];
        }

        $data['clientid'] = $order->customer_id;
        $data['billing_street'] = $agent->billing_street;
        $data['billing_city'] = $agent->billing_city;
        $data['billing_state'] = $agent->state;
        $data['billing_zip'] = $agent->billing_zip;
        $data['billing_country'] = $agent->billing_country;
        $data['include_shipping'] = 1;
        $data['show_shipping_on_invoice'] = 1;
        $data['shipping_street'] = $agent->shipping_street;
        $data['shipping_city'] = $agent->shipping_city;
        $data['shipping_state'] = $agent->state;
        $data['shipping_zip'] = $agent->shipping_zip;
        $data['allowed_payment_modes'] = $pm_arr;
        $date_format   = get_option('dateformat');
        $date_format   = explode('|', $date_format ?? '');
        $date_format   = $date_format[0];       
        $data['date'] = date($date_format);

        $data['duedate'] = _d(date("Y-m-d", strtotime("+1 month", strtotime(date("Y-m-d")))));
        $data['terms'] = get_option('predefined_terms_invoice');

        $__number = get_option('next_invoice_number');
        $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);

        $data['currency'] = $order->currency;
        $data['newitems'] = $newitems;
        $data['number'] = $_invoice_number;
        $data['total'] = $order->total;
        $data['subtotal'] = $order->total;      
        $data['total_tax'] = 0;
        $data['discount_total'] = 0;
       
        $data['discount_type'] = 'after_tax';
        $data['sale_agent'] = '';
        $data['adjustment'] = 0;

        $id = $this->invoices_model->add($data);
        if($id){

            $this->db->where('id', $shipping_id);
            $this->db->update(db_prefix().'lg_shippings', ['invoice_id' => $id]);

            return $id;
        }

        return false;
    }


    /**
     * [get_shipping_attachments description]
     * @return [type] [description]
     */
    public function get_shipping_shipment_attachments($shipping_id){
        $this->db->where('rel_type', 'shipping_attach');
        $this->db->where('rel_id', $shipping_id);

        return $this->db->get(db_prefix().'files')->result_array();

    }

    /**
     * [get_tracking_histories_shipping description]
     * @param  [type] $shipping_id [description]
     * @return [type]             [description]
     */
    public function get_tracking_histories_shipping($shipping_id){
        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping');

        return $this->db->get(db_prefix().'lg_tracking_history')->result_array();


    }

    /**
     * [get_action_histories_shipping description]
     * @param  [type] $shipping_id [description]
     * @return [type]             [description]
     */
    public function get_action_histories_shipping($shipping_id){
        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping');

        return $this->db->get(db_prefix().'lg_action_history')->result_array();


    }


     /**
     * [delivery_shipment description]
     * @return [type] [description]
     */
    public function shipping_delivery_shipment($data){
        $this->load->model('clients_model');
        $data['delivery_date'] = to_sql_date($data['delivery_date'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_shippings_delivery_shipment', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            $this->db->where('style_name', 'delivered');
            $this->db->where('is_default_status', 1);
            $delivered_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

            $status = [];
            $status['delivery_status'] = (isset($delivered_status->id) ? $delivered_status->id : null);

            $this->db->where('id', $data['shipping_id']);
            $this->db->update(db_prefix().'lg_shippings', $status);


            $action_data = [];
            $action_data['rel_id'] = $data['shipping_id'];
            $action_data['rel_type'] = 'shipping';
            $action_data['time_update'] =  $data['delivery_date'];
            $action_data['user'] = $data['created_by'];
            $action_data['action'] = _l('lg_delivery_shipment');
            $action_data['created_at'] = $data['created_at'];
            $action_data['created_by'] = $data['created_by'];

            $this->db->insert(db_prefix().'lg_action_history', $action_data);


            $tracking_history = [];
            $tracking_history['rel_id'] = $data['shipping_id'];
            $tracking_history['rel_type'] = 'shipping';
            $tracking_history['new_location'] = null;
            $tracking_history['delivery_status'] = $status['delivery_status'];
            $tracking_history['time_update'] =  $data['delivery_date'];
            $tracking_history['remark'] = ($data['note'] != '') ? $data['note'] : _l('lg_the_shipment_has_been_delivered');
            $tracking_history['created_at'] = $data['created_at'];
            $tracking_history['created_by'] = $data['created_by'];
            $this->db->insert(db_prefix().'lg_tracking_history', $tracking_history);


            $shipping = $this->get_shipping($data['shipping_id']);
            if($shipping){
                $contact_id = get_primary_contact_user_id($shipping->customer_id);
                if(is_numeric($contact_id) && $contact_id > 0){
                    $contact = $this->clients_model->get_contact($contact_id);
                    $template = mail_template('logistic_shipping_delivered', 'logistic', $shipping, $contact);
                    $template->send();
                }


                if(is_numeric($shipping->created_by) && $shipping->created_by > 0){
                    $notified = add_notification([
                    'description'     => _l('lg_the_shipment_has_been_delivered'),
                    'link'            => 'logistic/shipping_detail/'.$data['shipping_id'],
                    'touserid'  => $shipping->created_by,
                    'fromcompany' => '',
                    'additional_data' => serialize([
                        $shipping->shipping_prefix.$shipping->number_code,
                    ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$shipping->created_by]);
                    }
                }
            }


            return $insert_id;
        }
        return false;
    }

    /**
     * [get_delivery_shipment description]
     * @return [type] [description]
     */
    public function get_shipping_delivery_shipment($package_id){
        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'shipping_sm_sign');

        return $this->db->get(db_prefix().'files')->row();
    }

    /**
     * [remove_shipping_shipment_sign description]
     * @return [type] [description]
     */
    public function remove_shipping_shipment_sign($shipping_id){

        $rs = 0;
        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping_sm_sign');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }


        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/sign/'. $shipping_id)) {
           
            if(delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/sign/'. $shipping_id)){
                $rs++;
            }
            
        }

        if($rs > 1){
            return true;
        }
        return false;

    }

    /**
     * [get_shipping_delivery_shipments description]
     * @return [type] [description]
     */
    public function get_shipping_delivery_shipments($shipping_id){
        $this->db->where('shipping_id', $shipping_id);
        return $this->db->get(db_prefix().'lg_shippings_delivery_shipment')->row();
    }

    /**
     * [add_shipping_shipment_tracking description]
     */
    public function add_shipping_shipment_tracking($data){
        if(isset($data['shipping_id'])){
            unset($data['shipping_id']);
        }
        $data['time_update'] = to_sql_date($data['time_update'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_tracking_history', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            if($data['rel_type'] == 'shipping'){
                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'lg_shippings', ['delivery_status' => $data['delivery_status']]);
            }

            $action_data = [];
            $action_data['rel_id'] = $data['rel_id'];
            $action_data['rel_type'] = 'shipping';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = _l('lg_new_shipment_tracking');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            $_shipping = $this->get_shipping( $data['rel_id']);
            $contact_id = get_primary_contact_user_id($_shipping->customer_id);
            if(is_numeric($contact_id) && $contact_id > 0){
                $contact = $this->clients_model->get_contact($contact_id);
                $template = mail_template('Logistic_shipping_shipment_tracking', 'logistic', $_shipping, $contact);
                $template->send();
            }

            return $insert_id;
        }
        return false;
    }

     /**
     * [delete_shipping description]
     * @return [type] [description]
     */
    public function delete_shipping($shipping_id){
        $rs = 0;

        $this->db->where('id', $shipping_id);
        $this->db->delete(db_prefix().'lg_shippings');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'lg_shipping');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $shipping_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $shipping_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_code/barcode/'. $shipping_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_code/barcode/'. $shipping_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_code/qr/'. $shipping_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_code/qr/'. $shipping_id);
        }

        $this->db->where('shipping_id', $shipping_id);
        $this->db->delete(db_prefix().'lg_shipping_detail');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping');
        $this->db->delete(db_prefix().'lg_action_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping');
        $this->db->delete(db_prefix().'lg_tracking_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('shipping_id', $shipping_id);
        $this->db->delete(db_prefix().'lg_shippings_delivery_shipment');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping_sm_sign');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $shipping_id);
        $this->db->where('rel_type', 'shipping_attach');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/attachments/'. $shipping_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/attachments/'. $shipping_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/sign/'. $shipping_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shipping_delivery_shipment/sign/'. $shipping_id);
        }




        if($rs > 0){
            return true;
        }

        return false;
        

    }

    /**
     * { delete  hippingattachment }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean 
     */
    public function delete_shipping_attachment($id)
    {
        $attachment = $this->get_shipping_dl_attachments('', $id);
        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'files');        
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/shippings/'. $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * [get_recipient_address description]
     * @return [type] [description]
     */
    public function get_recipient_address($recipient_address_id){
        $this->db->where('id', $recipient_address_id);
        return $this->db->get(db_prefix().'lg_recipient_address')->row();
    }

    /**
     * [approve_pickup description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function approve_pickup($data){

        if(!isset($data['shipping_id'])){
            return false;
        }

        $shipping = $this->get_shipping($data['shipping_id']);

        $status = null;
        $shipping_type = 'pickup';
        $action_str = '';

        $template_name = '';

        if($data['approval_status'] == 'approved'){
            $this->db->where('style_name', 'approved');
            $this->db->where('is_default_status', 1);
            $approved_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

            $status = isset($approved_status->id) ? $approved_status->id : null;

            $shipping_type = 'shipping';

            $action_str = _l('approved_pickup');

            $template_name = 'pickup_approved';

        }else{
            $this->db->where('style_name', 'rejected');
            $this->db->where('is_default_status', 1);
            $rejected_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

            $status = isset($rejected_status->id) ? $rejected_status->id : null;

            $shipping_type = 'pickup';

            $action_str = _l('rejected_pickup');

            $template_name = 'pickup_rejected';
        }

        $this->db->where('id', $data['shipping_id']);
        $this->db->update(db_prefix().'lg_shippings', [
            'delivery_status' => $status,
            'approve_status' => $data['approval_status'],
            'approve_note' => $data['approve_note'],
            'shipping_type' => $shipping_type,

        ]);

        if($this->db->affected_rows() > 0){

            $action_data = [];
            $action_data['rel_id'] = $data['shipping_id'];
            $action_data['rel_type'] = 'shipping';
            $action_data['time_update'] =  $data['delivery_date'];
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = $action_str;
            $action_data['created_at'] = $data['created_at'];
            $action_data['created_by'] = get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);


            if($template_name != ''){
                $contact = $this->clients_model->get_contact($shipping->created_by);

                if ($contact && $shipping->created_from == 'client') {
                    $template = mail_template($template_name, 'logistic', $shipping, $contact);
                    $template->send();
                }
                
            }
             
            return true;
        }
        return false;

    }

    

    /**
     * [get_consolidation description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_consolidation($id){

        $this->db->where('id', $id);
        $consolidation = $this->db->get(db_prefix().'lg_consolidated')->row();

        if($consolidation){
            $this->db->where('consolidated_id', $id);
            $consolidation->shipment_detail = $this->db->get(db_prefix().'lg_consolidated_detail')->result_array();

            return $consolidation;
        }
        return false;
    }


    /**
     * [get_consolidation_attachments description]
     * @return [type] [description]
     */
    public function get_consolidation_attachments($consolidated_id){
        $this->db->where('rel_type', 'lg_consolidated');
        $this->db->where('rel_id', $consolidated_id);

        return $this->db->get(db_prefix().'files')->result_array();
    }

    /**
     * [add_consolidation description]
     */
    public function add_consolidation( $data){

        $this->load->model('clients_model');
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] =  get_staff_user_id();

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['pre_alert_id'])){
            $pre_alert_id = $data['pre_alert_id'];
            unset($data['pre_alert_id']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        if(isset($data['rel_id'])){
            $rel_ids = $data['rel_id'];
            $data['rel_id'] = implode(',', $data['rel_id']);
        }

        $this->db->where('style_name', 'pending');
        $this->db->where('is_default_status', 1);
        $pending_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

        if(!isset($data['delivery_status']) || $data['delivery_status'] == null){
            $data['delivery_status'] = (isset($pending_status->id) ? $pending_status->id : null);
        }

        

        $this->db->insert(db_prefix().'lg_consolidated', $data);
        $insert_id = $this->db->insert_id();

        if($insert_id){

            if(isset($package_information)){
                foreach($package_information as $detail_data){

                    $detail_data['consolidated_id'] = $insert_id;
                    $detail_data['created_at'] = $data['created_at'];
                    $detail_data['created_by'] = $data['created_by'];
                    $this->db->insert(db_prefix().'lg_consolidated_detail', $detail_data);
                }
            }

            if(count($rel_ids) > 0){
                $this->db->where('style_name', 'consolidate');
                $this->db->where('is_default_status', 1);
                $consolidate_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

                $_consolidate_status = (isset($consolidate_status->id) ? $consolidate_status->id : $data['delivery_status']);

                foreach($rel_ids as $rel_id){
                    if($data['rel_type'] == 'locker_packages'){
                        $this->db->where('id', $rel_id);
                        $this->db->update(db_prefix().'lg_packages', ['delivery_status' => $_consolidate_status]);
                    }else if($data['rel_type'] == 'shipping'){
                        $this->db->where('id', $rel_id);
                        $this->db->update(db_prefix().'lg_shippings', ['delivery_status' => $_consolidate_status]);
                    }
                }

            }


            $action_data = [];
            $action_data['rel_id'] = $insert_id;
            $action_data['rel_type'] = 'consolidated';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = $data['created_by'];
            $action_data['action'] = _l('lg_consolidated_created');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = $data['created_by'];

            $this->db->insert(db_prefix().'lg_action_history', $action_data);


            $contact_id = get_primary_contact_user_id($data['customer_id']);
            $_consolidation = $this->get_consolidation($insert_id);
            if(is_numeric($contact_id) && $contact_id > 0){
                $contact = $this->clients_model->get_contact($contact_id);
                $template = mail_template('Logistic_consolidation_created_send_to_customer', 'logistic', $_consolidation, $contact);
                $template->send();
            }

            return $insert_id;
        }
        
        return false;

    }


    /**
     * [update_consolidated description]
     * @return [type] [description]
     */
    public function update_consolidation($data, $id){

        $update_rs = 0;

        $data['to_currency'] = $data['currency'];

        if(isset($data['package_information'])){
            $package_information = $data['package_information'];
            unset($data['package_information']);
        }

        if(isset($data['rel_id'])){
            $rel_ids = $data['rel_id'];
            $data['rel_id'] = implode(',', $data['rel_id']);
        }

        if(isset($data['country_code'])){
            unset($data['country_code']);
        }

        if(isset($data['prefix_by_country_code'])){
            unset($data['prefix_by_country_code']);
        }

        if(isset($data['package_information_update'])){
            $package_information_update = $data['package_information_update'];
            unset($data['package_information_update']);
        }

        if(isset($data['removed_package_detail_ids'])){
            $removed_package_detail_ids = $data['removed_package_detail_ids'];
            unset($data['removed_package_detail_ids']);
        }

        $this->db->where('consolidated_id', $id);
        $this->db->delete(db_prefix().'lg_consolidated_detail');
         if($this->db->affected_rows() > 0){
            $update_rs++;
        }

        if(isset($package_information)){
            foreach($package_information as $detail_data){

                $detail_data['consolidated_id'] = $id;
                $this->db->insert(db_prefix().'lg_consolidated_detail', $detail_data);
                $detail_inser_id = $this->db->insert_id();
                if($detail_inser_id){
                    $update_rs++; 
                }

            }
        }   


        if(isset($removed_package_detail_ids)){
            foreach($removed_package_detail_ids as $detail_id){
                $this->db->where('id', $detail_id);
                $this->db->delete(db_prefix().'lg_consolidated_detail');
                if($this->db->affected_rows() > 0){
                    $update_rs++;
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'lg_consolidated', $data);
        if($this->db->affected_rows() > 0){
            $update_rs++;
        }

        if($update_rs > 0){

            $action_data = [];
            $action_data['rel_id'] = $id;
            $action_data['rel_type'] = 'consolidated';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = _l('lg_consolidated_updated');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = (isset($data['created_from']) && $data['created_from'] == 'client') ? get_contact_user_id() : get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            return true;
        }
        return false;
    }

     /**
     * Gets the part attachments.
     *
     * @param      <type>  $surope  The surope
     * @param      string  $id      The identifier
     *
     * @return     <type>  The part attachments.
     */
    public function get_consolidated_dl_attachments($surope, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $assets);
        }
        $this->db->where('rel_type', 'lg_consolidated');
        $result = $this->db->get(db_prefix().'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }
       


    /**
     * { delete  hippingattachment }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean 
     */
    public function delete_consolidated_attachment($id)
    {
        $attachment = $this->get_consolidated_dl_attachments('', $id);
        $deleted    = false;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'files');        
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
            }

            if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * { consolidation pdf }
     *
     * @param      <type>  $consolidation  The pur consolidation
     *
     * @return     <type>  ( consolidation pdf )
     */
    public function consolidation_pdf($consolidation)
    {
        return app_pdf('lg_consolidation', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Consolidations_pdf'), $consolidation);
    }

    /**
     * [get_consolidation_pdf_html description]
     * @return [type] [description]
     */
    public function get_consolidation_pdf_html($consolidation_data){

        if(!isset($consolidation_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($consolidation_data->customer_id);

        $shipping_number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/consolidation_code/barcode/'.$consolidation_data->id.'/' . md5($shipping_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_consolidation_getBarcode($shipping_number, $consolidation_data->id);
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            <td width="40%" class="fs13 bold  text-center">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                            </td>
                            <td width="30%" class="text-right"><img class="images_w_table" src="' . $barcode_path . '" alt="' . $shipping_number . '" ><span class="fs11 print-item-code">#'.$shipping_number.format_lg_package_status($consolidation_data->delivery_status).'</span></td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';
        $rel_str = '';
        if($consolidation_data->rel_id != ''){
            $rel_arr = explode(',', $consolidation_data->rel_id);
            foreach($rel_arr as $key => $rel_id){
                if($key == 0){
                    $rel_str .= lg_get_tracking_number_by_type($consolidation_data->rel_type, $rel_id);
                }else{
                    $rel_str .= '<br>'.lg_get_tracking_number_by_type($consolidation_data->rel_type, $rel_id);
                }
            }
        }

        // Customer, company information
        $html .= '<table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="header-height">
                            <td width="33%">
                                <strong class="text-uppercase">'._l('lg_bill_to').'</strong><br>
                                '.get_company_name($consolidation_data->customer_id).'
                                <br><br>
                                '.$client->address.' '.$client->city.'<br>
                                '.get_country_name($client->country).'<br>
                                '.$client->phonenumber.'<br>
                                '.lg_get_contact_primary_email($consolidation_data->customer_id).'

                            </td>

                            <td width="33%" class="text-center">
                                 <strong class="text-uppercase fs21">'._l('lg_consolidated').'</strong><br>
                                 '._l('lg_package_type').': '._l('lg_'.$consolidation_data->rel_type).'<br>
                                '.$rel_str.'
                            </td>

                            <td width="33%" class="text-right">
                                <strong class="text-uppercase">'._l('lg_shipping_information').'</strong><br><br>
                                <strong>'._l('lg_service_mode').': </strong>'.lg_get_service_name_by_id($consolidation_data->service_mode).'<br>
                                <strong>'._l('lg_courrier_company').': </strong>'.lg_get_shipping_company_name($consolidation_data->courrier_company).'<br>
                                <strong>'._l('lg_logistic_service').': </strong>'.lg_get_logistic_service_name_by_id($consolidation_data->logistic_service_id).'<br>
                                <strong>'._l('lg_shipping_date').': </strong>'._dt($consolidation_data->created_at).'<br>
                               

                            </td>
                        </tr>
                    </tbody>
                </table><br><br>';

        $html .= '<table class="table border" border="0.1" data-type="estimate">
                                        <thead >
                                            <tr>
                                                <th class="thead-dark">'._l('lg_amount').'</th>
                                                <th class="thead-dark width20">'._l('lg_package_description').'</th>
                                                <th class="thead-dark">'._l('lg_weight').'</th>
                                                <th class="thead-dark">'._l('lg_length').'</th>
                                                <th class="thead-dark">'._l('lg_width').'</th>
                                                <th class="thead-dark">'._l('lg_height').'</th>
                                                <th class="thead-dark">'._l('lg_weight_vol').'</th>
                                                <th class="text-right thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="text-right thead-dark">'._l('lg_dec_value').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                                            $weight = 0;
                                            $volumetric_weight = 0;
                                            $total_dec = 0;
                                            foreach($consolidation_data->shipment_detail as $key => $detail){ 
                                                if($detail['weight_vol'] >= $detail['weight']){
                                                    $volumetric_weight += $detail['weight_vol'];
                                                }else{
                                                    $weight += $detail['weight'];
                                                }

                                                $total_dec += $detail['dec_value'];

                                            
                                             $html .= '<tr>

                                                    <td>'.e($detail['amount']).'</td>
                                                    <td>'.e($detail['package_description']).'</td>
                                                    <td>'.e($detail['weight']).'</td>
                                                    <td>'.e($detail['length']).'</td>
                                                    <td>'.e($detail['width']).'</td>
                                                    <td>'.e($detail['height']).'</td>
                                                    <td>'.e($detail['weight_vol']).'</td>
                                                    <td class="text-right">'.e($detail['fixed_charge']).'</td>
                                                    <td class="text-right">'.e($detail['dec_value']).'</td>
                                                </tr>';

                                            }

                                            $html .= '<tr>

                                                <td colspan="6"><span class="bold">'._l('lg_price').' '.$consolidation_data->weight_units_setting.': '.'</span>'.e($consolidation_data->price_kg).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_subtotal').'</td>
                                                <td class="text-right">'.app_format_money($consolidation_data->subtotal, $consolidation_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="6"><span class="bold">'._l('lg_weight').': '.'</span>'.e($weight).'</td>
                                                <td colspan="2" class="text-right">'._l('lg_discount'). ' '.$consolidation_data->discount_percent.'%'.'</td>
                                                <td class="text-right">'.app_format_money($consolidation_data->discount, $consolidation_data->currency).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_volumetric_weight').': '.'</span>'.e($volumetric_weight).'</td>
                                            </tr>

                                            <tr>
                                                <td colspan="9"><span class="bold">'._l('lg_total_weight_calculation').': '.'</span>'.e($volumetric_weight + $weight).'</td>
                                               
                                              
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<br><br><br><table class="table" border="0.1">
                                        <thead>
                                            <tr>
                                                <th class="thead-dark">'._l('lg_value_assured').'</th>
                                                <th class="thead-dark">'._l('lg_shipping_insurance').' '.app_format_number($consolidation_data->shipping_insurance_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_custom_duties').' '.app_format_number($consolidation_data->custom_duties_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_declared_total_value').'</th>
                                                <th class="thead-dark">'._l('lg_declared_value'). ' '.app_format_number($consolidation_data->declared_value_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_tax'). ' '.app_format_number($consolidation_data->tax_percent).'%'.'</th>
                                                <th class="thead-dark">'._l('lg_fixed_charge').'</th>
                                                <th class="thead-dark">'._l('lg_reissue').'</th>
                                                <th class="thead-dark">'._l('lg_total').'</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>'.app_format_number($consolidation_data->value_assured).'</td>
                                                <td>'.app_format_number($consolidation_data->shipping_insurance).'</td>
                                                <td>'.app_format_number($consolidation_data->custom_duties).'</td>
                                                <td>'.app_format_number($total_dec).'</td>
                                                <td>'.app_format_number($consolidation_data->declared_value).'</td>
                                                <td>'.app_format_number($consolidation_data->tax).'</td>
                                                <td>'.app_format_number($consolidation_data->fixed_charge).'</td>
                                                <td>'.app_format_number($consolidation_data->reissue).'</td>
                                                <td>'.app_format_money($consolidation_data->total, $consolidation_data->currency).'</td>
                                            </tr>

                                        </tbody>
                                    </table>';

        $html .= '<p class="fs21 text-uppercase text-center">'._l('lg_terms').'</p><hr>';
         $html .= '<table><thead><tr><td></td></tr></thead></table>';
        $html .= '<span>'.get_option('lg_default_invoice_terms').'</span><br><br><hr>';

        $html .= '<table>
                    <thead>
                        <tr>
                            <td class="text-center text-uppercase fs20"></td>
                             <td class="text-center text-uppercase fs20"></td>
                        </tr>
                        <tr>
                            <td class="text-center text-uppercase fs20">'.get_option('lg_invoice_company_signature').'</td>
                             <td class="text-center text-uppercase fs20">'.get_option('lg_customer_signature_billing').'</td>
                        </tr>
                    </thead>
                </table>';

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }


    /**
     * { shipping pdf }
     *
     * @param      <type>  $consolidation  The pur consolidation
     *
     * @return     <type>  ( consolidation pdf )
     */
    public function consolidation_label_pdf($consolidation)
    {
        return app_pdf('lg_consolidation', module_dir_path(LOGISTIC_MODULE_NAME, 'libraries/pdf/Consolidation_label_pdf'), $consolidation);
    }

    /**
     * [get_consolidation_label_pdf_html description]
     * @return [type] [description]
     */
    public function get_consolidation_label_pdf_html($consolidation_data){

        if(!isset($consolidation_data->id)){
            return '';
        }

        $this->load->model('clients_model');

        $client = $this->clients_model->get($consolidation_data->customer_id);

        $shipping_number = $consolidation_data->shipping_prefix.$consolidation_data->number_code;

        $barcode_path = FCPATH.'modules/logistic/uploads/consolidation_code/barcode/'.$consolidation_data->id.'/' . md5($shipping_number ?? '').'.svg';
        if(!file_exists($barcode_path)){
            lg_consolidation_getBarcode($shipping_number, $consolidation_data->id);
        }

        $rel_str = '';
        if($consolidation_data->rel_id != ''){
            $rel_arr = explode(',', $consolidation_data->rel_id);
            foreach($rel_arr as $key => $rel_id){
                if($key == 0){
                    $rel_str .= lg_get_tracking_number_by_type($consolidation_data->rel_type, $rel_id);
                }else{
                    $rel_str .= ', '.lg_get_tracking_number_by_type($consolidation_data->rel_type, $rel_id);
                }
            }
        }

        $html = '';

        // Header information
        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                            <td width="30%">'.pdf_logo_url().'</td>
                            
                            <td width="30%" class="text-right fs13">
                                <strong>'._l('tin').': </strong>'.get_option('company_vat').'<br>
                                <strong>'._l('lg_phone').': </strong>'.get_option('invoice_company_phonenumber').'<br>
                                <strong>'._l('lg_address').': </strong>'.get_option('invoice_company_address').'<br>
                                
                            </td>
                        </tr>
                    </tbody>
                </table><hr><br><br>';

        // Customer, company information
        $html .= '<table class="table">
        
                    <tbody>
                        <tr class="header-height text-center">
                         
                            <td width="33%" >
                               <img class="" src="' . $barcode_path . '" alt="' . $shipping_number . '" ><span class="fs11 print-item-code">#'.$shipping_number.'</span>
                            </td>

                        </tr>
                    </tbody>
                </table>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="header-height">
                            <td width="33%" class="text-center" >
                                <strong class="text-uppercase fs24">'.$consolidation_data->shipping_prefix.$consolidation_data->number_code.'</strong><br>
                            </td>
                        </tr>
                    </tbody>
                </table>';

        $weight = 0;
        $volumetric_weight = 0;
        $total_dec = 0;
        $amount = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        foreach($consolidation_data->shipment_detail as $key => $detail){ 
            $amount++;
            $length += $detail['length'];
            $width += $detail['width'];
            $height += $detail['height'];
            if($detail['weight_vol'] >= $detail['weight']){
                $volumetric_weight += $detail['weight_vol'];
            }else{
                $weight += $detail['weight'];
            }

            $total_dec += $detail['dec_value'];

        }

        $weight_units_setting = ($consolidation_data->weight_units_setting != '' ) ? $consolidation_data->weight_units_setting : get_option('lg_weight_units');
        $length_units_setting = ($consolidation_data->length_units_setting != '' ) ? $consolidation_data->length_units_setting : get_option('lg_length_units');



        $html .= '<table class="table">
                    <tbody>

                        <tr class="">
                            <td width="33%" colspan="2" >
                                <strong class="text-uppercase">'._l('lg_package_type').': </strong>'._l('lg_'.$consolidation_data->rel_type).'<br>
                                <strong class="text-uppercase">'._l('lg_package_details').': </strong>'.$rel_str.'<br>
                            </td>
                        </tr>

                        <tr class="">
                            <td width="33%" colspan="2" >
                                <strong class="text-uppercase">'._l('lg_package_reference').'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_time').': </strong>'._d($consolidation_data->created_at).'
                            </td>
                             <td>
                                <strong>'._l('lg_amount').': </strong>'.$amount.'
                            </td>
                        </tr>
                        <tr>
                           <td>
                                <strong>'._l('lg_weight').': </strong>'.$weight.' '.$weight_units_setting.'
                            </td>
                             <td>
                                <strong>'._l('lg_cost').': </strong>'.app_format_money($consolidation_data->total, $consolidation_data->currency).'
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>'._l('lg_length').': </strong>'.$length.' '.$length_units_setting.'
                            </td>
                            <td>
                                <strong>'._l('lg_width').': </strong>'.$width.' '.$length_units_setting.'
                            </td>
                        </tr>

                        <tr>
                            <td>
                            </td>
                            <td>
                                <strong>'._l('lg_height').': </strong>'.$height.' '.$length_units_setting.'
                            </td>
                        </tr>
                
                    </tbody>
                </table><br><br>';


        $html .= '<table class="table">
                    <tbody>
                        <tr class="">
                            <td width="33%" >
                                <strong class="text-uppercase">'._l('lg_service_reference').'</strong>
                            </td>
                        </tr>

                        <tr class="">
                            <td width="33%" >
                                '.lg_get_service_name_by_id($consolidation_data->service_mode).' | '.lg_get_shipping_company_name($consolidation_data->courrier_company).'
                            </td>
                        </tr>
                
                    </tbody>
                </table><br>';

     

        $html .= '<br><br><table class="table">
            <tbody>
                <tr class="">
                    <td width="33%" class="text-center">
                        <strong class="text-uppercase fs21">'.lg_get_recipient_address_str($consolidation_data->recipient_address_id).'</strong>
                    </td>
                </tr>
            </tbody>
        </table><br><br>';

        if($client->locker_code == '' ||  $client->locker_code == null){
            update_locker_code_for_client($client->userid);
        }

        $qrcode_path = FCPATH.'modules/logistic/uploads/consolidation_code/qr/'.$consolidation_data->id.'/' . md5($shipping_number ?? '').'.png';
        if(!file_exists($qrcode_path)){
            lg_consolidation_getQrcode($shipping_number, $consolidation_data->id);
        }

        $recipient = $this->get_recipient($consolidation_data->recipient_id);
        $recipient_address = $this->get_recipient_address($consolidation_data->recipient_address_id);

        $html .= '<table class="table">
                  
                    <tbody>
                        <tr class="header-height">
                           
                            
                            <td width="30%" class="text-left ">
                                '._l('lg_sender').'<br>
                                <strong class="fs15">'.get_company_name($consolidation_data->customer_id).'</strong><br>
                                '.lg_get_customer_address_str($consolidation_data->customer_address, 1).'<br>
                                '.$client->phonenumber.'<br>
                               
                            </td>
                             <td width="30%" class="text-right">
                             '._l('lg_recipient').'<br>
                             <strong class="fs15">'.$recipient->first_name.' '.$recipient->last_name.'</strong><br>
                             '.$recipient_address->address.'<br>
                             '.$recipient->phone.'
                             </td>

                        </tr>
                        <tr>
                        <td width="30%"><img class="" src="' . $qrcode_path . '" alt="' . $shipping_number . '" ></td>
                            
                            <td width="30%" class="text-right ">
                               
                            </td>

                        </tr>
                    </tbody>
                </table><br><br>';
        

        $html .= '<link href="' . FCPATH.'modules/logistic/assets/css/pdf_style.css' . '"  rel="stylesheet" type="text/css" />';

        return $html;
    }


    /**
     * Send consolidation to client
     * @param  mixed  $id        invoiceid
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach consolidation pdf or not
     * @return boolean
     */
    public function send_consolidation_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false, $attachStatement = [])
    {
      
        $consolidation = $this->get_consolidation($id);
        

        if ($template_name == '') {
            $template_name = 'logistic_consolidation_send_to_customer';
            
        }

        $emails_sent = [];
        $send_to     = [];

        
        $send_to = $this->input->post('sent_to');
        

        if (is_array($send_to) && count($send_to) > 0) {
     

            if ($attachpdf) {

                $consolidation_html = $this->get_consolidation_pdf_html($consolidation);

                set_mailing_constant();
                $pdf    = $this->consolidation_pdf($consolidation_html);
                $attach = $pdf->Output($consolidation->shipping_prefix.$consolidation->number_code . '.pdf', 'S');
            }

            $i = 0;
            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, 'logistic', $consolidation, $contact, $cc);

                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => str_replace('/', '-', $consolidation->shipping_prefix.$consolidation->number_code . '.pdf'),
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        $sent = true;
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } 

        if (count($emails_sent) > 0) {
        
            return true;
        }

        return false;
    }

    /**
     * [get_consolidation_delivery_shipments description]
     * @return [type] [description]
     */
    public function get_consolidation_delivery_shipments($consolidated_id){
        $this->db->where('consolidated_id', $consolidated_id);
        return $this->db->get(db_prefix().'lg_consolidation_delivery_shipment')->row();
    }

    /**
     * [add_consolidation_shipment_tracking description]
     */
    public function add_consolidation_shipment_tracking($data){
        $this->load->model('clients_model');

        if(isset($data['consolidated_id'])){
            unset($data['consolidated_id']);
        }
        $data['time_update'] = to_sql_date($data['time_update'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'lg_tracking_history', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            if($data['rel_type'] == 'consolidated'){
                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'lg_consolidated', ['delivery_status' => $data['delivery_status']]);
            }

            $action_data = [];
            $action_data['rel_id'] = $data['rel_id'];
            $action_data['rel_type'] = 'consolidated';
            $action_data['time_update'] = date('Y-m-d H:i:s');
            $action_data['user'] = get_staff_user_id();
            $action_data['action'] = _l('lg_new_shipment_tracking');
            $action_data['created_at'] = date('Y-m-d H:i:s');
            $action_data['created_by'] = get_staff_user_id();

            $this->db->insert(db_prefix().'lg_action_history', $action_data);

            $_consolidation = $this->get_consolidation( $data['rel_id']);
            $contact_id = get_primary_contact_user_id($_consolidation->customer_id);
            if(is_numeric($contact_id) && $contact_id > 0){
                $contact = $this->clients_model->get_contact($contact_id);
                $template = mail_template('Logistic_consolidation_shipment_tracking', 'logistic', $_consolidation, $contact);
                $template->send();
            }

            return $insert_id;
        }
        return false;
    }


    /**
     * [get_consolidation_attachments description]
     * @return [type] [description]
     */
    public function get_consolidation_shipment_attachments($consolidation_id){
        $this->db->where('rel_type', 'consolidated_attach');
        $this->db->where('rel_id', $consolidation_id);

        return $this->db->get(db_prefix().'files')->result_array();

    }

    /**
     * [get_tracking_histories_consolidation description]
     * @param  [type] $consolidation_id [description]
     * @return [type]             [description]
     */
    public function get_tracking_histories_consolidation($consolidation_id){
        $this->db->where('rel_id', $consolidation_id);
        $this->db->where('rel_type', 'consolidated');

        return $this->db->get(db_prefix().'lg_tracking_history')->result_array();


    }

    /**
     * [get_action_histories_consolidation description]
     * @param  [type] $consolidation_id [description]
     * @return [type]             [description]
     */
    public function get_action_histories_consolidation($consolidation_id){
        $this->db->where('rel_id', $consolidation_id);
        $this->db->where('rel_type', 'consolidated');

        return $this->db->get(db_prefix().'lg_action_history')->result_array();


    }

    /**
     * [get_delivery_shipment description]
     * @return [type] [description]
     */
    public function get_consolidated_delivery_shipment($package_id){
        $this->db->where('rel_id', $package_id);
        $this->db->where('rel_type', 'consolidated_sm_sign');

        return $this->db->get(db_prefix().'files')->row();
    }

    /**
     * [remove_consolidation_shipment_sign description]
     * @return [type] [description]
     */
    public function remove_consolidation_shipment_sign($consolidation_id){

        $rs = 0;
        $this->db->where('rel_id', $consolidation_id);
        $this->db->where('rel_type', 'consolidated_sm_sign');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }


        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/sign/'. $consolidation_id)) {
           
            if(delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/sign/'. $consolidation_id)){
                $rs++;
            }
            
        }

        if($rs > 1){
            return true;
        }
        return false;

    }

    /**
     * [delivery_shipment description]
     * @return [type] [description]
     */
    public function consolidation_delivery_shipment($data){

        $data['delivery_date'] = to_sql_date($data['delivery_date'], true);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['consolidated_id'] = $data['consolidation_id'];
        unset($data['consolidation_id']);

        $this->db->insert(db_prefix().'lg_consolidation_delivery_shipment', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){

            $this->db->where('style_name', 'delivered');
            $this->db->where('is_default_status', 1);
            $delivered_status = $this->db->get(db_prefix().'lg_style_and_states')->row();

            $status = [];
            $status['delivery_status'] = (isset($delivered_status->id) ? $delivered_status->id : null);

            $this->db->where('id', $data['consolidated_id']);
            $this->db->update(db_prefix().'lg_consolidated', $status);


            $action_data = [];
            $action_data['rel_id'] = $data['consolidated_id'];
            $action_data['rel_type'] = 'consolidated';
            $action_data['time_update'] =  $data['delivery_date'];
            $action_data['user'] = $data['created_by'];
            $action_data['action'] = _l('lg_delivery_shipment');
            $action_data['created_at'] = $data['created_at'];
            $action_data['created_by'] = $data['created_by'];

            $this->db->insert(db_prefix().'lg_action_history', $action_data);


            $tracking_history = [];
            $tracking_history['rel_id'] = $data['consolidated_id'];
            $tracking_history['rel_type'] = 'consolidated';
            $tracking_history['new_location'] = null;
            $tracking_history['delivery_status'] = $status['delivery_status'];
            $tracking_history['time_update'] =  $data['delivery_date'];
            $tracking_history['remark'] = ($data['note'] != '') ? $data['note'] : _l('lg_the_shipment_has_been_delivered');
            $tracking_history['created_at'] = $data['created_at'];
            $tracking_history['created_by'] = $data['created_by'];
            $this->db->insert(db_prefix().'lg_tracking_history', $tracking_history);


            $consolidation = $this->get_consolidation($data['consolidated_id']);
            if($consolidation){
                $contact_id = get_primary_contact_user_id($consolidation->customer_id);
                if(is_numeric($contact_id) && $contact_id > 0){
                    $contact = $this->clients_model->get_contact($contact_id);
                    $template = mail_template('logistic_consolidation_delivered', 'logistic', $consolidation, $contact);
                    $template->send();
                }


                if(is_numeric($consolidation->created_by) && $consolidation->created_by > 0){
                    $notified = add_notification([
                    'description'     => _l('lg_the_shipment_has_been_delivered'),
                    'link'            => 'logistic/consolidated_detail/'.$data['consolidated_id'],
                    'touserid'  => $consolidation->created_by,
                    'fromcompany' => '',
                    'additional_data' => serialize([
                        $consolidation->shipping_prefix.$consolidation->number_code,
                    ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$consolidation->created_by]);
                    }
                }
            }


            return $insert_id;
        }
        return false;
    }


    /**
     * [delete_consolidated description]
     * @return [type] [description]
     */
    public function delete_consolidated($consolidated_id){
        $rs = 0;

        $this->db->where('id', $consolidated_id);
        $this->db->delete(db_prefix().'lg_consolidated');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $consolidated_id);
        $this->db->where('rel_type', 'lg_consolidated');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $consolidated_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidated/'. $consolidated_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_code/barcode/'. $consolidated_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_code/barcode/'. $consolidated_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_code/qr/'. $consolidated_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_code/qr/'. $consolidated_id);
        }

        $this->db->where('consolidated_id', $consolidated_id);
        $this->db->delete(db_prefix().'lg_consolidated_detail');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $consolidated_id);
        $this->db->where('rel_type', 'consolidated');
        $this->db->delete(db_prefix().'lg_action_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $consolidated_id);
        $this->db->where('rel_type', 'consolidated');
        $this->db->delete(db_prefix().'lg_tracking_history');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('consolidated_id', $consolidated_id);
        $this->db->delete(db_prefix().'lg_consolidation_delivery_shipment');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $consolidated_id);
        $this->db->where('rel_type', 'consolidated_sm_sign');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        $this->db->where('rel_id', $consolidated_id);
        $this->db->where('rel_type', 'consolidated_attach');
        $this->db->delete(db_prefix().'files');
        if($this->db->affected_rows() > 0){
            $rs++;
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/attachments/'. $consolidated_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/attachments/'. $consolidated_id);
        }

        if (is_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/sign/'. $consolidated_id)) {
            delete_dir(LOGISTIC_MODULE_UPLOAD_FOLDER .'/consolidation_delivery_shipment/sign/'. $consolidated_id);
        }


        if($rs > 0){
            return true;
        }

        return false;
        

    }

    /**
     * [get_client_consolidated description]
     * @return [type] [description]
     */
    public function get_client_consolidated($client_id){
        $this->db->where('customer_id', $client_id);

        return $this->db->get(db_prefix().'lg_consolidated')->result_array();
    }

    /**
     * [count_package_by_status description]
     * @return [type] [description]
     */
    public function count_package_by_status(){
        $styles = $this->get_style_and_states('is_default_status = 1');

        $chart = [];

        foreach ($styles as $status) {
            $total = total_rows(db_prefix().'lg_packages',['delivery_status' => $status['id']]);
           

            $chart[] = ['name' => _l('lg_'.$status['style_name']), 'y' => $total, 'z'=>100]; 
        }

    
        return $chart;
    }

     /**
     * get data Purchase statistics by cost
     *
     * @param      string  $year   The year
     *
     * @return     array
     */
    public function package_sales_graph_data($year = '', $currency = ''){
        $chart = [];

        if($year == ''){
            $year = date('Y');
        }

        $base_currency = get_base_currency();

        $currency = $base_currency->id;
        
        $where = 'AND '.db_prefix().'lg_packages.currency IN (0, '.$currency.')';
        


        $query = $this->db->query('SELECT DATE_FORMAT(created_at, "%m") AS month, Sum(total) as total 
            FROM '.db_prefix().'lg_packages where DATE_FORMAT(created_at, "%Y") = '.$year.' '. $where.'
            group by month')->result_array();
        $result = [];
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $cost = [];
        $rs = 0;
        foreach ($query as $value) {
            if($value['total'] > 0){
                $result[$value['month'] - 1] =  (double)$value['total'];
            }
        }

        $chart['data'] =  $result;
        $chart['unit'] = $base_currency->symbol;
        $chart['name'] = $base_currency->name;

        return $chart;
    }

    /**
     * get data Purchase statistics by cost
     *
     * @param      string  $year   The year
     *
     * @return     array
     */
    public function shipping_sales_graph_data($year = '', $currency = ''){
        $chart = [];

        if($year == ''){
            $year = date('Y');
        }

        $base_currency = get_base_currency();

        $currency = $base_currency->id;
        
        $where = 'AND '.db_prefix().'lg_shippings.currency IN (0, '.$currency.')';
        


        $query = $this->db->query('SELECT DATE_FORMAT(created_at, "%m") AS month, Sum(total) as total 
            FROM '.db_prefix().'lg_shippings where DATE_FORMAT(created_at, "%Y") = '.$year.' '. $where.'
            group by month')->result_array();
        $result = [];
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $cost = [];
        $rs = 0;
        foreach ($query as $value) {
            if($value['total'] > 0){
                $result[$value['month'] - 1] =  (double)$value['total'];
            }
        }

        $chart['data'] =  $result;
        $chart['unit'] = $base_currency->symbol;
        $chart['name'] = $base_currency->name;

        return $chart;
    }

    /**
     * [count_shipping_by_status description]
     * @return [type] [description]
     */
    public function count_shipping_by_status(){
        $styles = $this->get_style_and_states('is_default_status = 1');

        $chart = [];

        foreach ($styles as $status) {
            $total = total_rows(db_prefix().'lg_shippings',['delivery_status' => $status['id']]);
           

            $chart[] = ['name' => _l('lg_'.$status['style_name']), 'y' => $total, 'z'=>100]; 
        }

    
        return $chart;
    }

    /**
     * get data Purchase statistics by cost
     *
     * @param      string  $year   The year
     *
     * @return     array
     */
    public function consolidated_sales_graph_data($year = '', $currency = ''){
        $chart = [];

        if($year == ''){
            $year = date('Y');
        }

        $base_currency = get_base_currency();

        $currency = $base_currency->id;
        
        $where = 'AND '.db_prefix().'lg_consolidated.currency IN (0, '.$currency.')';
        


        $query = $this->db->query('SELECT DATE_FORMAT(created_at, "%m") AS month, Sum(total) as total 
            FROM '.db_prefix().'lg_consolidated where DATE_FORMAT(created_at, "%Y") = '.$year.' '. $where.'
            group by month')->result_array();
        $result = [];
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $result[] = 0;
        $cost = [];
        $rs = 0;
        foreach ($query as $value) {
            if($value['total'] > 0){
                $result[$value['month'] - 1] =  (double)$value['total'];
            }
        }

        $chart['data'] =  $result;
        $chart['unit'] = $base_currency->symbol;
        $chart['name'] = $base_currency->name;

        return $chart;
    }

    /**
     * [count_shipping_by_status count_consolidated_by_status]
     * @return [type] [description]
     */
    public function count_consolidated_by_status(){
        $styles = $this->get_style_and_states('is_default_status = 1');

        $chart = [];

        foreach ($styles as $status) {
            $total = total_rows(db_prefix().'lg_consolidated',['delivery_status' => $status['id']]);
           

            $chart[] = ['name' => _l('lg_'.$status['style_name']), 'y' => $total, 'z'=>100]; 
        }

    
        return $chart;
    }

}
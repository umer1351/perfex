<?php

    defined('BASEPATH') || exit('No direct script access allowed');

    function get_all_staff_members()
    {
        $CI = &get_instance();
        $CI->db->where('admin', 0);

        return $CI->db->get(db_prefix().'staff')->result_array();
    }

    function is_staff_active()
    {
        $CI = &get_instance();
        $CI->load->model('staff_model');
        $result = $CI->staff_model->get(get_staff_user_id());
        if ($result->active==1) {
            return true;
        }
        return false;
    }

    /* End of file "extended_email_helper.".php */
hooks()->add_filter('email_template_from_headers', 'change_email_template_from_headers', 1, 2);
function change_email_template_from_headers($data, $template){
    $ci = & get_instance();
    $email_config = $ci->load->config('email');
    $from = $email_config['smtp_user'];
    $data['fromemail'] = $from;
    return $data;
}
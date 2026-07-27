<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_236 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        
        $exists = $CI->db->where('name', 'sms_whatsapp_delivery_badges')
                          ->get(db_prefix().'options')
                          ->num_rows();
        
        if ($exists == 0) {
            add_option('sms_whatsapp_delivery_badges', 0);
        }
    }
}

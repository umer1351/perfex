<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_250 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        // aiwriter_openai_model
        $aiwriter_demo_mode = $CI->db->query('SELECT * FROM '.db_prefix() . 'options where name = "aiwriter_openai_model";')->row();
        if(!$aiwriter_demo_mode){
            add_option('aiwriter_openai_model', 'text-davinci-003');
        }
    }
}
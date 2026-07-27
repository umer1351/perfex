<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_002 extends App_module_migration
{
    function __construct()
    {
        parent::__construct();
        $CI = &get_instance();
        $this->db = $CI->db;
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `" . perfex_saas_table('companies') . "` ADD `custom_domain` VARCHAR(255) NULL AFTER `dsn`;");
    }
}

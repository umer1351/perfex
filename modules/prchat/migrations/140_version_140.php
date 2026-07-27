<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_140 extends App_module_migration
{
    public function up()
    {
        add_option('chat_allow_staff_to_create_tickets', 1);
    }
}

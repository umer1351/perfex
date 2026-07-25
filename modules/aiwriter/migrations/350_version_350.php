<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_350 extends App_module_migration
{
    public function up()
    {
        update_option('aiwriter_openai_model','gpt-3.5-turbo-instruct');
    }
}
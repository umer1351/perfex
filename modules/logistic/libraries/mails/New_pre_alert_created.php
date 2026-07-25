<?php

defined('BASEPATH') or exit('No direct script access allowed');

class New_pre_alert_created extends App_mail_template
{
    protected $for = 'staff';

    protected $data;

    protected $contact;

    public $slug = 'new-pre-alert-created';

    public $rel_type = 'pre_alert';

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;

    }

    public function build()
    {
        
        $this->to($this->data->email)
        ->set_rel_id($this->data->pre_alert_id)
        ->set_merge_fields('pre_alert_merge_fields', $this->data->pre_alert_id);

    }
}

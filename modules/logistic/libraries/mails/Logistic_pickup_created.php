<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_pickup_created extends App_mail_template
{
    protected $for = 'staff';

    protected $shipping;

    protected $staff;

    public $slug = 'logistic-pickup-created-to-staff';

    public $rel_type = 'logistic_shipping';

    public function __construct($shipping, $staff, $cc = '')
    {
        parent::__construct();

        $this->shipping = $shipping;
        $this->staff = $staff;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->staff->email)
        ->set_rel_id($this->shipping->id)
        ->set_merge_fields('shipping_merge_fields', $this->shipping->id);
    }
}

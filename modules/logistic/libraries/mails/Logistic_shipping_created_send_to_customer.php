<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_shipping_created_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $shipping;

    protected $contact;

    public $slug = 'logistic-shipping-created-to-contact';

    public $rel_type = 'logistic_shipping';

    public function __construct($shipping, $contact, $cc = '')
    {
        parent::__construct();

        $this->shipping = $shipping;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->contact->email)
        ->set_rel_id($this->shipping->id)
        ->set_merge_fields('logistic_client_merge_fields', $this->shipping->customer_id, $this->contact->id)
        ->set_merge_fields('shipping_merge_fields', $this->shipping->id);
    }
}

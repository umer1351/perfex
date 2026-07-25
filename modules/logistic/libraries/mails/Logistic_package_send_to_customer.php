<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_package_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $package;

    protected $contact;

    public $slug = 'logistic-package-to-contact';

    public $rel_type = 'logistic_package';

    public function __construct($package, $contact, $cc = '')
    {
        parent::__construct();

        $this->package = $package;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->contact->email)
        ->set_rel_id($this->package->id)
        ->set_merge_fields('logistic_client_merge_fields', $this->package->customer_id, $this->contact->id)
        ->set_merge_fields('package_merge_fields', $this->package->id);
    }
}

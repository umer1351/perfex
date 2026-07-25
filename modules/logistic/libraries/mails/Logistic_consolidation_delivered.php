<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_consolidation_delivered extends App_mail_template
{
    protected $for = 'customer';

    protected $consolidation;

    protected $contact;

    public $slug = 'consolidation-delivered-to-contact';

    public $rel_type = 'logistic_consolidation';

    public function __construct($consolidation, $contact, $cc = '')
    {
        parent::__construct();

        $this->consolidation = $consolidation;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->contact->email)
        ->set_rel_id($this->consolidation->id)
        ->set_merge_fields('logistic_client_merge_fields', $this->consolidation->customer_id, $this->contact->id)
        ->set_merge_fields('consolidation_merge_fields', $this->consolidation->id);
    }
}

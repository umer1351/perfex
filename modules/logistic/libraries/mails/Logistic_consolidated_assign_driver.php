<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_consolidated_assign_driver extends App_mail_template
{
    protected $for = 'staff';

    protected $consolidated;

    protected $staff;

    public $slug = 'logistic-consolidated-assign-driver';

    public $rel_type = 'logistic_consolidated';

    public function __construct($consolidated, $staff, $cc = '')
    {
        parent::__construct();

        $this->consolidated = $consolidated;
        $this->staff = $staff;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->staff->email)
        ->set_rel_id($this->consolidated->id)
        ->set_merge_fields('consolidation_merge_fields', $this->consolidated->id);
    }
}

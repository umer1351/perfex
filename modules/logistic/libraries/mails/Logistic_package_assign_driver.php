<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_package_assign_driver extends App_mail_template
{
    protected $for = 'staff';

    protected $package;

    protected $staff;

    public $slug = 'logistic-package-assign-driver';

    public $rel_type = 'logistic_package';

    public function __construct($package, $staff, $cc = '')
    {
        parent::__construct();

        $this->package = $package;
        $this->staff = $staff;
        $this->cc      = $cc;
    }

    public function build()
    {
        
        $this->to($this->staff->email)
        ->set_rel_id($this->package->id)
        ->set_merge_fields('package_merge_fields', $this->package->id);
    }
}

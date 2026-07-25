<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">

                        <h4 class="no-margin font-bold"><i class="fa fa-users" aria-hidden="true"></i> <?php echo _l('lg_users'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="horizontal-scrollable-tabs">
                            <nav>
                                <ul class="nav nav-tabs mbot15" id="myTab" role="tablist">
                                  <li class="active">
                                  <a href="<?php echo admin_url('logistic/users?group=employee'); ?>" data-group="employee"><?php echo _l('lg_staff'); ?></a>
                                  </li>

                                  <li class="">
                                  <a href="<?php echo admin_url('logistic/users?group=customers'); ?>" data-group="customers"><?php echo _l('lg_customers'); ?></a>
                                  </li>

                                  <li class="">
                                  <a href="<?php echo admin_url('logistic/users?group=drivers'); ?>" data-group="drivers"><?php echo _l('lg_drivers'); ?></a>
                                  </li>
                                </ul>
                            </nav>
                        </div>


                        <?php $this->load->view('users/'.$group); ?>
    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<?php if($group == 'employee'){ ?>

<?php require 'modules/logistic/assets/js/users/employee/employee_js.php';?>


<?php }else if($group == 'customers'){ ?>

<?php $this->load->view('admin/clients/client_js'); ?>
<div id="contact_data"></div>
<div id="consent_data"></div>

<?php require 'modules/logistic/assets/js/users/customers/customers_js.php';?>
<?php }else if($group == 'drivers'){ ?>

<?php require 'modules/logistic/assets/js/users/drivers/drivers_js.php';?>
<?php } ?>
</body>

</html>
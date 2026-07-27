<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs" role="tablist">
                    <?php
                    foreach ($tab as $key => $gr) {
                        ?>
                        <li class="<?php if ($key == 0) {
                            echo 'active ';
                        } ?>setting_tab_<?php echo html_entity_decode($key); ?>">
                            <a data-group="<?php echo html_entity_decode($gr); ?>"
                               href="<?php echo admin_url('assetcentral/settings?group=' . $gr); ?>">
                                <?php if ($gr == 'asset_categories') {
                                    echo '<i class="fa fa-th" aria-hidden="true"></i>';
                                } elseif ($gr == 'asset_locations') {
                                    echo '<i class="fa fa-map-marker-alt" aria-hidden="true"></i>';
                                } elseif ($gr == 'asset_request_types') {
                                    echo '<i class="fa fa-clipboard-list" aria-hidden="true"></i>';
                                }  elseif ($gr == 'asset_general') {
                                    echo '<i class="fa fa-cog" aria-hidden="true"></i>';
                                } ?>
                                <?php echo _l('assetcentral_' . $gr); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <div>
                            <div class="tab-content">
                                <?php $this->load->view($tabs['view']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

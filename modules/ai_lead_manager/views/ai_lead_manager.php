<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#general_settings_tab" aria-controls="general_settings_tab" role="tab" data-toggle="tab">General Settings</a>
            </li>
            <li role="presentation">
                <a href="#bland_ai_settings_tab" aria-controls="bland_ai_settings_tab" role="tab" data-toggle="tab">Bland.AI Settings</a>
            </li>
            <li role="presentation">
                <a href="#vapi_ai_settings_tab" aria-controls="vapi_ai_settings_tab" role="tab" data-toggle="tab">Vapi.AI Settings</a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="general_settings_tab">
        <?php echo $this->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/tabs/general_settings'); ?>
    </div>
    <div role="tabpanel" class="tab-pane" id="bland_ai_settings_tab">
        <?php echo $this->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/tabs/bland_ai'); ?>
    </div>

    <div role="tabpanel" class="tab-pane" id="vapi_ai_settings_tab">
        <?php echo $this->load->view(AI_LEAD_MANAGER_MODULE_NAME . '/tabs/vapi_ai'); ?>
    </div>
</div>
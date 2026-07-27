<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?php
        $options = [
            [
                'id' => 'bland_ai',
                'name' => 'Bland AI'
            ],
            [
                'id' => 'vapi_ai',
                'name' => 'Vapi AI'
            ]
        ];
        echo render_select('settings[alm_voice_assistant]', $options, ['id', 'name'], 'AI Voice Assistant', get_option('alm_voice_assistant'), [], [], '', '', false);

        echo '<hr>';
        echo '<h4 class="tw-font-semibold">Inbound Call Settings</h4>';
        echo render_input('settings[alm_first_sentence]', 'First Sentence <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The first sentence that the assistant will say." data-original-title="" title=""></i>', get_option('alm_first_sentence'), 'text', ['placeholder' => 'First geeting sentence the agent will speak to the lead']);
        echo render_textarea('settings[alm_system_prompt]', 'System Prompt<i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The system prompt can be used to configure the context, role, personality, instructions and so on for the assistant." data-original-title="" title=""></i>', get_option('alm_system_prompt'), ['placeholder' => 'The system prompt can be used to configure the context, role, personality, instructions and so on for the assistant.']);

        ?>
        <!-- <p>
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{company_name}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{address}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{city}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{state}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{zip_code}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{country_code}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{phone}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{vat_number}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt">{vat_number_with_label}</a>
        </p> -->
        <hr>
        <h4 class="tw-font-semibold">Outbound Call Settings</h4>
        <?php
        echo render_input('settings[alm_first_sentence_outbound]', 'First Sentence <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The first sentence that the assistant will say." data-original-title="" title=""></i>', get_option('alm_first_sentence_outbound'), 'text', ['placeholder' => 'First geeting sentence the agent will speak to the lead']);
        echo render_textarea('settings[alm_system_prompt_outbound]', 'System Prompt<i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The system prompt can be used to configure the context, role, personality, instructions and so on for the assistant." data-original-title="" title=""></i>', get_option('alm_system_prompt_outbound'), ['placeholder' => 'The system prompt can be used to configure the context, role, personality, instructions and so on for the assistant.']);
        ?>
        <!-- <p>
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{company_name}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{address}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{city}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{state}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{zip_code}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{country_code}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{phone}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{vat_number}</a>,
            <a href="#" class="settings-textarea-merge-field" data-to="alm_system_prompt_outbound">{vat_number_with_label}</a>
        </p> -->
    </div>

    <div class="col-md-12">
        <hr>
        <h4 class="tw-font-semibold">Twilio Account Settings</h4>
        <p>Enter your Twilio Account SID, Auth Token, and Account Phone Number, which you can find in your <a href="https://console.twilio.com/">Twilio Console</a> under the Account Info section.</p>
    </div>
    <div class="col-md-4">
        <?php echo render_input('settings[twilio_account_sid]', 'Twilio Account SID <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="This is your Twilio Account SID that will be used to handle this phone number."></i>', get_option('twilio_account_sid'), 'text', ['placeholder' => 'Enter your Twilio Account SID']); ?>
    </div>
    <div class="col-md-4">
        <?php echo render_input('settings[twilio_auth_token]', 'Twilio Auth Token <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="This is the Twilio Auth Token that will be used to handle this phone number."></i>', get_option('twilio_auth_token'), 'text', ['placeholder' => 'Enter your Twilio Auth Token']); ?>
    </div>
    <div class="col-md-4">
        <?php echo render_input('settings[twilio_account_number]', 'Twilio Account Phone Number <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="These are the digits of the phone number you own on your Twilio."></i>', get_option('twilio_account_number'), 'text', ['placeholder' => 'Enter your Twilio Account Phone Number']); ?>
    </div>

    <div class="col-md-12">
        <hr>
        <h4 class="tw-font-semibold">Lead Settings</h4>
    </div>
    <div class="col-md-4">
        <?php echo render_select(
            'settings[alm_lead_status]',
            $leads_statuses,
            ['id', 'name'],
            'Follow-Up Lead Status <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Select a lead status that will trigger automated follow-up calls. For example, if you create a lead or update an existing lead to the selected status, the system will automatically initiate a call to schedule a meeting or perform the assigned follow-up task."></i>',
            get_option('alm_lead_status'),
            []
        ); ?>
    </div>
    <div class="col-md-4">
        <?php echo render_select(
            'settings[alm_lead_status_after_follow_up]',
            $leads_statuses,
            ['id', 'name'],
            'Post-Follow-Up Lead Status <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Choose a lead status that will be automatically assigned after a follow-up call. This status indicates that the lead has been followed up. For inbound calls, the AI assistant will collect necessary details to create a lead and assign this status to signify it was managed by AI or the lead is created by AI."></i>',
            get_option('alm_lead_status_after_follow_up'),
            []
        ); ?>
    </div>
    <div class="col-md-4">
        <?php echo render_select('settings[alm_lead_source]', $leads_sources, ['id', 'name'], 'Lead Source <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="Select the lead source that will be automatically assigned when a lead is created by AI through inbound calls."></i>', get_option('alm_lead_source'), []); ?>
    </div>
</div>
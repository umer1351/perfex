<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <input type="hidden" id="user_id" value="<?php echo get_staff_user_id() ?>">
    <div class="col-md-12">
        <p>Enter your API Key from <a href="https://dashboard.vapi.ai/org/api-keys">Vapi.ai Dashboard Settings</a> to enable seamless AI-powered features.</p>
        <?php echo render_input('settings[vapi_ai_api_key]', 'API Key', '1234567890-1234567890-1234567890', 'text', ['placeholder' => 'Enter your Vapi AI API Key']); ?>
        <?= (!empty(get_option('vapi_ai_assistant_id')) ? render_input('settings[vapi_ai_assistant_id]', 'Assistant ID', get_option('vapi_ai_assistant_id'), 'text', ['disabled' => true, 'placeholder' => 'Enter your Vapi AI Assistant ID']) : '') ?>
    </div>


    <?php
    if (get_option('vapi_ai_api_key') != '') {
    ?>
        <div class="col-md-12">
            <hr>
        </div>

        <div class="col-md-4">
            <?php echo render_input('settings[vapi_ai_max_duration]', 'Max Call Duration (secs) <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="This is the maximum number of seconds that the call will last. When the call reaches this duration, it will be ended."></i>', get_option('vapi_ai_max_duration'), "number", ['min' => 10, 'max' => 43200]); ?>
        </div>

        <div class="col-md-4">
            <?php echo render_select('settings[vapi_ai_voice_provider]', alm_vapi_ai_get_voice_providers(), ['id', 'name'], 'Select Voice Provider', get_option('vapi_ai_voice_provider')); ?>
        </div>

        <div class="col-md-4" id="vapi_agent_voice_select" <?= (get_option('vapi_ai_is_custom_voice_id') != 0 ? 'style="display: none;"' : '') ?>>
            <?php
            echo render_select_with_input_group('settings[vapi_ai_agent_voice]', alm_vapi_ai_get_voices(get_option('vapi_ai_voice_provider')), ['providerId', 'name', 'provider'], 'Select Agent Voice', get_option('vapi_ai_agent_voice'), '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="play_selected_voice_vapi();return false;" class="inline-field-new"><i class="fa fa-volume-high"></i></a></div>');
            ?>
        </div>

        <div class="col-md-4" id="vapi_custom_id_input" <?= (get_option('vapi_ai_is_custom_voice_id') == 0 ? 'style="display: none;"' : '') ?>>
            <?= render_input('settings[vapi_ai_agent_voice_id]', 'Custom Voice ID', get_option('vapi_ai_agent_voice_id')) ?>
        </div>

        <div class="col-md-4">
            <?= form_hidden('settings[vapi_ai_is_custom_voice_id]', 0) ?>
            <div class="checkbox checkbox-primary tw-pt-2">
                <input type="checkbox" id="vapi_ai_is_custom_voice_id" name="settings[vapi_ai_is_custom_voice_id]" value="1" <?= (get_option('vapi_ai_is_custom_voice_id') == 1 ? 'checked' : '') ?>>
                <label for="vapi_ai_custom_voice_id">Add Voice ID Manually</label>
            </div>
        </div>

        <div class="col-md-4">
            <?php echo render_yes_no_option(
                'filler_injection_enabled',
                'filler_injection_enabled',
                'This determines whether fillers are injected into the Model output before inputting it into the Voice provider.'
            ); ?>
        </div>

        <div class="col-md-4">
            <?php echo render_yes_no_option(
                'back_channeling_enabled',
                'back_channeling_enabled',
                'Make the bot say words like \'mhmm\', \'ya\' etc. while listening to make the conversation sounds natural. Default disabled'
            ); ?>
        </div>
        <div class="col-md-12">
            <hr>
        </div>
        <div class="col-md-4">
            <?php echo render_yes_no_option(
                'end_call_function_enabled',
                'end_call_function_enabled',
                'This will allow the assistant to end the call from its side. (Best for gpt-4 and bigger models.)'
            ); ?>
        </div>

        <div class="col-md-4">
            <?php echo render_yes_no_option(
                'dial_keypad_function_enabled',
                'dial_keypad_function_enabled',
                'This sets whether the assistant can dial digits on the keypad.'
            ); ?>
        </div>


        <div class="col-md-12">
            <hr>
        </div>

        <div class="col-md-4">
            <?php
            echo render_input('settings[vapi_ai_temperature]', 'Temperature <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The temperature is used to control the randomness of the output. When you set it higher, you\'ll get more random outputs. When you set it lower, towards 0, the values are more deterministic."></i>', get_option('vapi_ai_temperature'), "number", ['min' => 0.0, 'max' => 2.0, 'step' => 0.1]);
            ?>
            <input type="range" id="vapi_ai_temperature" min="0.0" max="2.0" step="0.1" value="<?= get_option('vapi_ai_temperature'); ?>" style="width: 100%; margin-top: 10px;" />
        </div>

        <div class="col-md-4">
            <?php echo render_input('settings[vapi_ai_max_tokens]', 'Max Tokens <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="This is the max number of tokens that the assistant will be allowed to generate in each turn of the conversation."></i>', get_option('vapi_ai_max_tokens'), "number", ['min' => 10, 'max' => 43200]); ?>
        </div>

        <div class="col-md-4">
            <?php echo render_yes_no_option(
                'vapi_ai_detect_emotions',
                'vapi_ai_detect_emotions',
                'Enable this property to detect user\'s emotion such as anger, joy etc. and feed it as an additional context to the model'
            ); ?>
        </div>
</div>
<div class="row">
    <div class="col-md-12">
        <hr>
    </div>

    <div class="col-md-3">
        <?php echo render_select('settings[vapi_ai_tools_inbound][]', alm_vapi_ai_get_tools(), ['id', 'name', 'id'], 'Inbound Tools', json_decode(get_option('vapi_ai_tools_inbound')), ['multiple' => true]); ?>
    </div>
    <div class="col-md-3">
        <?php echo render_select('settings[vapi_ai_tools_outbound][]', alm_vapi_ai_get_tools(), ['id', 'name','id'], 'Outbound Tools', json_decode(get_option('vapi_ai_tools_outbound')), ['multiple' => true]); ?>
    </div>

    <div class="col-md-3">
        <?php echo render_select('settings[vapi_ai_knowledgebase_inbound][]', alm_vapi_ai_get_files(), ['id', 'name'], 'Inbound Knowledge Base', json_decode(get_option('vapi_ai_knowledgebase_inbound')), ['multiple' => true]); ?>
    </div>
    <div class="col-md-3">
        <?php echo render_select('settings[vapi_ai_knowledgebase_outbound][]', alm_vapi_ai_get_files(), ['id', 'name'], 'Outbound Knowledge Base', json_decode(get_option('vapi_ai_knowledgebase_outbound')), ['multiple' => true]); ?>
    </div>


    <div class="col-md-12">
        <hr>
        <div class="row">
            <div class="col-md-10">
                <h4 class="tw-font-semibold">Knowledge Base</h4>
                <p class="tw-mb-3">Add your knowledge base here. You can add as many as you want.</p>
            </div>
            <div class="col-md-2">
                <div class="btn btn-default" data-toggle="modal" data-target="#vapi_ai_kwnowledgebase_modal"><i class="fa fa-regular fa-plus"></i> Add Knowledge Base</div>
            </div>
        </div>
        <table class="table">

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Size</th>
                <th>File Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach (alm_vapi_ai_get_files() as $knowledgebase): ?>
                <tr>
                    <td><?= $knowledgebase['id']; ?></td>
                    <td><?= $knowledgebase['name']; ?></td>
                    <td><?= alm_format_bytes($knowledgebase['bytes']); ?></td>
                    <td><?= $knowledgebase['mimetype'] ?? ''; ?></td>
                    <td><?= _dt($knowledgebase['createdAt']); ?></td>
                    <td>
                        <a href="<?= admin_url('ai_lead_manager/delete_knowledgebase/vapi_ai/' . $knowledgebase['id']); ?>" class="btn btn-danger">Delete</a>
                        <a href="<?= $knowledgebase['url'] ?>" class="btn btn-default" target="_blank" download>Download</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php } ?>
</div>
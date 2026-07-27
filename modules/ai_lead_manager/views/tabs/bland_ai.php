<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <p>Enter your API Key from <a href="https://app.bland.ai/dashboard/settings">Bland.ai Dashboard Settings</a> to enable seamless AI-powered features.</p>
        <?php echo render_input('settings[bland_ai_api_key]', 'API Key', 'org_123456789012345678901234567890', 'text', ['placeholder' => 'Enter your Bland AI API Key']); ?>
    </div>

    <?php
    if (get_option('bland_ai_api_key') != '') {
    ?>
        <div class="col-md-12">
            <hr>
        </div>
        <div class="col-md-4">
            <?php echo render_input('settings[bland_ai_max_duration]', 'Max Call Duration (mins) <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="When the call starts, a timer is set for the max_duration minutes. At the end of that timer, if the call is still active it will be automatically ended."></i>', get_option('bland_ai_max_duration'), "number"); ?>
        </div>

        <div class="col-md-4">
            <?php
            echo render_select_with_input_group(
                'settings[bland_ai_agent_voice]',
                alm_bland_ai_get_voices(),
                ['id', 'name', 'description'],
                'Select Agent Voice <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="The voice of the AI agent to use. Accepts any form of voice ID, including custom voice clones and voice presets. Default voices can be referenced directly by their name instead of an id."></i>',
                get_option('bland_ai_agent_voice'),
                '<div class="input-group-btn">
                    <a href="#" class="btn btn-default" onclick="play_selected_voice_bland();return false;" class="inline-field-new">
                        <i class="fa fa-volume-high"></i>
                    </a>
                </div>'
            );
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo render_input('settings[bland_ai_temperature]', 'Temperature <i class="fa fa-regular fa-question-circle pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="A value between 0 and 1 that controls the randomness of the LLM. 0 will cause more deterministic outputs while 1 will cause more random."></i>', get_option('bland_ai_temperature'), "number", ['min' => 0.0, 'max' => 1.0, 'step' => 0.1]);
            ?>
            <input type="range" id="bland_ai_temperature" min="0.0" max="1.0" step="0.1" value="<?= get_option('bland_ai_temperature'); ?>" style="width: 100%; margin-top: 10px;" />
        </div>
</div>
<div class="row">
    <div class="col-md-12">
        <hr>
    </div>

    <div class="col-md-4">
        <?php echo render_select('settings[bland_ai_knowledgebase_inbound][]', alm_bland_ai_get_knowledgebase(), ['vector_id', 'name'], 'Inbound Knowledge Base', json_decode(get_option('bland_ai_knowledgebase_inbound')), ['multiple' => true], [], '', '', false); ?>
    </div>

    <div class="col-md-4">
        <?php echo render_select('settings[bland_ai_knowledgebase_outbound][]', alm_bland_ai_get_knowledgebase(), ['vector_id', 'name'], 'Outbound Knowledge Base', json_decode(get_option('bland_ai_knowledgebase_outbound')), ['multiple' => true], [], '', '', false); ?>
    </div>

    <div class="col-md-12">
        <hr>
        <div class="row">
            <div class="col-md-10">
                <h4 class="tw-font-semibold">Knowledge Base</h4>
                <p class="tw-mb-3">Add your knowledge base here. You can add as many as you want.</p>
            </div>
            <div class="col-md-2">
                <div class="btn btn-default" data-toggle="modal" data-target="#bland_ai_kwnowledgebase_modal"><i class="fa fa-regular fa-plus"></i> Add Knowledge Base</div>
            </div>
        </div>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach (alm_bland_ai_get_knowledgebase() as $knowledgebase): ?>
                <tr>
                    <td><?= $knowledgebase['vector_id']; ?></td>
                    <td><?= $knowledgebase['name']; ?></td>
                    <td><?= $knowledgebase['description']; ?></td>
                    <td><?= _dt($knowledgebase['created_at']); ?></td>
                    <td>
                        <a href="<?= admin_url('ai_lead_manager/delete_knowledgebase/bland_ai/' . $knowledgebase['vector_id']); ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php } ?>
</div>
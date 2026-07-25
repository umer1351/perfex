<div role="tabpanel" class="tab-pane" id="tab_ai_call_logs_leads">
    <?php
    $alm_voice_assistant = get_option('alm_voice_assistant');

    $table_data = [
        '#',
        _l('alm_call_log_call_id'),
        _l('alm_call_log_created_at'),
        _l('alm_call_log_direction'),
        _l('alm_call_log_to_number'),
        _l('alm_call_log_from_number'),
        _l('alm_call_log_recording'),
        ($alm_voice_assistant == 'vapi_ai' ? _l('alm_call_log_ended_reason') : _l('alm_call_log_status')),
        _l('alm_call_log_duration'),
        _l('alm_call_log_price')
    ];

    $table_data = hooks()->apply_filters('alm_call_logs_relation_table_columns', $table_data);
    render_datatable($table_data, 'alm_call_logs-lead', [], [
        'data-last-order-identifier' => 'alm_call_logs-relation',
        'data-default-order'         => get_table_last_order('alm_call_logs-relation'),
    ]);
    ?>
</div>
<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();
$alm_voice_assistant = get_option('alm_voice_assistant');

$aColumns = [
    '' . db_prefix() . 'alm_call_logs.id as id',
    'call_id',
    'created_at',
    'direction',
    'to_number',
    'from_number',
    'recording_url',
    ($alm_voice_assistant == 'vapi_ai' ? 'call_ended_by' : 'status'),
    'call_length',
    'price'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'alm_call_logs';
$join         = [];

$where = 'AND rel_id = ' . $rel_id . ' AND rel_type = "' . $rel_type . '" AND ai_provider = "' . get_option('alm_voice_assistant') . '"';

if ($rel_type == 'customer') {
    $this->ci->db->where('userid', $rel_id);
    $customer = $this->ci->db->get(db_prefix() . 'clients')->row();
    if ($customer) {
        if (!is_null($customer->leadid)) {
            $where .= ' OR rel_type="lead" AND rel_id=' . $customer->leadid;
        }
    }
}

$where = [$where];

if (!has_permission('alm_call_logs', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'alm_call_logs.staff_id = ' . get_staff_user_id());
}

$aColumns = hooks()->apply_filters('alm_call_logs_relation_table_sql_columns', $aColumns);
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="' . admin_url('ai_lead_manager/call_logs/call_log/' . $aRow['id']) . '" onclick="init_alm_call_log_modal(' . $aRow['id'] . '); return false;">' . $aRow['id'] . '</a>';
    $row[] = '<button class="btn btn-default btn-sm" onClick="alm_copy_to_clipboard(\'' . $aRow['call_id'] . '\')">' . substr($aRow['call_id'], 0, 5) . '... &nbsp;&nbsp;&nbsp;<i class="fa fa-regular fa-copy"></i></button>';

    $row[] = _d($aRow['created_at']).'<br><a href="' . admin_url('ai_lead_manager/call_logs/call_log/' . $aRow['id']) . '"  onclick="init_alm_call_log_modal(' . $aRow['id'] . '); return false;">view</a>';
    $row[] = $aRow['direction'] == 0 ? '<i class="fa fa-arrow-down " title="Inbound" style="font-size: 20px; color: #ed143d"></i>' : '<i class="fa fa-arrow-up " style="font-size: 20px; color: #84c529" title="Outbound"></i>';

    $row[] = $aRow['to_number'];

    $row[] = $aRow['from_number'];

    $row[] = '<audio controls style="width: 210px; height: 30px"><source src="' . $aRow['recording_url'] . '" type="audio/wav">Your browser does not support the audio element.</audio>';

    $row[] =  ($alm_voice_assistant == 'vapi_ai' ? alm_format_call_log_status($aRow['call_ended_by']) : $aRow['status']);

    $row[] = convert_duration($aRow['call_length']);
    // $amount = app_format_money($aRow['price'], ($aRow['currency'] != 0 ? get_currency($aRow['currency']) : $baseCurrency));
    $row[] = $aRow['price'];

    $output['aaData'][] = $row;
}

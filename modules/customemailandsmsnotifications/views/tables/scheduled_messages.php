<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'customer_or_leads',
    'subject',
    'mail_or_sms',
    'custom_date',
    'custom_time',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'custom_email_sms';

$where = ['AND is_delivered = 0'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['select_customer', 'template', 'message']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    
    for ($i=0; $i<count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[$sIndexColumn];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }
        
        if ($aColumns[$i] == 'id') {
            $_data = '<a href="'.admin_url('customemailandsmsnotifications/scheduled_messages/view/'.$aRow['id']).'">#'.$_data.'</a>';
        } else if ($aColumns[$i] == 'customer_or_leads') {
            $_data = '<span class="label label-default">'.ucfirst($_data).'</span>';
        } else if ($aColumns[$i] == 'subject') {
            if (empty($_data)) {
                $_data = '<span class="text-muted">No subject</span>';
            }
        } else if ($aColumns[$i] == 'mail_or_sms') {
            $label_class = $_data == 'mail' ? 'info' : ($_data == 'whatsapp' ? 'success' : 'warning');
            $_data = '<span class="label label-'.$label_class.'">'.ucfirst($_data).'</span>';
        } else if ($aColumns[$i] == 'custom_date') {
            $time_part = !empty($aRow['custom_time']) ? ' ' . $aRow['custom_time'] : '';
            $_data = $aRow['custom_date'] . $time_part;
        } else if ($aColumns[$i] == 'custom_time') {
            continue; // Skip this column as it's combined with date
        }
        
        $row[] = $_data;
    }
    
    // Actions column
    $options = '<div class="btn-group">';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/scheduled_messages/view/'.$aRow['id']).'" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/scheduled_messages/edit/'.$aRow['id']).'" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></a>';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/scheduled_messages/send_now/'.$aRow['id']).'" class="btn btn-success btn-sm"><i class="fa fa-paper-plane"></i></a>';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/scheduled_messages/cancel/'.$aRow['id']).'" class="btn btn-danger btn-sm _delete"><i class="fa fa-remove"></i></a>';
    $options .= '</div>';
    
    $row[] = $options;
    
    $output['aaData'][] = $row;
}

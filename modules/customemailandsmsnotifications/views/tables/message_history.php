<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'message_type',
    'recipient_name',
    'recipient_contact',
    'subject',
    'status',
    'gateway',
    'sent_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'custom_message_history';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    'staff_id',
    'message_content',
    'created_at',
    'error_message'
]);

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
            $_data = '<a href="'.admin_url('customemailandsmsnotifications/message_history/view/'.$aRow['id']).'">#'.$_data.'</a>';
        } else if ($aColumns[$i] == 'message_type') {
            $_data = '<span class="label label-default">'.ucfirst($_data).'</span>';
        } else if ($aColumns[$i] == 'status') {
            $label_class = 'default';
            if ($_data == 'sent') {
                $label_class = 'success';
            } else if ($_data == 'failed') {
                $label_class = 'danger';
            } else if ($_data == 'scheduled') {
                $label_class = 'warning';
            } else if ($_data == 'pending') {
                $label_class = 'info';
            }
            $_data = '<span class="label label-'.$label_class.'">'.ucfirst($_data).'</span>';
        } else if ($aColumns[$i] == 'subject') {
            if (empty($_data)) {
                $_data = '<span class="text-muted">N/A</span>';
            }
        } else if ($aColumns[$i] == 'sent_at') {
            if (empty($_data)) {
                $_data = '<span class="text-muted">Not sent</span>';
            } else {
                $_data = _dt($_data);
            }
        }
        
        $row[] = $_data;
    }
    
    // Actions column
    $options = '<div class="btn-group">';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/message_history/view/'.$aRow['id']).'" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
    
    if ($aRow['status'] == 'failed') {
        $options .= '<a href="'.admin_url('customemailandsmsnotifications/message_history/resend/'.$aRow['id']).'" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i></a>';
    }
    
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/message_history/delete/'.$aRow['id']).'" class="btn btn-danger btn-sm _delete"><i class="fa fa-remove"></i></a>';
    $options .= '</div>';
    
    $row[] = $options;
    
    $output['aaData'][] = $row;
}

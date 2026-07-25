<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'group_name',
    'description',
    'recipient_type',
    'recipient_count',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'custom_recipient_groups';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['staff_id', 'customer_ids', 'lead_ids']);

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
            $_data = '<a href="'.admin_url('customemailandsmsnotifications/recipient_groups/manage/'.$aRow['id']).'">#'.$_data.'</a>';
        } else if ($aColumns[$i] == 'group_name') {
            $_data = '<a href="'.admin_url('customemailandsmsnotifications/recipient_groups/manage/'.$aRow['id']).'"><strong>'.$_data.'</strong></a>';
        } else if ($aColumns[$i] == 'description') {
            if (empty($_data)) {
                $_data = '<span class="text-muted">No description</span>';
            }
        } else if ($aColumns[$i] == 'recipient_type') {
            $_data = '<span class="label label-default">'.ucfirst($_data).'</span>';
        } else if ($aColumns[$i] == 'recipient_count') {
            $_data = '<span class="badge">'.$_data.'</span>';
        } else if ($aColumns[$i] == 'created_at') {
            $_data = _dt($_data);
        }
        
        $row[] = $_data;
    }
    
    // Actions column
    $options = '<div class="btn-group">';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/recipient_groups/manage/'.$aRow['id']).'" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></a>';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/recipient_groups/delete/'.$aRow['id']).'" class="btn btn-danger btn-sm _delete"><i class="fa fa-remove"></i></a>';
    $options .= '</div>';
    
    $row[] = $options;
    
    $output['aaData'][] = $row;
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'description',
    'color',
    'icon',
    'sort_order',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'custom_template_categories';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['created_at']);

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
            $_data = '#'.$_data;
        } else if ($aColumns[$i] == 'name') {
            $_data = '<strong>'.$_data.'</strong>';
        } else if ($aColumns[$i] == 'description') {
            if (empty($_data)) {
                $_data = '<span class="text-muted">No description</span>';
            }
        } else if ($aColumns[$i] == 'color') {
            $_data = '<span style="display:inline-block;width:20px;height:20px;background-color:'.$_data.';border-radius:3px;"></span> '.$_data;
        } else if ($aColumns[$i] == 'icon') {
            $_data = '<i class="fa '.$_data.'"></i> '.$_data;
        }
        
        $row[] = $_data;
    }
    
    // Actions column
    $options = '<div class="btn-group">';
    $options .= '<a href="javascript:void(0);" onclick="edit_category('.$aRow['id'].');" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i></a>';
    $options .= '<a href="'.admin_url('customemailandsmsnotifications/template/delete_category/'.$aRow['id']).'" class="btn btn-danger btn-sm _delete"><i class="fa fa-remove"></i></a>';
    $options .= '</div>';
    
    $row[] = $options;
    
    $output['aaData'][] = $row;
}

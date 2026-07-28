<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['file_sharing/file_sharing_client'] = 'file_sharing_client/index';
$route['file_sharing/gtsverify/activate'] = 'gtsverify/activate';
$route['file_sharing/download_directory/(:any)'] = 'file_sharing_public/download_directory/$1';
$route['file_sharing/download_file/(:any)'] = 'file_sharing_public/download_file/$1';
$route['file_sharing/file_sharing_client'] = 'file_sharing_client/index';
$route['file_sharing/file_sharing_client/(:any)'] = 'file_sharing_client/$1';
$route['file_sharing/file_sharing_public/download'] = 'file_sharing_public/download';
$route['file_sharing/file_sharing_public/check_download'] = 'file_sharing_public/check_download';
$route['file_sharing/file_sharing_public/share_downloaded'] = 'file_sharing_public/share_downloaded';
$route['file_sharing/file_sharing_public/get_url_by_hash'] = 'file_sharing_public/get_url_by_hash';

// Admin File_sharing controller methods must be matched here explicitly,
// before the catch-all below, or they get swallowed by it and routed to the
// public-facing controller instead.
$route['file_sharing/manage'] = 'file_sharing/manage';
$route['file_sharing/file_sharing_media_connector'] = 'file_sharing/file_sharing_media_connector';
$route['file_sharing/getDirectories/(.+)'] = 'file_sharing/getDirectories/$1';
$route['file_sharing/setting'] = 'file_sharing/setting';
$route['file_sharing/change_staff_permissions/(.+)'] = 'file_sharing/change_staff_permissions/$1';
$route['file_sharing/new_folder'] = 'file_sharing/new_folder';
$route['file_sharing/add_new_share'] = 'file_sharing/add_new_share';
$route['file_sharing/add_new_config'] = 'file_sharing/add_new_config';
$route['file_sharing/delete_config/(.+)'] = 'file_sharing/delete_config/$1';
$route['file_sharing/update_field/(.+)'] = 'file_sharing/update_field/$1';
$route['file_sharing/update_sharing_permission/(.+)'] = 'file_sharing/update_sharing_permission/$1';
$route['file_sharing/update_setting'] = 'file_sharing/update_setting';
$route['file_sharing/download_management'] = 'file_sharing/download_management';
$route['file_sharing/sharing'] = 'file_sharing/sharing';
$route['file_sharing/download_management_table'] = 'file_sharing/download_management_table';
$route['file_sharing/sharing_table'] = 'file_sharing/sharing_table';
$route['file_sharing/sharing_detail_table'] = 'file_sharing/sharing_detail_table';
$route['file_sharing/reports'] = 'file_sharing/reports';
$route['file_sharing/edit_sharing/(.+)'] = 'file_sharing/edit_sharing/$1';
$route['file_sharing/delete_sharing/(.+)'] = 'file_sharing/delete_sharing/$1';
$route['file_sharing/sharing_chart'] = 'file_sharing/sharing_chart';
$route['file_sharing/download_chart'] = 'file_sharing/download_chart';
$route['file_sharing/send_mail_to_public'] = 'file_sharing/send_mail_to_public';

$route['file_sharing/(:any)'] = 'file_sharing_public/index/$1';
<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Admin Flexform controller methods must be matched here explicitly,
// before the catch-all below, or they get swallowed by it and routed to the
// client-facing Clientflexform controller instead (which also happens to
// define a same-named 'pending' method, making the conflict silent).
$route['flexform/pending'] = 'flexform/pending';
$route['flexform/staff'] = 'flexform/staff';
$route['flexform/staff/(.+)'] = 'flexform/staff/$1';
$route['flexform/new_form'] = 'flexform/new_form';
$route['flexform/update_form'] = 'flexform/update_form';
$route['flexform/update_block'] = 'flexform/update_block';
$route['flexform/update_logic'] = 'flexform/update_logic';
$route['flexform/publish'] = 'flexform/publish';
$route['flexform/responses'] = 'flexform/responses';
$route['flexform/responses/(.+)'] = 'flexform/responses/$1';
$route['flexform/duplicate/(.+)'] = 'flexform/duplicate/$1';
$route['flexform/delete/(.+)'] = 'flexform/delete/$1';

$route['flexform/(:any)'] = 'clientflexform/$1';

<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['deals/track/open/(:any)'] = 'track/open/$1';
$route['deals/track/click/(:any)'] = 'track/click/$1';
$route['deals/track/unsubscribe/(:any)'] = 'track/unsubscribe/$1';
$route['deals/ingress/inbound/(:any)'] = 'ingress/inbound/$1';
$route['deals/ingress/bounce/(:any)'] = 'ingress/bounce/$1';
$route['deals/platform/oauth/(:any)'] = 'integrationcontroller/oauth/$1';
$route['deals/platform/webhook/(:any)/(:num)'] = 'webhookcontroller/provider/$1/$2';

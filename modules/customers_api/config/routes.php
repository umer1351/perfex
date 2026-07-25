<?php

$method = $_SERVER['REQUEST_METHOD'];

$route['customers_api/(.*)/(.*)/(.*)/(.*)/(.*)/(.*)'] = '$1/$2/$2/$3/$4/$5/$6';
$route['customers_api/(.*)/(.*)/(.*)/(.*)']           = '$1/$2/$2/$3/$4';
$route['customers_api/(:any)/(:any)']                 = '$1/$2/$2';

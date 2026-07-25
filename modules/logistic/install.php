<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'lg_office_group')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_office_group` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `office_name` TEXT NOT NULL,
  `office_code` TEXT NOT NULL,
  `address` TEXT NOT NULL,
  `city` TEXT NULL,
  `phone` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_agency_group')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_agency_group` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `agency_name` TEXT NOT NULL,
  `address` TEXT NOT NULL,
  `city` TEXT NULL,
  `phone` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shipping_companies')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shipping_companies` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_company_name` TEXT NOT NULL,
  `address` TEXT NOT NULL,
  `city` TEXT NULL,
  `country` INT(11) NULL,
  `phone` TEXT NULL,
  `postcode` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_type_of_packages')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_type_of_packages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type_of_package_name` TEXT NOT NULL,
  `package_type_details` TEXT NOT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shipping_modes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shipping_modes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_mode_name` TEXT NOT NULL,
  `service_price_details` TEXT NOT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shipping_times')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shipping_times` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_time_name` TEXT NOT NULL,
  `service_price_details` TEXT NOT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_style_and_states')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_style_and_states` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `style_name` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `button_color` TEXT NOT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_logistics_services')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_logistics_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `logistics_service_name` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

add_option('lg_minium_cost_to_apply_the_tax', 300);
add_option('lg_tax_percent', 19);
add_option('lg_minium_cost_to_apply_declared_tax', 250);
add_option('lg_tax_declared', 3);
add_option('lg_shipping_insurance_percent', 2);
add_option('lg_customs_duties', 0.1);
add_option('lg_volume_percentage_l_w_h', 500);
add_option('lg_length_units', 'cm');
add_option('lg_weight_value', 3.55);
add_option('lg_weight_units', 'kg');

if (!$CI->db->table_exists(db_prefix() . 'lg_countries')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_countries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `country_name` TEXT NOT NULL,
  `iso_code` TEXT NULL,
  `phone_code` TEXT NULL,
  `capital` TEXT NULL,
  `region` TEXT NULL,
  `currency_id` INT(11) NULL,
  `active` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_states')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_states` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `state_name` TEXT NOT NULL,
  `iso_code` TEXT NULL,
  `country` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_cities')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_cities` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `city_name` TEXT NOT NULL,
  `state` INT(11) NULL,
  `country` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shipping_rates_list')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shipping_rates_list` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `origin` INT(11) NOT NULL,
  `state` INT(11) NULL,
  `country` INT(11) NULL,
  `city` INT(11) NULL,
  `start_weight_range` DECIMAL(15,2) NULL,
  `end_weight_range` DECIMAL(15,2) NULL,
  `rate_price` DECIMAL(15,2) NULL,
  `active` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

add_option('lg_delivery_prefix', 'AWB');
add_option('lg_consolidate_prefix', 'COEE');
add_option('lg_internet_shopping_prefix', 'WIL');
add_option('lg_number_digits_in_the_trace', 6);
add_option('lg_number_digits_in_the_consolidate', 6);
add_option('lg_number_digits_to_track_locker_packages', 6);
add_option('lg_tracking_number_type', 'random');
add_option('lg_number_of_random_digits', 10);
add_option('lg_default_invoice_terms', '');
add_option('lg_invoice_company_signature', '');
add_option('lg_customer_signature_billing', '');

if (!$CI->db->table_exists(db_prefix() . 'lg_payment_terms')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_payment_terms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `days` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

add_option('lg_default_logistic_service', '');
add_option('lg_default_type_of_package', '');
add_option('lg_default_courier_company', '');
add_option('lg_default_service_mode', '');
add_option('lg_default_delivery_status', '');
add_option('lg_default_payment_method', '');
add_option('lg_default_delivery_time', '');
add_option('lg_default_payment_terms', '');



if (!$CI->db->field_exists('note' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `note` TEXT NULL
  ;");
}

// driver / staff
if (!$CI->db->field_exists('staff_type' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `staff_type` VARCHAR(11) NULL DEFAULT 'staff'
  ;");
}



if (!$CI->db->field_exists('gender' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `gender` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('vehicle_license_plate' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `vehicle_license_plate` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('vehicle_code' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `vehicle_code` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('office_group' ,db_prefix() . 'staff')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "staff`
    ADD COLUMN `office_group` INT(11) NULL
  ;");
}


if (!$CI->db->table_exists(db_prefix() . 'lg_packages')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_packages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_prefix` TEXT NULL,
  `number_code` TEXT NULL,
  `number` INT NULL,
  `number_type` TEXT NULL,
  `agency` INT(11) NULL,
  `office_of_origin` INT(11) NULL,
  `customer_id` INT(11) NULL,
  `customer_address` INT(11) NULL,
  `tracking_purchase` TEXT NULL,
  `store_supplier` TEXT NULL,
  `purchase_price` DECIMAL(15,2) NULL,
  `logistic_service_id` INT(11) NULL,
  `type_of_package` INT(11) NULL,
  `courrier_company` INT(11) NULL,
  `service_mode` INT(11) NULL,
  `delivery_time` INT(11) NULL,
  `assign_driver` INT(11) NULL,
  `delivery_status` INT(11) NULL,
  `price_kg` DECIMAL(15,2) NULL,
  `discount_percent` DECIMAL(15,2) NULL,
  `discount` DECIMAL(15,2) NULL,
  `value_assured` DECIMAL(15,2) NULL,
  `shipping_insurance_percent` DECIMAL(15,2) NULL,
  `shipping_insurance` DECIMAL(15,2) NULL,
  `custom_duties_percent` DECIMAL(15,2) NULL,
  `custom_duties` DECIMAL(15,2) NULL,
  `tax_percent` DECIMAL(15,2) NULL,
  `tax` DECIMAL(15,2) NULL,
  `declared_value_percent` DECIMAL(15,2) NULL,
  `declared_value` DECIMAL(15,2) NULL,
  `reissue` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `subtotal` DECIMAL(15,2) NULL,
  `total` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_the_tax_setting` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_declared_tax_setting` DECIMAL(15,2) NULL,
  `volume_percentage_setting` DECIMAL(15,2) NULL,
  `length_units_setting` TEXT NULL,
  `weight_units_setting` TEXT NULL,
  `weight_value_setting` DECIMAL(15,2) NULL,
  `currency` INT(11) NULL,
  `currency_rate` DECIMAL(15,6) NULL,
  `from_currency` TEXT NULL,
  `to_currency` TEXT NULL,
  `invoice_id` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'lg_package_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_package_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `package_id` INT(11) NULL,
  `amount` DECIMAL(15,2) NULL,
  `weight` DECIMAL(15,2) NULL,
  `length` DECIMAL(15,2) NULL,
  `width` DECIMAL(15,2) NULL,
  `height` DECIMAL(15,2) NULL,
  `weight_vol` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `dec_value` DECIMAL(15,2) NULL,
  `package_description` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_client_address')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_client_address` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `country` INT(11) NULL,
  `state` INT(11) NULL,
  `city` INT(11) NULL,
  `client_id` INT(11) NULL,
  `zip_code` TEXT NULL,
  `address` TEXT NULL,
  `created_by_type` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'currency_rates')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "currency_rates` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_currency_id` int(11) NULL,
    `from_currency_name` VARCHAR(100) NULL,
    `from_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `to_currency_id` int(11) NULL,
    `to_currency_name` VARCHAR(100) NULL,
    `to_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'currency_rate_logs')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "currency_rate_logs` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_currency_id` int(11) NULL,
    `from_currency_name` VARCHAR(100) NULL,
    `from_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `to_currency_id` int(11) NULL,
    `to_currency_name` VARCHAR(100) NULL,
    `to_currency_rate` decimal(15,6) NOT NULL DEFAULT '0.000000',
    `date` DATE NULL,

    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


add_option('cr_date_cronjob_currency_rates', '');
add_option('cr_automatically_get_currency_rate', 1);
add_option('cr_global_amount_expiration', 0);

if (!$CI->db->field_exists('is_default_status' ,db_prefix() . 'lg_style_and_states')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "lg_style_and_states`
    ADD COLUMN `is_default_status` INT(11) NULL DEFAULT '0'
  ;");
}
lg_create_default_style_and_states();


if (!$CI->db->table_exists(db_prefix() . 'lg_packages_delivery_shipment')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_packages_delivery_shipment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `package_id` INT(11) NULL,
  `delivery_date` DATETIME NULL,
  `delivered_by` INT(11) NULL,
  `receive_by` TEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_tracking_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_tracking_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NULL,
  `rel_type` TEXT NULL,
  `time_update` DATETIME NULL,
  `new_location` INT(11) NULL,
  `city_or_address` TEXT NULL,
  `office` INT(11) NULL,
  `delivery_status` INT(11) NULL,
  `remark` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_action_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_action_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NULL,
  `rel_type` TEXT NULL,
  `time_update` DATETIME NULL,
  `user` INT(11) NULL,
  `action` TEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


add_option('lg_virtual_locker_number_type', 'random');
add_option('lg_locker_number_of_random_digits', 5);
add_option('lg_locker_prefix', 'LOC');

if (!$CI->db->field_exists('locker_code' ,db_prefix() . 'clients')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "clients`
    ADD COLUMN `locker_code` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('locker_code_prefix' ,db_prefix() . 'clients')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "clients`
    ADD COLUMN `locker_code_prefix` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('locker_code_number' ,db_prefix() . 'clients')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "clients`
    ADD COLUMN `locker_code_number` INT(11) NULL
  ;");
}

if (!$CI->db->field_exists('locker_code_type' ,db_prefix() . 'clients')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "clients`
    ADD COLUMN `locker_code_type` TEXT NULL
  ;");
}

create_email_template('Locker Package', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have prepared the following package for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{package_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Package (Sent to contact)', 'logistic-package-to-contact');


create_email_template('Package Delivered', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The shipment #{tracking_number} has been delivered </span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{package_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Package Delivered (Sent to contact)', 'package-delivered-to-contact');

if (!$CI->db->table_exists(db_prefix() . 'lg_pre_alert')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_pre_alert` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tracking_purchase` TEXT NULL,
  `delivery_date` DATETIME NULL,
  `courier_company` INT(11) NULL,
  `store_supplier` TEXT NULL,
  `purchase_price` DECIMAL(15,2) NULL,
  `package_description` TEXT NULL,
  `package_id` INT(11) NULL,
  `client_id` INT(11) NULL,
  `status` INT(11) NULL,
  `currency` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


create_email_template('New Pre Alert Created', '<span style=\"font-size: 12pt;\">New Pre Alert Created #{tracking_purchase} </span><br /><br /><span style=\"font-size: 12pt;\">Click here to convert to package <a href="{convert_link}">#{tracking_purchase}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the pre alert on the following link: <a href="{pre_alert_list_url}">Pre Alert List</a>', 'logistic', 'New Pre Alert Created', 'new-pre-alert-created');


if (!$CI->db->table_exists(db_prefix() . 'lg_recipients')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_recipients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NULL,
  `first_name` TEXT NULL,
  `last_name` TEXT NULL,
  `phone` TEXT NULL,
  `email` TEXT NULL,
  `created_by_type` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_recipient_address')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_recipient_address` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` INT(11) NULL,
  `country` INT(11) NULL,
  `state` INT(11) NULL,
  `city` INT(11) NULL,
  `zip_code` TEXT NULL,
  `address` TEXT NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shippings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shippings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_prefix` TEXT NULL,
  `number_code` TEXT NULL,
  `number` INT NULL,
  `number_type` TEXT NULL,
  `agency` INT(11) NULL,
  `office_of_origin` INT(11) NULL,
  `customer_id` INT(11) NULL,
  `customer_address` INT(11) NULL,
  `recipient_id` INT(11) NULL,
  `recipient_address_id` INT(11) NULL,
  `tracking_purchase` TEXT NULL,
  `store_supplier` TEXT NULL,
  `purchase_price` DECIMAL(15,2) NULL,
  `logistic_service_id` INT(11) NULL,
  `payment_term_id` INT(11) NULL,
  `type_of_package` INT(11) NULL,
  `courrier_company` INT(11) NULL,
  `service_mode` INT(11) NULL,
  `delivery_time` INT(11) NULL,
  `assign_driver` INT(11) NULL,
  `delivery_status` INT(11) NULL,
  `payment_term` INT(11) NULL,
  `price_kg` DECIMAL(15,2) NULL,
  `discount_percent` DECIMAL(15,2) NULL,
  `discount` DECIMAL(15,2) NULL,
  `value_assured` DECIMAL(15,2) NULL,
  `shipping_insurance_percent` DECIMAL(15,2) NULL,
  `shipping_insurance` DECIMAL(15,2) NULL,
  `custom_duties_percent` DECIMAL(15,2) NULL,
  `custom_duties` DECIMAL(15,2) NULL,
  `tax_percent` DECIMAL(15,2) NULL,
  `tax` DECIMAL(15,2) NULL,
  `declared_value_percent` DECIMAL(15,2) NULL,
  `declared_value` DECIMAL(15,2) NULL,
  `reissue` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `subtotal` DECIMAL(15,2) NULL,
  `total` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_the_tax_setting` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_declared_tax_setting` DECIMAL(15,2) NULL,
  `volume_percentage_setting` DECIMAL(15,2) NULL,
  `length_units_setting` TEXT NULL,
  `weight_units_setting` TEXT NULL,
  `weight_value_setting` DECIMAL(15,2) NULL,
  `currency` INT(11) NULL,
  `currency_rate` DECIMAL(15,6) NULL,
  `from_currency` TEXT NULL,
  `to_currency` TEXT NULL,
  `invoice_id` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_shipping_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shipping_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_id` INT(11) NULL,
  `amount` DECIMAL(15,2) NULL,
  `weight` DECIMAL(15,2) NULL,
  `length` DECIMAL(15,2) NULL,
  `width` DECIMAL(15,2) NULL,
  `height` DECIMAL(15,2) NULL,
  `weight_vol` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `dec_value` DECIMAL(15,2) NULL,
  `package_description` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


create_email_template('The Shipping', '<span style=\"font-size: 12pt;\"> Dear {recipient_firstname} {recipient_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have prepared the following shipment for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">The shipping status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping (Sent to recipient)', 'logistic-shipping-to-contact');

if (!$CI->db->table_exists(db_prefix() . 'lg_shippings_delivery_shipment')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_shippings_delivery_shipment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_id` INT(11) NULL,
  `delivery_date` DATETIME NULL,
  `delivered_by` INT(11) NULL,
  `receive_by` TEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


create_email_template('Pickup Delivered', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The shipment #{tracking_number} has been delivered </span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the pickup on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping Delivered (Sent to contact)', 'shipping-delivered-to-contact');

if (!$CI->db->field_exists('shipping_type' ,db_prefix() . 'lg_shippings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "lg_shippings`
    ADD COLUMN `shipping_type` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('approve_status' ,db_prefix() . 'lg_shippings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "lg_shippings`
    ADD COLUMN `approve_status` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('created_from' ,db_prefix() . 'lg_shippings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "lg_shippings`
    ADD COLUMN `created_from` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('approve_note' ,db_prefix() . 'lg_shippings')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "lg_shippings`
    ADD COLUMN `approve_note` TEXT NULL
  ;");
}

create_email_template('Pickup Approved', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> The pickup has been approved</span><br /><br /><span style=\"font-size: 12pt;\">Status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Pickup Approved (Sent to contact)', 'pickup-approved');


create_email_template('Pickup Rejected', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> The pickup has been rejected</span><br /><br /><span style=\"font-size: 12pt;\">Status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Pickup Rejected (Sent to contact)', 'pickup-rejected');


if (!$CI->db->table_exists(db_prefix() . 'lg_consolidated')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_consolidated` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shipping_prefix` TEXT NULL,
  `number_code` TEXT NULL,
  `number` INT NULL,
  `number_type` TEXT NULL,
  `agency` INT(11) NULL,
  `stamps` TEXT NULL,
  `office_of_origin` INT(11) NULL,
  `customer_id` INT(11) NULL,
  `customer_address` INT(11) NULL,
  `recipient_id` INT(11) NULL,
  `recipient_address_id` INT(11) NULL,
  `tracking_purchase` TEXT NULL,
  `store_supplier` TEXT NULL,
  `purchase_price` DECIMAL(15,2) NULL,
  `logistic_service_id` INT(11) NULL,
  `payment_term_id` INT(11) NULL,
  `type_of_package` INT(11) NULL,
  `courrier_company` INT(11) NULL,
  `service_mode` INT(11) NULL,
  `delivery_time` INT(11) NULL,
  `assign_driver` INT(11) NULL,
  `delivery_status` INT(11) NULL,
  `payment_term` INT(11) NULL,
  `price_kg` DECIMAL(15,2) NULL,
  `discount_percent` DECIMAL(15,2) NULL,
  `discount` DECIMAL(15,2) NULL,
  `value_assured` DECIMAL(15,2) NULL,
  `shipping_insurance_percent` DECIMAL(15,2) NULL,
  `shipping_insurance` DECIMAL(15,2) NULL,
  `custom_duties_percent` DECIMAL(15,2) NULL,
  `custom_duties` DECIMAL(15,2) NULL,
  `tax_percent` DECIMAL(15,2) NULL,
  `tax` DECIMAL(15,2) NULL,
  `declared_value_percent` DECIMAL(15,2) NULL,
  `declared_value` DECIMAL(15,2) NULL,
  `reissue` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `subtotal` DECIMAL(15,2) NULL,
  `total` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_the_tax_setting` DECIMAL(15,2) NULL,
  `minium_cost_to_apply_declared_tax_setting` DECIMAL(15,2) NULL,
  `volume_percentage_setting` DECIMAL(15,2) NULL,
  `length_units_setting` TEXT NULL,
  `weight_units_setting` TEXT NULL,
  `weight_value_setting` DECIMAL(15,2) NULL,
  `currency` INT(11) NULL,
  `currency_rate` DECIMAL(15,6) NULL,
  `from_currency` TEXT NULL,
  `to_currency` TEXT NULL,
  `rel_type` TEXT NULL,
  `rel_id` TEXT NULL,
  `invoice_id` INT(11) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'lg_consolidated_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_consolidated_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `consolidated_id` INT(11) NULL,
  `amount` DECIMAL(15,2) NULL,
  `weight` DECIMAL(15,2) NULL,
  `length` DECIMAL(15,2) NULL,
  `width` DECIMAL(15,2) NULL,
  `height` DECIMAL(15,2) NULL,
  `weight_vol` DECIMAL(15,2) NULL,
  `fixed_charge` DECIMAL(15,2) NULL,
  `dec_value` DECIMAL(15,2) NULL,
  `package_description` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

create_email_template('Consolidated Package', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have prepared the following package for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the consolidated on the following link: <a href="{consolidated_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Consolidated (Sent to contact)', 'logistic-consolidation-to-contact');


if (!$CI->db->table_exists(db_prefix() . 'lg_consolidation_delivery_shipment')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'lg_consolidation_delivery_shipment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `consolidated_id` INT(11) NULL,
  `delivery_date` DATETIME NULL,
  `delivered_by` INT(11) NULL,
  `receive_by` TEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


create_email_template('Consolidation Delivered', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The consolidation #{tracking_number} has been delivered </span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{consolidated_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Consolidation Delivered (Sent to contact)', 'consolidation-delivered-to-contact');


create_email_template('Locker Package Created', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have created the following locker package for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{package_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Package Created (Sent to contact)', 'logistic-package-created-to-contact');



create_email_template('Package Shipment Tracking', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The shipment tracking for package #{tracking_number} has been created</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{package_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Package Shipment Tracking Created (Sent to contact)', 'logistic-package-shipment-tracking');


create_email_template('Package Assign Driver', '<span style=\"font-size: 12pt;\"> Dear driver</span><br /><br /><span style=\"font-size: 12pt;\">You are assigned to the package #{tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{package_admin_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Package Assign Driver (Sent to Driver)', 'logistic-package-assign-driver');



create_email_template('Shipping Assign Driver', '<span style=\"font-size: 12pt;\"> Dear driver</span><br /><br /><span style=\"font-size: 12pt;\">You are assigned to the shipping #{tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_admin_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping Assign Driver (Sent to Driver)', 'logistic-shipping-assign-driver');


create_email_template('Consolidated Assign Driver', '<span style=\"font-size: 12pt;\"> Dear driver</span><br /><br /><span style=\"font-size: 12pt;\">You are assigned to the consolidation #{tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{consolidated_admin_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Consolidated Assign Driver (Sent to Driver)', 'logistic-consolidated-assign-driver');


create_email_template('Shipping Created', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have created the following shipping for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping Created (Sent to contact)', 'logistic-shipping-created-to-contact');


create_email_template('Shipping Shipment Tracking', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The shipment tracking for package #{tracking_number} has been created</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping Shipment Tracking Created (Sent to contact)', 'logistic-shipping-shipment-tracking');


create_email_template('Pickup Created', '<span style=\"font-size: 12pt;\"> The pickup has been created: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{shipping_admin_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Shipping Created (Sent to staff)', 'logistic-pickup-created-to-staff');


create_email_template('Consolidation Shipment Tracking', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The shipment tracking for package #{tracking_number} has been created</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{consolidated_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Consolidation Shipment Tracking (Sent to contact)', 'consolidation-shipment-tracking-to-contact');


create_email_template('Consolidation Created', '<span style=\"font-size: 12pt;\"> Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\"> We have created the following consolidation for you: # {tracking_number}</span><br /><br /><span style=\"font-size: 12pt;\">Package status: {delivery_status}
  </span><br /><br /><span style=\"font-size: 12pt;\">You can view the package on the following link: <a href="{consolidated_client_url}">{tracking_number}</a>
  </span><br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.
  </span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'logistic', 'Consolidation Created (Sent to contact)', 'logistic-consolidation-created-to-contact');
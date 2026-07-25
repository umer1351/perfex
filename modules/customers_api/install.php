<?php

defined('BASEPATH') || exit('No direct script access allowed');

update_option('customers_api_enabled', 1);
add_option('allow_register_api', 1);

if (table_exists('contacts')) {
	if (!get_instance()->db->field_exists('customer_api_key', db_prefix() . 'contacts')) {
	    get_instance()->db->query('ALTER TABLE `' . db_prefix() . 'contacts` ADD `customer_api_key` TEXT NULL DEFAULT NULL AFTER `ticket_emails`');
	}
}

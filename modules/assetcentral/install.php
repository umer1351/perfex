<?php
defined('BASEPATH') or exit('No direct script access allowed');

add_option('assetcentral_show_assets_on_clients_menu', '1');

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_assets')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_assets` (
  `id` int(11) NOT NULL,
  `asset_name` text,
  `asset_description` text,
  `serial_number` text,
  `model_number` text,
  `purchase_cost` text,
  `supplier_name` text,
  `supplier_phone_number` text,
  `supplier_address` text,
  `asset_image` text,
  `asset_manager` int default 0,
  `warranty_period_month` int default 0,
  `depreciation_months` int default 0,
  `depreciation_percentage` text,
  `residual_value` text,
  `depreciation_method` text,
  `appreciation_rate` text,
  `appreciation_periods` text,
  `location_id` int default 0,
  `category_id` int default 0,
  `asset_status` int default 1,
  `is_enabled` int default 0, 
  `show_project_assets_to_client` int default 0, 
  `purchase_date` datetime,
  `created_at` datetime,
  `update_date` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_assets`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_assigned')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_assigned` (
  `id` int(11) NOT NULL,
  `asset_id` int default 0,
  `assigned_to` text,
  `assigned_id` text, 
  `assign_date` datetime,
  `return_date` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_assigned`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_assigned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_history` (
      `id` int(11) NOT NULL,
      `asset_id` int(11) NOT NULL,
      `event` text,
      `field` text,
      `changed_from` text,
      `changed_to` text,
      `changed_by` int(11) NOT NULL,
      `changed_at` datetime
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_history`
      ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_history`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_events')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_events` (
      `id` int(11) NOT NULL,
      `asset_id` int(11) NOT NULL,
      `event_by` int(11) DEFAULT 0,
      `event_form` text,
      `created_at` datetime
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_events`
      ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_events`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_categories` (
  `id` int(11) NOT NULL,
  `category_name` text,
  `category_description` text,
  `is_enabled` int default 1, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_categories`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_locations` (
  `id` int(11) NOT NULL,
  `location_name` text,
  `manager_id` int default 0,
  `address` text,
  `city` text,
  `state` text,
  `country` int default 0,
  `zip_code` text,
  `lat` text,
  `lng` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_locations`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
require_once __DIR__ . '/vendor/chillerlan/php-qrcode/src/Data/Argument.php';
if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_maintenance')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_maintenance` (
  `id` int(11) NOT NULL,
  `asset_id` int default 0,
  `maintenance_title` text,
  `maintenance_description` text,
  `cost` text,
  `start_date` datetime,
  `end_date` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_maintenance`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_requests')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_requests` (
  `id` int(11) NOT NULL,
  `request_title` text,
  `request_description` text,
  `asset_id` int default 0,
  `requested_by` int default 0,
  `requested_by_type` text,
  `request_type_id` int default 0,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_requests`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

//Request type can be broken/lost/change asset etc.. or we can make this dynamic
if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_request_types')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_request_types` (
  `id` int(11) NOT NULL,
  `type_name` text,
  `type_description` text,
  `is_enabled` int default 1, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_request_types`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_request_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'assetcentral_asset_audits')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "assetcentral_asset_audits` (
  `id` int(11) NOT NULL,
  `audit_name` text,
  `audit_by` int,
  `audit_date` text,
  `assets_list` text,
  `notes` text,
  `is_finished` int default 0, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_audits`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'assetcentral_asset_audits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
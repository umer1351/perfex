<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if ($CI->db->field_exists('deal_comment_id', db_prefix() . 'files')) {
    $CI->db->query("ALTER TABLE " . db_prefix() . "`files` DROP COLUMN `deal_comment_id`;");
}
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_comments`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_email`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_items`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_mettings`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_pipelines`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_source`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_stages`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deal_activity_log`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deal_calls`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_followups`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_campaigns`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_campaign_steps`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_automation_rules`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_automation_queue`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_campaign_messages`;");
$CI->db->query("DROP TABLE IF EXISTS `tbl_deals_email_preferences`;");



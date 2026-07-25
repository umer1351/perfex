<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook & Instagram Leads Integration Language File
 * 
 * @package    FacebookLeadsIntegration
 * @author     Themesic Interactive
 * @version    2.0.0
 */

// Module name and description
$lang['facebookleadsintegration'] = 'Meta Leads';
$lang['fb_leads_module_description'] = 'Sync leads from Facebook & Instagram Lead Ads to ZAM ERP in real-time. Monitor multiple pages and capture leads automatically.';

// Navigation and menu
$lang['fb_leads_settings'] = 'Settings';
$lang['fb_leads_sync_history'] = 'Sync History';
$lang['fb_leads_field_mapping'] = 'Field Mapping';
$lang['fb_leads_back_to_settings'] = 'Back to Settings';

// Setup wizard
$lang['fb_leads_setup_wizard'] = 'Setup Wizard';
$lang['fb_leads_setup_wizard_title'] = 'Facebook & Instagram Leads Integration Setup';
$lang['fb_leads_setup_wizard_description'] = 'Follow these steps to configure Facebook & Instagram Lead Ads integration with your ZAM ERP installation.';
$lang['fb_leads_step'] = 'Step';
$lang['fb_leads_next_step'] = 'Next Step';
$lang['fb_leads_prev_step'] = 'Previous';
$lang['fb_leads_complete_setup'] = 'Complete Setup';
$lang['fb_leads_skip_setup_wizard'] = 'Skip setup wizard and configure manually';
$lang['fb_leads_setup_complete'] = 'Setup completed successfully! You can now start receiving leads from Facebook & Instagram.';

// Setup wizard steps
$lang['fb_leads_step1_title'] = 'Create Facebook App';
$lang['fb_leads_step1_description'] = 'Create a new Facebook App in the Meta Developers portal.';
$lang['fb_leads_step1_instruction1'] = 'Go to Meta for Developers and click "Create App"';
$lang['fb_leads_step1_instruction2'] = 'Filter by "Other" on the left sidebar';
$lang['fb_leads_step1_instruction3'] = 'Select "Create an app without a use case" (last option at the bottom)';
$lang['fb_leads_step1_instruction4'] = 'Select "I don\'t want to connect a business portfolio yet." and click Next';
$lang['fb_leads_step1_instruction5'] = 'Enter your app name (e.g., "My CRM Leads") and contact email, then click Create';
$lang['fb_leads_create_app_button'] = 'Create Facebook App';

$lang['fb_leads_step2_title'] = 'Enter App Credentials';
$lang['fb_leads_step2_description'] = 'Copy your App ID and App Secret from the Facebook App settings.';
$lang['fb_leads_validate_credentials'] = 'Validate Credentials';
$lang['fb_leads_credentials_valid'] = 'Credentials are valid!';
$lang['fb_leads_credentials_invalid'] = 'Invalid credentials. Please check your App ID and App Secret.';
$lang['fb_leads_enter_credentials'] = 'Please enter both App ID and App Secret';
$lang['fb_leads_validating'] = 'Validating...';

$lang['fb_leads_step3_title'] = 'Configure Webhooks';
$lang['fb_leads_step3_description'] = 'Set up the webhook in your Facebook App to receive lead notifications.';
$lang['fb_leads_step3_instruction1'] = 'In your Facebook App, go to "Webhooks" in the left sidebar and click "Add Product" if needed';
$lang['fb_leads_step3_instruction2'] = 'Click "Configure" next to Webhooks, then select "Page" from the dropdown';
$lang['fb_leads_step3_instruction3'] = 'Click "Subscribe to this object" and enter the Callback URL and Verify Token below';
$lang['fb_leads_step3_instruction4'] = 'Under "Page Subscriptions", check the "leadgen" checkbox and save';

$lang['fb_leads_step4_title'] = 'Connect Facebook Pages';
$lang['fb_leads_step4_description'] = 'Log in with Facebook and select which pages to monitor for leads.';
$lang['fb_leads_select_pages_to_monitor'] = 'Select pages to monitor for leads:';
$lang['fb_leads_connected_successfully'] = 'Connected to Facebook successfully!';

$lang['fb_leads_step5_title'] = 'Configure Lead Settings';
$lang['fb_leads_step5_description'] = 'Set default values for incoming leads.';
$lang['fb_leads_completing'] = 'Completing setup...';

// Connection status
$lang['fb_leads_connection_status'] = 'Connection Status';
$lang['fb_leads_app_credentials'] = 'App Credentials';
$lang['fb_leads_webhook_status'] = 'Webhook';
$lang['fb_leads_access_token'] = 'Access Token';
$lang['fb_leads_configured'] = 'Configured';
$lang['fb_leads_not_configured'] = 'Not Configured';
$lang['fb_leads_verified'] = 'Verified';
$lang['fb_leads_not_verified'] = 'Not Verified';
$lang['fb_leads_token_saved'] = 'Token Saved';
$lang['fb_leads_no_token'] = 'No Token';
$lang['fb_leads_test_connection'] = 'Test Connection';
$lang['fb_leads_send_test_lead'] = 'Send Test Lead';
$lang['fb_leads_testing'] = 'Testing...';
$lang['fb_leads_sending'] = 'Sending...';
$lang['fb_leads_connection_successful'] = 'Connection Successful';
$lang['fb_leads_connection_failed'] = 'Connection Failed';
$lang['fb_leads_permissions'] = 'Permissions';
$lang['fb_leads_all_permissions_granted'] = 'All required permissions granted';
$lang['fb_leads_missing_permissions'] = 'Missing permissions';
$lang['fb_leads_view_test_lead_confirm'] = 'Test lead created! Would you like to view it?';

// App settings
$lang['fb_leads_app_settings'] = 'Meta App Settings';
$lang['fb_leads_app_id'] = 'App ID';
$lang['fb_leads_app_secret'] = 'App Secret';
$lang['fb_leads_app_id_help'] = 'Find this in your Meta App → Settings → Basic';
$lang['fb_leads_app_secret_help'] = 'Keep this secret! Never share it publicly.';
$lang['fb_leads_get_from_meta'] = 'Get from Meta Developers';
$lang['fb_leads_save_settings'] = 'Save Settings';
$lang['fb_leads_saving'] = 'Saving...';

// Webhook settings
$lang['fb_leads_webhook_settings'] = 'Webhook Settings';
$lang['fb_leads_webhook_url'] = 'Webhook Callback URL';
$lang['fb_leads_verify_token'] = 'Verify Token';
$lang['fb_leads_webhook_callback_url'] = 'Callback URL';
$lang['fb_leads_webhook_url_help'] = 'Enter this URL in your Facebook App webhook settings.';
$lang['fb_leads_verify_token_help'] = 'Enter this token when configuring the webhook in Facebook.';
$lang['fb_leads_webhook_instructions'] = 'Copy these values to your Meta App → Webhooks → Configure. Subscribe to "Page" → "leadgen" field.';
$lang['fb_leads_copied_to_clipboard'] = 'Copied to clipboard!';

// Lead settings
$lang['fb_leads_lead_settings'] = 'Lead Settings';
$lang['fb_leads_default_assigned'] = 'Default Assigned Staff';
$lang['fb_leads_default_source'] = 'Default Source';
$lang['fb_leads_default_status'] = 'Default Status';
$lang['fb_leads_no_assignment'] = 'No Assignment';
$lang['fb_leads_select_source'] = 'Select Source';
$lang['fb_leads_select_status'] = 'Select Status';
$lang['fb_leads_default_assigned_help'] = 'Staff member to be assigned to new leads automatically.';
$lang['fb_leads_enable_duplicate_detection'] = 'Enable duplicate detection';
$lang['fb_leads_enable_notifications'] = 'Send email notifications for new leads';
$lang['fb_leads_default_lead_settings'] = 'Default Lead Settings';
$lang['fb_leads_quick_settings'] = 'Quick Settings';
$lang['fb_leads_leave_blank_unchanged'] = 'Leave blank to keep unchanged';

// Facebook pages
$lang['fb_leads_facebook_pages'] = 'Connected Pages';
$lang['fb_leads_pages_description'] = 'Connect your Facebook account to fetch and subscribe to pages for Facebook & Instagram lead monitoring.';
$lang['fb_leads_connect_facebook'] = 'Connect with Facebook';
$lang['fb_leads_refresh_pages'] = 'Refresh Pages';
$lang['fb_leads_connecting'] = 'Connecting...';
$lang['fb_leads_pages_fetched'] = 'Pages fetched successfully!';
$lang['fb_leads_no_pages_yet'] = 'No pages connected yet. Click "Connect with Facebook" to get started.';
$lang['fb_leads_no_pages_found'] = 'No pages found for your account.';
$lang['fb_leads_status'] = 'Status';
$lang['fb_leads_leads_received'] = 'Leads Received';
$lang['fb_leads_monitoring'] = 'Monitoring';
$lang['fb_leads_not_monitoring'] = 'Not Monitoring';
$lang['fb_leads_subscribe'] = 'Monitor';
$lang['fb_leads_unsubscribe'] = 'Stop Monitoring';
$lang['fb_leads_page_subscribed'] = 'Page is now being monitored for leads!';
$lang['fb_leads_page_unsubscribed'] = 'Page monitoring has been stopped.';
$lang['fb_leads_page_assigned_to'] = 'Assign Leads To';
$lang['fb_leads_use_default'] = '— Use Global Default —';
$lang['fb_leads_page_settings_saved'] = 'Page assignment saved!';
$lang['page_name'] = 'Page Name';
$lang['action'] = 'Action';

// Statistics
$lang['fb_leads_synced_this_month'] = 'Synced This Month';
$lang['fb_leads_failed_this_month'] = 'Failed This Month';
$lang['fb_leads_pending_retry'] = 'Pending Retry';
$lang['fb_leads_total_this_month'] = 'Total This Month';
$lang['fb_leads_successful_syncs'] = 'Successful Syncs';
$lang['fb_leads_failed_syncs'] = 'Failed Syncs';
$lang['fb_leads_skipped_duplicates'] = 'Skipped (Duplicates)';

// Sync history
$lang['fb_leads_sync_history_description'] = 'View the history of all lead syncs from Facebook & Instagram, including successful imports, failures, and skipped duplicates.';
$lang['fb_leads_no_sync_history'] = 'No sync history yet. Leads will appear here once they start coming in from Facebook or Instagram.';
$lang['fb_leads_datetime'] = 'Date/Time';
$lang['fb_leads_facebook_id'] = 'Meta Lead ID';
$lang['fb_leads_perfex_lead'] = 'ZAM Lead';
$lang['fb_leads_message'] = 'Message';
$lang['fb_leads_actions'] = 'Actions';
$lang['fb_leads_sync_details'] = 'Sync Details';
$lang['fb_leads_last_sync'] = 'Last sync';
$lang['fb_leads_filter_status'] = 'Filter by status';
$lang['fb_leads_all_statuses'] = 'All Statuses';
$lang['fb_leads_status_success'] = 'Success';
$lang['fb_leads_status_failed'] = 'Failed';
$lang['fb_leads_status_skipped'] = 'Skipped';
$lang['fb_leads_date_from'] = 'From';
$lang['fb_leads_date_to'] = 'To';
$lang['fb_leads_apply_filter'] = 'Apply Filter';
$lang['fb_leads_clear_filter'] = 'Clear';
$lang['fb_leads_pending_items_notice'] = 'You have items in the retry queue!';
$lang['fb_leads_pending_items_description'] = 'Some leads failed to sync and are waiting to be retried. Click the button to process them now.';
$lang['fb_leads_process_retry_queue'] = 'Process Retry Queue';
$lang['fb_leads_processing'] = 'Processing...';
$lang['fb_leads_retry_results'] = 'Processed %d items: %d succeeded, %d failed';

// Field mapping
$lang['fb_leads_field_mapping_description'] = 'Configure how Facebook & Instagram Lead form fields map to ZAM ERP lead fields.';
$lang['fb_leads_default_mappings_info'] = 'Default Field Mappings';
$lang['fb_leads_default_mappings_description'] = 'The module automatically maps common Facebook & Instagram Lead form fields to ZAM ERP fields. You can add custom mappings below for additional fields.';
$lang['fb_leads_facebook_standard_fields'] = 'Meta Standard Fields';
$lang['fb_leads_perfex_lead_fields'] = 'ZAM ERP Lead Fields';
$lang['fb_leads_facebook_field'] = 'Meta Lead Field';
$lang['fb_leads_maps_to'] = 'Maps To';
$lang['fb_leads_field_name'] = 'Field Name';
$lang['fb_leads_field_type'] = 'Type';
$lang['fb_leads_custom_field_mappings'] = 'Custom Field Mappings';
$lang['fb_leads_add_mapping'] = 'Add Mapping';
$lang['fb_leads_custom_mapping_description'] = 'Map custom Facebook & Instagram form fields to ZAM ERP fields.';
$lang['fb_leads_no_custom_mappings'] = 'No custom mappings configured. Click "Add Mapping" to create one.';
$lang['fb_leads_select_field'] = 'Select Field';
$lang['fb_leads_standard_fields'] = 'Standard Fields';
$lang['fb_leads_custom_fields'] = 'Custom Fields';
$lang['fb_leads_save_mappings'] = 'Save Mappings';
$lang['fb_leads_mappings_saved'] = 'Field mappings saved successfully!';
$lang['fb_leads_your_custom_fields'] = 'Your ZAM ERP Custom Fields';
$lang['fb_leads_custom_fields_info'] = 'These are the custom fields you have created for leads in ZAM ERP. Use the slug when naming your Facebook form fields for automatic mapping.';
$lang['fb_leads_slug'] = 'Slug';
$lang['fb_leads_custom_field_tip'] = 'Tip: Name your Facebook/Instagram form fields using the slug (e.g., "leads_budget") and they will be automatically mapped to the custom field.';

// Settings tab
$lang['fb_leads_module_configured'] = 'Module is configured and ready to receive leads from Facebook & Instagram.';
$lang['fb_leads_module_needs_config'] = 'Module needs configuration. Click the button to set it up.';
$lang['fb_leads_open_full_settings'] = 'Open Full Settings';
$lang['fb_leads_full_settings'] = 'Full Settings';

// Help and documentation
$lang['fb_leads_need_help'] = 'Need Help?';
$lang['fb_leads_documentation_text'] = 'Check our documentation for detailed setup instructions and troubleshooting.';
$lang['fb_leads_view_documentation'] = 'View Documentation';
$lang['fb_leads_contact_support'] = 'Contact Support';

// Notifications
$lang['fb_leads_notification_subject'] = 'New Lead from Meta: %s';
$lang['fb_leads_notification_body'] = 'A new lead has been imported from Facebook/Instagram Lead Ads.

Name: %s
Email: %s
Phone: %s
Company: %s

View Lead: %s';

// Errors
$lang['fb_leads_error_credentials_required'] = 'App ID and App Secret are required.';
$lang['fb_leads_error_token_required'] = 'Access token is required.';
$lang['fb_leads_error_page_not_found'] = 'Page not found or missing access token.';
$lang['fb_leads_error_save_failed'] = 'Failed to save settings.';

// Legacy compatibility (keeping old strings for backward compatibility)
$lang['app_id'] = 'Meta Application ID';
$lang['app_secret'] = 'Meta Application Secret';
$lang['app_settings'] = 'Meta Application Settings';
$lang['verify_token'] = 'Webhook Verify Token';
$lang['fetch_facebook_pages'] = 'Fetch Facebook Pages';
$lang['fbleadssubscribe'] = 'Monitor Page Leads';
$lang['fbleadsunsubscribe'] = 'Unmonitor';
$lang['leadsmodule_settings'] = 'Module Settings';
$lang['fetch_settings'] = 'Fetch/relist pages';
$lang['newleads_settings'] = 'Select default values for imported leads:';
$lang['webhook_callback_url'] = 'Your unique webhook callback URL is:';
$lang['no_page_subscribed_yet'] = 'No page subscribed yet.';
$lang['status'] = 'Status';

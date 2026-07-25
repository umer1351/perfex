<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CRM Chat Module Settings
 */

// Get all settings values
$chat_enabled = get_option('pusher_chat_enabled');
$chat_client_enabled = get_option('chat_client_enabled');
$chat_staff_can_access_clients_val = get_option('chat_staff_can_access_clients');
$chat_staff_can_access_clients = ($chat_staff_can_access_clients_val !== '' && $chat_staff_can_access_clients_val !== false) ? $chat_staff_can_access_clients_val : '1';
$chat_members_can_create_groups = get_option('chat_members_can_create_groups');
$allow_to_convert_tickets = get_option('chat_allow_staff_to_create_tickets');
$notification_option = get_option('chat_desktop_messages_notifications');
$floating_notifications_val = get_option('chat_floating_notifications_enabled');
$floating_notifications_enabled = ($floating_notifications_val !== '' && $floating_notifications_val !== false) ? $floating_notifications_val : '1';
$toggled_chat_val = get_option('prchat_toggled_chat_disabled');
$toggled_chat_enabled = ($toggled_chat_val !== '' && $toggled_chat_val !== false) ? $toggled_chat_val : '1';

// Client Widget settings - use ?? instead of ?: since '0' is a valid value
$widget_position = get_option('chat_client_widget_position') ?: 'right';
$primary_color = get_option('chat_client_widget_primary_color') ?: '#4f46e5';
$show_logo_val = get_option('chat_client_widget_show_logo');
$show_logo = ($show_logo_val !== '' && $show_logo_val !== false) ? $show_logo_val : '1';
$show_names_val = get_option('chat_client_widget_show_staff_names');
$show_names = ($show_names_val !== '' && $show_names_val !== false) ? $show_names_val : '1';
$show_roles_val = get_option('chat_client_widget_show_staff_roles');
$show_roles = ($show_roles_val !== '' && $show_roles_val !== false) ? $show_roles_val : '1';
$staff_visibility = get_option('chat_client_staff_visibility') ?: 'assigned_and_responded';

// Call settings - use proper null checks since '0' is valid
$calls_clients_val = get_option('chat_client_calls_enabled');
$calls_enabled_clients = ($calls_clients_val !== '' && $calls_clients_val !== false) ? $calls_clients_val : '0';
$calls_staff_val = get_option('chat_staff_calls_enabled');
$calls_enabled_staff = ($calls_staff_val !== '' && $calls_staff_val !== false) ? $calls_staff_val : '1';
$video_calls_val = get_option('chat_calls_video_enabled');
$video_calls_enabled = ($video_calls_val !== '' && $video_calls_val !== false) ? $video_calls_val : '1';
?>

<!-- Settings Tabs -->
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#chat_general" aria-controls="chat_general" role="tab" data-toggle="tab">
                    <i class="fa fa-cog"></i> <?php echo _l('chat_tab_general'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_widget" aria-controls="chat_widget" role="tab" data-toggle="tab">
                    <i class="fa fa-paint-brush"></i> <?php echo _l('chat_tab_widget'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_calls" aria-controls="chat_calls" role="tab" data-toggle="tab">
                    <i class="fa fa-phone"></i> <?php echo _l('chat_tab_calls'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_danger" aria-controls="chat_danger" role="tab" data-toggle="tab">
                    <i class="fa fa-exclamation-triangle text-danger"></i> <?php echo _l('chat_tab_danger'); ?>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <!-- General Settings Tab -->
    <div role="tabpanel" class="tab-pane active" id="chat_general">
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_enable_option'); ?></label>
            <p class="text-muted"><?php echo _l('chat_enable_option_help'); ?></p>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="chat_enabled_yes" name="settings[pusher_chat_enabled]" value="1" <?php echo ($chat_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="chat_enabled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="chat_enabled_no" name="settings[pusher_chat_enabled]" value="0" <?php echo ($chat_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="chat_enabled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_client_module_enabled'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="client_enabled_yes" name="settings[chat_client_enabled]" value="1" <?php echo ($chat_client_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="client_enabled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="client_enabled_no" name="settings[chat_client_enabled]" value="0" <?php echo ($chat_client_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="client_enabled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_show_desktop_messages_notifications'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="notif_yes" name="settings[chat_desktop_messages_notifications]" value="1"
                        <?php echo ($notification_option == '1') ? 'checked' : ''; ?>>
                    <label for="notif_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="notif_no" name="settings[chat_desktop_messages_notifications]" value="0"
                        <?php echo ($notification_option != '1') ? 'checked' : ''; ?>>
                    <label for="notif_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>

        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_show_toggled_chat'); ?></label>
            <p class="text-muted"><?php echo _l('chat_show_toggled_chat_desc'); ?></p>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="toggled_yes" name="settings[prchat_toggled_chat_disabled]" value="1" <?php echo ($toggled_chat_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="toggled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="toggled_no" name="settings[prchat_toggled_chat_disabled]" value="0" <?php echo ($toggled_chat_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="toggled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_staff_can_create_groups'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="groups_yes" name="settings[chat_members_can_create_groups]" value="1" <?php echo ($chat_members_can_create_groups == '1') ? 'checked' : ''; ?>>
                    <label for="groups_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="groups_no" name="settings[chat_members_can_create_groups]" value="0" <?php echo ($chat_members_can_create_groups != '1') ? 'checked' : ''; ?>>
                    <label for="groups_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_allow_staff_to_create_tickets'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="tickets_yes" name="settings[chat_allow_staff_to_create_tickets]" value="1"
                        <?php echo ($allow_to_convert_tickets == '1') ? 'checked' : ''; ?>>
                    <label for="tickets_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="tickets_no" name="settings[chat_allow_staff_to_create_tickets]" value="0"
                        <?php echo ($allow_to_convert_tickets != '1') ? 'checked' : ''; ?>>
                    <label for="tickets_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="alert alert-info">
            <a href="<?php echo admin_url('staff'); ?>" target="_blank">
                <i class="fa fa-info-circle"></i> <?php echo _l('chat_permissions_info'); ?>
            </a>
        </div>
    </div>

    <!-- Client Widget Tab -->
    <div role="tabpanel" class="tab-pane" id="chat_widget">
        <p class="text-muted"><?php echo _l('chat_widget_customization_desc'); ?></p>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_widget_position"
                        class="control-label"><?php echo _l('chat_widget_position'); ?></label>
                    <select id="chat_client_widget_position" name="settings[chat_client_widget_position]"
                        class="form-control selectpicker" data-width="100%">
                        <option value="right" <?php echo ($widget_position == 'right') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_widget_position_right'); ?></option>
                        <option value="left" <?php echo ($widget_position == 'left') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_widget_position_left'); ?></option>
                    </select>
                </div>
                <hr />
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_logo'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="logo_yes" name="settings[chat_client_widget_show_logo]" value="1"
                                <?php echo ($show_logo == '1') ? 'checked' : ''; ?>>
                            <label for="logo_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="logo_no" name="settings[chat_client_widget_show_logo]" value="0"
                                <?php echo ($show_logo != '1') ? 'checked' : ''; ?>>
                            <label for="logo_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_widget_primary_color"
                        class="control-label"><?php echo _l('chat_widget_primary_color'); ?></label>
                    <input type="color" id="chat_client_widget_primary_color" class="form-control"
                        name="settings[chat_client_widget_primary_color]"
                        value="<?php echo htmlspecialchars($primary_color); ?>" style="height: 38px; padding: 2px;">
                </div>
                <hr />
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_staff_names'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="names_yes" name="settings[chat_client_widget_show_staff_names]"
                                value="1" <?php echo ($show_names == '1') ? 'checked' : ''; ?>>
                            <label for="names_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="names_no" name="settings[chat_client_widget_show_staff_names]"
                                value="0" <?php echo ($show_names != '1') ? 'checked' : ''; ?>>
                            <label for="names_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-6">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_staff_roles'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="roles_yes" name="settings[chat_client_widget_show_staff_roles]"
                                value="1" <?php echo ($show_roles == '1') ? 'checked' : ''; ?>>
                            <label for="roles_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="roles_no" name="settings[chat_client_widget_show_staff_roles]"
                                value="0" <?php echo ($show_roles != '1') ? 'checked' : ''; ?>>
                            <label for="roles_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo _l('chat_staff_can_access_clients'); ?>
                        <i class="fa fa-info-circle text-info" data-toggle="tooltip" data-placement="right"
                            title="<?php echo htmlspecialchars(_l('chat_staff_can_access_clients_help')); ?>"></i>
                    </label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="staff_access_clients_yes" name="settings[chat_staff_can_access_clients]"
                                value="1" <?php echo ($chat_staff_can_access_clients == '1') ? 'checked' : ''; ?>>
                            <label for="staff_access_clients_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="staff_access_clients_no" name="settings[chat_staff_can_access_clients]"
                                value="0" <?php echo ($chat_staff_can_access_clients != '1') ? 'checked' : ''; ?>>
                            <label for="staff_access_clients_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_staff_visibility"
                        class="control-label">
                        <?php echo _l('chat_staff_visibility'); ?>
                        <i class="fa fa-info-circle text-info" data-toggle="tooltip" data-placement="right"
                            title="<?php echo htmlspecialchars(_l('chat_staff_visibility_desc')); ?>"></i>
                    </label>
                    <select id="chat_client_staff_visibility" name="settings[chat_client_staff_visibility]"
                        class="form-control selectpicker" data-width="100%">
                        <option value="assigned_and_responded" <?php echo ($staff_visibility == 'assigned_and_responded' || $staff_visibility == 'assigned_only') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_staff_visibility_assigned_and_responded'); ?></option>
                        <option value="all_staff" <?php echo ($staff_visibility == 'all_staff') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_staff_visibility_all_staff'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="alert alert-info mtop10" style="font-size: 13px; line-height: 1.7;">
            <?php echo _l('chat_client_settings_explanation'); ?>
        </div>
    </div>

    <!-- Calls Settings Tab -->
    <div role="tabpanel" class="tab-pane" id="chat_calls">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <?php echo _l('chat_calls_webrtc_info'); ?>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_clients'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_clients_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_clients_yes" name="settings[chat_client_calls_enabled]"
                                value="1" <?php echo ($calls_enabled_clients == '1') ? 'checked' : ''; ?>>
                            <label for="calls_clients_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_clients_no" name="settings[chat_client_calls_enabled]"
                                value="0" <?php echo ($calls_enabled_clients != '1') ? 'checked' : ''; ?>>
                            <label for="calls_clients_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_staff'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_staff_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_staff_yes" name="settings[chat_staff_calls_enabled]" value="1"
                                <?php echo ($calls_enabled_staff == '1') ? 'checked' : ''; ?>>
                            <label for="calls_staff_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_staff_no" name="settings[chat_staff_calls_enabled]" value="0"
                                <?php echo ($calls_enabled_staff != '1') ? 'checked' : ''; ?>>
                            <label for="calls_staff_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_video'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_video_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="video_yes" name="settings[chat_calls_video_enabled]" value="1" <?php echo ($video_calls_enabled == '1') ? 'checked' : ''; ?>>
                            <label for="video_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="video_no" name="settings[chat_calls_video_enabled]" value="0" <?php echo ($video_calls_enabled != '1') ? 'checked' : ''; ?>>
                            <label for="video_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone Tab -->
    <div role="tabpanel" class="tab-pane" id="chat_danger">
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> <strong><?php echo _l('chat_danger_zone_warning'); ?></strong>
        </div>

        <div class="row">
            <!-- Legacy Chat Data -->
            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo _l('chat_danger_delete_old_conversations'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_delete_old_desc'); ?></small></p>

                        <!-- Staff History -->
                        <div class="mbot15"
                            style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-users text-muted"></i>
                                        <?php echo _l("chat_purge_staff_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeStaffHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_staff'); ?>
                            </p>
                        </div>

                        <!-- Client History -->
                        <div class="mbot15"
                            style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-user text-muted"></i>
                                        <?php echo _l("chat_purge_clients_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeClientsHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_clients'); ?>
                            </p>
                        </div>

                        <!-- Group History -->
                        <div style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-comments text-muted"></i>
                                        <?php echo _l("chat_purge_groups_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeGroupsHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_groups'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Chatbot & Full Wipe -->
            <div class="col-md-6">
                <!-- AI Chatbot Data -->
                <div class="panel panel-danger mbot20">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo _l('chat_danger_ai_chatbot_data'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_ai_chatbot_desc'); ?></small></p>
                        <div style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-robot text-muted"></i>
                                        <?php echo _l('chat_danger_purge_chatbot'); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button"
                                        onclick="purgeChatbotConversations()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_chatbot'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Complete Wipe -->
                <div class="panel panel-danger">
                    <div class="panel-heading" style="background-color: #f2dede;">
                        <h4 class="panel-title" style="color: #a94442;"><i class="fa fa-bomb"></i>
                            <?php echo _l("chat_delete_all_data"); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_full_reset_desc'); ?></small></p>
                        <div style="padding: 10px; background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px; color: #a94442;"><i
                                            class="fa fa-exclamation-circle"></i>
                                        <?php echo _l("chat_purge_everything"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeAllHistory()">
                                        <i class="fa fa-bomb"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_all'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const settingsGroupLink = document.querySelector('.settings-group-perfex_chat_settings a');
        settingsGroupLink?.classList.remove('tw-text-neutral-800');
        settingsGroupLink?.classList.add('tw-text-black');

        if (typeof jQuery !== 'undefined') {
            jQuery('#chat_widget [data-toggle="tooltip"], #chat_general [data-toggle="tooltip"]').tooltip({container: 'body', trigger: 'hover focus'});
        }
    });
</script>

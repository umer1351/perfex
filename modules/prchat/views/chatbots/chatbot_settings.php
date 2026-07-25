<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-mb-4">
                            <i class="fa fa-robot tw-mr-2"></i> <?= _l('chatbot_ai_chatbot') ?>
                        </h4>

                        <?php
                        $resolvedApiKey = chatbot_resolve_openai_key();
                        $hasApiKey = !empty($resolvedApiKey);
                        ?>
                        <?php if (!$hasApiKey): ?>
                            <div class="alert alert-warning tw-flex tw-items-center tw-gap-3 tw-mb-4" style="border-left:4px solid #d97706;">
                                <i class="fa fa-exclamation-triangle" style="font-size:20px; color:#d97706;"></i>
                                <div>
                                    <strong><?= _l('chatbot_no_api_key_title') ?></strong><br>
                                    <span class="tw-text-sm"><?= _l('chatbot_no_api_key_message') ?></span>
                                </div>
                                <a href="#api-keys" class="btn btn-warning btn-sm tw-ml-auto" data-toggle="tab" onclick="$('.nav-tabs a[href=\'#api-keys\']').tab('show');">
                                    <i class="fa fa-key"></i> <?= _l('chatbot_configure_api_key') ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($chatbot->allowed_domains)): ?>
                            <div id="domain-security-warning" class="alert alert-warning tw-mb-4" style="border-left:4px solid #3b82f6; background:#eff6ff; border-color:#93c5fd;">
                                <div style="display:flex; align-items:start; gap:12px;">
                                    <i class="fa fa-info-circle" style="color:#2563eb; font-size:20px; margin-top:2px;"></i>
                                    <div>
                                        <p style="margin:0; color:#1e40af;">
                                            <?= sprintf(_l('chatbot_no_domains_info'), '<a href="#embed" onclick="$(\'.nav-tabs a[href=\\\'#embed\\\']\').tab(\'show\');" style="color:#1d4ed8; text-decoration:underline; font-weight:600;">' . _l('chatbot_no_domains_info_link') . '</a>') ?> </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">
                                    <i class="fa fa-cog"></i> <?= _l('chatbot_settings_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#widget" aria-controls="widget" role="tab" data-toggle="tab">
                                    <i class="fa fa-paint-brush"></i> <?= _l('chatbot_widget_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#training" aria-controls="training" role="tab" data-toggle="tab">
                                    <i class="fa fa-graduation-cap"></i> <?= _l('chatbot_training_data_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#embed" aria-controls="embed" role="tab" data-toggle="tab">
                                    <i class="fa fa-code"></i> <?= _l('chatbot_embed_code_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#api-keys" aria-controls="api-keys" role="tab" data-toggle="tab">
                                    <i class="fa fa-key"></i> <?= _l('chatbot_api_keys_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#canned-responses" aria-controls="canned-responses" role="tab" data-toggle="tab">
                                    <i class="fa fa-bolt"></i> <?= _l('chatbot_canned_responses_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tags" aria-controls="tags" role="tab" data-toggle="tab">
                                    <i class="fa fa-tags"></i> <?= _l('chatbot_tags_tab') ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#leads" aria-controls="leads" role="tab" data-toggle="tab">
                                    <i class="fa fa-user-plus"></i> <?= str_replace(':', '', _l('chatbot_leads_title')) ?>
                                </a>
                            </li>
                        </ul>

                        <?php
                        $appearance = $chatbot->appearance ?? [];
                        $leadFields = $chatbot->lead_fields ?? [];
                        ?>

                        <!-- Tab Content -->
                        <div class="tab-content tw-mt-4">

                            <!-- Settings Tab -->
                            <div role="tabpanel" class="tab-pane active" id="settings">
                                <?php echo form_open(admin_url('prchat/Chatbot_Admin/save'), ['id' => 'chatbot-settings-form']); ?>
                                <input type="hidden" name="id" value="<?= $chatbot->id ?>">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel_s chatbot-general-panel tw-border tw-border-solid tw-rounded" style="border-color:#e2e8f0; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                            <div class="panel-body" style="padding: 0;">
                                                <div class="tw-flex tw-items-center tw-gap-4 tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb; font-size:12px;"><i class="fa fa-cog"></i></div>
                                                        <strong><?= _l('chatbot_general') ?></strong>
                                                    </div>
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#bbf7d0; color:#16a34a; font-size:12px;"><i class="fa fa-cogs"></i></div>
                                                        <strong><?= _l('chatbot_ai_configuration') ?></strong>
                                                    </div>
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#fde68a; color:#d97706; font-size:12px;"><i class="fa fa-lock"></i></div>
                                                        <strong><?= _l('chatbot_gdpr_section') ?></strong>
                                                    </div>
                                                </div>
                                                <div style="padding: 20px;">
                                                    <h5 class="tw-font-semibold tw-mb-3" style="margin-top: 0;"><?= _l('chatbot_general') ?></h5>

                                                    <div class="form-group">
                                                        <label><?= _l('chatbot_name') ?></label>
                                                        <input type="text" name="name" class="form-control" required
                                                            value="<?= htmlspecialchars($chatbot->name ?? '') ?>">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>
                                                            <input type="checkbox" name="enabled" value="1"
                                                                <?= ($chatbot->enabled ?? 1) ? 'checked' : '' ?>>
                                                            <?= _l('chatbot_enabled') ?>
                                                        </label>
                                                    </div>

                                                    <div class="form-group">
                                                        <label><?= _l('chatbot_widget_language') ?>
                                                            <i class="fa fa-question-circle text-muted" data-toggle="tooltip" data-placement="top"
                                                                title="<?= htmlspecialchars(_l('chatbot_widget_language_help')) ?>"></i>
                                                        </label>
                                                        <?php
                                                        $widgetLang = $appearance['widget_language'] ?? get_option('active_language');
                                                        $availableLangs = [
                                                            'english' => 'English',
                                                            'dutch' => 'Dutch',
                                                            'french' => 'French',
                                                            'german' => 'German',
                                                            'italian' => 'Italian',
                                                            'spanish' => 'Spanish',
                                                            'portuguese_br' => 'Portuguese (BR)',
                                                            'turkish' => 'Turkish',
                                                            'ukrainian' => 'Ukrainian',
                                                        ];
                                                        ?>
                                                        <select name="widget_language" class="form-control">
                                                            <?php foreach ($availableLangs as $langKey => $langName): ?>
                                                                <option value="<?= $langKey ?>" <?= $widgetLang === $langKey ? 'selected' : '' ?>><?= $langName ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <p class="help-block"><?= _l('chatbot_widget_language_help') ?></p>
                                                    </div>

                                                    <hr style="margin: 20px 0; border-color: #e4e7ea;">

                                                    <h5 class="tw-font-semibold tw-mb-3"><?= _l('chatbot_ai_configuration') ?></h5>

                                                    <div class="form-group">
                                                        <label><?= _l('chatbot_model') ?>
                                                            <i class="fa fa-question-circle text-muted" data-toggle="tooltip" data-placement="top"
                                                                title="<?= htmlspecialchars(_l('chatbot_model_recommended') . ' gpt-4o-mini for cost-efficient chat; gpt-4o or gpt-4.1 for quality; gpt-5 family for latest.') ?>"></i>
                                                        </label>
                                                        <select name="ai_model" class="form-control">
                                                            <optgroup label="GPT-4o (fast, flexible)">
                                                                <option value="gpt-4o-mini" <?= ($chatbot->ai_model ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : '' ?>>gpt-4o-mini (recommended, cost-efficient)</option>
                                                                <option value="gpt-4o" <?= ($chatbot->ai_model ?? '') === 'gpt-4o' ? 'selected' : '' ?>>gpt-4o</option>
                                                            </optgroup>
                                                            <optgroup label="GPT-4.1 (smart non-reasoning)">
                                                                <option value="gpt-4.1-nano" <?= ($chatbot->ai_model ?? '') === 'gpt-4.1-nano' ? 'selected' : '' ?>>gpt-4.1-nano</option>
                                                                <option value="gpt-4.1-mini" <?= ($chatbot->ai_model ?? '') === 'gpt-4.1-mini' ? 'selected' : '' ?>>gpt-4.1-mini</option>
                                                                <option value="gpt-4.1" <?= ($chatbot->ai_model ?? '') === 'gpt-4.1' ? 'selected' : '' ?>>gpt-4.1</option>
                                                            </optgroup>
                                                            <optgroup label="GPT-5 (latest)">
                                                                <option value="gpt-5-nano" <?= ($chatbot->ai_model ?? '') === 'gpt-5-nano' ? 'selected' : '' ?>>gpt-5-nano</option>
                                                                <option value="gpt-5-mini" <?= ($chatbot->ai_model ?? '') === 'gpt-5-mini' ? 'selected' : '' ?>>gpt-5-mini</option>
                                                                <option value="gpt-5" <?= ($chatbot->ai_model ?? '') === 'gpt-5' ? 'selected' : '' ?>>gpt-5</option>
                                                                <option value="gpt-5.1" <?= ($chatbot->ai_model ?? '') === 'gpt-5.1' ? 'selected' : '' ?>>gpt-5.1</option>
                                                                <option value="gpt-5.2" <?= ($chatbot->ai_model ?? '') === 'gpt-5.2' ? 'selected' : '' ?>>gpt-5.2</option>
                                                            </optgroup>
                                                            <optgroup label="Other">
                                                                <option value="gpt-4-turbo" <?= ($chatbot->ai_model ?? '') === 'gpt-4-turbo' ? 'selected' : '' ?>>gpt-4-turbo</option>
                                                                <option value="gpt-3.5-turbo" <?= ($chatbot->ai_model ?? '') === 'gpt-3.5-turbo' ? 'selected' : '' ?>>gpt-3.5-turbo (legacy)</option>
                                                            </optgroup>
                                                        </select>
                                                        <p class="help-block">
                                                            <strong><?= _l('chatbot_model_recommended') ?></strong> gpt-4o-mini for cost-efficient chat; gpt-4o or gpt-4.1 for quality; gpt-5 family for latest. See <a href="https://platform.openai.com/docs/models" target="_blank" rel="noopener">OpenAI Models</a>.
                                                        </p>
                                                    </div>

                                                    <div class="form-group">
                                                        <label><?= _l('chatbot_system_prompt') ?>
                                                            <i class="fa fa-question-circle text-muted" data-toggle="tooltip" data-placement="top" data-html="true"
                                                                title="<?= htmlspecialchars(_l('chatbot_system_prompt_help') . ' Company name, address, phone, and website from CRM are always included automatically; this field adds additional instructions.') ?>"></i>
                                                        </label>
                                                        <textarea name="system_prompt" class="form-control" rows="4"><?= htmlspecialchars($chatbot->system_prompt ?? '') ?></textarea>
                                                        <p class="help-block">
                                                            <?= _l('chatbot_system_prompt_help') ?> Company name, address, phone, and website from CRM are always included automatically; this field adds <strong>additional instructions</strong>.<br>
                                                            <strong>Examples:</strong><br>
                                                            • "You are a helpful customer support assistant for [Company Name]. Be friendly, professional, and concise."<br>
                                                            • "You help customers with product questions. Always recommend contacting support for billing issues."<br>
                                                            • "You are a technical support bot. Ask clarifying questions before providing solutions."
                                                        </p>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label><?= _l('chatbot_temperature') ?>
                                                                    <i class="fa fa-question-circle text-muted" data-toggle="tooltip"
                                                                        data-placement="top" title="<?= _l('chatbot_temperature_help') ?>"></i>
                                                                </label>
                                                                <input type="number" name="temperature" class="form-control"
                                                                    min="0" max="1" step="0.1" value="<?= $chatbot->temperature ?? 0.7 ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label><?= _l('chatbot_max_tokens') ?>
                                                                    <i class="fa fa-question-circle text-muted" data-toggle="tooltip"
                                                                        data-placement="top" title="<?= _l('chatbot_max_tokens_help') ?>"></i>
                                                                </label>
                                                                <input type="number" name="max_output_tokens" class="form-control"
                                                                    value="<?= $chatbot->max_output_tokens ?? 500 ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label><?= _l('chatbot_context_window') ?>
                                                            <i class="fa fa-question-circle text-muted" data-toggle="tooltip"
                                                                data-html="true"
                                                                title="<strong>5</strong> = <?= _l('chatbot_context_5'); ?><br><strong>10</strong> = <?= _l('chatbot_context_10'); ?><br><strong>20</strong> = <?= _l('chatbot_context_20'); ?><br><strong>50</strong> = <?= _l('chatbot_context_50'); ?><br><br><em><?= _l('chatbot_context_note'); ?></em>"></i>
                                                        </label>
                                                        <input type="number" name="context_window" class="form-control"
                                                            min="3" max="50" value="<?= $chatbot->context_window ?? 10 ?>">
                                                        <p class="help-block"><?= _l('chatbot_context_window_help') ?></p>
                                                    </div>

                                                    <hr style="margin: 20px 0; border-color: #e4e7ea;">

                                                    <h5 class="tw-font-semibold tw-mb-3"><?= _l('chatbot_gdpr_section') ?></h5>

                                                    <?php if (function_exists('is_gdpr') && is_gdpr()): ?>

                                                        <div class="form-group">
                                                            <label>
                                                                <input type="checkbox" name="gdpr_enabled" value="1"
                                                                    <?= ($appearance['gdpr_enabled'] ?? 0) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_gdpr_enabled') ?>
                                                            </label>
                                                            <p class="help-block"><?= _l('chatbot_gdpr_enabled_help') ?></p>
                                                        </div>

                                                        <?php if (!empty($consentPurposes)): ?>
                                                            <div class="form-group">
                                                                <label><?= _l('chatbot_gdpr_consent_purpose') ?></label>
                                                                <select name="gdpr_consent_purpose_id" class="form-control">
                                                                    <option value=""><?= _l('chatbot_gdpr_no_purpose') ?></option>
                                                                    <?php foreach ($consentPurposes as $purpose): ?>
                                                                        <option value="<?= $purpose['id'] ?>"
                                                                            <?= ($appearance['gdpr_consent_purpose_id'] ?? '') == $purpose['id'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($purpose['name']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <p class="help-block"><?= _l('chatbot_gdpr_purpose_help') ?></p>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-info" style="font-size: 13px;">
                                                                <i class="fa fa-info-circle"></i>
                                                                <?= _l('chatbot_gdpr_no_purposes_notice') ?>
                                                                <strong><a href="<?= admin_url('gdpr') ?>" target="_blank"><?= _l('chatbot_gdpr_setup_link') ?></a></strong>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_gdpr_consent_text') ?>
                                                                <i class="fa fa-question-circle text-muted" data-toggle="tooltip" title="<?= _l('chatbot_gdpr_consent_text_help') ?>"></i>
                                                            </label>
                                                            <input type="text" name="gdpr_consent_text" class="form-control"
                                                                value="<?= htmlspecialchars($appearance['gdpr_consent_text'] ?? '') ?>"
                                                                placeholder="<?= _l('chatbot_gdpr_default_consent') ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_gdpr_privacy_url') ?></label>
                                                            <?php $autoUrl = function_exists('privacy_policy_url') ? privacy_policy_url() : ''; ?>
                                                            <input type="text" name="gdpr_privacy_url" class="form-control"
                                                                value="<?= htmlspecialchars($appearance['gdpr_privacy_url'] ?? '') ?>"
                                                                placeholder="<?= htmlspecialchars($autoUrl) ?>">
                                                            <p class="help-block">
                                                                <?= _l('chatbot_gdpr_privacy_url_auto') ?>
                                                                <a href="<?= htmlspecialchars($autoUrl) ?>" target="_blank"><?= htmlspecialchars($autoUrl) ?></a>
                                                            </p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning" style="font-size: 13px;">
                                                            <i class="fa fa-exclamation-triangle"></i>
                                                            <?= _l('chatbot_gdpr_not_enabled') ?>
                                                            <a href="<?= admin_url('gdpr') ?>" target="_blank"><?= _l('chatbot_gdpr_enable_link') ?></a>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> <?= _l('chatbot_save_settings') ?>
                                </button>
                                </form>
                            </div>

                            <!-- Widget Tab -->
                            <div role="tabpanel" class="tab-pane" id="widget">
                                <div class="row">
                                    <div class="col-md-6" style="padding-right: 0px;">
                                        <div class="panel_s chatbot-widget-top-panel tw-border tw-border-solid tw-rounded" style="border-color:#e2e8f0; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 0;">
                                            <div class="panel-body" style="padding: 0;">
                                                <div class="tw-flex tw-items-center tw-gap-4 tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb; font-size:12px;"><i class="fa fa-paint-brush"></i></div>
                                                        <strong><?= _l('chatbot_appearance') ?></strong>
                                                    </div>
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#e9d5ff; color:#7c3aed; font-size:12px;"><i class="fa fa-comments"></i></div>
                                                        <strong><?= _l('chatbot_widget_messages') ?></strong>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin: 0;">
                                                    <div class="col-md-12" style="padding: 20px 20px 0px 20px;">
                                                        <h5 class="tw-font-semibold tw-mb-3" style="margin-top: 0; color: #374151;"><?= _l('chatbot_appearance') ?></h5>

                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_primary_color') ?></label>
                                                                    <input form="chatbot-settings-form" type="color" name="primary_color" class="form-control" style="height: 38px; padding: 4px;"
                                                                        value="<?= $appearance['primary_color'] ?? '#007bff' ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_header_bg_color') ?></label>
                                                                    <input form="chatbot-settings-form" type="color" name="header_bg_color" class="form-control" style="height: 38px; padding: 4px;"
                                                                        value="<?= $appearance['header_bg_color'] ?? ($appearance['primary_color'] ?? '#007bff') ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_position') ?></label>
                                                                    <select form="chatbot-settings-form" name="position" class="form-control">
                                                                        <option value="bottom-right" <?= ($appearance['position'] ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' ?>><?= _l('chatbot_bottom_right') ?></option>
                                                                        <option value="bottom-left" <?= ($appearance['position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>><?= _l('chatbot_bottom_left') ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_distance_bottom') ?></label>
                                                                    <div class="input-group">
                                                                        <input form="chatbot-settings-form" type="number" name="distance_from_bottom" class="form-control" min="0" max="200"
                                                                            value="<?= $appearance['distance_from_bottom'] ?? 20 ?>">
                                                                        <span class="input-group-addon">px</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_distance_side') ?></label>
                                                                    <div class="input-group">
                                                                        <input form="chatbot-settings-form" type="number" name="distance_from_side" class="form-control" min="0" max="200"
                                                                            value="<?= $appearance['distance_from_side'] ?? 20 ?>">
                                                                        <span class="input-group-addon">px</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_icon_type') ?></label>
                                                                    <select form="chatbot-settings-form" name="icon_type" class="form-control">
                                                                        <option value="chat" <?= ($appearance['icon_type'] ?? 'chat') === 'chat' ? 'selected' : '' ?>><?= _l('chatbot_icon_chat') ?></option>
                                                                        <option value="message" <?= ($appearance['icon_type'] ?? '') === 'message' ? 'selected' : '' ?>><?= _l('chatbot_icon_message') ?></option>
                                                                        <option value="help" <?= ($appearance['icon_type'] ?? '') === 'help' ? 'selected' : '' ?>><?= _l('chatbot_icon_help') ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr style="border-color: #e4e7ea;margin: 0 0 20px 0;">
                                                    </div>
                                                    <div class="col-md-12" style="padding: 0px 20px;">
                                                        <h5 class="tw-font-semibold tw-mb-3" style="margin-top: 0; color: #374151;"><?= _l('chatbot_widget_messages') ?></h5>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_welcome_message') ?></label>
                                                            <input form="chatbot-settings-form" type="text" name="welcome_message" class="form-control"
                                                                value="<?= htmlspecialchars($appearance['welcome_message'] ?? '') ?>"
                                                                placeholder="<?= htmlspecialchars(_l('chatbot_widget_welcome')) ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_display_name_mode') ?></label>
                                                            <select form="chatbot-settings-form" name="display_name_mode" class="form-control">
                                                                <option value="chatbot_only" <?= ($appearance['display_name_mode'] ?? 'chatbot_only') === 'chatbot_only' ? 'selected' : '' ?>><?= _l('chatbot_display_name_chatbot_only') ?></option>
                                                                <option value="company_and_chatbot" <?= ($appearance['display_name_mode'] ?? '') === 'company_and_chatbot' ? 'selected' : '' ?>><?= _l('chatbot_display_name_company_and_chatbot') ?></option>
                                                            </select>
                                                            <p class="help-block"><?= _l('chatbot_display_name_mode_help') ?></p>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_input_placeholder') ?></label>
                                                                    <input form="chatbot-settings-form" type="text" name="input_placeholder" class="form-control"
                                                                        value="<?= htmlspecialchars($appearance['input_placeholder'] ?? '') ?>"
                                                                        placeholder="Type your message...">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label><?= _l('chatbot_header_subtitle') ?></label>
                                                                    <input form="chatbot-settings-form" type="text" name="intro_subtitle" class="form-control"
                                                                        value="<?= htmlspecialchars($appearance['intro_subtitle'] ?? '') ?>"
                                                                        placeholder="<?= _l('chatbot_reply_time_placeholder'); ?>">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_agent_avatar') ?></label>
                                                            <input form="chatbot-settings-form" type="text" name="agent_avatar_url" class="form-control"
                                                                value="<?= htmlspecialchars($appearance['agent_avatar_url'] ?? '') ?>"
                                                                placeholder="https://example.com/avatar.png">
                                                            <p class="help-block"><?= _l('chatbot_agent_avatar_help') ?></p>
                                                        </div>
                                                        <hr style="margin: 20px 0; border-color: #e4e7ea;">
                                                        <div class="form-group">
                                                            <div class="panel-group">
                                                                <div class="panel panel-default">
                                                                    <div class="panel-heading" style="background:#f8fafc; cursor:pointer;" data-toggle="collapse" data-target="#thinking-phrases-panel">
                                                                        <h4 class="panel-title" style="display:flex; justify-content:space-between; align-items:center;">
                                                                            <span><?= _l('chatbot_thinking_phrases') ?></span>
                                                                            <i class="fa fa-chevron-up"></i>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="thinking-phrases-panel" class="panel-collapse collapse">
                                                                        <div class="panel-body">
                                                                            <p class="help-block" style="margin-top:0;"><?= _l('chatbot_thinking_phrases_help') ?></p>
                                                                            <?php
                                                                            $thinkingPhrases = (!empty($appearance['thinking_phrases']) && is_array($appearance['thinking_phrases']))
                                                                                ? $appearance['thinking_phrases']
                                                                                : [
                                                                                    _l('chatbot_thinking_1'),
                                                                                    _l('chatbot_thinking_2'),
                                                                                    _l('chatbot_thinking_3'),
                                                                                    _l('chatbot_thinking_4'),
                                                                                    _l('chatbot_thinking_5'),
                                                                                    _l('chatbot_thinking_6'),
                                                                                    _l('chatbot_thinking_7'),
                                                                                ];
                                                                            foreach ($thinkingPhrases as $index => $phrase): ?>
                                                                                <div class="input-group" style="margin-bottom:8px;">
                                                                                    <span class="input-group-addon" style="min-width:35px;"><?= $index + 1 ?></span>
                                                                                    <input form="chatbot-settings-form" type="text" name="thinking_phrases[]" class="form-control"
                                                                                        value="<?= htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8') ?>"
                                                                                        placeholder="<?= htmlspecialchars(_l('chatbot_thinking_phrase_placeholder')) ?>">
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr style="margin: 20px 0; border-color: #e4e7ea;">
                                                        <h5 class="tw-font-semibold tw-mb-3"><?= _l('chatbot_proactive_trigger') ?></h5>
                                                        <div class="form-group">
                                                            <label>
                                                                <input form="chatbot-settings-form" type="checkbox" name="proactive_enabled" value="1"
                                                                    <?= !empty($appearance['proactive_enabled']) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_proactive_enabled') ?>
                                                            </label>
                                                            <p class="help-block"><?= _l('chatbot_proactive_help') ?></p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_proactive_delay_seconds') ?></label>
                                                            <input form="chatbot-settings-form" type="number" name="proactive_delay_seconds" class="form-control" min="0" max="120"
                                                                value="<?= (int)($appearance['proactive_delay_seconds'] ?? 5) ?>">
                                                            <p class="help-block"><?= _l('chatbot_proactive_delay_help'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="padding-left: 0px;">
                                        <div class="col-md-12">
                                            <div class="panel_s chatbot-visitor-panel tw-border tw-border-solid tw-rounded" style="border-color:#e2e8f0; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                <div class="panel-body" style="padding: 0;">
                                                    <div class="tw-flex tw-items-center tw-gap-4 tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                                        <div class="tw-flex tw-items-center tw-gap-2">
                                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb; font-size:12px;"><i class="fa fa-user-plus"></i></div>
                                                            <strong><?= _l('chatbot_lead_capture') ?></strong>
                                                        </div>
                                                        <div class="tw-flex tw-items-center tw-gap-2">
                                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#bbf7d0; color:#16a34a; font-size:12px;"><i class="fa fa-headphones"></i></div>
                                                            <strong><?= _l('chatbot_escalation') ?></strong>
                                                        </div>
                                                        <div class="tw-flex tw-items-center tw-gap-2">
                                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#e9d5ff; color:#7c3aed; font-size:12px;"><i class="fa fa-cogs"></i></div>
                                                            <strong><?= _l('chatbot_behavior') ?></strong>
                                                        </div>
                                                    </div>
                                                    <div style="padding: 20px;">
                                                        <h5 class="tw-font-semibold tw-mb-3" style="margin-top: 0;"><?= _l('chatbot_lead_capture') ?></h5>

                                                        <div class="form-group">
                                                            <label>
                                                                <input form="chatbot-settings-form" type="checkbox" name="capture_leads" value="1" id="capture_leads_toggle"
                                                                    <?= ($chatbot->capture_leads ?? 0) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_enable_lead_capture') ?>
                                                            </label>
                                                            <p class="help-block"><?= _l('chatbot_lead_capture_help') ?></p>
                                                        </div>

                                                        <div id="lead-fields-config" style="<?= ($chatbot->capture_leads ?? 0) ? '' : 'display:none;' ?>">
                                                            <div class="well well-sm" style="background: #f9f9f9;">
                                                                <label class="text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;"><?= _l('chatbot_lead_form_fields') ?></label>

                                                                <div class="form-group" style="margin-bottom: 8px;">
                                                                    <label style="font-weight: normal;">
                                                                        <input type="checkbox" checked disabled>
                                                                        <?= _l('chatbot_lead_field_email') ?>
                                                                        <span class="label label-default" style="font-size: 10px;"><?= _l('chatbot_always_required') ?></span>
                                                                    </label>
                                                                </div>

                                                                <div class="form-group" style="margin-bottom: 8px;">
                                                                    <label style="font-weight: normal;">
                                                                        <input form="chatbot-settings-form" type="checkbox" name="lead_field_name" value="1"
                                                                            <?= ($leadFields['name']['enabled'] ?? false) ? 'checked' : '' ?>>
                                                                        <?= _l('chatbot_lead_field_name') ?>
                                                                    </label>
                                                                    <label style="font-weight: normal; margin-left: 15px;">
                                                                        <input form="chatbot-settings-form" type="checkbox" name="lead_field_name_required" value="1"
                                                                            <?= ($leadFields['name']['required'] ?? false) ? 'checked' : '' ?>>
                                                                        <small><?= _l('chatbot_required') ?></small>
                                                                    </label>
                                                                </div>

                                                                <div class="form-group" style="margin-bottom: 8px;">
                                                                    <label style="font-weight: normal;">
                                                                        <input form="chatbot-settings-form" type="checkbox" name="lead_field_phone" value="1"
                                                                            <?= ($leadFields['phone']['enabled'] ?? false) ? 'checked' : '' ?>>
                                                                        <?= _l('chatbot_lead_field_phone') ?>
                                                                    </label>
                                                                    <label style="font-weight: normal; margin-left: 15px;">
                                                                        <input form="chatbot-settings-form" type="checkbox" name="lead_field_phone_required" value="1"
                                                                            <?= ($leadFields['phone']['required'] ?? false) ? 'checked' : '' ?>>
                                                                        <small><?= _l('chatbot_required') ?></small>
                                                                    </label>
                                                                </div>

                                                                <div class="form-group" style="margin-bottom: 0;">
                                                                    <label><?= _l('chatbot_lead_success_message') ?></label>
                                                                    <input form="chatbot-settings-form" type="text" name="lead_capture_success_message" class="form-control"
                                                                        value="<?= htmlspecialchars($chatbot->lead_capture_success_message ?? '') ?>"
                                                                        placeholder="Thank you! Your info has been saved. How can we help you today?">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr style="margin: 20px 0; border-color: #e4e7ea;">

                                                        <h5 class="tw-font-semibold tw-mb-3"><?= _l('chatbot_escalation') ?></h5>

                                                        <div class="form-group">
                                                            <label>
                                                                <input form="chatbot-settings-form" type="checkbox" name="escalation_enabled" value="1"
                                                                    <?= ($chatbot->escalation_enabled ?? 1) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_allow_escalation') ?>
                                                            </label>
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_escalation_message') ?></label>
                                                            <input form="chatbot-settings-form" type="text" name="escalation_message" class="form-control"
                                                                value="<?= htmlspecialchars($chatbot->escalation_message ?? '') ?>"
                                                                placeholder="<?= _l('chatbot_escalation_message_placeholder') ?>">
                                                        </div>

                                                        <?php
                                                        $escKw = $chatbot->escalation_keywords;
                                                        $escPhrases = !empty($escKw['phrases']) ? implode("\n", $escKw['phrases']) : '';
                                                        $escCoreWords = !empty($escKw['core_words']) ? implode(', ', $escKw['core_words']) : '';
                                                        ?>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_escalation_phrases') ?></label>
                                                            <textarea form="chatbot-settings-form" name="escalation_phrases" class="form-control" rows="5"
                                                                placeholder="<?= _l('chatbot_escalation_phrases_placeholder') ?>"><?= htmlspecialchars($escPhrases) ?></textarea>
                                                            <p class="help-block"><?= _l('chatbot_escalation_phrases_help') ?></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_escalation_core_words') ?></label>
                                                            <input form="chatbot-settings-form" type="text" name="escalation_core_words" class="form-control"
                                                                value="<?= htmlspecialchars($escCoreWords) ?>"
                                                                placeholder="<?= _l('chatbot_escalation_core_words_placeholder') ?>">
                                                            <p class="help-block"><?= _l('chatbot_escalation_core_words_help') ?></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>
                                                                <input form="chatbot-settings-form" type="checkbox" name="csat_enabled" value="1"
                                                                    <?= ($chatbot->csat_enabled ?? 1) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_csat_enabled') ?>
                                                            </label>
                                                            <p class="help-block"><?= _l('chatbot_csat_enabled_help') ?></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>
                                                                <input form="chatbot-settings-form" type="checkbox" name="visitor_file_upload" value="1"
                                                                    <?= !empty($appearance['visitor_file_upload']) ? 'checked' : '' ?>>
                                                                <?= _l('chatbot_visitor_file_upload') ?>
                                                            </label>
                                                            <p class="help-block"><?= _l('chatbot_visitor_file_upload_help') ?></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_max_messages') ?>
                                                                <i class="fa fa-question-circle text-muted" data-toggle="tooltip" title="<?= _l('chatbot_max_messages_help') ?>"></i>
                                                            </label>
                                                            <input form="chatbot-settings-form" type="number" name="max_messages" class="form-control" min="0"
                                                                value="<?= $chatbot->max_messages ?? '' ?>"
                                                                placeholder="0 = <?= _l('chatbot_unlimited') ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label><?= _l('chatbot_auto_close_timeout') ?>
                                                                <i class="fa fa-question-circle text-muted" data-toggle="tooltip" title="<?= _l('chatbot_auto_close_timeout_help') ?>"></i>
                                                            </label>
                                                            <select form="chatbot-settings-form" name="auto_close_timeout" class="form-control selectpicker">
                                                                <option value="0" <?= ($chatbot->auto_close_timeout ?? 30) == 0 ? 'selected' : '' ?>><?= _l('chatbot_never') ?></option>
                                                                <option value="5" <?= ($chatbot->auto_close_timeout ?? 30) == 5 ? 'selected' : '' ?>>5 <?= _l('chatbot_minutes') ?></option>
                                                                <option value="10" <?= ($chatbot->auto_close_timeout ?? 30) == 10 ? 'selected' : '' ?>>10 <?= _l('chatbot_minutes') ?></option>
                                                                <option value="15" <?= ($chatbot->auto_close_timeout ?? 30) == 15 ? 'selected' : '' ?>>15 <?= _l('chatbot_minutes') ?></option>
                                                                <option value="30" <?= ($chatbot->auto_close_timeout ?? 30) == 30 ? 'selected' : '' ?>>30 <?= _l('chatbot_minutes') ?></option>
                                                                <option value="60" <?= ($chatbot->auto_close_timeout ?? 30) == 60 ? 'selected' : '' ?>>60 <?= _l('chatbot_minutes') ?></option>
                                                            </select>
                                                            <p class="help-block"><?= _l('chatbot_auto_close_timeout_note') ?></p>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" form="chatbot-settings-form" name="active_tab" value="widget">
                                <hr>
                                <button type="submit" form="chatbot-settings-form" class="btn btn-primary">
                                    <i class="fa fa-save"></i> <?= _l('chatbot_save_settings') ?>
                                </button>
                            </div>

                            <!-- Training Tab -->
                            <div role="tabpanel" class="tab-pane" id="training">
                                <?php
                                $links = $chatbot->getTrainingLinks();
                                $qas = $chatbot->getTrainingQAs();
                                $files = $chatbot->getTrainingFiles();
                                $totalItems = count($links) + count($qas) + count($files);
                                $trainedCount = 0;
                                $failedCount = 0;
                                foreach ([$qas] as $group) {
                                    foreach ($group as $item) {
                                        if ($item->training_status === 'trained') $trainedCount++;
                                        if ($item->training_status === 'failed') $failedCount++;
                                    }
                                }
                                foreach ([$links, $files] as $group) {
                                    foreach ($group as $item) {
                                        if (($item->processing_status ?? '') !== 'pending' && ($item->processing_status ?? '') !== 'failed' && $item->training_status === 'trained') $trainedCount++;
                                        if ($item->training_status === 'failed' || ($item->processing_status ?? '') === 'failed') $failedCount++;
                                    }
                                }
                                $pendingCount = $totalItems - $trainedCount - $failedCount;

                                $orphanedFileCount = 0;
                                $recoverableFileCount = 0;
                                foreach ($files as $f) {
                                    if (!empty($f->file_path) && !file_exists(FCPATH . $f->file_path)) {
                                        $orphanedFileCount++;
                                    }
                                }
                                if ($orphanedFileCount > 0) {
                                    $CI = &get_instance();
                                    $recoverableFileCount = (int)$CI->db->where('chatbot_id', $chatbot->id)
                                        ->where('file_data IS NOT NULL')
                                        ->where('file_data !=', '')
                                        ->count_all_results(db_prefix() . 'chatbot_training_files');
                                }

                                $vectorStore = new \PerfexChat\Neuron\VectorStore\ChatbotVectorStore($chatbot);
                                $vectorFileMissing = ($trainedCount > 0 && !$vectorStore->hasDocuments());

                                function training_status_badge($status, $processingStatus = null, $errorMessage = null, $retryType = null, $retryId = null)
                                {
                                    $tip = ($errorMessage) ? ' data-toggle="tooltip" title="' . htmlspecialchars($errorMessage) . '"' : '';
                                    $retryBtn = '';
                                    if ($retryType && $retryId && ($status === 'failed' || $processingStatus === 'failed')) {
                                        $retryUrl = admin_url('prchat/Chatbot_Admin/retry_training_item/' . $retryType . '/' . $retryId);
                                        $retryBtn = ' <a href="' . $retryUrl . '" class="tw-inline-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-rounded-full tw-bg-warning-100 tw-text-warning-700 tw-transition-all hover:tw-bg-warning-200" data-toggle="tooltip" title="' . _l('chatbot_retry') . '"><i class="fa fa-refresh tw-text-[10px]"></i></a>';
                                    }
                                    if ($processingStatus === 'pending') {
                                        return '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-neutral-100 tw-text-neutral-600"' . $tip . '>' . _l('chatbot_status_pending') . '</span>';
                                    }
                                    if ($processingStatus === 'failed') {
                                        return '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-danger-100 tw-text-danger-700"' . $tip . '>' . _l('chatbot_status_failed') . '</span>' . $retryBtn;
                                    }
                                    if ($status === 'trained') {
                                        return '<span class="tw-bg-success-100 tw-font-medium tw-inline-flex tw-items-center tw-px-2.5 tw-py-1.5 tw-rounded-full tw-text-success-700 tw-text-xs"' . $tip . '>' . _l('chatbot_status_trained') . '</span>';
                                    }
                                    if ($status === 'failed') {
                                        return '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-danger-100 tw-text-danger-700"' . $tip . '>' . _l('chatbot_status_failed') . '</span>' . $retryBtn;
                                    }
                                    return '<span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-warning-100 tw-text-warning-700"' . $tip . '>' . _l('chatbot_status_pending') . '</span>';
                                }
                                ?>

                                <!-- Header -->
                                <div class="tw-flex tw-justify-between tw-items-start tw-mb-5">                                    
                                    <div>
                                        <p class="tw-text-neutral-500 tw-m-0"><?= _l('chatbot_training_description') ?></p>
                                        <?php if ($totalItems > 0): ?>
                                            <p class="tw-text-xs tw-mt-1.5 tw-mb-0 tw-flex tw-gap-3">
                                                <span class="tw-text-success-600"><i class="fa fa-check-circle"></i> <?= $trainedCount ?> <?= _l('chatbot_status_trained') ?></span>
                                                <?php if ($pendingCount > 0): ?><span class="tw-text-warning-600"><i class="fa fa-clock"></i> <?= $pendingCount ?> <?= _l('chatbot_status_pending') ?></span><?php endif; ?>
                                                <?php if ($failedCount > 0): ?><span class="tw-text-danger-600"><i class="fa fa-exclamation-circle"></i> <?= $failedCount ?> <?= _l('chatbot_status_failed') ?></span><?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tw-flex tw-gap-2 tw-flex-shrink-0">
                                        <?= form_open(admin_url('prchat/Chatbot_Admin/clear_all_training/' . $chatbot->id), ['style' => 'display:inline']); ?>
                                        <button type="submit" class="btn btn-default _delete" data-confirm-message="<?= _l('chatbot_clear_all_confirm') ?>">
                                            <i class="fa fa-trash"></i> <?= _l('chatbot_clear_all') ?>
                                        </button>
                                        <?= form_close(); ?>
                                        <button type="button" class="btn btn-secondary" id="run-training">
                                            <i class="fa fa-play"></i> <?= _l('chatbot_train_ai') ?>
                                        </button>
                                    </div>
                                  </div>
                                <!-- Stats -->
                                <div class="tw-flex tw-flex-wrap tw-gap-4 tw-mb-5">
                                    <div class="tw-flex-1 tw-min-w-[180px] tw-p-4 tw-rounded-lg tw-border tw-border-solid tw-border-neutral-200 tw-bg-success-50 tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-success-200 tw-text-success-600"><i class="fa fa-link"></i></div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold tw-text-neutral-800"><?= count($links) ?></div>
                                            <div class="tw-text-xs tw-text-neutral-500"><?= _l('chatbot_training_stats_links') ?></div>
                                        </div>
                                    </div>
                                    <div class="tw-flex-1 tw-min-w-[180px] tw-p-4 tw-rounded-lg tw-border tw-border-solid tw-border-neutral-200 tw-bg-info-50 tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-info-200 tw-text-info-600"><i class="fa fa-question-circle"></i></div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold tw-text-neutral-800"><?= count($qas) ?></div>
                                            <div class="tw-text-xs tw-text-neutral-500"><?= _l('chatbot_training_stats_qa') ?></div>
                                        </div>
                                    </div>
                                    <div class="tw-flex-1 tw-min-w-[180px] tw-p-4 tw-rounded-lg tw-border tw-border-solid tw-border-neutral-200 tw-bg-warning-50 tw-flex tw-items-center tw-gap-3">
                                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-warning-200 tw-text-warning-600"><i class="fa fa-file"></i></div>
                                        <div>
                                            <div class="tw-text-2xl tw-font-bold tw-text-neutral-800"><?= count($files) ?></div>
                                            <div class="tw-text-xs tw-text-neutral-500"><?= _l('chatbot_training_stats_files') ?></div>
                                        </div>
                                    </div>
                                </div>

                                                       <?php if ($orphanedFileCount > 0): ?>
                                    <div class="tw-p-3 tw-rounded-lg tw-border tw-border-solid tw-border-warning-300 tw-bg-warning-50 tw-mb-4 tw-flex tw-items-start tw-gap-2.5 tw-text-sm tw-text-warning-800">
                                        <i class="fa fa-exclamation-triangle tw-mt-0.5 tw-flex-shrink-0"></i>
                                        <div>
                                            <strong><?= sprintf(_l('chatbot_orphaned_files_count'), $orphanedFileCount) ?></strong>
                                            <?php if ($recoverableFileCount > 0): ?>
                                                <br><?= sprintf(_l('chatbot_orphaned_files_recoverable'), $recoverableFileCount) ?>
                                            <?php else: ?>
                                                <br><?= _l('chatbot_orphaned_files_not_recoverable') ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($vectorFileMissing): ?>
                                    <div class="tw-p-3 tw-rounded-lg tw-border tw-border-solid tw-border-danger-300 tw-bg-danger-50 tw-mb-4 tw-flex tw-items-start tw-gap-2.5 tw-text-sm tw-text-danger-800">
                                        <i class="fa fa-exclamation-circle tw-mt-0.5 tw-flex-shrink-0"></i>
                                        <span><?= _l('chatbot_vectors_missing_warning') ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Website URLs -->
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-lg tw-mb-4 tw-overflow-hidden tw-bg-white">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-bg-neutral-50 tw-border-b tw-border-solid tw-border-neutral-200">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-success-200 tw-text-success-600 tw-text-xs"><i class="fa fa-link"></i></div>
                                            <strong><?= _l('chatbot_website_urls') ?></strong>
                                            <?php if (!empty($links)): ?><span class="text-muted tw-text-xs">(<?= count($links) ?>)</span><?php endif; ?>
                                        </div>
                                        <?php if (!empty($links)): ?>
                                            <?= form_open(admin_url('prchat/Chatbot_Admin/delete_all_training_links/' . $chatbot->id), ['style' => 'display:inline']); ?>
                                            <button type="submit" class="btn btn-danger btn-xs _delete tw-p-1.5" data-confirm-message="<?= _l('chatbot_delete_all_urls_confirm') ?>">
                                                <i class="fa fa-trash"></i> <?= _l('chatbot_delete_all') ?>
                                            </button>
                                            <?= form_close(); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tw-p-4">
                                        <?php echo form_open(admin_url('prchat/Chatbot_Admin/add_training_link'), ['class' => 'tw-mb-3']); ?>
                                        <input type="hidden" name="chatbot_id" value="<?= $chatbot->id ?>">
                                        <div class="tw-flex tw-flex-wrap tw-gap-3 tw-items-center tw-mb-2">
                                            <div class="tw-flex-1 tw-min-w-[200px]">
                                                <input type="url" name="url" class="form-control" placeholder="https://example.com/documentation" required>
                                            </div>
                                            <div class="tw-w-[100px] tw-flex-shrink-0">
                                                <button type="submit" class="btn btn-primary btn-block"><?= _l('chatbot_add') ?></button>
                                            </div>
                                        </div>
                                        <div class="tw-flex tw-flex-wrap tw-gap-3 tw-items-center tw-mb-2 tw-pt-1">
                                            <label class="tw-inline-flex tw-items-center tw-gap-2 tw-mb-0 tw-text-sm tw-text-neutral-600 tw-cursor-pointer">
                                                <input type="checkbox" name="crawl_subpages" value="1" id="crawl_subpages_checkbox" class="tw-rounded tw-border-neutral-300">
                                                <span><?= _l('chatbot_crawl_subpages') ?></span>
                                            </label>
                                            <div id="crawl_depth_container" class="tw-hidden tw-flex tw-items-center tw-gap-2" style="display:none;">
                                                <label class="tw-text-xs tw-text-neutral-500 tw-mb-0"><?= _l('chatbot_depth') ?></label>
                                                <input type="number" name="crawl_depth" class="form-control tw-w-16 tw-py-1 tw-text-sm" value="1" min="1" max="3" data-toggle="tooltip" title="<?= _l('chatbot_depth_tooltip') ?>">
                                            </div>
                                        </div>
                                        <small class="text-muted tw-text-xs tw-block"><?= _l('chatbot_crawl_help') ?></small>
                                        </form>
                                        <script>
                                            document.getElementById('crawl_subpages_checkbox').addEventListener('change', function() {
                                                var el = document.getElementById('crawl_depth_container');
                                                el.style.display = this.checked ? 'flex' : 'none';
                                            });
                                        </script>
                                        <?php if (!empty($links)):
                                            $parentLinks = array_filter($links, fn($l) => empty($l->parent_link_id));
                                            $subpagesByParent = [];
                                            foreach ($links as $link) {
                                                if (!empty($link->parent_link_id)) {
                                                    $subpagesByParent[$link->parent_link_id][] = $link;
                                                }
                                            }
                                            $totalLinks = count($parentLinks);
                                            $showLimit = 10;
                                            $hasMoreLinks = $totalLinks > $showLimit;
                                        ?>
                                            <div class="training-links-list">
                                                <?php
                                                $linkIndex = 0;
                                                foreach ($parentLinks as $link):
                                                    $subpages = $subpagesByParent[$link->id] ?? [];
                                                    $hasSubpages = !empty($subpages);
                                                    $linkIndex++;
                                                    $isHidden = $hasMoreLinks && $linkIndex > $showLimit;
                                                ?>
                                                    <div class="training-link-group tw-mb-2 tw-border tw-border-solid tw-rounded tw-p-2<?= $isHidden ? ' tw-hidden links-hidden-item' : '' ?>" style="background:#fafafa; border-color:#e2e8f0;">
                                                        <div class="tw-flex tw-items-center tw-justify-between">
                                                            <div class="tw-flex tw-items-center tw-gap-2 tw-flex-1">
                                                                <?php if ($hasSubpages): ?>
                                                                    <a href="javascript:void(0)" class="toggle-subpages" data-parent="<?= $link->id ?>">
                                                                        <i class="fa fa-chevron-right"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="<?= htmlspecialchars($link->url) ?>" target="_blank" class="tw-flex-1">
                                                                    <?= htmlspecialchars($link->title ?: substr($link->url, 0, 60)) ?>
                                                                </a>
                                                                <?php if ($link->crawl_subpages): ?>
                                                                    <span class="label label-info" data-toggle="tooltip" title="<?= _l('chatbot_will_crawl_subpages') ?>">
                                                                        <i class="fa fa-sitemap"></i> <?= $hasSubpages ? count($subpages) . ' ' . _l('chatbot_subpages') : _l('chatbot_crawl_enabled') ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="tw-flex tw-items-center tw-gap-2">
                                                                <?= training_status_badge($link->training_status, $link->processing_status ?? null, $link->error_message ?? null, 'link', $link->id) ?>
                                                                <a href="<?= admin_url('prchat/Chatbot_Admin/delete_training_link/' . $link->id) ?>"
                                                                    class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-lg tw-border tw-border-neutral-200 tw-text-danger-600 tw-transition-all tw-duration-200 tw-ease-out hover:tw-scale-105 hover:tw-bg-neutral-100 hover:tw-border-neutral-300 hover:tw-opacity-90 _delete" data-toggle="tooltip" title="<?= _l('chatbot_delete') ?><?= $hasSubpages ? ' (' . _l('chatbot_includes_subpages') . ')' : '' ?>">
                                                                    <i class="fa fa-trash tw-text-xs"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <?php if ($hasSubpages): ?>
                                                            <div class="subpages-container" id="subpages-<?= $link->id ?>" style="display:none; margin-top:10px; padding-left:20px; border-left:2px solid #ddd;">
                                                                <?php foreach ($subpages as $subpage): ?>
                                                                    <div class="tw-flex tw-items-center tw-justify-between tw-py-1 text-muted" style="font-size:0.9em;">
                                                                        <a href="<?= htmlspecialchars($subpage->url) ?>" target="_blank" class="tw-flex-1">
                                                                            <i class="fa fa-file tw-mr-1"></i>
                                                                            <?= htmlspecialchars($subpage->title ?: substr($subpage->url, 0, 50)) ?>
                                                                        </a>
                                                                        <div class="tw-flex tw-items-center tw-gap-2">
                                                                            <?= training_status_badge($subpage->training_status, $subpage->processing_status ?? null, $subpage->error_message ?? null, 'link', $subpage->id) ?>
                                                                            <a href="<?= admin_url('prchat/Chatbot_Admin/delete_training_link/' . $subpage->id) ?>" class="tw-inline-flex tw-items-center tw-justify-center tw-w-7 tw-h-7 tw-rounded-lg tw-border tw-border-neutral-200 tw-text-danger-600 tw-no-underline tw-transition-all tw-duration-200 tw-ease-out hover:tw-scale-105 hover:tw-bg-neutral-100 hover:tw-border-neutral-300 hover:tw-text-danger-600 hover:tw-no-underline hover:tw-opacity-90 _delete" data-toggle="tooltip" title="<?= _l('chatbot_delete') ?>">
                                                                                <i class="fa fa-times tw-text-xs"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if ($hasMoreLinks): ?>
                                                <div class="tw-text-center tw-mt-3">
                                                    <button type="button" class="btn btn-default btn-sm tw-toggle-links-more" data-show-text="Show more" data-hide-text="Show less">
                                                        <i class="fa fa-chevron-down tw-mr-1"></i> Show more (<?= $totalLinks - $showLimit ?>)
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <script>
                                                document.querySelectorAll('.toggle-subpages').forEach(function(btn) {
                                                    btn.addEventListener('click', function() {
                                                        var parentId = this.dataset.parent;
                                                        var container = document.getElementById('subpages-' + parentId);
                                                        var icon = this.querySelector('i');
                                                        if (container.style.display === 'none') {
                                                            container.style.display = 'block';
                                                            icon.classList.remove('fa-chevron-right');
                                                            icon.classList.add('fa-chevron-down');
                                                        } else {
                                                            container.style.display = 'none';
                                                            icon.classList.remove('fa-chevron-down');
                                                            icon.classList.add('fa-chevron-right');
                                                        }
                                                    });
                                                });

                                                // Toggle show more/less for links list
                                                document.querySelectorAll('.tw-toggle-links-more').forEach(function(btn) {
                                                    btn.addEventListener('click', function() {
                                                        var hiddenItems = document.querySelectorAll('.links-hidden-item');
                                                        var icon = this.querySelector('i');
                                                        var showText = this.dataset.showText || 'Show more';
                                                        var hideText = this.dataset.hideText || 'Show less';

                                                        if (hiddenItems.length > 0 && hiddenItems[0].classList.contains('tw-hidden')) {
                                                            // Show all
                                                            hiddenItems.forEach(function(item) {
                                                                item.classList.remove('tw-hidden');
                                                            });
                                                            icon.classList.remove('fa-chevron-down');
                                                            icon.classList.add('fa-chevron-up');
                                                            this.innerHTML = '<i class="fa fa-chevron-up tw-mr-1"></i> ' + hideText;
                                                        } else {
                                                            // Hide extra items
                                                            hiddenItems.forEach(function(item) {
                                                                item.classList.add('tw-hidden');
                                                            });
                                                            icon.classList.remove('fa-chevron-up');
                                                            icon.classList.add('fa-chevron-down');
                                                            var totalHidden = hiddenItems.length;
                                                            this.innerHTML = '<i class="fa fa-chevron-down tw-mr-1"></i> ' + showText + ' (' + totalHidden + ')';
                                                        }
                                                    });
                                                });
                                            </script>
                                        <?php else: ?>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-mt-2 tw-mb-0"><?= _l('chatbot_no_training_data') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Files -->
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-lg tw-mb-4 tw-overflow-hidden tw-bg-white">
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-3 tw-bg-neutral-50 tw-border-b tw-border-solid tw-border-neutral-200">
                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-warning-200 tw-text-warning-600 tw-text-xs"><i class="fa fa-file"></i></div>
                                        <strong><?= _l('chatbot_file_upload') ?></strong>
                                        <?php if (!empty($files)): ?><span class="text-muted tw-text-xs">(<?= count($files) ?>)</span><?php endif; ?>
                                    </div>
                                    <div class="tw-p-4">
                                        <p class="text-muted tw-mb-2 tw-text-sm"><?= _l('chatbot_file_upload_desc') ?> <span class="tw-text-neutral-500"><?= _l('chatbot_supported_formats') ?></span></p>
                                        <div class="alert alert-info" style="font-size:13px;"><i class="fa fa-info-circle"></i> <?= _l('chatbot_file_upload_info') ?></div>
                                        <?php echo form_open_multipart(admin_url('prchat/Chatbot_Admin/add_training_file'), ['class' => 'tw-mb-4']); ?>
                                        <input type="hidden" name="chatbot_id" value="<?= $chatbot->id ?>">
                                        <div class="tw-flex tw-flex-wrap tw-gap-3 tw-items-center tw-mb-3">
                                            <div class="tw-flex-1 tw-min-w-[120px] tw-max-w-[320px]">
                                                <input type="file" name="training_file" class="form-control" accept=".txt,.pdf,.doc,.docx,.md" required>
                                            </div>
                                            <div class="tw-w-[120px] tw-flex-shrink-0">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fa fa-upload"></i> <?= _l('chatbot_upload_file') ?>
                                                </button>
                                            </div>
                                        </div>
                                        </form>
                                        <?php if (!empty($files)): ?>
                                            <div class="tw-border tw-border-neutral-200 tw-rounded-md tw-overflow-hidden">
                                                <table class="table table-striped tw-mb-0">
                                                    <thead>
                                                        <tr class="tw-bg-neutral-50">
                                                            <th class="tw-text-left tw-py-2.5 tw-px-3.5 tw-text-xs tw-font-medium tw-text-neutral-500 tw-border-b tw-border-neutral-200"><?= _l('chatbot_original_name') ?></th>
                                                            <th class="tw-text-left tw-py-2.5 tw-px-3.5 tw-text-xs tw-font-medium tw-text-neutral-500 tw-border-b tw-border-neutral-200"><?= _l('chatbot_file_size') ?></th>
                                                            <th class="tw-text-left tw-py-2.5 tw-px-3.5 tw-text-xs tw-font-medium tw-text-neutral-500 tw-border-b tw-border-neutral-200" width="80"><?= _l('chatbot_status') ?></th>
                                                            <th class="tw-w-10 tw-border-b tw-border-neutral-200"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($files as $file): ?>
                                                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0">
                                                                <td class="tw-py-2.5 tw-px-3.5 tw-font-medium">
                                                                    <i class="fa fa-file-<?= in_array($file->file_type, ['pdf']) ? 'pdf' : (in_array($file->file_type, ['doc', 'docx']) ? 'word' : 'text') ?> tw-mr-1"></i>
                                                                    <?= htmlspecialchars($file->original_name) ?>
                                                                </td>
                                                                <td class="tw-py-2.5 tw-px-3.5 tw-text-neutral-500">
                                                                    <?php
                                                                    if ($file->file_size) {
                                                                        echo $file->file_size >= 1048576
                                                                            ? round($file->file_size / 1048576, 1) . ' MB'
                                                                            : round($file->file_size / 1024, 1) . ' KB';
                                                                    } else {
                                                                        echo '-';
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="tw-py-2.5 tw-px-3.5"><?= training_status_badge($file->training_status, $file->processing_status ?? null, $file->error_message ?? null, 'file', $file->id) ?></td>
                                                                <td class="tw-py-2.5 tw-px-3.5">
                                                                    <a href="<?= admin_url('prchat/Chatbot_Admin/delete_training_file/' . $file->id) ?>"
                                                                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-lg tw-border tw-border-neutral-200 tw-text-danger-600 tw-transition-all tw-duration-200 tw-ease-out hover:tw-scale-105 hover:tw-bg-neutral-100 hover:tw-border-neutral-300 hover:tw-opacity-90 _delete" data-toggle="tooltip" title="<?= _l('chatbot_delete') ?>"><i class="fa fa-trash tw-text-xs"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-mt-2 tw-mb-0"><?= _l('chatbot_no_training_data') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <!-- Q&A Pairs -->
                                <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-lg tw-mb-4 tw-overflow-hidden tw-bg-white">
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-px-4 tw-py-3 tw-bg-neutral-50 tw-border-b tw-border-solid tw-border-neutral-200">
                                        <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-info-200 tw-text-info-600 tw-text-xs"><i class="fa fa-question-circle"></i></div>
                                        <strong><?= _l('chatbot_qa_pairs') ?></strong>
                                        <?php if (!empty($qas)): ?><span class="text-muted tw-text-xs">(<?= count($qas) ?>)</span><?php endif; ?>
                                    </div>
                                    <div class="tw-p-4">
                                        <?php echo form_open(admin_url('prchat/Chatbot_Admin/add_training_qa'), ['class' => 'tw-mb-3']); ?>
                                        <input type="hidden" name="chatbot_id" value="<?= $chatbot->id ?>">
                                        <div class="tw-flex tw-flex-wrap tw-gap-3 tw-items-start tw-mb-3">
                                            <div class="tw-flex-1 tw-min-w-[200px]">
                                                <input type="text" name="question" class="form-control" placeholder="<?= _l('chatbot_question_placeholder') ?>" required>
                                            </div>
                                            <div class="tw-flex-1 tw-min-w-[200px]">
                                                <input type="text" name="answer" class="form-control" placeholder="<?= _l('chatbot_answer_placeholder') ?>" required>
                                            </div>
                                            <div class="tw-w-[100px] tw-flex-shrink-0">
                                                <button type="submit" class="btn btn-primary btn-block"><?= _l('chatbot_add') ?></button>
                                            </div>
                                        </div>
                                        </form>
                                        <?php if (!empty($qas)): ?>
                                            <div class="tw-border tw-border-neutral-200 tw-rounded-md tw-overflow-hidden">
                                                <table class="table table-striped tw-mb-0">
                                                    <?php foreach ($qas as $qa): ?>
                                                        <tr>
                                                            <td><strong>Q:</strong> <?= htmlspecialchars($qa->question) ?></td>
                                                            <td><strong>A:</strong> <?= htmlspecialchars(substr($qa->answer, 0, 60)) ?>...</td>
                                                            <td width="100"><?= training_status_badge($qa->training_status, null, $qa->error_message ?? null, 'qa', $qa->id) ?></td>
                                                            <td width="48" class="tw-px-3">
                                                                <a href="<?= admin_url('prchat/Chatbot_Admin/delete_training_qa/' . $qa->id) ?>"
                                                                    class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-lg tw-border tw-border-neutral-200 tw-text-danger-600 tw-transition-all tw-duration-200 tw-ease-out hover:tw-scale-105 hover:tw-bg-neutral-100 hover:tw-border-neutral-300 hover:tw-opacity-90 _delete" data-toggle="tooltip" title="<?= _l('chatbot_delete') ?>"><i class="fa fa-trash tw-text-xs"></i></a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="tw-text-sm tw-text-neutral-500 tw-mt-2 tw-mb-0"><?= _l('chatbot_no_training_data') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                                                                                    <!-- Training data info -->
                        <div class="tw-p-3 tw-rounded-lg tw-border tw-border-solid tw-border-primary-200 tw-bg-primary-50 tw-mb-4 tw-flex tw-items-start tw-gap-2.5 tw-text-sm tw-text-primary-800">
                            <i class="fa fa-info-circle tw-mt-0.5 tw-flex-shrink-0"></i>
                            <span><?= _l('chatbot_training_data_info') ?></span>
                        </div>
                            </div>

                            <!-- Embed Tab -->
                            <div role="tabpanel" class="tab-pane" id="embed">
                                <div class="tw-border tw-border-solid tw-rounded tw-mb-4" style="border-color:#e2e8f0;">
                                    <div class="tw-flex tw-items-center tw-gap-4 tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb; font-size:12px;"><i class="fa fa-code"></i></div>
                                            <strong><?= _l('chatbot_embed_external') ?></strong>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#bbf7d0; color:#16a34a; font-size:12px;"><i class="fa fa-globe"></i></div>
                                            <strong><?= _l('chatbot_allowed_domains') ?></strong>
                                        </div>
                                    </div>
                                    <div class="tw-p-4">
                                        <p class="text-muted tw-mb-3"><?= _l('chatbot_embed_instructions') ?></p>

                                        <div class="form-group">
                                            <textarea class="form-control tw-font-mono" rows="4" readonly onclick="this.select()" style="border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc;"><?= htmlspecialchars($chatbot->getEmbedCode()) ?></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary" onclick="copyEmbed()" style="border-radius:6px; font-weight:600;">
                                            <i class="fa fa-copy"></i> <?= _l('chatbot_copy_code') ?>
                                        </button>

                                        <hr style="margin: 24px 0; border-color:#e2e8f0;">

                                        <h5 class="tw-font-semibold tw-mb-2">
                                            <?= _l('chatbot_allowed_domains') ?>
                                            <i class="fa fa-question-circle text-muted domain-info-trigger" data-toggle="popover" data-trigger="hover" data-placement="right" data-html="true" data-content="<strong><?= _l('chatbot_domain_tip_title') ?></strong><br><br><i class='fa fa-circle text-muted' style='font-size:6px;vertical-align:middle;margin-right:4px;'></i><?= _l('chatbot_domain_tip_exact') ?><br><br><i class='fa fa-circle text-muted' style='font-size:6px;vertical-align:middle;margin-right:4px;'></i><?= _l('chatbot_domain_tip_wildcard') ?><br><br><i class='fa fa-circle text-muted' style='font-size:6px;vertical-align:middle;margin-right:4px;'></i><?= _l('chatbot_domain_tip_protocol') ?>" style="font-size: 14px; cursor: help;"></i>
                                        </h5>
                                        <p class="text-muted tw-mb-3"><?= _l('chatbot_allowed_domains_help') ?></p>

                                        <div id="domain-manager"
                                            data-chatbot-id="<?= $chatbot->id ?>"
                                            data-save-url="<?= admin_url('prchat/Chatbot_Admin/save_domains') ?>"
                                            data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
                                            data-csrf-hash="<?= $this->security->get_csrf_hash() ?>">

                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <div class="domain-input-row">
                                                    <div class="domain-input-field">
                                                        <input type="text" id="domain-input" class="form-control" placeholder="<?= _l('chatbot_domain_placeholder') ?>" autocomplete="off">
                                                        <div id="domain-error" class="domain-error-msg"></div>
                                                    </div>
                                                    <button type="button" id="domain-add-btn" class="btn btn-primary domain-add-btn">
                                                        <i class="fa fa-plus"></i> <?= _l('chatbot_add_domain') ?>
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="domain-tags" class="domain-tags-container">
                                                <?php
                                                $domains = $chatbot->allowed_domains ?? [];
                                                if (is_array($domains)):
                                                    foreach ($domains as $d):
                                                        $isWild = str_starts_with($d, '*.'); ?>
                                                        <span class="domain-tag<?= $isWild ? ' domain-tag-wildcard' : '' ?>" data-domain="<?= htmlspecialchars($d) ?>">
                                                            <?php if ($isWild): ?>
                                                                <i class="fa fa-asterisk domain-wildcard-icon" data-toggle="tooltip" title="<?= _l('chatbot_domain_wildcard_tip') ?>"></i>
                                                            <?php endif; ?>
                                                            <span class="domain-tag-text"><?= htmlspecialchars($d) ?></span>
                                                            <button type="button" class="domain-remove" data-toggle="tooltip" title="Remove">&times;</button>
                                                        </span>
                                                <?php endforeach;
                                                endif; ?>
                                            </div>

                                            <div id="domain-empty" class="domain-empty-state" <?= !empty($domains) ? 'style="display:none;"' : '' ?> style="<?= empty($domains) ? 'background:#eff6ff; border:2px solid #93c5fd; padding:16px; border-radius:8px;' : '' ?>">
                                                <i class="fa fa-info-circle" style="color:#2563eb; font-size:18px;"></i>
                                                <span style="color:#1e40af; font-weight:600;"><?= _l('chatbot_allowed_domains_empty') ?></span>
                                                <small style="display:block; margin-top:8px; color:#1e40af;"><?= _l('chatbot_allowed_domains_empty_tip') ?></small>
                                            </div>

                                            <div id="domain-status" class="domain-status-bar" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- API Keys Tab -->
                            <div role="tabpanel" class="tab-pane" id="api-keys">
                                <div class="tw-border tw-border-solid tw-rounded" style="border-color:#e2e8f0;">
                                    <div class="tw-flex tw-items-center tw-gap-4 tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb; font-size:12px;"><i class="fa fa-key"></i></div>
                                            <strong><?= _l('chatbot_openai_api_key') ?></strong>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#e9d5ff; color:#7c3aed; font-size:12px;"><i class="fa fa-cog"></i></div>
                                            <strong><?= _l('chatbot_embedding_model') ?></strong>
                                        </div>
                                    </div>
                                    <div class="tw-p-4">
                                        <?php echo form_open(admin_url('prchat/Chatbot_Admin/save_api_keys')); ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php
                                                $moduleKey = get_option('openai_api_key');
                                                $chatbotKey = get_option('chatbot_openai_api_key');
                                                $hasChatbotKey = !empty($chatbotKey);
                                                $hasModuleKey = !empty($moduleKey);
                                                ?>
                                                <?php if ($hasChatbotKey): ?>
                                                    <div class="alert alert-success tw-mb-3">
                                                        <i class="fa fa-check-circle"></i>
                                                        <?= _l('chatbot_using_own_key') ?>
                                                    </div>
                                                <?php elseif ($hasModuleKey): ?>
                                                    <div class="alert alert-success tw-mb-3">
                                                        <i class="fa fa-check-circle"></i>
                                                        <?= _l('chatbot_using_module_key') ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-danger tw-mb-3">
                                                        <i class="fa fa-times-circle"></i>
                                                        <?= _l('chatbot_no_api_key_message') ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="form-group">
                                                    <label><?= _l('chatbot_openai_api_key') ?></label>
                                                    <input type="password" name="chatbot_openai_api_key" class="form-control"
                                                        value="<?= $chatbotKey ? '••••••••' : '' ?>" placeholder="sk-..."
                                                        data-has-key="<?= $chatbotKey ? '1' : '0' ?>"
                                                        onfocus="if(this.value==='••••••••'){this.value='';this.dataset.cleared='1'}"
                                                        onblur="if(!this.value && this.dataset.hasKey==='1' && !this.dataset.cleared){this.value='••••••••'}">
                                                    <p class="help-block">
                                                        <a href="https://platform.openai.com/api-keys" target="_blank"><?= _l('chatbot_get_api_key') ?></a>
                                                        &middot; <?= _l('chatbot_api_key_help') ?>
                                                        <?php if ($hasModuleKey && !$hasChatbotKey): ?>
                                                            &middot; <?= _l('chatbot_module_key_fallback_note') ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?= _l('chatbot_embedding_model') ?></label>
                                                    <input type="text" class="form-control" disabled
                                                        value="<?= get_option('chatbot_openai_embedding_model') ?: 'text-embedding-3-small' ?>">
                                                    <p class="help-block"><?= _l('chatbot_embedding_model_help') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary" style="border-radius:6px; font-weight:600;">
                                            <i class="fa fa-save"></i> <?= _l('chatbot_save_api_keys') ?>
                                        </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Canned Responses Tab -->
                            <div role="tabpanel" class="tab-pane" id="canned-responses">
                                <p class="text-muted tw-mb-4"><?= _l('chatbot_canned_responses_desc') ?></p>

                                <div class="tw-border tw-border-solid tw-rounded tw-mb-4" style="border-color:#e2e8f0;">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-w-7 tw-h-7 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#fde68a; color:#d97706; font-size:12px;"><i class="fa fa-bolt"></i></div>
                                            <strong><?= _l('chatbot_canned_responses_tab') ?></strong>
                                        </div>
                                    </div>
                                    <div class="tw-p-4">
                                        <?php echo form_open(admin_url('prchat/Chatbot_Admin/add_canned_response'), ['class' => 'tw-mb-4']); ?>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <input type="text" name="title" class="form-control" placeholder="<?= _l('chatbot_canned_title_placeholder') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="shortcut" class="form-control" placeholder="<?= _l('chatbot_canned_shortcut_placeholder') ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <textarea name="content" class="form-control" rows="1" placeholder="<?= _l('chatbot_canned_content_placeholder') ?>" required></textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary btn-block" style="border-radius:6px; font-weight:600;">
                                                    <i class="fa fa-plus"></i> <?= _l('chatbot_add') ?>
                                                </button>
                                            </div>
                                        </div>
                                        </form>

                                        <?php if (!empty($cannedResponses)): ?>
                                            <div class="table-responsive" style="border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;">
                                                <table class="table table-condensed tw-mb-0" style="border-collapse:collapse;">
                                                    <thead>
                                                        <tr style="background:#f8fafc;">
                                                            <th style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"><?= _l('chatbot_canned_title_placeholder') ?></th>
                                                            <th style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"><?= _l('chatbot_canned_shortcut_placeholder') ?></th>
                                                            <th style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"><?= _l('chatbot_canned_content_placeholder') ?></th>
                                                            <th width="50" style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($cannedResponses as $cr): ?>
                                                            <tr style="border-bottom:1px solid #e2e8f0;">
                                                                <td style="padding:12px 14px;"><strong><?= htmlspecialchars($cr->title) ?></strong></td>
                                                                <td style="padding:12px 14px;"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($cr->shortcut ?: '-') ?></code></td>
                                                                <td style="padding:12px 14px; color:#64748b;"><?= htmlspecialchars(substr($cr->content, 0, 80)) ?><?= strlen($cr->content) > 80 ? '...' : '' ?></td>
                                                                <td style="padding:12px 14px;">
                                                                    <a href="<?= admin_url('prchat/Chatbot_Admin/delete_canned_response/' . $cr->id) ?>"
                                                                        class="text-danger _delete"><i class="fa fa-trash"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info tw-mb-0" style="background:#f0f9ff; border-color:#bae6fd; color:#0369a1;">
                                                <i class="fa fa-info-circle"></i> <?= _l('chatbot_no_canned_responses') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tags Tab -->
                            <div role="tabpanel" class="tab-pane" id="tags">
                                <p class="text-muted tw-mb-4"><?= _l('chatbot_tags_desc') ?></p>
                                <div class="tw-border tw-border-solid tw-rounded tw-mb-4" style="border-color:#e2e8f0;">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                        <strong><?= _l('chatbot_tags_tab') ?></strong>
                                    </div>
                                    <div class="tw-p-4">
                                        <div class="row tw-mb-4">
                                            <div class="col-md-4">
                                                <input type="text" id="chatbot-tag-name" class="form-control" placeholder="<?= _l('chatbot_tag_name') ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="color" id="chatbot-tag-color" class="form-control" value="#6c757d" style="height:34px;padding:2px;cursor:pointer;">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" id="chatbot-tag-add" class="btn btn-primary btn-block" style="border-radius:6px;"><i class="fa fa-plus"></i> <?= _l('chatbot_add') ?></button>
                                            </div>
                                        </div>
                                        <div class="table-responsive" style="border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;">
                                            <table class="table table-condensed tw-mb-0" id="chatbot-tags-table" style="border-collapse:collapse;">
                                                <thead>
                                                    <tr style="background:#f8fafc;">
                                                        <th style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"><?= _l('chatbot_tag_name') ?></th>
                                                        <th width="80" style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"><?= _l('chatbot_tag_color') ?></th>
                                                        <th width="100" style="border-bottom:1px solid #e2e8f0; padding:12px 14px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (isset($chatbot_tags) ? $chatbot_tags : [] as $t): ?>
                                                        <tr class="chatbot-tag-row" data-id="<?= (int)$t['id'] ?>" style="border-bottom:1px solid #e2e8f0;">
                                                            <td class="chatbot-tag-name" style="padding:12px 14px;"><?= htmlspecialchars($t['name']) ?></td>
                                                            <td style="padding:12px 14px;"><span class="chatbot-tag-swatch" style="display:inline-block;width:24px;height:24px;border-radius:4px;background:<?= htmlspecialchars($t['color'] ?? '#6c757d') ?>;border:1px solid #e2e8f0;"></span></td>
                                                            <td style="padding:12px 14px;">
                                                                <a href="#" class="chatbot-tag-edit text-primary"><i class="fa fa-edit"></i></a>
                                                                <a href="#" class="chatbot-tag-delete text-danger tw-ml-2"><i class="fa fa-trash"></i></a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if (empty($chatbot_tags)): ?>
                                            <p class="text-muted tw-mt-3 tw-mb-0"><?= _l('chatbot_no_tags') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Edit tag modal -->
                                <div class="modal fade" id="chatbot-tag-edit-modal" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title"><?= _l('chatbot_edit_tag') ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" id="chatbot-tag-edit-id">
                                                <div class="form-group">
                                                    <label><?= _l('chatbot_tag_name') ?></label>
                                                    <input type="text" id="chatbot-tag-edit-name" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label><?= _l('chatbot_tag_color') ?></label>
                                                    <input type="color" id="chatbot-tag-edit-color" class="form-control" style="height:38px;padding:2px;cursor:pointer;">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('chatbot_cancel') ?></button>
                                                <button type="button" class="btn btn-primary" id="chatbot-tag-edit-save"><i class="fa fa-save"></i> <?= _l('chatbot_save') ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Leads Tab -->
                            <div role="tabpanel" class="tab-pane" id="leads">
                                <?php
                                $totalLeads = count($leads);
                                $convertedCount = count(array_filter($leads, fn($l) => !empty($l->client_id)));
                                $assignedCount = count(array_filter($leads, fn($l) => !empty($l->assigned)));
                                $recentCount = count(array_filter($leads, fn($l) => strtotime($l->dateadded) >= strtotime('-7 days')));
                                $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100) : 0;
                                ?>

                                <div class="row tw-mb-5">
                                    <div class="col-md-3 col-sm-6">
                                        <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#e2e8f0; background:#f8fafc;">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#dbeafe; color:#2563eb;">
                                                    <i class="fa fa-users"></i>
                                                </div>
                                                <div>
                                                    <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $totalLeads ?></div>
                                                    <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_total_leads') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#d1fae5; background:#f0fdf4;">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#bbf7d0; color:#16a34a;">
                                                    <i class="fa fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $convertedCount ?></div>
                                                    <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_converted_clients') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#e9d5ff; background:#faf5ff;">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#e9d5ff; color:#7c3aed;">
                                                    <i class="fa fa-percent"></i>
                                                </div>
                                                <div>
                                                    <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $conversionRate ?>%</div>
                                                    <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_conversion_rate') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="tw-p-4 tw-rounded tw-border tw-border-solid" style="border-color:#fde68a; background:#fffbeb;">
                                            <div class="tw-flex tw-items-center tw-gap-3">
                                                <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center" style="background:#fde68a; color:#d97706;">
                                                    <i class="fa fa-clock"></i>
                                                </div>
                                                <div>
                                                    <div class="tw-text-2xl tw-font-bold" style="color:#1e293b;"><?= $recentCount ?></div>
                                                    <div class="tw-text-xs" style="color:#64748b;"><?= _l('chatbot_leads_this_week') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($totalLeads === 0): ?>
                                    <div class="tw-text-center tw-py-8">
                                        <div style="color:#94a3b8; font-size:48px;" class="tw-mb-3"><i class="fa fa-user-plus"></i></div>
                                        <h4 style="color:#475569;" class="tw-mb-2"><?= _l('chatbot_no_leads_captured_title') ?></h4>
                                        <p style="color:#94a3b8; max-width:400px;" class="tw-mx-auto"><?= _l('chatbot_no_leads_captured') ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped dt-table" id="chatbot-leads-table">
                                            <thead>
                                                <tr>
                                                    <th style="min-width:120px;"><?= _l('chatbot_name_label') ?></th>
                                                    <th style="min-width:160px;"><?= _l('chatbot_email') ?></th>
                                                    <th style="min-width:110px;"><?= _l('chatbot_phone_label') ?></th>
                                                    <th style="min-width:120px;"><?= _l('chatbot_location') ?></th>
                                                    <th style="min-width:80px;"><?= ucfirst(_l('source')) ?></th>
                                                    <th style="min-width:80px;"><?= _l('chatbot_lead_status') ?></th>
                                                    <th style="min-width:100px;"><?= _l('chatbot_assigned_to') ?></th>
                                                    <th style="min-width:90px;"><?= _l('chatbot_converted') ?></th>
                                                    <th style="min-width:140px;"><?= _l('chatbot_timeline') ?></th>
                                                    <th style="width:110px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($leads as $lead): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?= admin_url('leads/index/' . $lead->id) ?>">
                                                                <strong><?= htmlspecialchars($lead->name ?: '-') ?></strong>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="mailto:<?= htmlspecialchars($lead->email) ?>"><?= htmlspecialchars($lead->email) ?></a>
                                                        </td>
                                                        <td><?= htmlspecialchars($lead->phonenumber ?: '-') ?></td>
                                                        <td>
                                                            <?php
                                                            $visitorInfo = json_decode($lead->visitor_info ?? '{}', true);
                                                            $location = [];
                                                            if (!empty($visitorInfo['city'])) $location[] = $visitorInfo['city'];
                                                            if (!empty($visitorInfo['country'])) $location[] = $visitorInfo['country'];
                                                            $locationStr = !empty($location) ? implode(', ', $location) : null;

                                                            $mapsUrl = '';
                                                            if ($locationStr) {
                                                                $hasCoords = !empty($visitorInfo['latitude']) && !empty($visitorInfo['longitude']);
                                                                $mapsUrl = $hasCoords
                                                                    ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($visitorInfo['latitude'] . ',' . $visitorInfo['longitude'])
                                                                    : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($locationStr);
                                                            }
                                                            ?>
                                                            <?php if ($locationStr): ?>
                                                                <a href="<?= $mapsUrl ?>" target="_blank" rel="noopener noreferrer"
                                                                    style="color:#64748b; text-decoration:none;"
                                                                    data-toggle="tooltip"
                                                                    title="<?= _l('chatbot_open_in_maps') ?>">
                                                                    <i class="fa fa-map-marker" style="color:#dc2626;"></i> <?= htmlspecialchars($locationStr) ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($lead->source)): ?>
                                                                <span class="label" style="background-color:#64748b; color:#fff;"><?= htmlspecialchars($lead->source) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="label" style="background-color:<?= htmlspecialchars($lead->status_color ?: '#94a3b8') ?>; color:#fff;"><?= htmlspecialchars($lead->status_name ?: '-') ?></span>
                                                        </td>
                                                        <td><?= htmlspecialchars($lead->assigned_staff_name ?: '-') ?></td>
                                                        <td>
                                                            <?php if (!empty($lead->client_id)): ?>
                                                                <a href="<?= admin_url('clients/client/' . $lead->client_id) ?>" class="text-success" style="font-weight:500;">
                                                                    <i class="fa fa-check-circle"></i> <?= _l('client') ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td data-order="<?= strtotime($lead->conversation_started_at) ?>">
                                                            <div style="line-height:1.4;">
                                                                <strong><?= _l('chatbot_started') ?>:</strong> <?= _dt($lead->conversation_started_at) ?>
                                                                <br><small class="text-muted"><strong><?= _l('chatbot_captured') ?>:</strong> <?= _dt($lead->dateadded) ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="tw-flex tw-items-center tw-gap-1">
                                                                <a href="<?= admin_url('leads/index/' . $lead->id) ?>"
                                                                    class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('chatbot_view_lead') ?>">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                <?php if (!empty($lead->client_id)): ?>
                                                                    <a href="<?= admin_url('clients/client/' . $lead->client_id) ?>"
                                                                        class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('chatbot_view_client') ?>">
                                                                        <i class="fa fa-building"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a href="<?= admin_url('prchat/Chatbot_Admin/live_chat/' . $lead->conversation_id) ?>"
                                                                    class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('chatbot_view_conversation') ?>">
                                                                    <i class="fa fa-comments"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<!-- Domain Manager Styles -->
<style>
    a:hover>i.fa-trash {
        color: #e74c3c !important;
    }

    .domain-input-row {
        display: flex;
        gap: 8px;
        align-items: flex-start;
    }

    .domain-input-field {
        flex: 1;
    }

    .domain-add-btn {
        white-space: nowrap;
    }

    .domain-error-msg {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 4px;
        display: none;
    }

    .domain-tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
        min-height: 10px;
    }

    .domain-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f0f4f8;
        border: 1px solid #d0d9e3;
        border-radius: 20px;
        font-size: 13px;
        color: #333;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        transition: all 0.2s ease;
        animation: domainTagIn 0.2s ease;
    }

    @keyframes domainTagIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .domain-tag:hover {
        background: #e2e8f0;
        border-color: #b8c4d0;
    }

    .domain-tag .domain-remove {
        background: none;
        border: none;
        color: #aaa;
        font-size: 16px;
        line-height: 1;
        padding: 0 2px;
        cursor: pointer;
        transition: color 0.15s;
        margin-left: 2px;
    }

    .domain-tag .domain-remove:hover {
        color: #e74c3c;
    }

    .domain-tag-wildcard {
        background: #eef2ff;
        border-color: #a4b8f0;
    }

    .domain-tag-wildcard:hover {
        background: #dce4ff;
        border-color: #8da4e8;
    }

    .domain-wildcard-icon {
        font-size: 10px;
        color: #4a6cf7;
    }

    .domain-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px;
        font-size: 13px;
        border-radius: 8px;
        margin-bottom: 15px;
        background: #fef2f2;
        border: 2px solid #fca5a5;
    }

    .domain-empty-state .fa-exclamation-triangle {
        font-size: 24px;
        color: #dc2626;
        margin-bottom: 4px;
    }

    .domain-status-bar {
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 4px;
        margin-top: 10px;
        transition: opacity 0.3s;
    }

    .domain-status-bar.status-saving {
        background: #fef9e7;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .domain-status-bar.status-success {
        background: #eafaf1;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
    }

    .domain-status-bar.status-error {
        background: #fdf0f0;
        color: #c0392b;
        border: 1px solid #f5c6cb;
    }

    .domain-info-trigger {
        vertical-align: middle;
    }
</style>

<!-- Training Progress Modal -->
<style>
    #training-progress-bar {
        font-size: 0 !important;
        color: transparent !important;
        text-indent: -9999px !important;
        overflow: hidden !important;
    }
</style>
<div class="modal fade" id="trainingProgressModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-cogs"></i> <?= _l('chatbot_training_ai_title') ?></h4>
            </div>
            <div class="modal-body">
                <div id="training-status">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:15px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary" id="training-spinner"></i>
                        <span id="training-status-text"><?= _l('chatbot_processing') ?></span>
                    </div>
                    <div class="progress" style="margin-bottom:15px;height:20px;">
                        <div id="training-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" style="width:5%;"></div>
                    </div>
                    <p id="training-details" style="font-size:13px;color:#666;margin:0;"></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var pollTimer = null;
        var trainingDone = false;

        function pollProgress($bar, $statusText, $details) {
            if (trainingDone) return;
            $.getJSON('<?= admin_url("prchat/Chatbot_Admin/training_progress/" . $chatbot->id) ?>', function(p) {
                if (trainingDone || !p || p.phase === 'idle' || p.phase === 'done') return;

                if (p.message) $statusText.text(p.message);

                if (p.phase === 'init') {
                    $bar.css('width', '5%');
                } else if (p.phase === 'processing') {
                    var pct = (p.total > 0) ? 5 + Math.round((p.current / p.total) * 40) : 25;
                    $bar.css('width', Math.min(pct, 45) + '%');
                    $details.text(p.detail || (p.current + ' / ' + p.total));
                } else if (p.phase === 'embedding') {
                    var pct = (p.total > 0) ? 45 + Math.round((p.current / p.total) * 50) : 50;
                    $bar.css('width', Math.min(pct, 95) + '%');
                    var detail = p.current + ' / ' + p.total;
                    if (p.failed > 0) detail += ' (' + p.failed + ' failed)';
                    $details.text(detail);
                }
            }).always(function() {
                if (!trainingDone) pollTimer = setTimeout(function() {
                    pollProgress($bar, $statusText, $details);
                }, 1500);
            });
        }

        $('#run-training').on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $modal = $('#trainingProgressModal');
            var $progressBar = $('#training-progress-bar');
            var $statusText = $('#training-status-text');
            var $details = $('#training-details');

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?= _l("chatbot_training") ?>');

            trainingDone = false;
            $progressBar.css('width', '2%').removeClass('progress-bar-success progress-bar-danger').addClass('progress-bar-striped active');
            $statusText.text('<?= _l("chatbot_starting_training") ?>');
            $details.empty();
            $('#training-spinner').removeClass('fa-check fa-times text-success text-danger').addClass('fa-spinner fa-spin text-primary');

            $modal.modal('show');

            pollTimer = setTimeout(function() {
                pollProgress($progressBar, $statusText, $details);
            }, 1000);

            $.post('<?= admin_url("prchat/Chatbot_Admin/run_training/" . $chatbot->id) ?>', function(response) {
                trainingDone = true;
                if (pollTimer) clearTimeout(pollTimer);

                var data = (typeof response === 'string') ? JSON.parse(response) : response;

                if (data && data.success) {
                    $progressBar.css('width', '100%').removeClass('active progress-bar-striped').addClass('progress-bar-success');
                    $('#training-spinner').removeClass('fa-spinner fa-spin text-primary').addClass('fa-check text-success');
                    $statusText.text('<?= _l("chatbot_training_completed") ?>');
                    $details.html('<strong>' + (data.trained || 0) + '</strong> <?= _l("chatbot_items_trained") ?>');

                    if (data.errors && data.errors.length > 0) {
                        var errorHtml = '<br><span style="color:#c0392b;font-weight:600;">' + data.errors.length + ' <?= _l("chatbot_items_errors") ?></span>';
                        errorHtml += '<ul style="margin:6px 0 0;padding-left:18px;font-size:12px;color:#666;list-style:disc;">';
                        data.errors.forEach(function(e) {
                            errorHtml += '<li>' + $('<span>').text(e).html() + '</li>';
                        });
                        errorHtml += '</ul>';
                        $details.append(errorHtml);
                    }

                    $btn.prop('disabled', false).html('<i class="fa fa-play"></i> <?= _l("chatbot_train_ai") ?>');
                    setTimeout(function() {
                        $modal.modal('hide');
                        window.location.reload(true);
                    }, 1500);
                } else {
                    $progressBar.css('width', '100%').removeClass('active progress-bar-striped').addClass('progress-bar-danger');
                    $('#training-spinner').removeClass('fa-spinner fa-spin text-primary').addClass('fa-times text-danger');
                    $statusText.text('<?= _l("chatbot_training_failed") ?>');
                    $details.html('<span style="color:red;">' + (data && data.error ? data.error : '<?= _l("chatbot_unknown_error") ?>') + '</span>');
                    setTimeout(function() {
                        $modal.modal('hide');
                        $btn.prop('disabled', false).html('<i class="fa fa-play"></i> <?= _l("chatbot_train_ai") ?>');
                    }, 3000);
                }
            }).fail(function(xhr, status, error) {
                trainingDone = true;
                if (pollTimer) clearTimeout(pollTimer);

                $progressBar.css('width', '100%').removeClass('active progress-bar-striped').addClass('progress-bar-danger');
                $('#training-spinner').removeClass('fa-spinner fa-spin text-primary').addClass('fa-times text-danger');
                $statusText.text('<?= _l("chatbot_training_error") ?>');
                $details.html('<span style="color:red;"><?= _l("chatbot_request_failed") ?> ' + (error || status || '<?= _l("chatbot_network_error") ?>') + '</span>');
                setTimeout(function() {
                    $modal.modal('hide');
                    $btn.prop('disabled', false).html('<i class="fa fa-play"></i> <?= _l("chatbot_train_ai") ?>');
                }, 3000);
            });
        });
    });

    // Copy embed code
    function copyEmbed() {
        const textarea = document.querySelector('#embed textarea');
        textarea.select();
        document.execCommand('copy');
        alert_float('success', '<?= _l("chatbot_embed_code_copied") ?>');
    }
    window.copyEmbed = copyEmbed;
</script>


<script>
    $(function() {
        // Handle tab persistence via URL hash
        var hash = window.location.hash;
        if (hash && hash.length > 1) {
            // Remove active from default tab
            $('.nav-tabs li').removeClass('active');
            $('.tab-pane').removeClass('active');

            // Activate the correct tab
            var $tabLink = $('.nav-tabs a[href="' + hash + '"]');
            if ($tabLink.length) {
                $tabLink.parent('li').addClass('active');
                $(hash).addClass('active');
            }

            // Scroll to top after tab activation
            setTimeout(() => {
                window.scrollTo(0, 0);
            }, 100);
        }

        // Update URL hash when tab changes (without page scroll)
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var newHash = $(e.target).attr('href');
            if (history.replaceState) {
                history.replaceState(null, null, newHash);
            }
        });
    });

    // Chatbot tags: add, edit, delete
    (function() {
        var baseUrl = '<?= admin_url("prchat/Chatbot_Admin/") ?>';
        var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash() ?>';

        function post(url, data) {
            data[csrfName] = csrfHash;
            return $.post(baseUrl + url, data).then(function(r) {
                if (r && r[csrfName]) csrfHash = r[csrfName];
                return r;
            });
        }

        function addRow(tag) {
            var tbody = $('#chatbot-tags-table tbody');
            var emptyMsg = tbody.siblings('p.text-muted');
            if (emptyMsg.length) emptyMsg.remove();
            tbody.append(
                '<tr class="chatbot-tag-row" data-id="' + tag.id + '" style="border-bottom:1px solid #e2e8f0;">' +
                '<td class="chatbot-tag-name" style="padding:12px 14px;">' + $('<span>').text(tag.name).html() + '</td>' +
                '<td style="padding:12px 14px;"><span class="chatbot-tag-swatch" style="display:inline-block;width:24px;height:24px;border-radius:4px;background:' + (tag.color || '#6c757d') + ';border:1px solid #e2e8f0;"></span></td>' +
                '<td style="padding:12px 14px;"><a href="#" class="chatbot-tag-edit text-primary"><i class="fa fa-edit"></i></a> <a href="#" class="chatbot-tag-delete text-danger tw-ml-2"><i class="fa fa-trash"></i></a></td></tr>'
            );
        }
        $('#chatbot-tag-add').on('click', function() {
            var name = $('#chatbot-tag-name').val().trim();
            var color = $('#chatbot-tag-color').val() || '#6c757d';
            if (!name) {
                alert_float('warning', '<?= _l("chatbot_tag_name_required") ?>');
                return;
            }
            post('create_tag', {
                name: name,
                color: color
            }).then(function(r) {
                if (r && r.success && r.tag) {
                    addRow(r.tag);
                    $('#chatbot-tag-name').val('');
                    alert_float('success', '<?= _l("chatbot_tag_added") ?>');
                } else {
                    alert_float('danger', r && r.error ? r.error : '<?= _l("chatbot_error") ?>');
                }
            });
        });
        $(document).on('click', '.chatbot-tag-edit', function(e) {
            e.preventDefault();
            var $row = $(this).closest('.chatbot-tag-row');
            $('#chatbot-tag-edit-id').val($row.data('id'));
            $('#chatbot-tag-edit-name').val($row.find('.chatbot-tag-name').text());
            var bg = $row.find('.chatbot-tag-swatch').css('background-color');
            $('#chatbot-tag-edit-color').val(rgbToHex(bg) || '#6c757d');
            $('#chatbot-tag-edit-modal').modal('show');
        });

        function rgbToHex(rgb) {
            if (!rgb || rgb.indexOf('rgb') === -1) return null;
            var m = rgb.match(/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i);
            if (!m) return null;
            return '#' + [1, 2, 3].map(function(i) {
                var h = parseInt(m[i], 10).toString(16);
                return h.length === 1 ? '0' + h : h;
            }).join('');
        }
        $('#chatbot-tag-edit-save').on('click', function() {
            var id = $('#chatbot-tag-edit-id').val();
            var name = $('#chatbot-tag-edit-name').val().trim();
            var color = $('#chatbot-tag-edit-color').val() || '#6c757d';
            if (!name) {
                alert_float('warning', '<?= _l("chatbot_tag_name_required") ?>');
                return;
            }
            post('update_tag', {
                id: id,
                name: name,
                color: color
            }).then(function(r) {
                if (r && r.success) {
                    var $row = $('.chatbot-tag-row[data-id="' + id + '"]');
                    $row.find('.chatbot-tag-name').text(name);
                    $row.find('.chatbot-tag-swatch').css('background', color);
                    $('#chatbot-tag-edit-modal').modal('hide');
                    alert_float('success', '<?= _l("chatbot_tag_updated") ?>');
                } else {
                    alert_float('danger', r && r.error ? r.error : '<?= _l("chatbot_error") ?>');
                }
            });
        });
        $(document).on('click', '.chatbot-tag-delete', function(e) {
            e.preventDefault();
            if (!confirm('<?= _l("chatbot_confirm_delete_tag") ?>')) return;
            var id = $(this).closest('.chatbot-tag-row').data('id');
            post('delete_tag', {
                id: id
            }).then(function(r) {
                if (r && r.success) {
                    $('.chatbot-tag-row[data-id="' + id + '"]').remove();
                    alert_float('success', '<?= _l("chatbot_tag_deleted") ?>');
                } else {
                    alert_float('danger', '<?= _l("chatbot_error") ?>');
                }
            });
        });
    })();

    // Force AI Chatbot sidebar active (hash fragments break Perfex's exact-URL matching)
    $(window).on('load', function() {
        setTimeout(function() {
            var $link = $('#side-menu a[href*="prchat/Chatbot_Admin"]').not('[href*="live_chat"]').first();
            if ($link.length) {
                $link.parent('li').addClass('active');
                $link.parents('li').addClass('active');
                $link.prop('aria-expanded', true);
                var $sub = $link.closest('ul.nav-second-level');
                $sub.addClass('in').prop('aria-expanded', true);
                $sub.parent('li').addClass('active').find('> a').prop('aria-expanded', true);
            }
        }, 50);
    });

    // Prevent scroll on page load with hash
    if (window.location.hash) {
        setTimeout(function() {
            window.scrollTo(0, 0);
        }, 1000);
    }
</script>

<!-- Lead fields toggle -->
<script>
    $(function() {
        $('#capture_leads_toggle').on('change', function() {
            $('#lead-fields-config').toggle(this.checked);
        });
    });
</script>

<!-- Domain Manager -->
<script>
    $(function() {
        // Initialize tooltips and popovers for domain section
        $('.domain-info-trigger').popover({
            container: 'body'
        });
        $('.domain-wildcard-icon').tooltip({
            container: 'body'
        });

        var manager = document.getElementById('domain-manager');
        if (!manager) return;

        var input = document.getElementById('domain-input');
        var addBtn = document.getElementById('domain-add-btn');
        var tagsContainer = document.getElementById('domain-tags');
        var errorEl = document.getElementById('domain-error');
        var emptyEl = document.getElementById('domain-empty');
        var statusEl = document.getElementById('domain-status');

        var chatbotId = manager.getAttribute('data-chatbot-id');
        var saveUrl = manager.getAttribute('data-save-url');
        var csrfName = manager.getAttribute('data-csrf-name');
        var csrfHash = manager.getAttribute('data-csrf-hash');

        var domainRegex = /^(\*\.)?([a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
        var saveTimer = null;
        var isSaving = false;

        function getDomains() {
            var tags = tagsContainer.querySelectorAll('.domain-tag');
            var list = [];
            for (var i = 0; i < tags.length; i++) {
                list.push(tags[i].getAttribute('data-domain'));
            }
            return list;
        }

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.style.display = 'block';
            input.style.borderColor = '#e74c3c';
        }

        function clearError() {
            errorEl.style.display = 'none';
            input.style.borderColor = '';
        }

        function updateEmpty() {
            var isEmpty = getDomains().length === 0;
            emptyEl.style.display = isEmpty ? '' : 'none';

            // Add security warning styling when empty
            if (isEmpty) {
                emptyEl.style.background = '#fef2f2';
                emptyEl.style.border = '2px solid #fca5a5';
                emptyEl.style.padding = '16px';
            }
        }

        function showStatus(type, msg) {
            statusEl.className = 'domain-status-bar status-' + type;
            statusEl.innerHTML = msg;
            statusEl.style.display = 'block';
            statusEl.style.opacity = '1';
            if (type !== 'saving') {
                setTimeout(function() {
                    statusEl.style.opacity = '0';
                    setTimeout(function() {
                        statusEl.style.display = 'none';
                    }, 300);
                }, 2500);
            }
        }

        function cleanDomain(raw) {
            var d = raw.trim().toLowerCase();
            d = d.replace(/^https?:\/\//, '');
            d = d.replace(/^www\./, '');
            d = d.replace(/\/+$/, '');
            d = d.replace(/:\d+$/, '');
            return d;
        }

        function saveDomains() {
            if (isSaving) return;
            isSaving = true;
            showStatus('saving', '<i class="fa fa-spinner fa-spin"></i> <?= _l("chatbot_domain_saved") ?>...');

            var formData = new FormData();
            formData.append('id', chatbotId);
            formData.append('domains_json', JSON.stringify(getDomains()));
            formData.append(csrfName, csrfHash);

            fetch(saveUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(resp) {
                    return resp.json();
                })
                .then(function(data) {
                    isSaving = false;
                    if (data.success) {
                        showStatus('success', '<i class="fa fa-check"></i> <?= _l("chatbot_domain_saved") ?>');

                        // Hide top security warning banner when domains exist
                        var securityWarning = document.getElementById('domain-security-warning');
                        if (securityWarning && getDomains().length > 0) {
                            securityWarning.style.transition = 'opacity 0.3s';
                            securityWarning.style.opacity = '0';
                            setTimeout(function() {
                                securityWarning.style.display = 'none';
                            }, 300);
                        }
                    } else {
                        showStatus('error', '<i class="fa fa-times"></i> ' + (data.message || '<?= _l("chatbot_domain_save_error") ?>'));
                    }
                })
                .catch(function() {
                    isSaving = false;
                    showStatus('error', '<i class="fa fa-times"></i> <?= _l("chatbot_domain_network_error") ?>');
                });
        }

        function createTag(domain) {
            var isWild = domain.indexOf('*.') === 0;
            var tag = document.createElement('span');
            tag.className = 'domain-tag' + (isWild ? ' domain-tag-wildcard' : '');
            tag.setAttribute('data-domain', domain);

            if (isWild) {
                var icon = document.createElement('i');
                icon.className = 'fa fa-asterisk domain-wildcard-icon';
                icon.setAttribute('data-toggle', 'tooltip');
                icon.setAttribute('title', '<?= _l("chatbot_domain_wildcard_tip") ?>');
                tag.appendChild(icon);
                $(icon).tooltip({
                    container: 'body'
                });
            }

            var text = document.createElement('span');
            text.className = 'domain-tag-text';
            text.textContent = domain;
            tag.appendChild(text);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'domain-remove';
            removeBtn.title = 'Remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function() {
                var currentDomains = getDomains();

                // ⚠️ Security warning if removing the last domain
                if (currentDomains.length === 1) {
                    if (!confirm('You are about to remove the last allowed domain.\n\nWithout any allowed domains, the widget will load on ANY website with no restrictions.\n\nAre you sure you want to proceed?')) {
                        return; // User cancelled
                    }
                }

                tag.style.opacity = '0';
                tag.style.transform = 'scale(0.8)';
                setTimeout(function() {
                    tag.remove();
                    updateEmpty();

                    // Show top security warning if no domains left
                    if (getDomains().length === 0) {
                        var securityWarning = document.getElementById('domain-security-warning');
                        if (securityWarning) {
                            securityWarning.style.display = 'block';
                            securityWarning.style.opacity = '1';
                        }
                    }

                    saveDomains();
                }, 150);
            });
            tag.appendChild(removeBtn);

            return tag;
        }

        function addDomain() {
            clearError();
            var raw = input.value;
            if (!raw.trim()) return;

            var domain = cleanDomain(raw);

            if (!domain) {
                showError('<?= _l("chatbot_domain_enter") ?>');
                return;
            }

            if (!domainRegex.test(domain)) {
                showError('<?= _l("chatbot_domain_invalid") ?>');
                return;
            }

            var existing = getDomains();
            for (var i = 0; i < existing.length; i++) {
                if (existing[i] === domain) {
                    showError('<?= _l("chatbot_domain_duplicate") ?>');
                    return;
                }
            }

            tagsContainer.appendChild(createTag(domain));
            input.value = '';
            updateEmpty();
            input.focus();
            saveDomains();
        }

        addBtn.addEventListener('click', addDomain);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addDomain();
            }
        });

        // Wire up remove buttons for server-rendered tags
        tagsContainer.querySelectorAll('.domain-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tag = btn.closest('.domain-tag');
                tag.style.opacity = '0';
                tag.style.transform = 'scale(0.8)';
                setTimeout(function() {
                    tag.remove();
                    updateEmpty();
                    saveDomains();
                }, 150);
            });
        });

        // Toggle collapse icon for thinking phrases panel
        $('#thinking-phrases-panel').on('shown.bs.collapse hidden.bs.collapse', function() {
            var icon = $(this).prev('.panel-heading').find('i');
            icon.toggleClass('fa-chevron-up fa-chevron-down');
        });
    });
</script>

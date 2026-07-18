<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="wac-chat-container">

                    <!-- LEFT PANEL - Conversation List -->
                    <div class="wac-left-panel" id="wac-left-panel">
                        <div class="wac-left-header">
                            <div class="wac-logo-row">
                                <svg class="wac-whatsapp-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span class="wac-header-title"><?php echo _l('whatsapp_messages'); ?></span>
                            </div>
                            <div class="wac-header-right">
                                <span class="wac-total-count" id="wac-total-count">
                                    <?php echo count($conversations ?? []); ?> <?php echo _l('conversations'); ?>
                                </span>
                                <?php if (is_admin()) : ?>
                                    <a href="<?php echo admin_url('whatsapp_chat/settings'); ?>" class="wac-settings-btn" title="<?php echo _l('settings'); ?>">
                                        <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="wac-conversation-search">
                            <input type="text" id="wac-search" class="form-control"
                                   placeholder="<?php echo _l('search'); ?>..." autocomplete="off">
                        </div>

                        <div class="wac-conversation-list" id="wac-conversation-list">
                            <?php $this->load->view('whatsapp_chat/partials/conversations', ['conversations' => $conversations ?? []]); ?>
                        </div>
                    </div>

                    <!-- RIGHT PANEL - Chat Messages -->
                    <div class="wac-right-panel" id="wac-right-panel">

                        <!-- Empty state (shown when no conversation selected) -->
                        <div class="wac-empty-state" id="wac-empty-state">
                            <i class="fa fa-whatsapp wac-empty-icon"></i>
                            <h3><?php echo _l('whatsapp_messages'); ?></h3>
                            <p><?php echo _l('wac_select_conversation') ?: 'Select a conversation from the left panel to view messages.'; ?></p>
                        </div>

                        <!-- Chat panel (shown once a conversation is selected) -->
                        <div class="wac-chat-panel" id="wac-chat-panel" style="display:none;">

                            <div class="wac-chat-header" id="wac-chat-header">
                                <!-- Populated via AJAX -->
                            </div>

                            <div class="wac-messages-wrap" id="wac-messages-wrap">
                                <!-- Messages loaded via AJAX -->
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Expose URLs to JS
    var WAC_BASE_URL = '<?php echo admin_url("whatsapp_chat"); ?>';
    var WAC_CSRF_TOKEN = '<?php echo $this->security->get_csrf_hash(); ?>';
</script>

<?php init_tail(); ?>

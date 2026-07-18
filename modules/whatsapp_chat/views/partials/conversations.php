<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * partials/conversations.php
 *
 * Renders the conversation list items for the left panel.
 * Called on initial load AND on AJAX polling response.
 * Expects $conversations array.
 */
$conversations = $conversations ?? [];
?>
<?php if (empty($conversations)) : ?>
    <div class="wac-no-convs">
        <i class="fa fa-comments-o"></i>
        <p><?php echo _l('wac_no_conversations') ?: 'No conversations yet.'; ?></p>
        <small><?php echo _l('wac_no_conversations_hint') ?: 'Messages will appear here once WhatsApp messages arrive.'; ?></small>
    </div>
<?php else : ?>
    <?php foreach ($conversations as $conv) : ?>
        <?php
            // Safe extraction with defaults
            $conv_id     = (int) ($conv['id'] ?? 0);
            $phone       = $conv['phone'] ?? '';
            $phone_norm  = $conv['phone_normalized'] ?? $phone;
            $display     = !empty($conv['display_name']) ? $conv['display_name'] : $phone_norm;
            $preview     = htmlspecialchars(mb_substr($conv['last_message_text'] ?? '', 0, 55), ENT_QUOTES, 'UTF-8');
            $time_ago    = wac_time_ago($conv['last_message_at'] ?? null);
            $unread      = (int) ($conv['unread_count'] ?? 0);
            $direction   = $conv['last_direction'] ?? 'inbound';
            $is_inbound  = ($direction === 'inbound');
            $customer_id = (int) ($conv['customer_id'] ?? 0);
            $lead_id     = (int) ($conv['lead_id'] ?? 0);

            // Build CRM badge
            $crm_badge = '';
            if ($customer_id > 0) {
                $crm_badge = '<span class="wac-crm-badge wac-badge-customer" title="' . _l('wac_linked_customer') . ' #' . $customer_id . '"><i class="fa fa-user"></i></span>';
            } elseif ($lead_id > 0) {
                $crm_badge = '<span class="wac-crm-badge wac-badge-lead" title="' . _l('wac_linked_lead') . ' #' . $lead_id . '"><i class="fa fa-user-o"></i></span>';
            }

            // Avatar letter
            $avatar_letter = strtoupper(mb_substr($display, 0, 1));
            if (empty($avatar_letter) || !preg_match('/[A-Za-z0-9]/', $avatar_letter)) {
                $avatar_letter = '#';
            }
        ?>
        <div class="wac-conv-item <?php echo $unread > 0 ? 'wac-conv-unread' : ''; ?>"
             data-conv-id="<?php echo $conv_id; ?>"
             data-display-name="<?php echo htmlspecialchars($display, ENT_QUOTES, 'UTF-8'); ?>"
             data-phone="<?php echo htmlspecialchars($phone_norm, ENT_QUOTES, 'UTF-8'); ?>"
             data-customer-id="<?php echo $customer_id; ?>"
             data-lead-id="<?php echo $lead_id; ?>"
             onclick="wacLoadConversation(<?php echo $conv_id; ?>, this)">

            <div class="wac-conv-avatar">
                <?php echo $avatar_letter; ?>
            </div>

            <div class="wac-conv-info">
                <div class="wac-conv-top-row">
                    <span class="wac-conv-name">
                        <?php echo htmlspecialchars($display, ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo $crm_badge; ?>
                    </span>
                    <span class="wac-conv-time"><?php echo $time_ago; ?></span>
                </div>
                <div class="wac-conv-bottom-row">
                    <span class="wac-conv-preview">
                        <?php if (!$is_inbound) : ?>
                            <i class="fa fa-check wac-sent-tick" title="<?php echo _l('wac_sent') ?: 'Sent'; ?>"></i>
                        <?php endif; ?>
                        <?php echo $preview ?: '<em class="text-muted">' . (_l('wac_no_preview') ?: 'No message') . '</em>'; ?>
                    </span>
                    <?php if ($unread > 0) : ?>
                        <span class="wac-unread-badge"><?php echo $unread > 99 ? '99+' : $unread; ?></span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

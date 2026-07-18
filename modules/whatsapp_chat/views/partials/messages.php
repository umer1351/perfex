<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * partials/messages.php
 *
 * Renders the chat header and message bubbles for a selected conversation.
 * Expects: $conversation (array), $messages (array)
 */

// Safe extraction with defaults
$conversation = $conversation ?? [];
$messages     = $messages ?? [];

$phone        = $conversation['phone'] ?? '';
$phone_norm   = $conversation['phone_normalized'] ?? $phone;
$display_name = !empty($conversation['display_name']) ? $conversation['display_name'] : $phone_norm;
$customer_id  = (int) ($conversation['customer_id'] ?? 0);
$lead_id      = (int) ($conversation['lead_id'] ?? 0);

// Avatar letter
$avatar_letter = strtoupper(mb_substr($display_name, 0, 1));
if (empty($avatar_letter) || !preg_match('/[A-Za-z0-9]/', $avatar_letter)) {
    $avatar_letter = '#';
}

// Build CRM link badge for the header
$crm_link = '';
if ($customer_id > 0) {
    $crm_link = '<a href="' . admin_url('clients/client/' . $customer_id) . '" target="_blank" class="wac-crm-link wac-link-customer"><i class="fa fa-user"></i> ' . _l('customer') . ' #' . $customer_id . '</a>';
} elseif ($lead_id > 0) {
    $crm_link = '<a href="' . admin_url('leads/index/' . $lead_id) . '" target="_blank" class="wac-crm-link wac-link-lead"><i class="fa fa-user-o"></i> ' . _l('lead') . ' #' . $lead_id . '</a>';
}
?>

<!-- Chat Header -->
<div class="wac-chat-header-inner">
    <div class="wac-chat-header-avatar">
        <?php echo $avatar_letter; ?>
    </div>
    <div class="wac-chat-header-info">
        <div class="wac-chat-header-name"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="wac-chat-header-phone">
            <i class="fa fa-whatsapp"></i> +<?php echo htmlspecialchars($phone_norm, ENT_QUOTES, 'UTF-8'); ?>
            <?php if ($crm_link) : ?>
                &nbsp;&middot;&nbsp; <?php echo $crm_link; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Messages list -->
<div class="wac-messages-inner" id="wac-messages-inner">

<?php if (empty($messages)) : ?>
    <div class="wac-no-messages">
        <i class="fa fa-comments-o"></i>
        <p><?php echo _l('wac_no_messages') ?: 'No messages yet in this conversation.'; ?></p>
    </div>
<?php else : ?>

    <?php $prev_date = ''; ?>

    <?php foreach ($messages as $msg) : ?>
        <?php
            // Safe extraction
            $direction    = $msg['direction'] ?? 'inbound';
            $is_outbound  = ($direction === 'outbound');
            $msg_type     = $msg['message_type'] ?? 'text';
            $msg_text     = $msg['message_text'] ?? '';
            $media_url    = $msg['media_url'] ?? '';
            $sender_name  = $msg['sender_name'] ?? '';
            $sent_at      = $msg['sent_at'] ?? null;
            $is_read      = (int) ($msg['is_read'] ?? 0);

            // Handle null/empty sent_at – use Perfex timezone for display
            if (!empty($sent_at)) {
                try {
                    $tz_str   = function_exists('wac_get_timezone') ? wac_get_timezone() : (function_exists('get_option') ? get_option('timezone') : 'Asia/Dhaka');
                    $tz_str   = !empty($tz_str) ? $tz_str : 'Asia/Dhaka';
                    $dt       = new DateTime($sent_at, new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone($tz_str));
                    $msg_date = $dt->format('d M Y');
                    $msg_time = $dt->format('H:i');
                } catch (Exception $e) {
                    $msg_date = '';
                    $msg_time = '';
                }
            } else {
                $msg_date  = '';
                $msg_time  = '';
            }

            $bubble_class = $is_outbound ? 'wac-bubble wac-bubble-out' : 'wac-bubble wac-bubble-in';
            $sender       = $is_outbound
                          ? (!empty($sender_name) ? $sender_name : (_l('wac_bot') ?: 'Bot'))
                          : (!empty($sender_name) ? $sender_name : $display_name);
        ?>

        <?php if (!empty($msg_date) && $msg_date !== $prev_date) : ?>
            <div class="wac-date-divider"><span><?php echo $msg_date; ?></span></div>
            <?php $prev_date = $msg_date; ?>
        <?php endif; ?>

        <div class="wac-message-row <?php echo $is_outbound ? 'wac-row-out' : 'wac-row-in'; ?>">
            <div class="<?php echo $bubble_class; ?>">

                <?php if (!$is_outbound && !empty($sender)) : ?>
                    <span class="wac-sender-name"><?php echo htmlspecialchars($sender, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>

                <?php if ($msg_type === 'text' || empty($msg_type)) : ?>
                    <?php if (!empty($msg_text)) : ?>
                        <div class="wac-msg-text"><?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?></div>
                    <?php else : ?>
                        <div class="wac-msg-text"><em class="text-muted"><?php echo _l('wac_empty_message') ?: '(empty message)'; ?></em></div>
                    <?php endif; ?>

                <?php elseif ($msg_type === 'image' && !empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <img src="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo _l('image') ?: 'image'; ?>" class="wac-media-img" loading="lazy">
                        <?php if (!empty($msg_text)) : ?>
                            <div class="wac-msg-text"><?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?></div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($msg_type === 'audio' && !empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <audio controls src="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" preload="metadata"></audio>
                    </div>

                <?php elseif ($msg_type === 'video' && !empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <video controls src="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" class="wac-media-video" preload="metadata"></video>
                        <?php if (!empty($msg_text)) : ?>
                            <div class="wac-msg-text"><?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?></div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($msg_type === 'document' && !empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <a href="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="wac-file-link">
                            <i class="fa fa-file-pdf-o"></i> <?php echo _l('wac_document') ?: 'Document'; ?>
                        </a>
                        <?php if (!empty($msg_text)) : ?>
                            <div class="wac-msg-text"><?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?></div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($msg_type === 'location') : ?>
                    <div class="wac-msg-media">
                        <i class="fa fa-map-marker"></i> <?php echo _l('wac_location') ?: 'Location shared'; ?>
                        <?php if (!empty($msg_text)) : ?>
                            <div class="wac-msg-text"><?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?></div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($msg_type === 'sticker' && !empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <img src="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" alt="sticker" class="wac-media-sticker" loading="lazy">
                    </div>

                <?php elseif (!empty($media_url)) : ?>
                    <div class="wac-msg-media">
                        <a href="<?php echo htmlspecialchars($media_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="wac-file-link">
                            <i class="fa fa-file"></i> <?php echo ucfirst(htmlspecialchars($msg_type, ENT_QUOTES, 'UTF-8')); ?> <?php echo _l('attachment') ?: 'attachment'; ?>
                        </a>
                    </div>

                <?php else : ?>
                    <div class="wac-msg-text">
                        <?php if (!empty($msg_text)) : ?>
                            <?php echo nl2br(htmlspecialchars($msg_text, ENT_QUOTES, 'UTF-8')); ?>
                        <?php else : ?>
                            <em class="text-muted">[<?php echo ucfirst(htmlspecialchars($msg_type, ENT_QUOTES, 'UTF-8')); ?>]</em>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <span class="wac-msg-time">
                    <?php echo $msg_time; ?>
                    <?php if ($is_outbound) : ?>
                        <?php if ($is_read) : ?>
                            <span class="wac-double-check wac-read" title="<?php echo _l('wac_read') ?: 'Read'; ?>">
                                <i class="fa fa-check"></i><i class="fa fa-check"></i>
                            </span>
                        <?php else : ?>
                            <i class="fa fa-check wac-read-tick" title="<?php echo _l('wac_sent') ?: 'Sent'; ?>"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>

            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

</div>

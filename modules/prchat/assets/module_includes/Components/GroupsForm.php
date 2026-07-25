<?php $instance = &get_instance(); ?>
<form hidden enctype="multipart/form-data" name="groupFileForm" id="groupFileForm" method="post"
  onsubmit="uploadGroupFileForm(this);return false;">
  <input type="file" class="file" name="userfile" multiple required />
  <input type="submit" name="submit" class="save" value="save" />
  <input type="hidden" name="<?php echo get_instance()->security->get_csrf_token_name(); ?>"
    value="<?php echo get_instance()->security->get_csrf_hash(); ?>">
</form>

<form hidden method="post" enctype="multipart/form-data" name="groupMessagesForm" id="groupMessagesForm"
  onsubmit="return false;">
  <div class="message-input group_msg_input prchat-composer-row">
    <div class="message-actions">
      <i class="fa fa-paperclip chat-action-upload-group" data-toggle="tooltip"
        title="<?php echo _l('chat_file_upload'); ?>" aria-hidden="true"></i>
      <i class="fa fa-microphone chat-action-voice chat-action-voice-group" data-toggle="tooltip"
        title="<?php echo _l('chat_voice_message'); ?>" aria-hidden="true"></i>
      <i class="fa fa-smile chat-action-emoji" data-toggle="tooltip" title="<?php echo _l('chat_emoji'); ?>"
        aria-hidden="true"></i>
    </div>
    <div class="wrap">
      <textarea name="g_message" class="group_chatbox ays-ignore mention"
        placeholder="<?= _l('chat_type_a_message_mention'); ?>"></textarea>
      <div id="group-mention-dropdown" class="prchat-mention-dropdown" style="display:none;"></div>

      <input type="hidden" class="ays-ignore from" name="from" value="" />
      <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
      <input type="hidden" class="ays-ignore" name="<?php echo get_instance()->security->get_csrf_token_name(); ?>"
        value="<?php echo get_instance()->security->get_csrf_hash(); ?>">
    </div>
    <button class="submit enterGroupBtn" name="enterGroupBtn"><svg class="fa-paper-plane" fill="#ffffff"
        viewBox="0 0 24 24">
        <path
          d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M8,7.71V11.05L15.14,12L8,12.95V16.29L18,12L8,7.71Z" />
      </svg></button>
  </div>
</form>

<?php $instance = &get_instance(); ?>
<form hidden enctype="multipart/form-data" name="fileForm" method="post" onsubmit="uploadFileForm(this);return false;">
  <input type="file" class="file" name="userfile" multiple required />
  <input type="submit" name="submit" class="save" value="save" />
  <input type="hidden" name="<?php echo get_instance()->security->get_csrf_token_name(); ?>"
    value="<?php echo get_instance()->security->get_csrf_hash(); ?>">
</form>

<form method="post" enctype="multipart/form-data" name="pusherMessagesForm" id="pusherMessagesForm"
  onsubmit="return false;">
  <div class="message-input prchat-composer-row">
    <div class="message-actions">
      <i class="fa fa-paperclip chat-action-upload" data-toggle="tooltip" title="<?php echo _l('chat_file_upload'); ?>"
        aria-hidden="true"></i>
      <i class="fa fa-microphone chat-action-voice" data-toggle="tooltip"
        title="<?php echo _l('chat_voice_message'); ?>" aria-hidden="true"></i>
      <i class="fa fa-smile chat-action-emoji" data-toggle="tooltip" title="<?php echo _l('chat_emoji'); ?>"
        aria-hidden="true"></i>
    </div>
    <div class="wrap">
      <textarea disabled name="msg" class="chatbox ays-ignore"
        placeholder="<?= _l('chat_type_a_message'); ?>"></textarea>

      <input type="hidden" class="ays-ignore from" name="from" value="" />
      <input type="hidden" class="ays-ignore to" name="to" value="" />
      <input type="hidden" class="ays-ignore typing" name="typing" value="false" />
      <input type="hidden" class="ays-ignore" name="<?php echo get_instance()->security->get_csrf_token_name(); ?>"
        value="<?php echo get_instance()->security->get_csrf_hash(); ?>">
      <input type="hidden" class="ays-ignore has_newmessages" id="" value="false" />
    </div>
    <button class="submit enterBtn" name="enterBtn"><svg class="fa-paper-plane" fill="#ffffff" viewBox="0 0 24 24">
        <path
          d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M8,7.71V11.05L15.14,12L8,12.95V16.29L18,12L8,7.71Z" />
      </svg></button>
  </div>
</form>

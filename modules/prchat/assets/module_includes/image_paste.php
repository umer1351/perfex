<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    (function() {
        "use strict";

        class ImagePasteHandler {
            constructor() {
                this.csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
                this.csrfValue = '<?= $this->security->get_csrf_hash(); ?>';
                this.init();
            }

        init() {
            $(document).on('paste', 'textarea.message-input', (e) => {
                this.handlePaste(e, 'staff');
            });
            $(document).on('paste', 'textarea.group_chatbox', (e) => {
                this.handlePaste(e, 'group');
            });
            $(document).on('paste', 'textarea.client_chatbox', (e) => {
                this.handlePaste(e, 'client');
            });
            $(document).on('paste', 'textarea.clients_textarea', (e) => {
                this.handlePaste(e, 'client-dashboard');
            });
            $(document).on('paste', 'textarea.chatbox', (e) => {
                this.handlePaste(e, 'staff');
            });
        }

        handlePaste(event, chatType) {
            const clipboardData = event.originalEvent.clipboardData || window.clipboardData;
            if (!clipboardData) return;

            var imageFile = null;

            if (clipboardData.items) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    if (clipboardData.items[i].type.indexOf('image') !== -1) {
                        imageFile = clipboardData.items[i].getAsFile();
                        break;
                    }
                }
            }

            if (!imageFile && clipboardData.files && clipboardData.files.length > 0) {
                for (let i = 0; i < clipboardData.files.length; i++) {
                    if (clipboardData.files[i].type.indexOf('image') !== -1) {
                        imageFile = clipboardData.files[i];
                        break;
                    }
                }
            }

            if (!imageFile) return;
            event.preventDefault();

            const ext = { 'image/png': 'png', 'image/jpeg': 'jpg', 'image/gif': 'gif', 'image/webp': 'webp', 'image/bmp': 'bmp' }[imageFile.type] || 'png';
            const file = new File([imageFile], 'pasted-image-' + Date.now() + '.' + ext, { type: imageFile.type, lastModified: Date.now() });

            this.uploadPastedImage(file, chatType);
        }

            getUploadUrl(chatType) {
                switch (chatType) {
                    case 'staff':   return '<?= admin_url("prchat/Prchat_Controller/uploadMethod"); ?>';
                    case 'group':   return '<?= admin_url("prchat/Prchat_Controller/groupUploadMethod"); ?>';
                    case 'client':  return '<?= site_url("prchat/Prchat_ClientsController/uploadMethod"); ?>';
                    case 'client-dashboard': return '<?= site_url("prchat/Prchat_ClientsController/uploadMethod"); ?>';
                    default:        return '<?= admin_url("prchat/Prchat_Controller/uploadMethod"); ?>';
                }
            }

            getBasePath(chatType) {
                switch (chatType) {
                    case 'group':   return '<?= base_url("modules/prchat/uploads/groups/"); ?>';
                    case 'client':
                    case 'client-dashboard': return '<?= base_url("modules/prchat/uploads/clients/"); ?>';
                    default:        return '<?= base_url("modules/prchat/uploads/"); ?>';
                }
            }

            uploadPastedImage(file, chatType) {
                alert_float('info', '<?= _l("chat_msg_sending"); ?>');

                var formData = new FormData();
                formData.append('userfile', file);
                formData.append(this.csrfName, this.csrfValue);

                var userId = typeof userSessionId !== 'undefined' ? userSessionId : '<?= get_staff_user_id(); ?>';

                if (chatType === 'group') {
                    var groupId = $("#frame .group_messages .chat_group_messages").attr("id");
                    if (!groupId) { alert_float('warning', '<?= _l("chat_select_group_first"); ?>'); return; }
                    formData.append('send_from', userId);
                    formData.append('to_group', groupId);
                } else if (chatType === 'staff') {
                    var sentTo = $('li.contact.active').attr('id');
                    if (!sentTo) { alert_float('warning', '<?= _l("chat_select_staff_first"); ?>'); return; }
                    formData.append('send_from', userId);
                    formData.append('send_to', sentTo);
                } else if (chatType === 'client') {
                    var clientId = $('.chat_clients_list > li.contact_name.selected').attr('id');
                    if (!clientId) { alert_float('warning', '<?= _l("chat_select_client_first"); ?>'); return; }
                    formData.append('send_from', 'staff_' + userId);
                    formData.append('send_to', clientId);
                } else if (chatType === 'client-dashboard') {
                    var staffId = $('.m-area').attr('data-staffid');
                    if (!staffId) { alert_float('warning', '<?= _l("chat_select_staff_first"); ?>'); return; }
                    formData.append('from', 'client_<?= get_contact_user_id(); ?>');
                    formData.append('to', staffId);
                }

                var self = this;

                $.ajax({
                    type: 'POST',
                    url: this.getUploadUrl(chatType),
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(r) {
                        if (r.error) {
                            alert_float('danger', r.error);
                            return;
                        }
                        if (r.upload_data) {
                            var filePath = self.getBasePath(chatType) + r.upload_data.file_name;
                            self.sendFileMessage(filePath, chatType);
                        }
                    },
                    error: function() {
                        alert_float('danger', '<?= _l("chat_error_float"); ?>');
                    }
                });
            }

            sendFileMessage(filePath, chatType) {
                switch (chatType) {
                    case 'staff': {
                        var $textarea = $("#frame textarea.chatbox");
                        if (!$textarea.length) $textarea = $("#frame textarea.message-input");
                        $textarea.val(filePath);
                        $textarea.trigger($.Event('keypress', { which: 13 }));
                        break;
                    }
                    case 'group': {
                        var $groupTextarea = $("#frame textarea.group_chatbox");
                        $groupTextarea.val(filePath);
                        $groupTextarea.trigger($.Event('keypress', { which: 13 }));
                        break;
                    }
                    case 'client': {
                        var $clientTextarea = $("#frame textarea.client_chatbox");
                        $clientTextarea.val(filePath);
                        $clientTextarea.trigger($.Event('keypress', { which: 13 }));
                        break;
                    }
                    case 'client-dashboard': {
                        var $dashTextarea = $("#clientChat textarea.clients_textarea");
                        $dashTextarea.val(filePath);
                        $dashTextarea.trigger($.Event('keypress', { which: 13 }));
                        break;
                    }
                }
            }
        }

        $(document).ready(function() {
            window.imagePasteHandler = new ImagePasteHandler();
        });

    })();
</script>

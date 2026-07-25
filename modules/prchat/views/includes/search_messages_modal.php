<div class="modal right fade" id="search_messages_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl side-modal-dialog" role="any">
        <div class="modal-content side-modal-content">
            <!-- Modern Header -->
            <div class="history-modal-header">
                <div class="history-header-left">
                    <div class="history-header-icon">
                        <i class="fa fa-comments"></i>
                    </div>
                    <h4><?= _l('chat_messages_history_title'); ?></h4>
                </div>
                <button type="button" class="history-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="history-modal-body">
                <!-- Conversation Header -->
                <div class="history-convo-header">
                    <img src="<?= contact_profile_image_url($id); ?>" class="history-convo-avatar" alt="">
                    <div>
                        <strong><?= $user_full_name; ?></strong>
                        <span class="history-convo-role"><?= (strpos($id, 'client_') !== false) ? _l('chat_client') : _l('chat_staff'); ?></span>
                    </div>
                </div>

                <!-- TAB NAVIGATION -->
                <div class="history-tabs">
                    <button onclick="switchTab('messages', event)" class="history-tab-btn active" id="messages-tab">
                        <i class="fa fa-comments"></i> <?= _l('chat_messages'); ?>
                    </button>
                    <button onclick="switchTab('files', event)" class="history-tab-btn" id="files-tab">
                        <i class="fa fa-file"></i> <?= _l('chat_files_text'); ?>
                    </button>
                    <button onclick="switchTab('photos', event)" class="history-tab-btn" id="photos-tab">
                        <i class="fa fa-image"></i> <?= _l('chat_photos_text'); ?>
                    </button>
                </div>

                <!-- MESSAGES TAB -->
                <div id="messages-content" class="history-tab-panel active">
                    <div class="history-search-bar">
                        <i class="fa fa-search"></i>
                        <input type="text" placeholder="<?= _l('chat_messages_search_here'); ?>" id="search_mutual_messages" onkeydown="if(event.key === 'Tab') event.preventDefault();">
                    </div>
                    <div class="history-msg-count" id="history-msg-count-el" style="display:none;">
                        <span><?= _l('chat_total_mutual_messages_label'); ?> <strong id="history-msg-total"></strong></span>
                    </div>
                    <div class="history-messages chat_message_history_view">
                    </div>
                </div>

                <!-- FILES TAB -->
                <div id="files-content" class="history-tab-panel" style="display: none;">
                    <div class="history-search-bar">
                        <i class="fa fa-search"></i>
                        <input type="text" placeholder="<?= _l('chat_messages_search_here'); ?>" id="search_files">
                    </div>
                    <div class="files-loading" style="display: none;">
                        <div class="history-loading">
                            <i class="fa fa-spinner fa-spin"></i> <?= _l('loading'); ?>...
                        </div>
                    </div>
                    <div class="shared-files-container">
                    </div>
                </div>

                <!-- PHOTOS TAB -->
                <div id="photos-content" class="history-tab-panel" style="display: none;">
                    <div class="history-search-bar">
                        <i class="fa fa-search"></i>
                        <input type="text" placeholder="<?= _l('chat_messages_search_here'); ?>" id="search_photos">
                    </div>
                    <div class="photos-loading" style="display: none;">
                        <div class="history-loading">
                            <i class="fa fa-spinner fa-spin"></i> <?= _l('loading'); ?>...
                        </div>
                    </div>
                    <div class="shared-photos-container">
                    </div>
                </div>

                <?php if ($messages !== 'null' && is_admin()) : ?>
                    <div class="history-modal-actions">
                        <button class="history-action-btn danger" id="deleteConversation" onClick="deleteConversation(<?php echo $id; ?>)">
                            <i class="fa fa-trash"></i> <?= _l("chat_delete_conversation"); ?>
                        </button>
                        <a class="history-action-btn primary" id="exportConversation" href="<?= admin_url('prchat/Prchat_Controller/exportCSV?user=' . $id); ?>">
                            <i class="fa fa-download"></i> <?= _l("chat_export_messages_label"); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict";

        var messages_history;
        try {
            messages_history = JSON.parse('<?= $messages; ?>');
        } catch(e) {
            messages_history = null;
        }
        var messageHistoryParent = $('.chat_message_history_view');
        var currentUserId = '<?= get_staff_user_id(); ?>';
        var selectedUserId = '<?= $id; ?>';
        var totalCount = <?= $this->session->userdata('chat_messages_count') ?: 0; ?>;

        var sharedFiles = [];
        var sharedPhotos = [];
        var filteredFiles = [];
        var filteredPhotos = [];

        if (!$.isEmptyObject(messages_history)) {
            // Show message count
            $('#history-msg-count-el').show();
            $('#history-msg-total').text(totalCount);

            renderMessagesToView(messages_history);

            $('body').on('keydown', '#search_mutual_messages', _debounce(function() {
                var input = $.trim($(this).val());
                var render = [];

                if (input.length) {
                    for (var i = 0; i < messages_history.length; i++) {
                        if (messages_history[i].message.toLowerCase().indexOf(input.toLowerCase()) !== -1 &&
                            !render.includes(messages_history[i])) {
                            render.push(messages_history[i]);
                        }
                    }
                    renderMessagesToView(render);
                } else {
                    renderMessagesToView(messages_history);
                }
            }, 300));
        } else {
            messageHistoryParent.html('<div class="history-empty-tab"><i class="fa fa-comments"></i><p><?= _l("chat_sorry_no_data"); ?></p></div>');
            $('#deleteConversation, #exportConversation').remove();
        }

        function renderMessagesToView(messages) {
            var html = '';

            messages.sort(function(a, b) {
                return new Date(b.time_sent) - new Date(a.time_sent);
            });

            $.each(messages, function(i, msg) {
                var sender_fullname = msg.sender_fullname ? msg.sender_fullname : msg.contact_fullname;
                var processedMessage = processHistoryMessage(msg.message);
                var isOwn = (msg.sender_id === userSessionId || msg.sender_id == 'staff_' + userSessionId);
                var avatarSrc = msg.user_image_path || '';

                html += '<div class="history-msg' + (isOwn ? ' own' : '') + '">';
                html += '<div class="history-msg-avatar">';
                html += '<img src="' + avatarSrc + '" alt="" onerror="this.src=\'<?= base_url("assets/images/user-placeholder.jpg"); ?>\'">';
                html += '</div>';
                html += '<div class="history-msg-body">';
                html += '<div class="history-msg-meta">';
                html += '<span class="history-msg-sender">' + sender_fullname + '</span>';
                html += '<span class="history-msg-time">' + moment(msg.time_sent, "YYYYMMDD h:mm:ss").fromNow() + '</span>';
                html += '</div>';
                html += '<div class="history-msg-text">' + processedMessage + '</div>';
                html += '</div>';
                html += '</div>';
            });

            if (html === '') {
                messageHistoryParent.html('<div class="history-empty-tab"><i class="fa fa-search"></i><p><?= _l("chat_sorry_no_data"); ?></p></div>');
                return;
            }

            messageHistoryParent.html(html);
        }

        function processHistoryMessage(message) {
            if (!message) return '';

            var decoded = message;
            if (decoded.includes('&amp;') || decoded.includes('&lt;') || decoded.includes('&gt;') || decoded.includes('&#039;') || decoded.includes('&quot;') || decoded.includes('&bull;')) {
                decoded = decoded.replace(/&amp;/g, '&');
                decoded = decoded.replace(/&lt;/g, '<')
                    .replace(/&gt;/g, '>')
                    .replace(/&#039;/g, "'")
                    .replace(/&quot;/g, '"')
                    .replace(/&bull;/g, '\u2022');
            }
            decoded = decoded.replace(/&bull;/g, '\u2022');

            var callMatch = decoded.match(/^(Voice call|Video call)\s*[•·\u2022]\s*(.+)$/i);
            if (callMatch) {
                var callType = callMatch[1];
                var duration = callMatch[2];
                var icon = callType.toLowerCase().includes('video') ? 'fa-video-camera' : 'fa-phone';
                return '<span class="history-call-msg"><i class="fa ' + icon + '"></i> ' + callType + ' &bull; ' + duration + '</span>';
            }

            if (decoded.includes("<a") || decoded.includes("<img")) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = decoded;
                tempDiv.querySelectorAll('script, style, iframe, object, embed').forEach(function(el) { el.remove(); });

                var fileLink = tempDiv.querySelector('[data-file-url], a[href*="modules/prchat/uploads"]');
                if (fileLink) {
                    var filePath = fileLink.getAttribute('data-file-url') || fileLink.getAttribute('href');
                    if (filePath && filePath.includes('modules/prchat/uploads')) {
                        return renderFileMessage(filePath);
                    }
                }

                if (window.CustomEmoji) {
                    var customEmoji = new window.CustomEmoji();
                    var html = tempDiv.innerHTML;
                    html = customEmoji.shortcodesToEmojis(html);
                    return html;
                }
                return tempDiv.innerHTML;
            }

            if (window.CustomEmoji) {
                var customEmoji = new window.CustomEmoji();
                decoded = customEmoji.shortcodesToEmojis(decoded);
            }

            var content = decoded.replace(/\n/g, '<br>');

            if (content.includes('/modules/prchat/uploads/') && !content.includes('<')) {
                return renderFileMessage(content.trim());
            }

            content = makeLinksClickable(content);
            return content;
        }

        function renderFileMessage(filePath) {
            if (isImageFile(filePath)) {
                return '<img src="' + filePath + '" alt="Image" class="history-img-preview" onclick="openImageModal(\'' + filePath + '\', \'Image\')">';
            } else {
                var fileName = filePath.split('/').pop();
                return '<a href="' + filePath + '" target="_blank" class="history-file-link"><i class="fa fa-file"></i> ' + fileName + '</a>';
            }
        }

        function makeLinksClickable(text) {
            var urlRegex = /(https?:\/\/[^\s]+)/g;
            return text.replace(urlRegex, function(url) {
                if (url.match(/\.(png|jpg|jpeg|gif|webp)$/i)) {
                    return '<img src="' + url + '" alt="Image" class="history-img-preview" onclick="openImageModal(\'' + url + '\', \'Image\')">';
                }
                return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
            });
        }

        // Tab switching
        window.switchTab = function(tabName, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            $('.history-tab-btn').removeClass('active');
            $('.history-tab-panel').removeClass('active').hide();

            $('#' + tabName + '-tab').addClass('active');
            $('#' + tabName + '-content').addClass('active').show();

            if (tabName === 'files' && sharedFiles.length === 0) {
                loadSharedFiles();
            } else if (tabName === 'photos' && sharedPhotos.length === 0) {
                loadSharedFiles();
            }
        };

        $('#search_mutual_messages').on('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                return false;
            }
        });

        // Shared files loading
        function loadSharedFiles() {
            $('.files-loading, .photos-loading').show();

            var contactIdForFiles = selectedUserId;
            if (selectedUserId.indexOf('client_') === 0) {
                contactIdForFiles = selectedUserId.replace('client_', '');
            }

            $.post(prchatSettings.getSharedFiles, {
                own_id: currentUserId,
                contact_id: contactIdForFiles
            }).done(function(response) {
                try {
                    var htmlString = typeof response === 'string' ? JSON.parse(response) : response;
                    parseSharedFiles(htmlString);
                } catch (e) {
                    parseSharedFiles(response);
                }
            }).fail(function(xhr, status, error) {
                console.error('Error loading shared files:', status, error);
            }).always(function() {
                $('.files-loading, .photos-loading').hide();
            });
        }

        function parseSharedFiles(htmlString) {
            sharedFiles = [];
            sharedPhotos = [];

            if (!htmlString || htmlString.trim() === '') {
                filteredFiles = [...sharedFiles];
                filteredPhotos = [...sharedPhotos];
                renderFiles();
                renderPhotos();
                return;
            }

            var tempDiv = $('<div>').html(htmlString);

            var photoLinks = tempDiv.find('#photos a[href*=".jpg"], #photos a[href*=".jpeg"], #photos a[href*=".png"], #photos a[href*=".gif"], #photos a[href*=".webp"]');
            photoLinks.each(function() {
                var href = $(this).attr('href');
                if (href && href.includes('/modules/prchat/uploads/')) {
                    var fileName = href.split('/').pop();
                    if (isImageFile(fileName)) {
                        sharedPhotos.push({ file_name: fileName, url: href });
                    }
                }
            });

            var fileLinks = tempDiv.find('#files a[href*="/modules/prchat/uploads/"]');
            fileLinks.each(function() {
                var href = $(this).attr('href');
                if (href && href.includes('/modules/prchat/uploads/')) {
                    var fileName = href.split('/').pop();
                    if (!isImageFile(fileName)) {
                        sharedFiles.push({ file_name: fileName, url: href });
                    }
                }
            });

            filteredFiles = [...sharedFiles];
            filteredPhotos = [...sharedPhotos];
            renderFiles();
            renderPhotos();
        }

        function isImageFile(fileName) {
            var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'swf', 'bmp', 'webp', 'svg'];
            var extension = fileName.split('.').pop().toLowerCase();
            return imageExtensions.includes(extension);
        }

        function renderFiles() {
            var container = $('.shared-files-container');

            if (filteredFiles.length === 0) {
                container.html('<div class="history-empty-tab"><i class="fa fa-file"></i><p><?= _l("chat_no_files_shared"); ?></p></div>');
                return;
            }

            var html = '<div class="history-msg-count"><span><strong>' + filteredFiles.length + '</strong> <?= _l("chat_files_text"); ?></span></div>';
            html += '<div class="shared-files-list">';

            filteredFiles.forEach(function(file) {
                var fileIcon = getFileIcon(file.file_name);
                var fileExt = getFileExtension(file.file_name).toUpperCase();

                html += '<div class="shared-file-item">';
                html += '<div class="file-icon"><i class="' + fileIcon + '"></i></div>';
                html += '<div class="file-details">';
                html += '<div class="file-name">' + file.file_name + '</div>';
                html += '<div class="file-meta">' + fileExt + '</div>';
                html += '</div>';
                html += '<div class="file-actions">';
                html += '<a href="' + file.url + '" target="_blank" class="file-action-btn" data-toggle="tooltip" title="<?= _l("chat_open_file"); ?>"><i class="fa fa-external-link"></i></a>';
                html += '<a href="' + file.url + '" download="' + file.file_name + '" class="file-action-btn" data-toggle="tooltip" title="<?= _l("chat_download_file"); ?>"><i class="fa fa-download"></i></a>';
                html += '</div></div>';
            });

            html += '</div>';
            container.html(html);
        }

        function renderPhotos() {
            var container = $('.shared-photos-container');

            if (filteredPhotos.length === 0) {
                container.html('<div class="history-empty-tab"><i class="fa fa-image"></i><p><?= _l("chat_no_photos_shared"); ?></p></div>');
                return;
            }

            var html = '<div class="history-msg-count"><span><strong>' + filteredPhotos.length + '</strong> <?= _l("chat_photos_shared"); ?></span></div>';
            html += '<div class="photos-grid">';

            filteredPhotos.forEach(function(photo) {
                html += '<div class="photo-item" onclick="openImageModal(\'' + photo.url + '\', \'' + photo.file_name + '\')">';
                html += '<div class="photo-thumbnail" style="background-image: url(' + photo.url + ')"></div>';
                html += '<div class="photo-overlay"><i class="fa fa-search-plus"></i></div>';
                html += '</div>';
            });

            html += '</div>';
            container.html(html);
        }

        function getFileIcon(fileName) {
            var extension = getFileExtension(fileName).toLowerCase();
            var iconMap = {
                pdf: 'fa fa-file-pdf', doc: 'fa fa-file-word', docx: 'fa fa-file-word',
                xls: 'fa fa-file-excel', xlsx: 'fa fa-file-excel',
                ppt: 'fa fa-file-powerpoint', pptx: 'fa fa-file-powerpoint',
                txt: 'fa fa-file-text', zip: 'fa fa-file-archive', rar: 'fa fa-file-archive',
                mp3: 'fa fa-file-audio', mp4: 'fa fa-file-video', avi: 'fa fa-file-video'
            };
            return iconMap[extension] || 'fa fa-file';
        }

        function getFileExtension(fileName) {
            return fileName.split('.').pop() || '';
        }

        // File search
        $('#search_files').on('input', function() {
            var query = $(this).val().toLowerCase();
            filteredFiles = query ? sharedFiles.filter(function(f) { return f.file_name.toLowerCase().includes(query); }) : [...sharedFiles];
            renderFiles();
        });

        // Photo search
        $('#search_photos').on('input', function() {
            var query = $(this).val().toLowerCase();
            filteredPhotos = query ? sharedPhotos.filter(function(p) { return p.file_name.toLowerCase().includes(query); }) : [...sharedPhotos];
            renderPhotos();
        });

        // ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#search_messages_modal').hasClass('in')) {
                $('#search_messages_modal').modal('hide');
            }
        });

        $('#search_messages_modal').on('show.bs.modal', function() {
            setTimeout(function() {
                $('#search_mutual_messages').focus();
                loadSharedFiles();
            }, 500);
        });
    })(jQuery);

    function deleteConversation(id) {
        var table = '';
        var actualId = id;

        if (typeof(id) == 'string' && id.indexOf('client_') === 0) {
            table = 'chatclientmessages';
            actualId = id.replace('client_', '');
        } else if (typeof(id) == 'number' || (typeof(id) == 'string' && !isNaN(id))) {
            table = 'chatmessages';
            actualId = id;
        } else if (typeof(id) == 'object') {
            table = 'chatclientmessages';
            actualId = $(id).children('li.selected').attr('id') || $(id).attr('id');
            if (actualId && actualId.indexOf('client_') === 0) {
                actualId = actualId.replace('client_', '');
            }
        }

        var lang_confirm_deletion = confirm("<?= _l('chat_are_you_sure_delete_conversation'); ?>");
        var hreff = $('#exportConversation').attr('href');

        if (lang_confirm_deletion) {
            if (confirm("<?= _l('chat_do_you_want_to_export'); ?>")) {
                $.ajax({
                    url: hreff,
                    method: 'GET',
                    xhrFields: { responseType: 'blob' },
                    success: function(data) {
                        let currDate = new Date().toDateString().replace(/\s/g, '');
                        let a = document.createElement('a');
                        let url = window.URL.createObjectURL(data);
                        a.href = url;
                        a.download = 'messages_' + currDate + '.csv';
                        document.body.append(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        _deleteUserMessages(actualId, table);
                    }
                });
            } else {
                _deleteUserMessages(actualId, table);
            }
        }

        function _deleteUserMessages(id, table) {
            $.post('deleteChatConversation', {
                id: id,
                table: table,
                beforeSend: function() {
                    $('#deleteConversation').attr('disabled', true);
                    $('#deleteConversation').html("<?= _l('chat_deleting_message') ?> <i class='fa fa-refresh fa-spin fa-fw'></i>");
                },
            }).done(function(r) {
                if (r.success == true) {
                    setTimeout(function() {
                        alert_float('success', "<?= _l('chat_conversation_deleted'); ?>");
                        location.reload();
                    }, 2000);
                }
            });
        }
    }
</script>

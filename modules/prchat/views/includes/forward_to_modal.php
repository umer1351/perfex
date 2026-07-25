<div class="modal fade" id="forwardToModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="any" style="max-width: 600px;">
        <div class="modal-content chat-modal">
            <div class="chat-modal-header">
                <h4><i class="fa fa-share"></i> <?= _l('chat_forward_message_btn') ?></h4>
                <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
            </div>
            <div class="chat-modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div>
                    <div class="form-group">
                        <label for="_searchUsers"><?= _l('chat_search_users') ?></label>
                        <input type="text" id="_searchUsers" placeholder="<?= _l('kb_search') ?>..." class="form-control">
                    </div>

                    <!-- Staff Members Section -->
                    <div class="forward-section">
                        <div class="section-header">
                            <i class="fa fa-users text-primary"></i>
                            <strong><?= _l('staff_members') ?></strong>
                        </div>
                        <ul class="staffList"></ul>
                    </div>

                    <!-- Customers Section -->
                    <div class="forward-section">
                        <div class="section-header">
                            <i class="fa fa-user text-success"></i>
                            <strong><?= _l('clients') ?></strong>
                        </div>
                        <ul class="clientsList"></ul>
                    </div>

                    <!-- Groups Section -->
                    <div class="forward-section">
                        <div class="section-header">
                            <i class="fa fa-comments text-warning"></i>
                            <strong><?= _l('chat_groups_text') ?></strong>
                        </div>
                        <ul class="groupsList">
                            <?php
                            if (is_array($groups) && !empty($groups)) {
                                foreach ($groups as $group) : ?>
                                    <li class="group-item" id="<?= $group['group_id'] ?>">
                                        <div class="user-info">
                                            <img class="user-avatar" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='512' height='512' viewBox='0 0 512 512'%3E%3Cpath fill='%234f46e5' d='M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256 256-114.6 256-256S397.4 0 256 0zm0 96c35.3 0 64 28.7 64 64s-28.7 64-64 64-64-28.7-64-64 28.7-64 64-64zm128 320c0 17.7-14.3 32-32 32H160c-17.7 0-32-14.3-32-32v-19.2c0-26.4 21.4-47.8 47.8-47.8h.4c12.3 0 23.5-4.6 32-12.2 8.5 7.6 19.7 12.2 32 12.2h64c12.3 0 23.5-4.6 32-12.2 8.5 7.6 19.7 12.2 32 12.2h.4c26.4 0 47.8 21.4 47.8 47.8V416z'/%3E%3C/svg%3E" data-toggle="tooltip" data-title="<?= $group['group_name'] ?>">
                                            <div class="user-details">
                                                <span class="user-name"><?= $group['group_name']; ?></span>
                                                <small class="text-muted">Group Chat</small>
                                            </div>
                                        </div>
                                        <button class="btn btn-warning btn-sm send-btn" onClick="_forwardTo(<?= $group['group_id'] ?>, this,'groups')"><?= _l('send') ?></button>
                                    </li>
                            <?php endforeach;
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="chat-modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    var _fwdSendLabel = <?= json_encode(_l('send')) ?>;
    var _fwdSiteUrl = '<?= site_url() ?>';
    var _fwdAdminUrl = '<?= admin_url() ?>';

    function _fwdRenderStaff(items, $list) {
        $list.empty();
        if (!items || !items.length) return;
        for (var i = 0; i < items.length; i++) {
            var s = items[i];
            var li = document.createElement('li');
            li.className = '_user staff-item';
            li.id = s.id;
            var info = document.createElement('div');
            info.className = 'user-info';
            var details = document.createElement('div');
            details.className = 'user-details';
            var nameEl = document.createElement('span');
            nameEl.className = 'user-name';
            nameEl.textContent = s.name;
            details.appendChild(nameEl);
            if (s.subtext) {
                var sub = document.createElement('small');
                sub.className = 'user-role text-muted';
                sub.textContent = s.subtext;
                details.appendChild(sub);
            }
            info.appendChild(details);
            li.appendChild(info);
            var btn = document.createElement('button');
            btn.className = 'btn btn-primary btn-sm send-btn';
            btn.textContent = _fwdSendLabel;
            btn.setAttribute('data-staff-id', s.id);
            btn.onclick = (function(sid, el) { return function() { _forwardTo(sid, el, null); }; })(s.id, btn);
            li.appendChild(btn);
            $list.append(li);
        }
    }

    function _fwdRenderClients(items, $list) {
        $list.empty();
        if (!items || !items.length) return;
        for (var i = 0; i < items.length; i++) {
            var c = items[i];
            var li = document.createElement('li');
            li.className = '_user client-item';
            li.id = 'client_' + c.id;
            var info = document.createElement('div');
            info.className = 'user-info';
            var details = document.createElement('div');
            details.className = 'user-details';
            var nameEl = document.createElement('span');
            nameEl.className = 'user-name';
            nameEl.textContent = c.name;
            details.appendChild(nameEl);
            if (c.subtext) {
                var sub = document.createElement('small');
                sub.className = 'user-company text-muted';
                sub.textContent = c.subtext;
                details.appendChild(sub);
            }
            info.appendChild(details);
            li.appendChild(info);
            var btn = document.createElement('button');
            btn.className = 'btn btn-success btn-sm send-btn';
            btn.textContent = _fwdSendLabel;
            btn.setAttribute('data-client-id', c.id);
            btn.onclick = (function(cid, el) { return function() { _forwardTo('client_' + cid, el, 'client'); }; })(c.id, btn);
            li.appendChild(btn);
            $list.append(li);
        }
    }

    function _fwdLoadInitial() {
        $.getJSON(prchatSettings.ajaxSearchStaff, function(data) {
            _fwdRenderStaff(data, $('#forwardToModal .staffList'));
        });
        $.getJSON(prchatSettings.ajaxSearchClients, function(data) {
            _fwdRenderClients(data, $('#forwardToModal .clientsList'));
        });
    }

    $('#forwardToModal').on('show.bs.modal', function() {
        $(this).find('.loaderParent').remove();
        $('#_searchUsers').val('');
        _fwdLoadInitial();
        $('#forwardToModal .forward-section').show();
    });

    $("body").on("keyup", '#_searchUsers', _debounce(function() {
        var value = $.trim($(this).val());

        if (value === '') {
            _fwdLoadInitial();
            $('#forwardToModal .forward-section').show();
            return;
        }

        $.getJSON(prchatSettings.ajaxSearchStaff, { q: value }, function(data) {
            _fwdRenderStaff(data, $('#forwardToModal .staffList'));
            $('#forwardToModal .staffList').closest('.forward-section').toggle(data.length > 0);
        });
        $.getJSON(prchatSettings.ajaxSearchClients, { q: value }, function(data) {
            _fwdRenderClients(data, $('#forwardToModal .clientsList'));
            $('#forwardToModal .clientsList').closest('.forward-section').toggle(data.length > 0);
        });

        // Filter groups client-side (always fully loaded)
        var lowerVal = value.toLowerCase();
        $("#forwardToModal .groupsList li").each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(lowerVal) > -1);
        });
    }, 400));

    function _forwardTo(receiver_id, el, target) {
        $(el).attr('disabled', true);

        let message = $('#forwardToModal').find('._dataMessage').first().data('message');
        let messageEscaped = $('#forwardToModal').find('._dataMessage.escaped').data('message-escaped');
        message = message.replace(/"/g, "'").replace('controls="', 'controls ');

        // Check for file URLs in the original message
        if (message && message.includes('/modules/prchat/uploads/')) {
            // Try to extract the URL from the HTML first
            var urlMatch = message.match(/href=['"']([^'"]*\/modules\/prchat\/uploads\/[^'"]*)['"']/);
            var fileUrl = '';

            if (urlMatch) {
                // URL found in HTML link
                fileUrl = urlMatch[1];
            } else {
                // Check if message is just a raw URL
                var rawUrlMatch = message.match(/(https?:\/\/[^'"\s,]*\/modules\/prchat\/uploads\/[^'"\s,]*\.[a-zA-Z0-9]+)/);
                if (rawUrlMatch) {
                    fileUrl = rawUrlMatch[1];
                } else {
                    // Check for relative URL without protocol
                    var relativeUrlMatch = message.match(/([^'"\s,]*\/modules\/prchat\/uploads\/[^'"\s,]*\.[a-zA-Z0-9]+)/);
                    if (relativeUrlMatch) {
                        fileUrl = relativeUrlMatch[1];
                    }
                }
            }

            if (fileUrl) {
                var filename = fileUrl.replace(/^.*[\\\/]/, '');
                if (filename.match(/\.(gif|jpg|jpeg|png|swf|PNG|JPG|JPEG)$/i)) {
                    messageEscaped = "<?= _l('chat_photos_text'); ?>";
                } else {
                    messageEscaped = filename;
                }
                message = fileUrl; // Use the full URL for sending
            }
        }

        // Decode HTML entities for non-file messages
        if (!message.includes('/modules/prchat/uploads/')) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = message;
            message = tempDiv.textContent || tempDiv.innerText || '';
        }

        if (message.match('<audio controls')) {
            message = message.replace(/'/g, '"');
            messageEscaped = "<?= _l('chat_new_audio_message_sent'); ?>";
        }

        if (message.match('data-video-embed=')) {
            message = message.match(/href='([^']*)/)[1];
            // Check if this is a video URL for preview
            if (isVideoUrl(message)) {
                messageEscaped = "Video";
            }
        }

        if (message.match("class='prchat_convertedImage'")) {
            message = message.match(/href='([^']*)/)[1];
            messageEscaped = "<?= _l('chat_new_file_sent'); ?>";
        }

        if (target === null) {
            // Send to staff
            $.post(prchatSettings.serverPath, {
                from: userSessionId,
                to: receiver_id,
                msg: message,
                typing: false
            }).done(function() {
                $(el).text('Sent');
                $(el).removeClass('btn-primary').addClass('btn-success');
            }).fail(function() {
                $(el).attr('disabled', false);
                $(el).text('Send');
                $(el).removeClass('disabled');
            });

            $('li.contact#' + receiver_id + ' a .meta p.preview').html("<?= _l('chat_message_you') ?>" + ' ' + messageEscaped);

            // Check if time_ago element exists, if not create it
            var timeAgoElement = $('li.contact#' + receiver_id + ' a .meta .pull-right.time_ago');
            if (timeAgoElement.length === 0) {
                // Create time_ago element if it doesn't exist
                $('li.contact#' + receiver_id + ' a .meta .meta-row-preview').append('<p class="pull-right time_ago">' + moment().format(prchatSettings.timeFormat) + '</p>');
            } else {
                // Update existing time_ago element
                timeAgoElement.html(moment().format(prchatSettings.timeFormat));
            }
        }

        if (target == 'client') {
            // Send to contact - use the correct client messaging endpoint and parameters
            var contact_id = receiver_id.replace('client_', '');
            var contactElement = $(el).parent('li');

            // Get contact name without the Send button text
            var contactNameElement = contactElement.clone();
            contactNameElement.find('button').remove(); // Remove the Send button
            var contactName = contactNameElement.text().trim();

            var contactNameParts = contactName.match(/^(.+?)\s*\(([^)]+)\)$/);
            var contact_full_name = contactNameParts ? contactNameParts[1] : contactName;
            var company = contactNameParts ? contactNameParts[2] : '';
            var client_userid = contactElement.data('client-userid');

            var requestData = {
                from: 'staff_' + userSessionId,
                to: 'client_' + contact_id,
                client_message: message,
                client_id: contact_id, // This is actually the contact ID
                contact_full_name: contact_full_name,
                company: company,
                typing: false
            };


            $.post(prchatSettings.clientsMessagesPath, requestData).done(function(response) {
                // Update contact chat UI if visible
                if ($('#client_' + contact_id).length > 0) {
                    $('#client_' + contact_id + ' .meta p.preview').html("<?= _l('chat_message_you') ?>" + ' ' + messageEscaped);
                    var timeAgoElement = $('#client_' + contact_id + ' .meta .pull-right.time_ago');
                    if (timeAgoElement.length === 0) {
                        $('#client_' + contact_id + ' .meta').append('<p class="pull-right time_ago">' + moment().format(prchatSettings.timeFormat) + '</p>');
                    } else {
                        timeAgoElement.html(moment().format(prchatSettings.timeFormat));
                    }
                }
                $(el).text('Sent');
                $(el).removeClass('btn-success').addClass('btn-success');
            }).fail(function() {
                $(el).attr('disabled', false);
                $(el).text('Send');
                $(el).removeClass('disabled');
            });
        }

        if (target == 'groups') {

            let group_id = $(el).parent('li').attr('id');

            $.post(prchatSettings.groupMessagePath, {
                from: userSessionId,
                group_id: group_id,
                g_message: message,
                typing: false
            }).done(function() {
                $(el).text('Sent');
                $(el).removeClass('btn-warning').addClass('btn-success');
            }).fail(function() {
                $(el).attr('disabled', false);
                $(el).text('Send');
                $(el).removeClass('disabled');
            });

        }
    }
</script>

<style>
    #forwardToModal .loaderParent {
        display: flex;
        justify-content: center;
    }

    #forwardToModal .loading {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: forward_spin 2s linear infinite;
    }

    #forwardToModal .chat-modal-body > div {
        margin-top: 0;
        padding: 0;
    }

    /* Forward Section Styling */
    #forwardToModal .forward-section {
        margin-bottom: 25px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: #fafafa;
        overflow: hidden;
    }

    #forwardToModal .section-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #forwardToModal .section-header i {
        font-size: 16px;
    }

    #forwardToModal .section-header strong {
        font-size: 14px;
        color: #495057;
    }

    /* List Styling */
    #forwardToModal ul.staffList,
    #forwardToModal ul.clientsList,
    #forwardToModal ul.groupsList {
        margin: 0;
        padding: 0;
        list-style: none;
        max-height: none;
        background: white;
    }

    /* User Item Styling */
    #forwardToModal li._user,
    #forwardToModal li.staff-item,
    #forwardToModal li.client-item,
    #forwardToModal li.group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.2s ease;
        list-style: none;
    }

    #forwardToModal li._user:hover,
    #forwardToModal li.staff-item:hover,
    #forwardToModal li.client-item:hover,
    #forwardToModal li.group-item:hover {
        background: #f8f9fa;
        transform: translateX(2px);
    }

    #forwardToModal li._user:last-child,
    #forwardToModal li.staff-item:last-child,
    #forwardToModal li.client-item:last-child,
    #forwardToModal li.group-item:last-child {
        border-bottom: none;
    }

    /* User Info Section */
    #forwardToModal .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    #forwardToModal .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
        transition: border-color 0.2s ease;
    }

    #forwardToModal li:hover .user-avatar {
        border-color: #007bff;
    }

    #forwardToModal .user-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    #forwardToModal .user-name {
        font-weight: 500;
        color: #212529;
        font-size: 14px;
        line-height: 1.2;
    }

    #forwardToModal .user-role,
    #forwardToModal .user-company {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.2;
    }

    /* Send Button Styling */
    #forwardToModal .send-btn {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        min-width: 60px;
    }

    #forwardToModal .send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Color coding for different sections */
    #forwardToModal .staff-item .send-btn {
        background: #007bff;
        border-color: #007bff;
    }

    #forwardToModal .staff-item .send-btn:hover {
        background: #0056b3;
        border-color: #0056b3;
    }

    #forwardToModal .client-item .send-btn {
        background: #28a745;
        border-color: #28a745;
    }

    #forwardToModal .client-item .send-btn:hover {
        background: #1e7e34;
        border-color: #1e7e34;
    }

    #forwardToModal .group-item .send-btn {
        background: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    #forwardToModal .group-item .send-btn:hover {
        background: #e0a800;
        border-color: #e0a800;
        color: #212529;
    }

    /* Disabled send button styling */
    #forwardToModal .send-btn:disabled,
    #forwardToModal .send-btn.disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    #forwardToModal .send-btn.btn-success {
        background: #28a745 !important;
        border-color: #28a745 !important;
        color: white !important;
    }

    /* Search Input Styling */
    #forwardToModal #_searchUsers {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 10px 12px;
        font-size: 14px;
        transition: border-color 0.2s ease;
    }

    #forwardToModal #_searchUsers:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    /* Modal Styling */
    #forwardToModal.modal {
        z-index: 99999999999999999999999;
    }


    /* Scrollbar Styling */
    #forwardToModal ul::-webkit-scrollbar {
        width: 6px;
    }

    #forwardToModal ul::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #forwardToModal ul::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    #forwardToModal ul::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Legacy compatibility for dynamically added items */
    #forwardToModal ul.staffList img._imageAppended {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
        margin-right: 12px;
    }

    #forwardToModal ul.staffList li._appended {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.2s ease;
    }

    @keyframes forward_spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

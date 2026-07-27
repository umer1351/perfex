/**
 * PRChat Lightweight Mentions System
 *
 * Reusable @mentions for both staff and client chat.
 * Dependencies: jQuery
 *
 * Usage:
 *   var mentions = new PrchatMentions({
 *       input: '#myTextarea',
 *       dropdown: '#myDropdown',
 *       members: [{ id: 1, name: 'John Doe', avatar: 'url', type: 'staff' }],
 *       labels: { staff: 'Staff', contact: 'Client' },
 *       placeholder: 'assets/images/user-placeholder.jpg',
 *       onSend: function(message) { ... }
 *   });
 *
 *   mentions.setMembers(newMembersArray);
 */
(function($, window) {
    "use strict";

    function PrchatMentions(options) {
        this.opts = $.extend({
            input: null,
            dropdown: null,
            members: [],
            labels: { staff: 'Staff', contact: 'Client' },
            placeholder: 'assets/images/user-placeholder.jpg',
            onSend: null
        }, options);

        this.$input = $(this.opts.input);
        this.$dropdown = $(this.opts.dropdown);
        this.members = this.opts.members || [];
        this.active = false;
        this.query = '';
        this.startPos = -1;
        this.selectedIdx = 0;
        this.filtered = [];

        this._init();
    }

    PrchatMentions.prototype = {
        _init: function() {
            var self = this;

            // Input event for @ detection
            this.$input.on('input.prchatMentions', function() {
                self._onInput();
            });

            // Keydown for navigation and preventing send during mention selection
            this.$input.on('keydown.prchatMentions', function(e) {
                if (self.active) {
                    if (e.which === 40) { // Down
                        e.preventDefault();
                        self._moveSelection(1);
                    } else if (e.which === 38) { // Up
                        e.preventDefault();
                        self._moveSelection(-1);
                    } else if (e.which === 13 || e.which === 9) { // Enter or Tab
                        if (self.filtered.length > 0) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            self._selectCurrent();
                            return false;
                        }
                    } else if (e.which === 27) { // Escape
                        self._hide();
                    }
                }
            });

            // Click on dropdown item
            this.$dropdown.on('click', 'li', function() {
                var name = $(this).data('name');
                if (name) self._insertMention(name);
            });

            // Hide dropdown on click outside
            $(document).on('mousedown.prchatMentions', function(e) {
                if (!$(e.target).closest(self.$dropdown).length && !$(e.target).is(self.$input)) {
                    self._hide();
                }
            });
        },

        setMembers: function(members) {
            this.members = members || [];
        },

        isActive: function() {
            return this.active;
        },

        _onInput: function() {
            var val = this.$input.val();
            var pos = this.$input[0].selectionStart;
            var textBefore = val.substring(0, pos);

            // Find the last @ that is at start or preceded by whitespace
            var atIdx = textBefore.lastIndexOf('@');
            if (atIdx >= 0 && (atIdx === 0 || /\s/.test(textBefore.charAt(atIdx - 1)))) {
                var query = textBefore.substring(atIdx + 1);
                // Only trigger if query is reasonable (no spaces, not too long)
                if (query.indexOf(' ') === -1 && query.length <= 25) {
                    this.startPos = atIdx;
                    this.query = query;
                    this._showDropdown(query);
                    return;
                }
            }
            this._hide();
        },

        _showDropdown: function(query) {
            var self = this;
            this.filtered = this.members.filter(function(m) {
                return m.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            });

            if (this.filtered.length === 0) {
                this._hide();
                return;
            }

            this.selectedIdx = 0;
            var html = '';
            this.filtered.forEach(function(m, idx) {
                var isEveryone = m.id === 'everyone';
                var typeClass = isEveryone ? 'type-everyone' : (m.type === 'staff' ? 'type-staff' : 'type-contact');
                var typeLabel = isEveryone ? 'All' : (m.type === 'staff' ? self.opts.labels.staff : self.opts.labels.contact);
                var avatarHtml = isEveryone
                    ? '<span class="mention-everyone-icon"><i class="fa fa-users"></i></span>'
                    : '<img src="' + (m.avatar || self.opts.placeholder) + '" onerror="this.src=\'' + self.opts.placeholder + '\'" />';
                html += '<li data-idx="' + idx + '" data-name="' + self._escapeAttr(m.name) + '"' +
                    (idx === 0 ? ' class="active"' : '') + '>' +
                    avatarHtml +
                    '<span class="mention-name">' + self._escapeHtml(m.name) + '</span>' +
                    '<span class="mention-type ' + typeClass + '">' + typeLabel + '</span>' +
                '</li>';
            });

            this.$dropdown.html(html).show();
            this.active = true;
        },

        _hide: function() {
            this.$dropdown.hide().empty();
            this.active = false;
            this.filtered = [];
        },

        _moveSelection: function(dir) {
            var $items = this.$dropdown.find('li');
            if ($items.length === 0) return;
            this.selectedIdx = Math.max(0, Math.min(this.selectedIdx + dir, $items.length - 1));
            $items.removeClass('active').eq(this.selectedIdx).addClass('active');

            // Scroll into view
            var $active = $items.eq(this.selectedIdx);
            var container = this.$dropdown[0];
            if ($active.length && container) {
                var itemTop = $active[0].offsetTop;
                var itemBottom = itemTop + $active[0].offsetHeight;
                if (itemBottom > container.scrollTop + container.offsetHeight) {
                    container.scrollTop = itemBottom - container.offsetHeight;
                } else if (itemTop < container.scrollTop) {
                    container.scrollTop = itemTop;
                }
            }
        },

        _selectCurrent: function() {
            if (this.filtered.length > 0 && this.selectedIdx < this.filtered.length) {
                this._insertMention(this.filtered[this.selectedIdx].name);
            }
        },

        _insertMention: function(name) {
            var val = this.$input.val();
            var before = val.substring(0, this.startPos);
            var cursorPos = this.$input[0].selectionStart;
            var after = val.substring(cursorPos);
            var newVal = before + '@' + name + ' ' + after;
            this.$input.val(newVal);
            this.$input.focus();
            var pos = before.length + name.length + 2; // +2 for @ and space
            this.$input[0].setSelectionRange(pos, pos);
            this._hide();
        },

        _escapeHtml: function(text) {
            if (!text) return '';
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        _escapeAttr: function(text) {
            return (text || '').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        },

        destroy: function() {
            this.$input.off('.prchatMentions');
            this.$dropdown.off('click', 'li');
            $(document).off('mousedown.prchatMentions');
            this._hide();
        }
    };

    /**
     * Static utility: highlight @mentions in message HTML.
     * Call on message text before inserting into DOM.
     *
     * @param {string} text - The message text (already HTML-escaped)
     * @param {Array} members - Array of {name: '...'}
     * @returns {string} Text with mentions wrapped in <span class="mention-highlight">
     */
    PrchatMentions.highlightMentions = function(text, members) {
        if (!text || !members || members.length === 0) return text;

        var baseUrl = (typeof site_url !== 'undefined') ? site_url : '';

        text = text.replace(/@everyone(?=\s|$|[.,;!?]|<)/gi,
            '<span class="mention-highlight mention-everyone" data-mention-id="everyone">@everyone</span>');

        members.forEach(function(m) {
            if (!m.name || m.id === 'everyone') return;
            var escaped = m.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var regex = new RegExp('@(' + escaped + ')(?=\\s|$|[.,;!?]|<)', 'gi');
            var safeName = (m.name || '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
            text = text.replace(regex,
                '<a href="' + baseUrl + 'admin/profile/' + m.id + '" class="user_mentioned mention-highlight" data-chatmentioned="true" data-toggle="tooltip" title="' + safeName + '" target="_blank" data-mention-id="' + m.id + '">@$1</a>');
        });

        return text;
    };

    // Export
    window.PrchatMentions = PrchatMentions;

})(jQuery, window);

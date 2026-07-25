/**
 * PrchatSafeRenderer - Unified, XSS-safe message rendering for prchat.
 *
 * Replaces the scattered rendering logic across crm_clients.php, mutual_and_helper_functions.php,
 * and other files with a single, secure pipeline.
 *
 * Two entry points:
 *   render(html)         - For server-processed HTML (LOAD + REALTIME paths)
 *   renderFromText(text) - For raw user input (SEND path)
 *
 * All output passes through DOMPurify before DOM insertion.
 */
var PrchatSafeRenderer = (function () {
  "use strict";

  var PURIFY_CONFIG = {
    ALLOWED_TAGS: [
      "a",
      "img",
      "video",
      "audio",
      "source",
      "span",
      "div",
      "br",
      "strong",
      "em",
      "i",
      "b",
      "u",
      "p",
      "small",
      "bold",
      "ul",
      "ol",
      "li",
      "code",
      "pre",
      "h3",
      "h4",
      "blockquote",
    ],
    ALLOWED_ATTR: [
      "href",
      "src",
      "target",
      "class",
      "rel",
      "controls",
      "type",
      "style",
      "alt",
      "data-video-embed",
      "data-chat-file",
      "data-file-url",
      "data-filename",
      "data-original-message-id",
      "data-mention-id",
      "data-chatmentioned",
      "data-toggle",
      "data-placement",
      "data-container",
      "title",
      "aria-hidden",
    ],
    ALLOW_DATA_ATTR: true,
    ADD_ATTR: ["target"],
    FORBID_TAGS: [
      "script",
      "style",
      "iframe",
      "object",
      "embed",
      "form",
      "input",
      "textarea",
      "select",
      "button",
    ],
    FORBID_ATTR: [
      "onerror",
      "onload",
      "onclick",
      "onmouseover",
      "onfocus",
      "onblur",
    ],
  };

  var IMAGE_REGEX = /\.(gif|jpg|jpeg|png|bmp|webp|tiff|svg)$/i;
  var AUDIO_REGEX = /\.(mp3|wav|ogg|aac|m4a|webm)$/i;
  var VOICE_MSG_REGEX = /voice_\d+\.(webm|ogg|m4a)$/i;
  var VIDEO_FILE_REGEX = /\.(mp4|avi|mov|wmv|flv|mkv)$/i;
  var URL_REGEX = /(^|\s)((?:https?:\/\/|www\.)(?:[^\s<>"']+))/gi;

  var VIDEO_PLATFORM_PATTERNS = [
    /youtube(-nocookie)?\.com/i,
    /youtu\.be/i,
    /vimeo(pro)?\.com/i,
    /facebook\.com\/[^/]+\/videos\//i,
    /((maps|www)\.)?google\.[^/]+\/.*maps/i,
  ];

  var UPLOAD_PATH = "/modules/prchat/uploads/";

  // ── Escaping ──

  function escapeHtml(text) {
    if (!text) return "";
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  function escapeAttr(text) {
    if (!text) return "";
    return text
      .replace(/&/g, "&amp;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  // ── Markdown Processing ──
  // Lightweight markdown-to-HTML for chat messages.
  // Input is HTML-escaped text with <br> as line breaks.
  function processMarkdown(html) {
    if (!html || typeof html !== 'string') return html;

    // Split by <br> to process line-by-line
    var lines = html.split('<br>');
    var result = [];
    var inList = false;
    var inCodeBlock = false;
    var codeBlockContent = [];

    for (var i = 0; i < lines.length; i++) {
      var line = lines[i];

      // Fenced code blocks (``` ... ```)
      if (line.match(/^`{3}/)) {
        if (inCodeBlock) {
          // End code block
          result.push('<pre><code>' + codeBlockContent.join('\n') + '</code></pre>');
          codeBlockContent = [];
          inCodeBlock = false;
        } else {
          // Start code block — close any open list first
          if (inList) { result.push('</ul>'); inList = false; }
          inCodeBlock = true;
        }
        continue;
      }
      if (inCodeBlock) {
        codeBlockContent.push(line);
        continue;
      }

      // Bullet lists: lines starting with - , * , or •
      var bulletMatch = line.match(/^(?:\s*)(?:[-*•])\s+(.*)/);
      if (bulletMatch) {
        if (!inList) { result.push('<ul>'); inList = true; }
        result.push('<li>' + processInlineMarkdown(bulletMatch[1]) + '</li>');
        continue;
      }

      // Numbered lists: lines starting with 1. 2. etc.
      var numMatch = line.match(/^(?:\s*)\d+\.\s+(.*)/);
      if (numMatch) {
        // Close unordered list if open, switch to ordered
        if (inList) { result.push('</ul>'); inList = false; }
        // Simple approach: wrap each in <li> inside existing or new <ol>
        // For simplicity, treat like bullet list
        if (!inList) { result.push('<ul>'); inList = true; }
        result.push('<li>' + processInlineMarkdown(numMatch[1]) + '</li>');
        continue;
      }

      // Close list if this line is not a list item
      if (inList) { result.push('</ul>'); inList = false; }

      // Headers: #### before ### before ## (longer match first)
      var h4Match = line.match(/^####\s+(.*)/);
      if (h4Match) {
        result.push('<h4>' + processInlineMarkdown(h4Match[1]) + '</h4>');
        continue;
      }
      var h3Match = line.match(/^###\s+(.*)/);
      if (h3Match) {
        result.push('<h3>' + processInlineMarkdown(h3Match[1]) + '</h3>');
        continue;
      }
      var h2Match = line.match(/^##\s+(.*)/);
      if (h2Match) {
        result.push('<h3>' + processInlineMarkdown(h2Match[1]) + '</h3>');
        continue;
      }

      // Blockquote: > text
      var bqMatch = line.match(/^&gt;\s?(.*)/);
      if (bqMatch) {
        result.push('<blockquote>' + processInlineMarkdown(bqMatch[1]) + '</blockquote>');
        continue;
      }

      // Regular line with inline markdown
      result.push(processInlineMarkdown(line));
    }

    // Close any open constructs
    if (inList) result.push('</ul>');
    if (inCodeBlock && codeBlockContent.length > 0) {
      result.push('<pre><code>' + codeBlockContent.join('\n') + '</code></pre>');
    }

    // Join with <br>, then clean up redundant <br> around block elements
    var output = result.join('<br>');
    // Remove <br> immediately before/after block-level open/close tags
    output = output.replace(/<br>(<\/?(?:ul|ol|li|pre|h[34]|blockquote)>)/gi, '$1');
    output = output.replace(/(<\/?(?:ul|ol|li|pre|h[34]|blockquote)>)<br>/gi, '$1');
    return output;
  }

  // Inline markdown: bold, italic, code, strikethrough
  // Splits by HTML tags so markdown patterns don't break URLs/attributes inside tags
  function processInlineMarkdown(text) {
    if (!text) return '';
    var parts = text.split(/(<[^>]+>)/);
    for (var i = 0; i < parts.length; i++) {
      if (parts[i].charAt(0) === '<') continue;
      var s = parts[i];
      s = s.replace(/`([^`]+)`/g, '<code>$1</code>');
      s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
      s = s.replace(/__([^_]+)__/g, '<strong>$1</strong>');
      s = s.replace(/(?<!\w)\*([^*]+)\*(?!\w)/g, '<em>$1</em>');
      s = s.replace(/(?<!\w)_([^_]+)_(?!\w)/g, '<em>$1</em>');
      s = s.replace(/~~([^~]+)~~/g, '<span style="text-decoration:line-through">$1</span>');
      parts[i] = s;
    }
    return parts.join('');
  }

  // ── URL Utilities ──

  function isVideoPlatformUrl(url) {
    for (var i = 0; i < VIDEO_PLATFORM_PATTERNS.length; i++) {
      if (VIDEO_PLATFORM_PATTERNS[i].test(url)) return true;
    }
    return false;
  }

  function isUploadUrl(text) {
    return text.indexOf(UPLOAD_PATH) !== -1;
  }

  function normalizeHref(url) {
    if (/^https?:\/\//i.test(url)) {
      return url.replace(/^http:\/\//i, "//");
    }
    if (!/^\/\//.test(url)) {
      return "//" + url;
    }
    return url;
  }

  function isSafeUrl(url) {
    var trimmed = url.trim().toLowerCase();
    if (trimmed.indexOf("javascript:") === 0) return false;
    if (trimmed.indexOf("data:") === 0 && trimmed.indexOf("data:image/") !== 0)
      return false;
    if (trimmed.indexOf("vbscript:") === 0) return false;
    return true;
  }

  // ── Sub-processors (operate on ALREADY-ESCAPED text for renderFromText) ──

  function processLinks(escapedText) {
    // Handle standalone voice filenames with subfolder: staff/123_voice_..., clients/client_94_voice_..., groups/456_voice_...
    var VOICE_FILENAME_REGEX = /(^|\s)((staff|clients|groups)\/[a-z0-9_]+_voice_[a-z0-9_]+\.(webm|ogg|m4a)|[a-z0-9_]+_voice_[a-z0-9_]+\.(webm|ogg|m4a))(\s|$)/gi;
    escapedText = escapedText.replace(VOICE_FILENAME_REGEX, function(match, prefix, filename, subfolder, ext1, ext2, suffix) {
      var siteUrl = (typeof site_url !== 'undefined' ? site_url : '') || (typeof window !== 'undefined' && window.location ? window.location.origin + '/' : '');
      var audioPath = siteUrl + 'modules/prchat/uploads/audio/' + filename;
      return prefix + '<audio controls preload="auto" class="prchat-inline-audio"><source src="' + escapeHtml(audioPath) + '"></audio>' + suffix;
    });

    return escapedText.replace(URL_REGEX, function (match, prefix, rawUrl) {
      var url = rawUrl;

      if (url.match(/&amp;$/)) {
        url = url.replace(/&amp;$/, "");
      }

      var displayUrl = url;
      var href = url.replace(/&amp;/g, "&");

      if (!isSafeUrl(href)) {
        return match;
      }

      href = normalizeHref(href);
      var safeHref = escapeAttr(href);
      var safeDisplay = displayUrl;

      if (isUploadUrl(href)) {
        var filename = href.split("/").pop();
        var safeFilename = escapeHtml(decodeURIComponent(filename));
        var safeFileUrl = escapeAttr(href);

        if (IMAGE_REGEX.test(filename)) {
          return (
            prefix +
            '<a href="' +
            safeFileUrl +
            '" target="_blank" data-chat-file="image" data-file-url="' +
            safeFileUrl +
            '" data-filename="' +
            escapeAttr(filename) +
            '">' +
            '<img class="prchat_convertedImage" src="' +
            safeFileUrl +
            '" alt="' +
            escapeAttr(filename) +
            '"/></a>'
          );
        }
        if (AUDIO_REGEX.test(filename)) {
          return (
            prefix +
            '<audio controls preload="auto" class="prchat-inline-audio"><source src="' +
            safeFileUrl +
            '"></audio>'
          );
        }
        if (VIDEO_FILE_REGEX.test(filename)) {
          return (
            prefix +
            '<video controls style="max-width:300px;max-height:200px;"><source src="' +
            safeFileUrl +
            '"></video>'
          );
        }
        return (
          prefix +
          '<a target="_blank" href="' +
          safeFileUrl +
          '" data-chat-file="file" data-file-url="' +
          safeFileUrl +
          '" data-filename="' +
          escapeAttr(filename) +
          '">' +
          safeFilename +
          "</a>"
        );
      }

      if (IMAGE_REGEX.test(href)) {
        return (
          prefix +
          '<a href="' +
          safeHref +
          '" target="_blank"><img class="prchat_convertedImage" src="' +
          safeHref +
          '"/></a>'
        );
      }
      if (isVideoPlatformUrl(href)) {
        return (
          prefix +
          '<a href="' +
          safeHref +
          '" target="_blank" data-video-embed="true">' +
          safeDisplay +
          "</a>"
        );
      }
      return (
        prefix +
        '<a target="_blank" rel="nofollow" href="' +
        safeHref +
        '">' +
        safeDisplay +
        "</a>"
      );
    });
  }

  /**
   * Turn server/clickable() <a href=".../modules/prchat/uploads/...voice...."> into <audio controls>
   * so LOAD + REALTIME paths match renderFromText() behaviour (inline player, not new-tab link).
   */
  function convertUploadAnchorsToAudio(html) {
    if (!html || html.indexOf("<a") === -1) return html;
    if (typeof DOMParser === "undefined") return html;
    try {
      var doc = new DOMParser().parseFromString(
        '<div id="prchat-audio-wrap">' + html + "</div>",
        "text/html"
      );
      var root = doc.body.querySelector("#prchat-audio-wrap");
      if (!root) return html;
      var links = root.querySelectorAll("a[href]");
      for (var i = 0; i < links.length; i++) {
        var a = links[i];
        var href = a.getAttribute("href");
        if (!href) continue;
        var dec = href.replace(/&amp;/g, "&");
        if (dec.indexOf(UPLOAD_PATH) === -1) continue;
        var pathOnly = dec.replace(/^https?:\/\/[^/]+/i, "");
        if (pathOnly.indexOf(UPLOAD_PATH) === -1) continue;
        var filename = pathOnly.split("/").pop().split("?")[0] || "";
        if (!AUDIO_REGEX.test(filename) && !VOICE_MSG_REGEX.test(filename)) continue;
        var audio = doc.createElement("audio");
        audio.setAttribute("controls", "");
        audio.setAttribute("preload", "auto");
        audio.setAttribute("class", "prchat-inline-audio");
        var source = doc.createElement("source");
        source.setAttribute("src", dec);
        audio.appendChild(source);
        a.parentNode.replaceChild(audio, a);
      }
      return root.innerHTML;
    } catch (e) {
      return html;
    }
  }

  function processFileUploadPlainText(escapedText) {
    var raw = escapedText
      .replace(/&amp;/g, "&")
      .replace(/&lt;/g, "<")
      .replace(/&gt;/g, ">")
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'");

    if (!isUploadUrl(raw) || raw.indexOf("<a") !== -1) {
      return null;
    }

    var filename = raw.split("/").pop();
    var safeUrl = escapeAttr(raw);
    var safeFilename = escapeHtml(decodeURIComponent(filename));

    if (IMAGE_REGEX.test(filename)) {
      return (
        '<a href="' +
        safeUrl +
        '" target="_blank" data-chat-file="image" data-file-url="' +
        safeUrl +
        '" data-filename="' +
        escapeAttr(filename) +
        '">' +
        '<img class="prchat_convertedImage" src="' +
        safeUrl +
        '" alt="' +
        escapeAttr(filename) +
        '"/></a>'
      );
    }
    if (AUDIO_REGEX.test(filename)) {
      return (
        '<audio controls preload="auto" class="prchat-inline-audio"><source src="' +
        safeUrl +
        '"></audio>'
      );
    }
    if (VIDEO_FILE_REGEX.test(filename)) {
      return (
        '<video controls style="max-width:300px;max-height:200px;"><source src="' +
        safeUrl +
        '"></video>'
      );
    }
    return (
      '<a target="_blank" href="' +
      safeUrl +
      '" data-chat-file="file" data-file-url="' +
      safeUrl +
      '" data-filename="' +
      escapeAttr(filename) +
      '">' +
      safeFilename +
      "</a>"
    );
  }

  // ── Reply Processing ──

  var REPLY_REGEX = /\[REPLY:([^:]+):([^:]+):([^\]]*)\]\s*/;

  function processReplies(html) {
    var match = html.match(REPLY_REGEX);
    if (!match) return html;

    var originalMessageId = escapeAttr(match[1]);
    var type = match[2];
    var preview = match[3] || "";
    var actualMessage = html.replace(REPLY_REGEX, "");

    if (!preview) {
      var fallbacks = {
        image: "Photo",
        video: "Video",
        audio: "Audio",
        file: "File",
        link: "Link",
      };
      preview = fallbacks[type] || "...";
    }

    var replyHTML =
      '<div class="message-reply-context" data-original-message-id="' +
      originalMessageId +
      '">' +
      '<div class="reply-context-content">' +
      '<div class="reply-context-text">' +
      preview +
      "</div>" +
      "</div>" +
      "</div>";

    return replyHTML + actualMessage;
  }

  // ── Emoji Processing ──

  function processEmojis(html) {
    if (window.CustomEmoji) {
      try {
        var emojiProcessor = new window.CustomEmoji();
        return emojiProcessor.shortcodesToEmojis(html);
      } catch (e) {
        // fallback
      }
    }
    return html;
  }

  // ── DOMPurify Sanitization ──

  function sanitize(html) {
    if (typeof DOMPurify !== "undefined") {
      return DOMPurify.sanitize(html, PURIFY_CONFIG);
    }
    return html;
  }

  // ── Sidebar Preview Extraction ──

  function extractSidebarPreview(message, rawMessage) {
    if (!message && !rawMessage) return "";

    var source = rawMessage || message;
    var combined = String(message || "") + String(rawMessage || "");

    // Check for [CALL:...] format, possibly prefixed with "You: " or similar
    var callSource = source;
    var callPrefix = "";
    var prefixMatch = source.match(/^(.+?\s)\[CALL:/);
    if (prefixMatch) {
      callPrefix = prefixMatch[1];
      callSource = source.substring(callPrefix.length);
    }
    var cp = formatCallPreview(callSource);
    if (cp) return callPrefix + cp;

    var replyMatch = source.match(/\[REPLY:[^:]+:[^:]+:([^\]]+)\]\s*(.*)/);
    if (replyMatch) {
      return stripTags(replyMatch[2] || replyMatch[1]);
    }

    if (/data-chat-file="image"|class="prchat_convertedImage"/i.test(message)) {
      return "📷 Photo";
    }

    // Quick mention links (raw HTML or HTML-entity encoded from DB)
    if (/quickMentionLink/i.test(combined)) {
      var qmSrc = source;
      if (qmSrc.indexOf('&lt;') !== -1) {
        qmSrc = qmSrc.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/&#0*39;/g, "'").replace(/&quot;/g, '"');
      }
      var qmText = stripTags(qmSrc);
      return qmText.length > 50 ? qmText.substring(0, 50) + '...' : qmText;
    }

    // Check for raw image URLs (before server processes them)
    var imageUrlPattern = /\.(jpg|jpeg|png|gif|webp|bmp|svg)(\?|$)/i;
    if (imageUrlPattern.test(source)) {
      return "📷 Photo";
    }

    if (/data-chat-file="file"/i.test(message)) {
      var fileMatch = message.match(/>([^<]+)<\/a>/);
      return fileMatch ? "📎 " + fileMatch[1] : "📎 File";
    }
    if (/data-video-embed="true"/i.test(message)) {
      return "🔗 Link";
    }
    // Voice notes: inline player HTML, or standalone filename (with or without subfolder)
    // Matches: staff/123_voice_..., clients/client_94_voice_..., groups/456_voice_..., or just 123_voice_...
    if (/prchat-inline-audio/i.test(combined) || /(^|\s|\/)((staff|clients|groups)\/)?[a-z0-9_]+_voice_[a-z0-9_]+\.(webm|ogg|m4a)(\s|$|<)/i.test(combined)) {
      var _vm =
        typeof window !== "undefined" && window.prchatI18n && window.prchatI18n.chat_voice_message
          ? window.prchatI18n.chat_voice_message
          : "Voice message";
      return _vm;
    }
    if (/audio\/ogg|<\s*audio\b|\.mp3|\.wav|\.aac/i.test(combined)) {
      return "🎵 Audio";
    }
    if (/<video/i.test(message)) {
      return "🎥 Video";
    }

    var text = stripTags(message);
    if (window.CustomEmoji) {
      try {
        text = new window.CustomEmoji().shortcodesToEmojis(text);
      } catch (e) {}
    }
    text = stripTags(text);

    if (text.includes('&bull;')) {
      text = text.replace(/&bull;/g, '\u2022');
    }

    return text.length > 50 ? text.substring(0, 50) + "..." : text;
  }

  function stripTags(html) {
    if (!html) return "";
    var cleaned = sanitize(html);
    return cleaned.replace(/<[^>]*>/g, "").trim();
  }

  function extractCallTokenFromMessage(raw) {
    if (raw === undefined || raw === null) return "";
    var s = String(raw).trim();
    if (/^\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]$/.test(s)) {
      return s;
    }
    var m = s.match(/\[CALL:(voice|video|missed_voice|missed_video):\d+:\d+:\d+\]/);
    return m ? m[0] : "";
  }

  function formatCallPreview(msg) {
    if (!msg) return null;
    var m = msg.match(/^\[CALL:(voice|video|missed_voice|missed_video):(\d+):(\d+):(\d+)\]$/);
    if (!m) return null;
    var type = m[1];
    if (type === "missed_voice" || type === "missed_video") {
      return "\uD83D\uDCF5 Missed call";
    }
    var dur = parseInt(m[2]);
    var mins = Math.floor(dur / 60);
    var secs = dur % 60;
    var durStr = (mins > 0 ? mins + "m " : "") + secs + "s";
    var icon = type === "video" ? "\uD83D\uDCF9" : "\uD83D\uDCDE";
    var label = type === "video" ? "Video call" : "Voice call";
    return icon + " " + label + " \u2022 " + durStr;
  }

  // ── Public API ──

  return {
    /**
     * Render server-processed HTML (LOAD + REALTIME paths).
     * Server has already converted URLs to <a> tags and images to <img> tags.
     * We process replies, emojis, and sanitize.
     */
    render: function (html) {
      if (!html) return { display: "", sidebar: "" };

      var str = String(html).trim();
      if (str.indexOf("<") === -1 && str.indexOf(">") === -1) {
        // Server text with no HTML tags may contain HTML entities (e.g. &#039; for ')
        // Decode entities before passing to renderFromText (which will re-escape safely)
        var tmp = document.createElement('textarea');
        tmp.innerHTML = str;
        return this.renderFromText(tmp.value);
      }

      var processed = html;

      processed = processReplies(processed);
      processed = convertUploadAnchorsToAudio(processed);
      // Convert newlines to <br> for server-loaded messages
      processed = processed.replace(/\n/g, '<br>');
      // Apply inline markdown (bold, italic, code) to server-loaded messages
      // Block-level markdown (lists, headers) handled only in renderFromText path
      processed = processInlineMarkdown(processed);
      processed = processEmojis(processed);
      var display = sanitize(processed);

      return {
        display: display,
        sidebar: extractSidebarPreview(display, html),
      };
    },

    /**
     * Render raw user text (SEND path).
     * Escapes ALL content first, then builds safe HTML structures.
     */
    renderFromText: function (text) {
      if (!text) return { display: "", sidebar: "" };

      var callPrev = formatCallPreview(text);
      if (callPrev) {
        return { display: callPrev, sidebar: callPrev };
      }

      var replyMatch = text.match(REPLY_REGEX);
      var replyPart = "";
      var messagePart = text;

      if (replyMatch) {
        var originalMessageId = escapeAttr(replyMatch[1]);
        var type = replyMatch[2];
        var rawPreview = replyMatch[3] || "";
        messagePart = text.replace(REPLY_REGEX, "");

        if (!rawPreview) {
          var fallbacks = {
            image: "Photo",
            video: "Video",
            audio: "Audio",
            file: "File",
            link: "Link",
          };
          rawPreview = fallbacks[type] || "...";
        }

        replyPart =
          '<div class="message-reply-context" data-original-message-id="' +
          originalMessageId +
          '">' +
          '<div class="reply-context-content">' +
          '<div class="reply-context-text">' +
          escapeHtml(rawPreview) +
          "</div>" +
          "</div>" +
          "</div>";
      }

      var escaped = escapeHtml(messagePart);

      // Check for file upload URL first (before markdown, which could modify the URL)
      var fileUploadHtml = processFileUploadPlainText(escaped);
      if (fileUploadHtml) {
        escaped = fileUploadHtml;
      } else {
        // Process links BEFORE newline-to-br so URLs after newlines are matched
        escaped = processLinks(escaped);
        // Convert newlines to <br> AFTER links but BEFORE markdown (processMarkdown splits on <br>)
        escaped = escaped.replace(/\n/g, '<br>');
        escaped = processMarkdown(escaped);
      }

      escaped = processEmojis(escaped);

      var display = sanitize(replyPart + escaped);

      return {
        display: display,
        sidebar: extractSidebarPreview(display, text),
      };
    },

    /**
     * Sanitize arbitrary HTML through DOMPurify.
     */
    sanitize: sanitize,

    /**
     * Escape HTML for safe text rendering.
     */
    escapeHtml: escapeHtml,

    /**
     * Extract a plain-text sidebar preview from a message.
     */
    getSidebarPreview: extractSidebarPreview,

    /**
     * Strip HTML tags safely.
     */
    stripTags: stripTags,

    formatCallPreview: formatCallPreview,

    extractCallTokenFromMessage: extractCallTokenFromMessage,

    /**
     * HTML for modern message rows: keep raw [CALL:…] so UI can render the centered call pill
     * (render()/renderFromText turn calls into emoji preview text and break isCallMessageStr).
     */
    displayForModernMessageRow: function (raw) {
      var t = extractCallTokenFromMessage(raw);
      if (t) return t;
      return this.render(raw).display;
    },

    cleanNotificationText: function (msg) {
      if (!msg) return '';

      var i18n = (typeof window !== 'undefined' && window.prchatI18n) ? window.prchatI18n : {};
      var uploadedFileText = i18n.chat_new_file_uploaded || 'uploaded a new file';
      var uploadedPhotoText = i18n.chat_new_photo_uploaded || 'uploaded a new photo';

      var rawMsg = String(msg);
      var normalizedMsg = rawMsg
        .replace(/&lt;/gi, '<')
        .replace(/&gt;/gi, '>')
        .replace(/&quot;/gi, '"')
        .replace(/&#0*39;/gi, "'")
        .replace(/&amp;/gi, '&');

      // Strip all HTML helper — never return raw HTML in a notification
      var stripHtml = function (s) { return String(s).replace(/<[^>]*>/g, '').trim(); };

      if (normalizedMsg.indexOf('/modules/prchat/uploads/') !== -1) {
        var lowerMsg = normalizedMsg.toLowerCase();
        if (/\.(jpg|jpeg|png|gif|webp)(\?|$)/.test(lowerMsg) || lowerMsg.indexOf('<img') !== -1) {
          return uploadedPhotoText;
        }
        if (/\.(mp3|ogg|wav|webm|m4a)(\?|$)/.test(lowerMsg) || lowerMsg.indexOf('type="audio') !== -1 || lowerMsg.indexOf('/prchat_audio/') !== -1) {
          return i18n.chat_voice_message || 'Voice message';
        }
        return uploadedFileText;
      }

      if (normalizedMsg.toLowerCase().indexOf('<img') !== -1) {
        return uploadedPhotoText;
      }

      var cp = formatCallPreview(normalizedMsg);
      if (cp) return cp;
      var replyMatch = normalizedMsg.match(/\[REPLY:[^:]+:[^:]+:([^\]]*)\]\s*([\s\S]*)/);
      if (replyMatch) {
        var body = stripHtml(replyMatch[2] || '');
        if (body) return body.substring(0, 100);
        var preview = (replyMatch[1] || '').trim();
        if (preview) return preview.substring(0, 100);
      }
      var stripped = stripHtml(normalizedMsg.replace(/\[CALL:[^\]]*\]\s*/, ''));
      return stripped.substring(0, 100) || stripHtml(normalizedMsg).substring(0, 100);
    },
  };
})();

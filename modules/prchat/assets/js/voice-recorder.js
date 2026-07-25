/**
 * PRChat Voice Recorder — shared module for recording and sending voice messages.
 *
 * Usage:
 *   PrchatVoiceRecorder.init({
 *     triggerBtn:  document.querySelector('.chat-action-voice'),
 *     container:   document.querySelector('.message-input .wrap'),
 *     uploadUrl:   '/prchat/Prchat_Controller/uploadMethod',
 *     basePath:    '/modules/prchat/uploads/',
 *     csrfName:    'csrf_token_name',
 *     csrfValue:   '...',
 *     onSend:      function(fileUrl) { ... },
 *     onError:     function(msg) { ... }
 *   });
 */
(function(root) {
  "use strict";

  var MIME = (function() {
    if (typeof MediaRecorder !== "undefined") {
      if (MediaRecorder.isTypeSupported("audio/webm;codecs=opus")) return "audio/webm;codecs=opus";
      if (MediaRecorder.isTypeSupported("audio/webm")) return "audio/webm";
      if (MediaRecorder.isTypeSupported("audio/ogg;codecs=opus")) return "audio/ogg;codecs=opus";
      if (MediaRecorder.isTypeSupported("audio/mp4")) return "audio/mp4";
    }
    return "";
  })();

  var EXT_MAP = {
    "audio/webm;codecs=opus": "webm",
    "audio/webm": "webm",
    "audio/ogg;codecs=opus": "ogg",
    "audio/mp4": "m4a"
  };

  function formatTime(sec) {
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    return (m < 10 ? "0" : "") + m + ":" + (s < 10 ? "0" : "") + s;
  }

  function createRecorderUI() {
    var el = document.createElement("div");
    el.className = "prchat-voice-recorder";

    var pulse = document.createElement("div");
    pulse.className = "pvr-pulse";
    el.appendChild(pulse);

    var timer = document.createElement("span");
    timer.className = "pvr-timer";
    timer.textContent = "00:00";
    el.appendChild(timer);

    var actions = document.createElement("div");
    actions.className = "pvr-actions";

    var cancelBtn = document.createElement("button");
    cancelBtn.type = "button";
    cancelBtn.className = "pvr-cancel";
    cancelBtn.setAttribute("aria-label", "Cancel");
    cancelBtn.title = "Cancel";
    var cancelIcon = document.createElement("i");
    cancelIcon.className = "fa fa-times";
    cancelBtn.appendChild(cancelIcon);

    var sendBtn = document.createElement("button");
    sendBtn.type = "button";
    sendBtn.className = "pvr-send";
    sendBtn.setAttribute("aria-label", "Stop and send");
    sendBtn.title = "Stop and send";
    var sendIcon = document.createElement("i");
    sendIcon.className = "fa fa-stop";
    sendBtn.appendChild(sendIcon);
    var sendLabel = document.createElement("span");
    sendLabel.className = "pvr-send-label";
    sendLabel.textContent = "Send";
    sendBtn.appendChild(sendLabel);

    actions.appendChild(cancelBtn);
    actions.appendChild(sendBtn);
    el.appendChild(actions);

    return el;
  }

  function Instance(cfg) {
    this.cfg = cfg;
    this.recorder = null;
    this.stream = null;
    this.chunks = [];
    this.seconds = 0;
    this.timer = null;
    this.ui = null;
    this.active = false;

    var self = this;
    if (cfg.triggerBtn) {
      cfg.triggerBtn.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (self.active) return;
        self.start();
      });
    }
  }

  Instance.prototype.start = function() {
    if (!MIME) {
      this.cfg.onError && this.cfg.onError("Voice recording is not supported in this browser.");
      return;
    }
    var self = this;
    navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
      self.stream = stream;
      self.chunks = [];
      self.seconds = 0;
      self.active = true;

      self.recorder = new MediaRecorder(stream, { mimeType: MIME });
      self.recorder.ondataavailable = function(e) {
        if (e.data && e.data.size > 0) self.chunks.push(e.data);
      };
      self.recorder.onstop = function() {
        self._onStop();
      };
      self.recorder.start(250);

      self._showUI();
      self.timer = setInterval(function() {
        self.seconds++;
        if (self.ui) {
          self.ui.querySelector(".pvr-timer").textContent = formatTime(self.seconds);
        }
      }, 1000);
    }).catch(function() {
      self.cfg.onError && self.cfg.onError("Microphone access denied.");
    });
  };

  Instance.prototype._showUI = function() {
    this.ui = createRecorderUI();
    var self = this;

    this.ui.querySelector(".pvr-cancel").onclick = function() { self.cancel(); };
    this.ui.querySelector(".pvr-send").onclick = function() { self.stop(); };

    var hideTags = ".message-actions, textarea, .chatbox, .client_chatbox, .group_chatbox, .clients_textarea, button.submit, button.enterBtn, .send_client_message, .send-btn-inline, .input-textarea-wrap, .input-textarea-wrap-client, .input-actions-left, .prchat-composer-row > .wrap, .prchat-client-composer-row > .input-textarea-wrap-client";
    if (this.cfg.container) {
      this.cfg.container.classList.add("prchat-voice-recording-active");
      this.cfg.container.querySelectorAll(hideTags).forEach(function(el) {
        el.style.display = "none";
      });
      this.cfg.container.appendChild(this.ui);
    }
  };

  Instance.prototype._hideUI = function() {
    if (this.ui && this.ui.parentNode) {
      this.ui.parentNode.removeChild(this.ui);
    }
    var showTags = ".message-actions, textarea, .chatbox, .client_chatbox, .group_chatbox, .clients_textarea, button.submit, button.enterBtn, .send_client_message, .send-btn-inline, .input-textarea-wrap, .input-textarea-wrap-client, .input-actions-left, .prchat-composer-row > .wrap, .prchat-client-composer-row > .input-textarea-wrap-client";
    if (this.cfg.container) {
      this.cfg.container.classList.remove("prchat-voice-recording-active");
      this.cfg.container.querySelectorAll(showTags).forEach(function(el) {
        el.style.display = "";
      });
    }
    this.ui = null;
  };

  Instance.prototype.cancel = function() {
    clearInterval(this.timer);
    this.active = false;
    if (this.recorder && this.recorder.state !== "inactive") {
      this.recorder.ondataavailable = null;
      this.recorder.onstop = null;
      this.recorder.stop();
    }
    this._stopStream();
    this._hideUI();
  };

  Instance.prototype.stop = function() {
    clearInterval(this.timer);
    if (this.recorder && this.recorder.state !== "inactive") {
      this.recorder.stop();
    }
  };

  Instance.prototype._stopStream = function() {
    if (this.stream) {
      this.stream.getTracks().forEach(function(t) { t.stop(); });
      this.stream = null;
    }
  };

  Instance.prototype._onStop = function() {
    this._stopStream();
    this.active = false;

    if (this.chunks.length === 0 || this.seconds < 1) {
      this._hideUI();
      return;
    }

    var blob = new Blob(this.chunks, { type: MIME });
    var ext = EXT_MAP[MIME] || "webm";
    var userId = (this.cfg.extraFormData && typeof this.cfg.extraFormData === "function") 
      ? (this.cfg.extraFormData().send_from || this.cfg.extraFormData().from || '') 
      : '';
    var userPart = userId ? String(userId).replace(/[^a-zA-Z0-9]/g, '_') : 'unknown';
    var filename = userPart + "_voice_" + Date.now() + "_" + Math.random().toString(36).slice(2, 8) + "." + ext;
    var file = new File([blob], filename, { type: MIME });

    this._upload(file);
    this._hideUI();
  };

  Instance.prototype._upload = function(file) {
    var cfg = this.cfg;
    var fd = new FormData();
    fd.append("userfile", file);
    fd.append("prchat_voice_upload", "1");
    if (cfg.csrfName && cfg.csrfValue) {
      fd.append(cfg.csrfName, cfg.csrfValue);
    }
    var ex = (typeof cfg.extraFormData === "function") ? cfg.extraFormData() : cfg.extraFormData;
    if (ex && typeof ex === "object") {
      for (var k in ex) {
        if (Object.prototype.hasOwnProperty.call(ex, k) && ex[k] != null && ex[k] !== "") {
          fd.append(k, ex[k]);
        }
      }
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", cfg.uploadUrl, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          var res = JSON.parse(xhr.responseText);
          if (res.error) {
            cfg.onError && cfg.onError(res.error);
          } else if (res.upload_data && res.upload_data.file_name) {
            // Send filename with subfolder if provided, otherwise just filename
            var fullPath = res.upload_data.subfolder 
              ? res.upload_data.subfolder + '/' + res.upload_data.file_name
              : res.upload_data.file_name;
            cfg.onSend && cfg.onSend(fullPath);
          }
        } catch (e) {
          cfg.onError && cfg.onError("Upload failed.");
        }
      } else {
        cfg.onError && cfg.onError("Upload failed (HTTP " + xhr.status + ").");
      }
    };
    xhr.onerror = function() {
      cfg.onError && cfg.onError("Upload network error.");
    };
    xhr.send(fd);
  };

  Instance.prototype.destroy = function() {
    this.cancel();
  };

  root.PrchatVoiceRecorder = {
    init: function(cfg) {
      return new Instance(cfg);
    },
    isSupported: function() {
      return !!(typeof MediaRecorder !== "undefined" && MIME && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }
  };

})(window);

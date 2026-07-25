/*
 * Minimal Call UI scaffold (feature-flagged). This will evolve to:
 * - Incoming call modal
 * - In-call controls (mute, camera, end)
 * - Screen sharing toggle
 */
(function (window, document) {
  "use strict";


  function ensureStyles() {
    if (document.getElementById("chat-call-ui-styles")) return;
    var style = document.createElement("style");
    style.id = "chat-call-ui-styles";
    style.textContent =
      ".chat-call-banner{position:fixed;bottom:16px;right:16px;background:#111827;color:#fff;padding:10px 14px;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,.2);font-size:14px;z-index:99999}" +
      ".chat-call-overlay{position:fixed;inset:0;background:rgba(17,24,39,.6);z-index:100000;display:flex;align-items:center;justify-content:center}" +
      ".chat-call-box{background:#fff;border-radius:16px;box-shadow:0 28px 80px rgba(0,0,0,.35);padding:22px 22px 18px;min-width:340px;max-width:92vw;text-align:center}" +
      ".chat-call-title{font-size:18px;font-weight:700;color:#111827;margin:4px 0 4px}" +
      ".chat-call-subtitle{font-size:13px;color:#6b7280;margin:0 0 8px}" +
      ".chat-call-actions{display:flex;gap:10px;align-items:center;justify-content:center;margin-top:12px}" +
      ".chat-call-btn{border:0;border-radius:999px;padding:10px 16px;font-weight:600;color:#fff;cursor:pointer;box-shadow:0 10px 20px rgba(0,0,0,.12);transition:transform .12s ease}" +
      ".chat-call-btn:active{transform:scale(.98)}" +
      ".chat-call-btn--accept{background:#10b981}" +
      ".chat-call-btn--decline{background:#ef4444}" +
      ".chat-call-btn--neutral{background:#6b7280}" +
      ".chat-call-avatar{width:64px;height:64px;border-radius:999px;background:linear-gradient(135deg,#60a5fa,#34d399);margin:0 auto 8px;position:relative;overflow:hidden}" +
      ".chat-call-wave:before,.chat-call-wave:after{content:'';position:absolute;inset:-6px;border-radius:50%;border:2px solid rgba(255,255,255,.55);animation:chat-wave 1.8s infinite ease-out}" +
      ".chat-call-wave:after{animation-delay:.9s}" +
      "@keyframes chat-wave{0%{transform:scale(.9);opacity:.8}70%{transform:scale(1.2);opacity:.15}100%{transform:scale(1.35);opacity:0}}" +
      ".chat-call-timer{font-size:12px;color:#374151;margin-top:6px}" +
      ".chat-call-select{appearance:none;-webkit-appearance:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 16px;font-size:14px;color:#111827;outline:none;box-shadow:0 1px 2px rgba(0,0,0,.04);margin:8px 0;min-width:280px;cursor:pointer;background-image:url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDEyIDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xIDFMNiA2TDExIDEiIHN0cm9rZT0iIzZCNzI4MCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4K');background-repeat:no-repeat;background-position:right 12px center;padding-right:40px}" +
      ".chat-call-select:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}" +
      ".chat-call-device-section{margin:16px 0;text-align:left}" +
      ".chat-call-device-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}" +
      "#chat-call-pip{position:fixed;bottom:20px;right:20px;width:640px;height:480px;z-index:9999998;border-radius:12px;overflow:hidden;background:#000;box-shadow:0 12px 48px rgba(0,0,0,0.45);transition:width 0.25s ease,height 0.25s ease,top 0.25s ease,left 0.25s ease,bottom 0.25s ease,right 0.25s ease,border-radius 0.25s ease}" +
      "#chat-call-pip.pip-fullscreen{top:0!important;left:0!important;bottom:auto!important;right:auto!important;width:100vw!important;height:100vh!important;border-radius:0}" +
      "#chat-call-pip.pip-minimized{width:200px!important;height:150px!important;border-radius:8px;cursor:pointer}" +
      "#chat-call-pip.pip-minimized #chat-call-pip-topbar,#chat-call-pip.pip-minimized #chat-video-call-controls,#chat-call-pip.pip-minimized #chat-call-local-video{display:none!important}" +
      "#chat-call-pip.pip-minimized #chat-call-pip-restore{display:flex!important}" +
      "#chat-call-pip-topbar{position:absolute;top:0;left:0;right:0;height:36px;background:linear-gradient(180deg,rgba(0,0,0,0.6) 0%,transparent 100%);z-index:3;display:flex;align-items:center;justify-content:flex-end;padding:0 8px;cursor:grab;-webkit-user-select:none;user-select:none}" +
      "#chat-call-pip-topbar button{width:28px;height:28px;border:none;border-radius:6px;background:rgba(255,255,255,0.15);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;margin-left:4px;transition:background 0.15s}" +
      "#chat-call-pip-topbar button:hover{background:rgba(255,255,255,0.3)}" +
      "#chat-call-pip-restore{display:none;position:absolute;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);z-index:4;cursor:pointer}" +
      "#chat-call-pip-restore i{color:#fff;font-size:24px}" +
      "#chat-call-pip-resize{position:absolute;bottom:0;right:0;width:18px;height:18px;cursor:nwse-resize;z-index:5}" +
      "#chat-call-remote-video{width:100%;height:100%;object-fit:cover;background:#000;display:block}" +
      "#chat-call-local-video{position:absolute;bottom:60px;right:12px;width:160px;height:120px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);background:#000;object-fit:cover;border:2px solid rgba(255,255,255,0.15);z-index:2}";
    document.head.appendChild(style);
  }

  function ensureModalRoot() {
    var root = document.getElementById("chat-call-modal-root");
    if (!root) {
      root = document.createElement("div");
      root.id = "chat-call-modal-root";
      root.style.position = "fixed";
      root.style.top = "0";
      root.style.left = "0";
      root.style.right = "0";
      root.style.bottom = "0";
      root.style.zIndex = "100000";
      root.style.display = "none";
      document.body.appendChild(root);
    }
    return root;
  }

  function openModal(contentNode, options) {
    options = options || {};
    ensureStyles();

    // For device picker, use separate modal to preserve call UI
    if (options.isDevicePicker) {
      var deviceRoot = document.getElementById("chat-call-device-root");
      if (!deviceRoot) {
        deviceRoot = document.createElement("div");
        deviceRoot.id = "chat-call-device-root";
        deviceRoot.style.cssText = "position:fixed;top:0;left:0;right:0;bottom:0;z-index:10000001;display:none;pointer-events:auto;";
        document.body.appendChild(deviceRoot);
      }

      while (deviceRoot.firstChild) { deviceRoot.removeChild(deviceRoot.firstChild); }
      var overlay = document.createElement("div");
      overlay.className = "chat-call-overlay";
      overlay.style.pointerEvents = "auto";
      var box = document.createElement("div");
      box.className = "chat-call-box";
      box.appendChild(contentNode);
      overlay.appendChild(box);
      deviceRoot.appendChild(overlay);
      deviceRoot.style.display = "block";
      deviceRoot.setAttribute("data-open", "1");
      return;
    }

    // Regular modal
    var root = ensureModalRoot();
    root.innerHTML = "";
    var overlay = document.createElement("div");
    overlay.className = "chat-call-overlay";
    // Non-dismissible overlay to prevent accidental call interruption
    var box = document.createElement("div");
    box.className = "chat-call-box";
    box.appendChild(contentNode);
    overlay.appendChild(box);
    root.appendChild(overlay);
    root.style.display = "block";
    root.setAttribute("data-open", "1");
  }

  function closeModal(deviceOnly) {
    if (deviceOnly) {
      // Close only device picker modal
      var deviceRoot = document.getElementById("chat-call-device-root");
      if (deviceRoot) {
        deviceRoot.style.display = "none";
        deviceRoot.innerHTML = "";
        deviceRoot.removeAttribute("data-open");
      }
      return;
    }

    // Close main modal and also device modal if open
    var root = ensureModalRoot();
    root.style.display = "none";
    root.innerHTML = "";
    root.removeAttribute("data-open");
    root.removeAttribute("data-notice");

    var deviceRoot = document.getElementById("chat-call-device-root");
    if (deviceRoot) {
      deviceRoot.style.display = "none";
      deviceRoot.innerHTML = "";
      deviceRoot.removeAttribute("data-open");
    }

    // Also hide video call controls and floating voice bar
    hideVideoCallControls();
    hideFloatingVoiceBar();
  }

  function showIncomingCall(callerLabel, onAccept, onDecline, opts) {
    opts = opts || {};
    stopRingtone();

    if (!isAudioLocked()) {
      startRingtone("incoming");
    }
    var wrap = document.createElement("div");
    var avatar = document.createElement("div");
    avatar.className = "chat-call-avatar chat-call-wave";
    if (opts.avatarUrl) {
      avatar.style.backgroundImage = 'url("' + opts.avatarUrl + '")';
      avatar.style.backgroundSize = "cover";
      avatar.style.backgroundPosition = "center";
    }
    var title = document.createElement("div");
    title.className = "chat-call-title";

    // Add call type icon (video or voice)
    var isVideo = opts.isVideo || false;
    var callIcon = document.createElement("i");
    callIcon.className = isVideo ? "fa fa-video-camera" : "fa fa-phone";
    callIcon.style.cssText = "margin-right: 8px; color: #4CAF50;";

    var titleText = document.createTextNode(
      (isVideo ? "Video" : "Voice") + " call from " + (callerLabel || "User"),
    );
    title.appendChild(callIcon);
    title.appendChild(titleText);

    var subtitle = document.createElement("div");
    subtitle.className = "chat-call-subtitle";
    subtitle.textContent = "Tap Accept to join";
    var actions = document.createElement("div");
    actions.className = "chat-call-actions";

    // Check if audio is locked at the time of showing the modal
    var showEnableSound = isAudioLocked();

    if (showEnableSound) {
      var unlock = document.createElement("button");
      unlock.textContent = "Enable sound";
      unlock.className = "chat-call-btn chat-call-btn--neutral";
      unlock.onclick = function () {
        unlockAudio();
        startRingtone("incoming");
        // Hide this button after clicking
        unlock.style.display = "none";
      };
      actions.appendChild(unlock);
    }
    var accept = document.createElement("button");
    accept.textContent = "Accept";
    accept.className = "chat-call-btn chat-call-btn--accept";
    var decline = document.createElement("button");
    decline.textContent = "Decline";
    decline.className = "chat-call-btn chat-call-btn--decline";
    accept.onclick = function () {
      // Unlock audio before accepting (in case it's still locked)
      unlockAudio();
      // Close current modal first so next modal (in-call) is not removed
      stopRingtone();
      closeModal();
      try {
        onAccept && onAccept({});
      } catch (_) { console.warn('[prchat-call] onAccept error:', _); }
    };
    decline.onclick = function () {
      stopRingtone();
      closeModal();
      try {
        onDecline && onDecline();
      } catch (_) { console.warn('[prchat-call] onDecline error:', _); }
    };
    actions.appendChild(accept);
    actions.appendChild(decline);
    wrap.appendChild(avatar);
    wrap.appendChild(title);
    wrap.appendChild(subtitle);
    wrap.appendChild(actions);
    openModal(wrap);
  }

  function showInCall(targetLabel, onEnd, opts) {
    opts = opts || {};
    stopRingtone();

    var isVideo = opts.isVideo || false;

    // For video calls, use floating controls instead of modal
    if (isVideo) {
      showVideoCallControls(targetLabel, onEnd, opts);
      return;
    }

    // Voice call - floating compact bar (non-blocking, can chat during call)
    showFloatingVoiceBar(targetLabel, onEnd, opts);
  }

  function showFloatingVoiceBar(targetLabel, onEnd, opts) {
    opts = opts || {};
    hideFloatingVoiceBar();
    ensureStyles();

    var bar = document.createElement("div");
    bar.id = "chat-call-voice-bar";
    bar.style.cssText =
      "position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:10000000;" +
      "background:rgba(17,24,39,0.95);backdrop-filter:blur(10px);" +
      "border-radius:50px;padding:8px 16px 8px 8px;" +
      "box-shadow:0 8px 32px rgba(0,0,0,0.3);" +
      "display:flex;align-items:center;gap:12px;cursor:grab;" +
      "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;";

    var avatarEl = document.createElement("div");
    avatarEl.style.cssText =
      "width:40px;height:40px;border-radius:50%;" +
      "background:linear-gradient(135deg,#60a5fa,#34d399);flex-shrink:0;overflow:hidden;";
    if (opts.avatarUrl) {
      avatarEl.style.backgroundImage = 'url("' + opts.avatarUrl + '")';
      avatarEl.style.backgroundSize = "cover";
      avatarEl.style.backgroundPosition = "center";
    }

    var info = document.createElement("div");
    info.style.cssText = "color:#fff;min-width:0;";
    var nameEl = document.createElement("div");
    nameEl.style.cssText = "font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;";
    nameEl.textContent = targetLabel || "Voice Call";
    var timerEl = document.createElement("div");
    timerEl.style.cssText = "font-size:11px;color:#9ca3af;";
    timerEl.id = "chat-call-voice-bar-timer";
    timerEl.textContent = "00:00";
    info.appendChild(nameEl);
    info.appendChild(timerEl);

    var muteBtn = document.createElement("button");
    muteBtn.style.cssText =
      "width:36px;height:36px;border-radius:50%;border:none;background:#374151;" +
      "color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;" +
      "flex-shrink:0;transition:background 0.15s;";
    var muteIcon = document.createElement("i");
    muteIcon.className = "fa fa-microphone";
    muteIcon.style.fontSize = "14px";
    muteBtn.appendChild(muteIcon);
    muteBtn.title = "Mute";
    muteBtn.onclick = function (e) {
      e.stopPropagation();
      try {
        if (window.__chatCallManager && window.__chatCallManager.setMuted) {
          var next = !window.__chatCallManager.isMuted();
          window.__chatCallManager.setMuted(next);
          muteIcon.className = next ? "fa fa-microphone-slash" : "fa fa-microphone";
          muteBtn.title = next ? "Unmute" : "Mute";
          muteBtn.style.background = next ? "#ef4444" : "#374151";
        }
      } catch (e2) { console.warn("[prchat-call] voice mute toggle error:", e2); }
    };

    var devicesBtn = document.createElement("button");
    devicesBtn.style.cssText =
      "width:36px;height:36px;border-radius:50%;border:none;background:#374151;" +
      "color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;" +
      "flex-shrink:0;transition:background 0.15s;";
    var devIcon = document.createElement("i");
    devIcon.className = "fa fa-cog";
    devIcon.style.fontSize = "14px";
    devicesBtn.appendChild(devIcon);
    devicesBtn.title = "Devices";
    devicesBtn.onclick = function (e) {
      e.stopPropagation();
      try { openDevicePicker(); } catch (e2) { console.error("[prchat-call] device picker error:", e2); }
    };

    var endBtn = document.createElement("button");
    endBtn.style.cssText =
      "width:36px;height:36px;border-radius:50%;border:none;background:#ef4444;" +
      "color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;" +
      "flex-shrink:0;transition:background 0.15s;";
    var endIcon = document.createElement("i");
    endIcon.className = "fa fa-phone";
    endIcon.style.cssText = "font-size:14px;transform:rotate(135deg)";
    endBtn.appendChild(endIcon);
    endBtn.title = "End call";
    endBtn.onclick = function (e) {
      e.stopPropagation();
      try { onEnd && onEnd(); } finally { hideFloatingVoiceBar(); }
    };

    bar.appendChild(avatarEl);
    bar.appendChild(info);
    bar.appendChild(muteBtn);
    bar.appendChild(devicesBtn);
    bar.appendChild(endBtn);
    document.body.appendChild(bar);

    // Drag logic
    (function initBarDrag() {
      var dragging = false, offsetX = 0, offsetY = 0;
      bar.addEventListener("mousedown", function (e) {
        if (e.target.tagName === "BUTTON" || e.target.tagName === "I") return;
        dragging = true;
        var rect = bar.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        bar.style.cursor = "grabbing";
        bar.style.transform = "none";
        e.preventDefault();
      });
      document.addEventListener("mousemove", function (e) {
        if (!dragging) return;
        bar.style.left = Math.max(0, Math.min(e.clientX - offsetX, window.innerWidth - bar.offsetWidth)) + "px";
        bar.style.top = Math.max(0, Math.min(e.clientY - offsetY, window.innerHeight - bar.offsetHeight)) + "px";
        bar.style.right = "auto";
        bar.style.bottom = "auto";
      });
      document.addEventListener("mouseup", function () {
        if (dragging) { dragging = false; bar.style.cursor = "grab"; }
      });
    })();

    // Acquire microphone
    (function acquireVoiceMedia() {
      var m = window.__chatCallManager;
      if (m && m.localStream) return;
      navigator.mediaDevices.getUserMedia({ audio: true })
        .then(function (stream) {
          var mgr = window.__chatCallManager;
          if (!mgr || !mgr.pc) { stream.getTracks().forEach(function (t) { t.stop(); }); return; }
          mgr.localStream = stream;
          stream.getTracks().forEach(function (track) {
            var senders = mgr.pc.getSenders();
            var replaced = false;
            for (var i = 0; i < senders.length; i++) {
              if (!senders[i].track && mgr.pc.getTransceivers) {
                var trs = mgr.pc.getTransceivers();
                for (var j = 0; j < trs.length; j++) {
                  if (trs[j].sender === senders[i] && trs[j].receiver &&
                      trs[j].receiver.track && trs[j].receiver.track.kind === track.kind) {
                    senders[i].replaceTrack(track).catch(function () {});
                    replaced = true;
                    break;
                  }
                }
              }
              if (replaced) break;
            }
            if (!replaced && mgr.pc) mgr.pc.addTrack(track, stream);
          });
          if (typeof mgr.onLocalTrack === "function") {
            try { mgr.onLocalTrack(stream); } catch (_) { console.warn("[prchat-call] voice onLocalTrack error:", _); }
          }
        })
        .catch(function (err) {
          console.warn("[prchat-call] voice getUserMedia failed:", err);
        });
    })();

    // Timer
    var startedAt = Date.now();
    if (window.__chatCallTimer) clearInterval(window.__chatCallTimer);
    window.__chatCallTimer = setInterval(function () {
      var s = Math.floor((Date.now() - startedAt) / 1000);
      var mm = String(Math.floor(s / 60)).padStart(2, "0");
      var ss = String(s % 60).padStart(2, "0");
      var t = document.getElementById("chat-call-voice-bar-timer");
      if (t) t.textContent = mm + ":" + ss;
    }, 1000);
  }

  function hideFloatingVoiceBar() {
    var bar = document.getElementById("chat-call-voice-bar");
    if (bar) bar.parentNode.removeChild(bar);
    if (window.__chatCallTimer) {
      clearInterval(window.__chatCallTimer);
      window.__chatCallTimer = null;
    }
  }

  function showVideoCallControls(targetLabel, onEnd, opts) {
    opts = opts || {};
    ensureStyles();

    // Clean up any previous PiP
    var oldPip = document.getElementById("chat-call-pip");
    if (oldPip) oldPip.parentNode.removeChild(oldPip);

    // ── PiP container ────────────────────────────────────────────────
    var pip = document.createElement("div");
    pip.id = "chat-call-pip";
    document.body.appendChild(pip);

    // Store default geometry for restore-from-minimize / restore-from-fullscreen
    var pipState = { w: 640, h: 480, top: null, left: null, minimized: false, fullscreen: false };

    // ── Top bar (drag handle + minimize / fullscreen buttons) ────────
    var topbar = document.createElement("div");
    topbar.id = "chat-call-pip-topbar";

    var minimizeBtn = document.createElement("button");
    minimizeBtn.title = "Minimize";
    minimizeBtn.innerHTML = '<i class="fa fa-minus" style="font-size:12px"></i>';
    minimizeBtn.onclick = function (e) { e.stopPropagation(); toggleMinimize(); };

    var fullscreenBtn = document.createElement("button");
    fullscreenBtn.title = "Fullscreen";
    fullscreenBtn.innerHTML = '<i class="fa fa-expand" style="font-size:12px"></i>';
    fullscreenBtn.onclick = function (e) { e.stopPropagation(); toggleFullscreen(); };

    topbar.appendChild(minimizeBtn);
    topbar.appendChild(fullscreenBtn);
    pip.appendChild(topbar);

    // ── Restore overlay (visible only when minimized) ────────────────
    var restoreOverlay = document.createElement("div");
    restoreOverlay.id = "chat-call-pip-restore";
    restoreOverlay.innerHTML = '<i class="fa fa-expand"></i>';
    restoreOverlay.onclick = function () { toggleMinimize(); };
    pip.appendChild(restoreOverlay);

    // ── Resize handle ────────────────────────────────────────────────
    var resizeHandle = document.createElement("div");
    resizeHandle.id = "chat-call-pip-resize";
    pip.appendChild(resizeHandle);

    // ── Controls bar ─────────────────────────────────────────────────
    var controlsId = "chat-video-call-controls";
    var controls = document.createElement("div");
    controls.id = controlsId;
    controls.style.cssText =
      "position:absolute;bottom:12px;left:50%;transform:translateX(-50%);" +
      "background:rgba(17,24,39,0.9);backdrop-filter:blur(10px);" +
      "padding:10px 20px;border-radius:50px;box-shadow:0 6px 24px rgba(0,0,0,0.3);" +
      "z-index:3;display:flex;align-items:center;gap:10px;" +
      "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;";

    var timer = document.createElement("div");
    timer.style.cssText = "color:#fff;font-size:13px;font-weight:600;margin-right:6px;min-width:44px;";
    timer.textContent = "00:00";

    var muteBtn = createControlButton("fa fa-microphone", "Mute");
    muteBtn.onclick = function () {
      try {
        if (window.__chatCallManager && window.__chatCallManager.setMuted) {
          var next = !window.__chatCallManager.isMuted();
          window.__chatCallManager.setMuted(next);
          var icon = muteBtn.querySelector("i");
          icon.className = next ? "fa fa-microphone-slash" : "fa fa-microphone";
          muteBtn.title = next ? "Unmute" : "Mute";
        }
      } catch (e) { console.warn('[prchat-call] video mute toggle error:', e); }
    };

    var cameraBtn = createControlButton("fa fa-video-camera", "Turn off camera");
    cameraBtn.onclick = function () {
      try {
        var mgr = window.__chatCallManager;
        if (!mgr) return;
        if (mgr.localStream) {
          var videoTrack = mgr.localStream.getVideoTracks()[0];
          if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            var icon = cameraBtn.querySelector("i");
            icon.className = videoTrack.enabled ? "fa fa-video-camera" : "fa fa-ban";
            cameraBtn.title = videoTrack.enabled ? "Turn off camera" : "Turn on camera";
            var lv = document.getElementById("chat-call-local-video");
            if (lv) lv.style.opacity = videoTrack.enabled ? "1" : "0.3";
          }
        } else if (mgr.pc) {
          var icon2 = cameraBtn.querySelector("i");
          icon2.className = "fa fa-spinner fa-spin";
          cameraBtn.title = "Requesting camera access…";
          Promise.race([
            navigator.mediaDevices.getUserMedia({ audio: true, video: true }),
            new Promise(function (_, rej) { setTimeout(function () { rej(new Error("timeout")); }, 8000); })
          ]).then(function (stream) {
            if (!mgr.pc) { stream.getTracks().forEach(function (t) { t.stop(); }); return; }
            attachStreamToPC(stream);
            icon2.className = "fa fa-video-camera";
            cameraBtn.title = "Turn off camera";
          }).catch(function (err) {
            console.error("[prchat-call] camera request failed:", err);
            icon2.className = "fa fa-video-camera";
            cameraBtn.title = "Turn on camera (click to retry)";
          });
        }
      } catch (e) {
        console.error("Error toggling camera:", e);
      }
    };

    var devicesBtn = createControlButton("fa fa-cog", "Settings");
    devicesBtn.onclick = function (ev) {
      ev.stopPropagation();
      try { openDevicePicker(); } catch (e) { console.error("[prchat-call] device picker error:", e); }
    };

    var endBtn = createControlButton("fa fa-phone", "End call", true);
    endBtn.onclick = function () {
      try { onEnd && onEnd(); } finally { hideVideoCallControls(); }
    };

    controls.appendChild(timer);
    controls.appendChild(muteBtn);
    controls.appendChild(cameraBtn);
    controls.appendChild(devicesBtn);
    controls.appendChild(endBtn);
    pip.appendChild(controls);

    // ── Shared helper: attach a getUserMedia stream to the peer connection ──
    function attachStreamToPC(stream) {
      var m = window.__chatCallManager;
      if (!m || !m.pc) { stream.getTracks().forEach(function (t) { t.stop(); }); return; }
      m.localStream = stream;
      var transceivers = m.pc.getTransceivers();
      stream.getTracks().forEach(function (track) {
        var matched = false;
        for (var i = 0; i < transceivers.length; i++) {
          var tr = transceivers[i];
          if (tr.sender && tr.sender.track === null && tr.receiver &&
              tr.receiver.track && tr.receiver.track.kind === track.kind) {
            tr.sender.replaceTrack(track).catch(function () {});
            matched = true;
            break;
          }
        }
        if (!matched && m.pc) m.pc.addTrack(track, stream);
      });
      if (typeof m.onLocalTrack === "function") {
        try { m.onLocalTrack(stream); } catch (_) { console.warn('[prchat-call] onLocalTrack error:', _); }
      }
      var ci = cameraBtn.querySelector("i");
      if (ci) ci.className = "fa fa-video-camera";
    }

    // ── Camera permission overlay (inside PiP) ──────────────────────
    (function showMediaPromptIfNeeded() {
      var promptId = "chat-call-media-prompt";
      var mgr = window.__chatCallManager;
      if (mgr && mgr.localStream) return;
      var old = document.getElementById(promptId);
      if (old) old.parentNode.removeChild(old);

      var prompt = document.createElement("div");
      prompt.id = promptId;
      prompt.style.cssText =
        "position:absolute;inset:0;z-index:6;" +
        "display:flex;flex-direction:column;align-items:center;justify-content:center;" +
        "cursor:pointer;background:rgba(0,0,0,0.75);";

      var box = document.createElement("div");
      box.style.cssText =
        "background:rgba(0,0,0,0.85);border:2px solid rgba(255,255,255,0.2);" +
        "border-radius:16px;padding:24px 32px;text-align:center;" +
        "box-shadow:0 20px 60px rgba(0,0,0,0.5);max-width:340px;";

      var iconEl = document.createElement("div");
      iconEl.style.cssText = "font-size:36px;margin-bottom:12px;color:#60a5fa;";
      var ic = document.createElement("i");
      ic.className = "fa fa-video-camera";
      iconEl.appendChild(ic);

      var heading = document.createElement("div");
      heading.style.cssText = "color:#fff;font-size:17px;font-weight:700;margin-bottom:6px;";
      heading.textContent = "Enable Camera & Microphone";

      var desc = document.createElement("div");
      desc.style.cssText = "color:#9ca3af;font-size:13px;line-height:1.4;margin-bottom:16px;";
      desc.textContent = "Click to allow camera and microphone access.";

      var btn = document.createElement("div");
      btn.style.cssText =
        "display:inline-block;background:#3b82f6;color:#fff;padding:10px 28px;" +
        "border-radius:999px;font-weight:600;font-size:14px;" +
        "box-shadow:0 4px 15px rgba(59,130,246,0.4);";
      btn.textContent = "Allow Access";

      box.appendChild(iconEl);
      box.appendChild(heading);
      box.appendChild(desc);
      box.appendChild(btn);
      prompt.appendChild(box);
      pip.appendChild(prompt);

      function requestMedia() {
        navigator.mediaDevices
          .getUserMedia({ audio: true, video: true })
          .then(function (stream) {
            var arrowEl = document.getElementById("chat-call-permission-arrow");
            if (arrowEl) arrowEl.parentNode.removeChild(arrowEl);
            var el = document.getElementById(promptId);
            if (el) el.parentNode.removeChild(el);
            attachStreamToPC(stream);
          })
          .catch(function (err) {
            console.error("[prchat-call] media request error:", err);
            var arrowEl = document.getElementById("chat-call-permission-arrow");
            if (arrowEl) arrowEl.parentNode.removeChild(arrowEl);
            heading.textContent = "Camera Access Needed";
            desc.textContent = "Permission denied. Click the padlock icon in your address bar, or continue with audio only.";
            btn.textContent = "Try Again";
            btn.style.opacity = "1";
            btn.style.background = "#3b82f6";
            // Add "Continue with Audio Only" button
            if (!document.getElementById("chat-call-audio-only-btn")) {
              var audioOnlyBtn = document.createElement("div");
              audioOnlyBtn.id = "chat-call-audio-only-btn";
              audioOnlyBtn.style.cssText =
                "display:inline-block;background:#10b981;color:#fff;padding:10px 28px;" +
                "border-radius:999px;font-weight:600;font-size:14px;margin-top:10px;cursor:pointer;" +
                "box-shadow:0 4px 15px rgba(16,185,129,0.4);";
              audioOnlyBtn.textContent = "Continue with Audio Only";
              audioOnlyBtn.onclick = function (e) {
                e.stopPropagation();
                navigator.mediaDevices.getUserMedia({ audio: true })
                  .then(function (audioStream) {
                    var el = document.getElementById(promptId);
                    if (el) el.parentNode.removeChild(el);
                    attachStreamToPC(audioStream);
                    var ci = cameraBtn.querySelector("i");
                    if (ci) ci.className = "fa fa-video-camera";
                    cameraBtn.title = "Turn on camera (click to retry)";
                  })
                  .catch(function () {
                    // Mic also denied — close prompt and continue without local media
                    var el = document.getElementById(promptId);
                    if (el) el.parentNode.removeChild(el);
                  });
              };
              box.appendChild(audioOnlyBtn);
            }
          });
      }

      function showWaitingState() {
        heading.textContent = "Look at the top of your browser";
        desc.textContent = "Click \"Allow\" in the popup near your address bar.";
        btn.textContent = "Waiting for permission…";
        btn.style.opacity = "0.7";
        btn.style.background = "#6b7280";
        if (!document.getElementById("chat-call-permission-arrow")) {
          var arrow = document.createElement("div");
          arrow.style.cssText =
            "position:fixed;top:0;left:50%;transform:translateX(-50%);" +
            "color:#fbbf24;font-size:16px;font-weight:700;padding:12px 24px;" +
            "background:rgba(0,0,0,0.9);border-bottom-left-radius:12px;" +
            "border-bottom-right-radius:12px;z-index:10000002;text-align:center;" +
            "animation:chat-wave 1.5s infinite ease-in-out;";
          arrow.id = "chat-call-permission-arrow";
          arrow.textContent = "Click \"Allow\" in the browser popup above";
          document.body.appendChild(arrow);
        }
      }

      btn.textContent = "Requesting…";
      btn.style.opacity = "0.7";
      requestMedia();

      prompt.onclick = function () {
        showWaitingState();
        requestMedia();
      };
    })();

    // ── Drag logic ───────────────────────────────────────────────────
    (function initDrag() {
      var dragging = false, offsetX = 0, offsetY = 0;
      topbar.addEventListener("mousedown", function (e) {
        if (e.target.tagName === "BUTTON" || e.target.tagName === "I") return;
        if (pipState.fullscreen || pipState.minimized) return;
        dragging = true;
        var rect = pip.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        topbar.style.cursor = "grabbing";
        e.preventDefault();
      });
      document.addEventListener("mousemove", function (e) {
        if (!dragging) return;
        var x = Math.max(0, Math.min(e.clientX - offsetX, window.innerWidth - pip.offsetWidth));
        var y = Math.max(0, Math.min(e.clientY - offsetY, window.innerHeight - pip.offsetHeight));
        pip.style.left = x + "px";
        pip.style.top = y + "px";
        pip.style.right = "auto";
        pip.style.bottom = "auto";
      });
      document.addEventListener("mouseup", function () {
        if (dragging) {
          dragging = false;
          topbar.style.cursor = "grab";
          pipState.top = pip.style.top;
          pipState.left = pip.style.left;
        }
      });
    })();

    // ── Resize logic ─────────────────────────────────────────────────
    (function initResize() {
      var resizing = false, startX = 0, startY = 0, startW = 0, startH = 0;
      resizeHandle.addEventListener("mousedown", function (e) {
        if (pipState.fullscreen || pipState.minimized) return;
        resizing = true;
        startX = e.clientX;
        startY = e.clientY;
        startW = pip.offsetWidth;
        startH = pip.offsetHeight;
        e.preventDefault();
        e.stopPropagation();
      });
      document.addEventListener("mousemove", function (e) {
        if (!resizing) return;
        var w = Math.max(320, startW + (e.clientX - startX));
        var h = Math.max(240, startH + (e.clientY - startY));
        pip.style.width = w + "px";
        pip.style.height = h + "px";
      });
      document.addEventListener("mouseup", function () {
        if (resizing) {
          resizing = false;
          pipState.w = pip.offsetWidth;
          pipState.h = pip.offsetHeight;
        }
      });
    })();

    // ── Minimize / Restore ───────────────────────────────────────────
    function toggleMinimize() {
      if (pipState.minimized) {
        pip.classList.remove("pip-minimized");
        pip.style.width = pipState.w + "px";
        pip.style.height = pipState.h + "px";
        if (pipState.top) pip.style.top = pipState.top;
        if (pipState.left) pip.style.left = pipState.left;
        pipState.minimized = false;
      } else {
        if (pipState.fullscreen) toggleFullscreen();
        pipState.w = pip.offsetWidth;
        pipState.h = pip.offsetHeight;
        pipState.top = pip.style.top;
        pipState.left = pip.style.left;
        pip.classList.add("pip-minimized");
        pip.style.bottom = "20px";
        pip.style.right = "20px";
        pip.style.top = "auto";
        pip.style.left = "auto";
        pipState.minimized = true;
      }
    }

    // ── Fullscreen toggle ────────────────────────────────────────────
    function toggleFullscreen() {
      if (pipState.fullscreen) {
        pip.classList.remove("pip-fullscreen");
        pip.style.width = pipState.w + "px";
        pip.style.height = pipState.h + "px";
        if (pipState.top) { pip.style.top = pipState.top; pip.style.bottom = "auto"; }
        else { pip.style.bottom = "20px"; pip.style.top = "auto"; }
        if (pipState.left) { pip.style.left = pipState.left; pip.style.right = "auto"; }
        else { pip.style.right = "20px"; pip.style.left = "auto"; }
        fullscreenBtn.innerHTML = '<i class="fa fa-expand" style="font-size:12px"></i>';
        fullscreenBtn.title = "Fullscreen";
        pipState.fullscreen = false;
      } else {
        if (pipState.minimized) toggleMinimize();
        pipState.w = pip.offsetWidth;
        pipState.h = pip.offsetHeight;
        pipState.top = pip.style.top;
        pipState.left = pip.style.left;
        pip.classList.add("pip-fullscreen");
        fullscreenBtn.innerHTML = '<i class="fa fa-compress" style="font-size:12px"></i>';
        fullscreenBtn.title = "Exit fullscreen";
        pipState.fullscreen = true;
      }
    }

    // Double-click top bar to toggle fullscreen
    topbar.addEventListener("dblclick", function (e) {
      if (e.target.tagName === "BUTTON" || e.target.tagName === "I") return;
      toggleFullscreen();
    });

    // ── Timer ────────────────────────────────────────────────────────
    var startedAt = Date.now();
    if (window.__chatCallTimer) clearInterval(window.__chatCallTimer);
    window.__chatCallTimer = setInterval(function () {
      var s = Math.floor((Date.now() - startedAt) / 1000);
      var mm = String(Math.floor(s / 60)).padStart(2, "0");
      var ss = String(s % 60).padStart(2, "0");
      timer.textContent = mm + ":" + ss;
    }, 1000);
  }

  function createControlButton(iconClass, title, isDanger) {
    var btn = document.createElement("button");
    btn.style.cssText =
      "width:48px;height:48px;border-radius:50%;border:none;" +
      "background:" +
      (isDanger ? "#ef4444" : "#374151") +
      ";" +
      "color:#fff;cursor:pointer;display:flex;align-items:center;" +
      "justify-content:center;transition:all 0.2s ease;" +
      "box-shadow:0 2px 8px rgba(0,0,0,0.2);";
    btn.title = title;
    btn.onmouseenter = function () {
      btn.style.transform = "scale(1.1)";
      btn.style.background = isDanger ? "#dc2626" : "#4b5563";
    };
    btn.onmouseleave = function () {
      btn.style.transform = "scale(1)";
      btn.style.background = isDanger ? "#ef4444" : "#374151";
    };
    var icon = document.createElement("i");
    icon.className = iconClass;
    icon.style.fontSize = "18px";
    btn.appendChild(icon);
    return btn;
  }

  function hideVideoCallControls() {
    var pip = document.getElementById("chat-call-pip");
    if (pip) pip.parentNode.removeChild(pip);
    var prompt = document.getElementById("chat-call-media-prompt");
    if (prompt) prompt.parentNode.removeChild(prompt);
    var arrow = document.getElementById("chat-call-permission-arrow");
    if (arrow) arrow.parentNode.removeChild(arrow);
    if (window.__chatCallTimer) {
      clearInterval(window.__chatCallTimer);
      window.__chatCallTimer = null;
    }
  }

  function openDevicePicker() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
      console.warn("[prchat-call] enumerateDevices not available");
      if (typeof alert_float !== "undefined") {
        alert_float(
          "warning",
          "Device selection not supported in this browser",
        );
      }
      return;
    }
    Promise.race([
      navigator.mediaDevices.enumerateDevices(),
      new Promise(function (_, rej) {
        setTimeout(function () { rej(new Error("enumerateDevices timeout")); }, 5000);
      })
    ])
      .then(function (devices) {
        var wrap = document.createElement("div");

        // Title
        var t = document.createElement("div");
        t.className = "chat-call-title";
        t.textContent = "Audio devices";

        // Subtitle
        var subtitle = document.createElement("div");
        subtitle.className = "chat-call-subtitle";
        subtitle.textContent = "Select your preferred microphone and speaker";

        // Microphone section
        var micSection = document.createElement("div");
        micSection.className = "chat-call-device-section";
        var micLabel = document.createElement("label");
        micLabel.className = "chat-call-device-label";
        micLabel.textContent = "Microphone:";
        var micSel = document.createElement("select");
        micSel.className = "chat-call-select";

        // Speaker section
        var spkSection = document.createElement("div");
        spkSection.className = "chat-call-device-section";
        var spkLabel = document.createElement("label");
        spkLabel.className = "chat-call-device-label";
        spkLabel.textContent = "Speaker:";
        var spkSel = document.createElement("select");
        spkSel.className = "chat-call-select";

        var mics = devices.filter(function (d) {
          return d.kind === "audioinput";
        });
        var spks = devices.filter(function (d) {
          return d.kind === "audiooutput";
        });

        // Populate microphone options
        if (mics.length === 0) {
          var om = document.createElement("option");
          om.value = "";
          om.textContent = "No microphones found";
          micSel.appendChild(om);
          micSel.disabled = true;
        } else {
          var defaultMic = document.createElement("option");
          defaultMic.value = "";
          defaultMic.textContent = "Default microphone";
          micSel.appendChild(defaultMic);
        }

        mics.forEach(function (d) {
          var o = document.createElement("option");
          o.value = d.deviceId;
          o.textContent = d.label || "Microphone " + micSel.options.length;
          micSel.appendChild(o);
        });

        // Populate speaker options
        if (spks.length === 0) {
          var os = document.createElement("option");
          os.value = "";
          os.textContent = "No speakers found";
          spkSel.appendChild(os);
          spkSel.disabled = true;
        } else {
          var defaultSpk = document.createElement("option");
          defaultSpk.value = "";
          defaultSpk.textContent = "Default speaker";
          spkSel.appendChild(defaultSpk);
        }

        spks.forEach(function (d) {
          var o = document.createElement("option");
          o.value = d.deviceId;
          o.textContent = d.label || "Speaker " + spkSel.options.length;
          spkSel.appendChild(o);
        });

        // Actions
        var actions = document.createElement("div");
        actions.className = "chat-call-actions";

        var cancel = document.createElement("button");
        cancel.textContent = "Cancel";
        cancel.className = "chat-call-btn chat-call-btn--neutral";
        cancel.onclick = function () {
          closeModal(true); // Close device modal only
        };

        var save = document.createElement("button");
        save.textContent = "Save";
        save.className = "chat-call-btn chat-call-btn--accept";
        save.onclick = function () {
          var micId = micSel.value;
          var spkId = spkSel.value;
          var promises = [];

          try {
            if (window.__chatCallManager) {
              if (micId && window.__chatCallManager.setAudioInput) {
                promises.push(window.__chatCallManager.setAudioInput(micId));
              }
              if (spkId && window.__chatCallManager.setAudioOutput) {
                window.__chatCallManager.setAudioOutput(spkId);
              }
            }
          } catch (e) {
            console.error("Error setting audio devices:", e);
          }

          Promise.all(promises).finally(function () {
            closeModal(true); // Close device modal only
            if (typeof alert_float !== "undefined") {
              alert_float("success", "Audio devices updated successfully");
            }
          });
        };

        // Build the modal
        micSection.appendChild(micLabel);
        micSection.appendChild(micSel);
        spkSection.appendChild(spkLabel);
        spkSection.appendChild(spkSel);
        actions.appendChild(cancel);
        actions.appendChild(save);

        wrap.appendChild(t);
        wrap.appendChild(subtitle);
        wrap.appendChild(micSection);
        wrap.appendChild(spkSection);
        wrap.appendChild(actions);

        openModal(wrap, { isDevicePicker: true });
      })
      .catch(function (error) {
        console.error("[prchat-call] enumerateDevices error:", error);
        if (typeof alert_float !== "undefined") {
          alert_float("warning", "Grant camera/microphone access first, then try Settings again");
        }
      });
  }

  function showNotice(titleText, options) {
    options = options || {};
    ensureStyles();
    var root = ensureModalRoot();
    var now = Date.now();
    if (!window.__chatCallLastNotice) {
      window.__chatCallLastNotice = { title: null, at: 0 };
    }
    var current = root.getAttribute("data-notice");
    if (
      (current && current === String(titleText)) ||
      (window.__chatCallLastNotice.title === String(titleText) &&
        now - window.__chatCallLastNotice.at < 2000)
    ) {
      return;
    }
    var wrap = document.createElement("div");
    var avatar = document.createElement("div");
    avatar.className = "chat-call-avatar";
    if (options.avatarUrl) {
      avatar.style.background =
        'url("' + options.avatarUrl + '") center / cover no-repeat';
    }
    var title = document.createElement("div");
    title.className = "chat-call-title";
    title.textContent = titleText || "";
    var actions = document.createElement("div");
    actions.className = "chat-call-actions";
    var ok = document.createElement("button");
    ok.textContent = "OK";
    ok.className = "chat-call-btn chat-call-btn--neutral";
    ok.onclick = function () {
      closeModal();
    };
    actions.appendChild(ok);
    wrap.appendChild(avatar);
    wrap.appendChild(title);
    wrap.appendChild(actions);
    root.setAttribute("data-notice", String(titleText));
    window.__chatCallLastNotice = { title: String(titleText), at: now };
    openModal(wrap);
    if (options.autoCloseMs && Number(options.autoCloseMs) > 0) {
      setTimeout(function () {
        closeModal();
      }, Number(options.autoCloseMs));
    }
  }

  // Ringtone using WebAudio API (gracefully no-ops if blocked)
  var __ringer = null;
  var __ringerCtx = null;
  var __unlockBound = false;
  var __ringtoneInterval = null; // For ChatSoundManager ringtones
  function ensureAudioCtx() {
    var Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    if (!__ringerCtx) __ringerCtx = new Ctx();
    return __ringerCtx;
  }
  function isAudioLocked() {
    // Check ChatSoundManager first if available (more accurate state)
    if (window.chatSoundManager) {
      // If ChatSoundManager has an audioContext and it's ready, we're unlocked
      if (window.chatSoundManager.audioContextReady) {
        return false;
      }
      // If it has an audioContext, check its state
      if (window.chatSoundManager.audioContext) {
        return window.chatSoundManager.audioContext.state === "suspended";
      }
    }

    // Fallback to local AudioContext
    var ctx = ensureAudioCtx();
    if (!ctx) {
      return true; // Assume locked if no context
    }
    return ctx.state === "suspended";
  }
  function unlockAudio() {
    try {
      // Unlock ChatSoundManager's AudioContext first if available
      if (
        window.chatSoundManager &&
        window.chatSoundManager.unlockAudioContext
      ) {
        window.chatSoundManager.unlockAudioContext();
      }

      // Also unlock local AudioContext
      var ctx = ensureAudioCtx();
      if (ctx && ctx.state === "suspended") {
        ctx.resume().catch(function () {});
      }
    } catch (e) { console.warn('[prchat-call] unlockAudio error:', e); }
  }
  function startRingtone(kind) {
    try {
      stopRingtone();

      // Try to unlock audio context if locked (non-intrusive attempt)
      if (isAudioLocked()) {
        unlockAudio();
      }

      // Use ChatSoundManager for consistent ringtone sounds
      if (window.chatSoundManager) {
        var playRingtone = function () {
          if (kind === "incoming") {
            window.chatSoundManager.playCallIncomingSound();
          } else {
            window.chatSoundManager.playSound("call_outgoing", null);
          }
        };

        playRingtone(); // Play immediately
        __ringtoneInterval = setInterval(
          playRingtone,
          kind === "incoming" ? 3000 : 2500,
        );
        return;
      }

      // Fallback to old system if ChatSoundManager not available
      var ctx = ensureAudioCtx();
      if (!ctx) return;
      if (ctx.state === "suspended") {
        ctx.resume().catch(function () {});
      }
      // unlock on first user gesture if blocked
      if (!__unlockBound) {
        __unlockBound = true;
        var unlock = function () {
          ctx.resume().catch(function () {});
          document.removeEventListener("pointerdown", unlock);
          document.removeEventListener("keydown", unlock);
        };
        document.addEventListener("pointerdown", unlock, { once: true });
        document.addEventListener("keydown", unlock, { once: true });
      }
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.type = "sine";
      osc.frequency.value = kind === "incoming" ? 480 : 440; // standard-ish tones
      gain.gain.value = 0;
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      var onMs = kind === "incoming" ? 800 : 1000;
      var offMs = kind === "incoming" ? 800 : 2000;
      // cadence using alternating on/off with smooth ramps
      var active = true;
      gain.gain.setTargetAtTime(0.25, ctx.currentTime, 0.02);
      var interval = setInterval(function () {
        active = !active;
        gain.gain.setTargetAtTime(active ? 0.25 : 0.0, ctx.currentTime, 0.02);
      }, onMs);
      // swap to off/on durations
      var flip = setInterval(function () {
        onMs =
          onMs === (kind === "incoming" ? 800 : 1000)
            ? offMs
            : kind === "incoming"
              ? 800
              : 1000;
      }, onMs);
      __ringer = {
        ctx: ctx,
        osc: osc,
        gain: gain,
        interval: interval,
        flip: flip,
      };
    } catch (e) {
      // ignore
    }
  }

  function stopRingtone() {
    try {
      // Stop ChatSoundManager interval
      if (__ringtoneInterval) {
        clearInterval(__ringtoneInterval);
        __ringtoneInterval = null;
      }

      // Fallback: stop old system
      if (__ringer) {
        clearInterval(__ringer.interval);
        if (__ringer.flip) clearInterval(__ringer.flip);
        __ringer.gain.gain.setTargetAtTime(0.0, __ringer.ctx.currentTime, 0.02);
        __ringer.osc.stop();
        __ringer = null;
      }
    } catch (e) {}
  }

  function showOutgoingCall(targetLabel, onCancel, opts) {
    opts = opts || {};
    stopRingtone();

    if (!isAudioLocked()) {
      startRingtone("outgoing");
    }
    var wrap = document.createElement("div");
    var avatar = document.createElement("div");
    avatar.className = "chat-call-avatar chat-call-wave";
    if (opts.avatarUrl) {
      avatar.style.backgroundImage = 'url("' + opts.avatarUrl + '")';
      avatar.style.backgroundSize = "cover";
      avatar.style.backgroundPosition = "center";
    }
    var title = document.createElement("div");
    title.className = "chat-call-title";

    // Add call type icon (video or voice)
    var isVideo = opts.isVideo || false;
    var callIcon = document.createElement("i");
    callIcon.className = isVideo ? "fa fa-video-camera" : "fa fa-phone";
    callIcon.style.cssText = "margin-right: 8px; color: #2196F3;";

    var titleText = document.createTextNode(
      "Calling " + (targetLabel || "User") + "…",
    );
    title.appendChild(callIcon);
    title.appendChild(titleText);

    var subtitle = document.createElement("div");
    subtitle.className = "chat-call-subtitle";
    subtitle.textContent = isVideo
      ? "Video call - Waiting for answer"
      : "Voice call - Waiting for answer";

    var showEnableSound = isAudioLocked();

    if (showEnableSound) {
      var unlock = document.createElement("button");
      unlock.textContent = "Enable sound";
      unlock.className = "chat-call-btn chat-call-btn--neutral";
      unlock.style.marginRight = "8px";
      unlock.onclick = function () {
        unlockAudio();
        startRingtone("outgoing");
        // Hide this button after clicking
        unlock.style.display = "none";
      };
      wrap.appendChild(unlock);
    }
    var cancel = document.createElement("button");
    cancel.textContent = "Cancel";
    cancel.className = "chat-call-btn chat-call-btn--neutral";
    cancel.onclick = function () {
      try {
        onCancel && onCancel();
      } finally {
        stopRingtone();
        closeModal();
      }
    };
    wrap.appendChild(avatar);
    wrap.appendChild(title);
    wrap.appendChild(subtitle);
    wrap.appendChild(cancel);
    openModal(wrap);
  }

  window.ChatCallUI = {
    showIncomingCall: showIncomingCall,
    showInCall: showInCall,
    showOutgoingCall: showOutgoingCall,
    showNotice: showNotice,
    stopRingtone: stopRingtone,
    closeModal: closeModal,
    hideVideoCallControls: hideVideoCallControls,
    hideFloatingVoiceBar: hideFloatingVoiceBar,
    unlockAudio: unlockAudio,
    isAudioLocked: isAudioLocked,
  };
})(window, document);

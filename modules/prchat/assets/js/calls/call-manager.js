/*
 * CallManager – WebRTC 1:1 calls with Pusher signaling.
 * URLs come from window.PRCHAT_CALLS (set by PHP admin_url, always absolute).
 * CSRF token from window.PRCHAT_CSRF.
 */
(function (window) {
  "use strict";

  var ICE_SERVERS = [];
  if (window.CHAT_CALLS_TURN_URL && window.CHAT_CALLS_TURN_USERNAME) {
    ICE_SERVERS.push({
      urls: [window.CHAT_CALLS_TURN_URL],
      username: window.CHAT_CALLS_TURN_USERNAME,
      credential: window.CHAT_CALLS_TURN_CREDENTIAL,
    });
  }
  ICE_SERVERS.push({ urls: ["stun:stun.l.google.com:19302"] });

  function prchatCallTrace() {
    var on =
      window.PRCHAT_DEBUG_CALLS === true ||
      (typeof localStorage !== "undefined" &&
        localStorage.getItem("prchat_debug_calls") === "1");
    if (!on) return;
    var args = Array.prototype.slice.call(arguments);
    args.unshift("[prchat-call]");
    console.log.apply(console, args);
  }
  window.prchatCallTrace = prchatCallTrace;

  function getUrl(key, fallback) {
    return (window.PRCHAT_CALLS && window.PRCHAT_CALLS[key]) || fallback || "";
  }

  function appendCsrf(body) {
    if (window.PRCHAT_CSRF) {
      body +=
        "&" +
        encodeURIComponent(PRCHAT_CSRF.name) +
        "=" +
        encodeURIComponent(PRCHAT_CSRF.hash);
    }
    return body;
  }

  function postSignal(url, body) {
    return window.fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: appendCsrf(body),
    });
  }

  // Match a local MediaStreamTrack to the correct RTCRtpSender by kind,
  // using transceivers (since sender.track is null before replaceTrack).
  function findSenderForTrack(pc, track) {
    var transceivers = pc.getTransceivers();
    for (var i = 0; i < transceivers.length; i++) {
      var t = transceivers[i];
      if (t.sender && t.sender.track === null && t.receiver && t.receiver.track && t.receiver.track.kind === track.kind) {
        return t.sender;
      }
    }
    for (var j = 0; j < transceivers.length; j++) {
      var t2 = transceivers[j];
      if (t2.sender && t2.sender.track === null && t2.mid !== null) {
        return t2.sender;
      }
    }
    return null;
  }

  function CallManager(signaling) {
    this.signaling = signaling;
    this.pc = null;
    this.localStream = null;
    this.remoteStream = null;
    this.onRemoteTrack = function () {};
    this.onLocalTrack = null;
    this.onStateChange = function () {};
    this.isVideo = false;
    this.selfId = null;
    this.peerId = null;
    this.pendingCandidates = [];
    this.remoteDescriptionSet = false;
    this.muted = false;
    this.hasEnded = false;
    this.audioInputId = null;
    this.audioOutputId = null;
    this.recipientType = "staff";
    this.callerType = "staff";
  }

  CallManager.prototype._createPeer = function () {
    prchatCallTrace("_createPeer", "self", this.selfId, "peer", this.peerId);
    var self = this;
    var pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

    pc.onicecandidate = function (e) {
      if (!e.candidate) {
        prchatCallTrace("ICE gathering complete");
        return;
      }
      var url = getUrl("ice");
      if (!url) {
        console.error("[prchat-call] PRCHAT_CALLS.ice not set");
        return;
      }
      var body =
        "from_id=" + encodeURIComponent(self.selfId) +
        "&to_id=" + encodeURIComponent(self.peerId) +
        "&candidate=" + encodeURIComponent(JSON.stringify(e.candidate));
      if (self.recipientType) {
        body += "&recipient_type=" + encodeURIComponent(self.recipientType);
      }
      postSignal(url, body).then(function (r) {
        prchatCallTrace("iceCandidate HTTP", r.status);
      }).catch(function (err) {
        console.error("[prchat-call] iceCandidate fetch error", err);
      });
    };

    pc.ontrack = function (ev) {
      prchatCallTrace("ontrack", ev.track && ev.track.kind);
      self.remoteStream = ev.streams[0];
      self.onRemoteTrack(self.remoteStream);
    };

    this.pc = pc;
  };

  CallManager.prototype._getMedia = function (isVideo) {
    this.isVideo = !!isVideo;
    prchatCallTrace("_getMedia isVideo=" + !!isVideo);
    var mediaPromise;
    if (isVideo && window.ChatMediaManager) {
      mediaPromise = window.ChatMediaManager.requestCamera();
    } else if (window.ChatMediaManager) {
      mediaPromise = window.ChatMediaManager.requestMicrophone();
    } else {
      var audio = this.audioInputId
        ? { deviceId: { exact: this.audioInputId } }
        : true;
      mediaPromise = navigator.mediaDevices.getUserMedia({ audio: audio, video: !!isVideo });
    }
    // Wrap with timeout — getUserMedia hangs indefinitely if the user ignores
    // the browser permission prompt. After 8s, reject so callers get feedback.
    return Promise.race([
      mediaPromise,
      new Promise(function (_, reject) {
        setTimeout(function () {
          reject(new Error("Camera/mic permission timeout – check your browser's address bar"));
        }, 8000);
      })
    ]);
  };

  // ── Outgoing call ──────────────────────────────────────────────────
  // Uses addTransceiver to build a proper SDP immediately without waiting
  // for getUserMedia. Media is acquired in parallel and tracks are replaced
  // on the existing senders once available.
  CallManager.prototype.call = function (selfId, peerId, isVideo, opts) {
    var self = this;
    selfId = parseInt(selfId, 10);
    peerId = parseInt(peerId, 10);
    prchatCallTrace("call() OUTGOING", "self=" + selfId, "peer=" + peerId, "video=" + !!isVideo);

    if (!selfId || selfId < 1) {
      console.error("[prchat-call] Invalid selfId", selfId);
      return Promise.reject(new Error("Invalid self id"));
    }
    if (!peerId || peerId < 1) {
      console.error("[prchat-call] Invalid peerId", peerId);
      return Promise.reject(new Error("Invalid peer id – select a contact"));
    }
    if (selfId === peerId) {
      return Promise.reject(new Error("Cannot call yourself"));
    }

    var startUrl = getUrl("start");
    if (!startUrl) {
      console.error("[prchat-call] PRCHAT_CALLS.start not set – cannot place call");
      return Promise.reject(new Error("PRCHAT_CALLS.start not configured"));
    }

    this.selfId = selfId;
    this.peerId = peerId;
    this.isVideo = !!isVideo;
    this.hasEnded = false;
    this.remoteDescriptionSet = false;
    this.pendingCandidates = [];
    this.recipientType = (opts && opts.recipientType) || "staff";
    this.callerType = (opts && opts.callerType) || "staff";

    this._createPeer();

    // Declare media types via transceivers so createOffer produces a full SDP
    // immediately without needing getUserMedia to resolve first.
    this.pc.addTransceiver("audio", { direction: "sendrecv" });
    if (isVideo) {
      this.pc.addTransceiver("video", { direction: "sendrecv" });
    }
    prchatCallTrace("transceivers added (audio" + (isVideo ? "+video" : "") + ")");

    // Media is acquired via the UI overlay (call-ui.js "Enable Camera" prompt)
    // to guarantee a user gesture. No background getUserMedia here — it would
    // queue in the browser and block subsequent requests if permission isn't
    // already granted.

    // Create offer → setLocalDescription → POST startCall (runs immediately)
    return self.pc.createOffer()
      .then(function (offer) {
        if (self.hasEnded || !self.pc) return Promise.reject(new Error("Call cancelled"));
        prchatCallTrace("createOffer OK, sdp length=" + (offer.sdp ? offer.sdp.length : 0));
        return self.pc.setLocalDescription(offer).then(function () { return offer; });
      })
      .then(function (offer) {
        if (self.hasEnded) return Promise.reject(new Error("Call cancelled"));
        var body =
          "from_id=" + encodeURIComponent(selfId) +
          "&to_id=" + encodeURIComponent(peerId) +
          "&is_video=" + (isVideo ? "1" : "0") +
          "&sdp=" + encodeURIComponent(JSON.stringify(offer));
        if (opts && opts.recipientType) {
          body += "&recipient_type=" + encodeURIComponent(opts.recipientType);
        }
        if (opts && opts.callerType) {
          body += "&caller_type=" + encodeURIComponent(opts.callerType);
        }
        prchatCallTrace("POST startCall →", startUrl);
        return postSignal(startUrl, body);
      })
      .then(function (res) {
        prchatCallTrace("startCall HTTP", res.status);
        return res.text().then(function (t) {
          if (!res.ok) {
            console.error("[prchat-call] startCall failed", res.status, t.slice(0, 200));
            return Promise.reject(new Error("startCall HTTP " + res.status));
          }
          var json;
          try { json = JSON.parse(t); } catch (e) { json = { success: false, _raw: t }; }
          prchatCallTrace("startCall OK", json);
          return json;
        });
      });
  };

  // ── Incoming call (answer) ─────────────────────────────────────────
  // Same non-blocking pattern as call(): set transceiver directions to
  // sendrecv so the answer SDP advertises send capability, then acquire
  // media in the background and hot-swap tracks onto existing senders.
  CallManager.prototype.answer = function (selfId, peerId, offerSdp, opts) {
    var self = this;
    prchatCallTrace("answer() INCOMING", "self=" + selfId, "peer=" + peerId);

    this.selfId = parseInt(selfId, 10) || selfId;
    this.peerId = parseInt(peerId, 10) || peerId;
    this.hasEnded = false;
    this.remoteDescriptionSet = false;
    this.pendingCandidates = [];
    this.recipientType = (opts && opts.recipientType) || "staff";
    this.callerType = (opts && opts.callerType) || "staff";
    this.isVideo = !!(offerSdp && offerSdp.sdp && offerSdp.sdp.indexOf("m=video") !== -1);

    this._createPeer();

    return this.pc
      .setRemoteDescription(new RTCSessionDescription(offerSdp))
      .then(function () {
        prchatCallTrace("setRemoteDescription(offer) OK");
        self.remoteDescriptionSet = true;
        self.pendingCandidates.forEach(function (c) {
          if (self.pc) self.pc.addIceCandidate(new RTCIceCandidate(c)).catch(function () {});
        });
        self.pendingCandidates = [];

        // Mark all transceivers as sendrecv so the answer SDP tells the
        // caller we intend to send media (tracks arrive via replaceTrack later).
        if (self.pc) {
          self.pc.getTransceivers().forEach(function (t) {
            if (t.direction === "recvonly" || t.direction === "inactive") {
              t.direction = "sendrecv";
            }
          });
          prchatCallTrace("transceivers set to sendrecv");
        }

        // Media is acquired via the UI overlay (call-ui.js "Enable Camera" prompt).
        // No background getUserMedia — avoids queuing browser permission requests.
      })
      .then(function () {
        if (self.hasEnded || !self.pc) return Promise.reject(new Error("Call cancelled before createAnswer"));
        return self.pc.createAnswer();
      })
      .then(function (answer) {
        prchatCallTrace("createAnswer OK, sdp length=" + (answer.sdp ? answer.sdp.length : 0));
        return self.pc.setLocalDescription(answer).then(function () {
          return answer;
        });
      })
      .then(function (answer) {
        var url = getUrl("answer");
        if (!url) return Promise.reject(new Error("PRCHAT_CALLS.answer not set"));
        var body =
          "from_id=" + encodeURIComponent(self.selfId) +
          "&to_id=" + encodeURIComponent(self.peerId) +
          "&sdp=" + encodeURIComponent(JSON.stringify(answer)) +
          "&is_video=" + (self.isVideo ? "1" : "0");
        if (self.recipientType) body += "&recipient_type=" + encodeURIComponent(self.recipientType);
        if (self.callerType) body += "&caller_type=" + encodeURIComponent(self.callerType);
        prchatCallTrace("POST answerCall →", url);
        return postSignal(url, body);
      })
      .then(function (res) {
        prchatCallTrace("answerCall HTTP", res.status);
        return res.text().then(function (t) {
          if (!res.ok) return Promise.reject(new Error("answerCall HTTP " + res.status));
          var json;
          try { json = JSON.parse(t); } catch (e) { json = { success: false, _raw: t }; }
          prchatCallTrace("answerCall OK", json);
          return json;
        });
      });
  };

  CallManager.prototype.handleRemoteAnswer = function (answerSdp) {
    var self = this;
    prchatCallTrace("handleRemoteAnswer");
    return this.pc
      .setRemoteDescription(new RTCSessionDescription(answerSdp))
      .then(function () {
        prchatCallTrace("setRemoteDescription(answer) OK");
        self.remoteDescriptionSet = true;
        self.pendingCandidates.forEach(function (c) {
          self.pc.addIceCandidate(new RTCIceCandidate(c)).catch(function () {});
        });
        self.pendingCandidates = [];
      });
  };

  CallManager.prototype.addIceCandidate = function (candidate) {
    if (!this.pc || !this.pc.remoteDescription || !this.pc.remoteDescription.type) {
      prchatCallTrace("addIceCandidate queued");
      this.pendingCandidates.push(candidate);
      return Promise.resolve();
    }
    prchatCallTrace("addIceCandidate apply");
    return this.pc.addIceCandidate(new RTCIceCandidate(candidate)).catch(function (e) { console.warn('[prchat-call] addIceCandidate failed:', e); });
  };

  // ── Hangup ─────────────────────────────────────────────────────────
  CallManager.prototype.hangup = function () {
    prchatCallTrace("hangup()", "peer=" + this.peerId);
    if (this.hasEnded) return;
    this.hasEnded = true;
    try {
      var url = getUrl("hangup");
      if (url && this.selfId && this.peerId) {
        var body =
          "from_id=" + encodeURIComponent(this.selfId) +
          "&to_id=" + encodeURIComponent(this.peerId);
        if (this.recipientType) body += "&recipient_type=" + encodeURIComponent(this.recipientType);
        postSignal(url, body).catch(function (e) { console.warn('[prchat-call] hangup signal failed:', e); });
      }
    } catch (e) { console.warn('[prchat-call] hangup error:', e); }
    this._cleanup();
    try {
      window.dispatchEvent(
        new CustomEvent("prchat-call-ended", {
          detail: { self: this.selfId, peer: this.peerId },
        })
      );
    } catch (e) { console.warn('[prchat-call] hangup dispatch error:', e); }
  };

  CallManager.prototype.endFromRemote = function () {
    if (this.hasEnded) return;
    this.hasEnded = true;
    this._cleanup();
    try {
      window.dispatchEvent(
        new CustomEvent("prchat-call-ended", {
          detail: { self: this.selfId, peer: this.peerId, fromRemote: true },
        })
      );
    } catch (e) { console.warn('[prchat-call] endFromRemote dispatch error:', e); }
  };

  CallManager.prototype._cleanup = function () {
    if (this.pc) {
      try {
        this.pc.getSenders().forEach(function (s) {
          if (s.track) s.track.stop();
        });
      } catch (e) { console.warn('[prchat-call] cleanup getSenders error:', e); }
      this.pc.close();
      this.pc = null;
    }
    if (this.localStream) {
      this.localStream.getTracks().forEach(function (t) { t.stop(); });
      this.localStream = null;
    }
    this.muted = false;
  };

  // ── Audio I/O helpers ──────────────────────────────────────────────
  CallManager.prototype.setAudioInput = function (deviceId) {
    this.audioInputId = deviceId || null;
    var self = this;
    if (!this.pc) return Promise.resolve();
    return navigator.mediaDevices
      .getUserMedia({ audio: self.audioInputId ? { deviceId: { exact: self.audioInputId } } : true })
      .then(function (stream) {
        var newTrack = stream.getAudioTracks()[0];
        if (!newTrack) return;
        self.localStream = self.localStream || new MediaStream();
        var sender = self.pc.getSenders().find(function (s) {
          return s.track && s.track.kind === "audio";
        });
        if (sender) {
          return sender.replaceTrack(newTrack).then(function () {
            self.localStream.getAudioTracks().forEach(function (t) { t.stop(); });
            self.localStream.addTrack(newTrack);
            self.muted = false;
          });
        }
        self.pc.addTrack(newTrack, stream);
        self.localStream.addTrack(newTrack);
        self.muted = false;
      });
  };

  CallManager.prototype.setAudioOutput = function (deviceId) {
    this.audioOutputId = deviceId || null;
    try {
      var el = document.getElementById("chat-call-remote");
      if (el && typeof el.setSinkId === "function" && this.audioOutputId) {
        el.setSinkId(this.audioOutputId).catch(function (e) { console.warn('[prchat-call] setSinkId failed:', e); });
      }
    } catch (e) { console.warn('[prchat-call] setAudioOutput error:', e); }
  };

  CallManager.prototype.connectMicrophone = function () {
    return this.setAudioInput(this.audioInputId);
  };

  CallManager.prototype.setMuted = function (muted) {
    this.muted = !!muted;
    if (this.localStream) {
      this.localStream.getAudioTracks().forEach(function (track) {
        track.enabled = !muted;
      });
    }
    if (this.peerId && this.selfId) {
      var url = getUrl("mute_status");
      if (url) {
        var body =
          "from_id=" + encodeURIComponent(this.selfId) +
          "&to_id=" + encodeURIComponent(this.peerId) +
          "&is_muted=" + (muted ? "1" : "0");
        if (this.recipientType) body += "&recipient_type=" + encodeURIComponent(this.recipientType);
        postSignal(url, body).catch(function () {});
      }
    }
  };

  CallManager.prototype.isMuted = function () {
    return !!this.muted;
  };

  CallManager.prototype.startCall = function (peerId, isVideo, opts) {
    var selfId =
      window.userSessionId ||
      (window.chatConfig && window.chatConfig.currentUserId);
    if (!selfId) {
      console.error("[prchat-call] userSessionId not defined");
      return Promise.reject(new Error("userSessionId not defined"));
    }
    return this.call(selfId, peerId, !!isVideo, opts || {});
  };

  window.ChatCallManager = CallManager;
})(window);

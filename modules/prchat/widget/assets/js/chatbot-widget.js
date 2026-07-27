/**
 * Chatbot Widget
 *
 * A zero-dependency, Shadow DOM isolated chatbot widget.
 */
(function () {
  "use strict";

  if (window.ChatbotWidgetLoaded) return;
  window.ChatbotWidgetLoaded = true;

  const CONFIG = window.CHATBOT_CONFIG || {};

  // Short notification sound (base64 encoded)
  const NOTIFICATION_SOUND =
    "data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAADhAC7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7v/////////////////////////////////" +
    "///////////AAAAATGF2YzU4LjEzAAAAAAAAAAAAAAAAJAAAAAAAAAAABIRNnMjFAAAAAAD/+1DEASMA0ABpBwAACAaAA0g4AABAAAH+IAAAAAAAAAAAADKRVNUID0gQW1hcmFYWCBHUk9VUAAAAAAAAAAAAP/7UMQOg8AAADSAAAAAgAAA0gAAABMQU1FMy4xMDBVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7UMQ6g8AAADSAAAAAgAAA0gAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV";

  function generateId() {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
      /[xy]/g,
      function (c) {
        const r = (Math.random() * 16) | 0;
        return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
      },
    );
  }

  function getVisitorId() {
    let id = localStorage.getItem("chatbot_visitor_id");
    if (!id) {
      id = generateId();
      localStorage.setItem("chatbot_visitor_id", id);
    }
    return id;
  }

  function getSessionId() {
    let id = sessionStorage.getItem("chatbot_session_id");
    if (!id) {
      id = generateId();
      sessionStorage.setItem("chatbot_session_id", id);
    }
    return id;
  }

  const ICON_SVGS = {
    chat: '<svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>',
    message:
      '<svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
    help: '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>',
    avatar:
      '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>',
  };

  class ChatbotWidget extends HTMLElement {
    constructor() {
      super();
      this.shadow = this.attachShadow({ mode: "closed" });
      this.config = null;
      this.isOpen = false;
      this.isLoading = false;
      this.messages = [];
      this.visitorId = getVisitorId();
      this.sessionId = getSessionId();
      this.conversationId = null;
      this.pusher = null;
      this.pusherChannel = null;
      this.typingInterval = null;
      this.humanConnected = false;
      this.waitingForAgent = false;
      this.conversationClosed = false;
      this.leadSubmitted =
        localStorage.getItem("chatbot_lead_submitted") === "true";
      this.gdprAccepted =
        localStorage.getItem("chatbot_gdpr_accepted") === "true";
      this.originalTitle = document.title;
      this.titleFlashInterval = null;
      this.unreadCount = 0;
      this.csatRated = false;
      this._proactiveKey = null;
      this._lastTypingSent = 0;
      this._typingStopTimer = null;
      this._staffTypingTimer = null;
      this._currentStaffName = null;
      this._currentStaffImage = null;
    }

    t(key, fallback, replacements) {
      let str =
        this.config && this.config.i18n && this.config.i18n[key]
          ? this.config.i18n[key]
          : fallback;
      if (replacements) {
        Object.keys(replacements).forEach((k) => {
          str = str.replace("%s", replacements[k]);
        });
      }
      return str;
    }

    esc(text) {
      if (!text) return "";
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    }

    sanitizeHtml(html) {
      if (!html) return "";
      let parsed;
      try {
        parsed = new DOMParser().parseFromString(html, "text/html");
      } catch (e) {
        return "";
      }
      const root = parsed && parsed.body;
      if (!root) return "";
      const allowedTags = ["b","i","strong","em","u","br","p","ul","ol","li","code","pre","a","span","h1","h2","h3","h4","h5","h6","blockquote"];
      const allowedAttrs = {"a":["href","title","target"],"span":["class"],"code":["class"],"pre":["class"]};
      const cleanNode = (node) => {
        if (node.nodeType === 3) return document.createTextNode(node.textContent);
        if (node.nodeType !== 1) return null;
        const tagName = node.tagName.toLowerCase();
        if (!allowedTags.includes(tagName)) {
          const fragment = document.createDocumentFragment();
          Array.from(node.childNodes).forEach(child => {
            const cleaned = cleanNode(child);
            if (cleaned) fragment.appendChild(cleaned);
          });
          return fragment;
        }
        const cleanEl = document.createElement(tagName);
        if (allowedAttrs[tagName]) {
          allowedAttrs[tagName].forEach(attr => {
            if (node.hasAttribute(attr)) {
              let value = node.getAttribute(attr);
              if (attr === "href") {
                if (!value || !/^https?:\/\//i.test(value)) return;
                const low = value.trim().toLowerCase();
                if (low.indexOf("javascript:") === 0 || low.indexOf("data:") === 0 || low.indexOf("vbscript:") === 0) return;
              }
              if (/^on/i.test(attr) || attr === "style") return;
              cleanEl.setAttribute(attr, value);
            }
          });
        }
        Array.from(node.childNodes).forEach(child => {
          const cleaned = cleanNode(child);
          if (cleaned) cleanEl.appendChild(cleaned);
        });
        return cleanEl;
      };
      const fragment = document.createDocumentFragment();
      Array.from(root.childNodes).forEach(child => {
        const cleaned = cleanNode(child);
        if (cleaned) fragment.appendChild(cleaned);
      });
      const cleanDiv = document.createElement("div");
      cleanDiv.appendChild(fragment);
      return cleanDiv.innerHTML;
    }
    getAvatarHtml() {
      return this.config.agent_avatar_url
        ? '<img src="' +
            this.esc(this.config.agent_avatar_url) +
            '" alt="' +
            this.esc(this.config.name || "Assistant") +
            '">'
        : ICON_SVGS.avatar;
    }
    connectedCallback() {
      this.widgetKey = this.getAttribute("widget-key") || CONFIG.widget_key;
      this.apiUrl = CONFIG.api_url;

      if (!this.widgetKey || !this.apiUrl) return;
      this.loadConfig();
    }

    async loadConfig() {
      try {
        const response = await fetch(`${this.apiUrl}/config/${this.widgetKey}`);
        if (!response.ok) throw new Error("Failed to load config");
        this.config = await response.json();
        this.render();
        this.loadHistory();
        this.initPusher();
        this.startProactiveTrigger();
      } catch (error) {
        return;
      }
    }

    startProactiveTrigger() {
      if (!this.config.proactive_enabled) return;
      // When lead capture is required, do not auto-open; visitor must click the widget first, then see the lead form.
      if (this.needsLeadForm()) return;
      // One auto-open per tab session: avoid opening again on every page load as the visitor navigates.
      // Key is set when we actually open. If key exists we skip scheduling the timer entirely.
      // To test a different delay (e.g. 5 vs 15s), use a new tab or clear Session Storage for this site.
      const key = "chatbot_proactive_" + (this.widgetKey || "");
      try {
        if (sessionStorage.getItem(key)) return;
      } catch (e) {
        return;
      }
      const delaySec = Math.min(120, Math.max(0, Number(this.config.proactive_delay_seconds) || 5));
      const delayMs = delaySec * 1000;
      const openWhenReady = () => {
        try {
          if (sessionStorage.getItem(key)) return;
          if (this.isOpen) return;
          if (!this.shadow || !this.shadow.querySelector(".widget-container")) return;
          // Don't set the flag yet - only set after GDPR accepted or message sent
          // This allows widget to re-open on reload if user didn't engage
          this._openedByProactive = true;
          this._proactiveKey = key; // Store key to set later
          this.open();
        } catch (e) {}
      };
      // Wait for widget to be in DOM and layout (rAF + short delay), then wait delaySec before opening
      const startCountdown = () => {
        setTimeout(openWhenReady, delayMs);
      };
      const readyDelay = 400;
      setTimeout(() => {
        if (typeof requestAnimationFrame !== "undefined") {
          requestAnimationFrame(startCountdown);
        } else {
          startCountdown();
        }
      }, readyDelay);
    }

    async loadAndShowProactiveSuggestions() {
      try {
        const res = await fetch(`${this.apiUrl}/suggested_questions/${this.widgetKey}`);
        if (!res.ok) return;
        const data = await res.json();
        if (data.suggestions && data.suggestions.length) {
          this.showProactiveSuggestions(data.suggestions);
        }
      } catch (e) {}
    }

    showProactiveSuggestions(suggestions) {
      const messagesEl = this.shadow.querySelector(".widget-messages");
      if (!messagesEl) return;

      const block = document.createElement("div");
      block.className = "proactive-suggestions";
      block._suggestions = suggestions;
      const title = this.t("proactive_suggested_title", "Suggested questions");
      let html = '<p class="proactive-suggestions-title">' + this.esc(title) + '</p><div class="proactive-suggestions-chips">';
      suggestions.forEach((s, i) => {
        const q = (s.question || "").trim();
        if (!q) return;
        html += '<button type="button" class="proactive-suggestion-chip" data-idx="' + i + '">' + this.esc(q) + "</button>";
      });
      html += "</div>";
      block.innerHTML = html;

      block.querySelectorAll(".proactive-suggestion-chip").forEach((btn) => {
        btn.addEventListener("click", () => {
          const idx = parseInt(btn.getAttribute("data-idx"), 10);
          const s = block._suggestions && block._suggestions[idx];
          if (s) {
            const question = (s.question || "").trim();
            if (question) {
              btn.disabled = true;
              btn.setAttribute("aria-pressed", "true");
              this.sendMessage(question);
            }
          }
          const messagesEl2 = this.shadow.querySelector(".widget-messages");
          if (messagesEl2) messagesEl2.scrollTop = messagesEl2.scrollHeight;
        });
      });
      messagesEl.appendChild(block);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    initPusher() {
      if (!this.config.pusher_key || !this.config.pusher_cluster) return;
      if (typeof Pusher === "undefined") {
        const script = document.createElement("script");
        script.src = "https://js.pusher.com/8.0.0/pusher.min.js";
        script.onload = () => this.connectPusher();
        document.head.appendChild(script);
      } else {
        this.connectPusher();
      }
    }

    connectPusher() {
      if (typeof Pusher === "undefined" || !this.config.pusher_key) return;
      try {
        this.pusher = new Pusher(this.config.pusher_key, {
          cluster: this.config.pusher_cluster,
        });
      } catch (error) {}
      if (this.pusher && this.conversationId && !this.pusherChannel) {
        this.subscribeToPusherChannel();
      }
    }

    subscribeToPusherChannel() {
      if (!this.pusher || !this.conversationId) return;
      if (this.pusherChannel) {
        this.pusher.unsubscribe("chatbot-conversation-" + this.conversationId);
      }
      try {
        const channelName = "chatbot-conversation-" + this.conversationId;
        this.pusherChannel = this.pusher.subscribe(channelName);

        this.pusherChannel.bind("staff-message", (data) => {
          this.hideTyping();
          this.hideStaffTyping();
          this.addMessage(
            "assistant",
            data.content,
            data.staff_name,
            data.staff_image,
            data.time,
          );
          this.notifyNewMessage();
        });

        this.pusherChannel.bind("human-takeover", (data) => {
          this.hideTyping();
          this.humanConnected = true;
          this.waitingForAgent = false;
          this.conversationClosed = false;
          this.updateHeaderForHuman(data.staff_name, data.staff_image);
          this.enableInput();
          this.addSystemMessage(data.message);
        });

        this.pusherChannel.bind("conversation-closed", (data) => {
          this.hideTyping();
          this.humanConnected = false;
          this.waitingForAgent = false;
          this.conversationClosed = true;
          this.updateHeaderForClosed();
          this.addSystemMessage(data.message);
          this.disableInput();
          // Show CSAT rating if backend says conversation is eligible (has staff messages)
          if (data.csat_eligible !== false) {
            this.checkShowCSAT();
          }
        });

        this.pusherChannel.bind("staff-typing", (data) => {
          if (data.typing) {
            this.showStaffTyping(data.staff_name);
          } else {
            this.hideStaffTyping();
          }
        });

        this.pusherChannel.bind("conversation-deleted", () => {
          this.startNewChat();
        });
      } catch (error) {}
    }

    // --- Sound & Tab Notifications ---

    notifyNewMessage() {
      if (!this.isOpen || document.hidden) {
        this.playSound();
        this.unreadCount++;
        this.updateUnreadBadge();
        this.startTitleFlash();
      }
    }

    updateUnreadBadge() {
      const badge = this.shadow && this.shadow.querySelector(".widget-badge");
      if (!badge) return;
      if (this.unreadCount <= 0) {
        badge.style.display = "none";
        badge.textContent = "";
      } else {
        badge.textContent = this.unreadCount > 99 ? "99+" : String(this.unreadCount);
        badge.style.display = "flex";
      }
    }

    playSound() {
      try {
        const audio = new Audio(NOTIFICATION_SOUND);
        audio.volume = 0.4;
        audio.play().catch(() => {});
      } catch (e) {}
    }

    startTitleFlash() {
      if (this.titleFlashInterval) return;
      const orig = this.originalTitle;
      let toggle = false;
      this.titleFlashInterval = setInterval(() => {
        document.title = toggle
          ? orig
          : `(${this.unreadCount}) ${this.t("new_message", "New message...")}`;
        toggle = !toggle;
      }, 1000);
    }

    stopTitleFlash() {
      if (this.titleFlashInterval) {
        clearInterval(this.titleFlashInterval);
        this.titleFlashInterval = null;
      }
      this.unreadCount = 0;
      document.title = this.originalTitle;
    }

    // --- Header State ---

    updateHeaderForHuman(staffName, staffImage) {
      this._currentStaffName = staffName || null;
      this._currentStaffImage = staffImage || null;
      const subtitle = this.shadow.querySelector(".widget-header-info p");
      if (subtitle) {
        const name = staffName || this.t("support_agent", "Support Agent");
        subtitle.textContent = this.t(
          "connected_with",
          "Connected with {name}",
        ).replace("{name}", name);
        subtitle.style.color = "#90EE90";
      }
      if (staffImage) {
        const avatarEl = this.shadow.querySelector(".widget-avatar");
        if (avatarEl) {
          avatarEl.innerHTML =
            '<img src="' + this.esc(staffImage) + '" alt="">';
        }
      }
    }

    updateHeaderForAI() {
      const subtitle = this.shadow.querySelector(".widget-header-info p");
      if (subtitle) {
        subtitle.textContent =
          this.config.intro_subtitle ||
          (this.config.i18n && this.config.i18n.reply_time) ||
          this.t("ai_assistant", "AI Assistant");
        subtitle.style.color = "";
      }
      const avatarEl = this.shadow.querySelector(".widget-avatar");
      if (avatarEl) avatarEl.innerHTML = this.getAvatarHtml();
    }

    updateHeaderForClosed() {
      const subtitle = this.shadow.querySelector(".widget-header-info p");
      if (subtitle) {
        subtitle.textContent = this.t(
          "conversation_ended",
          "Conversation ended",
        );
        subtitle.style.color = "#ffcdd2";
      }
    }
    disableInput() {
      const inputArea = this.shadow.querySelector(".widget-input-area");
      if (inputArea && this.conversationClosed) {
        inputArea.innerHTML = `<button type="button" class="widget-new-chat-btn">${this.t("start_new_chat", "Start New Chat")}</button>`;
        inputArea
          .querySelector(".widget-new-chat-btn")
          .addEventListener("click", () => this.startNewChat());
      } else {
        const input = this.shadow.querySelector(".widget-input");
        const btn = this.shadow.querySelector(".widget-send");
        if (input) {
          input.disabled = true;
          input.placeholder = this.t("please_wait", "Please wait...");
        }
        if (btn) btn.disabled = true;
      }
    }

    updateHeaderForWaitingAgent() {
      const subtitle = this.shadow.querySelector(".widget-header-info p");
      if (subtitle) {
        subtitle.textContent = this.t(
          "connecting_agent",
          "Connecting you with an agent...",
        );
        subtitle.style.color = "#f59e0b";
      }
    }

    showWaitingForAgentInputState() {
      this.waitingForAgent = true;
      const input = this.shadow.querySelector(".widget-input");
      const btn = this.shadow.querySelector(".widget-send");
      if (input) {
        input.disabled = true;
        input.placeholder = this.t("please_wait", "Please wait...");
      }
      if (btn) btn.disabled = true;
    }

    startNewChat() {
      this.conversationId = null;
      this.conversationClosed = false;
      this.humanConnected = false;
      this.waitingForAgent = false;
      this.messages = [];
      this.leadSubmitted = false;
      this.csatRated = false;
      localStorage.removeItem("chatbot_lead_submitted");
      const newSessionId =
        "sess_" + Math.random().toString(36).substring(2, 15);
      sessionStorage.setItem("chatbot_session_id", newSessionId);
      this.sessionId = newSessionId;
      if (this.pusherChannel && this.pusher) {
        this.pusher.unsubscribe(this.pusherChannel.name);
        this.pusherChannel = null;
      }
      this.render();
      this.updateHeaderForAI();
      this.open();
    }

    enableInput() {
      const input = this.shadow.querySelector(".widget-input");
      const btn = this.shadow.querySelector(".widget-send");
      if (input) {
        input.disabled = false;
        input.placeholder =
          this.config.input_placeholder ||
          this.t("type_message", "Type your message...");
      }
      if (btn) btn.disabled = false;
    }

    addSystemMessage(text) {
      const messagesEl = this.shadow.querySelector(".widget-messages");
      if (!messagesEl) return;
      const systemMsg = document.createElement("div");
      systemMsg.className = "message system";
      systemMsg.textContent = text;
      messagesEl.appendChild(systemMsg);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async loadHistory() {
      try {
        const response = await fetch(
          `${this.apiUrl}/history/${this.widgetKey}`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ session_id: this.sessionId }),
          },
        );
        if (!response.ok) return;
        const data = await response.json();
        if (data.messages && data.messages.length > 0) {
          this.messages = data.messages;
          this.renderMessages();
          if (this.config.capture_leads) this.leadSubmitted = true;

          if (
            !this.isOpen &&
            localStorage.getItem("chatbot_widget_open") === "true"
          ) {
            this.open();
          }
        }
        if (data.conversation_id) {
          this.conversationId = data.conversation_id;
          this.subscribeToPusherChannel();
        }
        if (data.closed) {
          this.conversationClosed = true;
          this.updateHeaderForClosed();
          this.disableInput();
          if (data.csat_eligible !== false) {
            this.checkShowCSAT();
          }
        } else if (data.human_handling) {
          this.humanConnected = true;
          this.waitingForAgent = false;
          this.updateHeaderForHuman(data.staff_name, data.staff_image);
          this.enableInput();
        } else if (data.waiting_for_agent) {
          this.waitingForAgent = true;
          this.updateHeaderForWaitingAgent();
          this.showWaitingForAgentInputState();
        } else if (data.escalation_form) {
          if (this.config.lead_fields && !this.leadSubmitted) {
            this.showEscalationLeadForm();
          } else {
            this.confirmEscalation();
          }
        } else if (data.message_limit_reached) {
          this.addSystemMessage(
            this.t(
              "message_limit",
              "You have reached the message limit for this conversation. Please contact us directly for further assistance.",
            ),
          );
          this.disableInput();
        }
      } catch (error) {}
    }

    // --- Lead Form ---

    needsLeadForm() {
      return (
        this.config.capture_leads &&
        this.config.lead_fields &&
        !this.leadSubmitted
      );
    }

    needsGdprConsent() {
      return this.config.gdpr_enabled && !this.gdprAccepted;
    }

    showLeadForm() {
      const fields = this.config.lead_fields;
      const messagesEl = this.shadow.querySelector(".widget-messages");
      if (!messagesEl) return;

      const welcome = messagesEl.querySelector(".welcome-message");
      if (welcome) welcome.remove();

      const form = document.createElement("div");
      form.className = "lead-form";
      let html = `<div class="lead-form-header"><h4>${this.t("lead_title", "Before we start")}</h4><p>${this.t("lead_subtitle", "Please tell us a bit about yourself")}</p></div>`;

      // Email (always required)
      html += `<div class="lead-form-field lead-field-icon">
                <svg class="lead-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                <input type="email" class="lead-input" data-field="email" placeholder="Email *" required />
            </div>`;

      // Name
      if (fields.name && fields.name.enabled) {
        const req = fields.name.required ? " *" : "";
        html += `<div class="lead-form-field lead-field-icon">
                    <svg class="lead-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <input type="text" class="lead-input" data-field="name" placeholder="${fields.name.label || "Name"}${req}" ${fields.name.required ? "required" : ""} />
                </div>`;
      }

      // Phone
      if (fields.phone && fields.phone.enabled) {
        const req = fields.phone.required ? " *" : "";
        html += `<div class="lead-form-field lead-field-icon">
                    <svg class="lead-icon" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    <input type="tel" class="lead-input" data-field="phone" placeholder="${fields.phone.label || "Phone"}${req}" ${fields.phone.required ? "required" : ""} />
                </div>`;
      }

      // GDPR consent inline (if needed)
      if (this.needsGdprConsent()) {
        const text =
          this.config.gdpr_consent_text ||
          this.t(
            "gdpr_default",
            "By chatting, you agree to our privacy policy.",
          );
        const url = this.config.gdpr_privacy_url;
        const linkText = url
          ? ` <a href="${url}" target="_blank" rel="noopener" style="color:inherit;">${this.t("privacy_policy", "Privacy Policy")}</a>`
          : "";
        html += `<div class="lead-form-field lead-gdpr-row"><label><input type="checkbox" class="lead-gdpr-check" /> <span>${text}${linkText}</span></label></div>`;
      }

      html += `<div class="lead-form-field"><button type="button" class="lead-submit-btn">${this.t("lead_start", "Start Chat")}</button></div>`;
      html += '<div class="lead-form-error" style="display:none;"></div>';
      form.innerHTML = html;

      messagesEl.appendChild(form);

      // Disable main input while lead form is active
      const inputArea = this.shadow.querySelector(".widget-input-area");
      if (inputArea) inputArea.style.display = "none";

      form
        .querySelector(".lead-submit-btn")
        .addEventListener("click", () => this.submitLeadForm(form));

      // Enter key on inputs
      form.querySelectorAll(".lead-input").forEach((inp) => {
        inp.addEventListener("keydown", (e) => {
          if (e.key === "Enter") {
            e.preventDefault();
            this.submitLeadForm(form);
          }
        });
      });
    }

    async submitLeadForm(formEl) {
      const errorEl = formEl.querySelector(".lead-form-error");
      errorEl.style.display = "none";

      const email = formEl.querySelector('[data-field="email"]').value.trim();
      const nameInput = formEl.querySelector('[data-field="name"]');
      const phoneInput = formEl.querySelector('[data-field="phone"]');
      const gdprCheck = formEl.querySelector(".lead-gdpr-check");

      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errorEl.textContent = this.t(
          "email_invalid",
          "Please enter a valid email address.",
        );
        errorEl.style.display = "block";
        return;
      }

      const fields = this.config.lead_fields;
      if (
        nameInput &&
        fields.name &&
        fields.name.required &&
        !nameInput.value.trim()
      ) {
        errorEl.textContent = this.t("name_required", "Name is required.");
        errorEl.style.display = "block";
        return;
      }
      if (
        phoneInput &&
        fields.phone &&
        fields.phone.required &&
        !phoneInput.value.trim()
      ) {
        errorEl.textContent = this.t("phone_required", "Phone is required.");
        errorEl.style.display = "block";
        return;
      }
      if (
        phoneInput &&
        phoneInput.value.trim() &&
        !/^\+?[\d\s\-().]{7,20}$/.test(phoneInput.value.trim())
      ) {
        errorEl.textContent = this.t(
          "phone_invalid",
          "Please enter a valid phone number.",
        );
        errorEl.style.display = "block";
        return;
      }
      if (gdprCheck && !gdprCheck.checked) {
        errorEl.textContent = this.t(
          "gdpr_required",
          "Please accept the privacy policy to continue.",
        );
        errorEl.style.display = "block";
        return;
      }

      const btn = formEl.querySelector(".lead-submit-btn");
      btn.disabled = true;
      btn.textContent = this.t("please_wait", "Please wait...");

      try {
        const body = {
          email: email,
          name: nameInput ? nameInput.value.trim() : "",
          phone: phoneInput ? phoneInput.value.trim() : "",
          visitor_id: this.visitorId,
          session_id: this.sessionId,
          gdpr_consent: gdprCheck ? gdprCheck.checked : false,
        };

        const response = await fetch(`${this.apiUrl}/lead/${this.widgetKey}`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(body),
        });

        const data = await response.json();
        if (data.success) {
          this.leadSubmitted = true;
          localStorage.setItem("chatbot_lead_submitted", "true");
          if (gdprCheck && gdprCheck.checked) {
            this.gdprAccepted = true;
            localStorage.setItem("chatbot_gdpr_accepted", "true");
          }

          formEl.remove();
          const inputArea = this.shadow.querySelector(".widget-input-area");
          if (inputArea) inputArea.style.display = "flex";

          // Show success message
          const successMsg =
            this.config.lead_capture_success_message ||
            this.t(
              "lead_success",
              "Thank you! Your info has been saved. How can we help you today?",
            );
          const messagesEl = this.shadow.querySelector(".widget-messages");
          const welcomeDiv = document.createElement("div");
          welcomeDiv.className = "welcome-message";
          welcomeDiv.innerHTML = `<h4>${successMsg}</h4>`;
          messagesEl.appendChild(welcomeDiv);

          if (data.conversation_id) {
            this.conversationId = data.conversation_id;
            this.subscribeToPusherChannel();
          }

          this.enableInput();
          this.bindEvents();
          if (
            this.config.proactive_enabled &&
            this.messages.length === 0 &&
            !this.shadow.querySelector(".proactive-suggestions")
          ) {
            this.loadAndShowProactiveSuggestions();
          }
          this.shadow.querySelector(".widget-input").focus();
        } else {
          errorEl.textContent =
            data.error ||
            this.t("error_generic", "Something went wrong. Please try again.");
          errorEl.style.display = "block";
          btn.disabled = false;
          btn.textContent = this.t("lead_start", "Start Chat");
        }
      } catch (e) {
        errorEl.textContent = this.t(
          "error_network",
          "Network error. Please try again.",
        );
        errorEl.style.display = "block";
        btn.disabled = false;
        btn.textContent = this.t("lead_start", "Start Chat");
      }
    }

    // --- Escalation Lead Form (shown during human escalation) ---
    showEscalationLeadForm() {
      const fields = this.config.lead_fields;
      const messagesEl = this.shadow.querySelector(".widget-messages");

      const form = document.createElement("div");
      form.className = "escalation-lead-form";
      let html = `<div class="lead-form-header"><h4>${this.t("escalation_lead_title", "Contact Information")}</h4><p>${this.t("escalation_lead_subtitle", "Help us connect you with the right agent")}</p></div>`;

      // Email field (always required for escalation)
      html += `<div class="lead-form-field lead-field-icon">
        <svg class="lead-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
        <input type="email" class="lead-input" data-field="email" placeholder="${this.t("lead_email", "Your Email")} *" required />
      </div>`;

      // Name field
      if (fields.name && fields.name.enabled) {
        const req = fields.name.required ? " *" : "";
        html += `<div class="lead-form-field lead-field-icon">
          <svg class="lead-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          <input type="text" class="lead-input" data-field="name" placeholder="${fields.name.label || this.t("lead_name", "Your Name")}${req}" ${fields.name.required ? "required" : ""} />
        </div>`;
      }

      // Phone field
      if (fields.phone && fields.phone.enabled) {
        const req = fields.phone.required ? " *" : "";
        html += `<div class="lead-form-field lead-field-icon">
          <svg class="lead-icon" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
          <input type="tel" class="lead-input" data-field="phone" placeholder="${fields.phone.label || this.t("lead_phone", "Your Phone")}${req}" ${fields.phone.required ? "required" : ""} />
        </div>`;
      }

      // GDPR consent if enabled
      if (this.needsGdprConsent()) {
        const text =
          this.config.gdpr_consent_text ||
          this.t("gdpr_default", "By chatting, you agree to our privacy policy.");
        const url = this.config.gdpr_privacy_url;
        const linkText = url
          ? ` <a href="${url}" target="_blank" rel="noopener" style="color:inherit;">${this.t("privacy_policy", "Privacy Policy")}</a>`
          : "";
        html += `<div class="lead-form-field lead-gdpr-row"><label><input type="checkbox" class="lead-gdpr-check" /> <span>${text}${linkText}</span></label></div>`;
      }

      html += `<div class="lead-form-buttons">
        <button type="button" class="lead-submit-btn">${this.t("escalation_submit", "Submit & Continue")}</button>
      </div>`;
      html += '<div class="lead-form-error" style="display:none;"></div>';

      form.innerHTML = html;
      messagesEl.appendChild(form);
      messagesEl.scrollTop = messagesEl.scrollHeight;

      // Disable main input while form is active
      this.disableInput();

      // Event listeners
      form
        .querySelector(".lead-submit-btn")
        .addEventListener("click", () => this.submitEscalationLeadForm(form));

      // Submit on Enter
      form.querySelectorAll(".lead-input").forEach((inp) => {
        inp.addEventListener("keypress", (e) => {
          if (e.key === "Enter") {
            e.preventDefault();
            this.submitEscalationLeadForm(form);
          }
        });
      });
    }

    async submitEscalationLeadForm(formEl) {
      const errorEl = formEl.querySelector(".lead-form-error");
      errorEl.style.display = "none";

      const emailInput = formEl.querySelector('[data-field="email"]');
      const nameInput = formEl.querySelector('[data-field="name"]');
      const phoneInput = formEl.querySelector('[data-field="phone"]');
      const gdprCheck = formEl.querySelector(".lead-gdpr-check");

      const email = emailInput ? emailInput.value.trim() : "";
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errorEl.textContent = this.t(
          "email_invalid",
          "Please enter a valid email address.",
        );
        errorEl.style.display = "block";
        return;
      }

      const fields = this.config.lead_fields;
      if (
        nameInput &&
        fields.name &&
        fields.name.required &&
        !nameInput.value.trim()
      ) {
        errorEl.textContent = this.t("name_required", "Name is required.");
        errorEl.style.display = "block";
        return;
      }
      if (
        phoneInput &&
        fields.phone &&
        fields.phone.required &&
        !phoneInput.value.trim()
      ) {
        errorEl.textContent = this.t("phone_required", "Phone is required.");
        errorEl.style.display = "block";
        return;
      }
      if (
        phoneInput &&
        phoneInput.value.trim() &&
        !/^\+?[\d\s\-().]{7,20}$/.test(phoneInput.value.trim())
      ) {
        errorEl.textContent = this.t(
          "phone_invalid",
          "Please enter a valid phone number.",
        );
        errorEl.style.display = "block";
        return;
      }
      if (
        this.config.gdpr_enabled &&
        !this.gdprAccepted &&
        (!gdprCheck || !gdprCheck.checked)
      ) {
        errorEl.textContent = this.t(
          "gdpr_required",
          "Please accept the privacy policy to continue.",
        );
        errorEl.style.display = "block";
        return;
      }

      const btn = formEl.querySelector(".lead-submit-btn");
      btn.disabled = true;
      btn.textContent = this.t("submitting", "Submitting...");

      try {
        const body = {
          email: email,
          name: nameInput ? nameInput.value.trim() : "",
          phone: phoneInput ? phoneInput.value.trim() : "",
          visitor_id: this.visitorId,
          session_id: this.sessionId,
          conversation_id: this.conversationId,
          gdpr_consent: gdprCheck ? gdprCheck.checked : false,
        };

        const response = await fetch(`${this.apiUrl}/lead/${this.widgetKey}`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(body),
        });

        const data = await response.json();
        if (data.success) {
          this.leadSubmitted = true;
          localStorage.setItem("chatbot_lead_submitted", "true");

          if (gdprCheck && gdprCheck.checked) {
            this.gdprAccepted = true;
            localStorage.setItem("chatbot_gdpr_accepted", "true");
          }

          if (data.conversation_id && !this.conversationId) {
            this.conversationId = data.conversation_id;
            this.subscribeToPusherChannel();
          }

          formEl.remove();
          this.addSystemMessage(
            this.t(
              "escalation_success",
              "Thank you! Your information has been submitted. An agent will be with you shortly.",
            ),
          );
          this.confirmEscalation();
        } else {
          errorEl.textContent =
            data.error ||
            this.t("error_generic", "Something went wrong. Please try again.");
          errorEl.style.display = "block";
          btn.disabled = false;
          btn.textContent = this.t("escalation_submit", "Submit & Continue");
        }
      } catch (e) {
        errorEl.textContent = this.t(
          "error_network",
          "Network error. Please try again.",
        );
        errorEl.style.display = "block";
        btn.disabled = false;
        btn.textContent = this.t("escalation_submit", "Submit & Continue");
      }
    }

    async confirmEscalation() {
      this.waitingForAgent = true;
      this.updateHeaderForWaitingAgent();
      this.showWaitingForAgentInputState();

      try {
        await fetch(`${this.apiUrl}/confirm_escalation/${this.widgetKey}`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            session_id: this.sessionId,
            conversation_id: this.conversationId,
          }),
        });
      } catch (e) {}
    }

    // --- GDPR Consent (standalone, when no lead form) ---

    showGdprConsent() {
      const inputArea = this.shadow.querySelector(".widget-input-area");
      if (!inputArea) return;

      const consentBar = document.createElement("div");
      consentBar.className = "gdpr-consent-bar";
      const text =
        this.config.gdpr_consent_text ||
        this.t("gdpr_default", "By chatting, you agree to our privacy policy.");
      const url = this.config.gdpr_privacy_url;
      const link = url
        ? ` <a href="${url}" target="_blank" rel="noopener">${this.t("privacy_policy", "Privacy Policy")}</a>`
        : "";
      consentBar.innerHTML = `<p>${text}${link}</p><button type="button" class="gdpr-accept-btn">${this.t("gdpr_agree", "I Agree")}</button>`;

      inputArea.parentNode.insertBefore(consentBar, inputArea);
      inputArea.style.display = "none";

      consentBar
        .querySelector(".gdpr-accept-btn")
        .addEventListener("click", () => {
          this.gdprAccepted = true;
          localStorage.setItem("chatbot_gdpr_accepted", "true");
          // Now that user engaged, prevent auto-open on next page load
          if (this._proactiveKey) {
            try {
              sessionStorage.setItem(this._proactiveKey, "1");
            } catch (e) {}
          }
          consentBar.remove();
          inputArea.style.display = "flex";
          this.enableInput();
          this.bindEvents();
          if (
            this.config.proactive_enabled &&
            this.messages.length === 0 &&
            !this.shadow.querySelector(".proactive-suggestions")
          ) {
            this.loadAndShowProactiveSuggestions();
          }
          this.shadow.querySelector(".widget-input").focus();
        });
    }

    // --- Main Render ---

    render() {
      const cfg = this.config;
      const position = cfg.position || "bottom-right";
      const isRight = position.includes("right");
      const distBottom = cfg.distance_from_bottom || 20;
      const distSide = cfg.distance_from_side || 20;
      const primaryColor = cfg.primary_color || "#007bff";
      const headerBg = cfg.header_bg_color || primaryColor;
      const iconHtml = cfg.custom_icon_url
        ? `<img src="${cfg.custom_icon_url}" alt="Chat" style="width:28px;height:28px;object-fit:contain;">`
        : (ICON_SVGS[cfg.icon_type] || ICON_SVGS.chat);

      this.shadow.innerHTML = `
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }

                    :host {
                        position: fixed;
                        bottom: ${distBottom}px;
                        ${isRight ? "right" : "left"}: ${distSide}px;
                        z-index: 999999;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                        font-size: 14px;
                        line-height: 1.5;
                    }

                    .widget-button {
                        width: 60px; height: 60px; border-radius: 50%;
                        background: ${primaryColor}; border: none; cursor: pointer;
                        display: flex; align-items: center; justify-content: center;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        transition: transform 0.2s, box-shadow 0.2s;
                        position: relative;
                    }
                    .widget-button:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
                    .widget-button svg { width: 28px; height: 28px; fill: white; }
                    .widget-button img { width: 28px; height: 28px; object-fit: contain; }
                    .widget-badge {
                        position: absolute; top: -4px; right: -4px;
                        background: #e74c3c; color: white; border-radius: 50%;
                        width: 22px; height: 22px; font-size: 11px; font-weight: 700;
                        display: none; align-items: center; justify-content: center;
                    }

                    .widget-container {
                        position: absolute; bottom: 32px;
                        ${isRight ? "right" : "left"}: 0;
                        width: 380px; max-width: calc(100vw - 40px);
                        height: 520px; max-height: calc(100vh - 100px);
                        background: white; border-radius: 16px;
                        box-shadow: 0 5px 40px rgba(0,0,0,0.16);
                        display: none; flex-direction: column; overflow: hidden;
                    }
                    .widget-container.open { display: flex; animation: slideUp 0.3s ease; }
                    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

                    .widget-header {
                        background: ${headerBg}; color: white;
                        padding: 20px; display: flex; align-items: center; gap: 12px;
                    }
                    .widget-avatar {
                        width: 45px; height: 45px; border-radius: 50%;
                        background: rgba(255,255,255,0.2);
                        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    }
                    .widget-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
                    .widget-avatar svg { width: 24px; height: 24px; fill: white; }
                    .widget-header-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 2px; }
                    .widget-header-info p { font-size: 12px; opacity: 0.9; }
                    .widget-close {
                        margin-left: auto; background: none; border: none; color: white;
                        cursor: pointer; padding: 5px; opacity: 0.8; transition: opacity 0.2s;
                    }
                    .widget-close:hover { opacity: 1; }

                    .widget-messages {
                        flex: 1; overflow-y: auto; overflow-x: hidden; padding: 16px;
                        display: flex; flex-direction: column; gap: 12px; background: #f8f9fa;
                    }
                    .message { max-width: 85%; padding: 12px 16px; border-radius: 16px; word-wrap: break-word; word-break: break-word; }
                    .message.user {
                        align-self: flex-end; background: ${primaryColor}; color: white; border-bottom-right-radius: 4px;
                    }
                    .message.assistant { align-self: flex-start; color: #333; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; background: #f3f4f6; }
                    .message.system { align-self: center; background: #e3f2fd; color: #1976d2; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;font-size: 12px; padding: 8px 16px; border-radius: 20px;margin: 8px 0; text-align: center;}
                    .message.staff-message {
                        background: transparent; padding: 0; box-shadow: none; border-radius: 0;
                        max-width: 85%; display: flex; flex-direction: row; align-items: flex-start; gap: 8px;
                    }
                    .message.staff-message.staff-cont { margin-left: 44px; }
                    .staff-avatar-col {
                        flex-shrink: 0; width: 36px;
                    }
                    .staff-avatar-col img {
                        width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
                    }
                    .staff-avatar-col .staff-initials {
                        width: 36px; height: 36px; border-radius: 50%;
                        background: ${primaryColor}; display: flex; align-items: center; justify-content: center;
                        font-size: 13px; font-weight: 600; color: #fff;
                    }
                    .staff-name { font-weight: 600; font-size: 11px; color: ${primaryColor}; margin-bottom: 2px; }
                    .staff-content-col { display: flex; flex-direction: column; min-width: 0; }
                    .staff-bubble {background: #f3f4f6; color: #1e293b; padding: 10px 60px 22px 14px;                        border-radius: 16px; border-top-left-radius: 4px;                        font-size: 14px; line-height: 1.5; word-break: break-word;position: relative;margin-bottom: 0;}
                    .staff-message-content { margin-bottom: 0; }
                    .staff-bubble .msg-time { position: absolute; bottom: 5px; right: 10px; margin-top: 0; color: #9ca3af; font-size: 10px; }
                    .msg-time { font-size: 10px; color: #9ca3af; margin-top: 3px; }
                    .message.user .msg-time { text-align: right; color: rgba(255,255,255,0.6); }
                    .message a, .message .widget-msg-link, .staff-bubble a, .staff-bubble .widget-msg-link { color: ${primaryColor}; text-decoration: underline; text-underline-offset: 2px; font-weight: 500; }
                    .message a:hover, .message .widget-msg-link:hover, .staff-bubble a:hover, .staff-bubble .widget-msg-link:hover { opacity: 0.9; }
                    .message.user a, .message.user .widget-msg-link { color: rgba(255,255,255,0.95); }
                    .widget-chat-image { max-width: 100%; max-height: 220px; border-radius: 8px; margin: 4px 0; display: block; cursor: pointer; }
                    .message code, .staff-bubble code { background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
                    .message.ai-message { background: transparent; padding: 0; box-shadow: none; border-radius: 0; max-width: 85%; display: flex; flex-direction: row; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
                    .ai-avatar-col { flex-shrink: 0; width: 36px; }
                    .ai-avatar-col img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
                    .ai-avatar-col svg { width: 36px; height: 36px; border-radius: 50%; background: ${primaryColor}; padding: 8px; fill: white; }
                    .ai-content-col { display: flex; flex-direction: column; min-width: 0; }
                    .ai-name { font-weight: 600; font-size: 11px; color: ${primaryColor}; margin-bottom: 2px; }
                    .ai-bubble { background: #f3f4f6; color: #1e293b; padding: 10px 60px 22px 14px; border-radius: 16px; border-top-left-radius: 4px; font-size: 14px; line-height: 1.5; word-break: break-word; position: relative; }
                    .ai-message-content { margin-bottom: 0; }
                    .ai-bubble .msg-time { position: absolute; bottom: 5px; right: 10px; margin-top: 0; color: #9ca3af; font-size: 10px; }
                    .ai-bubble a, .ai-bubble .widget-msg-link { color: ${primaryColor}; text-decoration: underline; text-underline-offset: 2px; font-weight: 500; }
                    .ai-bubble a:hover, .ai-bubble .widget-msg-link:hover { opacity: 0.9; }
                    .ai-bubble code { background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
                    .typing-indicator { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 12px; }
                    .typing-text { color: #666; font-size: 12px; padding: 9px; background: #f3f4f6; font-style: italic; transition: opacity 0.3s ease; border-radius: 16px; margin-top: 4px; border-top-left-radius: 4px; }
                    .typing-dots {
                        display: flex; gap: 4px; align-items: center;
                    }
                    .typing-dots span {
                        width: 6px; height: 6px; background: #999; border-radius: 50%;
                        animation: typing 1.4s ease-in-out infinite;
                    }
                    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
                    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
                    @keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-4px); } }
                    .staff-typing-bubble { padding: 10px 14px 10px; }
                    .staff-typing-dots { display: flex; gap: 3px; align-items: center; margin-top: 4px; }
                    .staff-typing-dots span { width: 5px; height: 5px; background: #999; border-radius: 50%; animation: typing 1.4s ease-in-out infinite; }
                    .staff-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
                    .staff-typing-dots span:nth-child(3) { animation-delay: 0.4s; }

                    .widget-input-area {
                        padding: 12px 16px; background: white; border-top: 1px solid #eee; display: flex; gap: 8px;
                    }
                    .widget-input {
                        flex: 1; border: 1px solid #ddd; border-radius: 24px; padding: 10px 16px;
                        font-size: 14px; outline: none; transition: border-color 0.2s;
                    }
                    .widget-input:focus { border-color: ${primaryColor}; }
                    .widget-input:disabled { background: #f5f5f5; cursor: not-allowed; }
                    .widget-attach {
                        width: 36px; height: 36px; border-radius: 50%; background: transparent;
                        border: none; cursor: pointer; display: flex; align-items: center;
                        justify-content: center; color: #9ca3af; transition: color 0.2s; flex-shrink: 0; padding: 0;
                    }
                    .widget-attach:hover { color: ${primaryColor}; }
                    .widget-send {
                        width: 40px; height: 40px; border-radius: 50%; background: ${primaryColor};
                        border: none; cursor: pointer; display: flex; align-items: center;
                        justify-content: center; transition: opacity 0.2s;
                    }
                    .widget-send:disabled { opacity: 0.5; cursor: not-allowed; }
                    .widget-send svg { width: 18px; height: 18px; fill: white; }

                    .widget-new-chat-btn {
                        flex: 1; padding: 12px 20px; background: ${primaryColor}; color: white;
                        border: none; border-radius: 24px; font-size: 14px; font-weight: 500;
                        cursor: pointer; transition: opacity 0.2s;
                    }
                    .widget-new-chat-btn:hover { opacity: 0.9; }

                    .welcome-message { text-align: center; padding: 9px 20px 0px 20px; color: #666; }
                    .welcome-message h4 { color: #333; margin-bottom: 8px; }
                    .proactive-suggestions { padding: 0px 20px; }
                    .proactive-suggestions-title { font-size: 13px; color: #666; margin: 0 0 10px 0; font-weight: 600; }
                    .proactive-suggestions-chips { display: flex; flex-wrap: wrap; gap: 8px; }
                    .proactive-suggestion-chip {
                        padding: 8px 14px; font-size: 13px; border-radius: 20px; border: 1px solid #ddd;
                        background: #f8f9fa; color: #333; cursor: pointer; transition: background 0.2s, border-color 0.2s;
                    }
                    .proactive-suggestion-chip:hover { background: #e9ecef; border-color: ${primaryColor}; }
                    .proactive-suggestion-chip:disabled { opacity: 0.7; cursor: default; }

                    /* Lead Form */
                    .lead-form { padding: 24px 20px; text-align: center;margin-top: 18px; }
                    .lead-form-header h4 { color: #333; font-size: 16px; margin-bottom: 4px; }
                    .lead-form-header p { color: #888; font-size: 13px; margin-bottom: 16px; }
                    .lead-form-field { margin-bottom: 10px; }
                    .lead-field-icon { position: relative; }
                    .lead-icon {
                        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
                        width: 18px; height: 18px; fill: #999; pointer-events: none;
                    }
                    .lead-field-icon .lead-input { padding-left: 38px; }
                    .lead-input {
                        width: 100%; padding: 10px 14px; border: 1px solid #ddd;
                        border-radius: 8px; font-size: 14px; outline: none;
                        transition: border-color 0.2s; font-family: inherit;
                    }
                    .lead-input:focus { border-color: ${primaryColor}; }
                    .lead-field-icon .lead-input:focus + .lead-icon,
                    .lead-field-icon:focus-within .lead-icon { fill: ${primaryColor}; }
                    .lead-submit-btn {
                        width: 100%; padding: 11px; background: ${primaryColor}; color: white;
                        border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
                        cursor: pointer; transition: opacity 0.2s;
                        margin-top: 6px;
                    }
                    .lead-submit-btn:hover { opacity: 0.9; }
                    .lead-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
                    .lead-form-error {
                        color: #e74c3c; font-size: 12px; margin-top: 8px; text-align: center;
                    }
                    .lead-gdpr-row { text-align: left; font-size: 12px; color: #666; margin-top: 4px; }
                    .lead-gdpr-row label { display: flex; align-items: flex-start; gap: 6px; cursor: pointer; }
                    .lead-gdpr-row input[type="checkbox"] { margin-top: 2px; }
                    .lead-gdpr-row a { color: ${primaryColor}; }

                    /* Escalation Lead Form */
                    .escalation-lead-form {
                        padding: 16px; margin: 12px 0; background: #f8f9fa;
                        border-radius: 12px; border: 1px solid #e9ecef;
                    }
                    .escalation-lead-form .lead-form-header h4 {
                        color: #333; font-size: 15px; margin-bottom: 4px;
                    }
                    .escalation-lead-form .lead-form-header p {
                        color: #666; font-size: 12px; margin-bottom: 12px;
                    }
                    .escalation-lead-form .lead-form-field { margin-bottom: 8px; }
                    .escalation-lead-form .lead-form-buttons {
                        display: flex; gap: 8px; margin-top: 12px;
                    }
                    .escalation-lead-form .lead-submit-btn {
                        flex: 1; padding: 10px 12px; border: none; border-radius: 6px;
                        font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
                        background: ${primaryColor}; color: white;
                    }
                    .escalation-lead-form .lead-submit-btn:hover {
                        opacity: 0.9;
                    }

                    /* GDPR Consent Bar */
                    .gdpr-consent-bar {
                        padding: 12px 16px; background: #f8f9fa; border-top: 1px solid #eee;
                        text-align: center; font-size: 12px; color: #666;
                    }
                    .gdpr-consent-bar p { margin-bottom: 8px; }
                    .gdpr-consent-bar a { color: ${primaryColor}; }
                    .gdpr-accept-btn {
                        padding: 6px 20px; background: ${primaryColor}; color: white;
                        border: none; border-radius: 16px; font-size: 12px; font-weight: 600;
                        cursor: pointer; transition: opacity 0.2s;
                    }
                    .gdpr-accept-btn:hover { opacity: 0.9; }

                    /* CSAT Rating Styles */
                    .csat-rating-container {
                        padding: 16px 0;
                        border-top: 1px solid #eee;
                        margin-top: 16px;
                    }

                    .csat-rating {
                        background: #f8f9fa;
                        border-radius: 12px;
                        padding: 16px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }

                    .csat-title {
                        font-size: 14px;
                        font-weight: 600;
                        color: #333;
                        margin-bottom: 12px;
                        text-align: center;
                    }

                    .csat-stars {
                        display: flex;
                        justify-content: center;
                        gap: 4px;
                        margin-bottom: 12px;
                    }

                    .csat-star {
                        font-size: 24px;
                        color: #ddd;
                        cursor: pointer;
                        transition: color 0.2s ease;
                        user-select: none;
                    }

                    .csat-star:hover,
                    .csat-star.hover {
                        color: #ffc107;
                    }

                    .csat-star.active {
                        color: #ff9800;
                    }

                    .csat-comment {
                        width: 100%;
                        padding: 8px 12px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        font-size: 13px;
                        font-family: inherit;
                        resize: vertical;
                        margin-bottom: 12px;
                        background: white;
                        color: #333;
                        box-sizing: border-box;
                    }

                    .csat-comment:focus {
                        outline: none;
                        border-color: ${primaryColor};
                    }

                    .csat-actions {
                        display: flex;
                        gap: 8px;
                        justify-content: flex-end;
                    }

                    .csat-skip,
                    .csat-submit {
                        padding: 8px 16px;
                        border: 1px solid #ddd;
                        border-radius: 6px;
                        font-size: 13px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: white;
                        color: #333;
                    }

                    .csat-submit {
                        background: ${primaryColor};
                        color: white;
                        border-color: ${primaryColor};
                    }

                    .csat-submit:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }

                    .csat-skip:hover {
                        background: #f0f0f0;
                    }

                    .csat-submit:hover:not(:disabled) {
                        opacity: 0.9;
                    }

                    .csat-thank-you {
                        background: #e8f5e8;
                        color: #2e7d32;
                        border: 1px solid #c8e6c9;
                        text-align: center;
                        font-weight: 500;
                    }
                </style>

                <button type="button" class="widget-button" aria-label="Open chat">
                    ${iconHtml}
                    <span class="widget-badge"></span>
                </button>

                <div class="widget-container" role="dialog" aria-label="Chat widget">
                    <div class="widget-header">
                        <div class="widget-avatar">
                            ${cfg.agent_avatar_url ? `<img src="${cfg.agent_avatar_url}" alt="Agent">` : ICON_SVGS.avatar}
                        </div>
                        <div class="widget-header-info">
                            <h3>${this.esc(cfg.intro_title || cfg.name)}</h3>
                            <p>${this.esc(cfg.intro_subtitle || (cfg.i18n && cfg.i18n.reply_time) || "We typically reply within minutes")}</p>
                        </div>
                        <button type="button" class="widget-close" aria-label="Close chat">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="widget-messages" aria-live="polite">
                        <div class="welcome-message">
                            <h4>${this.esc(cfg.welcome_message || (cfg.i18n && cfg.i18n.welcome) || "Hi! How can I help you today?")}</h4>
                        </div>
                    </div>

                    <div class="widget-input-area">
                        <button type="button" class="widget-attach" aria-label="Attach file" title="Attach file">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"></path></svg>
                        </button>
                        <input type="file" class="widget-file-input" accept="image/*,.pdf,.doc,.docx,.txt,.csv,.zip" style="display:none" />
                        <input type="text" class="widget-input" placeholder="${this.esc(cfg.input_placeholder || (cfg.i18n && cfg.i18n.type_message) || "Type your message...")}" autocomplete="off" />
                        <button type="button" class="widget-send" aria-label="Send message">
                            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </div>
            `;

      this.bindEvents();
    }

    bindEvents() {
      const button = this.shadow.querySelector(".widget-button");
      const closeBtn = this.shadow.querySelector(".widget-close");
      const input = this.shadow.querySelector(".widget-input");
      const sendBtn = this.shadow.querySelector(".widget-send");

      if (!button || !closeBtn || !input || !sendBtn) return;

      if (!this._boundToggle) {
        this._boundToggle = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.toggle();
        };
        this._boundClose = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.close();
        };
        this._boundSend = (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.sendMessage();
        };
        this._boundKeydown = (e) => {
          if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            e.stopPropagation();
            this.sendMessage();
          }
        };
      }

      if (!this._boundTypingInput) {
        this._boundTypingInput = () => {
          clearTimeout(this._typingStopTimer);
          if (input.value.trim()) {
            this.sendTypingIndicator(true);
            this._typingStopTimer = setTimeout(
              () => this.sendTypingIndicator(false),
              3000,
            );
          } else {
            this.sendTypingIndicator(false);
          }
        };
      }

      button.removeEventListener("click", this._boundToggle);
      closeBtn.removeEventListener("click", this._boundClose);
      sendBtn.removeEventListener("click", this._boundSend);
      input.removeEventListener("keydown", this._boundKeydown);
      input.removeEventListener("input", this._boundTypingInput);

      button.addEventListener("click", this._boundToggle);
      closeBtn.addEventListener("click", this._boundClose);
      sendBtn.addEventListener("click", this._boundSend);
      input.addEventListener("keydown", this._boundKeydown);
      input.addEventListener("input", this._boundTypingInput);

      const attachBtn = this.shadow.querySelector(".widget-attach");
      const fileInput = this.shadow.querySelector(".widget-file-input");
      const visitorUploadEnabled = this.config && this.config.visitor_file_upload;

      if (attachBtn) {
        if (visitorUploadEnabled && fileInput) {
          attachBtn.style.display = "flex";
          attachBtn.addEventListener("click", () => fileInput.click());
          fileInput.addEventListener("change", (e) => {
            if (e.target.files && e.target.files.length > 0) {
              this.uploadFile(e.target.files[0]);
              e.target.value = "";
            }
          });
        } else {
          attachBtn.style.display = "none";
        }
      }

      if (visitorUploadEnabled) {
        input.addEventListener("paste", (e) => {
          const cd = e.clipboardData || window.clipboardData;
          if (!cd) return;
          let imgFile = null;
          if (cd.items) { for (let i = 0; i < cd.items.length; i++) { if (cd.items[i].type.indexOf("image") !== -1) { imgFile = cd.items[i].getAsFile(); break; } } }
          if (!imgFile && cd.files) { for (let i = 0; i < cd.files.length; i++) { if (cd.files[i].type.indexOf("image") !== -1) { imgFile = cd.files[i]; break; } } }
          if (imgFile) {
            e.preventDefault();
            const ext = {"image/png":"png","image/jpeg":"jpg","image/gif":"gif","image/webp":"webp"}[imgFile.type] || "png";
            const file = new File([imgFile], "pasted-image-" + Date.now() + "." + ext, { type: imgFile.type });
            this.uploadFile(file);
          }
        });
      }

      if (!this._visibilityBound) {
        this._visibilityBound = true;
        document.addEventListener("visibilitychange", () => {
          if (!document.hidden && this.isOpen && this.unreadCount > 0) {
            this.stopTitleFlash();
          }
        });
      }

      if (!this._escapeBound) {
        this._escapeBound = true;
        document.addEventListener("keydown", (e) => {
          if (e.key === "Escape" && this.isOpen) {
            this.close();
          }
        });
      }
    }

    trapFocus() {
      if (this._focusTrapHandler) return;
      this._focusTrapHandler = (e) => {
        if (e.key !== "Tab") return;
        const container = this.shadow.querySelector(".widget-container");
        if (!container) return;
        const focusable = container.querySelectorAll(
          'input:not([disabled]), button:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])',
        );
        if (focusable.length === 0) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
          if (this.shadow.activeElement === first) {
            e.preventDefault();
            last.focus();
          }
        } else {
          if (this.shadow.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      };
      this.shadow.addEventListener("keydown", this._focusTrapHandler);
    }

    removeFocusTrap() {
      if (this._focusTrapHandler) {
        this.shadow.removeEventListener("keydown", this._focusTrapHandler);
        this._focusTrapHandler = null;
      }
    }

    toggle() {
      this.isOpen ? this.close() : this.open();
    }

    open() {
      this.isOpen = true;
      localStorage.setItem("chatbot_widget_open", "true");
      const container = this.shadow.querySelector(".widget-container");
      const button = this.shadow.querySelector(".widget-button");
      container.classList.add("open");
      button.style.display = "none";
      button.setAttribute("aria-label", this.t("close_chat", "Close chat"));
      this.trapFocus();

      this.stopTitleFlash();
      const badge = this.shadow.querySelector(".widget-badge");
      if (badge) badge.style.display = "none";

      setTimeout(() => {
        const messagesEl = this.shadow.querySelector(".widget-messages");
        if (messagesEl) messagesEl.scrollTop = messagesEl.scrollHeight;

        if (this.needsLeadForm() && !this.shadow.querySelector(".lead-form")) {
          this.showLeadForm();
        } else {
          if (this._openedByProactive) this._openedByProactive = false;
          if (
            this.config.proactive_enabled &&
            this.messages.length === 0 &&
            !this.shadow.querySelector(".proactive-suggestions")
          ) {
            this.loadAndShowProactiveSuggestions();
          }

          // Show standalone GDPR consent bar only when there's no lead form
          // (the lead form already has its own inline GDPR checkbox)
          if (
            this.needsGdprConsent() &&
            !this.shadow.querySelector(".gdpr-consent-bar")
          ) {
            this.showGdprConsent();
          } else {
            const inp = this.shadow.querySelector(".widget-input");
            if (inp && !inp.disabled) inp.focus();
          }
        }
      }, 300);
    }

    close() {
      this.isOpen = false;
      localStorage.removeItem("chatbot_widget_open");
      const container = this.shadow.querySelector(".widget-container");
      const button = this.shadow.querySelector(".widget-button");
      container.classList.remove("open");
      button.style.display = "flex";
      button.setAttribute("aria-label", this.t("open_chat", "Open chat"));
      this.removeFocusTrap();
      button.focus();
    }

    async uploadFile(file) {
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) {
        this.appendSystemMessage("File too large. Max 5MB.");
        return;
      }
      if (!this.conversationId) {
        this.appendSystemMessage("Please send a message first to start a conversation.");
        return;
      }

      const formData = new FormData();
      formData.append("userfile", file);
      formData.append("conversation_id", this.conversationId);
      formData.append("token", this.sessionId || "");

      const uploadUrl = this.apiUrl + "/upload";

      try {
        const resp = await fetch(uploadUrl, { method: "POST", body: formData });
        const data = await resp.json();
        if (data.success && data.file_url) {
          this.sendMessage(data.file_url);
        } else {
          this.appendSystemMessage(data.error || "Upload failed");
        }
      } catch (err) {
        this.appendSystemMessage("Upload failed");
      }
    }

    appendSystemMessage(text) {
      const messagesEl = this.shadow.querySelector(".widget-messages");
      if (!messagesEl) return;
      const div = document.createElement("div");
      div.className = "message system";
      div.textContent = text;
      messagesEl.appendChild(div);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async sendMessage(optionalMessage) {
      const input = this.shadow.querySelector(".widget-input");
      const message =
        optionalMessage != null
          ? String(optionalMessage).trim()
          : input.value.trim();
      if (
        !message ||
        this.isLoading ||
        this.conversationClosed ||
        this.waitingForAgent
      )
        return;

      this.addMessage("user", message);
      if (optionalMessage == null) {
        input.value = "";
        input.disabled = true;
      }
      // User sent a message - mark proactive as done
      if (this._proactiveKey) {
        try {
          sessionStorage.setItem(this._proactiveKey, "1");
        } catch (e) {}
      }
      clearTimeout(this._typingStopTimer);
      this.sendTypingIndicator(false);

      if (!this.humanConnected) this.showTyping();
      this.isLoading = true;

      try {
        const response = await fetch(
          `${this.apiUrl}/message/${this.widgetKey}`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              message,
              visitor_id: this.visitorId,
              session_id: this.sessionId,
            }),
          },
        );

        const data = await response.json();
        this.hideTyping();

        if (data.conversation_id && !this.conversationId) {
          this.conversationId = data.conversation_id;
          this.subscribeToPusherChannel();
        }

        if (data.success) {
          if (data.closed) {
            this.conversationClosed = true;
            this.updateHeaderForClosed();
            this.disableInput();
            this.addSystemMessage(
              this.t(
                "conversation_closed",
                "This conversation has been closed.",
              ),
            );
            return;
          }
          if (data.human_handling) {
            this.humanConnected = true;
            this.waitingForAgent = false;
          } else if (data.escalation_form) {
            if (data.message) {
              this.addMessage("assistant", data.message);
            }
            if (this.config.lead_fields && !this.leadSubmitted) {
              this.showEscalationLeadForm();
            } else {
              this.confirmEscalation();
            }
            this.isLoading = false;
            return;
          } else if (data.escalated) {
            if (data.message) {
              this.addMessage("assistant", data.message);
            }
            this.waitingForAgent = true;
            this.updateHeaderForWaitingAgent();
            this.showWaitingForAgentInputState();
            this.isLoading = false;
            return;
          } else if (data.message) {
            this.addMessage("assistant", data.message);
            this.notifyNewMessage();
          }
          // After showing the AI response, check if limit is now reached
          if (data.message_limit_reached) {
            this.addSystemMessage(
              this.t(
                "message_limit",
                "You have reached the message limit for this conversation. Please contact us directly for further assistance.",
              ),
            );
            this.disableInput();
            return;
          }
        } else if (data.message_limit_reached) {
          this.addSystemMessage(
            this.t(
              "message_limit",
              "You have reached the message limit for this conversation. Please contact us directly for further assistance.",
            ),
          );
          this.disableInput();
          return;
        } else {
          this.addMessage(
            "assistant",
            data.message ||
              this.t("error_generic", "Sorry, something went wrong."),
          );
        }
      } catch (error) {
        this.hideTyping();
        this.addMessage(
          "assistant",
          this.t(
            "error_send",
            "Sorry, I encountered an error. Please try again.",
          ),
        );
      }

      this.isLoading = false;
      input.disabled = false;
      input.focus();
    }

    formatMessageContent(text) {
      if (!text) return "";
      var trimmed = text.trim();
      var imgMatch = trimmed.match(/^(https?:\/\/\S+\.(?:png|jpe?g|gif|webp|bmp))$/i);
      if (imgMatch) {
        return '<a href="' + this.esc(imgMatch[1]) + '" target="_blank" rel="noopener"><img class="widget-chat-image" src="' + this.esc(imgMatch[1]) + '" alt="Shared image" loading="lazy"></a>';
      }
      const div = document.createElement("div");
      div.textContent = text;
      let s = div.innerHTML;
      s = s.replace(/\n/g, "<br>");
      s = s.replace(
        /(https?:\/\/[^\s<>"\']+?)([.,;:!?)]*)(?=\s|$|<\/)/g,
        (_, url, trail) => {
          if (/\.(png|jpe?g|gif|webp|bmp)$/i.test(url)) {
            return '<a href="' + url + '" target="_blank" rel="noopener"><img class="widget-chat-image" src="' + url + '" alt="Image" loading="lazy"></a>' + (trail || "");
          }
          return '<a class="widget-msg-link" href="' + url + '" target="_blank" rel="noopener">' + url + "</a>" + (trail || "");
        },
      );
      return s;
    }

    formatWidgetTime(date) {
      if (!(date instanceof Date) || isNaN(date)) return "";
      var h = date.getHours(),
        m = date.getMinutes();
      var ampm = h >= 12 ? "PM" : "AM";
      return (h % 12 || 12) + ":" + (m < 10 ? "0" : "") + m + " " + ampm;
    }

    addMessage(
      role,
      content,
      senderName = null,
      staffImage = null,
      time = null,
    ) {
      if (!content) return;
      const messagesEl = this.shadow.querySelector(".widget-messages");
      var welcome = messagesEl.querySelector(".welcome-message");
      if (welcome) welcome.remove();

      var isStaff = !!(senderName && role === "assistant");
      var lastMsg = this.messages[this.messages.length - 1];
      var showAvatar =
        isStaff &&
        !(lastMsg && lastMsg.is_staff && lastMsg.staff_name === senderName);
      var timeStr = time || this.formatWidgetTime(new Date());
      var displayContent =
        role === "assistant" && content.indexOf("<") !== -1
          ? this.sanitizeHtml(content)
          : this.formatMessageContent(content);

      var msgEl = document.createElement("div");

      if (isStaff) {
        msgEl.className =
          "message assistant staff-message" + (showAvatar ? "" : " staff-cont");
        var html = "";
        if (showAvatar) {
          var avatarHtml = staffImage
            ? '<img src="' +
              this.esc(staffImage) +
              '" alt="' +
              this.esc(senderName) +
              '">'
            : '<span class="staff-initials">' +
              senderName
                .split(" ")
                .map(function (w) {
                  return w[0];
                })
                .join("")
                .substring(0, 2) +
              "</span>";
          html += '<div class="staff-avatar-col">' + avatarHtml + "</div>";
        }
        var nameHtml = showAvatar
          ? '<div class="staff-name">' + this.esc(senderName) + "</div>"
          : "";
        html +=
          '<div class="staff-content-col"><div class="staff-bubble"><div class="staff-message-content">' +
          nameHtml +
          displayContent +
          '</div><div class="msg-time">' +
          timeStr +
          "</div></div></div>";
        msgEl.innerHTML = html;
      } else if (role === "assistant") {
        msgEl.className = "message assistant ai-message";
        var avatarHtml = this.getAvatarHtml();
        var html =
          `<div class="ai-avatar-col">${avatarHtml}</div>` +
          `<div class="ai-content-col"><div class="ai-bubble"><div class="ai-message-content"><div class="ai-name">${this.esc(this.config.intro_title || this.config.name || 'Assistant')}</div>${displayContent}</div><div class="msg-time">${timeStr}</div></div></div>`;
        msgEl.innerHTML = html;
      } else {
        msgEl.className = "message " + role;
        msgEl.innerHTML = `${displayContent}<div class="msg-time">${timeStr}</div>`;
      }
      messagesEl.appendChild(msgEl);
      messagesEl.scrollTop = messagesEl.scrollHeight;
      this.messages.push({
        role: role,
        content: content,
        staff_name: senderName,
        staff_image: staffImage,
        is_staff: isStaff,
        time: timeStr,
      });
    }
    renderMessages() {
      var messagesEl = this.shadow.querySelector(".widget-messages");
      var welcome = messagesEl.querySelector(".welcome-message");
      if (welcome) welcome.remove();
      var prevStaffName = null;
      var self = this;
      this.messages.forEach(function (msg) {
        if (msg.role === "system") {
          var sysEl = document.createElement("div");
          sysEl.className = "message system";
          sysEl.textContent = msg.content;
          messagesEl.appendChild(sysEl);
          prevStaffName = null;
          return;
        }
        var isStaff = msg.is_staff && msg.staff_name;
        var showAvatar = isStaff && prevStaffName !== msg.staff_name;
        var timeStr =
          msg.time ||
          self.formatWidgetTime(
            msg.created_at ? new Date(msg.created_at) : new Date(),
          );
        var displayContent;
        if (msg.role === "user") {
          displayContent = self.formatMessageContent(msg.content || "");
        } else {
          displayContent =
            msg.content && msg.content.indexOf("<") !== -1
              ? self.sanitizeHtml(msg.content)
              : self.formatMessageContent(msg.content || "");
        }

        var msgEl = document.createElement("div");

        if (isStaff) {
          msgEl.className =
            "message assistant staff-message" +
            (showAvatar ? "" : " staff-cont");
          var html = "";
          if (showAvatar) {
            var avatarHtml = msg.staff_image
              ? '<img src="' +
                self.esc(msg.staff_image) +
                '" alt="' +
                self.esc(msg.staff_name) +
                '">'
              : '<span class="staff-initials">' +
                msg.staff_name
                  .split(" ")
                  .map(function (w) {
                    return w[0];
                  })
                  .join("")
                  .substring(0, 2) +
                "</span>";
            html += '<div class="staff-avatar-col">' + avatarHtml + "</div>";
          }
          var nameHtml = showAvatar
            ? '<div class="staff-name">' + self.esc(msg.staff_name) + "</div>"
            : "";
          html +=
            '<div class="staff-content-col"><div class="staff-bubble"><div class="staff-message-content">' +
            nameHtml +
            displayContent +
            '</div><div class="msg-time">' +
            timeStr +
            "</div></div></div>";
          msgEl.innerHTML = html;
          prevStaffName = msg.staff_name;
        } else if (msg.role === "assistant") {
          msgEl.className = "message assistant ai-message";
          var avatarHtml = self.getAvatarHtml();
          var html =
            `<div class="ai-avatar-col">${avatarHtml}</div>` +
            `<div class="ai-content-col"><div class="ai-bubble"><div class="ai-message-content"><div class="ai-name">${self.esc(self.config.intro_title || self.config.name || 'Assistant')}</div>${displayContent}</div><div class="msg-time">${timeStr}</div></div></div>`;
          msgEl.innerHTML = html;
          prevStaffName = null;
        } else {
          msgEl.className = "message " + msg.role;
          msgEl.innerHTML =
            displayContent + '<div class="msg-time">' + timeStr + "</div>";
          prevStaffName = null;
        }
        messagesEl.appendChild(msgEl);
      });
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    sendTypingIndicator(isTyping) {
      if (!this.conversationId) return;
      const now = Date.now();
      if (isTyping && now - this._lastTypingSent < 3000) return;
      this._lastTypingSent = now;
      fetch(`${this.apiUrl}/typing/${this.widgetKey}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          conversation_id: this.conversationId,
          visitor_id: this.visitorId,
          typing: isTyping,
        }),
      }).catch(() => {});
    }

    showTyping() {
      const messagesEl = this.shadow.querySelector(".widget-messages");
      const existing = this.shadow.querySelector("#typing");
      if (existing) return;
      const phrases =
        this.config && this.config.i18n && this.config.i18n.thinking_phrases
          ? this.config.i18n.thinking_phrases
          : [
              "Thinking...",
              "Analyzing your question...",
              "Processing...",
              "Searching knowledge base...",
              "Preparing response...",
              "Just a moment...",
              "Working on it...",
            ];

      let currentIndex = 0;
      const typing = document.createElement("div");
      typing.className = "typing-indicator";
      typing.id = "typing";
      typing.innerHTML = `<div class="ai-avatar-col">${this.getAvatarHtml()}</div><div class="ai-content-col"><span class="typing-text">${phrases[0]}</span></div>`;
      messagesEl.appendChild(typing);
      messagesEl.scrollTop = messagesEl.scrollHeight;

      this.typingInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % phrases.length;
        const textEl = typing.querySelector(".typing-text");
        if (textEl) textEl.textContent = phrases[currentIndex];
      }, 2500);
    }

    hideTyping() {
      if (this.typingInterval) {
        clearInterval(this.typingInterval);
        this.typingInterval = null;
      }
      const typing = this.shadow.querySelector("#typing");
      if (typing) typing.remove();
    }

    showStaffTyping(staffName) {
      const messagesEl = this.shadow.querySelector(".widget-messages");
      if (!messagesEl) return;
      const existing = this.shadow.querySelector("#staff-typing");
      if (existing) return;
      const el = document.createElement("div");
      el.className = "message assistant staff-message";
      el.id = "staff-typing";
      const name = staffName || this._currentStaffName || "Agent";
      const escapedName = this.esc(name);
      const img = this._currentStaffImage || null;
      const avatarHtml = img
        ? '<img src="' + this.esc(img) + '" alt="' + escapedName + '">'
        : '<span class="staff-initials">' + this.esc(name.split(' ').map(function(w){return w[0];}).join('').substring(0,2).toUpperCase()) + '</span>';
      el.innerHTML =
        '<div class="staff-avatar-col">' + avatarHtml + '</div>' +
        '<div class="staff-content-col">' +
          '<div class="staff-bubble staff-typing-bubble">' +
            '<div class="staff-name">' + escapedName + '</div>' +
            '<span class="staff-typing-dots"><span></span><span></span><span></span></span>' +
          '</div>' +
        '</div>';
      messagesEl.appendChild(el);
      messagesEl.scrollTop = messagesEl.scrollHeight;
      this._staffTypingTimer = setTimeout(() => this.hideStaffTyping(), 5000);
    }

    hideStaffTyping() {
      clearTimeout(this._staffTypingTimer);
      const el = this.shadow.querySelector("#staff-typing");
      if (el) el.remove();
    }

    showCSATRating() {
      // Don't show if already rated or no conversation
      if (!this.conversationId || this.csatRated) return;

      const messagesEl = this.shadow.querySelector(".widget-messages");
      const csatEl = document.createElement("div");
      csatEl.className = "csat-rating-container";
      csatEl.innerHTML = `
        <div class="csat-rating">
          <div class="csat-title">${this.config.i18n.chatbot_csat_rate_conversation || "Rate this conversation"}</div>
          <div class="csat-stars">
            <span class="csat-star" data-rating="1">★</span>
            <span class="csat-star" data-rating="2">★</span>
            <span class="csat-star" data-rating="3">★</span>
            <span class="csat-star" data-rating="4">★</span>
            <span class="csat-star" data-rating="5">★</span>
          </div>
          <textarea class="csat-comment" placeholder="${this.config.i18n.chatbot_csat_comment_placeholder || "Tell us how we did (optional)"}" rows="3"></textarea>
          <div class="csat-actions">
            <button class="csat-skip">${this.config.i18n.chatbot_csat_skip || "Skip"}</button>
            <button class="csat-submit" disabled>${this.config.i18n.chatbot_csat_submit || "Submit Rating"}</button>
          </div>
        </div>
      `;

      messagesEl.appendChild(csatEl);
      messagesEl.scrollTop = messagesEl.scrollHeight;

      // Bind star rating events
      const stars = csatEl.querySelectorAll(".csat-star");
      const submitBtn = csatEl.querySelector(".csat-submit");
      let selectedRating = 0;

      stars.forEach((star, index) => {
        star.addEventListener("click", () => {
          selectedRating = index + 1;
          stars.forEach((s, i) => {
            s.classList.toggle("active", i < selectedRating);
          });
          submitBtn.disabled = false;
        });

        star.addEventListener("mouseenter", () => {
          stars.forEach((s, i) => {
            s.classList.toggle("hover", i <= index);
          });
        });

        star.addEventListener("mouseleave", () => {
          stars.forEach((s) => s.classList.remove("hover"));
        });
      });

      // Submit rating
      submitBtn.addEventListener("click", () => {
        const comment = csatEl.querySelector(".csat-comment").value.trim();
        this.submitCSATRating(selectedRating, comment);
        csatEl.remove();
      });

      // Skip rating
      csatEl.querySelector(".csat-skip").addEventListener("click", () => {
        csatEl.remove();
        this.csatRated = true;
      });
    }

    submitCSATRating(score, comment) {
      if (!this.conversationId || score < 1 || score > 5) return;

      fetch(`${this.apiUrl}/submit_rating/${this.widgetKey}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          conversation_id: this.conversationId,
          session_id: this.sessionId,
          score: score,
          comment: comment,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.markConversationAsRated();
            const messagesEl = this.shadow.querySelector(".widget-messages");
            const thankYouEl = document.createElement("div");
            thankYouEl.className = "message system csat-thank-you";
            thankYouEl.textContent =
              data.message ||
              this.config.i18n.chatbot_csat_thank_you ||
              "Thank you for your feedback!";
            messagesEl.appendChild(thankYouEl);
            messagesEl.scrollTop = messagesEl.scrollHeight;
          }
        });
    }

    checkShowCSAT() {
      // Check if CSAT is enabled for this chatbot
      if (!this.config || !this.config.csat_enabled) {
        return;
      }

      // Don't show if already rated for this conversation
      if (this.csatRated || this.hasRatedThisConversation()) {
        return;
      }

      const hasStaffMessages = this.messages.some((msg) => msg.is_staff);

      if (hasStaffMessages && this.conversationClosed && this.conversationId) {
        // Add a small delay to let the conversation settle
        setTimeout(() => {
          this.showCSATRating();
        }, 2000);
      }
    }

    hasRatedThisConversation() {
      // Check if we've already rated this specific conversation
      const ratedConversations = JSON.parse(
        localStorage.getItem("chatbot_rated_conversations") || "[]",
      );
      return ratedConversations.includes(this.conversationId);
    }

    markConversationAsRated() {
      // Mark this conversation as rated in localStorage
      const ratedConversations = JSON.parse(
        localStorage.getItem("chatbot_rated_conversations") || "[]",
      );
      if (!ratedConversations.includes(this.conversationId)) {
        ratedConversations.push(this.conversationId);
        localStorage.setItem(
          "chatbot_rated_conversations",
          JSON.stringify(ratedConversations),
        );
      }
      this.csatRated = true;
    }
  }

  customElements.define("chatbot-widget", ChatbotWidget);
})();

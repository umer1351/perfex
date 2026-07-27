(function () {
  "use strict";

  var EMOJIS = ["👍", "❤️", "😂", "😮", "😢", "😡", "🎉", "🔥"];

  function ReactionPicker(opts) {
    this.onPick = opts.onPick || function () {};
    this.visible = false;
    this._el = null;
    this._outsideHandler = null;
    this._build();
  }

  ReactionPicker.prototype._build = function () {
    var el = document.createElement("div");
    el.className = "reaction-picker-popup";
    el.style.cssText =
      "position:fixed;z-index:999999;display:none;background:#fff;border:1px solid #d1d5db;border-radius:24px;padding:4px 6px;box-shadow:0 4px 16px rgba(0,0,0,.15);";

    var isDark =
      document.body.classList.contains("dark-mode") ||
      document.documentElement.classList.contains("dark-mode");
    if (isDark) {
      el.style.background = "#1f2937";
      el.style.borderColor = "#4b5563";
    }

    var self = this;
    var row = document.createElement("div");
    row.style.cssText = "display:flex;gap:2px;align-items:center;";

    for (var i = 0; i < EMOJIS.length; i++) {
      (function (emoji) {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.textContent = emoji;
        btn.style.cssText =
          "border:none;background:transparent;font-size:18px;line-height:1;cursor:pointer;padding:4px;border-radius:6px;transition:background .15s;";
        btn.addEventListener("mouseenter", function () {
          this.style.background = isDark ? "#374151" : "#f3f4f6";
        });
        btn.addEventListener("mouseleave", function () {
          this.style.background = "transparent";
        });
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          self.close();
          self.onPick(emoji);
        });
        row.appendChild(btn);
      })(EMOJIS[i]);
    }

    el.appendChild(row);
    document.body.appendChild(el);
    this._el = el;
  };

  ReactionPicker.prototype.openNear = function (anchor) {
    if (!this._el) return;
    var rect =
      typeof anchor.getBoundingClientRect === "function"
        ? anchor.getBoundingClientRect()
        : anchor;
    var position = anchor._position || "top";

    this._el.style.display = "block";
    this.visible = true;

    var elW = this._el.offsetWidth;
    var elH = this._el.offsetHeight;
    var vw = window.innerWidth;
    var vh = window.innerHeight;

    var left, top;

    if (position === "left") {
      left = rect.left - elW - 6;
      top = rect.top + (rect.height || 0) / 2 - elH / 2;
    } else {
      left = rect.left + (rect.width || 0) / 2 - elW / 2;
      top = rect.top - elH - 6;
      if (top < 4) top = rect.bottom + 6;
    }

    if (left < 4) left = 4;
    if (left + elW > vw - 4) left = vw - elW - 4;
    if (top < 4) top = 4;
    if (top + elH > vh - 4) top = vh - elH - 4;

    this._el.style.left = left + "px";
    this._el.style.top = top + "px";

    var self = this;
    if (this._outsideHandler) {
      document.removeEventListener("click", this._outsideHandler, true);
    }
    setTimeout(function () {
      self._outsideHandler = function (e) {
        var t = e.target;
        if (!t || !t.closest) return;
        if (self._el.contains(t)) return;
        // Client widget: keep picker open when interacting with message menu / three-dots
        if (
          t.closest("._reactMessage") ||
          t.closest(".optionsMore") ||
          t.closest(".messageOptionsDiv") ||
          t.closest(".chooseOption")
        ) {
          return;
        }
        self.close();
      };
      document.addEventListener("click", self._outsideHandler, true);
    }, 50);
  };

  ReactionPicker.prototype.close = function () {
    if (!this._el) return;
    this._el.style.display = "none";
    this.visible = false;
    if (this._outsideHandler) {
      document.removeEventListener("click", this._outsideHandler, true);
      this._outsideHandler = null;
    }
    var opts = document.querySelectorAll(".optionsMore, .chooseOption");
    for (var i = 0; i < opts.length; i++) {
      opts[i].style.display = "none";
    }
  };

  window.ReactionPicker = ReactionPicker;
})();

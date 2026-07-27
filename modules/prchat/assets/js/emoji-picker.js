/**
 * Emoji Picker UI for PRChat
 * Provides a user-friendly interface for selecting emojis
 */

class EmojiPicker {
  constructor() {
    this.customEmoji = new CustomEmoji();
    this.isVisible = false;
    this.currentCategory = "Smileys";
    this.recentEmojis = this.loadRecentEmojis();
    this.picker = null;
    this.targetTextarea = null;
  }

  /**
   * Initialize the emoji picker
   */
  init() {
    this.createPicker();
    this.bindEvents();
  }

  /**
   * Create the emoji picker HTML structure
   */
  createPicker() {
    const pickerHtml = `
            <div id="emoji-picker" class="emoji-picker">
                <div class="emoji-picker-header">
                    <div class="emoji-categories">
                        <button class="emoji-category-btn active" data-category="Recent">Recent</button>
                        <button class="emoji-category-btn" data-category="Smileys">😀</button>
                        <button class="emoji-category-btn" data-category="Hearts">❤️</button>
                        <button class="emoji-category-btn" data-category="Shortcuts">:)</button>
                    </div>
                </div>
                <div class="emoji-picker-body">
                    <div class="emoji-grid" id="emoji-grid">
                        <!-- Emojis will be populated here -->
                    </div>
                </div>
            </div>
        `;

    // Remove existing picker if any
    $("#emoji-picker").remove();

    // Add to body
    $("body").append(pickerHtml);
    this.picker = $("#emoji-picker");

    // Initially show recent emojis
    this.showCategory("Recent");
  }

  /**
   * Bind event handlers
   */
  bindEvents() {
    // Category buttons
    $(document).on("click", ".emoji-category-btn", (e) => {
      e.preventDefault();
      const category = $(e.target).data("category");
      this.showCategory(category);

      // Update active button
      $(".emoji-category-btn").removeClass("active");
      $(e.target).addClass("active");
    });

    // Emoji selection
    $(document).on("click", ".emoji-item", (e) => {
      e.preventDefault();
      const emoji = $(e.target).text();
      this.selectEmoji(emoji);
    });

    // Close picker when clicking outside
    $(document).on("click", (e) => {
      if (
        this.isVisible &&
        !$(e.target).closest(
          '#emoji-picker, .chat-settings-option[data-action="emoji-picker"], .chat-action-emoji, .emoji-trigger'
        ).length
      ) {
        this.hide();
      }
    });

    // ESC key to close
    $(document).on("keydown", (e) => {
      if (e.key === "Escape" && this.isVisible) {
        this.hide();
      }
    });
  }

  /**
   * Show emojis for a specific category
   */
  showCategory(category) {
    const grid = $("#emoji-grid");
    grid.empty();

    let emojis = {};

    if (category === "Recent") {
      // Show recent emojis
      for (const emoji of this.recentEmojis) {
        emojis[emoji] = emoji;
      }
    } else {
      // Get emojis by category
      const categories = this.customEmoji.getEmojisByCategory();
      emojis = categories[category] || {};
    }

    // If no emojis in category, show message
    if (Object.keys(emojis).length === 0) {
      if (category === "Recent") {
        grid.html('<div class="emoji-empty">No recent emojis</div>');
      } else {
        grid.html('<div class="emoji-empty">No emojis in this category</div>');
      }
      return;
    }

    // Create emoji items
    for (const [shortcode, emoji] of Object.entries(emojis)) {
      const emojiItem = $('<div class="emoji-item"></div>')
        .attr("title", shortcode)
        .attr("data-shortcode", shortcode)
        .text(emoji);
      grid.append(emojiItem);
    }

    this.currentCategory = category;
  }

  /**
   * Handle emoji selection
   */
  selectEmoji(emoji) {
    if (!this.targetTextarea || !this.targetTextarea.length) {
      console.warn("No target textarea found for emoji insertion");
      return;
    }

    // Insert emoji at cursor position
    const textarea = this.targetTextarea[0];
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;

    // Insert emoji
    const newText = text.substring(0, start) + emoji + text.substring(end);
    textarea.value = newText;

    // Set cursor position after emoji
    const newCursorPos = start + emoji.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);

    // Focus back to textarea
    textarea.focus();

    // Add to recent emojis
    this.addToRecent(emoji);

    // Trigger input event for any listeners
    $(textarea).trigger("input");
  }

  /**
   * Add emoji to recent list
   */
  addToRecent(emoji) {
    // Remove if already exists
    this.recentEmojis = this.recentEmojis.filter((e) => e !== emoji);

    // Add to beginning
    this.recentEmojis.unshift(emoji);

    // Keep only last 24 emojis
    this.recentEmojis = this.recentEmojis.slice(0, 24);

    // Save to localStorage
    this.saveRecentEmojis();
  }

  /**
   * Load recent emojis from localStorage
   */
  loadRecentEmojis() {
    try {
      const stored = localStorage.getItem("prchat_recent_emojis");
      return stored
        ? JSON.parse(stored)
        : ["😀", "😂", "❤️", "👍", "🔥", "💯", "😍", "🤔"];
    } catch (e) {
      return ["😀", "😂", "❤️", "👍", "🔥", "💯", "😍", "🤔"];
    }
  }

  /**
   * Save recent emojis to localStorage
   */
  saveRecentEmojis() {
    try {
      localStorage.setItem(
        "prchat_recent_emojis",
        JSON.stringify(this.recentEmojis)
      );
    } catch (e) {
      console.warn("Could not save recent emojis to localStorage");
    }
  }

  /**
   * Show the emoji picker
   */
  show(targetTextarea, position) {
    this.targetTextarea = targetTextarea;

    if (!this.picker) {
      this.init();
    }

    // Reveal off-screen so the browser can compute real dimensions
    this.picker.css({ position: "fixed", visibility: "hidden", top: "-9999px", left: "-9999px" });
    this.picker.addClass("show");
    void this.picker[0].offsetHeight; // force reflow

    // Position the picker just above the trigger element
    if (position) {
      const pickerHeight = this.picker.outerHeight() || 400;
      const pickerWidth = this.picker.outerWidth() || 320;
      const windowWidth = $(window).width();

      // Place directly above the trigger with a small gap
      let top = position.top - pickerHeight - 8;
      let left = position.left - (pickerWidth / 2);

      // Clamp to viewport top edge (always stay above)
      if (top < 10) {
        top = 10;
      }

      // Keep within right edge
      if (left + pickerWidth > windowWidth - 10) {
        left = windowWidth - pickerWidth - 10;
      }

      // Keep within left edge
      if (left < 10) {
        left = 10;
      }

      this.picker.css({
        top: top + "px",
        left: left + "px",
        visibility: "visible",
        zIndex: 1001,
      });
    } else {
      this.picker.css({ visibility: "visible", top: "auto", left: "auto" });
    }

    this.isVisible = true;
  }

  /**
   * Hide the emoji picker
   */
  hide() {
    if (this.picker) {
      this.picker.removeClass("show");
    }
    this.isVisible = false;
    this.targetTextarea = null;
  }

  /**
   * Toggle the emoji picker
   */
  toggle(targetTextarea, position) {
    if (this.isVisible) {
      this.hide();
    } else {
      this.show(targetTextarea, position);
    }
  }
}

// Global function for easy access
window.toggleEmojiPicker = function (triggerElement) {
  if (!window.emojiPickerInstance) {
    window.emojiPickerInstance = new EmojiPicker();
    window.emojiPickerInstance.init();
  }

  // Find the target textarea based on the context
  let targetTextarea;
  const settingsDropdown = $(triggerElement).closest(".chat-settings-dropdown");

  if (settingsDropdown.length) {
    // Find textarea in the same form
    const form = settingsDropdown.closest("form");
    targetTextarea = form.find("textarea").first();
  } else {
    // Fallback: find active textarea
    targetTextarea = $(".message-input textarea:visible").first();
  }

  if (!targetTextarea.length) {
    console.warn("Could not find target textarea for emoji picker");
    return;
  }

  // Pass the trigger element's actual position so show() can place the picker above it
  const triggerRect = triggerElement.getBoundingClientRect();
  const position = {
    top: triggerRect.top,
    bottom: triggerRect.bottom,
    left: triggerRect.left + (triggerRect.width / 2),
  };

  window.emojiPickerInstance.toggle(targetTextarea, position);
};

// Make EmojiPicker available globally
window.EmojiPicker = EmojiPicker;

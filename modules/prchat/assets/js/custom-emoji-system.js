/**
 * Custom Emoji System for PRChat
 * Handles emoji conversion between shortcodes and Unicode
 * Includes URL protection to prevent corruption of links
 */

class CustomEmoji {
  constructor() {
    this.emojiMap = {
      // Smileys & Emotion
      ":grinning:": "😀",
      ":smiley:": "😃",
      ":smile:": "😄",
      ":grin:": "😁",
      ":laughing:": "😆",
      ":sweat_smile:": "😅",
      ":rofl:": "🤣",
      ":joy:": "😂",
      ":slightly_smiling_face:": "🙂",
      ":upside_down_face:": "🙃",
      ":wink:": "😉",
      ":blush:": "😊",
      ":innocent:": "😇",
      ":smiling_face_with_hearts:": "🥰",
      ":heart_eyes:": "😍",
      ":star_struck:": "🤩",
      ":kissing_heart:": "😘",
      ":kissing:": "😗",
      ":kissing_closed_eyes:": "😚",
      ":kissing_smiling_eyes:": "😙",
      ":yum:": "😋",
      ":stuck_out_tongue:": "😛",
      ":stuck_out_tongue_winking_eye:": "😜",
      ":zany_face:": "🤪",
      ":stuck_out_tongue_closed_eyes:": "😝",
      ":money_mouth_face:": "🤑",
      ":hugs:": "🤗",
      ":hand_over_mouth:": "🤭",
      ":shushing_face:": "🤫",
      ":thinking:": "🤔",
      ":zipper_mouth_face:": "🤐",
      ":raised_eyebrow:": "🤨",
      ":neutral_face:": "😐",
      ":expressionless:": "😑",
      ":no_mouth:": "😶",
      ":smirk:": "😏",
      ":unamused:": "😒",
      ":roll_eyes:": "🙄",
      ":grimacing:": "😬",
      ":lying_face:": "🤥",
      ":relieved:": "😌",
      ":pensive:": "😔",
      ":sleepy:": "😪",
      ":drooling_face:": "🤤",
      ":sleeping:": "😴",
      ":mask:": "😷",
      ":face_with_thermometer:": "🤒",
      ":face_with_head_bandage:": "🤕",
      ":nauseated_face:": "🤢",
      ":vomiting_face:": "🤮",
      ":sneezing_face:": "🤧",
      ":hot_face:": "🥵",
      ":cold_face:": "🥶",
      ":woozy_face:": "🥴",
      ":dizzy_face:": "😵",
      ":exploding_head:": "🤯",
      ":cowboy_hat_face:": "🤠",
      ":partying_face:": "🥳",
      ":smiling_face_with_sunglasses:": "😎",
      ":sunglasses:": "😎",
      ":nerd_face:": "🤓",
      ":confused:": "😕",
      ":worried:": "😟",
      ":slightly_frowning_face:": "🙁",
      ":frowning_face:": "☹️",
      ":open_mouth:": "😮",
      ":hushed:": "😯",
      ":astonished:": "😲",
      ":flushed:": "😳",
      ":pleading_face:": "🥺",
      ":frowning:": "😦",
      ":anguished:": "😧",
      ":fearful:": "😨",
      ":cold_sweat:": "😰",
      ":disappointed_relieved:": "😥",
      ":cry:": "😢",
      ":sob:": "😭",
      ":scream:": "😱",
      ":confounded:": "😖",
      ":persevere:": "😣",
      ":disappointed:": "😞",
      ":sweat:": "😓",
      ":weary:": "😩",
      ":tired_face:": "😫",
      ":triumph:": "😤",
      ":rage:": "😡",
      ":angry:": "😠",
      ":cursing_face:": "🤬",
      ":smiling_imp:": "😈",
      ":imp:": "👿",
      ":skull:": "💀",
      ":skull_and_crossbones:": "☠️",
      ":poop:": "💩",
      ":clown_face:": "🤡",
      ":japanese_ogre:": "👹",
      ":japanese_goblin:": "👺",
      ":ghost:": "👻",
      ":alien:": "👽",
      ":space_invader:": "👾",
      ":robot:": "🤖",

      // Hearts & Symbols
      ":heart:": "❤️",
      ":orange_heart:": "🧡",
      ":yellow_heart:": "💛",
      ":green_heart:": "💚",
      ":blue_heart:": "💙",
      ":purple_heart:": "💜",
      ":black_heart:": "🖤",
      ":white_heart:": "🤍",
      ":brown_heart:": "🤎",
      ":broken_heart:": "💔",
      ":heart_exclamation:": "❣️",
      ":two_hearts:": "💕",
      ":revolving_hearts:": "💞",
      ":heartbeat:": "💓",
      ":heartpulse:": "💗",
      ":sparkling_heart:": "💖",
      ":cupid:": "💘",
      ":gift_heart:": "💝",
      ":heart_decoration:": "💟",
      ":peace_symbol:": "☮️",
      ":latin_cross:": "✝️",
      ":star_and_crescent:": "☪️",
      ":om:": "🕉️",
      ":wheel_of_dharma:": "☸️",
      ":star_of_david:": "✡️",
      ":six_pointed_star:": "🔯",
      ":menorah:": "🕎",
      ":yin_yang:": "☯️",
      ":orthodox_cross:": "☦️",
      ":place_of_worship:": "🛐",
      ":ophiuchus:": "⛎",
      ":aries:": "♈",
      ":taurus:": "♉",
      ":gemini:": "♊",
      ":cancer:": "♋",
      ":leo:": "♌",
      ":virgo:": "♍",
      ":libra:": "♎",
      ":scorpius:": "♏",
      ":sagittarius:": "♐",
      ":capricorn:": "♑",
      ":aquarius:": "♒",
      ":pisces:": "♓",

      // Text shortcuts
      "<3": "❤️",
      "</3": "💔",
      ":)": "🙂",
      ":-)": "🙂",
      ":(": "🙁",
      ":-(": "🙁",
      ":D": "😀",
      ":-D": "😀",
      ":P": "😛",
      ":-P": "😛",
      ":p": "😛",
      ":-p": "😛",
      ";)": "😉",
      ";-)": "😉",
      ":o": "😮",
      ":-o": "😮",
      ":O": "😮",
      ":-O": "😮",
      ":|": "😐",
      ":-|": "😐",
      ":*": "😘",
      ":-*": "😘",
      ":thumbsup:": "👍",
      ":thumbsdown:": "👎",
      ":thumbs_up:": "👍",
      ":thumbs_down:": "👎",
      ":+1:": "👍",
      ":-1:": "👎",
      ":clap:": "👏",
      ":wave:": "👋",
      ":fire:": "🔥",
      ":100:": "💯",

      // Missing emojis that cause ???? on storage
      ":tada:": "🎉",
      ":party:": "🎉",
      ":selfie:": "🤳",
      ":pinching_hand:": "🤏",
      ":love_you_gesture:": "🤟",
      ":raised_hand:": "✋",
      ":ok_hand:": "👌",
      ":pinched_fingers:": "🤌",
      ":victory_hand:": "✌️",
      ":crossed_fingers:": "🤞",
      ":call_me_hand:": "🤙",
      ":muscle:": "💪",
      ":pray:": "🙏",
      ":handshake:": "🤝",
      ":writing_hand:": "✍️",
      ":nail_care:": "💅",
      ":eyes:": "👀",
      ":tongue:": "👅",
      ":lips:": "👄",
      ":brain:": "🧠",
      ":people_hugging:": "🫂",
      ":star:": "⭐",
      ":sparkles:": "✨",
      ":boom:": "💥",
      ":droplet:": "💧",
      ":dash:": "💨",
      ":monkey_face:": "🐵",
      ":see_no_evil:": "🙈",
      ":hear_no_evil:": "🙉",
      ":speak_no_evil:": "🙊",
      ":rocket:": "🚀",
      ":check_mark:": "✅",
      ":x:": "❌",
      ":warning:": "⚠️",
      ":question:": "❓",
      ":exclamation:": "❗",
      ":bulb:": "💡",
      ":mega:": "📣",
      ":bell:": "🔔",
      ":gift:": "🎁",
      ":trophy:": "🏆",
      ":medal:": "🏅",
      ":crown:": "👑",
      ":gem:": "💎",
      ":money_bag:": "💰",
      ":calendar:": "📅",
      ":clock:": "🕐",
      ":hourglass:": "⏳",
      ":lock:": "🔒",
      ":key:": "🔑",
      ":hammer:": "🔨",
      ":link:": "🔗",
      ":paperclip:": "📎",
      ":scissors:": "✂️",
      ":pencil:": "✏️",
      ":book:": "📖",
      ":memo:": "📝",
      ":email:": "📧",
      ":phone:": "📱",
      ":computer:": "💻",
      ":camera:": "📷",
      ":pizza:": "🍕",
      ":coffee:": "☕",
      ":beer:": "🍺",
      ":wine_glass:": "🍷",
      ":cake:": "🎂",
      ":balloon:": "🎈",
      ":confetti_ball:": "🎊",
      ":musical_note:": "🎵",
      ":headphones:": "🎧",
      ":sun:": "☀️",
      ":rainbow:": "🌈",
      ":umbrella:": "☂️",
      ":snowflake:": "❄️",
      ":zap:": "⚡",
      ":earth:": "🌍",
      ":rose:": "🌹",
      ":four_leaf_clover:": "🍀",
      ":palm_tree:": "🌴",
    };

    // Create reverse mapping for converting Unicode back to shortcodes
    this.reverseMap = {};
    for (const [shortcode, emoji] of Object.entries(this.emojiMap)) {
      this.reverseMap[emoji] = shortcode;
    }
  }

  /**
   * Convert shortcodes to Unicode emojis for display
   * Includes URL protection to prevent corruption
   */
  shortcodesToEmojis(text) {
    if (!text || typeof text !== "string") {
      return text;
    }

    // Protect URLs from emoji processing
    const urlPlaceholders = {};
    let placeholderIndex = 0;

    // Match URLs (http, https, ftp, etc.)
    const urlRegex = /(https?:\/\/[^\s]+|ftp:\/\/[^\s]+|www\.[^\s]+)/gi;
    text = text.replace(urlRegex, (match) => {
      const placeholder = `__URL_PLACEHOLDER_${placeholderIndex}__`;
      urlPlaceholders[placeholder] = match;
      placeholderIndex++;
      return placeholder;
    });

    // Convert shortcodes to emojis (process longer shortcodes first to avoid conflicts)
    const sortedEntries = Object.entries(this.emojiMap).sort(
      (a, b) => b[0].length - a[0].length
    );
    for (const [shortcode, emoji] of sortedEntries) {
      const regex = new RegExp(this.escapeRegex(shortcode), "g");
      text = text.replace(regex, emoji);
    }

    // Restore URLs
    for (const [placeholder, url] of Object.entries(urlPlaceholders)) {
      text = text.replace(placeholder, url);
    }

    return text;
  }

  /**
   * Convert Unicode emojis to shortcodes for storage
   */
  emojisToShortcodes(text) {
    if (!text || typeof text !== "string") {
      return text;
    }

    // Process longer emojis first to avoid conflicts
    const sortedReverseEntries = Object.entries(this.reverseMap).sort(
      (a, b) => b[0].length - a[0].length
    );
    for (const [emoji, shortcode] of sortedReverseEntries) {
      const regex = new RegExp(this.escapeRegex(emoji), "g");
      text = text.replace(regex, shortcode);
    }

    return text;
  }

  /**
   * Escape special regex characters
   */
  escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  /**
   * Get all available emojis for picker
   */
  getAllEmojis() {
    return this.emojiMap;
  }

  /**
   * Get emojis by category for organized display
   */
  getEmojisByCategory() {
    const emojis = this.getAllEmojis();
    const categories = {
      Smileys: {},
      Hearts: {},
      Shortcuts: {},
    };

    for (const [shortcode, emoji] of Object.entries(emojis)) {
      if (
        shortcode.includes("heart") ||
        shortcode === "<3" ||
        shortcode === "</3"
      ) {
        categories.Hearts[shortcode] = emoji;
      } else if (shortcode.length <= 3 && !shortcode.includes(":")) {
        categories.Shortcuts[shortcode] = emoji;
      } else {
        categories.Smileys[shortcode] = emoji;
      }
    }

    return categories;
  }
}

// Make globally available
window.CustomEmoji = CustomEmoji;

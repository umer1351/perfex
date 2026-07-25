/**
 * ChatSoundManager - Centralized sound notification system for Perfex Chat
 *
 * Features:
 * - Oscillated sounds with different tones for different events
 * - Per-user sound enable/disable functionality
 * - Automatic AudioContext management with user gesture handling
 * - Multiple sound types (message, call, notification, etc.)
 * - Persistent settings via localStorage
 *
 * @version 1.0.0
 */
class ChatSoundManager {
  constructor() {
    this.audioContext = null;
    this.audioContextReady = false;
    this.globalSoundEnabled = true;
    this.userStatus = "online";

    // Sound type definitions with oscillated patterns
    this.soundTypes = {
      message: {
        frequencies: [800, 600],
        durations: [0.15, 0.1],
        volumes: [0.3, 0.2],
        type: "sine",
      },
      call_incoming: {
        frequencies: [880, 440, 880, 440],
        durations: [0.4, 0.2, 0.4, 0.2],
        volumes: [0.4, 0.3, 0.4, 0.3],
        type: "sine",
      },
      call_outgoing: {
        frequencies: [660, 440, 660, 440],
        durations: [0.3, 0.15, 0.3, 0.15],
        volumes: [0.35, 0.25, 0.35, 0.25],
        type: "sine",
      },
      call_ended: {
        frequencies: [440, 220],
        durations: [0.2, 0.3],
        volumes: [0.3, 0.2],
        type: "triangle",
      },
      notification: {
        frequencies: [1000],
        durations: [0.1],
        volumes: [0.25],
        type: "sine",
      },
      client_message: {
        frequencies: [587.33, 659.25, 783.99],
        durations: [0.15, 0.15, 0.2],
        volumes: [0.25, 0.3, 0.35],
        type: "sine",
      },
      staff_message: {
        frequencies: [700, 500],
        durations: [0.15, 0.1],
        volumes: [0.3, 0.2],
        type: "sine",
      },
      group_message: {
        frequencies: [600, 800, 600],
        durations: [0.1, 0.1, 0.1],
        volumes: [0.25, 0.3, 0.25],
        type: "square",
      },
    };

    this.init();
  }

  /**
   * Initialize the sound manager
   */
  init() {
    this.loadSettings();
    this.setupEventListeners();

    // Expose global unlock function for compatibility
    window.prchatUnlockAudioContext = () => this.unlockAudioContext();
  }

  /**
   * Load settings from localStorage
   */
  loadSettings() {
    try {
      const globalEnabled = localStorage.getItem("chatGlobalSoundEnabled");
      this.globalSoundEnabled =
        globalEnabled !== null ? JSON.parse(globalEnabled) : true;

      // Clean up legacy per-contact sound settings (replaced by server-side mute)
      localStorage.removeItem("soundDisabledMembers");
    } catch (error) {
      console.error("[ChatSoundManager] Failed to load settings:", error);
      this.globalSoundEnabled = true;
    }
  }

  /**
   * Save settings to localStorage
   */
  saveSettings() {
    try {
      localStorage.setItem(
        "chatGlobalSoundEnabled",
        JSON.stringify(this.globalSoundEnabled)
      );
    } catch (error) {
      console.error("[ChatSoundManager] Failed to save settings:", error);
    }
  }

  /**
   * Setup event listeners for user interactions
   */
  setupEventListeners() {
    // Auto-unlock on any user interaction
    const unlockEvents = ["click", "touchstart", "keydown"];
    const unlockHandler = (event) => {
      // Only unlock on trusted events (real user interactions, not synthetic jQuery triggers)
      if (!event || !event.isTrusted) {
        return;
      }
      
      this.unlockAudioContext();
      unlockEvents.forEach((eventName) => {
        document.removeEventListener(eventName, unlockHandler);
      });
    };

    unlockEvents.forEach((event) => {
      document.addEventListener(event, unlockHandler, { once: false });
    });
  }

  /**
   * Initialize AudioContext after user gesture
   */
  async unlockAudioContext() {
    if (this.audioContextReady) {
      return true;
    }

    try {
      if (!this.audioContext) {
        this.audioContext = new (window.AudioContext ||
          window.webkitAudioContext)();
        
        // Update ready state when AudioContext state changes
        this.audioContext.onstatechange = () => {
          this.audioContextReady = this.audioContext.state === "running";
        };
      }

      if (this.audioContext.state === "suspended") {
        await this.audioContext.resume();
      }

      this.audioContextReady = this.audioContext.state === "running";
      return this.audioContextReady;
    } catch (error) {
      console.error("[ChatSoundManager] Failed to unlock AudioContext:", error);
      this.audioContextReady = false;
      return false;
    }
  }

  /**
   * Check if sound is enabled globally (per-contact muting is handled server-side)
   * @returns {boolean}
   */
  isSoundEnabled() {
    if (!this.globalSoundEnabled) return false;
    if (this.userStatus === "busy" || this.userStatus === "offline")
      return false;
    return true;
  }

  /**
   * Set global sound enable/disable
   * @param {boolean} enabled
   */
  setGlobalSound(enabled) {
    this.globalSoundEnabled = enabled;
    this.saveSettings();
  }

  /**
   * Update user status (affects sound behavior)
   * @param {string} status - 'online', 'busy', 'offline'
   */
  setUserStatus(status) {
    this.userStatus = status;
  }

  /**
   * Play oscillated sound pattern
   * @param {string} soundType - Type of sound from this.soundTypes
   * @param {string} contactId - Contact ID to check if sound is enabled
   * @returns {Promise<boolean>} - Success status
   */
  async playSound(soundType = "message", contactId = null) {
    // Check if sound should be played (per-contact muting handled by caller)
    if (!this.isSoundEnabled()) {
      return false;
    }

    // Ensure AudioContext is ready
    if (!this.audioContextReady) {
      const unlocked = await this.unlockAudioContext();
      if (!unlocked) {
        console.warn(
          "[ChatSoundManager] Cannot play sound - AudioContext not ready"
        );
        return false;
      }
    }

    const pattern = this.soundTypes[soundType] || this.soundTypes.message;

    try {
      await this.playOscillatedPattern(pattern);
      return true;
    } catch (error) {
      console.error("[ChatSoundManager] Sound playback failed:", error);
      return false;
    }
  }

  /**
   * Play an oscillated sound pattern
   * @param {object} pattern - Sound pattern definition
   * @returns {Promise<void>}
   */
  async playOscillatedPattern(pattern) {
    let currentTime = this.audioContext.currentTime;

    for (let i = 0; i < pattern.frequencies.length; i++) {
      const oscillator = this.audioContext.createOscillator();
      const gainNode = this.audioContext.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(this.audioContext.destination);

      oscillator.frequency.value = pattern.frequencies[i];
      oscillator.type = pattern.type;

      const duration = pattern.durations[i] || 0.1;
      const volume = pattern.volumes[i] || 0.3;

      gainNode.gain.setValueAtTime(0, currentTime);
      gainNode.gain.linearRampToValueAtTime(volume, currentTime + 0.01);
      gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + duration);

      oscillator.start(currentTime);
      oscillator.stop(currentTime + duration);

      currentTime += duration + 0.05; // Small gap between tones
    }
  }

  /**
   * Convenience methods for different sound types
   */

  playMessageSound(contactId = null) {
    return this.playSound("message", contactId);
  }

  playStaffMessageSound(contactId = null) {
    return this.playSound("staff_message", contactId);
  }

  playClientMessageSound(contactId = null) {
    return this.playSound("client_message", contactId);
  }

  playGroupMessageSound(contactId = null) {
    return this.playSound("group_message", contactId);
  }

  playCallIncomingSound() {
    return this.playSound("call_incoming");
  }

  playCallOutgoingSound() {
    return this.playSound("call_outgoing");
  }

  playCallEndedSound() {
    return this.playSound("call_ended");
  }

  playNotificationSound() {
    return this.playSound("notification");
  }

  /**
   * Legacy compatibility methods
   */

  playNotificationBeep(contactId = null) {
    return this.playMessageSound(contactId);
  }

  appendUserSound(data = null) {
    const contactId = data && data.from ? `staff_${data.from}` : null;
    return this.playStaffMessageSound(contactId);
  }

  initUserSound(data = null) {
    if (!data) return false;
    const contactId = `staff_${data.from || data.original_from}`;
    return this.playStaffMessageSound(contactId);
  }

  initClientSound(data = null) {
    if (!data) return false;
    const contactId = `client_${data.from || data.original_from}`;
    return this.playClientMessageSound(contactId);
  }

  playChatSound() {
    return this.playMessageSound();
  }

  playPushSound() {
    return this.playMessageSound();
  }

  clientSeenNotify() {
    return this.playNotificationSound();
  }

  userSeenNotify(data = null) {
    return this.playNotificationSound();
  }

  // No-op for compatibility
  stopUserSound() {
    return true;
  }
}

// Initialize global instance
window.chatSoundManager = new ChatSoundManager();

 // Legacy compatibility function (per-contact muting now handled server-side)
window.isSoundActive = function (data) {
  if (window.chatSoundManager) {
    return window.chatSoundManager.isSoundEnabled();
  }
  return true;
};

// Per-contact sound toggle removed - replaced by Mute feature (server-side)

// Export legacy functions for backward compatibility
window.playNotificationBeep = (contactId) =>
  window.chatSoundManager.playNotificationBeep(contactId);
window.appendUserSound = (data) =>
  window.chatSoundManager.appendUserSound(data);
window.initUserSound = (data) => window.chatSoundManager.initUserSound(data);
window.initClientSound = (data) =>
  window.chatSoundManager.initClientSound(data);
window.playChatSound = () => window.chatSoundManager.playChatSound();
window.playPushSound = () => window.chatSoundManager.playPushSound();
window.clientSeenNotify = (data) =>
  window.chatSoundManager.clientSeenNotify(data);
window.userSeenNotify = (data) => window.chatSoundManager.userSeenNotify(data);
window.stopUserSound = () => window.chatSoundManager.stopUserSound();

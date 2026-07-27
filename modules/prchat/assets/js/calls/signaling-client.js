/*
 * Minimal signaling client scaffold (disabled by default via feature flags)
 * We will wire this to Pusher/Socket.IO in subsequent steps.
 */
(function (window) {
  "use strict";

  /**
   * @typedef {Object} SignalingClientOptions
   * @property {function(string, any):void} onMessage
   */

  /**
   * SignalingClient provides a minimal publish/subscribe API to exchange SDP/ICE
   * messages between peers. Currently a no-op scaffold.
   * @param {SignalingClientOptions} options
   */
  function SignalingClient(options) {
    this.onMessage =
      options && options.onMessage ? options.onMessage : function () {};
    this.pusher = null;
  }

  /**
   * Connect to signaling backend (placeholder)
   */
  SignalingClient.prototype.connect = function () {
    return new Promise(
      function (resolve) {
        if (window.Pusher && window.pusher) {
          this.pusher = window.pusher;
          resolve();
          return;
        }
        resolve();
      }.bind(this)
    );
  };

  /**
   * Send a signaling message (placeholder)
   * @param {string} channel
   * @param {any} payload
   */
  SignalingClient.prototype.send = function (channel, payload) {
  };

  window.ChatSignalingClient = SignalingClient;
})(window);

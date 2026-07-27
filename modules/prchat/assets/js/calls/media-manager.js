/*
 * Minimal media manager scaffold to request user media.
 * Disabled by feature flags; used later for voice/video calls.
 */
(function (window, navigator) {
  "use strict";

  /**
   * Request audio stream from user
   * @returns {Promise<MediaStream>}
   */
  function requestMicrophone() {
    return navigator.mediaDevices.getUserMedia({ audio: true });
  }

  /**
   * Request audio+video stream from user
   * @returns {Promise<MediaStream>}
   */
  function requestCamera() {
    return navigator.mediaDevices.getUserMedia({ audio: true, video: true });
  }

  window.ChatMediaManager = {
    requestMicrophone: requestMicrophone,
    requestCamera: requestCamera,
  };
})(window, navigator);

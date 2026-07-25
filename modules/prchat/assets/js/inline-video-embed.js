/**
 * Inline Video Embed System for PrChat
 * Lightweight inline video players
 * Supports YouTube, Vimeo, Facebook Videos
 */

(function () {
  "use strict";

  // Video platform configurations
  const VIDEO_PLATFORMS = {
    youtube: {
      regex: /(youtube(-nocookie)?\.com|youtu\.be)/i,
      extractId: function (url) {
        const patterns = [
          /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
          /youtube\.com\/watch\?.*v=([^&\n?#]+)/,
        ];
        for (const pattern of patterns) {
          const match = url.match(pattern);
          if (match) return match[1];
        }
        return null;
      },
      getThumbnail: function (videoId) {
        return `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
      },
      getEmbedUrl: function (videoId) {
        return `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
      },
    },
    vimeo: {
      regex: /(vimeo(pro)?.com)/i,
      extractId: function (url) {
        const match = url.match(/vimeo\.com\/(\d+)/);
        return match ? match[1] : null;
      },
      getThumbnail: function (videoId) {
        // Vimeo thumbnails require API call, we'll use a placeholder for now
        return `https://vumbnail.com/${videoId}.jpg`;
      },
      getEmbedUrl: function (videoId) {
        return `https://player.vimeo.com/video/${videoId}?autoplay=1`;
      },
    },
    facebook: {
      regex: /(facebook\.com)\/([a-z0-9_-]*)\/videos\//i,
      extractId: function (url) {
        const match = url.match(/facebook\.com\/.*\/videos\/(\d+)/);
        return match ? match[1] : null;
      },
      getThumbnail: function (videoId) {
        // Facebook doesn't provide direct thumbnail access, use placeholder
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQwIiBoZWlnaHQ9IjM2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzc2NWE2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GYWNlYm9vayBWaWRlbzwvdGV4dD48L3N2Zz4=";
      },
      getEmbedUrl: function (videoId) {
        return `https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/video.php?v=${videoId}&autoplay=1`;
      },
    },
  };

  /**
   * Detects if a URL is a supported video platform
   */
  function detectVideoPlatform(url) {
    for (const [platform, config] of Object.entries(VIDEO_PLATFORMS)) {
      if (config.regex.test(url)) {
        return platform;
      }
    }
    return null;
  }

  /**
   * Creates an inline video embed element
   */
  function createVideoEmbed(url, platform, videoId) {
    const config = VIDEO_PLATFORMS[platform];
    const thumbnail = config.getThumbnail(videoId);
    const embedUrl = config.getEmbedUrl(videoId);

    const container = document.createElement("div");
    container.className = "prchat-video-embed";
    container.setAttribute("data-video-url", url);
    container.setAttribute("data-platform", platform);
    container.setAttribute("data-video-id", videoId);

    container.innerHTML = `
            <div class="prchat-video-thumbnail" style="
                position: relative;
                cursor: pointer;
                border-radius: 8px;
                overflow: hidden;
                max-width: 480px;
                aspect-ratio: 16/9;
                background: #000;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 8px 0;
            ">
                <img src="${thumbnail}" alt="Video thumbnail" style="
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                " onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                
                <!-- Fallback when thumbnail fails -->
                <div style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 18px;
                    font-weight: 500;
                ">
                    <i class="fa fa-play-circle" style="margin-right: 8px; font-size: 24px;"></i>
                    ${
                      platform.charAt(0).toUpperCase() + platform.slice(1)
                    } Video
                </div>
                
                <!-- Play button overlay -->
                <div class="prchat-video-play-button" style="
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 80px;
                    height: 80px;
                    background: rgba(0, 0, 0, 0.8);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 32px;
                    transition: all 0.3s ease;
                    backdrop-filter: blur(5px);
                ">
                    <i class="fa fa-play" style="margin-left: 4px;"></i>
                </div>

                <!-- Platform badge -->
                <div style="
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 500;
                    backdrop-filter: blur(5px);
                ">
                    ${platform.charAt(0).toUpperCase() + platform.slice(1)}
                </div>
            </div>
        `;

    // Add click handler to load actual video
    container.addEventListener("click", function () {
      // Add a loading state
      container.classList.add("loading");
      loadVideoPlayer(container, embedUrl);
      // Remove loading state after a brief delay
      setTimeout(() => {
        container.classList.remove("loading");
        container.classList.add("loaded");
      }, 300);
    });

    // Add hover effect
    const playButton = container.querySelector(".prchat-video-play-button");
    container.addEventListener("mouseenter", function () {
      playButton.style.transform = "translate(-50%, -50%) scale(1.1)";
      playButton.style.background = "rgba(0, 0, 0, 0.9)";
    });
    container.addEventListener("mouseleave", function () {
      playButton.style.transform = "translate(-50%, -50%) scale(1)";
      playButton.style.background = "rgba(0, 0, 0, 0.8)";
    });

    return container;
  }

  /**
   * Loads the actual video player (iframe)
   */
  function loadVideoPlayer(container, embedUrl) {
    const thumbnail = container.querySelector(".prchat-video-thumbnail");

    // Get the current dimensions of the thumbnail
    const thumbnailRect = thumbnail.getBoundingClientRect();
    const currentWidth = thumbnailRect.width;
    const currentHeight = thumbnailRect.height;

    const iframe = document.createElement("iframe");
    iframe.src = embedUrl;
    iframe.style.cssText = `
             width: 100%;
             height: 100%;
             border: none;
             border-radius: 8px;
             display: block;
         `;
    iframe.setAttribute("allowfullscreen", "");
    iframe.setAttribute(
      "allow",
      "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
    );

    // Maintain the exact same dimensions as the thumbnail
    thumbnail.style.cssText = `
             position: relative;
             width: ${currentWidth}px;
             height: ${currentHeight}px;
             max-width: 480px;
             aspect-ratio: 16/9;
             background: #000;
             border-radius: 8px;
             overflow: hidden;
             margin: 8px 0;
             display: flex;
             align-items: center;
             justify-content: center;
         `;

    thumbnail.innerHTML = "";
    thumbnail.appendChild(iframe);
  }

  /**
   * Processes text and replaces video URLs with inline embeds
   */
  function processVideoEmbeds(text) {
    if (!text) return text;

    // Find all URLs in the text
    const urlRegex = /https?:\/\/[^\s<>"]+/gi;

    return text.replace(urlRegex, function (url) {
      const platform = detectVideoPlatform(url);
      if (!platform) return url;

      const config = VIDEO_PLATFORMS[platform];
      const videoId = config.extractId(url);
      if (!videoId) return url;

      // Create a placeholder that will be replaced with the actual embed
      return `<div class="prchat-video-placeholder" data-url="${url}" data-platform="${platform}" data-video-id="${videoId}"></div>`;
    });
  }

  /**
   * Replaces video placeholders with actual embeds
   */
  function replaceVideoPlaceholders(element) {
    const placeholders = element.querySelectorAll(".prchat-video-placeholder");
    placeholders.forEach(function (placeholder) {
      const url = placeholder.getAttribute("data-url");
      const platform = placeholder.getAttribute("data-platform");
      const videoId = placeholder.getAttribute("data-video-id");

      const embed = createVideoEmbed(url, platform, videoId);
      placeholder.parentNode.replaceChild(embed, placeholder);
    });
  }

  /**
   * Main function to convert video URLs to inline embeds
   */
  function convertVideoLinks(container) {
    // Validate container parameter
    if (!container) {
      return; // Silently return instead of warning for null containers
    }

    // Ensure container is a DOM element
    if (typeof container === "string") {
      container = document.querySelector(container);
      if (!container) {
        return; // Element not found, silently return
      }
    }

    // Check if container is a valid DOM element
    if (
      !container ||
      !container.nodeType ||
      typeof container.querySelectorAll !== "function"
    ) {
      return; // Silently return for invalid containers
    }

    // Check if this container has already been processed
    if (
      container.hasAttribute &&
      container.hasAttribute("data-video-processed")
    ) {
      return; // Already processed, skip
    }

    let hasVideoContent = false;

    try {
      // Process existing text content
      const textNodes = [];
      const walker = document.createTreeWalker(
        container,
        NodeFilter.SHOW_TEXT,
        null,
        false
      );

      let node;
      while ((node = walker.nextNode())) {
        if (node.nodeValue && node.nodeValue.trim()) {
          textNodes.push(node);
        }
      }

      textNodes.forEach(function (textNode) {
        const processed = processVideoEmbeds(textNode.nodeValue);
        if (processed !== textNode.nodeValue) {
          hasVideoContent = true;
          const wrapper = document.createElement("div");
          wrapper.innerHTML = processed;
          if (textNode.parentNode) {
            textNode.parentNode.replaceChild(wrapper, textNode);
            replaceVideoPlaceholders(wrapper);
          }
        }
      });

      // Process existing links that might be videos
      // First, try links specifically marked with data-video-embed
      const videoLinks = container.querySelectorAll(
        'a[data-video-embed="true"]'
      );
      videoLinks.forEach(function (link) {
        const url = link.getAttribute("href");
        const platform = detectVideoPlatform(url);

        if (platform) {
          const config = VIDEO_PLATFORMS[platform];
          const videoId = config.extractId(url);

          if (videoId) {
            hasVideoContent = true;
            const embed = createVideoEmbed(url, platform, videoId);
            if (link.parentNode) {
              link.parentNode.replaceChild(embed, link);
            }
          }
        }
      });

      // Then process any other links that might be videos (fallback)
      const allLinks = container.querySelectorAll("a[href]");
      allLinks.forEach(function (link) {
        // Skip if already processed or if it's not a video link
        if (
          link.hasAttribute("data-video-embed") ||
          link.querySelector(".prchat-video-embed")
        ) {
          return;
        }

        const url = link.getAttribute("href");
        const platform = detectVideoPlatform(url);

        if (platform) {
          const config = VIDEO_PLATFORMS[platform];
          const videoId = config.extractId(url);

          if (videoId) {
            hasVideoContent = true;
            const embed = createVideoEmbed(url, platform, videoId);
            if (link.parentNode) {
              link.parentNode.replaceChild(embed, link);
            }
          }
        }
      });
      // Only mark as processed if we actually found and processed video content
      if (hasVideoContent && container.setAttribute) {
        container.setAttribute("data-video-processed", "true");
      }
    } catch (error) {
      console.error("PrChatVideoEmbed: Error processing video links", error);
    }
  }

  // Export functions for global use
  window.PrChatVideoEmbed = {
    convertVideoLinks: convertVideoLinks,
    processVideoEmbeds: processVideoEmbeds,
    detectVideoPlatform: detectVideoPlatform,
    createVideoEmbed: createVideoEmbed,
  };

  // Auto-initialize on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      // Auto-convert any existing video links
      if (document.body) {
        convertVideoLinks(document.body);
      }
    });
  } else {
    // DOM is already ready
    if (document.body) {
      convertVideoLinks(document.body);
    }
  }
})();

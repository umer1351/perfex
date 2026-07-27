<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script type="text/javascript">
  // Simple drag and drop uploader
  class ChatDragDropUploader {
    constructor() {
      this.activeUploads = new Set();
      this.uploadTimeout = null;
      this.uploadInProgress = false;
      this.pendingUploads = new Map();
      this.isDragging = false;
      this.initializeDragDrop();
    }

    initializeDragDrop() {
      const mainContent = document.querySelector('#frame .content');
      if (mainContent) {
        this.setupDragDropZone(mainContent);
      }

      // Prevent default drag behaviors
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        document.addEventListener(eventName, this.preventDefaults.bind(this), false);
      });

      // Add ESC key listener to cancel drag operation
      document.addEventListener('keydown', this.handleKeyDown.bind(this), false);
    }

    setupDragDropZone(element) {
      // Create overlay with proper translation
      const overlay = document.createElement('div');
      overlay.className = 'drag-drop-overlay';
      const overlayText = "<?= _l('drop_files_here_to_upload') ?>";
      overlay.innerHTML = `
                <div class="drag-drop-content">
                    <i class="fa-solid fa-upload"></i>
                    <p>${overlayText}</p>
                </div>
            `;

      element.style.position = 'relative';
      element.appendChild(overlay);

      // Simple text protection - restore text if it gets cleared
      const paragraph = overlay.querySelector('p');
      if (paragraph) {
        // Store the original text
        paragraph.setAttribute('data-original-text', overlayText);

        // Simple restoration function
        const restoreText = () => {
          if (paragraph.textContent !== overlayText) {
            paragraph.textContent = overlayText;
          }
        };

        // Restore text periodically (simple but effective)
        setInterval(restoreText, 100);

        // Restore on any mutation
        const observer = new MutationObserver(restoreText);
        observer.observe(paragraph, {
          childList: true,
          characterData: true,
          subtree: true
        });
      }

      // Add event listeners
      ['dragenter', 'dragover'].forEach(eventName => {
        element.addEventListener(eventName, this.handleDragEnter.bind(this), false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        element.addEventListener(eventName, this.handleDragLeave.bind(this), false);
      });

      element.addEventListener('drop', this.handleDrop.bind(this), false);
    }

    preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    handleKeyDown(e) {
      // Cancel drag operation on ESC key
      if (e.key === 'Escape' && this.isDragging) {
        this.cancelDragOperation();
      }
    }

    cancelDragOperation() {
      this.isDragging = false;

      // Remove dragover class and hide overlay
      const mainContent = document.querySelector('#frame .content');
      if (mainContent) {
        mainContent.classList.remove('dragover');
        const overlay = mainContent.querySelector('.drag-drop-overlay');
        if (overlay) {
          overlay.classList.remove('active');
        }
      }
    }

    handleDragEnter(e) {
      this.preventDefaults(e);
      if (e.dataTransfer.types.includes('Files')) {
        this.isDragging = true;
        e.currentTarget.classList.add('dragover');
        const overlay = e.currentTarget.querySelector('.drag-drop-overlay');
        if (overlay) {
          overlay.classList.add('active');
          // Ensure text is visible when overlay becomes active
          const paragraph = overlay.querySelector('p');
          if (paragraph) {
            const originalText = paragraph.getAttribute('data-original-text');
            if (originalText && paragraph.textContent !== originalText) {
              paragraph.textContent = originalText;
            }
          }
        }
      }
    }

    handleDragLeave(e) {
      this.preventDefaults(e);
      if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('dragover');
        const overlay = e.currentTarget.querySelector('.drag-drop-overlay');
        if (overlay) {
          overlay.classList.remove('active');
        }
      }
    }

    handleDrop(e) {
      this.preventDefaults(e);
      e.currentTarget.classList.remove('dragover');

      const overlay = e.currentTarget.querySelector('.drag-drop-overlay');
      if (overlay) {
        overlay.classList.remove('active');
      }

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        this.processFiles(files);
      }
    }

    processFiles(files) {
      // Clear any existing timeout
      if (this.uploadTimeout) {
        clearTimeout(this.uploadTimeout);
      }

      // Prevent multiple simultaneous uploads
      if (this.uploadInProgress) {
        return;
      }

      const validFiles = [];
      const timestamp = Date.now();

      // Validate files
      Array.from(files).forEach((file, index) => {
        if (this.validateFile(file)) {
          const fileId = `${file.name}_${file.size}_${timestamp}_${index}`;

          // Enhanced duplicate check
          if (!this.activeUploads.has(fileId) && !this.pendingUploads.has(fileId)) {
            validFiles.push({
              file,
              fileId
            });
            this.activeUploads.add(fileId);
            this.pendingUploads.set(fileId, file);
          }
        }
      });

      if (validFiles.length === 0) {
        return;
      }

      // Set upload in progress flag
      this.uploadInProgress = true;

      // Show uploading indicator
      this.showUploadingIndicator();

      // Upload files one by one with delay
      validFiles.forEach(({
        file,
        fileId
      }, index) => {
        setTimeout(() => {
          this.uploadSingleFile(file, fileId, index === validFiles.length - 1);
        }, index * 500);
      });
    }

    validateFile(file) {
      // Extract just the filename from potential URL or path
      let filename = file.name;
      if (filename.includes('/')) {
        filename = filename.split('/').pop();
      }

      // More lenient validation - only block truly problematic characters
      const regex = new RegExp("[~%]");
      if (regex.test(filename)) {
        alert_float('warning', "<?= _l('chat_permitted_files') ?>");
        return false;
      }
      return true;
    }

    showUploadingIndicator() {
      // Use existing chat-module-loader
      if ($(".chat-module-loader").length == 0) {
        $(".content").prepend("<div class=\"chat-module-loader\"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>");
      } else {
        $(".content .chat-module-loader").fadeIn();
      }
    }

    hideUploadingIndicator() {
      $(".content .chat-module-loader").fadeOut(function () {
        $(this).remove();
      });
    }

    uploadSingleFile(file, fileId, isLast = false) {
      // Determine context and upload
      try {
        if ($('#frame .group_messages .chat_group_messages').length && $('#frame .group_messages .chat_group_messages').is(':visible')) {
          this.uploadToGroup(file, fileId, isLast);
        } else if ($('.chat_clients_list > li.contact_name.selected').length && $('.client_messages').is(':visible')) {
          this.uploadToClient(file, fileId, isLast);
        } else if ($('li.contact.active').length && $('.messages').is(':visible')) {
          this.uploadToStaff(file, fileId, isLast);
        } else {
          this.cleanupFile(fileId);
          if (isLast) {
            this.hideUploadingIndicator();
            this.uploadInProgress = false;
          }
        }
      } catch (error) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
      }
    }

    uploadToStaff(file, fileId, isLast = false) {
      const sentTo = $('li.contact.active').attr('id');
      if (!sentTo) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const form = document.querySelector('form[name="fileForm"]');
      if (!form) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const fileInput = form.querySelector('input[type="file"]');

      // Set flag to prevent duplicate uploads
      if (window.setDragDropUploadInProgress) {
        window.setDragDropUploadInProgress(true);
      }

      // Create a new DataTransfer and file input to avoid conflicts
      const dt = new DataTransfer();
      dt.items.add(file);

      // Temporarily disable the change event to prevent duplicate uploads
      const originalChangeHandler = fileInput.onchange;
      fileInput.onchange = null;

      // Set the files
      fileInput.files = dt.files;

      // Create a unique form data manually to bypass normal form submission
      const formData = new FormData();
      formData.append("userfile", file);
      formData.append("send_to", sentTo);
      formData.append("send_from", userSessionId);

      // Get CSRF token from the 3rd input element
      const csrfInput = form.querySelector('input[type="hidden"]:nth-child(3)');
      if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
      }

      // Setup cleanup
      const cleanup = () => {
        this.cleanupFile(fileId);
        // Restore the original change handler
        fileInput.onchange = originalChangeHandler;
        // Reset form after AJAX completes to prevent race conditions
        form.reset();
        if (isLast) {
          setTimeout(() => {
            this.hideUploadingIndicator();
            this.uploadInProgress = false;
          }, 1000);
        }
      };

      // Upload directly via AJAX to avoid duplicate handlers
      $.ajax({
        type: "POST",
        url: prchatSettings.uploadMethod,
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (r) {
          if (!r.error) {
            var uploadSend = $.Event("keypress", {
              which: 13
            });
            var basePath = "<?php echo base_url('modules/prchat/uploads/'); ?>";
            $("#frame textarea.chatbox").val(basePath + r.upload_data.file_name);
            setTimeout(function () {
              if ($("#frame textarea.chatbox").trigger(uploadSend)) {
                // Success
              }
            }, 100);
            if (typeof getSharedFiles === 'function') {
              getSharedFiles(userSessionId, sentTo);
            }
          } else {
            alert_float("danger", r.error);
          }
          cleanup();
        },
        error: function () {
          alert_float("danger", "<?= _l('chat_upload_failed'); ?>");
          cleanup();
        }
      });
    }

    uploadToClient(file, fileId, isLast = false) {
      const clientSentTo = $('.chat_clients_list > li.contact_name.selected').attr('id');
      if (!clientSentTo) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const form = document.querySelector('form[name="clientFileForm"]');
      if (!form) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const fileInput = form.querySelector('input[type="file"]');

      // Set flag to prevent duplicate uploads
      if (window.setDragDropUploadInProgress) {
        window.setDragDropUploadInProgress(true);
      }

      // Create a new DataTransfer and file input to avoid conflicts
      const dt = new DataTransfer();
      dt.items.add(file);

      // Temporarily disable the change event to prevent duplicate uploads
      const originalChangeHandler = fileInput.onchange;
      fileInput.onchange = null;

      // Set the files
      fileInput.files = dt.files;

      // Create a unique form data manually to bypass normal form submission
      const formData = new FormData();
      formData.append("userfile", file);
      formData.append("send_to", clientSentTo);
      formData.append("send_from", "staff_" + userSessionId);

      // Get CSRF token from the 3rd input element
      const csrfInput = form.querySelector('input[type="hidden"]:nth-child(3)');
      if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
      }

      // Setup cleanup
      const cleanup = () => {
        this.cleanupFile(fileId);
        // Restore the original change handler
        fileInput.onchange = originalChangeHandler;
        // Reset form after AJAX completes to prevent race conditions
        form.reset();
        if (isLast) {
          setTimeout(() => {
            this.hideUploadingIndicator();
            this.uploadInProgress = false;
          }, 1000);
        }
      };

      // Upload directly via AJAX to avoid duplicate handlers
      $.ajax({
        type: "POST",
        url: prchatSettings.uploadMethod,
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (r) {
          if (!r.error) {
            var uploadSend = $.Event("keypress", {
              which: 13
            });
            var basePath = "<?php echo base_url('modules/prchat/uploads/'); ?>";
            $("#frame textarea.client_chatbox").val(basePath + r.upload_data.file_name);
            setTimeout(function () {
              if ($("#frame textarea.client_chatbox").trigger(uploadSend)) {
                // Success
              }
            }, 100);
          } else {
            alert_float("danger", r.error);
          }
          cleanup();
        },
        error: function () {
          alert_float("danger", "<?= _l('chat_upload_failed'); ?>");
          cleanup();
        }
      });
    }

    uploadToGroup(file, fileId, isLast = false) {
      const groupId = $('#frame .group_messages .chat_group_messages').attr('id');
      if (!groupId) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const form = document.querySelector('form[name="groupFileForm"]');
      if (!form) {
        this.cleanupFile(fileId);
        if (isLast) {
          this.hideUploadingIndicator();
          this.uploadInProgress = false;
        }
        return;
      }

      const fileInput = form.querySelector('input[type="file"]');

      // Set flag to prevent duplicate uploads
      if (window.setDragDropUploadInProgress) {
        window.setDragDropUploadInProgress(true);
      }

      // Create a new DataTransfer and file input to avoid conflicts
      const dt = new DataTransfer();
      dt.items.add(file);

      // Temporarily disable the change event to prevent duplicate uploads
      const originalChangeHandler = fileInput.onchange;
      fileInput.onchange = null;

      // Set the files
      fileInput.files = dt.files;

      // Create a unique form data manually to bypass normal form submission
      const formData = new FormData();
      formData.append("userfile", file);
      formData.append("to_group", groupId);
      formData.append("send_from", userSessionId);

      // Get CSRF token from the 3rd input element
      const csrfInput = form.querySelector('input[type="hidden"]:nth-child(3)');
      if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
      }

      // Setup cleanup
      const cleanup = () => {
        this.cleanupFile(fileId);
        // Restore the original change handler
        fileInput.onchange = originalChangeHandler;
        // Reset form after AJAX completes to prevent race conditions
        form.reset();
        if (isLast) {
          setTimeout(() => {
            this.hideUploadingIndicator();
            this.uploadInProgress = false;
          }, 1000);
        }
      };

      // Upload directly via AJAX to avoid duplicate handlers
      $.ajax({
        type: "POST",
        url: prchatSettings.groupUploadMethod,
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (r) {
          if (!r.error) {
            var uploadSend = $.Event("keypress", {
              which: 13
            });
            var basePath = "<?php echo base_url('modules/prchat/uploads/groups/'); ?>";
            $("#frame textarea.group_chatbox").val(basePath + r.upload_data.file_name);
            setTimeout(function () {
              if ($("#frame textarea.group_chatbox").trigger(uploadSend)) {
                // Success
              }
            }, 100);
          } else {
            alert_float("danger", r.error);
          }
          cleanup();
        },
        error: function () {
          alert_float("danger", "<?= _l('chat_upload_failed'); ?>");
          cleanup();
        }
      });
    }

    cleanupFile(fileId) {
      this.activeUploads.delete(fileId);
      this.pendingUploads.delete(fileId);
    }
  }

  // Initialize drag and drop functionality
  function initializeDragDrop() {
    if (typeof window.chatDragDropUploader === 'undefined') {
      window.chatDragDropUploader = new ChatDragDropUploader();
    }
  }

  // Try multiple initialization methods for reliability
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDragDrop);
  } else {
    initializeDragDrop();
  }

  // Fallback with timeout to ensure initialization
  setTimeout(function () {
    if (typeof window.chatDragDropUploader === 'undefined') {
      initializeDragDrop();
    }
  }, 1000);
</script>

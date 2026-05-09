"use strict";

// CRM Chat Module
var CRMChat = (function () {
  var currentCustomerId = null;
  var lastMessageId = 0;
  var lastMessageDate = null; // Track newest message date for polling
  var pollInterval = null;
  var globalPollInterval = null; // For contact list updates
  var gwSyncInterval = null; // For gateway auto-sync
  var selectedFile = null;
  var selectedFileType = null;
  var serverTimeOffset = 0; // Difference in seconds (Server - Client)
  var currentReplyMessageId = null; // Store message ID for reply context
  var currentReplyContent = null;
  var currentReplySenderName = null;
  var currentForwardMessage = null;
  var selectedForwardTargets = [];

  var gwSyncInterval = null; // For gateway auto-sync
  var selectedFile = null;
  var selectedFileType = null;

  // Notification Sound
  var notificationSound = new Audio(appUrl + "assets/media/web_whatsapp.mp3");
  var audioUnlocked = false;

  // Unlock audio on first user interaction (browser security policy)
  var unlockAudio = function () {
    if (audioUnlocked) return;

    // Play and immediately pause to unlock audio context
    notificationSound
      .play()
      .then(function () {
        notificationSound.pause();
        notificationSound.currentTime = 0;
        audioUnlocked = true;
        console.log("🔓 Audio unlocked");
      })
      .catch(function () {
        // Still locked, will try again on next interaction
      });
  };

  var clearReplyContext = function () {
    currentReplyMessageId = null;
    currentReplyContent = null;
    currentReplySenderName = null;
    $("#attachment-preview").empty().addClass("d-none");
  };

  var clearForwardContext = function () {
    currentForwardMessage = null;
    selectedForwardTargets = [];
    $("#forward-search").val("");
    $("#forward-selected-count").text("0 dipilih");
    $("#btn-forward-selected").prop("disabled", true);
    $("#forward-target-list").html(
      '<div class="text-center text-muted py-5">Memuat chat aktif terbaru...</div>',
    );
  };

  var getDisplayName = function (entity, fallback) {
    if (!entity) return fallback || "Tanpa Nama";

    var name =
      entity.display_name ||
      entity.name ||
      entity.wa_number ||
      entity.jid ||
      fallback ||
      "Tanpa Nama";

    name = String(name).trim();
    return name || fallback || "Tanpa Nama";
  };

  var updateChatIdentityInUi = function (chatId, chatType, newName) {
    var displayName = getDisplayName({ display_name: newName }, "Tanpa Nama");

    $('.customer-item[data-id="' + chatId + '"][data-type="' + chatType + '"]')
      .find(".fw-bold, .fw-semibold")
      .first()
      .text(displayName);

    if (
      String(currentCustomerId) === String(chatId) &&
      String(currentCustomerType) === String(chatType)
    ) {
      $("#chat-customer-name").text(displayName);
      $("#sidebar-name").text(displayName);
    }
  };

  var updateForwardSelectionUi = function () {
    var count = selectedForwardTargets.length;
    $("#forward-selected-count").text(count + " dipilih");
    $("#btn-forward-selected").prop("disabled", count === 0);

    $(".forward-target-item").each(function () {
      var item = $(this);
      var targetId = String(item.data("id"));
      var targetType = String(item.data("type") || "individual");
      var isSelected = selectedForwardTargets.some(function (target) {
        return String(target.id) === targetId && String(target.type) === targetType;
      });

      item.toggleClass("active border-primary bg-light-primary", isSelected);
      item.find(".forward-target-checkbox").prop("checked", isSelected);
    });
  };

  var getCleanMessageContent = function (content) {
    return String(content || "")
      .replace(/^\[SENDER:.*?\]\s*/, "")
      .replace(/^\[FORWARDED\]\s*/, "");
  };

  var parseMediaMeta = function (mediaMeta) {
    if (!mediaMeta) return {};
    if (typeof mediaMeta === "object") return mediaMeta;

    try {
      var parsed = JSON.parse(mediaMeta);
      return parsed && typeof parsed === "object" ? parsed : {};
    } catch (e) {
      return {};
    }
  };

  var resolvePreferredMediaUrl = function (message, fallbackUrl) {
    var mediaMeta = parseMediaMeta(message && message.media_meta);

    if (mediaMeta.cloudinary) return mediaMeta.cloudinary;
    if (mediaMeta.minio) return mediaMeta.minio;
    if (mediaMeta.wag) return mediaMeta.wag;
    if (message && message.media_url) return message.media_url;
    return fallbackUrl || "";
  };

  var isForwardedTextMessage = function (msg) {
    if (!msg || msg.type !== "text") {
      return false;
    }

    return /^\[FORWARDED\]\s*/.test(String(msg.content || ""));
  };

  var renderReplyPreviewContent = function (content, type, message) {
    var body = getCleanMessageContent(content || "");
    var replyType = type || "text";
    var preferredUrl = resolvePreferredMediaUrl(message, "");
    var thumbWrapperStyle =
      'style="width: 50px; height: 50px; min-width: 50px; max-width: 50px; max-height: 50px; overflow: hidden; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;"';
    var thumbImageStyle =
      'style="width: 50px; height: 50px; min-width: 50px; max-width: 50px; max-height: 50px; object-fit: cover; display: block;"';

    if (replyType === "image" || body.indexOf("[IMAGE:") !== -1) {
      var imageMatch = body.match(/\[IMAGE:([^\]]+)\]/);
      var imageUrl = preferredUrl || (imageMatch ? imageMatch[1] : "");
      if (isValidMediaUrl(imageUrl)) {
        return (
          '<div class="reply-preview-media" ' +
          thumbWrapperStyle +
          ">" +
          '<img src="' +
          imageUrl +
          '" class="reply-preview-thumb" alt="Image reply" ' +
          thumbImageStyle +
          ' onerror="onChatMediaError(this)" />' +
          "</div>"
        );
      }
      return (
        '<div class="reply-preview-file" ' +
        thumbWrapperStyle +
        '><i class="bi bi-image"></i></div>'
      );
    }

    if (replyType === "video" || body.indexOf("[VIDEO:") !== -1) {
      var videoMatch = body.match(/\[VIDEO:([^\]]+)\]/);
      var videoUrl = preferredUrl || (videoMatch ? videoMatch[1] : "");
      if (isValidMediaUrl(videoUrl)) {
        return (
          '<div class="reply-preview-media position-relative" ' +
          thumbWrapperStyle +
          ">" +
          '<img src="' +
          videoUrl +
          '" class="reply-preview-thumb" alt="Video reply" ' +
          thumbImageStyle +
          ' onerror="onChatMediaError(this)" />' +
          '<span class="reply-preview-badge"><i class="bi bi-play-fill"></i></span>' +
          "</div>"
        );
      }
      return (
        '<div class="reply-preview-file" ' +
        thumbWrapperStyle +
        '><i class="bi bi-camera-video"></i></div>'
      );
    }

    if (replyType === "sticker" || body.indexOf("[STICKER:") !== -1) {
      var stickerMatch = body.match(/\[STICKER:([^\]]+)\]/);
      var stickerUrl = preferredUrl || (stickerMatch ? stickerMatch[1] : "");
      if (isValidMediaUrl(stickerUrl)) {
        return (
          '<div class="reply-preview-media" ' +
          thumbWrapperStyle +
          ">" +
          '<img src="' +
          stickerUrl +
          '" class="reply-preview-thumb" alt="Sticker reply" ' +
          thumbImageStyle +
          ' onerror="onChatMediaError(this)" />' +
          "</div>"
        );
      }
      return (
        '<div class="reply-preview-file" ' +
        thumbWrapperStyle +
        '><i class="bi bi-sticky"></i></div>'
      );
    }

    if (replyType === "document" || body.indexOf("[DOCUMENT:") !== -1) {
      return (
        '<div class="reply-preview-file" ' +
        thumbWrapperStyle +
        '><i class="bi bi-file-earmark-text"></i></div>'
      );
    }

    if (
      replyType === "audio" ||
      body.indexOf("[AUDIO:") !== -1 ||
      body.indexOf("[PTT:") !== -1
    ) {
      return (
        '<div class="reply-preview-file" ' +
        thumbWrapperStyle +
        '><i class="bi bi-mic-fill"></i></div>'
      );
    }

    return '<div class="reply-preview-text">' + formatWhatsAppText(escapeHtml(body)) + "</div>";
  };

  var renderComposerReplyPreview = function (content, type, message) {
    var body = getCleanMessageContent(content || "");
    var replyType = type || "text";
    var preferredUrl = resolvePreferredMediaUrl(message, "");
    var mediaHtml = "";
    var textHtml = "";

    if (replyType === "image" || body.indexOf("[IMAGE:") !== -1) {
      var imageMatch = body.match(/\[IMAGE:([^\]]+)\]/);
      var imageUrl = preferredUrl || (imageMatch ? imageMatch[1] : "");
      if (isValidMediaUrl(imageUrl)) {
        mediaHtml =
          '<img src="' +
          imageUrl +
          '" alt="Reply image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; display: block;" onerror="onChatMediaError(this)" />';
      } else {
        mediaHtml =
          '<div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded" style="width: 50px; height: 50px;"><i class="bi bi-image"></i></div>';
      }
      textHtml = "Foto";
    } else if (replyType === "video" || body.indexOf("[VIDEO:") !== -1) {
      mediaHtml =
        '<div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded position-relative" style="width: 50px; height: 50px;">' +
        '<i class="bi bi-camera-video"></i>' +
        "</div>";
      textHtml = "Video";
    } else if (replyType === "sticker" || body.indexOf("[STICKER:") !== -1) {
      var stickerMatch = body.match(/\[STICKER:([^\]]+)\]/);
      var stickerUrl = preferredUrl || (stickerMatch ? stickerMatch[1] : "");
      if (isValidMediaUrl(stickerUrl)) {
        mediaHtml =
          '<img src="' +
          stickerUrl +
          '" alt="Reply sticker" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; display: block;" onerror="onChatMediaError(this)" />';
      } else {
        mediaHtml =
          '<div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded" style="width: 50px; height: 50px;"><i class="bi bi-sticky"></i></div>';
      }
      textHtml = "Stiker";
    } else if (replyType === "document" || body.indexOf("[DOCUMENT:") !== -1) {
      mediaHtml =
        '<div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded" style="width: 50px; height: 50px;"><i class="bi bi-file-earmark-text"></i></div>';
      var docMatch = body.match(/\[DOCUMENT:(.+):([^:]+)\]/);
      textHtml = docMatch && docMatch[2] ? docMatch[2] : "Dokumen";
    } else if (
      replyType === "audio" ||
      body.indexOf("[AUDIO:") !== -1 ||
      body.indexOf("[PTT:") !== -1
    ) {
      mediaHtml =
        '<div class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded" style="width: 50px; height: 50px;"><i class="bi bi-mic-fill"></i></div>';
      textHtml = body.indexOf("[PTT:") !== -1 ? "Pesan suara" : "Audio";
    } else {
      textHtml = body.substring(0, 80) + (body.length > 80 ? "..." : "");
    }

    return (
      '<div class="d-flex align-items-center overflow-hidden">' +
      (mediaHtml ? '<div class="me-3 flex-shrink-0">' + mediaHtml + "</div>" : "") +
      '<div class="fs-8 text-muted text-truncate">' +
      escapeHtml(textHtml || "-") +
      "</div>" +
      "</div>"
    );
  };

  // Add click listener to unlock audio
  $(document).one("click", unlockAudio);
  $(document).one("keydown", unlockAudio);

  var playNotificationSound = function () {
    if (!audioUnlocked) {
      console.log(
        "⚠️ Audio not yet unlocked. Click anywhere on the page first.",
      );
      return;
    }
    notificationSound.currentTime = 0;
    notificationSound.play().catch(function (error) {
      console.log("Audio play failed:", error);
    });
  };

  var GLOBAL_POLL_INTERVAL = 5000; // 5 seconds
  var GW_SYNC_INTERVAL = 60000; // 60 seconds

  // Filter state
  var currentFilter = "all"; // 'all', 'unread'
  var currentLabelId = "";
  var currentDealStageId = "";
  var currentCustomerType = "individual"; // individual, groupize chat

  // Track previous unread count for sound notification (-1 = not initialized yet, skip first load)
  var previousTotalUnread = -1;

  // Initialize chat
  var init = function () {
    initCustomerList();
    initMessageForm();
    initAttachments();
    initDragDrop();
    initUploadPreviewEvents();
    initActions();
    initTemplates();
    initDealModal();
    initMessageActions();

    // Load initial customers list
    loadCustomers("");

    // Polling for contact list updates (for webhook data display)
    startGlobalPolling();

    // DISABLED: Gateway auto-sync removed - use manual sync button instead
    // startGatewaySync();

    // Auto-select if customer ID provided
    if (typeof selectedCustomerId !== "undefined" && selectedCustomerId) {
      selectCustomer(selectedCustomerId);
    }
  };

  // Customer list click handler
  var initCustomerList = function () {
    $(document).on("click", "#btn-save-message-edit", function () {
      var id = $("#modal-edit-message").data("id");
      var content = $("#edit-message-input").val().trim();

      if (!id || !content) return;

      var btn = $(this);
      var originalHtml = btn.html();
      btn
        .prop("disabled", true)
        .html('<span class="spinner-border spinner-border-sm"></span>');

      $.post(
        appUrl + "chat/edit_message",
        { id: id, content: content },
        function (response) {
          if (response.success) {
            toastr.success("Pesan diperbarui");
            $("#modal-edit-message").modal("hide");
          } else {
            toastr.error(response.error || "Gagal mengubah pesan");
          }
          btn.prop("disabled", false).html(originalHtml);
        },
      );
    });

    // Handle cancel reply
    $(document).on("click", "#btn-cancel-reply", function () {
      $("#attachment-preview").empty().addClass("d-none");
      currentReplyMessageId = null; // Clear state
    });

    // Handle Enter key in edit input
    $(document).on("keypress", "#edit-message-input", function (e) {
      if (e.which === 13) {
        $("#btn-save-message-edit").click();
      }
    });

    $(document).on("click", ".customer-item", function () {
      var id = $(this).data("id");
      var type = $(this).data("type") || "individual";
      selectCustomer(id, type);
    });

    // Search customers
    $("#search-customers").on(
      "input",
      debounce(function () {
        var search = $(this).val();
        customerPage = 1; // Reset page on search
        isMoreCustomers = true;
        loadCustomers(search);
      }, 300),
    );

    // Infinite scroll for customer list
    $("#chat_contacts_body").on("scroll", function () {
      var container = $(this);
      var scrollTop = container.scrollTop();
      var scrollHeight = container[0].scrollHeight;
      var containerHeight = container.height();

      // Load more when 100px from bottom
      if (scrollTop + containerHeight >= scrollHeight - 100) {
        loadMoreCustomers();
      }
    });

    // Filter buttons (All, Unread)
    $(document).on("click", ".chat-filter", function () {
      $(".chat-filter")
        .removeClass("active btn-light-primary")
        .addClass("btn-light");
      $(this).removeClass("btn-light").addClass("active btn-light-primary");

      var filter = $(this).data("filter");
      currentFilter = filter;

      // Reset dropdown buttons text
      $("#filter-labels-btn")
        .text("Labels")
        .removeClass("btn-light-primary")
        .addClass("btn-light");
      currentLabelId = "";
      currentDealStageId = "";

      customerPage = 1;
      isMoreCustomers = true;
      loadCustomers($("#search-customers").val());
    });

    // Label filter dropdown
    $(document).on("click", ".label-filter-item", function (e) {
      e.preventDefault();
      var labelId = $(this).data("label");
      var labelName = $(this).text().trim();

      currentLabelId = labelId;
      currentFilter = "all";
      currentDealStageId = "";

      // Update button states
      $(".chat-filter")
        .removeClass("active btn-light-primary")
        .addClass("btn-light");

      if (labelId) {
        $("#filter-labels-btn")
          .text(labelName)
          .removeClass("btn-light")
          .addClass("btn-light-primary");
      } else {
        $("#filter-labels-btn")
          .text("Labels")
          .removeClass("btn-light-primary")
          .addClass("btn-light");
        $('.chat-filter[data-filter="all"]')
          .removeClass("btn-light")
          .addClass("active btn-light-primary");
      }

      customerPage = 1;
      isMoreCustomers = true;
      loadCustomers($("#search-customers").val());
    });

    // Deal stage filter dropdown
    $(document).on("click", ".deal-filter-item", function (e) {
      e.preventDefault();
      var stageId = $(this).data("stage");
      var stageName = $(this).text().trim();

      currentDealStageId = stageId;
      currentFilter = "all";
      currentLabelId = "";

      // Update button states
      $(".chat-filter")
        .removeClass("active btn-light-primary")
        .addClass("btn-light");
      $("#filter-labels-btn")
        .text("Labels")
        .removeClass("btn-light-primary")
        .addClass("btn-light");

      if (stageId) {
        $("#filter-deals-btn")
          .text(stageName)
          .removeClass("btn-light")
          .addClass("btn-light-primary");
      } else {
        $("#filter-deals-btn")
          .text("Deals")
          .removeClass("btn-light-primary")
          .addClass("btn-light");
        $('.chat-filter[data-filter="all"]')
          .removeClass("btn-light")
          .addClass("active btn-light-primary");
      }

      customerPage = 1;
      isMoreCustomers = true;
      loadCustomers($("#search-customers").val());
    });

    // New Chat Search
    $("#search-new-chat").on(
      "input",
      debounce(function () {
        var search = $(this).val();
        loadNewChatCustomers(search);
      }, 300),
    );

    // New Chat Select
    $(document).on("click", ".new-chat-item", function () {
      var id = $(this).data("id");
      var type = $(this).data("type") || "individual";
      // Close modal
      $("#modal_new_chat").modal("hide");
      // Select customer
      selectCustomer(id, type);
    });

    // Ghost Chats Modal - Load ghost chats when modal opens
    $("#modal_ghost_chats").on("show.bs.modal", function () {
      loadGhostChats();
    });

    // Ghost Assign - Search target customers
    $("#search-assign-target").on(
      "input",
      debounce(function () {
        var search = $(this).val();
        searchAssignTargets(search);
      }, 300),
    );

    // Ghost Assign - Select target
    $(document).on("click", ".assign-target-item", function () {
      var ghostId = $("#assign-ghost-id").val();
      var targetId = $(this).data("id");
      var targetName = $(this).data("name");

      if (confirm('Gabungkan Ghost Chat ke "' + targetName + '"?')) {
        mergeGhost(ghostId, targetId);
      }
    });

    // Bulk Mark All as Read
    $(document).on("click", "#btn-mark-all-read", function () {
      if (!confirm("Tandai semua pesan sebagai sudah dibaca?")) return;

      var btn = $(this);
      var originalHtml = btn.html();
      btn
        .html('<span class="spinner-border spinner-border-sm"></span>')
        .prop("disabled", true);

      $.post(appUrl + "chat/bulk_mark_read", function (response) {
        if (response.success) {
          toastr.success(response.message);
          // Clear all unread UI elements
          $(".badge-circle").remove();
          $(".fw-bold").removeClass("fw-bold").addClass("fw-semibold");
          $(".text-gray-900")
            .removeClass("text-gray-900")
            .addClass("text-gray-800");
          $(".text-gray-700")
            .removeClass("text-gray-700")
            .addClass("text-muted");
          $(".text-success.fw-semibold")
            .removeClass("text-success fw-semibold")
            .addClass("text-muted");
        } else {
          toastr.info(
            response.message || "Tidak ada pesan baru untuk ditandai",
          );
        }
        btn.html(originalHtml).prop("disabled", false);
      }).fail(function () {
        toastr.error("Terjadi kesalahan sistem");
        btn.html(originalHtml).prop("disabled", false);
      });
    });

    // Context Menu (Right Click) logic
    var contextTarget = null;
    $(document).on("contextmenu", ".customer-item", function (e) {
      e.preventDefault();
      contextTarget = $(this);
      var id = contextTarget.data("id");
      var type = contextTarget.data("type") || "individual";

      // Highlight item
      $(".customer-item").removeClass("context-open");
      contextTarget.addClass("context-open");

      var menu = $("#chat-context-menu");
      var labelAction = $("#menu-assign-label");
      var renameActionLabel = $("#menu-rename-chat span");

      if (type === "group") {
        labelAction.addClass("d-none");
        renameActionLabel.text("Ubah Nama Grup");
      } else {
        labelAction.removeClass("d-none");
        renameActionLabel.text("Ubah Nama Kontak");
      }

      menu.css({
        display: "block",
        left: e.pageX,
        top: e.pageY,
      });

      // Store data for menu actions
      menu.data("id", id);
      menu.data("type", type);
    });

    // Hide context menu on click elsewhere
    $(document).on("click", function () {
      $("#chat-context-menu").hide();
      $(".customer-item").removeClass("context-open");
    });

    // Context Menu Action: Mark as Read
    $(document).on("click", "#menu-mark-as-read", function (e) {
      e.preventDefault();
      var menu = $("#chat-context-menu");
      var id = menu.data("id");
      var type = menu.data("type");

      if (!id) return;

      $.post(
        appUrl + "chat/mark_chat_as_read",
        { id: id, type: type },
        function (response) {
          if (response.success) {
            // Update UI for this specific item
            if (contextTarget) {
              contextTarget.find(".badge-circle").remove();
              contextTarget
                .find(".fw-bold")
                .removeClass("fw-bold")
                .addClass("fw-semibold");
              contextTarget
                .find(".text-gray-900")
                .removeClass("text-gray-900")
                .addClass("text-gray-800");
              contextTarget
                .find(".text-gray-700")
                .removeClass("text-gray-700")
                .addClass("text-muted");
              contextTarget
                .find(".text-success.fw-semibold")
                .removeClass("text-success fw-semibold")
                .addClass("text-muted");
            }
            toastr.success("Berhasil ditandai sudah dibaca");
          } else {
            toastr.error(response.message || "Gagal menandai chat");
          }
          menu.hide();
        },
      );
    });

    // Context Menu Action: Mark as Unread
    $(document).on("click", "#menu-mark-as-unread", function (e) {
      e.preventDefault();
      var menu = $("#chat-context-menu");
      var id = menu.data("id");
      var type = menu.data("type");

      if (!id) return;

      $.post(
        appUrl + "chat/mark_chat_as_unread",
        { id: id, type: type },
        function (response) {
          if (response.success) {
            // Update UI for this specific item
            if (contextTarget) {
              // Add unread badge if not exists
              if (contextTarget.find(".badge-circle").length === 0) {
                var badgeContainer = contextTarget
                  .find(".d-flex.justify-content-between.align-items-center")
                  .last();
                var badgeHtml =
                  '<div class="ms-2 d-flex flex-column align-items-end"><span class="badge badge-circle badge-success w-18px h-18px fs-9">1</span></div>';
                badgeContainer.append(badgeHtml);
              }
              contextTarget
                .find(".fw-semibold")
                .removeClass("fw-semibold")
                .addClass("fw-bold");
              contextTarget
                .find(".text-gray-800")
                .removeClass("text-gray-800")
                .addClass("text-gray-900");
              contextTarget
                .find(".text-muted")
                .removeClass("text-muted")
                .addClass("text-gray-700");
              // Also for groups if status text is present
              contextTarget
                .find(".text-success.fw-semibold")
                .removeClass("text-muted");
            }
            toastr.success("Berhasil ditandai belum dibaca");
          } else {
            toastr.error(response.message || "Gagal menandai chat");
          }
          menu.hide();
        },
      );
    });

    $(document).on("click", "#menu-rename-chat", function (e) {
      e.preventDefault();
      var menu = $("#chat-context-menu");
      var id = menu.data("id");
      var type = menu.data("type") || "individual";

      if (!id || !contextTarget) return;

      var currentName = contextTarget.find(".fw-bold, .fw-semibold").first().text().trim();

      $("#rename-chat-id").val(id);
      $("#rename-chat-type").val(type);
      $("#rename-chat-title").text(type === "group" ? "Ubah Nama Grup" : "Ubah Nama Kontak");
      $("#rename-chat-input").val(currentName);
      $("#modal-rename-chat").modal("show");
      menu.hide();

      setTimeout(function () {
        $("#rename-chat-input").trigger("focus").trigger("select");
      }, 200);
    });

    // ESC Key Handling
    $(document).on("keydown", function (e) {
      if (e.key === "Escape") {
        // If modal is open, let Bootstrap handle it
        if ($(".modal.show").length > 0) return;
        // If template preview is open, close it
        if (!$("#template-sticky-container").hasClass("d-none")) {
          $("#template-sticky-container").addClass("d-none");
        }

        // If context menu is open, close it
        $("#chat-context-menu").hide();
        $("#message-context-menu").hide();

        // If sidebar is open, close it
        if (!$("#chat-right-sidebar").hasClass("d-none")) {
          $("#chat-right-sidebar").addClass("d-none");
          return;
        }

        // If chat is active, close it
        if (!$("#chat-container").hasClass("d-none")) {
          closeActiveChat();
        }
      }
    });
  };

  var closeActiveChat = function () {
    currentCustomerId = null;
    currentCustomerType = null;
    $("#chat-container").addClass("d-none").removeClass("d-flex");
    $("#no-chat-selected").removeClass("d-none").addClass("d-flex");
    $(".customer-item").removeClass("active");
    $("#current-customer-id").val("");
    $("#messages-list").empty();
    $("#chat-right-sidebar").addClass("d-none");
    clearReplyContext();
  };

  // Select customer and load conversation
  var selectCustomer = function (customerId, type = "individual") {
    // Close detail sidebar on switch
    $("#chat-right-sidebar").addClass("d-none");
    clearReplyContext();

    currentCustomerId = customerId;
    currentCustomerType = type;
    $("#current-customer-id").val(customerId);

    // Highlight selected customer and clear unread badge UI immediately
    var $item = $(
      '.customer-item[data-id="' + customerId + '"][data-type="' + type + '"]',
    );
    $(".customer-item").removeClass("active");
    $item.addClass("active");

    // Remove unread badges and bold styling on click
    $item.find(".badge-circle").remove();
    $item.find(".fw-bold").removeClass("fw-bold").addClass("fw-semibold");
    $item
      .find(".text-gray-900")
      .removeClass("text-gray-900")
      .addClass("text-gray-800");
    $item
      .find(".text-gray-700")
      .removeClass("text-gray-700")
      .addClass("text-muted");
    $item
      .find(".text-success.fw-semibold")
      .removeClass("text-success fw-semibold")
      .addClass("text-muted");

    // Show/Hide Right Sidebar specific items (Labels are only for individual)
    if (type === "group") {
      $(".customer-info-section").addClass("d-none");
      $("#chat-header-actions").addClass("d-none"); // Hide Status/Assign/Edit/Detail for Groups
      $("#chat-header-status").text("WhatsApp Group");
      $("#chat-header-avatar-initials")
        .addClass("bg-light-success text-success")
        .removeClass("bg-light-primary text-primary");
    } else {
      $(".customer-info-section").removeClass("d-none");
      $("#chat-header-actions").removeClass("d-none"); // Show for Individuals
      $("#chat-header-status").text("Online"); // Or actual status if available
      $("#chat-header-avatar-initials")
        .addClass("bg-light-primary text-primary")
        .removeClass("bg-light-success text-success");
    }

    // Show loading
    $("#messages-list").html(
      '<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>',
    );

    // Toggle visibility
    $("#no-chat-selected").addClass("d-none");
    $("#no-chat-selected").removeClass("d-flex");

    $("#chat-container").removeClass("d-none");
    $("#chat-container").addClass("d-flex");

    // Load conversation
    $.ajax({
      url: appUrl + "chat/conversation",
      type: "POST",
      data: {
        customer_id: customerId,
        type: type,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Update server time offset
          if (response.server_time) {
            serverTimeOffset =
              parseInt(response.server_time) -
              Math.floor(new Date().getTime() / 1000);
            console.log("⏰ Time Sync:", serverTimeOffset, "sec offset");
          }

          // Use chat_info from backend if available, otherwise fallback
          var chatInfo = response.chat_info;
          if (!chatInfo) {
            // Fallback logic
            var item = $(
              '.customer-item[data-id="' +
                customerId +
                '"][data-type="' +
                type +
                '"]',
            );
            var name = item.find(".fw-bold, .fw-semibold").first().text();
            chatInfo = { id: customerId, name: name, wa_number: "" };
          }

          displayConversation(chatInfo, response.data, false);
          startPolling();
        }
      },
    });
  };

  // Display conversation
  var displayConversation = function (customer, messages, has_more) {
    // Update header
    $("#chat-customer-name").text(getDisplayName(customer, "Tanpa Nama"));
    $("#chat-customer-phone").text(formatPhone(customer.wa_number));

    // Update header avatar
    var type = customer.type || "individual";
    var name = getDisplayName(customer, customer.wa_number || "Tanpa Nama");

    if (type === "group") {
      $("#chat-header-initials")
        .html('<i class="bi bi-people-fill text-white"></i>')
        .css("background-color", "#6f42c1")
        .removeClass("d-none");
      $("#chat-header-avatar-img").addClass("d-none");
    } else if (customer.avatar_url) {
      $("#chat-header-initials").addClass("d-none");
      $("#chat-header-avatar-img")
        .attr("src", customer.avatar_url)
        .removeClass("d-none");
    } else {
      $("#chat-header-initials")
        .html(getInitials(name))
        .css("background-color", "#00a884")
        .removeClass("d-none");
      $("#chat-header-avatar-img").addClass("d-none");
    }

    // Update labels
    var labelsHtml = "";
    if (customer.labels && customer.labels.length > 0) {
      customer.labels.forEach(function (label) {
        labelsHtml +=
          '<span class="badge badge-sm me-1" style="background-color: ' +
          label.color +
          "20; color: " +
          label.color +
          '">' +
          label.name +
          "</span>";
      });
    }
    $("#chat-customer-labels").html(labelsHtml);

    // Update detail button
    $("#btn-customer-detail").data("id", customer.id);

    // Render messages
    renderMessages(messages);

    // Add Load Previous button if has_more
    if (has_more) {
      $("#messages-list").prepend(
        '<div id="btn-load-previous-wrapper" class="text-center py-2"><button id="btn-load-previous" class="btn btn-sm btn-light-primary">Muat Chat Sebelumnya</button></div>',
      );
    }

    scrollToBottom();

    // Focus on input
    $("#message-input").focus();
  };

  // Render messages
  var renderMessages = function (messages) {
    var html = "";
    var lastDate = "";

    // Reset tracking variables
    lastMessageId = 0;
    lastMessageDate = null;

    messages.forEach(function (msg) {
      var msgDate = msg.created_at.split(" ")[0];

      // Date separator
      if (msgDate !== lastDate) {
        html +=
          '<div class="date-separator"><span>' +
          formatDate(msgDate) +
          "</span></div>";
        lastDate = msgDate;
      }

      html += renderMessage(msg);
      lastMessageId = Math.max(lastMessageId, msg.id);

      // Track the newest message date for polling
      if (!lastMessageDate || msg.created_at > lastMessageDate) {
        lastMessageDate = msg.created_at;
      }
    });

    if (html === "") {
      html =
        '<div class="text-center text-muted py-10 messages-empty-state">Belum ada pesan</div>';
    }

    $("#messages-list").html(html);
  };

  // Render single message
  var renderMessage = function (msg) {
    var isOut = msg.direction === "out";
    var wrapperClass = isOut ? "message-out" : "message-in";

    // Handle Deleted Status
    if (msg.is_deleted == 1) {
      wrapperClass += " message-deleted";
    }

    // Raw content for parsing
    var rawContent = msg.content || "";
    var isForwardedText = isForwardedTextMessage(msg);
    var senderLabel = isOut
      ? "Anda"
      : $("#chat-customer-name").text() || "Customer";

    if (isForwardedText) {
      rawContent = rawContent.replace(/^\[FORWARDED\]\s*/, "");
    }

    // Extract [SENDER:Name] for Group Messages (before status processing)
    var groupSenderName = null;
    if (rawContent && rawContent.indexOf("[SENDER:") !== -1) {
      var match = rawContent.match(/\[SENDER:(.*?)\]/);
      if (match && match[1]) {
        groupSenderName = match[1];
        rawContent = rawContent.replace(/\[SENDER:.*?\]\s*/, "");
      }
    }

    // Process Content based on Status (Deleted/Edited)
    var displayContent = "";
    var toggleButtonHtml = "";
    var historyHtmlList = "";

    if (msg.is_deleted == 1) {
      displayContent =
        '<span class="message-deleted-text"><i class="bi bi-trash3 opacity-70 me-1"></i> ***Pesan ini telah dihapus***</span>';

      // Toggle button for deleted history
      if (
        rawContent &&
        rawContent.indexOf("***Pesan ini telah dihapus") === -1
      ) {
        toggleButtonHtml =
          '<div class="message-toggle-container" onclick="toggleMessageHistory(' +
          msg.id +
          ', event)">' +
          '<button class="btn-history-toggle btn-toggle-deleted"><i class="bi bi-eye"></i></button>' +
          "</div>";

        historyHtmlList =
          '<div id="history-' +
          msg.id +
          '" class="message-history-list d-none">' +
          '<div class="history-bubble history-bubble-deleted">' +
          parseMessageContent(rawContent, msg.type, msg) +
          '<span class="history-time">' +
          msg.created_at.split(" ")[1].substring(0, 5) +
          "</span>" +
          "</div>" +
          "</div>";
      }
    } else {
      // Normal message or Edited
      displayContent = parseMessageContent(rawContent, msg.type, msg);

      if (msg.is_edited == 1) {
        // Add "Edited" label to the content (will be styled to bottom right)
        var editTime = (msg.updated_at || msg.created_at)
          .split(" ")[1]
          .substring(0, 5);
        displayContent +=
          '<span class="message-edited-label">Edited ' + editTime + "</span>";

        if (msg.revisions && msg.revisions.length > 0) {
          toggleButtonHtml =
            '<div class="message-toggle-container" onclick="toggleMessageHistory(' +
            msg.id +
            ', event)">' +
            '<button class="btn-history-toggle btn-toggle-edited"><i class="bi bi-eye"></i></button>' +
            "</div>";

          historyHtmlList =
            '<div id="history-' +
            msg.id +
            '" class="message-history-list d-none">';
          msg.revisions.forEach(function (rev) {
            historyHtmlList +=
              '<div class="history-bubble history-bubble-revisions">' +
              parseMessageContent(rev.old_content, msg.type, msg) +
              '<span class="history-time">' +
              rev.created_at.split(" ")[1].substring(0, 5) +
              "</span>" +
              "</div>";
          });
          historyHtmlList += "</div>";
        }
      }
    }

    // Sender label Html
    var senderLabelHtml = "";
    var forwardedLabelHtml = "";
    if (isOut) {
      senderLabel = "Dari Device lain";
      if (
        rawContent &&
        (msg.is_auto_reply || rawContent.indexOf("[AUTO-REPLY]") !== -1)
      ) {
        senderLabel = "Auto-reply";
      } else if (msg.user_id && msg.user_id > 0) {
        senderLabel = msg.user_name || "Anda";
      }
      senderLabelHtml =
        '<div class="message-sender-label text-muted fs-8 mb-1">' +
        senderLabel +
        "</div>";
    } else if (groupSenderName) {
      senderLabelHtml =
        '<div class="message-sender-label text-primary fw-bold fs-8 mb-1">' +
        groupSenderName +
        "</div>";
    }

    if (isForwardedText) {
      forwardedLabelHtml =
        '<div class="message-sender-label text-muted fs-8 mb-1 d-flex align-items-center gap-1">' +
        '<i class="bi bi-forward-fill"></i>' +
        "<span>Diteruskan</span>" +
        "</div>";
    }

    // Status checkmark
    var statusHtml = "";
    if (isOut) {
      var iconClass =
        msg.status === "read"
          ? "bi bi-check2-all text-primary"
          : msg.status === "delivered"
            ? "bi bi-check2-all text-gray-500"
            : "bi bi-check2 text-gray-500";
      statusHtml = '<span class="message-status ' + iconClass + '"></span>';
    }

    var time = msg.created_at.split(" ")[1].substring(0, 5);

    var replyHtml = "";
    var hasReply =
      msg.reply_message || (msg.reply_content && msg.reply_content !== "");

    if (hasReply) {
      var rawSender =
        msg.reply_sender_name ||
        (msg.reply_message ? msg.reply_message.sender_name : "") ||
        "Pesan";
      var isSelf =
        rawSender === "Anda" ||
        rawSender === "You" ||
        (msg.reply_message && msg.reply_message.user_id > 0);
      var replySender = isSelf ? "You" : rawSender;
      var replyColorClass = isSelf ? "reply-self" : "reply-customer";

      var replyContent =
        msg.reply_content ||
        (msg.reply_message ? msg.reply_message.content : "") ||
        "";
      var shortReply =
        replyContent.substring(0, 100) +
        (replyContent.length > 100 ? "..." : "");

      // Set data-message-id-wa-reply for potential scrolling
      var replyMsgIdWa = msg.reply_message_id || "";

      replyHtml =
        '<div class="message-reply-quote ' +
        replyColorClass +
        '" onclick="scrollToMessage(\'' +
        replyMsgIdWa +
        "')\">" +
        '<div class="reply-sender">' +
        escapeHtml(replySender) +
        "</div>" +
        '<div class="reply-content text-muted">' +
        renderReplyPreviewContent(
          shortReply,
          msg.reply_message ? msg.reply_message.type : "text",
          msg.reply_message || null,
        ) +
        "</div>" +
        "</div>";
    }

    // Big Emoji Detection
    var isBigEmoji = false;
    var isMediaMessage =
      [
        "image",
        "video",
        "audio",
        "document",
        "location",
        "contact",
        "sticker",
      ].indexOf(msg.type) !== -1 ||
      rawContent.indexOf("[IMAGE:") !== -1 ||
      rawContent.indexOf("[VIDEO:") !== -1 ||
      rawContent.indexOf("[AUDIO:") !== -1 ||
      rawContent.indexOf("[PTT:") !== -1 ||
      rawContent.indexOf("[DOCUMENT:") !== -1 ||
      rawContent.indexOf("[LOCATION:") !== -1 ||
      rawContent.indexOf("[CONTACT:") !== -1 ||
      rawContent.indexOf("[STICKER:") !== -1;

    if (
      msg.type === "text" &&
      msg.is_deleted != 1 &&
      !msg.is_edited &&
      rawContent
    ) {
      try {
        if (
          rawContent.length < 20 &&
          /^(?:[\uD800-\uDBFF][\uDC00-\uDFFF]|[\u2700-\u27bf]|[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF]|\s)+$/.test(
            rawContent,
          )
        ) {
          isBigEmoji = true;
        }
      } catch (e) {}
    }

    var messageTextClass = isBigEmoji
      ? "message-text big-emoji fa-2x"
      : "message-text";

    if (isMediaMessage) {
      messageTextClass += " message-text-media";
    }

    var bubbleInlineStyle = "";
    var messageBodyHtml = "";

    if (isMediaMessage) {
      bubbleInlineStyle =
        ' style="display:inline-block; width:auto; max-width:262px; padding:6px 6px 4px 6px;"';
      messageBodyHtml =
        '<div class="' +
        messageTextClass +
        '" style="display:inline-flex; flex-direction:column; align-items:flex-start; width:auto; max-width:250px;">' +
        '<div class="message-content-text" style="display:block; width:auto; max-width:250px;">' +
        displayContent +
        "</div>" +
        '<div class="message-time" style="display:flex; justify-content:flex-end; align-items:center; align-self:flex-end; margin:4px 0 0 0; float:none; width:auto;">' +
        time +
        statusHtml +
        "</div>" +
        "</div>";
    } else {
      messageBodyHtml =
        '<div class="' +
        messageTextClass +
        '">' +
        '<span class="message-content-text">' +
        displayContent +
        "</span>" +
        '<span class="message-time">' +
        time +
        statusHtml +
        "</span>" +
        "</div>";
    }

    // Build final HTML with side-by-side toggle button
    var bubbleClass = "message-bubble" + (hasReply ? " has-reply" : "");
    var bubbleSideHtml = isOut
      ? toggleButtonHtml +
        '<div class="' +
        bubbleClass +
        '"' +
        bubbleInlineStyle +
        ' data-raw-content="' +
        escapeHtml(rawContent) +
        '" data-type="' +
        msg.type +
        '">'
      : '<div class="' +
        bubbleClass +
        '" data-raw-content="' +
        escapeHtml(rawContent) +
        '" data-type="' +
        msg.type +
        '"' +
        bubbleInlineStyle +
        ">";
    var bubbleEndHtml = isOut ? "</div>" : "</div>" + toggleButtonHtml;

    return (
      '<div class="message-history-wrapper ' +
      wrapperClass +
      '" data-message-id="' +
      msg.id +
      '" data-message-id-wa="' +
      (msg.message_id || "") +
      '" data-timestamp="' +
      (msg.created_at_ts || 0) +
      '" data-user-id="' +
      (msg.user_id || 0) +
      '" data-created-at="' +
      msg.created_at +
      '" data-updated-at="' +
      (msg.updated_at || msg.created_at) +
      '">' +
      '<div class="message-wrapper ' +
      wrapperClass +
      '">' +
      bubbleSideHtml +
      senderLabelHtml +
      forwardedLabelHtml +
      replyHtml +
      messageBodyHtml +
      bubbleEndHtml +
      "</div>" +
      historyHtmlList +
      "</div>"
    );
  };

  // Toggle message history visibility
  window.toggleMessageHistory = function (messageId, event) {
    if (event) event.stopPropagation();
    var historyList = $("#history-" + messageId);

    if (historyList.hasClass("d-none")) {
      historyList.removeClass("d-none");
    } else {
      historyList.addClass("d-none");
    }
  };

  // ... (parseMessageContent unchanged) ...

  // Append new message to chat
  var appendMessage = function (msg) {
    // Handle Update (if message already exists on screen)
    var existingMsg = $('[data-message-id="' + msg.id + '"]');
    if (existingMsg.length > 0) {
      // Re-render and Replace existing
      var updatedHtml = renderMessage(msg);
      existingMsg.replaceWith(updatedHtml);
      return;
    }

    var html = renderMessage(msg);

    // Remove only the empty-state placeholder, not legitimate muted metadata.
    $("#messages-list .messages-empty-state").remove();

    $("#messages-list").append(html);
    lastMessageId = msg.id;
    scrollToBottom();
  };

  // Check if media URL is valid/viewable in app
  var isValidMediaUrl = function (url) {
    if (!url) return false;

    // If URL contains static/media (local gateway cache), it's valid
    if (
      url.indexOf("/static/media") !== -1 ||
      url.indexOf("static/media") !== -1
    ) {
      return true;
    }

    // If URL is local/relative or from our app, it's valid
    if (url.startsWith("/") || url.indexOf(appUrl) !== -1) {
      return true;
    }

    // If it's a raw WhatsApp CDN URL (mmg.whatsapp.net, etc.) without static/media, reject it
    // These require auth and won't load in browser
    if (url.indexOf(".whatsapp.net") !== -1) {
      return false;
    }

    // For other URLs (could be external images), allow
    return true;
  };

  // Global media error handler
  window.onChatMediaError = function (el) {
    var fallbackHtml =
      '<div class="p-2 bg-light-warning rounded text-warning fs-7 d-flex align-items-center"><i class="bi bi-phone me-2 fs-5"></i>Media tidak dapat dilihat di aplikasi. Silakan cek di HP.</div>';
    // Check if wrapped in specific containers?
    // For video, it's inside div.position-relative.
    if (el.tagName === "VIDEO") {
      $(el).closest(".position-relative").replaceWith(fallbackHtml);
    } else {
      $(el).replaceWith(fallbackHtml);
    }
  };

  // Show loading overlay on chat area
  var showChatLoading = function (message) {
    message = message || "Memproses...";
    var overlayHtml =
      '<div id="chat-loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); z-index: 1000;">' +
      '<div class="text-center">' +
      '<span class="spinner-border spinner-border-sm text-primary me-2"></span>' +
      '<span class="text-primary fw-bold">' +
      message +
      "</span>" +
      "</div>" +
      "</div>";

    // Ensure #chat-container has position-relative
    $("#chat-container").css("position", "relative");
    $("#chat-loading-overlay").remove(); // Remove any existing
    $("#chat-container").append(overlayHtml);
  };

  // Hide loading overlay
  var hideChatLoading = function () {
    $("#chat-loading-overlay").remove();
  };

  // Parse message content (handle images, documents)
  var parseMessageContent = function (body, type, message) {
    if (!body) return "";

    var senderHtml = "";
    var senderMatch = body.match(/\[SENDER:([^\]]+)\]/);
    if (senderMatch) {
      senderHtml =
        '<div class="message-sender">' + escapeHtml(senderMatch[1]) + "</div>";
      body = body.replace(senderMatch[0], "").trim();
    }

    var fallbackHtml =
      '<div class="p-2 bg-light-warning rounded text-warning fs-7 d-flex align-items-center"><i class="bi bi-phone me-2 fs-5"></i>Media tidak dapat dilihat di aplikasi. Silakan cek di HP.</div>';

    // Handle Location
    if (type === "location" || body.indexOf("[LOCATION:") !== -1) {
      var match = body.match(/\[LOCATION:([-.\d]+),([-.\d]+)\]/);
      if (match) {
        var lat = match[1];
        var lng = match[2];
        var mapUrl = "https://www.google.com/maps?q=" + lat + "," + lng;
        return (
          senderHtml +
          '<div class="message-location">' +
          '<div class="d-flex align-items-center mb-2">' +
          '<i class="bi bi-geo-alt-fill text-danger fs-2 me-2"></i>' +
          '<span class="fw-bold">Lokasi Terkirim</span>' +
          "</div>" +
          '<a href="' +
          mapUrl +
          '" target="_blank" class="btn btn-sm btn-primary w-100">Lihat di Google Maps</a>' +
          "</div>"
        );
      }
    }

    // Handle Contact (Backward compatible with : and |)
    if (body.indexOf("[CONTACT") !== -1) {
      var contactRegex = /\[CONTACT[:|]([^:|]*)[:|]([^:|]*)[:|]([^\]]*)\]/g;
      var contactHtml = "";
      var match;

      while ((match = contactRegex.exec(body)) !== null) {
        var name = match[1].trim();
        var waid = match[2].trim();
        var phone = match[3].trim();
        var contactId = waid || phone.replace(/\D/g, "");

        contactHtml +=
          '<div class="message-contact mb-2">' +
          '<div class="d-flex align-items-center mb-3">' +
          '<div class="symbol symbol-40px me-3">' +
          '<div class="symbol-label bg-light-primary text-primary"><i class="bi bi-person-fill fs-2"></i></div>' +
          "</div>" +
          '<div class="d-flex flex-column">' +
          '<div class="fw-bold fs-6 text-gray-800">' +
          name +
          "</div>" +
          '<div class="text-muted fs-7">' +
          (phone || waid) +
          "</div>" +
          "</div>" +
          "</div>" +
          '<div class="d-flex gap-2">' +
          "<button onclick=\"startChatFromContact('" +
          contactId +
          '\')" class="btn btn-sm btn-light-success flex-grow-1"><i class="bi bi-chat-dots me-1"></i>Chat</button>' +
          "<button onclick=\"saveContact('" +
          encodeURIComponent(name) +
          "', '" +
          contactId +
          '\')" class="btn btn-sm btn-light-primary flex-grow-1"><i class="bi bi-person-plus me-1"></i>Simpan</button>' +
          "</div>" +
          "</div>";
      }

      if (contactHtml) return senderHtml + contactHtml;
    }

    // Handle Image
    if (type === "image" || body.indexOf("[IMAGE:") !== -1) {
      var match = body.match(/\[IMAGE:([^\]]+)\]/);
      if (match) {
        var url = resolvePreferredMediaUrl(message, match[1]);
        if (!isValidMediaUrl(url)) return senderHtml + fallbackHtml;

        var caption = body.replace(match[0], "").trim();
        return (
          senderHtml +
          '<div style="display:inline-flex; flex-direction:column; align-items:flex-start; width:250px; max-width:250px;">' +
          '<div style="width:250px; height:350px; overflow:hidden; border-radius:6px;">' +
          '<img src="' +
          url +
          '" class="message-image" style="display:block; width:250px; height:350px; min-width:250px; min-height:350px; max-width:none; max-height:none; object-fit:cover; object-position:center; border-radius:0; cursor:pointer; margin:0;" onerror="onChatMediaError(this)" onclick="window.open(\'' +
          url +
          "')\">" +
          "</div>" +
          (caption
            ? '<div class="message-media-caption" style="display:block; width:250px; max-width:250px; white-space:pre-wrap; overflow-wrap:anywhere; margin-top:6px;">' +
              escapeHtml(caption) +
              "</div>"
            : "") +
          "</div>"
        );
      }
    }

    // Handle Video
    if (type === "video" || body.indexOf("[VIDEO:") !== -1) {
      var match = body.match(/\[VIDEO:([^\]]+)\]/);
      if (match) {
        var url = resolvePreferredMediaUrl(message, match[1]);
        if (!isValidMediaUrl(url)) return senderHtml + fallbackHtml;

        var caption = body.replace(match[0], "").trim();
        return (
          senderHtml +
          '<div class="position-relative" style="max-width:330px">' +
          '<video src="' +
          url +
          '" class="w-100 rounded" controls style="max-height:330px" onerror="onChatMediaError(this)"></video>' +
          "</div>" +
          (caption ? '<div class="mt-1">' + escapeHtml(caption) + "</div>" : "")
        );
      }
    }

    // Handle Audio / Voice Note (PTT)
    if (
      type === "audio" ||
      body.indexOf("[AUDIO:") !== -1 ||
      body.indexOf("[PTT:") !== -1
    ) {
      var match = body.match(/\[(AUDIO|PTT):([^\]]+)\]/);
      if (match) {
        var isPtt = match[1] === "PTT";
        var url = resolvePreferredMediaUrl(message, match[2]);
        if (!isValidMediaUrl(url)) return senderHtml + fallbackHtml;

        return (
          senderHtml +
          '<div class="message-audio ' +
          (isPtt ? "voice-note" : "") +
          '">' +
          (isPtt
            ? '<div class="d-flex align-items-center mb-1 text-primary"><i class="bi bi-mic-fill me-2"></i><span class="fs-7 fw-bold">Pesan Suara</span></div>'
            : "") +
          '<audio controls class="w-100" style="min-width: 250px;">' +
          '<source src="' +
          url +
          '" type="audio/mpeg">' +
          "Your browser does not support the audio element." +
          "</audio>" +
          "</div>"
        );
      }
    }

    // Handle Sticker
    if (body.indexOf("[STICKER:") !== -1) {
      var match = body.match(/\[STICKER:([^\]]+)\]/);
      if (match) {
        var url = resolvePreferredMediaUrl(message, match[1]);
        if (!isValidMediaUrl(url)) return senderHtml + fallbackHtml;

        return (
          senderHtml +
          '<div class="message-sticker"><img src="' +
          url +
          '" class="rounded" style="max-width:150px; cursor:pointer;" onclick="window.open(\'' +
          url +
          "')\"></div>"
        );
      }
    }

    // Handle Document
    if (type === "document" || body.indexOf("[DOCUMENT:") !== -1) {
      // Use greedy match for URL to handle http:// or query params, split by last colon
      var match = body.match(/\[DOCUMENT:(.+):([^:]+)\]/);
      if (match) {
        var url = resolvePreferredMediaUrl(message, match[1]);
        var filename = match[2];
        if (!isValidMediaUrl(url)) return senderHtml + fallbackHtml;

        var ext = filename.split(".").pop().toUpperCase();
        return (
          senderHtml +
          '<div class="message-document">' +
          '<i class="bi bi-file-earmark-text fs-1 text-gray-600"></i>' +
          '<div class="message-document-info ms-2">' +
          '<div class="message-document-name">' +
          escapeHtml(filename) +
          "</div>" +
          '<div class="message-document-size">' +
          ext +
          " File</div>" +
          "</div>" +
          '<a href="' +
          url +
          '" target="_blank" class="btn btn-icon btn-sm btn-light-primary ms-2"><i class="bi bi-download"></i></a>' +
          "</div>"
        );
      }
    }

    // Handle Text with WhatsApp formatting and line breaks
    return senderHtml + formatWhatsAppText(escapeHtml(body));
  };

  // WhatsApp-style text formatting
  var formatWhatsAppText = function (text) {
    if (!text) return "";

    // Bold: *text* -> <b>text</b>
    text = text.replace(/\*([^*]+)\*/g, "<b>$1</b>");

    // Italic: _text_ -> <i>text</i>
    text = text.replace(/\_([^_]+)\_/g, "<i>$1</i>");

    // Strikethrough: ~text~ -> <s>text</s>
    text = text.replace(/\~([^~]+)\~/g, "<s>$1</s>");

    // Monospace: ```text``` -> <code>text</code>
    text = text.replace(
      /\`\`\`([^`]+)\`\`\`/g,
      '<code class="bg-light px-1 rounded">$1</code>',
    );

    // Single backtick monospace: `text` -> <code>text</code>
    text = text.replace(
      /\`([^`]+)\`/g,
      '<code class="bg-light px-1 rounded">$1</code>',
    );

    // Line breaks
    text = text.replace(/\n/g, "<br>");

    return text;
  };

  // Initialize message form
  var initMessageForm = function () {
    $("#send-message-form").on("submit", function (e) {
      e.preventDefault();

      if (selectedFile) {
        sendFile();
      } else {
        sendMessage();
      }
    });

    // Enter to send (Shift+Enter for new line)
    $("#message-input").on("keydown", function (e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        $("#send-message-form").submit();
      }
    });
  };

  // Send text message
  var sendMessage = function () {
    var message = $("#message-input").val().trim();
    if (!message || !currentCustomerId) return;

    var btn = $("#btn-send");
    var originalHtml = btn.html();

    // Show spinner on button instead of overlay
    btn.prop("disabled", true);
    btn.html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
      url: appUrl + "chat/send",
      method: "POST",
      data: {
        customer_id: currentCustomerId,
        type: currentCustomerType,
        message: message,
        reply_message_id: currentReplyMessageId,
        reply_content: currentReplyContent,
        reply_sender_name: currentReplySenderName,
      },
      success: function (response) {
        if (response.success) {
          $("#message-input").val("");
          clearReplyContext(); // Clear reply state after successful send
          appendMessage(response.data);
          // Refresh sidebar to show updated last chat
          loadCustomers($("#search-customers").val());
        } else {
          toastr.error(response.message || "Gagal mengirim pesan");
        }
      },
      complete: function () {
        btn.prop("disabled", false);
        btn.html(originalHtml);
      },
    });
  };

  // Send file (image/document) - WA Style
  var sendFile = function () {
    var formData = new FormData();
    formData.append("customer_id", currentCustomerId);
    formData.append("type", currentCustomerType);

    var caption = $("#message-input").val().trim();
    var isImage = selectedFileType === "image";
    var fileType = selectedFileType; // Save before clear

    if (isImage) {
      formData.append("image", selectedFile);
      formData.append("caption", caption);
    } else {
      formData.append("document", selectedFile);
    }

    if (currentReplyMessageId) {
      formData.append("reply_message_id", currentReplyMessageId);
      formData.append("reply_content", currentReplyContent);
      formData.append("reply_sender_name", currentReplySenderName);
    }

    // Generate unique temp ID for this upload
    var tempId = "upload-" + Date.now();

    // Create preview in chat immediately (WA style)
    var previewHtml = "";
    var time = new Date().toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });

    if (isImage) {
      // Show image preview with loading overlay
      var imgUrl = URL.createObjectURL(selectedFile);
      previewHtml =
        '<div class="message-wrapper message-out" id="' +
        tempId +
        '">' +
        '<div class="message-bubble" style="display:inline-block; width:auto; max-width:262px; padding:6px 6px 4px 6px;">' +
        '<div class="position-relative d-inline-block">' +
        '<div style="width:250px; height:350px; overflow:hidden; border-radius:6px;">' +
        '<img src="' +
        imgUrl +
        '" class="message-image" style="display:block; width:250px; height:350px; min-width:250px; min-height:350px; max-width:none; max-height:none; object-fit:cover; object-position:center; opacity:0.7;">' +
        "</div>" +
        '<div class="upload-overlay position-absolute top-50 start-50 translate-middle text-center">' +
        '<div class="spinner-border text-light" role="status" style="width: 40px; height: 40px;"></div>' +
        '<div class="upload-status-text">Mengirim...</div>' +
        "</div>" +
        "</div>" +
        (caption
          ? '<div class="message-media-caption mt-1" style="display:block; width:250px; max-width:250px;">' +
            escapeHtml(caption) +
            "</div>"
          : "") +
        '<div class="message-text"><span class="message-time">' +
        time +
        ' <i class="bi bi-clock text-muted"></i></span></div>' +
        "</div>" +
        "</div>";
    } else {
      // Show document preview with loading
      var ext = selectedFile.name.split(".").pop().toUpperCase();
      previewHtml =
        '<div class="message-wrapper message-out" id="' +
        tempId +
        '">' +
        '<div class="message-bubble">' +
        '<div class="message-document position-relative" style="opacity: 0.7;">' +
        '<i class="bi bi-file-earmark-text fs-1 text-gray-600"></i>' +
        '<div class="message-document-info ms-2">' +
        '<div class="message-document-name">' +
        escapeHtml(selectedFile.name) +
        "</div>" +
        '<div class="message-document-size">' +
        ext +
        " File</div>" +
        "</div>" +
        '<div class="spinner-border spinner-border-sm text-success ms-2"></div>' +
        "</div>" +
        '<div class="message-text"><span class="message-time">' +
        time +
        ' <i class="bi bi-clock text-muted"></i></span></div>' +
        "</div>" +
        "</div>";
    }

    // Add preview to chat
    $("#messages-list").append(previewHtml);
    scrollToBottom();

    // Clear attachment preview
    clearAttachment();
    $("#message-input").val("");

    // Disable send button
    var btn = $("#btn-send");
    btn.prop("disabled", true);

    // Use XMLHttpRequest for progress
    var xhr = new XMLHttpRequest();

    xhr.upload.addEventListener("progress", function (e) {
      if (e.lengthComputable) {
        var percent = Math.round((e.loaded / e.total) * 100);
        // Update circular progress (circumference = 2*PI*16 = 100.53)
        var circumference = 100.53;
        var offset = circumference - (percent / 100) * circumference;
        $("#" + tempId + " .upload-progress-circle").attr(
          "stroke-dashoffset",
          offset,
        );
        $("#" + tempId + " .upload-percent-text").text(percent + "%");
      }
    });

    xhr.addEventListener("load", function () {
      try {
        var response = JSON.parse(xhr.responseText);
        if (response.success) {
          // Remove temp preview and reload to show actual message
          $("#" + tempId).remove();
          clearReplyContext(); // Clear reply state
          selectCustomer(currentCustomerId, currentCustomerType);
          loadCustomers($("#search-customers").val());
        } else {
          // Show error on the preview
          $("#" + tempId + " .upload-overlay").html(
            '<i class="bi bi-x-circle text-danger fs-1"></i>',
          );
          $("#" + tempId + " .message-image").css("opacity", "0.4");
          toastr.error(response.message || "Gagal mengirim file");
        }
      } catch (e) {
        $("#" + tempId).remove();
        toastr.error("Gagal mengirim file");
      }
      btn.prop("disabled", false);
    });

    xhr.addEventListener("error", function () {
      $("#" + tempId + " .upload-overlay").html(
        '<i class="bi bi-x-circle text-danger fs-1"></i>',
      );
      toastr.error("Gagal mengirim file");
      btn.prop("disabled", false);
    });

    xhr.open("POST", appUrl + "chat/send_" + fileType);
    xhr.send(formData);
  };

  // Initialize attachments
  var initAttachments = function () {
    $("#input-image").on("change", function () {
      if (this.files && this.files.length > 0) {
        // Trigger preview modal with selected files
        triggerUploadPreview(Array.from(this.files));
        this.value = ""; // Reset input
      }
    });

    $("#input-document").on("change", function () {
      if (this.files && this.files.length > 0) {
        // Trigger preview modal - send as document type
        triggerUploadPreview(Array.from(this.files), true);
        this.value = ""; // Reset input
      }
    });

    $("#remove-attachment").on("click", clearAttachment);

    // Load More Chats (Local DB Pagination)
    $("#btn-load-more-chats").on("click", function () {
      var btn = $(this);
      // Disable button and start spinner
      btn.prop("disabled", true);
      btn.text("Loading...");

      loadCustomers($("#search-customers").val(), customerPage + 1);
    });
  };

  // Upload preview variables (shared between button upload and drag/drop)
  var pendingFiles = [];
  var currentPreviewIndex = 0;
  var forceAsDocument = false; // When true, send all files as document type

  // Function to trigger upload preview from anywhere
  // sendAsDoc: if true, all files will be sent as document type
  var triggerUploadPreview = function (files, sendAsDoc) {
    if (!currentCustomerId) {
      toastr.warning("Pilih customer dulu");
      return;
    }
    pendingFiles = files;
    currentPreviewIndex = 0;
    forceAsDocument = sendAsDoc || false;
    showUploadPreviewModal();
  };

  // Initialize drag and drop upload - WA Style
  var initDragDrop = function () {
    var dragCounter = 0;

    // Remove existing elements if any (prevent duplicates)
    $("#drop-zone").remove();
    $("#upload-preview-modal").remove();

    // Create WA-style drop zone - covers messages area
    var dropZoneHtml =
      '<div id="drop-zone" class="drop-zone-wa d-none">' +
      '<div class="drop-zone-wa-content">' +
      '<i class="bi bi-cloud-arrow-up-fill drop-zone-icon"></i>' +
      '<div class="drop-zone-text">Drag Gambar/Dokumen disini</div>' +
      "</div>" +
      "</div>";

    // Create preview modal
    var previewModalHtml =
      '<div id="upload-preview-modal" class="upload-preview-modal d-none">' +
      '<div class="upload-preview-header">' +
      '<button type="button" class="btn btn-icon" id="close-preview-modal"><i class="bi bi-x-lg text-white"></i></button>' +
      "</div>" +
      '<div class="upload-preview-body">' +
      '<div class="upload-preview-main">' +
      '<img id="upload-preview-image" src="" alt="Preview">' +
      '<div id="upload-preview-doc" class="d-none"><i class="bi bi-file-earmark-text"></i><span></span></div>' +
      "</div>" +
      "</div>" +
      '<div class="upload-preview-footer">' +
      '<div class="upload-footer-input-row">' +
      '<div class="upload-caption-wrapper">' +
      '<input type="text" id="upload-preview-caption" class="form-control" placeholder="Type a message">' +
      '<button type="button" class="btn btn-icon upload-emoji-btn"><i class="bi bi-emoji-smile"></i></button>' +
      "</div>" +
      "</div>" +
      '<div class="upload-footer-thumbs-row">' +
      '<div class="upload-preview-thumbs" id="upload-thumbs"></div>' +
      '<button class="btn btn-success btn-icon rounded-circle" id="send-preview-files"><i class="bi bi-send-fill"></i></button>' +
      "</div>" +
      "</div>" +
      "</div>";

    // Append to body (always exists)
    $("body").append(dropZoneHtml).append(previewModalHtml);

    var dropZone = $("#drop-zone");
    var previewModal = $("#upload-preview-modal");
    var isDragging = false;

    // Show drop zone on dragenter
    $(document).on("dragenter", function (e) {
      e.preventDefault();
      if (!currentCustomerId) return;

      var cc = $("#chat-container");
      if (cc.length && !isDragging) {
        var mc = $("#messages-container");
        var ccOffset = cc.offset();
        var ccBottom = ccOffset.top + cc.outerHeight();

        // Calculate top position from messages-container (below topbar)
        var dropTop = mc.length ? mc.offset().top : ccOffset.top;
        // Height extends to bottom of chat-container (includes text editor)
        var dropHeight = ccBottom - dropTop;

        // Check if mouse is inside chat container (for detection)
        if (
          e.originalEvent.clientX >= ccOffset.left &&
          e.originalEvent.clientX <= ccOffset.left + cc.outerWidth() &&
          e.originalEvent.clientY >= ccOffset.top &&
          e.originalEvent.clientY <= ccOffset.top + cc.outerHeight()
        ) {
          isDragging = true;
          // Position: starts below topbar, extends to include text editor
          dropZone
            .css({
              position: "fixed",
              top: dropTop,
              left: ccOffset.left,
              width: cc.outerWidth(),
              height: dropHeight,
              zIndex: 9999,
            })
            .removeClass("d-none");
        }
      }
    });

    // Keep showing on dragover
    $(document).on("dragover", function (e) {
      e.preventDefault();
    });

    // Hide on dragleave only if leaving container bounds
    $(document).on("dragleave", function (e) {
      e.preventDefault();
      if (!isDragging) return;

      var cc = $("#chat-container");
      if (cc.length) {
        var offset = cc.offset();
        var rect = {
          top: offset.top,
          left: offset.left,
          right: offset.left + cc.outerWidth(),
          bottom: offset.top + cc.outerHeight(),
        };

        // Check if mouse left the container bounds
        if (
          e.originalEvent.clientX < rect.left ||
          e.originalEvent.clientX > rect.right ||
          e.originalEvent.clientY < rect.top ||
          e.originalEvent.clientY > rect.bottom
        ) {
          isDragging = false;
          dropZone.addClass("d-none");
        }
      }
    });

    // Handle drop
    $(document).on("drop", function (e) {
      e.preventDefault();
      isDragging = false;
      dropZone.addClass("d-none");

      if (!currentCustomerId) return;

      // Check if dropped on chat container
      var cc = $("#chat-container");
      if (cc.length) {
        var offset = cc.offset();
        if (
          e.originalEvent.clientX >= offset.left &&
          e.originalEvent.clientX <= offset.left + cc.outerWidth() &&
          e.originalEvent.clientY >= offset.top &&
          e.originalEvent.clientY <= offset.top + cc.outerHeight()
        ) {
          pendingFiles = Array.from(e.originalEvent.dataTransfer.files);
          if (pendingFiles.length === 0) return;
          currentPreviewIndex = 0;
          showUploadPreviewModal();
        }
      }
    });
  }; // End initDragDrop

  // Show upload preview modal (shared function)
  var showUploadPreviewModal = function () {
    var previewModal = $("#upload-preview-modal");
    var thumbsHtml = "";
    pendingFiles.forEach(function (f, i) {
      var isImg = f.type.startsWith("image/");
      if (isImg) {
        thumbsHtml +=
          '<div class="upload-thumb' +
          (i === currentPreviewIndex ? " active" : "") +
          '" data-idx="' +
          i +
          '"><img src="' +
          URL.createObjectURL(f) +
          '"></div>';
      } else {
        thumbsHtml +=
          '<div class="upload-thumb' +
          (i === currentPreviewIndex ? " active" : "") +
          '" data-idx="' +
          i +
          '"><i class="bi bi-file-earmark"></i></div>';
      }
    });
    thumbsHtml +=
      '<div class="upload-thumb add-more" id="add-more-btn"><i class="bi bi-plus"></i></div>';
    $("#upload-thumbs").html(thumbsHtml);
    updateUploadPreview();

    // Position modal same as drop zone (below topbar, includes text editor)
    var cc = $("#chat-container");
    var mc = $("#messages-container");
    if (cc.length) {
      var ccOffset = cc.offset();
      var ccBottom = ccOffset.top + cc.outerHeight();
      var dropTop = mc.length ? mc.offset().top : ccOffset.top;
      var dropHeight = ccBottom - dropTop;

      previewModal.css({
        position: "fixed",
        top: dropTop,
        left: ccOffset.left,
        width: cc.outerWidth(),
        height: dropHeight,
        zIndex: 9999,
      });
    }

    previewModal.removeClass("d-none");
  };

  // Update preview image/doc
  var updateUploadPreview = function () {
    var f = pendingFiles[currentPreviewIndex];
    if (!f) return;
    if (f.type.startsWith("image/")) {
      $("#upload-preview-image").attr("src", URL.createObjectURL(f)).show();
      $("#upload-preview-doc").addClass("d-none");
    } else {
      $("#upload-preview-image").hide();
      $("#upload-preview-doc").removeClass("d-none").find("span").text(f.name);
    }
    $(".upload-thumb").removeClass("active");
    $('.upload-thumb[data-idx="' + currentPreviewIndex + '"]').addClass(
      "active",
    );
  };

  // Upload preview event handlers
  var initUploadPreviewEvents = function () {
    $(document).on("click", ".upload-thumb[data-idx]", function () {
      currentPreviewIndex = parseInt($(this).data("idx"));
      updateUploadPreview();
    });

    $(document).on("click", "#add-more-btn", function () {
      $('<input type="file" multiple>')
        .on("change", function () {
          pendingFiles = pendingFiles.concat(Array.from(this.files));
          showUploadPreviewModal();
        })
        .click();
    });

    $(document).on("click", "#close-preview-modal", function () {
      $("#upload-preview-modal").addClass("d-none");
      pendingFiles = [];
      $("#upload-emoji-picker").remove();
    });

    // Emoji picker
    $(document).on("click", ".upload-emoji-btn", function (e) {
      e.stopPropagation();
      var picker = $("#upload-emoji-picker");
      if (picker.length) {
        picker.remove();
        return;
      }

      var emojis = [
        "😀",
        "😁",
        "😂",
        "🤣",
        "😃",
        "😄",
        "😅",
        "😆",
        "😉",
        "😊",
        "😋",
        "😎",
        "😍",
        "😘",
        "🥰",
        "😗",
        "😙",
        "🥲",
        "😚",
        "☺️",
        "🙂",
        "🤗",
        "🤩",
        "🤔",
        "🤨",
        "😐",
        "😑",
        "😶",
        "🙄",
        "😏",
        "😣",
        "😥",
        "😮",
        "🤐",
        "😯",
        "😪",
        "😫",
        "🥱",
        "😴",
        "😌",
        "😛",
        "😜",
        "😝",
        "🤤",
        "😒",
        "😓",
        "😔",
        "😕",
        "🙃",
        "🤑",
        "😲",
        "☹️",
        "🙁",
        "😖",
        "😞",
        "😟",
        "😤",
        "😢",
        "😭",
        "😦",
        "😧",
        "😨",
        "😩",
        "🤯",
        "😬",
        "😰",
        "😱",
        "🥵",
        "🥶",
        "😳",
        "🤪",
        "😵",
        "🥴",
        "😠",
        "😡",
        "🤬",
        "😷",
        "🤒",
        "🤕",
        "🤢",
        "🤮",
        "🤧",
        "😇",
        "🥳",
        "🥸",
        "🥺",
        "🤡",
        "🤠",
        "🤥",
        "😈",
        "👿",
        "👋",
        "🤚",
        "🖐",
        "✋",
        "🖖",
        "👌",
        "🤌",
        "🤏",
        "✌️",
        "🤞",
        "🤟",
        "🤘",
        "🤙",
        "👈",
        "👉",
        "👆",
        "🖕",
        "👇",
        "☝️",
        "👍",
        "👎",
        "✊",
        "👊",
        "🤛",
        "🤜",
        "👏",
        "🙌",
        "👐",
        "🤲",
        "🤝",
        "🙏",
        "❤️",
        "🧡",
        "💛",
        "💚",
        "💙",
        "💜",
        "🖤",
        "🤍",
        "🤎",
        "💔",
        "❣️",
        "💕",
        "💞",
        "💓",
        "💗",
        "💖",
        "💘",
        "💝",
        "💟",
      ];

      var emojiHtml =
        '<div id="upload-emoji-picker" class="upload-emoji-picker">';
      emojis.forEach(function (e) {
        emojiHtml +=
          '<span class="emoji-item" data-emoji="' + e + '">' + e + "</span>";
      });
      emojiHtml += "</div>";

      $(this).parent().append(emojiHtml);
    });

    // Insert emoji
    $(document).on("click", ".emoji-item", function (e) {
      e.stopPropagation();
      var emoji = $(this).data("emoji");
      var input = $("#upload-preview-caption");
      input.val(input.val() + emoji);
      input.focus();
    });

    // Close emoji picker on outside click
    $(document).on("click", function () {
      $("#upload-emoji-picker").remove();
    });

    $(document).on("click", "#send-preview-files", function () {
      var caption = $("#upload-preview-caption").val().trim();
      $("#upload-preview-modal").addClass("d-none");

      pendingFiles.forEach(function (f, i) {
        selectedFile = f;
        // If forceAsDocument is true, send as document regardless of file type
        if (forceAsDocument) {
          selectedFileType = "document";
        } else {
          selectedFileType = f.type.startsWith("image/") ? "image" : "document";
        }
        $("#message-input").val(i === 0 ? caption : "");
        sendFile();
      });
      pendingFiles = [];
      forceAsDocument = false; // Reset flag
      $("#upload-preview-caption").val("");
    });
  };

  var showAttachmentPreview = function (name, type) {
    var icon = type === "image" ? "🖼️" : "📄";
    $("#attachment-name").text(icon + " " + name);
    $("#attachment-preview").removeClass("d-none");
  };

  var clearAttachment = function () {
    selectedFile = null;
    selectedFileType = null;
    $("#attachment-preview").addClass("d-none");
    $("#input-image").val("");
    $("#input-document").val("");
  };

  // Initialize templates
  var initTemplates = function () {
    // Load templates when dropdown is shown
    $("#template-dropdown")
      .closest(".dropdown")
      .on("show.bs.dropdown", function () {
        loadTemplates();
      });

    // Select template
    $(document).on("click", ".template-item", function () {
      var content = $(this).data("content");
      $("#message-input").val(content);
      $("#message-input").focus();
    });
  };

  // Load templates from API
  var loadTemplates = function () {
    $.ajax({
      url: appUrl + "chat/templates",
      method: "GET",
      success: function (response) {
        if (response.success && response.grouped) {
          var html = "";
          var categoryNames = {
            greeting: "👋 Salam",
            thanks: "🙏 Terima Kasih",
            payment: "💰 Pembayaran",
            product: "📦 Produk",
            shipping: "🚚 Pengiriman",
            info: "ℹ️ Info",
            general: "📝 Umum",
          };

          for (var cat in response.grouped) {
            var catName = categoryNames[cat] || cat;
            html +=
              '<div class="fw-bold text-muted fs-8 mb-2 mt-2">' +
              catName +
              "</div>";

            html += '<div class="row g-3">';
            response.grouped[cat].forEach(function (t) {
              var preview =
                t.content.substring(0, 80) +
                (t.content.length > 80 ? "..." : "");
              html += '<div class="col-md-6 mb-2">';
              html +=
                '<div class="template-grid-item" data-content="' +
                escapeHtml(t.content) +
                '" onclick="selectTemplate(this)">';
              html +=
                '<div class="d-flex justify-content-between align-items-center mb-1">';
              html +=
                '<div class="fw-bold fs-6 text-gray-800">' +
                escapeHtml(t.name) +
                "</div>";
              if (t.shortcut) {
                html +=
                  '<span class="badge badge-light-primary fs-8">' +
                  t.shortcut +
                  "</span>";
              }
              html += "</div>";
              html +=
                '<div class="text-gray-600 fs-7">' +
                escapeHtml(preview) +
                "</div>";
              html += "</div></div>";
            });
            html += "</div>";
          }

          if (!html) {
            html =
              '<div class="text-center text-muted py-5">Belum ada template</div>';
          }

          $("#template-sticky-body").html(html);
        }
      },
    });
  };

  // Toggle template preview
  window.toggleTemplatePreview = function () {
    var container = $("#template-sticky-container");
    var isHidden = container.hasClass("d-none");

    if (isHidden) {
      container.removeClass("d-none");
      // Hide emoji picker if open
      if (!$("#emoji_picker").hasClass("d-none")) {
        toggleEmojiPicker();
      }
      loadTemplates();
    } else {
      container.addClass("d-none");
    }
  };

  // Close sticky on click outside
  $(document).on("click", function (e) {
    if (
      !$(e.target).closest("#template-sticky-container").length &&
      !$(e.target).closest('button[onclick="toggleTemplatePreview()"]').length
    ) {
      $("#template-sticky-container").addClass("d-none");
    }
  });

  // Initialize actions
  var initActions = function () {
    // Init tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Customer detail Sidebar
    $("#btn-customer-detail")
      .off("click")
      .on("click", function () {
        if (!currentCustomerId) return;
        openRightSidebar(currentCustomerId, false);
      });

    $("#btn-close-sidebar").on("click", function () {
      $("#chat-right-sidebar").addClass("d-none");
    });

    $("#btn-force-update-avatar").on("click", function () {
      if (!currentCustomerId) return;
      openRightSidebar(currentCustomerId, true);
    });

    // Edit Contact
    $("#btn-edit-contact").on("click", function () {
      if (!currentCustomerId || currentCustomerType === "group") {
        toastr.warning("Hanya dapat edit kontak individual");
        return;
      }
      window.open(
        appUrl + "admin/customers/edit/" + currentCustomerId,
        "_blank",
      );
    });

    // New Deal Modal Triggers
    $(document).on(
      "click",
      "#btn-header-deal-baru, #btn-sidebar-deal-baru",
      function () {
        if (!currentCustomerId || currentCustomerType === "group") {
          toastr.warning("Pilih customer individual terlebih dahulu");
          return;
        }

        $("#form-deal")[0].reset();
        $("#deal-id").val("");
        $("#modal-deal-title").text("Deal Baru");

        // Pre-fill current customer in Select2
        var customerName = $("#chat-customer-name").text();
        var option = new Option(customerName, currentCustomerId, true, true);
        $("#deal-customer").append(option).trigger("change");

        $("#modal-deal").modal("show");
      },
    );

    // Sync Chat (using offset pagination)
    $("#btn-sync-chat").on("click", function () {
      var btn = $(this);
      var icon = btn.find("i");

      if (btn.prop("disabled")) return;

      // Reset state
      btn.prop("disabled", true);
      icon.attr("class", "spinner-border spinner-border-sm text-success");

      syncMessagesPage(0, btn, icon); // Start from offset 0
    });

    // Load Previous Messages
    $(document).on("click", "#btn-load-previous", function () {
      loadPreviousMessages();
    });

    // Assign user
    $(document).on("click", ".assign-option", function (e) {
      e.preventDefault();
      var userId = $(this).data("id");

      $.post(
        appUrl + "chat/assign",
        {
          customer_id: currentCustomerId,
          user_id: userId,
        },
        function (response) {
          if (response.success) {
            toastr.success(response.message);
          }
        },
      );
    });

    // Assign Labels
    $(document).on("click", "#menu-assign-label", function (e) {
      e.preventDefault();
      var menu = $("#chat-context-menu");
      var id = menu.data("id");
      if (!id) return;
      openAssignLabelModal(id);
      menu.hide();
    });

    $("#btn-assign-label").on("click", function () {
      if (!currentCustomerId) return;
      openAssignLabelModal(currentCustomerId);
    });

    $("#btn-save-labels").on("click", function () {
      saveCustomerLabels();
    });

    $("#btn-save-rename-chat").on("click", function () {
      var btn = $(this);
      var id = $("#rename-chat-id").val();
      var type = $("#rename-chat-type").val() || "individual";
      var name = $("#rename-chat-input").val().trim();

      if (!id || !name) {
        toastr.warning("Nama baru wajib diisi");
        return;
      }

      btn.prop("disabled", true);

      $.post(
        appUrl + "chat/rename_chat",
        { id: id, type: type, name: name },
        function (response) {
          if (response.success) {
            var updatedName = getDisplayName(response.data || { display_name: name }, name);
            updateChatIdentityInUi(id, type, updatedName);
            $("#modal-rename-chat").modal("hide");
            toastr.success(response.message || "Nama berhasil diubah");
            loadCustomers($("#search-customers").val());

            if (
              String(currentCustomerId) === String(id) &&
              String(currentCustomerType) === String(type)
            ) {
              if (!$("#chat-right-sidebar").hasClass("d-none") && type === "individual") {
                openRightSidebar(currentCustomerId);
              }
            }
          } else {
            toastr.error(response.error || response.message || "Gagal mengubah nama");
          }

          btn.prop("disabled", false);
        },
      ).fail(function (xhr) {
        var message = "Gagal mengubah nama";
        if (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
          message = xhr.responseJSON.error || xhr.responseJSON.message;
        }
        toastr.error(message);
        btn.prop("disabled", false);
      });
    });

    $("#rename-chat-input").on("keypress", function (e) {
      if (e.which === 13) {
        $("#btn-save-rename-chat").trigger("click");
      }
    });
  };

  // Sync messages for a specific chat - using offset pagination
  var syncMessagesPage = function (offset, btn, icon) {
    $.post(
      appUrl + "chat/sync",
      {
        customer_id: currentCustomerId,
        type: currentCustomerType,
        offset: offset,
      },
      function (response) {
        if (response.success) {
          toastr.success(response.message);

          // If has_next is true, continue syncing with next_offset
          if (response.has_next && response.next_offset !== undefined) {
            syncMessagesPage(response.next_offset, btn, icon);
          } else {
            finishMessageSync(btn, icon);
            // Reload conversation after full sync
            selectCustomer(currentCustomerId, currentCustomerType);
            // Refresh sidebar
            loadCustomers($("#search-customers").val());
          }
        } else {
          toastr.error(response.message || "Gagal sinkronisasi");
          finishMessageSync(btn, icon);
        }
      },
    ).fail(function () {
      toastr.error("Terjadi kesalahan koneksi");
      finishMessageSync(btn, icon);
    });
  };

  var finishMessageSync = function (btn, icon) {
    btn.prop("disabled", false);
    icon.attr("class", "ki-outline ki-arrows-circle fs-2"); // Reset icon
  };

  var openAssignLabelModal = function (customerId) {
    $("#assign-label-customer-id").val(customerId);

    // Clear checkboxes
    $('#label-checkbox-list input[type="checkbox"]').prop("checked", false);

    // Fetch current labels for the customer
    $.ajax({
      url: appUrl + "chat/conversation",
      type: "POST",
      data: {
        customer_id: customerId,
        type: "individual",
      },
      dataType: "json",
      success: function (response) {
        if (
          response.success &&
          response.chat_info &&
          response.chat_info.labels
        ) {
          var activeLabels = response.chat_info.labels.map(function (l) {
            return l.id;
          });
          $('#label-checkbox-list input[type="checkbox"]').each(function () {
            if (activeLabels.includes($(this).val())) {
              $(this).prop("checked", true);
            }
          });
        }
        $("#modal-assign-label").modal("show");
      },
    });
  };

  var saveCustomerLabels = function () {
    var customerId = $("#assign-label-customer-id").val();
    var labelIds = [];
    $("#label-checkbox-list input:checked").each(function () {
      labelIds.push($(this).val());
    });

    var btn = $("#btn-save-labels");
    btn.prop("disabled", true).find(".indicator-label").hide();
    btn.find(".indicator-progress").show();

    $.post(
      appUrl + "chat/assign_labels",
      {
        customer_id: customerId,
        label_ids: labelIds,
      },
      function (response) {
        if (response.success) {
          toastr.success("Label diperbarui");
          $("#modal-assign-label").modal("hide");

          // Update UI if this is the currently selected customer
          if (customerId == currentCustomerId) {
            var labelsHtml = "";
            if (response.labels && response.labels.length > 0) {
              response.labels.forEach(function (label) {
                labelsHtml +=
                  '<span class="badge badge-sm me-1" style="background-color: ' +
                  label.color +
                  "20; color: " +
                  label.color +
                  '">' +
                  label.name +
                  "</span>";
              });
            }
            $("#chat-customer-labels").html(labelsHtml);
          }

          // Refresh sidebar to reflect label changes
          refreshContactsWithUnread();
        } else {
          toastr.error(response.error || "Gagal memperbarui label");
        }

        btn.prop("disabled", false).find(".indicator-label").show();
        btn.find(".indicator-progress").hide();
      },
    );
  };

  // Auto resize textarea
  var initAutoResize = function () {
    $("#message-input").on("input", function () {
      this.style.height = "auto";
      this.style.height = Math.min(this.scrollHeight, 120) + "px";
    });
  };

  // Polling for new messages
  // Use recursive timeout instead of interval to prevent stacking requests
  var startPolling = function () {
    stopPolling();

    // If no messages yet, set baseline to current time so we only get truly new messages
    if (!lastMessageDate) {
      var now = new Date();
      lastMessageDate = now.toISOString().slice(0, 19).replace("T", " ");
    }

    poll();
  };

  var poll = function () {
    if (!currentCustomerId) return;

    pollInterval = setTimeout(function () {
      $.ajax({
        url: appUrl + "chat/poll",
        method: "GET",
        data: {
          customer_id: currentCustomerId,
          type: currentCustomerType,
          after_date: lastMessageDate,
        },
        success: function (response) {
          if (response.success && response.data) {
            if (Array.isArray(response.data) && response.data.length > 0) {
              var hasNewIncoming = false;

              response.data.forEach(function (msg) {
                appendMessage(msg);
                // Update lastMessageDate to newest (either created or updated)
                if (msg.created_at > lastMessageDate) {
                  lastMessageDate = msg.created_at;
                }
                if (msg.updated_at && msg.updated_at > lastMessageDate) {
                  lastMessageDate = msg.updated_at;
                }

                // Check for incoming message for sound
                if (msg.direction === "in") {
                  hasNewIncoming = true;
                }
              });

              if (hasNewIncoming) {
                playNotificationSound();
                // Refresh sidebar to update badge counts immediately
                refreshContactsWithUnread();
              }
            }
          }
        },
        complete: function () {
          // Schedule next poll only after current one finishes
          if (currentCustomerId) {
            poll();
          }
        },
      });
    }, 1000); // Wait 1s before sending request
  };

  var stopPolling = function () {
    if (pollInterval) {
      clearTimeout(pollInterval);
      pollInterval = null;
    }
  };

  // Global polling for contact list updates (runs even without chat selected)
  var startGlobalPolling = function () {
    if (globalPollInterval) return;

    globalPollInterval = setInterval(function () {
      // Refresh contacts list to get unread counts
      refreshContactsWithUnread();
    }, GLOBAL_POLL_INTERVAL);

    console.log("Global polling started");
  };

  var stopGlobalPolling = function () {
    if (globalPollInterval) {
      clearInterval(globalPollInterval);
      globalPollInterval = null;
    }
  };

  // Automatic Gateway Sync (Self-healing)
  var startGatewaySync = function () {
    if (gwSyncInterval) return;

    gwSyncInterval = setInterval(function () {
      // Sync top 20 chats to capture updates missed by webhook
      $.post(appUrl + "chat/sync_contacts", { offset: 0 }, function (response) {
        if (response.success) {
          console.log("Auto-sync completed");
          // Refresh list to show changes
          loadCustomers($("#search-customers").val());
        }
      });
    }, GW_SYNC_INTERVAL);

    console.log("Gateway auto-sync started");
  };

  var stopGatewaySync = function () {
    if (gwSyncInterval) {
      clearInterval(gwSyncInterval);
      gwSyncInterval = null;
    }
  };

  var isSyncing = false; // Flag to track if sync is in progress (declared here so refreshContactsWithUnread can access it)
  var isRefreshingContacts = false; // Prevent duplicate refresh requests

  var refreshContactsWithUnread = function () {
    // Skip if already refreshing or sync is in progress
    if (isRefreshingContacts || isSyncing) {
      return;
    }

    isRefreshingContacts = true;

    $.ajax({
      url: appUrl + "chat/customers",
      method: "GET",
      cache: false, // Prevent browser caching
      data: {
        search: $("#search-customers").val() || "",
        filter: currentFilter,
        label_id: currentLabelId,
        deal_stage_id: currentDealStageId,
      },
      success: function (response) {
        if (response.success && response.data) {
          // Calculate total unread count
          var currentTotalUnread = 0;
          response.data.forEach(function (customer) {
            currentTotalUnread += customer.unread_count || 0;
          });

          // Play notification sound if unread count increased (new message)
          // Skip on first load (previousTotalUnread === -1)
          console.log(
            "📊 Unread check - Previous:",
            previousTotalUnread,
            "Current:",
            currentTotalUnread,
          );
          if (
            previousTotalUnread !== -1 &&
            currentTotalUnread > previousTotalUnread
          ) {
            console.log("🔔 New message detected! Playing sound.");
            playNotificationSound();
          }
          previousTotalUnread = currentTotalUnread;

          renderCustomersList(response.data, true);

          // Update Ghost Count Badge
          var ghostCount = response.ghost_count || 0;
          if (ghostCount > 0) {
            $("#ghost-count-value").text(ghostCount);
            $("#ghost-count-container")
              .removeClass("d-none")
              .addClass("d-flex");
          } else {
            $("#ghost-count-container")
              .removeClass("d-flex")
              .addClass("d-none");
          }
        }
      },
      complete: function () {
        isRefreshingContacts = false;
      },
    });
  };

  var syncOffset = 0;
  var customerPage = 1;
  // isSyncing is declared earlier (before refreshContactsWithUnread)
  var stopSync = false; // Flag to stop sync
  var isMoreCustomers = true;

  // Sync Chat (Contacts) - using offset pagination
  $("#btn-sync-contacts").on("click", function () {
    if (isSyncing) return;

    var btn = $(this);
    var icon = btn.find("i");
    var originalIconClass = icon.attr("class");

    // Disable button and start spinner
    btn.prop("disabled", true);
    icon.attr("class", "spinner-border spinner-border-sm text-success");
    isSyncing = true;
    stopSync = false; // Reset stop flag
    syncOffset = 0; // Start from beginning

    // Pause global polling during sync to prevent request storm
    stopGlobalPolling();

    // Show stop button
    $("#btn-stop-sync").removeClass("d-none");

    toastr.info("Memulai sinkronisasi kontak...");

    // Start recursive sync
    performSync(btn, icon, originalIconClass, syncOffset);
  });

  // Stop Sync Button
  $("#btn-stop-sync").on("click", function () {
    if (!isSyncing) return;

    stopSync = true;
    toastr.warning("Menghentikan sinkronisasi...");
  });

  var performSync = function (btn, icon, originalIconClass, offset) {
    // Check if stop was requested
    if (stopSync) {
      toastr.info("Sinkronisasi dihentikan oleh user");
      finishSync(btn, icon, originalIconClass, true);
      return;
    }

    $.post(
      appUrl + "chat/sync_contacts",
      { offset: offset },
      function (response) {
        // Check again if stop was requested during request
        if (stopSync) {
          toastr.info("Sinkronisasi dihentikan oleh user");
          finishSync(btn, icon, originalIconClass, true);
          return;
        }

        if (response.success) {
          toastr.success(response.message);

          // If has_next is true, continue syncing with next_offset
          if (response.has_next && response.next_offset !== undefined) {
            performSync(btn, icon, originalIconClass, response.next_offset);
          } else {
            finishSync(btn, icon, originalIconClass, false);
            loadCustomers($("#search-customers").val(), 1);
          }
        } else {
          toastr.error(response.message || "Gagal sinkronisasi kontak");
          finishSync(btn, icon, originalIconClass, false);
        }
      },
    ).fail(function () {
      toastr.error("Terjadi kesalahan koneksi saat sinkronisasi");
      finishSync(btn, icon, originalIconClass, false);
    });
  };

  var finishSync = function (btn, icon, originalIconClass, wasStopped) {
    isSyncing = false;
    stopSync = false;
    btn.prop("disabled", false);
    icon.attr("class", originalIconClass);

    // Hide stop button
    $("#btn-stop-sync").addClass("d-none");

    // Resume global polling
    startGlobalPolling();

    // Refresh contact list (only if not stopped)
    if (!wasStopped) {
      loadCustomers($("#search-customers").val(), 1);
    }
  };

  // Load customers list for Sidebar
  var loadCustomers = function (search, page = 1) {
    if (page === 1) {
      customerPage = 1;
      isMoreCustomers = true;
      isLoadingCustomers = false;
      $("#customers-list").html(
        '<div class="text-center py-10" id="customers-loading"><span class="spinner-border text-primary"></span></div>',
      );
    } else {
      customerPage = page;
    }

    $.ajax({
      url: appUrl + "chat/customers",
      method: "GET",
      data: {
        search: search,
        page: page,
        filter: currentFilter,
        label_id: currentLabelId,
        deal_stage_id: currentDealStageId,
      },
      success: function (response) {
        if (response.success) {
          isMoreCustomers = response.has_more;
          renderCustomersList(response.data, page === 1);

          // Hide scroll loading indicator
          $("#customers-scroll-loading").addClass("d-none");
          isLoadingCustomers = false;

          // Update Ghost Count Badge
          var ghostCount = response.ghost_count || 0;
          if (ghostCount > 0) {
            $("#ghost-count-value").text(ghostCount);
            $("#ghost-count-container")
              .removeClass("d-none")
              .addClass("d-flex");
          } else {
            $("#ghost-count-container")
              .removeClass("d-flex")
              .addClass("d-none");
          }

          // Show end of list message if no more
          if (!isMoreCustomers && page > 1) {
            if ($("#end-of-list").length === 0) {
              $("#customers-list").append(
                '<div id="end-of-list" class="text-center text-muted py-3 fs-7">Semua data telah dimuat</div>',
              );
            }
          }
        }
      },
      error: function () {
        isLoadingCustomers = false;
        $("#customers-scroll-loading").addClass("d-none");
      },
    });
  };

  // Load more customers (for infinite scroll)
  var isLoadingCustomers = false;
  var loadMoreCustomers = function () {
    // Skip if already loading, no more data, or syncing
    if (isLoadingCustomers || !isMoreCustomers || isSyncing) {
      return;
    }

    isLoadingCustomers = true;
    customerPage++;

    // Show scroll loading indicator
    $("#customers-scroll-loading").removeClass("d-none");

    var search = $("#search-customers").val() || "";
    loadCustomers(search, customerPage);
  };

  // Load ghost chats list
  var loadGhostChats = function () {
    $("#ghost-chats-list").html(
      '<div class="text-center py-5"><span class="spinner-border text-primary"></span></div>',
    );

    $.ajax({
      url: appUrl + "chat/ghost_chats",
      method: "GET",
      success: function (response) {
        if (response.success && response.data.length > 0) {
          var html = "";
          response.data.forEach(function (ghost) {
            html += '<div class="d-flex align-items-center p-3 border-bottom">';
            html +=
              '<div class="symbol symbol-45px me-3"><span class="symbol-label bg-light-danger text-danger"><i class="fas fa-ghost fs-3"></i></span></div>';
            html += '<div class="flex-grow-1">';
            html +=
              '<div class="fw-bold">' +
              (ghost.name || "Tidak diketahui") +
              "</div>";
            html +=
              '<div class="text-muted fs-7">LID: ' + ghost.wa_number + "</div>";
            html +=
              '<div class="text-muted fs-7">' +
              ghost.message_count +
              " pesan</div>";
            html += "</div>";
            html +=
              '<button class="btn btn-sm btn-light-primary btn-assign-ghost" data-id="' +
              ghost.id +
              '" data-name="' +
              (ghost.name || ghost.wa_number) +
              '"><i class="bi bi-link-45deg"></i> Assign</button>';
            html += "</div>";
          });
          $("#ghost-chats-list").html(html);
        } else {
          $("#ghost-chats-list").html(
            '<div class="text-center text-muted py-5">Tidak ada Ghost Chat</div>',
          );
        }
      },
    });
  };

  // Open assign ghost modal
  $(document).on("click", ".btn-assign-ghost", function () {
    var ghostId = $(this).data("id");
    var ghostName = $(this).data("name");

    $("#assign-ghost-id").val(ghostId);
    $("#assign-ghost-info").html(
      "<strong>" + ghostName + "</strong> (ID: " + ghostId + ")",
    );
    $("#search-assign-target").val("");
    $("#assign-target-list").html(
      '<div class="text-center text-muted py-3">Ketik untuk mencari kontak tujuan</div>',
    );

    $("#modal_ghost_assign").modal("show");
  });

  // Search assign targets
  var searchAssignTargets = function (search) {
    if (!search || search.length < 2) {
      $("#assign-target-list").html(
        '<div class="text-center text-muted py-3">Ketik minimal 2 karakter</div>',
      );
      return;
    }

    $("#assign-target-list").html(
      '<div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></div>',
    );

    $.ajax({
      url: appUrl + "chat/valid_customers",
      method: "GET",
      data: { search: search },
      success: function (response) {
        if (response.success && response.data.length > 0) {
          var html = "";
          response.data.forEach(function (cust) {
            html +=
              '<div class="assign-target-item d-flex align-items-center p-2 border-bottom cursor-pointer hover-bg-light" data-id="' +
              cust.id +
              '" data-name="' +
              cust.name +
              '" style="cursor:pointer;">';
            html += '<div class="flex-grow-1">';
            html += '<div class="fw-semibold">' + cust.name + "</div>";
            html += '<div class="text-muted fs-7">' + cust.wa_number + "</div>";
            html += "</div>";
            html += '<i class="bi bi-chevron-right"></i>';
            html += "</div>";
          });
          $("#assign-target-list").html(html);
        } else {
          $("#assign-target-list").html(
            '<div class="text-center text-muted py-3">Tidak ditemukan</div>',
          );
        }
      },
    });
  };

  // Merge ghost to target
  var mergeGhost = function (ghostId, targetId) {
    $.ajax({
      url: appUrl + "chat/merge_ghost",
      method: "POST",
      data: { ghost_id: ghostId, target_customer_id: targetId },
      success: function (response) {
        if (response.success) {
          toastr.success(response.message);
          $("#modal_ghost_assign").modal("hide");
          loadGhostChats(); // Refresh list
          loadCustomers($("#search-customers").val()); // Refresh sidebar
        } else {
          toastr.error(response.error || "Gagal merge");
        }
      },
    });
  };
  var renderCustomersList = function (customers, replace = true) {
    var html = "";

    customers.forEach(function (c) {
      var displayName = getDisplayName(
        c,
        (c.type || "individual") === "group" ? "Group" : "Tanpa Nama",
      );
      var activeClass = c.id == currentCustomerId ? "active" : "";
      var hasUnread = c.unread_count > 0;

      // Format last message with icons
      var lastMsgHtml = formatLastMessage(c.last_message, c.wa_number);

      // Format time - show time only, or "Yesterday"/date for older
      var timeText = c.last_chat_at ? formatChatTime(c.last_chat_at) : "";
      var timeClass = hasUnread ? "text-success fw-semibold" : "text-muted";

      // Unread badge (circle with count)
      var unreadHtml = "";
      if (hasUnread) {
        var unreadCount = c.unread_count || 1;
        unreadHtml =
          '<div class="ms-2 d-flex flex-column align-items-end"><span class="badge badge-circle badge-success w-18px h-18px fs-9">' +
          unreadCount +
          "</span></div>";
      }

      // Labels
      var labelsHtml = "";
      if (c.labels && c.labels.length > 0) {
        var maxLabels = 2; // Show max 2 labels
        for (var i = 0; i < Math.min(c.labels.length, maxLabels); i++) {
          var label = c.labels[i];
          var labelColor = label.color || "#6c757d";
          labelsHtml +=
            '<span class="badge badge-sm me-1" style="background-color: ' +
            labelColor +
            '; color: white; font-size: 9px;">' +
            escapeHtml(label.name) +
            "</span>";
        }
        if (c.labels.length > maxLabels) {
          labelsHtml +=
            '<span class="text-muted fs-9">+' +
            (c.labels.length - maxLabels) +
            "</span>";
        }
      }

      // Name styling - bold if unread
      var nameClass = hasUnread
        ? "fw-bold text-gray-900"
        : "fw-semibold text-gray-800";
      var msgClass = hasUnread ? "text-gray-700" : "text-muted";

      var type = c.type || "individual";
      var activeClass =
        c.id == currentCustomerId && currentCustomerType == type
          ? "active"
          : "";

      // Icon helper for groups
      var avatarHtml = "";
      if (type === "group") {
        avatarHtml =
          '<div class="symbol symbol-50px symbol-circle">' +
          '<div class="symbol-label fs-5 fw-bold" style="background-color: #6f42c1; color: white;">' + // Purple for groups
          '<i class="bi bi-people-fill text-white"></i>' +
          "</div>" +
          "</div>";
      } else {
        if (c.avatar_url) {
          avatarHtml =
            '<div class="symbol symbol-50px symbol-circle">' +
            '<img src="' +
            c.avatar_url +
            '" alt="image" style="object-fit:cover;" />' +
            "</div>";
        } else {
          avatarHtml =
            '<div class="symbol symbol-50px symbol-circle">' +
            '<div class="symbol-label fs-5 fw-bold" style="background-color: #00a884; color: white;">' +
            getInitials(displayName || c.wa_number) +
            "</div>" +
            "</div>";
        }
      }

      html +=
        '<div class="customer-item d-flex align-items-center py-3 px-4 border-bottom cursor-pointer ' +
        activeClass +
        '" data-id="' +
        c.id +
        '" data-type="' +
        type +
        '" style="transition: background 0.15s;">' +
        // Avatar
        '<div class="me-3 flex-shrink-0">' +
        avatarHtml +
        "</div>" +
        // Content
        '<div class="d-flex flex-column flex-grow-1 overflow-hidden">' +
        // Row 1: Name + Time
        '<div class="d-flex justify-content-between align-items-center mb-1">' +
        '<div class="d-flex align-items-center overflow-hidden">' +
        '<span class="' +
        nameClass +
        ' text-truncate" style="max-width: 130px;">' +
        escapeHtml(displayName) +
        "</span>" +
        "</div>" +
        '<span class="' +
        timeClass +
        ' fs-8 flex-shrink-0 ms-2">' +
        timeText +
        "</span>" +
        "</div>" +
        // Row 2: Message preview + Unread badge
        '<div class="d-flex justify-content-between align-items-center">' +
        '<span class="' +
        msgClass +
        ' fs-7 text-truncate" style="max-width: 180px;">' +
        lastMsgHtml +
        "</span>" +
        unreadHtml +
        "</div>" +
        // Row 3: Labels (if any)
        (labelsHtml
          ? '<div class="d-flex align-items-center mt-1">' +
            labelsHtml +
            "</div>"
          : "") +
        "</div>" +
        "</div>";
    });

    if (html === "" && replace) {
      html =
        '<div class="text-center text-muted py-10">Tidak ada customer ditemukan</div>';
    }

    if (replace) {
      $("#customers-list").html(html);
    } else {
      $("#customers-list").append(html);
    }
  };
  // Load customers list for New Chat Modal
  var loadNewChatCustomers = function (search) {
    if (!search) {
      $("#new-chat-list").html(
        '<div class="text-center text-muted py-5">Ketik untuk mencari customer</div>',
      );
      return;
    }

    $.ajax({
      url: appUrl + "chat/customers", // Reuse same endpoint
      method: "GET",
      data: { search: search, mode: "all" },
      success: function (response) {
        if (response.success) {
          renderNewChatList(response.data);
        }
      },
    });
  };

  var renderNewChatList = function (customers) {
    var html = "";

    customers.forEach(function (customer) {
      var type = customer.type || "individual";
      var displayName = getDisplayName(customer, "Tanpa Nama");
      html +=
        '<div class="d-flex flex-stack py-3 border-bottom cursor-pointer hover-bg-light p-2 rounded new-chat-item" data-id="' +
        customer.id +
        '" data-type="' +
        type +
        '">' +
        '<div class="d-flex align-items-center">' +
        '<div class="symbol symbol-35px symbol-circle me-3">' +
        '<div class="symbol-label fs-6 fw-semibold bg-light-info text-info">' +
        getInitials(displayName || customer.wa_number) +
        "</div>" +
        "</div>" +
        '<div class="d-flex flex-column">' +
        '<span class="fw-bold text-gray-800">' +
        escapeHtml(displayName) +
        "</span>" +
        '<span class="text-muted fs-7">' +
        formatPhone(customer.wa_number) +
        "</span>" +
        "</div>" +
        "</div>" +
        '<div class="d-flex align-items-center">' +
        '<i class="ki-outline ki-message-text-2 fs-2 text-primary"></i>' +
        "</div>" +
        "</div>";
    });

    if (html === "") {
      html =
        '<div class="text-center text-muted py-5">Customer tidak ditemukan</div>';
    }

    $("#new-chat-list").html(html);
  };

  var loadForwardTargets = function (search) {
    $("#forward-target-list").html(
      '<div class="text-center py-5"><span class="spinner-border spinner-border-sm text-primary"></span></div>',
    );

    var requestData = {};
    if (search) {
      requestData.search = search;
      requestData.mode = "all";
    } else {
      requestData.page = 1;
    }

    $.ajax({
      url: appUrl + "chat/customers",
      method: "GET",
      data: requestData,
      success: function (response) {
        if (response.success) {
          var contacts = response.data || [];
          if (!search) {
            contacts = contacts.slice(0, 10);
          }
          renderForwardTargetList(contacts, !!search);
        } else {
          $("#forward-target-list").html(
            '<div class="text-center text-muted py-5">Gagal memuat daftar chat</div>',
          );
        }
      },
      error: function () {
        $("#forward-target-list").html(
          '<div class="text-center text-muted py-5">Gagal memuat daftar chat</div>',
        );
      },
    });
  };

  var renderForwardTargetList = function (customers, isSearchResult) {
    var html = "";

    customers.forEach(function (customer) {
      var type = customer.type || "individual";
      var displayName = getDisplayName(customer, "Tanpa Nama");
      var subtitle = customer.wa_number || "";
      var isCurrentChat =
        String(customer.id) === String(currentCustomerId) &&
        String(type) === String(currentCustomerType);
      var avatarHtml = "";

      if (customer.avatar_url) {
        avatarHtml =
          '<div class="symbol symbol-circle me-3" style="width: 40px; height: 40px; overflow: hidden; flex: 0 0 40px;">' +
          '<img src="' +
          escapeHtml(customer.avatar_url) +
          '" alt="' +
          escapeHtml(displayName) +
          '" style="display: block; width: 40px; height: 40px; object-fit: cover;" />' +
          "</div>";
      } else {
        avatarHtml =
          '<div class="symbol symbol-circle me-3" style="width: 40px; height: 40px; flex: 0 0 40px;">' +
          '<div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">' +
          escapeHtml(getInitials(displayName)) +
          "</div>" +
          "</div>";
      }

      html +=
        '<button type="button" class="btn btn-light w-100 text-start d-flex align-items-center justify-content-between mb-2 forward-target-item border" data-id="' +
        customer.id +
        '" data-type="' +
        type +
        '" ' +
        (isCurrentChat ? 'data-current-chat="1"' : "") +
        ">" +
        '<div class="d-flex align-items-center">' +
        avatarHtml +
        '<div class="d-flex flex-column overflow-hidden">' +
        '<span class="fw-bold text-gray-800 text-truncate">' +
        escapeHtml(displayName) +
        "</span>" +
        '<span class="text-muted fs-7 text-truncate">' +
        escapeHtml(subtitle) +
        "</span>" +
        "</div>" +
        "</div>" +
        '<div class="d-flex align-items-center gap-2">' +
        (isCurrentChat
          ? '<span class="badge badge-light-primary">Chat aktif</span>'
          : "") +
        '<input type="checkbox" class="form-check-input forward-target-checkbox" tabindex="-1" />' +
        "</div>" +
        "</button>";
    });

    if (!html) {
      html =
        '<div class="text-center text-muted py-5">' +
        (isSearchResult
          ? "Chat tujuan tidak ditemukan"
          : "Belum ada chat aktif terbaru") +
        "</div>";
    } else if (!isSearchResult) {
      html =
        '<div class="text-muted fs-7 fw-semibold mb-3">10 chat aktif terbaru</div>' +
        html;
    }

    $("#forward-target-list").html(html);
    updateForwardSelectionUi();
  };

  // Helper functions
  var scrollToBottom = function () {
    var container = document.getElementById("messages-container");
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  };

  var loadPreviousMessages = function (options) {
    options = options || {};

    // Get all rendered messages and skip separators/button wrappers
    var messageDivs = $("#messages-list").children("[data-message-id]");
    if (messageDivs.length === 0) {
      if (typeof options.onComplete === "function") {
        options.onComplete(false);
      }
      return;
    }

    var firstMsgElement = messageDivs.first()[0];
    var firstMsgDate = firstMsgElement.getAttribute("data-created-at");

    if (!firstMsgDate) {
      if (!options.silent) {
        console.error("No created-at found on first message");
      }
      if (typeof options.onComplete === "function") {
        options.onComplete(false);
      }
      return;
    }

    var container = document.getElementById("messages-container");
    var oldScrollHeight = container ? container.scrollHeight : 0;
    var btn = $("#btn-load-previous");
    var originalHtml = btn.length ? btn.html() : "";

    if (btn.length) {
      btn.html(
        '<span class="spinner-border spinner-border-sm"></span> Loading...',
      );
      btn.prop("disabled", true);
    }

    $.ajax({
      url: appUrl + "chat/conversation/" + currentCustomerId,
      method: "GET",
      data: {
        limit: 30,
        before_date: firstMsgDate,
      },
      success: function (response) {
        var loaded = false;

        if (
          response.success &&
          response.messages &&
          response.messages.length > 0
        ) {
          loaded = true;

          $("#btn-load-previous-wrapper").remove();

          var html = "";
          response.messages.forEach(function (msg) {
            html += renderMessage(msg);
          });

          $("#messages-list").prepend(html);

          if (container) {
            var newScrollHeight = container.scrollHeight;
            container.scrollTop = newScrollHeight - oldScrollHeight;
          }

          if (response.has_more) {
            $("#messages-list").prepend(
              '<div id="btn-load-previous-wrapper" class="text-center py-2"><button id="btn-load-previous" class="btn btn-sm btn-light-primary">Muat Chat Sebelumnya</button></div>',
            );
          }
        } else {
          $("#btn-load-previous-wrapper").remove();
        }

        if (typeof options.onComplete === "function") {
          options.onComplete(loaded);
        }
      },
      error: function () {
        if (!options.silent) {
          toastr.error("Gagal memuat pesan sebelumnya");
        }

        if (btn.length) {
          btn.html(originalHtml || "Muat Chat Sebelumnya");
          btn.prop("disabled", false);
        }

        if (typeof options.onComplete === "function") {
          options.onComplete(false);
        }
      },
    });
  };

  window.scrollToMessage = function (messageIdWa, attempt) {
    var targetMessageId = String(messageIdWa || "").trim();
    var currentAttempt = attempt || 0;

    if (!targetMessageId) {
      return;
    }

    var target = $("#messages-list")
      .children("[data-message-id]")
      .filter(function () {
        return String($(this).attr("data-message-id-wa") || "") === targetMessageId;
      })
      .first();

    if (target.length) {
      var container = $("#messages-container");
      if (!container.length) {
        return;
      }

      var nextScrollTop =
        target.position().top + container.scrollTop() - container.height() / 2 + target.outerHeight() / 2;

      container.stop(true).animate(
        {
          scrollTop: Math.max(nextScrollTop, 0),
        },
        250,
      );

      $(".highlight-reply").removeClass("highlight-reply");
      target.addClass("highlight-reply");

      setTimeout(function () {
        target.removeClass("highlight-reply");
      }, 2000);

      return;
    }

    if ($("#btn-load-previous").length && currentAttempt < 10) {
      loadPreviousMessages({
        silent: true,
        onComplete: function (loaded) {
          if (loaded) {
            window.scrollToMessage(targetMessageId, currentAttempt + 1);
          } else {
            toastr.info("Pesan asal tidak ditemukan di chat yang dimuat");
          }
        },
      });
      return;
    }

    toastr.info("Pesan asal belum tersedia di percakapan ini");
  };

  var formatPhone = function (phone) {
    if (!phone) return "";
    if (phone.length >= 10) {
      return (
        "+" +
        phone.substring(0, 2) +
        " " +
        phone.substring(2, 5) +
        "-" +
        phone.substring(5, 9) +
        "-" +
        phone.substring(9)
      );
    }
    return "+" + phone;
  };

  // Format last message with WhatsApp-style icons
  var formatLastMessage = function (message, fallbackPhone) {
    if (!message) {
      return formatPhone(fallbackPhone);
    }

    var text =
      typeof message === "object" && message !== null
        ? message.content || ""
        : message;

    // If message deleted or type is revoked
    if (typeof message === "object" && message.type === "revoked") {
      return '<i class="bi bi-slash-circle me-1"></i>Pesan dihapus';
    }

    var trimmed = text.trim();
    if (trimmed.startsWith("[FORWARDED]")) {
      text = text.replace(/^\[FORWARDED\]\s*/, "");
      trimmed = text.trim();
    }

    // Detect and add icons for media types
    if (trimmed.startsWith("[IMAGE:")) {
      return '<i class="bi bi-image me-1"></i>Photo';
    }
    if (trimmed.startsWith("[VIDEO:")) {
      return '<i class="bi bi-camera-video me-1"></i>Video';
    }
    if (trimmed.startsWith("[AUDIO:") || trimmed.startsWith("[PTT:")) {
      return '<i class="bi bi-mic me-1"></i>Pesan Suara';
    }
    if (trimmed.startsWith("[STICKER:")) {
      return '<i class="bi bi-sticky me-1"></i>Stiker';
    }
    if (trimmed.startsWith("[DOCUMENT:")) {
      return '<i class="bi bi-file-earmark me-1"></i>Document';
    }
    if (trimmed.startsWith("[LOCATION:")) {
      return '<i class="bi bi-geo-alt me-1"></i>Lokasi';
    }
    if (trimmed.startsWith("[CONTACT:")) {
      return '<i class="bi bi-person me-1"></i>Kontak';
    }
    if (trimmed.startsWith("[CONTACT|")) {
      return '<i class="bi bi-person me-1"></i>Kontak';
    }

    // Clean up any remaining brackets
    text = text.replace(/\[IMAGE:[^\]]+\]/g, "📷 ");
    text = text.replace(/\[VIDEO:[^\]]+\]/g, "🎬 ");
    text = text.replace(/\[AUDIO:[^\]]+\]/g, "🎵 ");
    text = text.replace(/\[PTT:[^\]]+\]/g, "🎤 Pesan Suara ");
    text = text.replace(/\[STICKER:[^\]]+\]/g, "🖼️ Stiker ");
    text = text.replace(/\[DOCUMENT:[^\]]+\]/g, "📄 ");
    text = text.replace(/\[LOCATION:[^\]]+\]/g, "📍 ");
    text = text.replace(/\[CONTACT[:|][^\]]+\]/g, "👤 Kontak ");

    return escapeHtml(truncate(text, 35));
  };

  // Format time like WhatsApp (time if today, "Yesterday", or date)
  var formatChatTime = function (dateStr) {
    if (!dateStr) return "";

    var date = new Date(dateStr);
    var now = new Date();
    var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    var yesterday = new Date(today.getTime() - 86400000);
    var msgDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    if (msgDate.getTime() === today.getTime()) {
      // Today - show time like "10:23"
      return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    } else if (msgDate.getTime() === yesterday.getTime()) {
      return "Yesterday";
    } else {
      // Show date like "12/01"
      return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
      });
    }
  };

  // Escape HTML to prevent XSS
  var escapeHtml = function (text) {
    if (!text) return "";
    var div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  };

  var formatDate = function (dateStr) {
    var date = new Date(dateStr);
    var today = new Date();
    var yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
      return "Hari ini";
    } else if (date.toDateString() === yesterday.toDateString()) {
      return "Kemarin";
    } else {
      return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
      });
    }
  };

  var getInitials = function (name) {
    if (!name) return "?";
    var words = name.trim().split(" ");
    var initials = "";
    words.forEach(function (word) {
      if (word) initials += word.charAt(0).toUpperCase();
    });
    return initials.substring(0, 2);
  };

  var truncate = function (str, len) {
    if (!str) return "";
    return str.length > len ? str.substring(0, len) + "..." : str;
  };

  var escapeHtml = function (text) {
    var div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  };

  var debounce = function (func, wait) {
    var timeout;
    return function () {
      var context = this,
        args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }; // End formatWhatsAppText

  // Open right sidebar & fetch customer details including avatar
  var openRightSidebar = function (customerId, forceUpdate) {
    var sidebar = $("#chat-right-sidebar");
    sidebar.removeClass("d-none");

    $("#right-sidebar-content").addClass("d-none");
    $("#right-sidebar-loading").removeClass("d-none");

    if (forceUpdate) {
      $("#btn-force-update-avatar")
        .prop("disabled", true)
        .html(
          '<span class="spinner-border spinner-border-sm me-1"></span> Mengupdate...',
        );
    }

    $.ajax({
      url: appUrl + "chat/get_customer_detail_api",
      type: "GET",
      data: { id: customerId, force: forceUpdate ? "true" : "false" },
      success: function (res) {
        if (res.success && res.data) {
          var c = res.data;
          $("#sidebar-name").text(getDisplayName(c, c.wa_number || "Tanpa Nama"));
          $("#sidebar-phone").text(c.wa_number);
          $("#sidebar-email").text(c.email || "-");
          $("#sidebar-address").text(c.address || "-");
          $("#sidebar-assignee").text(c.assigned_user_name || "-");
          $("#sidebar-last-chat").text(c.last_chat_at || "-");
          $("#sidebar-last-updated").text(
            c.avatar_last_updated || "Belum diupdate",
          );

          if (c.avatar_url) {
            $("#sidebar-avatar").attr("src", c.avatar_url);
          } else {
            $("#sidebar-avatar").attr(
              "src",
              appUrl + "assets/media/avatars/blank.png",
            );
          }

          // Render deals
          var dealsHtml = '<span class="text-muted fs-8">-</span>';
          if (c.deals && c.deals.length > 0) {
            dealsHtml = "";
            c.deals.forEach(function (deal) {
              var color = deal.stage_color || "#6c757d";
              dealsHtml +=
                '<div class="d-flex flex-column mb-2 border-start border-3 ps-3 py-1" style="border-color: ' +
                color +
                ' !important;">' +
                '<a href="' +
                appUrl +
                "deals/detail/" +
                deal.id +
                '" target="_blank" class="text-gray-800 text-hover-primary fw-bold fs-7 mb-1">' +
                escapeHtml(deal.title) +
                "</a>" +
                "<div>" +
                '<span class="badge badge-sm" style="background-color: ' +
                color +
                "20; color: " +
                color +
                "; border: 1px solid " +
                color +
                '40; font-size: 10px;">' +
                escapeHtml(deal.stage_name) +
                "</span>" +
                "</div>" +
                "</div>";
            });
          }
          $("#sidebar-deals").html(dealsHtml);

          if (res.debug_api_avatar !== undefined) {
            console.log(res.debug_api_avatar);
          }
        }
      },
      error: function (xhr) {
        var message = "Gagal mengambil data customer";
        if (xhr.responseJSON && xhr.responseJSON.error) {
          message = xhr.responseJSON.error;
        }
        toastr.error(message);
      },
      complete: function () {
        $("#right-sidebar-loading").addClass("d-none");
        $("#right-sidebar-content").removeClass("d-none");
        $("#btn-force-update-avatar")
          .prop("disabled", false)
          .html('<i class="ki-outline ki-arrows-circle fs-6"></i> Update Foto');
      },
    });
  };

  var initDealModal = function () {
    // Init Select2 for customer search in modal
    if ($.fn.select2) {
      $("#deal-customer").select2({
        dropdownParent: $("#modal-deal"),
        placeholder: "Cari customer...",
        allowClear: true,
        ajax: {
          url: appUrl + "deals/search_customers",
          dataType: "json",
          delay: 300,
          data: function (params) {
            return { term: params.term };
          },
          processResults: function (data) {
            return data;
          },
        },
        minimumInputLength: 1,
      });
    }

    // Handle Deal Form Submission
    $("#form-deal").on("submit", function (e) {
      e.preventDefault();
      var btn = $("#btn-save-deal");
      var originalHtml = btn.html();

      btn.find(".indicator-label").hide();
      btn.find(".indicator-progress").show();
      btn.prop("disabled", true);

      var formData = new FormData(this);
      var id = $("#deal-id").val();
      var url = id ? appUrl + "deals/update" : appUrl + "deals/create";

      $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
          if (res.success) {
            $("#modal-deal").modal("hide");
            toastr.success(res.message || "Deal berhasil disimpan");

            // If sidebar is open, refresh it
            if (!$("#chat-right-sidebar").hasClass("d-none")) {
              openRightSidebar(currentCustomerId);
            }
          } else {
            toastr.error(res.message || "Gagal menyimpan deal");
          }
        },
        error: function () {
          toastr.error("Terjadi kesalahan sistem");
        },
        complete: function () {
          btn.find(".indicator-label").show();
          btn.find(".indicator-progress").hide();
          btn.prop("disabled", false);
        },
      });
    });
  };

  var initMessageActions = function () {
    // Message Context Menu (Right Click) logic
    var messageContextTarget = null;
    $(document).on("contextmenu", ".message-bubble", function (e) {
      e.preventDefault();
      messageContextTarget = $(this);
      var wrapper = messageContextTarget.closest(".message-history-wrapper");
      var id = wrapper.attr("data-message-id");
      var direction = wrapper.hasClass("message-out") ? "out" : "in";
      var type = messageContextTarget.attr("data-type") || "text";
      var timestamp = parseInt(wrapper.attr("data-timestamp") || 0);
      var userId = parseInt(wrapper.attr("data-user-id") || 0);
      var rawContent = messageContextTarget.attr("data-raw-content") || "";

      // If it's a deleted message, don't show context menu
      if (messageContextTarget.find(".message-deleted-text").length > 0) return;

      var menu = $("#message-context-menu");

      // Show/Hide Edit based on 15 min rule, direction, and sender (only app users)
      var canEdit = false;
      if (
        direction === "out" &&
        type === "text" &&
        userId > 0 &&
        timestamp > 0
      ) {
        // Calculate current time synchronized with server
        var now = Math.floor(new Date().getTime() / 1000) + serverTimeOffset;
        var age = now - timestamp;

        // Debug log (F12 to see)
        console.log("🔍 Edit Debug:", {
          id: id,
          userId: userId,
          timestamp: timestamp,
          now: now,
          age: age,
          limit: 15 * 60 + 300, // 15 mins + 5 mins buffer
        });

        // WhatsApp limit is 15 minutes (900 seconds)
        // We use a 5-minute buffer (total 20 mins) to account for slight clock drift between UI and Gateway
        if (age < 15 * 60 + 300) {
          canEdit = true;
        }
      }

      if (canEdit) {
        $("#msg-menu-edit").removeClass("d-none");
        $("#msg-menu-edit-divider").removeClass("d-none");
      } else {
        $("#msg-menu-edit").addClass("d-none");
        $("#msg-menu-edit-divider").addClass("d-none");

        // If userId is missing but it's an outgoing text, it might be a caching issue
        // We'll log it for debugging
        if (direction === "out" && type === "text") {
          console.debug("🚫 Edit Disabled:", {
            userId: userId,
            timestamp: timestamp,
            age:
              Math.floor(new Date().getTime() / 1000) +
              serverTimeOffset -
              timestamp,
          });
        }
      }

      menu.css({
        display: "block",
        left: e.pageX,
        top: e.pageY,
      });

      // Set data for menu actions
      menu.data("id", id);
      menu.data("content", rawContent);
      menu.data("type", type);
    });

    // Hide message context menu on click elsewhere
    $(document).on("click", function () {
      $("#message-context-menu").hide();
    });

    // Message Context Menu Actions
    $(document).on("click", "#msg-menu-copy", function (e) {
      e.preventDefault();
      var content = $("#message-context-menu").data("content");
      if (content) {
        var cleanContent = getCleanMessageContent(content);

        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(cleanContent).then(function () {
            toastr.success("Pesan disalin");
          });
        } else {
          var textArea = document.createElement("textarea");
          textArea.value = cleanContent;
          document.body.appendChild(textArea);
          textArea.focus();
          textArea.select();
          try {
            document.execCommand("copy");
            toastr.success("Pesan disalin");
          } catch (err) {
            toastr.error("Gagal menyalin pesan");
          }
          document.body.removeChild(textArea);
        }
      }
      $("#message-context-menu").hide();
    });

    $(document).on("click", "#msg-menu-reply", function (e) {
      e.preventDefault();
      var content = $("#message-context-menu").data("content");
      if (content) {
        var replyType = $("#message-context-menu").data("type") || "text";
        var cleanContent = getCleanMessageContent(content);

        var wrapper = messageContextTarget.closest(".message-history-wrapper");
        var waMessageId = wrapper.attr("data-message-id-wa");
        var dbMessageId = wrapper.attr("data-message-id");

        // Only allow reply if we have WhatsApp message ID and it's not temporary
        var waMessageId = wrapper.attr("data-message-id-wa");
        if (
          !waMessageId ||
          waMessageId === "" ||
          waMessageId.startsWith("temp-")
        ) {
          toastr.warning(
            "Tidak dapat reply pesan ini. Pesan belum tersinkronisasi dengan WhatsApp.",
          );
          $("#message-context-menu").hide();
          return;
        }

        var id = waMessageId; // Use WhatsApp message ID for reply
        var senderName = "Customer";

        if (wrapper.hasClass("message-out")) {
          senderName = "Anda";
        } else {
          // Try to find sender name from label or context
          var senderLabel = wrapper.find(".message-sender");
          if (senderLabel.length) {
            senderName = senderLabel.text().trim();
          }
        }

        currentReplyMessageId = id;
        currentReplyContent = cleanContent;
        currentReplySenderName = senderName;

        $("#attachment-preview")
          .html(
            '<div class="d-flex align-items-center justify-content-between p-2">' +
              '<div class="border-start border-primary border-4 ps-3 overflow-hidden flex-grow-1">' +
              '<div class="fw-bold fs-7 text-primary">' +
              escapeHtml(senderName) +
              "</div>" +
              renderComposerReplyPreview(cleanContent, replyType, msg) +
              "</div>" +
              '<button type="button" class="btn btn-icon btn-sm" id="btn-cancel-reply"><i class="bi bi-x-lg"></i></button>' +
              "</div>",
          )
          .removeClass("d-none");

        $("#message-input").focus();
      }
      $("#message-context-menu").hide();
    });

    $(document).on("click", "#msg-menu-forward", function (e) {
      e.preventDefault();

      var id = $("#message-context-menu").data("id");
      var content = $("#message-context-menu").data("content") || "";
      var type = $("#message-context-menu").data("type") || "text";

      if (!id) {
        toastr.error("Pesan tidak ditemukan");
        $("#message-context-menu").hide();
        return;
      }

      var wrapper = messageContextTarget.closest(".message-history-wrapper");
      var senderName = wrapper.hasClass("message-out") ? "Anda" : "Customer";
      var senderLabel = wrapper.find(".message-sender");
      if (!wrapper.hasClass("message-out") && senderLabel.length) {
        senderName = senderLabel.text().trim();
      }

      var cleanContent = getCleanMessageContent(content);
      var previewContent = cleanContent;

      if (type === "image") {
        previewContent = "Gambar" + (cleanContent.replace(/^\[IMAGE:[^\]]+\]\s*/, "") ? ": " + cleanContent.replace(/^\[IMAGE:[^\]]+\]\s*/, "") : "");
      } else if (type === "document") {
        var match = cleanContent.match(/^\[DOCUMENT:[^:]+:(.+)\]$/);
        previewContent = "Dokumen: " + (match ? match[1] : "File");
      }

      currentForwardMessage = {
        id: id,
        type: type,
        senderName: senderName,
        content: cleanContent,
      };

      $("#forward-preview-sender").text(senderName);
      $("#forward-preview-content").text(previewContent || "-");
      $("#forward-search").val("");
      $("#forward-target-list").html(
        '<div class="text-center text-muted py-5">Memuat chat aktif terbaru...</div>',
      );
      selectedForwardTargets = [];
      updateForwardSelectionUi();
      $("#modal-forward-message").modal("show");
      loadForwardTargets("");
      setTimeout(function () {
        $("#forward-search").trigger("focus");
      }, 200);

      $("#message-context-menu").hide();
    });

    $(document).on("click", "#msg-menu-edit", function (e) {
      e.preventDefault();
      var id = $("#message-context-menu").data("id");
      var content = $("#message-context-menu").data("content");

      if (id && content) {
        openEditMessageModal(id, content);
      }
      $("#message-context-menu").hide();
    });

    $("#forward-search").on(
      "input",
      debounce(function () {
        loadForwardTargets($(this).val().trim());
      }, 250),
    );

    $(document).on("click", ".forward-target-item", function () {
      if (!currentForwardMessage) {
        toastr.error("Tidak ada pesan yang dipilih untuk diteruskan");
        return;
      }

      var btn = $(this);
      var targetId = String(btn.data("id"));
      var targetType = String(btn.data("type") || "individual");
      var existingIndex = selectedForwardTargets.findIndex(function (target) {
        return String(target.id) === targetId && String(target.type) === targetType;
      });

      if (existingIndex >= 0) {
        selectedForwardTargets.splice(existingIndex, 1);
      } else {
        selectedForwardTargets.push({
          id: targetId,
          type: targetType,
          isCurrentChat: String(btn.attr("data-current-chat")) === "1",
        });
      }

      updateForwardSelectionUi();
    });

    $("#btn-forward-selected").on("click", function () {
      if (!currentForwardMessage || selectedForwardTargets.length === 0) {
        toastr.warning("Pilih minimal satu chat tujuan");
        return;
      }

      var btn = $(this);
      btn.prop("disabled", true);

      $.ajax({
        url: appUrl + "chat/forward_message",
        method: "POST",
        data: {
          source_message_id: currentForwardMessage.id,
          target_chat_ids: selectedForwardTargets.map(function (target) {
            return target.id;
          }),
          target_types: selectedForwardTargets.map(function (target) {
            return target.type;
          }),
        },
        success: function (response) {
          if (response.success) {
            toastr.success(response.message || "Pesan berhasil diteruskan");
            $("#modal-forward-message").modal("hide");

            if (Array.isArray(response.data)) {
              response.data.forEach(function (msg, index) {
                var target = selectedForwardTargets[index];
                if (target && target.isCurrentChat) {
                  appendMessage(msg);
                }
              });
            }

            loadCustomers($("#search-customers").val());

            if (response.failed_targets && response.failed_targets.length > 0) {
              toastr.warning(response.failed_targets.length + " target gagal dikirim");
            }
          } else {
            toastr.error(response.error || response.message || "Gagal meneruskan pesan");
          }
        },
        error: function (xhr) {
          var message = "Gagal meneruskan pesan";
          if (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
            message = xhr.responseJSON.error || xhr.responseJSON.message;
          }
          toastr.error(message);
        },
        complete: function () {
          btn.prop("disabled", false);
          updateForwardSelectionUi();
        },
      });
    });

    $("#modal-forward-message").on("hidden.bs.modal", function () {
      clearForwardContext();
    });

    // Handle Save Edit
    $(document).on("click", "#btn-save-message-edit", function () {
      var id = $("#modal-edit-message").data("id");
      var content = $("#edit-message-input").val().trim();

      if (!id || !content) return;

      var btn = $(this);
      var originalHtml = btn.html();
      btn
        .prop("disabled", true)
        .html('<span class="spinner-border spinner-border-sm"></span>');

      $.post(
        appUrl + "chat/edit_message",
        { id: id, content: content },
        function (response) {
          if (response.success) {
            toastr.success("Pesan diperbarui");
            $("#modal-edit-message").modal("hide");
          } else {
            toastr.error(response.error || "Gagal mengubah pesan");
          }
          btn.prop("disabled", false).html(originalHtml);
        },
      );
    });

    // Handle cancel reply
    $(document).on("click", "#btn-cancel-reply", function () {
      clearReplyContext();
    });

    // Handle Enter key in edit input
    $(document).on("keypress", "#edit-message-input", function (e) {
      if (e.which === 13) {
        $("#btn-save-message-edit").click();
      }
    });

    function openEditMessageModal(id, content) {
      var modal = $("#modal-edit-message");
      var previewBubbleContainer = $("#edit-message-preview-bubble");
      var input = $("#edit-message-input");

      var bubbleClone = messageContextTarget.clone();
      bubbleClone.find(".message-toggle-container").remove();

      previewBubbleContainer.html(bubbleClone);
      input.val(content);
      modal.data("id", id);

      modal.modal("show");

      modal.on("shown.bs.modal", function () {
        input.focus().select();
      });
    }
  };

  return {
    init: init,
  };
})();

// Initialize on DOM ready
KTUtil.onDOMContentLoaded(function () {
  CRMChat.init();
});

// Global functions for Chat UI interactions
window.formatText = function (wrapper) {
  const textarea = document.querySelector("#message-input");
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const text = textarea.value;

  let result = "";

  if (wrapper === "```") {
    result =
      text.substring(0, start) +
      wrapper +
      text.substring(start, end) +
      wrapper +
      text.substring(end);
  } else {
    result =
      text.substring(0, start) +
      wrapper +
      text.substring(start, end) +
      wrapper +
      text.substring(end);
  }

  textarea.value = result;
  textarea.focus();
  textarea.selectionStart = start + wrapper.length;
  textarea.selectionEnd = end + wrapper.length;

  // Trigger auto resize
  autoResize(textarea);
};

window.toggleEmojiPicker = function () {
  const picker = document.getElementById("emoji_picker");
  picker.classList.toggle("d-none");
};

window.insertEmoji = function (emoji) {
  const textarea = document.querySelector("#message-input");
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const text = textarea.value;

  textarea.value = text.substring(0, start) + emoji + text.substring(end);
  textarea.focus();

  // Move cursor after emoji
  const newCursorPos = start + emoji.length;
  textarea.selectionStart = newCursorPos;
  textarea.selectionEnd = newCursorPos;

  // Close picker
  document.getElementById("emoji_picker").classList.add("d-none");

  // Trigger auto resize
  autoResize(textarea);
};

window.autoResize = function (textarea) {
  textarea.style.height = "auto";
  textarea.style.height = Math.min(textarea.scrollHeight, 120) + "px";
};

window.startChatFromContact = function (contactId) {
  if (!contactId) return;
  // Redirect to chat with this number/waid
  window.location.href = appUrl + "chat?phone=" + contactId;
};

window.saveContact = function (name, phone) {
  if (!phone) return;
  window.open(
    appUrl +
      "admin/customers/create?name=" +
      decodeURIComponent(name) +
      "&wa_number=" +
      phone,
    "_blank",
  );
};

window.selectTemplate = function (element) {
  var content = $(element).data("content");
  var textarea = document.querySelector("#message-input");
  var text = textarea.value;

  // Insert content at cursor or append
  var start = textarea.selectionStart;
  var end = textarea.selectionEnd;

  textarea.value = text.substring(0, start) + content + text.substring(end);
  textarea.focus();

  // Move cursor after content
  var newCursorPos = start + content.length;
  textarea.selectionStart = newCursorPos;
  textarea.selectionEnd = newCursorPos;

  // Close sticky container
  $("#template-sticky-container").addClass("d-none");

  // Trigger auto resize
  autoResize(textarea);
};

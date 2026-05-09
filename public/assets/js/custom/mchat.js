/**
 * Mobile Chat JavaScript
 * Full-screen WhatsApp-style mobile interface
 */

(function() {
    'use strict';

    var isConversation = typeof customerUuid !== 'undefined';
    var pollInterval = null;
    var lastMessageDate = null;
    var renderedMessageIds = new Set();
    
    var selectedFile = null;
    var selectedFileType = null; // 'image' or 'document'

    // Initialize based on page type
    $(document).ready(function() {
        if (isConversation) {
            initConversation();
        } else {
            initContactList();
        }
    });

    // ========== CONTACT LIST ==========
    var currentFilter = 'all';
    var currentLabelId = '';
    var isSyncing = false;

    function initContactList() {
        loadContacts();
        loadLabels();
        startContactPolling();

        // Search with debounce
        var searchTimeout;
        $('#search-input').on('input', function() {
            clearTimeout(searchTimeout);
            var search = $(this).val();
            searchTimeout = setTimeout(function() {
                loadContacts();
            }, 300);
        });

        // Filter Chips
        $('.filter-chip[data-filter]').on('click', function() {
            $('.filter-chip[data-filter]').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            loadContacts();
        });

        // Dropdowns
        $('.dropdown-toggle').on('click', function(e) {
            e.stopPropagation();
            var menu = $(this).next('.dropdown-menu');
            $('.dropdown-menu').not(menu).removeClass('show');
            menu.toggleClass('show');
        });

        $(document).on('click', function() {
            $('.dropdown-menu').removeClass('show');
        });

        // Label Filter
        $('#label-dropdown').on('click', '.dropdown-item', function() {
            var value = $(this).data('value');
            var text = $(this).text();
            currentLabelId = value;
            
            var btn = $('#label-filter-btn');
            if (value) {
                btn.addClass('active').html(text + ' <i class="bi bi-x"></i>');
                btn.find('i').on('click', function(e) {
                    e.stopPropagation();
                    resetLabelFilter();
                });
            } else {
                resetLabelFilter();
            }
            loadContacts();
        });



        // Sync Button
        $('#btn-sync').on('click', function() {
            if (isSyncing) return;
            
            var btn = $(this);
            var icon = btn.find('i');
            
            if (confirm('Mulai sinkronisasi kontak WA?')) {
                isSyncing = true;
                // Stop polling during sync
                if (pollInterval) clearInterval(pollInterval);
                
                icon.removeClass('bi-arrow-repeat').addClass('spinner-border spinner-border-sm');
                
                performSync(0, function() {
                    isSyncing = false;
                    icon.removeClass('spinner-border spinner-border-sm').addClass('bi-arrow-repeat');
                    loadContacts();
                    startContactPolling(); // Resume polling
                    alert('Sinkronisasi selesai');
                });
            }
        });

        // Refresh button
        $('#btn-refresh').on('click', function() {
            loadContacts();
        });
    }

    function startContactPolling() {
        if (pollInterval) clearInterval(pollInterval);
        
        pollInterval = setInterval(function() {
            // Skip if syncing or user is searching/interacting (optional guard?)
            // For now just skip if syncing
            if (!isSyncing) {
                loadContacts(true); // silent = true
            }
        }, 5000);
    }

    function resetLabelFilter() {
        currentLabelId = '';
        $('#label-filter-btn').removeClass('active').html('Label <i class="bi bi-chevron-down"></i>');
    }



    function loadLabels() {
        $.get(appUrl + 'mchat/get_labels', function(response) {
            if (response.success && response.data) {
                var html = '<div class="dropdown-item" data-value="">Semua Label</div>';
                response.data.forEach(function(label) {
                    html += '<div class="dropdown-item" data-value="' + label.id + '">' + 
                        '<span style="color:' + label.color + '">●</span> ' + escapeHtml(label.name) + '</div>';
                });
                $('#label-dropdown').html(html);
            }
        });
    }



    function performSync(offset, callback) {
        $.post(appUrl + 'chat/sync_contacts', { offset: offset }, function(response) {
            if (response.success) {
                if (response.has_next && response.next_offset !== undefined) {
                    performSync(response.next_offset, callback);
                } else {
                    callback();
                }
            } else {
                alert(response.message || 'Gagal sinkronisasi');
                callback();
            }
        }).fail(function() {
            alert('Gagal koneksi server');
            callback();
        });
    }

    function loadContacts() {
        var search = $('#search-input').val();
        $('#contacts-list').html('<div class="loading-spinner"><div class="spinner"></div></div>');

        $.get(appUrl + 'mchat/customers', { 
            search: search || '',
            filter: currentFilter,
            label_id: currentLabelId
        }, function(response) {
            if (response.success && response.data) {
                renderContacts(response.data);
            } else {
                $('#contacts-list').html('<div class="empty-state"><i class="bi bi-chat-dots"></i><p>Tidak ada chat</p></div>');
            }
        }).fail(function() {
            $('#contacts-list').html('<div class="empty-state"><i class="bi bi-exclamation-circle"></i><p>Gagal memuat data</p></div>');
        });
    }

    function renderContacts(contacts, silent) {
        if (!contacts.length) {
            $('#contacts-list').html('<div class="empty-state"><i class="bi bi-chat-dots"></i><p>Tidak ada chat</p></div>');
            return;
        }

        // If completely empty (initial load or empty search), render full list first
        if (!silent && $('#contacts-list .contact-item').length === 0) {
             $('#contacts-list').empty(); // Remove spinners
        }

        // Remove empty state if bringing back items
        $('#contacts-list .empty-state').remove();

        var container = $('#contacts-list');
        var existingOrder = [];

        // Track seen UUIDs to remove deleted ones later
        var seenUuids = [];

        contacts.forEach(function(c, index) {
            seenUuids.push(c.uuid);
            var itemSelector = '#contact-' + c.uuid;
            var $item = $(itemSelector);
            
            var initial = (c.name || c.wa_number || '?').charAt(0).toUpperCase();
            var name = escapeHtml(c.name || c.wa_number || 'Unknown');
            var lastMsg = c.last_message ? escapeHtml(truncate(c.last_message.content || '', 40)) : '';
            var time = c.last_chat_at ? formatTime(c.last_chat_at) : '';
            var unread = c.unread_count || 0;
            var unreadClass = unread > 0 ? 'unread' : '';
            var badgeHtml = unread > 0 ? '<span class="contact-badge">' + unread + '</span>' : '';

            // Construct Inner HTML
            var innerHtml = 
                '<div class="contact-avatar">' + initial + '</div>' +
                '<div class="contact-content">' +
                    '<div class="contact-header">' +
                        '<span class="contact-name">' + name + '</span>' +
                        '<span class="contact-time ' + unreadClass + '">' + time + '</span>' +
                    '</div>' +
                    '<div class="contact-preview">' +
                        '<span class="contact-message">' + lastMsg + '</span>' +
                        badgeHtml +
                    '</div>' +
                '</div>';

            if ($item.length) {
                // Update existing
                // Check if content changed to avoid unnecessary repaint? 
                // For simplicity, just update inner HTML, it's cheap enough for individual rows
                // Or better: Checking specific parts could be more performant but complex
                
                // Compare critical parts to reduce paint
                var currentMsg = $item.find('.contact-message').text();
                var currentBadge = $item.find('.contact-badge').text() || 0;
                var currentTime = $item.find('.contact-time').text();
                
                // Only update if changed
                if(currentMsg !== c.last_message?.content || currentBadge != unread || currentTime !== time) {
                    $item.html(innerHtml);
                }
            } else {
                // Create new
                $item = $('<a>', {
                    'href': appUrl + 'mchat/conversation/' + c.uuid,
                    'class': 'contact-item',
                    'id': 'contact-' + c.uuid,
                    'html': innerHtml
                });
                container.append($item);
            }

            // Re-order if position doesn't match
            // We append to container in order. `append` moves existing element to end if it exists.
            // But doing `append` for every item is heavy.
            // Better: Check index
            var currentIndex = container.children('.contact-item').index($item);
            if (currentIndex !== index) {
                if (index === 0) {
                    container.prepend($item);
                } else {
                    $item.insertAfter(container.children('.contact-item').eq(index - 1));
                }
            }
        });

        // Remove items not in new list
        container.children('.contact-item').each(function() {
            var id = $(this).attr('id').replace('contact-', '');
            if (seenUuids.indexOf(id) === -1) {
                $(this).remove();
            }
        });
        
        // Show empty state if all removed
        if (contacts.length === 0 && container.children('.contact-item').length === 0) {
             $('#contacts-list').html('<div class="empty-state"><i class="bi bi-chat-dots"></i><p>Tidak ada chat</p></div>');
        }
    }

    // ========== CONVERSATION ==========
    function initConversation() {
        loadMessages();
        startPolling();
        initSendForm();
        initTextareaAutoResize();
        initAttachments();
        initEmojiPicker();
        initTemplates();
    }

    function loadMessages() {
        $.get(appUrl + 'mchat/messages/' + customerUuid, function(response) {
            if (response.success && response.data) {
                renderMessages(response.data, true);
            }
        });
    }

    function renderMessages(messages, scrollToBottom) {
        if (!messages.length) {
            $('#messages-container').html('<div class="empty-state"><i class="bi bi-chat-dots"></i><p>Belum ada pesan</p></div>');
            return;
        }

        var html = '';
        var lastDate = null;
        
        // Reset rendered IDs when full re-rendering
        renderedMessageIds.clear();

        messages.forEach(function(msg) {
            // Date separator
            var msgDate = msg.created_at ? msg.created_at.split(' ')[0] : null;
            if (msgDate && msgDate !== lastDate) {
                html += '<div class="date-separator"><span>' + formatDate(msgDate) + '</span></div>';
                lastDate = msgDate;
            }

            var direction = msg.direction === 'out' ? 'out' : 'in';
            var time = msg.created_at ? formatMessageTime(msg.created_at) : '';
            var status = '';
            if (direction === 'out') {
                if (msg.status === 'read') {
                    status = '<span class="message-status read"><i class="bi bi-check2-all"></i></span>';
                } else if (msg.status === 'delivered') {
                    status = '<span class="message-status"><i class="bi bi-check2-all"></i></span>';
                } else {
                    status = '<span class="message-status"><i class="bi bi-check2"></i></span>';
                }
            }

            var content = parseMessageContent(msg.content, msg.type, msg);
            
            html += '<div class="message-wrapper ' + direction + '">' +
                '<div class="message-bubble">' +
                    '<div class="message-text">' + content + '</div>' +
                    '<div class="message-meta">' +
                        '<span class="message-time">' + time + '</span>' +
                        status +
                    '</div>' +
                '</div>' +
            '</div>';

            // Track last message date for polling
            if (msg.created_at && (!lastMessageDate || msg.created_at > lastMessageDate)) {
                lastMessageDate = msg.created_at;
            }
        });

        $('#messages-container').html(html);

        if (scrollToBottom) {
            scrollToBottomMessages();
        }
    }

    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        
        pollInterval = setInterval(function() {
            $.get(appUrl + 'mchat/messages/' + customerUuid, { after_date: lastMessageDate }, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    // Append new messages
                    response.data.forEach(function(msg) {
                        appendMessage(msg);
                        if (msg.created_at && (!lastMessageDate || msg.created_at > lastMessageDate)) {
                            lastMessageDate = msg.created_at;
                        }
                    });
                }
            });
        }, 3000);
    }

    function appendMessage(msg) {
        // Deduplication based on message_id if available
        if (msg.id && renderedMessageIds.has(msg.id)) return;
        if (msg.id) renderedMessageIds.add(msg.id);

        // Remove empty state if exists
        $('#messages-container .empty-state').remove();

        var direction = msg.direction === 'out' ? 'out' : 'in';
        var time = msg.created_at ? formatMessageTime(msg.created_at) : '';
        var status = '';
        if (direction === 'out') {
            if (msg.status === 'read') {
                status = '<span class="message-status read"><i class="bi bi-check2-all"></i></span>';
            } else if (msg.status === 'delivered') {
                status = '<span class="message-status"><i class="bi bi-check2-all"></i></span>';
            } else {
                status = '<span class="message-status"><i class="bi bi-check2"></i></span>';
            }
        }

        var content = parseMessageContent(msg.content, msg.type, msg);

        var html = '<div class="message-wrapper ' + direction + '" ' + (msg.id ? 'id="msg-obj-' + msg.id + '"' : '') + '>' +
            '<div class="message-bubble">' +
                '<div class="message-text">' + content + '</div>' +
                '<div class="message-meta">' +
                    '<span class="message-time">' + time + '</span>' +
                    status +
                '</div>' +
            '</div>' +
        '</div>';

        $('#messages-container').append(html);
        scrollToBottomMessages();
    }

    function parseMessageContent(body, type, message) {
        if (!body) return '';
        
        var fallbackHtml = '<div class="p-2 bg-light-warning rounded text-warning fs-7 d-flex align-items-center"><i class="bi bi-phone me-2 fs-5"></i>Media tidak dapat dilihat di aplikasi.</div>';

        // Handle Location
        if (type === 'location' || body.indexOf('[LOCATION:') !== -1) {
            var match = body.match(/\[LOCATION:([-.\d]+),([-.\d]+)\]/);
            if (match) {
                var lat = match[1];
                var lng = match[2];
                var mapUrl = 'https://www.google.com/maps?q=' + lat + ',' + lng;
                return '<div class="message-location">' +
                    '<div class="d-flex align-items-center mb-2">' +
                        '<i class="bi bi-geo-alt-fill text-danger fs-3 me-2"></i>' +
                        '<span class="fw-bold">Lokasi Terkirim</span>' +
                    '</div>' +
                    '<a href="' + mapUrl + '" target="_blank" class="btn btn-sm btn-primary w-100">Buka Maps</a>' +
                '</div>';
            }
        }

        // Handle Contact (Backward compatible)
        if (body.indexOf('[CONTACT') !== -1) {
            var contactRegex = /\[CONTACT[:|]([^:|]*)[:|]([^:|]*)[:|]([^\]]*)\]/g;
            var contactHtml = '';
            var match;
            
            while ((match = contactRegex.exec(body)) !== null) {
                var name = match[1].trim();
                var waid = match[2].trim();
                var phone = match[3].trim();
                var contactId = waid || phone.replace(/\D/g, '');
                
                contactHtml += '<div class="message-contact mb-2">' +
                    '<div class="d-flex align-items-center mb-3">' +
                        '<div class="symbol symbol-40px me-3">' +
                            '<div class="symbol-label bg-light-primary text-primary"><i class="bi bi-person-fill fs-3"></i></div>' +
                        '</div>' +
                        '<div class="d-flex flex-column overflow-hidden">' +
                            '<div class="fw-bold fs-6 text-gray-800 text-truncate">' + name + '</div>' +
                            '<div class="text-muted fs-7">' + (phone || waid) + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="d-flex gap-2">' +
                        '<button onclick="startChatFromContact(\'' + contactId + '\')" class="btn btn-sm btn-light-success flex-grow-1">Chat</button>' +
                        '<button onclick="saveContact(\'' + encodeURIComponent(name) + '\', \'' + contactId + '\')" class="btn btn-sm btn-light-primary flex-grow-1">Simpan</button>' +
                    '</div>' +
                '</div>';
            }
            if (contactHtml) return contactHtml;
        }

        // Handle Image
        if (type === 'image' || body.indexOf('[IMAGE:') !== -1) {
            var match = body.match(/\[IMAGE:([^\]]+)\]/);
            if (match) {
                var url = resolvePreferredMediaUrl(message, match[1]);
                var caption = body.replace(match[0], '').trim();
                return '<img src="' + url + '" class="message-image" onclick="window.open(\'' + url + '\')">' + 
                       (caption ? '<div class="mt-1">' + formatWhatsAppText(escapeHtml(caption)) + '</div>' : '');
            }
        }
        
        // Handle Video
        if (type === 'video' || body.indexOf('[VIDEO:') !== -1) {
            var match = body.match(/\[VIDEO:([^\]]+)\]/);
            if (match) {
                var url = resolvePreferredMediaUrl(message, match[1]);
                var caption = body.replace(match[0], '').trim();
                return '<video src="' + url + '" class="w-100 rounded" controls style="max-height:300px"></video>' + 
                       (caption ? '<div class="mt-1">' + formatWhatsAppText(escapeHtml(caption)) + '</div>' : '');
            }
        }
        
        // Handle Audio / Voice Note (PTT)
        if (type === 'audio' || body.indexOf('[AUDIO:') !== -1 || body.indexOf('[PTT:') !== -1) {
            var match = body.match(/\[(AUDIO|PTT):([^\]]+)\]/);
            if (match) {
                var isPtt = match[1] === 'PTT';
                var url = resolvePreferredMediaUrl(message, match[2]);
                if (!isValidMediaUrl(url)) return fallbackHtml;

                return '<div class="message-audio ' + (isPtt ? 'voice-note' : '') + '">' +
                            (isPtt ? '<div class="d-flex align-items-center mb-1 text-primary"><i class="bi bi-mic-fill me-2"></i><span class="fs-7 fw-bold">Pesan Suara</span></div>' : '') +
                            '<audio controls class="w-100">' +
                                '<source src="' + url + '" type="audio/mpeg">' +
                            '</audio>' +
                        '</div>';
            }
        }
        
        // Handle Sticker
        if (body.indexOf('[STICKER:') !== -1) {
            var match = body.match(/\[STICKER:([^\]]+)\]/);
            if (match) {
                var url = resolvePreferredMediaUrl(message, match[1]);
                if (!isValidMediaUrl(url)) return fallbackHtml;

                return '<div class="message-sticker"><img src="' + url + '" class="rounded" style="max-width:120px;" onclick="window.open(\'' + url + '\')"></div>';
            }
        }
        
        // Handle Document
        if (type === 'document' || body.indexOf('[DOCUMENT:') !== -1) {
            var match = body.match(/\[DOCUMENT:(.+):([^:]+)\]/);
            if (match) {
                var url = resolvePreferredMediaUrl(message, match[1]);
                var filename = match[2];
                var ext = filename.split('.').pop().toUpperCase();
                return '<div class="message-document">' +
                    '<i class="bi bi-file-earmark-text fs-1 text-gray-600"></i>' +
                    '<div class="message-document-info ms-2">' +
                        '<div class="message-document-name">' + escapeHtml(filename) + '</div>' +
                        '<div class="message-document-size">' + ext + ' File</div>' +
                    '</div>' +
                    '<a href="' + url + '" target="_blank" class="ms-2"><i class="bi bi-download"></i></a>' +
                '</div>';
            }
        }
        
        return formatWhatsAppText(escapeHtml(body));
    }

    function formatWhatsAppText(text) {
        if (!text) return '';
        text = text.replace(/\*([^*]+)\*/g, '<b>$1</b>');
        text = text.replace(/\_([^_]+)\_/g, '<i>$1</i>');
        text = text.replace(/\~([^~]+)\~/g, '<s>$1</s>');
        text = text.replace(/\`\`\`([^`]+)\`\`\`/g, '<code>$1</code>');
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    function parseMediaMeta(mediaMeta) {
        if (!mediaMeta) return {};
        if (typeof mediaMeta === 'object') return mediaMeta;

        try {
            var parsed = JSON.parse(mediaMeta);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function resolvePreferredMediaUrl(message, fallbackUrl) {
        var mediaMeta = parseMediaMeta(message && message.media_meta);
        if (mediaMeta.cloudinary) return mediaMeta.cloudinary;
        if (mediaMeta.minio) return mediaMeta.minio;
        if (mediaMeta.wag) return mediaMeta.wag;
        if (message && message.media_url) return message.media_url;
        return fallbackUrl || '';
    }

    function initAttachments() {
        $('#btn-attach').on('click', function(e) {
            e.stopPropagation();
            $('#attachment-menu').toggleClass('mchat-visible-grid');
            $('#emoji-picker').removeClass('mchat-visible');
        });

        $(document).on('click', function() {
            $('#attachment-menu').removeClass('mchat-visible-grid');
        });

        $('#attach-image').on('click', function() {
            $('#input-image').click();
        });

        $('#attach-document').on('click', function() {
            $('#input-document').click();
        });

        $('#attach-template').on('click', function() {
            $('#attachment-menu').removeClass('mchat-visible-grid');
            $('#template-drawer').addClass('mchat-visible');
            loadTemplates();
        });

        $('#input-image').on('change', function() {
            if (this.files && this.files[0]) {
                selectedFile = this.files[0];
                selectedFileType = 'image';
                triggerImagePreview();
            }
        });

        $('#input-document').on('change', function() {
            if (this.files && this.files[0]) {
                selectedFile = this.files[0];
                selectedFileType = 'document';
                sendFile(); // Direct send for documents
            }
        });

        $('#close-preview').on('click', function() {
            $('#preview-modal').removeClass('mchat-visible');
            selectedFile = null;
        });

        $('#btn-send-image').on('click', function() {
            sendFile();
        });
    }

    function triggerImagePreview() {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#image-preview-src').attr('src', e.target.result);
            $('#image-caption').val($('#message-input').val());
            $('#preview-modal').addClass('mchat-visible');
        }
        reader.readAsDataURL(selectedFile);
    }

    function sendFile() {
        var formData = new FormData();
        formData.append('uuid', customerUuid);
        
        var tempId = 'up-' + Date.now();
        var time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

        if (selectedFileType === 'image') {
            var caption = $('#image-caption').val();
            formData.append('image', selectedFile);
            formData.append('caption', caption);
            $('#preview-modal').removeClass('mchat-visible');
            
            // Show temp preview
            var imgUrl = URL.createObjectURL(selectedFile);
            var html = '<div class="message-wrapper out" id="' + tempId + '">' +
                '<div class="message-bubble">' +
                    '<div class="position-relative">' +
                        '<img src="' + imgUrl + '" class="message-image" style="opacity:0.6">' +
                        '<div class="upload-overlay position-absolute top-50 start-50 translate-middle text-center">' +
                            '<div class="spinner-border text-light"></div>' +
                            '<div class="upload-status-text">0%</div>' +
                        '</div>' +
                    '</div>' +
                    (caption ? '<div class="mt-1">' + escapeHtml(caption) + '</div>' : '') +
                    '<div class="message-meta"><span class="message-time">' + time + ' <i class="bi bi-clock"></i></span></div>' +
                '</div>' +
            '</div>';
            $('#messages-container').append(html);
        } else {
            formData.append('document', selectedFile);
            var ext = selectedFile.name.split('.').pop().toUpperCase();
            var html = '<div class="message-wrapper out" id="' + tempId + '">' +
                '<div class="message-bubble">' +
                    '<div class="message-document" style="opacity:0.6">' +
                        '<i class="bi bi-file-earmark-text fs-1"></i>' +
                        '<div class="message-document-info ms-2">' +
                            '<div class="message-document-name">' + escapeHtml(selectedFile.name) + '</div>' +
                            '<div class="message-document-size">Mengirim...</div>' +
                        '</div>' +
                        '<div class="spinner-border spinner-border-sm text-primary"></div>' +
                    '</div>' +
                    '<div class="message-meta"><span class="message-time">' + time + ' <i class="bi bi-clock"></i></span></div>' +
                '</div>' +
            '</div>';
            $('#messages-container').append(html);
        }

        scrollToBottomMessages();
        
        var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percent = Math.round((e.loaded / e.total) * 100);
                $('#' + tempId + ' .upload-status-text').text(percent + '%');
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $('#' + tempId).remove();
                    appendMessage(response.data);
                } else {
                    alert(response.error || 'Gagal mengirim file');
                    $('#' + tempId).remove();
                }
                selectedFile = null;
            }
        };

        xhr.open('POST', appUrl + 'mchat/send_' + selectedFileType);
        xhr.send(formData);
    }

    function initEmojiPicker() {
        $('#btn-emoji-toggle').on('click', function(e) {
            e.stopPropagation();
            $('#emoji-picker').toggleClass('mchat-visible');
            $('#attachment-menu').removeClass('mchat-visible-grid');
            
            if ($('#emoji-picker').hasClass('mchat-visible')) {
                $('#messages-container').css('padding-bottom', '300px');
                scrollToBottomMessages();
            } else {
                $('#messages-container').css('padding-bottom', '12px');
            }
        });

        $('.emoji-item').on('click', function() {
            var emoji = $(this).text();
            var input = $('#message-input');
            input.val(input.val() + emoji).trigger('input').focus();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#emoji-picker, #btn-emoji-toggle').length) {
                $('#emoji-picker').removeClass('mchat-visible');
                $('#messages-container').css('padding-bottom', '12px');
            }
        });
    }

    function initTemplates() {
        $('#close-templates').on('click', function() {
            $('#template-drawer').removeClass('mchat-visible');
        });

        $(document).on('click', '.template-item', function() {
            var content = $(this).find('.template-content-raw').val();
            $('#message-input').val(content).trigger('input').focus();
            $('#template-drawer').removeClass('mchat-visible');
        });
    }

    function loadTemplates() {
        $.get(appUrl + 'mchat/get_templates', function(response) {
            if (response.success && response.data) {
                var html = '';
                response.data.forEach(function(tpl) {
                    html += '<div class="template-item">' +
                        '<div class="template-title">' + escapeHtml(tpl.title) + '</div>' +
                        '<div class="template-content">' + escapeHtml(tpl.content) + '</div>' +
                        '<input type="hidden" class="template-content-raw" value="' + escapeHtml(tpl.content) + '">' +
                    '</div>';
                });
                $('#template-list').html(html || '<div class="text-center py-5">Tidak ada template</div>');
            }
        });
    }

    function initSendForm() {
        $('#send-form').on('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    function sendMessage() {
        var message = $('#message-input').val().trim();
        if (!message) return;

        var btn = $('#btn-send');
        btn.prop('disabled', true);

        $.post(appUrl + 'mchat/send', {
            uuid: customerUuid,
            message: message
        }, function(response) {
            if (response.success && response.data) {
                $('#message-input').val('').trigger('input');
                // Append message from server data to ensure ID exists
                appendMessage(response.data);
                
                // Update lastMessageDate so next polling doesn't fetch this same message
                if (response.data.created_at && (!lastMessageDate || response.data.created_at > lastMessageDate)) {
                    lastMessageDate = response.data.created_at;
                }
            } else {
                alert(response.error || 'Gagal mengirim pesan');
            }
        }).fail(function() {
            alert('Gagal mengirim pesan');
        }).always(function() {
            btn.prop('disabled', false);
        });
    }

    function initTextareaAutoResize() {
        var textarea = document.getElementById('message-input');
        if (!textarea) return;

        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }

    function scrollToBottomMessages() {
        var container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // ========== HELPERS ==========
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function truncate(str, len) {
        if (!str) return '';
        return str.length > len ? str.substring(0, len) + '...' : str;
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        var date = new Date(dateStr);
        var now = new Date();
        var diff = now - date;
        var days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) {
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        } else if (days === 1) {
            return 'Kemarin';
        } else if (days < 7) {
            return ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][date.getDay()];
        } else {
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: '2-digit' });
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var date = new Date(dateStr);
        var now = new Date();
        var diff = Math.floor((now - date) / (1000 * 60 * 60 * 24));

        if (diff === 0) return 'Hari ini';
        if (diff === 1) return 'Kemarin';
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function formatMessageTime(dateStr) {
        if (!dateStr) return '';
        var date = new Date(dateStr);
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

})();

window.startChatFromContact = function(contactId) {
    if (!contactId) return;
    window.location.href = appUrl + 'mchat?phone=' + contactId;
};

window.saveContact = function(name, phone) {
    if (!phone) return;
    window.open(appUrl + 'admin/customers/create?name=' + decodeURIComponent(name) + '&wa_number=' + phone, '_blank');
};

$(document).ready(function() {
    MChat.init();
});

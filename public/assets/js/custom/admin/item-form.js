"use strict";

// Materials/Applications form handler
var ItemForm = function() {
    var selectedFiles = [];
    
    // Init sortable images
    var initSortableImages = function() {
        var container = document.getElementById('existing-images');
        if (container && typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 150,
                ghostClass: 'bg-light-primary',
                onEnd: function() {
                    saveImageOrder(module);
                }
            });
        }
        
        // Also make preview sortable
        var previewContainer = document.getElementById('image-preview');
        if (previewContainer && typeof Sortable !== 'undefined') {
            new Sortable(previewContainer, {
                animation: 150,
                ghostClass: 'bg-light-primary'
            });
        }
    };

    // Save image order
    var saveImageOrder = function(module) {
        var order = [];
        $('#existing-images .image-item').each(function(index) {
            order.push({
                id: $(this).data('id'),
                order: index
            });
        });

        // Get ID from hidden input
        var itemId = $('input[name="id"]').val();

        if (order.length && itemId) {
            $.ajax({
                url: appUrl + 'admin/' + module + '/reorder_images',
                type: 'POST',
                data: { images: order, item_id: itemId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('Order saved');
                    }
                }
            });
        }
    };

    // Image preview before upload with delete
    var initImagePreview = function() {
        var fileInput = $('input[name="images[]"]');
        
        fileInput.on('change', function(e) {
            var container = $('#image-preview');
            container.empty();
            selectedFiles = Array.from(e.target.files);
            
            selectedFiles.forEach(function(file, index) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var html = '<div class="col-4 mb-2 preview-item" data-index="' + index + '" style="cursor: grab;">' +
                        '<div class="position-relative">' +
                        '<img src="' + e.target.result + '" class="rounded w-100" style="height: 80px; object-fit: cover;">' +
                        '<button type="button" class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 btn-remove-preview" data-index="' + index + '">' +
                        '<i class="ki-outline ki-cross fs-5"></i>' +
                        '</button>' +
                        '</div>' +
                        '</div>';
                    container.append(html);
                };
                reader.readAsDataURL(file);
            });
        });
        
        // Remove preview image
        $(document).on('click', '.btn-remove-preview', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            $(this).closest('.preview-item').remove();
            
            // Update file input
            selectedFiles.splice(index, 1);
            updateFileInput();
        });
    };
    
    // Update file input with remaining files
    var updateFileInput = function() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function(file) {
            dt.items.add(file);
        });
        $('input[name="images[]"]')[0].files = dt.files;
    };

    // Delete existing image handler
    var initDeleteImage = function(module) {
        $(document).on('click', '.btn-delete-image', function(e) {
            e.preventDefault();
            var btn = $(this);
            var imageId = btn.data('id');
            var imageItem = btn.closest('.image-item');

            Swal.fire({
                title: 'Hapus gambar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: appUrl + 'admin/' + module + '/delete_image/' + imageId,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                imageItem.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }
                        }
                    });
                }
            });
        });
    };

    // AI Generate handler
    var initAiGenerate = function() {
        $(document).on('click', '.btn-ai-generate', function() {
            var btn = $(this);
            var target = btn.data('target');
            var type = btn.data('type') || 'product';
            var name = $('#item_name').val() || $('input[name="name"]').val();
            
            if (!name) {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Isi nama terlebih dahulu',
                    icon: 'warning'
                });
                return;
            }
            
            var originalHtml = btn.html();
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: appUrl + 'admin/ai/generate',
                type: 'POST',
                data: { name: name, type: type },
                dataType: 'json',
                success: function(response) {
                    btn.attr('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        $('#' + target).val(response.description);
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: response.message || 'Gagal generate deskripsi',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    btn.attr('disabled', false).html(originalHtml);
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan jaringan',
                        icon: 'error'
                    });
                }
            });
        });
    };

    // AI Image Generate handler
    var initAiImageGenerate = function(module) {
        $(document).on('click', '.btn-ai-image-generate', function() {
            var btn = $(this);
            var type = btn.data('type') || (module === 'materials' ? 'material' : 'application');
            var name = $('#item_name').val() || $('input[name="name"]').val();
            
            if (!name) {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Isi nama terlebih dahulu',
                    icon: 'warning'
                });
                return;
            }
            
            var originalHtml = btn.html();
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');
            
            $.ajax({
                url: appUrl + 'admin/ai/generate_image',
                type: 'POST',
                data: { name: name, type: type },
                dataType: 'json',
                timeout: 60000,
                success: function(response) {
                    btn.attr('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'Gambar Berhasil Digenerate!',
                            html: '<img src="' + response.image_url + '" class="img-fluid rounded mb-3" style="max-height: 300px;">' +
                                  '<p class="text-muted">Gambar tersimpan di: ' + response.image_path + '</p>' +
                                  '<p>Klik <strong>Gunakan</strong> untuk menambahkan ke daftar, atau <strong>Download</strong> untuk simpan manual.</p>',
                            icon: 'success',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Gunakan',
                            denyButtonText: 'Download',
                            cancelButtonText: 'Tutup'
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                // Add image preview to form
                                var html = '<div class="col-4 mb-2 preview-item ai-generated" style="cursor: grab;">' +
                                    '<div class="position-relative">' +
                                    '<img src="' + response.image_url + '" class="rounded w-100" style="height: 80px; object-fit: cover;">' +
                                    '<input type="hidden" name="ai_images[]" value="' + response.image_path + '">' +
                                    '<button type="button" class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 btn-remove-ai-preview">' +
                                    '<i class="ki-outline ki-cross fs-5"></i>' +
                                    '</button>' +
                                    '</div>' +
                                    '</div>';
                                $('#image-preview').append(html);
                                
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Gambar ditambahkan. Jangan lupa simpan data.',
                                    icon: 'success',
                                    timer: 2000
                                });
                            } else if (result.isDenied) {
                                // Download image
                                var link = document.createElement('a');
                                link.href = response.image_url;
                                link.download = 'ai_generated_' + name.replace(/\s+/g, '_') + '.png';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: response.message || 'Gagal generate gambar',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    btn.attr('disabled', false).html(originalHtml);
                    var message = 'Terjadi kesalahan jaringan';
                    if (status === 'timeout') {
                        message = 'Request timeout. Proses generate gambar membutuhkan waktu lebih lama.';
                    }
                    Swal.fire({
                        title: 'Error',
                        text: message,
                        icon: 'error'
                    });
                }
            });
        });
        
        // Remove AI generated image preview (already handled above in initAiGenerate but safe to keep or rely on shared class)
    };

    return {
        init: function(module) {
            initSortableImages();
            initImagePreview();
            initDeleteImage(module);
            initAiGenerate();
            initAiImageGenerate(module);
        }
    };
}();

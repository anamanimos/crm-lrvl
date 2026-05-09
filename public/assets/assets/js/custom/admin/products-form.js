"use strict";

// Product form handler
var ProductForm = function() {
    var productId = document.getElementById('product_id') ? document.getElementById('product_id').value : '';
    var selectedFiles = [];

    // Init image input
    var initImageInput = function() {
        var imageInputs = document.querySelectorAll('[data-kt-image-input="true"]');
        imageInputs.forEach(function(el) {
            new KTImageInput(el);
        });
    };

    // Init sortable images
    var initSortableImages = function() {
        var container = document.getElementById('existing-images');
        if (container && typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 150,
                ghostClass: 'bg-light-primary',
                onEnd: function(evt) {
                    saveImageOrder();
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
    var saveImageOrder = function() {
        var order = [];
        $('#existing-images .image-item').each(function(index) {
            order.push({
                id: $(this).data('id'),
                order: index
            });
        });

        if (order.length && productId) {
            $.ajax({
                url: appUrl + 'admin/products/reorder_images',
                type: 'POST',
                data: { images: order, product_id: productId },
                dataType: 'json'
            });
        }
    };

    // Image preview before upload
    var initImagePreview = function() {
        $('input[name="images[]"]').on('change', function(e) {
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

    // Delete image handler
    var initDeleteImage = function() {
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
                        url: appUrl + 'admin/products/delete_image/' + imageId,
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
            var name = $('#product_name').val() || $('input[name="name"]').val();
            
            if (!name) {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Isi nama produk terlebih dahulu',
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
                        // If target is description and Quill editor is available, use it
                        if (target === 'description' && typeof Quill !== 'undefined' && ProductForm.getEditor()) {
                            var editor = ProductForm.getEditor();
                            editor.clipboard.dangerouslyPasteHTML(response.description);
                            $('#description').val(editor.root.innerHTML);
                        } else {
                            $('#' + target).val(response.description);
                        }
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
    var initAiImageGenerate = function() {
        $('#btn-generate-image').on('click', function() {
            var btn = $(this);
            var name = $('#product_name').val() || $('input[name="name"]').val();
            
            if (!name) {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Isi nama produk terlebih dahulu',
                    icon: 'warning'
                });
                return;
            }
            
            var originalHtml = btn.html();
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');
            
            $.ajax({
                url: appUrl + 'admin/ai/generate_image',
                type: 'POST',
                data: { name: name, type: 'product' },
                dataType: 'json',
                timeout: 60000, // 60 seconds timeout for image generation
                success: function(response) {
                    btn.attr('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'Gambar Berhasil Digenerate!',
                            html: '<img src="' + response.image_url + '" class="img-fluid rounded mb-3" style="max-height: 300px;">' +
                                  '<p class="text-muted">Gambar tersimpan di: ' + response.image_path + '</p>' +
                                  '<p>Klik <strong>Gunakan</strong> untuk menambahkan ke produk, atau <strong>Download</strong> untuk simpan manual.</p>',
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
                                    text: 'Gambar ditambahkan ke daftar. Jangan lupa simpan produk.',
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
        
        // Remove AI generated image preview
        $(document).on('click', '.btn-remove-ai-preview', function(e) {
            e.preventDefault();
            $(this).closest('.preview-item').remove();
        });
    };

    // Custom tabs handler
    var initCustomTabs = function() {
        // Add new tab
        $('#btn-add-tab').on('click', function() {
            var template = `
                <div class="custom-tab-item border rounded p-4 mb-3" data-id="">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control tab-name" placeholder="Nama Tab">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select tab-type">
                                <option value="text">Teks</option>
                                <option value="table">Tabel</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-sm btn-success btn-save-tab me-1">
                                <i class="ki-outline ki-check fs-4"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light-danger btn-delete-tab">
                                <i class="ki-outline ki-trash fs-4"></i>
                            </button>
                        </div>
                    </div>
                    <textarea class="form-control tab-content" rows="4" placeholder="Konten tab..."></textarea>
                </div>
            `;
            $('#custom-tabs-container').append(template);
        });

        // Save tab
        $(document).on('click', '.btn-save-tab', function() {
            var item = $(this).closest('.custom-tab-item');
            var data = {
                id: item.data('id'),
                product_id: productId,
                tab_name: item.find('.tab-name').val(),
                tab_type: item.find('.tab-type').val(),
                content: item.find('.tab-content').val()
            };

            $.ajax({
                url: appUrl + 'admin/products/save_custom_tab',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        item.data('id', response.id);
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Tab berhasil disimpan',
                            icon: 'success',
                            timer: 1500
                        });
                    }
                }
            });
        });

        // Delete tab
        $(document).on('click', '.btn-delete-tab', function() {
            var item = $(this).closest('.custom-tab-item');
            var tabId = item.data('id');

            if (tabId) {
                Swal.fire({
                    title: 'Hapus tab?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: appUrl + 'admin/products/delete_custom_tab/' + tabId,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    item.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                }
                            }
                        });
                    }
                });
            } else {
                item.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    };

    // Quill editor instance
    var quillEditor = null;

    // Init Quill editor
    var initQuill = function() {
        var editorContainer = document.getElementById('description_editor');
        if (!editorContainer || typeof Quill === 'undefined') return;

        quillEditor = new Quill('#description_editor', {
            theme: 'snow',
            placeholder: 'Tulis deskripsi produk...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Sync content to hidden textarea on change
        quillEditor.on('text-change', function() {
            $('#description').val(quillEditor.root.innerHTML);
        });
    };

    // Sync Quill content to form before submit
    var initFormSync = function() {
        $('#product-form').on('submit', function() {
            if (quillEditor) {
                $('#description').val(quillEditor.root.innerHTML);
            }
        });
    };

    return {
        init: function() {
            initImageInput();
            initSortableImages();
            initImagePreview();
            initDeleteImage();
            initAiGenerate();
            initAiImageGenerate();
            initCustomTabs();
            initQuill();
            initFormSync();
        },
        
        // Expose editor for external use (AI Generate)
        getEditor: function() {
            return quillEditor;
        }
    };
}();

// Init
KTUtil.onDOMContentLoaded(function() {
    ProductForm.init();
});

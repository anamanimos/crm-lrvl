"use strict";

// Access keys management
var AccessKeys = function() {
    var table;

    var initDatatable = function() {
        // Check if DataTable is available
        if (typeof $.fn.DataTable === 'undefined') {
            console.log('DataTable not available, skipping initialization');
            return;
        }
        
        table = $('#kt_datatable').DataTable({
            "info": false,
            "order": [[4, 'desc']],
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data tidak ditemukan",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    };

    var initCopyButton = function() {
        $(document).on('click', '.btn-copy', function() {
            var link = $(this).data('link');
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(function() {
                    Swal.fire({
                        title: 'Tersalin!',
                        text: 'Link berhasil disalin ke clipboard',
                        icon: 'success',
                        timer: 1500
                    });
                });
            } else {
                // Fallback for non-secure context
                var textArea = document.createElement("textarea");
                textArea.value = link;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        title: 'Tersalin!',
                        text: 'Link berhasil disalin ke clipboard',
                        icon: 'success',
                        timer: 1500
                    });
                } catch (err) {
                    Swal.fire({
                        title: 'Gagal',
                        text: 'Gagal menyalin link',
                        icon: 'error'
                    });
                }
                
                document.body.removeChild(textArea);
            }
        });

        $(document).on('click', '.btn-copy-generated', function() {
            var link = $('#generatedLink').val();
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(function() {
                    Swal.fire({
                        title: 'Tersalin!',
                        text: 'Link berhasil disalin ke clipboard',
                        icon: 'success',
                        timer: 1500
                    });
                });
            } else {
                // Fallback for non-secure context
                var textArea = document.createElement("textarea");
                textArea.value = link;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        title: 'Tersalin!',
                        text: 'Link berhasil disalin ke clipboard',
                        icon: 'success',
                        timer: 1500
                    });
                } catch (err) {
                    Swal.fire({
                        title: 'Gagal',
                        text: 'Gagal menyalin link',
                        icon: 'error'
                    });
                }
                
                document.body.removeChild(textArea);
            }
        });
    };

    var initCreateKey = function() {
        $('#btnCreateKey').on('click', function() {
            var btn = $(this);
            var form = $('#createKeyForm');

            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Membuat...');

            $.ajax({
                url: appUrl + 'admin/access/create',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.attr('disabled', false).html('Buat Link');
                    
                    if (response.success) {
                        form.hide();
                        $('#generatedLink').val(response.link);
                        $('.btn-send-wa-generated').attr('data-id', response.key.id); // Add ID to button
                        $('#generatedLinkContainer').removeClass('d-none');
                        btn.hide();
                    } else {
                        Swal.fire({
                            title: 'Validasi Gagal!',
                            text: response.message,
                            icon: 'warning'
                        });
                    }
                },
                error: function() {
                    btn.attr('disabled', false).html('Buat Link');
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat membuat link',
                        icon: 'error'
                    });
                }
            });
        });

        // Reset modal on close
        $('#createKeyModal').on('hidden.bs.modal', function() {
            $('#createKeyForm').show().trigger('reset');
            $('#generatedLinkContainer').addClass('d-none');
            $('#btnCreateKey').show();
        });
    };

    var initDelete = function() {
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');

            Swal.fire({
                title: 'Hapus Access Key?',
                text: 'Link ini tidak akan bisa diakses lagi!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: appUrl + 'admin/access/delete/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }
                        }
                    });
                }
            });
        });
    };

    var initSendWA = function() {
        // Table Button
        $(document).on('click', '.btn-send-wa', function() {
            var id = $(this).data('id');
            var btn = $(this);
            var originalContent = btn.html();
            
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: appUrl + 'admin/access/send_wa/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    btn.attr('disabled', false).html(originalContent);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'Terkirim!',
                            text: 'Pesan WhatsApp berhasil dikirim',
                            icon: 'success',
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message,
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    btn.attr('disabled', false).html(originalContent);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem',
                        icon: 'error'
                    });
                }
            });
        });

        // Generated Modal Button
        // NOTE: We need to handle this differently because we don't have the ID readily available in the button data attribute initially?
        // Ah, the controller returns the whole key object. Let's update `create` response to include ID in a data attribute or global var?
        // Simpler: The `response.key` object has ID. We can store it.
    };

    return {
        init: function() {
            initDatatable();
            initCopyButton();
            initCreateKey();
            initDelete();
            initSendWA();
        }
    };
}();

KTUtil.onDOMContentLoaded(function() {
    AccessKeys.init();
});

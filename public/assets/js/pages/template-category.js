jQuery(document).ready(function($) {
    const form = $('#category-form');
    const formTitle = $('#form-title');
    const nameInput = $('#category-name');
    const btnSubmit = $('#btn-submit');
    const btnCancel = $('#btn-cancel-edit');
    
    if (form.length === 0) return;

    const storeUrl = form.data('store-url');
    const updateUrlBase = form.data('update-url-base');
    const deleteUrlBase = form.data('delete-url-base');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // 1. AJAX Form Submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = $(this).attr('action');
        
        form.attr('data-kt-indicator', 'on');
        btnSubmit.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(data) {
                form.removeAttr('data-kt-indicator');
                btnSubmit.prop('disabled', false);

                if (data.success) {
                    Swal.fire({
                        text: data.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: { confirmButton: "btn btn-primary" }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        text: data.message || "Error",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                }
            },
            error: function(xhr) {
                form.removeAttr('data-kt-indicator');
                btnSubmit.prop('disabled', false);
                let msg = 'Terjadi kesalahan pada server.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error!', msg, 'error');
            }
        });
    });

    // 2. Edit Action (Event Delegation)
    $(document).on('click', '.btn-edit-category', function(e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');
        
        formTitle.text("Edit Kategori");
        nameInput.val(name);
        form.attr('action', updateUrlBase + '/' + id);
        btnSubmit.find('.indicator-label').text("Simpan Perubahan");
        btnCancel.removeClass('d-none');
        nameInput.focus();
        
        $('html, body').animate({
            scrollTop: $("#kt_app_content").offset().top - 20
        }, 500);
    });

    // 3. Cancel Action
    btnCancel.on('click', function() {
        formTitle.text("Tambah Kategori");
        nameInput.val("");
        form.attr('action', storeUrl);
        btnSubmit.find('.indicator-label').text("Simpan");
        btnCancel.addClass('d-none');
    });

    // 4. Delete Action (Event Delegation)
    $(document).on('click', '.btn-delete-category', function(e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');
        
        Swal.fire({
            title: 'Hapus Kategori?',
            text: 'Kategori "' + name + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-light"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrlBase + '/' + id,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Gagal menghapus.', 'error');
                    }
                });
            }
        });
    });
});

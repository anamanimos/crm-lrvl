"use strict";

// Roles management
var RolesManager = function() {
    
    var initDeleteButtons = function() {
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                
                Swal.fire({
                    title: 'Hapus Role?',
                    text: 'Role akan dihapus permanen. Pastikan tidak ada user yang menggunakan role ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(appUrl + 'admin/roles/delete/' + id, { method: 'POST' })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!', 'Terjadi kesalahan', 'error');
                            });
                    }
                });
            });
        });
    };
    
    return {
        init: function() {
            initDeleteButtons();
        }
    };
}();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    RolesManager.init();
});

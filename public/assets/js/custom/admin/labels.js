"use strict";

// Labels management JavaScript
var CRMLabels = function () {
    
    var initDelete = function() {
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                var name = this.dataset.name;
                
                Swal.fire({
                    title: 'Hapus Label?',
                    text: 'Label "' + name + '" akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(appUrl + 'admin/labels/delete/' + id, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    };
    
    return {
        init: function () {
            initDelete();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    CRMLabels.init();
});

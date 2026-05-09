"use strict";

// Funnels management JavaScript
var CRMFunnels = function () {
    
    var initSortable = function() {
        var sortableList = document.getElementById('sortable-funnels');
        if (!sortableList) return;
        
        new Sortable(sortableList, {
            handle: '.handle',
            animation: 150,
            onEnd: function() {
                var order = [];
                sortableList.querySelectorAll('tr[data-id]').forEach(function(row) {
                    order.push(row.dataset.id);
                });
                
                // Save order via AJAX
                fetch(appUrl + 'admin/funnels/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: order.map((id, index) => 'order[' + index + ']=' + id).join('&')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success('Urutan berhasil diperbarui');
                    }
                });
            }
        });
    };
    
    var initDelete = function() {
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                var name = this.dataset.name;
                
                Swal.fire({
                    title: 'Hapus Status Funnel?',
                    text: 'Status "' + name + '" akan dihapus. Customer dengan status ini akan di-reset.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(appUrl + 'admin/funnels/delete/' + id, {
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
            initSortable();
            initDelete();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    CRMFunnels.init();
});

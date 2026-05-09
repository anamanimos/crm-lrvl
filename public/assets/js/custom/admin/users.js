"use strict";

// Users management
var Users = function() {
    var table;

    var initDatatable = function() {
        // Check if DataTable is available
        if (typeof $.fn.DataTable === 'undefined') {
            console.log('DataTable not available, skipping initialization');
            return;
        }
        
        table = $('#kt_datatable').DataTable({
            "info": false,
            "order": [],
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

    var initDelete = function() {
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');

            Swal.fire({
                title: 'Hapus Pengguna?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: appUrl + 'admin/users/delete/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                });
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        }
                    });
                }
            });
        });
    };

    return {
        init: function() {
            initDatatable();
            initDelete();
        }
    };
}();

KTUtil.onDOMContentLoaded(function() {
    Users.init();
});

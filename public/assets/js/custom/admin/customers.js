"use strict";

// Customer management JavaScript
var CRMCustomers = function () {
    
    var initSearch = function() {
        var searchInput = document.querySelector('[data-kt-customer-table-filter="search"]');
        if (!searchInput) return;
        
        var timer;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(timer);
            timer = setTimeout(function() {
                var search = e.target.value;
                var url = new URL(window.location.href);
                if (search) {
                    url.searchParams.set('search', search);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }, 500);
        });
    };
    
    var initFilters = function() {
        var funnelFilter = document.getElementById('filter-funnel');
        var labelFilter = document.getElementById('filter-label');
        
        if (funnelFilter) {
            funnelFilter.addEventListener('change', function() {
                var url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('funnel', this.value);
                } else {
                    url.searchParams.delete('funnel');
                }
                window.location.href = url.toString();
            });
        }
        
        if (labelFilter) {
            labelFilter.addEventListener('change', function() {
                var url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('label', this.value);
                } else {
                    url.searchParams.delete('label');
                }
                window.location.href = url.toString();
            });
        }
    };
    
    var initDelete = function() {
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                var name = this.dataset.name;
                
                Swal.fire({
                    title: 'Hapus Customer?',
                    text: 'Customer "' + name + '" akan dihapus permanent.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch(appUrl + 'admin/customers/delete/' + id, {
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
    
    var initArchive = function() {
        document.querySelectorAll('.btn-archive').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                
                fetch(appUrl + 'admin/customers/archive/' + id, {
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
            });
        });
    };
    
    return {
        init: function () {
            initSearch();
            initFilters();
            initDelete();
            initArchive();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    CRMCustomers.init();
});

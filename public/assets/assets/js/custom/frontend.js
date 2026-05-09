"use strict";

// Frontend JavaScript for catalog

document.addEventListener('DOMContentLoaded', function() {
    initGallery();
    initTabs();
    initMaterialSelection();
    initDescriptionToggle();
});

// Image Gallery
function initGallery() {
    var thumbs = document.querySelectorAll('.gallery-thumb');
    var mainImage = document.getElementById('mainImage');
    
    if (!thumbs.length || !mainImage) return;
    
    thumbs.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            var src = this.dataset.src;
            mainImage.src = src;
            
            // Update active state
            thumbs.forEach(function(t) {
                t.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

// Tabs Navigation
function initTabs() {
    var tabBtns = document.querySelectorAll('.tab-btn');
    var tabPanes = document.querySelectorAll('.tab-pane');
    
    if (!tabBtns.length) return;
    
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tabId = this.dataset.tab;
            
            // Update buttons
            tabBtns.forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update panes
            tabPanes.forEach(function(pane) {
                pane.classList.remove('active');
            });
            
            var targetPane = document.getElementById('tab-' + tabId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });
}

// Material Selection for WA
function initMaterialSelection() {
    var itemCards = document.querySelectorAll('.item-card');
    var checkboxes = document.querySelectorAll('.material-checkbox');
    var actionBar = document.getElementById('waActionBar');
    var actionInfoTitle = document.getElementById('actionInfoTitle');
    var selectedItems = document.getElementById('selectedItems');
    var btnWaSend = document.getElementById('btnWaSend');
    var btnWaText = document.getElementById('btnWaText');
    var productName = document.getElementById('productName');
    
    // Initial State Check
    if (actionBar) {
        updateWaButton();
    }
    
    if (!checkboxes.length) return;
    
    // Click on card to toggle checkbox
    itemCards.forEach(function(card) {
        if (!card.querySelector('.material-checkbox')) return;
        
        card.addEventListener('click', function(e) {
            if (e.target.type === 'checkbox') return;
            
            var checkbox = this.querySelector('.material-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });
    
    // Update selection
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var card = this.closest('.item-card');
            if (this.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
            
            updateWaButton();
        });
    });
    
    function updateWaButton() {
        if (!actionBar) return;
        
        var selected = document.querySelectorAll('.material-checkbox:checked');
        var count = selected.length;
        var product = productName ? productName.value : '';
        
        if (count > 0) {
            // Selection State
            if (actionInfoTitle) actionInfoTitle.textContent = count + ' bahan dipilih';
            if (btnWaText) btnWaText.textContent = 'Kirim Pesanan';
            
            // Build selected items text
            var names = [];
            selected.forEach(function(cb) {
                var card = cb.closest('.item-card');
                names.push(card.dataset.name);
            });
            if (selectedItems) selectedItems.textContent = names.join(', ');
            
            // Build WA link
            var message = 'Halo, saya tertarik dengan produk: ' + product + '\n\n';
            message += 'Bahan yang dipilih:\n';
            names.forEach(function(name, i) {
                message += (i + 1) + '. ' + name + '\n';
            });
            message += '\nMohon informasi lebih lanjut. Terima kasih.';
            
            if (btnWaSend) btnWaSend.href = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(message);
        } else {
            // Default State (No Selection)
            if (actionInfoTitle) actionInfoTitle.textContent = 'Tertarik dengan produk ini?';
            if (selectedItems) selectedItems.textContent = 'Konsultasikan dengan kami via WhatsApp';
            if (btnWaText) btnWaText.textContent = 'Hubungi WA';
            
            var defaultMessage = 'Halo, saya tertarik dengan produk: ' + product + '. Mohon informasi lebih lanjut.';
            
            if (btnWaSend) btnWaSend.href = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(defaultMessage);
        }
    }
}

// Description Toggle
function initDescriptionToggle() {
    // Initial check
    setTimeout(checkDescriptionOverflow, 100);
    
    // Check on tab change
    var tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            setTimeout(checkDescriptionOverflow, 100);
        });
    });
    
    // Toggle click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.desc-toggle-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            var btn = e.target.closest('.desc-toggle-btn');
            var wrapper = btn.closest('.item-desc-wrapper');
            var desc = wrapper.querySelector('.item-card-desc');
            
            if (desc) {
                desc.classList.toggle('expanded');
                btn.classList.toggle('active');
            }
        }
    });

    // Check if description overflow
    function checkDescriptionOverflow() {
        var descs = document.querySelectorAll('.item-card-desc');
        descs.forEach(function(desc) {
            var btn = desc.nextElementSibling;
            if (btn && btn.classList.contains('desc-toggle-btn')) {
                // Check if text is clamped
                if (desc.scrollHeight > desc.clientHeight) {
                    btn.style.display = 'flex';
                } else {
                    // If expanded, keep button visible if it was visible before
                    if (!desc.classList.contains('expanded')) {
                        btn.style.display = 'none';
                    }
                }
            }
        });
    }
}

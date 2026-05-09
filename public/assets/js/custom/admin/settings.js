"use strict";

// Settings page functionality
var Settings = function() {
    
    var initAiProvider = function() {
        var providerSelect = $('#ai_provider');
        var modelSelect = $('#ai_model');
        var currentModel = $('#current_ai_model').val();
        
        // Load models when provider changes
        providerSelect.on('change', function() {
            var provider = $(this).val();
            loadModels(provider);
        });
        
        // Load initial models if provider is set
        if (providerSelect.val()) {
            loadModels(providerSelect.val(), currentModel);
        }
        
        function loadModels(provider, selectedModel) {
            modelSelect.html('<option value="">Loading...</option>');
            
            if (!provider) {
                modelSelect.html('<option value="">-- Pilih Model --</option>');
                return;
            }
            
            $.ajax({
                url: appUrl + 'admin/ai/models',
                type: 'GET',
                data: { provider: provider },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">-- Pilih Model --</option>';
                        response.models.forEach(function(model) {
                            var selected = (model === selectedModel) ? 'selected' : '';
                            options += '<option value="' + model + '" ' + selected + '>' + model + '</option>';
                        });
                        modelSelect.html(options);
                    }
                },
                error: function() {
                    modelSelect.html('<option value="">Error loading models</option>');
                }
            });
        }
    };
    
    var initTestAi = function() {
        $('#btn-test-ai').on('click', function() {
            var btn = $(this);
            var originalHtml = btn.html();
            
            btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Testing...');
            
            $.ajax({
                url: appUrl + 'admin/ai/test',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    btn.attr('disabled', false).html(originalHtml);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Koneksi AI berhasil',
                            icon: 'success',
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message || 'Koneksi gagal',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    btn.attr('disabled', false).html(originalHtml);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan jaringan',
                        icon: 'error'
                    });
                }
            });
        });
    };
    
    var initImageProvider = function() {
        var imageProviderSelect = $('#ai_image_provider');
        var modelContainer = $('#gemini_image_model_container');
        
        // Toggle model container based on provider
        imageProviderSelect.on('change', function() {
            if ($(this).val() === 'gemini_imagen') {
                modelContainer.show();
            } else {
                modelContainer.hide();
            }
        });
    };
    
    return {
        init: function() {
            initAiProvider();
            initTestAi();
            initImageProvider();
        }
    };
}();

KTUtil.onDOMContentLoaded(function() {
    Settings.init();
});

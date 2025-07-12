/**
 * FITBOT AI Chatbot Admin JavaScript
 * Handles admin interface interactions
 */

(function($) {
    'use strict';
    
    var FitbotAdmin = {
        
        init: function() {
            this.initTabs();
            this.initColorPicker();
            this.initFormValidation();
            this.initBulkUpload();
            this.initAnalytics();
            this.bindEvents();
        },
        
        initTabs: function() {
            $('.fitbot-tab-nav .nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var targetTab = $(this).attr('href').replace('#', '');
                
                $('.fitbot-tab-nav .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.fitbot-tab-pane').removeClass('active');
                $('#' + targetTab).addClass('active');
                
                if (history.pushState) {
                    var newUrl = window.location.href.split('&tab=')[0] + '&tab=' + targetTab;
                    history.pushState(null, null, newUrl);
                }
            });
            
            var urlParams = new URLSearchParams(window.location.search);
            var activeTab = urlParams.get('tab') || 'api-settings';
            $('#' + activeTab).addClass('active');
            $('.fitbot-tab-nav .nav-tab[href="#' + activeTab + '"]').addClass('nav-tab-active');
        },
        
        initColorPicker: function() {
            if (typeof wp !== 'undefined' && wp.colorPicker) {
                $('.fitbot-color-field').wpColorPicker({
                    change: function(event, ui) {
                        var color = ui.color.toString();
                        $(this).val(color);
                        FitbotAdmin.updateColorPreview($(this), color);
                    }
                });
            } else {
                $('.fitbot-color-field').on('change', function() {
                    FitbotAdmin.updateColorPreview($(this), $(this).val());
                });
            }
        },
        
        updateColorPreview: function($input, color) {
            var $preview = $input.siblings('.fitbot-color-preview');
            if ($preview.length) {
                $preview.css('background-color', color);
            }
        },
        
        initFormValidation: function() {
            $('#fitbot-settings-form').on('submit', function(e) {
                var isValid = true;
                var errors = [];
                
                var apiKey = $('#fitbot_openai_api_key').val().trim();
                if (!apiKey) {
                    errors.push('OpenAI API Key is required');
                    isValid = false;
                } else if (!apiKey.startsWith('sk-')) {
                    errors.push('OpenAI API Key should start with "sk-"');
                    isValid = false;
                }
                
                var startPlan = $('#fitbot_start_plan_id').val();
                var proPlan = $('#fitbot_pro_plan_id').val();
                var vipPlan = $('#fitbot_vip_plan_id').val();
                
                if (startPlan && !$.isNumeric(startPlan)) {
                    errors.push('Start Plan Product ID must be a number');
                    isValid = false;
                }
                
                if (proPlan && !$.isNumeric(proPlan)) {
                    errors.push('Pro Plan Product ID must be a number');
                    isValid = false;
                }
                
                if (vipPlan && !$.isNumeric(vipPlan)) {
                    errors.push('VIP Plan Product ID must be a number');
                    isValid = false;
                }
                
                var delay = $('#fitbot_chatbot_delay').val();
                if (delay && (!$.isNumeric(delay) || delay < 0)) {
                    errors.push('Chatbot delay must be a positive number');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    FitbotAdmin.showMessage(errors.join('<br>'), 'error');
                    return false;
                }
                
                FitbotAdmin.showMessage('Saving settings...', 'info');
            });
            
            this.initSlugValidation();
        },
        
        initSlugValidation: function() {
            var reservedSlugs = ['start', 'pro', 'vip'];
            
            $('#assistant_slug').on('input blur', function() {
                var slug = $(this).val().toLowerCase();
                var $errorMsg = $('#slug-error-message');
                
                $errorMsg.remove();
                
                if (reservedSlugs.includes(slug)) {
                    $(this).after('<p id="slug-error-message" style="color: #d63638; margin-top: 5px;">Warning: This slug is already used by a default assistant. Please choose a different slug.</p>');
                    $(this).css('border-color', '#d63638');
                } else {
                    $(this).css('border-color', '');
                }
            });
            
            $('form').on('submit', function(e) {
                var slug = $('#assistant_slug').val().toLowerCase();
                if (reservedSlugs.includes(slug)) {
                    e.preventDefault();
                    alert('Please choose a different slug. "' + slug + '" is already used by a default assistant.');
                    $('#assistant_slug').focus();
                }
            });
        },
        
        initBulkUpload: function() {
            var $uploadArea = $('#bulk-upload-area');
            var $fileInput = $('#bulk-upload-input');
            var $progress = $('#upload-progress');
            var $results = $('#upload-results');
            
            if (!$uploadArea.length) return;
            
            $uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            $uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            $uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                var files = e.originalEvent.dataTransfer.files;
                FitbotAdmin.handleFileUpload(files);
            });
            
            $fileInput.on('change', function() {
                FitbotAdmin.handleFileUpload(this.files);
            });
        },
        
        handleFileUpload: function(files) {
            if (files.length === 0) return;
            
            var $progress = $('#upload-progress');
            var $results = $('#upload-results');
            
            $progress.show();
            $results.hide();
            
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            formData.append('action', 'fitbot_bulk_upload');
            formData.append('nonce', fitbot_admin.nonce);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = (e.loaded / e.total) * 100;
                            $('.progress-fill').css('width', percentComplete + '%');
                            $('.progress-text').text(Math.round(percentComplete) + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $progress.hide();
                    $results.show();
                    
                    if (response.success) {
                        $('.upload-summary').html('<div class="fitbot-message success">' + response.data.message + '</div>');
                        FitbotAdmin.refreshContentCounts();
                    } else {
                        $('.upload-summary').html('<div class="fitbot-message error">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    $progress.hide();
                    $results.show();
                    $('.upload-summary').html('<div class="fitbot-message error">Upload failed. Please try again.</div>');
                }
            });
        },
        
        initAnalytics: function() {
            this.loadAnalyticsData();
            
            setInterval(function() {
                FitbotAdmin.loadAnalyticsData();
            }, 30000);
        },
        
        loadAnalyticsData: function() {
            if (!$('#fitbot-analytics').length) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_get_analytics',
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FitbotAdmin.updateAnalyticsDisplay(response.data);
                    }
                }
            });
        },
        
        updateAnalyticsDisplay: function(data) {
            $('#total-conversations').text(data.total_conversations || 0);
            $('#today-conversations').text(data.today_conversations || 0);
            $('#active-users').text(data.active_users || 0);
            
            $('#start-plan-users').text(data.start_plan_users || 0);
            $('#pro-plan-users').text(data.pro_plan_users || 0);
            $('#vip-plan-users').text(data.vip_plan_users || 0);
            
            $('#total-recipes').text(data.total_recipes || 0);
            $('#total-workouts').text(data.total_workouts || 0);
            $('#total-pdfs').text(data.total_pdfs || 0);
            
            $('#api-calls-today').text(data.api_calls_today || 0);
            $('#api-calls-month').text(data.api_calls_month || 0);
        },
        
        refreshContentCounts: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_refresh_content_counts',
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.fitbot-content-card .count').each(function() {
                            var type = $(this).data('type');
                            if (response.data[type]) {
                                $(this).text(response.data[type]);
                            }
                        });
                    }
                }
            });
        },
        
        bindEvents: function() {
            var self = this;
            
            $('#test-api-connection').on('click', function(e) {
                e.preventDefault();
                self.testApiConnection();
            });
            
            $('#export-settings').on('click', function(e) {
                e.preventDefault();
                self.exportSettings();
            });
            
            $('#import-settings').on('change', function() {
                self.importSettings(this.files[0]);
            });
            
            $('#clear-conversations').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to clear all conversation history? This action cannot be undone.')) {
                    self.clearConversations();
                }
            });
            
            $('#reset-settings').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to reset all plugin settings to defaults? This action cannot be undone.')) {
                    self.resetSettings();
                }
            });
        },
        
        testApiConnection: function() {
            var apiKey = $('#fitbot_openai_api_key').val().trim();
            
            if (!apiKey) {
                this.showMessage('Please enter an API key first.', 'error');
                return;
            }
            
            $('#test-api-connection').prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_test_api',
                    api_key: apiKey,
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FitbotAdmin.showMessage('API connection successful!', 'success');
                    } else {
                        FitbotAdmin.showMessage('API connection failed: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    FitbotAdmin.showMessage('API test failed. Please check your connection.', 'error');
                },
                complete: function() {
                    $('#test-api-connection').prop('disabled', false).text('Test Connection');
                }
            });
        },
        
        exportSettings: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_export_settings',
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var blob = new Blob([JSON.stringify(response.data, null, 2)], {type: 'application/json'});
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'fitbot-settings-' + new Date().toISOString().split('T')[0] + '.json';
                        a.click();
                        window.URL.revokeObjectURL(url);
                        
                        FitbotAdmin.showMessage('Settings exported successfully!', 'success');
                    } else {
                        FitbotAdmin.showMessage('Export failed: ' + response.data.message, 'error');
                    }
                }
            });
        },
        
        importSettings: function(file) {
            if (!file) return;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fitbot_import_settings',
                            settings: JSON.stringify(settings),
                            nonce: fitbot_admin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                FitbotAdmin.showMessage('Settings imported successfully! Please refresh the page.', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                FitbotAdmin.showMessage('Import failed: ' + response.data.message, 'error');
                            }
                        }
                    });
                } catch (error) {
                    FitbotAdmin.showMessage('Invalid settings file format.', 'error');
                }
            };
            reader.readAsText(file);
        },
        
        clearConversations: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_clear_conversations',
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FitbotAdmin.showMessage('Conversation history cleared successfully!', 'success');
                        FitbotAdmin.loadAnalyticsData();
                    } else {
                        FitbotAdmin.showMessage('Failed to clear conversations: ' + response.data.message, 'error');
                    }
                }
            });
        },
        
        resetSettings: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fitbot_reset_settings',
                    nonce: fitbot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FitbotAdmin.showMessage('Settings reset successfully! Please refresh the page.', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        FitbotAdmin.showMessage('Failed to reset settings: ' + response.data.message, 'error');
                    }
                }
            });
        },
        
        showMessage: function(message, type) {
            var $container = $('#fitbot-admin-messages');
            if (!$container.length) {
                $container = $('<div id="fitbot-admin-messages"></div>').prependTo('.fitbot-admin-page');
            }
            
            var $message = $('<div class="fitbot-message ' + type + '">' + message + '</div>');
            $container.html($message);
            
            if (type === 'success') {
                setTimeout(function() {
                    $message.fadeOut();
                }, 5000);
            }
            
            $('html, body').animate({scrollTop: 0}, 300);
        }
    };
    
    $(document).ready(function() {
        FitbotAdmin.init();
    });
    
    window.FitbotAdmin = FitbotAdmin;
    
})(jQuery);

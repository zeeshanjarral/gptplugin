/**
 * FITBOT AI Chatbot Frontend JavaScript
 * Responsive design for mobile, desktop, and tablet
 */

(function($) {
    'use strict';
    
    var FitbotChatbot = {
        isOpen: false,
        isTyping: false,
        conversationHistory: [],
        settings: {
            delay: fitbot_ajax.delay || 3000,
            greeting: fitbot_ajax.greeting || 'Hello! I\'m FITBOT, your AI fitness assistant. What are your health and fitness goals today?',
            color: fitbot_ajax.color || '#0073aa',
            position: fitbot_ajax.position || 'bottom-right',
            enableSound: fitbot_ajax.enable_sound || true,
            enableTyping: fitbot_ajax.enable_typing || true
        },
        
        init: function() {
            this.createChatbot();
            this.bindEvents();
            this.showChatbotAfterDelay();
            this.detectDevice();
        },
        
        detectDevice: function() {
            var isMobile = window.innerWidth <= 768;
            var isTablet = window.innerWidth > 768 && window.innerWidth <= 1024;
            
            $('body').toggleClass('fitbot-mobile', isMobile);
            $('body').toggleClass('fitbot-tablet', isTablet);
            $('body').toggleClass('fitbot-desktop', !isMobile && !isTablet);
        },
        
        createChatbot: function() {
            var chatbotHtml = `
                <div id="fitbot-chatbot" class="fitbot-chatbot fitbot-${this.settings.position}">
                    <div id="fitbot-button" class="fitbot-button">
                        <svg class="fitbot-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span class="fitbot-close-icon">×</span>
                    </div>
                    
                    <div id="fitbot-chat-window" class="fitbot-chat-window">
                        <div class="fitbot-header">
                            <div class="fitbot-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="fitbot-info">
                                <h4>FITBOT AI</h4>
                                <span class="fitbot-status">Online</span>
                            </div>
                            <button class="fitbot-minimize" aria-label="Minimize chat">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="fitbot-messages" id="fitbot-messages">
                            <div class="fitbot-message fitbot-bot-message">
                                <div class="fitbot-message-content">
                                    ${this.settings.greeting}
                                </div>
                                <div class="fitbot-message-time">${this.getCurrentTime()}</div>
                            </div>
                        </div>
                        
                        <div class="fitbot-typing-indicator" id="fitbot-typing" style="display: none;">
                            <div class="fitbot-typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <span class="fitbot-typing-text">FITBOT is typing...</span>
                        </div>
                        
                        <div class="fitbot-input-area">
                            <div class="fitbot-quick-actions" id="fitbot-quick-actions">
                                <button class="fitbot-quick-btn" data-message="I want a healthy recipe">🍽️ Recipe</button>
                                <button class="fitbot-quick-btn" data-message="Show me a workout">💪 Workout</button>
                                <button class="fitbot-quick-btn" data-message="Nutrition advice">🥗 Nutrition</button>
                                <button class="fitbot-quick-btn" data-message="Health question">❤️ Health</button>
                            </div>
                            
                            <div class="fitbot-input-container">
                                <textarea 
                                    id="fitbot-input" 
                                    class="fitbot-input" 
                                    placeholder="Type your message..."
                                    rows="1"
                                    maxlength="500"
                                ></textarea>
                                <button id="fitbot-send" class="fitbot-send-btn" disabled>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22,2 15,22 11,13 2,9 22,2"></polygon>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="fitbot-powered-by">
                                Powered by OpenAI GPT-4 Turbo
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(chatbotHtml);
            this.applyStyling();
        },
        
        applyStyling: function() {
            var style = `
                <style id="fitbot-dynamic-styles">
                    .fitbot-chatbot {
                        --fitbot-primary-color: ${this.settings.color};
                        --fitbot-primary-rgb: ${this.hexToRgb(this.settings.color)};
                    }
                </style>
            `;
            $('head').append(style);
        },
        
        bindEvents: function() {
            var self = this;
            
            $(document).on('click', '#fitbot-button', function() {
                self.toggleChatbot();
            });
            
            $(document).on('click', '.fitbot-minimize', function() {
                self.closeChatbot();
            });
            
            $(document).on('click', '#fitbot-send', function() {
                self.sendMessage();
            });
            
            $(document).on('keydown', '#fitbot-input', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                } else if (e.key === 'Enter' && e.shiftKey) {
                    return true;
                }
            });
            
            $(document).on('input', '#fitbot-input', function() {
                self.autoResizeTextarea(this);
                self.toggleSendButton();
            });
            
            $(document).on('click', '.fitbot-quick-btn', function() {
                var message = $(this).data('message');
                $('#fitbot-input').val(message);
                self.toggleSendButton();
                self.sendMessage();
            });
            
            $(window).on('resize', function() {
                self.detectDevice();
                self.adjustChatbotSize();
            });
            
            $(document).on('click', function(e) {
                if (self.isOpen && $(window).width() <= 768) {
                    if (!$(e.target).closest('#fitbot-chatbot').length) {
                        self.closeChatbot();
                    }
                }
            });
            
            $(window).on('orientationchange', function() {
                setTimeout(function() {
                    self.adjustChatbotSize();
                }, 100);
            });
        },
        
        showChatbotAfterDelay: function() {
            var self = this;
            setTimeout(function() {
                $('#fitbot-chatbot').addClass('fitbot-visible');
                
                setTimeout(function() {
                    $('#fitbot-button').addClass('fitbot-pulse');
                    setTimeout(function() {
                        $('#fitbot-button').removeClass('fitbot-pulse');
                    }, 2000);
                }, 500);
            }, this.settings.delay);
        },
        
        toggleChatbot: function() {
            if (this.isOpen) {
                this.closeChatbot();
            } else {
                this.openChatbot();
            }
        },
        
        openChatbot: function() {
            this.isOpen = true;
            $('#fitbot-chatbot').addClass('fitbot-open');
            $('#fitbot-input').focus();
            this.adjustChatbotSize();
            this.scrollToBottom();
            
            if (this.conversationHistory.length > 0) {
                $('#fitbot-quick-actions').hide();
            }
        },
        
        closeChatbot: function() {
            this.isOpen = false;
            $('#fitbot-chatbot').removeClass('fitbot-open');
        },
        
        adjustChatbotSize: function() {
            var $chatWindow = $('#fitbot-chat-window');
            var windowHeight = $(window).height();
            var windowWidth = $(window).width();
            
            if (windowWidth <= 768) {
                $chatWindow.css({
                    'height': windowHeight - 20 + 'px',
                    'max-height': windowHeight - 20 + 'px'
                });
            } else if (windowWidth <= 1024) {
                $chatWindow.css({
                    'height': Math.min(600, windowHeight - 100) + 'px',
                    'max-height': windowHeight - 100 + 'px'
                });
            } else {
                $chatWindow.css({
                    'height': '500px',
                    'max-height': windowHeight - 100 + 'px'
                });
            }
        },
        
        sendMessage: function() {
            var message = $('#fitbot-input').val().trim();
            if (!message || this.isTyping) return;
            
            this.addMessage(message, 'user');
            $('#fitbot-input').val('');
            this.autoResizeTextarea($('#fitbot-input')[0]);
            this.toggleSendButton();
            
            $('#fitbot-quick-actions').hide();
            
            this.showTypingIndicator();
            this.sendToServer(message);
        },
        
        addMessage: function(message, sender, data = {}) {
            var time = this.getCurrentTime();
            var messageClass = sender === 'user' ? 'fitbot-user-message' : 'fitbot-bot-message';
            var avatar = sender === 'user' ? '' : `
                <div class="fitbot-message-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            `;
            
            var upgradeButton = '';
            if (data.type === 'upgrade_suggestion' && data.upgrade_url) {
                upgradeButton = `
                    <div class="fitbot-upgrade-button">
                        <a href="${data.upgrade_url}" target="_blank" class="fitbot-upgrade-link">
                            Upgrade Now
                        </a>
                    </div>
                `;
            }
            
            var pdfDownload = '';
            if (data.type === 'pdf_response' && data.pdf_url) {
                pdfDownload = `
                    <div class="fitbot-pdf-download">
                        <a href="${data.pdf_url}" target="_blank" class="fitbot-pdf-link">
                            📄 Download: ${data.pdf_title || 'PDF Resource'}
                        </a>
                    </div>
                `;
            }
            
            var messageHtml = `
                <div class="fitbot-message ${messageClass}">
                    ${avatar}
                    <div class="fitbot-message-bubble">
                        <div class="fitbot-message-content">${this.formatMessage(message)}</div>
                        ${upgradeButton}
                        ${pdfDownload}
                        <div class="fitbot-message-time">${time}</div>
                    </div>
                </div>
            `;
            
            $('#fitbot-messages').append(messageHtml);
            this.scrollToBottom();
            
            if (sender === 'bot' && this.settings.enableSound) {
                this.playNotificationSound();
            }
            
            this.conversationHistory.push({
                message: message,
                sender: sender,
                time: time,
                data: data
            });
        },
        
        formatMessage: function(message) {
            message = message.replace(/\n/g, '<br>');
            
            message = message.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
            
            message = message.replace(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1">$1</a>');
            
            return message;
        },
        
        sendToServer: function(message) {
            var self = this;
            
            $.ajax({
                url: fitbot_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'fitbot_chat',
                    message: message,
                    type: this.detectMessageType(message),
                    nonce: fitbot_ajax.nonce
                },
                success: function(response) {
                    self.hideTypingIndicator();
                    
                    if (response.success) {
                        self.addMessage(response.data.message, 'bot', response.data);
                    } else {
                        self.addMessage(response.data.message || 'Sorry, something went wrong. Please try again.', 'bot', {type: 'error'});
                    }
                },
                error: function() {
                    self.hideTypingIndicator();
                    self.addMessage('Sorry, I\'m having trouble connecting right now. Please try again in a moment.', 'bot', {type: 'error'});
                }
            });
        },
        
        detectMessageType: function(message) {
            var messageLower = message.toLowerCase();
            
            if (messageLower.includes('recipe') || messageLower.includes('meal') || messageLower.includes('food')) {
                return 'recipe_request';
            } else if (messageLower.includes('workout') || messageLower.includes('exercise') || messageLower.includes('training')) {
                return 'workout_request';
            } else if (messageLower.includes('nutrition') || messageLower.includes('calories') || messageLower.includes('protein')) {
                return 'nutrition_question';
            } else if (messageLower.includes('health') || messageLower.includes('thyroid') || messageLower.includes('bloating')) {
                return 'health_question';
            } else if (messageLower.includes('pdf') || messageLower.includes('download') || messageLower.includes('guide')) {
                return 'pdf_request';
            }
            
            return 'general';
        },
        
        showTypingIndicator: function() {
            if (!this.settings.enableTyping) return;
            
            this.isTyping = true;
            $('#fitbot-typing').show();
            this.scrollToBottom();
        },
        
        hideTypingIndicator: function() {
            this.isTyping = false;
            $('#fitbot-typing').hide();
        },
        
        autoResizeTextarea: function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        },
        
        toggleSendButton: function() {
            var hasText = $('#fitbot-input').val().trim().length > 0;
            $('#fitbot-send').prop('disabled', !hasText || this.isTyping);
        },
        
        scrollToBottom: function() {
            var $messages = $('#fitbot-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        },
        
        getCurrentTime: function() {
            var now = new Date();
            return now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        },
        
        playNotificationSound: function() {
            try {
                var audioContext = new (window.AudioContext || window.webkitAudioContext)();
                var oscillator = audioContext.createOscillator();
                var gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } catch (e) {
            }
        },
        
        hexToRgb: function(hex) {
            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? 
                parseInt(result[1], 16) + ',' + parseInt(result[2], 16) + ',' + parseInt(result[3], 16) :
                '0,115,170';
        }
    };
    
    $(document).ready(function() {
        FitbotChatbot.init();
    });
    
    window.FitbotChatbot = FitbotChatbot;
    
})(jQuery);

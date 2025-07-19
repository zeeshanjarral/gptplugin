/**
 * FITBOT AI Chatbot Frontend JavaScript
 * Responsive design for mobile, desktop, and tablet
 * Supports multiple assistant instances via shortcodes
 */

(function($) {
    'use strict';
    
    function FitbotChatbot(options) {
        this.options = $.extend({
            container: null,
            assistantId: null,
            assistantSlug: null,
            assistantName: 'FITBOT Assistant',
            color: '#0073aa',
            greeting: 'Hello! How can I help you today?',
            ajax_url: fitbot_ajax ? fitbot_ajax.ajax_url : '/wp-admin/admin-ajax.php',
            nonce: fitbot_ajax ? fitbot_ajax.nonce : '',
            uniqueId: 'fitbot_' + Math.random().toString(36).substr(2, 9),
            enableSound: true,
            enableTyping: true
        }, options);
        
        this.isOpen = false;
        this.isTyping = false;
        this.conversationHistory = [];
        this.container = $(this.options.container);
        
        if (this.container.length) {
            this.init();
        }
    }
    
    FitbotChatbot.prototype = {
        
        init: function() {
            this.setupChatInterface();
            this.bindEvents();
            this.detectDevice();
        },
        
        detectDevice: function() {
            var isMobile = window.innerWidth <= 768;
            var isTablet = window.innerWidth > 768 && window.innerWidth <= 1024;
            
            $('body').toggleClass('fitbot-mobile', isMobile);
            $('body').toggleClass('fitbot-tablet', isTablet);
            $('body').toggleClass('fitbot-desktop', !isMobile && !isTablet);
        },
        
        setupChatInterface: function() {
            var chatArea = this.container.find('.fitbot-chat-area');
            if (chatArea.length && chatArea.find('.fitbot-chat-messages').length) {
                this.messagesContainer = chatArea.find('.fitbot-chat-messages');
                this.inputContainer = chatArea.find('.fitbot-input-container');
                this.messageInput = chatArea.find('.fitbot-message-input');
                this.sendButton = chatArea.find('.fitbot-send-button');
                this.typingIndicator = chatArea.find('.fitbot-typing-indicator');
                
                if (this.messagesContainer.length && this.messageInput.length && this.sendButton.length) {
                    this.applyStyling();
                    return;
                } else {
                    console.warn('FITBOT: Some required elements not found, falling back to HTML generation');
                }
            }
            
            var chatbotHtml = `
                <div class="fitbot-chat-messages" id="fitbot-messages-${this.options.assistantSlug}">
                    <div class="fitbot-message fitbot-assistant-message">
                        <div class="fitbot-message-content">${this.options.greeting}</div>
                        <div class="fitbot-message-time">${this.getCurrentTime()}</div>
                    </div>
                </div>
                
                <div class="fitbot-typing-indicator" id="fitbot-typing-${this.options.assistantSlug}" style="display: none;">
                    <span></span><span></span><span></span>
                </div>
                
                <div class="fitbot-input-container">
                    <textarea 
                        id="fitbot-input-${this.options.assistantSlug}" 
                        class="fitbot-message-input" 
                        placeholder="Type your message..."
                        rows="1"></textarea>
                    <button 
                        id="fitbot-send-${this.options.assistantSlug}" 
                        class="fitbot-send-button" 
                        type="button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
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
            
            var chatArea = this.container.find('.fitbot-chat-area');
            chatArea.html(chatbotHtml);
            
            this.messagesContainer = chatArea.find('.fitbot-chat-messages');
            this.inputContainer = chatArea.find('.fitbot-input-container');
            this.messageInput = chatArea.find('.fitbot-message-input');
            this.sendButton = chatArea.find('.fitbot-send-button');
            this.typingIndicator = chatArea.find('.fitbot-typing-indicator');
            
            this.applyStyling();
        },
        
        applyStyling: function() {
            this.container.css('--fitbot-primary-color', this.options.color);
            this.container.css('--fitbot-primary-rgb', this.hexToRgb(this.options.color));
        },
        
        bindEvents: function() {
            var self = this;
            var assistantSlug = this.options.assistantSlug;
            
            if (this.sendButton && this.sendButton.length) {
                this.sendButton.on('click', function() {
                    self.sendMessage();
                });
            } else {
                console.error('FITBOT: Send button not found, cannot bind click event');
                return;
            }
            
            if (this.messageInput && this.messageInput.length) {
                this.messageInput.on('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        self.sendMessage();
                    } else if (e.key === 'Enter' && e.shiftKey) {
                        return true;
                    }
                });
                
                this.messageInput.on('input', function() {
                    self.autoResizeTextarea(this);
                    self.toggleSendButton();
                });
            } else {
                console.error('FITBOT: Message input not found, cannot bind input events');
            }
            
            this.container.on('click', '.fitbot-send-button', function() {
                self.sendMessage();
            });
            
            $(window).on('resize', function() {
                self.detectDevice();
            });
        },
        
        
        sendMessage: function() {
            if (!this.messageInput || !this.messageInput.length) {
                console.error('FITBOT: Message input not found');
                return;
            }
            
            var message = this.messageInput.val().trim();
            if (!message || this.isTyping) return;
            
            console.log('FITBOT: Sending message:', message);
            
            this.addMessage(message, 'user');
            this.messageInput.val('');
            this.autoResizeTextarea(this.messageInput[0]);
            this.toggleSendButton();
            
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
            
            this.messagesContainer.append(messageHtml);
            this.scrollToBottom();
            
            if (sender === 'bot' && this.options.enableSound) {
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
                url: this.options.ajax_url,
                type: 'POST',
                data: {
                    action: 'fitbot_chat',
                    message: message,
                    assistant_slug: this.options.assistantSlug,
                    assistant_id: this.options.assistantId,
                    type: this.detectMessageType(message),
                    nonce: this.options.nonce
                },
                success: function(response) {
                    self.hideTypingIndicator();
                    
                    if (response.success) {
                        self.addMessage(response.data.message, 'bot', response.data);
                    } else {
                        self.addMessage(response.data.message || 'Sorry, something went wrong. Please try again.', 'bot', {type: 'error'});
                    }
                    
                    if (self.messageInput && self.messageInput.length) {
                        setTimeout(function() {
                            self.messageInput.focus();
                        }, 100);
                    }
                },
                error: function() {
                    self.hideTypingIndicator();
                    self.addMessage('Sorry, I\'m having trouble connecting right now. Please try again in a moment.', 'bot', {type: 'error'});
                    
                    if (self.messageInput && self.messageInput.length) {
                        setTimeout(function() {
                            self.messageInput.focus();
                        }, 100);
                    }
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
            if (!this.options.enableTyping) return;
            
            this.isTyping = true;
            this.typingIndicator.show();
            this.scrollToBottom();
        },
        
        hideTypingIndicator: function() {
            this.isTyping = false;
            this.typingIndicator.hide();
        },
        
        autoResizeTextarea: function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        },
        
        toggleSendButton: function() {
            var hasText = this.messageInput.val().trim().length > 0;
            this.sendButton.prop('disabled', !hasText || this.isTyping);
        },
        
        scrollToBottom: function() {
            if (this.messagesContainer.length) {
                this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
            }
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
    
    window.FitbotAssistants = {
        instances: {},
        
        initShortcodes: function() {
            var self = this;
            $('.fitbot-assistant-container').each(function() {
                var $container = $(this);
                var assistantId = $container.data('assistant-id');
                var assistantSlug = $container.data('assistant-slug');
                var uniqueId = $container.data('unique-id');
                
                if (assistantSlug && !self.instances[assistantSlug + '_' + uniqueId]) {
                    self.instances[assistantSlug + '_' + uniqueId] = new FitbotChatbot({
                        container: this,
                        assistantId: assistantId,
                        assistantSlug: assistantSlug,
                        uniqueId: uniqueId
                    });
                }
            });
        },
        
        initShortcode: function(data) {
            if (data.hasAccess && data.isLoggedIn && data.slug) {
                var instanceKey = data.slug + '_' + data.uniqueId;
                if (!this.instances[instanceKey]) {
                    this.instances[instanceKey] = new FitbotChatbot({
                        container: '#fitbot-assistant-' + data.slug,
                        assistantId: data.id,
                        assistantSlug: data.slug,
                        assistantName: data.name,
                        color: data.color,
                        greeting: data.greeting,
                        ajax_url: data.ajax_url,
                        nonce: data.nonce,
                        uniqueId: data.uniqueId
                    });
                }
            }
        }
    };
    
    var LegacyFitbotChatbot = {
        init: function() {
            if (typeof fitbot_ajax !== 'undefined' && $('#fitbot-chatbot-container').length) {
                new FitbotChatbot({
                    container: '#fitbot-chatbot-container',
                    assistantSlug: 'legacy',
                    color: fitbot_ajax.color || '#0073aa',
                    greeting: fitbot_ajax.greeting || 'Hello! How can I help you today?',
                    ajax_url: fitbot_ajax.ajax_url,
                    nonce: fitbot_ajax.nonce
                });
            }
        }
    };
    
    $(document).ready(function() {
        if (typeof FitbotAssistants !== 'undefined') {
            FitbotAssistants.initShortcodes();
        }
        
        LegacyFitbotChatbot.init();
    });
    
    window.FitbotChatbot = FitbotChatbot;
    
})(jQuery);

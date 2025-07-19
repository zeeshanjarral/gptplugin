<?php
/**
 * Assistant Shortcode Template
 * This template renders individual AI assistants via shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_plan = 'none';
$has_access = false;
if (is_user_logged_in()) {
    $subscription_checker = new Fitbot_Woo_Subscription_Check();
    $user_plan = $subscription_checker->get_user_plan(get_current_user_id());
    $has_access = $subscription_checker->check_assistant_access(get_current_user_id(), $assistant['id']);
}

$container_id = 'fitbot-assistant-' . esc_attr($assistant['slug']);
$unique_id = uniqid('fitbot_');
?>

<div class="fitbot-assistant-container" 
     id="<?php echo esc_attr($container_id); ?>"
     data-assistant-id="<?php echo esc_attr($assistant['id']); ?>"
     data-assistant-slug="<?php echo esc_attr($assistant['slug']); ?>"
     data-unique-id="<?php echo esc_attr($unique_id); ?>"
     style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>; --fitbot-primary-color: <?php echo esc_attr($assistant['color']); ?>;">
    
    <div class="fitbot-assistant-header">
        <div class="fitbot-assistant-info">
            <h3 class="fitbot-assistant-name"><?php echo esc_html($assistant['name']); ?></h3>
            <span class="fitbot-assistant-personality"><?php echo esc_html(ucfirst($assistant['personality'])); ?></span>
        </div>
        <div class="fitbot-assistant-pricing">
            <span class="fitbot-price"><?php echo esc_html($assistant['currency'] . ' ' . number_format($assistant['price'], 2)); ?>/mo</span>
        </div>
    </div>
    
    <div class="fitbot-chat-area" id="fitbot-chat-<?php echo esc_attr($assistant['slug']); ?>">
        <?php if (!is_user_logged_in()): ?>
            <div class="fitbot-login-required">
                <div class="fitbot-message-bubble fitbot-system-message">
                    <p><?php _e('Please log in to chat with this assistant.', 'fitbot-ai-chatbot'); ?></p>
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="fitbot-login-btn">
                        <?php _e('Log In', 'fitbot-ai-chatbot'); ?>
                    </a>
                </div>
            </div>
        <?php elseif (!$has_access): ?>
            <div class="fitbot-subscription-required">
                <div class="fitbot-message-bubble fitbot-system-message">
                    <p><?php printf(__('Subscribe to %s to chat with this assistant.', 'fitbot-ai-chatbot'), $assistant['name']); ?></p>
                    <?php if ($assistant['woo_product_id']): ?>
                        <a href="<?php echo get_permalink($assistant['woo_product_id']); ?>" class="fitbot-subscribe-btn">
                            <?php printf(__('Subscribe for %s %s/mo', 'fitbot-ai-chatbot'), $assistant['currency'], number_format($assistant['price'], 2)); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="fitbot-chat-messages" id="fitbot-messages-<?php echo esc_attr($assistant['slug']); ?>">
                <div class="fitbot-message-bubble fitbot-assistant-message">
                    <div class="fitbot-message-content">
                        <?php echo esc_html($assistant['greeting_message'] ?: 'Hello! How can I help you today?'); ?>
                    </div>
                    <div class="fitbot-message-time"><?php echo current_time('H:i'); ?></div>
                </div>
            </div>
            
            <div class="fitbot-chat-input-area">
                <div class="fitbot-typing-indicator" id="fitbot-typing-<?php echo esc_attr($assistant['slug']); ?>" style="display: none;">
                    <span></span><span></span><span></span>
                </div>
                
                <div class="fitbot-input-container">
                    <textarea 
                        id="fitbot-input-<?php echo esc_attr($assistant['slug']); ?>" 
                        class="fitbot-message-input" 
                        placeholder="<?php _e('Type your message...', 'fitbot-ai-chatbot'); ?>"
                        rows="1"></textarea>
                    <button 
                        id="fitbot-send-<?php echo esc_attr($assistant['slug']); ?>" 
                        class="fitbot-send-button" 
                        type="button">
                        Submit
                    </button>
                </div>
                
                <div class="fitbot-usage-info">
                    <span class="fitbot-usage-text">
                        <?php printf(__('Daily limit: %d | Monthly limit: %d', 'fitbot-ai-chatbot'), $assistant['daily_limit'], $assistant['monthly_limit']); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var assistantData = {
        id: <?php echo json_encode($assistant['id']); ?>,
        slug: <?php echo json_encode($assistant['slug']); ?>,
        name: <?php echo json_encode($assistant['name']); ?>,
        color: <?php echo json_encode($assistant['color']); ?>,
        greeting: <?php echo json_encode($assistant['greeting_message']); ?>,
        hasAccess: <?php echo $has_access ? 'true' : 'false'; ?>,
        isLoggedIn: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>,
        uniqueId: <?php echo json_encode($unique_id); ?>,
        ajax_url: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo json_encode(wp_create_nonce('fitbot_nonce')); ?>
    };
    
    if (typeof FitbotAssistants === 'undefined') {
        window.FitbotAssistants = {
            instances: {},
            initShortcode: function(data) {
                if (data.hasAccess && data.isLoggedIn) {
                    this.instances[data.slug] = new FitbotChatbot({
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
        };
    }
    
    FitbotAssistants.initShortcode(assistantData);
});
</script>

<style>
.fitbot-assistant-container {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    display: flex;
    flex-direction: column;
}

.fitbot-assistant-header {
    background: var(--fitbot-primary-color, #0073aa);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fitbot-assistant-name {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.fitbot-assistant-personality {
    font-size: 12px;
    opacity: 0.9;
    text-transform: capitalize;
}

.fitbot-price {
    font-size: 14px;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 4px;
}

.fitbot-chat-area {
    height: calc(100% - 70px);
    display: flex;
    flex-direction: column;
    min-height: 300px;
}

.fitbot-chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    max-height: calc(100% - 120px);
    background: #000000;
}

.fitbot-message-bubble {
    margin-bottom: 15px;
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 80%;
    word-wrap: break-word;
}

.fitbot-assistant-message {
    background: #9b8841;
    color: #000000;
    margin-right: auto;
}

.fitbot-user-message {
    background: #000000;
    color: #9b8841;
    margin-left: auto;
    border: 1px solid #9b8841;
}

.fitbot-system-message {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    text-align: center;
    margin: 20px auto;
    max-width: 90%;
}

.fitbot-message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 5px;
}

.fitbot-chat-input-area {
    border-top: 1px solid #9b8841;
    padding: 15px 20px;
    background: #000000;
}

.fitbot-input-container {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.fitbot-message-input {
    flex: 1;
    border: 1px solid #9b8841;
    border-radius: 20px;
    padding: 10px 15px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
    max-height: 100px;
    min-height: 40px;
    background: #000000;
    color: #9b8841;
}

.fitbot-message-input:focus {
    outline: none;
    border-color: #9b8841;
}

.fitbot-message-input::placeholder {
    color: rgba(155, 136, 65, 0.7);
}

.fitbot-send-button {
    background: #9b8841;
    color: #000000;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
    font-weight: bold;
    font-size: 12px;
}

.fitbot-send-button:hover {
    background: rgba(255, 215, 0, 0.8);
    color: #000000;
}

.fitbot-login-btn, .fitbot-subscribe-btn {
    display: inline-block;
    background: var(--fitbot-primary-color, #0073aa);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    margin-top: 10px;
    font-weight: 600;
}

.fitbot-login-btn:hover, .fitbot-subscribe-btn:hover {
    background: var(--fitbot-primary-color-dark, #005a87);
    color: white;
}

.fitbot-usage-info {
    margin-top: 8px;
    text-align: center;
}

.fitbot-usage-text {
    font-size: 11px;
    color: #666;
}

.fitbot-typing-indicator {
    padding: 10px 15px;
    margin-bottom: 10px;
}

.fitbot-typing-indicator span {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #999;
    margin: 0 2px;
    animation: fitbot-typing 1.4s infinite ease-in-out;
}

.fitbot-typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.fitbot-typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes fitbot-typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .fitbot-assistant-container {
        border-radius: 0;
        height: 100vh !important;
        width: 100% !important;
    }
    
    .fitbot-assistant-header {
        padding: 12px 15px;
    }
    
    .fitbot-assistant-name {
        font-size: 16px;
    }
    
    .fitbot-chat-messages {
        padding: 15px;
    }
    
    .fitbot-message-bubble {
        max-width: 90%;
    }
}
</style>

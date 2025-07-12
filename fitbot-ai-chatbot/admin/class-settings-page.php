<?php
/**
 * Settings Page for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Settings_Page {
    
    public function render() {
        ?>
        <div class="wrap">
            <h1><?php _e('FITBOT AI Chatbot Settings', 'fitbot-ai-chatbot'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('fitbot_settings_nonce'); ?>
                
                <div class="fitbot-settings-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#api-settings" class="nav-tab nav-tab-active"><?php _e('API Settings', 'fitbot-ai-chatbot'); ?></a>
                        <a href="#woo-settings" class="nav-tab"><?php _e('WooCommerce', 'fitbot-ai-chatbot'); ?></a>
                        <a href="#ui-settings" class="nav-tab"><?php _e('UI Settings', 'fitbot-ai-chatbot'); ?></a>
                        <a href="#upgrade-settings" class="nav-tab"><?php _e('Upgrade Messages', 'fitbot-ai-chatbot'); ?></a>
                    </nav>
                    
                    <div id="api-settings" class="tab-content active">
                        <h2><?php _e('OpenAI API Configuration', 'fitbot-ai-chatbot'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_openai_api_key"><?php _e('OpenAI API Key', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="password" id="fitbot_openai_api_key" name="fitbot_openai_api_key" 
                                           value="<?php echo esc_attr(get_option('fitbot_openai_api_key')); ?>" 
                                           class="regular-text" />
                                    <p class="description">
                                        <?php _e('Enter your OpenAI API key for GPT-4 Turbo access.', 'fitbot-ai-chatbot'); ?>
                                        <a href="https://platform.openai.com/api-keys" target="_blank"><?php _e('Get API Key', 'fitbot-ai-chatbot'); ?></a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_chatbot_delay"><?php _e('Chatbot Delay (ms)', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="fitbot_chatbot_delay" name="fitbot_chatbot_delay" 
                                           value="<?php echo esc_attr(get_option('fitbot_chatbot_delay', 3000)); ?>" 
                                           min="1000" max="10000" step="500" />
                                    <p class="description"><?php _e('Delay before chatbot appears on page (milliseconds).', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_chatbot_greeting"><?php _e('Chatbot Greeting', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <textarea id="fitbot_chatbot_greeting" name="fitbot_chatbot_greeting" 
                                              rows="3" cols="50" class="large-text"><?php echo esc_textarea(get_option('fitbot_chatbot_greeting', 'Hello! I\'m FITBOT, your AI fitness assistant. What are your health and fitness goals today?')); ?></textarea>
                                    <p class="description"><?php _e('Initial greeting message shown to users.', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="woo-settings" class="tab-content">
                        <h2><?php _e('WooCommerce Integration', 'fitbot-ai-chatbot'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_woo_start_product_id"><?php _e('Start Plan Product ID', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="fitbot_woo_start_product_id" name="fitbot_woo_start_product_id" 
                                           value="<?php echo esc_attr(get_option('fitbot_woo_start_product_id')); ?>" />
                                    <p class="description"><?php _e('WooCommerce product ID for Start plan (€5/mo).', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_woo_pro_product_id"><?php _e('Pro Plan Product ID', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="fitbot_woo_pro_product_id" name="fitbot_woo_pro_product_id" 
                                           value="<?php echo esc_attr(get_option('fitbot_woo_pro_product_id')); ?>" />
                                    <p class="description"><?php _e('WooCommerce product ID for Pro plan (€12/mo).', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_woo_vip_product_id"><?php _e('VIP Plan Product ID', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="fitbot_woo_vip_product_id" name="fitbot_woo_vip_product_id" 
                                           value="<?php echo esc_attr(get_option('fitbot_woo_vip_product_id')); ?>" />
                                    <p class="description"><?php _e('WooCommerce product ID for VIP plan (€24/mo).', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php if (!class_exists('WooCommerce')): ?>
                            <div class="notice notice-warning">
                                <p><?php _e('WooCommerce is not installed or activated. Please install WooCommerce to use subscription features.', 'fitbot-ai-chatbot'); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!function_exists('wcs_user_has_subscription')): ?>
                            <div class="notice notice-warning">
                                <p><?php _e('WooCommerce Subscriptions is not installed or activated. Please install WooCommerce Subscriptions to use plan-based features.', 'fitbot-ai-chatbot'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="ui-settings" class="tab-content">
                        <h2><?php _e('Chatbot UI Customization', 'fitbot-ai-chatbot'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_chatbot_color"><?php _e('Primary Color', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="color" id="fitbot_chatbot_color" name="fitbot_chatbot_color" 
                                           value="<?php echo esc_attr(get_option('fitbot_chatbot_color', '#0073aa')); ?>" />
                                    <p class="description"><?php _e('Primary color for chatbot UI elements.', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_chatbot_position"><?php _e('Chatbot Position', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <select id="fitbot_chatbot_position" name="fitbot_chatbot_position">
                                        <option value="bottom-right" <?php selected(get_option('fitbot_chatbot_position', 'bottom-right'), 'bottom-right'); ?>>
                                            <?php _e('Bottom Right', 'fitbot-ai-chatbot'); ?>
                                        </option>
                                        <option value="bottom-left" <?php selected(get_option('fitbot_chatbot_position'), 'bottom-left'); ?>>
                                            <?php _e('Bottom Left', 'fitbot-ai-chatbot'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Position of the chatbot button on the page.', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_enable_sound"><?php _e('Enable Sound', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="fitbot_enable_sound" name="fitbot_enable_sound" value="1" 
                                           <?php checked(get_option('fitbot_enable_sound', 1), 1); ?> />
                                    <label for="fitbot_enable_sound"><?php _e('Play notification sounds for new messages', 'fitbot-ai-chatbot'); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_enable_typing_indicator"><?php _e('Typing Indicator', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="fitbot_enable_typing_indicator" name="fitbot_enable_typing_indicator" value="1" 
                                           <?php checked(get_option('fitbot_enable_typing_indicator', 1), 1); ?> />
                                    <label for="fitbot_enable_typing_indicator"><?php _e('Show typing indicator while AI is responding', 'fitbot-ai-chatbot'); ?></label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="upgrade-settings" class="tab-content">
                        <h2><?php _e('Upgrade Messages', 'fitbot-ai-chatbot'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_upgrade_message_pro"><?php _e('Pro Plan Upgrade Message', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <textarea id="fitbot_upgrade_message_pro" name="fitbot_upgrade_message_pro" 
                                              rows="3" cols="50" class="large-text"><?php echo esc_textarea(get_option('fitbot_upgrade_message_pro', 'This feature is available in the Pro plan (€12/mo). Would you like to upgrade for daily workouts, 3 recipes per day, and nutrition facts?')); ?></textarea>
                                    <p class="description"><?php _e('Message shown when suggesting upgrade to Pro plan.', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fitbot_upgrade_message_vip"><?php _e('VIP Plan Upgrade Message', 'fitbot-ai-chatbot'); ?></label>
                                </th>
                                <td>
                                    <textarea id="fitbot_upgrade_message_vip" name="fitbot_upgrade_message_vip" 
                                              rows="3" cols="50" class="large-text"><?php echo esc_textarea(get_option('fitbot_upgrade_message_vip', 'This feature is available in the VIP plan (€24/mo). Would you like to upgrade for unlimited recipes, smart health answers, and specialized meal plans?')); ?></textarea>
                                    <p class="description"><?php _e('Message shown when suggesting upgrade to VIP plan.', 'fitbot-ai-chatbot'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(__('Save Settings', 'fitbot-ai-chatbot')); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                
                $(this).addClass('nav-tab-active');
                
                var target = $(this).attr('href');
                $(target).addClass('active');
            });
        });
        </script>
        
        <style>
        .fitbot-settings-tabs {
            margin-top: 20px;
        }
        
        .tab-content {
            display: none;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-top: none;
            padding: 20px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-content h2 {
            margin-top: 0;
        }
        
        .form-table th {
            width: 200px;
        }
        
        .notice {
            margin: 20px 0;
        }
        </style>
        <?php
    }
}

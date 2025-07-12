<?php
/**
 * Admin Menu for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Admin_Menu {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_menu_page(
            __('FITBOT AI Chatbot', 'fitbot-ai-chatbot'),
            __('FITBOT AI', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-settings',
            array($this, 'settings_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'fitbot-settings',
            __('Settings', 'fitbot-ai-chatbot'),
            __('Settings', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'fitbot-settings',
            __('Content Management', 'fitbot-ai-chatbot'),
            __('Content', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-content',
            array($this, 'content_page')
        );
        
        add_submenu_page(
            'fitbot-settings',
            __('Analytics', 'fitbot-ai-chatbot'),
            __('Analytics', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'fitbot-settings',
            __('User Management', 'fitbot-ai-chatbot'),
            __('Users', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-users',
            array($this, 'users_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('fitbot_api_settings', 'fitbot_openai_api_key');
        register_setting('fitbot_api_settings', 'fitbot_chatbot_delay');
        register_setting('fitbot_api_settings', 'fitbot_chatbot_greeting');
        
        register_setting('fitbot_woo_settings', 'fitbot_woo_start_product_id');
        register_setting('fitbot_woo_settings', 'fitbot_woo_pro_product_id');
        register_setting('fitbot_woo_settings', 'fitbot_woo_vip_product_id');
        
        register_setting('fitbot_ui_settings', 'fitbot_chatbot_color');
        register_setting('fitbot_ui_settings', 'fitbot_chatbot_position');
        register_setting('fitbot_ui_settings', 'fitbot_chatbot_icon');
        register_setting('fitbot_ui_settings', 'fitbot_enable_sound');
        register_setting('fitbot_ui_settings', 'fitbot_enable_typing_indicator');
        
        register_setting('fitbot_upgrade_settings', 'fitbot_upgrade_message_pro');
        register_setting('fitbot_upgrade_settings', 'fitbot_upgrade_message_vip');
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings_page = new Fitbot_Settings_Page();
        $settings_page->render();
    }
    
    /**
     * Content management page callback
     */
    public function content_page() {
        $content_page = new Fitbot_Content_Upload_Page();
        $content_page->render();
    }
    
    /**
     * Analytics page callback
     */
    public function analytics_page() {
        $this->render_analytics_page();
    }
    
    /**
     * Users page callback
     */
    public function users_page() {
        $this->render_users_page();
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('fitbot_settings_nonce');
        
        $settings = array(
            'fitbot_openai_api_key',
            'fitbot_chatbot_delay',
            'fitbot_chatbot_greeting',
            'fitbot_woo_start_product_id',
            'fitbot_woo_pro_product_id',
            'fitbot_woo_vip_product_id',
            'fitbot_chatbot_color',
            'fitbot_chatbot_position',
            'fitbot_enable_sound',
            'fitbot_enable_typing_indicator',
            'fitbot_upgrade_message_pro',
            'fitbot_upgrade_message_vip'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, sanitize_text_field($_POST[$setting]));
            }
        }
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'fitbot-ai-chatbot') . '</p></div>';
        });
    }
    
    /**
     * Render analytics page
     */
    private function render_analytics_page() {
        global $wpdb;
        
        $conversations_table = $wpdb->prefix . 'fitbot_conversations';
        $usage_table = $wpdb->prefix . 'fitbot_usage';
        
        $total_conversations = $wpdb->get_var("SELECT COUNT(*) FROM $conversations_table");
        $today_conversations = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $conversations_table WHERE DATE(created_at) = %s",
            current_time('Y-m-d')
        ));
        
        $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $conversations_table");
        $active_users_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM $conversations_table WHERE DATE(created_at) = %s",
            current_time('Y-m-d')
        ));
        
        $recipe_requests = $wpdb->get_var("SELECT SUM(recipes_requested) FROM $usage_table");
        $workout_requests = $wpdb->get_var("SELECT SUM(workouts_requested) FROM $usage_table");
        $questions_asked = $wpdb->get_var("SELECT SUM(questions_asked) FROM $usage_table");
        
        $popular_recipes = $wpdb->get_results(
            "SELECT post_title, meta_value as view_count 
             FROM {$wpdb->posts} p 
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'fitbot_recipe' 
             AND pm.meta_key = '_fitbot_view_count' 
             ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC 
             LIMIT 5"
        );
        
        ?>
        <div class="wrap">
            <h1><?php _e('FITBOT Analytics', 'fitbot-ai-chatbot'); ?></h1>
            
            <div class="fitbot-analytics-grid">
                <div class="fitbot-stat-card">
                    <h3><?php _e('Total Conversations', 'fitbot-ai-chatbot'); ?></h3>
                    <div class="stat-number"><?php echo number_format($total_conversations); ?></div>
                </div>
                
                <div class="fitbot-stat-card">
                    <h3><?php _e('Today\'s Conversations', 'fitbot-ai-chatbot'); ?></h3>
                    <div class="stat-number"><?php echo number_format($today_conversations); ?></div>
                </div>
                
                <div class="fitbot-stat-card">
                    <h3><?php _e('Total Users', 'fitbot-ai-chatbot'); ?></h3>
                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                </div>
                
                <div class="fitbot-stat-card">
                    <h3><?php _e('Active Users Today', 'fitbot-ai-chatbot'); ?></h3>
                    <div class="stat-number"><?php echo number_format($active_users_today); ?></div>
                </div>
            </div>
            
            <div class="fitbot-analytics-row">
                <div class="fitbot-analytics-col">
                    <h3><?php _e('Usage Statistics', 'fitbot-ai-chatbot'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Type', 'fitbot-ai-chatbot'); ?></th>
                                <th><?php _e('Total Requests', 'fitbot-ai-chatbot'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php _e('Recipe Requests', 'fitbot-ai-chatbot'); ?></td>
                                <td><?php echo number_format($recipe_requests); ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('Workout Requests', 'fitbot-ai-chatbot'); ?></td>
                                <td><?php echo number_format($workout_requests); ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('Questions Asked', 'fitbot-ai-chatbot'); ?></td>
                                <td><?php echo number_format($questions_asked); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="fitbot-analytics-col">
                    <h3><?php _e('Popular Recipes', 'fitbot-ai-chatbot'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Recipe', 'fitbot-ai-chatbot'); ?></th>
                                <th><?php _e('Views', 'fitbot-ai-chatbot'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($popular_recipes)): ?>
                                <?php foreach ($popular_recipes as $recipe): ?>
                                    <tr>
                                        <td><?php echo esc_html($recipe->post_title); ?></td>
                                        <td><?php echo number_format($recipe->view_count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2"><?php _e('No data available', 'fitbot-ai-chatbot'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <style>
        .fitbot-analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .fitbot-stat-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .fitbot-stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .fitbot-analytics-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .fitbot-analytics-col {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .fitbot-analytics-row {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Render users page
     */
    private function render_users_page() {
        global $wpdb;
        
        $subscription_checker = new Fitbot_Woo_Subscription_Check();
        
        $users_with_conversations = $wpdb->get_results(
            "SELECT DISTINCT u.ID, u.display_name, u.user_email, 
                    COUNT(c.id) as conversation_count,
                    MAX(c.created_at) as last_conversation
             FROM {$wpdb->users} u
             JOIN {$wpdb->prefix}fitbot_conversations c ON u.ID = c.user_id
             GROUP BY u.ID
             ORDER BY last_conversation DESC
             LIMIT 50"
        );
        
        ?>
        <div class="wrap">
            <h1><?php _e('FITBOT Users', 'fitbot-ai-chatbot'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('User', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Email', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Plan', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Conversations', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Last Activity', 'fitbot-ai-chatbot'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users_with_conversations)): ?>
                        <?php foreach ($users_with_conversations as $user): ?>
                            <?php $plan = $subscription_checker->get_user_plan($user->ID); ?>
                            <tr>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td>
                                    <span class="fitbot-plan-badge fitbot-plan-<?php echo esc_attr($plan); ?>">
                                        <?php echo esc_html($subscription_checker->get_plan_display_name($plan)); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user->conversation_count); ?></td>
                                <td><?php echo human_time_diff(strtotime($user->last_conversation), current_time('timestamp')) . ' ago'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5"><?php _e('No users found', 'fitbot-ai-chatbot'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .fitbot-plan-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .fitbot-plan-none {
            background: #f1f1f1;
            color: #666;
        }
        
        .fitbot-plan-start {
            background: #e1f5fe;
            color: #0277bd;
        }
        
        .fitbot-plan-pro {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .fitbot-plan-vip {
            background: #fff3e0;
            color: #ef6c00;
        }
        </style>
        <?php
    }
}

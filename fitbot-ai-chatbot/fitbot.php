<?php
/**
 * Plugin Name: FITBOT AI Chatbot
 * Plugin URI: https://www.coachlatif.com
 * Description: AI-powered fitness chatbot with subscription-based features for personalized health and fitness guidance.
 * Version: 1.0.0
 * Author: Coach Latif
 * Author URI: https://www.coachlatif.com
 * Text Domain: fitbot-ai-chatbot
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FITBOT_PLUGIN_VERSION', '1.0.0');
define('FITBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FITBOT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FITBOT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main FITBOT AI Chatbot Plugin Class
 */
class FitbotAIChatbot {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // add_action('wp_footer', array($this, 'render_chatbot_widget'));
        
        add_shortcode('fitbot_assistant', array($this, 'render_assistant_shortcode'));
        
        add_action('wp_ajax_fitbot_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_nopriv_fitbot_chat', array($this, 'handle_chat_request'));
        add_action('wp_ajax_fitbot_get_user_plan', array($this, 'get_user_plan'));
        add_action('wp_ajax_nopriv_fitbot_get_user_plan', array($this, 'get_user_plan'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once FITBOT_PLUGIN_PATH . 'includes/class-fitbot-core.php';
        require_once FITBOT_PLUGIN_PATH . 'includes/class-gpt-chat-api.php';
        require_once FITBOT_PLUGIN_PATH . 'includes/class-woo-subscription-check.php';
        require_once FITBOT_PLUGIN_PATH . 'includes/class-recipe-workout-loader.php';
        require_once FITBOT_PLUGIN_PATH . 'includes/class-custom-post-types.php';
        require_once FITBOT_PLUGIN_PATH . 'includes/class-ajax-handlers.php';
        
        if (is_admin()) {
            require_once FITBOT_PLUGIN_PATH . 'admin/class-admin-menu.php';
            require_once FITBOT_PLUGIN_PATH . 'admin/class-content-upload-page.php';
            require_once FITBOT_PLUGIN_PATH . 'admin/class-settings-page.php';
            require_once FITBOT_PLUGIN_PATH . 'admin/class-assistant-manager.php';
        }
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        add_action('admin_notices', array($this, 'check_tables_notice'));
        
        new Fitbot_Core();
        new Fitbot_Custom_Post_Types();
        new Fitbot_Ajax_Handlers();
        
        if (is_admin()) {
            new Fitbot_Admin_Menu();
            new Fitbot_Assistant_Manager();
            new Fitbot_Settings_Page();
        }
        
        load_plugin_textdomain('fitbot-ai-chatbot', false, dirname(FITBOT_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'fitbot-chatbot',
            FITBOT_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery'),
            FITBOT_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fitbot-chatbot',
            FITBOT_PLUGIN_URL . 'assets/css/chatbot.css',
            array(),
            FITBOT_PLUGIN_VERSION
        );
        
        wp_localize_script('fitbot-chatbot', 'fitbot_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fitbot_nonce'),
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in()
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'fitbot') === false) {
            return;
        }
        
        wp_enqueue_script(
            'fitbot-admin',
            FITBOT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FITBOT_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fitbot-admin',
            FITBOT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FITBOT_PLUGIN_VERSION
        );
    }
    
    /**
     * Render chatbot widget in footer
     */
    public function render_chatbot_widget() {
        include FITBOT_PLUGIN_PATH . 'templates/chatbot-widget.php';
    }
    
    /**
     * Handle chat AJAX requests
     */
    public function handle_chat_request() {
        check_ajax_referer('fitbot_nonce', 'nonce');
        
        $ajax_handler = new Fitbot_Ajax_Handlers();
        $ajax_handler->handle_chat_request();
    }
    
    /**
     * Get user subscription plan
     */
    public function get_user_plan() {
        check_ajax_referer('fitbot_nonce', 'nonce');
        
        $subscription_checker = new Fitbot_Woo_Subscription_Check();
        $plan = $subscription_checker->get_user_plan(get_current_user_id());
        
        wp_send_json_success(array('plan' => $plan));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_tables();
        
        $this->set_default_options();
        
        $this->create_default_assistants();
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $creation_results = array();
        
        $tables = array(
            'fitbot_conversations' => "
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                message_type varchar(20) NOT NULL,
                message text NOT NULL,
                response text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id)
            ",
            'fitbot_usage' => "
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                date date NOT NULL,
                recipes_requested int(11) DEFAULT 0,
                workouts_requested int(11) DEFAULT 0,
                questions_asked int(11) DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY user_date (user_id, date)
            ",
            'fitbot_assistants' => "
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL,
                slug varchar(50) NOT NULL,
                prompt text NOT NULL,
                personality varchar(50) DEFAULT 'professional',
                price decimal(10,2) NOT NULL,
                currency varchar(3) DEFAULT 'EUR',
                daily_limit int(11) DEFAULT 10,
                monthly_limit int(11) DEFAULT 300,
                woo_product_id bigint(20),
                color varchar(7) DEFAULT '#0073aa',
                greeting_message text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            "
        );
        
        foreach ($tables as $table_name => $columns) {
            $result = $this->create_table_with_fallback($table_name, $columns, $charset_collate);
            $creation_results[$table_name] = $result;
            
            error_log("FITBOT Table Creation - $table_name: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . " - " . $result['message']);
        }
        
        // Store detailed results for debugging
        update_option('fitbot_table_creation_results', $creation_results);
        update_option('fitbot_table_creation_attempted', current_time('mysql'));
    }
    
    /**
     * Create table with comprehensive fallback and debugging
     */
    private function create_table_with_fallback($table_name, $columns, $charset_collate) {
        global $wpdb;
        
        $full_table_name = $wpdb->prefix . $table_name;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name) {
            return array('success' => true, 'message' => "Table $table_name already exists");
        }
        
        $test_result = $this->test_database_permissions();
        if (!$test_result['success']) {
            return array('success' => false, 'message' => "Database permission error: " . $test_result['message']);
        }
        
        $dbdelta_result = $this->try_dbdelta_creation($full_table_name, $columns, $charset_collate);
        if ($dbdelta_result['success']) {
            return $dbdelta_result;
        }
        
        $direct_result = $this->try_direct_sql_creation($full_table_name, $columns, $charset_collate);
        if ($direct_result['success']) {
            return $direct_result;
        }
        
        $basic_result = $this->try_basic_sql_creation($full_table_name, $table_name, $charset_collate);
        if ($basic_result['success']) {
            return $basic_result;
        }
        
        return array('success' => false, 'message' => "All table creation methods failed. dbDelta: " . $dbdelta_result['message'] . " | Direct SQL: " . $direct_result['message'] . " | Basic SQL: " . $basic_result['message']);
    }
    
    /**
     * Test database permissions
     */
    private function test_database_permissions() {
        global $wpdb;
        
        $test_table = $wpdb->prefix . 'fitbot_test_' . time();
        $sql = "CREATE TABLE $test_table (id INT AUTO_INCREMENT PRIMARY KEY, test_col VARCHAR(10))";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Cannot create tables: ' . $wpdb->last_error);
        }
        
        $wpdb->query("DROP TABLE IF EXISTS $test_table");
        
        return array('success' => true, 'message' => 'Database permissions OK');
    }
    
    /**
     * Try dbDelta table creation
     */
    private function try_dbdelta_creation($full_table_name, $columns, $charset_collate) {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql = "CREATE TABLE $full_table_name (
            $columns
        ) $charset_collate;";
        
        $result = dbDelta($sql);
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name) {
            return array('success' => true, 'message' => 'Created via dbDelta');
        }
        
        return array('success' => false, 'message' => 'dbDelta failed silently');
    }
    
    /**
     * Try direct SQL table creation
     */
    private function try_direct_sql_creation($full_table_name, $columns, $charset_collate) {
        global $wpdb;
        
        $sql = "CREATE TABLE IF NOT EXISTS $full_table_name (
            $columns
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Direct SQL error: ' . $wpdb->last_error);
        }
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name) {
            return array('success' => true, 'message' => 'Created via direct SQL');
        }
        
        return array('success' => false, 'message' => 'Direct SQL executed but table not found');
    }
    
    /**
     * Try basic SQL table creation without advanced features
     */
    private function try_basic_sql_creation($full_table_name, $table_name, $charset_collate) {
        global $wpdb;
        
        if ($table_name === 'fitbot_assistants') {
            $sql = "CREATE TABLE IF NOT EXISTS $full_table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) NOT NULL,
                prompt TEXT NOT NULL,
                personality VARCHAR(50) DEFAULT 'professional',
                price DECIMAL(10,2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'EUR',
                daily_limit INT DEFAULT 10,
                monthly_limit INT DEFAULT 300,
                woo_product_id BIGINT,
                color VARCHAR(7) DEFAULT '#0073aa',
                greeting_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate";
        } else if ($table_name === 'fitbot_conversations') {
            $sql = "CREATE TABLE IF NOT EXISTS $full_table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                message_type VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                response TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate";
        } else if ($table_name === 'fitbot_usage') {
            $sql = "CREATE TABLE IF NOT EXISTS $full_table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                date DATE NOT NULL,
                recipes_requested INT DEFAULT 0,
                workouts_requested INT DEFAULT 0,
                questions_asked INT DEFAULT 0
            ) $charset_collate";
        }
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Basic SQL error: ' . $wpdb->last_error);
        }
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name) {
            if ($table_name === 'fitbot_assistants') {
                $wpdb->query("ALTER TABLE $full_table_name ADD UNIQUE KEY slug (slug)");
            }
            if ($table_name === 'fitbot_usage') {
                $wpdb->query("ALTER TABLE $full_table_name ADD UNIQUE KEY user_date (user_id, date)");
            }
            return array('success' => true, 'message' => 'Created via basic SQL');
        }
        
        return array('success' => false, 'message' => 'Basic SQL executed but table not found');
    }
    
    /**
     * Manual table creation for troubleshooting
     */
    public function manual_create_tables() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'fitbot-ai-chatbot'));
        }
        
        $this->create_tables();
        
        $creation_results = get_option('fitbot_table_creation_results', array());
        $missing_tables = array();
        $success_messages = array();
        $error_messages = array();
        
        global $wpdb;
        $required_tables = array(
            'fitbot_assistants' => $wpdb->prefix . 'fitbot_assistants',
            'fitbot_conversations' => $wpdb->prefix . 'fitbot_conversations', 
            'fitbot_usage' => $wpdb->prefix . 'fitbot_usage'
        );
        
        foreach ($required_tables as $name => $full_name) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_name'") != $full_name) {
                $missing_tables[] = $name;
                if (isset($creation_results[$name])) {
                    $error_messages[] = "$name: " . $creation_results[$name]['message'];
                }
            } else {
                if (isset($creation_results[$name])) {
                    $success_messages[] = "$name: " . $creation_results[$name]['message'];
                }
            }
        }
        
        if (empty($missing_tables)) {
            $message = 'All database tables created successfully! ' . implode(', ', $success_messages);
            $message_type = 'success';
        } else {
            $message = 'Table creation failed for: ' . implode(', ', $missing_tables) . '. Detailed errors: ' . implode(' | ', $error_messages) . '. Please contact your hosting provider about database permissions.';
            $message_type = 'error';
        }
        
        wp_redirect(admin_url('admin.php?page=fitbot-settings&message=' . urlencode($message) . '&message_type=' . $message_type));
        exit;
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'openai_api_key' => '',
            'chatbot_delay' => 3000,
            'chatbot_greeting' => 'Hello! I\'m FITBOT, your AI fitness assistant. What are your health and fitness goals today?',
            'upgrade_message_pro' => 'This feature is available in the Pro plan. Would you like to upgrade?',
            'upgrade_message_vip' => 'This feature is available in the VIP plan. Would you like to upgrade?',
            'woo_start_product_id' => '',
            'woo_pro_product_id' => '',
            'woo_vip_product_id' => ''
        );
        
        foreach ($default_options as $key => $value) {
            if (get_option('fitbot_' . $key) === false) {
                add_option('fitbot_' . $key, $value);
            }
        }
    }
    
    /**
     * Create default assistants on activation
     */
    private function create_default_assistants() {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($existing > 0) {
            return;
        }
        
        $assistants = array(
            array(
                'name' => 'Start Plan Assistant',
                'slug' => 'start',
                'price' => 5.00,
                'currency' => 'EUR',
                'prompt' => 'You are a basic fitness assistant focused on helping beginners start their fitness journey. Provide simple, encouraging advice for basic workouts and healthy eating habits.',
                'personality' => 'encouraging',
                'daily_limit' => 5,
                'monthly_limit' => 100,
                'color' => '#28a745',
                'greeting_message' => 'Hi! I\'m your Start Plan Assistant. Ready to begin your fitness journey?'
            ),
            array(
                'name' => 'Pro Plan Assistant',
                'slug' => 'pro',
                'price' => 12.00,
                'currency' => 'EUR',
                'prompt' => 'You are a professional fitness coach with expertise in intermediate to advanced training programs, nutrition planning, and workout optimization.',
                'personality' => 'professional',
                'daily_limit' => 15,
                'monthly_limit' => 300,
                'color' => '#007cba',
                'greeting_message' => 'Hello! I\'m your Pro Plan Assistant. Let\'s optimize your fitness routine!'
            ),
            array(
                'name' => 'VIP Plan Assistant',
                'slug' => 'vip',
                'price' => 24.00,
                'currency' => 'EUR',
                'prompt' => 'You are an elite wellness expert providing comprehensive health guidance including advanced fitness programs, detailed nutrition analysis, medical wellness advice, and personalized health optimization strategies.',
                'personality' => 'expert',
                'daily_limit' => 50,
                'monthly_limit' => 1000,
                'color' => '#dc3545',
                'greeting_message' => 'Welcome! I\'m your VIP Plan Assistant. Let\'s achieve your ultimate health and fitness goals!'
            )
        );
        
        foreach ($assistants as $assistant) {
            $wpdb->insert($table, $assistant);
        }
    }
    
    /**
     * Get assistant by slug
     */
    public function get_assistant_by_slug($slug) {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE slug = %s",
            $slug
        ), ARRAY_A);
    }
    
    /**
     * Get assistant by slug with alias support
     */
    public function get_assistant_by_slug_with_aliases($slug) {
        $slug_aliases = array(
            'starter-plan' => 'start',
            'starter' => 'start',
            'basic' => 'start',
            'pro-plan' => 'pro',
            'professional' => 'pro',
            'vip-plan' => 'vip',
            'premium' => 'vip'
        );
        
        if (isset($slug_aliases[$slug])) {
            $slug = $slug_aliases[$slug];
        }
        
        return $this->get_assistant_by_slug($slug);
    }
    
    /**
     * Render assistant shortcode
     */
    public function render_assistant_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'slug' => '',
            'width' => '100%',
            'height' => '500px'
        ), $atts);
        
        if (empty($atts['slug'])) {
            return '<div class="fitbot-error" style="padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">Error: Assistant slug is required. Available slugs: start, pro, vip</div>';
        }
        
        $assistant = $this->get_assistant_by_slug_with_aliases($atts['slug']);
        if (!$assistant) {
            global $wpdb;
            $table = $wpdb->prefix . 'fitbot_assistants';
            $available_slugs = $wpdb->get_col("SELECT slug FROM $table ORDER BY slug");
            $slugs_list = !empty($available_slugs) ? implode(', ', $available_slugs) : 'start, pro, vip';
            
            return '<div class="fitbot-error" style="padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">Error: Assistant "' . esc_html($atts['slug']) . '" not found. Available slugs: ' . esc_html($slugs_list) . '</div>';
        }
        
        $template_path = FITBOT_PLUGIN_PATH . 'templates/assistant-shortcode.php';
        if (!file_exists($template_path)) {
            return '<div class="fitbot-error" style="padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px; color: #c62828;">Error: Assistant template file not found.</div>';
        }
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('FITBOT AI Chatbot requires WooCommerce to be installed and activated.', 'fitbot-ai-chatbot');
        echo '</p></div>';
    }
    
    /**
     * Check if required tables exist and show notice
     */
    public function check_tables_notice() {
        global $wpdb;
        
        $assistants_table = $wpdb->prefix . 'fitbot_assistants';
        $conversations_table = $wpdb->prefix . 'fitbot_conversations';
        $usage_table = $wpdb->prefix . 'fitbot_usage';
        
        $missing_tables = array();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$assistants_table'") != $assistants_table) {
            $missing_tables[] = 'fitbot_assistants';
        }
        if ($wpdb->get_var("SHOW TABLES LIKE '$conversations_table'") != $conversations_table) {
            $missing_tables[] = 'fitbot_conversations';
        }
        if ($wpdb->get_var("SHOW TABLES LIKE '$usage_table'") != $usage_table) {
            $missing_tables[] = 'fitbot_usage';
        }
        
        if (!empty($missing_tables)) {
            $create_url = admin_url('admin.php?page=fitbot-settings&action=create_tables&_wpnonce=' . wp_create_nonce('fitbot_create_tables'));
            echo '<div class="notice notice-error"><p>';
            echo __('FITBOT AI Chatbot: Required database tables are missing: ', 'fitbot-ai-chatbot') . implode(', ', $missing_tables);
            echo '<br><a href="' . esc_url($create_url) . '" class="button button-primary">' . __('Create Tables Now', 'fitbot-ai-chatbot') . '</a>';
            echo '</p></div>';
        }
    }
}

function fitbot_ai_chatbot_init() {
    return FitbotAIChatbot::get_instance();
}

add_action('plugins_loaded', 'fitbot_ai_chatbot_init');

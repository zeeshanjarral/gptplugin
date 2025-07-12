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
        
        new Fitbot_Core();
        new Fitbot_Custom_Post_Types();
        new Fitbot_Ajax_Handlers();
        
        if (is_admin()) {
            new Fitbot_Admin_Menu();
            new Fitbot_Assistant_Manager();
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
        
        $table_name = $wpdb->prefix . 'fitbot_conversations';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            message_type varchar(20) NOT NULL,
            message text NOT NULL,
            response text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        $usage_table = $wpdb->prefix . 'fitbot_usage';
        $usage_sql = "CREATE TABLE $usage_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            date date NOT NULL,
            recipes_requested int(11) DEFAULT 0,
            workouts_requested int(11) DEFAULT 0,
            questions_asked int(11) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY user_date (user_id, date)
        ) $charset_collate;";
        
        $assistants_table = $wpdb->prefix . 'fitbot_assistants';
        $assistants_sql = "CREATE TABLE $assistants_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(50) NOT NULL UNIQUE,
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
            KEY slug (slug)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($usage_sql);
        dbDelta($assistants_sql);
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
            return '<div class="fitbot-error">Error: Assistant slug is required</div>';
        }
        
        $assistant = $this->get_assistant_by_slug($atts['slug']);
        if (!$assistant) {
            return '<div class="fitbot-error">Error: Assistant not found</div>';
        }
        
        ob_start();
        include FITBOT_PLUGIN_PATH . 'templates/assistant-shortcode.php';
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
}

function fitbot_ai_chatbot_init() {
    return FitbotAIChatbot::get_instance();
}

add_action('plugins_loaded', 'fitbot_ai_chatbot_init');

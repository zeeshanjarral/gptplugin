<?php
/**
 * Core functionality for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Core {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_head', array($this, 'add_meta_tags'));
    }
    
    /**
     * Add meta tags for chatbot
     */
    public function add_meta_tags() {
        echo '<meta name="fitbot-enabled" content="true">' . "\n";
    }
    
    /**
     * Get plugin settings
     */
    public static function get_setting($key, $default = '') {
        return get_option('fitbot_' . $key, $default);
    }
    
    /**
     * Update plugin setting
     */
    public static function update_setting($key, $value) {
        return update_option('fitbot_' . $key, $value);
    }
    
    /**
     * Log conversation
     */
    public static function log_conversation($user_id, $message_type, $message, $response = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fitbot_conversations';
        
        return $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'message_type' => $message_type,
                'message' => $message,
                'response' => $response,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get user conversation history
     */
    public static function get_conversation_history($user_id, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fitbot_conversations';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
                $user_id,
                $limit
            )
        );
    }
    
    /**
     * Track user usage
     */
    public static function track_usage($user_id, $type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fitbot_usage';
        $today = current_time('Y-m-d');
        
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND date = %s",
                $user_id,
                $today
            )
        );
        
        if ($existing) {
            $column = $type . '_requested';
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_name SET $column = $column + 1 WHERE user_id = %d AND date = %s",
                    $user_id,
                    $today
                )
            );
        } else {
            $data = array(
                'user_id' => $user_id,
                'date' => $today,
                'recipes_requested' => $type === 'recipes' ? 1 : 0,
                'workouts_requested' => $type === 'workouts' ? 1 : 0,
                'questions_asked' => $type === 'questions' ? 1 : 0
            );
            
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Get user daily usage
     */
    public static function get_daily_usage($user_id, $date = null) {
        global $wpdb;
        
        if (!$date) {
            $date = current_time('Y-m-d');
        }
        
        $table_name = $wpdb->prefix . 'fitbot_usage';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND date = %s",
                $user_id,
                $date
            )
        );
    }
    
    /**
     * Check if user has reached daily limit
     */
    public static function has_reached_limit($user_id, $type, $plan) {
        $usage = self::get_daily_usage($user_id);
        
        if (!$usage) {
            return false;
        }
        
        $limits = array(
            'start' => array(
                'recipes' => 1,
                'workouts' => 0,
                'questions' => 10
            ),
            'pro' => array(
                'recipes' => 3,
                'workouts' => 1,
                'questions' => 20
            ),
            'vip' => array(
                'recipes' => -1, // unlimited
                'workouts' => -1, // unlimited
                'questions' => -1 // unlimited
            )
        );
        
        $plan_limits = $limits[$plan] ?? $limits['start'];
        $limit = $plan_limits[$type] ?? 0;
        
        if ($limit === -1) {
            return false; // unlimited
        }
        
        $current_usage = $usage->{$type . '_requested'} ?? 0;
        
        return $current_usage >= $limit;
    }
}

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
    public static function log_conversation($user_id, $message_type, $message, $response = '', $assistant_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fitbot_conversations';
        
        $data = array(
            'user_id' => $user_id,
            'message_type' => $message_type,
            'message' => $message,
            'response' => $response,
            'created_at' => current_time('mysql')
        );
        
        if ($assistant_id) {
            $data['assistant_id'] = $assistant_id;
        }
        
        return $wpdb->insert(
            $table_name,
            $data,
            array('%d', '%s', '%s', '%s', '%s', '%d')
        );
    }
    
    /**
     * Get user conversation history
     */
    public static function get_conversation_history($user_id, $limit = 10, $assistant_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fitbot_conversations';
        
        $where_clause = "WHERE user_id = %d";
        $params = array($user_id);
        
        if ($assistant_id) {
            $where_clause .= " AND assistant_id = %d";
            $params[] = $assistant_id;
        }
        
        $params[] = $limit;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d",
                ...$params
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
    public static function get_daily_usage($user_id, $assistant_id = null, $date = null) {
        global $wpdb;
        
        if (!$date) {
            $date = current_time('Y-m-d');
        }
        
        if ($assistant_id) {
            $conversations_table = $wpdb->prefix . 'fitbot_conversations';
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $conversations_table 
                     WHERE user_id = %d AND assistant_id = %d 
                     AND DATE(created_at) = %s",
                    $user_id,
                    $assistant_id,
                    $date
                )
            );
            return intval($count);
        }
        
        $table_name = $wpdb->prefix . 'fitbot_usage';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND date = %s",
                $user_id,
                $date
            )
        );
        
        if (!$result) {
            return 0;
        }
        
        return $result->recipes_requested + $result->workouts_requested + $result->questions_asked;
    }
    
    /**
     * Get user monthly usage
     */
    public static function get_monthly_usage($user_id, $assistant_id = null) {
        global $wpdb;
        
        $current_month = current_time('Y-m');
        
        if ($assistant_id) {
            $conversations_table = $wpdb->prefix . 'fitbot_conversations';
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $conversations_table 
                     WHERE user_id = %d AND assistant_id = %d 
                     AND DATE_FORMAT(created_at, '%%Y-%%m') = %s",
                    $user_id,
                    $assistant_id,
                    $current_month
                )
            );
            return intval($count);
        }
        
        $table_name = $wpdb->prefix . 'fitbot_usage';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND date LIKE %s",
                $user_id,
                $current_month . '%'
            )
        );
        
        $total_usage = 0;
        foreach ($results as $usage) {
            $total_usage += $usage->recipes_requested + $usage->workouts_requested + $usage->questions_asked;
        }
        
        return $total_usage;
    }
    
    /**
     * Track assistant usage
     */
    public static function track_assistant_usage($user_id, $assistant_id, $type) {
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

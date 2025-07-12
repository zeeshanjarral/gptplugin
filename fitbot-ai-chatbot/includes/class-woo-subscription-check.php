<?php
/**
 * WooCommerce Subscription Integration for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Woo_Subscription_Check {
    
    private $start_product_id;
    private $pro_product_id;
    private $vip_product_id;
    
    public function __construct() {
        $this->start_product_id = Fitbot_Core::get_setting('woo_start_product_id');
        $this->pro_product_id = Fitbot_Core::get_setting('woo_pro_product_id');
        $this->vip_product_id = Fitbot_Core::get_setting('woo_vip_product_id');
    }
    
    /**
     * Get user's current subscription plan
     */
    public function get_user_plan($user_id) {
        if (!$user_id || !function_exists('wcs_user_has_subscription')) {
            return 'none';
        }
        
        if ($this->vip_product_id && $this->has_active_subscription($user_id, $this->vip_product_id)) {
            return 'vip';
        }
        
        if ($this->pro_product_id && $this->has_active_subscription($user_id, $this->pro_product_id)) {
            return 'pro';
        }
        
        if ($this->start_product_id && $this->has_active_subscription($user_id, $this->start_product_id)) {
            return 'start';
        }
        
        return 'none';
    }
    
    /**
     * Check if user has active subscription for specific product
     */
    private function has_active_subscription($user_id, $product_id) {
        if (!function_exists('wcs_user_has_subscription')) {
            return false;
        }
        
        return wcs_user_has_subscription($user_id, $product_id, 'active');
    }
    
    /**
     * Get subscription details for user
     */
    public function get_subscription_details($user_id) {
        if (!$user_id || !function_exists('wcs_get_users_subscriptions')) {
            return array();
        }
        
        $subscriptions = wcs_get_users_subscriptions($user_id);
        $details = array();
        
        foreach ($subscriptions as $subscription) {
            if ($subscription->has_status('active')) {
                $details[] = array(
                    'id' => $subscription->get_id(),
                    'status' => $subscription->get_status(),
                    'next_payment' => $subscription->get_date('next_payment'),
                    'total' => $subscription->get_total(),
                    'currency' => $subscription->get_currency(),
                    'products' => $this->get_subscription_products($subscription)
                );
            }
        }
        
        return $details;
    }
    
    /**
     * Get products in subscription
     */
    private function get_subscription_products($subscription) {
        $products = array();
        
        foreach ($subscription->get_items() as $item) {
            $product = $item->get_product();
            if ($product) {
                $products[] = array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'price' => $product->get_price()
                );
            }
        }
        
        return $products;
    }
    
    /**
     * Get upgrade URL for specific plan
     */
    public function get_upgrade_url($target_plan) {
        $product_id = '';
        
        switch ($target_plan) {
            case 'start':
                $product_id = $this->start_product_id;
                break;
            case 'pro':
                $product_id = $this->pro_product_id;
                break;
            case 'vip':
                $product_id = $this->vip_product_id;
                break;
        }
        
        if (!$product_id) {
            return home_url('/shop/');
        }
        
        return add_query_arg('add-to-cart', $product_id, wc_get_cart_url());
    }
    
    /**
     * Check if user can access feature based on plan
     */
    public function can_access_feature($user_id, $feature) {
        $plan = $this->get_user_plan($user_id);
        
        $feature_access = array(
            'none' => array(),
            'start' => array('basic_qa', 'recipe_single'),
            'pro' => array('basic_qa', 'recipe_single', 'recipe_multiple', 'workout', 'nutrition'),
            'vip' => array('basic_qa', 'recipe_single', 'recipe_multiple', 'workout', 'nutrition', 'health_answers', 'pdf_download', 'meal_plans', 'unlimited')
        );
        
        return in_array($feature, $feature_access[$plan] ?? array());
    }
    
    /**
     * Get plan limits for user
     */
    public function get_plan_limits($user_id) {
        $plan = $this->get_user_plan($user_id);
        
        $limits = array(
            'none' => array(
                'recipes_per_day' => 0,
                'workouts_per_day' => 0,
                'questions_per_day' => 0
            ),
            'start' => array(
                'recipes_per_day' => 1,
                'workouts_per_day' => 0,
                'questions_per_day' => 10
            ),
            'pro' => array(
                'recipes_per_day' => 3,
                'workouts_per_day' => 1,
                'questions_per_day' => 20
            ),
            'vip' => array(
                'recipes_per_day' => -1, // unlimited
                'workouts_per_day' => -1, // unlimited
                'questions_per_day' => -1 // unlimited
            )
        );
        
        return $limits[$plan] ?? $limits['none'];
    }
    
    /**
     * Log subscription check for debugging
     */
    private function log_subscription_check($user_id, $plan, $details = '') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'FITBOT Subscription Check - User ID: %d, Plan: %s, Details: %s',
                $user_id,
                $plan,
                $details
            ));
        }
    }
    
    /**
     * Get plan display name
     */
    public function get_plan_display_name($plan) {
        $names = array(
            'none' => __('No Plan', 'fitbot-ai-chatbot'),
            'start' => __('Start Plan', 'fitbot-ai-chatbot'),
            'pro' => __('Pro Plan', 'fitbot-ai-chatbot'),
            'vip' => __('VIP Plan', 'fitbot-ai-chatbot')
        );
        
        return $names[$plan] ?? $names['none'];
    }
    
    /**
     * Check if WooCommerce Subscriptions is active
     */
    public function is_subscriptions_active() {
        return class_exists('WC_Subscriptions') && function_exists('wcs_user_has_subscription');
    }
    
    /**
     * Check if user has access to specific assistant
     */
    public function check_assistant_access($user_id, $assistant_id) {
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        $assistant = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $assistant_id
        ), ARRAY_A);
        
        if (!$assistant || !$assistant['woo_product_id']) {
            return true; // Free assistant or no product linked
        }
        
        return $this->has_active_subscription($user_id, $assistant['woo_product_id']);
    }
}

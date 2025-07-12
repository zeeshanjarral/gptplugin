<?php
/**
 * AJAX Handlers for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Ajax_Handlers {
    
    private $gpt_api;
    private $subscription_checker;
    private $content_loader;
    
    public function __construct() {
        $this->gpt_api = new Fitbot_GPT_Chat_API();
        $this->subscription_checker = new Fitbot_Woo_Subscription_Check();
        $this->content_loader = new Fitbot_Recipe_Workout_Loader();
    }
    
    /**
     * Handle chat request from frontend
     */
    public function handle_chat_request() {
        $user_id = get_current_user_id();
        $message = sanitize_text_field($_POST['message'] ?? '');
        $message_type = sanitize_text_field($_POST['type'] ?? 'general');
        $assistant_slug = sanitize_text_field($_POST['assistant_slug'] ?? '');
        $assistant_id = intval($_POST['assistant_id'] ?? 0);
        
        if (empty($message)) {
            wp_send_json_error(array(
                'message' => __('Please enter a message.', 'fitbot-ai-chatbot')
            ));
        }
        
        $assistant = null;
        if (!empty($assistant_slug)) {
            $assistant = $this->get_assistant_by_slug($assistant_slug);
        } elseif (!empty($assistant_id)) {
            $assistant = $this->get_assistant_by_id($assistant_id);
        }
        
        if (!$assistant) {
            wp_send_json_error(array(
                'message' => __('Invalid assistant specified.', 'fitbot-ai-chatbot')
            ));
        }
        
        $has_access = $this->check_assistant_access($user_id, $assistant['id']);
        if (!$has_access) {
            wp_send_json_success(array(
                'message' => $this->get_subscription_message($assistant),
                'type' => 'subscription_required',
                'upgrade_url' => $this->get_assistant_purchase_url($assistant)
            ));
        }
        
        if ($this->has_reached_assistant_limit($user_id, $assistant['id'], $message_type)) {
            wp_send_json_success(array(
                'message' => $this->get_limit_message($assistant),
                'type' => 'limit_reached',
                'upgrade_url' => $this->get_assistant_purchase_url($assistant)
            ));
        }
        
        $conversation_history = Fitbot_Core::get_conversation_history($user_id, 5, $assistant['id']);
        
        $response = $this->process_assistant_message($message, $message_type, $assistant, $conversation_history);
        
        Fitbot_Core::log_conversation($user_id, $message_type, $message, $response['message'], $assistant['id']);
        
        Fitbot_Core::track_assistant_usage($user_id, $assistant['id'], $this->get_usage_type($message_type));
        
        wp_send_json_success($response);
    }
    
    /**
     * Process message based on type and plan
     */
    private function process_message($message, $type, $plan, $history) {
        switch ($type) {
            case 'recipe_request':
                return $this->handle_recipe_request($message, $plan, $history);
                
            case 'workout_request':
                return $this->handle_workout_request($message, $plan, $history);
                
            case 'nutrition_question':
                return $this->handle_nutrition_question($message, $plan, $history);
                
            case 'health_question':
                return $this->handle_health_question($message, $plan, $history);
                
            case 'pdf_request':
                return $this->handle_pdf_request($message, $plan, $history);
                
            default:
                return $this->handle_general_chat($message, $plan, $history);
        }
    }
    
    /**
     * Process message for specific assistant
     */
    private function process_assistant_message($message, $type, $assistant, $history) {
        $enhanced_prompt = $this->build_assistant_prompt($assistant, $message, $type);
        
        $response = $this->gpt_api->get_chat_response($enhanced_prompt, $assistant['slug'], $history, $assistant['prompt']);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => $type . '_response',
                'language' => $response['language'] ?? 'en',
                'assistant_id' => $assistant['id']
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your message right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error',
            'assistant_id' => $assistant['id']
        );
    }
    
    /**
     * Handle recipe requests
     */
    private function handle_recipe_request($message, $plan, $history) {
        if (!$this->subscription_checker->can_access_feature(get_current_user_id(), 'recipe_single')) {
            return array(
                'message' => $this->get_upgrade_message($plan, 'recipe'),
                'type' => 'upgrade_suggestion',
                'upgrade_url' => $this->subscription_checker->get_upgrade_url('start')
            );
        }
        
        $recipes = $this->content_loader->get_recipes_for_plan($plan);
        $recipe_context = $this->build_recipe_context($recipes, $message);
        
        $enhanced_message = $message . "\n\nAvailable recipes: " . $recipe_context;
        
        $response = $this->gpt_api->get_chat_response($enhanced_message, $plan, $history);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => 'recipe_response',
                'language' => $response['language'] ?? 'en'
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your recipe request right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error'
        );
    }
    
    /**
     * Handle workout requests
     */
    private function handle_workout_request($message, $plan, $history) {
        if (!$this->subscription_checker->can_access_feature(get_current_user_id(), 'workout')) {
            return array(
                'message' => $this->get_upgrade_message($plan, 'workout'),
                'type' => 'upgrade_suggestion',
                'upgrade_url' => $this->subscription_checker->get_upgrade_url('pro')
            );
        }
        
        $workouts = $this->content_loader->get_workouts_for_plan($plan);
        $workout_context = $this->build_workout_context($workouts, $message);
        
        $enhanced_message = $message . "\n\nAvailable workouts: " . $workout_context;
        
        $response = $this->gpt_api->get_chat_response($enhanced_message, $plan, $history);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => 'workout_response',
                'language' => $response['language'] ?? 'en'
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your workout request right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error'
        );
    }
    
    /**
     * Handle nutrition questions
     */
    private function handle_nutrition_question($message, $plan, $history) {
        if (!$this->subscription_checker->can_access_feature(get_current_user_id(), 'nutrition')) {
            return array(
                'message' => $this->get_upgrade_message($plan, 'nutrition'),
                'type' => 'upgrade_suggestion',
                'upgrade_url' => $this->subscription_checker->get_upgrade_url('pro')
            );
        }
        
        $response = $this->gpt_api->get_chat_response($message, $plan, $history);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => 'nutrition_response',
                'language' => $response['language'] ?? 'en'
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your nutrition question right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error'
        );
    }
    
    /**
     * Handle health questions
     */
    private function handle_health_question($message, $plan, $history) {
        if (!$this->subscription_checker->can_access_feature(get_current_user_id(), 'health_answers')) {
            return array(
                'message' => $this->get_upgrade_message($plan, 'health'),
                'type' => 'upgrade_suggestion',
                'upgrade_url' => $this->subscription_checker->get_upgrade_url('vip')
            );
        }
        
        $response = $this->gpt_api->get_chat_response($message, $plan, $history);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => 'health_response',
                'language' => $response['language'] ?? 'en'
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your health question right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error'
        );
    }
    
    /**
     * Handle PDF requests
     */
    private function handle_pdf_request($message, $plan, $history) {
        if (!$this->subscription_checker->can_access_feature(get_current_user_id(), 'pdf_download')) {
            return array(
                'message' => $this->get_upgrade_message($plan, 'pdf'),
                'type' => 'upgrade_suggestion',
                'upgrade_url' => $this->subscription_checker->get_upgrade_url('vip')
            );
        }
        
        $pdfs = $this->content_loader->get_pdfs_for_plan($plan);
        
        if (empty($pdfs)) {
            return array(
                'message' => __('No PDF resources are currently available for your plan.', 'fitbot-ai-chatbot'),
                'type' => 'info'
            );
        }
        
        $matching_pdf = $this->find_matching_pdf($pdfs, $message);
        
        if ($matching_pdf) {
            $this->content_loader->increment_pdf_download($matching_pdf['id']);
            
            return array(
                'message' => sprintf(
                    __('Here\'s your requested PDF: %s', 'fitbot-ai-chatbot'),
                    $matching_pdf['title']
                ),
                'type' => 'pdf_response',
                'pdf_url' => $matching_pdf['url'],
                'pdf_title' => $matching_pdf['title']
            );
        }
        
        $pdf_list = $this->build_pdf_list($pdfs);
        return array(
            'message' => __('Here are the available PDF resources for your plan:', 'fitbot-ai-chatbot') . "\n\n" . $pdf_list,
            'type' => 'pdf_list',
            'pdfs' => $pdfs
        );
    }
    
    /**
     * Handle general chat
     */
    private function handle_general_chat($message, $plan, $history) {
        $response = $this->gpt_api->get_chat_response($message, $plan, $history);
        
        if ($response['success']) {
            return array(
                'message' => $response['message'],
                'type' => 'general_response',
                'language' => $response['language'] ?? 'en'
            );
        }
        
        return array(
            'message' => __('Sorry, I couldn\'t process your message right now. Please try again.', 'fitbot-ai-chatbot'),
            'type' => 'error'
        );
    }
    
    /**
     * Check if user has reached daily limit
     */
    private function has_reached_daily_limit($user_id, $message_type, $plan) {
        $usage_type = $this->get_usage_type($message_type);
        return Fitbot_Core::has_reached_limit($user_id, $usage_type, $plan);
    }
    
    /**
     * Get usage type from message type
     */
    private function get_usage_type($message_type) {
        $type_mapping = array(
            'recipe_request' => 'recipes',
            'workout_request' => 'workouts',
            'nutrition_question' => 'questions',
            'health_question' => 'questions',
            'pdf_request' => 'questions',
            'general' => 'questions'
        );
        
        return $type_mapping[$message_type] ?? 'questions';
    }
    
    /**
     * Get upgrade message based on plan and feature
     */
    private function get_upgrade_message($current_plan, $feature) {
        $messages = array(
            'none' => __('Please subscribe to access FITBOT features. Start with our Start plan (€5/mo) for basic recipes and Q&A.', 'fitbot-ai-chatbot'),
            'start' => array(
                'workout' => __('Daily workouts are available in the Pro plan (€12/mo). Would you like to upgrade for workouts, 3 recipes per day, and nutrition facts?', 'fitbot-ai-chatbot'),
                'nutrition' => __('Nutrition facts are available in the Pro plan (€12/mo). Would you like to upgrade?', 'fitbot-ai-chatbot'),
                'health' => __('Smart health answers are available in the VIP plan (€24/mo). Would you like to upgrade for unlimited access and specialized guidance?', 'fitbot-ai-chatbot'),
                'pdf' => __('PDF downloads are available in the VIP plan (€24/mo). Would you like to upgrade?', 'fitbot-ai-chatbot')
            ),
            'pro' => array(
                'health' => __('Smart health answers are available in the VIP plan (€24/mo). Would you like to upgrade for unlimited access and specialized guidance?', 'fitbot-ai-chatbot'),
                'pdf' => __('PDF downloads are available in the VIP plan (€24/mo). Would you like to upgrade?', 'fitbot-ai-chatbot')
            )
        );
        
        if ($current_plan === 'none') {
            return $messages['none'];
        }
        
        return $messages[$current_plan][$feature] ?? __('This feature requires a higher plan. Would you like to upgrade?', 'fitbot-ai-chatbot');
    }
    
    /**
     * Get target plan for upgrade
     */
    private function get_target_plan($current_plan) {
        $upgrade_path = array(
            'none' => 'start',
            'start' => 'pro',
            'pro' => 'vip'
        );
        
        return $upgrade_path[$current_plan] ?? 'start';
    }
    
    /**
     * Build recipe context for GPT
     */
    private function build_recipe_context($recipes, $message) {
        if (empty($recipes)) {
            return 'No recipes available for your plan.';
        }
        
        $context = '';
        foreach (array_slice($recipes, 0, 5) as $recipe) {
            $context .= sprintf(
                "- %s (Prep: %d min, Cook: %d min, Calories: %d)\n",
                $recipe['title'],
                $recipe['prep_time'],
                $recipe['cook_time'],
                $recipe['calories']
            );
        }
        
        return $context;
    }
    
    /**
     * Build workout context for GPT
     */
    private function build_workout_context($workouts, $message) {
        if (empty($workouts)) {
            return 'No workouts available for your plan.';
        }
        
        $context = '';
        foreach (array_slice($workouts, 0, 5) as $workout) {
            $context .= sprintf(
                "- %s (%s, %d min, %s)\n",
                $workout['title'],
                $workout['difficulty'],
                $workout['duration'],
                $workout['type']
            );
        }
        
        return $context;
    }
    
    /**
     * Find matching PDF based on message content
     */
    private function find_matching_pdf($pdfs, $message) {
        $message_lower = strtolower($message);
        
        foreach ($pdfs as $pdf) {
            $title_lower = strtolower($pdf['title']);
            $content_lower = strtolower($pdf['content']);
            
            if (strpos($message_lower, $title_lower) !== false || 
                strpos($title_lower, $message_lower) !== false ||
                strpos($content_lower, $message_lower) !== false) {
                return $pdf;
            }
        }
        
        return null;
    }
    
    /**
     * Build PDF list for display
     */
    private function build_pdf_list($pdfs) {
        $list = '';
        foreach ($pdfs as $pdf) {
            $list .= sprintf("- %s\n", $pdf['title']);
        }
        
        return $list;
    }
    
    /**
     * Get assistant by slug
     */
    private function get_assistant_by_slug($slug) {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE slug = %s",
            $slug
        ), ARRAY_A);
    }
    
    /**
     * Get assistant by ID
     */
    private function get_assistant_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ), ARRAY_A);
    }
    
    /**
     * Check if user has access to specific assistant
     */
    private function check_assistant_access($user_id, $assistant_id) {
        if (!$user_id) {
            return false;
        }
        
        $assistant = $this->get_assistant_by_id($assistant_id);
        if (!$assistant || !$assistant['woo_product_id']) {
            return true; // Free assistant or no product linked
        }
        
        return $this->subscription_checker->has_active_subscription($user_id, $assistant['woo_product_id']);
    }
    
    /**
     * Check if user has reached assistant usage limits
     */
    private function has_reached_assistant_limit($user_id, $assistant_id, $message_type) {
        $assistant = $this->get_assistant_by_id($assistant_id);
        if (!$assistant) {
            return false;
        }
        
        $daily_usage = Fitbot_Core::get_daily_usage($user_id, $assistant_id);
        $monthly_usage = Fitbot_Core::get_monthly_usage($user_id, $assistant_id);
        
        return ($daily_usage >= $assistant['daily_limit']) || ($monthly_usage >= $assistant['monthly_limit']);
    }
    
    /**
     * Get subscription message for assistant
     */
    private function get_subscription_message($assistant) {
        return sprintf(
            __('To chat with %s, you need an active subscription. Subscribe for %s %s per month to get access.', 'fitbot-ai-chatbot'),
            $assistant['name'],
            $assistant['currency'],
            number_format($assistant['price'], 2)
        );
    }
    
    /**
     * Get limit reached message for assistant
     */
    private function get_limit_message($assistant) {
        return sprintf(
            __('You\'ve reached your usage limit for %s. Daily limit: %d, Monthly limit: %d. Consider upgrading for more access.', 'fitbot-ai-chatbot'),
            $assistant['name'],
            $assistant['daily_limit'],
            $assistant['monthly_limit']
        );
    }
    
    /**
     * Get purchase URL for assistant
     */
    private function get_assistant_purchase_url($assistant) {
        if ($assistant['woo_product_id']) {
            return get_permalink($assistant['woo_product_id']);
        }
        
        return home_url('/shop/');
    }
    
    /**
     * Build enhanced prompt for assistant
     */
    private function build_assistant_prompt($assistant, $message, $type) {
        $base_prompt = $assistant['prompt'] ?: 'You are a helpful AI assistant.';
        
        $personality_context = '';
        if ($assistant['personality']) {
            $personality_context = sprintf(
                "\n\nPersonality: You should respond in a %s manner.",
                $assistant['personality']
            );
        }
        
        $context_info = sprintf(
            "\n\nAssistant: %s\nUser message type: %s\nUser message: %s",
            $assistant['name'],
            $type,
            $message
        );
        
        return $base_prompt . $personality_context . $context_info;
    }
}

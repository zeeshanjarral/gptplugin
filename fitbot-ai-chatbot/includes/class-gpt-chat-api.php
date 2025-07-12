<?php
/**
 * OpenAI GPT-4 Turbo API Integration for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_GPT_Chat_API {
    
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    
    public function __construct() {
        $this->api_key = Fitbot_Core::get_setting('openai_api_key');
    }
    
    /**
     * Send message to GPT-4 Turbo and get response
     */
    public function get_chat_response($message, $user_plan = 'start', $conversation_history = array()) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('OpenAI API key not configured. Please contact administrator.', 'fitbot-ai-chatbot')
            );
        }
        
        $system_prompt = $this->build_system_prompt($user_plan);
        $messages = $this->prepare_messages($system_prompt, $message, $conversation_history);
        
        $response = $this->make_api_request($messages);
        
        if ($response['success']) {
            $detected_language = $this->detect_language($message);
            return array(
                'success' => true,
                'message' => $response['message'],
                'language' => $detected_language,
                'tokens_used' => $response['tokens_used'] ?? 0
            );
        }
        
        return $response;
    }
    
    /**
     * Build system prompt based on user plan
     */
    private function build_system_prompt($user_plan) {
        $base_prompt = "You are FITBOT, an AI fitness and health assistant for Coach Latif's website (www.coachlatif.com). ";
        $base_prompt .= "You help users with personalized fitness advice, nutrition guidance, and health recommendations. ";
        $base_prompt .= "Always respond in the same language as the user's input. ";
        $base_prompt .= "Be friendly, encouraging, and professional. ";
        
        switch ($user_plan) {
            case 'start':
                $base_prompt .= "User has START plan (€5/mo): Provide 1 recipe per day (breakfast OR dinner), basic Q&A only. ";
                $base_prompt .= "If they ask for features outside their plan, suggest upgrading to Pro (€12/mo) or VIP (€24/mo). ";
                break;
                
            case 'pro':
                $base_prompt .= "User has PRO plan (€12/mo): Provide up to 3 recipes per day (breakfast, lunch, dinner), ";
                $base_prompt .= "daily written workouts, nutrition facts (calories, carbs, fats, protein). ";
                $base_prompt .= "If they ask for VIP features, suggest upgrading to VIP (€24/mo). ";
                break;
                
            case 'vip':
                $base_prompt .= "User has VIP plan (€24/mo): Provide unlimited recipes, smart health answers ";
                $base_prompt .= "(thyroid, bloating, sleep, etc.), condition-based workouts, meal plan types ";
                $base_prompt .= "(keto, hormone-friendly, etc.). You can offer PDF downloads and comprehensive guidance. ";
                break;
                
            default:
                $base_prompt .= "User is not subscribed. Encourage them to subscribe to START plan (€5/mo) for basic features, ";
                $base_prompt .= "PRO plan (€12/mo) for enhanced features, or VIP plan (€24/mo) for premium features. ";
        }
        
        $base_prompt .= "Keep responses concise but helpful. Focus on actionable advice.";
        
        return $base_prompt;
    }
    
    /**
     * Prepare messages array for API request
     */
    private function prepare_messages($system_prompt, $user_message, $conversation_history = array()) {
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            )
        );
        
        $recent_history = array_slice($conversation_history, -5);
        foreach ($recent_history as $exchange) {
            $messages[] = array(
                'role' => 'user',
                'content' => $exchange->message
            );
            if (!empty($exchange->response)) {
                $messages[] = array(
                    'role' => 'assistant',
                    'content' => $exchange->response
                );
            }
        }
        
        $messages[] = array(
            'role' => 'user',
            'content' => $user_message
        );
        
        return $messages;
    }
    
    /**
     * Make API request to OpenAI
     */
    private function make_api_request($messages) {
        $headers = array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json'
        );
        
        $body = array(
            'model' => 'gpt-4-turbo-preview',
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        );
        
        $args = array(
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => 30,
            'method' => 'POST'
        );
        
        $response = wp_remote_request($this->api_url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Failed to connect to OpenAI API. Please try again later.', 'fitbot-ai-chatbot')
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            error_log('OpenAI API Error: ' . $response_body);
            return array(
                'success' => false,
                'message' => __('AI service temporarily unavailable. Please try again later.', 'fitbot-ai-chatbot')
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return array(
                'success' => false,
                'message' => __('Invalid response from AI service. Please try again.', 'fitbot-ai-chatbot')
            );
        }
        
        return array(
            'success' => true,
            'message' => trim($data['choices'][0]['message']['content']),
            'tokens_used' => $data['usage']['total_tokens'] ?? 0
        );
    }
    
    /**
     * Detect language of user input
     */
    private function detect_language($text) {
        $language_patterns = array(
            'es' => array('hola', 'gracias', 'por favor', 'sí', 'no', 'cómo', 'qué', 'dónde'),
            'fr' => array('bonjour', 'merci', 's\'il vous plaît', 'oui', 'non', 'comment', 'quoi', 'où'),
            'de' => array('hallo', 'danke', 'bitte', 'ja', 'nein', 'wie', 'was', 'wo'),
            'it' => array('ciao', 'grazie', 'prego', 'sì', 'no', 'come', 'cosa', 'dove'),
            'pt' => array('olá', 'obrigado', 'por favor', 'sim', 'não', 'como', 'que', 'onde'),
            'nl' => array('hallo', 'dank je', 'alsjeblieft', 'ja', 'nee', 'hoe', 'wat', 'waar')
        );
        
        $text_lower = strtolower($text);
        
        foreach ($language_patterns as $lang => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($text_lower, $pattern) !== false) {
                    return $lang;
                }
            }
        }
        
        return 'en'; // Default to English
    }
    
    /**
     * Generate upgrade suggestion message
     */
    public function generate_upgrade_message($requested_feature, $current_plan) {
        $upgrade_messages = array(
            'start_to_pro' => __('This feature is available in the Pro plan (€12/mo). Would you like to upgrade for daily workouts, 3 recipes per day, and nutrition facts?', 'fitbot-ai-chatbot'),
            'start_to_vip' => __('This feature is available in the VIP plan (€24/mo). Would you like to upgrade for unlimited recipes, smart health answers, and specialized meal plans?', 'fitbot-ai-chatbot'),
            'pro_to_vip' => __('This feature is available in the VIP plan (€24/mo). Would you like to upgrade for unlimited access and specialized health guidance?', 'fitbot-ai-chatbot')
        );
        
        $upgrade_key = $current_plan . '_to_vip';
        if ($current_plan === 'start' && in_array($requested_feature, array('workout', 'nutrition'))) {
            $upgrade_key = 'start_to_pro';
        }
        
        return $upgrade_messages[$upgrade_key] ?? $upgrade_messages['start_to_pro'];
    }
    
    /**
     * Check if feature is available for user plan
     */
    public function is_feature_available($feature, $plan) {
        $plan_features = array(
            'start' => array('basic_qa', 'recipe_single'),
            'pro' => array('basic_qa', 'recipe_single', 'recipe_multiple', 'workout', 'nutrition'),
            'vip' => array('basic_qa', 'recipe_single', 'recipe_multiple', 'workout', 'nutrition', 'health_answers', 'pdf_download', 'meal_plans')
        );
        
        return in_array($feature, $plan_features[$plan] ?? array());
    }
}

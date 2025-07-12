<?php
/**
 * Content Loader for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Recipe_Workout_Loader {
    
    /**
     * Get recipes for specific plan
     */
    public function get_recipes_for_plan($plan) {
        $args = array(
            'post_type' => 'fitbot_recipe',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        if ($plan !== 'vip') {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_plan_level',
                'field' => 'slug',
                'terms' => array($plan, 'start'), // Include start level for higher plans
                'operator' => 'IN'
            );
        }
        
        $recipes = get_posts($args);
        $formatted_recipes = array();
        
        foreach ($recipes as $recipe) {
            $formatted_recipes[] = array(
                'id' => $recipe->ID,
                'title' => $recipe->post_title,
                'content' => $recipe->post_content,
                'ingredients' => get_post_meta($recipe->ID, '_fitbot_ingredients', true),
                'instructions' => get_post_meta($recipe->ID, '_fitbot_instructions', true),
                'prep_time' => intval(get_post_meta($recipe->ID, '_fitbot_prep_time', true)),
                'cook_time' => intval(get_post_meta($recipe->ID, '_fitbot_cook_time', true)),
                'servings' => intval(get_post_meta($recipe->ID, '_fitbot_servings', true)),
                'calories' => intval(get_post_meta($recipe->ID, '_fitbot_calories', true)),
                'carbs' => floatval(get_post_meta($recipe->ID, '_fitbot_carbs', true)),
                'fats' => floatval(get_post_meta($recipe->ID, '_fitbot_fats', true)),
                'protein' => floatval(get_post_meta($recipe->ID, '_fitbot_protein', true)),
                'meal_types' => wp_get_post_terms($recipe->ID, 'fitbot_meal_type', array('fields' => 'names')),
                'conditions' => wp_get_post_terms($recipe->ID, 'fitbot_condition', array('fields' => 'names'))
            );
        }
        
        return $formatted_recipes;
    }
    
    /**
     * Get workouts for specific plan
     */
    public function get_workouts_for_plan($plan) {
        $args = array(
            'post_type' => 'fitbot_workout',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        if ($plan !== 'vip') {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_plan_level',
                'field' => 'slug',
                'terms' => array($plan, 'start'), // Include start level for higher plans
                'operator' => 'IN'
            );
        }
        
        $workouts = get_posts($args);
        $formatted_workouts = array();
        
        foreach ($workouts as $workout) {
            $formatted_workouts[] = array(
                'id' => $workout->ID,
                'title' => $workout->post_title,
                'content' => $workout->post_content,
                'duration' => intval(get_post_meta($workout->ID, '_fitbot_duration', true)),
                'difficulty' => get_post_meta($workout->ID, '_fitbot_difficulty', true),
                'equipment' => get_post_meta($workout->ID, '_fitbot_equipment', true),
                'exercises' => get_post_meta($workout->ID, '_fitbot_exercises', true),
                'type' => wp_get_post_terms($workout->ID, 'fitbot_workout_type', array('fields' => 'names')),
                'conditions' => wp_get_post_terms($workout->ID, 'fitbot_condition', array('fields' => 'names'))
            );
        }
        
        return $formatted_workouts;
    }
    
    /**
     * Get PDFs for specific plan
     */
    public function get_pdfs_for_plan($plan) {
        $args = array(
            'post_type' => 'fitbot_pdf',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array()
        );
        
        if ($plan !== 'vip') {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_plan_level',
                'field' => 'slug',
                'terms' => array($plan, 'start'), // Include start level for higher plans
                'operator' => 'IN'
            );
        }
        
        $pdfs = get_posts($args);
        $formatted_pdfs = array();
        
        foreach ($pdfs as $pdf) {
            $formatted_pdfs[] = array(
                'id' => $pdf->ID,
                'title' => $pdf->post_title,
                'content' => $pdf->post_content,
                'url' => get_post_meta($pdf->ID, '_fitbot_pdf_url', true),
                'size' => get_post_meta($pdf->ID, '_fitbot_pdf_size', true),
                'download_count' => intval(get_post_meta($pdf->ID, '_fitbot_download_count', true))
            );
        }
        
        return $formatted_pdfs;
    }
    
    /**
     * Get motivation message for today
     */
    public function get_daily_motivation() {
        $args = array(
            'post_type' => 'fitbot_motivation',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'rand'
        );
        
        $messages = get_posts($args);
        
        if (!empty($messages)) {
            return array(
                'id' => $messages[0]->ID,
                'title' => $messages[0]->post_title,
                'content' => $messages[0]->post_content
            );
        }
        
        return null;
    }
    
    /**
     * Search recipes by criteria
     */
    public function search_recipes($criteria) {
        $args = array(
            'post_type' => 'fitbot_recipe',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        if (!empty($criteria['search'])) {
            $args['s'] = $criteria['search'];
        }
        
        if (!empty($criteria['meal_type'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_meal_type',
                'field' => 'slug',
                'terms' => $criteria['meal_type']
            );
        }
        
        if (!empty($criteria['condition'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_condition',
                'field' => 'slug',
                'terms' => $criteria['condition']
            );
        }
        
        if (!empty($criteria['plan'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_plan_level',
                'field' => 'slug',
                'terms' => $criteria['plan']
            );
        }
        
        if (!empty($criteria['min_calories']) || !empty($criteria['max_calories'])) {
            $calorie_query = array('relation' => 'AND');
            
            if (!empty($criteria['min_calories'])) {
                $calorie_query[] = array(
                    'key' => '_fitbot_calories',
                    'value' => intval($criteria['min_calories']),
                    'compare' => '>='
                );
            }
            
            if (!empty($criteria['max_calories'])) {
                $calorie_query[] = array(
                    'key' => '_fitbot_calories',
                    'value' => intval($criteria['max_calories']),
                    'compare' => '<='
                );
            }
            
            $args['meta_query'][] = $calorie_query;
        }
        
        return get_posts($args);
    }
    
    /**
     * Search workouts by criteria
     */
    public function search_workouts($criteria) {
        $args = array(
            'post_type' => 'fitbot_workout',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        if (!empty($criteria['search'])) {
            $args['s'] = $criteria['search'];
        }
        
        if (!empty($criteria['workout_type'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_workout_type',
                'field' => 'slug',
                'terms' => $criteria['workout_type']
            );
        }
        
        if (!empty($criteria['condition'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'fitbot_condition',
                'field' => 'slug',
                'terms' => $criteria['condition']
            );
        }
        
        if (!empty($criteria['difficulty'])) {
            $args['meta_query'][] = array(
                'key' => '_fitbot_difficulty',
                'value' => $criteria['difficulty'],
                'compare' => '='
            );
        }
        
        if (!empty($criteria['min_duration']) || !empty($criteria['max_duration'])) {
            $duration_query = array('relation' => 'AND');
            
            if (!empty($criteria['min_duration'])) {
                $duration_query[] = array(
                    'key' => '_fitbot_duration',
                    'value' => intval($criteria['min_duration']),
                    'compare' => '>='
                );
            }
            
            if (!empty($criteria['max_duration'])) {
                $duration_query[] = array(
                    'key' => '_fitbot_duration',
                    'value' => intval($criteria['max_duration']),
                    'compare' => '<='
                );
            }
            
            $args['meta_query'][] = $duration_query;
        }
        
        return get_posts($args);
    }
    
    /**
     * Increment PDF download count
     */
    public function increment_pdf_download($pdf_id) {
        $current_count = intval(get_post_meta($pdf_id, '_fitbot_download_count', true));
        update_post_meta($pdf_id, '_fitbot_download_count', $current_count + 1);
    }
    
    /**
     * Get popular recipes
     */
    public function get_popular_recipes($limit = 5) {
        $args = array(
            'post_type' => 'fitbot_recipe',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_key' => '_fitbot_view_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );
        
        return get_posts($args);
    }
    
    /**
     * Get popular workouts
     */
    public function get_popular_workouts($limit = 5) {
        $args = array(
            'post_type' => 'fitbot_workout',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_key' => '_fitbot_view_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        );
        
        return get_posts($args);
    }
    
    /**
     * Increment content view count
     */
    public function increment_view_count($post_id) {
        $current_count = intval(get_post_meta($post_id, '_fitbot_view_count', true));
        update_post_meta($post_id, '_fitbot_view_count', $current_count + 1);
    }
}

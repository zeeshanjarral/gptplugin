<?php
/**
 * Custom Post Types for FITBOT AI Chatbot Content Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Custom_Post_Types {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        register_post_type('fitbot_recipe', array(
            'labels' => array(
                'name' => __('Recipes', 'fitbot-ai-chatbot'),
                'singular_name' => __('Recipe', 'fitbot-ai-chatbot'),
                'add_new' => __('Add New Recipe', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Recipe', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Recipe', 'fitbot-ai-chatbot'),
                'new_item' => __('New Recipe', 'fitbot-ai-chatbot'),
                'view_item' => __('View Recipe', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Recipes', 'fitbot-ai-chatbot'),
                'not_found' => __('No recipes found', 'fitbot-ai-chatbot'),
                'not_found_in_trash' => __('No recipes found in trash', 'fitbot-ai-chatbot')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'fitbot-content',
            'capability_type' => 'post',
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => false
        ));
        
        register_post_type('fitbot_workout', array(
            'labels' => array(
                'name' => __('Workouts', 'fitbot-ai-chatbot'),
                'singular_name' => __('Workout', 'fitbot-ai-chatbot'),
                'add_new' => __('Add New Workout', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Workout', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Workout', 'fitbot-ai-chatbot'),
                'new_item' => __('New Workout', 'fitbot-ai-chatbot'),
                'view_item' => __('View Workout', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Workouts', 'fitbot-ai-chatbot'),
                'not_found' => __('No workouts found', 'fitbot-ai-chatbot'),
                'not_found_in_trash' => __('No workouts found in trash', 'fitbot-ai-chatbot')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'fitbot-content',
            'capability_type' => 'post',
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => false
        ));
        
        register_post_type('fitbot_motivation', array(
            'labels' => array(
                'name' => __('Motivation Messages', 'fitbot-ai-chatbot'),
                'singular_name' => __('Motivation Message', 'fitbot-ai-chatbot'),
                'add_new' => __('Add New Message', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Motivation Message', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Motivation Message', 'fitbot-ai-chatbot'),
                'new_item' => __('New Motivation Message', 'fitbot-ai-chatbot'),
                'view_item' => __('View Motivation Message', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Messages', 'fitbot-ai-chatbot'),
                'not_found' => __('No messages found', 'fitbot-ai-chatbot'),
                'not_found_in_trash' => __('No messages found in trash', 'fitbot-ai-chatbot')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'fitbot-content',
            'capability_type' => 'post',
            'supports' => array('title', 'editor'),
            'has_archive' => false,
            'rewrite' => false
        ));
        
        register_post_type('fitbot_pdf', array(
            'labels' => array(
                'name' => __('PDF Resources', 'fitbot-ai-chatbot'),
                'singular_name' => __('PDF Resource', 'fitbot-ai-chatbot'),
                'add_new' => __('Add New PDF', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New PDF Resource', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit PDF Resource', 'fitbot-ai-chatbot'),
                'new_item' => __('New PDF Resource', 'fitbot-ai-chatbot'),
                'view_item' => __('View PDF Resource', 'fitbot-ai-chatbot'),
                'search_items' => __('Search PDFs', 'fitbot-ai-chatbot'),
                'not_found' => __('No PDFs found', 'fitbot-ai-chatbot'),
                'not_found_in_trash' => __('No PDFs found in trash', 'fitbot-ai-chatbot')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'fitbot-content',
            'capability_type' => 'post',
            'supports' => array('title', 'editor'),
            'has_archive' => false,
            'rewrite' => false
        ));
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        register_taxonomy('fitbot_meal_type', 'fitbot_recipe', array(
            'labels' => array(
                'name' => __('Meal Types', 'fitbot-ai-chatbot'),
                'singular_name' => __('Meal Type', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Meal Type', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Meal Type', 'fitbot-ai-chatbot'),
                'update_item' => __('Update Meal Type', 'fitbot-ai-chatbot'),
                'view_item' => __('View Meal Type', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Meal Types', 'fitbot-ai-chatbot')
            ),
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false
        ));
        
        register_taxonomy('fitbot_plan_level', array('fitbot_recipe', 'fitbot_workout', 'fitbot_pdf'), array(
            'labels' => array(
                'name' => __('Plan Levels', 'fitbot-ai-chatbot'),
                'singular_name' => __('Plan Level', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Plan Level', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Plan Level', 'fitbot-ai-chatbot'),
                'update_item' => __('Update Plan Level', 'fitbot-ai-chatbot'),
                'view_item' => __('View Plan Level', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Plan Levels', 'fitbot-ai-chatbot')
            ),
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false
        ));
        
        register_taxonomy('fitbot_condition', array('fitbot_recipe', 'fitbot_workout'), array(
            'labels' => array(
                'name' => __('Health Conditions', 'fitbot-ai-chatbot'),
                'singular_name' => __('Health Condition', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Condition', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Condition', 'fitbot-ai-chatbot'),
                'update_item' => __('Update Condition', 'fitbot-ai-chatbot'),
                'view_item' => __('View Condition', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Conditions', 'fitbot-ai-chatbot')
            ),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false
        ));
        
        register_taxonomy('fitbot_workout_type', 'fitbot_workout', array(
            'labels' => array(
                'name' => __('Workout Types', 'fitbot-ai-chatbot'),
                'singular_name' => __('Workout Type', 'fitbot-ai-chatbot'),
                'add_new_item' => __('Add New Workout Type', 'fitbot-ai-chatbot'),
                'edit_item' => __('Edit Workout Type', 'fitbot-ai-chatbot'),
                'update_item' => __('Update Workout Type', 'fitbot-ai-chatbot'),
                'view_item' => __('View Workout Type', 'fitbot-ai-chatbot'),
                'search_items' => __('Search Workout Types', 'fitbot-ai-chatbot')
            ),
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false
        ));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fitbot_recipe_details',
            __('Recipe Details', 'fitbot-ai-chatbot'),
            array($this, 'recipe_meta_box_callback'),
            'fitbot_recipe',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fitbot_workout_details',
            __('Workout Details', 'fitbot-ai-chatbot'),
            array($this, 'workout_meta_box_callback'),
            'fitbot_workout',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fitbot_pdf_details',
            __('PDF Details', 'fitbot-ai-chatbot'),
            array($this, 'pdf_meta_box_callback'),
            'fitbot_pdf',
            'normal',
            'high'
        );
    }
    
    /**
     * Recipe meta box callback
     */
    public function recipe_meta_box_callback($post) {
        wp_nonce_field('fitbot_recipe_meta', 'fitbot_recipe_meta_nonce');
        
        $ingredients = get_post_meta($post->ID, '_fitbot_ingredients', true);
        $instructions = get_post_meta($post->ID, '_fitbot_instructions', true);
        $prep_time = get_post_meta($post->ID, '_fitbot_prep_time', true);
        $cook_time = get_post_meta($post->ID, '_fitbot_cook_time', true);
        $servings = get_post_meta($post->ID, '_fitbot_servings', true);
        $calories = get_post_meta($post->ID, '_fitbot_calories', true);
        $carbs = get_post_meta($post->ID, '_fitbot_carbs', true);
        $fats = get_post_meta($post->ID, '_fitbot_fats', true);
        $protein = get_post_meta($post->ID, '_fitbot_protein', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="fitbot_ingredients">' . __('Ingredients', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><textarea id="fitbot_ingredients" name="fitbot_ingredients" rows="5" cols="50">' . esc_textarea($ingredients) . '</textarea></td></tr>';
        
        echo '<tr><th><label for="fitbot_instructions">' . __('Instructions', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><textarea id="fitbot_instructions" name="fitbot_instructions" rows="8" cols="50">' . esc_textarea($instructions) . '</textarea></td></tr>';
        
        echo '<tr><th><label for="fitbot_prep_time">' . __('Prep Time (minutes)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" id="fitbot_prep_time" name="fitbot_prep_time" value="' . esc_attr($prep_time) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_cook_time">' . __('Cook Time (minutes)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" id="fitbot_cook_time" name="fitbot_cook_time" value="' . esc_attr($cook_time) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_servings">' . __('Servings', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" id="fitbot_servings" name="fitbot_servings" value="' . esc_attr($servings) . '" /></td></tr>';
        
        echo '<tr><th colspan="2"><strong>' . __('Nutrition Facts (per serving)', 'fitbot-ai-chatbot') . '</strong></th></tr>';
        
        echo '<tr><th><label for="fitbot_calories">' . __('Calories', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" id="fitbot_calories" name="fitbot_calories" value="' . esc_attr($calories) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_carbs">' . __('Carbs (g)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" step="0.1" id="fitbot_carbs" name="fitbot_carbs" value="' . esc_attr($carbs) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_fats">' . __('Fats (g)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" step="0.1" id="fitbot_fats" name="fitbot_fats" value="' . esc_attr($fats) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_protein">' . __('Protein (g)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" step="0.1" id="fitbot_protein" name="fitbot_protein" value="' . esc_attr($protein) . '" /></td></tr>';
        
        echo '</table>';
    }
    
    /**
     * Workout meta box callback
     */
    public function workout_meta_box_callback($post) {
        wp_nonce_field('fitbot_workout_meta', 'fitbot_workout_meta_nonce');
        
        $duration = get_post_meta($post->ID, '_fitbot_duration', true);
        $difficulty = get_post_meta($post->ID, '_fitbot_difficulty', true);
        $equipment = get_post_meta($post->ID, '_fitbot_equipment', true);
        $exercises = get_post_meta($post->ID, '_fitbot_exercises', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="fitbot_duration">' . __('Duration (minutes)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" id="fitbot_duration" name="fitbot_duration" value="' . esc_attr($duration) . '" /></td></tr>';
        
        echo '<tr><th><label for="fitbot_difficulty">' . __('Difficulty', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><select id="fitbot_difficulty" name="fitbot_difficulty">';
        echo '<option value="beginner"' . selected($difficulty, 'beginner', false) . '>' . __('Beginner', 'fitbot-ai-chatbot') . '</option>';
        echo '<option value="intermediate"' . selected($difficulty, 'intermediate', false) . '>' . __('Intermediate', 'fitbot-ai-chatbot') . '</option>';
        echo '<option value="advanced"' . selected($difficulty, 'advanced', false) . '>' . __('Advanced', 'fitbot-ai-chatbot') . '</option>';
        echo '</select></td></tr>';
        
        echo '<tr><th><label for="fitbot_equipment">' . __('Equipment Needed', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><textarea id="fitbot_equipment" name="fitbot_equipment" rows="3" cols="50">' . esc_textarea($equipment) . '</textarea></td></tr>';
        
        echo '<tr><th><label for="fitbot_exercises">' . __('Exercises', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><textarea id="fitbot_exercises" name="fitbot_exercises" rows="10" cols="50">' . esc_textarea($exercises) . '</textarea></td></tr>';
        
        echo '</table>';
    }
    
    /**
     * PDF meta box callback
     */
    public function pdf_meta_box_callback($post) {
        wp_nonce_field('fitbot_pdf_meta', 'fitbot_pdf_meta_nonce');
        
        $file_url = get_post_meta($post->ID, '_fitbot_pdf_url', true);
        $file_size = get_post_meta($post->ID, '_fitbot_pdf_size', true);
        $download_count = get_post_meta($post->ID, '_fitbot_download_count', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="fitbot_pdf_url">' . __('PDF File URL', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="url" id="fitbot_pdf_url" name="fitbot_pdf_url" value="' . esc_url($file_url) . '" class="regular-text" />';
        echo '<button type="button" class="button" id="upload_pdf_button">' . __('Upload PDF', 'fitbot-ai-chatbot') . '</button></td></tr>';
        
        echo '<tr><th><label for="fitbot_pdf_size">' . __('File Size (MB)', 'fitbot-ai-chatbot') . '</label></th>';
        echo '<td><input type="number" step="0.1" id="fitbot_pdf_size" name="fitbot_pdf_size" value="' . esc_attr($file_size) . '" /></td></tr>';
        
        echo '<tr><th>' . __('Download Count', 'fitbot-ai-chatbot') . '</th>';
        echo '<td>' . intval($download_count) . '</td></tr>';
        
        echo '</table>';
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        if (isset($_POST['fitbot_recipe_meta_nonce']) && wp_verify_nonce($_POST['fitbot_recipe_meta_nonce'], 'fitbot_recipe_meta')) {
            $fields = array('ingredients', 'instructions', 'prep_time', 'cook_time', 'servings', 'calories', 'carbs', 'fats', 'protein');
            foreach ($fields as $field) {
                if (isset($_POST['fitbot_' . $field])) {
                    update_post_meta($post_id, '_fitbot_' . $field, sanitize_text_field($_POST['fitbot_' . $field]));
                }
            }
        }
        
        if (isset($_POST['fitbot_workout_meta_nonce']) && wp_verify_nonce($_POST['fitbot_workout_meta_nonce'], 'fitbot_workout_meta')) {
            $fields = array('duration', 'difficulty', 'equipment', 'exercises');
            foreach ($fields as $field) {
                if (isset($_POST['fitbot_' . $field])) {
                    update_post_meta($post_id, '_fitbot_' . $field, sanitize_text_field($_POST['fitbot_' . $field]));
                }
            }
        }
        
        if (isset($_POST['fitbot_pdf_meta_nonce']) && wp_verify_nonce($_POST['fitbot_pdf_meta_nonce'], 'fitbot_pdf_meta')) {
            $fields = array('pdf_url', 'pdf_size');
            foreach ($fields as $field) {
                if (isset($_POST['fitbot_' . $field])) {
                    update_post_meta($post_id, '_fitbot_' . $field, sanitize_text_field($_POST['fitbot_' . $field]));
                }
            }
        }
    }
}

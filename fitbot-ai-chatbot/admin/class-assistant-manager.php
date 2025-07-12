<?php
/**
 * Assistant Manager Admin Interface
 * Handles CRUD operations for AI assistants
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Assistant_Manager {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_fitbot_save_assistant', array($this, 'save_assistant'));
        add_action('admin_post_fitbot_delete_assistant', array($this, 'delete_assistant'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'fitbot-settings',
            __('AI Assistants', 'fitbot-ai-chatbot'),
            __('AI Assistants', 'fitbot-ai-chatbot'),
            'manage_options',
            'fitbot-assistants',
            array($this, 'render_page')
        );
    }
    
    /**
     * Render the assistants management page
     */
    public function render_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $assistant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        switch ($action) {
            case 'edit':
                $this->render_edit_form($assistant_id);
                break;
            case 'add':
                $this->render_add_form();
                break;
            default:
                $this->render_list();
                break;
        }
    }
    
    /**
     * Render assistants list
     */
    private function render_list() {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        $assistants = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1><?php _e('AI Assistants', 'fitbot-ai-chatbot'); ?>
                <a href="<?php echo admin_url('admin.php?page=fitbot-assistants&action=add'); ?>" class="page-title-action">
                    <?php _e('Add New Assistant', 'fitbot-ai-chatbot'); ?>
                </a>
            </h1>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($_GET['message']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="notice notice-info" style="margin: 20px 0;">
                <h4>How to Use Assistant Shortcodes</h4>
                <p>Copy any shortcode from the table below and paste it into your pages or posts:</p>
                <ul>
                    <li><code>[fitbot_assistant slug="start"]</code> - Start Plan Assistant</li>
                    <li><code>[fitbot_assistant slug="pro"]</code> - Pro Plan Assistant</li>
                    <li><code>[fitbot_assistant slug="vip"]</code> - VIP Plan Assistant</li>
                </ul>
                <p><strong>Tip:</strong> You can also use aliases like <code>starter-plan</code>, <code>pro-plan</code>, or <code>vip-plan</code></p>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Slug', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Price', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Daily Limit', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Monthly Limit', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Shortcode', 'fitbot-ai-chatbot'); ?></th>
                        <th><?php _e('Actions', 'fitbot-ai-chatbot'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assistants)): ?>
                        <tr>
                            <td colspan="7"><?php _e('No assistants found.', 'fitbot-ai-chatbot'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assistants as $assistant): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($assistant->name); ?></strong>
                                    <div style="color: <?php echo esc_attr($assistant->color); ?>; font-size: 12px;">
                                        <?php echo esc_html($assistant->personality); ?>
                                    </div>
                                </td>
                                <td><code><?php echo esc_html($assistant->slug); ?></code></td>
                                <td><?php echo esc_html($assistant->currency . ' ' . $assistant->price); ?></td>
                                <td><?php echo esc_html($assistant->daily_limit); ?></td>
                                <td><?php echo esc_html($assistant->monthly_limit); ?></td>
                                <td>
                                    <code>[fitbot_assistant slug="<?php echo esc_attr($assistant->slug); ?>"]</code>
                                    <br><small style="color: #666;">Copy this shortcode to use in pages/posts</small>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=fitbot-assistants&action=edit&id=' . $assistant->id); ?>" class="button button-small">
                                        <?php _e('Edit', 'fitbot-ai-chatbot'); ?>
                                    </a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=fitbot_delete_assistant&id=' . $assistant->id), 'delete_assistant_' . $assistant->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this assistant?', 'fitbot-ai-chatbot'); ?>')">
                                        <?php _e('Delete', 'fitbot-ai-chatbot'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render add form
     */
    private function render_add_form() {
        $this->render_form();
    }
    
    /**
     * Render edit form
     */
    private function render_edit_form($assistant_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        $assistant = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $assistant_id));
        
        if (!$assistant) {
            wp_die(__('Assistant not found.', 'fitbot-ai-chatbot'));
        }
        
        $this->render_form($assistant);
    }
    
    /**
     * Render assistant form
     */
    private function render_form($assistant = null) {
        $is_edit = !empty($assistant);
        $title = $is_edit ? __('Edit Assistant', 'fitbot-ai-chatbot') : __('Add New Assistant', 'fitbot-ai-chatbot');
        
        $name = $is_edit ? $assistant->name : '';
        $slug = $is_edit ? $assistant->slug : '';
        $prompt = $is_edit ? $assistant->prompt : '';
        $personality = $is_edit ? $assistant->personality : 'professional';
        $price = $is_edit ? $assistant->price : '5.00';
        $currency = $is_edit ? $assistant->currency : 'EUR';
        $daily_limit = $is_edit ? $assistant->daily_limit : 10;
        $monthly_limit = $is_edit ? $assistant->monthly_limit : 300;
        $woo_product_id = $is_edit ? $assistant->woo_product_id : '';
        $color = $is_edit ? $assistant->color : '#0073aa';
        $greeting_message = $is_edit ? $assistant->greeting_message : '';
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($title); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('fitbot_save_assistant', 'fitbot_nonce'); ?>
                <input type="hidden" name="action" value="fitbot_save_assistant">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="assistant_id" value="<?php echo esc_attr($assistant->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="assistant_name"><?php _e('Assistant Name', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="assistant_name" name="assistant_name" value="<?php echo esc_attr($name); ?>" class="regular-text" required>
                            <p class="description"><?php _e('Display name for the assistant', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="assistant_slug"><?php _e('Slug', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="assistant_slug" name="assistant_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" required pattern="[a-z0-9-]+">
                            <p class="description"><?php _e('Unique identifier for shortcode (lowercase, numbers, hyphens only)', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="assistant_prompt"><?php _e('AI Prompt', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <textarea id="assistant_prompt" name="assistant_prompt" rows="6" class="large-text" required><?php echo esc_textarea($prompt); ?></textarea>
                            <p class="description"><?php _e('Instructions for the AI assistant behavior and personality', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="assistant_personality"><?php _e('Personality', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <select id="assistant_personality" name="assistant_personality">
                                <option value="professional" <?php selected($personality, 'professional'); ?>><?php _e('Professional', 'fitbot-ai-chatbot'); ?></option>
                                <option value="encouraging" <?php selected($personality, 'encouraging'); ?>><?php _e('Encouraging', 'fitbot-ai-chatbot'); ?></option>
                                <option value="expert" <?php selected($personality, 'expert'); ?>><?php _e('Expert', 'fitbot-ai-chatbot'); ?></option>
                                <option value="friendly" <?php selected($personality, 'friendly'); ?>><?php _e('Friendly', 'fitbot-ai-chatbot'); ?></option>
                                <option value="motivational" <?php selected($personality, 'motivational'); ?>><?php _e('Motivational', 'fitbot-ai-chatbot'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="assistant_price"><?php _e('Price', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="assistant_price" name="assistant_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" class="small-text" required>
                            <select name="assistant_currency">
                                <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR</option>
                                <option value="USD" <?php selected($currency, 'USD'); ?>>USD</option>
                                <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP</option>
                            </select>
                            <p class="description"><?php _e('Monthly subscription price', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="daily_limit"><?php _e('Daily Usage Limit', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="daily_limit" name="daily_limit" value="<?php echo esc_attr($daily_limit); ?>" min="1" class="small-text" required>
                            <p class="description"><?php _e('Maximum interactions per day', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="monthly_limit"><?php _e('Monthly Usage Limit', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="monthly_limit" name="monthly_limit" value="<?php echo esc_attr($monthly_limit); ?>" min="1" class="regular-text" required>
                            <p class="description"><?php _e('Maximum interactions per month', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="woo_product_id"><?php _e('WooCommerce Product ID', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="woo_product_id" name="woo_product_id" value="<?php echo esc_attr($woo_product_id); ?>" class="regular-text">
                            <p class="description"><?php _e('WooCommerce subscription product ID for this assistant', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="assistant_color"><?php _e('Theme Color', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="assistant_color" name="assistant_color" value="<?php echo esc_attr($color); ?>">
                            <p class="description"><?php _e('Primary color for this assistant\'s interface', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="greeting_message"><?php _e('Greeting Message', 'fitbot-ai-chatbot'); ?></label>
                        </th>
                        <td>
                            <textarea id="greeting_message" name="greeting_message" rows="3" class="large-text"><?php echo esc_textarea($greeting_message); ?></textarea>
                            <p class="description"><?php _e('Initial message shown to users', 'fitbot-ai-chatbot'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($is_edit ? __('Update Assistant', 'fitbot-ai-chatbot') : __('Create Assistant', 'fitbot-ai-chatbot')); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Save assistant
     */
    public function save_assistant() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'fitbot-ai-chatbot'));
        }
        
        check_admin_referer('fitbot_save_assistant', 'fitbot_nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        $assistant_id = isset($_POST['assistant_id']) ? intval($_POST['assistant_id']) : 0;
        $name = sanitize_text_field($_POST['assistant_name']);
        $slug = sanitize_title($_POST['assistant_slug']);
        $prompt = sanitize_textarea_field($_POST['assistant_prompt']);
        $personality = sanitize_text_field($_POST['assistant_personality']);
        $price = floatval($_POST['assistant_price']);
        $currency = sanitize_text_field($_POST['assistant_currency']);
        $daily_limit = intval($_POST['daily_limit']);
        $monthly_limit = intval($_POST['monthly_limit']);
        $woo_product_id = intval($_POST['woo_product_id']);
        $color = sanitize_hex_color($_POST['assistant_color']);
        $greeting_message = sanitize_textarea_field($_POST['greeting_message']);
        
        if (!$assistant_id) {
            $existing_slug = $wpdb->get_var($wpdb->prepare(
                "SELECT slug FROM $table WHERE slug = %s",
                $slug
            ));
            
            if ($existing_slug) {
                wp_die(__('Error: An assistant with the slug "' . esc_html($slug) . '" already exists. Please choose a different slug.', 'fitbot-ai-chatbot'));
            }
        }
        
        $data = array(
            'name' => $name,
            'slug' => $slug,
            'prompt' => $prompt,
            'personality' => $personality,
            'price' => $price,
            'currency' => $currency,
            'daily_limit' => $daily_limit,
            'monthly_limit' => $monthly_limit,
            'woo_product_id' => $woo_product_id,
            'color' => $color,
            'greeting_message' => $greeting_message
        );
        
        if ($assistant_id) {
            $result = $wpdb->update($table, $data, array('id' => $assistant_id));
            $message = __('Assistant updated successfully!', 'fitbot-ai-chatbot');
        } else {
            $result = $wpdb->insert($table, $data);
            $message = __('Assistant created successfully!', 'fitbot-ai-chatbot');
        }
        
        if ($result === false) {
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false && strpos($wpdb->last_error, 'slug') !== false) {
                wp_die(__('Error: An assistant with this slug already exists. Please choose a different slug.', 'fitbot-ai-chatbot'));
            }
            elseif (strpos($wpdb->last_error, 'cannot be null') !== false) {
                wp_die(__('Error: All required fields must be filled out.', 'fitbot-ai-chatbot'));
            }
            else {
                wp_die(__('Error saving assistant: ' . esc_html($wpdb->last_error), 'fitbot-ai-chatbot'));
            }
        }
        
        wp_redirect(admin_url('admin.php?page=fitbot-assistants&message=' . urlencode($message)));
        exit;
    }
    
    /**
     * Delete assistant
     */
    public function delete_assistant() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'fitbot-ai-chatbot'));
        }
        
        $assistant_id = intval($_GET['id']);
        check_admin_referer('delete_assistant_' . $assistant_id);
        
        global $wpdb;
        $table = $wpdb->prefix . 'fitbot_assistants';
        
        $result = $wpdb->delete($table, array('id' => $assistant_id));
        
        if ($result === false) {
            wp_die(__('Error deleting assistant', 'fitbot-ai-chatbot'));
        }
        
        $message = __('Assistant deleted successfully!', 'fitbot-ai-chatbot');
        wp_redirect(admin_url('admin.php?page=fitbot-assistants&message=' . urlencode($message)));
        exit;
    }
}

<?php
/**
 * Chatbot Widget Template
 * This template is loaded on the frontend to display the chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = array(
    'delay' => Fitbot_Core::get_setting('chatbot_delay', 3000),
    'greeting' => Fitbot_Core::get_setting('greeting_message', __('Hello! I\'m FITBOT, your AI fitness assistant. What are your health and fitness goals today?', 'fitbot-ai-chatbot')),
    'color' => Fitbot_Core::get_setting('primary_color', '#0073aa'),
    'position' => Fitbot_Core::get_setting('chatbot_position', 'bottom-right'),
    'enable_sound' => Fitbot_Core::get_setting('enable_sound', true),
    'enable_typing' => Fitbot_Core::get_setting('enable_typing', true)
);

$user_plan = 'none';
if (is_user_logged_in()) {
    $subscription_checker = new Fitbot_Woo_Subscription_Check();
    $user_plan = $subscription_checker->get_user_plan(get_current_user_id());
}
?>

<script type="text/javascript">
var fitbot_ajax = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('fitbot_nonce'); ?>',
    delay: <?php echo intval($settings['delay']); ?>,
    greeting: <?php echo json_encode($settings['greeting']); ?>,
    color: <?php echo json_encode($settings['color']); ?>,
    position: <?php echo json_encode($settings['position']); ?>,
    enable_sound: <?php echo $settings['enable_sound'] ? 'true' : 'false'; ?>,
    enable_typing: <?php echo $settings['enable_typing'] ? 'true' : 'false'; ?>,
    user_plan: <?php echo json_encode($user_plan); ?>,
    is_logged_in: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>
};
</script>

<?php
if ($settings['color'] !== '#0073aa') {
    $rgb = hex_to_rgb($settings['color']);
    echo '<style id="fitbot-custom-theme">
        .fitbot-chatbot {
            --fitbot-primary-color: ' . esc_attr($settings['color']) . ';
            --fitbot-primary-rgb: ' . esc_attr($rgb) . ';
        }
    </style>';
}

$position_class = 'fitbot-position-' . str_replace('-', '_', $settings['position']);
echo '<script>document.body.classList.add("' . esc_js($position_class) . '");</script>';
?>

<!-- Chatbot will be injected here by JavaScript -->
<div id="fitbot-chatbot-container"></div>

<?php
function hex_to_rgb($hex) {
    $hex = ltrim($hex, '#');
    
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return "$r, $g, $b";
}
?>

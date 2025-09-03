<?php
class Glint_Email_Automation_Save_Settings {

    public function __construct() {
        add_action('save_post', array($this, 'save_settings'));
    }

    public function save_settings($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['automation_nonce']) || !wp_verify_nonce($_POST['automation_nonce'], 'glint_email_automation_nonce')) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $settings = array(
            'triggered_by' => sanitize_text_field($_POST['triggered_by']),
            'product_trigger' => array_map('absint', $_POST['product_trigger'] ?? array()),
            'category_trigger' => array_map('absint', $_POST['category_trigger'] ?? array()),
            'send_from_title' => sanitize_text_field($_POST['send_from_title']),
            'send_from_email' => sanitize_email($_POST['send_from_email']),
            'bcc' => sanitize_text_field($_POST['bcc']),
            'email_title' => sanitize_text_field($_POST['email_title']), 
            'email_head' => wp_kses_post($_POST['email_head']),
            'email_footer' => wp_kses_post($_POST['email_footer']),
            'days_after' => absint($_POST['days_after']),
            'days_between' => absint($_POST['days_between']),
            'maximum_sent' => absint($_POST['maximum_sent']) 
        );

        update_post_meta($post_id, '_glint_email_automation_settings', $settings);
    }
}
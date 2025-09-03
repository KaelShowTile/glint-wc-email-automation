<?php
class Glint_Email_Automation_Meta_Boxes {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'glint-email-automation-settings',
            'Email Settings',
            array($this, 'render_settings_meta_box'),
            'email-automation',
            'normal',
            'high'
        );
    }

    public function render_settings_meta_box($post) {
        wp_nonce_field('glint_email_automation_nonce', 'automation_nonce');
        
        $settings = get_post_meta($post->ID, '_glint_email_automation_settings', true);
        $settings = wp_parse_args($settings, array(
            'triggered_by' => 'product',
            'product_trigger' => array(),
            'category_trigger' => array(),
            'send_from_title' => '',
            'send_from_email' => '',
            'bcc' => '',
            'email_title' => '', 
            'email_head' => '', 
            'email_footer' => '',
            'days_after' => 0,
            'days_between' => 1,
            'maximum_sent' => 1
        ));

        include GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/views/meta-box-settings.php';
    }

    public function enqueue_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) return;
        
        // Enqueue Select2
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
        
        // Enqueue our scripts
        wp_enqueue_script('glint-email-automation-admin', 
            GLINT_WC_EMAIL_AUTOMATION_PLUGIN_URL . 'assets/js/admin.js', 
            array('jquery', 'select2'), 
            GLINT_WC_EMAIL_AUTOMATION_VERSION, 
            true
        );
        
        wp_enqueue_style('glint-email-automation-admin', 
            GLINT_WC_EMAIL_AUTOMATION_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            GLINT_WC_EMAIL_AUTOMATION_VERSION
        );
        
        // Localize script with nonce
        wp_localize_script('glint-email-automation-admin', 'glint_email_automation', array(
            'nonce' => wp_create_nonce('glint_email_automation_nonce')
        ));
    }
}
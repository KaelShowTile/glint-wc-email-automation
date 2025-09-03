<?php
class Glint_Email_Automation_Post_Type {

    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
    }

    public function register_post_type() {
        $labels = array(
            'name' => __('Email Automation', 'glint-wc-email-automation'),
            'singular_name' => __('Email Automation', 'glint-wc-email-automation'),
            'menu_name' => __('Email Automation', 'glint-wc-email-automation'),
            'name_admin_bar' => __('Email Automation', 'glint-wc-email-automation'),
            'add_new' => __('Add New', 'glint-wc-email-automation'),
            'add_new_item' => __('Add New Automation', 'glint-wc-email-automation'),
            'new_item' => __('New Automation', 'glint-wc-email-automation'),
            'edit_item' => __('Edit Automation', 'glint-wc-email-automation'),
            'view_item' => __('View Automation', 'glint-wc-email-automation'),
            'all_items' => __('All Automations', 'glint-wc-email-automation'),
            'search_items' => __('Search Automations', 'glint-wc-email-automation'),
            'not_found' => __('No Automations found.', 'glint-wc-email-automation'),
            'not_found_in_trash' => __('No Automations found in Trash.', 'glint-wc-email-automation')
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'email-automation'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor'),
            'menu_icon' => 'dashicons-email'
        );
        register_post_type('email-automation', $args);
    }
}

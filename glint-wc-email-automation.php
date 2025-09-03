<?php
/**
 * Plugin Name: CHT WooCommerce Email Automation
 * Description: Send automated emails based on purchase triggers
 * Version: 1.0.0
 * Author: Kael
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('GLINT_WC_EMAIL_AUTOMATION_VERSION', '1.0.0');
define('GLINT_WC_EMAIL_AUTOMATION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-post-type.php';
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-meta-boxes.php';
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-save-settings.php';
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-ajax-handlers.php'; 
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-database.php';
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-order-handler.php'; 
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-admin-page.php';
require_once GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/class-cron.php';

class Glint_WC_Email_Automation {

    private $post_type;
    private $meta_boxes;
    private $save_settings;
    private $ajax_handlers;
    private $database;
    private $order_handler;
    private $admin_page;
    private $cron;

    public function __construct() {
        $this->init();
    }

    public function init() {
        // Initialize all components
        $this->post_type = new Glint_Email_Automation_Post_Type();
        $this->meta_boxes = new Glint_Email_Automation_Meta_Boxes();
        $this->save_settings = new Glint_Email_Automation_Save_Settings();
        $this->ajax_handlers = new Glint_Email_Automation_AJAX_Handlers();
        $this->database = new Glint_Email_Automation_Database();
        $this->order_handler = new Glint_Email_Automation_Order_Handler();
        $this->admin_page = new Glint_Email_Automation_Admin_Page();
        $this->cron = new Glint_Email_Automation_Cron();
        
        // Register activation hook to flush rewrite rules
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function activate() {
        // Flush rewrite rules on plugin activation
        flush_rewrite_rules();

        // Create database tables
        $database = new Glint_Email_Automation_Database();
        $database->create_tables();
    }
}

new Glint_WC_Email_Automation();
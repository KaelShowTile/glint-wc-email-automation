<?php
class Glint_Email_Automation_Database {

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'create_tables'));
        add_action('plugins_loaded', array($this, 'check_tables'));
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';

        $sql = "CREATE TABLE $table_name (
            email_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            automation_id BIGINT UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            purchase_date DATETIME NOT NULL,
            first_sending_date DATETIME NOT NULL,
            next_sending_date DATETIME NULL,
            total_email_sent INT UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(20) DEFAULT 'scheduled',
            last_sent_date DATETIME NULL,
            order_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (email_id),
            KEY automation_id (automation_id),
            KEY next_sending_date (next_sending_date),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function check_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $this->create_tables();
        }
    }
}
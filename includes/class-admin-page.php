<?php
class Glint_Email_Automation_Admin_Page {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=email-automation', // Changed from 'woocommerce'
            __('Email Automation Schedules', 'glint-wc-email-automation'),
            __('Scheduled Email', 'glint-wc-email-automation'), // Changed label
            'manage_options',
            'glint-email-automation',
            array($this, 'render_admin_page')
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ('email-automation_page_glint-email-automation' !== $hook) {
            return;
        }
        
        wp_enqueue_style('glint-email-automation-admin', 
            GLINT_WC_EMAIL_AUTOMATION_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            GLINT_WC_EMAIL_AUTOMATION_VERSION
        );
        
        wp_enqueue_script('glint-email-automation-admin', 
            GLINT_WC_EMAIL_AUTOMATION_PLUGIN_URL . 'assets/js/admin-list.js', 
            array('jquery'), 
            GLINT_WC_EMAIL_AUTOMATION_VERSION, 
            true
        );
        
        wp_localize_script('glint-email-automation-admin', 'glint_email_automation', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('glint_email_automation_nonce')
        ));
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';

        // Handle manual process trigger
        if (isset($_POST['process_emails_manually'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'process_emails_manually')) {
                $cron = new Glint_Email_Automation_Cron();
                $cron->process_scheduled_emails();
                echo '<div class="notice notice-success"><p>Email processing completed.</p></div>';
            }
        }
        
        // Handle bulk actions
        if (isset($_POST['action']) || isset($_POST['action2'])) {
            $this->handle_bulk_actions();
        }
        
        // Get pagination parameters
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Build query
        $where = array();
        $query_params = array();
        
        if (isset($_GET['status']) && $_GET['status'] !== 'all') {
            $where[] = 'status = %s';
            $query_params[] = sanitize_text_field($_GET['status']);
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table_name $where_clause";
        if (!empty($query_params)) {
            $count_query = $wpdb->prepare($count_query, $query_params);
        }
        $total_items = $wpdb->get_var($count_query);
        
        // Get items
        $query = "SELECT * FROM $table_name $where_clause ORDER BY next_sending_date DESC LIMIT %d OFFSET %d";
        $query_params[] = $per_page;
        $query_params[] = $offset;
        
        $items = $wpdb->get_results($wpdb->prepare($query, $query_params));
        
        include GLINT_WC_EMAIL_AUTOMATION_PLUGIN_PATH . 'includes/views/admin-page.php';

        echo '<form method="post" style="margin: 40px auto auto;">';
        wp_nonce_field('process_emails_manually');
        echo '<input type="submit" name="process_emails_manually" class="button button-primary" value="Process Emails Now">';
        echo '</form>';
    }

    private function handle_bulk_actions() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'bulk-emails')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        $action = isset($_POST['action']) ? $_POST['action'] : $_POST['action2'];
        
        if (empty($_POST['email_ids'])) {
            return;
        }
        
        $email_ids = array_map('intval', $_POST['email_ids']);
        
        switch ($action) {
            case 'delete':
                $placeholders = implode(',', array_fill(0, count($email_ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name WHERE email_id IN ($placeholders)",
                    $email_ids
                ));
                break;
                
            case 'mark_sent':
                $placeholders = implode(',', array_fill(0, count($email_ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET status = 'sent', last_sent_date = NOW() WHERE email_id IN ($placeholders)",
                    $email_ids
                ));
                break;
                
            case 'mark_scheduled':
                $placeholders = implode(',', array_fill(0, count($email_ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET status = 'scheduled' WHERE email_id IN ($placeholders)",
                    $email_ids
                ));
                break;
        }
    }
}
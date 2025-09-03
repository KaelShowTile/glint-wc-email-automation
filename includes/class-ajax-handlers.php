<?php
class Glint_Email_Automation_AJAX_Handlers {

    public function __construct() {
        add_action('wp_ajax_glint_search_products', array($this, 'search_products'));
        add_action('wp_ajax_glint_search_categories', array($this, 'search_categories'));
        add_action('wp_ajax_glint_delete_email_record', array($this, 'handle_delete_email_record'));
    }

    public function search_products() {
        $this->verify_request();
        
        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        
        if (empty($term)) {
            wp_send_json(array());
        }
        
        // Search for products
        $data_store = WC_Data_Store::load('product');
        $ids = $data_store->search_products($term, '', true, false, 10);
        
        $products = array();
        
        foreach ($ids as $id) {
            $product = wc_get_product($id);
            
            if ($product) {
                $products[] = array(
                    'id' => $id,
                    'text' => $product->get_formatted_name()
                );
            }
        }
        
        wp_send_json($products);
    }

    public function search_categories() {
        $this->verify_request();
        
        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        
        if (empty($term)) {
            wp_send_json(array());
        }
        
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'name__like' => $term,
            'hide_empty' => false,
            'number' => 10
        ));
        
        $results = array();
        
        foreach ($categories as $category) {
            $results[] = array(
                'id' => $category->term_id,
                'text' => $category->name
            );
        }
        
        wp_send_json($results);
    }
    
    private function verify_request() {
        check_ajax_referer('glint_email_automation_nonce', 'security');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('You do not have sufficient permissions.'));
        }
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error(__('WooCommerce is not active.'));
        }
    }

    public function handle_delete_email_record() {
        // Check if email_id and nonce are provided
        if (!isset($_POST['email_id']) || !isset($_POST['nonce'])) {
            wp_send_json_error(__('Missing required parameters.', 'glint-wc-email-automation'));
            wp_die();
        }
        
        $email_id = intval($_POST['email_id']);
        $nonce = sanitize_text_field($_POST['nonce']);
        
        // Verify nonce
        if (!wp_verify_nonce($nonce, 'delete_email_record_' . $email_id)) {
            wp_send_json_error(__('Security check failed.', 'glint-wc-email-automation'));
            wp_die();
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'glint-wc-email-automation'));
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        
        // Delete the record
        $result = $wpdb->delete(
            $table_name,
            array('email_id' => $email_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(__('Failed to delete the record from the database.', 'glint-wc-email-automation'));
        } else {
            wp_send_json_success(__('Record deleted successfully.', 'glint-wc-email-automation'));
        }
        
        wp_die();
    }
}
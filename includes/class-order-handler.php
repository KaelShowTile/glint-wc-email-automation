<?php
class Glint_Email_Automation_Order_Handler {

    public function __construct() {
        // Hook for traditional checkout (shortcode)
        add_action('woocommerce_checkout_order_processed', array($this, 'process_order_traditional'), 10, 3);

        // Hook for block checkout
        add_action('woocommerce_store_api_checkout_order_processed', array($this, 'process_order_block'), 10, 1);
    }

    public function process_order_traditional($order_id, $posted_data, $order) {
        $this->process_order_automation($order_id, $order);
    }

    public function process_order_block($order) {
        $this->process_order_automation($order->get_id(), $order);
    }

    public function process_order_automation($order_id, $order) {

        // Get all active automations
        $automations = $this->get_all_automations();

        // Get order items
        $items = $order->get_items();
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $purchase_date = current_time('Y-m-d H:i:s');

        //error_log('Raw date created: ' . print_r($order->get_date_created(), true));
        
        foreach ($automations as $automation) {
            $settings = get_post_meta($automation->ID, '_glint_email_automation_settings', true);
            
            if (!$settings || empty($settings)) {
                continue;
            }
            
            // Check if this order matches the automation triggers
            $is_match = $this->check_order_against_automation($order, $items, $settings);
            
            if ($is_match) {
                $this->schedule_email($automation->ID, $customer_name, $customer_email, $purchase_date, $settings, $order_id);
            }
        }
    }

    private function get_all_automations() {
        $args = array(
            'post_type' => 'email-automation',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );
        
        return get_posts($args);
    }

    private function check_order_against_automation($order, $items, $settings) {
        $triggered_by = $settings['triggered_by'];
        
        if ($triggered_by === 'product') {
            return $this->check_products($items, $settings['product_trigger']);
        } elseif ($triggered_by === 'category') {
            return $this->check_categories($items, $settings['category_trigger']);
        }
        
        return false;
    }

    private function check_products($items, $trigger_products) {
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            if (in_array($product_id, $trigger_products)) {
                return true;
            }
        }
        return false;
    }

    private function check_categories($items, $trigger_categories) {
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $product_categories = wc_get_product_term_ids($product_id, 'product_cat');
            
            if (array_intersect($trigger_categories, $product_categories)) {
                return true;
            }
        }
        return false;
    }

    private function schedule_email($automation_id, $customer_name, $customer_email, $purchase_date, $settings, $order_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glint_wc_automated_email';
        
        $days_after = intval($settings['days_after']);
        $first_sending_date = date('Y-m-d H:i:s', strtotime($purchase_date . " + {$days_after} days"));
        
        $wpdb->insert(
            $table_name,
            array(
                'automation_id' => $automation_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'purchase_date' => $purchase_date,
                'first_sending_date' => $first_sending_date,
                'next_sending_date' => $first_sending_date,
                'total_email_sent' => 0,
                'order_id' => $order_id,
                'status' => 'scheduled'
            ),
            array(
                '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s'
            )
        );
    }
}
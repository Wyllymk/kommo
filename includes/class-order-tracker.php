<?php

namespace WyllyMk\KommoCRM;

class OrderTracker {
    private static $instance = null;
    private $table_name;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'kommo_order_tracking';
    }

    /**
     * Track a WooCommerce order
     *
     * @param int $order_id WooCommerce order ID
     * @return bool|int False on failure, number of rows affected on success
     */
    public function track_order($order_id) {
        global $wpdb;
        
        $order = wc_get_order($order_id);
        if (!$order) return false;

        // Collect order data
        $data = array(
            'order_id'       => $order_id,
            'customer_id'    => $order->get_customer_id(),
            'customer_email' => sanitize_email($order->get_billing_email()),
            'customer_name'  => sanitize_text_field($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'order_total'    => $order->get_total(),
            'payment_method' => sanitize_text_field($order->get_payment_method_title()),
            'order_status'   => sanitize_text_field($order->get_status()),
            'created_at'     => $order->get_date_created()->format('Y-m-d H:i:s'),
            'products'       => $this->get_order_products($order),
            'coupons'        => $this->get_order_coupons($order),
            'billing_address'=> wp_json_encode($this->get_billing_address($order))
        );

        return $wpdb->insert(
            $this->table_name,
            $data,
            array('%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get formatted order products
     */
    private function get_order_products($order) {
        $products = array();
        foreach ($order->get_items() as $item) {
            $products[] = array(
                'id' => $item->get_product_id(),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total()
            );
        }
        return wp_json_encode($products);
    }

    /**
     * Get used coupons
     */
    private function get_order_coupons($order) {
        return wp_json_encode($order->get_coupon_codes());
    }

    /**
     * Get billing address
     */
    private function get_billing_address($order) {
        return array(
            'address_1' => $order->get_billing_address_1(),
            'address_2' => $order->get_billing_address_2(),
            'city'      => $order->get_billing_city(),
            'state'     => $order->get_billing_state(),
            'postcode'  => $order->get_billing_postcode(),
            'country'   => $order->get_billing_country()
        );
    }

}
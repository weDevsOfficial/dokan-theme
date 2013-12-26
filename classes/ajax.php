<?php

class Dokan_Ajax {

    public static function init() {

        static $instance = false;

        if ( !$instance ) {
            $instance = new self;
        }

        return $instance;
    }

    function init_ajax() {
        //withdraw note
        $withdraw = Dokan_Template_Withdraw::init();
        add_action( 'wp_ajax_note', array( $withdraw, 'note_update' ) );
        add_action( 'wp_ajax_withdraw_ajax_submission', array( $withdraw, 'withdraw_ajax' ) );

        // reviews
        $reviews = Dokan_Template_reviews::init();
        add_action( 'wp_ajax_wpuf_comment_status', array( $reviews, 'ajax_comment_status' ) );
        add_action( 'wp_ajax_wpuf_update_comment', array( $reviews, 'ajax_update_comment' ) );

        //settings
        $settings = Dokan_Template_Settings::init();
        add_action( 'wp_ajax_dokan_settings', array( $settings, 'ajax_settings' ) );

        add_action( 'wp_ajax_dokan-mark-order-complete', array( $this, 'complete_order' ) );
        add_action( 'wp_ajax_dokan-mark-order-processing', array( $this, 'process_order' ) );
        add_action( 'wp_ajax_dokan_grant_access_to_download', array( $this, 'grant_access_to_download' ) );

    }

    function complete_order() {
        if ( !is_admin() ) {
            die();
        }

        if ( !current_user_can('edit_shop_orders') ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        if ( !check_admin_referer('dokan-mark-order-complete')) {
            wp_die( __( 'You have taken too long. Please go back and retry.', 'dokan' ) );
        }

        $order_id = isset($_GET['order_id']) && (int) $_GET['order_id'] ? (int) $_GET['order_id'] : '';
        if ( !$order_id ) {
            die();
        }

        if ( !dokan_is_seller_has_order( get_current_user_id(), $order_id ) ) {
            wp_die( __( 'You do not have permission to change this order', 'dokan' ) );
        }

        $order = new WC_Order( $order_id );
        $order->update_status( 'completed' );

        wp_safe_redirect( wp_get_referer() );
    }

    function process_order() {
        if ( !is_admin() ) {
            die();
        }

        if ( !current_user_can('edit_shop_orders') ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        if ( !check_admin_referer('dokan-mark-order-processing')) {
            wp_die( __( 'You have taken too long. Please go back and retry.', 'dokan' ) );
        }

        $order_id = isset($_GET['order_id']) && (int) $_GET['order_id'] ? (int) $_GET['order_id'] : '';
        if ( !$order_id ) {
            die();
        }

        if ( !dokan_is_seller_has_order( get_current_user_id(), $order_id ) ) {
            wp_die( __( 'You do not have permission to change this order', 'dokan' ) );
        }

        $order = new WC_Order( $order_id );
        $order->update_status( 'processing' );

        wp_safe_redirect( wp_get_referer() );
    }

    /**
     * Grant download permissions via ajax function
     *
     * @access public
     * @return void
     */
    function grant_access_to_download() {

        check_ajax_referer( 'grant-access', 'security' );

        global $wpdb;

        $order_id   = intval( $_POST['order_id'] );
        $product_id = intval( $_POST['product_id'] );
        $loop       = intval( $_POST['loop'] );
        $file_count = 0;

        $order      = new WC_Order( $order_id );
        $product    = get_product( $product_id );

        $user_email = sanitize_email( $order->billing_email );

        if ( ! $user_email )
            die();

        $limit      = trim( get_post_meta( $product_id, '_download_limit', true ) );
        $expiry     = trim( get_post_meta( $product_id, '_download_expiry', true ) );
        $file_paths = apply_filters( 'woocommerce_file_download_paths', get_post_meta( $product_id, '_file_paths', true ), $product_id, $order_id, null );

        $limit      = empty( $limit ) ? '' : (int) $limit;

        // Default value is NULL in the table schema
        $expiry     = empty( $expiry ) ? null : (int) $expiry;

        if ( $expiry )
            $expiry = date_i18n( "Y-m-d", strtotime( 'NOW + ' . $expiry . ' DAY' ) );

        $wpdb->hide_errors();

        $response = array();
        if ( $file_paths ) {
            foreach ( $file_paths as $download_id => $file_path ) {

                $data = array(
                    'download_id'           => $download_id,
                    'product_id'            => $product_id,
                    'user_id'               => (int) $order->user_id,
                    'user_email'            => $user_email,
                    'order_id'              => $order->id,
                    'order_key'             => $order->order_key,
                    'downloads_remaining'   => $limit,
                    'access_granted'        => current_time( 'mysql' ),
                    'download_count'        => 0
                );

                $format = array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d'
                );

                if ( ! is_null( $expiry ) ) {
                    $data['access_expires'] = $expiry;
                    $format[] = '%s';
                }

                // Downloadable product - give access to the customer
                $success = $wpdb->insert( $wpdb->prefix . 'woocommerce_downloadable_product_permissions',
                    $data,
                    $format
                );

                if ( $success ) {

                    $download = new stdClass();
                    $download->product_id   = $product_id;
                    $download->download_id  = $download_id;
                    $download->order_id     = $order->id;
                    $download->order_key    = $order->order_key;
                    $download->download_count       = 0;
                    $download->downloads_remaining  = $limit;
                    $download->access_expires       = $expiry;

                    $loop++;
                    $file_count++;

                    include dirname( dirname( __FILE__ ) ) . '/templates/orders/order-download-permission-html.php';
                }
            }
        }

        die();
    }

}
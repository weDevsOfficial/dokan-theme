<?php

/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_get_seller_orders( $seller_id ) {
    global $wpdb;

    $sql = "SELECT oi.order_id, p.post_date FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            WHERE
                oim.meta_key = '_product_id' AND
                p.post_author = %d AND
                p.post_status = 'publish'
            GROUP BY oi.order_id";

    return $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );
}

function dokan_get_seller_order_ids( $seller_id ) {
    $orders = dokan_get_seller_orders( $seller_id );
    $order_ids = array();

    if ( $orders ) {
        $order_ids = wp_list_pluck( $orders, 'order_id' );
    }

    return $order_ids;
}


/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_is_seller_has_order( $seller_id, $order_id ) {
    global $wpdb;

    $sql = "SELECT oi.order_id FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            WHERE oim.meta_key = '_product_id' AND p.post_author = %d AND oi.order_id = %d
            GROUP BY oi.order_id";

    return $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $order_id ) );
}

function dokan_is_user_seller( $user_id ) {
    if ( !user_can( $user_id, 'edit_shop_orders' ) ) {
        return false;
    }

    return true;
}

function dokan_is_product_author( $product_id = 0 ) {
    global $post;

    if ( !$product_id ) {
        $author = $post->post_author;
    } else {
        $author = get_post_field( 'post_author', $product_id );
    }

    if ( $author == get_current_user_id() ) {
        return true;
    }

    return false;
}

function dokan_redirect_login() {
    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }
}

function dokan_redirect_if_not_seller( $redirect = '' ) {
    if ( !dokan_is_user_seller( get_current_user_id() ) ) {
        $redirect = empty( $redirect ) ? home_url( '/' ) : $redirect;

        wp_redirect( $redirect );
        exit;
    }
}

function dokan_delete_product_handler() {
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-delete-product' ) {
        $product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;

        if ( !$product_id ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan-delete-product' ) ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        if ( !dokan_is_product_author( $product_id ) ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        // wp_delete_post( $product_id );
        wp_redirect( add_query_arg( array( 'message' => 'product_deleted' ), get_permalink() ) );
        exit;
    }
}
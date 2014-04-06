<?php
/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_get_seller_orders( $seller_id, $status = 'all', $limit = 10, $offset = 0 ) {
    global $wpdb;

    $cache_key = 'dokan-seller-orders-' . $status . '-' . $seller_id;
    $orders = wp_cache_get( $cache_key, 'dokan' );

    if ( $orders === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );

        $sql = "SELECT do.order_id, p.post_date
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_status = 'publish'
                    $status_where
                GROUP BY do.order_id
                ORDER BY p.post_date DESC
                LIMIT $offset, $limit";

        $orders = $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );
        wp_cache_set( $cache_key, $orders, 'dokan' );
    }

    return $orders;
}

/**
 * Get the orders total from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_get_seller_orders_number( $seller_id, $status = 'all' ) {
    global $wpdb;

    $cache_key = 'dokan-seller-orders-count-' . $status . '-' . $seller_id;
    $count = wp_cache_get( $cache_key, 'dokan' );

    if ( $count === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );

        $sql = "SELECT COUNT(do.order_id) as count
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_status = 'publish'
                    $status_where";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $count = $result->count;

        wp_cache_set( $cache_key, $count, 'dokan' );
    }

    return $count;
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

    $sql = "SELECT do.order_id, p.post_date
            FROM {$wpdb->prefix}dokan_orders AS do
            LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
            WHERE
                do.seller_id = %d AND
                p.post_status = 'publish' AND
                do.order_id = %d
            GROUP BY do.order_id";

    return $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $order_id ) );
}

/**
 * Count orders for a seller
 *
 * @global WPDB $wpdb
 * @param int $user_id
 * @return array
 */
function dokan_count_orders( $user_id ) {
    global $wpdb;

    $cache_key = 'dokan-count-orders-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( $counts === false ) {
        $counts = array('pending' => 0, 'completed' => 0, 'on-hold' => 0, 'processing' => 0, 'refunded' => 0, 'cancelled' => 0, 'total' => 0);

        $sql = "SELECT do.order_status
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_type = 'shop_order' AND
                    p.post_status = 'publish'";

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

        if ($results) {
            $total = 0;

            foreach ($results as $order) {
                if ( isset( $counts[$order->order_status] ) ) {
                    $counts[$order->order_status] += 1;
                    $counts['total'] += 1;
                }
            }
        }

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}

/**
 * Update the child order status when a parent order status is changed
 *
 * @global object $wpdb
 * @param int $order_id
 * @param string $old_status
 * @param string $new_status
 */
function dokan_on_order_status_change( $order_id, $old_status, $new_status ) {
    global $wpdb;

    // insert on dokan sync table
    $wpdb->update( $wpdb->prefix . 'dokan_orders',
        array( 'order_status' => $new_status ),
        array( 'order_id' => $order_id ),
        array( '%s' ),
        array( '%d' )
    );

    // if any child orders found, change the orders as well
    $sub_orders = get_children( array( 'post_parent' => $order_id, 'post_type' => 'shop_order' ) );
    if ( $sub_orders ) {
        foreach ($sub_orders as $order_post) {
            $order = new WC_Order( $order_post->ID );
            $order->update_status( $new_status );
        }
    }
}

add_action( 'woocommerce_order_status_changed', 'dokan_on_order_status_change', 10, 3 );


/**
 * Mark the parent order as complete when all the child order are completed
 *
 * @param int $order_id
 * @param string $old_status
 * @param string $new_status
 * @return void
 */
function dokan_on_child_order_status_change( $order_id, $old_status, $new_status ) {
    $order_post = get_post( $order_id );

    // we are monitoring only child orders
    if ( $order_post->post_parent === 0 ) {
        return;
    }

    // get all the child orders and monitor the status
    $parent_order_id = $order_post->post_parent;
    $sub_orders = get_children( array( 'post_parent' => $parent_order_id, 'post_type' => 'shop_order' ) );


    // return if any child order is not completed
    $all_complete = true;

    if ( $sub_orders ) {
        foreach ($sub_orders as $sub) {
            $order = new WC_Order( $sub->ID );

            if ( $order->status != 'completed' ) {
                $all_complete = false;
            }
        }
    }

    // seems like all the child orders are completed
    // mark the parent order as complete
    if ( $all_complete ) {
        $parent_order = new WC_Order( $parent_order_id );
        $parent_order->update_status( 'completed', __( 'Mark parent order completed as all child orders are completed.', 'dokan' ) );
    }
}

add_action( 'woocommerce_order_status_changed', 'dokan_on_child_order_status_change', 99, 3 );


/**
 * Delete a order row from sync table when a order is deleted from WooCommerce
 *
 * @global object $wpdb
 * @param type $order_id
 */
function dokan_delete_sync_order( $order_id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'dokan_orders', array( 'order_id' => $order_id ) );
}


/**
 * Insert a order in sync table once a order is created
 *
 * @global object $wpdb
 * @param int $order_id
 */
function dokan_sync_insert_order( $order_id ) {
    global $wpdb;

    $order = new WC_Order( $order_id );
    $seller_id = dokan_get_seller_id_by_order( $order_id );
    $percentage = dokan_get_seller_percentage( $seller_id );
    $order_total = $order->get_total();

    $wpdb->insert( $wpdb->prefix . 'dokan_orders',
        array(
            'order_id' => $order_id,
            'seller_id' => $seller_id,
            'order_total' => $order_total,
            'net_amount' => ($order_total * $percentage)/100,
            'order_status' => $order->status,
        ),
        array(
            '%d',
            '%d',
            '%f',
            '%f',
            '%s',
        )
    );
}

add_action( 'woocommerce_checkout_update_order_meta', 'dokan_sync_insert_order' );
add_action( 'dokan_checkout_update_order_meta', 'dokan_sync_insert_order' );


/**
 * Get a seller ID based on WooCommerce order.
 *
 * If multiple post author is found, then this order contains products
 * from multiple sellers. In that case, the seller ID becomes `0`.
 *
 * @global object $wpdb
 * @param int $order_id
 * @return int
 */
function dokan_get_seller_id_by_order( $order_id ) {
    global $wpdb;

    $sql = "SELECT p.post_author AS seller_id
            FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            WHERE oim.meta_key = '_product_id' AND oi.order_id = %d GROUP BY p.post_author";

    $sellers = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

    if ( count( $sellers ) == 1 ) {
        return (int) reset( $sellers )->seller_id;
    }

    return 0;
}


/**
 * Get bootstrap label class based on order status
 *
 * @param string $status
 * @return string
 */
function dokan_get_order_status_class( $status ) {
    switch ($status) {
        case 'completed':
            return 'success';
            break;

        case 'pending':
            return 'danger';
            break;

        case 'on-hold':
            return 'warning';
            break;

        case 'processing':
            return 'info';
            break;

        case 'refunded':
            return 'default';
            break;

        case 'cancelled':
            return 'default';
            break;

        case 'failed':
            return 'danger';
            break;
    }
}
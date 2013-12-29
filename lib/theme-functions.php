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

    $cache_key = 'dokan-seller-orders-' . $seller_id;

    $orders = wp_cache_get( $cache_key, 'dokan' );

    if ( $orders === false ) {
        $sql = "SELECT oi.order_id, p.post_date FROM {$wpdb->prefix}woocommerce_order_items oi
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
                LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
                WHERE
                    oim.meta_key = '_product_id' AND
                    p.post_author = %d AND
                    p.post_status = 'publish'
                GROUP BY oi.order_id";

        $orders = $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );
        wp_cache_set( $cache_key, $orders, 'dokan' );
    }

    return $orders;
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

function dokan_count_posts( $post_type, $user_id ) {
    global $wpdb;

    $cache_key = 'dokan-count-' . $post_type . '-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( false === $counts ) {
        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d GROUP BY post_status";
        $results = $wpdb->get_results( $wpdb->prepare( $query, $post_type, $user_id ), ARRAY_A );
        $counts = array_fill_keys( get_post_stati(), 0 );

        $total = 0;
        foreach ( $results as $row ) {
            $counts[ $row['post_status'] ] = (int) $row['num_posts'];
            $total += (int) $row['num_posts'];
        }

        $counts['total'] = $total;
        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}

function dokan_count_comments( $post_type, $user_id ) {
    global $wpdb, $current_user;

    $cache_key = 'dokan-count-comments-' . $post_type . '-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( $counts === false ) {
        $query = "SELECT c.comment_approved, COUNT( * ) AS num_comments
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author = %d AND
                p.post_status = 'publish' AND
                c.comment_post_ID = p.ID AND
                p.post_type = %s
            GROUP BY c.comment_approved";

        $count = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $post_type ), ARRAY_A );

        $counts = array('moderated' => 0, 'approved' => 0, 'spam' => 0, 'trash' => 0, 'total' => 0);
        $statuses = array('0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed');
        $total = 0;
        foreach ($count as $row) {
            if ( isset( $statuses[$row['comment_approved']] ) ) {
                $counts[$statuses[$row['comment_approved']]] = (int) $row['num_comments'];
                $total += (int) $row['num_comments'];
            }
        }
        $counts['total'] = $total;

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}

function dokan_count_orders( $user_id ) {
    global $wpdb;

    $cache_key = 'dokan-count-orders-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( $counts === false ) {
        $counts = array('pending' => 0, 'completed' => 0, 'on-hold' => 0, 'processing' => 0, 'refunded' => 0, 'cancelled' => 0, 'total' => 0);
        $order_ids = dokan_get_seller_order_ids( $user_id );
        $order_ids = count( $order_ids ) ? implode( ', ', $order_ids ) : 0;

        $sql = "SELECT terms.slug
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->term_relationships} rel ON p.ID = rel.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} terms ON tax.term_id = terms.term_id
                WHERE p.post_type = 'shop_order' AND
                    tax.taxonomy = 'shop_order_status' AND
                    p.ID IN($order_ids)";

        $results = $wpdb->get_results( $sql );

        if ($results) {
            $total = 0;

            foreach ($results as $term_slug) {
                if ( isset( $counts[$term_slug->slug] ) ) {
                    $counts[$term_slug->slug] += 1;
                    $counts['total'] += 1;
                }
            }
        }

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}
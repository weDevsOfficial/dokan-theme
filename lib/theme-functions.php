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

function dokan_author_pageviews( $seller_id ) {
    global $wpdb;

    $cache_key = 'dokan-pageview-' . $seller_id;
    $pageview = wp_cache_get( $cache_key, 'dokan' );

    if ( $pageview === false ) {
        $sql = "SELECT SUM(meta_value) as pageview
            FROM {$wpdb->postmeta} AS meta
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = meta.post_id
            WHERE meta.meta_key = 'pageview' AND p.post_author = %d AND p.post_status IN ('publish', 'pending', 'draft')";

        $count = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $pageview = $count->pageview;

        wp_cache_set( $cache_key, $pageview, 'dokan' );
    }

    return $pageview;
}


function dokan_author_total_earning( $seller_id ) {
    global $wpdb;

    $cache_key = 'dokan-earning-' . $seller_id;
    $earnings = wp_cache_get( $cache_key, 'dokan' );

    if ( $earnings === false ) {
        $order_ids = dokan_get_seller_order_ids( $seller_id );
        $order_ids = count( $order_ids ) ? implode( ', ', $order_ids ) : 0;

        $sql = "SELECT SUM(oim.meta_value) as earnings
                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oim.order_item_id = oi.order_item_id
                LEFT JOIN {$wpdb->term_relationships} rel ON oi.order_id = rel.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} terms ON tax.term_id = terms.term_id
                WHERE oi.order_id IN ($order_ids) AND oim.meta_key = '_line_total' AND terms.slug IN ('completed', 'processing')";

        $count = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $earnings = $count->earnings;

        wp_cache_set( $cache_key, $earnings, 'dokan' );
    }

    return $earnings;
}

function dokan_generate_sync_table() {
    global $wpdb;

    $sql = "SELECT oi.order_id, p.ID as product_id, p.post_title, p.post_author as seller_id,
                oim2.meta_value as order_total, terms.name as order_status
            FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oim2.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            LEFT JOIN {$wpdb->term_relationships} rel ON oi.order_id = rel.object_id
            LEFT JOIN {$wpdb->term_taxonomy} tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
            LEFT JOIN {$wpdb->terms} terms ON tax.term_id = terms.term_id
            WHERE
                oim.meta_key = '_product_id' AND
                oim2.meta_key = '_line_total'
            GROUP BY oi.order_id";

    $orders = $wpdb->get_results( $sql );
    $table_name = $wpdb->prefix . 'dokan_orders';
    $percentage = dokan_get_seller_percentage();

    $wpdb->query( 'TRUNCATE TABLE ' . $table_name );

    if ( $orders ) {
        foreach ($orders as $order) {
            $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $order->order_id,
                    'seller_id' => $order->seller_id,
                    'order_total' => $order->order_total,
                    'net_amount' => ($order->order_total * $percentage)/100,
                    'order_status' => $order->order_status,
                ),
                array(
                    '%d',
                    '%d',
                    '%f',
                    '%f',
                    '%s',
                )
            );
        } // foreach
    } // if
}

function dokan_create_sync_table() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_orders` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` bigint(20) DEFAULT NULL,
      `seller_id` bigint(20) DEFAULT NULL,
      `order_total` float(11,2) DEFAULT NULL,
      `net_amount` float(11,2) DEFAULT NULL,
      `order_status` varchar(30) DEFAULT NULL,
      `status` tinyint(1) DEFAULT '1',
      PRIMARY KEY (`id`),
      KEY `order_id` (`order_id`),
      KEY `seller_id` (`seller_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $wpdb->query( $sql );
}

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
            $order = new WC_Order( $order_post );
            $order->update_status( $new_status );
        }
    }
}

add_action( 'woocommerce_order_status_changed', 'dokan_on_order_status_change', 10, 3 );

function dokan_sync_insert_order( $order_id ) {
    global $wpdb;

    $order = new WC_Order( $order_id );
    $percentage = dokan_get_seller_percentage();
    $order_total = $order->get_total();

    $wpdb->insert( $wpdb->prefix . 'dokan_orders',
        array(
            'order_id' => $order_id,
            'seller_id' => dokan_get_seller_id_by_order( $order_id ),
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

function dokan_sync_update_order_status( $order_id, $status = 1 ) {
    global $wpdb;

    $wpdb->update( $wpdb->prefix . 'dokan_orders',
        array( 'status' => $status ),
        array( 'order_id' => $order_id ),
        array( '%d' )
    );
}

function dokan_get_seller_percentage() {
    return 90;
}

function dokan_get_seller_id_by_order( $order_id ) {
    global $wpdb;

    $sql = "SELECT p.post_author AS seller_id
            FROM wp_woocommerce_order_items oi
            LEFT JOIN wp_woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN wp_posts p ON oim.meta_value = p.ID
            WHERE oim.meta_key = '_product_id' AND oi.order_id = %d GROUP BY p.post_author";

    $sellers = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

    if ( count( $sellers ) == 1 ) {
        return (int) reset( $sellers )->seller_id;
    }

    return 0;
}
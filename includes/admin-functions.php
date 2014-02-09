<?php

/**
 * Filter all the shop orders to remove child orders
 *
 * @param WP_Query $query
 */
function dokan_admin_shop_order_remove_parents( $query ) {
    if ( $query->is_main_query() && $query->query['post_type'] == 'shop_order' ) {
        $query->set( 'orderby', 'ID' );
        $query->set( 'order', 'DESC' );
    }
}

add_action( 'pre_get_posts', 'dokan_admin_shop_order_remove_parents' );

/**
 * Remove child orders from WC reports
 *
 * @param array $query
 * @return array
 */
function dokan_admin_order_reports_remove_parents( $query ) {

    $query['where'] .= ' AND posts.post_parent = 0';

    return $query;
}

add_filter( 'woocommerce_reports_get_order_report_query', 'dokan_admin_order_reports_remove_parents' );

/**
 * Change the columns shown in admin.
 */
function dokan_admin_shop_order_edit_columns( $existing_columns ) {
    $columns = array();

    $columns['cb']               = '<input type="checkbox" />';
    $columns['order_status']     = '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'dokan' ) . '">' . esc_attr__( 'Status', 'dokan' ) . '</span>';
    $columns['order_title']      = __( 'Order', 'dokan' );
    $columns['order_items']      = __( 'Purchased', 'dokan' );
    $columns['shipping_address'] = __( 'Ship to', 'dokan' );

    $columns['customer_message'] = '<span class="notes_head tips" data-tip="' . esc_attr__( 'Customer Message', 'dokan' ) . '">' . esc_attr__( 'Customer Message', 'dokan' ) . '</span>';
    $columns['order_notes']      = '<span class="order-notes_head tips" data-tip="' . esc_attr__( 'Order Notes', 'dokan' ) . '">' . esc_attr__( 'Order Notes', 'dokan' ) . '</span>';
    $columns['order_date']       = __( 'Date', 'dokan' );
    $columns['order_total']      = __( 'Total', 'dokan' );
    $columns['order_actions']    = __( 'Actions', 'dokan' );
    $columns['seller']        = __( 'Seller', 'dokan' );
    $columns['suborder']        = __( 'Sub Order', 'dokan' );

    return $columns;
}

add_filter( 'manage_edit-shop_order_columns', 'dokan_admin_shop_order_edit_columns', 11 );

/**
 * Adds custom column on dokan admin shop order table
 *
 * @global type $post
 * @global type $woocommerce
 * @global WC_Order $the_order
 * @param type $col
 */
function dokan_shop_order_custom_columns( $col ) {
    global $post, $woocommerce, $the_order;

    if ( empty( $the_order ) || $the_order->id != $post->ID ) {
        $the_order = new WC_Order( $post->ID );
    }

    switch ($col) {
        case 'order_title':
            if ($post->post_parent !== 0) {
                echo '<strong>';
                echo __( 'Sub Order of', 'dokan' );
                printf( ' <a href="%s">#%s</a>', admin_url( 'post.php?action=edit&post=' . $post->post_parent ), $post->post_parent );
                echo '</strong>';
            }
            break;

        case 'suborder':
            $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

            if ( $has_sub == '1' ) {
                printf( '<a href="#" class="show-sub-orders" data-class="parent-%1$d" data-show="%2$s" data-hide="%3$s">%2$s</a>', $post->ID, __( 'Show Sub-Orders', 'dokan' ), __( 'Hide Sub-Orders', 'dokan' ));
            }
            break;

        case 'seller':
            $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

            if ( $has_sub != '1' ) {
                $seller = get_user_by( 'id', $post->post_author );
                printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=shop_order&author=' . $seller->ID ), $seller->display_name );
            }

            break;
    }
}

add_action( 'manage_shop_order_posts_custom_column', 'dokan_shop_order_custom_columns', 11 );

/**
 * Adds css classes on admin shop order table
 *
 * @global WP_Post $post
 * @param array $classes
 * @param int $post_id
 * @return array
 */
function dokan_admin_shop_order_row_classes( $classes, $post_id ) {
    global $post;

    if ( $post->post_type == 'shop_order' && $post->post_parent != 0 ) {
        $classes[] = 'sub-order parent-' . $post->post_parent;
    }

    return $classes;
}

add_filter( 'post_class', 'dokan_admin_shop_order_row_classes', 10, 2);

/**
 * Show/hide sub order css/js
 *
 * @return void
 */
function dokan_admin_shop_order_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        $('tr.sub-order').hide();

        $('a.show-sub-orders').on('click', function(e) {
            e.preventDefault();

            var $self = $(this),
                el = $('tr.' + $self.data('class') );

            if ( el.is(':hidden') ) {
                el.show();
                $self.text( $self.data('hide') );
            } else {
                el.hide();
                $self.text( $self.data('show') );
            }
        });

        $('button.toggle-sub-orders').on('click', function(e) {
            e.preventDefault();

            $('tr.sub-order').toggle();
        });
    });
    </script>

    <style type="text/css">
        tr.sub-order {
            background: #ECFFF2;
        }
    </style>
    <?php
}

add_action( 'admin_footer-edit.php', 'dokan_admin_shop_order_scripts' );

/**
 * Delete sub orders when parent order is trashed
 *
 * @param int $post_id
 */
function dokan_admin_on_trash_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_trash_post( $order_post->ID );
            }
        }
    }
}

add_action( 'wp_trash_post', 'dokan_admin_on_trash_order' );

/**
 * Untrash sub orders when parent orders are untrashed
 *
 * @param int $post_id
 */
function dokan_admin_on_untrash_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_untrash_post( $order_post->ID );
            }
        }
    }
}

add_action( 'wp_untrash_post', 'dokan_admin_on_untrash_order' );


/**
 * Delete sub orders and from dokan sync table when a order is deleted
 *
 * @param int $post_id
 */
function dokan_admin_on_delete_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' ) {
        dokan_delete_sync_order( $post_id );

        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_delete_post( $order_post->ID );
            }
        }
    }
}

add_action( 'delete_post', 'dokan_admin_on_delete_order' );

/**
 * Show a toggle button to toggle all the sub orders
 *
 * @global WP_Query $wp_query
 */
function dokan_admin_shop_order_toggle_sub_orders() {
    global $wp_query;

    if ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'shop_order' ) {
        echo '<button class="toggle-sub-orders button">' . __( 'Toggle Sub-orders', 'dokan' ) . '</button>';
    }
}

add_action( 'restrict_manage_posts', 'dokan_admin_shop_order_toggle_sub_orders');
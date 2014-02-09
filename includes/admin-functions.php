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
    $columns['order_status']     = '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'woocommerce' ) . '">' . esc_attr__( 'Status', 'woocommerce' ) . '</span>';
    $columns['order_title']      = __( 'Order', 'woocommerce' );
    $columns['order_items']      = __( 'Purchased', 'woocommerce' );
    $columns['shipping_address'] = __( 'Ship to', 'woocommerce' );

    $columns['customer_message'] = '<span class="notes_head tips" data-tip="' . esc_attr__( 'Customer Message', 'woocommerce' ) . '">' . esc_attr__( 'Customer Message', 'woocommerce' ) . '</span>';
    $columns['order_notes']      = '<span class="order-notes_head tips" data-tip="' . esc_attr__( 'Order Notes', 'woocommerce' ) . '">' . esc_attr__( 'Order Notes', 'woocommerce' ) . '</span>';
    $columns['order_date']       = __( 'Date', 'woocommerce' );
    $columns['order_total']      = __( 'Total', 'woocommerce' );
    $columns['order_actions']    = __( 'Actions', 'woocommerce' );
    $columns['suborder']        = __( 'Sub Order', 'woocommerce' );

    return $columns;
}

add_filter( 'manage_edit-shop_order_columns', 'dokan_admin_shop_order_edit_columns', 11 );

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
    }
}

add_action( 'manage_shop_order_posts_custom_column', 'dokan_shop_order_custom_columns', 11 );

add_filter( 'post_class', function( $classes, $post_id ) {
    global $post;

    if ( $post->post_parent != 0 ) {
        $classes[] = 'sub-order parent-' . $post->post_parent;
    }

    return $classes;
}, 10, 2);

function dokan_admin_shop_order_scripts( $something ) {
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
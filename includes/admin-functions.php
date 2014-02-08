<?php

/**
 * Filter all the shop orders to remove child orders
 *
 * @param WP_Query $query
 */
function dokan_admin_shop_order_remove_parents( $query ) {
    if ( $query->is_main_query() && $query->query['post_type'] == 'shop_order' ) {
        $query->set( 'post_parent', 0 );
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
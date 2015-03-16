<?php

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * Renders item-bar of products in the loop
 *
 * @global WC_Product $product
 */
function dokan_product_loop_price() {
    global $product;
    ?>
    <span class="item-bar">

        <?php woocommerce_template_loop_price(); ?>

        <span class="item-button">
            <?php woocommerce_template_loop_add_to_cart(); ?>
            <?php if ( function_exists( 'dokan_add_to_wishlist_link' ) ) dokan_add_to_wishlist_link(); ?>
        </span>
    </span>
    <?php
}

add_action( 'woocommerce_after_shop_loop_item', 'dokan_product_loop_price' );


/**
 * Filters WC breadcrumb parameters
 *
 * @param type $args
 * @return type
 */
function dokan_woo_breadcrumb( $args ) {
    return array(
        'delimiter'   => '&nbsp; <i class="fa fa-angle-right"></i> &nbsp;',
        'wrap_before' => '<nav class="breadcrumb" ' . ( is_single() ? 'itemprop="breadcrumb"' : '' ) . '>',
        'wrap_after'  => '</nav>',
        'before'      => '<li>',
        'after'       => '</li>',
        'home'        => _x( 'Home', 'breadcrumb', 'dokan' ),
    );
}

add_filter( 'woocommerce_breadcrumb_defaults', 'dokan_woo_breadcrumb' );

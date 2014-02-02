<?php
/**
 * Loop Add to Cart
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

$icon_class = ($product->product_type == 'variable' ) ? 'fa-bars' : 'fa-shopping-cart';

echo apply_filters( 'woocommerce_loop_add_to_cart_link',
    sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="cat btn add_to_cart_button product_type_%s" title="%s">%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( $product->id ),
        esc_attr( $product->get_sku() ),
        esc_attr( $product->product_type ),
        esc_html( $product->add_to_cart_text() ),
        sprintf( '<i class="fa %s"></i>', $icon_class )
    ),
$product );
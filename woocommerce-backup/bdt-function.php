<?php

/*--------------------------------------------------
:: BDT Function - START
-------------------------------------------------- */

function sbw_get_show_price_type() {

    // return 'usd';
    // return 'bdt';
    return 'both';
}

function sbw_get_bdt() {
    return apply_filters( 'sbw_get_bdt', 78 );
}

function sbw_usd_to_bdt( $usd ) {
    return ceil( $usd * sbw_get_bdt() );
}

function sbw_bdt_wrap( $bdt, $bracket = true ) {
    if ( $bracket ) {
        $bdt = sprintf(' <span class="price-bdt">(%s টাকা)</span>', apply_filters( 'number_format_i18n', $bdt ) );
    } else {
        $bdt = sprintf(' <span class="price-bdt">%s টাকা</span>', apply_filters( 'number_format_i18n', $bdt ) );
    }

    return $bdt;
}


/*--------------------------------------------------
:: Woo Currency Converter
-------------------------------------------------- */

add_filter( 'wccc_preset_currency_list', function( $list ) {
    $list[] = 'BDT';

    return $list;
} );

add_filter( 'wccc_preset_currency', function( $currency, $code ) {

    if ( $code == 'BDT' ) {
        $currency = array(
            'code' => 'BDT',
            'label' => __( 'Bangladeshi Taka', 'wccc' ),
            'primary' => 0,
            'priority' => '',
            'symbol' => '&#2547;',
            'symbol_pos' => 'left_space',
            'thousands_sep' => 'comma',
            'decimal_sep' => 'dot',
            'decimal_places' => '',
            'round_precision' => '',
            'custom_rate' => ''
        );
    }

    return $currency;

}, 10 ,2 );


/*--------------------------------------------------
:: Template Functions
-------------------------------------------------- */

function sbw_woo_page_url( $page ) {
    if ( !function_exists( 'woocommerce_get_page_id' ) ) {
        return '#';
    }

    return get_permalink( woocommerce_get_page_id( $page ) );
}

function sbw_woo_account_url() {
    return sbw_woo_page_url( 'myaccount' );
}

function sbw_woo_cart_url() {
    return sbw_woo_page_url( 'cart' );
}

function sbw_woo_shop_url() {
    return sbw_woo_page_url( 'shop' );
}

function sbw_unhook_woo_single_product_content() {
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}

add_action( 'init', 'sbw_unhook_woo_single_product_content', 1 );

function sbw_woo_single_product_price_wrap_open() {
    echo '<div class="price-wrap">';
}

function sbw_woo_single_product_price_wrap_close() {
    echo '</div>';
}

add_action( 'woocommerce_single_product_summary', 'sbw_woo_single_product_price_wrap_open', 19);
add_action( 'woocommerce_single_product_summary', 'sbw_woo_single_product_price_wrap_close' , 31);

function sbw_cart_header() {
    global $woocommerce;

    if ( !$woocommerce ) {
        return;
    }

    $items = $woocommerce->cart->get_cart();
    ?>
    <a href="#" id="nav-cart-pop">
        <i class="icon-shopping-cart"></i>
        <?php printf( __( 'Cart &nbsp; <span class="label label-danger">%s <span class="caret"></span>&nbsp;</span>', 'wedevs' ), $woocommerce->cart->get_cart_total() ); ?>
    </a>

    <div class="cart-items">

        <?php if ( $items ) { ?>

            <table class="mini-product-list table-condensed">
                <?php
                foreach ( $items as $hash => $item ) {
                    $product = $item['data'];
                    ?>
                    <tr>
                        <td class="product-remove-link">
                            <?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( $woocommerce->cart->get_remove_url( $hash ) ), __( 'Remove this item', 'woocommerce' ) ), $hash ); ?>
                        </td>

                        <td class="product-thumb">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_in_cart_product_thumbnail', $product->get_image(), $item, $hash );
                            printf( '<a href="%s">%s</a>', esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product_id', $item['product_id'] ) ) ), $thumbnail );
                            ?>
                        </td>

                        <td class="product-name">
                            <?php
                            if ( $product->exists() && $item['quantity'] > 0 ) {

                                if ( !$product->is_visible() || ($product instanceof WC_Product_Variation && !$product->parent_is_visible()) ) {
                                    echo apply_filters( 'woocommerce_in_cart_product_title', $product->get_title(), $item, $hash );
                                } else {
                                    printf( '<a href="%s">%s</a>', esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product_id', $item['product_id'] ) ) ), apply_filters( 'woocommerce_in_cart_product_title', $product->get_title(), $item, $hash ) );
                                }

                            }
                            ?>
                        </td>

                        <td class="product-price">
                            <?php echo $item['quantity'] ?> x

                            <span class="price">
                                <?php
                                $product_price = get_option( 'woocommerce_display_cart_prices_excluding_tax' ) == 'yes' || $woocommerce->customer->is_vat_exempt() ? $product->get_price_excluding_tax() : $product->get_price();

                                echo apply_filters( 'woocommerce_cart_item_price_html', woocommerce_price( $product_price ), $item, $hash );
                                ?>
                            </span>

                        </td>
                        <!-- .product-details -->
                    </tr>
                    <?php } ?>
            </table>

            <div class="buttons clearfix">
                <a class="btn btn-info" href="<?php echo $woocommerce->cart->get_cart_url(); ?>"><?php _e( 'View Cart', 'dokan' ); ?></a>
                <a class="btn btn-warning" href="<?php echo $woocommerce->cart->get_checkout_url(); ?>"><?php _e( 'Checkout', 'dokan' ); ?></a>
            </div>
        <?php } else { ?>

            <?php _e( 'No items found in cart', 'dokan' ); ?>

        <?php } ?>
    </div> <!-- .cart-items -->
    <?php
}


/**
 * Output the WooCommerce Breadcrumb
 *
 * @access public
 * @return void
 */
function woocommerce_breadcrumb( $args = array() ) {

    $defaults = apply_filters( 'woocommerce_breadcrumb_defaults', array(
        'delimiter'   => '',
        'wrap_before' => '<ol class="breadcrumb" itemprop="breadcrumb"><i class="icon-double-angle-right"></i> &nbsp;',
        'wrap_after'  => '</ol>',
        'before'      => '<li>',
        'after'       => '</li>',
        'home'        => _x( 'Home', 'breadcrumb', 'woocommerce' ),
    ) );

    $args = wp_parse_args( $args, $defaults );

    woocommerce_get_template( 'shop/breadcrumb.php', $args );
}

function get_product_search_form() {
    ?>
    <form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
        <div class="input-group">
            <input type="text" class="form-control" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" id="s" placeholder="<?php esc_attr_e( 'Search products &hellip;', 'wedevs' ); ?>" />

            <span class="input-group-btn">
                <button class="btn btn-danger" id="searchsubmit" type="button"><?php esc_attr_e( 'Search', 'wedevs' ); ?></button>
                <input type="hidden" name="post_type" value="product" />
            </span>
        </div><!-- /input-group -->
    </form>
    <?php
}
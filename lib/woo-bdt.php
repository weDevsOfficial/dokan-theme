<?php

function sbw_woo_show_price( $usd, $bdt, $product = false ) {
    $type = sbw_get_show_price_type();

    switch ($type) {
        case 'usd':
            return $usd;

        case 'bdt':
            if ( $product && $product->is_on_sale() ) {
                $sale = get_post_meta( $product->id, '_bdt', true );
                $price = sprintf('<del>%s</del> <ins>%s</ins>', sbw_bdt_wrap( $bdt, false ), sbw_bdt_wrap( $sale, false ) );

                return $price;
            }

            return sbw_bdt_wrap( $bdt, false );

        case 'both':
            return $usd . sbw_bdt_wrap( $bdt );
    }
}

function sbw_woocommerce_currencies( $currencies ) {
    $currencies['BDT'] = __( 'Bangladeshi Taka', 'sbw' );

    return $currencies;
}

add_filter( 'woocommerce_currencies', 'sbw_woocommerce_currencies' );

function sbw_woocommerce_currency_symbol( $symbol, $currency ) {

    if ( $currency == 'BDT' ) {
        return '&#2547;';
    }

    return $symbol;
}

add_filter( 'woocommerce_currency_symbol', 'sbw_woocommerce_currency_symbol', 10, 2 );

function sbw_get_product_bdt( $product ) {

    if ( $product->is_on_sale() ) {

        $bdt = get_post_meta( $product->id, '_bdt_regular_price', true );
        if ( $bdt != '' ) {
            $bdt_price = $bdt;
        } else {
            $bdt_price = sbw_usd_to_bdt( $product->regular_price );
        }

    } else {
        $bdt = get_post_meta( $product->id, '_bdt', true );

        if ( $bdt != '' ) {
            $bdt_price = $bdt;
        } else {
            $bdt_price = sbw_usd_to_bdt( $product->get_price() );
        }
    }

    return $bdt_price;
}

function sbw_woo_bdt_price_html( $price, $product ) {
    $bdt = sbw_get_product_bdt( $product );
    $price = sbw_woo_show_price( $price, $bdt, $product );

    return $price;
}

add_filter( 'woocommerce_get_price_html', 'sbw_woo_bdt_price_html', 10, 2 );

add_filter( 'woocommerce_cart_item_price_html', function( $price, $product ) {
    $bdt = sbw_get_product_bdt( $product['data'] );
    $price = sbw_woo_show_price( $price, $bdt, $product['data'] );

    return $price;
}, 10, 2 );

function sbw_woo_cart_item_subtotal_bdt( $price, $product ) {

    $bdt = sbw_get_product_bdt( $product['data'] ) * $product['quantity'];
    $price = sbw_woo_show_price( $price, $bdt, $product['data'] );

    return $price;
}

add_filter( 'woocommerce_cart_item_subtotal', 'sbw_woo_cart_item_subtotal_bdt', 10, 2 );

function sbw_get_cart_subtotal() {
    global $woocommerce;

    $total = 0;
    $bdt = sbw_get_bdt();

    if ( sizeof($woocommerce->cart->cart_contents) > 0 ) {
        foreach ($woocommerce->cart->cart_contents as $cart_item_key => $values ) {
            $line_total = $values['quantity'] * sbw_get_product_bdt( $values['data'] );
            $total = $total + $line_total;
        }
    }

    return $total;
}


function sbw_woo_cart_subtotal( $subtotal, $compound, $product ) {
    $bdt = sbw_get_cart_subtotal();
    $subtotal = sbw_woo_show_price( $subtotal, $bdt );

    return $subtotal;
}

add_filter( 'woocommerce_cart_subtotal', 'sbw_woo_cart_subtotal', 10, 3 );

function sbw_woo_admin_bdt_attribute() {
    woocommerce_wp_text_input( array( 'id' => '_bdt', 'label' => 'Price in BDT', 'desc_tip' => 'true', 'description' => __( 'Give the price in BDT', 'woocommerce' ) ) );
    woocommerce_wp_text_input( array( 'id' => '_bdt_regular_price', 'label' => 'Sale Price in BDT', 'desc_tip' => 'true', 'description' => __( 'Give the sale price in BDT', 'woocommerce' ) ) );
}

add_action( 'woocommerce_product_options_sku', 'sbw_woo_admin_bdt_attribute' );

function sbw_save_product_data( $post_id ) {
    if ( isset( $_POST['_bdt'] ) ) {
        update_post_meta( $post_id, '_bdt', stripslashes( $_POST['_bdt'] ) );
    }

    if ( isset( $_POST['_bdt_regular_price'] ) ) {
        update_post_meta( $post_id, '_bdt_regular_price', stripslashes( $_POST['_bdt_regular_price'] ) );
    }
}

add_action( 'woocommerce_process_product_meta_simple', 'sbw_save_product_data' );

function sbw_get_cart_total() {
    global $woocommerce;

    $that = $woocommerce->cart;
    $total = sbw_get_cart_subtotal() + sbw_usd_to_bdt( $that->tax_total ) + sbw_usd_to_bdt( $that->shipping_tax_total ) + sbw_usd_to_bdt( $that->shipping_total ) - sbw_usd_to_bdt( $that->discount_total ) + sbw_usd_to_bdt( $that->fee_total );

    return $total;
}

function sbw_show_cart_total( $total ) {
    $bdt = sbw_get_cart_total();
    $total = sbw_woo_show_price( $total, $bdt );

    return $total;
}

add_filter( 'woocommerce_cart_total', 'sbw_show_cart_total' );

function sbw_woo_checkout_bdt_input() {
    $order_total = sbw_get_cart_total();

    printf( '<input type="hidden" name="_checkout_bdt" value="%s" />', esc_attr( $order_total ) );
}

add_action( 'woocommerce_checkout_order_review', 'sbw_woo_checkout_bdt_input' );

function sbw_woo_save_order_bdt( $order_id ) {
    $bdt = isset( $_POST['_checkout_bdt'] ) ? woocommerce_format_decimal( $_POST['_checkout_bdt'] ) : 0;

    update_post_meta( $order_id, '_bdt', $bdt );
}

add_action( 'woocommerce_checkout_order_processed', 'sbw_woo_save_order_bdt' );

function sbw_woo_order_bdt( $total, $order ) {
    $bdt_price = get_post_meta( $order->id, '_bdt', true );

    if ( $bdt_price != '' ) {
        $bdt_price = sbw_bdt_wrap( $bdt_price );
        $total = $total . $bdt_price;
    }

    return $total;
}

add_filter( 'woocommerce_get_formatted_order_total', 'sbw_woo_order_bdt', 10, 2 );

function sbw_woo_order_details_bdt( $order ) {
    $bdt_price = get_post_meta( $order->id, '_bdt', true );

    if ( $bdt_price != '' ) {
        $bdt_price = sbw_bdt_wrap( $bdt_price );
        ?>
        <p class="form-field form-field-wide">
            Price in BDT: <?php echo $bdt_price; ?>
        </p>
        <?php
    }
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'sbw_woo_order_details_bdt' );
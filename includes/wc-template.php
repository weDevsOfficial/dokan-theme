<?php

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

function dokan_product_seller_info( $item_data, $cart_item ) {
    $seller_info = get_userdata( $cart_item['data']->post->post_author );

    $item_data[] = array(
        'name' => __( 'Seller', 'dokan' ),
        'value' => $seller_info->display_name
    );

    return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'dokan_product_seller_info', 10, 2 );

function dokan_seller_product_tab( $tabs) {

    $tabs['seller'] = array(
        'title' => __( 'Seller Info', 'dokan' ),
        'priority' => 90,
        'callback' => 'dokan_product_seller_tab'
    );

    return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'dokan_seller_product_tab' );

function dokan_product_seller_tab( $val ) {
    global $product;

    $author = get_user_by( 'id', $product->post->post_author );
    ?>

    <?php _e( 'Seller:', 'dokan' ); ?> <?php printf( '<a href="%s">%s</a>', dokan_get_store_url( $author->ID ), $author->display_name ); ?>

    <?php
}

function dokan_product_loop_price() {
    global $product;
    ?>
    <span class="item-bar">

        <?php woocommerce_template_loop_price(); ?>

        <span class="item-button">
            <?php woocommerce_template_loop_add_to_cart(); ?>
            <a href="#" class="btn fav"><i class="fa fa-heart"></i></a>
        </span>
    </span>
    <?php
}

add_action( 'woocommerce_after_shop_loop_item', 'dokan_product_loop_price' );


function dokan_woo_breadcrumb( $args ) {
    return array(
        'delimiter'   => '&nbsp; <i class="fa fa-angle-right"></i> &nbsp;',
        'wrap_before' => '<nav class="breadcrumb" ' . ( is_single() ? 'itemprop="breadcrumb"' : '' ) . '>',
        'wrap_after'  => '</nav>',
        'before'      => '<li>',
        'after'       => '</li>',
        'home'        => _x( '<i class="fa fa-home"></i> Home', 'breadcrumb', 'dokan' ),
    );
}

add_filter( 'woocommerce_breadcrumb_defaults', 'dokan_woo_breadcrumb' );

function dokan_order_show_suborders( $parent_order ) {

    $sub_orders = get_children( array( 'post_parent' => $parent_order->id, 'post_type' => 'shop_order' ) );

    if ( !$sub_orders ) {
        return;
    }
    ?>
    <header>
        <h2><?php _e( 'Sub Orders', 'dokan' ); ?></h2>
    </header>

    <label class="label label-success">Note:</label>
    <div class="dokan-info">
        This order has products from multiple vendors/sellers. So we divided this order into multiple seller orders.
        Each order will be handled by their respective seller independently.
    </div>


    <table class="shop_table my_account_orders">

        <thead>
            <tr>
                <th class="order-number"><span class="nobr"><?php _e( 'Order', 'dokan' ); ?></span></th>
                <th class="order-date"><span class="nobr"><?php _e( 'Date', 'dokan' ); ?></span></th>
                <th class="order-status"><span class="nobr"><?php _e( 'Status', 'dokan' ); ?></span></th>
                <th class="order-total"><span class="nobr"><?php _e( 'Total', 'dokan' ); ?></span></th>
                <th class="order-actions">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($sub_orders as $order_post) {
            $order = new WC_Order( $order_post->ID );
            $status = get_term_by( 'slug', $order->status, 'shop_order_status' );
            $item_count = $order->get_item_count();
            ?>
                <tr class="order">
                    <td class="order-number">
                        <a href="<?php echo $order->get_view_order_url(); ?>">
                            <?php echo $order->get_order_number(); ?>
                        </a>
                    </td>
                    <td class="order-date">
                        <time datetime="<?php echo date('Y-m-d', strtotime( $order->order_date ) ); ?>" title="<?php echo esc_attr( strtotime( $order->order_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></time>
                    </td>
                    <td class="order-status" style="text-align:left; white-space:nowrap;">
                        <?php echo ucfirst( __( $status->name, 'dokan' ) ); ?>
                    </td>
                    <td class="order-total">
                        <?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); ?>
                    </td>
                    <td class="order-actions">
                        <?php
                            $actions = array();

                            if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'failed' ), $order ) ) )
                                $actions['pay'] = array(
                                    'url'  => $order->get_checkout_payment_url(),
                                    'name' => __( 'Pay', 'dokan' )
                                );

                            if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) )
                                $actions['cancel'] = array(
                                    'url'  => $order->get_cancel_order_url(),
                                    'name' => __( 'Cancel', 'dokan' )
                                );

                            $actions['view'] = array(
                                'url'  => $order->get_view_order_url(),
                                'name' => __( 'View', 'dokan' )
                            );

                            $actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

                            foreach( $actions as $key => $action ) {
                                echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
                            }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
}

add_action( 'woocommerce_order_details_after_order_table', 'dokan_order_show_suborders' );
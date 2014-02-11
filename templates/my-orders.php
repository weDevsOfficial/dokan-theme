<?php
/**
 * Template Name: My Orders
 */

get_header();
?>

<div id="primary" class="content-area col-md-12">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <div class="entry-content">
                <?php
                    global $woocommerce;

                    $customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
                        'numberposts' => -1,
                        'meta_key'    => '_customer_user',
                        'meta_value'  => get_current_user_id(),
                        'post_type'   => 'shop_order',
                        'post_status' => 'publish',
                        'post_parent' => 0
                    ) ) );

                    if ( $customer_orders ) : ?>

                        <h2><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', __( 'Recent Orders', 'dokan' ) ); ?></h2>

                        <table class="shop_table my_account_orders table table-striped">

                            <thead>
                                <tr>
                                    <th class="order-number"><span class="nobr"><?php _e( 'Order', 'dokan' ); ?></span></th>
                                    <th class="order-date"><span class="nobr"><?php _e( 'Date', 'dokan' ); ?></span></th>
                                    <th class="order-status"><span class="nobr"><?php _e( 'Status', 'dokan' ); ?></span></th>
                                    <th class="order-total"><span class="nobr"><?php _e( 'Total', 'dokan' ); ?></span></th>
                                    <th class="order-actions">&nbsp;</th>
                                </tr>
                            </thead>

                            <tbody><?php
                                foreach ( $customer_orders as $customer_order ) {
                                    $order = new WC_Order();

                                    $order->populate( $customer_order );

                                    $status     = get_term_by( 'slug', $order->status, 'shop_order_status' );
                                    $item_count = $order->get_item_count();

                                    ?><tr class="order">
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
                                            <?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'dokan' ), $order->get_formatted_order_total(), $item_count ); ?>
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
                                                        'url'  => $order->get_cancel_order_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ),
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
                                    </tr><?php
                                }
                            ?></tbody>

                        </table>

                    <?php else: ?>

                        <p class="dokan-info"><?php _e( 'No orders found!', 'dokan' ); ?></p>

                    <?php endif; ?>

                </div><!-- .entry-content -->

            </article><!-- #post-<?php the_ID(); ?> -->


        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
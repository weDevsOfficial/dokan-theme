<?php
/**
 * Template Name: Dashboard - Orders
 */

get_header();
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'order' ) ); ?>

<div id="primary" class="content-area col-md-10">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <?php $user_orders = dokan_get_seller_orders( get_current_user_id() ); ?>

                <h3>Orders</h3>

                <?php if ( $user_orders ) { ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Order</th>
                                <th>Billing</th>
                                <th>Shipping</th>
                                <th>Order Total</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($user_orders as $order) {
                                $the_order = new WC_Order( $order->order_id );
                                ?>
                                <tr>
                                    <td>
                                        <?php printf( '<mark class="%s tips" data-tip="%s">%s</mark>', sanitize_title( $the_order->status ), esc_html__( $the_order->status, 'woocommerce' ), esc_html__( $the_order->status, 'woocommerce' ) ); ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $the_order->user_id )
                                            $user_info = get_userdata( $the_order->user_id );

                                        if ( !empty( $user_info ) ) {

                                            $user = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

                                            if ( $user_info->first_name || $user_info->last_name )
                                                $user .= esc_html( $user_info->first_name . ' ' . $user_info->last_name );
                                            else
                                                $user .= esc_html( $user_info->display_name );

                                            $user .= '</a>';
                                        } else {
                                            $user = __( 'Guest', 'woocommerce' );
                                        }

                                        echo '<a href="' . admin_url( 'post.php?post=' . absint( $the_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $the_order->get_order_number() ) ) . '</strong></a> ' . __( 'made by', 'woocommerce' ) . ' ' . $user;

                                        if ( $the_order->billing_email )
                                            echo '<small class="meta">' . __( 'Email:', 'woocommerce' ) . ' ' . '<a href="' . esc_url( 'mailto:' . $the_order->billing_email ) . '">' . esc_html( $the_order->billing_email ) . '</a></small>';

                                        if ( $the_order->billing_phone )
                                            echo '<small class="meta">' . __( 'Tel:', 'woocommerce' ) . ' ' . esc_html( $the_order->billing_phone ) . '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $the_order->get_formatted_billing_address() )
                                            echo '<a target="_blank" href="' . esc_url( 'http://maps.google.com/maps?&q=' . urlencode( $the_order->get_billing_address() ) . '&z=16' ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $the_order->get_formatted_billing_address() ) ) . '</a>';
                                        else
                                            echo '&ndash;';

                                        if ( $the_order->payment_method_title )
                                            echo '<small class="meta">' . __( 'Via', 'woocommerce' ) . ' ' . esc_html( $the_order->payment_method_title ) . '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $the_order->get_formatted_shipping_address() )
                                            echo '<a target="_blank" href="' . esc_url( 'http://maps.google.com/maps?&q=' . urlencode( $the_order->get_shipping_address() ) . '&z=16' ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $the_order->get_formatted_shipping_address() ) ) . '</a>';
                                        else
                                            echo '&ndash;';

                                        if ( $the_order->shipping_method_title )
                                            echo '<small class="meta">' . __( 'Via', 'woocommerce' ) . ' ' . esc_html( $the_order->shipping_method_title ) . '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo esc_html( strip_tags( $the_order->get_formatted_order_total() ) ); ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                            $t_time = $h_time = __( 'Unpublished', 'woocommerce' );
                                        } else {
                                            $t_time = get_the_time( __( 'Y/m/d g:i:s A', 'woocommerce' ), $post );

                                            $gmt_time = strtotime( $post->post_date_gmt . ' UTC' );
                                            $time_diff = current_time( 'timestamp', 1 ) - $gmt_time;

                                            if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 )
                                                $h_time = sprintf( __( '%s ago', 'woocommerce' ), human_time_diff( $gmt_time, current_time( 'timestamp', 1 ) ) );
                                            else
                                                $h_time = get_the_time( __( 'Y/m/d', 'woocommerce' ), $post );
                                        }

                                        echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post ) ) . '</abbr>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        do_action( 'woocommerce_admin_order_actions_start', $the_order );

                                        $actions = array();

                                        if ( in_array( $the_order->status, array('pending', 'on-hold') ) )
                                            $actions['processing'] = array(
                                                'url' => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce-mark-order-processing&order_id=' . $the_order->id ), 'woocommerce-mark-order-processing' ),
                                                'name' => __( 'Processing', 'woocommerce' ),
                                                'action' => "processing"
                                            );

                                        if ( in_array( $the_order->status, array('pending', 'on-hold', 'processing') ) )
                                            $actions['complete'] = array(
                                                'url' => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce-mark-order-complete&order_id=' . $the_order->id ), 'woocommerce-mark-order-complete' ),
                                                'name' => __( 'Complete', 'woocommerce' ),
                                                'action' => "complete"
                                            );

                                        $actions['view'] = array(
                                            'url' => admin_url( 'post.php?post=' . $the_order->id . '&action=edit' ),
                                            'name' => __( 'View', 'woocommerce' ),
                                            'action' => "view"
                                        );

                                        $actions = apply_filters( 'woocommerce_admin_order_actions', $actions, $the_order );

                                        foreach ($actions as $action) {
                                            $image = ( isset( $action['image_url'] ) ) ? $action['image_url'] : $woocommerce->plugin_url() . '/assets/images/icons/' . $action['action'] . '.png';
                                            printf( '<a class="button tips" href="%s" data-tip="%s"><img src="%s" alt="%s" width="14" /></a>', esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $image ), esc_attr( $action['name'] ) );
                                        }

                                        do_action( 'woocommerce_admin_order_actions_end', $the_order );
                                        ?>
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>
                <?php } else { ?>

                    <?php _e( 'No orders found', 'dokan' ); ?>

                <?php } ?>

                <?php

                // if ( $query->max_num_pages > 1 ) {
                //     echo '<div class="pagination pagination-centered">';
                //     echo paginate_links( array(
                //         'current' => max( 1, get_query_var( 'paged' ) ),
                //         'total' => $query->max_num_pages,
                //         'base' => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) ),
                //         'type' => 'list',
                //         'prev_text' => __( '&laquo;' ),
                //         'next_text' => __( '&raquo;' )
                //     ) );
                //     echo '</div>';
                // }
                ?>
            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php get_footer(); ?>
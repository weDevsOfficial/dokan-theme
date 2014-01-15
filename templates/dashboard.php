<?php
/**
 * Template Name: Dashboard
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

get_header();

wp_enqueue_script( 'jquery-chart' );
wp_enqueue_script( 'jquery-flot' );

global $wp_locale;

$user_id = get_current_user_id();
$orders_counts = dokan_count_orders( $user_id );
$post_counts = dokan_count_posts( 'product', $user_id );
$comment_counts = dokan_count_comments( 'product', $user_id );
$pageviews = dokan_author_pageviews( $user_id );
$earning = dokan_author_total_earning( $user_id );

$products_url = dokan_get_page_url( 'products' );
$orders_url = dokan_get_page_url( 'orders' );
$reviews_url = dokan_get_page_url( 'reviews' );
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'dashboard' ) ); ?>

<div id="primary" class="content-area col-md-10">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-widget big-counter">
                            <ul class="list-inline">
                                <li>
                                    <div class="title"><?php _e( 'Pageview', 'dokan' ); ?></div>
                                    <div class="count"><?php echo (int) $pageviews; ?></div>
                                </li>
                                <li>
                                    <div class="title"><?php _e( 'Sales', 'dokan' ); ?></div>
                                    <div class="count"><?php echo ($orders_counts->completed + $orders_counts->processing + $orders_counts->{'on-hold'}); ?></div>
                                </li>
                                <li>
                                    <div class="title"><?php _e( 'Earned', 'dokan' ); ?></div>
                                    <div class="count"><?php echo woocommerce_price( $earning ); ?></div>
                                </li>
                            </ul>
                        </div> <!-- .big-counter -->

                        <div class="dashboard-widget orders">
                            <div class="widget-title"><i class="fa fa-shopping-cart"></i> Orders</div>

                            <?php
                            $order_data = array(
                                array( 'value' => $orders_counts->completed, 'color' => '#73a724'),
                                array( 'value' => $orders_counts->pending, 'color' => '#999'),
                                array( 'value' => $orders_counts->processing, 'color' => '#21759b'),
                                array( 'value' => $orders_counts->cancelled, 'color' => '#d54e21'),
                                array( 'value' => $orders_counts->refunded, 'color' => '#e6db55'),
                                array( 'value' => $orders_counts->{'on-hold'}, 'color' => '#f0ad4e'),
                            );
                            ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled list-count">
                                        <li>
                                            <a href="<?php echo $orders_url; ?>">
                                                <span class="title"><?php _e( 'Total', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->total; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'completed' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'Completed', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->completed; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'pending' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'Pending', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->pending; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'processing' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'Processing', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->processing; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'cancelled' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'Cancelled', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->cancelled; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'refunded' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'Refunded', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->refunded; ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo add_query_arg( array( 'order_status' => 'on-hold' ), $orders_url ); ?>">
                                                <span class="title"><?php _e( 'On hold', 'dokan' ); ?></span> <span class="count"><?php echo $orders_counts->{'on-hold'}; ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-6" style="text-align: center;">
                                    <canvas id="order-stats" width="175" height="175"></canvas>
                                </div>
                            </div>
                        </div> <!-- .orders -->

                        <div class="dashboard-widget reviews">
                            <div class="widget-title"><i class="fa fa-comments"></i> Reviews</div>

                            <ul class="list-unstyled list-count">
                                <li>
                                    <a href="<?php echo $reviews_url; ?>">
                                        <span class="title"><?php _e( 'All', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->total; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'comment_status' => 'hold' ), $reviews_url ); ?>">
                                        <span class="title"><?php _e( 'Pending', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->moderated; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'comment_status' => 'spam' ), $reviews_url ); ?>">
                                        <span class="title"><?php _e( 'Spam', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->spam; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'comment_status' => 'trash' ), $reviews_url ); ?>">
                                        <span class="title"><?php _e( 'Trash', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->trash; ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div> <!-- .reviews -->

                    </div> <!-- .col-md-6 -->

                    <div class="col-md-6">
                        <div class="dashboard-widget sells-graph">
                            <div class="widget-title"><i class="fa fa-credit-card"></i> <?php _e( 'Sales', 'dokan' ); ?></div>

                            <div id="placeholder" style="height: 350px;"></div>
                            <?php require_once dirname( __DIR__ ) . '/includes/reports.php'; ?>
                        </div> <!-- .sells-graph -->


                        <div class="dashboard-widget products">
                            <div class="widget-title">
                                <i class="icon-briefcase"></i> <?php _e( 'Products', 'dokan' ); ?>

                                <span class="pull-right">
                                    <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-success btn-sm">+ Add new product</a>
                                </span>
                            </div>

                            <ul class="list-unstyled list-count">
                                <li>
                                    <a href="<?php echo $products_url; ?>">
                                        <span class="title"><?php _e( 'Total', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->total; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $products_url ); ?>">
                                        <span class="title"><?php _e( 'Live', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->publish; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'post_status' => 'draft' ), $products_url ); ?>">
                                        <span class="title"><?php _e( 'Offline', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->draft; ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo add_query_arg( array( 'post_status' => 'pending' ), $products_url ); ?>">
                                        <span class="title"><?php _e( 'Pending Review', 'dokan' ); ?></span> <span class="count"><?php echo $post_counts->pending; ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div> <!-- .products -->

                    </div>
                </div>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<script type="text/javascript">
    jQuery(function($) {
        var order_stats = <?php echo json_encode( $order_data ); ?>;

        var order_data = jQuery.parseJSON( '<?php echo dokan_sales_overview_chart_data(); ?>' );

        var d = order_data.order_counts;
        var d2 = order_data.order_amounts;

        for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
        for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

        var ctx = $("#order-stats").get(0).getContext("2d");
        var poststats = new Chart(ctx).Doughnut(order_stats);

        var placeholder = jQuery("#placeholder");
        var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
            series: {
                lines: { show: true, fill: true },
                points: { show: true }
            },
            grid: {
                show: true,
                aboveData: false,
                color: '#ccc',
                backgroundColor: '#fff',
                borderWidth: 0,
                borderColor: '#ccc',
                clickable: false,
                hoverable: true,
                // markings: weekendAreas
            },
            xaxis: {
                mode: "time",
                timeformat: "%d %b",
                monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                tickLength: 1,
                minTickSize: [1, "day"]
            },
            yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
            colors: ["#8a4b75", "#47a03e"],
            legend: {
                show: true,
                position: "nw"
            }
        });

        placeholder.resize();
    })
</script>


<?php get_footer(); ?>
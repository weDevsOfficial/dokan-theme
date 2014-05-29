<?php
/**
 * Template Name: Dashboard - Orders
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

get_header();
dokan_frontend_dashboard_scripts();
?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'order' ) ); ?>

<div id="primary" class="content-area col-md-10 col-sm-9">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php if ( isset( $_GET['order_id'] ) ) { ?>
                    <a href="<?php the_permalink(); ?>" class="btn btn-default btn-sm"><?php _e( '&larr; Orders', 'dokan' ); ?></a>
                <?php } else {
                    dokan_order_listing_status_filter();
                } ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <?php
                $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;

                if ( $order_id ) {
                    get_template_part( 'templates/orders/order-details' );
                } else {
                    get_template_part( 'templates/orders/listing' );
                }
                ?>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php get_footer(); ?>
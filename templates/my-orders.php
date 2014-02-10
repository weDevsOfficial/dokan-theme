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

                <div class="entry-content">
                    <?php wc_get_template( 'myaccount/my-orders.php', array( 'order_count' => -1 ) ); ?>
                </div><!-- .entry-content -->

            </article><!-- #post-<?php the_ID(); ?> -->


        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
get_header();
?>

<?php get_sidebar( 'home' ); ?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content woocommerce" role="main">


        <h2 class="bordered">
            <span>Latest Products</span>
            <small> - <a href="<?php echo sbw_woo_shop_url(); ?>" class="label label-info">View All &rarr;</a></small>
            <div class="border"></div>
        </h2>

        <ul class="products">

        <?php if ( have_posts() ) : ?>

            <?php
            $the_query = new WP_Query( array(
                'post_type' => 'product',
                'posts_per_page' => 4
            ) );

            while ($the_query->have_posts()) : $the_query->the_post(); ?>

                <?php woocommerce_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; ?>

        <?php else : ?>

            <?php get_template_part( 'no-results', 'index' ); ?>

        <?php endif; ?>

        </ul> <!-- .row -->

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
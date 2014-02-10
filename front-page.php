<?php
/**
 * The main template file for homepage.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
get_header();
?>

<div id="primary" class="home-content-area col-md-9">
    <div id="content" class="site-content" role="main">

        <?php do_action( 'dokan_home_slider_top' ); ?>

        <div class="row">
            <div class="col-md-4">
                <?php dokan_category_widget(); ?>
            </div>

            <div class="col-md-8">
                <?php
                 if ( dokan_get_option( 'show_slider', 'dokan_home', 'on' ) == 'on' ) {
                    $slider_id = dokan_get_option( 'slider_id', 'dokan_home', '-1' );

                    if ( $slider_id != '-1' ) {
                        Dokan_Slider::init()->get_slider( $slider_id );
                    }

                    do_action( 'dokan_home_on_slider' );
                }
                ?>
            </div>
        </div> <!-- #home-page-section-1 -->

        <?php do_action( 'dokan_home_after_slider' ); ?>

        <?php if ( dokan_get_option( 'show_featured', 'dokan_home', 'on' ) == 'on' ) { ?>
            <div class="slider-container woocommerce">
                <h2 class="slider-heading"><?php _e( 'Featured Products', 'dokan' ); ?></h2>

                <div class="product-sliders">
                    <ul class="slides">
                        <?php
                        $featured_query = dokan_get_featured_products();
                        ?>
                        <?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); ?>

                            <?php wc_get_template_part( 'content', 'product' ); ?>

                        <?php endwhile; ?>
                    </ul>
                </div>
            </div> <!-- .slider-container -->
        <?php } ?>

        <?php do_action( 'dokan_home_after_featured' ); ?>

        <?php if ( dokan_get_option( 'show_latest', 'dokan_home', 'on' ) == 'on' ) { ?>
            <div class="slider-container woocommerce">
                <h2 class="slider-heading"><?php _e( 'Latest Products', 'dokan' ); ?></h2>

                <div class="product-sliders">
                    <ul class="slides">
                        <?php
                        $latest_query = new WP_Query( array(
                            'posts_per_page' => 8,
                            'post_type' => 'product'
                        ) );
                        ?>
                        <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>

                            <?php wc_get_template_part( 'content', 'product' ); ?>

                        <?php endwhile; ?>
                    </ul>
                </div>
            </div> <!-- .slider-container -->
        <?php } ?>

        <?php if ( dokan_get_option( 'show_best_selling', 'dokan_home', 'on' ) == 'on' ) { ?>
            <div class="slider-container woocommerce">
                <h2 class="slider-heading"><?php _e( 'Best Selling Products', 'dokan' ); ?></h2>

                <div class="product-sliders">
                    <ul class="slides">
                        <?php
                        $best_selling_query = dokan_get_best_selling_products();
                        ?>
                        <?php while ( $best_selling_query->have_posts() ) : $best_selling_query->the_post(); ?>

                            <?php wc_get_template_part( 'content', 'product' ); ?>

                        <?php endwhile; ?>
                    </ul>
                </div>
            </div> <!-- .slider-container -->
        <?php } ?>

        <?php if ( dokan_get_option( 'show_top_rated', 'dokan_home', 'on' ) == 'on' ) { ?>
            <div class="slider-container woocommerce">
                <h2 class="slider-heading"><?php _e( 'Top Rated Products', 'dokan' ); ?></h2>

                <div class="product-sliders">
                    <ul class="slides">
                        <?php
                        $top_rated_query = dokan_get_top_rated_products();
                        ?>
                        <?php while ( $top_rated_query->have_posts() ) : $top_rated_query->the_post(); ?>

                            <?php wc_get_template_part( 'content', 'product' ); ?>

                        <?php endwhile; ?>
                    </ul>
                </div>
            </div> <!-- .slider-container -->
        <?php } ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->


<?php get_sidebar( 'home' ); ?>
<?php get_footer(); ?>
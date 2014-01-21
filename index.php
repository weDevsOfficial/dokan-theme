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
<?php $url = get_template_directory_uri() . '/assets/images/footer'; ?>
<div id="primary" class="home-content-area col-md-9">
    <div id="content" class="site-content" role="main">

        <div class="row">
            <div class="col-md-4">
                <?php dokan_category_widget(); ?>
            </div>

            <div class="col-md-8">
                <?php Dokan_Slider::get_instance()->get_slider(384); ?>
            </div>
        </div> <!-- #home-page-section-1 -->

        <div id="home-page-section-2" class="row">

            <div id="home-page-container" class="col-md-12">

                <div class="featured-tab-area clearfix">
                    <ul id="dokan-feat-tab" class="nav nav-tabs">
                        <li class="active"><a href="#latest-products" data-toggle="tab"><?php _e( 'Latest', 'dokan' ); ?></a></li>
                        <li><a href="#featured-products" data-toggle="tab"><?php _e( 'Featured', 'dokan' ); ?></a></li>
                        <li><a href="#special-products" data-toggle="tab"><?php _e( 'Special', 'dokan' ); ?></a></li>
                    </ul>

                    <div class="tab-content woocommerce">
                        <div class="tab-pane fade in active" id="latest-products">
                            <ul class="products list-inline">
                                <?php
                                $latest_query = new WP_Query( array(
                                    'posts_per_page' => 8,
                                    'post_type' => 'product'
                                ) );
                                ?>
                                <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>

                                    <?php woocommerce_get_template_part( 'content', 'product' ); ?>

                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="tab-pane fade" id="featured-products">
                            <ul class="products list-inline">
                                <?php
                                $latest_query = new WP_Query( array(
                                    'posts_per_page' => 8,
                                    'post_type' => 'product'
                                ) );
                                ?>
                                <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>

                                    <?php woocommerce_get_template_part( 'content', 'product' ); ?>

                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="tab-pane fade" id="special-products">
                            <ul class="products list-inline">
                                <?php
                                $latest_query = new WP_Query( array(
                                    'posts_per_page' => 8,
                                    'post_type' => 'product'
                                ) );
                                ?>
                                <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>

                                    <?php woocommerce_get_template_part( 'content', 'product' ); ?>

                                <?php endwhile; ?>
                            </ul>
                        </div> <!-- .tab-pane -->
                    </div> <!-- .tab-content -->
                </div> <!-- .featured-tab-area -->

                <div class="best-sellers-area">
                    <h2>Best Sellers</h2>

                    <div class="best-sellers-sliders woocommerce clearfix">
                        <ul class="products">
                            <?php
                            $latest_query = new WP_Query( array(
                                'posts_per_page' => 8,
                                'post_type' => 'product'
                            ) );
                            ?>

                            <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>

                                <?php woocommerce_get_template_part( 'content', 'product' ); ?>

                            <?php endwhile; // end of the loop. ?>
                        </ul>
                    </div>
                </div> <!-- .best-sellers-area -->
            </div> <!-- #home-page-container -->
        </div>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<div id="secondary" class="col-md-3" role="complementary">
    <div class="widget-area">

    <?php dynamic_sidebar( 'sidebar-home' ); ?>

    <?php $url = get_template_directory_uri() . '/assets/images/footer/'; ?>
    <div id="super-deals">
        <h3 class="deal-title">Super Deals</h3>
        <ul class="deal-items">
            <li>
                <figure>
                    <img src="<?php echo $url; ?>button.jpg" alt="Sports">
                    <figcaption>
                        <div class="deal-status">60% Off</div>
                        <ul class="deal-time-bar">
                            <li class="deal-day">
                                <span class="d-time-title">Days</span>
                                <span class="d-time">01</span>
                            </li>
                            <li class="deal-hours">
                                <span class="d-time-title">Hours</span>
                                <span class="d-time d-h">01</span>
                            </li>
                            <li class="deal-minutes">
                                <span class="d-time-title">Minutes</span>
                                <span class="d-time d-m">01</span>
                            </li>
                        </ul>
                    </figcaption>
                </figure>
            </li>
            <li>
                <figure>
                    <img src="<?php echo $url; ?>button.jpg" alt="Sports">
                    <figcaption>
                        <div class="deal-status">60% Off</div>
                        <ul class="deal-time-bar">
                            <li class="deal-day">
                                <span class="d-time-title">Days</span>
                                <span class="d-time">01</span>
                            </li>
                            <li class="deal-hours">
                                <span class="d-time-title">Hours</span>
                                <span class="d-time d-h">01</span>
                            </li>
                            <li class="deal-minutes">
                                <span class="d-time-title">Minutes</span>
                                <span class="d-time d-m">01</span>
                            </li>
                        </ul>
                    </figcaption>
                </figure>
            </li>
            <li>
                <figure>
                    <img src="<?php echo $url; ?>button.jpg" alt="Sports">
                    <figcaption>
                        <div class="deal-status">60% Off</div>
                        <ul class="deal-time-bar">
                            <li class="deal-day">
                                <span class="d-time-title">Days</span>
                                <span class="d-time">01</span>
                            </li>
                            <li class="deal-hours">
                                <span class="d-time-title">Hours</span>
                                <span class="d-time d-h">01</span>
                            </li>
                            <li class="deal-minutes">
                                <span class="d-time-title">Minutes</span>
                                <span class="d-time d-m">01</span>
                            </li>
                        </ul>
                    </figcaption>
                </figure>
            </li>
        </ul>
    </div>


    </div>
</div>

<?php get_footer(); ?>
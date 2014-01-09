<?php
/**
 * The Template for displaying all single posts.
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */

$store_user = get_userdata( get_query_var( 'author' ) );
$scheme = is_ssl() ? 'https' : 'http';
wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );

get_header();
?>

<?php $url = get_template_directory_uri() . '/assets/images/footer'; ?>


<?php get_sidebar( 'store' ); ?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content store-page-wrap" role="main">

        <div class="profile-frame">

            <style type="text/css">
                .profile-frame {
                    background-image: url('<?php echo $url; ?>/dokan-store-banner.png');
                }
            </style>

            <div class="col-md-4 profile-info-box">
                <div class="profile-img">
                    <?php echo get_avatar( $store_user->ID ); ?>
                </div>

                <div class="profile-info">
                    <ul class="list-unstyled">
                        <li><strong>Store Name</strong></li>
                        <li><i class="fa fa-map-marker"></i> House #8 Road #13 Dhanmondi R/A, Dhaka - 1209</li>
                        <li><i class="fa fa-envelope-o"></i> store@dokan.me</li>
                    </ul>

                    <ul class="list-inline store-social">
                        <li><a href="#"><img src="<?php echo $url; ?>/f-facebook.png" alt=""></a></li>
                        <li><a href="#"><img src="<?php echo $url; ?>/f-gplus.png" alt=""></a></li>
                        <li><a href="#"><img src="<?php echo $url; ?>/f-twitter.png" alt=""></a></li>
                    </ul>
                </div> <!-- .profile-info -->
            </div> <!-- .profile-info-box -->
        </div> <!-- .profile-frame -->

        <?php if ( have_posts() ) { ?>

            <div class="seller-items woocommerce">

                <?php woocommerce_product_loop_start(); ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php wc_get_template_part( 'content', 'product' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php woocommerce_product_loop_end(); ?>

            </div>

            <?php wedevs_content_nav( 'nav-below' ); ?>

        <?php } ?>
    </div>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
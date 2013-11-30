<?php
/**
 * Template Name: Dashboard - Coupon
 */

require_once __DIR__ . '/../classes/coupons.php';

$dokan_template_coupons = Dokan_Template_coupons::init();
$dokan_template_coupons->coupons_create();


get_header();
?>


<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'coupon' ) ); ?>

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
                <?php $dokan_template_coupons->user_coupons(); ?>

                <?php $dokan_template_coupons->add_coupons_form(); ?>
                <p>
                   <!-- <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-large btn-info">Add New Coupon</a> -->
                </p>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>

<?php get_footer(); ?>
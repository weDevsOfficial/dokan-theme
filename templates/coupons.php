<?php
/**
 * Template Name: Dashboard - Coupon
 */

require_once __DIR__ . '/../classes/coupons.php';

$dokan_template_coupons = Dokan_Template_coupons::init();
if( is_user_logged_in() ) {
    $dokan_template_coupons->coupons_create();
    $dokan_template_coupons->coupun_delete();
}


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
    
                <p>
                   <a href="<?php echo add_query_arg( array( 'view' => 'add_coupons'), get_permalink() ); ?>" class="btn btn-large btn-info"><?php _e('Add New Coupon','dokan'); ?></a>
                </p>

                <?php $dokan_template_coupons->user_coupons(); ?>

                <?php $dokan_template_coupons->add_coupons_form(); ?>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>

<?php get_footer(); ?>
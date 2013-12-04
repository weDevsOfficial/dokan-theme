<?php
/**
 * Template Name: Dashboard - Coupon
 */


$dokan_template_coupons = Dokan_Template_Coupons::init();

$validated = $dokan_template_coupons->validate();

if ( !is_wp_error( $validated ) ) {
    $dokan_template_coupons->coupons_create();
}

$dokan_template_coupons->coupun_delete();



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

                <?php
                if ( is_wp_error( $validated )) {
                    $messages = $validated->get_error_messages();

                    foreach ($messages as $message) {
                        ?>
                        <div class="alert alert-danger" style="width: 40%; margin-left: 25%;">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong><?php _e( $message,'dokan'); ?></strong>
                        </div>
                        <?php
                    }
                }
                ?>
                <?php $dokan_template_coupons->add_coupons_form($validated); ?>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>

<?php get_footer(); ?>
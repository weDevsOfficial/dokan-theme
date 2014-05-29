<?php
/**
 * Template Name: Dashboard - Coupon
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();


$dokan_template_coupons = Dokan_Template_Coupons::init();

$validated = $dokan_template_coupons->validate();

if ( !is_wp_error( $validated ) ) {
    $dokan_template_coupons->coupons_create();
}

$dokan_template_coupons->coupun_delete();
$is_edit_page = isset( $_GET['view'] ) && $_GET['view'] == 'add_coupons';

get_header();
dokan_frontend_dashboard_scripts();
?>


<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'coupon' ) ); ?>

<div id="primary" class="content-area col-md-10 col-sm-9">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <div class="row">
                        <span class="col-md-9">
                            <h1 class="entry-title">
                            <?php the_title(); ?>

                            <?php if ( $is_edit_page ) {
                                printf( '<small> - %s</small>', __( 'Edit Coupon', 'dokan' ) );
                            } ?>
                            </h1>
                        </span>

                        <?php if ( !$is_edit_page ) { ?>
                            <span class="col-md-3">
                                <a href="<?php echo add_query_arg( array( 'view' => 'add_coupons'), get_permalink() ); ?>" class="btn btn-large btn-theme pull-right"><i class="fa fa-gift">&nbsp;</i> <?php _e( 'Add new Coupon', 'dokan' ); ?></a>
                            </span>
                        <?php } ?>
                    </div>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <?php
                if ( !dokan_is_seller_enabled( get_current_user_id() ) ) {
                    dokan_seller_not_enabled_notice();
                } else {
                    ?>

                    <?php $dokan_template_coupons->list_user_coupons(); ?>

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

                <?php } ?>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>

<?php get_footer(); ?>
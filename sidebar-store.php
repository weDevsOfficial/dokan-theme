<?php
$store_user   = get_userdata( get_query_var( 'author' ) );
$store_info   = get_user_meta( $store_user->ID, 'dokan_profile_settings', true );
$map_location = isset( $store_info['location'] ) ? esc_attr( $store_info['location'] ) : '';
?>

<div id="secondary" class="col-md-3 clearfix" role="complementary">
    <button type="button" class="navbar-toggle widget-area-toggle" data-toggle="collapse" data-target=".widget-area">
        <i class="fa fa-bars"></i>
        <span class="bar-title"><?php _e( 'Toggle Sidebar', 'dokan-theme' ); ?></span>
    </button>

    <div class="widget-area collapse widget-collapse">
       <?php do_action( 'dokan_sidebar_store_before', $store_user, $store_info ); ?>
        <?php
        if ( ! dynamic_sidebar( 'sidebar-store' ) ) {

            $args = array(
                'before_widget' => '<aside class="widget">',
                'after_widget'  => '</aside>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            );

            if ( class_exists( 'Dokan_Store_Location' ) ) {
                the_widget( 'Dokan_Store_Category_Menu', array( 'title' => __( 'Store Category', 'dokan-theme' ) ), $args );
                if( dokan_get_option( 'store_map', 'dokan_general', 'on' ) == 'on' ) {
                    the_widget( 'Dokan_Store_Location', array( 'title' => __( 'Store Location', 'dokan-theme' ) ), $args );
                }
                if( dokan_get_option( 'contact_seller', 'dokan_general', 'on' ) == 'on' ) {
                    the_widget( 'Dokan_Store_Contact_Form', array( 'title' => __( 'Contact Seller', 'dokan-theme' ) ), $args );
                }
            }

        }
        ?>

        <?php do_action( 'dokan_sidebar_store_after', $store_user, $store_info ); ?>
    </div>
</div><!-- #secondary .widget-area -->
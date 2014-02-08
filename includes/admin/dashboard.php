<?php
//wp_widget_rss_output
function dokan_admin_dash_metabox( $title = '', $callback = null ) {
    ?>
    <div class="postbox">
        <h3 class="hndle"><span><?php echo esc_html( $title ); ?></span></h3>
        <div class="inside">
            <div class="main">
                <?php if ( is_callable( $callback ) ) {
                    call_user_func( $callback );
                } ?>
            </div> <!-- .main -->
        </div> <!-- .inside -->
    </div> <!-- .postbox -->
    <?php
}

function dokan_admin_dash_widget_news() {
    wp_widget_rss_output( 'http://wedevs.com/tag/dokan/feed/', array( 'items' => 8, 'show_summary' => true, 'show_date' => true ) );
}

function dokan_admin_dash_metabox_glance() {
    $user_count = count_users();
    $withdraw_counts = dokan_get_withdraw_count();
    $seller_count = isset( $user_count['avail_roles']['seller'] ) ? $user_count['avail_roles']['seller'] : 0;
    ?>

    <ul class="main">
        <li class="seller-count">
            <a href="<?php echo admin_url( 'admin.php?page=dokan-sellers' ); ?>"><?php printf( __( '%d Sellers', 'dokan' ), $seller_count ); ?></a>
        </li>
        <li class="withdraw-pending">
            <a href="<?php echo admin_url( 'admin.php?page=dokan-withdraw' ); ?>"><?php printf( __( '%d Pending Withdraw', 'dokan' ), $withdraw_counts['pending'] ); ?></a>
        </li>
        <li class="withdraw-completed">
            <a href="<?php echo admin_url( 'admin.php?page=dokan-withdraw&amp;status=completed' ); ?>"><?php printf( __( '%d Completed Withdraw', 'dokan' ), $withdraw_counts['completed'] ); ?></a>
        </li>
        <li class="withdraw-pending">
            <a href="<?php echo admin_url( 'admin.php?page=dokan-withdraw&amp;status=cancelled' ); ?>"><?php printf( __( '%d Cancelled Withdraw', 'dokan' ), $withdraw_counts['cancelled'] ); ?></a>
        </li>
    </ul>

    <?php
}

?>
<div class="wrap dokan-dashboard">

    <h2><?php _e( 'Dokan Dashboard', 'dokan' ); ?></h2>

    <div class="metabox-holder">
        <div class="post-box-container">
            <div class="meta-box-sortables">
                <?php dokan_admin_dash_metabox( __( 'At a Glance', 'dokan' ), 'dokan_admin_dash_metabox_glance' ); ?>

                <?php do_action( 'dokan_admin_dashboard_metabox_left' ); ?>
            </div>
        </div> <!-- .post-box-container -->

        <div class="post-box-container">
            <div class="meta-box-sortables">
                <?php dokan_admin_dash_metabox( __( 'Dokan News Updates', 'dokan' ), 'dokan_admin_dash_widget_news'); ?>

                <?php do_action( 'dokan_admin_dashboard_metabox_right' ); ?>
            </div>
        </div> <!-- .post-box-container -->

    </div> <!-- .metabox-holder -->

</div> <!-- .wrap -->
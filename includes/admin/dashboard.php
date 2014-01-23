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
    $seller_count = isset( $user_count['avail_roles']['seller'] ) ? $user_count['avail_roles']['seller'] : 0;

    var_dump($seller_count);
}

?>
<div class="wrap dokan-dashboard">

    <h2><?php _e( 'Dokan Dashboard', 'dokan' ); ?></h2>

    <div class="metabox-holder">
        <div class="post-box-container">
            <div class="meta-box-sortables">
                <?php dokan_admin_dash_metabox( __( 'At a Glance', 'dokan' ), 'dokan_admin_dash_metabox_glance' ); ?>
            </div>
        </div> <!-- .post-box-container -->

        <div class="post-box-container">
            <div class="meta-box-sortables">
                <?php dokan_admin_dash_metabox( __( 'Dokan News Updates', 'dokan' ), 'dokan_admin_dash_widget_news'); ?>
            </div>
        </div> <!-- .post-box-container -->

    </div> <!-- .metabox-holder -->

</div> <!-- .wrap -->
<?php
$store_user = get_userdata( get_query_var( 'author' ) );
$store_info = get_user_meta( $store_user->ID, 'dokan_profile_settings', true );
$map_location = isset( $store_info['location'] ) ? esc_attr( $store_info['location'] ) : '';
?>

<div id="secondary" class="col-md-3 clearfix" role="complementary">
    <button type="button" class="navbar-toggle widget-area-toggle" data-toggle="collapse" data-target=".widget-area">
        <i class="fa fa-bars"></i>
        <span class="bar-title"><?php _e( 'Toggle Sidebar', 'dokan' ); ?></span>
    </button>

    <div class="widget-area collapse widget-collapse">

        <?php dokan_store_category_menu( $store_user->ID ); ?>

        <?php do_action( 'dokan_sidebar_store_before', $store_user, $store_info ); ?>

        <?php
        $show_map   = dokan_get_option( 'store_map', 'dokan_general', 'on' );
        if ( $show_map == 'on' ) { ?>
            <?php if ( !empty( $map_location ) ) { ?>
            <aside class="widget store-location">
                <h3 class="widget-title"><?php _e( 'Store Location', 'dokan' ); ?></h3>

                <div class="location-container">
                    <div id="dokan-store-location"></div>

                    <script type="text/javascript">
                        jQuery(function($) {
                            <?php
                            $locations = explode( ',', $map_location );
                            $def_lat = isset( $locations[0] ) ? $locations[0] : 90.40714300000002;
                            $def_long = isset( $locations[1] ) ? $locations[1] : 23.709921;
                            ?>

                            var def_longval = <?php echo $def_long; ?>;
                            var def_latval = <?php echo $def_lat; ?>;

                            var curpoint = new google.maps.LatLng(def_latval, def_longval),
                                $map_area = $('#dokan-store-location');

                            var gmap = new google.maps.Map( $map_area[0], {
                                center: curpoint,
                                zoom: 15,
                                mapTypeId: window.google.maps.MapTypeId.ROADMAP
                            });

                            var marker = new window.google.maps.Marker({
                                position: curpoint,
                                map: gmap
                            });
                        })

                    </script>
                </div>
            </aside>
            <?php } ?>
        <?php } ?>

        <?php
        $show_form   = dokan_get_option( 'contact_seller', 'dokan_general', 'on' );
        if ( $show_form == 'on' ) {
            ?>
            <aside class="widget store-contact">
                <h3 class="widget-title"><?php _e( 'Contact Seller', 'dokan' ); ?></h3>

                <form id="dokan-form-contact-seller" action="" method="post" class="seller-form clearfix">
                    <div class="ajax-response"></div>
                    <ul>
                        <li class="form-group">
                            <input type="text" name="name" value="" placeholder="<?php esc_attr_e( 'Your Name', 'dokan' ); ?>" class="form-control" minlength="5" required="required">
                        </li>
                        <li class="form-group">
                            <input type="email" name="email" value="" placeholder="<?php esc_attr_e( 'you@example.com', 'dokan' ); ?>" class="form-control" required="required">
                        </li>
                        <li class="form-group">
                            <textarea  name="message" maxlength="1000" cols="25" rows="6" value="" placeholder="<?php esc_attr_e( 'Type your messsage...', 'dokan' ); ?>" class="form-control" required="required"></textarea>
                        </li>
                    </ul>

                    <?php wp_nonce_field( 'dokan_contact_seller' ); ?>
                    <input type="hidden" name="seller_id" value="<?php echo $store_user->ID; ?>">
                    <input type="hidden" name="action" value="dokan_contact_seller">
                    <input type="submit" name="store_message_send" value="<?php esc_attr_e( 'Send Message', 'dokan' ); ?>" class="pull-right btn btn-theme">
                </form>
            </aside>
        <?php } ?>

        <?php dynamic_sidebar( 'sidebar-store' ); ?>

        <?php do_action( 'dokan_sidebar_store_after', $store_user, $store_info ); ?>
    </div>
</div><!-- #secondary .widget-area -->
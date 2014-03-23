<?php
/**
 * Dokan settings Class
 *
 * @ weDves
 */


class Dokan_Template_Settings{

    public static function init() {
        static $instance = false;

        if( !$instance ) {
            $instance = new Dokan_Template_Settings();
        }

        return $instance;
    }

    function ajax_settings() {

        if( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_settings_nonce' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $_POST['dokan_update_profile'] = '';

        $ajax_validate =  $this->validate();

        if( is_wp_error( $ajax_validate ) ) {
            wp_send_json_error( $ajax_validate->errors );
        }

        // we are good to go
        $save_data = $this->insert_settings_info();

        wp_send_json_success( __( 'Your information has been saved successfully', 'dokan' ) );
    }


    function validate() {

        if( !isset( $_POST['dokan_update_profile'] ) ) {
            return false;
        }

        if( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_settings_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $error = new WP_Error();

        $dokan_name = sanitize_text_field( $_POST['dokan_store_name'] );

        if ( empty( $dokan_name ) ) {
            $error->add('dokan_name', __('Dokan name required', 'dokan' ));
        }

        if ( isset($_POST['setting_category']) ) {

            if ( !is_array( $_POST['setting_category'] ) || !count($_POST['setting_category']) ) {
                $error->add('dokan_type', __('Dokan type required', 'dokan' ));
            }
        }

        if( !empty( $_POST['setting_paypal_email'] ) ) {
            $email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );
            if( empty( $email ) ) {
                $error->add('dokan_email', __('Invalid email', 'dokan' ) );
            }
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;

    }

    function insert_settings_info() {


        $social = $_POST['settings']['social'];

        $dokan_settings = array(
            'store_name'      => $_POST['dokan_store_name'],
            'social' => array(
                'fb' => filter_var( $social['fb'], FILTER_VALIDATE_URL ),
                'gplus' => filter_var( $social['gplus'], FILTER_VALIDATE_URL ),
                'twitter' => filter_var( $social['twitter'], FILTER_VALIDATE_URL ),
                'linkedin' => filter_var( $social['linkedin'], FILTER_VALIDATE_URL ),
                'youtube' => filter_var( $social['youtube'], FILTER_VALIDATE_URL ),
            ),
            'payment' => array(),
            'phone' => $_POST['setting_phone'],
            'show_email' => $_POST['setting_show_email'],
            'address' => $_POST['setting_address'],
            'location' => $_POST['location'],
            'find_address' => $_POST['find_address'],
            'banner' => $_POST['dokan_banner'],
            'gravatar' => $_POST['dokan_gravatar'],
        );

        if ( isset( $_POST['settings']['bank'] ) ) {
            $bank = $_POST['settings']['bank'];

            $dokan_settings['payment']['bank'] = array(
                'ac_name' => sanitize_text_field( $bank['ac_name'] ),
                'ac_number' => sanitize_text_field( $bank['ac_number'] ),
                'bank_name' => sanitize_text_field( $bank['bank_name'] ),
                'bank_addr' => sanitize_text_field( $bank['bank_addr'] ),
                'swift' => sanitize_text_field( $bank['swift'] ),
            );
        }

        if ( isset( $_POST['settings']['paypal'] ) ) {
            $dokan_settings['payment']['paypal'] = array(
                'email' => filter_var( $_POST['settings']['paypal']['email'], FILTER_VALIDATE_EMAIL )
            );
        }

        if ( isset( $_POST['settings']['skrill'] ) ) {
            $dokan_settings['payment']['skrill'] = array(
                'email' => filter_var( $_POST['settings']['skrill']['email'], FILTER_VALIDATE_EMAIL )
            );
        }

        $store_id = get_current_user_id();
        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        do_action( 'dokan_store_profile_saved', $store_id, $dokan_settings );

        if ( !defined('DOING_AJAX') && DOING_AJAX !== true ) {
            wp_redirect( add_query_arg( array( 'message' => 'profile_saved' ), get_permalink() ) );
        }
    }

    function setting_field( $validate = '' ) {
        global $current_user;

        if ( isset($_GET['message'])) {
            ?>
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?php _e('Your profile has been updated successfully!','dokan'); ?></strong>
            </div>
            <?php
        }

        $profile_info = dokan_get_store_info( $current_user->ID );

        $banner = isset( $profile_info['banner'] ) ? absint( $profile_info['banner'] ) : 0;
        $storename = isset( $profile_info['store_name'] ) ? esc_attr( $profile_info['store_name'] ) : '';
        $gravatar = isset( $profile_info['gravatar'] ) ? absint( $profile_info['gravatar'] ) : 0;

        $fb = isset( $profile_info['social']['fb'] ) ? esc_url( $profile_info['social']['fb'] ) : '';
        $twitter = isset( $profile_info['social']['twitter'] ) ? esc_url( $profile_info['social']['twitter'] ) : '';
        $gplus = isset( $profile_info['social']['gplus'] ) ? esc_url ( $profile_info['social']['gplus'] ) : '';
        $linkedin = isset( $profile_info['social']['linkedin'] ) ? esc_url( $profile_info['social']['linkedin'] ) : '';
        $youtube = isset( $profile_info['social']['youtube'] ) ? esc_url( $profile_info['social']['youtube'] ) : '';

        // bank
        $phone = isset( $profile_info['phone'] ) ? esc_attr( $profile_info['phone'] ) : '';
        $show_email = isset( $profile_info['show_email'] ) ? esc_attr( $profile_info['show_email'] ) : 'no';
        $address = isset( $profile_info['address'] ) ? esc_textarea( $profile_info['address'] ) : '';
        $map_location = isset( $profile_info['location'] ) ? esc_attr( $profile_info['location'] ) : '';
        $map_address = isset( $profile_info['find_address'] ) ? esc_attr( $profile_info['find_address'] ) : '';
        $dokan_category = isset( $profile_info['dokan_category'] ) ? $profile_info['dokan_category'] : '';


        if ( is_wp_error( $validate) ) {
            $social = $_POST['settings']['social'];

            $storename = $_POST['dokan_store_name'];

            $fb = esc_url( $social['fb'] );
            $twitter = esc_url( $social['twitter'] );
            $gplus = esc_url( $social['gplus'] );
            $linkedin = esc_url( $social['linkedin'] );
            $youtube = esc_url( $social['youtube'] );

            $phone = $_POST['setting_phone'];
            $address = $_POST['setting_address'];
            $map_location = $_POST['location'];
            $map_address = $_POST['find_address'];
        }
        ?>

            <div class="dokan-ajax-response"></div>

            <form method="post" id="settings-form"  action="" class="form-horizontal">

                <?php wp_nonce_field( 'dokan_settings_nonce' ); ?>

                <div class="dokan-banner">

                    <div class="image-wrap<?php echo $banner ? '' : ' dokan-hide'; ?>">
                        <?php $banner_url = $banner ? wp_get_attachment_url( $banner ) : ''; ?>
                        <input type="hidden" class="dokan-file-field" value="<?php echo $banner; ?>" name="dokan_banner">
                        <img class="dokan-banner-img" src="<?php echo esc_url( $banner_url ); ?>">

                        <a class="close dokan-remove-banner-image">&times;</a>
                    </div>

                    <div class="button-area<?php echo $banner ? ' dokan-hide' : ''; ?>">
                        <i class="fa fa-cloud-upload"></i>

                        <a href="#" class="dokan-banner-drag btn btn-info"><?php _e( 'Upload banner', 'dokan' ); ?></a>
                        <p class="help-block"><?php _e( '(Upload a banner for your store. Banner size is (825x300) pixel. )', 'dokan' ); ?></p>
                    </div>
                </div> <!-- .dokan-banner -->


                <div class="form-group">
                    <label class="col-md-3 control-label" for="dokan_store_name"><?php _e( 'Store Name', 'dokan' ); ?></label>

                    <div class="col-md-5">
                        <input id="dokan_store_name" required value="<?php echo $storename; ?>" name="dokan_store_name" placeholder="store name" class="form-control input-md" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="dokan_gravatar"><?php _e( 'Profile Picture', 'dokan' ); ?></label>

                    <div class="col-md-5 dokan-gravatar">
                        <div class="pull-left gravatar-wrap<?php echo $gravatar ? '' : ' dokan-hide'; ?>">
                            <?php $gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : ''; ?>
                            <input type="hidden" class="dokan-file-field" value="<?php echo $gravatar; ?>" name="dokan_gravatar">
                            <img class="dokan-gravatar-img" src="<?php echo esc_url( $gravatar_url ); ?>">
                            <a class="close dokan-remove-gravatar-image">&times;</a>
                        </div>
                        <div class="gravatar-button-area<?php echo $gravatar ? ' dokan-hide' : ''; ?>">
                            <i class="fa fa-cloud-upload"></i>

                            <a href="#" class="dokan-gravatar-drag btn btn-info"><?php _e( 'Upload Photo', 'dokan' ); ?></a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="settings[social][fb]"><?php _e( 'Social Profile', 'dokan' ); ?></label>

                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-facebook-square"></i></span>
                            <input id="settings[social][fb]" value="<?php echo $fb; ?>" name="settings[social][fb]" class="form-control" placeholder="http://" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="settings[social][plus]"></label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-google-plus"></i></span>
                            <input id="settings[social][gplus]" value="<?php echo $gplus; ?>" name="settings[social][gplus]" class="form-control" placeholder="http://" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="settings[social][twitter]"></label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-twitter"></i></span>
                            <input id="settings[social][twitter]" value="<?php echo $twitter; ?>" name="settings[social][twitter]" class="form-control" placeholder="http://" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="settings[social][linkedin]"></label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-linkedin"></i></span>
                            <input id="settings[social][linkedin]" value="<?php echo $linkedin; ?>" name="settings[social][linkedin]" class="form-control" placeholder="http://" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="settings[social][youtube]"></label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-youtube"></i></span>
                            <input id="settings[social][youtube]" value="<?php echo $youtube; ?>" name="settings[social][youtube]" class="form-control" placeholder="http://" type="text">
                        </div>
                    </div>
                </div>

                <!-- payment tab -->
                <div class="form-group">
                    <label class="col-md-3 control-label" for="dokan_setting"><?php _e( 'Payment Method', 'dokan' ); ?></label>
                    <div class="col-md-6">

                        <?php $methods = dokan_withdraw_get_active_methods(); ?>

                        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                            <?php
                            $count = 0;
                            foreach ($methods as $method_key) {
                                $method = dokan_withdraw_get_method( $method_key );
                                ?>
                                <li<?php echo ( $count == 0 ) ? ' class="active"' : ''; ?>><a href="#dokan-payment-<?php echo $method_key; ?>" data-toggle="tab"><?php echo $method['title']; ?></a></li>
                                <?php
                                $count++;
                            } ?>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">

                            <?php
                            $count = 0;
                            foreach ($methods as $method_key) {
                                $method = dokan_withdraw_get_method( $method_key );

                                ?>
                                <div class="tab-pane<?php echo ($count == 0) ? ' active': ''; ?>" id="dokan-payment-<?php echo $method_key; ?>">
                                    <?php if ( is_callable( $method['callback']) ) {
                                        call_user_func( $method['callback'], $profile_info );
                                    } ?>
                                </div>

                                <?php
                                $count++;
                            } ?>
                        </div> <!-- .tab-content -->

                    </div> <!-- .col-md-4 -->
                </div> <!-- .form-group -->

                <div class="form-group">
                    <label class="col-md-3 control-label" for="setting_phone"><?php _e( 'Phone No', 'dokan' ); ?></label>
                    <div class="col-md-5">
                        <input id="setting_phone" value="<?php echo $phone; ?>" name="setting_phone" placeholder="+123456.." class="form-control input-md" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="setting_phone"><?php _e( 'Email', 'dokan' ); ?></label>
                    <div class="col-md-5">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="setting_show_email" value="no">
                                <input type="checkbox" name="setting_show_email" value="yes"<?php checked( $show_email, 'yes' ); ?>> <?php _e( 'Show email address in store', 'dokan' ); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="setting_address"><?php _e( 'Address', 'dokan' ); ?></label>
                    <div class="col-md-5">
                        <textarea class="form-control" rows="4" id="setting_address" name="setting_address"><?php echo $address; ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label" for="setting_map"><?php _e('Map', 'dokan'); ?></label>

                    <div class="col-md-4">
                        <input id="dokan-map-lat" type="hidden" name="location" value="<?php echo $map_location; ?>" size="30" />

                        <div class="input-group">
                            <span class="input-group-btn">
                                <input id="dokan-map-add" type="text" class="form-control" value="<?php echo $map_address; ?>" name="find_address" placeholder="<?php _e( 'Type an address to find', 'dokan' ); ?>" size="30" />
                                <a href="#" class="btn btn-default" id="dokan-location-find-btn" type="button"><?php _e( 'Find Address', 'dokan' ); ?></a>
                            </span>
                        </div><!-- /input-group -->

                        <div class="dokan-google-map" id="dokan-map"></div>
                    </div> <!-- col.md-4 -->
                </div> <!-- .form-group -->

                <div class="form-group">
                    <label class="col-md-3 control-label" for="dokan_setting"></label>

                    <div class="col-md-4 ajax_prev">
                        <input type="submit" name="dokan_update_profile" class="btn btn-primary" value="<?php esc_attr_e('Update Settings','dokan'); ?>">
                    </div>
                </div>

            </form>

                <script type="text/javascript">

                    (function($) {
                        $(function() {
                            <?php
                            $locations = explode( ',', $map_location );
                            $def_lat = isset( $locations[0] ) ? $locations[0] : 90.40714300000002;
                            $def_long = isset( $locations[1] ) ? $locations[1] : 23.709921;
                            ?>
                            var def_zoomval = 12;
                            var def_longval = '<?php echo $def_long; ?>';
                            var def_latval = '<?php echo $def_lat; ?>';
                            var curpoint = new google.maps.LatLng(def_latval, def_longval),
                                geocoder   = new window.google.maps.Geocoder(),
                                $map_area = $('#dokan-map'),
                                $input_area = $( '#dokan-map-lat' ),
                                $input_add = $( '#dokan-map-add' ),
                                $find_btn = $( '#dokan-location-find-btn' );

                            autoCompleteAddress();

                            $find_btn.on('click', function(e) {
                                e.preventDefault();

                                geocodeAddress( $input_add.val() );
                            });

                            var gmap = new google.maps.Map( $map_area[0], {
                                center: curpoint,
                                zoom: def_zoomval,
                                mapTypeId: window.google.maps.MapTypeId.ROADMAP
                            });

                            var marker = new window.google.maps.Marker({
                                position: curpoint,
                                map: gmap,
                                draggable: true
                            });

                            window.google.maps.event.addListener( gmap, 'click', function ( event ) {
                                marker.setPosition( event.latLng );
                                updatePositionInput( event.latLng );
                            } );

                            window.google.maps.event.addListener( marker, 'drag', function ( event ) {
                                updatePositionInput(event.latLng );
                            } );

                            function updatePositionInput( latLng ) {
                                $input_area.val( latLng.lat() + ',' + latLng.lng() );
                            }

                            function updatePositionMarker() {
                                var coord = $input_area.val(),
                                    pos, zoom;

                                if ( coord ) {
                                    pos = coord.split( ',' );
                                    marker.setPosition( new window.google.maps.LatLng( pos[0], pos[1] ) );

                                    zoom = pos.length > 2 ? parseInt( pos[2], 10 ) : 12;

                                    gmap.setCenter( marker.position );
                                    gmap.setZoom( zoom );
                                }
                            }

                            function geocodeAddress( address ) {
                                geocoder.geocode( {'address': address}, function ( results, status ) {
                                    if ( status == window.google.maps.GeocoderStatus.OK ) {
                                        updatePositionInput( results[0].geometry.location );
                                        marker.setPosition( results[0].geometry.location );
                                        gmap.setCenter( marker.position );
                                        gmap.setZoom( 15 );
                                    }
                                } );
                            }

                            function autoCompleteAddress(){
                                if (!$input_add) return null;

                                $input_add.autocomplete({
                                    source: function(request, response) {
                                        // TODO: add 'region' option, to help bias geocoder.
                                        geocoder.geocode( {'address': request.term }, function(results, status) {
                                            response(jQuery.map(results, function(item) {
                                                return {
                                                    label     : item.formatted_address,
                                                    value     : item.formatted_address,
                                                    latitude  : item.geometry.location.lat(),
                                                    longitude : item.geometry.location.lng()
                                                };
                                            }));
                                        });
                                    },
                                    select: function(event, ui) {

                                        $input_area.val(ui.item.latitude + ',' + ui.item.longitude );

                                        var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);

                                        gmap.setCenter(location);
                                        // Drop the Marker
                                        setTimeout( function(){
                                            marker.setValues({
                                                position    : location,
                                                animation   : window.google.maps.Animation.DROP
                                            });
                                        }, 1500);
                                    }
                                });
                            }

                        });
                    })(jQuery);
                </script>

                <script type="text/javascript">

                    jQuery(function($){
                        // $('#setting_category').chosen({
                        //     width: "95%"
                        // }).change(function() {
                        //     $("form#settings-form").validate().element("#setting_category");
                        // });
                    })

                </script>

        <?php
    }

    function get_dokan_categories() {
        $dokan_category = array(
            'book' => __('Book', 'dokan'),
            'dress' => __('Dress', 'dokan'),
            'electronic' => __('Electronic', 'dokan'),
        );

        return apply_filters('dokan_category', $dokan_category);
    }
}

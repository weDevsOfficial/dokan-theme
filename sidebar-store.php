<div id="secondary" class="col-md-3" role="complementary">
    <div class="widget-area">

        <aside class="widget dokan-category">
            <div id="cat-drop-stack">
                <ul>
                    <li><a href="#">Electronics</a></li>
                    <li><a href="#">Apparel &amp; Accessories</a></li>
                    <li><a href="#">Home &amp; Garden</a></li>
                    <li><a href="#">Bags &amp; Shoes</a></li>
                    <li><a href="#">Jewelry &amp; Watches</a></li>
                    <li><a href="#">Automotive</a></li>
                    <li><a href="#">Beauty &amp; Health</a></li>
                    <li><a href="#">Toys, Kids &amp; Baby</a></li>
                    <li><a href="#">Sports &amp; Entertainment</a></li>
                    <li><a href="#">All Categories</a></li>
                </ul>
            </div>
        </aside> <!-- .dokan-category -->

        <aside class="widget store-location">
            <h3 class="widget-title">Store Location</h3>

            <div class="location-container">
                <div id="dokan-store-location"></div>

                <script type="text/javascript">
                    jQuery(function($) {
                        <?php
                        $map_location = '23.709921,90.40714300000002';
                        $locations = explode( ',', $map_location );
                        $def_lat = isset( $locations[0] ) ? $locations[0] : 90.40714300000002;
                        $def_long = isset( $locations[1] ) ? $locations[1] : 23.709921;
                        ?>

                        var def_longval = <?php echo $def_long; ?>;
                        var def_latval = <?php echo $def_lat; ?>;

                        var curpoint = new google.maps.LatLng(def_latval, def_longval),
                            geocoder   = new window.google.maps.Geocoder(),
                            $map_area = $('#dokan-store-location');

                        var gmap = new google.maps.Map( $map_area[0], {
                            center: curpoint,
                            zoom: 12,
                            mapTypeId: window.google.maps.MapTypeId.ROADMAP
                        });

                        var marker = new window.google.maps.Marker({
                            position: curpoint,
                            map: gmap,
                            draggable: true
                        });
                    })

                </script>
            </div>
        </aside>

        <aside class="widget store-contact">
            <h3 class="widget-title">Contact Seller</h3>

            <form action="" method="post" class="seller-form">
                <ul>
                    <li>
                        <input type="text" name="name" value="" placeholder="<?php esc_attr_e( 'Name', 'dokan' ); ?>" class="form-control">
                    </li>
                    <li>
                        <input type="email" name="email" value="" placeholder="<?php esc_attr_e( 'you@example.com', 'dokan' ); ?>" class="form-control">
                    </li>
                    <li>
                        <textarea  name="message" maxlength="1000" cols="25" rows="6" value="" placeholder="<?php esc_attr_e( 'Type your messsage...', 'dokan' ); ?>" class="form-control"></textarea>
                    </li>
                    <li><input type="submit" name="store_message_send" value="<?php esc_attr_e( 'Send Message', 'dokan' ); ?>" class="pull-right btn btn-success"></li>
                </ul>
            </form>
        </aside>
    </div>
</div><!-- #secondary .widget-area -->
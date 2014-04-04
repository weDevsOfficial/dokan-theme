<?php

/**
 * Show the variable product options.
 *
 * @access public
 * @return void
 */
function dokan_variable_product_type_options() {
    global $post, $woocommerce;

    $attributes = maybe_unserialize( get_post_meta( $post->ID, '_product_attributes', true ) );

    // See if any are set
    $variation_attribute_found = false;
    if ( $attributes ) foreach( $attributes as $attribute ) {
        if ( isset( $attribute['is_variation'] ) ) {
            $variation_attribute_found = true;
            break;
        }
    }

    // Get tax classes
    $tax_classes = array_filter( array_map('trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
    $tax_class_options = array();
    $tax_class_options[''] = __( 'Standard', 'woocommerce' );
    if ( $tax_classes ) {
        foreach ( $tax_classes as $class ) {
            $tax_class_options[ sanitize_title( $class ) ] = esc_attr( $class );
        }
    }

    // var_dump( $attributes, $tax_classes, $tax_class_options );
    ?>
    <div id="variable_product_options" class="wc-metaboxes-wrapper">
        <div id="variable_product_options_inner">

        <?php if ( ! $variation_attribute_found ) : ?>

            <div id="message" class="inline woocommerce-message">
                <div class="squeezer">
                    <h4><?php _e( 'Before adding variations, add and save some attributes on the <strong>Attributes</strong> tab.', 'woocommerce' ); ?></h4>

                    <p class="submit"><a class="button-primary" href="http://docs.woothemes.com/document/product-variations/" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></p>
                </div>
            </div>

        <?php else : ?>

            <div class="woocommerce_variations wc-metaboxes">
                <?php
                // Get parent data
                $parent_data = array(
                    'id'        => $post->ID,
                    'attributes' => $attributes,
                    'tax_class_options' => $tax_class_options,
                    'sku'       => get_post_meta( $post->ID, '_sku', true ),
                    'weight'    => get_post_meta( $post->ID, '_weight', true ),
                    'length'    => get_post_meta( $post->ID, '_length', true ),
                    'width'     => get_post_meta( $post->ID, '_width', true ),
                    'height'    => get_post_meta( $post->ID, '_height', true ),
                    'tax_class' => get_post_meta( $post->ID, '_tax_class', true )
                );

                if ( ! $parent_data['weight'] )
                    $parent_data['weight'] = '0.00';

                if ( ! $parent_data['length'] )
                    $parent_data['length'] = '0';

                if ( ! $parent_data['width'] )
                    $parent_data['width'] = '0';

                if ( ! $parent_data['height'] )
                    $parent_data['height'] = '0';

                // Get variations
                $args = array(
                    'post_type'     => 'product_variation',
                    'post_status'   => array( 'private', 'publish' ),
                    'numberposts'   => -1,
                    'orderby'       => 'menu_order',
                    'order'         => 'asc',
                    'post_parent'   => $post->ID
                );
                $variations = get_posts( $args );
                $loop = 0;

                // var_dump( $variations );

                if ( $variations ) foreach ( $variations as $variation ) {

                    $variation_id           = absint( $variation->ID );
                    $variation_post_status  = esc_attr( $variation->post_status );
                    $variation_data         = get_post_meta( $variation_id );
                    $variation_data['variation_post_id'] = $variation_id;

                    // Grab shipping classes
                    $shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
                    $shipping_class = ( $shipping_classes && ! is_wp_error( $shipping_classes ) ) ? current( $shipping_classes )->term_id : '';

                    $variation_fields = array(
                        '_sku',
                        '_stock',
                        '_regular_price',
                        '_sale_price',
                        '_weight',
                        '_length',
                        '_width',
                        '_height',
                        '_download_limit',
                        '_download_expiry',
                        '_downloadable_files',
                        '_downloadable',
                        '_virtual',
                        '_thumbnail_id',
                        '_sale_price_dates_from',
                        '_sale_price_dates_to'
                    );

                    foreach ( $variation_fields as $field ) {
                        $$field = isset( $variation_data[ $field ][0] ) ? maybe_unserialize( $variation_data[ $field ][0] ) : '';
                    }

                    $_tax_class = isset( $variation_data['_tax_class'][0] ) ? $variation_data['_tax_class'][0] : null;
                    $image_id   = absint( $_thumbnail_id );
                    $image      = $image_id ? wp_get_attachment_thumb_url( $image_id ) : '';

                    // Locale formatting
                    $_regular_price = wc_format_localized_price( $_regular_price );
                    $_sale_price    = wc_format_localized_price( $_sale_price );
                    $_weight        = wc_format_localized_decimal( $_weight );
                    $_length        = wc_format_localized_decimal( $_length );
                    $_width         = wc_format_localized_decimal( $_width );
                        $_height        = wc_format_localized_decimal( $_height );

                    include DOKAN_INC_DIR . '/woo-views/variation-admin-html.php';

                    $loop++;
                }
                ?>
            </div> <!-- .woocommerce_variations -->

            <p class="toolbar">

                <button type="button" class="btn btn-sm btn-success button-primary add_variation" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Add Variation', 'woocommerce' ); ?></button>

                <button type="button" class="btn btn-sm btn-default link_all_variations" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Link all variations', 'woocommerce' ); ?></button>

                <strong><?php _e( 'Default selections:', 'woocommerce' ); ?></strong>
                <?php
                    $default_attributes = maybe_unserialize( get_post_meta( $post->ID, '_default_attributes', true ) );
                    foreach ( $attributes as $attribute ) {

                        // Only deal with attributes that are variations
                        if ( ! $attribute['is_variation'] )
                            continue;

                        // Get current value for variation (if set)
                        $variation_selected_value = isset( $default_attributes[ sanitize_title( $attribute['name'] ) ] ) ? $default_attributes[ sanitize_title( $attribute['name'] ) ] : '';

                        // Name will be something like attribute_pa_color
                        echo '<select name="default_attribute_' . sanitize_title( $attribute['name'] ) . '"><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                        // Get terms for attribute taxonomy or value if its a custom attribute
                        if ( $attribute['is_taxonomy'] ) {

                            $post_terms = wp_get_post_terms( $post->ID, $attribute['name'] );

                            foreach ( $post_terms as $term )
                                echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';

                        } else {

                            $options = array_map( 'trim', explode( '|', $attribute['value'] ) );

                            foreach ( $options as $option )
                                echo '<option ' . selected( sanitize_title( $variation_selected_value ), sanitize_title( $option ), false ) . ' value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) )  . '</option>';

                        }

                        echo '</select>';
                    }
                ?>
            </p> <!-- .toolbar -->

        <?php endif; ?>
    </div>
</div>
    <?php
    /**
     * Product Type Javascript
     */
    ob_start();
    ?>
    jQuery(function($){

        var variation_sortable_options = {
            items:'.woocommerce_variation',
            cursor:'move',
            axis:'y',
            handle: 'h3',
            scrollSensitivity:40,
            forcePlaceholderSize: true,
            helper: 'clone',
            opacity: 0.65,
            placeholder: 'wc-metabox-sortable-placeholder',
            start:function(event,ui){
                ui.item.css('background-color','#f6f6f6');
            },
            stop:function(event,ui){
                ui.item.removeAttr('style');
                variation_row_indexes();
            }
        };

        // Add a variation
        jQuery('#variable_product_options').on('click', 'button.add_variation', function(){

            jQuery('.woocommerce_variations').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

            var loop = jQuery('.woocommerce_variation').size();

            var data = {
                action: 'dokan_add_variation',
                post_id: <?php echo $post->ID; ?>,
                loop: loop,
                security: '<?php echo wp_create_nonce("add-variation"); ?>'
            };

            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                jQuery('.woocommerce_variations').append( response );
                jQuery(".tips").tooltip();

                jQuery('input.variable_is_downloadable, input.variable_is_virtual').change();

                jQuery('.woocommerce_variations').unblock();
                jQuery('#variable_product_options').trigger('woocommerce_variations_added');
            });

            return false;

        });

        jQuery('#variable_product_options').on('click', 'button.link_all_variations', function(){

            var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).', 'woocommerce' ) ); ?>');

            if (answer) {

                jQuery('#variable_product_options').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

                var data = {
                    action: 'dokan_link_all_variations',
                    post_id: <?php echo $post->ID; ?>,
                    security: '<?php echo wp_create_nonce("link-variations"); ?>'
                };

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                    var count = parseInt( response );

                    if (count==1) {
                        alert( count + ' <?php echo esc_js( __( "variation added", 'dokan' ) ); ?>');
                    } else if (count==0 || count>1) {
                        alert( count + ' <?php echo esc_js( __( "variations added", 'dokan' ) ); ?>');
                    } else {
                        alert('<?php echo esc_js( __( "No variations added", 'dokan' ) ); ?>');
                    }

                    if (count>0) {
                        var this_page = window.location.toString();

                        this_page = this_page.replace( 'post-new.php?', 'post.php?post=<?php echo $post->ID; ?>&action=edit&' );

                        $('#variable_product_options').load( this_page + ' #variable_product_options_inner', function() {
                            $('#variable_product_options').unblock();
                            jQuery('#variable_product_options').trigger('woocommerce_variations_added');
                        } );
                    } else {
                        $('#variable_product_options').unblock();
                    }

                });
            }
            return false;
        });

        jQuery('#variable_product_options').on('click', 'button.remove_variation', function(e){
            e.preventDefault();
            var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to remove this variation?', 'dokan' ) ); ?>');
            if (answer){

                var el = jQuery(this).parent().parent();

                var variation = jQuery(this).attr('rel');

                if (variation>0) {

                    jQuery(el).block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

                    var data = {
                        action: 'woocommerce_remove_variation',
                        variation_id: variation,
                        security: '<?php echo wp_create_nonce("delete-variation"); ?>'
                    };

                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                        // Success
                        jQuery(el).fadeOut('300', function(){
                            jQuery(el).remove();
                        });
                    });

                } else {
                    jQuery(el).fadeOut('300', function(){
                        jQuery(el).remove();
                    });
                }

            }
            return false;
        });

        jQuery('#variable_product_options').on('change', 'input.variable_is_downloadable', function(){

            jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_downloadable').hide();

            if (jQuery(this).is(':checked')) {
                jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_downloadable').show();
            }

        });

        jQuery('#variable_product_options').on('change', 'input.variable_is_virtual', function(){

            jQuery(this).closest('.woocommerce_variation').find('.hide_if_variation_virtual').show();

            if (jQuery(this).is(':checked')) {
                jQuery(this).closest('.woocommerce_variation').find('.hide_if_variation_virtual').hide();
            }

        });

        jQuery('input.variable_is_downloadable, input.variable_is_virtual').change();

        // Ordering
        $('#variable_product_options').on( 'woocommerce_variations_added', function() {
            $('.woocommerce_variations').sortable( variation_sortable_options );
        } );

        $('.woocommerce_variations').sortable( variation_sortable_options );

        function variation_row_indexes() {
            $('.woocommerce_variations .woocommerce_variation').each(function(index, el){
                $('.variation_menu_order', el).val( parseInt( $(el).index('.woocommerce_variations .woocommerce_variation') ) );
            });
        };

        // Uploader
        var variable_image_frame;
        var setting_variation_image_id;
        var setting_variation_image;
        var wp_media_post_id = wp.media.model.settings.post.id;

        wp.media.view.settings.post = <?php echo json_encode( array( 'param' => 'dokan', 'post_id' => $post->ID ) ); // big juicy hack. ?>;

        jQuery('#variable_product_options').on('click', '.upload_image_button', function( event ) {

            console.log('choose file');

            var $button                = jQuery( this );
            var post_id                = $button.attr('rel');
            var $parent                = $button.closest('.upload_image');
            setting_variation_image    = $parent;
            setting_variation_image_id = post_id;

            event.preventDefault();

            if ( $button.is('.remove') ) {

                setting_variation_image.find( '.upload_image_id' ).val( '' );
                setting_variation_image.find( 'img' ).attr( 'src', '<?php echo woocommerce_placeholder_img_src(); ?>' );
                setting_variation_image.find( '.upload_image_button' ).removeClass( 'remove' );

            } else {

                // If the media frame already exists, reopen it.
                if ( variable_image_frame ) {
                    variable_image_frame.uploader.uploader.param( 'post_id', setting_variation_image_id );
                    variable_image_frame.open();
                    return;
                } else {
                    wp.media.model.settings.post.id = setting_variation_image_id;
                    wp.media.model.settings.type = 'dokan';
                }

                // Create the media frame.
                variable_image_frame = wp.media.frames.variable_image = wp.media({
                    // Set the title of the modal.
                    title: '<?php echo esc_js( __( 'Choose an image', 'woocommerce' ) ); ?>',
                    button: {
                        text: '<?php echo esc_js( __( 'Set variation image', 'woocommerce' ) ); ?>'
                    }
                });

                // When an image is selected, run a callback.
                variable_image_frame.on( 'select', function() {

                    attachment = variable_image_frame.state().get('selection').first().toJSON();

                    setting_variation_image.find( '.upload_image_id' ).val( attachment.id );
                    setting_variation_image.find( '.upload_image_button' ).addClass( 'remove' );
                    setting_variation_image.find( 'img' ).attr( 'src', attachment.url );

                    wp.media.model.settings.post.id = wp_media_post_id;
                });

                // Finally, open the modal.
                variable_image_frame.open();
            }
        });

        // Restore ID
        jQuery('a.add_media').on('click', function() {
            wp.media.model.settings.post.id = wp_media_post_id;
        } );

    });
    <?php
    $javascript = ob_get_clean();
    wc_enqueue_js( $javascript );
}

/**
 * Save the product data meta box.
 *
 * @access public
 * @param mixed $post_id
 * @return void
 */
function dokan_process_product_meta( $post_id ) {
    global $wpdb, $woocommerce, $woocommerce_errors;

    // Add any default post meta
    add_post_meta( $post_id, 'total_sales', '0', true );

    // Get types
    $product_type       = empty( $_POST['_product_type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['_product_type'] ) );
    $is_downloadable    = isset( $_POST['_downloadable'] ) ? 'yes' : 'no';
    $is_virtual         = isset( $_POST['_virtual'] ) ? 'yes' : 'no';

    // Product type + Downloadable/Virtual
    wp_set_object_terms( $post_id, $product_type, 'product_type' );
    update_post_meta( $post_id, '_downloadable', $is_downloadable );
    update_post_meta( $post_id, '_virtual', $is_virtual );

    // Gallery Images
    $attachment_ids = array_filter( explode( ',', woocommerce_clean( $_POST['product_image_gallery'] ) ) );
    update_post_meta( $post_id, '_product_image_gallery', implode( ',', $attachment_ids ) );

    // Update post meta
    update_post_meta( $post_id, '_regular_price', stripslashes( $_POST['_regular_price'] ) );
    update_post_meta( $post_id, '_sale_price', stripslashes( $_POST['_sale_price'] ) );

    if ( isset( $_POST['_tax_status'] ) )
        update_post_meta( $post_id, '_tax_status', stripslashes( $_POST['_tax_status'] ) );

    if ( isset( $_POST['_tax_class'] ) )
        update_post_meta( $post_id, '_tax_class', stripslashes( $_POST['_tax_class'] ) );

    update_post_meta( $post_id, '_visibility', stripslashes( $_POST['_visibility'] ) );
    update_post_meta( $post_id, '_purchase_note', stripslashes( $_POST['_purchase_note'] ) );

    // Dimensions
    if ( $is_virtual == 'no' ) {
        update_post_meta( $post_id, '_weight', stripslashes( $_POST['_weight'] ) );
        update_post_meta( $post_id, '_length', stripslashes( $_POST['_length'] ) );
        update_post_meta( $post_id, '_width', stripslashes( $_POST['_width'] ) );
        update_post_meta( $post_id, '_height', stripslashes( $_POST['_height'] ) );
    } else {
        update_post_meta( $post_id, '_weight', '' );
        update_post_meta( $post_id, '_length', '' );
        update_post_meta( $post_id, '_width', '' );
        update_post_meta( $post_id, '_height', '' );
    }

    // Save shipping class
    $product_shipping_class = $_POST['product_shipping_class'] > 0 && $product_type != 'external' ? absint( $_POST['product_shipping_class'] ) : '';
    wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class');

    // Unique SKU
    $sku                = get_post_meta($post_id, '_sku', true);
    $new_sku            = woocommerce_clean( stripslashes( $_POST['_sku'] ) );
    if ( $new_sku == '' ) {
        update_post_meta( $post_id, '_sku', '' );
    } elseif ( $new_sku !== $sku ) {
        if ( ! empty( $new_sku ) ) {
            if (
                $wpdb->get_var( $wpdb->prepare("
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                    WHERE $wpdb->posts.post_type = 'product'
                    AND $wpdb->posts.post_status = 'publish'
                    AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
                 ", $new_sku ) )
                ) {
                $woocommerce_errors[] = __( 'Product SKU must be unique.', 'woocommerce' );
            } else {
                update_post_meta( $post_id, '_sku', $new_sku );
            }
        } else {
            update_post_meta( $post_id, '_sku', '' );
        }
    }

    // Save Attributes
    $attributes = array();

    if ( isset( $_POST['attribute_names'] ) ) {
        $attribute_names = $_POST['attribute_names'];
        $attribute_values = $_POST['attribute_values'];

        if ( isset( $_POST['attribute_visibility'] ) )
            $attribute_visibility = $_POST['attribute_visibility'];

        if ( isset( $_POST['attribute_variation'] ) )
            $attribute_variation = $_POST['attribute_variation'];

        $attribute_is_taxonomy = $_POST['attribute_is_taxonomy'];
        $attribute_position = $_POST['attribute_position'];

        $attribute_names_count = sizeof( $attribute_names );

        for ( $i=0; $i < $attribute_names_count; $i++ ) {
            if ( ! $attribute_names[ $i ] )
                continue;

            $is_visible     = isset( $attribute_visibility[ $i ] ) ? 1 : 0;
            $is_variation   = isset( $attribute_variation[ $i ] ) ? 1 : 0;
            $is_taxonomy    = $attribute_is_taxonomy[ $i ] ? 1 : 0;

            if ( $is_taxonomy ) {

                if ( isset( $attribute_values[ $i ] ) ) {

                    // Select based attributes - Format values (posted values are slugs)
                    if ( is_array( $attribute_values[ $i ] ) ) {
                        $values = array_map( 'sanitize_title', $attribute_values[ $i ] );

                    // Text based attributes - Posted values are term names - don't change to slugs
                    } else {
                        $values = array_map( 'stripslashes', array_map( 'strip_tags', explode( '|', $attribute_values[ $i ] ) ) );
                    }

                    // Remove empty items in the array
                    $values = array_filter( $values, 'strlen' );

                } else {
                    $values = array();
                }

                // Update post terms
                if ( taxonomy_exists( $attribute_names[ $i ] ) )
                    wp_set_object_terms( $post_id, $values, $attribute_names[ $i ] );

                if ( $values ) {
                    // Add attribute to array, but don't set values
                    $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                        'name'          => woocommerce_clean( $attribute_names[ $i ] ),
                        'value'         => '',
                        'position'      => $attribute_position[ $i ],
                        'is_visible'    => $is_visible,
                        'is_variation'  => $is_variation,
                        'is_taxonomy'   => $is_taxonomy
                    );
                }

            } elseif ( isset( $attribute_values[ $i ] ) ) {

                // Text based, separate by pipe
                $values = implode( ' | ', array_map( 'woocommerce_clean', $attribute_values[$i] ) );

                // Custom attribute - Add attribute to array and set the values
                $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                    'name'          => woocommerce_clean( $attribute_names[ $i ] ),
                    'value'         => $values,
                    'position'      => $attribute_position[ $i ],
                    'is_visible'    => $is_visible,
                    'is_variation'  => $is_variation,
                    'is_taxonomy'   => $is_taxonomy
                );
            }

         }
    }

    if ( ! function_exists( 'attributes_cmp' ) ) {
        function attributes_cmp( $a, $b ) {
            if ( $a['position'] == $b['position'] ) return 0;
            return ( $a['position'] < $b['position'] ) ? -1 : 1;
        }
    }
    uasort( $attributes, 'attributes_cmp' );

    update_post_meta( $post_id, '_product_attributes', $attributes );

    // Sales and prices
    if ( in_array( $product_type, array( 'variable' ) ) ) {

        // Variable products have no prices
        update_post_meta( $post_id, '_regular_price', '' );
        update_post_meta( $post_id, '_sale_price', '' );
        update_post_meta( $post_id, '_sale_price_dates_from', '' );
        update_post_meta( $post_id, '_sale_price_dates_to', '' );
        update_post_meta( $post_id, '_price', '' );

    } else {

        $date_from = isset( $_POST['_sale_price_dates_from'] ) ? $_POST['_sale_price_dates_from'] : '';
        $date_to = isset( $_POST['_sale_price_dates_to'] ) ? $_POST['_sale_price_dates_to'] : '';

        // Dates
        if ( $date_from )
            update_post_meta( $post_id, '_sale_price_dates_from', strtotime( $date_from ) );
        else
            update_post_meta( $post_id, '_sale_price_dates_from', '' );

        if ( $date_to )
            update_post_meta( $post_id, '_sale_price_dates_to', strtotime( $date_to ) );
        else
            update_post_meta( $post_id, '_sale_price_dates_to', '' );

        if ( $date_to && ! $date_from )
            update_post_meta( $post_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );

        // Update price if on sale
        if ( $_POST['_sale_price'] != '' && $date_to == '' && $date_from == '' )
            update_post_meta( $post_id, '_price', stripslashes( $_POST['_sale_price'] ) );
        else
            update_post_meta( $post_id, '_price', stripslashes( $_POST['_regular_price'] ) );

        if ( $_POST['_sale_price'] != '' && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) )
            update_post_meta( $post_id, '_price', stripslashes($_POST['_sale_price']) );

        if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
            update_post_meta( $post_id, '_price', stripslashes($_POST['_regular_price']) );
            update_post_meta( $post_id, '_sale_price_dates_from', '');
            update_post_meta( $post_id, '_sale_price_dates_to', '');
        }
    }

    // Sold Individuall
    if ( ! empty( $_POST['_sold_individually'] ) ) {
        update_post_meta( $post_id, '_sold_individually', 'yes' );
    } else {
        update_post_meta( $post_id, '_sold_individually', '' );
    }

    // Stock Data
    if ( get_option('woocommerce_manage_stock') == 'yes' ) {

        if ( ! empty( $_POST['_manage_stock'] ) ) {

            // Manage stock
            update_post_meta( $post_id, '_stock', (int) $_POST['_stock'] );
            update_post_meta( $post_id, '_stock_status', stripslashes( $_POST['_stock_status'] ) );
            update_post_meta( $post_id, '_backorders', stripslashes( $_POST['_backorders'] ) );
            update_post_meta( $post_id, '_manage_stock', 'yes' );

            // Check stock level
            if ( $product_type !== 'variable' && $_POST['_backorders'] == 'no' && (int) $_POST['_stock'] < 1 )
                update_post_meta( $post_id, '_stock_status', 'outofstock' );

        } else {

            // Don't manage stock
            update_post_meta( $post_id, '_stock', '' );
            update_post_meta( $post_id, '_stock_status', stripslashes( $_POST['_stock_status'] ) );
            update_post_meta( $post_id, '_backorders', stripslashes( $_POST['_backorders'] ) );
            update_post_meta( $post_id, '_manage_stock', 'no' );

        }

    } else {

        update_post_meta( $post_id, '_stock_status', stripslashes( $_POST['_stock_status'] ) );

    }

    // Upsells
    if ( isset( $_POST['upsell_ids'] ) ) {
        $upsells = array();
        $ids = $_POST['upsell_ids'];
        foreach ( $ids as $id )
            if ( $id && $id > 0 )
                $upsells[] = $id;

        update_post_meta( $post_id, '_upsell_ids', $upsells );
    } else {
        delete_post_meta( $post_id, '_upsell_ids' );
    }

    // Cross sells
    if ( isset( $_POST['crosssell_ids'] ) ) {
        $crosssells = array();
        $ids = $_POST['crosssell_ids'];
        foreach ( $ids as $id )
            if ( $id && $id > 0 )
                $crosssells[] = $id;

        update_post_meta( $post_id, '_crosssell_ids', $crosssells );
    } else {
        delete_post_meta( $post_id, '_crosssell_ids' );
    }

    // Downloadable options
    if ( $is_downloadable == 'yes' ) {

        $_download_limit = absint( $_POST['_download_limit'] );
        if ( ! $_download_limit )
            $_download_limit = ''; // 0 or blank = unlimited

        $_download_expiry = absint( $_POST['_download_expiry'] );
        if ( ! $_download_expiry )
            $_download_expiry = ''; // 0 or blank = unlimited

        // file paths will be stored in an array keyed off md5(file path)
        if ( isset( $_POST['_wc_file_urls'] ) ) {
            $files = array();

            $file_names    = isset( $_POST['_wc_file_names'] ) ? array_map( 'wc_clean', $_POST['_wc_file_names'] ) : array();
            $file_urls     = isset( $_POST['_wc_file_urls'] ) ? array_map( 'esc_url_raw', array_map( 'trim', $_POST['_wc_file_urls'] ) ) : array();
            $file_url_size = sizeof( $file_urls );

            for ( $i = 0; $i < $file_url_size; $i ++ ) {
                if ( ! empty( $file_urls[ $i ] ) )
                    $files[ md5( $file_urls[ $i ] ) ] = array(
                        'name' => $file_names[ $i ],
                        'file' => $file_urls[ $i ]
                    );
            }

            // grant permission to any newly added files on any existing orders for this product prior to saving
            do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $files );

            update_post_meta( $post_id, '_downloadable_files', $files );
        }

        update_post_meta( $post_id, '_download_limit', $_download_limit );
        update_post_meta( $post_id, '_download_expiry', $_download_expiry );

        if ( isset( $_POST['_download_limit'] ) )
            update_post_meta( $post_id, '_download_limit', esc_attr( $_download_limit ) );
        if ( isset( $_POST['_download_expiry'] ) )
            update_post_meta( $post_id, '_download_expiry', esc_attr( $_download_expiry ) );
    }

    // Save variations
    if ( $product_type == 'variable' )
        dokan_save_variations( $post_id );

    // Do action for product type
    do_action( 'woocommerce_process_product_meta_' . $product_type, $post_id );
    do_action( 'dokan_process_product_meta', $post_id );

    // Clear cache/transients
    wc_delete_product_transients( $post_id );
}

function dokan_save_variations( $post_id ) {
    global $woocommerce, $wpdb;

    $attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

    if ( isset( $_POST['variable_sku'] ) ) {

        $variable_post_id                   = $_POST['variable_post_id'];
        $variable_sku                       = $_POST['variable_sku'];
        $variable_regular_price             = $_POST['variable_regular_price'];
        $variable_sale_price                = $_POST['variable_sale_price'];
        $upload_image_id                    = $_POST['upload_image_id'];
        $variable_download_limit            = $_POST['variable_download_limit'];
        $variable_download_expiry           = $_POST['variable_download_expiry'];
        $variable_shipping_class            = $_POST['variable_shipping_class'];
        $variable_tax_class                 = isset( $_POST['variable_tax_class'] ) ? $_POST['variable_tax_class'] : array();
        $variable_menu_order                = $_POST['variation_menu_order'];
        $variable_sale_price_dates_from     = $_POST['variable_sale_price_dates_from'];
        $variable_sale_price_dates_to       = $_POST['variable_sale_price_dates_to'];

        $variable_weight                    = isset( $_POST['variable_weight'] ) ? $_POST['variable_weight'] : array();
        $variable_length                    = isset( $_POST['variable_length'] ) ? $_POST['variable_length'] : array();
        $variable_width                     = isset( $_POST['variable_width'] ) ? $_POST['variable_width'] : array();
        $variable_height                    = isset( $_POST['variable_height'] ) ? $_POST['variable_height'] : array();
        $variable_stock                     = isset( $_POST['variable_stock'] ) ? $_POST['variable_stock'] : array();
        $variable_enabled                   = isset( $_POST['variable_enabled'] ) ? $_POST['variable_enabled'] : array();
        $variable_is_virtual                = isset( $_POST['variable_is_virtual'] ) ? $_POST['variable_is_virtual'] : array();
        $variable_is_downloadable           = isset( $_POST['variable_is_downloadable'] ) ? $_POST['variable_is_downloadable'] : array();

        $max_loop = max( array_keys( $_POST['variable_post_id'] ) );

        for ( $i = 0; $i <= $max_loop; $i ++ ) {

            if ( ! isset( $variable_post_id[ $i ] ) )
                continue;

            $variation_id = absint( $variable_post_id[ $i ] );

            // Virtal/Downloadable
            $is_virtual = isset( $variable_is_virtual[ $i ] ) ? 'yes' : 'no';
            $is_downloadable = isset( $variable_is_downloadable[ $i ] ) ? 'yes' : 'no';

            // Enabled or disabled
            $post_status = isset( $variable_enabled[ $i ] ) ? 'publish' : 'private';

            // Generate a useful post title
            $variation_post_title = sprintf( __( 'Variation #%s of %s', 'woocommerce' ), absint( $variation_id ), esc_html( get_the_title( $post_id ) ) );

            // Update or Add post
            if ( ! $variation_id ) {

                $variation = array(
                    'post_title'    => $variation_post_title,
                    'post_content'  => '',
                    'post_status'   => $post_status,
                    'post_author'   => get_current_user_id(),
                    'post_parent'   => $post_id,
                    'post_type'     => 'product_variation',
                    'menu_order'    => $variable_menu_order[ $i ]
                );

                $variation_id = wp_insert_post( $variation );

                do_action( 'woocommerce_create_product_variation', $variation_id );

            } else {

                $wpdb->update( $wpdb->posts, array( 'post_status' => $post_status, 'post_title' => $variation_post_title, 'menu_order' => $variable_menu_order[ $i ] ), array( 'ID' => $variation_id ) );

                do_action( 'woocommerce_update_product_variation', $variation_id );

            }

            // Update post meta
            update_post_meta( $variation_id, '_sku', wc_clean( $variable_sku[ $i ] ) );
            update_post_meta( $variation_id, '_thumbnail_id', absint( $upload_image_id[ $i ] ) );
            update_post_meta( $variation_id, '_virtual', wc_clean( $is_virtual ) );
            update_post_meta( $variation_id, '_downloadable', wc_clean( $is_downloadable ) );

            if ( isset( $variable_weight[ $i ] ) )
                update_post_meta( $variation_id, '_weight', ( $variable_weight[ $i ] === '' ) ? '' : wc_format_decimal( $variable_weight[ $i ] ) );
            if ( isset( $variable_length[ $i ] ) )
                update_post_meta( $variation_id, '_length', ( $variable_length[ $i ] === '' ) ? '' : wc_format_decimal( $variable_length[ $i ] ) );
            if ( isset( $variable_width[ $i ] ) )
                update_post_meta( $variation_id, '_width', ( $variable_width[ $i ] === '' ) ? '' : wc_format_decimal( $variable_width[ $i ] ) );
            if ( isset( $variable_height[ $i ] ) )
                update_post_meta( $variation_id, '_height', ( $variable_height[ $i ] === '' ) ? '' : wc_format_decimal( $variable_height[ $i ] ) );

            // Stock handling
            if ( isset($variable_stock[$i]) )
                wc_update_product_stock( $variation_id, wc_clean( $variable_stock[ $i ] ) );

            // Price handling
            $regular_price  = wc_format_decimal( $variable_regular_price[ $i ] );
            $sale_price     = ( $variable_sale_price[ $i ] === '' ? '' : wc_format_decimal( $variable_sale_price[ $i ] ) );
            $date_from      = wc_clean( $variable_sale_price_dates_from[ $i ] );
            $date_to        = wc_clean( $variable_sale_price_dates_to[ $i ] );

            update_post_meta( $variation_id, '_regular_price', $regular_price );
            update_post_meta( $variation_id, '_sale_price', $sale_price );

            // Save Dates
            if ( $date_from )
                update_post_meta( $variation_id, '_sale_price_dates_from', strtotime( $date_from ) );
            else
                update_post_meta( $variation_id, '_sale_price_dates_from', '' );

            if ( $date_to )
                update_post_meta( $variation_id, '_sale_price_dates_to', strtotime( $date_to ) );
            else
                update_post_meta( $variation_id, '_sale_price_dates_to', '' );

            if ( $date_to && ! $date_from )
                update_post_meta( $variation_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );

            // Update price if on sale
            if ( $sale_price != '' && $date_to == '' && $date_from == '' )
                update_post_meta( $variation_id, '_price', $sale_price );
            else
                update_post_meta( $variation_id, '_price', $regular_price );

            if ( $sale_price != '' && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) )
                update_post_meta( $variation_id, '_price', $sale_price );

            if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
                update_post_meta( $variation_id, '_price', $regular_price );
                update_post_meta( $variation_id, '_sale_price_dates_from', '' );
                update_post_meta( $variation_id, '_sale_price_dates_to', '' );
            }

            if ( isset( $variable_tax_class[ $i ] ) && $variable_tax_class[ $i ] !== 'parent' )
                update_post_meta( $variation_id, '_tax_class', wc_clean( $variable_tax_class[ $i ] ) );
            else
                delete_post_meta( $variation_id, '_tax_class' );

            if ( $is_downloadable == 'yes' ) {
                update_post_meta( $variation_id, '_download_limit', wc_clean( $variable_download_limit[ $i ] ) );
                update_post_meta( $variation_id, '_download_expiry', wc_clean( $variable_download_expiry[ $i ] ) );

                $files         = array();
                $file_names    = isset( $_POST['_wc_variation_file_names'][ $variation_id ] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_names'][ $variation_id ] ) : array();
                $file_urls     = isset( $_POST['_wc_variation_file_urls'][ $variation_id ] ) ? array_map( 'esc_url_raw', array_map( 'trim', $_POST['_wc_variation_file_urls'][ $variation_id ] ) ) : array();
                $file_url_size = sizeof( $file_urls );

                for ( $ii = 0; $ii < $file_url_size; $ii ++ ) {
                    if ( ! empty( $file_urls[ $ii ] ) )
                        $files[ md5( $file_urls[ $ii ] ) ] = array(
                            'name' => $file_names[ $ii ],
                            'file' => $file_urls[ $ii ]
                        );
                }

                // grant permission to any newly added files on any existing orders for this product prior to saving
                do_action( 'woocommerce_process_product_file_download_paths', $post_id, $variation_id, $files );

                update_post_meta( $variation_id, '_downloadable_files', $files );
            } else {
                update_post_meta( $variation_id, '_download_limit', '' );
                update_post_meta( $variation_id, '_download_expiry', '' );
                update_post_meta( $variation_id, '_downloadable_files', '' );
            }

            // Save shipping class
            $variable_shipping_class[ $i ] = ! empty( $variable_shipping_class[ $i ] ) ? (int) $variable_shipping_class[ $i ] : '';
            wp_set_object_terms( $variation_id, $variable_shipping_class[ $i ], 'product_shipping_class');

            // Remove old taxonomies attributes so data is kept up to date
            if ( $variation_id ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND post_id = %d;", $variation_id ) );
                wp_cache_delete( $variation_id, 'post_meta');
            }

            // Update taxonomies
            foreach ( $attributes as $attribute ) {

                if ( $attribute['is_variation'] ) {
                    // Don't use wc_clean as it destroys sanitized characters
                    if ( isset( $_POST[ 'attribute_' . sanitize_title( $attribute['name'] ) ][ $i ] ) )
                        $value = sanitize_title( trim( stripslashes( $_POST[ 'attribute_' . sanitize_title( $attribute['name'] ) ][ $i ] ) ) );
                    else
                        $value = '';

                    update_post_meta( $variation_id, 'attribute_' . sanitize_title( $attribute['name'] ), $value );
                }

            }

            do_action( 'woocommerce_save_product_variation', $variation_id, $i );
        }
    }

    // Update parent if variable so price sorting works and stays in sync with the cheapest child
    WC_Product_Variable::sync( $post_id );

    // Update default attribute options setting
    $default_attributes = array();

    foreach ( $attributes as $attribute ) {
        if ( $attribute['is_variation'] ) {

            // Don't use wc_clean as it destroys sanitized characters
            if ( isset( $_POST[ 'default_attribute_' . sanitize_title( $attribute['name'] ) ] ) )
                $value = sanitize_title( trim( stripslashes( $_POST[ 'default_attribute_' . sanitize_title( $attribute['name'] ) ] ) ) );
            else
                $value = '';

            if ( $value )
                $default_attributes[ sanitize_title( $attribute['name'] ) ] = $value;
        }
    }

    update_post_meta( $post_id, '_default_attributes', $default_attributes );
}



/**
 * Monitors a new order and attempts to create sub-orders
 *
 * If an order contains products from multiple vendor, we can't show the order
 * to each seller dashboard. That's why we need to divide the main order to
 * some sub-orders based on the number of sellers.
 *
 * @param int $parent_order_id
 * @return void
 */
function dokan_create_sub_order( $parent_order_id ) {

    $parent_order = new WC_Order( $parent_order_id );
    $order_items = $parent_order->get_items();

    $sellers = array();
    foreach ($order_items as $item) {
        $seller_id = get_post_field( 'post_author', $item['product_id'] );
        $sellers[$seller_id][] = $item;
    }

    // return if we've only ONE seller
    if ( count( $sellers ) == 1 ) {
        $temp = array_keys( $sellers );
        $seller_id = reset( $temp );
        wp_update_post( array( 'ID' => $parent_order_id, 'post_author' => $seller_id ) );
        return;
    }

    // flag it as it has a suborder
    update_post_meta( $parent_order_id, 'has_sub_order', true );

    // seems like we've got multiple sellers
    foreach ($sellers as $seller_id => $seller_products ) {
        dokan_create_seller_order( $parent_order, $seller_id, $seller_products );
    }
}

add_action( 'woocommerce_checkout_update_order_meta', 'dokan_create_sub_order' );



/**
 * Creates a sub order
 *
 * @param int $parent_order
 * @param int $seller_id
 * @param array $seller_products
 */
function dokan_create_seller_order( $parent_order, $seller_id, $seller_products ) {
    $order_data = apply_filters( 'woocommerce_new_order_data', array(
        'post_type'     => 'shop_order',
        'post_title'    => sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
        'post_status'   => 'publish',
        'ping_status'   => 'closed',
        'post_excerpt'  => isset( $posted['order_comments'] ) ? $posted['order_comments'] : '',
        'post_author'   => $seller_id,
        'post_parent'   => $parent_order->id,
        'post_password' => uniqid( 'order_' )   // Protects the post just in case
    ) );

    $order_id = wp_insert_post( $order_data );

    if ( $order_id && !is_wp_error( $order_id ) ) {

        $order_total = $order_tax = 0;
        $product_ids = array();

        // now insert line items
        foreach ($seller_products as $item) {
            $order_total += (float) $item['line_total'];
            $order_tax += (float) $item['line_tax'];
            $product_ids[] = $item['product_id'];

            $item_id = wc_add_order_item( $order_id, array(
                'order_item_name' => $item['name'],
                'order_item_type' => 'line_item'
            ) );

            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, '_qty', $item['qty'] );
                wc_add_order_item_meta( $item_id, '_tax_class', $item['tax_class'] );
                wc_add_order_item_meta( $item_id, '_product_id', $item['product_id'] );
                wc_add_order_item_meta( $item_id, '_variation_id', $item['variation_id'] );
                wc_add_order_item_meta( $item_id, '_line_subtotal', $item['line_subtotal'] );
                wc_add_order_item_meta( $item_id, '_line_total', $item['line_total'] );
                wc_add_order_item_meta( $item_id, '_line_tax', $item['line_tax'] );
                wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
            }
        } // foreach

        $bill_ship = array(
            '_billing_country', '_billing_first_name', '_billing_last_name', '_billing_company',
            '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_state', '_billing_postcode',
            '_billing_email', '_billing_phone', '_shipping_country', '_shipping_first_name', '_shipping_last_name',
            '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city',
            '_shipping_state', '_shipping_postcode'
        );

        // save billing and shipping address
        foreach ($bill_ship as $val) {
            $order_key = ltrim( $val, '_' );
            update_post_meta( $order_id, $val, $parent_order->$order_key );
        }

        // do shipping
        $shipping_cost = dokan_create_sub_order_shipping( $parent_order, $order_id, $seller_products );

        // add coupons if any
        dokan_create_sub_order_coupon( $parent_order, $order_id, $product_ids );
        $discount = dokan_sub_order_get_total_coupon( $order_id );

        // calculate the total
        $order_in_total = $order_total + $shipping_cost + $order_tax - $discount;

        // set order meta
        update_post_meta( $order_id, '_payment_method',         $parent_order->payment_method );
        update_post_meta( $order_id, '_payment_method_title',   $parent_order->payment_method_title );

        update_post_meta( $order_id, '_order_shipping',         woocommerce_format_decimal( $shipping_cost ) );
        update_post_meta( $order_id, '_order_discount',         woocommerce_format_decimal( $discount ) );
        update_post_meta( $order_id, '_cart_discount',          '0' );
        update_post_meta( $order_id, '_order_tax',              woocommerce_format_decimal( $order_tax ) );
        update_post_meta( $order_id, '_order_shipping_tax',     '0' );
        update_post_meta( $order_id, '_order_total',            woocommerce_format_decimal( $order_in_total ) );
        update_post_meta( $order_id, '_order_key',              apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
        update_post_meta( $order_id, '_customer_user',          $parent_order->customer_user );
        update_post_meta( $order_id, '_order_currency',         get_post_meta( $parent_order->id, '_order_currency', true ) );
        update_post_meta( $order_id, '_prices_include_tax',     $parent_order->prices_include_tax );
        update_post_meta( $order_id, '_customer_ip_address',    get_post_meta( $parent_order->id, '_customer_ip_address', true ) );
        update_post_meta( $order_id, '_customer_user_agent',    get_post_meta( $parent_order->id, '_customer_user_agent', true ) );

        do_action( 'dokan_checkout_update_order_meta', $order_id );

        // Order status
        wp_set_object_terms( $order_id, 'pending', 'shop_order_status' );
    } // if order
}



/**
 * Get discount coupon total from a order
 *
 * @global WPDB $wpdb
 * @param int $order_id
 * @return int
 */
function dokan_sub_order_get_total_coupon( $order_id ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT SUM(oim.meta_value) FROM {$wpdb->prefix}woocommerce_order_itemmeta oim
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON oim.order_item_id = oi.order_item_id
            WHERE oi.order_id = %d AND oi.order_item_type = 'coupon'", $order_id );

    $result = $wpdb->get_var( $sql );
    if ( $result ) {
        return $result;
    }

    return 0;
}



/**
 * Create coupons for a sub-order if neccessary
 *
 * @param WC_Order $parent_order
 * @param int $order_id
 * @param array $product_ids
 * @return type
 */
function dokan_create_sub_order_coupon( $parent_order, $order_id, $product_ids ) {
    $used_coupons = $parent_order->get_used_coupons();

    if ( ! count( $used_coupons ) ) {
        return;
    }

    if ( $used_coupons ) {
        foreach ($used_coupons as $coupon_code) {
            $coupon = new WC_Coupon( $coupon_code );

            if ( $coupon && !is_wp_error( $coupon ) && array_intersect( $product_ids, $coupon->product_ids ) ) {

                // we found some match
                $item_id = wc_add_order_item( $order_id, array(
                    'order_item_name' => $coupon_code,
                    'order_item_type' => 'coupon'
                ) );

                // Add line item meta
                if ( $item_id ) {
                    wc_add_order_item_meta( $item_id, 'discount_amount', isset( WC()->cart->coupon_discount_amounts[ $coupon_code ] ) ? WC()->cart->coupon_discount_amounts[ $coupon_code ] : 0 );
                }
            }
        }
    }
}


/**
 * Create shipping for a sub-order if neccessary
 *
 * @param WC_Order $parent_order
 * @param int $order_id
 * @param array $product_ids
 * @return type
 */
function dokan_create_sub_order_shipping( $parent_order, $order_id, $seller_products ) {
    // take only the first shipping method
    $shipping_methods = $parent_order->get_shipping_methods();
    $shipping_method = is_array( $shipping_methods ) ? reset( $shipping_methods ) : array();

    // bail out if no shipping methods found
    if ( !$shipping_method ) {
        return;
    }

    $shipping_products = array();
    $packages = array();

    // emulate shopping cart for calculating the shipping method
    foreach ($seller_products as $product_item) {
        $product = get_product( $product_item['product_id'] );

        if ( $product->needs_shipping() ) {
            $shipping_products[] = array(
                'product_id' => $product_item['product_id'],
                'variation_id' => $product_item['variation_id'],
                'variation' => '',
                'quantity' => $product_item['qty'],
                'data' => $product,
                'line_total' => $product_item['line_total'],
                'line_tax' => $product_item['line_tax'],
                'line_subtotal' => $product_item['line_subtotal'],
                'line_subtotal_tax' => $product_item['line_subtotal_tax'],
            );
        }
    }

    if ( $shipping_products ) {
        $package = array(
            'contents' => $shipping_products,
            'contents_cost' => array_sum( wp_list_pluck( $shipping_products, 'line_total' ) ),
            'applied_coupons' => array(),
            'destination' => array(
                'country' => $parent_order->shipping_country,
                'state' => $parent_order->shipping_state,
                'postcode' => $parent_order->shipping_postcode,
                'city' => $parent_order->shipping_city,
                'address' => $parent_order->shipping_address_1,
                'address_2' => $parent_order->shipping_address_2,
            )
        );

        $wc_shipping = WC_Shipping::instance();
        $pack = $wc_shipping->calculate_shipping_for_package( $package );

        if ( array_key_exists( $shipping_method['method_id'], $pack['rates'] ) ) {
            $method = $pack['rates'][$shipping_method['method_id']];
            $cost = wc_format_decimal( $method->cost );

            $item_id = wc_add_order_item( $order_id, array(
                'order_item_name'       => $method->label,
                'order_item_type'       => 'shipping'
            ) );

            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, 'method_id', $method->id );
                wc_add_order_item_meta( $item_id, 'cost', $cost );
            }

            return $cost;
        };
    }

    return 0;
}



/**
 * Validates seller registration form from my-account page
 *
 * @param WP_Error $error
 * @return \WP_Error
 */
function dokan_seller_registration_errors( $error ) {
    $allowed_roles = array( 'customer', 'seller' );

    // is the role name allowed or user is trying to manipulate?
    if ( isset( $_POST['role'] ) && !in_array( $_POST['role'], $allowed_roles ) ) {
        return new WP_Error( 'role-error', __( 'Cheating, eh?', 'dokan' ) );
    }

    $role = $_POST['role'];

    if ( $role == 'seller' ) {

        $first_name = trim( $_POST['fname'] );
        if ( empty( $first_name ) ) {
            return new WP_Error( 'fname-error', __( 'Please enter your first name.', 'dokan' ) );
        }

        $last_name = trim( $_POST['lname'] );
        if ( empty( $last_name ) ) {
            return new WP_Error( 'lname-error', __( 'Please enter your last name.', 'dokan' ) );
        }

        $address = trim( $_POST['address'] );
        if ( empty( $address ) ) {
            return new WP_Error( 'address-error', __( 'Please enter your address.', 'dokan' ) );
        }

        $phone = trim( $_POST['phone'] );
        if ( empty( $phone ) ) {
            return new WP_Error( 'phone-error', __( 'Please enter your phone number.', 'dokan' ) );
        }
    }

    return $error;
}

add_filter( 'woocommerce_process_registration_errors', 'dokan_seller_registration_errors' );
add_filter( 'registration_errors', 'dokan_seller_registration_errors' );



/**
 * Inject first and last name to WooCommerce for new seller registraion
 *
 * @param array $data
 * @return array
 */
function dokan_new_customer_data( $data ) {
    $allowed_roles = array( 'customer', 'seller' );
    $role = ( isset( $_POST['role'] ) && in_array( $_POST['role'], $allowed_roles ) ) ? $_POST['role'] : 'customer';

    $data['role'] = $role;

    if ( $role == 'seller' ) {
        $data['first_name'] = strip_tags( $_POST['fname'] );
        $data['last_name'] = strip_tags( $_POST['lname'] );
        $data['user_nicename'] = sanitize_title( $_POST['shopurl'] );
    }

    return $data;
}

add_filter( 'woocommerce_new_customer_data', 'dokan_new_customer_data');



/**
 * Adds default dokan store settings when a new seller registers
 *
 * @param int $user_id
 * @param array $data
 * @return void
 */
function dokan_on_create_seller( $user_id, $data ) {
    if ( $data['role'] != 'seller' ) {
        return;
    }

    $dokan_settings = array(
        'store_name' => $_POST['shopname'],
        'social' => array(),
        'payment' => array(),
        'phone' => $_POST['phone'],
        'show_email' => 'no',
        'address' => $_POST['address'],
        'location' => '',
        'find_address' => '',
        'dokan_category' => '',
        'banner' => 0,
    );

    update_user_meta( $user_id, 'dokan_profile_settings', $dokan_settings );

    Dokan_Email::init()->new_seller_registered_mail( $user_id );
}

add_action( 'woocommerce_created_customer', 'dokan_on_create_seller', 10, 2);



/**
 * Get featured products
 *
 * Shown on homepage
 *
 * @param int $per_page
 * @return \WP_Query
 */
function dokan_get_featured_products( $per_page = 9) {
    $featured_query = new WP_Query( apply_filters( 'dokan_get_featured_products', array(
        'posts_per_page' => $per_page,
        'post_type' => 'product',
        'ignore_sticky_posts' => 1,
        'meta_query' => array(
            array(
                'key' => '_visibility',
                'value' => array('catalog', 'visible'),
                'compare' => 'IN'
            ),
            array(
                'key' => '_featured',
                'value' => 'yes'
            )
        )
    ) ) );

    return $featured_query;
}



/**
 * Get best selling products
 *
 * Shown on homepage
 *
 * @param int $per_page
 * @return \WP_Query
 */
function dokan_get_best_selling_products( $per_page = 8 ) {

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts' => 1,
        'posts_per_page' => $per_page,
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num',
        'meta_query' => array(
            array(
                'key' => '_visibility',
                'value' => array( 'catalog', 'visible' ),
                'compare' => 'IN'
            )
        )
    );

    $best_selling_query = new WP_Query( apply_filters( 'dokan_best_selling_query', $args ) );

    return $best_selling_query;
}



/**
 * Get top rated products
 *
 * Shown on homepage
 *
 * @param int $per_page
 * @return \WP_Query
 */
function dokan_get_top_rated_products( $per_page = 8 ) {

    $args = array(
        'post_type'             => 'product',
        'post_status'           => 'publish',
        'ignore_sticky_posts'   => 1,
        'posts_per_page'        => $per_page,
        'meta_query'            => array(
            array(
                'key'           => '_visibility',
                'value'         => array('catalog', 'visible'),
                'compare'       => 'IN'
            )
        )
    );

    add_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );

    $top_rated_query = new WP_Query( apply_filters( 'dokan_top_rated_query', $args ) );

    remove_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );

    return $top_rated_query;
}



/**
 * Get products on-sale
 *
 * Shown on homepage
 *
 * @param type $per_page
 * @param type $paged
 * @return \WP_Query
 */
function dokan_get_on_sale_products( $per_page = 10, $paged = 1 ) {
    // Get products on sale
    $product_ids_on_sale = wc_get_product_ids_on_sale();

    $args = array(
        'posts_per_page'    => $per_page,
        'no_found_rows'     => 1,
        'paged'             => $paged,
        'post_status'       => 'publish',
        'post_type'         => 'product',
        'post__in'          => array_merge( array( 0 ), $product_ids_on_sale ),
        'meta_query'        => array(
            array(
                'key'       => '_visibility',
                'value'     => array('catalog', 'visible'),
                'compare'   => 'IN'
            ),
            array(
                'key'       => '_stock_status',
                'value'     => 'instock',
                'compare'   => '='
            )
        )
    );

    return new WP_Query( apply_filters( 'dokan_on_sale_products_query', $args ) );
}



/**
 * Get current balance of a seller
 *
 * Total = SUM(net_amount) - SUM(withdraw)
 *
 * @global WPDB $wpdb
 * @param type $seller_id
 * @param type $formatted
 * @return type
 */
function dokan_get_seller_balance( $seller_id, $formatted = true ) {
    global $wpdb;

    $status = dokan_withdraw_get_active_order_status_in_comma();
    $cache_key = 'dokan_seller_balance_' . $seller_id;
    $earning = wp_cache_get( $cache_key, 'dokan' );

    if ( false === $earning ) {
        $sql = "SELECT SUM(net_amount) as earnings,
            (SELECT SUM(amount) FROM {$wpdb->prefix}dokan_withdraw WHERE user_id = %d AND status = 1) as withdraw
            FROM {$wpdb->prefix}dokan_orders
            WHERE seller_id = %d AND order_status IN({$status})";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $seller_id ) );
        $earning = $result->earnings - $result->withdraw;

        wp_cache_set( $cache_key, $earning, 'dokan' );
    }

    if ( $formatted ) {
        return wc_price( $earning );
    }

    return $earning;
}

/**
 * Output Product search forms.
 *
 * @access public
 * @subpackage  Forms
 * @param bool $echo (default: true)
 * @return string
 * @todo This function needs to be broken up in smaller pieces
 */
function get_product_search_form( $echo = true  ) {
    do_action( 'get_product_search_form'  );

    $search_form_template = locate_template( 'product-searchform.php' );
    if ( '' != $search_form_template  ) {
        require $search_form_template;
        return;
    }

    $form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
        <div class="input-group">
            <input type="text" class="form-control" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( 'Search for products', 'dokan' ) . '" />

            <span class="input-group-btn">
                <button type="submit" id="searchsubmit" class="btn btn-primary">'. esc_attr__( 'Search', 'dokan' ) .'</button>
                <input type="hidden" name="post_type" value="product" />
            </span>
        </div>
    </form>';

    if ( $echo  )
        echo apply_filters( 'get_product_search_form', $form );
    else
        return apply_filters( 'get_product_search_form', $form );
}


/**
 * Get seller rating
 *
 * @global WPDB $wpdb
 * @param type $seller_id
 * @return type
 */
function dokan_get_seller_rating( $seller_id ) {
    global $wpdb;

    $sql = "SELECT AVG(cm.meta_value) as average, COUNT(wc.comment_ID) as count FROM $wpdb->posts p
        INNER JOIN $wpdb->comments wc ON p.ID = wc.comment_post_ID
        LEFT JOIN $wpdb->commentmeta cm ON cm.comment_id = wc.comment_ID
        WHERE p.post_author = %d AND p.post_type = 'product' AND p.post_status = 'publish'
        AND ( cm.meta_key = 'rating' OR cm.meta_key IS NULL) AND wc.comment_approved = 1
        ORDER BY wc.comment_post_ID";

    $result = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );

    return array( 'rating' => number_format( $result->average, 2), 'count' => (int) $result->count );
}


/**
 * Get seller rating in a readable rating format
 *
 * @param int $seller_id
 * @return void
 */
function dokan_get_readable_seller_rating( $seller_id ) {
    $rating = dokan_get_seller_rating( $seller_id );

    if ( ! $rating['count'] ) {
        echo __( 'No ratings found yet!', 'dokan' );
        return;
    }

    $long_text = _n( __( '%s rating from %d review', 'dokan' ), __( '%s rating from %d reviews', 'dokan' ), $rating['count'], 'dokan' );
    $text = sprintf( __( 'Rated %s out of %d', 'dokan' ), $rating['rating'], number_format( 5 ) );
    $width = ( $rating['rating']/5 ) * 100;
    ?>
        <span class="seller-rating">
            <span title="<?php echo esc_attr( $text ); ?>" class="star-rating" itemtype="http://schema.org/Rating" itemscope="" itemprop="reviewRating">
                <span class="width" style="width: <?php echo $width; ?>%"></span>
                <span style=""><strong itemprop="ratingValue"><?php echo $rating['rating']; ?></strong></span>
            </span>
        </span>

        <span class="text"><a href="<?php echo dokan_get_review_url( $seller_id ); ?>"><?php printf( $long_text, $rating['rating'], $rating['count'] ); ?></a></span>

    <?php
}
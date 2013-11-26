<?php
/**
 * fix WooCommerce starting div
 *
 * @return void
 */
function dokan_output_content_wrapper() {
?>
<div id="primary" class="content-area">
    <div id="content" class="site-content span9" role="main">
        <?php
}

// remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
// add_action( 'woocommerce_before_main_content', 'dokan_output_content_wrapper' );

/**
 * Required scripts and styles for add/edit screen
 *
 * @return void
 */
function dokan_write_panel_scripts() {
    global $woocommerce, $post;

    $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

    wp_enqueue_script( 'thickbox' );
    wp_enqueue_script( 'woocommerce_admin', $woocommerce->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-core'), $woocommerce->version );
    wp_enqueue_script( 'jquery-ui-datepicker', $woocommerce->plugin_url() . '/assets/js/admin/ui-datepicker.js', array('jquery', 'jquery-ui-core'), $woocommerce->version );
    wp_enqueue_script( 'dokan-writepanel', get_template_directory_uri() . '/js/write-panel.js', array('jquery', 'jquery-ui-datepicker') );
    wp_enqueue_script( 'ajax-chosen', $woocommerce->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array('jquery', 'chosen'), $woocommerce->version );
    wp_enqueue_script( 'chosen', $woocommerce->plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array('jquery'), $woocommerce->version );
    wp_enqueue_script( 'plupload-handlers' );

    wp_enqueue_style( 'thickbox' );
    wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

    $woocommerce_witepanel_params = array(
        'remove_item_notice' => __( "Remove this item? If you have previously reduced this item's stock, or this order was submitted by a customer, will need to manually restore the item's stock.", 'woocommerce' ),
        'remove_attribute' => __( 'Remove this attribute?', 'woocommerce' ),
        'name_label' => __( 'Name', 'woocommerce' ),
        'remove_label' => __( 'Remove', 'woocommerce' ),
        'click_to_toggle' => __( 'Click to toggle', 'woocommerce' ),
        'values_label' => __( 'Value(s)', 'woocommerce' ),
        'text_attribute_tip' => __( 'Enter some text, or some attributes by pipe (|) separating values.', 'woocommerce' ),
        'visible_label' => __( 'Visible on the product page', 'woocommerce' ),
        'used_for_variations_label' => __( 'Used for variations', 'woocommerce' ),
        'new_attribute_prompt' => __( 'Enter a name for the new attribute term:', 'woocommerce' ),
        'meta_name' => __( 'Meta Name', 'woocommerce' ),
        'meta_value' => __( 'Meta Value', 'woocommerce' ),
        'no_customer_selected' => __( 'No customer selected', 'woocommerce' ),
        'tax_label' => __( 'Tax Label:', 'woocommerce' ),
        'compound_label' => __( 'Compound:', 'woocommerce' ),
        'cart_tax_label' => __( 'Cart Tax:', 'woocommerce' ),
        'shipping_tax_label' => __( 'Shipping Tax:', 'woocommerce' ),
        'plugin_url' => $woocommerce->plugin_url(),
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'add_order_item_nonce' => wp_create_nonce( "add-order-item" ),
        'add_attribute_nonce' => wp_create_nonce( "add-attribute" ),
        'calc_totals_nonce' => wp_create_nonce( "calc-totals" ),
        'get_customer_details_nonce' => wp_create_nonce( "get-customer-details" ),
        'search_products_nonce' => wp_create_nonce( "search-products" ),
        'calendar_image' => $woocommerce->plugin_url() . '/assets/images/calendar.png',
        'post_id' => $post->ID,
        'media_upload_page' => admin_url('media-upload.php?post_id=0')
    );

    wp_localize_script( 'dokan-writepanel', 'woocommerce_writepanel_params', $woocommerce_witepanel_params );
}


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
    <div id="variable_product_options" class="panel wc-metaboxes-wrapper">
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
                        '_price',
                        '_regular_price',
                        '_sale_price',
                        '_weight',
                        '_length',
                        '_width',
                        '_height',
                        '_download_limit',
                        '_download_expiry',
                        '_file_paths',
                        '_downloadable',
                        '_virtual',
                        '_thumbnail_id',
                        '_sale_price_dates_from',
                        '_sale_price_dates_to'
                    );

                    foreach ( $variation_fields as $field )
                        $$field = isset( $variation_data[ $field ][0] ) ? $variation_data[ $field ][0] : '';

                    // Tax class handling
                    $_tax_class = isset( $variation_data['_tax_class'][0] ) ? $variation_data['_tax_class'][0] : null;

                    // Price backwards compat
                    if ( $_regular_price == '' && $_price )
                        $_regular_price = $_price;

                    // Get image
                    $image = '';
                    $image_id = absint( $_thumbnail_id );
                    if ( $image_id )
                        $image = wp_get_attachment_url( $image_id );

                    // Format file paths
                    $_file_paths = maybe_unserialize( $_file_paths );
                    if ( is_array( $_file_paths ) )
                        $_file_paths = implode( "\n", $_file_paths );

                    include dirname( __FILE__ ) . '/variation-admin-html.php';

                    $loop++;
                }
                ?>
            </div> <!-- .woocommerce_variations -->

            <p class="toolbar">

                <button type="button" class="button button-primary add_variation" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Add Variation', 'woocommerce' ); ?></button>

                <button type="button" class="button link_all_variations" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Link all variations', 'woocommerce' ); ?></button>

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
                        echo '<select name="default_attribute_' . sanitize_title( $attribute['name'] ) . '"><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( $woocommerce->attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

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
    jQuery(function(){

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
                action: 'woocommerce_add_variation',
                post_id: <?php echo $post->ID; ?>,
                loop: loop,
                security: '<?php echo wp_create_nonce("add-variation"); ?>'
            };

            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                jQuery('.woocommerce_variations').append( response );

                /*
                jQuery(".tips").tipTip({
                    'attribute' : 'data-tip',
                    'fadeIn' : 50,
                    'fadeOut' : 50
                });
                */

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
                    action: 'woocommerce_link_all_variations',
                    post_id: <?php echo $post->ID; ?>,
                    security: '<?php echo wp_create_nonce("link-variations"); ?>'
                };

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                    var count = parseInt( response );

                    if (count==1) {
                        alert( count + ' <?php echo esc_js( __( "variation added", 'woocommerce' ) ); ?>');
                    } else if (count==0 || count>1) {
                        alert( count + ' <?php echo esc_js( __( "variations added", 'woocommerce' ) ); ?>');
                    } else {
                        alert('<?php echo esc_js( __( "No variations added", 'woocommerce' ) ); ?>');
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
            var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to remove this variation?', 'woocommerce' ) ); ?>');
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

        jQuery('.wc-metaboxes-wrapper').on('click', 'a.bulk_edit', function(event){
            var field_to_edit = jQuery('select#field_to_edit').val();

            if ( field_to_edit == 'toggle_enabled' ) {
                var checkbox = jQuery('input[name^="variable_enabled"]');
                checkbox.attr('checked', !checkbox.attr('checked'));
                return false;
            }
            else if ( field_to_edit == 'toggle_downloadable' ) {
                var checkbox = jQuery('input[name^="variable_is_downloadable"]');
                checkbox.attr('checked', !checkbox.attr('checked'));
                jQuery('input.variable_is_downloadable').change();
                return false;
            }
            else if ( field_to_edit == 'toggle_virtual' ) {
                var checkbox = jQuery('input[name^="variable_is_virtual"]');
                checkbox.attr('checked', !checkbox.attr('checked'));
                jQuery('input.variable_is_virtual').change();
                return false;
            }
            else if ( field_to_edit == 'delete_all' ) {

                var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'woocommerce' ) ); ?>');
                if (answer){

                    var answer = confirm('<?php echo esc_js( __( 'Last warning, are you sure?', 'woocommerce' ) ); ?>');

                    if (answer) {

                        var variation_ids = [];

                        jQuery('.woocommerce_variations .woocommerce_variation').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

                        jQuery('.woocommerce_variations .woocommerce_variation .remove_variation').each(function(){

                            var variation = jQuery(this).attr('rel');
                            if (variation>0) {
                                variation_ids.push(variation);
                            }
                        });

                        var data = {
                            action: 'woocommerce_remove_variations',
                            variation_ids: variation_ids,
                            security: '<?php echo wp_create_nonce("delete-variations"); ?>'
                        };

                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                            jQuery('.woocommerce_variations .woocommerce_variation').fadeOut('300', function(){
                                jQuery('.woocommerce_variations .woocommerce_variation').remove();
                            });
                        });

                    }

                }
                return false;
            }
            else {

                var input_tag = jQuery('select#field_to_edit :selected').attr('rel') ? jQuery('select#field_to_edit :selected').attr('rel') : 'input';

                var value = prompt("<?php echo esc_js( __( 'Enter a value', 'woocommerce' ) ); ?>");
                jQuery(input_tag + '[name^="' + field_to_edit + '["]').val( value ).change();
                return false;

            }
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

        wp.media.view.settings.post = <?php echo json_encode( array( 'param' => 'dokam', 'post_id' => 36) ); // big juicy hack. ?>;

        jQuery('#variable_product_options').on('click', '.upload_image_button', function( event ) {

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
    $woocommerce->add_inline_js( $javascript );
}
<?php
/**
 * Ajax handler for Dokan
 *
 * @package Dokan
 */
class Dokan_Ajax {

    /**
     * Singleton object
     *
     * @staticvar boolean $instance
     * @return \self
     */
    public static function init() {

        static $instance = false;

        if ( !$instance ) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * Init ajax handlers
     *
     * @return void
     */
    function init_ajax() {
        //withdraw note
        $withdraw = Dokan_Template_Withdraw::init();
        add_action( 'wp_ajax_note', array( $withdraw, 'note_update' ) );
        add_action( 'wp_ajax_withdraw_ajax_submission', array( $withdraw, 'withdraw_ajax' ) );

        // reviews
        $reviews = Dokan_Template_reviews::init();
        add_action( 'wp_ajax_wpuf_comment_status', array( $reviews, 'ajax_comment_status' ) );
        add_action( 'wp_ajax_wpuf_update_comment', array( $reviews, 'ajax_update_comment' ) );

        //settings
        $settings = Dokan_Template_Settings::init();
        add_action( 'wp_ajax_dokan_settings', array( $settings, 'ajax_settings' ) );

        add_action( 'wp_ajax_dokan-mark-order-complete', array( $this, 'complete_order' ) );
        add_action( 'wp_ajax_dokan-mark-order-processing', array( $this, 'process_order' ) );
        add_action( 'wp_ajax_dokan_grant_access_to_download', array( $this, 'grant_access_to_download' ) );

        add_action( 'wp_ajax_dokan_change_status', array( $this, 'change_order_status' ) );

        add_action( 'wp_ajax_dokan_contact_seller', array( $this, 'contact_seller' ) );

        add_action( 'wp_ajax_dokan_add_variation', array( $this, 'add_variation' ) );
        add_action( 'wp_ajax_dokan_link_all_variations', array( $this, 'link_all_variations' ) );
        add_action( 'wp_ajax_dokan_save_attributes', array( $this, 'save_attributes' ) );

        add_action( 'wp_ajax_dokan_toggle_seller', array( $this, 'toggle_seller_status' ) );


        add_action( 'wp_ajax_nopriv_shop_url', array($this, 'shop_url_check') );
    }

    /**
     * chop url check
     */
    function shop_url_check() {
        
        if ( !wp_verify_nonce( $_POST['_nonce'], 'dokan_reviews' ) ) {
            wp_send_json_error( array(
                'type' => 'nonce',
                'message' => 'Are you cheating?'
            ) );
        }

        $url_slug = $_POST['url_slug'];

        $check = true;

        $user = get_user_by( 'slug', $url_slug );

        if ( $user != '' ) {
            $check = false;
        }

        echo $check;


    }

    /**
     * Mark a order as complete
     *
     * Fires from seller dashboard in frontend
     */
    function complete_order() {
        if ( !is_admin() ) {
            die();
        }

        if ( !current_user_can( 'dokandar' ) || dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) != 'on' ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        if ( !check_admin_referer( 'dokan-mark-order-complete' ) ) {
            wp_die( __( 'You have taken too long. Please go back and retry.', 'dokan' ) );
        }

        $order_id = isset($_GET['order_id']) && (int) $_GET['order_id'] ? (int) $_GET['order_id'] : '';
        if ( !$order_id ) {
            die();
        }

        if ( !dokan_is_seller_has_order( get_current_user_id(), $order_id ) ) {
            wp_die( __( 'You do not have permission to change this order', 'dokan' ) );
        }

        $order = new WC_Order( $order_id );
        $order->update_status( 'completed' );

        wp_safe_redirect( wp_get_referer() );
        die();
    }

    /**
     * Mark a order as processing
     *
     * Fires from frontend seller dashboard
     */
    function process_order() {
        if ( !is_admin() ) {
            die();
        }

        if ( !current_user_can( 'edit_shop_orders' ) || dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) != 'on' ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        if ( !check_admin_referer( 'dokan-mark-order-processing' ) ) {
            wp_die( __( 'You have taken too long. Please go back and retry.', 'dokan' ) );
        }

        $order_id = isset( $_GET['order_id'] ) && (int) $_GET['order_id'] ? (int) $_GET['order_id'] : '';
        if ( !$order_id ) {
            die();
        }

        if ( !dokan_is_seller_has_order( get_current_user_id(), $order_id ) ) {
            wp_die( __( 'You do not have permission to change this order', 'dokan' ) );
        }

        $order = new WC_Order( $order_id );
        $order->update_status( 'processing' );

        wp_safe_redirect( wp_get_referer() );
    }

    /**
     * Grant download permissions via ajax function
     *
     * @access public
     * @return void
     */
    function grant_access_to_download() {

        check_ajax_referer( 'grant-access', 'security' );

        global $wpdb;

        $order_id       = intval( $_POST['order_id'] );
        $product_ids    = $_POST['product_ids'];
        $loop           = intval( $_POST['loop'] );
        $file_counter   = 0;
        $order          = new WC_Order( $order_id );

        if ( ! is_array( $product_ids ) ) {
            $product_ids = array( $product_ids );
        }

        foreach ( $product_ids as $product_id ) {
            $product    = get_product( $product_id );
            $files      = $product->get_files();

            if ( ! $order->billing_email )
                die();

            if ( $files ) {
                foreach ( $files as $download_id => $file ) {
                    if ( $inserted_id = wc_downloadable_file_permission( $download_id, $product_id, $order ) ) {

                        // insert complete - get inserted data
                        $download = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE permission_id = %d", $inserted_id ) );

                        $loop ++;
                        $file_counter ++;

                        if ( isset( $file['name'] ) ) {
                            $file_count = $file['name'];
                        } else {
                            $file_count = sprintf( __( 'File %d', 'woocommerce' ), $file_counter );
                        }

                        include dirname( dirname( __FILE__ ) ) . '/templates/orders/order-download-permission-html.php';
                    }
                }
            }
        }

        die();
    }

    /**
     * Add variation via ajax function
     *
     * @return void
     */
    public function add_variation() {
        global $woocommerce;

        check_ajax_referer( 'add-variation', 'security' );

        $post_id = intval( $_POST['post_id'] );
        $loop = intval( $_POST['loop'] );

        $variation = array(
            'post_title'    => 'Product #' . $post_id . ' Variation',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_parent'   => $post_id,
            'post_type'     => 'product_variation'
        );

        $variation_id = wp_insert_post( $variation );

        do_action( 'woocommerce_create_product_variation', $variation_id );

        if ( $variation_id ) {

            $variation_post_status = 'publish';
            $variation_data = get_post_meta( $variation_id );
            $variation_data['variation_post_id'] = $variation_id;

            // Get attributes
            $attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

            // Get tax classes
            $tax_classes = array_filter(array_map('trim', explode("\n", get_option('woocommerce_tax_classes'))));
            $tax_class_options = array();
            $tax_class_options['parent'] =__( 'Same as parent', 'woocommerce' );
            $tax_class_options[''] = __( 'Standard', 'woocommerce' );
            if ($tax_classes) foreach ( $tax_classes as $class )
                $tax_class_options[sanitize_title($class)] = $class;

            // Get parent data
            $parent_data = array(
                'id'        => $post_id,
                'attributes' => $attributes,
                'tax_class_options' => $tax_class_options,
                'sku'       => get_post_meta( $post_id, '_sku', true ),
                'weight'    => get_post_meta( $post_id, '_weight', true ),
                'length'    => get_post_meta( $post_id, '_length', true ),
                'width'     => get_post_meta( $post_id, '_width', true ),
                'height'    => get_post_meta( $post_id, '_height', true ),
                'tax_class' => get_post_meta( $post_id, '_tax_class', true )
            );

            if ( ! $parent_data['weight'] )
                $parent_data['weight'] = '0.00';

            if ( ! $parent_data['length'] )
                $parent_data['length'] = '0';

            if ( ! $parent_data['width'] )
                $parent_data['width'] = '0';

            if ( ! $parent_data['height'] )
                $parent_data['height'] = '0';

            $_tax_class = '';
            $_downloadable_files = '';
            $image_id = 0;
            $variation = get_post( $variation_id ); // Get the variation object

            // include( 'admin/post-types/meta-boxes/views/html-variation-admin.php' );
            include DOKAN_INC_DIR . '/woo-views/variation-admin-html.php';
        }

        die();
    }

    /**
     * Link all variations via ajax function
     */
    public function link_all_variations() {

        if ( ! defined( 'WC_MAX_LINKED_VARIATIONS' ) ) {
            define( 'WC_MAX_LINKED_VARIATIONS', 49 );
        }

        check_ajax_referer( 'link-variations', 'security' );

        @set_time_limit(0);

        $post_id = intval( $_POST['post_id'] );

        if ( ! $post_id ) die();

        $variations = array();

        $_product = get_product( $post_id, array( 'product_type' => 'variable' ) );

        // Put variation attributes into an array
        foreach ( $_product->get_attributes() as $attribute ) {

            if ( ! $attribute['is_variation'] ) continue;

            $attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

            if ( $attribute['is_taxonomy'] ) {
                $post_terms = wp_get_post_terms( $post_id, $attribute['name'] );
                $options = array();
                foreach ( $post_terms as $term ) {
                    $options[] = $term->slug;
                }
            } else {
                $options = explode( WC_DELIMITER, $attribute['value'] );
            }

            $options = array_map( 'sanitize_title', array_map( 'trim', $options ) );

            $variations[ $attribute_field_name ] = $options;
        }

        // Quit out if none were found
        if ( sizeof( $variations ) == 0 ) die();

        // Get existing variations so we don't create duplicates
        $available_variations = array();

        foreach( $_product->get_children() as $child_id ) {
            $child = $_product->get_child( $child_id );

            if ( ! empty( $child->variation_id ) ) {
                $available_variations[] = $child->get_variation_attributes();
            }
        }

        // Created posts will all have the following data
        $variation_post_data = array(
            'post_title' => 'Product #' . $post_id . ' Variation',
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'post_parent' => $post_id,
            'post_type' => 'product_variation'
        );

        // Now find all combinations and create posts
        if ( ! function_exists( 'array_cartesian' ) ) {
            /**
             * @param array $input
             * @return array
             */
            function array_cartesian( $input ) {
                $result = array();

                while ( list( $key, $values ) = each( $input ) ) {
                    // If a sub-array is empty, it doesn't affect the cartesian product
                    if ( empty( $values ) ) {
                        continue;
                    }

                    // Special case: seeding the product array with the values from the first sub-array
                    if ( empty( $result ) ) {
                        foreach ( $values as $value ) {
                            $result[] = array( $key => $value );
                        }
                    }
                    else {
                        // Second and subsequent input sub-arrays work like this:
                        //   1. In each existing array inside $product, add an item with
                        //      key == $key and value == first item in input sub-array
                        //   2. Then, for each remaining item in current input sub-array,
                        //      add a copy of each existing array inside $product with
                        //      key == $key and value == first item in current input sub-array

                        // Store all items to be added to $product here; adding them on the spot
                        // inside the foreach will result in an infinite loop
                        $append = array();
                        foreach( $result as &$product ) {
                            // Do step 1 above. array_shift is not the most efficient, but it
                            // allows us to iterate over the rest of the items with a simple
                            // foreach, making the code short and familiar.
                            $product[ $key ] = array_shift( $values );

                            // $product is by reference (that's why the key we added above
                            // will appear in the end result), so make a copy of it here
                            $copy = $product;

                            // Do step 2 above.
                            foreach( $values as $item ) {
                                $copy[ $key ] = $item;
                                $append[] = $copy;
                            }

                            // Undo the side effecst of array_shift
                            array_unshift( $values, $product[ $key ] );
                        }

                        // Out of the foreach, we can add to $results now
                        $result = array_merge( $result, $append );
                    }
                }

                return $result;
            }
        }

        $variation_ids = array();
        $added = 0;
        $possible_variations = array_cartesian( $variations );

        foreach ( $possible_variations as $variation ) {

            // Check if variation already exists
            if ( in_array( $variation, $available_variations ) )
                continue;

            $variation_id = wp_insert_post( $variation_post_data );

            $variation_ids[] = $variation_id;

            foreach ( $variation as $key => $value ) {
                update_post_meta( $variation_id, $key, $value );
            }

            $added++;

            do_action( 'product_variation_linked', $variation_id );

            if ( $added > WC_MAX_LINKED_VARIATIONS )
                break;
        }

        wc_delete_product_transients( $post_id );

        echo $added;

        die();
    }

    /**
     * Update a order status
     *
     * @return void
     */
    function change_order_status() {

        check_ajax_referer( 'dokan_change_status' );

        $order_id = intval( $_POST['order_id'] );
        $order_status = $_POST['order_status'];

        $order = new WC_Order( $order_id );
        $order->update_status( $order_status );

        $status_class = dokan_get_order_status_class( $order_status );
        echo '<label class="label label-' . $status_class . '">' . $order_status . '</label>';
        exit;
    }

    /**
     * Seller store page email contact form handler
     *
     * Catches the form submission from store page
     */
    function contact_seller() {
        $posted = $_POST;

        check_ajax_referer( 'dokan_contact_seller' );
        // print_r($posted);

        $seller = get_user_by( 'id', (int) $posted['seller_id'] );

        if ( !$seller ) {
            $message = sprintf( '<div class="alert alert-success">%s</div>', __( 'Something went wrong!', 'dokan' ) );
            wp_send_json_error( $message );
        }

        $contact_name = trim( strip_tags( $posted['name'] ) );

        Dokan_Email::init()->contact_seller( $seller->user_email, $contact_name, $posted['email'], $posted['message'] );

        $success = sprintf( '<div class="alert alert-success">%s</div>', __( 'Email sent successfully!', 'dokan' ) );
        wp_send_json_success( $success );
        exit;
    }

    /**
     * Save attributes from edit product page
     *
     * @return void
     */
    function save_attributes() {

        // check_ajax_referer( 'save-attributes', 'security' );

        // Get post data
        parse_str( $_POST['data'], $data );
        $post_id = absint( $_POST['post_id'] );

        // Save Attributes
        $attributes = array();

        if ( isset( $data['attribute_names'] ) ) {

            $attribute_names  = array_map( 'stripslashes', $data['attribute_names'] );
            $attribute_values = isset( $data['attribute_values'] ) ? $data['attribute_values'] : array();

            if ( isset( $data['attribute_visibility'] ) )
                $attribute_visibility = $data['attribute_visibility'];

            if ( isset( $data['attribute_variation'] ) )
                $attribute_variation = $data['attribute_variation'];

            $attribute_is_taxonomy = $data['attribute_is_taxonomy'];
            $attribute_position = $data['attribute_position'];

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
                            $values = array_map( 'stripslashes', array_map( 'strip_tags', explode( WC_DELIMITER, $attribute_values[ $i ] ) ) );
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
                            'name'          => wc_clean( $attribute_names[ $i ] ),
                            'value'         => '',
                            'position'      => $attribute_position[ $i ],
                            'is_visible'    => $is_visible,
                            'is_variation'  => $is_variation,
                            'is_taxonomy'   => $is_taxonomy
                        );
                    }

                } elseif ( isset( $attribute_values[ $i ] ) ) {

                    // Text based, separate by pipe
                    $values = implode( ' ' . WC_DELIMITER . ' ', array_map( 'wc_clean', array_map( 'stripslashes', $attribute_values[ $i ] ) ) );

                    // Custom attribute - Add attribute to array and set the values
                    $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                        'name'          => wc_clean( $attribute_names[ $i ] ),
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

        die();
    }

    /**
     * Enable/disable seller selling capability from admin seller listing page
     *
     * @return type
     */
    function toggle_seller_status() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $status = sanitize_text_field( $_POST['type'] );

        if ( $user_id && in_array( $status, array( 'yes', 'no' ) ) ) {
            update_user_meta( $user_id, 'dokan_enable_selling', $status );

            if ( $status == 'no' ) {
                $this->make_products_pending( $user_id );
            }
        }
        exit;
    }

    /**
     * Make all the products to pending once a seller is deactivated for selling
     *
     * @param int $seller_id
     */
    function make_products_pending( $seller_id ) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $seller_id,
            'orderby' => 'post_date',
            'order' => 'DESC'
        );

        $product_query = new WP_Query( $args );
        $products = $product_query->get_posts();

        if ( $products ) {
            foreach ($products as $pro) {
                wp_update_post( array( 'ID' => $pro->ID, 'post_status' => 'pending' ) );
            }
        }
    }
}
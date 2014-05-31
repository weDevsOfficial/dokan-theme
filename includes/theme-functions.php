<?php

require_once dirname(__FILE__) . '/order-functions.php';
require_once dirname(__FILE__) . '/withdraw-functions.php';

/**
 * Enqueue report related scripts
 *
 * @return void
 */
function dokan_reports_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-chart' );
    wp_enqueue_script( 'jquery-flot' );
    wp_enqueue_script( 'jquery-flot-time' );
    wp_enqueue_script( 'jquery-flot-pie' );
    wp_enqueue_script( 'jquery-flot-stack' );

    wp_enqueue_style( 'jquery-ui' );
}


/**
 * Includes frontend-dashboard scripts for seller
 *
 * @return void
 */
function dokan_frontend_dashboard_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'underscore' );
    wp_enqueue_script( 'post' );
    wp_enqueue_media();
    wp_enqueue_script( 'dokan-product-editor' );
    wp_enqueue_script( 'chosen' );
    wp_enqueue_script( 'reviews' );
    wp_enqueue_script( 'dokan-order' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );

    wp_enqueue_style( 'jquery-ui' );
    wp_enqueue_style( 'chosen-style' );
}


/**
 * Check if a user is seller
 *
 * @param int $user_id
 * @return boolean
 */
function dokan_is_user_seller( $user_id ) {
    if ( !user_can( $user_id, 'dokandar' ) ) {
        return false;
    }

    return true;
}


/**
 * Check if current user is the product author
 *
 * @global WP_Post $post
 * @param int $product_id
 * @return boolean
 */
function dokan_is_product_author( $product_id = 0 ) {
    global $post;

    if ( !$product_id ) {
        $author = $post->post_author;
    } else {
        $author = get_post_field( 'post_author', $product_id );
    }

    if ( $author == get_current_user_id() ) {
        return true;
    }

    return false;
}


/**
 * Redirect to login page if not already logged in
 *
 * @return void
 */
function dokan_redirect_login() {
    if ( ! is_user_logged_in() ) {
        wp_redirect( dokan_get_page_url( 'myaccount', 'woocommerce' ) );
        exit;
    }
}



/**
 * If the current user is not seller, redirect to homepage
 *
 * @param string $redirect
 */
function dokan_redirect_if_not_seller( $redirect = '' ) {
    if ( !dokan_is_user_seller( get_current_user_id() ) ) {
        $redirect = empty( $redirect ) ? home_url( '/' ) : $redirect;

        wp_redirect( $redirect );
        exit;
    }
}



/**
 * Handles the product delete action
 *
 * @return void
 */
function dokan_delete_product_handler() {
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan-delete-product' ) {
        $product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;

        if ( !$product_id ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan-delete-product' ) ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        if ( !dokan_is_product_author( $product_id ) ) {
            wp_redirect( add_query_arg( array( 'message' => 'error' ), get_permalink() ) );
            return;
        }

        wp_delete_post( $product_id );
        wp_redirect( add_query_arg( array( 'message' => 'product_deleted' ), get_permalink() ) );
        exit;
    }
}



/**
 * Count post type from a user
 *
 * @global WPDB $wpdb
 * @param string $post_type
 * @param int $user_id
 * @return array
 */
function dokan_count_posts( $post_type, $user_id ) {
    global $wpdb;

    $cache_key = 'dokan-count-' . $post_type . '-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( false === $counts ) {
        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d GROUP BY post_status";
        $results = $wpdb->get_results( $wpdb->prepare( $query, $post_type, $user_id ), ARRAY_A );
        $counts = array_fill_keys( get_post_stati(), 0 );

        $total = 0;
        foreach ( $results as $row ) {
            $counts[ $row['post_status'] ] = (int) $row['num_posts'];
            $total += (int) $row['num_posts'];
        }

        $counts['total'] = $total;
        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}



/**
 * Get comment count based on post type and user id
 *
 * @global WPDB $wpdb
 * @global WP_User $current_user
 * @param string $post_type
 * @param int $user_id
 * @return array
 */
function dokan_count_comments( $post_type, $user_id ) {
    global $wpdb, $current_user;

    $cache_key = 'dokan-count-comments-' . $post_type . '-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( $counts === false ) {
        $query = "SELECT c.comment_approved, COUNT( * ) AS num_comments
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author = %d AND
                p.post_status = 'publish' AND
                c.comment_post_ID = p.ID AND
                p.post_type = %s
            GROUP BY c.comment_approved";

        $count = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $post_type ), ARRAY_A );

        $counts = array('moderated' => 0, 'approved' => 0, 'spam' => 0, 'trash' => 0, 'total' => 0);
        $statuses = array('0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed');
        $total = 0;
        foreach ($count as $row) {
            if ( isset( $statuses[$row['comment_approved']] ) ) {
                $counts[$statuses[$row['comment_approved']]] = (int) $row['num_comments'];
                $total += (int) $row['num_comments'];
            }
        }
        $counts['total'] = $total;

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}



/**
 * Get total pageview for a seller
 *
 * @global WPDB $wpdb
 * @param int $seller_id
 * @return int
 */
function dokan_author_pageviews( $seller_id ) {
    global $wpdb;

    $cache_key = 'dokan-pageview-' . $seller_id;
    $pageview = wp_cache_get( $cache_key, 'dokan' );

    if ( $pageview === false ) {
        $sql = "SELECT SUM(meta_value) as pageview
            FROM {$wpdb->postmeta} AS meta
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = meta.post_id
            WHERE meta.meta_key = 'pageview' AND p.post_author = %d AND p.post_status IN ('publish', 'pending', 'draft')";

        $count = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $pageview = $count->pageview;

        wp_cache_set( $cache_key, $pageview, 'dokan' );
    }

    return $pageview;
}


/**
 * Get total sales amount of a seller
 *
 * @global WPDB $wpdb
 * @param int $seller_id
 * @return float
 */
function dokan_author_total_sales( $seller_id ) {
    global $wpdb;

    $cache_key = 'dokan-earning-' . $seller_id;
    $earnings = wp_cache_get( $cache_key, 'dokan' );

    if ( $earnings === false ) {

        $sql = "SELECT SUM(oim.meta_value) as earnings
                FROM {$wpdb->prefix}woocommerce_order_items AS oi
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oim.order_item_id = oi.order_item_id
                LEFT JOIN {$wpdb->prefix}dokan_orders do ON oi.order_id = do.order_id
                WHERE do.seller_id = %d AND oim.meta_key = '_line_total' AND do.order_status IN ('completed', 'processing', 'on-hold')";

        $count = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $earnings = $count->earnings;

        wp_cache_set( $cache_key, $earnings, 'dokan' );
    }

    return $earnings;
}



/**
 * Generate dokan sync table
 *
 * @global WPDB $wpdb
 */
function dokan_generate_sync_table() {
    global $wpdb;

    $sql = "SELECT oi.order_id, p.ID as product_id, p.post_title, p.post_author as seller_id,
                oim2.meta_value as order_total, terms.name as order_status
            FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oim2.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            LEFT JOIN {$wpdb->term_relationships} rel ON oi.order_id = rel.object_id
            LEFT JOIN {$wpdb->term_taxonomy} tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
            LEFT JOIN {$wpdb->terms} terms ON tax.term_id = terms.term_id
            WHERE
                oim.meta_key = '_product_id' AND
                oim2.meta_key = '_line_total'
            GROUP BY oi.order_id";

    $orders = $wpdb->get_results( $sql );
    $table_name = $wpdb->prefix . 'dokan_orders';

    $wpdb->query( 'TRUNCATE TABLE ' . $table_name );

    if ( $orders ) {
        foreach ($orders as $order) {
            $percentage = dokan_get_seller_percentage( $order->seller_id );

            $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $order->order_id,
                    'seller_id' => $order->seller_id,
                    'order_total' => $order->order_total,
                    'net_amount' => ($order->order_total * $percentage)/100,
                    'order_status' => $order->order_status,
                ),
                array(
                    '%d',
                    '%d',
                    '%f',
                    '%f',
                    '%s',
                )
            );
        } // foreach
    } // if
}


if ( !function_exists( 'dokan_get_seller_percentage' ) ) :

/**
 * Get store seller percentage settings
 *
 * @param int $seller_id
 * @return int
 */
function dokan_get_seller_percentage( $seller_id = 0 ) {
    $global_percentage = (int) dokan_get_option( 'seller_percentage', 'dokan_selling', '90' );

    if ( ! $seller_id ) {
        return $global_percentage;
    }

    $seller_percentage = (int) get_user_meta( $seller_id, 'dokan_seller_percentage', true );
    if ( $seller_percentage ) {
        return $seller_percentage;
    }

    return $global_percentage;
}

endif;

/**
 * Get product status based on user id and settings
 *
 * @return string
 */
function dokan_get_new_post_status() {
    $user_id = get_current_user_id();

    // trusted seller
    if ( dokan_is_seller_trusted( $user_id ) ) {
        return 'publish';
    }

    // if not trusted, send the option
    $status = dokan_get_option( 'product_status', 'dokan_selling', 'pending' );

    return $status;
}


/**
 * Function to get the client ip address
 *
 * @return string
 */
function dokan_get_client_ip() {
    $ipaddress = '';

    if ( getenv( 'HTTP_CLIENT_IP' ) )
        $ipaddress = getenv( 'HTTP_CLIENT_IP' );
    else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
        $ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' & quot );
    else if ( getenv( 'HTTP_X_FORWARDED' ) )
        $ipaddress = getenv( 'HTTP_X_FORWARDED' );
    else if ( getenv( 'HTTP_FORWARDED_FOR' ) )
        $ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
    else if ( getenv( 'HTTP_X_CLUSTER_CLIENT_IP' ) )
        $ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
    else if ( getenv( 'HTTP_FORWARDED' ) )
        $ipaddress = getenv( 'HTTP_FORWARDED' );
    else if ( getenv( 'REMOTE_ADDR' ) )
        $ipaddress = getenv( 'REMOTE_ADDR' );
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}



/**
 * Datetime format helper function
 *
 * @param string $datetime
 * @return string
 */
function dokan_format_time( $datetime ) {
    $timestamp = strtotime( $datetime );

    $date_format = get_option( 'date_format' );
    $time_format = get_option( 'time_format' );

    return date_i18n( $date_format . ' ' . $time_format, $timestamp );
}


/**
 * generate a input box based on arguments
 *
 * @param int $post_id
 * @param string $meta_key
 * @param array $attr
 * @param string $type
 */
function dokan_post_input_box( $post_id, $meta_key, $attr = array(), $type = 'text'  ) {
    $placeholder = isset( $attr['placeholder'] ) ? esc_attr( $attr['placeholder'] ) : '';
    $class = isset( $attr['class'] ) ? esc_attr( $attr['class'] ) : 'form-control';
    $name = isset( $attr['name'] ) ? esc_attr( $attr['name'] ) : $meta_key;
    $value = isset( $attr['value'] ) ? $attr['value'] : get_post_meta( $post_id, $meta_key, true );
    $size = isset( $attr['size'] ) ? $attr['size'] : 30;

    switch ($type) {
        case 'text':
            ?>
            <input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>">
            <?php
            break;

        case 'textarea':
            $rows = isset( $attr['rows'] ) ? absint( $attr['rows'] ) : 4;
            ?>
            <textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" rows="<?php echo $rows; ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>"><?php echo esc_textarea( $value ); ?></textarea>
            <?php
            break;

        case 'checkbox':
            $label = isset( $attr['label'] ) ? $attr['label'] : '';
            ?>

            <label class="checkbox-inline" for="<?php echo $name; ?>">
                <input name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $value; ?>" type="checkbox"<?php checked( $value, 'yes' ); ?>>
                <?php echo $label; ?>
            </label>

            <?php
            break;

        case 'select':
            $options = is_array( $attr['options'] ) ? $attr['options'] : array();
            ?>
            <select name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $class; ?>">
                <?php foreach ($options as $key => $label) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>"<?php selected( $value, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <?php
            break;

        case 'number':
            $min = isset( $attr['min'] ) ? $attr['min'] : 0;
            $step = isset( $attr['step'] ) ? $attr['step'] : 'any';
            ?>
            <input type="number" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo $class; ?>" placeholder="<?php echo $placeholder; ?>" min="<?php echo esc_attr( $min ); ?>" step="<?php echo esc_attr( $step ); ?>" size="<?php echo esc_attr( $size ); ?>">
            <?php
            break;
    }
}



/**
 * Get user friendly post status based on post
 *
 * @param string $status
 * @return string
 */
function dokan_get_post_status( $status ) {
    switch ($status) {
        case 'publish':
            return __( 'Online', 'dokan' );
            break;

        case 'draft':
            return __( 'Draft', 'dokan' );
            break;

        case 'pending':
            return __( 'Pending Review', 'dokan' );
            break;

        case 'future':
            return __( 'Scheduled', 'dokan' );
            break;

        default:
            return '';
            break;
    }
}


/**
 * Get readable product type based on product
 *
 * @param string $status
 * @return string
 */
function dokan_get_product_status( $status ) {
    switch ($status) {
        case 'simple':
            $name = __( 'Simple Product', 'dokan' );
            break;

        case 'variable':
            $name = __( 'Variable Product', 'dokan' );
            break;

        case 'grouped':
            $name = __( 'Grouped Product', 'dokan' );
            break;

        case 'external':
            $name = __( 'Scheduled', 'dokan' );
            break;

        default:
            $name = '';
            break;
    }

    return apply_filters( 'dokan_product_status_case', $name, $status );
}

/**
 * Helper function for input text field
 *
 * @param string $key
 * @return string
 */
function dokan_posted_input( $key ) {
    $value = isset( $_POST[$key] ) ? trim( $_POST[$key] ) : '';

    return esc_attr( $value );
}


/**
 * Helper function for input textarea
 *
 * @param string $key
 * @return string
 */
function dokan_posted_textarea( $key ) {
    $value = isset( $_POST[$key] ) ? trim( $_POST[$key] ) : '';

    return esc_textarea( $value );
}



/**
 * Helper function to include a file
 *
 * @param type $template_name
 * @param type $args
 */
function dokan_get_template( $template_name, $args = array() ) {

    if ( file_exists( $template_name ) ) {
        extract( $args );

        include_once $template_name;
    }
}


/**
 * Get page permalink based on context
 *
 * @param string $page
 * @param string $context
 * @return string url of the page
 */
function dokan_get_page_url( $page, $context = 'dokan' ) {

    if ( $context == 'woocommerce' ) {
        $page_id = wc_get_page_id( $page );
    } else {
        $page_id = dokan_get_option( $page, 'dokan_pages' );
    }

    return get_permalink( $page_id );
}


/**
 * Get edit product url
 *
 * @param type $product_id
 * @return type
 */
function dokan_edit_product_url( $product_id ) {
    if ( get_post_field( 'post_status', $product_id ) == 'publish' ) {
        return trailingslashit( get_permalink( $product_id ) ). 'edit/';
    }

    return add_query_arg( array( 'product_id' => $product_id, 'action' => 'edit' ), dokan_get_page_url('products') );
}

/**
 * Ads additional columns to admin user table
 *
 * @param array $columns
 * @return array
 */
function my_custom_admin_product_columns( $columns ) {
    $columns['author'] = __( 'Author', 'dokan' );

    return $columns;
}

add_filter( 'manage_edit-product_columns', 'my_custom_admin_product_columns' );


/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function dokan_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}



/**
 * Redirect users from standard WordPress register page to woocommerce
 * my account page
 *
 * @global string $action
 */
function dokan_redirect_to_register(){
    global $action;

    if ( $action == 'register' ) {
        wp_redirect( dokan_get_page_url( 'myaccount', 'woocommerce' ) );
        exit;
    }
}

add_action( 'login_init', 'dokan_redirect_to_register' );



/**
 * Pretty print a variable
 *
 * @param var $value
 */
function dokan_pre( $value ) {
    printf( '<pre>%s</pre>', print_r( $value, true ) );
}



/**
 * Check if the seller is enabled
 *
 * @param int $user_id
 * @return boolean
 */
function dokan_is_seller_enabled( $user_id ) {
    $selling = get_user_meta( $user_id, 'dokan_enable_selling', true );

    if ( $selling == 'yes' ) {
        return true;
    }

    return false;
}



/**
 * Check if the seller is trusted
 *
 * @param int $user_id
 * @return boolean
 */
function dokan_is_seller_trusted( $user_id ) {
    $publishing = get_user_meta( $user_id, 'dokan_publishing', true );

    if ( $publishing == 'yes' ) {
        return true;
    }

    return false;
}



/**
 * Get store page url of a seller
 *
 * @param int $user_id
 * @return string
 */
function dokan_get_store_url( $user_id ) {
    $userdata = get_userdata( $user_id );

    return sprintf( '%s/%s/', home_url( '/store' ), $userdata->user_nicename );
}


/**
 * Get review page url of a seller
 *
 * @param int $user_id
 * @return string
 */
function dokan_get_review_url( $user_id ) {
    $userstore = dokan_get_store_url( $user_id );

    return $userstore ."reviews";
}



/**
 * Helper function for loggin
 *
 * @param string $message
 */
function dokan_log( $message ) {
    $message = sprintf( "[%s] %s\n", date( 'd.m.Y h:i:s' ), $message );
    error_log( $message, 3, DOKAN_DIR . '/debug.log' );
}



/**
 * Filter WP Media Manager files if the current user is seller.
 *
 * Do not show other sellers images to a seller. He can see images only by him
 *
 * @param array $args
 * @return array
 */
function dokan_media_uploader_restrict( $args ) {
    // bail out for admin and editor
    if ( current_user_can( 'delete_pages' ) ) {
        return $args;
    }

    if ( current_user_can( 'dokandar' ) ) {
        $args['author'] = get_current_user_id();

        return $args;
    }

    return $args;
}

add_filter( 'ajax_query_attachments_args', 'dokan_media_uploader_restrict' );



/**
 * Get store info based on seller ID
 *
 * @param int $seller_id
 * @return array
 */
function dokan_get_store_info( $seller_id ) {
    $info = get_user_meta( $seller_id, 'dokan_profile_settings', true );
    $info = is_array( $info ) ? $info : array();

    $defaults = array(
        'store_name' => '',
        'social' => array(),
        'payment' => array( 'paypal' => array( 'email' ), 'bank' => array() ),
        'phone' => '',
        'show_email' => 'off',
        'address' => '',
        'location' => '',
        'banner' => 0
    );

    $info = wp_parse_args( $info, $defaults );

    return $info;
}



/**
 * Get withdraw email method based on seller ID and type
 *
 * @param int $seller_id
 * @param string $type
 * @return string
 */
function dokan_get_seller_withdraw_mail( $seller_id, $type = 'paypal' ) {
    $info = dokan_get_store_info( $seller_id );

    if ( isset( $info['payment'][$type]['email'] ) ) {
        return $info['payment'][$type]['email'];
    }

    return false;
}



/**
 * Get seller bank details
 *
 * @param int $seller_id
 * @return string
 */
function dokan_get_seller_bank_details( $seller_id ) {
    $info = dokan_get_store_info( $seller_id );
    $payment = $info['payment']['bank'];
    $details = array();

    if ( isset( $payment['ac_name'] ) ) {
        $details[] = sprintf( __( 'Account Name: %s', 'dokan' ), $payment['ac_name'] );
    }
    if ( isset( $payment['ac_number'] ) ) {
        $details[] = sprintf( __( 'Account Number: %s', 'dokan' ), $payment['ac_number'] );
    }
    if ( isset( $payment['bank_name'] ) ) {
        $details[] = sprintf( __( 'Bank Name: %s', 'dokan' ), $payment['bank_name'] );
    }
    if ( isset( $payment['bank_addr'] ) ) {
        $details[] = sprintf( __( 'Address: %s', 'dokan' ), $payment['bank_addr'] );
    }
    if ( isset( $payment['swift'] ) ) {
        $details[] = sprintf( __( 'SWIFT: %s', 'dokan' ), $payment['swift'] );
    }

    return nl2br( implode( "\n", $details ) );
}



/**
 * Get seller listing
 *
 * @param int $number
 * @param int $offset
 * @return array
 */
function dokan_get_sellers( $number = 10, $offset = 0 ) {
    $args = apply_filters( 'dokan_seller_list_query', array(
        'role' => 'seller',
        'number' => $number,
        'offset' => $offset,
        'orderby' => 'registered',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'dokan_enable_selling',
                'value' => 'yes',
                'compare' => '='
            )
        )
    ) );

    $user_query = new WP_User_Query( $args );
    $sellers = $user_query->get_results();

    return array( 'users' => $sellers, 'count' => $user_query->total_users );
}



/**
 * Add cart total amount on add_to_cart_fragments
 *
 * @param array $fragment
 * @return array
 */
function dokan_add_to_cart_fragments( $fragment ) {
    $fragment['amount'] = WC()->cart->get_cart_total();

    return $fragment;
}

add_filter( 'add_to_cart_fragments', 'dokan_add_to_cart_fragments' );



/**
 * Get wishlist url if YITH Wishlist plugin is installed
 *
 * @global WITH_WCWL $yith_wcwl
 * @global WC_Product $product
 */
function dokan_add_to_wishlist_link() {
    if ( class_exists( 'YITH_WCWL' ) ) {
        global $yith_wcwl, $product;

        printf( '<a href="%s" class="btn fav add_to_wishlist" data-product-id="%d" data-product-type=""><i class="fa fa-heart"></i></a>', $yith_wcwl->get_addtowishlist_url(), $product->id );
    }
}

/**
 * Put data with post_date's into an array of times
 *
 * @param  array $data array of your data
 * @param  string $date_key key for the 'date' field. e.g. 'post_date'
 * @param  string $data_key key for the data you are charting
 * @param  int $interval
 * @param  string $start_date
 * @param  string $group_by
 * @return string
 */
function dokan_prepare_chart_data( $data, $date_key, $data_key, $interval, $start_date, $group_by ) {
    $prepared_data = array();

    // Ensure all days (or months) have values first in this range
    for ( $i = 0; $i <= $interval; $i ++ ) {
        switch ( $group_by ) {
            case 'day' :
                $time = strtotime( date( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) * 1000;
            break;
            case 'month' :
                $time = strtotime( date( 'Ym', strtotime( "+{$i} MONTH", $start_date ) ) . '01' ) * 1000;
            break;
        }

        if ( ! isset( $prepared_data[ $time ] ) )
            $prepared_data[ $time ] = array( esc_js( $time ), 0 );
    }

    foreach ( $data as $d ) {
        switch ( $group_by ) {
            case 'day' :
                $time = strtotime( date( 'Ymd', strtotime( $d->$date_key ) ) ) * 1000;
            break;
            case 'month' :
                $time = strtotime( date( 'Ym', strtotime( $d->$date_key ) ) . '01' ) * 1000;
            break;
        }

        if ( ! isset( $prepared_data[ $time ] ) ) {
            continue;
        }

        if ( $data_key )
            $prepared_data[ $time ][1] += $d->$data_key;
        else
            $prepared_data[ $time ][1] ++;
    }

    return $prepared_data;
}



/**
 * Disable selling capability by default once a seller is registered
 *
 * @param int $user_id
 */
function dokan_admin_user_register( $user_id ) {
    $user = new WP_User( $user_id );
    $role = reset( $user->roles );

    if ( $role == 'seller' ) {

        if ( dokan_get_option( 'new_seller_enable_selling', 'dokan_selling' ) == 'off' ) {
            update_user_meta( $user_id, 'dokan_enable_selling', 'no' );
        } else {
            update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
        }
    }
}

add_action( 'user_register', 'dokan_admin_user_register' );



/**
 * Get seller count based on enable and disabled sellers
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_get_seller_count() {
    global $wpdb;


    $counts = array( 'yes' => 0, 'no' => 0 );

    $result = $wpdb->get_results( "SELECT COUNT(um.user_id) as count, um1.meta_value as type
                FROM $wpdb->usermeta um
                LEFT JOIN $wpdb->usermeta um1 ON um1.user_id = um.user_id
                WHERE um.meta_key = 'wp_capabilities' AND um1.meta_key = 'dokan_enable_selling'
                AND um.meta_value LIKE '%seller%'
                GROUP BY um1.meta_value" );

    if ( $result ) {
        foreach ($result as $row) {
            $counts[$row->type] = (int) $row->count;
        }
    }

    return $counts;
}

/**
 * Prevent sellers and customers from seeing the admin bar
 *
 * @param bool $show_admin_bar
 * @return bool
 */
function dokan_disable_admin_bar( $show_admin_bar ) {
    global $current_user;

    if ( $current_user->ID !== 0 ) {
        $role = reset( $current_user->roles );

        if ( in_array( $role, array( 'seller', 'customer' ) ) ) {
            return false;
        }
    }

    return $show_admin_bar;
}

add_filter( 'show_admin_bar', 'dokan_disable_admin_bar' );


/**
 * Human readable number format.
 *
 * Shortens the number by dividing 1000
 *
 * @param type $number
 * @return type
 */
function dokan_number_format( $number ) {
    $threshold = 10000;

    if ( $number > $threshold ) {
        return number_format( $number/1000, 0, '.', '' ) . ' K';
    }

    return $number;
}


/**
 * Get coupon edit url
 *
 * @param int $coupon_id
 * @param string $coupon_page
 * @return string
 */
function dokan_get_coupon_edit_url( $coupon_id, $coupon_page = '' ) {

    if ( !$coupon_page ) {
        $coupon_page = dokan_get_page_url( 'coupons' );
    }

    $edit_url = wp_nonce_url( add_query_arg( array('post' => $coupon_id, 'action' => 'edit', 'view' => 'add_coupons'), $coupon_page ), '_coupon_nonce', 'coupon_nonce_url' );

    return $edit_url;
}

/**
 * User avatar wrapper for custom uploaded avatar
 *
 * @since 2.0
 *
 * @param string $avatar
 * @param mixed $id_or_email
 * @param int $size
 * @param string $default
 * @param string $alt
 * @return string image tag of the user avatar
 */
function dokan_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

    if ( is_numeric( $id_or_email ) ) {
        $user = get_user_by( 'id', $id_or_email );
    } elseif ( is_object( $id_or_email ) ) {
        if ( $id_or_email->user_id != '0' ) {
            $user = get_user_by( 'id', $id_or_email->user_id );
        } else {
            return $avatar;
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( !$user ) {
        return $avatar;
    }

    // see if there is a user_avatar meta field
    $user_avatar = get_user_meta( $user->ID, 'dokan_profile_settings', true );
    $gravatar_id = isset( $user_avatar['gravatar'] ) ? $user_avatar['gravatar'] : 0;
    if ( empty( $gravatar_id ) ) {
        return $avatar;
    }

    $avater_url = wp_get_attachment_thumb_url( $gravatar_id );

    return sprintf( '<img src="%1$s" alt="%2$s" width="%3$s" height="%3$s" class="avatar photo">', esc_url( $avater_url ), $alt, $size );
}

add_filter( 'get_avatar', 'dokan_get_avatar', 99, 5 );

/**
 * Get best sellers list
 *
 * @param  integer $limit
 * @return array
 */
function dokan_get_best_sellers( $limit = 5 ) {
    global  $wpdb;

    $cache_key = 'dokan-best-seller-' . $limit;
    $seller = wp_cache_get( $cache_key, 'widget' );

    if ( false === $seller ) {

        $qry = "SELECT seller_id, display_name, SUM( net_amount ) AS total_sell
            FROM {$wpdb->prefix}dokan_orders AS o,{$wpdb->prefix}users AS u
            WHERE o.seller_id = u.ID
            GROUP BY o.seller_id
            ORDER BY total_sell DESC LIMIT ".$limit;

        $seller = $wpdb->get_results( $qry );
        wp_cache_set( $cache_key, $seller, 'widget' );
    }

    return $seller;
}
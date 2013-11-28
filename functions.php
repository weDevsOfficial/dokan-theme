<?php

/**
 * Dokan functions and definitions
 *
 * @package Dokan
 * @since Dokan 1.0
 */
/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Dokan 1.0
 */
if ( !isset( $content_width ) )
    $content_width = 640; /* pixels */

// Backwards compatibility for older than PHP 5.3.0
if ( !defined( '__DIR__' ) ) {
    define( '__DIR__', dirname( __FILE__ ) );
}



/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since Dokan 1.0
 */
class WeDevs_Dokan {

    function __construct() {

        //includes file
        $this->includes();

        //bind actions
        add_action( 'after_setup_theme', array($this, 'setup') );
        add_action( 'widgets_init', array($this, 'widgets_init') );
        add_action( 'wp_enqueue_scripts', array($this, 'scripts') );
        add_filter( 'posts_where', array($this, 'hide_others_uploads') );

        add_action( 'admin_init', array($this, 'install_theme' ) );

        //initalize user roles
        $this->user_roles();
    }

    function includes() {
        $lib_dir = __DIR__ . '/lib/';

        require_once dirname( __FILE__ ) . '/lib/bootstrap-walker.php';
        require_once dirname( __FILE__ ) . '/lib/woo-functions.php';
        require_once dirname( __FILE__ ) . '/lib/woo-template.php';
        require_once dirname( __FILE__ ) . '/lib/template-tags.php';

        if ( is_admin() ) {
            require_once dirname( __FILE__ ) . '/lib/admin.php';
        }


        $files = array(
            'woo-template.php', //Custom template fixing functions for WooCommerce
            'template-tags.php', //Custom template tags for this theme.
            'extras.php', // Custom functions that act independently of the theme templates
            'functions.php', //Helper functions
            //'featured-image.php', //Featured Image uploader functions

            /** roots files ***/
            'roots/utils.php',           // Utility functions
            'roots/wrapper.php',         // Theme wrapper class
            'roots/sidebar.php',         // Sidebar class
            'roots/config.php',          // Configuration
            'roots/titles.php',          // Page titles
            'roots/cleanup.php',         // Cleanup
            'roots/nav.php',             // Custom nav modifications
            'roots/gallery.php',         // Custom [gallery] modifications
            'roots/comments.php',        // Custom comments modifications
            'roots/rewrites.php',        // URL rewriting for assets
            'roots/relative-urls.php',   // Root relative URLs
            'roots/widgets.php',         // Sidebars and widgets
            'roots/custom.php',          // Custom functions
        );

        foreach ($files as $file) {
            //require_once $lib_dir . $file;
        }
    }

    /**
     * Setup dokan
     *
     * @uses `after_setup_theme` hook
     */
    function setup() {

        /**
         * Make theme available for translation
         * Translations can be filed in the /languages/ directory
         */
        load_theme_textdomain( 'dokan', get_template_directory() . '/languages' );

        /**
         * Add default posts and comments RSS feed links to head
         */
        add_theme_support( 'automatic-feed-links' );

        /**
         * Enable support for Post Thumbnails
         */
        add_theme_support( 'post-thumbnails' );

        // set_post_thumbnail_size(150, 150, false);
        // add_image_size('category-thumb', 300, 9999); // 300px wide (and unlimited height)
        // Add post formats (http://codex.wordpress.org/Post_Formats)
        // add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));
        // Tell the TinyMCE editor to use a custom stylesheet
        add_editor_style( '/assets/css/editor-style.css' );

        add_theme_support( 'root-relative-urls' );    // Enable relative URLs
        add_theme_support( 'rewrites' );              // Enable URL rewrites
        add_theme_support( 'bootstrap-top-navbar' );  // Enable Bootstrap's top navbar
        add_theme_support( 'bootstrap-gallery' );     // Enable Bootstrap's thumbnails component on [gallery]
        add_theme_support( 'nice-search' );           // Enable /?s= to /search/ redirect
        // add_theme_support( 'jquery-cdn' );            // Enable to load jQuery from the Google CDN

        /**
         * This theme uses wp_nav_menu() in one location.
         */
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'dokan' ),
            'top-left' => __( 'Top Left', 'dokan' ),
            'product-cat' => __( 'Product Category', 'dokan' ),
        ) );

        add_theme_support( 'woocommerce' );

        /**
         * Add support for the Aside Post Formats
         */
        // add_theme_support( 'post-formats', array('aside',) );

        // setup global tables
        global $wpdb;

        $wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';
    }

    function install_theme() {
        global $pagenow;

        if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
            $this->setup_pages();
        }
    }

    function setup_pages() {
        $meta_key = '_wp_page_template';

        $pages = array(
            array(
                'post_title' => __( 'Dashboard', 'dokan' ),
                'slug' => 'dashboard',
                'template' => 'templates/dashboard.php',
                'page_id' => 'dashboard',
                'child' => array(
                    array(
                        'post_title' => __( 'Products', 'dokan' ),
                        'slug' => 'products',
                        'template' => 'templates/products.php',
                        'page_id' => 'products',
                    ),
                    array(
                        'post_title' => __( 'Create Product', 'dokan' ),
                        'slug' => 'add-product',
                        'template' => 'templates/new-product.php',
                        'page_id' => 'new_product',
                    ),
                    array(
                        'post_title' => __( 'Orders', 'dokan' ),
                        'slug' => 'orders',
                        'template' => 'templates/orders.php',
                        'page_id' => 'orders',
                    ),
                    array(
                        'post_title' => __( 'Coupons', 'dokan' ),
                        'slug' => 'coupons',
                        'template' => 'templates/coupons.php',
                        'page_id' => 'coupons',
                    ),
                    array(
                        'post_title' => __( 'Reports', 'dokan' ),
                        'slug' => 'reports',
                        'template' => 'templates/reports.php',
                        'page_id' => 'reports',
                    ),
                    array(
                        'post_title' => __( 'Reviews', 'dokan' ),
                        'slug' => 'reviews',
                        'template' => 'templates/reviews.php',
                        'page_id' => 'reviews',
                    ),
                    array(
                        'post_title' => __( 'Withdraw', 'dokan' ),
                        'slug' => 'withdraw',
                        'template' => 'templates/withdraw.php',
                        'page_id' => 'withdraw',
                    ),
                    array(
                        'post_title' => __( 'Settings', 'dokan' ),
                        'slug' => 'settings',
                        'template' => 'templates/settings.php',
                        'page_id' => 'settings',
                    )
                )
            )
        );

        $dokan_page_settings = array();

        if ( $pages ) {
            foreach ($pages as $page) {
                $page_id = $this->create_page( $page );

                if ( $page_id ) {
                    $dokan_page_settings[$page['page_id']] = $page_id;

                    if ( isset( $page['child'] ) && count( $page['child'] ) > 0 ) {
                        foreach ($page['child'] as $child_page) {
                            $child_page_id = $this->create_page( $child_page );

                            if ( $child_page_id ) {
                                $dokan_page_settings[$child_page['page_id']] = $child_page_id;

                                wp_update_post( array( 'ID' => $child_page_id, 'post_parent' => $page_id ) );
                            }
                        }
                    } // if child
                } // if page_id
            } // end foreach
        } // if pages

        update_option( 'dokan_pages', $dokan_page_settings );
    }

    function create_page( $page ) {
        $meta_key = '_wp_page_template';
        $page_obj = get_page_by_path( $page['post_title'] );

        if ( !$page_obj ) {
            $page_id = wp_insert_post( array(
                'post_title' => $page['post_title'],
                'post_name' => $page['slug'],
                'post_status' => 'publish',
                'post_type' => 'page',
            ) );

            if ( $page_id && !is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, $meta_key, $page['template'] );

                return $page_id;
            }
        }

        return false;
    }

    /**
     * Register widgetized area and update sidebar with default widgets
     *
     * @since Dokan 1.0
     */
    function widgets_init() {
        register_sidebar( array(
            'name' => __( 'Sidebar', 'dokan' ),
            'id' => 'sidebar-1',
            'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
            'after_widget' => '</div></aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ) );

        register_sidebar( array(
            'name' => __( 'Home Sidebar', 'dokan' ),
            'id' => 'sidebar-home',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ) );
    }

    /**
     * Enqueue scripts and styles
     *
     * @since Dokan 1.0
     */
    function scripts() {
        $template_directory = get_template_directory_uri();

        wp_enqueue_media();

        wp_enqueue_style( 'bootstrap', $template_directory . '/assets/css/bootstrap.css', false, null );
        wp_enqueue_style( 'icomoon', $template_directory . '/assets/css/icomoon.css', false, null );
        wp_enqueue_style( 'dokan-style', $template_directory . '/assets/css/style.css', false, null );

        if ( is_single() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        if ( is_singular() && wp_attachment_is_image() ) {
            wp_enqueue_script( 'keyboard-image-navigation', $template_directory . '/assets/js/keyboard-image-navigation.js', array('jquery'), '20120202' );
        }

        // wp_register_script( 'modernizr', $template_directory . '/assets/js/vendor/modernizr-2.6.2.min.js', false, null, false );
        // wp_register_script( 'roots_plugins', $template_directory . '/assets/js/plugins.js', false, null, true );
        // wp_register_script( 'tip-tip', $template_directory . '/assets/js/jquery.tipTip.js', false, null, true );

        wp_enqueue_script( 'jquery' );

        wp_enqueue_script( 'bootstrap-min', $template_directory . '/assets/js/bootstrap.min.js', false, null, true );
        wp_enqueue_script( 'dokan-product-editor', $template_directory . '/assets/js/product-editor.js', false, null, true );
        wp_enqueue_script( 'dokan-scripts', $template_directory . '/assets/js/script.js', false, null, true );

        wp_enqueue_script( 'underscore' );
        wp_enqueue_script( 'modernizr' );
        // wp_enqueue_script( 'roots_plugins' );
        // wp_enqueue_script( 'roots_main' );
    }

    /**
     * Init dokan user roles
     *
     * @since Dokan 1.0
     * @global WP_Roles $wp_roles
     */
    function user_roles() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && !isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        add_role( 'seller', __( 'Seller', 'dokan' ), array(
            'read' => true,
            'publish_posts' => true,
            'edit_posts' => true,
            'delete_published_posts' => true,
            'edit_published_posts' => true,
            'delete_posts' => true,
            'unfiltered_html' => true,
            'upload_files' => true,
            'dokandar' => true
        ) );
    }

    /**
     * Hide other users uploads for `seller` users
     *
     * Hide media uploads in page "upload.php" and "media-upload.php" for
     * sellers. They can see only thier uploads.
     *
     * FIXME: fix the upload counts
     *
     * @global string $pagenow
     * @global object $wpdb
     * @param string $where
     * @return string
     */
    function hide_others_uploads( $where ) {
        global $pagenow, $wpdb;

        if ( ( $pagenow == 'upload.php' || $pagenow == 'media-upload.php') && current_user_can( 'dokandar' ) ) {
            $user_id = get_current_user_id();

            $where .= " AND $wpdb->posts.post_author = $user_id";
        }

        return $where;
    }

}

$dokan = new WeDevs_Dokan();

add_action( 'admin_init', function() {
    global $pagenow;

    $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';
    // var_dump( $pagenow, $post_type );
});

add_action( 'woocommerce_single_product_summary', function() {
    global $product;

    $author = get_user_by( 'id', $product->post->post_author );
    ?>

    Seller: <?php printf( '<a href="%s">%s</a>', get_author_posts_url( $author->ID ), $author->display_name ); ?>

    <?php
}, 99);

add_filter( 'woocommerce_product_tabs', function( $tabs) {

    $tabs['seller'] = array(
        'title' => __( 'Seller Info', 'dokan' ),
        'priority' => 90,
        'callback' => 'dokan_product_seller_tab'
    );

    return $tabs;
}, 10);

function dokan_product_seller_tab( $val ) {
    global $product;

    $author = get_user_by( 'id', $product->post->post_author );
    ?>

    Seller: <?php printf( '<a href="%s">%s</a>', get_author_posts_url( $author->ID ), $author->display_name ); ?>

    <?php
}

function dokan_edit_product_url( $product_id ) {
    return trailingslashit( get_permalink( $product_id ) ). 'edit';
}

/**
 * Register URL endpoints for photo and videos
 *
 * @return void
 */
function dokan_movie_rewrite_rule() {
    $permalinks = get_option( 'woocommerce_permalinks', array() );
    add_rewrite_rule( 'shop/([^/]+)(/[0-9]+)?/edit/?$', 'index.php?product=$matches[1]&page=$matches[2]&edit=true', 'top' );
}

add_action( 'init', 'dokan_movie_rewrite_rule' );

function dokan_register_query_var( $vars ) {
    $vars[] = 'edit';

    return $vars;
}

add_filter( 'query_vars', 'dokan_register_query_var' );

/**
 * Set photo and video templates on template_redirect
 *
 * @return void
 */
function dokan_url_rewrite_templates( $template ) {

    if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
        return get_template_directory() . '/templates/product-edit.php';
    }

    return $template;
}

add_action( 'template_include', 'dokan_url_rewrite_templates', 11 );

// add_filter( 'wp_mail', function( $mail ) {

//     echo( $mail['message'] );
//     die();
//     return $mail;
// });

/**
 * Ads additional columns to admin user table
 *
 * @param array $columns
 * @return array
 */
function my_custom_admin_product_columns( $columns ) {
    $columns['author'] = __( 'Author' );

    return $columns;
}

add_filter( 'manage_edit-product_columns', 'my_custom_admin_product_columns' );

function devplus_wpquery_where( $where ){
    global $current_user;

    if( is_user_logged_in() ){
         // logged in user, but ware we viewing the library?
         if( isset( $_POST['action'] ) && ( $_POST['action'] == 'query-attachments' ) ){
            $where .= ' AND post_author=' . $current_user->data->ID;
        }
    }

    return $where;
}

add_filter( 'posts_where', 'devplus_wpquery_where' );

add_action( 'wp_ajax_dokan_save_attributes', function() {
    global $woocommerce;

    // check_ajax_referer( 'save-attributes', 'security' );

    // Get post data
    parse_str( $_POST['data'], $data );
    $post_id = absint( $_POST['post_id'] );

    // print_r($data);

    // exit;

    // Save Attributes
    $attributes = array();

    if ( isset( $data['attribute_names'] ) ) {

        $attribute_names  = array_map( 'stripslashes', $data['attribute_names'] );
        $attribute_values = $data['attribute_values'];

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
                        $values = array_map( 'stripslashes', array_map( 'strip_tags', explode( '|', $attribute_values[ $i ] ) ) );
                    }

                    // Remove empty items in the array
                    $values = array_filter( $values );

                } else {
                    $values = array();
                }

                // Update post terms
                if ( taxonomy_exists( $attribute_names[ $i ] ) ) {
                    wp_set_object_terms( $post_id, $values, $attribute_names[ $i ] );
                    print_r($values);
                    // var_dump($values);
                    echo "wp_set_object_terms( $post_id, $values, {$attribute_names[ $i ]} )";
                }

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
                $values = implode( ' | ', array_map( 'woocommerce_clean', array_map( 'stripslashes', $attribute_values[ $i ] ) ) );
                // $values = array_map( 'sanitize_title', $attribute_values[ $i ] );

                // print_r( $values );

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
    // print_r( $attributes );

    update_post_meta( $post_id, '_product_attributes', $attributes );

    die();
});


/**
 * Some helper functions
 *
 * @since Dokan 1.0
 */

/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_get_seller_orders( $seller_id ) {
    global $wpdb;

    $sql = "SELECT oi.order_id FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            WHERE oim.meta_key = '_product_id'  AND p.post_author = %d";

    return $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );
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

function dokan_get_template( $template_name, $args = array() ) {

    if ( file_exists( $template_name ) ) {
        extract( $args );

        include_once $template_name;
    }
}

function dokan_get_page_url( $page ) {
    $page_id = dokan_get_option( $page, 'dokan_pages' );

    return get_permalink( $page_id );
}

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

// Function to get the client ip address
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

function dokan_format_time( $datetime ) {
    $timestamp = strtotime( $datetime );

    $date_format = get_option( 'date_format' );
    $time_format = get_option( 'time_format' );

    return date_i18n( $date_format . ' ' . $time_format, $timestamp );
}
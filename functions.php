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
 * Autoload class files on demand
 *
 * `WPUF_Form_Posting` becomes => form-posting.php
 * `WPUF_Dashboard` becomes => dashboard.php
 *
 * @param string $class requested class name
 */
function dokan_autoload( $class ) {
    if ( stripos( $class, 'Dokan_' ) !== false ) {
        $class_name = str_replace( array('Dokan_', '_'), array('', '-'), $class);
        $file_path = __DIR__ . '/classes/' . strtolower( $class_name ) . '.php';

        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}

spl_autoload_register( 'dokan_autoload' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since Dokan 1.0
 */
class WeDevs_Dokan {

    function __construct() {

        //includes file
        $this->includes();

        // init actions and filter
        $this->init_filters();
        $this->init_actions();

        // initialize classes
        $this->init_classes();

        //initalize user roles
        $this->user_roles();

        //for reviews ajax request
        $this->init_ajax();
    }

    function init_filters() {
        add_filter( 'posts_where', array($this, 'hide_others_uploads') );
    }

    function init_actions() {
        add_action( 'after_setup_theme', array($this, 'setup') );
        add_action( 'widgets_init', array($this, 'widgets_init') );
        add_action( 'wp_enqueue_scripts', array($this, 'scripts') );
        add_action( 'admin_init', array($this, 'install_theme' ) );
    }

    function init_classes() {
        if ( !is_admin() ) {
            new Dokan_Pageviews();
        }

        new Dokan_Rewrites();
    }

    function init_ajax() {
        $doing_ajax = defined('DOING_AJAX') && DOING_AJAX;

        if ( $doing_ajax ) {
            Dokan_Ajax::init()->init_ajax();
            new Dokan_Pageviews();
        }
    }

    function includes() {
        $lib_dir = __DIR__ . '/lib/';
        $inc_dir = __DIR__ . '/includes/';

        require_once $lib_dir . 'theme-functions.php';
        require_once $inc_dir . 'widgets/menu-category.php';

        if ( is_admin() ) {
            require_once $lib_dir . 'admin.php';
        } else {
            require_once $lib_dir . 'bootstrap-walker.php';
            require_once $lib_dir . 'woo-template.php';
            require_once $lib_dir . 'template-tags.php';
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
        add_post_type_support( 'product', 'author' );

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
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
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

        wp_enqueue_script( 'post' );
        wp_enqueue_media();

        wp_enqueue_style( 'bootstrap', $template_directory . '/assets/css/bootstrap.css', false, null );
        wp_enqueue_style( 'icomoon', $template_directory . '/assets/css/icomoon.css', false, null );
        wp_enqueue_style( 'fontawesome', $template_directory . '/assets/css/font-awesome.css', false, null );
        wp_enqueue_style( 'jquery-ui', $template_directory . '/assets/css/jquery-ui-1.10.0.custom.css', false, null );
        wp_enqueue_style( 'dokan-style', $template_directory . '/assets/css/style.css', false, null );
        wp_enqueue_style( 'style', $template_directory . '/style.css', false, null );
        wp_enqueue_style( 'chosen-style', $template_directory . '/assets/css/chosen.min.css', false, null );
        //reviews
        wp_enqueue_style( 'reviows-style', $template_directory . '/assets/css/reviews.css', false, null );

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
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'underscore' );

        wp_register_script( 'dokan-order', $template_directory . '/assets/js/orders.js', false, null, true );
        wp_register_script( 'jquery-flot', $template_directory . '/assets/js/jquery.flot.js', false, null, true );
        wp_register_script( 'jquery-chart', $template_directory . '/assets/js/Chart.min.js', false, null, true );

        wp_enqueue_script( 'menu-aim', $template_directory . '/assets/js/jquery.menu-aim.js', false, null, true );
        wp_enqueue_script( 'bootstrap-min', $template_directory . '/assets/js/bootstrap.min.js', false, null, true );
        wp_enqueue_script( 'dokan-product-editor', $template_directory . '/assets/js/product-editor.js', false, null, true );
        wp_enqueue_script( 'dokan-reviews', get_stylesheet_directory_uri() . '/assets/js/reviews.js', array('jquery', 'underscore') );
        wp_enqueue_script( 'chosen', $template_directory . '/assets/js/chosen.jquery.min.js', array('jquery'), null, true );
        wp_enqueue_script( 'chosen-ajax', $template_directory . '/assets/js/ajax-chosen.jquery.min.js', array('jquery'), null, true );
        wp_enqueue_script( 'form-validate', get_stylesheet_directory_uri() . '/assets/js/form-validate.js', array('jquery'), null, true  );
        wp_enqueue_script( 'dokan-scripts', $template_directory . '/assets/js/script.js', false, null, true );
        wp_localize_script( 'dokan-scripts', 'dokan', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'dokan_reviews' ),
            'ajax_loader' => $template_directory . '/assets/images/ajax-loader.gif'
        ) );


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
    if ( get_post_field( 'post_status', $product_id ) == 'publish' ) {
        return trailingslashit( get_permalink( $product_id ) ). 'edit/';
    }

    return add_query_arg( array( 'product_id' => $product_id, 'action' => 'edit' ), dokan_get_page_url('products') );
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
                    echo "wp_set_object_terms( $post_id, $values, {$attribute_names[$i]} )";
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

function dokan_get_page_url( $page, $context = 'dokan' ) {

    if ( $context == 'woocommerce' ) {
        $page_id = woocommerce_get_page_id( $page );
    } else {
        $page_id = dokan_get_option( $page, 'dokan_pages' );
    }

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
    update_post_meta( $post_id, '_featured', isset( $_POST['_featured'] ) ? 'yes' : 'no' );

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
        if ( isset( $_POST['_file_paths'] ) ) {
            $_file_paths = array();
            $file_paths = str_replace( "\r\n", "\n", esc_attr( $_POST['_file_paths'] ) );
            $file_paths = trim( preg_replace( "/\n+/", "\n", $file_paths ) );
            if ( $file_paths ) {
                $file_paths = explode( "\n", $file_paths );

                foreach ( $file_paths as $file_path ) {
                    $file_path = trim( $file_path );
                    $_file_paths[ md5( $file_path ) ] = $file_path;
                }
            }

            // grant permission to any newly added files on any existing orders for this product
            do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $_file_paths );

            update_post_meta( $post_id, '_file_paths', $_file_paths );
        }

        if ( isset( $_POST['_download_limit'] ) )
            update_post_meta( $post_id, '_download_limit', esc_attr( $_download_limit ) );
        if ( isset( $_POST['_download_expiry'] ) )
            update_post_meta( $post_id, '_download_expiry', esc_attr( $_download_expiry ) );
    }

    // Do action for product type
    do_action( 'woocommerce_process_product_meta_' . $product_type, $post_id );

    // Clear cache/transients
    $woocommerce->clear_product_transients( $post_id );
}

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

function dokan_get_product_status( $status ) {
    switch ($status) {
        case 'simple':
            return __( 'Simple Product', 'dokan' );
            break;

        case 'variable':
            return __( 'Variable Product', 'dokan' );
            break;

        case 'grouped':
            return __( 'Grouped Product', 'dokan' );
            break;

        case 'external':
            return __( 'Scheduled', 'dokan' );
            break;

        default:
            return '';
            break;
    }
}

function dokan_get_order_status_class( $status ) {
    switch ($status) {
        case 'completed':
            return 'success';
            break;

        case 'pending':
            return 'danger';
            break;

        case 'on-hold':
            return 'warning';
            break;

        case 'processing':
            return 'info';
            break;

        case 'refunded':
            return 'default';
            break;

        case 'cancelled':
            return 'default';
            break;

        case 'failed':
            return 'danger';
            break;
    }
}


// add_action( 'woocommerce_checkout_process', function() {
//     global $wpdb, $woocommerce, $current_user;

//     var_dump( $woocommerce->cart );
//     foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
//         // var_dump( $values );
//     }
//     die();
// });

add_filter( 'woocommerce_get_item_data', function($item_data, $cart_item) {
    $seller_info = get_userdata( $cart_item['data']->post->post_author );
    $item_data[] = array(
        'name' => __( 'Seller', 'dokan' ),
        'value' => $seller_info->display_name
    );

    return $item_data;

}, 10, 2 );


function dokan_create_sub_order( $parent_order_id ) {
    global $woocommerce;

    $parent_order = new WC_Order( $parent_order_id );
    $order_items = $parent_order->get_items();

    $sellers = array();
    foreach ($order_items as $item) {
        $seller_id = get_post_field( 'post_author', $item['product_id'] );
        $sellers[$seller_id][] = $item;
    }

    // return if we've only ONE seller
    if ( count( $sellers ) == 1 ) {
        return;
    }

    // var_dump( $parent_order );

    // seems like we've got multiple sellers
    foreach ($sellers as $seller_id => $seller_products ) {
        dokan_create_seller_order( $parent_order, $seller_products );
    }

    // echo "voila";

    // die();
}

add_action( 'woocommerce_checkout_update_order_meta', 'dokan_create_sub_order' );

function dokan_create_seller_order( $parent_order, $seller_products ) {
    $order_data = apply_filters( 'woocommerce_new_order_data', array(
        'post_type'     => 'shop_order',
        'post_title'    => sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
        'post_status'   => 'publish',
        'ping_status'   => 'closed',
        'post_excerpt'  => isset( $posted['order_comments'] ) ? $posted['order_comments'] : '',
        'post_author'   => 1,
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

            $item_id = woocommerce_add_order_item( $order_id, array(
                'order_item_name' => $item['name'],
                'order_item_type' => 'line_item'
            ) );

            if ( $item_id ) {
                woocommerce_add_order_item_meta( $item_id, '_qty', $item['qty'] );
                woocommerce_add_order_item_meta( $item_id, '_tax_class', $item['tax_class'] );
                woocommerce_add_order_item_meta( $item_id, '_product_id', $item['product_id'] );
                woocommerce_add_order_item_meta( $item_id, '_variation_id', $item['variation_id'] );
                woocommerce_add_order_item_meta( $item_id, '_line_subtotal', $item['line_subtotal'] );
                woocommerce_add_order_item_meta( $item_id, '_line_total', $item['line_total'] );
                woocommerce_add_order_item_meta( $item_id, '_line_tax', $item['line_tax'] );
                woocommerce_add_order_item_meta( $item_id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
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

        // set order meta
        update_post_meta( $order_id, '_payment_method',         $parent_order->payment_method );
        update_post_meta( $order_id, '_payment_method_title',   $parent_order->payment_method_title );

        update_post_meta( $order_id, '_order_shipping',         $shipping_cost );
        update_post_meta( $order_id, '_order_discount',         '0' );
        update_post_meta( $order_id, '_cart_discount',          '0' );
        update_post_meta( $order_id, '_order_tax',              woocommerce_format_total( $order_tax ) );
        update_post_meta( $order_id, '_order_shipping_tax',     '0' );
        update_post_meta( $order_id, '_order_total',            woocommerce_format_total( $order_total + $shipping_cost ) );
        update_post_meta( $order_id, '_order_key',              apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
        update_post_meta( $order_id, '_customer_user',          $parent_order->customer_user );
        update_post_meta( $order_id, '_order_currency',         $parent_order->order_custom_fields['_order_currency'] );
        update_post_meta( $order_id, '_prices_include_tax',     $parent_order->prices_include_tax );
        update_post_meta( $order_id, '_customer_ip_address',    $parent_order->order_custom_fields['_customer_ip_address'] );
        update_post_meta( $order_id, '_customer_user_agent',    $parent_order->order_custom_fields['_customer_user_agent'] );

        do_action( 'dokan_checkout_update_order_meta', $order_id );

        // Order status
        wp_set_object_terms( $order_id, 'pending', 'shop_order_status' );
    } // if order
}


function dokan_create_sub_order_coupon( $parent_order, $order_id, $product_ids ) {
    $used_coupons = $parent_order->get_used_coupons();

    if ( ! count( $used_coupons ) ) {
        return;
    }

    // seems like we've got some coupons
    $code = reset( $used_coupons ); // get the first one as we assume only 1 coupon can be applied at once
    $coupon = new WC_Coupon( $code );

    if ( $coupon && !is_wp_error( $coupon ) && array_intersect( $product_ids, $coupon->product_ids ) ) {
        // we found some match
        $item_id = wc_add_order_item( $order_id, array(
            'order_item_name' => $code,
            'order_item_type' => 'coupon'
        ) );

        // Add line item meta
        if ( $item_id ) {
            wc_add_order_item_meta( $item_id, 'discount_amount', 0 );
        }
    }
}

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

add_action( 'woocommerce_order_details_after_order_table', function( $parent_order ) {

    $sub_orders = get_children( array( 'post_parent' => $parent_order->id, 'post_type' => 'shop_order' ) );

    if ( !$sub_orders ) {
        return;
    }
    ?>
    <header>
        <h2><?php _e( 'Sub Orders', 'dokan' ); ?></h2>
    </header>

    <div class="alert alert-info">
        <label class="label label-success">Note:</label>
        This order has products from multiple vendors/sellers. So we divided this order into multiple seller orders.
        Each order will be handled by their respective seller independently.
    </div>


    <table class="shop_table my_account_orders">

        <thead>
            <tr>
                <th class="order-number"><span class="nobr"><?php _e( 'Order', 'woocommerce' ); ?></span></th>
                <th class="order-date"><span class="nobr"><?php _e( 'Date', 'woocommerce' ); ?></span></th>
                <th class="order-status"><span class="nobr"><?php _e( 'Status', 'woocommerce' ); ?></span></th>
                <th class="order-total"><span class="nobr"><?php _e( 'Total', 'woocommerce' ); ?></span></th>
                <th class="order-actions">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($sub_orders as $order_post) {
            $order = new WC_Order( $order_post->ID );
            $status = get_term_by( 'slug', $order->status, 'shop_order_status' );
            $item_count = $order->get_item_count();
            ?>
                <tr class="order">
                    <td class="order-number">
                        <a href="<?php echo esc_url( add_query_arg('order', $order->id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) ) ); ?>">
                            <?php echo $order->get_order_number(); ?>
                        </a>
                    </td>
                    <td class="order-date">
                        <time datetime="<?php echo date('Y-m-d', strtotime( $order->order_date ) ); ?>" title="<?php echo esc_attr( strtotime( $order->order_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></time>
                    </td>
                    <td class="order-status" style="text-align:left; white-space:nowrap;">
                        <?php echo ucfirst( __( $status->name, 'woocommerce' ) ); ?>
                    </td>
                    <td class="order-total">
                        <?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); ?>
                    </td>
                    <td class="order-actions">
                        <?php
                            $actions = array();

                            if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'failed' ), $order ) ) )
                                $actions['pay'] = array(
                                    'url'  => $order->get_checkout_payment_url(),
                                    'name' => __( 'Pay', 'woocommerce' )
                                );

                            if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) )
                                $actions['cancel'] = array(
                                    'url'  => $order->get_cancel_order_url(),
                                    'name' => __( 'Cancel', 'woocommerce' )
                                );

                            $actions['view'] = array(
                                'url'  => add_query_arg( 'order', $order->id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) ),
                                'name' => __( 'View', 'woocommerce' )
                            );

                            $actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

                            foreach( $actions as $key => $action ) {
                                echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
                            }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
});

add_action( 'wp_trash_post', function( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' ) {
        dokan_sync_update_order_status( $post_id, 0 );
    }
});

add_action( 'wp_untrash_post', function( $post_id ) {
    $post = get_post( $post_id );
    // var_dump($post);die();
    if ( $post->post_type == 'shop_order' ) {
        dokan_sync_update_order_status( $post_id, 1 );
    }
});

add_action( 'pre_get_posts', function( $query ) {
    global $wp_query;

    $author = get_query_var( 'store' );

    if ( $query->is_main_query() && !empty( $author ) ) {
        $query->set( 'post_type', 'product' );
        $query->set( 'author_name', $author );
    }
});

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

add_action( 'woocommerce_after_shop_loop_item', function() {
    global $product;
    ?>
    <span class="item-bar">

        <?php woocommerce_template_loop_price(); ?>

        <span class="item-button">
            <?php woocommerce_template_loop_add_to_cart(); ?>
            <a href="#" class="btn fav"><i class="fa fa-heart"></i></a>
        </span>
    </span>
    <?php
});

add_filter( 'woocommerce_breadcrumb_defaults', function( $args ) {
    return array(
        'delimiter'   => '',
        'wrap_before' => '<nav class="breadcrumb" ' . ( is_single() ? 'itemprop="breadcrumb"' : '' ) . '>',
        'wrap_after'  => '</nav>',
        'before'      => '<li>',
        'after'       => '</li>',
        'home'        => _x( '<i class="fa fa-home"></i> Home', 'breadcrumb', 'dokan' ),
    );
});
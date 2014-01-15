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

        require_once $lib_dir . 'customizer.php';
        if ( is_admin() ) {
            require_once $lib_dir . 'admin.php';
        } else {
            require_once $lib_dir . 'bootstrap-walker.php';
            require_once $lib_dir . 'wc-functions.php';
            require_once $lib_dir . 'wc-template.php';
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
            'footer' => __( 'Footer Menu', 'dokan' ),
        ) );

        add_theme_support( 'woocommerce' );
        add_post_type_support( 'product', 'author' );

        /*
         * This theme supports custom background color and image,
         * and here we also set up the default background color.
         */
        add_theme_support( 'custom-background', array(
            'default-color' => 'fcfcfc',
        ) );

        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

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
            // (new Dokan_Installer())->do_install();
        }
    }

    /**
     * Register widgetized area and update sidebar with default widgets
     *
     * @since Dokan 1.0
     */
    function widgets_init() {

        $sidebars = array(
            array( 'name' => __( 'General Sidebar', 'dokan' ), 'id' => 'sidebar-1' ),
            array( 'name' => __( 'Home Sidebar', 'dokan' ), 'id' => 'sidebar-home' ),
            array( 'name' => __( 'Footer Sidebar - 1', 'dokan' ), 'id' => 'footer-1' ),
            array( 'name' => __( 'Footer Sidebar - 2', 'dokan' ), 'id' => 'footer-2' ),
            array( 'name' => __( 'Footer Sidebar - 3', 'dokan' ), 'id' => 'footer-3' ),
            array( 'name' => __( 'Footer Sidebar - 4', 'dokan' ), 'id' => 'footer-4' ),
        );

        foreach ($sidebars as $sidebar) {
            register_sidebar( array(
                'name' => $sidebar['name'],
                'id' => $sidebar['id'],
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
        }
    }

    /**
     * Enqueue scripts and styles
     *
     * @since Dokan 1.0
     */
    function scripts() {
        $template_directory = get_template_directory_uri();

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

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-datepicker' );

        wp_register_script( 'dokan-order', $template_directory . '/assets/js/orders.js', false, null, true );

        wp_register_script( 'jquery-flot', $template_directory . '/assets/js/jquery.flot.js', false, null, true );
        wp_register_script( 'jquery-chart', $template_directory . '/assets/js/Chart.min.js', false, null, true );

        // wp_enqueue_script( 'menu-aim', $template_directory . '/assets/js/jquery.menu-aim.js', false, null, true );
        wp_enqueue_script( 'bootstrap-min', $template_directory . '/assets/js/bootstrap.min.js', false, null, true );

        wp_enqueue_script( 'dokan-reviews', $template_directory . '/assets/js/reviews.js', array('jquery', 'underscore') );

        wp_enqueue_script( 'chosen', $template_directory . '/assets/js/chosen.jquery.min.js', array('jquery'), null, true );
        wp_enqueue_script( 'chosen-ajax', $template_directory . '/assets/js/ajax-chosen.jquery.min.js', array('jquery'), null, true );
        wp_enqueue_script( 'form-validate', $template_directory . '/assets/js/form-validate.js', array('jquery'), null, true  );
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


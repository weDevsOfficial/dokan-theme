<?php

if ( !class_exists( 'WeDevs_Settings_API' ) ) {
    require_once DOKAN_LIB_DIR . '/class.settings-api.php';
}

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
class Dokan_Admin_Settings {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function dashboard_script() {
        $template = get_template_directory_uri() . '/assets/';

        wp_enqueue_style( 'dokan-admin-dash', $template . '/css/admin.css' );
        $this->report_scripts();
    }

    function report_scripts() {
        $template_directory = get_template_directory_uri();

        wp_enqueue_style( 'dokan-admin-report', $template_directory . '/assets/css/admin-report.css' );
        wp_enqueue_style( 'jquery-ui', $template_directory . '/assets/css/jquery-ui-1.10.0.custom.css', false, null );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-flot', $template_directory . '/assets/js/jquery.flot.min.js', false, null, true );
        wp_enqueue_script( 'jquery-flot-time', $template_directory . '/assets/js/jquery.flot.time.min.js', false, null, true );
        wp_enqueue_script( 'jquery-flot-pie', $template_directory . '/assets/js/jquery.flot.pie.min.js', false, null, true );
        wp_enqueue_script( 'jquery-flot-stack', $template_directory . '/assets/js/jquery.flot.stack.min.js', false, null, true );
        wp_enqueue_script( 'jquery-chart', $template_directory . '/assets/js/Chart.min.js', false, null, true );
    }

    function admin_init() {
        Dokan_Template_Withdraw::init()->bulk_action_handler();

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        $menu_position = apply_filters( 'doakn_menu_position', 17 );
        $capability = apply_filters( 'doakn_menu_capability', 'manage_options' );
        $withdraw = dokan_get_withdraw_count();
        $withdraw_text = __( 'Withdraw', 'dokan' );

        if ( $withdraw['pending'] ) {
            $withdraw_text = sprintf( __( 'Withdraw %s', 'dokan' ), '<span class="awaiting-mod count-1"><span class="pending-count">' . $withdraw['pending'] . '</span></span>');
        }

        $dashboard = add_menu_page( __( 'Dokan', 'dokan' ), __( 'Dokan', 'dokan' ), $capability, 'dokan', array($this, 'dashboard'), 'dashicons-vault', $menu_position );
        add_submenu_page( 'dokan', __( 'Dokan Dashboard', 'dokan' ), __( 'Dashboard', 'dokan' ), $capability, 'dokan', array($this, 'dashboard') );
        add_submenu_page( 'dokan', __( 'Withdraw', 'dokan' ), $withdraw_text, $capability, 'dokan-withdraw', array($this, 'withdraw_page') );
        add_submenu_page( 'dokan', __( 'Sellers Listing', 'dokan' ), __( 'All Sellers', 'dokan' ), $capability, 'dokan-sellers', array($this, 'seller_listing') );
        $report = add_submenu_page( 'dokan', __( 'Earning Reports', 'dokan' ), __( 'Earning Reports', 'dokan' ), $capability, 'dokan-reports', array($this, 'report_page') );
        add_submenu_page( 'dokan', __( 'Slider', 'dokan' ), __( 'Slider', 'dokan' ), $capability, 'edit.php?post_type=dokan_slider' );

        do_action( 'dokan_admin_menu' );

        add_submenu_page( 'dokan', __( 'Settings', 'dokan' ), __( 'Settings', 'dokan' ), $capability, 'dokan-settings', array($this, 'settings_page') );
        add_submenu_page( 'dokan', __( 'Add Ons', 'dokan' ), __( 'Add-ons', 'dokan' ), $capability, 'dokan-addons', array($this, 'addon_page') );

        add_action( $dashboard, array($this, 'dashboard_script' ) );
        add_action( $report, array($this, 'report_scripts' ) );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'dokan_general',
                'title' => __( 'General', 'dokan' )
            ),
            array(
                'id' => 'dokan_home',
                'title' => __( 'Home Page', 'dokan' )
            ),
            array(
                'id' => 'dokan_selling',
                'title' => __( 'Selling Options', 'dokan' )
            ),
            array(
                'id' => 'dokan_pages',
                'title' => __( 'Page Settings', 'dokan' )
            )
        );
        return apply_filters( 'dokan_settings_sections', $sections );
    }

    function get_post_type( $post_type ) {
        $pages_array = array( '-1' => __( '- select -', 'dokan' ) );
        $pages = get_posts( array('post_type' => $post_type, 'numberposts' => -1) );

        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }

        return $pages_array;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $pages_array = $this->get_post_type( 'page' );
        $slider_array = $this->get_post_type( 'dokan_slider' );

        $settings_fields = array(
            'dokan_general' => array(
                'admin_access' => array(
                    'name' => 'admin_access',
                    'label' => __( 'Admin area access', 'dokan' ),
                    'desc' => __( 'Disable sellers and customers from accessing wp-admin area', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'color_skin' => array(
                    'name' => 'color_skin',
                    'label' => __( 'Color Skin', 'dokan' ),
                    'desc' => __( 'Choose the color skin of the theme', 'dokan' ),
                    'type' => 'select',
                    'default' => 'orange',
                    'options' => apply_filters( 'dokan_color_skin', array(
                        'orange.css' => __( 'Orange', 'dokan' ),
                        'sky.css' => __( 'Sky', 'dokan' ),
                        'blue.css' => __( 'Blue', 'dokan' ),
                        'green.css' => __( 'Green', 'dokan' ),
                        'purple.css' => __( 'Purple', 'dokan' ),
                        'red.css' => __( 'Red', 'dokan' ),
                        'pink.css' => __( 'Pink', 'dokan' ),
                    ) )
                ),
                'store_map' => array(
                    'name' => 'store_map',
                    'label' => __( 'Show Map on Store Page', 'dokan' ),
                    'desc' => __( 'Enable showing Store location map on store left sidebar', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'contact_seller' => array(
                    'name' => 'contact_seller',
                    'label' => __( 'Show Contact Form on Store Page', 'dokan' ),
                    'desc' => __( 'Enable showing contact seller form on store left sidebar', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'footer_text' => array(
                    'name' => 'footer_text',
                    'label' => __( 'Site footer text', 'dokan' ),
                    'desc' => '',
                    'default' => sprintf( __( '&copy; %d. All rights are reserved', 'dokan' ), date('Y') ),
                    'type' => 'text',
                ),
            ),
            'dokan_selling' => array(
                'new_seller_enable_selling' => array(
                    'name' => 'new_seller_enable_selling',
                    'label' => __( 'New Seller Enable Selling', 'dokan' ),
                    'desc' => __( 'Make selling status enable for new registred seller', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'product_status' => array(
                    'name' => 'product_status',
                    'label' => __( 'New Product Status', 'dokan' ),
                    'desc' => __( 'Product status when a seller creates a product', 'dokan' ),
                    'type' => 'select',
                    'default' => 'pending',
                    'options' => array(
                        'publish' => __( 'Published', 'dokan' ),
                        'pending' => __( 'Pending Review', 'dokan' )
                    )
                ),
                'seller_percentage' => array(
                    'name' => 'seller_percentage',
                    'label' => __( 'Seller Percentage', 'dokan' ),
                    'desc' => __( 'How much amount (%) a seller will get from each order', 'dokan' ),
                    'default' => '90',
                    'type' => 'text',
                ),
                'order_status_change' => array(
                    'name' => 'order_status_change',
                    'label' => __( 'Order Status Change', 'dokan' ),
                    'desc' => __( 'Seller Could Change Order Status', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'withdraw_methods' => array(
                    'name' => 'withdraw_methods',
                    'label' => __( 'Withdraw Methods', 'dokan' ),
                    'desc' => __( 'Withdraw methods for sellers', 'dokan' ),
                    'type' => 'multicheck',
                    'default' => array( 'paypal' => 'paypal' ),
                    'options' => dokan_withdraw_get_methods()
                ),
                'withdraw_order_status' => array(
                    'name' => 'withdraw_order_status',
                    'label' => __( 'Order Status for Withdraw', 'dokan' ),
                    'desc' => __( 'Order status for which seller can make a withdraw request.', 'dokan' ),
                    'type' => 'multicheck',
                    'default' => array( 'completed' => __( 'Completed', 'dokan' ), 'processing' => __( 'Processing', 'dokan' ), 'on-hold' => __( 'On-hold', 'dokan' ) ),
                    'options' => array( 'completed' => __( 'Completed', 'dokan' ), 'processing' => __( 'Processing', 'dokan' ), 'on-hold' => __( 'On-hold', 'dokan' ) )
                ),
                'withdraw_limit' => array(
                    'name' => 'withdraw_limit',
                    'label' => __( 'Minimum Withdraw Limit', 'dokan' ),
                    'desc' => __( 'Minimum balance required to make a withdraw request', 'dokan' ),
                    'default' => '50',
                    'type' => 'text',
                ),
            ),
            'dokan_home' => array(
                'show_slider' => array(
                    'name' => 'show_slider',
                    'label' => __( 'Slider', 'dokan' ),
                    'desc' => __( 'Show Slider', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'slider_id' => array(
                    'name' => 'slider_id',
                    'label' => __( 'Select Slider', 'dokan' ),
                    'desc' => __( 'Select which slider to show on homepage', 'dokan' ),
                    'type' => 'select',
                    'options' => $slider_array
                ),
                'show_featured' => array(
                    'name' => 'show_featured',
                    'label' => __( 'Featured Products', 'dokan' ),
                    'desc' => __( 'Show Featured Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'show_latest' => array(
                    'name' => 'show_latest',
                    'label' => __( 'Latest Products', 'dokan' ),
                    'desc' => __( 'Show Latest Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'show_best_selling' => array(
                    'name' => 'show_best_selling',
                    'label' => __( 'Best Selling Products', 'dokan' ),
                    'desc' => __( 'Show Best Selling Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'show_top_rated' => array(
                    'name' => 'show_top_rated',
                    'label' => __( 'Top Rated Products', 'dokan' ),
                    'desc' => __( 'Show Top Rated Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                'show_on_sale' => array(
                    'name' => 'show_on_sale',
                    'label' => __( 'On Sale Products', 'dokan' ),
                    'desc' => __( 'Show On Sale Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
            ),
            'dokan_pages' => array(
                'dashboard' => array(
                    'name' => 'dashboard',
                    'label' => __( 'Dashboard', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'products' => array(
                    'name' => 'products',
                    'label' => __( 'Products Listing', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'new_product' => array(
                    'name' => 'new_product',
                    'label' => __( 'Create Product Page', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'orders' => array(
                    'name' => 'orders',
                    'label' => __( 'Orders', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'coupons' => array(
                    'name' => 'coupons',
                    'label' => __( 'Coupons', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'reports' => array(
                    'name' => 'reports',
                    'label' => __( 'Reports', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'reviews' => array(
                    'name' => 'reviews',
                    'label' => __( 'Reviews', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'withdraw' => array(
                    'name' => 'withdraw',
                    'label' => __( 'Withdraw', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'settings' => array(
                    'name' => 'settings',
                    'label' => __( 'Settings', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                'my_orders' => array(
                    'name' => 'my_orders',
                    'label' => __( 'My Orders', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
            )
        );

        return apply_filters( 'dokan_settings_fields', $settings_fields );
    }

    function dashboard() {
        include dirname(__FILE__) . '/dashboard.php';
    }

    function settings_page() {
        echo '<div class="wrap">';
        settings_errors();

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    function withdraw_page() {
        include dirname(__FILE__) . '/withdraw.php';
    }

    function seller_listing() {
        include dirname(__FILE__) . '/sellers.php';
    }

    function report_page() {
        global $wpdb;

        dokan_reports_scripts();

        include dirname(__FILE__) . '/reports.php';
    }

    function addon_page() {
        include dirname(__FILE__) . '/add-on.php';
    }

}

$settings = new Dokan_Admin_Settings();

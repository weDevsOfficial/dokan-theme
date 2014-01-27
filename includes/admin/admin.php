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
    }

    function admin_init() {
        Dokan_Template_Withdraw::init()->withdraw_csv();
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        $menu_position = apply_filters( 'doakn_menu_position', 17 );
        $capability = apply_filters( 'doakn_menu_capability', 'activate_plugins' );

        $dashboard = add_menu_page( __( 'Dokan', 'dokan' ), __( 'Dokan', 'dokan' ), $capability, 'dokan', array($this, 'dashboard'), 'dashicons-vault', $menu_position );
        add_submenu_page( 'dokan', __( 'Withdraw', 'dokan' ), __( 'Withdraw', 'dokan' ), $capability, 'dokan-withdraw', array($this, 'withdraw_page') );
        add_submenu_page( 'dokan', __( 'Settings', 'dokan' ), __( 'Settings', 'dokan' ), $capability, 'dokan-settings', array($this, 'settings_page') );

        add_action( $dashboard, array($this, 'dashboard_script' ) );
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

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $pages_array = array();
        $pages = get_posts( array('post_type' => 'page', 'numberposts' => -1) );

        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }

        $settings_fields = array(
            'dokan_general' => array(
                array(
                    'name' => 'footer_text',
                    'label' => __( 'Site footer text', 'dokan' ),
                    'desc' => '',
                    'default' => sprintf( __( '&copy; %d. All rights are reserved', 'dokan' ), date('Y') ),
                    'type' => 'text',
                ),
            ),
            'dokan_selling' => array(
                array(
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
                array(
                    'name' => 'seller_percentage',
                    'label' => __( 'Seller Percentage', 'dokan' ),
                    'desc' => __( 'How much amount (%) a seller will get from each order', 'dokan' ),
                    'default' => '90',
                    'type' => 'text',
                ),
                array(
                    'name' => 'withdraw_limit',
                    'label' => __( 'Minimum Withdraw Limit', 'dokan' ),
                    'desc' => __( 'Minimum balance required to make a withdraw request', 'dokan' ),
                    'default' => '50',
                    'type' => 'text',
                ),
            ),
            'dokan_home' => array(
                array(
                    'name' => 'show_slider',
                    'label' => __( 'Slider', 'dokan' ),
                    'desc' => __( 'Show Slider', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name' => 'show_featured',
                    'label' => __( 'Featured Products', 'dokan' ),
                    'desc' => __( 'Show Featured Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name' => 'show_latest',
                    'label' => __( 'Latest Products', 'dokan' ),
                    'desc' => __( 'Show Latest Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name' => 'show_best_selling',
                    'label' => __( 'Best Selling Products', 'dokan' ),
                    'desc' => __( 'Show Best Selling Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name' => 'show_top_rated',
                    'label' => __( 'Top Rated Products', 'dokan' ),
                    'desc' => __( 'Show Top Rated Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name' => 'show_on_sale',
                    'label' => __( 'On Sale Products', 'dokan' ),
                    'desc' => __( 'Show On Sale Products', 'dokan' ),
                    'type' => 'checkbox',
                    'default' => 'on'
                ),
            ),
            'dokan_pages' => array(
                array(
                    'name' => 'dashboard',
                    'label' => __( 'Dashboard', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'products',
                    'label' => __( 'Products Listing', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'new_product',
                    'label' => __( 'Create Product Page', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'orders',
                    'label' => __( 'Orders', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'coupons',
                    'label' => __( 'Coupons', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'reports',
                    'label' => __( 'Reports', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'reviews',
                    'label' => __( 'Reviews', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'withdraw',
                    'label' => __( 'Withdraw', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),
                array(
                    'name' => 'settings',
                    'label' => __( 'Settings', 'dokan' ),
                    'type' => 'select',
                    'options' => $pages_array
                ),

            )
        );

        return apply_filters( 'dokan_settings_fields', $settings_fields );
    }

    function dashboard() {
        include __DIR__ . '/dashboard.php';
    }

    function settings_page() {
        echo '<div class="wrap">';
        settings_errors();

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    function withdraw_page() {
        include __DIR__ . '/withdraw.php';
    }

}

$settings = new Dokan_Admin_Settings();

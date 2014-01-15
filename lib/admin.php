<?php

require_once dirname( __FILE__ ) . '/class.settings-api.php';

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

<<<<<<< HEAD
        add_menu_page( __( 'Doakn', 'dokan' ), __( 'Doakn', 'dokan' ), $capability, 'dokan', array($this, 'settings_page'), null, $menu_position );
        $withdraw = add_submenu_page( 'dokan', __( 'Withdraw', 'dokan' ), __( 'Withdraw', 'dokan' ), $capability, 'dokan-withdraw', array($this, 'withdraw_page') );

=======
        add_menu_page( __( 'Dokan', 'dokan' ), __( 'Dokan', 'dokan' ), $capability, 'dokan', array($this, 'settings_page'), 'dashicons-vault', $menu_position );
        add_submenu_page( 'dokan', __( 'Settings', 'dokan' ), __( 'Settings', 'dokan' ), $capability, 'dokan', array($this, 'settings_page') );
        add_submenu_page( 'dokan', __( 'Withdraw', 'dokan' ), __( 'Withdraw', 'dokan' ), $capability, 'dokan-withdraw', array($this, 'withdraw_page') );
>>>>>>> c1775c87b641f118a3b699216157b7449149ecbf
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'dokan_general',
                'title' => __( 'General Settings', 'dokan' )
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
                    'name' => 'seller_percentage',
                    'label' => __( 'Seller Percentage', 'dokan' ),
                    'desc' => __( 'How much amount (%) a seller will get from each order', 'dokan' ),
                    'default' => '90',
                    'type' => 'text',
                ),
                array(
                    'name' => 'footer_text',
                    'label' => __( 'Site footer text', 'dokan' ),
                    'desc' => '',
                    'default' => sprintf( __( '&copy; %d. All rights are reserved', 'dokan' ), date('Y') ),
                    'type' => 'text',
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

    function settings_page() {
        echo '<div class="wrap">';
        settings_errors();

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    function withdraw_page() {
        $dokan_admin_withdraw = Dokan_Template_Withdraw::init();
        $dokan_admin_withdraw->admin_withdraw_list();

    }

}

$settings = new Dokan_Admin_Settings();

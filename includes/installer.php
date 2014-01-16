<?php

/**
 * Dokan installer class
 *
 * @author weDevs
 */
class Dokan_Installer {

    function do_install() {
        $this->setup_pages();
        $this->create_tables();
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

    function create_tables() {

    }
}
<?php

/**
 * Dokan rewrite rules class
 *
 * @package Dokan
 */
class Dokan_Rewrites {

    function __construct() {
        add_action( 'init', array($this, 'register_rule') );
        add_filter( 'template_include', array($this, 'store_template') );
        add_filter( 'template_include', array($this, 'product_edit_template'), 11 );
        add_filter( 'query_vars', array($this, 'register_query_var') );
        add_filter( 'pre_get_posts', array($this, 'store_query_filter') );
    }

    /**
     * Register the rewrite rule
     *
     * @return void
     */
    function register_rule() {
        $permalinks = get_option( 'woocommerce_permalinks', array() );
        if( isset( $permalinks['product_base'] ) ) {
            $base = substr( $permalinks['product_base'], 1 );
        }

        if ( !empty( $base ) ) {
            add_rewrite_rule( $base . '/([^/]+)(/[0-9]+)?/edit/?$', 'index.php?product=$matches[1]&page=$matches[2]&edit=true', 'top' );
        }

        add_rewrite_rule( 'store/([^/]+)/?$', 'index.php?store=$matches[1]', 'top' );
        add_rewrite_rule( 'store/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?store=$matches[1]&paged=$matches[2]', 'top' );

        add_rewrite_rule( 'store/([^/]+)/reviews?$', 'index.php?store_review=$matches[1]', 'top' );
        add_rewrite_rule( 'store/([^/]+)/reviews/page/?([0-9]{1,})/?$', 'index.php?store_review=$matches[1]&paged=$matches[2]', 'top' );
    }

    /**
     * Register the query var
     *
     * @param array $vars
     * @return array
     */
    function register_query_var( $vars ) {
        $vars[] = 'store';
        $vars[] = 'store_review';
        $vars[] = 'edit';

        return $vars;
    }

    /**
     * Include store template
     *
     * @param type $template
     * @return string
     */
    function store_template( $template ) {

        $store_name = get_query_var( 'store' );

        if ( !empty( $store_name ) ) {
            $store_user = get_user_by( 'slug', $store_name );

            // no user found
            if ( !$store_user ) {
                return get_404_template();
            }

            // check if the user is seller
            if ( !dokan_is_user_seller( $store_user->ID ) ) {
                return get_404_template();
            }

            $templates = array(
                "store-{$store_name}.php",
                'store.php',
            );

            return get_query_template( 'store', $templates );
        }

        return $template;
    }

    function product_edit_template( $template ) {
        if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {

            return get_template_directory() . '/templates/product-edit.php';
        }

        return $template;
    }

    function store_query_filter( $query ) {
        global $wp_query;

        $author = get_query_var( 'store' );

        if ( !is_admin() && $query->is_main_query() && !empty( $author ) ) {
            $query->set( 'post_type', 'product' );
            $query->set( 'author_name', $author );
        }
    }

}

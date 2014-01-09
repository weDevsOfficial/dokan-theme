<?php

/**
 * Dokan rewrite rules class
 *
 * @package Dokan
 */
class Dokan_Rewrites {

    function __construct() {
        add_action( 'init', array($this, 'register_rule') );
        add_filter( 'template_include', array($this, 'include_template') );
        add_filter( 'query_vars', array($this, 'register_query_var') );
    }

    /**
     * Register the rewrite rule
     *
     * @return void
     */
    function register_rule() {
        add_rewrite_rule( 'store/([^/]+)/?$', 'index.php?store=$matches[1]', 'top' );
        add_rewrite_rule( 'store/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?store=$matches[1]&&paged=$matches[2]', 'top' );
    }

    /**
     * Register the query var
     *
     * @param array $vars
     * @return array
     */
    function register_query_var( $vars ) {
        $vars[] = 'store';

        return $vars;
    }

    /**
     * Include store template
     *
     * @param type $template
     * @return string
     */
    function include_template( $template ) {

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

}

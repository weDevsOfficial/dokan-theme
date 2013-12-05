<?php

class Dokan_Ajax{
    public static function init() {
        
        static $instance = false;
        
        if( !$instance ) {
            $instance = new self;
        }

        return $instance;
    }

    function all_ajax_action() {
        //withdraw note
        $withdraw = Dokan_Template_Withdraw::init();
        add_action('wp_ajax_note', array( $withdraw, 'note_update' ) );

        // reviews
        $reviews = Dokan_Template_reviews::init();
        add_action( 'wp_ajax_wpuf_comment_status', array( $reviews, 'ajax_comment_status' ) );
        add_action( 'wp_ajax_wpuf_update_comment', array( $reviews, 'ajax_update_comment' ) );

        //withdraw note
        $settings = Dokan_Template_Settings::init();
        add_action('wp_ajax_dokan_settings', array( $settings, 'ajax_settings' ) );

    }


}
jQuery(function( $ ) {
    "use strict";

    wp.customize( 'theme_logo', function( value ) {
        value.bind( function( to ) {
            $( '.site-header h1.site-title a' ).css( 'background', "url("+to+") no-repeat scroll 0 0 rgba(0, 0, 0, 0)");
        } );
    });

    wp.customize( 'header_bg_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-header' ).css( 'background-color', to );
        } );
    });

    wp.customize( 'nav_bg_color', function( value ) {
        value.bind( function( to ) {
            $( '.navbar-default' ).css( 'background-color', to );
        } );
    });

    wp.customize( 'nav_color', function( value ) {
        value.bind( function( to ) {
            $( '.navbar-default .navbar-nav > li > a' ).css( 'color', to );
        } );
    });

    wp.customize( 'nav_hover_color', function( value ) {
        value.bind( function( to ) {
            $( '.navbar-default .navbar-nav > li > a:hover' ).css( 'color', to );
        } );
    });

    wp.customize( 'link_color', function( value ) {
        value.bind( function( to ) {
            $( 'a' ).css( 'color', to );
        } );
    });


    wp.customize( 'link_hover_color', function( value ) {
        value.bind( function( to ) {
            $( 'a:hover' ).css( 'color', to );
        } );
    });

    wp.customize( 'widget_header_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-footer .widget h3' ).css( 'color', to );
        } );
    });

    wp.customize( 'widget_text_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-footer .widget ul li a' ).css( 'color', to );
        } );
    });

    wp.customize( 'widget_text_hover_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-footer .widget ul li a:hover' ).css( 'color', to );
        } );
    });

    wp.customize( 'footer_text_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-footer' ).css( 'color', to );
        } );
    });


    wp.customize( 'footer_bg_color', function( value ) {
        value.bind( function( to ) {
            $( '.site-footer' ).css( 'background-color', to );
        } );
    });
    
});
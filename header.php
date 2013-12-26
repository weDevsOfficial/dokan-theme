<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php
    /*
     * Print the <title> tag based on what is being viewed.
     */
    global $page, $paged;

    wp_title( '|', true, 'right' );

    // Add the blog name.
    bloginfo( 'name' );

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        echo " | $site_description";

    // Add a page number if necessary:
    if ( $paged >= 2 || $page >= 2 )
        echo ' | ' . sprintf( __( 'Page %s', 'wedevs' ), max( $paged, $page ) );

    ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="page" class="hfeed site">
        <?php do_action( 'before' ); ?>

        <nav class="navbar navbar-inverse navbar-top-area" role="navigation">
            <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        wp_nav_menu( array(
                            'menu'              => 'primary',
                            'theme_location'    => 'top-left',
                            // 'depth'             => 3,
                            'container'         => 'div',
                            'container_class'   => 'collapse navbar-collapse navbar-ex1-collapse',
                            'menu_class'        => 'nav navbar-nav',
                            'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                            'walker'            => new wp_bootstrap_navwalker())
                        );
                    ?>
                </div>
            </div>
            </div>
        </nav>

        <header id="masthead" class="site-header" role="banner">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <hgroup>
                            <h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> <small> - <?php bloginfo( 'description' ); ?></small></a></h1>
                        </hgroup>
                    </div><!-- .col-md-6 -->

                    <div class="col-md-8">
                        <ul class="cart-area-top list-inline">

                            <?php if ( is_user_logged_in() ) { ?>
                                <li><i class="icon-off"></i> <?php wp_loginout( home_url() ); ?></li>
                                <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><i class="icon-user"></i> Account</a></li>
                            <?php } else { ?>
                                <li><i class="icon-off"></i> <?php wp_loginout( home_url() ); ?></li>
                            <?php } ?>

                            <li class="nav-cart-link"><?php dokan_checkout_header_btn(); ?></li>
                            <script type="text/javascript">
                                jQuery(function($) {
                                    $('a#nav-cart-pop').popover({
                                        html: true,
                                        placement: 'bottom',
                                        trigger: 'manual',
                                        content: $('.nav-cart-link .cart-items').html()
                                    }).click(function(evt) {
                                        evt.stopPropagation();
                                        $(this).popover('show');
                                    });;

                                    $('html').click(function() {
                                        $('a#nav-cart-pop').popover('hide');
                                    });
                                });
                                </script>
                        </ul>
                    </div>
                </div><!-- .row -->
            </div><!-- .container -->

            <div class="menu-container">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <nav role="navigation" class="site-navigation main-navigation clearfix">
                                <h1 class="assistive-text"><i class="icon-reorder"></i> <?php _e( 'Menu', 'wedevs' ); ?></h1>
                                <div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'wedevs' ); ?>"><?php _e( 'Skip to content', 'wedevs' ); ?></a></div>
                                    <nav class="navbar navbar-default" role="navigation">
                                        <div class="navbar-header">
                                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                                                <span class="sr-only">Toggle navigation</span>
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                            </button>
                                            <a class="navbar-brand" href="<?php bloginfo('url')?>"><i class="icon-home"></i></a>
                                        </div>
                                        <div class="collapse navbar-collapse navbar-ex1-collapse">
                                            <?php
                                                wp_nav_menu( array(
                                                    'menu'              => 'primary',
                                                    'theme_location'    => 'primary',
                                                    // 'depth'             => 3,
                                                    'container'         => 'div',
                                                    'container_class'   => 'collapse navbar-collapse navbar-ex1-collapse',
                                                    'menu_class'        => 'nav navbar-nav',
                                                    'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                                                    'walker'            => new wp_bootstrap_navwalker())
                                                );
                                            ?>
                                        </div>
                                    </nav>
                            </nav><!-- .site-navigation .main-navigation -->
                        </div><!-- .span12 -->
                    </div><!-- .row -->
                </div><!-- .container -->
            </div> <!-- .menu-container -->
        </header><!-- #masthead .site-header -->

        <div id="main" class="site-main">
            <div class="container content-wrap">
                <div class="row">

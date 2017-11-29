<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class( 'woocommerce' ); ?>>

    <div id="page" class="hfeed site">
        <?php do_action( 'before' ); ?>

        <nav class="navbar navbar-inverse navbar-top-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-5">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-top-collapse">
                                <span class="sr-only"><?php _e( 'Toggle navigation', 'dokan-theme' ); ?></span>
                                <i class="fa fa-bars"></i>
                            </button>
                        </div>
                        <?php
                            wp_nav_menu( array(
                                'theme_location'    => 'top-left',
                                'depth'             => 0,
                                'container'         => 'div',
                                'container_class'   => 'collapse navbar-collapse navbar-top-collapse',
                                'menu_class'        => 'nav navbar-nav',
                                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                                'walker'            => new wp_bootstrap_navwalker())
                            );
                        ?>
                    </div>

                    <div class="col-md-6 col-sm-7">
                        <div class="collapse navbar-collapse navbar-top-collapse">
                            <?php dokan_header_user_menu(); ?>
                        </div>
                    </div>
                </div> <!-- .row -->
            </div> <!-- .container -->
        </nav>

        <header id="masthead" class="site-header" role="banner">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-sm-5">
                        <hgroup>
                            <h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> <small> - <?php bloginfo( 'description' ); ?></small></a></h1>
                        </hgroup>
                    </div><!-- .col-md-6 -->

                    <div class="col-md-8 col-sm-7 clearfix">
                        <?php dynamic_sidebar( 'sidebar-header' ) ?>
                    </div>
                </div><!-- .row -->
            </div><!-- .container -->

            <div class="menu-container">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <nav role="navigation" class="site-navigation main-navigation clearfix">
                                <h1 class="assistive-text"><i class="icon-reorder"></i> <?php _e( 'Menu', 'dokan-theme' ); ?></h1>
                                <div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'dokan-theme' ); ?>"><?php _e( 'Skip to content', 'dokan-theme' ); ?></a></div>
                                    <nav class="navbar navbar-default" role="navigation">
                                        <div class="navbar-header">
                                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                                                <span class="sr-only"><?php _e( 'Toggle navigation', 'dokan-theme' ); ?></span>
                                                <i class="fa fa-bars"></i>
                                            </button>
                                            <a class="navbar-brand" href="<?php echo home_url(); ?>"><i class="fa fa-home"></i> <?php _e( 'Home', 'dokan-theme' ); ?></a>
                                        </div>
                                        <div class="collapse navbar-collapse navbar-main-collapse">
                                            <?php
                                                wp_nav_menu( array(
                                                    'theme_location'    => 'primary',
                                                    'container'         => 'div',
                                                    'container_class'   => 'collapse navbar-collapse navbar-main-collapse',
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

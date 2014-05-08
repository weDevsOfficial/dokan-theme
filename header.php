<?php
if ( !function_exists( 'WC' ) ) {
    wp_die( sprintf( __( 'Please install <a href="%s"><strong>WooCommerce</strong></a> plugin first', 'dokan' ), 'http://wordpress.org/plugins/woocommerce/' ) );
}
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
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="page" class="hfeed site">
        <?php do_action( 'before' ); ?>

        <nav class="navbar navbar-inverse navbar-top-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-top-collapse">
                                <span class="sr-only"><?php _e( 'Toggle navigation', 'dokan' ); ?></span>
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

                    <div class="col-md-6">
                        <div class="collapse navbar-collapse navbar-top-collapse">
                            <ul class="nav navbar-nav navbar-right">
                                <li>
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php printf( __( 'Cart %s', 'dokan' ), '<span class="dokan-cart-amount-top">(' . WC()->cart->get_cart_total() . ')</span>' ); ?> <b class="caret"></b></a>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <div class="widget_shopping_cart_content"></div>
                                        </li>
                                    </ul>
                                </li>

                                <?php if ( is_user_logged_in() ) { ?>

                                    <?php
                                    global $current_user;

                                    $user_id = $current_user->ID;
                                    if ( dokan_is_user_seller( $user_id ) ) {
                                        ?>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php _e( 'Seller Dashboard', 'dokan' ); ?> <b class="caret"></b></a>

                                            <ul class="dropdown-menu">
                                                <li><a href="<?php echo dokan_get_store_url( $user_id ); ?>" target="_blank"><?php _e( 'Visit your store', 'dokan' ); ?> <i class="fa fa-external-link"></i></a></li>
                                                <li class="divider"></li>
                                                <?php
                                                $nav_urls = dokan_get_dashboard_nav();

                                                foreach ($nav_urls as $key => $item) {
                                                    printf( '<li><a href="%s">%s &nbsp;%s</a></li>', $item['url'], $item['icon'], $item['title'] );
                                                }
                                                ?>
                                            </ul>
                                        </li>
                                    <?php } ?>

                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo esc_html( $current_user->display_name ); ?> <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?php echo dokan_get_page_url( 'my_orders' ); ?>"><?php _e( 'My Orders', 'dokan' ); ?></a></li>
                                            <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'My Account', 'dokan' ); ?></a></li>
                                            <li><a href="<?php echo wc_customer_edit_account_url(); ?>"><?php _e( 'Edit Account', 'dokan' ); ?></a></li>
                                            <li class="divider"></li>
                                            <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'billing', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Billing Address', 'dokan' ); ?></a></li>
                                            <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'shipping', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Shipping Address', 'dokan' ); ?></a></li>
                                        </ul>
                                    </li>

                                    <li><?php wp_loginout( home_url() ); ?></li>

                                <?php } else { ?>
                                    <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'Log in', 'dokan' ); ?></a></li>
                                    <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'Sign Up', 'dokan' ); ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>

                    </div>
                </div> <!-- .row -->
            </div> <!-- .container -->
        </nav>

        <header id="masthead" class="site-header" role="banner">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <hgroup>
                            <h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> <small> - <?php bloginfo( 'description' ); ?></small></a></h1>
                        </hgroup>
                    </div><!-- .col-md-6 -->

                    <div class="col-md-8 clearfix">
                        <?php dynamic_sidebar( 'sidebar-header' ) ?>
                    </div>
                </div><!-- .row -->
            </div><!-- .container -->

            <div class="menu-container">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <nav role="navigation" class="site-navigation main-navigation clearfix">
                                <h1 class="assistive-text"><i class="icon-reorder"></i> <?php _e( 'Menu', 'dokan' ); ?></h1>
                                <div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'dokan' ); ?>"><?php _e( 'Skip to content', 'dokan' ); ?></a></div>
                                    <nav class="navbar navbar-default" role="navigation">
                                        <div class="navbar-header">
                                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                                                <span class="sr-only"><?php _e( 'Toggle navigation', 'dokan' ); ?></span>
                                                <i class="fa fa-bars"></i>
                                            </button>
                                            <a class="navbar-brand" href="<?php echo home_url(); ?>"><i class="fa fa-home"></i> <?php _e( 'Home', 'dokan' ); ?></a>
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

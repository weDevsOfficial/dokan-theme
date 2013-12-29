<?php
/**
 * Template Name: Dashboard - Products
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

$action = isset( $_GET['action'] ) ? $_GET['action'] : 'listing';

if ( $action == 'edit' ) {

    include dirname( __FILE__ ) . '/product-edit.php';

} else {
    include dirname( __FILE__ ) . '/products-listing.php';
}
<?php
$home_url = home_url();
$active_class = ' class="active"'
?>

<div class="col-md-2 dokan-dash-sidebar">
    <ul class="dokan-dashboard-menu">
        <li<?php echo ( $active_menu == 'dashboard' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('dashboard'); ?>"><i class="icon-dashboard"></i> Dashboard</a></li>
        <li<?php echo ( $active_menu == 'product' ) ? $active_class : ''; ?>>
            <a href="<?php echo dokan_get_page_url('products'); ?>" class=""><i class="icon-briefcase"></i> Products</a>

            <ul class="sub-menu">
                <li><a href="#">Products</a></li>
                <li><a href="#">Add Product</a></li>
            </ul>
        </li>
        <li<?php echo ( $active_menu == 'order' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('orders'); ?>" class=""><i class="icon-basket"></i> Orders</a></li>
        <li<?php echo ( $active_menu == 'coupon' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('coupons'); ?>" class=""><i class="icon-gift"></i> Coupons</a></li>
        <li<?php echo ( $active_menu == 'reports' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('reports'); ?>" class=""><i class="icon-stats"></i> Reports</a></li>
        <li<?php echo ( $active_menu == 'reviews' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('reviews'); ?>" class=""><i class="icon-bubbles"></i> Reivews</a></li>
        <li<?php echo ( $active_menu == 'withdraw' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('withdraw'); ?>" class=""><i class="icon-upload"></i> Withdraw</a></li>
        <li<?php echo ( $active_menu == 'settings' ) ? $active_class : ''; ?>><a href="<?php echo dokan_get_page_url('settings'); ?>" class=""><i class="icon-cog"></i> Settings</a></li>
    </ul>
</div>
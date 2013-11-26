<?php
$home_url = home_url();
$active_class = ' class="active"'
?>

<div class="row col-md-2 dokan-dash-sidebar">
    <ul class="dokan-dashboard-menu">
        <li<?php echo ( $active_menu == 'dashboard' ) ? $active_class : ''; ?>><a href="<?php echo $home_url; ?>/dashboard"><i class="icon-dashboard"></i> Dashboard</a></li>
        <li<?php echo ( $active_menu == 'product' ) ? $active_class : ''; ?>><a href="<?php echo $home_url; ?>/dashboard/products" class=""><i class="icon-briefcase"></i> Products</a></li>
        <li<?php echo ( $active_menu == 'order' ) ? $active_class : ''; ?>><a href="<?php echo $home_url; ?>/dashboard/orders" class=""><i class="icon-basket"></i> Orders</a></li>
        <li<?php echo ( $active_menu == 'coupon' ) ? $active_class : ''; ?>><a href="<?php echo $home_url; ?>/" class=""><i class="icon-gift"></i> Coupons</a></li>
        <li><a href="<?php echo $home_url; ?>/" class=""><i class="icon-stats"></i> Reports</a></li>
        <li><a href="<?php echo $home_url; ?>/" class=""><i class="icon-bubbles"></i> Reivews</a></li>
        <li><a href="<?php echo $home_url; ?>/" class=""><i class="icon-upload"></i> Withdraw</a></li>
        <li><a href="<?php echo $home_url; ?>/" class=""><i class="icon-cog"></i> Settings</a></li>
    </ul>
</div>
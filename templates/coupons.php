<?php
/**
 * Template Name: Dashboard - Coupon
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'coupon' ) ); ?>

    <div class="col-md-9">

        <p>
            <a href="#" class="btn btn-large btn-info">Add Coupon</a>
        </p>
    </div>
</div>

<?php get_footer(); ?>
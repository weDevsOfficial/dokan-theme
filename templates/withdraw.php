<?php
/**
 * Template Name: Dashboard - Withdraw
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'withdraw' ) ); ?>

    <div class="col-md-9">
        Withdraw
    </div>
</div>

<?php get_footer(); ?>
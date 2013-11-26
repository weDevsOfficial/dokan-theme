<?php
/**
 * Template Name: Dashboard - Reports
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'reports' ) ); ?>

    <div class="col-md-9">
        Reports
    </div>
</div>

<?php get_footer(); ?>
<?php
/**
 * Template Name: Dashboard - Settings
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'settings' ) ); ?>

    <div class="col-md-9">
        Settings
    </div>
</div>

<?php get_footer(); ?>
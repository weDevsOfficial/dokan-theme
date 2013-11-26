<?php
/**
 * Template Name: Dashboard - Reviews
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'reviews' ) ); ?>

    <div class="col-md-9">
        Reviews
    </div>
</div>

<?php get_footer(); ?>
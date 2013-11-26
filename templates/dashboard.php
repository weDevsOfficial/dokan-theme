<?php
/**
 * Template Name: Dashboard
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'dashboard' ) ); ?>

    <div class="col-md-9">

        <p>
            <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-large btn-info">Add new product</a>
        </p>
    </div>
</div>

<?php get_footer(); ?>
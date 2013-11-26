<?php
/**
 * Template Name: Dashboard
 */
?>

<div class="row">
    <div class="span3"><?php get_template_part( '/templates/dashboard-nav' ); ?></div>
    <div class="span9">

        <p>
            <a href="<?php echo home_url( 'new-product' ); ?>" class="btn btn-large btn-info">Add new product</a>
        </p>
    </div>
</div>
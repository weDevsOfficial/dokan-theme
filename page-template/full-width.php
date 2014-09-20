<?php
/**
 * The Template for displaying a full width page.
 *
 * Template Name: Full-width Page
 *
 * @package dokan
 * @package dokan - 2013 1.0
 */
get_header();
?>

<div id="primary" class="content-area col-md-12">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <?php get_template_part( 'content', 'page' ); ?>

            <?php
            // If comments are open or we have at least one comment, load up the comment template
            if ( comments_open() || '0' != get_comments_number() )
                comments_template( '', true );
            ?>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
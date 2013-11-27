<?php
/**
 * Template Name: Dashboard - Withdraw
 */
require_once __DIR__ . '/../classes/withdraw.php';

$dokan_withdraw = Dokan_withdraw::init();

// perform requests
$dokan_withdraw->insert_withdraw_info();
$dokan_withdraw->cancel_pending();

get_header();
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array('active_menu' => 'withdraw') ); ?>

<div id="primary" class="content-area col-md-10">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <?php $dokan_withdraw->withdraw_form(); ?>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->


<?php get_footer(); ?>
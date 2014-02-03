<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
?>
<div id="secondary" class="col-md-3 clearfix" role="complementary">

    <button type="button" class="navbar-toggle widget-area-toggle" data-toggle="collapse" data-target=".widget-area">
        <i class="fa fa-bars"></i>
        <span class="bar-title"><?php _e( 'Toggle Sidebar', 'dokan' ); ?></span>
    </button>

    <div class="widget-area collapse widget-collapse">

        <?php do_action( 'before_sidebar' ); ?>
        <?php if ( !dynamic_sidebar( 'sidebar-1' ) ) : ?>

            <aside id="search" class="widget widget_search">
                <?php get_search_form(); ?>
            </aside>

            <aside id="archives" class="widget">
                <h1 class="widget-title"><?php _e( 'Archives', 'dokan' ); ?></h1>
                <ul>
                    <?php wp_get_archives( array('type' => 'monthly') ); ?>
                </ul>
            </aside>

            <aside id="meta" class="widget">
                <h1 class="widget-title"><?php _e( 'Meta', 'dokan' ); ?></h1>
                <ul>
                    <?php wp_register(); ?>
                    <li><?php wp_loginout(); ?></li>
                    <?php wp_meta(); ?>
                </ul>
            </aside>

        <?php endif; // end sidebar widget area ?>
    </div>
</div><!-- #secondary .widget-area -->

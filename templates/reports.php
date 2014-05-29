<?php
/**
 * Template Name: Dashboard - Reports
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

get_header();

dokan_reports_scripts();
?>


<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'report' ) ); ?>

<div id="primary" class="content-area col-md-10 col-sm-9">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <div class="dokan-report-wrap">
                    <?php
                    global $woocommerce;

                    require_once dirname( dirname(__FILE__) ) . '/includes/reports.php';

                    $charts = dokan_get_reports_charts();

                    $link = get_permalink();
                    $current = isset( $_GET['chart'] ) ? $_GET['chart'] : 'overview';

                    echo '<ul class="nav nav-tabs">';
                    foreach ($charts['charts'] as $key => $value) {
                        $class = ( $current == $key ) ? ' class="active"' : '';
                        printf( '<li%s><a href="%s">%s</a></li>', $class, add_query_arg( array( 'chart' => $key ), $link ), $value['title'] );
                    }
                    echo '</ul>';
                    ?>

                    <?php if ( isset( $charts['charts'][$current] ) ) { ?>
                        <div class="tab-content">
                            <div class="tab-pane active" id="home">
                                <?php
                                $func = $charts['charts'][$current]['function'];
                                if ( $func && ( is_callable( $func ) ) ) {
                                    call_user_func( $func );
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
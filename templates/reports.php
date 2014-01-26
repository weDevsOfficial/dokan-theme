<?php
/**
 * Template Name: Dashboard - Reports
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

get_header();

wp_enqueue_script( 'jquery-flot' );
wp_enqueue_script( 'jquery-flot-time' );
wp_enqueue_script( 'jquery-flot-pie' );
wp_enqueue_script( 'jquery-flot-stack' );
?>


<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'report' ) ); ?>

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

                <div class="dokan-report-wrap">
                    <?php
                    global $woocommerce;

                    require_once dirname( __DIR__ ) . '/includes/reports.php';

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

<script type="text/javascript">
    jQuery(function($) {
    function showTooltip(x, y, contents) {
        jQuery('<div class="chart-tooltip">' + contents + '</div>').css({
            top: y - 16,
            left: x + 20
        }).appendTo("body").fadeIn(200);
    }

    var prev_data_index = null;
    var prev_series_index = null;

    jQuery(".chart-placeholder").bind("plothover", function(event, pos, item) {
        if (item) {
            if (prev_data_index != item.dataIndex || prev_series_index != item.seriesIndex) {
                prev_data_index = item.dataIndex;
                prev_series_index = item.seriesIndex;

                jQuery(".chart-tooltip").remove();

                if (item.series.points.show || item.series.enable_tooltip) {

                    var y = item.series.data[item.dataIndex][1];

                    tooltip_content = '';

                    if (item.series.prepend_label)
                        tooltip_content = tooltip_content + item.series.label + ": ";

                    if (item.series.prepend_tooltip)
                        tooltip_content = tooltip_content + item.series.prepend_tooltip;

                    tooltip_content = tooltip_content + y;

                    if (item.series.append_tooltip)
                        tooltip_content = tooltip_content + item.series.append_tooltip;

                    if (item.series.pie.show) {

                        showTooltip(pos.pageX, pos.pageY, tooltip_content);

                    } else {

                        showTooltip(item.pageX, item.pageY, tooltip_content);

                    }

                }
            }
        } else {
            jQuery(".chart-tooltip").remove();
            prev_data_index = null;
        }
    });
    });
</script>

<?php get_footer(); ?>
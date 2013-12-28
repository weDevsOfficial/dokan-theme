<?php
/**
 * Template Name: Dashboard
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

get_header();

wp_enqueue_script( 'jquery-chart' );
wp_enqueue_script( 'jquery-flot' );
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'dashboard' ) ); ?>

<div id="primary" class="content-area col-md-10">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- .entry-content -->

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-widget big-counter">
                            <ul class="list-inline">
                                <li>
                                    <div class="title">Pageview</div>
                                    <div class="count">1000</div>
                                </li>
                                <li>
                                    <div class="title">Sales</div>
                                    <div class="count">200</div>
                                </li>
                                <li>
                                    <div class="title">Earned</div>
                                    <div class="count">$500</div>
                                </li>
                            </ul>
                        </div> <!-- .big-counter -->

                        <div class="dashboard-widget orders">
                            <div class="widget-title"><i class="fa fa-shopping-cart"></i> Orders</div>

                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled list-count">
                                        <li><span class="title">Total</span> <span class="count">30</span></li>
                                        <li><span class="title">Completed</span> <span class="count">20</span></li>
                                        <li><span class="title">Pending</span> <span class="count">13</span></li>
                                        <li><span class="title">Processing</span> <span class="count">4</span></li>
                                        <li><span class="title">Cancelled</span> <span class="count">2</span></li>
                                    </ul>
                                </div>

                                <div class="col-md-6" style="text-align: center;">
                                    <canvas id="order-stats" width="130" height="130"></canvas>
                                </div>
                            </div>
                        </div> <!-- .orders -->

                        <div class="dashboard-widget reviews">
                            <div class="widget-title"><i class="fa fa-comments"></i> Reviews</div>

                            <ul class="list-unstyled list-count">
                                <li><span class="title">All</span> <span class="count">30</span></li>
                                <li><span class="title">Pending</span> <span class="count">20</span></li>
                                <li><span class="title">Spam</span> <span class="count">3</span></li>
                            </ul>
                        </div> <!-- .reviews -->

                    </div> <!-- .col-md-6 -->

                    <div class="col-md-6">
                        <div class="dashboard-widget sells-graph">
                            <div class="widget-title">Sales</div>

                            <div id="sell-stats" style="height: 295px;"></div>
                        </div> <!-- .sells-graph -->


                        <div class="dashboard-widget products">
                            <div class="widget-title">
                                <i class="icon-briefcase"></i> Products

                                <span class="pull-right">
                                    <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-success btn-sm">+ Add new product</a>
                                </span>
                            </div>

                            <ul class="list-unstyled list-count">
                                <li><span class="title">Total</span> <span class="count">30</span></li>
                                <li><span class="title">Live</span> <span class="count">20</span></li>
                                <li><span class="title">Offline</span> <span class="count">3</span></li>
                            </ul>
                        </div> <!-- .products -->

                    </div>
                </div>

            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<script type="text/javascript">
    jQuery(function($) {
        var order_stats = [
            {
                value: 30,
                color:"#F7464A"
            },
            {
                value : 50,
                color : "#E2EAE9"
            },
            {
                value : 100,
                color : "#D4CCC5"
            },
            {
                value : 40,
                color : "#949FB1"
            },
            {
                value : 120,
                color : "#5cb85c"
            }
        ];

        var sell_stats = {
            labels : ["January","February","March","April","May","June","July"],
            datasets : [
                {
                    fillColor : "rgba(220,220,220,0.5)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    data : [65,59,90,81,56,55,40]
                },
                {
                    fillColor : "rgba(151,187,205,0.5)",
                    strokeColor : "rgba(151,187,205,1)",
                    pointColor : "rgba(151,187,205,1)",
                    pointStrokeColor : "#fff",
                    // data : [28,48,40,19,96,27,100]
                    data : [[1385856000000,1],[1385942400000,0],[1386028800000,0],[1386115200000,0],[1386201600000,0],[1386288000000,0],[1386374400000,1],[1386460800000,0],[1386547200000,0],[1386633600000,0],[1386720000000,0],[1386806400000,1],[1386892800000,0],[1386979200000,0],[1387065600000,0],[1387152000000,0],[1387238400000,0],[1387324800000,0],[1387411200000,1],[1387497600000,0],[1387584000000,2],[1387670400000,0],[1387756800000,0],[1387843200000,1],[1387929600000,0],[1388016000000,0],[1388102400000,0],[1388188800000,0]]
                }
            ]
        };

        var params = {
            "currency_symbol":"$",
            "number_of_sales":"7",
            "sales_amount":"<span class=\"amount\">$705.97<\/span>",
            "sold":"Sold",
            "earned":"Earned",
            "month_names":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
            "order_data":"{\"order_counts\":[[1385856000000,1],[1385942400000,0],[1386028800000,0],[1386115200000,0],[1386201600000,0],[1386288000000,0],[1386374400000,1],[1386460800000,0],[1386547200000,0],[1386633600000,0],[1386720000000,0],[1386806400000,1],[1386892800000,0],[1386979200000,0],[1387065600000,0],[1387152000000,0],[1387238400000,0],[1387324800000,0],[1387411200000,1],[1387497600000,0],[1387584000000,2],[1387670400000,0],[1387756800000,0],[1387843200000,1],[1387929600000,0],[1388016000000,0],[1388102400000,0],[1388188800000,0]],\"order_amounts\":[[1385856000000,20],[1385942400000,0],[1386028800000,0],[1386115200000,0],[1386201600000,0],[1386288000000,0],[1386374400000,99],[1386460800000,0],[1386547200000,0],[1386633600000,0],[1386720000000,0],[1386806400000,399],[1386892800000,0],[1386979200000,0],[1387065600000,0],[1387152000000,0],[1387238400000,0],[1387324800000,0],[1387411200000,9.99],[1387497600000,0],[1387584000000,78.98],[1387670400000,0],[1387756800000,0],[1387843200000,99],[1387929600000,0],[1388016000000,0],[1388102400000,0],[1388188800000,0]]}"
        };

        var order_data = jQuery.parseJSON( params.order_data.replace(/&quot;/g, '"') );

        var d = order_data.order_counts;
        var d2 = order_data.order_amounts;

        console.log(d, d2);

        for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
        for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

        var ctx = $("#order-stats").get(0).getContext("2d");
        var poststats = new Chart(ctx).Doughnut(order_stats);

        var placeholder = jQuery("#sell-stats");
        var plot = jQuery.plot(placeholder, [ { label: params.number_of_sales, data: d }, { label: params.sales_amount, data: d2, yaxis: 2 } ], {
            series: {
                lines: { show: true, fill: true },
                points: { show: true }
            },
            grid: {
                show: true,
                aboveData: false,
                color: '#ccc',
                backgroundColor: '#fff',
                borderWidth: 0,
                borderColor: '#ccc',
                clickable: false,
                hoverable: true,
                // markings: weekendAreas
            },
            xaxis: {
                mode: "time",
                timeformat: "%d %b",
                monthNames: params.month_names,
                tickLength: 1,
                minTickSize: [1, "day"]
            },
            yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
            colors: ["#8a4b75", "#47a03e"],
            legend: {
                show: true,
                position: "nw"
            }
        });

        placeholder.resize();

        // var ctx = $("#sell-stats").get(0).getContext("2d");
        // var poststats = new Chart(ctx).Line(sell_stats);
    })
</script>


<?php get_footer(); ?>
<?php

/**
 * Returns the definitions for the reports and charts
 *
 * @return array
 */
function dokan_get_reports_charts() {
    $charts = array(
        'title'  => __( 'Sales', 'dokan' ),
        'charts' => array(
            "overview"          => array(
                'title'       => __( 'Overview', 'dokan' ),
                'description' => '',
                'hide_title'  => true,
                'function'    => 'dokan_sales_overview'
            ),
            "sales_by_day"      => array(
                'title'       => __( 'Sales by day', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_daily_sales'
            ),
            "sales_by_month"    => array(
                'title'       => __( 'Sales by month', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_monthly_sales'
            ),
            // "product_sales"     => array(
            //     'title'       => __( 'Product Sales', 'dokan' ),
            //     'description' => '',
            //     'function'    => 'dokan_product_sales'
            // ),
            "top_sellers"       => array(
                'title'       => __( 'Top sellers', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_top_sellers'
            ),
            "top_earners"       => array(
                'title'       => __( 'Top earners', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_top_earners'
            )
        )
    );

    return apply_filters( 'dokan_reports_charts', $charts );
}

/**
 * Output JavaScript for highlighting weekends on charts.
 *
 * @access public
 * @return void
 */
function woocommerce_weekend_area_js() {
    ?>
    function weekendAreas(axes) {
        var markings = [];
        var d = new Date(axes.xaxis.min);
        // go to the first Saturday
        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);
        var i = d.getTime();
        do {
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);

        return markings;
    }
    <?php
}

/**
 * Output JavaScript for chart tooltips.
 *
 * @access public
 * @return void
 */
function woocommerce_tooltip_js() {
    ?>
    function showTooltip(x, y, contents) {
        jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
            padding: '5px 10px',
            border: '3px solid #3da5d5',
            background: '#288ab7',
            color: '#ffffff'
        }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    jQuery("#placeholder").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;

                jQuery("#tooltip").remove();

                if (item.series.label=="<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>") {

                    var y = item.datapoint[1].toFixed(2);
                    showTooltip(item.pageX, item.pageY, item.series.label + " - " + "<?php echo get_woocommerce_currency_symbol(); ?>" + y);

                } else if (item.series.label=="<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>") {

                    var y = item.datapoint[1];
                    showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);

                } else {

                    var y = item.datapoint[1];
                    showTooltip(item.pageX, item.pageY, y);

                }
            }
        }
        else {
            jQuery("#tooltip").remove();
            previousPoint = null;
        }
    });
    <?php
}

function dokan_sales_overview() {
    global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale, $current_user;

    $total_sales = $total_orders = $order_items = $discount_total = $shipping_total = 0;
    $user_orders = dokan_get_seller_order_ids( $current_user->ID );
    $user_orders_in = implode( ',', $user_orders );

    $order_totals = apply_filters( 'woocommerce_reports_sales_overview_order_totals', $wpdb->get_row( "
        SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   meta.meta_key       = '_order_total'
        AND     posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     posts.ID            IN( {$user_orders_in} )
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
    " ) );

    $total_sales    = $order_totals->total_sales;
    $total_orders   = absint( $order_totals->total_orders );

    $discount_total = apply_filters( 'woocommerce_reports_sales_overview_discount_total', $wpdb->get_var( "
        SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts

        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   meta.meta_key       IN ('_order_discount', '_cart_discount')
        AND     posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     posts.ID            IN( {$user_orders_in} )
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
    " ) );

    $shipping_total = apply_filters( 'woocommerce_reports_sales_overview_shipping_total', $wpdb->get_var( "
        SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts

        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   meta.meta_key       = '_order_shipping'
        AND     posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     posts.ID            IN( {$user_orders_in} )
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
    " ) );

    $order_items = apply_filters( 'woocommerce_reports_sales_overview_order_items', absint( $wpdb->get_var( "
        SELECT SUM( order_item_meta.meta_value )
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )
        WHERE   term.slug IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     order_items.order_item_type = 'line_item'
        AND     order_item_meta.meta_key = '_qty'
        AND     posts.ID IN( {$user_orders_in} )
    " ) ) );
    ?>
    <div id="poststuff" class="dokan-reports-wrap row">
        <div class="dokan-reports-sidebar col-md-3">
            <div class="postbox">
                <h3><span><?php _e( 'Total sales', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_sales > 0 ) echo woocommerce_price($total_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Total orders', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order total', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($total_orders>0) echo woocommerce_price($total_sales/$total_orders); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order items', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($total_orders>0) echo number_format($order_items/$total_orders, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Discounts used', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($discount_total>0) echo woocommerce_price($discount_total); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Total shipping costs', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($shipping_total>0) echo woocommerce_price($shipping_total); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
        </div>
        <div class="woocommerce-reports-main col-md-9">
            <div class="postbox">
                <h3><span><?php _e( 'This month\'s sales', 'woocommerce' ); ?></span></h3>
                <div class="inside chart">
                    <div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
                    <div id="cart_legend"></div>
                </div>
            </div>
        </div>
    </div>
    <?php

    $start_date = strtotime( date('Ymd', strtotime( date('Ym', current_time('timestamp') ) . '01' ) ) );
    $end_date = strtotime( date('Ymd', current_time( 'timestamp' ) ) );

    // Blank date ranges to begin
    $order_counts = $order_amounts = array();

    $count = 0;

    $days = ( $end_date - $start_date ) / ( 60 * 60 * 24 );

    if ( $days == 0 )
        $days = 1;

    while ( $count < $days ) {
        $time = strtotime( date( 'Ymd', strtotime( '+ ' . $count . ' DAY', $start_date ) ) ) . '000';

        $order_counts[ $time ] = $order_amounts[ $time ] = 0;

        $count++;
    }

    // Get order ids and dates in range
    $orders = apply_filters('dokan_reports_sales_overview_orders', $wpdb->get_results( "
        SELECT posts.ID, posts.post_date FROM {$wpdb->posts} AS posts

        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'dokan_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
        AND     post_date > '" . date('Y-m-d', $start_date ) . "'
        AND     post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
        AND     posts.ID IN( {$user_orders_in} )
        ORDER BY post_date ASC
    " ) );

    if ( $orders ) {
        foreach ( $orders as $order ) {

            $order_total = get_post_meta( $order->ID, '_order_total', true );
            $time = strtotime( date( 'Ymd', strtotime( $order->post_date ) ) ) . '000';

            if ( isset( $order_counts[ $time ] ) )
                $order_counts[ $time ]++;
            else
                $order_counts[ $time ] = 1;

            if ( isset( $order_amounts[ $time ] ) )
                $order_amounts[ $time ] = $order_amounts[ $time ] + $order_total;
            else
                $order_amounts[ $time ] = floatval( $order_total );
        }
    }

    $order_counts_array = $order_amounts_array = array();

    foreach ( $order_counts as $key => $count )
        $order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );

    foreach ( $order_amounts as $key => $amount )
        $order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

    $order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

    $chart_data = json_encode( $order_data );
    ?>
    <script type="text/javascript">
        jQuery(function(){
            var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

            var d = order_data.order_counts;
            var d2 = order_data.order_amounts;

            for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
            for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

            var placeholder = jQuery("#placeholder");

            var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
                legend: {
                    container: jQuery('#cart_legend'),
                    noColumns: 2
                },
                series: {
                    lines: { show: true, fill: true },
                    points: { show: true }
                },
                grid: {
                    show: true,
                    aboveData: false,
                    color: '#aaa',
                    backgroundColor: '#fff',
                    borderWidth: 2,
                    borderColor: '#aaa',
                    clickable: false,
                    hoverable: true,
                    // markings: weekendAreas
                },
                xaxis: {
                    mode: "time",
                    timeformat: "%d %b",
                    monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                    tickLength: 1,
                    minTickSize: [1, "day"]
                },
                yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
                colors: ["#f05025", "#47a03e"]
            });

            placeholder.resize();

            <?php woocommerce_weekend_area_js(); ?>
            <?php woocommerce_tooltip_js(); ?>
        });
    </script>
    <?php
}

/**
 * Output the daily sales chart.
 *
 * @access public
 * @return void
 */
function dokan_daily_sales() {

    global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale, $current_user;

    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
    $user_orders = dokan_get_seller_order_ids( $current_user->ID );
    $user_orders_in = implode( ',', $user_orders );

    if ( ! $start_date)
        $start_date = date( 'Ymd', strtotime( date('Ym', current_time( 'timestamp' ) ) . '01' ) );
    if ( ! $end_date)
        $end_date = date( 'Ymd', current_time( 'timestamp' ) );

    $start_date = strtotime( $start_date );
    $end_date = strtotime( $end_date );

    $total_sales = $total_orders = $order_items = 0;

    // Blank date ranges to begin
    $order_counts = $order_amounts = array();

    $count = 0;

    $days = ( $end_date - $start_date ) / ( 60 * 60 * 24 );

    if ( $days == 0 )
        $days = 1;

    while ( $count < $days ) {
        $time = strtotime( date( 'Ymd', strtotime( '+ ' . $count . ' DAY', $start_date ) ) ) . '000';

        $order_counts[ $time ] = $order_amounts[ $time ] = 0;

        $count++;
    }

    // Get order ids and dates in range
    $orders = apply_filters( 'woocommerce_reports_daily_sales_orders', $wpdb->get_results( "
        SELECT posts.ID, posts.post_date, meta.meta_value AS total_sales FROM {$wpdb->posts} AS posts

        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   meta.meta_key       = '_order_total'
        AND     posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
        AND     post_date > '" . date('Y-m-d', $start_date ) . "'
        AND     post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
        AND     posts.ID IN( {$user_orders_in} )
        GROUP BY posts.ID
        ORDER BY post_date ASC
    " ), $start_date, $end_date );

    if ( $orders ) {

        $total_orders = sizeof( $orders );

        foreach ( $orders as $order ) {

            // get order timestamp
            $time = strtotime( date( 'Ymd', strtotime( $order->post_date ) ) ) . '000';

            // Add order total
            $total_sales += $order->total_sales;

            // Get items
            $order_items += apply_filters( 'woocommerce_reports_daily_sales_order_items', absint( $wpdb->get_var( $wpdb->prepare( "
                SELECT SUM( order_item_meta.meta_value )
                FROM {$wpdb->prefix}woocommerce_order_items as order_items
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                WHERE   order_id = %d
                AND     order_items.order_item_type = 'line_item'
                AND     order_item_meta.meta_key = '_qty'
            ", $order->ID ) ) ), $order->ID );

            // Set times
            if ( isset( $order_counts[ $time ] ) )
                $order_counts[ $time ]++;
            else
                $order_counts[ $time ] = 1;

            if ( isset( $order_amounts[ $time ] ) )
                $order_amounts[ $time ] = $order_amounts[ $time ] + $order->total_sales;
            else
                $order_amounts[ $time ] = floatval( $order->total_sales );
        }
    }
    ?>
    <form method="post" class="form-inline report-filter" action="">
        <div class="form-group">
            <label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label> <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" />
        </div>

        <div class="form-group">
            <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label>
            <input type="text" name="end_date" id="to" class="datepicker" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" />

            <input type="submit" class="btn btn-success btn-sm" value="<?php _e( 'Show', 'woocommerce' ); ?>" />
        </div>
    </form>

    <div id="poststuff" class="dokan-reports-wrap row">
        <div class="dokan-reports-sidebar col-md-3">
            <div class="postbox">
                <h3><span><?php _e( 'Total sales in range', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_sales > 0 ) echo woocommerce_price( $total_sales ); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Total orders in range', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order total in range', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_orders > 0 ) echo woocommerce_price( $total_sales / $total_orders ); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order items in range', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_orders > 0 ) echo number_format( $order_items / $total_orders, 2 ); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
        </div>
        <div class="woocommerce-reports-main col-md-9">
            <div class="postbox">
                <h3><span><?php _e( 'Sales in range', 'woocommerce' ); ?></span></h3>
                <div class="inside chart">
                    <div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
                    <div id="cart_legend"></div>
                </div>
            </div>
        </div>
    </div>
    <?php

    $order_counts_array = $order_amounts_array = array();

    foreach ( $order_counts as $key => $count )
        $order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );

    foreach ( $order_amounts as $key => $amount )
        $order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

    $order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

    $chart_data = json_encode($order_data);
    ?>
    <script type="text/javascript">
        jQuery(function(){
            var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

            var d = order_data.order_counts;
            var d2 = order_data.order_amounts;

            for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
            for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

            var placeholder = jQuery("#placeholder");

            var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
                legend: {
                    container: jQuery('#cart_legend'),
                    noColumns: 2
                },
                series: {
                    lines: { show: true, fill: true },
                    points: { show: true }
                },
                grid: {
                    show: true,
                    aboveData: false,
                    color: '#aaa',
                    backgroundColor: '#fff',
                    borderWidth: 2,
                    borderColor: '#aaa',
                    clickable: false,
                    hoverable: true,
                    markings: weekendAreas
                },
                xaxis: {
                    mode: "time",
                    timeformat: "%d %b",
                    monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                    tickLength: 1,
                    minTickSize: [1, "day"]
                },
                yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
                colors: ["#8a4b75", "#47a447"]
            });

            placeholder.resize();

            <?php woocommerce_weekend_area_js(); ?>
            <?php woocommerce_tooltip_js(); ?>
            <?php //woocommerce_datepicker_js(); ?>
        });
    </script>
    <?php
}


/**
 * Output the monthly sales chart.
 *
 * @access public
 * @return void
 */
function dokan_monthly_sales() {

    global $start_date, $end_date, $woocommerce, $wpdb, $wp_locale, $current_user;

    $first_year = $wpdb->get_var( "SELECT post_date FROM $wpdb->posts WHERE post_date != 0 ORDER BY post_date ASC LIMIT 1;" );

    $first_year = $first_year ? date( 'Y', strtotime( $first_year ) ) : date('Y');

    $current_year   = isset( $_POST['show_year'] ) ? $_POST['show_year'] : date( 'Y', current_time( 'timestamp' ) );
    $start_date     = strtotime( $current_year . '0101' );

    $total_sales = $total_orders = $order_items = 0;
    $order_counts = $order_amounts = array();
    $user_orders = dokan_get_seller_order_ids( $current_user->ID );
    $user_orders_in = implode( ',', $user_orders );

    for ( $count = 0; $count < 12; $count++ ) {
        $time = strtotime( date('Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' ) . '000';

        if ( $time > current_time( 'timestamp' ) . '000' )
            continue;

        $month = date( 'Ym', strtotime(date('Ym', strtotime('+ '.$count.' MONTH', $start_date)).'01') );

        $months_orders = apply_filters( 'woocommerce_reports_monthly_sales_orders', $wpdb->get_row( $wpdb->prepare( "
            SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
            LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
            LEFT JOIN {$wpdb->terms} AS term USING( term_id )

            WHERE   meta.meta_key       = '_order_total'
            AND     posts.post_type     = 'shop_order'
            AND     posts.post_status   = 'publish'
            AND     tax.taxonomy        = 'shop_order_status'
            AND     posts.ID IN( {$user_orders_in} )
            AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
            AND     %s                  = date_format(posts.post_date,'%%Y%%m')
        ", $month ) ), $month );

        $order_counts[ $time ]  = (int) $months_orders->total_orders;
        $order_amounts[ $time ] = (float) $months_orders->total_sales;

        $total_orders           += (int) $months_orders->total_orders;
        $total_sales            += (float) $months_orders->total_sales;

        // Count order items
        $order_items += apply_filters( 'woocommerce_reports_monthly_sales_order_items', absint( $wpdb->get_var( $wpdb->prepare( "
            SELECT SUM( order_item_meta.meta_value )
            FROM {$wpdb->prefix}woocommerce_order_items as order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
            LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
            LEFT JOIN {$wpdb->terms} AS term USING( term_id )
            WHERE   term.slug IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
            AND     posts.post_status   = 'publish'
            AND     tax.taxonomy        = 'shop_order_status'
            AND     %s                  = date_format( posts.post_date, '%%Y%%m' )
            AND     order_items.order_item_type = 'line_item'
            AND     posts.ID IN( {$user_orders_in} )
            AND     order_item_meta.meta_key = '_qty'
        ", $month ) ) ), $month );
    }
    ?>

    <form method="post" action="" class="report-filter">
        <p><label for="show_year"><?php _e( 'Year:', 'woocommerce' ); ?></label>
        <select name="show_year" id="show_year">
            <?php
                for ( $i = $first_year; $i <= date( 'Y' ); $i++ ) {
                    printf('<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
                }
            ?>
        </select>
        <input type="submit" class="btn btn-success btn-sm" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
    </form>

    <div id="poststuff" class="dokan-reports-wrap row">
        <div class="dokan-reports-sidebar col-md-3">
            <div class="postbox">
                <h3><span><?php _e( 'Total sales for year', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($total_sales>0) echo woocommerce_price($total_sales); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Total orders for year', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ( $total_orders > 0 ) echo $total_orders . ' (' . $order_items . ' ' . __( 'items', 'woocommerce' ) . ')'; else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order total for year', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($total_orders>0) echo woocommerce_price($total_sales/$total_orders); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
            <div class="postbox">
                <h3><span><?php _e( 'Average order items for year', 'woocommerce' ); ?></span></h3>
                <div class="inside">
                    <p class="stat"><?php if ($total_orders>0) echo number_format($order_items/$total_orders, 2); else _e( 'n/a', 'woocommerce' ); ?></p>
                </div>
            </div>
        </div>
        <div class="woocommerce-reports-main col-md-9">
            <div class="postbox">
                <h3><span><?php _e( 'Monthly sales for year', 'woocommerce' ); ?></span></h3>
                <div class="inside chart">
                    <div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
                    <div id="cart_legend"></div>
                </div>
            </div>
        </div>
    </div>
    <?php

    $order_counts_array = $order_amounts_array = array();

    foreach ( $order_counts as $key => $count )
        $order_counts_array[] = array( esc_js( $key ), esc_js( $count ) );

    foreach ( $order_amounts as $key => $amount )
        $order_amounts_array[] = array( esc_js( $key ), esc_js( $amount ) );

    $order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

    $chart_data = json_encode( $order_data );
    ?>
    <script type="text/javascript">
        jQuery(function(){
            var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

            var d = order_data.order_counts;
            var d2 = order_data.order_amounts;

            var placeholder = jQuery("#placeholder");

            var plot = jQuery.plot(placeholder, [ { label: "<?php echo esc_js( __( 'Number of sales', 'woocommerce' ) ) ?>", data: d }, { label: "<?php echo esc_js( __( 'Sales amount', 'woocommerce' ) ) ?>", data: d2, yaxis: 2 } ], {
                legend: {
                    container: jQuery('#cart_legend'),
                    noColumns: 2
                },
                series: {
                    lines: { show: true, fill: true },
                    points: { show: true, align: "left" }
                },
                grid: {
                    show: true,
                    aboveData: false,
                    color: '#aaa',
                    backgroundColor: '#fff',
                    borderWidth: 2,
                    borderColor: '#aaa',
                    clickable: false,
                    hoverable: true
                },
                xaxis: {
                    mode: "time",
                    timeformat: "%b %y",
                    monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                    tickLength: 1,
                    minTickSize: [1, "month"]
                },
                yaxes: [ { min: 0, tickSize: 10, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
                colors: ["#8a4b75", "#47a03e"]
            });

            placeholder.resize();

            <?php woocommerce_tooltip_js(); ?>
        });
    </script>
    <?php
}

/**
 * Output the top sellers chart.
 *
 * @access public
 * @return void
 */
function dokan_top_sellers() {

    global $start_date, $end_date, $woocommerce, $wpdb, $current_user;

    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

    if ( ! $start_date )
        $start_date = date( 'Ymd', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );
    if ( ! $end_date )
         $end_date = date( 'Ymd', current_time( 'timestamp' ) );

    $start_date = strtotime( $start_date );
    $end_date = strtotime( $end_date );

    $user_orders = dokan_get_seller_order_ids( $current_user->ID );
    $user_orders_in = implode( ',', $user_orders );

    // Get order ids and dates in range
    $order_items = apply_filters( 'woocommerce_reports_top_sellers_order_items', $wpdb->get_results( "
        SELECT order_item_meta_2.meta_value as product_id, SUM( order_item_meta.meta_value ) as item_quantity FROM {$wpdb->prefix}woocommerce_order_items as order_items

        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
        AND     post_date > '" . date('Y-m-d', $start_date ) . "'
        AND     post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
        AND     order_items.order_item_type = 'line_item'
        AND     order_item_meta.meta_key = '_qty'
        AND     order_item_meta_2.meta_key = '_product_id'
        AND     posts.ID IN( {$user_orders_in} )
        GROUP BY order_item_meta_2.meta_value
    " ), $start_date, $end_date );

    $found_products = array();

    if ( $order_items ) {
        foreach ( $order_items as $order_item ) {
            $found_products[ $order_item->product_id ] = $order_item->item_quantity;
        }
    }

    asort( $found_products );
    $found_products = array_reverse( $found_products, true );
    $found_products = array_slice( $found_products, 0, 25, true );
    reset( $found_products );
    ?>
    <form method="post" action="">
        <p><label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?php _e( 'Product', 'woocommerce' ); ?></th>
                <th><?php _e( 'Sales', 'woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $max_sales = current( $found_products );
                foreach ( $found_products as $product_id => $sales ) {
                    $width = $sales > 0 ? ( $sales / $max_sales ) * 100 : 0;
                    $product_title = get_the_title( $product_id );

                    if ( $product_title ) {
                        $product_name = '<a href="' . get_permalink( $product_id ) . '">'. __( $product_title ) .'</a>';
                        $orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( $product_title ) . '&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
                    } else {
                        $product_name = __( 'Product does not exist', 'woocommerce' );
                        $orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
                    }

                    $orders_link = apply_filters( 'woocommerce_reports_order_link', $orders_link, $product_id, $product_title );

                    echo '<tr><th>' . $product_name . '</th><td width="1%"><span>' . esc_html( $sales ) . '</span></td><td class="bars"><a href="' . esc_url( $orders_link ) . '" style="width:' . esc_attr( $width ) . '%">&nbsp;</a></td></tr>';
                }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
        jQuery(function(){
            <?php woocommerce_datepicker_js(); ?>
        });
    </script>
    <?php
}


/**
 * Output the top earners chart.
 *
 * @access public
 * @return void
 */
function dokan_top_earners() {

    global $start_date, $end_date, $woocommerce, $wpdb, $current_user;

    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

    if ( ! $start_date )
        $start_date = date( 'Ymd', strtotime( date('Ym', current_time( 'timestamp' ) ) . '01' ) );
    if ( ! $end_date )
        $end_date = date( 'Ymd', current_time( 'timestamp' ) );

    $start_date = strtotime( $start_date );
    $end_date = strtotime( $end_date );

    $user_orders = dokan_get_seller_order_ids( $current_user->ID );
    $user_orders_in = implode( ',', $user_orders );

    // Get order ids and dates in range
    $order_items = apply_filters( 'woocommerce_reports_top_earners_order_items', $wpdb->get_results( "
        SELECT order_item_meta_2.meta_value as product_id, SUM( order_item_meta.meta_value ) as line_total FROM {$wpdb->prefix}woocommerce_order_items as order_items

        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
        LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
        LEFT JOIN {$wpdb->terms} AS term USING( term_id )

        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   = 'publish'
        AND     tax.taxonomy        = 'shop_order_status'
        AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
        AND     post_date > '" . date('Y-m-d', $start_date ) . "'
        AND     post_date < '" . date('Y-m-d', strtotime('+1 day', $end_date ) ) . "'
        AND     order_items.order_item_type = 'line_item'
        AND     order_item_meta.meta_key = '_line_total'
        AND     order_item_meta_2.meta_key = '_product_id'
        AND     posts.ID IN( {$user_orders_in} )
        GROUP BY order_item_meta_2.meta_value
    " ), $start_date, $end_date );

    $found_products = array();

    if ( $order_items ) {
        foreach ( $order_items as $order_item ) {
            $found_products[ $order_item->product_id ] = $order_item->line_total;
        }
    }

    asort( $found_products );
    $found_products = array_reverse( $found_products, true );
    $found_products = array_slice( $found_products, 0, 25, true );
    reset( $found_products );
    ?>
    <form method="post" action="" class="report-filter form-inline">
        <div class="form-group">
            <label for="from"><?php _e( 'From:', 'woocommerce' ); ?></label>
            <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" />
        </div>

        <div class="form-group">
            <label for="to"><?php _e( 'To:', 'woocommerce' ); ?></label>
            <input type="text" class="datepicker" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" />
        </div>

        <input type="submit" class="btn btn-success btn-sm" value="<?php _e( 'Show', 'woocommerce' ); ?>" />

    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php _e( 'Product', 'woocommerce' ); ?></th>
                <th colspan="2"><?php _e( 'Sales', 'woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $max_sales = current( $found_products );
                foreach ( $found_products as $product_id => $sales ) {
                    $width = $sales > 0 ? ( round( $sales ) / round( $max_sales ) ) * 100 : 0;

                    $product_title = get_the_title( $product_id );

                    if ( $product_title ) {
                        $product_name = '<a href="'.get_permalink( $product_id ).'">'. __( $product_title ) .'</a>';
                        $orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( $product_title ) . '&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
                    } else {
                        $product_name = __( 'Product no longer exists', 'woocommerce' );
                        $orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
                    }

                    $orders_link = apply_filters( 'woocommerce_reports_order_link', $orders_link, $product_id, $product_title );

                    echo '<tr><th>' . $product_name . '</th><td width="1%"><span>' . woocommerce_price( $sales ) . '</span></td><td class="bars"><a href="' . esc_url( $orders_link ) . '" style="width:' . esc_attr( $width ) . '%">&nbsp;</a></td></tr>';
                }
            ?>
        </tbody>
    </table>
    <?php
}


/**
 * Output the product sales chart for single products.
 *
 * @access public
 * @return void
 */
function dokan_product_sales() {

    global $wpdb, $woocommerce;

    $chosen_product_ids = ( isset( $_POST['product_ids'] ) ) ? array_map( 'absint', (array) $_POST['product_ids'] ) : '';

    if ( $chosen_product_ids && is_array( $chosen_product_ids ) ) {

        $start_date = date( 'Ym', strtotime( '-12 MONTHS', current_time('timestamp') ) ) . '01';
        $end_date   = date( 'Ymd', current_time( 'timestamp' ) );

        $max_sales = $max_totals = 0;
        $product_sales = $product_totals = array();

        // Get titles and ID's related to product
        $chosen_product_titles = array();
        $children_ids = array();

        foreach ( $chosen_product_ids as $product_id ) {
            $children = (array) get_posts( 'post_parent=' . $product_id . '&fields=ids&post_status=any&numberposts=-1' );
            $children_ids = $children_ids + $children;
            $chosen_product_titles[] = get_the_title( $product_id );
        }

        // Get order items
        $order_items = apply_filters( 'woocommerce_reports_product_sales_order_items', $wpdb->get_results( "
            SELECT order_item_meta_2.meta_value as product_id, posts.post_date, SUM( order_item_meta.meta_value ) as item_quantity, SUM( order_item_meta_3.meta_value ) as line_total
            FROM {$wpdb->prefix}woocommerce_order_items as order_items

            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_ID
            LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
            LEFT JOIN {$wpdb->terms} AS term USING( term_id )

            WHERE   posts.post_type     = 'shop_order'
            AND     order_item_meta_2.meta_value IN ('" . implode( "','", array_merge( $chosen_product_ids, $children_ids ) ) . "')
            AND     posts.post_status   = 'publish'
            AND     tax.taxonomy        = 'shop_order_status'
            AND     term.slug           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
            AND     order_items.order_item_type = 'line_item'
            AND     order_item_meta.meta_key = '_qty'
            AND     order_item_meta_2.meta_key = '_product_id'
            AND     order_item_meta_3.meta_key = '_line_total'
            GROUP BY order_items.order_id
            ORDER BY posts.post_date ASC
        " ), array_merge( $chosen_product_ids, $children_ids ) );

        $found_products = array();

        if ( $order_items ) {
            foreach ( $order_items as $order_item ) {

                if ( $order_item->line_total == 0 && $order_item->item_quantity == 0 )
                    continue;

                // Get date
                $date   = date( 'Ym', strtotime( $order_item->post_date ) );

                // Set values
                $product_sales[ $date ]     = isset( $product_sales[ $date ] ) ? $product_sales[ $date ] + $order_item->item_quantity : $order_item->item_quantity;
                $product_totals[ $date ]    = isset( $product_totals[ $date ] ) ? $product_totals[ $date ] + $order_item->line_total : $order_item->line_total;

                if ( $product_sales[ $date ] > $max_sales )
                    $max_sales = $product_sales[ $date ];

                if ( $product_totals[ $date ] > $max_totals )
                    $max_totals = $product_totals[ $date ];
            }
        }
        ?>
        <h4><?php printf( __( 'Sales for %s:', 'woocommerce' ), implode( ', ', $chosen_product_titles ) ); ?></h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php _e( 'Month', 'woocommerce' ); ?></th>
                    <th colspan="2"><?php _e( 'Sales', 'woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ( sizeof( $product_sales ) > 0 ) {
                        foreach ( $product_sales as $date => $sales ) {
                            $width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
                            $width2 = ($product_totals[$date]>0) ? (round($product_totals[$date]) / round($max_totals)) * 100 : 0;

                            $orders_link = admin_url( 'edit.php?s&post_status=all&post_type=shop_order&action=-1&s=' . urlencode( implode( ' ', $chosen_product_titles ) ) . '&m=' . date( 'Ym', strtotime( $date . '01' ) ) . '&shop_order_status=' . implode( ",", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) );
                            $orders_link = apply_filters( 'woocommerce_reports_order_link', $orders_link, $chosen_product_ids, $chosen_product_titles );

                            echo '<tr><th><a href="' . esc_url( $orders_link ) . '">' . date_i18n( 'F', strtotime( $date . '01' ) ) . '</a></th>
                            <td width="1%"><span>' . esc_html( $sales ) . '</span><span class="alt">' . woocommerce_price( $product_totals[ $date ] ) . '</span></td>
                            <td class="bars">
                                <span style="width:' . esc_attr( $width ) . '%">&nbsp;</span>
                                <span class="alt" style="width:' . esc_attr( $width2 ) . '%">&nbsp;</span>
                            </td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">' . __( 'No sales :(', 'woocommerce' ) . '</td></tr>';
                    }
                ?>
            </tbody>
        </table>
        <?php

    } else {
        ?>
        <form method="post" action="">
            <p><select id="product_ids" name="product_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" style="width: 400px;"></select> <input type="submit" style="vertical-align: top;" class="button" value="<?php _e( 'Show', 'woocommerce' ); ?>" /></p>
            <script type="text/javascript">
                jQuery(function(){
                    jQuery("select.ajax_chosen_select_products").chosen();

                    // Ajax Chosen Product Selectors
                    jQuery("select.ajax_chosen_select_products").ajaxChosen({
                        method:     'GET',
                        url:        '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType:   'json',
                        afterTypeDelay: 100,
                        data:       {
                            action:         'woocommerce_json_search_products',
                            security:       '<?php echo wp_create_nonce("search-products"); ?>'
                        }
                    }, function (data) {

                        var terms = {};

                        jQuery.each(data, function (i, val) {
                            terms[i] = val;
                        });

                        return terms;
                    });

                });
            </script>
        </form>
        <?php
    }
}
<div class="wrap">
    <?php
    $tab = isset( $_GET['tab'] ) ?  $_GET['tab'] : 'report';
    $type = isset( $_GET['type'] ) ?  $_GET['type'] : 'day'; ?>

    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="admin.php?page=dokan-reports&amp;tab=report" class="nav-tab<?php if ( $tab == 'report' ) echo ' nav-tab-active'; ?>"><?php _e( 'Reports', 'dokan' ); ?></a>
        <a href="admin.php?page=dokan-reports&amp;tab=logs" class="nav-tab<?php if ( $tab == 'logs' ) echo ' nav-tab-active'; ?>"><?php _e( 'All Logs', 'dokan' ); ?></a>
    </h2>

    <?php if ( $tab == 'report' ) { ?>
        <ul class="subsubsub" style="float: none;">
            <li>
                <a href="admin.php?page=dokan-reports&amp;tab=report&amp;type=day" <?php if ( $type == 'day' ) echo 'class="current"'; ?>>
                    <?php _e( 'By Day', 'dokan' ); ?>
                </a> |
            </li>
            <li>
                <a href="admin.php?page=dokan-reports&amp;tab=report&amp;type=month" <?php if ( $type == 'month' ) echo 'class="current"'; ?>>
                    <?php _e( 'By Month', 'dokan' ); ?>
                </a>
            </li>
        </ul>

        <?php
        $start_date = date( 'Y-m-01', current_time('timestamp') );
        $end_date = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );
        $current_year = $selected_year = date('Y');

        if ( isset( $_POST['dokan_report_filter_date'] ) ) {
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
        }

        if ( isset( $_POST['dokan_report_filter_year'] ) ) {
            $selected_year = $_POST['report_year'];
        }

        if ( $type == 'day' ) { ?>
            <form method="post" class="form-inline report-filter" action="">
                <span class="form-group">
                    <label for="from"><?php _e( 'From:', 'dokan' ); ?></label> <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( $start_date ); ?>" />
                </span>

                <span class="form-group">
                    <label for="to"><?php _e( 'To:', 'dokan' ); ?></label>
                    <input type="text" name="end_date" id="to" class="datepicker" readonly="readonly" value="<?php echo esc_attr( $end_date ); ?>" />

                    <input type="submit" name="dokan_report_filter_date" class="button button-primary" value="<?php _e( 'Show', 'dokan' ); ?>" />
                </span>
            </form>
        <?php } elseif ( $type == 'month' ) { ?>
            <form method="post" class="form-inline report-filter" action="">
                <span class="form-group">
                    <label for="from"><?php _e( 'Year:', 'dokan' ); ?></label>
                    <select name="report_year">
                        <?php for ($i = ($current_year - 5); $i < ($current_year + 5); $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php selected( $selected_year, $i ); ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </span>

                <input type="submit" name="dokan_report_filter_year" class="button button-primary" value="<?php _e( 'Show', 'dokan' ); ?>" />
            </form>
        <?php } ?>

        <div class="admin-report-container">
            <?php
            $order_total = $earning_total = $total_orders = 0;

            if ( $type == 'day' ) {
                $report_data = dokan_admin_report();
            } elseif ( $type == 'month' ) {
                $report_data = dokan_admin_report( 'month', $selected_year );
            }

            if ( $report_data ) {
                foreach ($report_data as $row) {
                    $order_total += $row->order_total;
                    $earning_total += $row->earning;
                    $total_orders += $row->total_orders;
                }
            }
            ?>

            <div class="dokan-reports-sidebar">
                <ul class="chart-legend">
                    <li>
                        <strong><?php echo wc_price( dokan_site_total_earning() ); ?></strong>
                        <?php _e( 'Total Earning', 'dokan' ); ?>
                    </li>
                    <li>
                        <strong><?php echo wc_price( $earning_total ); ?></strong>
                        <?php _e( 'Total Earning in this period', 'dokan' ); ?>
                    </li>
                    <li>
                        <strong><?php echo wc_price( $order_total ); ?></strong>
                        <?php _e( 'Order total in this period', 'dokan' ); ?>
                    </li>
                    <li>
                        <strong><?php echo $total_orders; ?></strong>
                        <?php _e( 'orders placed in this period', 'dokan' ); ?>
                    </li>
                </ul>
            </div>

            <div class="chart-container">
                <div class="chart-placeholder main"></div>
            </div>
        </div>


    <?php } else { ?>

        <table class="widefat withdraw-table" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th><?php _e( 'Order', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller', 'dokan' ); ?></th>
                    <th><?php _e( 'Order Total', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller Earning', 'dokan' ); ?></th>
                    <th><?php _e( 'Commision', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e( 'Order', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller', 'dokan' ); ?></th>
                    <th><?php _e( 'Order Total', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller Earning', 'dokan' ); ?></th>
                    <th><?php _e( 'Commision', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                $count = 0;
                $pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
                $limit = 20;
                $offset = ( $pagenum - 1 ) * $limit;

                $seller_where = '';
                if ( isset( $_GET['seller_id'] ) ) {
                    $seller_where = $wpdb->prepare('AND seller_id = %d', $_GET['seller_id'] );
                }

                $sql = "SELECT do.*, p.post_date FROM {$wpdb->prefix}dokan_orders do
                        LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                        WHERE seller_id != 0 AND p.post_status = 'publish' $seller_where
                        ORDER BY do.order_id DESC LIMIT $offset, $limit";
                $all_logs = $wpdb->get_results( $sql );

                foreach ($all_logs as $log) {
                    $seller = get_user_by( 'id', $log->seller_id );
                    ?>
                    <tr<?php echo $count % 2 == 0 ? ' class="alternate"' : '' ;?>>
                        <th><?php printf( '<a href="%s">#%s</a>', admin_url( 'post.php?action=edit&amp;post='. $log->order_id ), $log->order_id ); ?></th>
                        <th><?php printf( '<a href="%s">%s</a> (<a href="%s">%s</a>)', add_query_arg( array( 'seller_id' => $log->seller_id ) ), $seller->display_name, admin_url( 'user-edit.php?user_id='. $log->seller_id ), __( 'edit', 'dokan' ) ); ?></th>
                        <th><?php echo wc_price( $log->order_total ); ?></th>
                        <th><?php echo wc_price( $log->net_amount ); ?></th>
                        <th><?php echo wc_price( $log->order_total - $log->net_amount ); ?></th>
                        <th><?php echo $log->order_status; ?></th>
                    </tr>
                    <?php
                    $count++;
                }
                ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
        <?php if ( $all_logs ) {
            $count_where = 'seller_id != 0';
            if ( isset( $_GET['seller_id'] ) ) {
                $count_where = $wpdb->prepare('seller_id = %d', $_GET['seller_id'] );
            }
            $count = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}dokan_orders WHERE $count_where");
            $num_of_pages = ceil( $count / $limit );
            $page_links = paginate_links( array(
                'base' => add_query_arg( 'paged', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'aag' ),
                'next_text' => __( '&raquo;', 'aag' ),
                'total' => $num_of_pages,
                'current' => $pagenum
            ) );

            if ( $page_links ) {
                echo '<div class="tablenav-pages">' . $page_links . '</div>';
            }
        } ?>
        </div>

    <?php } ?>

    <script type="text/javascript">
    jQuery(function($) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
    });
    </script>
</div>
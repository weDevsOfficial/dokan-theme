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
                </a> |
            </li>
            <li>
                <a href="admin.php?page=dokan-reports&amp;tab=report&amp;type=year" <?php if ( $type == 'year' ) echo 'class="current"'; ?>>
                    <?php _e( 'By Year', 'dokan' ); ?>
                </a>
            </li>
        </ul>
    <?php } else { ?>

        <table class="widefat withdraw-table" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th><?php _e( 'Order', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller', 'dokan' ); ?></th>
                    <th><?php _e( 'Order Total', 'dokan' ); ?></th>
                    <th><?php _e( 'Earning', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e( 'Order', 'dokan' ); ?></th>
                    <th><?php _e( 'Seller', 'dokan' ); ?></th>
                    <th><?php _e( 'Order Total', 'dokan' ); ?></th>
                    <th><?php _e( 'Earning', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                global $wpdb;

                $count = 0;
                $all_logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}dokan_orders WHERE seller_id != 0");

                foreach ($all_logs as $log) {
                    ?>
                    <tr<?php echo $count % 2 == 0 ? ' class="alternate"' : '' ;?>>
                        <th><?php printf( '<a href="%s">#%s</a>', admin_url( 'post.php?action=edit&amp;post='. $log->order_id ), $log->order_id ); ?></th>
                        <th><?php printf( '<a href="%s">%s</a>', admin_url( 'edit.php?action=edit&amp;id='. $log->seller_id ), $log->seller_id ); ?></th>
                        <th><?php echo wc_price( $log->order_total ); ?></th>
                        <th><?php echo wc_price( $log->net_amount ); ?></th>
                        <th><?php echo $log->order_status; ?></th>
                    </tr>
                    <?php
                    $count++;
                }
                ?>
            </tbody>
        </table>

    <?php } ?>
</div>
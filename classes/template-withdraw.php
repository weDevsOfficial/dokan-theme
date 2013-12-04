<?php

/**
 * Dokan Withdraw class
 *
 * @author weDevs
 */
class Dokan_Template_Withdraw {

    /**
     * Initializes the Bed_IQ() class
     *
     * Checks for an existing Bed_IQ() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Template_Withdraw();
        }

        return $instance;
    }

    function cancel_pending() {

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'dokan_cancel_withdrow' ) {

            if ( !wp_verify_nonce( $_GET['_wpnonce'], 'dokan_cancel_withdrow' ) ) {
                wp_die( __( 'Are you cheating?', 'dokan' ) );
            }

            global $current_user, $wpdb;

            $row_id = absint( $_GET['id'] );

            $this->update_status( $row_id, $current_user->ID, 2 );

            wp_redirect( add_query_arg( array( 'message' => 'request_cancelled' ), get_permalink() ) );
        }
    }

    function update_status( $row_id, $user_id, $status ) {
        global $wpdb;

        // 0 -> pending
        // 1 -> active
        // 2 -> cancelled

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->dokan_withdraw}
            SET status = %d WHERE user_id=%d AND id = %d",
            $status, $user_id, $row_id
        ) );
    }

    function insert_withdraw( $data = array() ) {
        global $wpdb;

        $data = array(
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'date' => current_time( 'mysql' ),
            'status' => $data['status'],
            'method' => $data['method'],
            'notes' => $data['notes'],
            'ip' => $data['ip']
        );

        $format = array('%d', '%f', '%s', '%d', '%s', '%s', '%s');

        $wpdb->insert( $wpdb->dokan_withdraw, $data, $format );
    }

    function insert_withdraw_info() {

        global $current_user, $wpdb;

        if ( !isset( $_POST['withdraw_submit'] ) || !wp_verify_nonce( $_POST['dokan_withdraw_nonce'], 'dokan_withdraw' ) ) {
            return;
        }

        $data_info = array(
            'user_id' => $current_user->ID,
            'amount' => filter_var( $_POST['witdraw_amount'], FILTER_SANITIZE_NUMBER_FLOAT ),
            'status' => 0,
            'method' => $_POST['withdraw_method'],
            'ip' => dokan_get_client_ip(),
            'notes' => ''
        );

        $this->insert_withdraw( $data_info );
        wp_redirect( add_query_arg( array( 'message' => 'request_success' ), get_permalink() ) );
    }

    function withdraw_table() {

        global $wpdb;
        $withdraw_db_version = '1.0';
        $installed_ver = get_option( "withdraw_db_version" );

        if ( $installed_ver != $withdraw_db_version ) {

            $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->dokan_withdraw} (
                   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                   `user_id` bigint(20) unsigned NOT NULL,
                   `amount` float(11) NOT NULL,
                   `date` timestamp NOT NULL,
                   `status` int(1) NOT NULL,
                   `method` varchar(30) NOT NULL,
                   `notes` text NOT NULL,
                   `ip` varchar(15) NOT NULL,
                  PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( "withdraw_db_version", $withdraw_db_version );
        }
    }

    function has_pending_request( $user_id ) {
        global $wpdb;

        $wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';

        $status = $wpdb->get_results( $wpdb->prepare(
                        "SELECT id
             FROM $wpdb->dokan_withdraw
             WHERE user_id = %d AND status = 0", $user_id
                ) );

        if ( $status ) {
            return true;
        }

        return false;
    }

    function get_withdraw_requests( $user_id, $status = 0 ) {
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->dokan_withdraw}
            WHERE user_id='%d' AND status = $status", $user_id
        ) );

        return $result;
    }

    function has_withdraw_balance( $user_id ) {

        $balance = $this->get_user_balance( $user_id );
        $withdraw_limit = $this->get_withdraw_limit();

        if ( $balance < $withdraw_limit ) {
            return false;
        }

        return true;
    }

    function get_withdraw_limit() {
        return 50;
    }

    function get_user_balance( $user_id ) {
        return 99;
    }

    function request_status( $status ) {
        switch ($status) {
            case 0:
                return '<span class="label label-danger">' . __( 'Pending Reivew', 'dokan' ) . '</span>';
                break;

            case 1:
                return '<span class="label label-warning">' . __( 'Accepted', 'dokan' ) . '</span>';
                break;
        }
    }

    function withdraw_requests( $user_id ) {
        $withdraw_requests = $this->get_withdraw_requests( $user_id );

        if ( $withdraw_requests ) {
            ?>
            <table class="table table-striped">
                <tr>
                    <th><?php _e( 'Amount', 'dokan' ); ?></th>
                    <th><?php _e( 'Method', 'dokan' ); ?></th>
                    <th><?php _e( 'Date', 'dokan' ); ?></th>
                    <th><?php _e( 'Cancel', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>

                <?php foreach ($withdraw_requests as $request) { ?>

                    <tr>
                        <td><?php echo woocommerce_price( $request->amount ); ?></td>
                        <td><?php echo $request->method; ?></td>
                        <td><?php echo dokan_format_time( $request->date ); ?></td>
                        <td>
                            <?php
                            $url = add_query_arg( array(
                                'action' => 'dokan_cancel_withdrow',
                                'id' => $request->id
                                    ), get_permalink() );
                            ?>
                            <a href="<?php echo wp_nonce_url( $url, 'dokan_cancel_withdrow' ); ?>">
                                <?php _e( 'Cancel', 'dokan' ); ?>
                            </a>
                        </td>
                        <td><?php echo $this->request_status( $request->status ); ?></td>
                    </tr>

                <?php } ?>

            </table>
            <?php
        }
    }

    function get_payment_methods() {
        $method = array(
            'none' => __( '- Select Method -', 'dokan' ),
            'paypal' => __( 'Paypal', 'dokan' ),
            'bank' => __( 'Bank Transfer', 'dokan' ),
        );

        $payment_methods = apply_filters( 'payment_withdraw_option', $method );

        return $payment_methods;
    }

    function show_alert_messages() {
        $type = isset( $_GET['message'] ) ? $_GET['message'] : '';

        switch ($type) {
            case 'request_cancelled':
                ?>
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?php _e( 'Your request has been cancelled successfully!', 'dokan' ); ?></strong>
                </div>
                <?php
                break;

            case 'request_success':
                ?>
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?php _e( 'Your request has been received successfully and is under review!', 'dokan' ); ?></strong>
                </div>
                <?php
                break;
        }
    }

    function withdraw_form() {
        global $current_user;

        // show alert messages
        $this->show_alert_messages();

        if ( $this->has_pending_request( $current_user->ID ) ) {
            ?>
            <div class="alert alert-warning">
                <p><strong>You've already pending withdraw request(s).</strong></p>
                <p>Until it's been cancelled or approved, you can't submit any new request.</p>
            </div>

            <?php
            $this->withdraw_requests( $current_user->ID );
            return;

        } else if ( !$this->has_withdraw_balance( $current_user->ID ) ) {

            print(__( 'You have no sufficient account balance for withdraw request', 'dokan' ) );
            return;
        }

        $payment_methods = $this->get_payment_methods();
        ?>

        <form class="form-horizontal" role="form" method="post">
            <div class="form-group">

                <label for="withdraw-amount" class="col-sm-3 control-label">
                    <?php _e( 'Withdraw Amount' ); ?>
                </label>

                <div class="col-sm-3">
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                        <input name="witdraw_amount" class="form-control" id="withdraw-amount" name="price" type="text" placeholder="9.99" value="">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="withdraw-method" class="col-sm-3 control-label">
                    <?php _e( 'Payment Method', 'dokan' ); ?>
                </label>

                <div class="col-sm-3">
                    <select class="form-control" name="withdraw_method" id="withdraw-method">
                        <?php foreach ($payment_methods as $value => $name) { ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php echo $name; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-10">
                    <?php wp_nonce_field( 'dokan_withdraw', 'dokan_withdraw_nonce' ); ?>
                    <input type="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Submit Request', 'dokan' ); ?>" name="withdraw_submit">
                </div>
            </div>
        </form>


        <?php
    }

}
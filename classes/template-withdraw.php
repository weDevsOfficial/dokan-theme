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

    function withdraw_csv() {
        if( ! isset( $_POST['dokan-withdraw-csv'] ) ) {
            
            return;
        }
        global $wpdb;
       
        if( $_POST['id'] === null ) {
            return;
        }

        //if id empty then empty value return
        if( ! is_array( $_POST['id'] ) && ! count( $_POST['id'] ) ) {
            return;
        }

        $id = implode( "','", $_POST['id'] );
  
        $result = $wpdb->get_results( 
            "SELECT * FROM {$wpdb->dokan_withdraw}
            WHERE id in('$id') AND status=0"
        );

        if( ! $result ) {
            return;
        }

        foreach( $result as $key => $obj ) {
          
            $data[] = array(
                'email' => get_user_by( 'id', $obj->user_id )->user_email,
                'amount' => $obj->amount,
                'currency' => get_option('woocommerce_currency') ,
            ); 
         
        }
        
        header('Content-type: html/csv');
        header('Content-Disposition: attachment; filename="withdraw-'.date('d-m-y').'.csv"');
        
        foreach ($data as $fields) {
            echo $fields['email']. ',';
            echo $fields['amount']. ',';
            echo $fields['currency'] . "\n";
        }
        die();
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

    function validate() {

        if ( !isset( $_POST['withdraw_submit'] ) ) { 
            return false;
        }

        if( !wp_verify_nonce( $_POST['dokan_withdraw_nonce'], 'dokan_withdraw' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        
        $error = new WP_Error();

        if( empty($_POST['witdraw_amount']) ) {
            $error->add('dokan_empty_withdrad', __('Withdraw amount required ', 'dokan' ));
        } else  {
            if( $_POST['witdraw_amount'] <= 49 ) {
                $error->add('dokan_withdraw_amount', __('Withdraw amount must be greater than 49', 'dokan' ));
            }
        }

        if( empty($_POST['withdraw_method']) ) {
            $error->add('dokan_withdraw_method', __('withdraw method required', 'dokan' ));
        }

        if ( $error->get_error_codes() ) {
            return $error;
        }

        return true;
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
        $wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';
        $data = array(
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'date' => current_time( 'mysql' ),
            'status' => $data['status'],
            'method' => $data['method'],
            'note' => $data['notes'],
            'ip' => $data['ip']
        );

        $format = array('%d', '%f', '%s', '%d', '%s', '%s', '%s');
     
        return $wpdb->insert( $wpdb->dokan_withdraw, $data, $format );
    }

    function insert_withdraw_info() {

        global $current_user, $wpdb;

        $data_info = array(
            'user_id' => $current_user->ID,
            'amount' => filter_var( $_POST['witdraw_amount'], FILTER_SANITIZE_NUMBER_FLOAT ),
            'status' => 0,
            'method' => $_POST['withdraw_method'],
            'ip' => dokan_get_client_ip(),
            'notes' => ''
        );

        $update = $this->insert_withdraw( $data_info );
        if ( !defined('DOING_AJAX') && DOING_AJAX !== true &&  $update) {
            wp_redirect( add_query_arg( array( 'message' => 'request_success' ), get_permalink() ) );
        } 

        if ( defined('DOING_AJAX') && DOING_AJAX === true &&  $update) {

            return $update;
            
        }


    }

    function withdraw_table() {

        global $wpdb;
        $withdraw_db_version = '1.0';
        $installed_ver = get_option( "withdraw_db_version" );

        if ( version_compare( $withdraw_db_version, $installed_ver, '<=' ) ) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->dokan_withdraw} (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `user_id` bigint(20) unsigned NOT NULL,
               `amount` float(11) NOT NULL,
               `date` timestamp NOT NULL,
               `status` int(1) NOT NULL,
               `method` varchar(30) NOT NULL,
               `note` text NOT NULL,
               `ip` varchar(15) NOT NULL,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( "withdraw_db_version", $withdraw_db_version );
        
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

    function get_withdraw_requests( $user_id='', $status = 0 ) {
        global $wpdb;

        $where = empty( $user_id ) ? '' : sprintf( "user_id ='%d' &&", $user_id ); 

        $result = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->dokan_withdraw}
            WHERE $where status = %d", $status
        ));

        return $result;
    }

    function forntend_admin_withdraw_list() {
        $user_id = get_current_user_id();
        $result = $this->get_withdraw_requests($user_id, $status = 0);
        if( ! count( $result ) ) {
            return;
        }
        ?>
        <form method="post" action="">
            <table class="widefat" style="margin-top: 20px;">
                <thead>
                <tr>
                    <th><?php _e( 'User Name', 'dokan' ); ?></th>
                    <th><?php _e( 'User Email', 'dokan' ); ?></th>
                    <th><?php _e( 'Amount', 'dokan' ); ?></th>
                    <th><?php _e( 'Date', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                    <th><?php _e( 'Method', 'dokan' ); ?></th>
                    <th><?php _e( 'Note', 'dokan' ); ?></th>
                    <th><?php _e( 'IP', 'dokan' ); ?></th>
                </tr>
                </thead>

        <?php
        foreach( $result as $key=>$result_array ) {
            $user_data = get_userdata($result_array->user_id);
            ?>

            <tr>
                <td><?php echo $user_data->user_login; ?></td>
                <td><?php echo $user_data->user_email; ?></td>
                <td><?php echo $result_array->amount; ?></td>
                <td><?php echo $result_array->date; ?></td>
                <td>
                    <?php 
                        echo $this->request_status( $result_array->status );
                    ?>
                </td>
                <td><?php echo $result_array->method; ?></td>
                <td >
                    <div class="dokan-add-note" style="width: 130px;">
                        <p class="ajax_note"><?php echo $result_array->note; ?></p>
                        <input type="text" class="dokan-note-text" style="display: none;" name="note">
                        <a class="dokan-note-submit btn btn-info" style="display: none;" data-admin_url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-row_id=<?php echo $result_array->id; ?> data-user_id=<?php echo $result_array->user_id; ?> href="#" ><?php _e('Note', 'dokan' ); ?></a>
                        <a href="#" style="display: none; margin-left: 72px;" class="dokan-note-cancle button"><?php _e('X', 'dokan' ); ?></a>
                        <a href="#" class="dokan-note-field"><?php _e('Add note', 'dokan' ); ?></a>
                    </div>
                    
                </td>
                <td><?php echo $result_array->ip; ?></td>
            </tr>
            <?php

        }
        echo '</table>';
        ?>

        </form>

        <?php
        $this->add_note_script();
    }
    

    function admin_withdraw_list() {
        $user_id = get_current_user_id();
        $result = $this->get_withdraw_requests($user_id, $status = 0);
        if( ! count( $result ) ) {
            return;
        }
        ?>
        <form method="post" action="">
            <table class="widefat" style="margin-top: 20px;">
                <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" id="cb-select-all-1" class="dokan-withdraw-allcheck"></th>
                    <th><?php _e( 'User Name', 'dokan' ); ?></th>
                    <th><?php _e( 'User Email', 'dokan' ); ?></th>
                    <th><?php _e( 'Amount', 'dokan' ); ?></th>
                    <th><?php _e( 'Date', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                    <th><?php _e( 'Method', 'dokan' ); ?></th>
                    <th><?php _e( 'Note', 'dokan' ); ?></th>
                    <th><?php _e( 'IP', 'dokan' ); ?></th>
                </tr>
                </thead>

        <?php
        foreach( $result as $key=>$result_array ) {
            $user_data = get_userdata($result_array->user_id);
            ?>

            <tr>
                <th class="check-column"><input type="checkbox" name="id[]" value="<?php echo $result_array->id;?>"></th>
                <th><?php echo $user_data->user_login; ?></th>
                <th><?php echo $user_data->user_email; ?></th>
                <th><?php echo $result_array->amount; ?></th>
                <th><?php echo $result_array->date; ?></th>
                <th>
                    <?php 
                        echo $this->request_status( $result_array->status );
                    ?>
                </th>
                <th><?php echo $result_array->method; ?></th>
                <th >
                    <div class="dokan-add-note" style="width: 130px;">
                        <p class="ajax_note"><?php echo $result_array->note; ?></p>
                        <input type="text" class="dokan-note-text" style="display: none;" name="note">
                        <a class="dokan-note-submit btn btn-info" style="display: none;" data-admin_url="<?php echo admin_url( 'admin-ajax.php' ); ?>" data-row_id=<?php echo $result_array->id; ?> data-user_id=<?php echo $result_array->user_id; ?> href="#" ><?php _e('Note', 'dokan' ); ?></a>
                        <a href="#" style="display: none; margin-left: 72px;" class="dokan-note-cancle button"><?php _e('X', 'dokan' ); ?></a>
                        <a href="#" class="dokan-note-field"><?php _e('Add note', 'dokan' ); ?></a>
                    </div>
                    
                </th>
                <th><?php echo $result_array->ip; ?></th>
            </tr>
            <?php

        }
        echo '</table>';
        ?>
        <input type="submit" name="dokan-withdraw-csv" class="button" value="Download">
        </form>
        <?php

        $this->add_note_script();
    }

    function add_note_script() {
        ?>
        <script type="text/javascript">

        jQuery(function($) {
            var dokan_admin = {
                init: function() {
                    $('div.dokan-add-note').on('click', 'a.dokan-note-field', this.addnote);
                    $('div.dokan-add-note').on('click', 'a.dokan-note-cancle', this.addnoteCancle);
                    $('div.dokan-add-note').on('click', 'a.dokan-note-submit', this.noteUpdate);
                },

                noteUpdate: function(e) {
                    e.preventDefault();

                    var self = $(this),
                    row_id = self.data('row_id'),
                    note = self.siblings('input.dokan-note-text').val(),
                    ajaxurl = self.data('admin_url');
                    data = {
                        'action': 'note',
                        'row_id': row_id,
                        'note': note,
                    };
                    
                    $.post( ajaxurl, data, function(resp) {
                        if(resp.success) {
                        
                            self.siblings('p.ajax_note').text(resp.data['note']);
                            self.hide();
                            self.siblings('input.dokan-note-text').hide();
                            self.siblings('a.dokan-note-cancle').hide();
                            self.siblings('a.dokan-note-field').show();
                        }
                    });
                },

                addnoteCancle: function(e) {
                    e.preventDefault();
                    var self = $(this);
                    self.hide();

                    self.siblings( "a.dokan-note-submit" ).hide();
                    self.siblings('input.dokan-note-text').hide();
                    self.siblings('a.dokan-note-field').show();

                },

                addnote: function(e) {
                    e.preventDefault();
                    var self = $(this);
                    
                    self.hide();
                    self.siblings( "a.dokan-note-submit" ).show();
                    self.siblings('input.dokan-note-text').show();
                    self.siblings('a.dokan-note-cancle').show();
                
                }
            }
            dokan_admin.init();
        })
        </script>

        <?php
    }

    function note_update() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dokan_withdraw';
        $update = $wpdb->update( $table_name, array('note' => sanitize_text_field( $_POST['note'] ) ), array( 'id' => $_POST['row_id'] ) );
        if( $update ) {
            $html = array(
                'note' => $_POST['note'],
            );
            wp_send_json_success( $html);
        } else {
            wp_send_json_error();
        }
        
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
            '' => __( '- Select Method -', 'dokan' ),
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
                    <strong><p><?php _e( 'Your request has been cancelled successfully!', 'dokan' ); ?></p></strong>
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
            case 'request_error':
                ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?php _e( 'Unknown error!', 'dokan' ); ?></strong>
                </div>
                <?php
                break;
        }
    }

    function withdraw_form($validate='') {
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

        if( is_wp_error($validate) ) {
            $amount = $_POST['witdraw_amount'];
            $withdraw_method = $_POST['withdraw_method'];
        } else {
            $amount = '';
            $withdraw_method = '';
        }
        ?>


        <div class="alert  alert-danger" style="display: none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong class="jquery_error_place"></strong>
        </div>

        <!-- <div class="alert  alert-success" style="display: none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong></strong>
        </div> -->
        <span class="ajax_table_shown"></span>
        <form class="form-horizontal withdraw" role="form" method="post">
            <div class="form-group">

                <label for="withdraw-amount" class="col-sm-3 control-label">
                    <?php _e( 'Withdraw Amount' ); ?>
                </label>

                <div class="col-sm-3 ">
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                        <input name="witdraw_amount" required number min="50" class="form-control" id="withdraw-amount" name="price" type="number" placeholder="9.99" value="<?php echo $amount; ?>"  >
                    </div>

                </div>
            </div>

            <div class="form-group">
                <label for="withdraw-method" class="col-sm-3 control-label">
                    <?php _e( 'Payment Method', 'dokan' ); ?>
                </label>

                <div class="col-sm-3">
                    <select class="form-control" required name="withdraw_method" id="withdraw-method">
                        <?php foreach ($payment_methods as $value => $name) { ?>
                            <option <?php selected( $withdraw_method, $value );  ?>value="<?php echo esc_attr( $value ); ?>"><?php echo $name; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-10 ajax_prev" style="width: 200px;">
                    <?php wp_nonce_field( 'dokan_withdraw', 'dokan_withdraw_nonce' ); ?>
                    <input type="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Submit Request', 'dokan' ); ?>" name="withdraw_submit">
                </div>
            </div>
        </form>


        <?php
    }

}
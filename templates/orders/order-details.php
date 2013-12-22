<?php
global $woocommerce;

$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
$order = new WC_Order( $order_id );
// var_dump($the_order);
?>
<div class="row">
    <div class="col-md-8">

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Order Items</div>
                    <div class="panel-body">

                        <table cellpadding="0" cellspacing="0" class="table order-items">
                            <thead>
                                <tr>
                                    <th class="item" colspan="2"><?php _e( 'Item', 'woocommerce' ); ?></th>

                                    <?php do_action( 'woocommerce_admin_order_item_headers' ); ?>

                                    <?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) : ?>
                                        <th class="tax_class"><?php _e( 'Tax Class', 'woocommerce' ); ?></th>
                                    <?php endif; ?>

                                    <th class="quantity"><?php _e( 'Qty', 'woocommerce' ); ?></th>

                                    <th class="line_cost"><?php _e( 'Totals', 'woocommerce' ); ?></th>

                                    <?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) : ?>
                                        <th class="line_tax"><?php _e( 'Tax', 'woocommerce' ); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="order_items_list">

                                <?php
                                    // List order items
                                    $order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item', 'fee' ) ) );

                                    foreach ( $order_items as $item_id => $item ) {

                                        switch ( $item['type'] ) {
                                            case 'line_item' :
                                                $_product   = $order->get_product_from_item( $item );
                                                $item_meta  = $order->get_item_meta( $item_id );

                                                include( 'order-item-html.php' );
                                            break;
                                            case 'fee' :
                                                include( 'order-fee-html.php' );
                                            break;
                                        }

                                        do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item );

                                    }
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">Billing Address</div>
                    <div class="panel-body">
                        <?php echo $order->get_formatted_billing_address(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">Shipping Address</div>
                    <div class="panel-body">
                        <?php echo $order->get_formatted_shipping_address(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">General Details</div>
                    <div class="panel-body general-details">
                        <ul class="list-unstyled order-status">
                            <li>
                                <span>Order Status:</span>
                                <label class="label label-success">Completed</label>
                            </li>
                            <li>
                                <span>Order Date:</span>
                                12 Dec, 2013 12:53pm
                            </li>
                        </ul>

                        <ul class="list-unstyled customer-details">
                            <li>
                                <span>Customer:</span>
                                <a href="#">Nizam Udding</a><br>
                            </li>
                            <li>
                                <span>Email:</span>
                                nizam@wedevs.com
                            </li>
                            <li>
                                <span>Phone:</span>
                                93838920
                            </li>
                            <li>
                                <span>Client IP:</span>
                                127.0.0.1
                            </li>
                        </ul>

                        <div class="alert alert-success customer-note">
                            <strong>Customer Note:</strong><br>
                            Please do something bla bla bla
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php _e( 'Order Notes', 'dokan' ); ?></div>
                    <div class="panel-body">
                        <?php
                        $args = array(
                            'post_id' => $order_id,
                            'approve' => 'approve',
                            'type' => 'order_note'
                        );

                        remove_filter('comments_clauses', 'woocommerce_exclude_order_comments');
                        $notes = get_comments( $args );

                        echo '<ul class="order_notes list-unstyled">';

                        if ( $notes ) {
                            foreach( $notes as $note ) {
                                $note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

                                ?>
                                <li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
                                    <div class="note_content">
                                        <?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
                                    </div>
                                    <p class="meta">
                                        <?php printf( __( 'added %s ago', 'woocommerce' ), human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ); ?> <a href="#" class="delete_note"><?php _e( 'Delete note', 'woocommerce' ); ?></a>
                                    </p>
                                </li>
                                <?php
                            }
                        } else {
                            echo '<li>' . __( 'There are no notes for this order yet.', 'woocommerce' ) . '</li>';
                        }

                        echo '</ul>';

                        add_filter('comments_clauses', 'woocommerce_exclude_order_comments');
                        ?>
                        <div class="add_note">
                            <h4><?php _e( 'Add note', 'woocommerce' ); ?></h4>
                            <form class="form-inline" id="add-order-note" role="form" method="post">
                                <p>
                                    <textarea type="text" name="order_note" class="form-control" cols="20" rows="3"></textarea>
                                </p>
                                <div class="clearfix">
                                    <div class="col-md-8 order_note_type">
                                        <select name="order_note_type" id="order_note_type" class="form-control">
                                            <option value="customer"><?php _e( 'Customer note', 'dokan' ); ?></option>
                                            <option value=""><?php _e( 'Private note', 'dokan' ); ?></option>
                                        </select>
                                    </div>

                                    <input type="submit" name="add_order_note" class="add_note btn btn-sm btn-primary" value="<?php esc_attr_e( 'Add Note', 'dokan' ); ?>">
                                </div>
                            </form>
                        </div> <!-- .add_note -->
                    </div> <!-- .panel-body -->
                </div> <!-- .panel -->
            </div>
        </div> <!-- .row -->

    </div> <!-- .col-md-4 -->

</div>
<?php
/**
 * Dokan Coupons Class
 *
 * @author weDevs
 */
class Dokan_Template_Coupons{

    private $perpage = 2;
    private $total_query_result;

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Template_Coupons();
        }

        return $instance;
    }

    function coupun_delete() {

        if ( !isset( $_GET['post'] ) || !isset( $_GET['action'] ) ) {
            return;
        } else if ( $_GET['action'] != 'delete' ) {
            return;
        }

        if ( !wp_verify_nonce( $_GET['coupon_del_nonce'], '_coupon_del_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        wp_delete_post( $_GET['post'], true );

        wp_redirect( add_query_arg( array('message' => 'delete_succefully'), get_permalink() ) );
    }

    function validate() {

        if ( !isset( $_POST['coupon_creation'] ) ) {
            return;
        }

        if ( !wp_verify_nonce( $_POST['coupon_nonce_field'], 'coupon_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $errors = new WP_Error();

        if ( empty( $_POST['title'] ) ) {
            $errors->add( 'title', __( 'Please enter the coupon title', 'dokan' ) );
        }

        if ( empty( $_POST['amount'] ) ) {
            $errors->add( 'amount', __( 'Please enter the amount', 'dokan' ) );
        }

        if ( !isset( $_POST['product_drop_down'] ) || !count( $_POST['product_drop_down'] ) ) {
            $errors->add( 'products', __( 'Please specify any products', 'dokan' ) );
        }

        if ( $errors->get_error_codes() ) {
            return $errors;
        }

        return true;
    }

    function coupons_create() {

        if ( !isset( $_POST['coupon_creation'] ) ) {
            return;
        }
        if ( !wp_verify_nonce( $_POST['coupon_nonce_field'], 'coupon_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }


        if ( empty( $_POST['post_id'] ) ) {

            $post = array(
                'post_title' => $_POST['title'],
                'post_content' => $_POST['description'],
                'post_status' => 'publish',
                'post_type' => 'shop_coupon',
            );

            $post_id = wp_insert_post( $post );

            $message = 'coupon_saved';
        } else {

            $post = array(
                'ID' => $_POST['post_id'],
                'post_title' => $_POST['title'],
                'post_content' => $_POST['description'],
                'post_status' => 'publish',
                'post_type' => 'shop_coupon',
            );
            $post_id = wp_update_post( $post );
            $message = 'coupon_update';
        }

        if ( !$post_id )
            return;

        $customer_email = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $_POST['email_restrictions'] ) ) ) );
        $type = sanitize_text_field( $_POST['discount_type'] );
        $amount = sanitize_text_field( $_POST['amount'] );
        $usage_limit = empty( $_POST['usage_limit'] ) ? '' : absint( $_POST['usage_limit'] );
        $expiry_date = sanitize_text_field( $_POST['expire'] );

        $apply_before_tax = isset( $_POST['apply_before_tax'] ) ? 'yes' : 'no';
        $free_shipping = isset( $_POST['enable_free_ship'] ) ? 'yes' : 'no';
        $exclude_sale_items = isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no';
        $minimum_amount = sanitize_text_field( $_POST['minium_ammount'] );



        if ( isset( $_POST['product_drop_down'] ) ) {
            $product_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['product_drop_down'] ) ) );
        } else {
            $product_ids = '';
        }

        if ( isset( $_POST['exclude_product_ids'] ) ) {
            $exclude_product_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['exclude_product_ids'] ) ) );
        } else {
            $exclude_product_ids = '';
        }

        update_post_meta( $post_id, 'discount_type', $type );
        update_post_meta( $post_id, 'coupon_amount', $amount );
        update_post_meta( $post_id, 'product_ids', $product_ids );
        update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
        update_post_meta( $post_id, 'usage_limit', $usage_limit );
        update_post_meta( $post_id, 'expiry_date', $expiry_date );
        update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
        update_post_meta( $post_id, 'free_shipping', $free_shipping );
        update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
        update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
        update_post_meta( $post_id, 'customer_email', $customer_email );

        if ( !defined( 'DOING_AJAX' ) ) {
            wp_redirect( add_query_arg( array('message' => $message), get_permalink() ) );
        }
    }

    function message() {
        if ( isset( $_GET['message'] ) && $_GET['message'] == 'delete_succefully' ) {
            ?>
            <div class="dokan-message">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?php _e( 'Coupon has been deleted successfully!', 'dokan' ); ?></strong>
            </div>
            <?php
        }

        if ( isset( $_GET['message'] ) && $_GET['message'] == 'coupon_saved' ) {
            ?>
            <div class="dokan-message">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?php _e('Coupon has been saved successfully!','dokan'); ?></strong>
            </div>
            <?php
        }

        if ( isset( $_GET['message'] ) && $_GET['message'] == 'coupon_update' ) {
            ?>
            <div class="dokan-message">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?php _e('Coupon has been updated successfully!','dokan'); ?></strong>
            </div>
            <?php
        }

    }

    function list_user_coupons() {
        //click add coupon then hide this function
        if( isset( $_GET['view'] ) && $_GET['view'] == 'add_coupons'  ) {
            return;
        }

        if( isset($_GET['post']) &&  $_GET['action'] == 'edit' ) {
            return;
        }

        $perpage = $this->perpage;
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $offset = ( $pagenum - 1 ) * $perpage;

        $paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type' => 'shop_coupon',
            'post_status' => array('publish'),
            'posts_per_page' => $this->perpage,
            'offset' => $offset,
            'author' => get_current_user_id(),
            'paged' => $paged
        );


        $coupon_query = new WP_Query( $args );
        $all_coupons = $coupon_query->get_posts();


        if ( $all_coupons ) {

            //pagination total
            $this->total_query_result = $coupon_query->found_posts;

            //message save, update, delte

            $this->message();
            ?>

            <table class="table">
                <thead>
                    <tr>
                        <th><?php _e('Code', 'dokan'); ?></th>
                        <th><?php _e('Coupon type', 'dokan'); ?></th>
                        <th><?php _e('Coupon amount', 'dokan'); ?></th>
                        <th><?php _e('Product IDs', 'dokan'); ?></th>
                        <th><?php _e('Usage / Limit', 'dokan'); ?></th>
                        <th><?php _e('Expiry date', 'dokan'); ?></th>
                    </tr>
                </thead>

            <?php

            foreach($coupon_query->posts as $key => $post) {

                ?>
                <tr>
                    <td class="coupon-code">
                        <?php $edit_url =  wp_nonce_url( add_query_arg( array('post' => $post->ID, 'action' => 'edit', 'view' => 'add_coupons'), get_permalink() ), '_coupon_nonce', 'coupon_nonce_url' ); ?>
                        <div class="code">
                            <a href="<?php echo $edit_url; ?>"><span><?php echo esc_attr( $post->post_title ); ?></span></a>
                        </div>

                        <div class="row-actions">
                            <?php $del_url = wp_nonce_url( add_query_arg( array('post' => $post->ID, 'action' => 'delete'), get_permalink() ) ,'_coupon_del_nonce', 'coupon_del_nonce'); ?>

                            <span class="edit"><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'dokan' ); ?></a> | </span>
                            <span class="delete"><a  href="<?php echo $del_url; ?>"  onclick="return confirm('<?php esc_attr_e( 'Are you sure want to delete', 'dokan' ); ?>');"><?php _e('delete', 'dokan'); ?></a></span>
                        </div>
                    </td>

                    <td>
                        <?php
                        $discount_type = get_post_meta( $post->ID, 'discount_type', true );
                        $type = '';

                        if ( $discount_type == 'fixed_product' ) {
                            $type = __( 'Fixed Amount', 'dokan' );
                        } elseif ( $discount_type == 'percent_product' ) {
                            $type = __( 'Percent', 'dokan' );
                        }

                        echo $type;
                        ?>
                    </td>

                    <td>
                        <?php echo esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ); ?>
                    </td>

                    <td>
                        <?php
                            $product_ids = get_post_meta( $post->ID, 'product_ids', true );
                            $product_ids = $product_ids ? array_map( 'absint', explode( ',', $product_ids ) ) : array();

                            if ( sizeof( $product_ids ) > 0 )
                                echo esc_html( implode( ', ', $product_ids ) );
                            else
                            echo '&ndash;';
                        ?>
                    </td>

                    <td>
                        <?php

                            $usage_count = absint( get_post_meta( $post->ID, 'usage_count', true ) );
                            $usage_limit = esc_html( get_post_meta($post->ID, 'usage_limit', true) );

                            if ( $usage_limit )
                                printf( __( '%s / %s', 'dokan' ), $usage_count, $usage_limit );
                            else
                                printf( __( '%s / &infin;', 'dokan' ), $usage_count );
                         ?>
                    </td>

                    <td>
                        <?php
                            $expiry_date = get_post_meta($post->ID, 'expiry_date', true);

                            if ( $expiry_date )
                                echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
                            else
                                echo '&ndash;';
                        ?>
                    </td>
                </tr>
                <?php
            }

            echo '</table>';

            echo $this->pagination();
        } else {
            ?>
            <p class="dokan-message"><?php _e( 'No coupons found!', 'dokan' ); ?></p>
            <?php
        }
    }

    function pagination() {

        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $num_of_pages = ceil( $this->total_query_result / $this->perpage );

        $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'aag' ),
            'next_text' => __( '&raquo;', 'aag' ),
            'total' => $num_of_pages,
            'current' => $pagenum
        ) );

        if ( $page_links ) {
            return '<div class="wpuf-pagination">' . $page_links . '</div>';
        }
    }

    function add_coupons_form($validated) {

        //intial time hide this function
        if ( !isset( $_GET['view'] ) ) {
            return;
        } else if ( $_GET['view'] != 'add_coupons' ) {
            return;
        }

        $button_name = __( 'Create Coupon', 'dokan' );

        if ( isset( $_GET['post'] ) && $_GET['action'] == 'edit' ) {
            if ( !wp_verify_nonce( $_GET['coupon_nonce_url'], '_coupon_nonce' ) ) {
                wp_die( __( 'Are you cheating?', 'dokan' ) );
            }

            $post = get_post( $_GET['post'] );
            $button_name = __( 'Update Coupon', 'dokan' );

            $discount_type = get_post_meta( $post->ID, 'discount_type', true );
            $amount = get_post_meta( $post->ID, 'coupon_amount', true );

            $products = get_post_meta( $post->ID, 'product_ids', true );
            $exclude_products = get_post_meta( $post->ID, 'exclude_product_ids', true );
            $usage_limit = get_post_meta( $post->ID, 'usage_limit', true );
            $expire = get_post_meta( $post->ID, 'expiry_date', true );
            $apply_before_tax = get_post_meta( $post->ID, 'apply_before_tax', true );
            $free_shipping = get_post_meta( $post->ID, 'free_shipping', true );
            $exclide_sale_item = get_post_meta( $post->ID, 'exclude_sale_items', true );
            $minimum_amount = get_post_meta( $post->ID, 'minimum_amount', true );
            $customer_email = get_post_meta( $post->ID, 'customer_email', true );
        }

        $post_id = isset( $post->ID ) ? $post->ID : '';
        $post_title = isset( $post->post_title ) ? $post->post_title : '';
        $description = isset( $post->post_content ) ? $post->post_content : '';

        $discount_type = isset( $discount_type ) ? $discount_type : '';
        if ( isset( $discount_type ) ) {
            if ( $discount_type == 'coupon_percent_product' ) {
                $discount_type = 'selected';
            }
        }

        $amount = isset( $amount ) ? $amount : '';
        $products = isset( $products ) ? $products : '';
        $exclude_products = isset( $exclude_products ) ? $exclude_products : '';
        $usage_limit = isset( $usage_limit ) ? $usage_limit : '';
        $expire = isset( $expire ) ? $expire : '';

        if ( isset( $free_shipping ) && $free_shipping == 'yes' ) {
            $free_shipping = 'checked';
        } else {
            $free_shipping = '';
        }

        if ( isset( $apply_before_tax ) && $apply_before_tax == 'yes' ) {
            $apply_before_tax = 'checked';
        } else {
            $apply_before_tax = '';
        }


        if ( isset( $exclide_sale_item ) && $exclide_sale_item == 'yes' ) {
            $exclide_sale_item = 'checked';
        } else {
            $exclide_sale_item = '';
        }

        $minimum_amount = isset( $minimum_amount ) ? $minimum_amount : '';
        $customer_email = isset( $customer_email ) ? implode( ',', $customer_email ) : '';

        if ( is_wp_error( $validated ) ) {

            $post_id = $_POST['post_id'];
            $post_title = $_POST['title'];
            $description = $_POST['description'];

            $discount_type = $_POST['discount_type'];

            if ( $discount_type == 'coupon_percent_product' ) {
                $discount_type = 'selected';
            }

            $amount = $_POST['amount'];


            if ( isset( $_POST['product_drop_down'] ) ) {
                $products = implode( ',', array_filter( array_map( 'intval', (array) $_POST['product_drop_down'] ) ) );
            } else {
                $products = '';
            }


            if ( isset( $_POST['exclude_product_ids'] ) ) {
                $exclude_products = implode( ',', array_filter( array_map( 'intval', (array) $_POST['exclude_product_ids'] ) ) );
            } else {
                $exclude_products = '';
            }

            $usage_limit = $_POST['usage_limit'];
            $expire = $_POST['expire'];



            if ( isset( $_POST['enable_free_ship'] ) && $_POST['enable_free_ship'] == 'yes' ) {
                $free_shipping = 'checked';
            } else {
                $free_shipping = '';
            }

            if ( isset( $_POST['apply_before_tax'] ) && $_POST['apply_before_tax'] == 'yes' ) {
                $apply_before_tax = 'checked';
            } else {
                $apply_before_tax = '';
            }


            if ( isset( $_POST['exclude_sale_items'] ) && $_POST['exclude_sale_items'] == 'yes' ) {
                $exclide_sale_item = 'checked';
            } else {
                $exclide_sale_item = '';
            }

            $minimum_amount = $_POST['minium_ammount'];
            $customer_email = $_POST['email_restrictions'];
        }


        ?>

        <div class="alert  alert-danger" style="display: none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong></strong>
        </div>

        <div class="alert  alert-success" style="display: none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong></strong>
        </div>

        <form method="post" action="" class="form-horizontal coupons">
            <input type="hidden"  value="<?php echo $post_id; ?>" name="post_id">
            <?php wp_nonce_field('coupon_nonce','coupon_nonce_field'); ?>
            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-3 control-label" for="title"><?php _e( 'Coupon Title', 'dokan' ); ?><span class="required"> *</span></label>
                <div class="col-md-5">
                    <input id="title" name="title" required value="<?php echo esc_attr( $post_title ); ?>" placeholder="Title" class="form-control input-md" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="description"><?php _e( 'Description', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <textarea class="form-control" id="description" name="description"><?php echo esc_textarea( $description ); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="discount_type"><?php _e( 'Discount Type', 'dokan' ); ?></label>

                <div class="col-md-5">
                    <select id="discount_type" name="discount_type" class="form-control">
                        <option value="fixed_product"><?php _e( 'Product Discount', 'dokan' ); ?></option>
                        <option value="percent_product"><?php _e( 'Product % Discount', 'dokan' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="amount"><?php _e( 'Amount', 'dokan' ); ?><span class="required"> *</span></label>
                <div class="col-md-5">
                    <input id="amount" required value="<?php echo esc_attr( $amount ); ?>" name="amount" placeholder="Amount" class="form-control input-md" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="email_restrictions"><?php _e( 'Email Restrictions', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <input id="email_restrictions" value="<?php echo esc_attr( $customer_email ); ?>" name="email_restrictions" placeholder="<?php _e( 'Email restrictions', 'dokan' ); ?>" class="form-control input-md" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="usage_limit"><?php _e( 'Usage Limit', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <input id="usage_limit" value="<?php echo esc_attr( $usage_limit ); ?>" name="usage_limit" placeholder="<?php _e( 'Usage Limit', 'dokan' ); ?>" class="form-control input-md" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="dokan-expire"><?php _e( 'Expire Date', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <input id="dokan-expire" value="<?php echo esc_attr( $expire ); ?>" name="expire" placeholder="<?php _e( 'Expire Date', 'dokan' ); ?>" class="form-control input-md datepicker" type="text">
                </div>
            </div>

            <?php
                $paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
                $args = array(
                    'post_type' => 'product',
                    'post_status' => array('publish', 'draft', 'pending'),
                    'posts_per_page' => 10,
                    'author' => get_current_user_id(),
                    'paged' => $paged
                );

                $query = new WP_Query( $args );
                $products_id = str_replace( ' ', '', $products );
                $products_id = explode( ',', $products_id );
            ?>

            <div class="form-group">
                <label class="col-md-3 control-label" for="product"><?php _e( 'Product', '' ); ?><span class="required"> *</span></label>
                <div class="col-md-5">
                    <select id="product" required name="product_drop_down[]" class="form-control" multiple>
                        <?php
                        foreach ($query->posts as $key => $object) {
                            if ( in_array( $object->ID, $products_id ) ) {
                                $select = 'selected';
                            } else {
                                $select = '';
                            }
                            ?>
                            <option <?php echo $select; ?>  value="<?php echo $object->ID; ?>"><?php _e( $object->post_title, 'dokan' ); ?></option>

                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="checkboxes"><?php _e( 'Enable Free Shipping', 'dokan' ); ?></label>
                <div class="col-md-9">
                    <div class="checkbox">
                        <label for="checkboxes-0">
                            <input name="enable_free_ship" <?php echo $free_shipping; ?> id="checkboxes-0" value="yes" type="checkbox">
                            <?php _e( 'Check this box if the coupon grants free shipping.', 'dokan' ); ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="checkboxes"><?php _e( 'Exclude Sale Items', 'dokan' ); ?></label>
                <div class="col-md-9">
                    <div class="checkbox">
                        <label for="checkboxes-2">
                            <input name="exclude_sale_items" <?php echo $exclide_sale_item; ?> id="checkboxes-2" value="yes" type="checkbox">
                            <?php _e( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are no sale items in the cart.', 'dokan' ); ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="minium_ammount"><?php _e( 'Minimum Amount', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <input id="minium_ammount" value="<?php echo $minimum_amount; ?>" name="minium_ammount" placeholder="<?php esc_attr_e( 'Minimum Amount', 'dokan' ); ?>" class="form-control input-md" type="text">
                </div>
            </div>

            <?php
            $exclude_products = str_replace( ' ', '', $exclude_products );
            $exclude_products = explode( ',', $exclude_products );
            ?>
            <div class="form-group">
                <label class="col-md-3 control-label" for="product"><?php _e( 'Exclude products', 'dokan' ); ?></label>
                <div class="col-md-5">
                    <select id="coupon_exclude_categories" name="exclude_product_ids[]" class="form-control" multiple>
                        <?php
                        foreach ($query->posts as $key => $object) {
                            if ( in_array( $object->ID, $exclude_products ) ) {
                                $select = 'selected';
                            } else {
                                $select = '';
                            }
                            ?>
                            <option <?php echo $select; ?>  value="<?php echo $object->ID; ?>"><?php _e( $object->post_title, 'dokan' ); ?></option>

                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for=""></label>
                <div class="col-md-4 ajax_prev">
                    <input type="submit" id="" name="coupon_creation" value="<?php echo $button_name; ?>" class="btn btn-theme">
                </div>
            </div>

        </form>

        <script type="text/javascript">

            jQuery(function($){
                $("#product").chosen({width: "95%"});
                $("#coupon_exclude_categories").chosen({width: "95%"});
            });

        </script>
        <?php
    }
}
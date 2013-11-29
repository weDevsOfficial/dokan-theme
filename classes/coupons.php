<?php
/**
 * Dokan settings Class
 *
 * @ weDves
 */
error_reporting(E_ALL);

class Dokan_Template_Coupons{

    public static function init() {
        static $instance = false;

        if( !$instance ) {
            $instance = new Dokan_Template_Coupons();
        }

        return $instance;
    }

    function coupons_create() {
        if( !isset($_POST['coupon_creation'] ) ) {
            return; 
        }
        if( !wp_verify_nonce( $_POST['coupon_nonce_field'], 'coupon_nonce') ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $post = array(
            'post_title'    => $_POST['title'],
            'post_content'  => $_POST['description'],
            'post_status'   => 'publish',
            'post_type'     => 'shop_coupon',
        );
        
        if( !isset($_POST['post_id']) ) {
            $post_id = wp_insert_post( $post );
        } else {
            $post_id = $_POST['post_id'];
        }

        if( !$post_id ) return;

        $customer_email     = array_filter( array_map( 'trim', explode( ',', woocommerce_clean( $_POST['email_restrictions'] ) ) ) );
        $type               = woocommerce_clean( $_POST['discount_type'] );
        $amount             = woocommerce_clean( $_POST['amount'] );
        $usage_limit        = empty( $_POST['usage_limit'] ) ? '' : absint( $_POST['usage_limit'] );
        $expiry_date        = woocommerce_clean( $_POST['expire'] );

        $apply_before_tax   = isset( $_POST['apply_before_tax'] ) ? 'yes' : 'no';
        $free_shipping      = isset( $_POST['enable_free_ship'] ) ? 'yes' : 'no';
        $exclude_sale_items = isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no';
        $minimum_amount     = woocommerce_clean( $_POST['minium_ammount'] );



        if ( isset( $_POST['product_ids'] ) ) {
            $product_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['product_drop_down'] ) ) );
        } else {
            $product_ids = '';
        }

        if ( isset( $_POST['exclude_product_ids'] ) ) {
            $exclude_product_ids    = implode( ',', array_filter( array_map( 'intval', (array) $_POST['exclude_product_ids'] ) ) );
        } else {
            $exclude_product_ids = '';
        }

        update_post_meta( $post_id, 'discount_type', $type  );
        update_post_meta( $post_id, 'coupon_amount', $amount  );

        update_post_meta( $post_id, 'product_ids', $product_ids );
        update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
        update_post_meta( $post_id, 'usage_limit', $usage_limit );
        update_post_meta( $post_id, 'expiry_date', $expiry_date );
        update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
        update_post_meta( $post_id, 'free_shipping', $free_shipping );
        update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
        update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
        update_post_meta( $post_id, 'customer_email', $customer_email );

        wp_redirect( add_query_arg( array( 'message' => 'coupon_saved' ), get_permalink() ) );
    }

    function user_coupons() {

        if( isset($_GET['post']) &&  $_GET['action'] == 'edit' ) {
            return;
        }

        $paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type' => 'shop_coupon',
            'post_status' => array('publish'),
            'posts_per_page' => 10,
            'author' => get_current_user_id(),
            'paged' => $paged
        );

        $query = new WP_Query( $args );
        if( !is_array( $query->posts ) || !count($query->posts) ) {
            return;
        }

        ?>
        <table class="table">
            <tr>
                <th><?php _e('Code', 'dokan'); ?></th>
                <th><?php _e('Coupon type', 'dokan'); ?></th>
                <th><?php _e('Coupon amount', 'dokan'); ?></th>
                <th><?php _e('Description', 'dokan'); ?></th>
                <th><?php _e('Product IDs', 'dokan'); ?></th>
                <th><?php _e('Usage / Limit', 'dokan'); ?></th>
                <th><?php _e('Expiry date', 'dokan'); ?></th>
            </tr>   

        <?php

        foreach($query->posts as $key=>$post) {

            ?>
            <tr>
                <td>
                    <?php $url=  wp_nonce_url( add_query_arg( array('post' => $post->ID, 'action' => 'edit'), get_permalink() ), '_coupon_nonce', 'coupon_nonce_url' ); ?>
                    <a href="<?php echo $url; ?>"><?php echo esc_attr( $post->post_title ); ?></a>
                </td>

                <td>
                    <?php echo esc_attr( get_post_meta( $post->ID, 'discount_type', true ) ); ?>
                </td>

                <td>
                    <?php echo esc_attr( get_post_meta( $post->ID, 'coupon_amount', true ) ); ?>
                </td>

                <td>
                    <?php echo esc_attr( $post->post_content ); ?>
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
    }



    function add_coupons_form() {

        if( isset($_GET['post']) &&  $_GET['action'] == 'edit' ) {
            if( !wp_verify_nonce( $_GET['coupon_nonce_url'], '_coupon_nonce') ) {
                wp_die( __( 'Are you cheating?', 'dokan' ) );
            }

            $post = get_post($_GET['post']);
        

            $discount_type = get_post_meta( $post->ID, 'discount_type', true  );
            $amount = get_post_meta( $post->ID, 'coupon_amount', true  );

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
        if(isset($discount_type)) {
            if( $discount_type == 'coupon_percent_product') {
                $discount_type = 'selected';
            }
        } 
        $amount = isset( $amount ) ? $amount : '';
        $products = isset( $products ) ? $products : '';
        $exclude_products = isset( $exclude_products ) ? $exclude_products : '';
        $usage_limit = isset( $usage_limit ) ? $usage_limit : '';
        $expire = isset( $expire ) ? $expire : '';

        if( isset( $free_shipping ) && $free_shipping == 'yes') {
            $free_shipping = 'checked';
        } else {
            $free_shipping = '';
        }
        if( isset($apply_before_tax) && $apply_before_tax == 'yes' ) {
            $apply_before_tax = 'checked';
        } else {
            $apply_before_tax = '';
        }
        

        if( isset($exclide_sale_item) && $exclide_sale_item == 'yes' ) {
            $exclide_sale_item = 'checked';
        } else {
            $exclide_sale_item = '';
        }
        $minimum_amount = isset( $minimum_amount ) ? $minimum_amount : '';
        $customer_email = isset( $customer_email ) ? implode(',', $customer_email) : '';
        
        ?>


        <form method="post" action="" class="form-horizontal">
            <input type="text" hidden value="<?php echo $post_id; ?>" name="post_id">
            <?php wp_nonce_field('coupon_nonce','coupon_nonce_field'); ?>
            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="title"><?php _e('Copon Title', 'dokan'); ?></label>  
              <div class="col-md-5">
              <input id="title" name="title" value="<?php echo $post_title; ?>" placeholder="Title" class="form-control input-md" type="text">
                
              </div>
            </div>


            <!-- Textarea -->
            <div class="form-group">
              <label class="col-md-3 control-label" for="description"><?php _e('Description', 'dokan'); ?></label>
              <div class="col-md-5">                     
                <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
              </div>
            </div>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-3 control-label" for="discount_type"><?php _e('Discount Type','dokan'); ?></label>
              <div class="col-md-5">
                <select id="discount_type" name="discount_type" class="form-control">
                  <option value="coupon_fixed_product"><?php _e( 'Product Discount', 'dokan'); ?></option>
                  <option <?php echo $discount_type; ?> value="coupon_percent_product"><?php _e('Product % Discount','dokan'); ?></option>
                </select>
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="amount"><?php _e('Amount', 'dokan'); ?></label>  
              <div class="col-md-5">
              <input id="amount" value="<?php echo $amount; ?>" name="amount" placeholder="Amount" class="form-control input-md" type="text">
                
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="email_restrictions"><?php _e('Email Restrictions','dokan'); ?></label>  
              <div class="col-md-5">
              <input id="email_restrictions" value="<?php echo $customer_email; ?>" name="email_restrictions" placeholder="Email restrictions" class="form-control input-md" type="text">
                
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="usage_limit"><?php _e('Usage Limit','dokan'); ?></label>  
              <div class="col-md-5">
              <input id="usage_limit" value="<?php echo $usage_limit; ?>" name="usage_limit" placeholder="Usage Limit" class="form-control input-md" type="text">
                
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="expire"><?php _e('Expire Date','dokan'); ?></label>  
              <div class="col-md-5">
              <input id="expire" value="<?php echo $expire; ?>" name="expire" placeholder="expire Date" class="form-control input-md" type="text">
                
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
                    

                ?>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-3 control-label" for="product"><?php _e('Product',''); ?></label>
              <div class="col-md-5">
                <select id="product" name="product_drop_down[]" class="form-control" multiple>
                    <?php
                    foreach($query->posts as $key=>$object) {
                        ?>
                        <option  value="<?php echo $object->ID; ?>"><?php _e($object->post_title, 'dokan'); ?></option>

                        <?php
                        
                    }
                    ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-3 control-label" for="checkboxes"><?php _e('Enable Free Shipping', 'dokan'); ?></label>
              <div class="col-md-9">
              <div class="checkbox">
                <label for="checkboxes-0">
                  <input name="enable_free_ship" <?php echo $free_shipping; ?> id="checkboxes-0" value="yes" type="checkbox">
                  <?php _e('Check this box if the coupon grants free shipping.','dokan'); ?>
                </label>
                </div>
              </div>
            </div>


            <div class="form-group">
              <label class="col-md-3 control-label" for="checkboxes"><?php _e('Apply Before Tax','dokan'); ?></label>
              <div class="col-md-9">
              <div class="checkbox">
                <label for="checkboxes-1">
                  <input name="apply_before_tax" <?php echo $apply_before_tax; ?> id="checkboxes-1" value="yes" type="checkbox">
                  <?php _e('Check this box if the coupon should be applied before calculating cart tax.','dokan'); ?>
                </label>
                </div>
              </div>
            </div>


            <div class="form-group">
              <label class="col-md-3 control-label" for="checkboxes"><?php _e('Exclude Sale Items','dokan'); ?></label>
              <div class="col-md-9">
              <div class="checkbox">
                <label for="checkboxes-2">
                  <input name="exclude_sale_items" <?php echo $exclide_sale_item; ?> id="checkboxes-2" value="yes" type="checkbox">
                  <?php _e('Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are no sale items in the cart.','dokan'); ?>
                </label>
                </div>
              </div>
            </div>


            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-3 control-label" for="minium_ammount"><?php _e('Minimum Ammount','dokan'); ?></label>  
              <div class="col-md-5">
              <input id="minium_ammount" value="<?php echo $minimum_amount; ?>" name="minium_ammount" placeholder="Minimum Ammount" class="form-control input-md" type="text">
                
              </div>
            </div>


            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-3 control-label" for="product"><?php _e('Exclude products','dokan'); ?></label>
              <div class="col-md-5">
                <select id="coupon_exclude_categories" name="exclude_product_ids[]" class="form-control" multiple>
                    <?php
                    foreach($query->posts as $key=>$object) {
                        ?>
                        <option value="<?php echo $object->ID; ?>"><?php _e($object->post_title, 'dokan'); ?></option>

                        <?php
                        
                    }
                    ?>
                </select>
              </div>
            </div>

            <!-- submit -->
            <div class="form-group">
              <label class="col-md-3 control-label" for=""></label>
              <div class="col-md-4">
                <input type="submit" id="" name="coupon_creation" value="<?php _e('Create Coupon','dokan'); ?>" class="btn btn-primary">
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
?>
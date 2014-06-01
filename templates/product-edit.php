<?php

global $post, $product;

dokan_redirect_login();
dokan_redirect_if_not_seller();

$post_id = $post->ID;
$seller_id = get_current_user_id();

if ( isset( $_GET['product_id'] ) ) {
    $post_id = intval( $_GET['product_id'] );
    $post = get_post( $post_id );
}

// bail out if not author
if ( $post->post_author != $seller_id ) {
    wp_die( __( 'Access Denied', 'dokan' ) );
}

if ( isset( $_POST['update_product']) ) {
    $product_info = array(
        'ID' => $post_id,
        'post_title' => sanitize_text_field( $_POST['post_title'] ),
        'post_content' => $_POST['post_content'],
        'post_excerpt' => $_POST['post_excerpt'],
        'post_status' => isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'pending',
        'comment_status' => isset( $_POST['_enable_reviews'] ) ? 'open' : 'closed'
    );

    wp_update_post( $product_info );

    /** set product category * */
    wp_set_object_terms( $post_id, (int) $_POST['product_cat'], 'product_cat' );
    wp_set_object_terms( $post_id, 'simple', 'product_type' );

    dokan_process_product_meta( $post_id );

    /** set images **/
    $featured_image = absint( $_POST['feat_image_id'] );
    if ( $featured_image ) {
        set_post_thumbnail( $post_id, $featured_image );
    }

    $edit_url = dokan_edit_product_url( $post_id );
    wp_redirect( add_query_arg( array( 'message' => 'success' ), $edit_url ) );
}

$_regular_price = get_post_meta( $post_id, '_regular_price', true );
$_sale_price = get_post_meta( $post_id, '_sale_price', true );
$is_discount = !empty( $_sale_price ) ? true : false;
$_sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
$_sale_price_dates_to = get_post_meta( $post_id, '_sale_price_dates_to', true );

$_sale_price_dates_from = !empty( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : '';
$_sale_price_dates_to = !empty( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : '';
$show_schedule = false;

if ( !empty( $_sale_price_dates_from ) && !empty( $_sale_price_dates_to ) ) {
    $show_schedule = true;
}

$_featured = get_post_meta( $post_id, '_featured', true );
$_weight = get_post_meta( $post_id, '_weight', true );
$_length = get_post_meta( $post_id, '_length', true );
$_width = get_post_meta( $post_id, '_width', true );
$_height = get_post_meta( $post_id, '_height', true );
$_downloadable = get_post_meta( $post_id, '_downloadable', true );
$_stock_status = get_post_meta( $post_id, '_stock_status', true );
$_visibility = get_post_meta( $post_id, '_visibility', true );
$_enable_reviews = $post->comment_status;

get_header();

dokan_frontend_dashboard_scripts();
?>

<div id="primary" class="content-area col-md-12 col-sm-9">
    <div id="content" class="site-content" role="main">

        <div class="row">
            <?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

            <form class="form" role="form" method="post">

                <div class="product-edit-container">

                    <div class="col-md-7">

                        <?php if ( isset( $_GET['message'] ) && $_GET['message'] == 'success') { ?>
                            <div class="dokan-message">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong><?php _e( 'Success!', 'dokan' ); ?></strong> <?php _e( 'The product has been updated successfully.', 'dokan' ); ?>

                                <?php if ( $post->post_status == 'publish' ) { ?>
                                    <a href="<?php echo get_permalink( $post_id ); ?>" target="_blank"><?php _e( 'View Product &rarr;', 'dokan' ); ?></a>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <div class="tabbable"> <!-- Only required for left/right tabs -->

                            <ul class="nav nav-tabs">
                                <?php 
                                $terms = wp_get_object_terms( $post->ID, 'product_type' );
                                $product_type = sanitize_title( current( $terms )->name );
                                $shipping_class = ($product_type == 'simple' ) ? '' : 'dokan-hide'; 
                                $variations_class = ($product_type == 'simple' ) ? 'dokan-hide' : ''; 
                                $dokan_product_data_tabs = apply_filters( 'dokan_product_data_tabs', array(

                                    'edit' => array(
                                        'label'  => __( 'Edit', 'dokan' ),
                                        'target' => 'edit-product',
                                        'class'  => array( 'active' ),
                                    ),
                                    'options' => array(
                                        'label'  => __( 'Options', 'dokan' ),
                                        'target' => 'product-options',
                                        'class'  => array(),
                                    ),
                                    'inventory' => array(
                                        'label'  => __( 'Inventory', 'dokan' ),
                                        'target' => 'product-inventory',
                                        'class'  => array(),
                                    ),
                                    'shipping' => array(
                                        'label'  => __( 'Shipping', 'dokan' ),
                                        'target' => 'product-shipping',
                                        'class'  => array( 'show_if_simple', $shipping_class ),
                                    ),
                                    'attributes' => array(
                                        'label'  => __( 'Attributes', 'dokan' ),
                                        'target' => 'product-attributes',
                                        'class'  => array(),
                                    ),
                                    'variations' => array(
                                        'label'  => __( 'Variations', 'dokan' ),
                                        'target' => 'product-variations',
                                        'class'  => array( 'show_if_variable', $variations_class ),
                                    ),

                                ) );

                                foreach ( $dokan_product_data_tabs as $key => $tab ) { ?>

                                    <li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , $tab['class'] ); ?>">
                                        <a href="#<?php echo $tab['target']; ?>" data-toggle="tab"><?php echo esc_html( $tab['label'] ); ?></a>
                                    </li>

                                <?php
                                }

                                do_action( 'dokan_product_data_panel_tabs' );
                                ?>

                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="edit-product">

                                    <?php do_action( 'dokan_product_edit_before_main' ); ?>

                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="dokan-feat-image-upload">
                                                <?php
                                                $wrap_class = ' dokan-hide';
                                                $instruction_class = '';
                                                $feat_image_id = 0;
                                                if ( has_post_thumbnail( $post_id ) ) {
                                                    $wrap_class = '';
                                                    $instruction_class = ' dokan-hide';
                                                    $feat_image_id = get_post_thumbnail_id( $post_id );
                                                }
                                                ?>

                                                <div class="instruction-inside<?php echo $instruction_class; ?>">
                                                    <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="<?php echo $feat_image_id; ?>">

                                                    <i class="fa fa-cloud-upload"></i>
                                                    <a href="#" class="dokan-feat-image-btn btn btn-sm"><?php _e( 'Upload a product cover image', 'dokan' ); ?></a>
                                                </div>

                                                <div class="image-wrap<?php echo $wrap_class; ?>">
                                                    <a class="close dokan-remove-feat-image">&times;</a>
                                                    <?php if ( $feat_image_id ) { ?>
                                                        <?php echo get_the_post_thumbnail( $post_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array( 'height' => '', 'width' => '' ) ); ?>
                                                    <?php } else { ?>
                                                        <img height="" width="" src="" alt="">
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-7">

                                            <div class="form-group">
                                                <input type="hidden" name="dokan_product_id" value="<?php echo $post_id; ?>">
                                                <?php dokan_post_input_box( $post_id, 'post_title', array( 'placeholder' => 'Product name..', 'value' => $post->post_title ) ); ?>
                                            </div>

                                            <div class="row show_if_simple">
                                                <div class="form-group col-md-6">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                        <?php dokan_post_input_box( $post_id, '_regular_price', array( 'placeholder' => '9.99' ) ); ?>
                                                    </div>
                                                </div>


                                                <span class="pull-right">
                                                    <label>
                                                        <input type="checkbox" <?php checked( $is_discount, true ); ?> class="_discounted_price"> <?php _e( 'Discounted Price', 'dokan' ); ?>
                                                    </label>
                                                </span>
                                            </div>

                                            <div class="show_if_simple">
                                                <div class="special-price-container<?php echo $is_discount ? '' : ' dokan-hide'; ?>">
                                                    <div class="row form-group">
                                                        <div class="input-group col-md-6">
                                                            <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                            <?php dokan_post_input_box( $post_id, '_sale_price', array( 'placeholder' => __( 'Special Price', 'dokan' ) ) ); ?>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <a href="#" class="sale-schedule pull-right"><?php _e( 'Schedule', 'dokan' ); ?></a>
                                                        </div>
                                                    </div>

                                                    <div class="row sale-schedule-container<?php echo $show_schedule ? '' : ' dokan-hide'; ?>">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon"><?php _e( 'From', 'dokan' ); ?></span>
                                                                    <input type="text" name="_sale_price_dates_from" class="form-control datepicker" value="<?php echo esc_attr( $_sale_price_dates_from ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="YYYY-MM-DD">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon"><?php _e( 'To', 'dokan' ); ?></span>
                                                                    <input type="text" name="_sale_price_dates_to" class="form-control datepicker" value="<?php echo esc_attr( $_sale_price_dates_to ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="YYYY-MM-DD">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- .show_if_simple -->



                                            <div class="form-group">
                                                <?php dokan_post_input_box( $post_id, 'post_excerpt', array( 'placeholder' => 'Short description about the product...', 'value' => $post->post_excerpt ), 'textarea' ); ?>
                                            </div>

                                            <div class="form-group">
                                                <?php
                                                $product_cat = -1;
                                                $term = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids') );
                                                if ( $term ) {
                                                    $product_cat = reset( $term );
                                                }

                                                wp_dropdown_categories( array(
                                                    'show_option_none' => __( '- Select a category -', 'dokan' ),
                                                    'hierarchical' => 1,
                                                    'hide_empty' => 0,
                                                    'name' => 'product_cat',
                                                    'id' => 'product_cat',
                                                    'taxonomy' => 'product_cat',
                                                    'title_li' => '',
                                                    'class' => 'product_cat form-control chosen',
                                                    'exclude' => '',
                                                    'selected' => $product_cat,
                                                ) );
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php wp_editor( esc_textarea( $post->post_content ), 'post_content', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_content') ); ?>
                                        </div>
                                    </div>

                                    <?php do_action( 'dokan_product_edit_after_main' ); ?>

                                </div> <!-- #edit-product -->

                                <div class="tab-pane fade" id="product-options">

                                    <?php include_once dirname(__FILE__) . '/edit/options.php'; ?>

                                    <?php do_action( 'dokan_product_edit_after_options' ); ?>

                                </div> <!-- #product-options -->

                                <div class="tab-pane fade" id="product-inventory">

                                    <?php include_once dirname(__FILE__) . '/edit/inventory.php'; ?>

                                    <?php do_action( 'dokan_product_edit_after_inventory' ); ?>

                                </div> <!-- #product-inventory -->

                                <div class="tab-pane fade" id="product-shipping">
                                    <?php include_once dirname(__FILE__) . '/edit/shipping.php'; ?>

                                    <?php do_action( 'dokan_product_edit_after_shipping' ); ?>
                                </div>

                                <!-- ===== Attributes ===== -->

                                <div class="tab-pane fade show_if_simple" id="product-attributes">

                                    <?php include_once dirname(__FILE__) . '/edit/attributes.php'; ?>
                                    <?php include_once dirname(__FILE__) . '/edit/templates-js.php'; ?>

                                    <?php do_action( 'dokan_product_edit_after_attributes' ); ?>

                                </div> <!-- #product-attributes -->

                                <div class="tab-pane fade show_if_variable" id="product-variations">

                                    <?php dokan_variable_product_type_options(); ?>

                                    <?php do_action( 'dokan_product_edit_after_variations' ); ?>

                                </div> <!-- #product-variations -->

                                <?php do_action( 'dokan_product_tab_content', $post, $seller_id ); ?>

                            </div> <!-- .tab-content -->
                        </div> <!-- .tabbable -->

                    </div> <!-- .col-md-7 -->

                    <!-- #################### Sidebar ######################### -->

                    <div class="col-md-3 dokan-edit-sidebar">

                        <?php include_once dirname(__FILE__) . '/edit/sidebar.php'; ?>

                    </div> <!-- .dokan-edit-sidebar -->
                </div> <!-- .product-edit-container -->
            </form>
        </div> <!-- .row -->

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php get_footer(); ?>
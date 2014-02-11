<?php
/**
 * Template Name: New Product
 */

dokan_redirect_login();
dokan_redirect_if_not_seller();

$errors = array();
$product_cat = -1;
$post_content = __( 'Details about your product...', 'dokan' );

if ( isset( $_POST['add_product'] ) ) {
    $post_title = trim( $_POST['post_title'] );
    $post_content = trim( $_POST['post_content'] );
    $post_excerpt = trim( $_POST['post_excerpt'] );
    $price = floatval( $_POST['price'] );
    $product_cat = intval( $_POST['product_cat'] );
    $featured_image = absint( $_POST['feat_image_id'] );

    if ( empty( $post_title ) ) {
        $errors[] = __( 'Please enter product title', 'dokan' );
    }

    if ( !$price ) {
        $errors[] = __( 'Please enter product price', 'dokan' );
    }

    if ( $product_cat < 0 ) {
        $errors[] = __( 'Please select a category', 'dokan' );
    }

    if ( !$errors ) {

        $post_data = array(
            'post_type' => 'product',
            'post_status' => dokan_get_new_post_status(),
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_excerpt' => $post_excerpt,
        );

        // var_dump( $post_data, $_POST );

        $product_id = wp_insert_post( $post_data );

        if ( $product_id ) {

            /** set images **/
            if ( $featured_image ) {
                set_post_thumbnail( $product_id, $featured_image );
            }

            /** set product category * */
            wp_set_object_terms( $product_id, (int) $_POST['product_cat'], 'product_cat' );
            wp_set_object_terms( $product_id, 'simple', 'product_type' );

            update_post_meta( $product_id, '_regular_price', $price );
            update_post_meta( $product_id, '_sale_price', '' );
            update_post_meta( $product_id, '_price', $price );
            update_post_meta( $product_id, '_visibility', 'hidden' );

            wp_redirect( dokan_edit_product_url( $product_id ) );
        }
    }
}

dokan_product_editor_scripts();

get_header();
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content" role="main">

        <?php if ( $errors ) { ?>
            <div class="alert alert-danger">
                <a class="close" data-dismiss="alert">&times;</a>

                <?php foreach ($errors as $error) { ?>

                    <strong>Error!</strong> <?php echo $error ?>.<br>

                <?php } ?>
            </div>
        <?php } ?>

        <?php if ( dokan_is_seller_enabled( get_current_user_id() ) ) { ?>

        <form class="form" method="post">

            <div class="row product-edit-container">
                <div class="col-md-4">
                    <div class="dokan-feat-image-upload">
                        <div class="instruction-inside">
                            <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="0">
                            <i class="fa fa-cloud-upload"></i>
                            <a href="#" class="dokan-feat-image-btn btn btn-sm"><?php _e( 'Upload a product cover image', 'dokan' ); ?></a>
                        </div>

                        <div class="image-wrap dokan-hide">
                            <a class="close dokan-remove-feat-image">&times;</a>
                                <img src="" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input class="form-control" name="post_title" id="post-title" type="text" placeholder="Product name.." value="<?php echo dokan_posted_input( 'post_title' ); ?>">
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                            <input class="form-control" name="price" id="product-price" type="text" placeholder="9.99" value="<?php echo dokan_posted_input( 'price' ); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <textarea name="post_excerpt" id="post-excerpt" rows="5" class="form-control" placeholder="Short description about the product..."><?php echo dokan_posted_textarea( 'post_excerpt' ); ?></textarea>
                    </div>

                    <div class="form-group">
                    <?php
                    wp_dropdown_categories( array(
                        'show_option_none' => __( '- Select a category -', 'dokan' ),
                        'hierarchical' => 1,
                        'hide_empty' => 0,
                        'name' => 'product_cat',
                        'id' => 'product_cat',
                        'taxonomy' => 'product_cat',
                        'title_li' => '',
                        'class' => 'product_cat form-control',
                        'exclude' => '',
                        'selected' => $product_cat,
                    ) );
                    ?>
                    </div>
                </div>
            </div>

            <!-- <textarea name="post_content" id="" cols="30" rows="10" class="span7" placeholder="Describe your product..."><?php echo dokan_posted_textarea( 'post_content' ); ?></textarea> -->
            <div class="form-group">
                <?php wp_editor( $post_content, 'post_content', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_content') ); ?>
            </div>


            <div class="form-group">
                <input type="submit" name="add_product" class="btn btn-primary" value="Add Product"/>
            </div>

        </form>

        <?php } else { ?>

            <?php dokan_seller_not_enabled_notice(); ?>

        <?php } ?>
    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
<?php
get_header();

global $post, $product;

$woo_product = get_product( $post->ID );
// var_dump( $woo_product );

$post_id = $post->ID;
$product_cat = -1;
$post_content = __( 'Details about your product...', 'dokan' );
$_regular_price = get_post_meta( $post->ID, '_regular_price', true );
$_sale_price = get_post_meta( $post->ID, '_sale_price', true );
$is_discount = !empty( $_sale_price ) ? true : false;
$_sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
$_sale_price_dates_to = get_post_meta( $post_id, '_sale_price_dates_to', true );

$_sale_price_dates_from = !empty( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : '';
$_sale_price_dates_to = !empty( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : '';
$show_schedule = false;

if ( !empty( $_sale_price_dates_from ) && !empty( $_sale_price_dates_to ) ) {
    $show_schedule = true;
}

$_purchase_note = get_post_meta( $post_id, '_purchase_note', true );
$_manage_stock = get_post_meta( $post_id, '_manage_stock', true );
$_backorders = get_post_meta( $post_id, '_backorders', true );
$_stock = get_post_meta( $post_id, '_stock', true );
$_sku = get_post_meta( $post_id, '_sku', true );
$_featured = get_post_meta( $post_id, '_featured', true );
$_weight = get_post_meta( $post_id, '_weight', true );
$_length = get_post_meta( $post_id, '_length', true );
$_width = get_post_meta( $post_id, '_width', true );
$_height = get_post_meta( $post_id, '_height', true );
$_downloadable = get_post_meta( $post_id, '_downloadable', true );
$_stock_status = get_post_meta( $post_id, '_stock_status', true );
$_visibility = get_post_meta( $post_id, '_visibility', true );


$term = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'ids') );
if ( $term ) {
    $product_cat = reset( $term );
}
?>

<div id="primary" class="content-area col-md-12">
    <div id="content" class="site-content" role="main">

        <?php
        if ( isset( $_POST['update_product']) ) {
            var_dump( $_POST );

            $product_meta = array(
                '_visibility',
                '_stock_status',
                '_downloadable',
                '_virtual',
                '_product_image_gallery',
                '_regular_price',
                '_sale_price',
                '_purchase_note',
                '_featured',
                '_weight',
                '_length',
                '_width',
                '_height',
                '_sku',
                '_product_attributes',
                '_sale_price_dates_from',
                '_sale_price_dates_to',
                '_price',
                '_sold_individually',
                '_stock',
                '_backorders',
                '_manage_stock'
            );

            foreach ($product_meta as $meta_key) {
                if ( isset( $_POST[$meta_key] ) ) {
                    echo $meta_key . ': ' . $_POST[$meta_key] . '<br>';
                }
            }
        }
        ?>

        <div class="row">
            <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

            <form class="form" role="form" method="post">

                <div class="product-edit-container">
                    <div class="col-md-7">

                        <div class="tabbable"> <!-- Only required for left/right tabs -->

                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#edit-product" data-toggle="tab">Edit</a>
                                </li>
                                <li><a href="#product-options" data-toggle="tab">Options</a></li>
                                <li><a href="#product-inventory" data-toggle="tab">Inventory</a></li>
                                <li class="actives"><a href="#product-attributes" data-toggle="tab">Attributes</a></li>
                                <li class="show_if_variable"><a href="#product-variations" data-toggle="tab">Variations</a></li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="edit-product">



                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="dokan-feat-image-upload">
                                                    <?php
                                                    $wrap_class = ' dokan-hide';
                                                    $instruction_class = '';
                                                    $feat_image_id = 0;
                                                    if ( has_post_thumbnail( $post->ID ) ) {
                                                        $wrap_class = '';
                                                        $instruction_class = ' dokan-hide';
                                                        $feat_image_id = get_post_thumbnail_id( $post->ID );
                                                    }
                                                    ?>

                                                    <div class="instruction-inside<?php echo $instruction_class; ?>">
                                                        <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="<?php echo $feat_image_id; ?>">
                                                        <a href="#" class="dokan-feat-image-btn">Upload a product cover image</a>
                                                    </div>
                                                    <div class="image-wrap<?php echo $wrap_class; ?>">
                                                        <a class="close dokan-remove-feat-image">&times;</a>
                                                        <?php if ( $feat_image_id ) { ?>
                                                            <?php echo get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) ); ?>
                                                        <?php } else { ?>
                                                            <img src="" alt="">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-7">

                                                <div class="form-group">
                                                    <input type="hidden" name="dokan_product_id" value="<?php echo $post->ID; ?>">
                                                    <input class="form-control" name="post_title" id="post-title" type="text" placeholder="Product name.." value="<?php echo esc_attr( $post->post_title ); ?>">
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                            <input class="form-control" name="_regular_price" id="product-price" type="text" placeholder="9.99" value="<?php echo esc_attr( $_regular_price ); ?>">
                                                        </div>
                                                    </div>


                                                    <span class="pull-right">
                                                        <label>
                                                            <input type="checkbox" <?php checked( $is_discount, true ); ?> class="_discounted_price"> Discounted Price
                                                        </label>
                                                    </span>
                                                </div>

                                                <div class="special-price-container<?php echo $is_discount ? '' : ' dokan-hide'; ?>">
                                                    <div class="row form-group">
                                                        <div class="input-group col-md-6">
                                                            <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                            <input class="form-control" name="_sale_price" id="product-price" type="text" placeholder="<?php esc_attr_e( 'Special price', 'dokan' ); ?>" value="<?php echo esc_attr( $_sale_price ); ?>">
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



                                                <div class="form-group">
                                                    <textarea name="post_excerpt" id="post-excerpt" rows="5" class="form-control" placeholder="Short description about the product..."><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
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

                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php wp_editor( esc_textarea( $post->post_content ), 'post_content', array('editor_height' => 50, 'quicktags' => false, 'media_buttons' => false, 'teeny' => true, 'editor_class' => 'post_content') ); ?>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div class="tab-pane" id="product-options">
                                    <ul class="list-unstyled  label-on-left">
                                        <li>
                                            <label>Purchase Note</label>
                                            <textarea name="_purchase_note" id="" cols="30" rows="3"><?php echo esc_textarea( $_purchase_note ); ?></textarea>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="hidden" name="_enable_reviews" value="false">
                                                <input type="checkbox" name="_enable_reviews" value="true">
                                                Enable Reviews
                                            </label>

                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-pane" id="product-inventory">
                                    <ul class="list-unstyled  label-on-left">
                                        <li>
                                            <label for="_sku">SKU</label>
                                            <input type="text" name="_sku" value="<?php echo esc_attr( $_sku ); ?>">
                                        </li>
                                        <li>
                                            <label>Manage Stock?</label>
                                            <input type="checkbox" name="_manage_stock" <?php checked( $_manage_stock, 'yes' ); ?> value="yes">
                                            <span class="help">Enable stock management at product level</span>
                                        </li>
                                        <li>
                                            <label for="_stock"><?php _e( 'Stock Qty', 'dokan' ); ?></label>
                                            <input type="text" name="_stock" value="<?php echo esc_attr( $_stock ); ?>">
                                        </li>
                                        <li>
                                            <label for="_stock_status"><?php _e( 'Stock Status', 'dokan' ); ?></label>
                                            <select name="_stock_status" id="">
                                                <option value="instock"<?php selected( $_stock_status, 'instock' ); ?>><?php _e( 'In Stock', 'dokan' ); ?></option>
                                                <option value="outstock"<?php selected( $_stock_status, 'outstock' ); ?>><?php _e( 'Out of Stock', 'dokan' ); ?></option>
                                            </select>
                                        </li>
                                        <li>
                                            <label for="_sku"><?php _e( 'Allow Backorders?', 'dokan' ); ?></label>
                                            <select name="_backorders" id="">
                                                <option value="no"<?php selected( $_backorders, 'no' ); ?>><?php _e( 'Do not allow', 'dokan' ); ?></option>
                                                <option value="notify"<?php selected( $_backorders, 'notify' ); ?>><?php _e( 'Allow but notify customer', 'dokan' ); ?></option>
                                                <option value="yes"<?php selected( $_backorders, 'yes' ); ?>><?php _e( 'Allow', 'dokan' ); ?></option>
                                            </select>
                                        </li>
                                    </ul>
                                </div>

                                <!-- ===== Attributes ===== -->

                                <div class="tab-pane actives" id="product-attributes">
                                    <h4>Attributes <small>Different types of this product (e.g. size, color)</small></h4>

                                    <div id="variants-holder" class="woocommerce_attributes">

                                        <?php
                                        $thepostid = $post->ID;
                                        global $woocommerce;

                                        // Array of defined attribute taxonomies
                                        $attribute_taxonomies = $woocommerce->get_attribute_taxonomies();

                                        // Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set
                                        $attributes = maybe_unserialize( get_post_meta( $thepostid, '_product_attributes', true ) );

                                        $i = -1;

                                        // var_dump($attributes, $attribute_taxonomies);

                                        // Custom Attributes
                                        if ( ! empty( $attributes ) ) {
                                            foreach ( $attributes as $attribute ) {
                                                // var_dump($attribute);

                                                if ( $attribute['is_taxonomy'] ) {
                                                    $tax = get_taxonomy( $attribute['name'] );

                                                    $attribute_name = $tax->labels->name;
                                                    $options = wp_get_post_terms( $thepostid, $attribute['name'], array('fields' => 'names') );
                                                } else {
                                                    $attribute_name = $attribute['name'];
                                                    $options = array_map( 'trim', explode('|', $attribute['value'] ) );
                                                }

                                                $i++;

                                                // var_dump($i);

                                                $position = empty( $attribute['position'] ) ? 0 : absint( $attribute['position'] );
                                                ?>

                                                <div class="inputs-box woocommerce_attribute" data-count="<?php echo $i; ?>">

                                                    <div class="box-header">
                                                        <?php if ( $attribute['is_taxonomy'] ) { ?>

                                                            <?php echo $attribute_name; ?>

                                                            <input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>">
                                                            <input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1">

                                                        <?php } else { ?>

                                                            <input type="text" class="category-name" placeholder="Category name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute_name ); ?>">
                                                            <input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0">

                                                        <?php } ?>

                                                        <input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />


                                                        <span class="actions">
                                                            <button class="row-remove button pull-right">Remove</button>
                                                        </span>
                                                    </div>

                                                    <div class="box-inside clearfix">

                                                        <div class="attribute-config">
                                                            <ul class="list-unstyled ">
                                                                <li>
                                                                    <label>
                                                                        <input type="checkbox" class="checkbox" <?php
                                                                        $tax = '';
                                                                        // $i = 1;
                                                                        if ( isset( $attribute['is_visible'] ) )
                                                                            checked( $attribute['is_visible'], 1 );
                                                                        else
                                                                            checked( apply_filters( 'default_attribute_visibility', false, $tax ), true );

                                                                        ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?>
                                                                    </label>
                                                                </li>

                                                                <li class="enable_variation show_if_variable">
                                                                    <label><input type="checkbox" class="checkbox" <?php

                                                                    if ( isset( $attribute['is_variation'] ) )
                                                                        checked( $attribute['is_variation'], 1 );
                                                                    else
                                                                        checked( apply_filters( 'default_attribute_variation', false, $tax ), true );

                                                                ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?></label>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <div class="attribute-options">
                                                            <ul class="option-couplet list-unstyled ">
                                                                <?php

                                                                if ($options) {
                                                                    foreach ($options as $count => $option) {
                                                                        ?>

                                                                        <li>
                                                                            <input type="text" class="option" placeholder="Option..." name="attribute_values[<?php echo $i; ?>][<?php echo $count; ?>]" value="<?php echo esc_attr( $option ); ?>">

                                                                            <span class="item-action actions">
                                                                                <a href="#" class="row-add">+</a>
                                                                                <a href="#" class="row-remove">-</a>
                                                                            </span>
                                                                        </li>

                                                                        <?php
                                                                    }
                                                                } else {
                                                                    ?>

                                                                    <li>
                                                                        <input type="text" class="option" name="attribute_values[<?php echo $i; ?>][0]" placeholder="Option...">

                                                                        <span class="item-action actions">
                                                                            <a href="#" class="row-add">+</a>
                                                                            <a href="#" class="row-remove">-</a>
                                                                        </span>
                                                                    </li>

                                                                    <?php
                                                                }
                                                                ?>
                                                            </ul>

                                                        </div> <!-- .attribute-options -->

                                                    </div> <!-- .box-inside -->

                                                </div> <!-- .input-box -->
                                            <?php } ?>
                                        <?php } ?>

                                    </div> <!-- #variants-holder -->

                                    <p>

                                    </p>

                                    <p class="toolbar">
                                        <button class="btn btn-success add-variant-category">+ Add a category</button>
                                        <button type="button" class="btn save_attributes"><?php _e( 'Save attributes', 'woocommerce' ); ?></button>
                                    </p>

                                    <?php //include_once dirname( __DIR__ ) . '/lib/edit-panel/attributes.php'; ?>

                                    <!-- #################### JS Template ######################### -->

                                    <script type="text/html" id="tmpl-sc-category">
                                        <div class="inputs-box woocommerce_attribute" data-count="[<%= row %>]">

                                            <div class="box-header">
                                                <input type="text" class="category-name" placeholder="Category name" name="attribute_names[<%= row %>]" value="">

                                                <input type="hidden" name="attribute_is_taxonomy[<%= row %>]" value="0">
                                                <input type="hidden" name="attribute_position[<%= row %>]]" class="attribute_position" value="<%= row %>" />

                                                <span class="actions">
                                                    <button class="row-remove button pull-right">Remove</button>
                                                </span>
                                            </div>

                                            <div class="box-inside clearfix">

                                                <div class="attribute-config">
                                                    <ul class="list-unstyled ">
                                                        <li>
                                                            <label>
                                                                <input type="checkbox" class="checkbox" name="attribute_visibility[<%= row %>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?>
                                                            </label>
                                                        </li>

                                                        <li class="enable_variation show_if_variable">
                                                            <label>
                                                                <input type="checkbox" class="checkbox" name="attribute_variation[<%= row %>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="attribute-options">
                                                    <ul class="option-couplet list-unstyled ">
                                                        <li>
                                                            <input type="text" class="option" placeholder="Option..." name="attribute_values[<%= row %>][0]">

                                                            <span class="item-action actions">
                                                                <a href="#" class="row-add">+</a>
                                                                <a href="#" class="row-remove">-</a>
                                                            </span>
                                                        </li>
                                                    </ul>

                                                </div> <!-- .attribute-options -->

                                            </div> <!-- .box-inside -->

                                        </div> <!-- .inputs-box -->
                                    </script>

                                    <script type="text/html" id="tmpl-sc-category-item">
                                        <li>
                                            <input type="text" class="option" placeholder="Option..." name="attribute_values[<%= row %>][<%= col %>]">

                                            <span class="actions item-action">
                                                <a href="#" class="row-add">+</a>
                                                <a href="#" class="row-remove">-</a>
                                            </span>
                                        </li>
                                    </script>




                                    <!-- #################### JS Template ######################### -->


                                </div> <!-- #product-attributes -->

                                <div class="tab-pane show_if_variable" id="product-variations">

                                    <?php
                                    dokan_variable_product_type_options();
                                    ?>
                                </div>
                            </div> <!-- .tab-content -->
                        </div> <!-- .tabbable -->

                    </div> <!-- .col-md-7 -->

                    <!-- #################### Sidebar ######################### -->

                    <div class="col-md-3 dokan-edit-sidebar">

                        <div class="form-group">
                            <input type="submit" name="update_product" class="btn btn-primary" value="Update Product"/>
                        </div>

                        <aside class="product-type">
                            <div class="dokan-side-head">
                                <span class="title">Product Type</span>

                                <?php
                                $supported_types = array( 'simple', 'variable' );
                                if ( $terms = wp_get_object_terms( $post->ID, 'product_type' ) ) {
                                    $product_type = sanitize_title( current( $terms )->name );
                                } else {
                                    $product_type = 'simple';
                                }

                                if ( !in_array( $product_type, $supported_types ) ) {
                                    $product_type = 'simple';
                                }


                                ?>
                                <select name="_product_type" id="_product_type" class="pull-right">
                                    <option value="simple" <?php selected( $product_type, 'simple' ); ?>><?php _e( 'Simple product', 'woocommerce' ); ?></option>
                                    <option value="variable" <?php selected( $product_type, 'variable' ); ?>><?php _e( 'Variable product', 'woocommerce' ); ?></option>
                                </select>
                            </div>

                        </aside> <!-- .product-type -->

                        <aside class="downloadable">
                            <div class="dokan-side-head">
                                <label>
                                    <span class="title"><?php _e( 'Downloadable Product', 'dokan' ); ?></span>

                                    <span class="checkbox pull-right">
                                        <input type="checkbox" id="_downloadable" name="_downloadable" value="yes"<?php checked( $_downloadable, 'yes' ); ?>>
                                    </span>
                                </label>
                            </div> <!-- .dokan-side-head -->

                            <div class="dokan-side-body dokan-hide">
                                <ul class="list-unstyled ">
                                    <li>
                                        <a href="#">Upload File</a><br>
                                        <textarea name="_files" id="" cols="30" rows="3"></textarea>
                                    </li>
                                    <li>
                                        <label>Limit</label>
                                        <input type="text" name="_limit" value="">
                                    </li>
                                    <li>
                                        <label>Expiry</label>
                                        <input type="text" name="_expiry" value="">
                                    </li>
                                </ul>
                            </div> <!-- .dokan-side-body -->
                        </aside> <!-- .downloadable -->

                        <aside class="product-gallery">
                            <div class="dokan-side-head">
                                Image Gallery
                            </div>

                            <div class="dokan-side-body" id="dokan-product-images">
                                <div id="product_images_container">
                                    <ul class="product_images clearfix"></ul>

                                    <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="">
                                </div>

                                <a href="#" class="add-product-images btn btn-success">+ Add product images</a>
                            </div>
                        </aside> <!-- .product-gallery -->

                    </div> <!-- .dokan-edit-sidebar -->
                </div> <!-- .product-edit-container -->
            </form>
        </div> <!-- .row -->

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php get_footer(); ?>
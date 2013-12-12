<div class="form-horizontal">
    <input type="hidden" name="product_shipping_class" value="0">


    <div class="form-group show_if_simple">
        <label class="col-md-4 control-label" for="_backorders"><?php _e( 'Weight (kg)', 'dokan' ); ?></label>
        <div class="col-md-4">
            <?php dokan_post_input_box( $post->ID, '_weight' ); ?>
        </div>
    </div>

    <div class="form-group show_if_simple">
        <label class="col-md-4 control-label" for="_backorders"><?php _e( 'Dimensions (cm)', 'dokan' ); ?></label>
        <div class="col-md-8 product-dimension">
            <?php dokan_post_input_box( $post->ID, '_length', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'length', 'dokan' ) ), 'number' ); ?>
            <?php dokan_post_input_box( $post->ID, '_width', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'width', 'dokan' ) ), 'number' ); ?>
            <?php dokan_post_input_box( $post->ID, '_height', array( 'class' => 'form-control col-sm-1', 'placeholder' => __( 'height', 'dokan' ) ), 'number' ); ?>
        </div>
    </div>

</div>


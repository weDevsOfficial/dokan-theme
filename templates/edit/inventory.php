<div class="form-horizontal">
    <div class="form-group">
        <label class="col-md-4 control-label" for="_sku"><?php _e( 'SKU', 'dokan' ); ?></label>
        <div class="col-md-4">
            <?php dokan_post_input_box( $post->ID, '_sku', array( 'placeholder' => 'SKU' ) ); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label" for=""><?php _e( 'Manage Stock?', 'dokan' ); ?></label>
        <div class="col-md-6">
            <?php dokan_post_input_box( $post->ID, '_manage_stock', array('label' => __( 'Enable stock management at product level', 'dokan' ) ), 'checkbox' ); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label" for="_stock_qty"><?php _e( 'Stock Qty', 'dokan' ); ?></label>
        <div class="col-md-4">
            <?php dokan_post_input_box( $post->ID, '_stock', array( 'placeholder' => '10' ) ); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label" for="_stock_status"><?php _e( 'Stock Status', 'dokan' ); ?></label>
        <div class="col-md-4">
            <?php dokan_post_input_box( $post->ID, '_stock_status', array( 'options' => array(
                'instock' => __( 'In Stock', 'dokan' ),
                'outofstock' => __( 'Out of Stock', 'dokan' )
                ) ), 'select'
            ); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label" for="_backorders"><?php _e( 'Allow Backorders', 'dokan' ); ?></label>
        <div class="col-md-4">
            <?php dokan_post_input_box( $post->ID, '_backorders', array( 'options' => array(
                'no' => __( 'Do not allow', 'dokan' ),
                'notify' => __( 'Allow but notify customer', 'dokan' ),
                'yes' => __( 'Allow', 'dokan' )
                ) ), 'select'
            ); ?>
        </div>
    </div>
</div> <!-- .form-horizontal -->
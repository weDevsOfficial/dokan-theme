<h4><?php _e( 'Attributes', 'dokan' ); ?> <small><?php _e( 'Different types of this product (e.g. size, color)', 'dokan' ); ?></small></h4>

<div id="variants-holder" class="woocommerce_attributes">

    <?php
    $thepostid = $post->ID;
    global $woocommerce;

    // Array of defined attribute taxonomies
    $attribute_taxonomies = wc_get_attribute_taxonomies();

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
                        <button class="row-remove btn pull-right btn-danger btn-sm"><?php _e( 'Remove', 'dokan' ); ?></button>
                    </span>
                </div>

                <div class="box-inside clearfix">

                    <div class="attribute-config">
                        <ul class="list-unstyled ">
                            <li>
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="checkbox" <?php
                                    $tax = '';
                                    // $i = 1;
                                    if ( isset( $attribute['is_visible'] ) )
                                        checked( $attribute['is_visible'], 1 );
                                    else
                                        checked( apply_filters( 'default_attribute_visibility', false, $tax ), true );

                                    ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'dokan' ); ?>
                                </label>
                            </li>

                            <li class="enable_variation show_if_variable">
                                <label class="checkbox-inline">
                                <input type="checkbox" class="checkbox" <?php

                                if ( isset( $attribute['is_variation'] ) )
                                    checked( $attribute['is_variation'], 1 );
                                else
                                    checked( apply_filters( 'default_attribute_variation', false, $tax ), true );

                            ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'dokan' ); ?></label>
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
                                        <input type="text" class="option" placeholder="<?php _e( 'Option...', 'dokan' ); ?>" name="attribute_values[<?php echo $i; ?>][<?php echo $count; ?>]" value="<?php echo esc_attr( $option ); ?>">

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
                                    <input type="text" class="option" name="attribute_values[<?php echo $i; ?>][0]" placeholder="<?php _e( 'Option...', 'dokan' ); ?>">

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

<p class="toolbar">
    <button class="btn btn-success add-variant-category"><?php _e( '+ Add a category', 'dokan' ); ?></button>
    <button type="button" class="btn btn-default save_attributes" data-id="<?php echo $thepostid; ?>"><?php _e( 'Save attributes', 'dokan' ); ?></button>
</p>
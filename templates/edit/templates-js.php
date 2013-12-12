<script type="text/html" id="tmpl-sc-category">
    <div class="inputs-box woocommerce_attribute" data-count="[<%= row %>]">

        <div class="box-header">
            <input type="text" class="category-name" placeholder="Category name" name="attribute_names[<%= row %>]" value="">

            <input type="hidden" name="attribute_is_taxonomy[<%= row %>]" value="0">
            <input type="hidden" name="attribute_position[<%= row %>]]" class="attribute_position" value="<%= row %>" />

            <span class="actions">
                <button class="row-remove btn pull-right btn-danger btn-sm">Remove</button>
            </span>
        </div>

        <div class="box-inside clearfix">

            <div class="attribute-config">
                <ul class="list-unstyled ">
                    <li>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="checkbox" name="attribute_visibility[<%= row %>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?>
                        </label>
                    </li>

                    <li class="enable_variation show_if_variable">
                        <label class="checkbox-inline">
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
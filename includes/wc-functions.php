<?php
/**
 * Output Product search forms.
 *
 * @access public
 * @subpackage  Forms
 * @param bool $echo (default: true)
 * @return string
 * @todo This function needs to be broken up in smaller pieces
 */
function get_product_search_form( $echo = true  ) {
    do_action( 'get_product_search_form'  );

    $search_form_template = locate_template( 'product-searchform.php' );
    if ( '' != $search_form_template  ) {
        require $search_form_template;
        return;
    }

    $form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
        <div class="input-group">
            <input type="text" class="form-control" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( 'Search for products', 'dokan' ) . '" />

            <span class="input-group-btn">
                <button type="submit" id="searchsubmit" class="btn btn-primary">'. esc_attr__( 'Search', 'dokan' ) .'</button>
                <input type="hidden" name="post_type" value="product" />
            </span>
        </div>
    </form>';

    if ( $echo  )
        echo apply_filters( 'get_product_search_form', $form );
    else
        return apply_filters( 'get_product_search_form', $form );
}
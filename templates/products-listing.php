<?php

dokan_delete_product_handler();
get_header();
?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

<div id="primary" class="content-area col-md-10 col-sm-9">
    <div id="content" class="site-content" role="main">

        <?php do_action( 'dokan_before_listing_product' ); ?>

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="product-listing-top clearfix">
                    <?php dokan_product_listing_status_filter(); ?>

                    <span class="col-md-3">
                        <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-large btn-theme pull-right"><i class="fa fa-briefcase">&nbsp;</i> <?php _e( 'Add new product', 'dokan' ); ?></a>
                    </span>
                </div>

                <?php dokan_product_dashboard_errors(); ?>

                <table class="table table-striped product-listing-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Image', 'dokan' ); ?></th>
                            <th><?php _e( 'Name', 'dokan' ); ?></th>
                            <th><?php _e( 'Status', 'dokan' ); ?></th>
                            <th><?php _e( 'SKU', 'dokan' ); ?></th>
                            <th><?php _e( 'Stock', 'dokan' ); ?></th>
                            <th><?php _e( 'Price', 'dokan' ); ?></th>
                            <th><?php _e( 'Type', 'dokan' ); ?></th>
                            <th><?php _e( 'Views', 'dokan' ); ?></th>
                            <th><?php _e( 'Date', 'dokan' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
                        $post_statuses = array('publish', 'draft', 'pending');
                        $args = array(
                            'post_type' => 'product',
                            'post_status' => $post_statuses,
                            'posts_per_page' => 10,
                            'author' => get_current_user_id(),
                            'orderby' => 'post_date',
                            'order' => 'DESC',
                            'paged' => $paged
                        );

                        if ( isset( $_GET['post_status']) && in_array( $_GET['post_status'], $post_statuses ) ) {
                            $args['post_status'] = $_GET['post_status'];
                        }

                        $original_post = $post;
                        $product_query = new WP_Query( apply_filters( 'dokan_product_listing_query', $args ) );

                        if ( $product_query->have_posts() ) {
                            while ($product_query->have_posts()) {
                                $product_query->the_post();

                                $tr_class = ($post->post_status == 'pending' ) ? ' class="danger"' : '';
                                $product = get_product( $post->ID );
                                ?>
                                <tr<?php echo $tr_class; ?>>
                                    <td>
                                        <a href="<?php echo dokan_edit_product_url( $post->ID ); ?>"><?php echo $product->get_image(); ?></a>
                                    </td>
                                    <td>
                                        <p><a href="<?php echo dokan_edit_product_url( $post->ID ); ?>"><?php echo $product->get_title(); ?></a></p>

                                        <div class="row-actions">
                                            <span class="edit"><a href="<?php echo dokan_edit_product_url( $post->ID ); ?>"><?php _e( 'Edit', 'dokan' ); ?></a> | </span>
                                            <span class="delete"><a onclick="return confirm('Are you sure?');" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'dokan-delete-product', 'product_id' => $post->ID ), get_permalink( $original_post->ID ) ), 'dokan-delete-product' ); ?>"><?php _e( 'Delete Permanently', 'dokan' ); ?></a> | </span>
                                            <span class="view"><a href="<?php echo get_permalink( $post->ID ); ?>" rel="permalink"><?php _e( 'View', 'dokan' ); ?></a></span>
                                        </div>
                                    </td>
                                    <td class="post-status">
                                        <label class="label <?php echo $post->post_status; ?>"><?php echo dokan_get_post_status( $post->post_status ); ?></label>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $product->get_sku() ) {
                                            echo $product->get_sku();
                                        } else {
                                            echo '<span class="na">&ndash;</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $product->is_in_stock() ) {
                                            echo '<mark class="instock">' . __( 'In stock', 'woocommerce' ) . '</mark>';
                                        } else {
                                            echo '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
                                        }

                                        if ( $product->managing_stock() ) :
                                            echo ' &times; ' . $product->get_total_stock();
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $product->get_price_html() ) {
                                            echo $product->get_price_html();
                                        } else {
                                            echo '<span class="na">&ndash;</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if( $product->product_type == 'grouped' ):
                                                echo '<span class="product-type tips grouped" title="' . __( 'Grouped', 'woocommerce' ) . '"></span>';
                                            elseif ( $product->product_type == 'external' ):
                                                echo '<span class="product-type tips external" title="' . __( 'External/Affiliate', 'woocommerce' ) . '"></span>';
                                            elseif ( $product->product_type == 'simple' ):

                                                if ( $product->is_virtual() ) {
                                                    echo '<span class="product-type tips virtual" title="' . __( 'Virtual', 'woocommerce' ) . '"></span>';
                                                } elseif ( $product->is_downloadable() ) {
                                                    echo '<span class="product-type tips downloadable" title="' . __( 'Downloadable', 'woocommerce' ) . '"></span>';
                                                } else {
                                                    echo '<span class="product-type tips simple" title="' . __( 'Simple', 'woocommerce' ) . '"></span>';
                                                }

                                            elseif ( $product->product_type == 'variable' ):
                                                echo '<span class="product-type tips variable" title="' . __( 'Variable', 'woocommerce' ) . '"></span>';
                                            else:
                                                // Assuming that we have other types in future
                                                echo '<span class="product-type tips ' . $product->product_type . '" title="' . ucfirst( $product->product_type ) . '"></span>';
                                            endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo (int) get_post_meta( $post->ID, 'pageview', true ); ?>
                                    </td>
                                    <td class="post-date">
                                        <?php
                                        if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                            $t_time = $h_time = __( 'Unpublished', 'dokan' );
                                            $time_diff = 0;
                                        } else {
                                            $t_time = get_the_time( __( 'Y/m/d g:i:s A', 'dokan' ) );
                                            $m_time = $post->post_date;
                                            $time = get_post_time( 'G', true, $post );

                                            $time_diff = time() - $time;

                                            if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
                                                $h_time = sprintf( __( '%s ago', 'dokan' ), human_time_diff( $time ) );
                                            } else {
                                                $h_time = mysql2date( __( 'Y/m/d', 'dokan' ), $m_time );
                                            }
                                        }

                                        echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', 'all' ) . '</abbr>';
                                        echo '<br />';
                                        if ( 'publish' == $post->post_status ) {
                                            _e( 'Published', 'dokan' );
                                        } elseif ( 'future' == $post->post_status ) {
                                            if ( $time_diff > 0 ) {
                                                echo '<strong class="attention">' . __( 'Missed schedule', 'dokan' ) . '</strong>';
                                            } else {
                                                _e( 'Scheduled', 'dokan' );
                                            }
                                        } else {
                                            _e( 'Last Modified', 'dokan' );
                                        }
                                        ?>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="7"><?php _e( 'No product found', 'dokan' ); ?></td>
                            </tr>
                        <?php } ?>

                    </tbody>

                </table>

                <?php
                wp_reset_postdata();

                if ( $product_query->max_num_pages > 1 ) {
                    echo '<div class="pagination-wrap">';
                    $page_links = paginate_links( array(
                        'current' => max( 1, get_query_var( 'paged' ) ),
                        'total' => $product_query->max_num_pages,
                        'base' => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) ),
                        'type' => 'array',
                        'prev_text' => __( '&laquo; Previous', 'dokan' ),
                        'next_text' => __( 'Next &raquo;', 'dokan' )
                    ) );

                    echo '<ul class="pagination"><li>';
                    echo join("</li>\n\t<li>", $page_links);
                    echo "</li>\n</ul>\n";
                    echo '</div>';
                }
                ?>
            </article>

        <?php endwhile; // end of the loop. ?>

        <?php do_action( 'dokan_after_listing_product' ); ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
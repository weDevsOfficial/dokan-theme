<?php
/**
 * Template Name: Dashboard - Products
 */

get_header();
?>

<?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'product' ) ); ?>

<div id="primary" class="content-area col-md-10">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <p>
                    <a href="<?php echo dokan_get_page_url( 'new_product' ); ?>" class="btn btn-large btn-info"><i class="icon-cart"></i> Add new product</a>
                </p>


                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Type</th>
                            <th>date</th>
                        </tr>
                    </thead>
                    <tbody>
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

                        if ( $query->have_posts() ) {
                            while ($query->have_posts()) {
                                $query->the_post();

                                $product = get_product( $post->ID );
                                ?>
                                <tr>
                                    <td><?php echo $product->get_image(); ?></td>
                                    <td><a href="<?php echo dokan_edit_product_url( $post->ID ); ?>"><?php echo $product->get_title(); ?></a></td>
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
                                        if ( $product->product_type == 'grouped' ) {
                                            echo '<span class="product-type tips ' . $product->product_type . '">' . __( 'Grouped', 'woocommerce' ) . '</span>';
                                        } elseif ( $product->product_type == 'external' ) {
                                            echo '<span class="product-type tips ' . $product->product_type . '">' . __( 'External/Affiliate', 'woocommerce' ) . '</span>';
                                        } elseif ( $product->product_type == 'simple' ) {

                                            if ( $product->is_virtual() ) {
                                                echo '<span class="product-type tips virtual">' . __( 'Virtual', 'woocommerce' ) . '</span>';
                                            } elseif ( $product->is_downloadable() ) {
                                                echo '<span class="product-type tips downloadable">' . __( 'Downloadable', 'woocommerce' ) . '</span>';
                                            } else {
                                                echo '<span class="product-type tips ' . $product->product_type . '">' . __( 'Simple', 'woocommerce' ) . '</span>';
                                            }
                                        } elseif ( $product->product_type == 'variable' ) {
                                            echo '<span class="product-type tips ' . $product->product_type . '">' . __( 'Variable', 'woocommerce' ) . '</span>';
                                        } else {
                                            // Assuming that we have other types in future
                                            echo '<span class="product-type tips ' . $product->product_type . '">' . ucwords( $product->product_type ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( '0000-00-00 00:00:00' == $post->post_date ) {
                                            $t_time = $h_time = __( 'Unpublished' );
                                            $time_diff = 0;
                                        } else {
                                            $t_time = get_the_time( __( 'Y/m/d g:i:s A' ) );
                                            $m_time = $post->post_date;
                                            $time = get_post_time( 'G', true, $post );

                                            $time_diff = time() - $time;

                                            if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
                                                $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
                                            } else {
                                                $h_time = mysql2date( __( 'Y/m/d' ), $m_time );
                                            }
                                        }

                                        echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', 'all' ) . '</abbr>';
                                        echo '<br />';
                                        if ( 'publish' == $post->post_status ) {
                                            _e( 'Published' );
                                        } elseif ( 'future' == $post->post_status ) {
                                            if ( $time_diff > 0 ) {
                                                echo '<strong class="attention">' . __( 'Missed schedule' ) . '</strong>';
                                            } else {
                                                _e( 'Scheduled' );
                                            }
                                        } else {
                                            _e( 'Last Modified' );
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

                if ( $query->max_num_pages > 1 ) {
                    echo '<div class="pagination pagination-centered">';
                    echo paginate_links( array(
                        'current' => max( 1, get_query_var( 'paged' ) ),
                        'total' => $query->max_num_pages,
                        'base' => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) ),
                        'type' => 'list',
                        'prev_text' => __( '&laquo;' ),
                        'next_text' => __( '&raquo;' )
                    ) );
                    echo '</div>';
                }
                ?>
            </article>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
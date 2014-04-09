<?php
/**
 * The Template for displaying all seller lists.
 *
 * Template Name: Seller List
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
get_header();
?>

<div id="primary" class="content-area col-md-9 seller-listing">
    <div id="content" class="site-content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <?php get_template_part( 'content', 'page' ); ?>

            <?php
            $paged = max( 1, get_query_var( 'paged' ) );
            $limit = 12;
            $offset = ( $paged - 1 ) * $limit;
            $sellers = dokan_get_sellers( $limit, $offset );

            if ( $sellers['users'] ) {
                ?>

                <div class="row">
                    <?php
                    foreach ($sellers['users'] as $seller) {
                        $store_info = dokan_get_store_info( $seller->ID );
                        $banner_id = isset( $store_info['banner'] ) ? $store_info['banner'] : 0;
                        $store_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'dokan' );
                        $store_url = dokan_get_store_url( $seller->ID );
                        ?>

                        <div class="col-sm-6 col-md-4 single-seller">
                            <div class="thumbnail">

                                <a href="<?php echo $store_url; ?>">
                                    <?php if ( $banner_id ) {
                                        $banner_url = wp_get_attachment_image_src( $banner_id, 'medium' );
                                        ?>
                                        <img src="<?php echo esc_url( $banner_url[0] ); ?>" alt="<?php echo esc_attr( $store_name ); ?>">
                                    <?php } else { ?>
                                        <img src="<?php echo dokan_get_no_seller_image(); ?>" alt="<?php _e( 'No Image', 'dokan' ); ?>">
                                    <?php } ?>
                                </a>

                                <div class="caption">
                                    <h3><a href="<?php echo $store_url; ?>"><?php echo $store_name; ?></a></h3>

                                    <address>
                                        <?php if ( isset( $store_info['address'] ) ) {
                                            $address = esc_html( $store_info['address'] );
                                            echo nl2br( $address );
                                        } ?>

                                        <?php if ( isset( $store_info['phone'] ) && !empty( $store_info['phone'] ) ) { ?>
                                            <br>
                                            <abbr title="<?php _e( 'Phone', 'dokan' ); ?>"><?php _e( 'P:', 'dokan' ); ?></abbr> <?php echo esc_html( $store_info['phone'] ); ?>
                                        <?php } ?>

                                    </address>

                                    <p><a class="btn btn-theme" href="<?php echo $store_url; ?>"><?php _e( 'Visit Store', 'dokan' ); ?></a></p>

                                </div> <!-- .caption -->
                            </div> <!-- .thumbnail -->
                        </div> <!-- .single-seller -->


                        <?php } ?>

                    </div> <!-- .row -->

                    <?php
                    $user_count = $sellers['count'];
                    $num_of_pages = ceil( $user_count / $limit );

                    if ( $num_of_pages > 1 ) {
                        echo '<div class="pagination-container clearfix">';
                        $page_links = paginate_links( array(
                            'current' => $paged,
                            'total' => $num_of_pages,
                            'base' => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) ),
                            'type' => 'array',
                            'prev_text' => __( '&larr; Previous', 'dokan' ),
                            'next_text' => __( 'Next &rarr;', 'dokan' ),
                        ) );

                        echo "<ul class='pagination'>\n\t<li>";
                        echo join("</li>\n\t<li>", $page_links);
                        echo "</li>\n</ul>\n";
                        echo '</div>';
                    }
                    ?>

            <?php
            } else {
                ?>

                <p class="dokan-error"><?php _e( 'No seller found!', 'dokan' ); ?></p>

            <?php } ?>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
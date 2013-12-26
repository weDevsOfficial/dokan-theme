<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package _bootstraps
 */


if ( ! function_exists( 'wedevs_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @package _bootstraps - 2013 1.0
 */
function wedevs_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'wedevs' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'wedevs' ), ' ' ); ?></p>
    <?php
            break;
        default :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <article id="comment-<?php comment_ID(); ?>" class="comment">
            <footer>
                <div class="comment-author vcard">
                    <div class="comment-avatar">
                        <?php echo get_avatar( $comment, 75 ); ?>
                    </div>
                    <?php printf( __( '%s <span class="says">says:</span>', 'wedevs' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>

                    <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time pubdate datetime="<?php comment_time( 'c' ); ?>">
                        <?php
                        /* translators: 1: date, 2: time */
                        printf( __( '%1$s at %2$s', 'wedevs' ), get_comment_date(), get_comment_time() );
                        ?>
                        </time>
                    </a>
                    <?php edit_comment_link( __( '(Edit)', 'wedevs' ), ' ' );
                    ?>
                </div><!-- .comment-author .vcard -->
                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em><?php _e( 'Your comment is awaiting moderation.', 'wedevs' ); ?></em>
                    <br />
                <?php endif; ?>
            </footer>

            <div class="comment-content"><?php comment_text(); ?></div>

            <div class="reply">
                <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
            </div><!-- .reply -->
        </article><!-- #comment-## -->

    <?php
            break;
    endswitch;
}
endif; // ends check for tp_comment()

if ( ! function_exists( 'wedevs_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @package _bootstraps - 2013 1.0
 */
function wedevs_posted_on() {
    printf( __( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"></span>', 'wedevs' ),
        esc_url( get_permalink() ),
        esc_attr( get_the_time() ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_attr( sprintf( __( 'View all posts by %s', 'wedevs' ), get_the_author() ) ),
        esc_html( get_the_author() )
    );
}
endif;

/**
 * Flush out the transients used in tp_categorized_blog
 *
 * @return void
 */
function tp_category_transient_flusher() {
    // Like, beat it. Dig?
    delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'tp_category_transient_flusher' );
add_action( 'save_post', 'tp_category_transient_flusher' );

/**
 * Display navigation to next/previous pages when applicable
 */
function wedevs_content_nav( $nav_id, $query = null ) {
    global $wp_query, $post;

    if ( $query ) {
        $wp_query = $query;
    }

    // Don't print empty markup on single pages if there's nowhere to navigate.
    if ( is_single() ) {
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next = get_adjacent_post( false, '', false );

        if ( !$next && !$previous )
            return;
    }

    // Don't print empty markup in archives if there's only one page.
    if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
        return;

    $nav_class = 'site-navigation paging-navigation';
    if ( is_single() )
        $nav_class = 'site-navigation post-navigation';
    ?>
    <nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
        <h1 class="assistive-text"><?php _e( 'Post navigation', 'wedevs' ); ?></h1>

        <ul class="pager">
        <?php if ( is_single() ) : // navigation links for single posts  ?>

            <li class="previous">
                <?php previous_post_link( '%link', _x( '&larr;', 'Previous post link', 'wedevs' ) . ' %title' ); ?>
            </li>
            <li class="next">
                <?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'wedevs' ) ); ?>
            </li>

        <?php endif; ?>
        </ul>


        <?php if ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
            <?php wedevs_page_navi( '', '', $wp_query ); ?>
        <?php endif; ?>

    </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
}

function wedevs_page_navi( $before = '', $after = '', $wp_query ) {

    $posts_per_page = intval( get_query_var( 'posts_per_page' ) );
    $paged = intval( get_query_var( 'paged' ) );
    $numposts = $wp_query->found_posts;
    $max_page = $wp_query->max_num_pages;
    if ( $numposts <= $posts_per_page ) {
        return;
    }
    if ( empty( $paged ) || $paged == 0 ) {
        $paged = 1;
    }
    $pages_to_show = 7;
    $pages_to_show_minus_1 = $pages_to_show - 1;
    $half_page_start = floor( $pages_to_show_minus_1 / 2 );
    $half_page_end = ceil( $pages_to_show_minus_1 / 2 );
    $start_page = $paged - $half_page_start;
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    $end_page = $paged + $half_page_end;
    if ( ($end_page - $start_page) != $pages_to_show_minus_1 ) {
        $end_page = $start_page + $pages_to_show_minus_1;
    }
    if ( $end_page > $max_page ) {
        $start_page = $max_page - $pages_to_show_minus_1;
        $end_page = $max_page;
    }
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }

    echo $before . '<div class="pagination-container"><ul class="pagination">' . "";
    if ( $paged > 1 ) {
        $first_page_text = "«";
        echo '<li class="prev"><a href="' . get_pagenum_link() . '" title="First">' . $first_page_text . '</a></li>';
    }

    $prevposts = get_previous_posts_link( '← Previous' );
    if ( $prevposts ) {
        echo '<li>' . $prevposts . '</li>';
    } else {
        echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'wedevs' ) . '</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ( $i == $paged ) {
            echo '<li class="active"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
        }
    }
    echo '<li class="">';
    next_posts_link( __('Next &rarr;', 'wedevs') );
    echo '</li>';
    if ( $end_page < $max_page ) {
        $last_page_text = "»";
        echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    }
    echo '</ul></div>' . $after . "";
}

function dokan_checkout_header_btn() {
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    ?>
    <span class="cart-link">
        <a href="<?php echo $woocommerce->cart->get_cart_url(); ?>"
           title="<?php _e( 'View your shopping cart', 'woothemes' ); ?>">
            <i class="icon-shopping-cart"></i>
            <span><?php printf( __( 'Cart (%d) %s', 'wedevs' ), $woocommerce->cart->get_cart_contents_count(), $woocommerce->cart->get_cart_total() ); ?></span>
        </a>

        <div class="cart-items">
            <span class="border"></span>

            <?php if ( $items ) { ?>

            <ul class="mini-product-list">
                <?php
                foreach ( $items as $hash => $item ) {
                    $product = $item['data'];
                    ?>

                    <li class="item clearfix">

                        <?php
                        $thumbnail = apply_filters( 'woocommerce_in_cart_product_thumbnail', $product->get_image(), $item, $hash );
                        printf( '<a href="%s">%s</a>', esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product_id', $item['product_id'] ) ) ), $thumbnail );
                        ?>

                        <div class="product-details">
                            <?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( $woocommerce->cart->get_remove_url( $hash ) ), __( 'Remove this item', 'woocommerce' ) ), $hash ); ?>

                            <p class="product-name">
                                <?php
                                if ( $product->exists() && $item['quantity'] > 0 ) {

                                    if ( !$product->is_visible() || ($product instanceof WC_Product_Variation && !$product->parent_is_visible()) ) {
                                        echo apply_filters( 'woocommerce_in_cart_product_title', $product->get_title(), $item, $hash );
                                    } else {
                                        printf( '<a href="%s">%s</a>', esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product_id', $item['product_id'] ) ) ), apply_filters( 'woocommerce_in_cart_product_title', $product->get_title(), $item, $hash ) );
                                    }

                                }
                                ?>
                            </p>

                            <?php echo $item['quantity'] ?> x

                            <span class="price">
                                <?php
                                $product_price = get_option( 'woocommerce_display_cart_prices_excluding_tax' ) == 'yes' || $woocommerce->customer->is_vat_exempt() ? $product->get_price_excluding_tax() : $product->get_price();

                                echo apply_filters( 'woocommerce_cart_item_price_html', woocommerce_price( $product_price ), $item, $hash );
                                ?>
                            </span>

                        </div>
                        <!-- .product-details -->
                    </li>
                    <?php } ?>
            </ul>

            <div class="buttons clearfix">
                <a class="btn btn-info"
                   href="<?php echo $woocommerce->cart->get_cart_url(); ?>"><?php _e( 'View Cart', 'dokan' ); ?></a>
                <a class="btn btn-warning"
                   href="<?php echo $woocommerce->cart->get_checkout_url(); ?>"><?php _e( 'Checkout', 'dokan' ); ?></a>
            </div>
            <?php } else { ?>
            <div class="alert alert-error">
                <a class="close" data-dismiss="alert">&times;</a>
                <?php _e( 'No items found in cart', 'dokan' ); ?>
            </div>
            <?php } ?>
        </div>
    </span>
<?php
}

function dokan_product_dashboard_errors() {
    $type = isset( $_GET['message'] ) ? $_GET['message'] : '';

    switch ($type) {
        case 'product_deleted':
            ?>
            <div class="alert alert-success">
                <?php echo __( 'Product has been deleted successfully!', 'dokan' ); ?>
            </div>
            <?php
            break;

        case 'error':
            ?>
            <div class="alert alert-danger">
                <?php echo __( 'Something went wrong!', 'dokan' ); ?>
            </div>
            <?php
            break;
    }
}
<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package dokan
 */


if ( ! function_exists( 'dokan_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @package dokan - 2014 1.0
 */
function dokan_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'dokan-theme' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'dokan-theme' ), ' ' ); ?></p>
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
                    <?php printf( __( '%s <span class="says">says:</span>', 'dokan-theme' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>

                    <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time pubdate datetime="<?php comment_time( 'c' ); ?>">
                        <?php
                        /* translators: 1: date, 2: time */
                        printf( __( '%1$s at %2$s', 'dokan-theme' ), get_comment_date(), get_comment_time() );
                        ?>
                        </time>
                    </a>
                    <?php edit_comment_link( __( '(Edit)', 'dokan-theme' ), ' ' );
                    ?>
                </div><!-- .comment-author .vcard -->
                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em><?php _e( 'Your comment is awaiting moderation.', 'dokan-theme' ); ?></em>
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

if ( ! function_exists( 'dokan_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @package dokan - 2014 1.0
 */
function dokan_posted_on() {
    printf( __( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"></span>', 'dokan-theme' ),
        esc_url( get_permalink() ),
        esc_attr( get_the_time() ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_attr( sprintf( __( 'View all posts by %s', 'dokan-theme' ), get_the_author() ) ),
        esc_html( get_the_author() )
    );
}
endif;

if ( ! function_exists( 'dokan_content_nav' ) ) :

/**
 * Display navigation to next/previous pages when applicable
 */
function dokan_content_nav( $nav_id, $query = null ) {
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
        <h1 class="assistive-text"><?php _e( 'Post navigation', 'dokan-theme' ); ?></h1>

        <ul class="pager">
        <?php if ( is_single() ) : // navigation links for single posts  ?>

            <li class="previous">
                <?php previous_post_link( '%link', _x( '&larr;', 'Previous post link', 'dokan-theme' ) . ' %title' ); ?>
            </li>
            <li class="next">
                <?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'dokan-theme' ) ); ?>
            </li>

        <?php endif; ?>
        </ul>


        <?php if ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
            <?php dokan_page_navi( '', '', $wp_query ); ?>
        <?php endif; ?>

    </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
}

endif;


if ( ! function_exists( 'dokan_page_navi' ) ) :

function dokan_page_navi( $before = '', $after = '', $wp_query ) {

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
        $first_page_text = "&laquo;";
        echo '<li class="prev"><a href="' . get_pagenum_link() . '" title="First">' . $first_page_text . '</a></li>';
    }

    $prevposts = get_previous_posts_link( '&larr; Previous' );
    if ( $prevposts ) {
        echo '<li>' . $prevposts . '</li>';
    } else {
        echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'dokan-theme' ) . '</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ( $i == $paged ) {
            echo '<li class="active"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
        }
    }
    echo '<li class="">';
    next_posts_link( __('Next &rarr;', 'dokan-theme') );
    echo '</li>';
    if ( $end_page < $max_page ) {
        $last_page_text = "&larr;";
        echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    }
    echo '</ul></div>' . $after . "";
}

endif;


if ( ! function_exists( 'dokan_category_widget' ) ) :

/**
 * Display the product category widget
 *
 * @return void
 */
function dokan_category_widget() {
        if ( class_exists( 'Dokan_Category_Widget' ) ) {
            the_widget( 'Dokan_Category_Widget', array(
                'title' => __( 'Product Categories', 'dokan-theme' )
            ), array(
                'before_widget' => '<aside class="widget dokan-category-menu">',
                'after_widget'  => '</aside>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            )
            );
        }
}

endif;


if ( !function_exists( 'dokan_header_user_menu' ) ) :

/**
 * User top navigation menu
 *
 * @return void
 */
function dokan_header_user_menu() {
    ?>
    <ul class="nav navbar-nav navbar-right">
        <li>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php printf( __( 'Cart %s', 'dokan-theme' ), '<span class="dokan-cart-amount-top">(' . WC()->cart->get_cart_total() . ')</span>' ); ?> <b class="caret"></b></a>

            <ul class="dropdown-menu">
                <li>
                    <div class="widget_shopping_cart_content"></div>
                </li>
            </ul>
        </li>

        <?php if ( is_user_logged_in() ) { ?>

            <?php
            global $current_user;
            
            
            $is_seller = false;
            
            if(  function_exists( 'dokan_is_user_seller' )){
                $is_seller = dokan_is_user_seller( $user_id );
            }

            $user_id = $current_user->ID;
            if ( $is_seller ) {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php _e( 'Seller Dashboard', 'dokan-theme' ); ?> <b class="caret"></b></a>

                    <ul class="dropdown-menu">
                        <li><a href="<?php echo dokan_get_store_url( $user_id ); ?>" target="_blank"><?php _e( 'Visit your store', 'dokan-theme' ); ?> <i class="fa fa-external-link"></i></a></li>
                        <li class="divider"></li>
                        <?php
                        $nav_urls = dokan_get_dashboard_nav();

                        foreach ($nav_urls as $key => $item) {
                            printf( '<li><a href="%s">%s &nbsp;%s</a></li>', $item['url'], $item['icon'], $item['title'] );
                        }
                        ?>
                    </ul>
                </li>
            <?php } ?>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo esc_html( $current_user->display_name ); ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>"><?php _e( 'My Account', 'dokan-theme' ); ?></a></li>
                    <li><a href="<?php echo wc_customer_edit_account_url(); ?>"><?php _e( 'Edit Account', 'dokan-theme' ); ?></a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'billing', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Billing Address', 'dokan-theme' ); ?></a></li>
                    <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'shipping', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Shipping Address', 'dokan-theme' ); ?></a></li>
                </ul>
            </li>

            <li><?php wp_loginout( home_url() ); ?></li>

        <?php } else { ?>
            <li><a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>"><?php _e( 'Log in', 'dokan-theme' ); ?></a></li>
            <li><a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>"><?php _e( 'Sign Up', 'dokan-theme' ); ?></a></li>
        <?php } ?>
    </ul>
    <?php
}

endif;

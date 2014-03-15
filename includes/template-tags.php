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
        <p><?php _e( 'Pingback:', 'dokan' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'dokan' ), ' ' ); ?></p>
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
                    <?php printf( __( '%s <span class="says">says:</span>', 'dokan' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>

                    <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time pubdate datetime="<?php comment_time( 'c' ); ?>">
                        <?php
                        /* translators: 1: date, 2: time */
                        printf( __( '%1$s at %2$s', 'dokan' ), get_comment_date(), get_comment_time() );
                        ?>
                        </time>
                    </a>
                    <?php edit_comment_link( __( '(Edit)', 'dokan' ), ' ' );
                    ?>
                </div><!-- .comment-author .vcard -->
                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em><?php _e( 'Your comment is awaiting moderation.', 'dokan' ); ?></em>
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
    printf( __( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"></span>', 'dokan' ),
        esc_url( get_permalink() ),
        esc_attr( get_the_time() ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_attr( sprintf( __( 'View all posts by %s', 'dokan' ), get_the_author() ) ),
        esc_html( get_the_author() )
    );
}
endif;


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
        <h1 class="assistive-text"><?php _e( 'Post navigation', 'dokan' ); ?></h1>

        <ul class="pager">
        <?php if ( is_single() ) : // navigation links for single posts  ?>

            <li class="previous">
                <?php previous_post_link( '%link', _x( '&larr;', 'Previous post link', 'dokan' ) . ' %title' ); ?>
            </li>
            <li class="next">
                <?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'dokan' ) ); ?>
            </li>

        <?php endif; ?>
        </ul>


        <?php if ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
            <?php dokan_page_navi( '', '', $wp_query ); ?>
        <?php endif; ?>

    </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
}

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
        echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'dokan' ) . '</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ( $i == $paged ) {
            echo '<li class="active"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
        }
    }
    echo '<li class="">';
    next_posts_link( __('Next &rarr;', 'dokan') );
    echo '</li>';
    if ( $end_page < $max_page ) {
        $last_page_text = "&larr;";
        echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    }
    echo '</ul></div>' . $after . "";
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

function dokan_product_listing_status_filter() {
    $permalink = get_permalink();
    $status_class = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';
    $post_counts = dokan_count_posts( 'product', get_current_user_id() );
    ?>
    <ul class="list-inline col-md-9 subsubsub">
        <li<?php echo $status_class == 'all' ? ' class="active"' : ''; ?>>
            <a href="<?php echo $permalink; ?>"><?php printf( __( 'All (%d)', 'dokan' ), $post_counts->total ); ?></a>
        </li>
        <li<?php echo $status_class == 'publish' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $permalink ); ?>"><?php printf( __( 'Online (%d)', 'dokan' ), $post_counts->publish ); ?></a>
        </li>
        <li<?php echo $status_class == 'pending' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'pending' ), $permalink ); ?>"><?php printf( __( 'Pending Review (%d)', 'dokan' ), $post_counts->pending ); ?></a>
        </li>
        <li<?php echo $status_class == 'draft' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'draft' ), $permalink ); ?>"><?php printf( __( 'Draft (%d)', 'dokan' ), $post_counts->draft ); ?></a>
        </li>
    </ul> <!-- .post-statuses-filter -->
    <?php
}

function dokan_order_listing_status_filter() {
    $orders_url = get_permalink();
    $status_class = isset( $_GET['order_status'] ) ? $_GET['order_status'] : 'all';
    $orders_counts = dokan_count_orders( get_current_user_id() );
    ?>

    <ul class="list-inline order-statuses-filter">
        <li<?php echo $status_class == 'all' ? ' class="active"' : ''; ?>>
            <a href="<?php echo $orders_url; ?>">
                <?php printf( __( 'All (%d)', 'dokan' ), $orders_counts->total ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'completed' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'completed' ), $orders_url ); ?>">
                <?php printf( __( 'Completed (%d)', 'dokan' ), $orders_counts->completed ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'processing' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'processing' ), $orders_url ); ?>">
                <?php printf( __( 'Processing (%d)', 'dokan' ), $orders_counts->processing ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'on-hold' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'on-hold' ), $orders_url ); ?>">
                <?php printf( __( 'On-hold (%d)', 'dokan' ), $orders_counts->{'on-hold'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'pending' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'pending' ), $orders_url ); ?>">
                <?php printf( __( 'Pending (%d)', 'dokan' ), $orders_counts->pending ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'canceled' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'cancelled' ), $orders_url ); ?>">
                <?php printf( __( 'Cancelled (%d)', 'dokan' ), $orders_counts->cancelled ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'refunded' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'refunded' ), $orders_url ); ?>">
                <?php printf( __( 'Refunded (%d)', 'dokan' ), $orders_counts->refunded ); ?></span>
            </a>
        </li>
    </ul>
    <?php
}

function dokan_get_dashboard_nav() {
    $urls = array(
        'dashboard' => array(
            'title' => __( 'Dashboard', 'dokan'),
            'icon' => '<i class="icon-dashboard"></i>',
            'url' => dokan_get_page_url( 'dashboard' )
        ),
        'product' => array(
            'title' => __( 'Products', 'dokan'),
            'icon' => '<i class="icon-briefcase"></i>',
            'url' => dokan_get_page_url( 'products' )
        ),
        'order' => array(
            'title' => __( 'Orders', 'dokan'),
            'icon' => '<i class="icon-basket"></i>',
            'url' => dokan_get_page_url( 'orders' )
        ),
        'coupon' => array(
            'title' => __( 'Coupons', 'dokan'),
            'icon' => '<i class="icon-gift"></i>',
            'url' => dokan_get_page_url( 'coupons' )
        ),
        'report' => array(
            'title' => __( 'Reports', 'dokan'),
            'icon' => '<i class="icon-stats"></i>',
            'url' => dokan_get_page_url( 'reports' )
        ),
        'reviews' => array(
            'title' => __( 'Reviews', 'dokan'),
            'icon' => '<i class="icon-bubbles"></i>',
            'url' => dokan_get_page_url( 'reviews' )
        ),
        'withdraw' => array(
            'title' => __( 'Withdraw', 'dokan'),
            'icon' => '<i class="icon-upload"></i>',
            'url' => dokan_get_page_url( 'withdraw' )
        ),
        'settings' => array(
            'title' => __( 'Settings', 'dokan'),
            'icon' => '<i class="icon-cog"></i>',
            'url' => dokan_get_page_url( 'settings' )
        ),
    );

    return apply_filters( 'dokan_get_dashboard_nav', $urls );
}

function dokan_dashboard_nav( $active_menu ) {
    $urls = dokan_get_dashboard_nav();
    $menu = '<ul class="dokan-dashboard-menu">';

    foreach ($urls as $key => $item) {
        $class = ( $active_menu == $key ) ? ' class="active"' : '';
        $menu .= sprintf( '<li%s><a href="%s">%s %s</a></li>', $class, $item['url'], $item['icon'], $item['title'] );
    }
    $menu .= '</ul>';

    return $menu;
}

function dokan_category_widget() {
     the_widget( 'Dokan_Category_Widget', array(
        'title' => __( 'Product Categories', 'dokan' )
        ),
        array(
            'before_widget' => '<aside class="widget dokan-category-menu">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        )
    );
}


function dokan_seller_reg_form_fields() {
    $role = isset( $_POST['role'] ) ? $_POST['role'] : 'customer';
    $role_style = ( $role == 'customer' ) ? ' style="display:none"' : '';
    ?>
    <div class="show_if_seller"<?php echo $role_style; ?>>

        <div class="split-row form-row-wide">
            <p class="form-row form-group">
                <label for="first-name"><?php _e( 'First Name', 'dokan' ); ?> <span class="required">*</span></label>
                <input type="text" class="input-text form-control" name="fname" id="first-name" value="<?php if ( ! empty( $_POST['fname'] ) ) echo esc_attr($_POST['fname']); ?>" required="required" />
            </p>

            <p class="form-row form-group">
                <label for="last-name"><?php _e( 'Last Name', 'dokan' ); ?> <span class="required">*</span></label>
                <input type="text" class="input-text form-control" name="lname" id="last-name" value="<?php if ( ! empty( $_POST['lname'] ) ) echo esc_attr($_POST['lname']); ?>" required="required" />
            </p>
        </div>

        <p class="form-row form-group form-row-wide">
            <label for="company-name"><?php _e( 'Shop Name', 'dokan' ); ?> <span class="required">*</span></label>
            <input type="text" class="input-text form-control" name="shopname" id="company-name" value="<?php if ( ! empty( $_POST['shopname'] ) ) echo esc_attr($_POST['shopname']); ?>" required="required" />
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="seller-url" class="pull-left"><?php _e( 'Shop URL', 'dokan' ); ?> <span class="required">*</span></label>
            <strong id="url-alart-mgs" class="pull-right"></strong>
            <input type="text" class="input-text form-control" name="shopurl" id="seller-url" value="<?php if ( ! empty( $_POST['shopurl'] ) ) echo esc_attr($_POST['shopurl']); ?>" required="required" />
            <small><?php echo home_url(); ?>/store/<strong id="url-alart"></strong></small>
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="seller-address"><?php _e( 'Address', 'dokan' ); ?><span class="required">*</span></label>
            <textarea type="text" id="seller-address" name="address" class="form-control input" required="required"><?php if ( ! empty( $_POST['address'] ) ) echo esc_textarea($_POST['address']); ?></textarea>
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="shop-phone"><?php _e( 'Phone', 'dokan' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text form-control" name="phone" id="shop-phone" value="<?php if ( ! empty( $_POST['phone'] ) ) echo esc_attr($_POST['phone']); ?>" required="required" />
        </p>
    </div>

    <p class="form-row form-group user-role">
        <label class="radio">
            <input type="radio" name="role" value="customer"<?php checked( $role, 'customer' ); ?>>
            <?php _e( 'I am a customer', 'dokan' ); ?>
        </label>

        <label class="radio">
            <input type="radio" name="role" value="seller"<?php checked( $role, 'seller' ); ?>>
            <?php _e( 'I am a seller', 'dokan' ); ?>
        </label>
    </p>

    <script type="text/javascript">
        jQuery(function($) {
            $('.user-role input[type=radio]').on('change', function() {
                var value = $(this).val();

                if ( value === 'seller') {
                    $('.show_if_seller').slideDown();
                } else {
                    $('.show_if_seller').slideUp();
                }
            });

            $('#company-name').on('focusout', function() {
                var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
                $('#seller-url').val(value);
                $('#url-alart').text( value );
                $('#seller-url').focus();
            });

            $('#seller-url').keyup(function() {
                var self = $(this);
                var value = self.val().toLowerCase().replace(/\s+/g, '-').replace(/[^-a-z0-9-]/g, '');

                self.val(value);
                $('#url-alart').text( value );

                $('#url-alart').removeClass('text-danger');
                $('#url-alart').removeClass('text-success');
                $('#url-alart-mgs').text('');
                $('#url-alart-mgs').removeClass('text-danger');
                $('#url-alart-mgs').removeClass('text-success');
                return;
            });

            $('#shop-phone').keyup(function() {
                var self = $(this);
                var value = self.val().replace(/[^-+-0-9-]/g, '');
                self.val(value);
                return;
            });

            $('#seller-url').on('focusout', function() {
                var self = $(this),
                data = {
                    action : 'shop_url',
                    url_slug : self.val(),
                    _nonce : dokan.nonce,
                };

                // self({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                $.post( dokan.ajaxurl, data, function(resp) {

                   if( resp == 0){
                        $('#url-alart').addClass('text-danger');
                        $('#url-alart-mgs').text('Not Availavle');
                        $('#url-alart-mgs').addClass('text-danger');
                   }else{
                        $('#url-alart').addClass('text-success');
                        $('#url-alart-mgs').text('Availavle');
                        $('#url-alart-mgs').addClass('text-success');
                   }

                } );

            });
        });
    </script>
    <?php
}

add_action( 'register_form', 'dokan_seller_reg_form_fields' );

function dokan_seller_not_enabled_notice() {
    ?>
        <div class="alert alert-warning">
            <strong><?php _e( 'Error!', 'dokan' ); ?></strong>
            <?php _e( 'Your account is not enabled for selling, please contact the admin', 'dokan' ); ?>
        </div>
    <?php
}
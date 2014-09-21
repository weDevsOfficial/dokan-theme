<?php
/**
 * The Template for displaying all reviews.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */

$store_user = get_userdata( get_query_var( 'author' ) );
$store_info = dokan_get_store_info( $store_user->ID );

$scheme = is_ssl() ? 'https' : 'http';
wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?sensor=true' );

get_header();
?>

<?php get_sidebar( 'store' ); ?>

<div id="primary" class="content-area dokan-single-store col-md-9">
    <div id="content" class="site-content store-review-wrap woocommerce" role="main">

        <?php dokan_get_template_part( 'store-header' ); ?>

        <?php
        $dokan_template_reviews = Dokan_Template_reviews::init();
        $id                     = $store_user->ID;
        $post_type              = 'product';
        $limit                  = 20;
        $status                 = '1';
        $comments               = $dokan_template_reviews->comment_query( $id, $post_type, $limit, $status );
        ?>

        <div id="reviews">
            <div id="comments">

                <h2 class="headline"><?php _e( 'Seller Review', 'dokan' ); ?></h2>

                <ol class="commentlist">
                    <?php
                    if ( count( $comments ) == 0 ) {
                        echo '<span colspan="5">' . __( 'No Result Found', 'dokan' ) . '</span>';
                    } else {

                        foreach ($comments as $single_comment) {
                            $GLOBALS['comment'] = $single_comment;
                            $comment_date       = get_comment_date( 'l, F jS, Y \a\t g:i a', $single_comment->comment_ID );
                            $comment_author_img = get_avatar( $single_comment->comment_author_email, 180 );
                            $permalink          = get_comment_link( $single_comment );
                            ?>

                            <li <?php comment_class(); ?> itemtype="http://schema.org/Review" itemscope="" itemprop="reviews">
                                <div class="review_comment_container">
                                    <div class="dokan-review-author-img"><?php echo $comment_author_img; ?></div>
                                    <div class="comment-text">
                                        <a href="<?php echo $permalink; ?>">
                                            <?php
                                            if ( get_option('woocommerce_enable_review_rating') == 'yes' ) :
                                                $rating =  intval( get_comment_meta( $single_comment->comment_ID, 'rating', true ) ); ?>
                                                <div class="dokan-rating">
                                                    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf(__( 'Rated %d out of 5', 'dokan' ), $rating) ?>">
                                                        <span style="width:<?php echo ( intval( get_comment_meta( $single_comment->comment_ID, 'rating', true ) ) / 5 ) * 100; ?>%"><strong itemprop="ratingValue"><?php echo $rating; ?></strong> <?php _e( 'out of 5', 'dokan' ); ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <p>
                                            <strong itemprop="author"><?php echo $single_comment->comment_author; ?></strong>
                                            <em class="verified"><?php echo $single_comment->user_id == 0 ? '(Guest)' : ''; ?></em>
                                            –
                                            <a href="<?php echo $permalink; ?>">
                                                <time datetime="<?php echo date( 'c', strtotime( $comment_date ) ); ?>" itemprop="datePublished"><?php echo $comment_date; ?></time>
                                            </a>
                                        </p>
                                        <div class="description" itemprop="description">
                                            <p><?php echo $single_comment->comment_content; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </li>

                           <?php
                        }
                    }
                    ?>
                </ol>
            </div>
        </div>

        <?php
        echo $dokan_template_reviews->review_pagination( $id, $post_type, $limit, $status );
        ?>
    </div>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
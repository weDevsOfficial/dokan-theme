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

<?php $url = get_template_directory_uri() . '/assets/images/footer'; ?>


<?php get_sidebar( 'store' ); ?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content store-page-wrap woocommerce" role="main">

        <div class="profile-frame">

            <?php if ( isset( $store_info['banner'] ) && !empty( $store_info['banner'] ) ) { ?>
            <style type="text/css">
                .profile-frame {
                    background-image: url('<?php echo wp_get_attachment_url( $store_info['banner'] ); ?>');
                }
            </style>
            <?php } ?>

            <div class="col-md-4 profile-info-box">
                <div class="profile-img">
                    <?php echo get_avatar( $store_user->ID, 80 ); ?>
                </div>

                <div class="profile-info">
                    <ul class="list-unstyled">

                        <?php if ( isset( $store_info['store_name'] ) ) { ?>
                            <li class="store-name"><?php echo esc_html( $store_info['store_name'] ); ?></li>
                        <?php } ?>

                        <?php if ( isset( $store_info['address'] ) && !empty( $store_info['address'] ) ) { ?>
                            <li><i class="fa fa-map-marker"></i> <?php echo esc_html( $store_info['address'] ); ?></li>
                        <?php } ?>

                        <?php if ( isset( $store_info['phone'] ) && !empty( $store_info['phone'] ) ) { ?>
                            <li><i class="fa fa-mobile"></i>
                                <a href="tel:<?php echo esc_html( $store_info['phone'] ); ?>"><?php echo esc_html( $store_info['phone'] ); ?></a>
                            </li>
                        <?php } ?>

                        <?php if ( isset( $store_info['show_email'] ) && $store_info['show_email'] == 'yes' ) { ?>
                            <li><i class="fa fa-envelope-o"></i>
                                <a href="mailto:<?php echo antispambot( $store_user->user_email ); ?>"><?php echo antispambot( $store_user->user_email ); ?></a>
                            </li>
                        <?php } ?>

                        <li>
                            <i class="fa fa-star"></i>
                            <?php dokan_get_readable_seller_rating( $store_user->ID ); ?>
                        </li>
                    </ul>

                    <ul class="list-inline store-social">
                        <?php if ( isset( $store_info['social']['fb'] ) && !empty( $store_info['social']['fb'] ) ) { ?>
                            <li>
                                <a href="<?php echo esc_url( $store_info['social']['fb'] ); ?>" target="_blank"><i class="fa fa-facebook-square"></i></a>
                            </li>
                        <?php } ?>

                        <?php if ( isset( $store_info['social']['gplus'] ) && !empty( $store_info['social']['gplus'] ) ) { ?>
                            <li>
                                <a href="<?php echo esc_url( $store_info['social']['gplus'] ); ?>" target="_blank"><i class="fa fa-google-plus-square"></i></a>
                            </li>
                        <?php } ?>

                        <?php if ( isset( $store_info['social']['twitter'] ) && !empty( $store_info['social']['twitter'] ) ) { ?>
                            <li>
                                <a href="<?php echo esc_url( $store_info['social']['twitter'] ); ?>" target="_blank"><i class="fa fa-twitter-square"></i></a>
                            </li>
                        <?php } ?>

                        <?php if ( isset( $store_info['social']['linkedin'] ) && !empty( $store_info['social']['linkedin'] ) ) { ?>
                            <li>
                                <a href="<?php echo esc_url( $store_info['social']['linkedin'] ); ?>" target="_blank"><i class="fa fa-linkedin-square"></i></a>
                            </li>
                        <?php } ?>

                        <?php if ( isset( $store_info['social']['youtube'] ) && !empty( $store_info['social']['youtube'] ) ) { ?>
                            <li>
                                <a href="<?php echo esc_url( $store_info['social']['youtube'] ); ?>" target="_blank"><i class="fa fa-youtube-square"></i></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div> <!-- .profile-info -->
            </div> <!-- .profile-info-box -->
        </div> <!-- .profile-frame -->

        <?php
        $dokan_template_reviews = Dokan_Template_reviews::init();
        $id = $store_user->ID;
        $post_type = 'product';
        $limit = 20;
        $status = '1';
        $comments = $dokan_template_reviews->comment_query( $id, $post_type, $limit, $status );
        ?>

        <div id="reviews">
            <div id="comments">

                <h2><?php _e( 'Seller Review', 'dokan' ); ?></h2>

                <ol class="commentlist">
                    <?php
                    if ( count( $comments ) == 0 ) {
                        return '<span colspan="5">' . __( 'No Result Found', 'dokan' ) . '</span>';
                    }

                    foreach ($comments as $single_comment) {
                        $comment_date = get_comment_date( 'l, F jS, Y \a\t g:i a', $single_comment->comment_ID );
                        $comment_author_img = get_avatar( $single_comment->comment_author_email, 180 );
                        $permalink = get_comment_link( $single_comment );
                        ?>

                        <li class="comment byuser comment-author-sk-shaikat" itemtype="http://schema.org/Review" itemscope="" itemprop="reviews">
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
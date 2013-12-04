<?php
/**
 * Show users all comments and process all bulk action
 *
 * @author Asaquzzaman
 */
error_reporting(E_ALL);
class Dokan_Template_reviews {

    private $limit = 15;
    private $pending;
    private $spam;
    private $trash;
    private $post_type;

    public static function init() {
        static $instance = false;

        if( !$instance ) {
            $instance = new Dokan_Template_reviews();
        }

        return $instance;
    }

    /**
     * dokan_reviews()
     *
     * Initial function of this Class or class controller.
     */
    function dokan_action() {

        // ajax
        add_action( 'wp_ajax_wpuf_comment_status', array($this, 'ajax_comment_status') );
        add_action( 'wp_ajax_wpuf_update_comment', array($this, 'ajax_update_comment') );

    }



    function ajax_comment_status() {

        if ( !wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) && !is_user_logged_in() ) {
            wp_send_json_error();
        }

        $comment_id = $_POST['comment_id'];
        $action = $_POST['comment_status'];
        $post_type = $_POST['post_type'];
        $page_status = $_POST['curr_page'];

        if ( $action == 'delete' && isset( $comment_id ) ) {
            wp_delete_comment( $comment_id );
        }

        if ( isset( $comment_id ) && isset( $action ) ) {
            wp_set_comment_status( $comment_id, $action );
        }

        $comment = get_comment( $comment_id );

        $this->get_count( $post_type );


        ob_start();
        $this->render_row( $comment, $post_type  );
        $html = array(
            'pending' => $this->pending,
            'spam' => $this->spam,
            'trash' => $this->trash,
            'content' => ob_get_clean()
        );

        wp_send_json_success( $html);
    }

    /**
     * WPUF_Comments()
     *
     * WPUF_Comments this shortcode activation function
     */
    function reviews_view() {



        if ( is_user_logged_in() ) {

            // initialize
            $this->limit = 15;
            $this->post_type = 'product';
            $post_type = 'product';

            $this->get_count( $post_type );

            echo '<div class="wpuf-comments-wrap">';
            //menu
            $this->wpuf_comments_menu( $post_type );

            //Show all comments in this form
            $this->show_comment_table( $post_type );

            echo '</div> <!-- .wpuf-comments-wrap -->';
        }


    }

    /**
     * Counting spam, pending, trash and save it private variable
     *
     * @global object $wpdb
     * @global object $current_user
     * @param string $post_type
     */
    function get_count( $post_type ) {
        global $wpdb, $current_user;

        $count = $wpdb->get_results( "SELECT $wpdb->comments.comment_approved, COUNT( * ) AS num_comments
            FROM $wpdb->comments, $wpdb->posts
            WHERE $wpdb->posts.post_author='$current_user->ID' AND
                $wpdb->posts.post_status='publish' AND
                $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
                $wpdb->posts.post_type='$post_type'
            GROUP BY $wpdb->comments.comment_approved", ARRAY_A );

        foreach ($count as $number) {
            if ( $number['comment_approved'] == '0' )
                $this->pending = $number['num_comments'];
            if ( $number['comment_approved'] == 'spam' )
                $this->spam = $number['num_comments'];
            if ( $number['comment_approved'] == 'trash' )
                $this->trash = $number['num_comments'];
        }
    }

    /**
     * Show all comments in this form
     *
     * @param string $post_type
     */
    function show_comment_table( $post_type ) {
        ?>

        <form id="wpuf_comments-form" action="" method="post">
            <table id="wpuf-comments-table">
                <tr>
                    <th class="col-check"><input id="wpuf-check-all" type="checkbox" ></th>
                    <th class="col-author"><?php _e( 'Author', 'wpuf-comment' ); ?></th>
                    <th class="col-content"><?php _e( 'Comment', 'wpuf-comment' ); ?></th>
                    <th class="col-link"><?php _e( 'Link To', 'wpuf-comment' ); ?></th>
                    <th class="col-link"><?php _e( 'Rating', 'wpuf-comment' ); ?></th>
                </tr>

                <?php echo $this->status( $post_type ); ?>

            </table>

            <select name="comment_status">
                <?php $this->bulk_option(); ?>
            </select>

            <?php wp_nonce_field('wpuf_comment_nonce', 'wpuf_nonce'); ?>

            <input type="submit" value="<?php _e( 'Submit', 'wpuf-comment' ); ?>" class="btn btn-primary" name="comt_stat_sub">
        </form>

            

        <script type="text/template" id="wpuf-edit-comment-row">
            <tr class="wpuf-comment-edit-row">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                <label for="author"><?php _e( 'Name', 'wpuf-comment' ); ?></label>
                                <input type="text" class="wpuf-cmt-author" value="<%= author %>" name="newcomment_author">
                            </td>
                            <td>
                                <label for="author-email"><?php _e( 'E-mail', 'wpuf-comment' ); ?></label>
                                <input type="text" class="wpuf-cmt-author-email" value="<%= email %>" name="newcomment_author_email">
                            </td>
                            <td>
                                <label for="author-url"><?php _e( 'URL', 'wpuf-comment' ); ?></label>
                                <input type="text" class="wpuf-cmt-author-url" value="<%= url %>" name="newcomment_author_url">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <textarea class="wpuf-cmt-body" name="newcomment_body" cols="50" rows="8"><%= body %></textarea>
                                <input type="hidden" class="wpuf-cmt-id" value="<%= id %>" >
                                <input type="hidden" class="wpuf-cmt-status" value="<%= status %>" >
                                <input type="hidden" class="wpuf-cmt-post-type" value="<?php echo $post_type; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><button class="wpuf-cmt-close-form"><?php _e( 'Close', 'wpuf-comment' ); ?></button></td>
                            <td colspan="1"><button class="wpuf-cmt-submit-form button"><?php _e( 'Update Comment', 'wpuf-comment' ); ?></button></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </script>

        <?php
        echo $this->pagination( $post_type );
    }

    /**
     * Pagination
     * @param string $post_type
     * @return string
     */
    function pagination( $post_type ) {
        global $wpdb, $current_user;
        $status = $this->page_status();

        if ( $status == '1' ) {
            $query = "$wpdb->comments.comment_approved IN ('1','0') AND";
        } else {
            $query = "$wpdb->comments.comment_approved='$status' AND";
        }

        $total = $wpdb->get_var(
                "SELECT COUNT(*)
            FROM $wpdb->comments, $wpdb->posts
            WHERE   $wpdb->posts.post_author='$current_user->ID' AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
            $query
            $wpdb->posts.post_type='$post_type'"
        );

        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $num_of_pages = ceil( $total / $this->limit );

        $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'aag' ),
            'next_text' => __( '&raquo;', 'aag' ),
            'total' => $num_of_pages,
            'current' => $pagenum
        ) );

        if ( $page_links ) {
            return '<div class="wpuf-pagination">' . $page_links . '</div>';
        }
    }

    /**
     * bulk_option()
     *
     * When you change comment status the bulk action option will change
     */
    function bulk_option() {
        $comment_status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : 'all';

        if ( $comment_status == 'hold' ) {
            ?>
            <option value="none"><?php _e( '-None-', 'wpuf-comment' ); ?></option>
            <option value="approve"><?php _e( 'Mark Approve', 'wpuf-comment' ); ?></option>
            <option value="spam"><?php _e( 'Mark Spam', 'wpuf-comment' ); ?></option>
            <option value="trash"><?php _e( 'Mark Trash', 'wpuf-comment' ); ?></option>
        <?php } else if ( $comment_status == 'spam' ) { ?>
            <option value="none"><?php _e( '-None-', 'wpuf-comment' ); ?></option>
            <option value="approve"><?php _e( 'Mark Not Spam', 'wpuf-comment' ); ?></option>
            <option value="delete"><?php _e( 'Delete permanently', 'wpuf-comment' ); ?></option>
        <?php } else if ( $comment_status == 'trash' ) { ?>
            <option value="none"><?php _e( '-None-', 'wpuf-comment' ); ?></option>
            <option value="approve"><?php _e( 'Resore', 'wpuf-comment' ); ?></option>
            <option value="delete"><?php _e( 'Delete permanently', 'wpuf-comment' ); ?></option>
        <?php } else { ?>
            <option value="none"><?php _e( '-None-', 'wpuf-comment' ); ?></option>
            <option value="hold"><?php _e( 'Mark Pending', 'wpuf-comment' ); ?></option>
            <option value="spam"><?php _e( 'Mark Spam', 'wpuf-comment' ); ?></option>
            <option value="trash"><?php _e( 'Mark Trash', 'wpuf-comment' ); ?></option>
            <?php
        }
    }

    /**
     * return current page status. Is it panding, spam, trash or all
     * @return string
     */
    function page_status() {
        $status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';

        if ( $status == 'hold' ) {
            return '0';
        } else if ( $status == 'spam' ) {
            return 'spam';
        } else if ( $status == 'trash' ) {
            return 'trash';
        } else {
            return '1';
        }
    }

    function get_comment_status( $status ) {
        switch ($status) {
            case '1':
                return 'approved';

            case '0':
                return 'pending';

            default:
                return $status;
        }
    }

    /**
     * return all comments by comments status
     * @global object $current_user
     * @global object $wpdb
     * @param string $post_type
     * @return string
     */
    function status( $post_type ) {
        global $current_user, $wpdb;

        $status = $this->page_status();

        $limit = $this->limit;
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $offset = ( $pagenum - 1 ) * $limit;


        if ( $status == '1' ) {
            $query = "c.comment_approved IN ('1','0') AND";
        } else {
            $query = "c.comment_approved='$status' AND";
        }

        $comments = $wpdb->get_results(
            "SELECT c.comment_content, c.comment_ID, c.comment_author,
                c.comment_author_email, c.comment_author_url,
                p.post_title, p.guid, c.comment_post_ID, c.comment_approved
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author='$current_user->ID' AND
                p.post_status='publish' AND
                c.comment_post_ID=p.ID AND
                $query
                p.post_type='$post_type'
            LIMIT $offset,$limit"
        );

        if ( count( $comments ) == 0 ) {
            return '<tr><td colspan="4">' . __( 'No Result Found', 'wpuf-comment' ) . '</td></tr>';
        }

        foreach ($comments as $comment) {
            $this->render_row( $comment, $post_type );
        }
    }

    function render_row( $comment, $post_type ) {
        // var_dump( $comment );

        $comment_date = get_comment_date( 'Y/m/d \a\t g:i a', $comment->comment_ID );
        $comment_author_img = get_avatar( $comment->comment_author_email, 32 );
        $eidt_post_url = get_edit_post_link( $comment->comment_post_ID );
        $permalink = get_comment_link( $comment );
        ?>
        <tr class="<?php echo $this->get_comment_status( $comment->comment_approved ); ?>">
            <td class="col-check"><input class="wpuf-check-col" type="checkbox" name="commentid[]" value="<?php echo $comment->comment_ID; ?>"></td>
            <td class="col-author">
                <div class="wpuf-author-img"><?php echo $comment_author_img; ?></div> <?php echo $comment->comment_author; ?> <br>
                <a href="<?php echo $comment->comment_author_url; ?>"><?php echo $comment->comment_author_url; ?></a><br>
                <?php echo $comment->comment_author_email; ?>
            </td>
            <td class="col-content"><div class="wpuf-comments-subdate">
                <?php _e( 'Submitted on ', 'wpuf-comment' );
                echo $comment_date; ?> </div>

                <div class="wpuf-comments-content"><?php echo $comment->comment_content; ?></div>

                <ul class="wpuf-cmt-row-actions">
                    <?php $this->row_action( $comment, $post_type ); ?>
                </ul>
            </td>
            <td class="col-link">
                <a href="<?php echo $permalink; ?>"><?php _e( 'View Comment', 'wpuf-comment' ); ?></a>

                <div style="display:none">
                    <div class="wpuf-cmt-hid-email"><?php echo esc_attr( $comment->comment_author_email ); ?></div>
                    <div class="wpuf-cmt-hid-author"><?php echo esc_attr( $comment->comment_author ); ?></div>
                    <div class="wpuf-cmt-hid-url"><?php echo esc_attr( $comment->comment_author_url ); ?></div>
                    <div class="wpuf-cmt-hid-id"><?php echo esc_attr( $comment->comment_ID ); ?></div>
                    <div class="wpuf-cmt-hid-status"><?php echo esc_attr( $comment->comment_approved ); ?></div>
                    <textarea class="wpuf-cmt-hid-body"><?php echo esc_textarea( $comment->comment_content ); ?></textarea>
                </div>
            </td>
            <td>
            <?php if ( get_option('woocommerce_enable_review_rating') == 'yes' ) : ?>
                <?php $rating =  intval( get_comment_meta( $comment->comment_ID, 'rating', true ) ); ?>
            
            <div class="dokan-rating">
                <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf(__( 'Rated %d out of 5', 'woocommerce' ), $rating) ?>">
                    <span style="width:<?php echo ( intval( get_comment_meta( $comment->comment_ID, 'rating', true ) ) / 5 ) * 100; ?>%"><strong itemprop="ratingValue"><?php echo $rating; ?></strong> <?php _e( 'out of 5', 'woocommerce' ); ?></span>
                </div>
            </div>

            <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    function row_action( $comment, $post_type ) {
        $page_status = $this->page_status();

        if ( $page_status == '0' ) {
            ?>

            <li><a href="#" data-curr_page="<?php echo $page_status; ?>"  data-post_type="<?php echo $post_type; ?>" data-page_status="0" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="1" class="wpuf-cmt-action"><?php _e( 'Approve', 'wpuf-comment' ); ?></a></li>
            <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="0" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="spam" class="wpuf-cmt-action"><?php _e( 'Spam', 'wpuf-comment' ); ?></a></li>
            <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="0" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="trash" class="wpuf-cmt-action"><?php _e( 'Trash', 'wpuf-comment' ); ?></a></li>

            <?php } else if ( $page_status == 'spam' ) { ?>

                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="spam" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="1" class="wpuf-cmt-action"><?php _e( 'Not Spam', 'wpuf-comment' ); ?></a></li>
                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="spam" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="delete" class="wpuf-cmt-action"><?php _e( 'Delete Permanently', 'wpuf-comment' ); ?></a></li>

            <?php } else if ( $page_status == 'trash' ) { ?>
                
                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="trash" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="1" class="wpuf-cmt-action"><?php _e( 'Restore', 'wpuf-comment' ); ?></a></li>
                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="trash" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="delete" class="wpuf-cmt-action"><?php _e( 'Delete Permanently', 'wpuf-comment' ); ?></a></li>

            <?php } else { ?>

                <?php if ( $this->get_comment_status( $comment->comment_approved ) == 'approved' ) { ?>
                    <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="1" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="0" class="wpuf-cmt-action"><?php _e( 'Unapprove', 'wpuf-comment' ); ?></a></li>
                <?php } else { ?>
                    <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="1" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="1" class="wpuf-cmt-action"><?php _e( 'Approve', 'wpuf-comment' ); ?></a></li>
                <?php } ?>

                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="1" class="wpuf-cmt-edit"><?php _e( 'Quick Edit', 'wpuf-comment' ); ?></a></li>
                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="1" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="spam" class="wpuf-cmt-action"><?php _e( 'Spam', 'wpuf-comment' ); ?></a></li>
                <li><a href="#" data-curr_page="<?php echo $page_status; ?>" data-post_type="<?php echo $post_type; ?>" data-page_status="1" data-comment_id="<?php echo $comment->comment_ID; ?>" data-cmt_status="trash" class="wpuf-cmt-action"><?php _e( 'Trash', 'wpuf-comment' ); ?></a></li>
            <?php
        }
    }

    function ajax_update_comment() {

        if ( !is_user_logged_in() ) {
            wp_send_json_error();
        }

        if ( !wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) ) {
            wp_send_json_error();
        }

        $comment_id = absint( $_POST['comment_id'] );
        $commentarr = array(
            'comment_ID' => $comment_id,
            'comment_content' => $_POST['content'],
            'comment_author' => $_POST['author'],
            'comment_author_email' => $_POST['email'],
            'comment_author_url' => $_POST['url'],
            'comment_approved' => $_POST['status'],
        );

        wp_update_comment( $commentarr );
        $comment = get_comment( $comment_id );

        ob_start();
        $this->render_row( $comment, $_POST['post_type'] );
        $html = ob_get_clean();

        wp_send_json_success( $html );
    }

    /**
     * Process bulk action
     */
    function handle_status() {
        
        if ( !isset( $_POST['comt_stat_sub'] ) ) {
            return;
        }

        if ( !wp_verify_nonce($_POST['wpuf_nonce'], 'wpuf_comment_nonce') && !is_user_logged_in() ) {
            return;
        }

        $action = $_POST['comment_status'];

        if ( !count( $_POST['commentid'] ) ) {
            return;
        }

        foreach ($_POST['commentid'] as $commentid) {
            if ( $action == 'delete' ) {
                wp_delete_comment( $commentid );
            } else {
                wp_set_comment_status( $commentid, $action );
            }
        }

        $current_status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';
        $redirect_to = add_query_arg( array('comment_status' => $current_status), get_permalink() );
        wp_redirect( $redirect_to );

    }

    /**
     * Show menu
     *
     * @param string $post_type
     */
    function wpuf_comments_menu( $post_type ) {
        $url = get_permalink();
        $pending = isset( $this->pending ) ? $this->pending : 0;
        $spam = isset( $this->spam ) ? $this->spam : 0;
        $trash = isset( $this->trash ) ? $this->trash : 0;
        ?>
        <div id="wpuf-comments_menu">
            <ul>
                <li><a href="<?php echo $url; ?>"><?php _e( 'All', 'wpuf' ); ?></a></li>
                <li>
                    <a href="<?php echo add_query_arg( array('comment_status' => 'hold'), $url ); ?>"><?php _e( 'Pending (', 'wpuf' ); ?><span class="comments-menu-pending"><?php echo $pending; ?></span><?php _e( ')', 'wpuf' ); ?></a>
                </li>
                <li>
                    <a href="<?php echo add_query_arg( array('comment_status' => 'spam'), $url ); ?>"><?php _e( 'Spam (', 'wpuf' ); ?><span class="comments-menu-spam"><?php echo $spam; ?></span><?php _e( ')', 'wpuf' ); ?></a>
                </li>
                <li>
                    <a href="<?php echo add_query_arg( array('comment_status' => 'trash'), $url ); ?>"><?php _e( 'Trash (', 'wpuf' ); ?><span class="comments-menu-trash"><?php echo $trash; ?></span><?php _e( ')', 'wpuf' ); ?></a>
                </li>
            </ul>
        </div>
        <?php
    }

    /**
     * count all, pending, spam, trash
     *
     * @param init, string $status
     * @parm string $post_type
     */
    function count_status( $post_type, $status ) {
        global $wpdb, $current_user;

        return $totalcomments = $wpdb->get_var(
            "SELECT count($wpdb->comments.comment_ID)
            FROM $wpdb->comments, $wpdb->posts
            WHERE $wpdb->posts.post_author=$current_user->ID AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=wp_posts.ID AND
            $wpdb->comments.comment_approved='$status' AND
            $wpdb->posts.post_type='$post_type'"
        );
    }

}


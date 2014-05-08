<?php
/**
 * User profile related tasks for wp-admin
 *
 * @package Dokan
 */
class Dokan_Admin_User_Profile {

    public function __construct() {
        add_action( 'show_user_profile', array( $this, 'add_meta_fields' ), 20 );
        add_action( 'edit_user_profile', array( $this, 'add_meta_fields' ), 20 );

        add_action( 'personal_options_update', array( $this, 'save_meta_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_meta_fields' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    function enqueue_scripts( $page ) {
        if ( in_array( $page, array( 'profile.php', 'user-edit.php' )) ) {
            wp_enqueue_media();
        }
    }

    /**
     * Add fields to user profile
     *
     * @param WP_User $user
     * @return void|false
     */
    function add_meta_fields( $user ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( !user_can( $user, 'dokandar' ) ) {
            return;
        }

        $selling = get_user_meta( $user->ID, 'dokan_enable_selling', true );
        $publishing = get_user_meta( $user->ID, 'dokan_publishing', true );
        $store_settings = dokan_get_store_info( $user->ID );
        $banner = isset( $store_settings['banner'] ) ? absint( $store_settings['banner'] ) : 0;
        $seller_percentage = get_user_meta( $user->ID, 'dokan_seller_percentage', true );

        $fb = isset( $store_settings['social']['fb'] ) ? esc_url( $store_settings['social']['fb'] ) : '';
        $twitter = isset( $store_settings['social']['twitter'] ) ? esc_url( $store_settings['social']['twitter'] ) : '';
        $gplus = isset( $store_settings['social']['gplus'] ) ? esc_url ( $store_settings['social']['gplus'] ) : '';
        $linkedin = isset( $store_settings['social']['linkedin'] ) ? esc_url( $store_settings['social']['linkedin'] ) : '';
        $youtube = isset( $store_settings['social']['youtube'] ) ? esc_url( $store_settings['social']['youtube'] ) : '';

        ?>
        <h3><?php _e( 'Dokan Options', 'dokan' ); ?></h3>

        <table class="form-table">
            <tbody>
                <tr>
                    <th><?php _e( 'Banner', 'dokan' ); ?></th>
                    <td>
                        <div class="dokan-banner">
                            <div class="image-wrap<?php echo $banner ? '' : ' dokan-hide'; ?>">
                                <?php $banner_url = $banner ? wp_get_attachment_url( $banner ) : ''; ?>
                                <input type="hidden" class="dokan-file-field" value="<?php echo $banner; ?>" name="dokan_banner">
                                <img class="dokan-banner-img" src="<?php echo esc_url( $banner_url ); ?>">

                                <a class="close dokan-remove-banner-image">&times;</a>
                            </div>

                            <div class="button-area<?php echo $banner ? ' dokan-hide' : ''; ?>">
                                <a href="#" class="dokan-banner-drag button button-primary"><?php _e( 'Upload banner', 'dokan' ); ?></a>
                                <p class="description"><?php _e( '(Upload a banner for your store. Banner size is (825x300) pixel. )', 'dokan' ); ?></p>
                            </div>
                        </div> <!-- .dokan-banner -->
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Store name', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_store_name" class="regular-text" value="<?php echo esc_attr( $store_settings['store_name'] ); ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Address', 'dokan' ); ?></th>
                    <td>
                        <textarea name="dokan_store_address" rows="4" cols="30"><?php echo esc_textarea( $store_settings['address'] ); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Phone', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_store_phone" class="regular-text" value="<?php echo esc_attr( $store_settings['phone'] ); ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Twitter Profile', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_social[twitter]" class="regular-text" value="<?php echo $twitter; ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Facebook Profile', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_social[fb]" class="regular-text" value="<?php echo $fb; ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Google Plus Profile', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_social[gplus]" class="regular-text" value="<?php echo $gplus; ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Linkedin Profile', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_social[linkedin]" class="regular-text" value="<?php echo $linkedin; ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'YouTube Profile', 'dokan' ); ?></th>
                    <td>
                        <input type="text" name="dokan_social[youtube]" class="regular-text" value="<?php echo $youtube; ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Selling', 'dokan' ); ?></th>
                    <td>
                        <label for="dokan_enable_selling">
                            <input type="hidden" name="dokan_enable_selling" value="no">
                            <input name="dokan_enable_selling" type="checkbox" id="dokan_enable_selling" value="yes" <?php checked( $selling, 'yes' ); ?> />
                            <?php _e( 'Enable Selling', 'dokan' ); ?>
                        </label>

                        <p class="description"><?php _e( 'Enable or disable product selling capability', 'dokan' ) ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Publishing', 'dokan' ); ?></th>
                    <td>
                        <label for="dokan_publish">
                            <input type="hidden" name="dokan_publish" value="no">
                            <input name="dokan_publish" type="checkbox" id="dokan_publish" value="yes" <?php checked( $publishing, 'yes' ); ?> />
                            <?php _e( 'Publish product directly', 'dokan' ); ?>
                        </label>

                        <p class="description"><?php _e( 'Instead going pending, products will be published directly', 'dokan' ) ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Seller Percentage', 'dokan' ); ?></th>
                    <td>
                        <input type="text" class="small-text" name="dokan_seller_percentage" value="<?php echo esc_attr( $seller_percentage ); ?>">

                        <p class="description"><?php _e( 'How much amount (%) will get from each order', 'dokan' ) ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <style type="text/css">
        .dokan-hide { display: none; }
        .button-area { padding-top: 100px; }
        .dokan-banner {
            border: 4px dashed #d8d8d8;
            height: 255px;
            margin: 0;
            overflow: hidden;
            position: relative;
            text-align: center;
            max-width: 700px;
        }
        .dokan-banner img { max-width:100%; }
        .dokan-banner .dokan-remove-banner-image {
            position:absolute;
            width:100%;
            height:270px;
            background:#000;
            top:0;
            left:0;
            opacity:.7;
            font-size:100px;
            color:#f00;
            padding-top:70px;
            display:none
        }
        .dokan-banner:hover .dokan-remove-banner-image {
            display:block;
            cursor: pointer;
        }
        </style>

        <script type="text/javascript">
        jQuery(function($){
            var Dokan_Settings = {

                init: function() {
                    $('a.dokan-banner-drag').on('click', this.imageUpload);
                    $('a.dokan-remove-banner-image').on('click', this.removeBanner);
                },

                imageUpload: function(e) {
                    e.preventDefault();

                    var file_frame,
                        self = $(this);

                    if ( file_frame ) {
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: jQuery( this ).data( 'uploader_title' ),
                        button: {
                            text: jQuery( this ).data( 'uploader_button_text' )
                        },
                        multiple: false
                    });

                    file_frame.on( 'select', function() {
                        var attachment = file_frame.state().get('selection').first().toJSON();

                        var wrap = self.closest('.dokan-banner');
                        wrap.find('input.dokan-file-field').val(attachment.id);
                        wrap.find('img.dokan-banner-img').attr('src', attachment.url);
                        $('.image-wrap', wrap).removeClass('dokan-hide');

                        $('.button-area').addClass('dokan-hide');
                    });

                    file_frame.open();

                },

                removeBanner: function(e) {
                    e.preventDefault();

                    var self = $(this);
                    var wrap = self.closest('.image-wrap');
                    var instruction = wrap.siblings('.button-area');

                    wrap.find('input.dokan-file-field').val('0');
                    wrap.addClass('dokan-hide');
                    instruction.removeClass('dokan-hide');
                },
            };

            Dokan_Settings.init();
        });
        </script>
        <?php
    }

    /**
     * Save user data
     *
     * @param int $user_id
     * @return void
     */
    function save_meta_fields( $user_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! isset( $_POST['dokan_enable_selling'] ) ) {
            return;
        }

        $selling = sanitize_text_field( $_POST['dokan_enable_selling'] );
        $publishing = sanitize_text_field( $_POST['dokan_publish'] );
        $percentage = floatval( $_POST['dokan_seller_percentage'] );
        $store_settings = dokan_get_store_info( $user_id );
        $social = $_POST['dokan_social'];

        $store_settings['banner'] = intval( $_POST['dokan_banner'] );
        $store_settings['store_name'] = sanitize_text_field( $_POST['dokan_store_name'] );
        $store_settings['address'] = wp_kses_post( $_POST['dokan_store_address'] );
        $store_settings['phone'] = sanitize_text_field( $_POST['dokan_store_phone'] );
        $store_settings['social'] = array(
            'fb' => filter_var( $social['fb'], FILTER_VALIDATE_URL ),
            'gplus' => filter_var( $social['gplus'], FILTER_VALIDATE_URL ),
            'twitter' => filter_var( $social['twitter'], FILTER_VALIDATE_URL ),
            'linkedin' => filter_var( $social['linkedin'], FILTER_VALIDATE_URL ),
            'youtube' => filter_var( $social['youtube'], FILTER_VALIDATE_URL ),
        );

        update_user_meta( $user_id, 'dokan_profile_settings', $store_settings );
        update_user_meta( $user_id, 'dokan_enable_selling', $selling );
        update_user_meta( $user_id, 'dokan_publishing', $publishing );
        update_user_meta( $user_id, 'dokan_seller_percentage', $percentage );
    }
}
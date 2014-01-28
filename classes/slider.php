<?php

/**
 * WeDevs Slider Engine
 *
 * @package WeDevs Framework
 */
class Dokan_Slider {

    private static $instance = null;

    private $post_type = 'dokan_slider';
    private $slider_meta = array(
        'slider_effect', 'slider_speed', 'slider_pagination',
        'direction_nav', 'slide_direction', 'touch', 'randomize', 'pauseOnHover', 'pausePlay');
    private $slide_default = array(
        'slide_type' => 'text-image',
        'slide_title' => '',
        'slide_content' => '',
        'slide_image' => '',
        'slide_video' => '',
        'slide_link' => '',
        'slide_link_open' => '_self'
    );

    private function __construct() {
        $this->actions();
    }

    public static function get_instance() {
        if ( !self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    function actions() {
        add_action( 'init', array($this, 'post_types') );
        add_action( 'do_meta_boxes', array($this, 'do_metaboxes' ) );
        add_action( 'admin_head', array($this, 'enqueue_scripts' ) );

        add_action( 'save_post', array($this, 'save_meta'), 10, 2 );
    }

	function do_metaboxes() {
        add_meta_box( 'slider-meta-box', __('Slider Options', 'wedevs'), array($this, 'meta_boxes'), $this->post_type );
	}

    function enqueue_scripts() {
        global $post;

        if( !$post ) {
            return;
        }

        if( $post->post_type == $this->post_type ) {
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_script( 'thickbox' );

            wp_enqueue_style( 'thickbox' );
        }
    }

    function post_types() {
        register_post_type( $this->post_type, array(
            'label' => __( 'Slider', 'dokan' ),
            'description' => '',
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => array('slug' => ''),
            'query_var' => false,
            'supports' => array('title'),
            'labels' => array(
                'name' => __( 'Slider', 'dokan' ),
                'singular_name' => __( 'Slider', 'dokan' ),
                'menu_name' => __( 'Dokan Slider', 'dokan' ),
                'add_new' => __( 'Add Slider', 'dokan' ),
                'add_new_item' => __( 'Add New Slider', 'dokan' ),
                'edit' => __( 'Edit', 'dokan' ),
                'edit_item' => __( 'Edit Slider', 'dokan' ),
                'new_item' => __( 'New Slider', 'dokan' ),
                'view' => __( 'View Slider', 'dokan' ),
                'view_item' => __( 'View Slider', 'dokan' ),
                'search_items' => __( 'Search Slider', 'dokan' ),
                'not_found' => __( 'No Slider Found', 'dokan' ),
                'not_found_in_trash' => __( 'No Slider found in trash', 'dokan' ),
                'parent' => __( 'Parent Slider', 'dokan' )
            ),
        ) );
    }

    function meta_boxes() {
		global $post;

        $metas = array();
        foreach ($this->slider_meta as $meta) {
            $metas[$meta] = get_post_meta( $post->ID, $meta, true );
        }
        extract( $metas );

        $slider_speed = ($slider_speed == '') ? '7000' : $slider_speed;
		?>
        <input type="hidden" name="wedevs-slider" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
		<div id="slider-table">
            <table class="form-table">
                <tr>
                    <td><?php _e( 'Effect', 'dokan' ); ?></td>
                    <td>
                        <select name="slider_effect" id="slider-effect">
                            <option value="fade"<?php selected( $slider_effect, 'fade' ); ?>>fade</option>
                            <option value="slide"<?php selected( $slider_effect, 'slide' ); ?>>slide</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Slide Speed', 'dokan' ); ?></td>
                    <td><input type="text" value="<?php echo esc_attr( $slider_speed ); ?>" name="slider_speed"></td>
                </tr>
                <tr>
                    <td><?php _e( 'Show Pagination', 'dokan' ); ?></td>
                    <td>
                        <select name="slider_pagination">
                            <option value="true"<?php selected( $slider_pagination, 'true' ); ?>><?php _e( 'Show', 'dokan' ); ?></option>
                            <option value="false"<?php selected( $slider_pagination, 'false' ); ?>><?php _e( 'Hide', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Direction Navigation', 'dokan' ); ?></td>
                    <td>
                        <select name="direction_nav">
                            <option value="true"<?php selected( $direction_nav, 'true' ); ?>><?php _e( 'Show', 'dokan' ); ?></option>
                            <option value="false"<?php selected( $direction_nav, 'false' ); ?>><?php _e( 'Hide', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Sliding Direction', 'dokan' ); ?></td>
                    <td>
                        <select name="slide_direction">
                            <option value="horizontal"<?php selected( $slide_direction, 'horizontal' ); ?>><?php _e( 'horizontal', 'dokan' ); ?></option>
                            <option value="vertical"<?php selected( $slide_direction, 'vertical' ); ?>><?php _e( 'vertical', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Touch Swipe', 'dokan' ); ?></td>
                    <td>
                        <select name="touch">
                            <option value="true"<?php selected( $touch, 'true' ); ?>><?php _e( 'Yes', 'dokan' ); ?></option>
                            <option value="false"<?php selected( $touch, 'false' ); ?>><?php _e( 'No', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Randomize', 'dokan' ); ?></td>
                    <td>
                        <select name="randomize">
                            <option value="false"<?php selected( $randomize, 'false' ); ?>><?php _e( 'No', 'dokan' ); ?></option>
                            <option value="true"<?php selected( $randomize, 'true' ); ?>><?php _e( 'Yes', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Pause on Hover', 'dokan' ); ?></td>
                    <td>
                        <select name="pauseOnHover">
                            <option value="false"<?php selected( $pauseOnHover, 'false' ); ?>><?php _e( 'No', 'dokan' ); ?></option>
                            <option value="true"<?php selected( $pauseOnHover, 'true' ); ?>><?php _e( 'Yes', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Pause/Play', 'dokan' ); ?></td>
                    <td>
                        <select name="pausePlay">
                            <option value="false"<?php selected( $pausePlay, 'false' ); ?>><?php _e( 'No', 'dokan' ); ?></option>
                            <option value="true"<?php selected( $pausePlay, 'true' ); ?>><?php _e( 'Yes', 'dokan' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <p><a href="#" class="button add-slide"><?php _e( 'Add Slide', 'dokan' ); ?></a></p>

            <ul class="slide-holder">
                <?php
                $slides = get_post_meta( $post->ID, 'slide_detail' );
                if ($slides) {
                    foreach ($slides as $slide) {
                        $this->slide_html( $slide );
                    }
                }
                ?>
            </ul>
		</div>
        <script type="text/tmpl" id="slide-template">
            <?php $this->slide_html(); ?>
        </script>

        <script type="text/javascript">
            (function($){
                var WeDevs_Slider = {
                    init: function () {

                        $('#slider-table').on('change', 'select.slide_type', this.showHideField);
                        $('#slider-table').on('click', 'a.add-slide', this.addSlide);
                        $('#slider-table').on('click', '.slide-hndle', this.toggleSlide);
                        $('#slider-table').on('click', '.submitdelete', this.removeSlide);
                        $('#slider-table').on('click', 'a.image_upload', WeDevs_Admin.imageUpload);
                        $('#slider-table').on('click', 'a.remove-image', WeDevs_Admin.removeImage);
                    },

                    showHideField: function() {
                        var self = $(this),
                            table = self.parents('.form-table'),
                            val = $(this).val(),
                            title = table.find('tr.slide-title'),
                            slide_content = table.find('tr.slide-content'),
                            slide_image = table.find('tr.slide-image'),
                            video_link = table.find('tr.video-link');

                        //show all
                        title.show();
                        slide_content.show();
                        slide_image.show();
                        video_link.show();

                        if( val == 'text-image' || val == 'image-text' ) {
                            video_link.hide();
                        } else if( val == 'text-video' || val == 'video-text' ) {
                            slide_image.hide();
                        } else if( val == 'image' ) {
                            title.hide();
                            slide_content.hide();
                            video_link.hide();
                        } else if( val == 'video' ) {
                            title.hide();
                            slide_content.hide();
                            slide_image.hide();
                        } else if( val == 'text' ) {
                            slide_image.hide();
                            video_link.hide();
                        }
                    },

                    addSlide: function (e) {
                        e.preventDefault();

                        var tmpl = $('#slide-template').html();

                        $('#slider-table ul.slide-holder').append(tmpl);
                    },

                    removeSlide: function (e) {
                        e.preventDefault();

                        $(this).parents('.slide-table').parent().remove();
                    },

                    toggleSlide: function (e) {
                        e.preventDefault();
                        $(this).next('.form-table').toggle();
                    }
                };

                $(function() {
                    WeDevs_Slider.init();
                });

            })(jQuery);
        </script>
        <style type="text/css">
            .slide-table {
                background: #FAFAFA;
                margin-bottom: 5px;
                border-radius: 5px;
                border: 1px solid #dfdfdf;
            }

            .slide-table .form-table td {
                vertical-align: top;
            }

            .slide-table .submitbox {
                padding: 5px;
                margin: 5px;
            }

            .image_placeholder {
                width: 300px;
            }

            .image_placeholder img{
                background: #ffffff;
                border: 1px solid #ccc;
                padding: 5px;
                border-radius: 5px;
                width: 100%;
                margin-top: 5px;
            }

            .image_placeholder a.remove-image span{
                background: url('<?php echo ''; ?>/images/ico-delete.png') no-repeat;
                width: 16px;
                height: 16px;
                display: inline-block;
                text-indent: -9999px;
            }

        </style>
		<?php
	}

    function slide_html( $args = array() ) {

        $args = wp_parse_args( $args, $this->slide_default );
        extract( $args );

        $slide_title_css = in_array( $slide_type, array('image', 'video') ) ? 'none' : 'table-row';
        $slide_content_css = in_array( $slide_type, array('image', 'video') ) ? 'none' : 'table-row';
        $slide_image_css = in_array( $slide_type, array('video', 'text', 'text-video', 'video-text') ) ? 'none' : 'table-row';
        $slide_video_css = in_array( $slide_type, array('video', 'text-video', 'video-text') ) ? 'table-row' : 'none';
        ?>
        <li>
            <div class="slide-table">
                <h3 class="slide-hndle"><span><?php _e( 'Slide', 'wedevs' ); ?></span></h3>
                <table class="form-table">
                    <tr class="slider_type_select">
                        <td><?php _e( 'Slide Type', 'wedevs' ); ?></td>
                        <td>
                            <select class="slide_type" name="slide_type[]">
                                <option value="text-image"<?php selected( $slide_type, 'text-image' ); ?>><?php _e( 'Text + Image', 'wedevs' ); ?></option>
                                <option value="image-text"<?php selected( $slide_type, 'image-text' ); ?>><?php _e( 'Image + Text', 'wedevs' ); ?></option>
                                <option value="text-video"<?php selected( $slide_type, 'text-video' ); ?>><?php _e( 'Text + Video', 'wedevs' ); ?></option>
                                <option value="video-text"<?php selected( $slide_type, 'video-text' ); ?>><?php _e( 'Video + Text', 'wedevs' ); ?></option>
                                <option value="image"<?php selected( $slide_type, 'image' ); ?>><?php _e( 'Full Image', 'wedevs' ); ?></option>
                                <option value="video"<?php selected( $slide_type, 'video' ); ?>><?php _e( 'Full Video', 'wedevs' ); ?></option>
                                <option value="text"<?php selected( $slide_type, 'text' ); ?>><?php _e( 'Text Only', 'wedevs' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="slide-title" style="display: <?php echo $slide_title_css; ?>">
                        <td><?php _e( 'Title Text', 'wedevs' ); ?></td>
                        <td>
                            <input type="text" class="regular-text" name="slide_title[]" value="<?php echo esc_attr( $slide_title ); ?>">
                        </td>
                    </tr>
                    <tr class="slide-content" style="display: <?php echo $slide_content_css; ?>">
                        <td><?php _e( 'Detail Text', 'wedevs' ); ?></td>
                        <td>
                            <textarea name="slide_content[]" rows="5" cols="55"><?php echo esc_textarea( $slide_content ); ?></textarea>
                        </td>
                    </tr>
                    <tr class="slide-image" style="display: <?php echo $slide_image_css; ?>">
                        <td><?php _e( 'Slide Image', 'wedevs' ); ?></td>
                        <td>
                            <input type="text" class="regular-text image_url" name="slide_image[]" value="<?php echo esc_url( $slide_image ) ?>" />
                            <a href="#" class="image_upload button">Upload Image</a>
                            <div class="image_placeholder">
                                <?php if ($slide_image != '') { ?>
                                    <img src="<?php echo esc_url( $slide_image ); ?>" alt="image" /><a href="#" class="remove-image"><span>Remove</span></a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr class="video-link" style="display: <?php echo $slide_video_css; ?>">
                        <td><?php _e( 'Video Embed Code', 'wedevs' ); ?></td>
                        <td>
                            <textarea name="slide_video[]" rows="3" cols="55"><?php echo esc_textarea( $slide_video ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Slide Link Url', 'wedevs' ); ?></td>
                        <td>
                            <input type="text" class="regular-text" value="<?php echo esc_url( $slide_link ); ?>" placeholder="http://example.com" name="slide_link[]" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Open Link', 'wedevs' ); ?></td>
                        <td>
                            <select name="slide_link_open[]">
                                <option value="_self"<?php selected( $slide_link_open, '_self'); ?>>Open link in same window</option>
                                <option value="_blank"<?php selected( $slide_link_open, '_blank'); ?>>Open link in new window</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="submitbox">
                            <a href="#" class="submitdelete">Remove</a>
                        </td>
                    </tr>
                </table>
            </div>
        </li>
        <?php
    }

    /**
     * Save slide meta fields
     *
     * @param int $post_id
     * @param object $post
     */
    function save_meta( $post_id, $post ) {
        /* Verify the nonce before proceeding. */
        if ( !isset( $_POST['wedevs-slider'] ) || !wp_verify_nonce( $_POST['wedevs-slider'], basename( __FILE__ ) ) )
            return $post_id;

        /* Get the post type object. */
        $post_type = get_post_type_object( $post->post_type );

        /* Check if the current user has permission to edit the post. */
        if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
            return $post_id;

        $posted = $_POST;
        // var_dump($_POST);
        // die();

        foreach ($this->slider_meta as $meta) {
            update_post_meta( $post_id, $meta, trim( $posted[$meta] ) );
        }

        //if any slides not added, bail out
        if( !isset( $_POST['slide_type'] ) ) {
            return $post_id;
        }

        //delete all previous meta field values
        delete_post_meta( $post_id, 'slide_detail' );

        foreach ($posted['slide_type'] as $key => $value) {
            $data = array(
                'slide_type' => $posted['slide_type'][$key],
                'slide_title' => trim( $posted['slide_title'][$key] ),
                'slide_content' => trim($posted['slide_content'][$key]),
                'slide_image' => trim( $posted['slide_image'][$key] ),
                'slide_video' => $posted['slide_video'][$key],
                'slide_link' => trim( $posted['slide_link'][$key] ),
                'slide_link_open' => $posted['slide_link_open'][$key],
            );

            add_post_meta( $post_id, 'slide_detail', $data);
        }
    }

    public function get_slider( $slider_id, $width_text = 'col-md-5', $width_image = 'col-md-7' ) {
        $metas = array();
        foreach ($this->slider_meta as $meta) {
            $metas[$meta] = get_post_meta( $slider_id, $meta, true );
        }
        extract( $metas );

        $slides = get_post_meta( $slider_id, 'slide_detail' );

        if( $slides ) {

            printf( '<div class="flexslider" id="flexslider-%d">', $slider_id );
            printf( '<ul class="slides">' );

            foreach ($slides as $slide) {
                echo "<li>";
                echo '<div class="row">';

                extract( $slide );

                $link_content = sprintf('<a href="%s" class="button" target="%s">%s</a>', esc_url( $slide_link ), $slide_link_open, __( 'Read More...', 'wedevs' ) );

                if( in_array( $slide_type, array( 'image', 'text', 'video') ) ) {
                    $text_width = $image_width = " full-width col-md-12";
                } else {
                    $text_width = " $width_text";
                    $image_width = " $width_image";
                }

                $text_content = sprintf( '<div class="slide-textarea%s">', $text_width );
                $text_content .= '<h2>' . $slide_title . '</h2>';
                $text_content .= '<div class="slide-detail">' . do_shortcode( $slide_content ) . '</div>';
                $text_content .= ( !empty( $slide_link ) ) ? "<div class='more'>$link_content</div>" : '';
                $text_content .= '</div>';

                $video_content = sprintf( '<div class="slide-video%s">%s</div>', $image_width, $slide_video );

                $image = sprintf( '<img src="%s" alt="%s" />', $slide_image, esc_attr( $slide_title ) );

                if( !empty( $slide_link ) ) {
                    $image = sprintf( '<a href="%s" target="%s">%s</a>', esc_url( $slide_link ), $slide_link_open, $image );
                }

                $image_content = sprintf( '<div class="slide-image%s">%s</div>', $image_width, $image );

                switch ( $slide_type ) {
                    case 'text-image':
                        echo $text_content . "\n";
                        echo $image_content . "\n";
                        break;

                    case 'image-text':
                        echo $image_content . "\n";
                        echo $text_content . "\n";
                        break;

                    case 'text-video':
                        echo $text_content . "\n";
                        echo $video_content . "\n";
                        break;

                    case 'video-text':
                        echo $video_content . "\n";
                        echo $text_content . "\n";
                        break;

                    case 'image':
                        echo $image_content . "\n";
                        break;

                    case 'video':
                        echo $video_content . "\n";
                        break;

                    case 'text':
                        echo $text_content . "\n";
                        break;
                }

                echo '</div>';
                echo "</li>";
            }
            echo "</ul>";
            echo '</div>';
        }
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            $('#flexslider-<?php echo $slider_id; ?>').flexslider({
                animation: '<?php echo $slider_effect; ?>',
                slideshowSpeed: <?php echo absint( $slider_speed ); ?>,
                mousewheel: false,
                directionNav: <?php echo $direction_nav == 'true' ? 'true' : 'false'; ?>,
                controlNav: <?php echo $slider_pagination == 'true' ? 'true' : 'false'; ?>,
                direction: '<?php echo $slide_direction; ?>',
                touch: <?php echo $touch == 'true' ? 'true' : 'false'; ?>,
                randomize: <?php echo $randomize == 'true' ? 'true' : 'false'; ?>,
                pauseOnHover: <?php echo $pauseOnHover == 'true' ? 'true' : 'false'; ?>,
                pausePlay: <?php echo $pausePlay == 'true' ? 'true' : 'false'; ?>
            });
        });
        // 'slide_direction', 'touch', 'randomize', 'pauseOnHover', 'pausePlay');
        </script>
        <?php
    }
}

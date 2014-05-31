<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
?>
</div><!-- .row -->
</div><!-- .container -->
</div><!-- #main .site-main -->

<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="footer-widget-area">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                </div>

                <div class="col-md-3">
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                </div>

                <div class="col-md-3">
                    <?php dynamic_sidebar( 'footer-3' ); ?>
                </div>

                <div class="col-md-3">
                    <?php dynamic_sidebar( 'footer-4' ); ?>
                </div>
            </div> <!-- .footer-widget-area -->
        </div>
    </div>

    <div class="copy-container">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="footer-copy">
                        <div class="col-md-6 site-info">
                            <?php
                            $footer_text = dokan_get_option( 'footer_text', 'dokan_general' );

                            if ( empty( $footer_text ) ) {
                                printf( __( '&copy; %d, %s. All rights are reserved.', 'dokan' ), date( 'Y' ), get_bloginfo( 'name' ) );
                                printf( __( 'Powered <a href="%s" target="_blank">Dokan</a> - by <a href="%s" target="_blank">weDevs</a>', 'dokan' ), esc_url( 'http://wedevs.com/theme/dokan/?utm_source=dokan&utm_medium=theme_footer&utm_campaign=product' ), esc_url( 'http://wedevs.com/?utm_source=dokan&utm_medium=theme_footer&utm_campaign=product' ) );
                            } else {
                                echo $footer_text;
                            }
                            ?>
                        </div><!-- .site-info -->

                        <div class="col-md-6 footer-gateway">
                            <?php
                                wp_nav_menu( array(
                                    'theme_location' => 'footer',
                                    'depth' => 1,
                                    'container_class' => 'footer-menu-container clearfix',
                                    'menu_class' => 'menu list-inline pull-right',
                                ) );
                            ?>
                        </div>
                    </div>
                </div>
            </div><!-- .row -->
        </div><!-- .container -->
    </div> <!-- .copy-container -->
</footer><!-- #colophon .site-footer -->
</div><!-- #page .hfeed .site -->

<?php wp_footer(); ?>

<div id="yith-wcwl-popup-message" style="display:none;"><div id="yith-wcwl-message"></div></div>
</body>
</html>
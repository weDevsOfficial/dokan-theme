<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Dokan
 * @subpackage WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<?php get_sidebar( 'shop' ); ?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content" role="main">

	<?php
    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action( 'woocommerce_before_main_content' );
	?>
		<div class="archive-title clearfix">

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		<?php endif; ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

		</div>

		<?php do_action( 'woocommerce_archive_description' );

        if ( woocommerce_product_loop() ) {

            woocommerce_product_loop_start();

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();

                    /**
                    * Hook: woocommerce_shop_loop.
                    *
                    * @hooked WC_Structured_Data::generate_product_data() - 10
                    */
                    do_action( 'woocommerce_shop_loop' );

                    wc_get_template_part( 'content', 'product' );
                }
            }

            woocommerce_product_loop_end();

            /**
            * Hook: woocommerce_after_shop_loop.
            *
            * @hooked woocommerce_pagination - 10
            */
            do_action( 'woocommerce_after_shop_loop' );
        } else {
            /**
            * Hook: woocommerce_no_products_found.
            *
            * @hooked wc_no_products_found - 10
            */
            do_action( 'woocommerce_no_products_found' );
        }

    /**
    * Hook: woocommerce_after_main_content.
    *
    * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
    */
    do_action( 'woocommerce_after_main_content' );?>

	</div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
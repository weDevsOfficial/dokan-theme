<?php
/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/woocommerce/single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header('shop'); ?>

<div id="primary" class="content-area col-md-12">
    <div id="content" class="site-content" role="main">

    	<?php do_action('woocommerce_before_main_content'); ?>

        <?php while (have_posts()) : the_post(); ?>

            <?php woocommerce_get_template_part( 'content', 'single-product' ); ?>

        <?php endwhile; // end of the loop. ?>

        <?php do_action('woocommerce_after_main_content'); ?>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer('shop'); ?>
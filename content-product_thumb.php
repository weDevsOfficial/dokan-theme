<?php
global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
    $woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
    $woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Ensure visibility
if ( ! $product || ! $product->is_visible() )
    return;

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] )
    $classes[] = 'first';
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )
    $classes[] = 'last';

?>

<li <?php post_class( $classes ); ?>>
    <figure>

        <a href="<?php the_permalink(); ?>"><?php echo woocommerce_get_product_thumbnail(); ?></a>

        <figcaption>

            <?php if ( $product->is_on_sale() ): ?>
                <span class="status on">On Sale</span>
            <?php endif; ?>

            <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

            <span class="item-bar">
                <?php if ( $price_html = $product->get_price_html() ) : ?>
                    <span class="item-price"><?php echo $price_html; ?></span>
                <?php endif; ?>

                <span class="item-button">

                    <a href="#" class="btn cat"><span class="fa fa-shopping-cart"></span></a>
                    <a href="#" class="btn fav"><span class="fa fa-heart"></span></a>
                </span>
            </span>
        </figcaption>
    </figure>
</li>
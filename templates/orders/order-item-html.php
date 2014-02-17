<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<tr class="item <?php if ( ! empty( $class ) ) echo $class; ?>" data-order_item_id="<?php echo $item_id; ?>">
	<td class="thumb" width="10%">
		<?php if ( $_product ) : ?>
			<a href="<?php echo esc_url( get_permalink( $_product->id ) ); ?>">
				<?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?>
			</a>
		<?php else : ?>
			<?php echo woocommerce_placeholder_img( 'shop_thumbnail' ); ?>
		<?php endif; ?>
	</td>

	<td class="name" width="65%">

		<?php if ( $_product ) : ?>
			<a target="_blank" href="<?php echo esc_url( get_permalink( $_product->id ) ); ?>">
				<?php echo esc_html( $item['name'] ); ?>
			</a>
		<?php else : ?>
			<?php echo esc_html( $item['name'] ); ?>
		<?php endif; ?>

		<small><?php if ( $_product && $_product->get_sku() ) echo '<br>' . esc_html( $_product->get_sku() ); ?></small>

		<?php
            if ( $_product && isset( $_product->variation_data ) )
                echo '<br/>' . wc_get_formatted_variation( $_product->variation_data, true );
        ?>
	</td>

	<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>

    <td width="1%">
        <?php if ( isset( $item['qty'] ) ) echo esc_html( $item['qty'] ); ?>
    </td>

    <td class="line_cost" width="1%">
        <?php
            if ( isset( $item['line_total'] ) ) {
                if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) echo '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ';

                echo wc_price( $item['line_total'] );
            }
        ?>
    </td>
</tr>
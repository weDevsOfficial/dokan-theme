<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<tr class="item <?php if ( ! empty( $class ) ) echo $class; ?>" data-order_item_id="<?php echo $item_id; ?>">
	<td class="thumb" width="3%">
		<?php if ( $_product ) : ?>
			<a href="<?php echo esc_url( get_permalink( $_product->id ) ); ?>">
				<?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?>
			</a>
		<?php else : ?>
			<?php echo woocommerce_placeholder_img( 'shop_thumbnail' ); ?>
		<?php endif; ?>
	</td>

	<td class="name" width="70%">

		<?php if ( $_product && $_product->get_sku() ) echo esc_html( $_product->get_sku() ) . ' &ndash; '; ?>

		<?php if ( $_product ) : ?>
			<a target="_blank" href="<?php echo esc_url( get_permalink( $_product->id ) ); ?>">
				<?php echo esc_html( $item['name'] ); ?>
			</a>
		<?php else : ?>
			<?php echo esc_html( $item['name'] ); ?>
		<?php endif; ?>

		<?php
			if ( $_product && isset( $_product->variation_data ) )
				echo '<br/>' . woocommerce_get_formatted_variation( $_product->variation_data, true );
		?>
	</td>

	<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>

	<?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) : ?>

	<td class="tax_class" width="1%">
		<select class="tax_class" name="order_item_tax_class[<?php echo absint( $item_id ); ?>]" title="<?php _e( 'Tax class', 'woocommerce' ); ?>">
			<?php
			$item_value = isset( $item['tax_class'] ) ? sanitize_title( $item['tax_class'] ) : '';

			$tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option('woocommerce_tax_classes' ) ) ) );

			$classes_options = array();
			$classes_options[''] = __( 'Standard', 'woocommerce' );

			if ( $tax_classes )
				foreach ( $tax_classes as $class )
					$classes_options[ sanitize_title( $class ) ] = $class;

			foreach ( $classes_options as $value => $name )
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $item_value, false ) . '>'. esc_html( $name ) . '</option>';
			?>
		</select>
	</td>

	<?php endif; ?>

	<td class="line-quantity" width="1%">
		<?php echo esc_attr( $item['qty'] ); ?>
	</td>

	<td class="line_cost" width="1%">
		<?php if ( isset( $item['line_subtotal'] ) ) echo woocommerce_price( $item['line_subtotal'] ); ?>
	</td>

	<?php if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) : ?>

	<td class="line_tax" width="1%">
		<input type="number" step="any" min="0" name="line_tax[<?php echo absint( $item_id ); ?>]" placeholder="0.00" value="<?php if ( isset( $item['line_tax'] ) ) echo esc_attr( $item['line_tax'] ); ?>" class="line_tax" />

		<span class="subtotal"><input type="number" step="any" min="0" name="line_subtotal_tax[<?php echo absint( $item_id ); ?>]" placeholder="0.00" value="<?php if ( isset( $item['line_subtotal_tax'] ) ) echo esc_attr( $item['line_subtotal_tax'] ); ?>" class="line_subtotal_tax" /></span>
	</td>

	<?php endif; ?>

</tr>
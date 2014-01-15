<tr>
	<td class="file_name"><input type="text" class="input_text" placeholder="<?php _e( 'File Name', 'woocommerce' ); ?>" name="_wc_variation_file_names[<?php echo $variation_id; ?>][]" value="<?php echo esc_attr( $file['name'] ); ?>" /></td>
	<td class="file_url"><input type="text" class="input_text wc_file_url" placeholder="<?php _e( "http://", 'woocommerce' ); ?>" name="_wc_variation_file_urls[<?php echo $variation_id; ?>][]" value="<?php echo esc_attr( $file['file'] ); ?>" /></td>
	<td class="file_url_choose" width="1%"><a href="#" class="btn btn-sm btn-default upload_file_button" data-choose="<?php _e( 'Choose file', 'woocommerce' ); ?>" data-update="<?php _e( 'Insert file URL', 'woocommerce' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'woocommerce' ) ); ?></a></td>
	<td width="1%"><a href="#" class="btn btn-sm btn-danger delete"><?php _e( 'Delete', 'woocommerce' ); ?></a></td>
</tr>
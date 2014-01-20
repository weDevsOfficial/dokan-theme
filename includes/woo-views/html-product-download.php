<tr>
	<td>
        <p>
            <label><?php _e( 'File Name:', 'dokan' ); ?> <span class="tips" title="<?php _e( 'This is the name of the download shown to the customer.', 'dokan' ); ?>">[?]</span></label>
            <input type="text" class="input_text" placeholder="<?php _e( 'File Name', 'dokan' ); ?>" name="_wc_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" />
        </p>

        <p>
            <label><?php _e( 'File URL:', 'dokan' ); ?> <span class="tips" title="<?php _e( 'This is the URL or absolute path to the file which customers will get access to.', 'woocommerce' ); ?>">[?]</span></label>
            <input type="text" class="input_text wc_file_url" placeholder="<?php _e( "http://", 'dokan' ); ?>" name="_wc_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" />
        </p>

        <p>
            <a href="#" class="btn btn-sm btn-default upload_file_button" data-choose="<?php _e( 'Choose file', 'dokan' ); ?>" data-update="<?php _e( 'Insert file URL', 'dokan' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'woocommerce' ) ); ?></a>
            <a href="#" class="btn btn-sm btn-danger delete"><span><?php _e( 'Delete', 'dokan' ); ?></span></a>
        </p>
    </td>
</tr>
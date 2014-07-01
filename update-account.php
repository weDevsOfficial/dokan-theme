<?php
/**
 * The Template for update customer to seller.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */
$user_id = get_current_user_id();
$user = get_userdata( $user_id );
var_dump($user);
if( isset( $_POST['update'] ) ) {
	dokan_user_update_to_seller( $user_id, $user, $_POST );
	wp_redirect( dokan_get_page_url( 'myaccount', 'woocommerce' ) );
}


$f_name = get_user_meta( $user_id, 'first_name' );
$f_name = $f_name[0];
$l_name = get_user_meta( $user_id, 'last_name' );
$l_name = $l_name[0];

if($f_name == '' ) {
	if( isset($_POST['fname'] ) ) {
		$f_name = $_POST['fname'];
	}
}

if($l_name == '' ) {
	if( isset($_POST['lname'] ) ) {
		$l_name = $_POST['lname'];
	}
}

var_dump(get_user_meta(2)); 
var_dump(get_user_meta(3)); 
// var_dump($user); , 'wp_capabilities'

get_header();

?>

<div id="primary" class="content-area col-md-9">
    <div id="content" class="site-content store-page-wrap woocommerce" role="main">


        <h2><?php _e( 'Update account to Seller', 'dokan' ); ?></h2>
        <form method="post" action="">

            <div class="show_if_seller">

		        <div class="split-row form-row-wide">
		            <p class="form-row form-group">
		                <label for="first-name"><?php _e( 'First Name', 'dokan' ); ?> <span class="required">*</span></label>
		                <input type="text" class="input-text form-control" name="fname" id="first-name" value="<?php if ( ! empty( $f_name ) ) echo esc_attr( $f_name ); ?>" required="required" />
		            </p>

		            <p class="form-row form-group">
		                <label for="last-name"><?php _e( 'Last Name', 'dokan' ); ?> <span class="required">*</span></label>
		                <input type="text" class="input-text form-control" name="lname" id="last-name" value="<?php if ( ! empty( $l_name ) ) echo esc_attr( $l_name ); ?>" required="required" />
		            </p>
		        </div>

		        <p class="form-row form-group form-row-wide">
		            <label for="company-name"><?php _e( 'Shop Name', 'dokan' ); ?> <span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="shopname" id="company-name" value="<?php if ( ! empty( $_POST['shopname'] ) ) echo esc_attr($_POST['shopname']); ?>" required="required" />
		        </p>

		        <p class="form-row form-group form-row-wide">
		            <label for="seller-url" class="pull-left"><?php _e( 'Shop URL', 'dokan' ); ?> <span class="required">*</span></label>
		            <strong id="url-alart-mgs" class="pull-right"></strong>
		            <input type="text" class="input-text form-control" name="shopurl" id="seller-url" value="<?php if ( ! empty( $_POST['shopurl'] ) ) echo esc_attr($_POST['shopurl']); ?>" required="required" />
		            <small><?php echo home_url(); ?>/store/<strong id="url-alart"></strong></small>
		        </p>

		        <p class="form-row form-group form-row-wide">
		            <label for="seller-address"><?php _e( 'Address', 'dokan' ); ?><span class="required">*</span></label>
		            <textarea type="text" id="seller-address" name="address" class="form-control input" required="required"><?php if ( ! empty( $_POST['address'] ) ) echo esc_textarea($_POST['address']); ?></textarea>
		        </p>

		        <p class="form-row form-group form-row-wide">
		            <label for="shop-phone"><?php _e( 'Phone', 'dokan' ); ?><span class="required">*</span></label>
		            <input type="text" class="input-text form-control" name="phone" id="shop-phone" value="<?php if ( ! empty( $_POST['phone'] ) ) echo esc_attr($_POST['phone']); ?>" required="required" />
		        </p>

			    <p class="form-row">
                <input type="submit" class="btn btn-theme" name="update" value="<?php _e( 'Update', 'dokan' ); ?>" />
            	</p>
		    </div>
		</form>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
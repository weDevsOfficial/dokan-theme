<?php
/**
 * Login form
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_user_logged_in() )
    return;
?>
<form method="post" class="login" <?php if ( $hidden ) echo 'style="display:none;"'; ?>>

    <?php do_action( 'woocommerce_login_form_start' ); ?>

    <?php if ( $message ) echo wpautop( wptexturize( $message ) ); ?>

    <p class="form-row form-row-first">
        <label for="username"><?php _e( 'Username or email', 'dokan' ); ?> <span class="required">*</span></label>
        <input type="text" class="input-text" name="username" id="username" />
    </p>
    <p class="form-row form-row-last">
        <label for="password"><?php _e( 'Password', 'dokan' ); ?> <span class="required">*</span></label>
        <input class="input-text" type="password" name="password" id="password" />
    </p>
    <div class="clear"></div>

    <?php do_action( 'woocommerce_login_form' ); ?>

    <p class="form-row">
        <?php wp_nonce_field( 'woocommerce-login' ); ?>
        <input type="submit" class="btn btn-theme" name="login" value="<?php _e( 'Login', 'dokan' ); ?>" />
        <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
        <label for="rememberme" class="inline">
            <input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'dokan' ); ?>
        </label>
    </p>
    <p class="lost_password">
        <a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'dokan' ); ?></a>
    </p>

    <div class="clear"></div>

    <?php do_action( 'woocommerce_login_form_end' ); ?>

</form>
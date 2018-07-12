<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="row" id="customer_login">

    <div class="col-md-6 login-form">

<?php endif; ?>

        <h2><?php _e( 'Login', 'dokan-theme' ); ?></h2>

        <form method="post" class="login">

            <?php do_action( 'woocommerce_login_form_start' ); ?>

            <p class="form-row form-row-wide">
                <label for="username"><?php _e( 'Username or email address', 'dokan-theme' ); ?> <span class="required">*</span></label>
                <input type="text" class="input-text form-control" name="username" id="username" />
            </p>
            <p class="form-row form-row-wide">
                <label for="password"><?php _e( 'Password', 'dokan-theme' ); ?> <span class="required">*</span></label>
                <input class="input-text form-control" type="password" name="password" id="password" />
            </p>

            <?php do_action( 'woocommerce_login_form' ); ?>

            <p class="form-row">
                <label for="rememberme" class="inline">
                    <input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'dokan-theme' ); ?>
                </label>
            </p>

            <p class="form-row">
                <?php wp_nonce_field( 'woocommerce-login' ); ?>
                <button type="submit" class="dokan-btn dokan-btn-theme" name="login" value="<?php _e( 'Login', 'dokan-theme' ); ?>"> <?php _e( 'Login', 'dokan-theme' ); ?> </button>
            </p>
            <p class="lost_password">
                <a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'dokan-theme' ); ?></a>
            </p>

            <?php do_action( 'woocommerce_login_form_end' ); ?>

        </form>

<?php if ( get_option('woocommerce_enable_myaccount_registration') === 'yes' && get_option( 'users_can_register' ) == '1' ) : ?>

    </div>

    <div class="col-md-6 reg-form">

        <h2><?php _e( 'Register', 'dokan-theme' ); ?></h2>

        <form id="register" method="post" class="register">
            <?php do_action( 'woocommerce_register_form_start' ); ?>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

                <p class="form-row form-group form-row-wide">
                    <label for="reg_username"><?php _e( 'Username', 'dokan-theme' ); ?> <span class="required">*</span></label>
                    <input type="text" class="input-text form-control" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) esc_attr( $_POST['username'] ); ?>" required="required" />
                </p>

            <?php endif; ?>

            <p class="form-row form-group form-row-wide">
                <label for="reg_email"><?php _e( 'Email address', 'dokan-theme' ); ?> <span class="required">*</span></label>
                <input type="email" class="input-text form-control" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) esc_attr($_POST['email']); ?>" required="required" />
            </p>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

                <p class="form-row form-group form-row-wide">
                    <label for="reg_password"><?php _e( 'Password', 'dokan-theme' ); ?> <span class="required">*</span></label>
                    <input type="password" class="input-text form-control" name="password" id="reg_password" value="<?php if ( ! empty( $_POST['password'] ) ) esc_attr( $_POST['password'] ); ?>" required="required" minlength="6" />
                </p>

            <?php endif; ?>

            <!-- Spam Trap -->
            <div style="left:-999em; position:absolute;"><label for="trap"><?php _e( 'Anti-spam', 'dokan-theme' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

            <?php do_action( 'woocommerce_register_form' ); ?>
            <?php do_action( 'register_form' ); ?>

            <p class="form-row">
                <?php wp_nonce_field( 'woocommerce-register', '_wpnonce' ); ?>
                <button type="submit" class="dokan-btn dokan-btn-theme" name="register" value="<?php _e( 'Register', 'dokan-theme' ); ?>"> <?php _e( 'Register', 'dokan-theme' ); ?> </button>
            </p>

            <?php do_action( 'woocommerce_register_form_end' ); ?>

        </form>

    </div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

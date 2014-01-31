<?php

/**
 *
 */
class Dokan_Email {

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * Get from name for email.
     *
     * @access public
     * @return string
     */
    function get_from_name() {
        return wp_specialchars_decode( esc_html( get_option( 'woocommerce_email_from_name' ) ), ENT_QUOTES );
    }

    /**
     * Get from email address.
     *
     * @access public
     * @return string
     */
    function get_from_address() {
        return sanitize_email( get_option( 'woocommerce_email_from_address' ) );
    }

    function admin_email() {
        return get_option( 'admin_email' );
    }

    function get_user_agent() {
        return substr( $_SERVER['HTTP_USER_AGENT'], 0, 150 );
    }

    function currency_symbol( $amount ) {
        $price = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $amount );
        $price = str_replace( array('&#163;', '&#8364;', '&#36;'), array('£', 'EUR', '$'), $price);

        return $price;
    }

    function contact_seller( $seller_email, $from_name, $from_email, $message ) {
        $template = DOKAN_INC_DIR . '/emails/contact-seller.php';
        ob_start();
        include $template;
        $body = ob_get_clean();

        $find = array(
            '%from_name%',
            '%from_email%',
            '%user_ip%',
            '%user_agent%',
            '%message%',
            '%site_name%',
            '%site_url%'
        );

        $replace = array(
            $from_name,
            $from_email,
            dokan_get_client_ip(),
            $this->get_user_agent(),
            $message,
            $this->get_from_name(),
            home_url()
        );

        $subject = sprintf( __( '"%s" sent you a message from your "%s" store', 'dokan' ), $from_name, $this->get_from_name() );
        $body = str_replace( $find, $replace, $body);
        $headers = array( "Reply-To: {$from_name}<{$from_email}>" );

        $this->send( $seller_email, $subject, $body, $headers );
    }

    function prepare_withdraw( $body, $user, $amount, $method, $note = '' ) {
        $find = array(
            '%username%',
            '%amount%',
            '%method%',
            '%profile_url%',
            '%withdraw_page%',
            '%site_name%',
            '%site_url%',
            '%notes%'
        );

        $replace = array(
            $user->user_login,
            $this->currency_symbol( $amount ),
            dokan_withdraw_get_method_title( $method ),
            admin_url( 'user-edit.php?user_id=' . $user->ID ),
            admin_url( 'admin.php?page=dokan-withdraw' ),
            $this->get_from_name(),
            home_url(),
            $note
        );

        $body = str_replace( $find, $replace, $body);

        return $body;
    }

    function new_withdraw_request( $user, $amount, $method ) {
        $template = DOKAN_INC_DIR . '/emails/withdraw-new.php';
        ob_start();
        include $template;
        $body = ob_get_clean();

        $subject = sprintf( __( '[%s] New Withdraw Request', 'dokan' ), $this->get_from_name() );
        $body = $this->prepare_withdraw( $body, $user, $amount, $method );

        $this->send( $this->admin_email(), $subject, $body );
    }

    function withdraw_request_approve( $user_id, $amount, $method ) {
        $template = DOKAN_INC_DIR . '/emails/withdraw-approve.php';
        ob_start();
        include $template;
        $body = ob_get_clean();

        $user = get_user_by( 'id', $user_id );
        $subject = sprintf( __( '[%s] Your Withdraw Request has been approved', 'dokan' ), $this->get_from_name() );
        $body = $this->prepare_withdraw( $body, $user, $amount, $method );

        $this->send( $this->admin_email(), $subject, $body );
    }

    function withdraw_request_cancel( $user_id, $amount, $method, $note = '' ) {
        $template = DOKAN_INC_DIR . '/emails/withdraw-cancel.php';
        ob_start();
        include $template;
        $body = ob_get_clean();

        $user = get_user_by( 'id', $user_id );
        $subject = sprintf( __( '[%s] Your Withdraw Request has been cancelled', 'dokan' ), $this->get_from_name() );
        $body = $this->prepare_withdraw( $body, $user, $amount, $method, $note );

        $this->send( $this->admin_email(), $subject, $body );
    }

    /**
     * Send the email.
     *
     * @access public
     * @param mixed $to
     * @param mixed $subject
     * @param mixed $message
     * @param string $headers
     * @param string $attachments
     * @return void
     */
    function send( $to, $subject, $message, $headers = array() ) {
        add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

        wp_mail( $to, $subject, $message, $headers );

        remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
        remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
    }


}
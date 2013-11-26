<?php
/**
 * Bangla Date translate class for WordPress
 *
 * Converts English months, dates to equivalent Bangla digits
 * and month names.
 *
 * @author Tareq Hasan <tareq@wedevs.com>
 */
class WP_BanglaDate {

    public function __construct() {
        add_filter( 'the_time', array( $this, 'translate' ) );
        add_filter( 'the_date', array( $this, 'translate' ) );

        add_filter( 'get_the_date', array( $this, 'translate' ) );
        add_filter( 'get_the_time', array( $this, 'translate' ) );
        add_filter( 'date_i18n', array( $this, 'translate' ) );

        add_filter( 'comments_number', array( $this, 'translate' ) );

        add_filter( 'get_comment_date', array( $this, 'translate' ) );
        add_filter( 'get_comment_time', array( $this, 'translate' ) );

        add_filter( 'number_format_i18n', array( $this, 'translate' ) );
    }

    /**
     * Main function that handles the string
     *
     * @param string $str
     * @return string
     */
    function translate( $str ) {
        if ( !$str ) {
            return;
        }

        $str = $this->translate_number( $str );
        $str = $this->translate_day( $str );
        $str = $this->translate_am( $str );

        return $str;
    }

    /**
     * Translate numbers only
     *
     * @param string $str
     * @return string
     */
    function translate_number( $str ) {
        $en = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 );
        $bn = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );

        $str = str_replace( $en, $bn, $str );

        return $str;
    }

    /**
     * Translate months only
     *
     * @param string $str
     * @return string
     */
    function translate_day( $str ) {
        $en = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
        $en_short = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
        $bn = array( 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );

        $str = str_replace( $en, $bn, $str );
        $str = str_replace( $en_short, $bn, $str );

        return $str;
    }

    /**
     * Translate AM and PM
     *
     * @param string $str
     * @return string
     */
    function translate_am( $str ) {
        $en = array( 'am', 'pm' );
        $bn = array( 'পূর্বাহ্ন', 'অপরাহ্ন' );

        $str = str_replace( $en, $bn, $str );

        return $str;
    }
}

$bn = new WP_BanglaDate();

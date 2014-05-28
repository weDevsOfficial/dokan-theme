<?php

class Dokan_Update {

    const base_url = 'http://wedevs.com/';
    const product_id = 'dokan';
    const option = 'dokan_license';
    const slug = 'dokan';

    function __construct() {

        add_action( 'dokan_admin_menu', array($this, 'admin_menu'), 99 );

        add_action( 'admin_notices', array($this, 'license_enter_notice') );
        add_action( 'admin_notices', array($this, 'license_check_notice') );

        add_filter( 'pre_set_site_transient_update_themes', array($this, 'check_update') );
    }

    /**
     * Add admin menu to User Frontend option
     *
     * @return void
     */
    function admin_menu() {
        add_submenu_page( 'dokan', __( 'Updates', 'dokan' ), __( 'Updates', 'dokan' ), 'manage_options', 'dokan_updates', array($this, 'plugin_update') );
    }

    /**
     * Get license key
     *
     * @return array
     */
    function get_license_key() {
        return get_option( self::option, array() );
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @return void
     */
    function license_enter_notice() {
        if ( $key = $this->get_license_key() ) {
            return;
        }
        ?>
        <div class="error">
            <p><?php printf( __( 'Please <a href="%s">enter</a> your <strong>Dokan</strong> theme license key to get regular update and support.', 'dokan' ), admin_url( 'admin.php?page=dokan_updates' ) ); ?></p>
        </div>
        <?php
    }

    /**
     * Check activation every 12 hours to the server
     *
     * @return void
     */
    function license_check_notice() {
        if ( !$key = $this->get_license_key() ) {
            return;
        }

        $error = __( 'Pleae activate your copy', 'dokan' );

        $license_status = get_option( 'dokan_license_status' );

        if ( $license_status && $license_status->activated ) {

            $status = get_transient( self::option );
            if ( false === $status ) {
                $status = $this->activation();

                $duration = 60 * 60 * 12; // 12 hour
                set_transient( self::option, $status, $duration );
            }

            if ( $status && $status->success ) {
                return;
            }

            // may be the request didn't completed
            if ( !isset( $status->error )) {
                return;
            }

            $error = $status->error;
        }
        ?>
        <div class="error">
            <p><strong><?php _e( 'Dokan Error:', 'dokan' ); ?></strong> <?php echo $error; ?></p>
        </div>
        <?php
    }

    /**
     * Activation request to the plugin server
     *
     * @return object
     */
    function activation( $request = 'check' ) {
        if ( !$option = $this->get_license_key() ) {
            return;
        }

        $args = array(
            'request' => $request,
            'email' => $option['email'],
            'licence_key' => $option['key'],
            'product_id' => self::product_id,
            'instance' => home_url()
        );

        $base_url = add_query_arg( 'wc-api', 'software-api', self::base_url );
        $target_url = $base_url . '&' . http_build_query( $args );
        $response = wp_remote_get( $target_url, array( 'timeout' => 15 ) );
        $update = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            return false;
        }

        return json_decode( $update );
    }

    /**
     * Integrates into plugin update api check
     *
     * @param object $transient
     * @return object
     */
    function check_update( $checked_data ) {
        if ( empty( $checked_data->checked ) ) {
            return $checked_data;
        }

        $remote_info = $this->get_update_info();

        if ( !$remote_info ) {
            return $checked_data;
        }

        list( $theme_name, $theme_version) = $this->get_theme_info();

        if ( version_compare( $theme_version, $remote_info->latest, '<' ) ) {

            $obj = array();
            $obj['new_version'] = $remote_info->latest;
            $obj['url'] = self::base_url . 'changelog/theme/dokan.txt';
            $obj['package'] = '';

            if ( isset( $remote_info->latest_url ) ) {
                $obj['package'] = $remote_info->latest_url;
            }

            $basefile = self::slug;
            $checked_data->response[$basefile] = $obj;
        }

        return $checked_data;
    }

    /**
     * Collects current plugin information
     *
     * @return array
     */
    function get_theme_info() {
        $theme_data = wp_get_theme( get_option( 'template' ) );
        $theme_name = $theme_data->Name;
        $theme_version = $theme_data->Version;

        return array($theme_name, $theme_version);
    }

    /**
     * Get plugin update information from server
     *
     * @global string $wp_version
     * @global object $wpdb
     * @return boolean
     */
    function get_update_info() {
        global $wp_version, $wpdb;

        list( $theme_name, $theme_version) = $this->get_theme_info();

        if ( is_multisite() ) {
            $wp_install = network_site_url();
        } else {
            $wp_install = home_url( '/' );
        }

        $license = $this->get_license_key();

        $params = array(
            'timeout' => 30,
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'body' => array(
                'name' => $theme_name,
                'slug' => self::slug,
                'type' => 'theme',
                'version' => $theme_version,
                'site_url' => $wp_install,
                'license' => isset( $license['key'] ) ? $license['key'] : '',
                'license_email' => isset( $license['email'] ) ? $license['email'] : '',
                'product_id' => self::product_id
            )
        );

        $response = wp_remote_post( self::base_url . '?action=wedevs_update_check', $params );
        $update = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            return false;
        }

        return json_decode( $update );
    }

    /**
     * Plugin license enter admin UI
     *
     * @return void
     */
    function plugin_update() {
        $errors = array();
        if ( isset( $_POST['submit'] ) ) {
            if ( empty( $_POST['email'] ) ) {
                $errors[] = __( 'Empty email address', 'dokan' );
            }

            if ( empty( $_POST['license_key'] ) ) {
                $errors[] = __( 'Empty license key', 'dokan' );
            }

            if ( !$errors ) {
                update_option( self::option, array('email' => $_POST['email'], 'key' => $_POST['license_key']) );
                delete_transient( self::option );

                $license_status = get_option( 'dokan_license_status' );

                if ( !isset( $license_status->activated ) || $license_status->activated != true ) {
                    $response = $this->activation( 'activation' );

                    if ( $response && isset( $response->activated ) && $response->activated ) {
                        update_option( 'dokan_license_status', $response );
                    }
                }


                echo '<div class="updated"><p>' . __( 'Settings Saved', 'dokan' ) . '</p></div>';
            }
        }

        if ( isset( $_POST['delete_license'] ) ) {
            delete_option( self::option );
            delete_transient( self::option );
            delete_option( 'dokan_license_status' );
        }

        $license = $this->get_license_key();
        $email = $license ? $license['email'] : '';
        $key = $license ? $license['key'] : '';
        ?>
        <div class="wrap">
            <?php screen_icon( 'plugins' ); ?>
            <h2><?php _e( 'Theme Activation', 'dokan' ); ?></h2>

            <p class="description">
                <?php _e( 'Enter the E-mail address that was used for purchasing the theme and the license key.', 'dokan' ); ?>
                <?php _e( 'We recommend you to enter those details to get regular <strong>theme update and support</strong>.', 'dokan' ); ?>
            </p>

            <?php
            if ( $errors ) {
                foreach ($errors as $error) {
                    ?>
                    <div class="error"><p><?php echo $error; ?></p></div>
                    <?php
                }
            }

            $license_status = get_option( 'dokan_license_status' );
            if ( !isset( $license_status->activated ) || $license_status->activated != true ) {
                ?>

                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'E-mail Address', 'dokan' ); ?></th>
                            <td>
                                <input type="email" name="email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" required>
                                <span class="description"><?php _e( 'Enter your purchase Email address', 'dokan' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'License Key', 'dokan' ); ?></th>
                            <td>
                                <input type="text" name="license_key" class="regular-text" value="<?php echo esc_attr( $key ); ?>">
                                <span class="description"><?php _e( 'Enter your license key', 'dokan' ); ?></span>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button( __( 'Save & Activate', 'dokan' ) ); ?>
                </form>
            <?php } else { ?>

                <div class="updated">
                    <p><?php _e( 'Theme is activated', 'dokan' ); ?></p>
                </div>

                <form method="post" action="">
                    <?php submit_button( __( 'Delete License', 'dokan' ), 'delete', 'delete_license' ); ?>
                </form>

            <?php } ?>
        </div>
        <?php
    }

}
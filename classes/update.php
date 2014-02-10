<?php

class Dokan_Update {

    const base_url = 'http://wedevs.com/';
    const product_id = 'dokan';
    const option = 'dokan_license';
    const slug = 'dokan';

    function __construct() {

        add_action( 'dokan_admin_menu', array($this, 'admin_menu'), 99 );

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                add_action( 'admin_notices', array($this, 'license_enter_notice') );
                add_action( 'admin_notices', array($this, 'license_check_notice') );
            }
        } else {
            add_action( 'admin_notices', array($this, 'license_enter_notice') );
            add_action( 'admin_notices', array($this, 'license_check_notice') );
        }

        add_filter( 'pre_set_site_transient_update_themes', array($this, 'check_update') );
        add_filter( 'themes_api', array(&$this, 'check_info'), 10, 3 );
    }

    /**
     * Add admin menu to User Frontend option
     *
     * @return void
     */
    function admin_menu() {
        add_submenu_page( 'dokan', __( 'Updates', 'dokan' ), __( 'Updates', 'dokan' ), 'activate_plugins', 'dokan_updates', array($this, 'plugin_update') );
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

        $error = __( 'Pleae activate your copy' );

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
    function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote_info = $this->get_info();

        if ( !$remote_info ) {
            return $transient;
        }

        list( $plugin_name, $theme_version) = $this->get_current_plugin_info();

        if ( version_compare( $theme_version, $remote_info->latest, '<' ) ) {

            $obj = new stdClass();
            $obj->slug = self::slug;
            $obj->new_version = $remote_info->latest;
            $obj->url = self::base_url;

            if ( isset( $remote_info->latest_url ) ) {
                $obj->package = $remote_info->latest_url;
            }

            $basefile = plugin_basename( dirname( dirname( __FILE__ ) ) . '/dokan.php' );
            $transient->response[$basefile] = $obj;
        }

        return $transient;
    }

    /**
     * Plugin changelog information popup
     *
     * @param type $false
     * @param type $action
     * @param type $args
     * @return \stdClass|boolean
     */
    function check_info( $false, $action, $args ) {
        if ( self::slug == $args->slug ) {

            $remote_info = $this->get_info();

            $obj = new stdClass();
            $obj->slug = self::slug;
            $obj->new_version = $remote_info->latest;

            if ( isset( $remote_info->latest_url ) ) {
                $obj->download_link = $remote_info->latest_url;
            }

            $obj->sections = array(
                'description' => $remote_info->msg
            );

            return $obj;
        }

        return false;
    }

    /**
     * Collects current plugin information
     *
     * @return array
     */
    function get_current_plugin_info() {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';

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
    function get_info() {
        global $wp_version, $wpdb;

        list( $theme_name, $theme_version) = $this->get_current_plugin_info();

        if ( is_multisite() ) {
            $wp_install = network_site_url();
        } else {
            $wp_install = home_url( '/' );
        }

        $license = $this->get_license_key();

        $params = array(
            'timeout' => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'body' => array(
                'name' => $theme_name,
                'slug' => self::slug,
                'type' => 'theme',
                'version' => $theme_version,
                'action' => 'theme_check',
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

        dokan_log( 'dokan update check' );

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

            <?php } ?>
        </div>
        <?php
    }

}
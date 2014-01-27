<?php

class Dokan_Admin_User_Profile {

    public function __construct() {
        add_action( 'show_user_profile', array( $this, 'add_meta_fields' ), 20 );
        add_action( 'edit_user_profile', array( $this, 'add_meta_fields' ), 20 );

        add_action( 'personal_options_update', array( $this, 'save_meta_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_meta_fields' ) );
    }

    function add_meta_fields( $user ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $selling = get_user_meta( $user->ID, 'dokan_enable_selling', true );
        $publishing = get_user_meta( $user->ID, 'dokan_publishing', true );
        ?>
        <h3><?php _e( 'Dokan Options', 'dokan' ); ?></h3>

        <table class="form-table">
            <tbody>
                <tr>
                    <th><?php _e( 'Selling', 'dokan' ); ?></th>
                    <td>
                        <label for="dokan_enable_selling">
                            <input type="hidden" name="dokan_enable_selling" value="no">
                            <input name="dokan_enable_selling" type="checkbox" id="dokan_enable_selling" value="yes" <?php checked( $selling, 'yes' ); ?> />
                            <?php _e( 'Enable Selling', 'dokan' ); ?>
                        </label>

                        <p class="description"><?php _e( 'Enable or disable product selling capability', 'dokan' ) ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Publishing', 'dokan' ); ?></th>
                    <td>
                        <label for="dokan_publish">
                            <input type="hidden" name="dokan_publish" value="no">
                            <input name="dokan_publish" type="checkbox" id="dokan_publish" value="yes" <?php checked( $publishing, 'yes' ); ?> />
                            <?php _e( 'Publish product directly', 'dokan' ); ?>
                        </label>

                        <p class="description"><?php _e( 'Instead going pending, products will be published directly', 'dokan' ) ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    function save_meta_fields( $user_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $selling = sanitize_text_field( $_POST['dokan_enable_selling'] );
        $publishing = sanitize_text_field( $_POST['dokan_publish'] );

        update_user_meta( $user_id, 'dokan_enable_selling', $selling );
        update_user_meta( $user_id, 'dokan_publishing', $publishing );
    }
}
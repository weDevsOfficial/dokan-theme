<div class="wrap">
    <h2><?php _e( 'Seller Listing', 'dokan' ); ?></h2>

    <form action="<?php echo admin_url( 'users.php' ); ?>" method="get" style="margin-top: 15px;">

        <input type="hidden" name="s" value="">
        <?php wp_nonce_field( 'bulk-users' ); ?>

        <table class="widefat withdraw-table">
            <thead>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" class="dokan-withdraw-allcheck">
                    </th>
                    <th><?php _e( 'Username', 'dokan' ); ?></th>
                    <th><?php _e( 'Name', 'dokan' ); ?></th>
                    <th><?php _e( 'Shop Name', 'dokan' ); ?></th>
                    <th><?php _e( 'E-mail', 'dokan' ); ?></th>
                    <th><?php _e( 'Products', 'dokan' ); ?></th>
                    <th><?php _e( 'Balance', 'dokan' ); ?></th>
                    <th><?php _e( 'Phone', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $paged = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
                $limit = 20;
                $count = 0;
                $offset = ( $paged - 1 ) * $limit;
                $user_search = new WP_User_Query( array( 'role' => 'seller', 'number' => $limit, 'offset' => $offset ) );
                $sellers = (array) $user_search->get_results();
                $post_counts = count_many_users_posts( wp_list_pluck( $sellers, 'ID' ), 'product' );

                if ( $sellers ) {

                    foreach ($sellers as $user) {
                        $info = dokan_get_store_info( $user->ID );
                        $seller_enable = dokan_is_seller_enabled( $user->ID );
                        $edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) ) );
                        ?>
                        <tr class="<?php echo ($count % 2 == 0) ? 'alternate' : 'odd'; ?> ">
                            <th class="check-column">
                                <input type="checkbox" class="dokan-withdraw-allcheck" value="<?php echo $user->ID; ?>" name="users[]">
                            </th>
                            <td>
                                <strong><a href="<?php echo $edit_link ?>"><?php echo $user->user_login; ?></strong></a>
                                <div class="row-actions toggle-seller-status">
                                    <?php if ( !$seller_enable ) { ?>
                                        <span class="active"><a class="toggle-seller" href="#" data-id="<?php echo $user->ID; ?>" data-type="yes"><?php _e( 'Activate Selling', 'dokan' ); ?></a> | </span>
                                    <?php } else { ?>
                                        <span class="active delete"><a class="toggle-seller" href="#" data-id="<?php echo $user->ID; ?>" data-type="no"><?php _e( 'Make Inactivate', 'dokan' ); ?></a> | </span>
                                    <?php } ?>

                                    <span class="products-link"><a href="<?php echo admin_url( 'edit.php?post_type=product&author=' . $user->ID ); ?>"><?php _e( 'Products', 'dokan' ); ?></a> | </span>
                                    <span class="orders-link"><a href="<?php echo admin_url( 'edit.php?post_type=shop_order&author=' . $user->ID ); ?>"><?php _e( 'Orders', 'dokan' ); ?></a></span>
                                </div>
                            </td>
                            <td><?php echo $user->display_name; ?></td>
                            <td><?php echo empty( $info['store_name'] ) ? '--' : $info['store_name']; ?></td>
                            <td><?php echo $user->user_email; ?></td>
                            <td>
                                <a href="<?php echo admin_url( 'edit.php?post_type=product&author=' . $user->ID ); ?>">
                                    <?php echo isset( $post_counts[$user->ID] ) ? $post_counts[$user->ID] : 0; ?>
                                </a>
                            </td>
                            <td><?php echo dokan_get_seller_balance( $user->ID ); ?></td>
                            <td><?php echo empty( $info['phone'] ) ? '--' : $info['phone']; ?></td>
                            <td>
                                <?php if ( $seller_enable ) {
                                    echo '<span class="seller-active">' . __( 'Active', 'dokan' ) . '</span>';
                                } else {
                                    echo '<span class="seller-inactive">' . __( 'Inactive', 'dokan' ) . '</span>';
                                } ?>
                            </td>
                        </tr>
                        <?php
                        $count++;
                    }
                } else {
                    echo '<tr><td colspan="9">' . __( 'No users found!', 'dokan' ) .'</td></tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" class="dokan-withdraw-allcheck">
                    </th>
                    <th><?php _e( 'Username', 'dokan' ); ?></th>
                    <th><?php _e( 'Name', 'dokan' ); ?></th>
                    <th><?php _e( 'Shop Name', 'dokan' ); ?></th>
                    <th><?php _e( 'E-mail', 'dokan' ); ?></th>
                    <th><?php _e( 'Products', 'dokan' ); ?></th>
                    <th><?php _e( 'Balance', 'dokan' ); ?></th>
                    <th><?php _e( 'Phone', 'dokan' ); ?></th>
                    <th><?php _e( 'Status', 'dokan' ); ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <select name="action2">
                    <option value="-1" selected="selected"><?php _e( 'Bulk Actions', 'dokan' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'dokan' ); ?></option>
                </select>

                <input type="submit" name="" id="doaction2" class="button button-primary" value="<?php esc_attr_e( 'Apply', 'dokan' ); ?>">
            </div>

            <?php
            $user_count = $user_search->total_users;
            $num_of_pages = ceil( $user_count / $limit );

            if ( $num_of_pages > 1 ) {
                $page_links = paginate_links( array(
                    'current' => $paged,
                    'total' => $num_of_pages,
                    // 'base' => admin_url( 'admin.php?page=dokan-sellers&amp;page=%#%' ),
                    'base' => add_query_arg( 'pagenum', '%#%' ),
                    'prev_text' => __( '&larr; Previous', 'dokan' ),
                    'next_text' => __( 'Next &rarr;', 'dokan' ),
                ) );

                if ( $page_links ) {
                    echo '<div class="tablenav-pages" style="margin: 1em 0"><span class="pagination-links">' . $page_links . '</span></div>';
                }
            }
            ?>
        </div>
    </form>

    <style type="text/css">
        .seller-active { color: green; }
        .seller-inactive { color: red; }
    </style>

    <script type="text/javascript">
        jQuery(function($) {
            $('.toggle-seller-status').on('click', 'a.toggle-seller', function(e) {
                e.preventDefault();

                var data = {
                    'action' : 'dokan_toggle_seller',
                    'user_id' : $(this).data('id'),
                    'type' : $(this).data('type')
                };

                $.post(ajaxurl, data, function(resp) {
                    window.location.reload();
                });
            });
        });
    </script>


</div>
<?php
$current = isset( $_GET['status'] ) ? $_GET['status'] : 'pending';
$status = ( $current == 'pending' ) ? 0 : 1;
?>
<div class="wrap">
    <h2><?php _e( 'Withdraw Requests', 'dokan' ); ?></h2>

    <ul class="subsubsub" style="float: none;">
        <li><a href="admin.php?page=dokan-withdraw&amp;status=pending" <?php if ( $current == 'pending' ) echo 'class="current"'; ?>><?php _e( 'Pending', 'dokan' ); ?></a> |</li>
        <li><a href="admin.php?page=dokan-withdraw&amp;status=completed" <?php if ( $current == 'completed' ) echo 'class="current"'; ?>><?php _e( 'Approved', 'dokan' ); ?></a></li>
    </ul>

    <?php
    $dokan_admin_withdraw = Dokan_Template_Withdraw::init();
    $dokan_admin_withdraw->admin_withdraw_list( $status );
    ?>
</div>
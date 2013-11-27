<?php
/**
 * Template Name: Dashboard - Withdraw
 */

get_header();
?>

<div class="row">

    <?php dokan_get_template( __DIR__ . '/dashboard-nav.php', array( 'active_menu' => 'withdraw' ) ); ?>

    <div class="col-md-9">
        <?php
        	class dokan_withdraw{
        		

        		function __construct() {
        			ob_start();
        			global $wpdb;

        			$wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';

        			$this->insert_withdraw_info();
        			$this->cancel_pending();
        			//create table
        			$this->withdraw_table();
        			//withdraw form 
        			$this->withdraw_form();
        		}

        		function cancel_pending() {
        			ob_start();
        			if( !isset($_GET['user_id']) && !wp_verify_nonce( $_GET['dokan_cancel_withdrow'], 'dokan_cancel_withdrow_nonce' ) )
        				return;

        			global $current_user, $wpdb;
        			if( $current_user->ID != $_GET['user_id'] ) return;

        			$wpdb->query( $wpdb->prepare(
        				"UPDATE {$wpdb->dokan_withdraw}
        				SET status = %d WHERE user_id=%d
        				", 1, $_GET['user_id'] 
        			));

        			ob_end_flush();
        			wp_redirect(get_permalink());
        		}

        		function insert_withdraw_info() {

        			global $current_user, $wpdb;
        			if( !isset($_POST['withdraw_submit']) || !wp_verify_nonce($_POST['dokan_withdraw_nonce'], 'dokan_withdraw') ) {
        				return;
        			}

        			$id = $current_user->ID;
        			$amount = $_POST['witdraw_amount'];
        			$date 	= current_time( 'mysql' );
        			$status = 0;
        			$method = $_POST['withdraw_method'];
        			$ip 	= $this->validate_ip();
        			$notes 	= '';

        			$table = $wpdb->dokan_withdraw;

        			$data = array(
        				'user_id'	=> $id,
        				'amount' 	=> $amount,
        				'date' 	 	=> $date,
        				'status' 	=> $status,
        				'notes' 	=> $notes,
        				'ip' 		=> $ip

        			);
        			$format = array('%d', '%d', '%s', '%d', '%s', '%s');
        			
        			if( is_user_logged_in() ) {
        				$wpdb->insert($table,$data, $format );
        			}
        			ob_end_flush();
        			wp_redirect( get_permalink() );

        		}


        		function withdraw_table() {

					global $wpdb;
					$withdraw_db_version = '1.0';
					$installed_ver = get_option( "withdraw_db_version" );

					if( $installed_ver != $withdraw_db_version ) {

						// $table_name = $wpdb->prefix . "dokan_withdraw";

						$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->dokan_withdraw} (
							   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
							   `user_id` bigint(20) unsigned NOT NULL,
							   `amount` float(11) NOT NULL,
							   `date` timestamp NOT NULL,
							   `status` int(1) NOT NULL,
							   `method` varchar(30) NOT NULL,
							   `notes` text NOT NULL,
							   `ip` varchar(15) NOT NULL,
							  PRIMARY KEY (id)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						dbDelta( $sql );

						add_option( "withdraw_db_version", $withdraw_db_version );
					}
					
        		}

        		function validate_ip() {
					$ip = $this->get_client_ip();
					if (filter_var($ip, FILTER_VALIDATE_IP)) {
						return $ip;
					} else {
						return false;
					}
				}

        		// Function to get the client ip address
				function get_client_ip() {
					$ipaddress = '';
					if (getenv('HTTP_CLIENT_IP'))
						$ipaddress = getenv('HTTP_CLIENT_IP');
					else if(getenv('HTTP_X_FORWARDED_FOR'))
						$ipaddress = getenv('HTTP_X_FORWARDED_FOR'&quot);
					else if(getenv('HTTP_X_FORWARDED'))
						$ipaddress = getenv('HTTP_X_FORWARDED');
					else if(getenv('HTTP_FORWARDED_FOR'))
						$ipaddress = getenv('HTTP_FORWARDED_FOR');
					else if(getenv('HTTP_X_CLUSTER_CLIENT_IP'))
						$ipaddress = getenv('HTTP_FORWARDED_FOR');
					else if(getenv('HTTP_FORWARDED'))
						$ipaddress = getenv('HTTP_FORWARDED');
					else if(getenv('REMOTE_ADDR'))
						$ipaddress = getenv('REMOTE_ADDR');
					else
						$ipaddress = 'UNKNOWN';
				 
					return $ipaddress;
				}

				function has_pending_request() {
					global $wpdb, $current_user;
					$wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';
					$status = $wpdb->get_var( $wpdb->prepare( 
						"
							SELECT status 
							FROM $wpdb->dokan_withdraw 
							WHERE user_id = %d
						", 
						$current_user->ID
					) );

					if( $status == 0 ) {
						return true;
					}

					return false;
				}

				function has_withdraw_balance() {
					global $current_user;

					$balance = $this->get_user_balance( $current_user->ID );
					$withdraw_limit = $this->get_withdraw_limit();

					if ( $balance < $withdraw_limit ) {
						return false;
					}

					return true;
				}

				function get_withdraw_limit() {
					return 50;
				}

				function get_user_balance( $user_id ) {
					return 99;
				}

				function td_status($status) {
					if( $status == 0 ) {
						return 'pending';
					}
				}

        		function withdraw_form() {
        			global $wpdb, $current_user;
        			if( $this->has_pending_request() ) {
        				
        				?>
        				<table class="table">
        					<th><?php printf(__('Amount','dokan')); ?></th>
        					<th><?php printf(__('Method','dokan')); ?></th>
        					<th><?php printf(__('Data','dokan')); ?></th>
        					<th><?php printf(__('Cancel','dokan')); ?></th>
        					<th><?php printf(__('Status','dokan')); ?></th>
        				

        				<?php
        		
        				$result = $wpdb->get_row($wpdb->prepare( 
        					
        					"SELECT * FROM {$wpdb->dokan_withdraw} 
        					WHERE user_id='%d'", $current_user->ID
        				));
        		
        				?>
        				<tr>
	        				<td><?php  _e( $result->amount, 'dokan'); ?></td>
	        				<td><?php _e( $result->method, 'dokan' ); ?></td>
	        				<td><?php _e( $result->date, 'dokan'); ?></td>
	        				<td>
		        				<a href="<?php echo wp_nonce_url( add_query_arg( array('user_id' => $current_user->ID) ),get_permalink(), 'dokan_cancel_withdrow', 'dokan_cancel_withdrow_nonce' ); ?>">
		        					<?php _e('Cancel', 'dokan'); ?>
		        				</a>
	        				</td>
	        				<td><?php echo $this->td_status($result->status); ?></td>
        				</tr>
        				</table>
        				<?php
        				return;
        			} else if( !$this->has_withdraw_balance() ) {
        				
        				print(__('You have no sufficient amount for withdraw request','dokan'));
        				return;
        			}
        			
        			
        			$method = array(
        				'none' 			=> __('--select--', 'dokan'),
        				'paypal'		=> __('Paypal', 'dokan'),
        				'bank_transfer'	=> __('Bank transfer', 'dokan'),
        			);

        			$payment_methods = apply_filters( 'payment_withdraw_option', $method );

        			?>

        			<form class="form-horizontal" role="form" active="" method="post" enctype="multipart/form-data">
        				<div class="form-group">
	        				<label for="inputAmount" class="col-sm-3 control-label">
	        					
	        						<?php printf(__('Amount', 'dokan_withdraw'), 'Amount'); ?>
	        				</label>
	        				<div class="col-sm-3">
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                    <input name="witdraw_amount" class="form-control" name="price" id="product-price" type="text" placeholder="9.99" value="">
                                </div>
	        				</div>
	        			</div>
	        			
	        			<div class="form-group">
		        			<label for="inputMethod3" class="col-sm-3 control-label">
		        				<?php printf(__('Payment Method', 'dokan') ); ?>
		        			</label>
		        		
        					<div class="col-sm-5">
	        				<?php
	        					if( is_array($payment_methods) && count($payment_methods) ) {
	        						$this->withdraw_option( $payment_methods );
	        					}
	        				?>
	        				</div>	
        				</div>
        				<?php wp_nonce_field('dokan_withdraw', 'dokan_withdraw_nonce'); ?>
        				<div class="form-group">
    						<div class="col-sm-offset-2 col-sm-10">
        						<input type="submit" class="btn btn-primary" value="<?php _e('Submit Request', 'dokan'); ?>" name="withdraw_submit">
        					</div>
        				</div>
        			</form>


        			<?php

        		}


        		function withdraw_option($option) { ?>
        			<select class="form-control" name="withdraw_method"><?php
        				
        				foreach( $option as $value=>$name ) { ?>
        					
        					<option value="<?php echo $value; ?>">
        						<?php echo $name; ?>
        					</option><?php
        				}?>

        			</select><?php
        		} 



        	}
        	new dokan_withdraw();
        ?>
    </div>
</div>


<?php get_footer(); ?>
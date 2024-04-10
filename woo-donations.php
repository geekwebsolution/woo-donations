<?php
/*
Plugin Name: Woo Donations
Description: Woo Donation is a plugin that is used to collect donations on your websites based on Woocommerce. You can add donation functionality in your site to ask your visitors/users community for financial support for the charity or non-profit programs, products, and organisation.
Author: Geek Code Lab
Version: 4.3.2
Author URI: https://geekcodelab.com/
WC tested up to: 8.7.0
Text Domain : woo-donations
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WDGK_BUILD', '4.3.2' );

if (!defined('ABSPATH')) exit;

if (!defined("WDGK_PLUGIN_DIR_PATH"))

	define("WDGK_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

if (!defined("WDGK_PLUGIN_URL"))

	define("WDGK_PLUGIN_URL", plugins_url() . '/' . basename(dirname(__FILE__)));


/** Add notice if woocommerce not activated */
if ( ! function_exists( 'wdgk_install_woocommerce_admin_notice' ) ) {
	/**
	 * Trigger an admin notice if WooCommerce is not installed.
	 */
	function wdgk_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
				// translators: %s is the plugin name.
				echo esc_html__( sprintf( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'Woo Donations' ), 'woo-donations' );
				?>
			</p>
		</div>
		<?php
	}
}
add_action( 'plugins_loaded', 'wdgk_after_plugins_loaded' );
function wdgk_after_plugins_loaded() {
    // Check WooCommerce installation
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wdgk_install_woocommerce_admin_notice' );
		return;
	}
}

function activate_woo_donations() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-donations-activator.php';
	Woo_Donations_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_woo_donations' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-donations.php';

/**
 * Settings links when plugin is active
 */
function wdgk_plugin_add_settings_link($links) {
	$support_link = '<a href="https://geekcodelab.com/contact/"  target="_blank" >' . __('Support','woo-donations') . '</a>';
	array_unshift($links, $support_link);

	$pro_link = '<a href="https://geekcodelab.com/wordpress-plugins/woo-donation-pro/"  target="_blank" style="color:#46b450;font-weight: 600;">' . __('Premium Upgrade','woo-donations') . '</a>';
	array_unshift($links, $pro_link);

	$settings_link = '<a href="admin.php?page=wdgk-donation-page">' . __('Settings','woo-donations') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wdgk_plugin_add_settings_link');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_donations() {

	$plugin = new Woo_Donations();
	$plugin->run();

}
run_woo_donations();

/** Add cron schedule */
add_filter( 'cron_schedules', function ( $schedules ) {
	$schedules['wdgk_every_one_minute'] = array(
		'interval' => 180,
		'display' => __( 'Every Minute' )
	);
	return $schedules;
 } );

/** Set order synchronization on wp init */
add_action( 'init', 'wdgk_update_order_flag_init' );
function wdgk_update_order_flag_init() {
	$wdgk_set_order_flag_status = get_option( 'wdgk_set_order_flag_status' );
	if(!$wdgk_set_order_flag_status) {
		if (! wp_next_scheduled ( 'wdgk_update_order_flag_action' )) {
			wp_schedule_event( time(), 'wdgk_every_one_minute', 'wdgk_update_order_flag_action' );
		}
	}else{
		if (wp_next_scheduled ( 'wdgk_update_order_flag_action' )) {
			wp_clear_scheduled_hook( 'wdgk_update_order_flag_action' );
		}
	}
}

/** Cron schedule which fires during sync orders - every one minute */
add_action( 'wdgk_update_order_flag_action', 'do_this_every_five_minute' );
function do_this_every_five_minute() {
	$wdgk_set_order_flag_status = get_option( 'wdgk_set_order_flag_status' );

	if(!$wdgk_set_order_flag_status) {
		global $wpdb;
		$settings				= get_option('wdgk_donation_settings');
		$donation_product_id 	= $settings['Product'];

		$wdgk_set_order_flag_process = get_option( 'wdgk_set_order_flag_process' );
		if(!$wdgk_set_order_flag_process) {
			$wdgk_set_order_flag_process['status'] = 'In progress';
			$wdgk_set_order_flag_process['start'] = 0;
		}

		$interval = 100;
		$statuses = 'trash';
		$start = $wdgk_set_order_flag_process['start'];
		$status = $wdgk_set_order_flag_process['status'];
		
		if( wdgk_woocommerce_hpos_tables_used() ) {
			$sql = "SELECT *,order_items.order_id as order_id
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->prefix}wc_orders AS orders ON order_items.order_id = orders.id
			WHERE orders.type = 'shop_order'
			AND orders.status != '".$statuses."'
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = $donation_product_id LIMIT" . " $start,$interval";
		}else{
			$sql = "SELECT *,order_items.order_id as order_id
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
			WHERE posts.post_type = 'shop_order'
			AND posts.post_status != '".$statuses."'
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = $donation_product_id LIMIT" . " $start,$interval";
		}

		$donation_order_result = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		if(count($donation_order_result) != 0) {
			foreach($donation_order_result as $key => $item){
				$order = wc_get_order($item['order_id']);

				$order->update_meta_data( 'wdgk_donation_order_flag', $donation_product_id );
				$order->save();
			}
			update_option( 'wdgk_set_order_flag_process', array("status"=>"In progress","start"=>intval($start)+$interval));
		}else{
			// blank
			$clear_schedule_hook = true;
			update_option('wdgk_set_order_flag_process',array("status"=>"Complete","start"=>intval($start)));
		}

		if(isset($clear_schedule_hook)) {
			wp_clear_scheduled_hook( 'wdgk_update_order_flag_action' );
			update_option( 'wdgk_set_order_flag_status',1 );
		}
	}
}

/** Admin notice for order sync progress */
add_action( 'admin_notices', 'wdgk_sync_donation_orders_admin_notice' );
function wdgk_sync_donation_orders_admin_notice() {
	$wdgk_set_order_flag_status = get_option( 'wdgk_set_order_flag_status' );
	
	if(!$wdgk_set_order_flag_status) {
		$currentScreen = get_current_screen();
		if(isset($currentScreen->id) && $currentScreen->id == 'woocommerce_page_wdgk-donation-page') {

			$class = 'notice notice-info';			
			printf( '<div class="%1$s"><p>%2$s - <strong>%3$s</strong></p></div>', esc_attr( $class ), __('✩ Database synchronization for donation orders is currently in progress.', 'woo-donations'), __('Woo Donations','woo-donations') );
		}
	}

}
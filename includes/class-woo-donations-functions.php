<?php
use Automattic\WooCommerce\Utilities\OrderUtil;

// Get form setting options
function wdgk_get_wc_donation_setting(){
	return get_option('wdgk_donation_settings');
}

// Success message
function success_option_msg_wdgk($msg){
	return ' <div class="notice notice-success wdgk-success-msg is-dismissible"><p>'. $msg . '</p></div>';		
}

// Error message
function failure_option_msg_wdgk($msg){
	return '<div class="notice notice-error wdgk-error-msg is-dismissible"><p>' . $msg . '</p></div>';		
}

// Checks if a product is marked as a donation product
function wdgk_is_donatable($id) {
	$product = "";
	$options = wdgk_get_wc_donation_setting();
	if (isset($options['Product'])) {
		$product = $options['Product'];
	}

	return apply_filters('wdgk_is_donatable', get_post_meta($id, '_donatable', true) == 'yes' && $product != $id);
}

function wdgk_add_donation_product_to_cart($id) {
	$found = false;
	//check if product already in cart
	if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
		
		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$_product = $values['data'];
			
			if ( $_product->get_id() == $id ){
				$found = true;
				WC()->cart->remove_cart_item($cart_item_key);
				WC()->cart->add_to_cart( $id );
			}
			
		}
		// if product not found, add it
		if ( ! $found )
			WC()->cart->add_to_cart( $id );
	
	} else {
		// if no products in cart, add it
		WC()->cart->add_to_cart( $id );
	}
}

/** Check woocommerce is using high speed order storage or not using */
function wdgk_woocommerce_hpos_tables_used() {
	if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
		return true;
	}

	return false;
}

/** Internal Style for Donation Form */
function wdgk_form_internal_style() {
	$color 				= "";
	$textcolor 			= "";
	$additional_style	= "";
	$options = wdgk_get_wc_donation_setting();

	if (isset($options['Color'])) {
		$color = $options['Color'];
		$additional_style .= '.wdgk_donation_content a.button.wdgk_add_donation { background-color: ' . $color . ' !important; } ';
	}

	if (isset($options['TextColor'])) {
		$textcolor = $options['TextColor'];
		$additional_style .= '.wdgk_donation_content a.button.wdgk_add_donation { color: ' . $textcolor . ' !important; }';
	}

	return $additional_style;
}
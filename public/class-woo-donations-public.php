<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://geekcodelab.com/
 * @since      1.0.0
 *
 * @package    Woo_Donations
 * @subpackage Woo_Donations/public
 */
class Woo_Donations_Public {
	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 */
        wp_enqueue_style( $this->plugin_name . '-front-style', plugin_dir_url( __DIR__ ) . 'assets/css/wdgk-front-style.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name . '-front-script', plugin_dir_url( __DIR__ ) .'assets/js/wdgk-front-script.js', array( 'jquery' ), $this->version, false );

        $decimal_separator  = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();
        $wdgk_options = [ "decimal_sep" => $decimal_separator, "thousand_sep" => $thousand_separator ];
        wp_localize_script( $this->plugin_name . '-front-script', 'wdgk_obj', array('ajaxurl' => admin_url( 'admin-ajax.php' ),'options' => $wdgk_options) );
	}

    public function cart_page_donation_form() {
        global $woocommerce;
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : $woocommerce->cart->get_checkout_url();
        $this->wdgk_donation_form_front_html($checkout_url);
    }

    public function checkout_page_donation_form($checkout) {
        global $woocommerce;
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();
        $this->wdgk_donation_form_front_html($cart_url);
    }

    public function wdgk_donation_form_front_html($redurl){

        global $woocommerce;
        $product = $text = $note = $note_html = $donation_price = $donation_note = "";
        $form_title			= "Donation";
        $amount_placeholder	= "Ex.100";
        $note_placeholder	= "Note";
    
        $options = wdgk_get_wc_donation_setting();
    
        if (isset($options['Product'])) {
            $product = $options['Product'];
        }
    
        if(wc()->cart){
            $cart_count = is_object($woocommerce->cart) ? $woocommerce->cart->get_cart_contents_count() : '';
            if ($cart_count != 0) {
                $cartitems = $woocommerce->cart->get_cart();
                if (!empty($cartitems) && isset($cartitems)) {
                    foreach ($cartitems as $item => $values) {
                        $product_id =  $values['product_id'];
                        $donation_price = (isset($values['donation_price'])) ? $values['donation_price'] : '' ;
                        if ($product_id == $product) {
                            $donation_price = isset($_COOKIE['wdgk_product_display_price']) ? $_COOKIE['wdgk_product_display_price'] : $donation_price;
                            if(isset($values['donation_note'])) $donation_note = str_replace("<br />","\n",$values['donation_note']);
                        }
                    }
                }
            }
        }
    
        if (isset($options['Text'])) {
            $text = $options['Text'];
        }
        if (isset($options['Note'])) {
            $note = $options['Note'];
        }	
        if(isset($options['Formtitle']) ){
            $form_title = $options['Formtitle'];
        }
        if(isset($options['AmtPlaceholder'])){
            $amount_placeholder = $options['AmtPlaceholder'];
        }
        if(isset($options['Noteplaceholder'])){
            $note_placeholder = $options['Noteplaceholder'];
        }
        if (!empty($product) && $note == 'on') {
            $note_html = '<textarea id="w3mission" rows="3" cols="20" placeholder="'.esc_attr(wp_unslash($note_placeholder),'woo-donations').'" name="donation_note" class="donation_note">'.wp_unslash($donation_note).'</textarea>';
        }
        
    
        if (!empty($product)) {
    
            $ajax_url		= admin_url('admin-ajax.php');
            $current_cur 	= get_woocommerce_currency();
            $cur_syambols 	= get_woocommerce_currency_symbols();
            if(!empty($donation_price))	{
                $decimal_separator = wc_get_price_decimal_separator();
                $thousand_separator = wc_get_price_thousand_separator();
                $price_decimals = wc_get_price_decimals();
                $donation_price = number_format($donation_price,$price_decimals,$decimal_separator,$thousand_separator);
            }
            
            printf('<div class="wdgk_donation_content"><h3>'.esc_attr__(wp_unslash($form_title),'woo-donations').'</h3><div class="wdgk_display_option"> <span>'.esc_attr($cur_syambols[$current_cur]).'</span><input type="text" name="donation-price" class="wdgk_donation" placeholder="'.esc_attr__(wp_unslash($amount_placeholder),'woo-donations').'" value="'.$donation_price.'" ></div>'.$note_html.'<a href="javascript:void(0)" class="button wdgk_add_donation" data-product-id="'.esc_attr($product).'" data-product-url="'.esc_attr($redurl).'">'.esc_attr__(wp_unslash($text),'woo-donations').'</a><input type="hidden" name="wdgk_product_id" value="" class="wdgk_product_id"><input type="hidden" name="wdgk_ajax_url" value="'.esc_attr($ajax_url).'" class="wdgk_ajax_url"><img src="'.WP_PLUGIN_DIR.'assets/images/ajax-loader.gif" class="wdgk_loader wdgk_loader_img"><div class="wdgk_error_front"></div></div>');
        }
    }

    public function wdgk_donation_form_shortcode_html() {
        global $woocommerce;

        $product 			= "";
        $text 				= "";
        $note 				= "";
        $note_html 			= "";
        $donation_price 	= "";
        $donation_note 		= "";
        $form_title			= "Donation";
        $amount_placeholder	= "Ex.100";
        $note_placeholder	= "Note";

        $options = wdgk_get_wc_donation_setting();

        if (isset($options['Product'])) {
            $product = $options['Product'];
        }
        if (isset($options['Text'])) {
            $text = $options['Text'];
        }
        if (isset($options['Note'])) {
            $note = $options['Note'];
        }	
        if(isset($options['Formtitle']) ){
            $form_title = $options['Formtitle'];
        }
        if(isset($options['AmtPlaceholder'])){
            $amount_placeholder = $options['AmtPlaceholder'];
        }
        if(isset($options['Noteplaceholder'])){
            $note_placeholder = $options['Noteplaceholder'];
        }

        if(wc()->cart){
            $cart_count = is_object($woocommerce->cart) ? $woocommerce->cart->get_cart_contents_count() : '';
            if ($cart_count != 0) {
                $cartitems = $woocommerce->cart->get_cart();
                if (!empty($cartitems) && isset($cartitems)) {
                    foreach ($cartitems as $item => $values) {
                        $product_id =  $values['product_id'];
                        if ($product_id == $product) {
                            $donation_price = isset($_COOKIE['wdgk_product_display_price']) ? $_COOKIE['wdgk_product_display_price'] : $values['donation_price'];
                            if(isset($values['donation_note'])) $donation_note = str_replace("<br />","\n",$values['donation_note']);
                        }
                    }
                }
            }
        }

        if (!empty($product) && $note == 'on') {
            $note_html = '<textarea id="w3mission" rows="3" cols="20" placeholder="'.esc_attr__(wp_unslash($note_placeholder)).'" name="donation_note" class="donation_note">'.wp_unslash($donation_note).'</textarea>';
        }
        
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();

        if (!empty($product)) {
            $ajax_url= admin_url('admin-ajax.php');
            $current_cur = get_woocommerce_currency();
            $cur_syambols = get_woocommerce_currency_symbols();

            if(!empty($donation_price))	{
                $decimal_separator = wc_get_price_decimal_separator();
                $thousand_separator = wc_get_price_thousand_separator();
                $price_decimals = wc_get_price_decimals();
                $donation_price = number_format($donation_price,$price_decimals,$decimal_separator,$thousand_separator);
            }

            return '<div class="wdgk_donation_content"><h3>'.esc_attr__(wp_unslash($form_title),'woo-donations').'</h3><div class="wdgk_display_option"> <span>'.esc_attr($cur_syambols[$current_cur]).'</span><input type="text" name="donation-price" class="wdgk_donation" placeholder="'.esc_attr__(wp_unslash($amount_placeholder),'woo-donations').'" value="'.$donation_price.'" ></div>'.$note_html.'<a href="javascript:void(0)" class="button wdgk_add_donation" data-product-id="'.esc_attr($product).'" data-product-url="'.esc_attr($cart_url).'">'.esc_attr__(wp_unslash($text),'woo-donations').'</a><input type="hidden" name="wdgk_product_id" value="" class="wdgk_product_id"><input type="hidden" name="wdgk_ajax_url" value="'.esc_attr($ajax_url).'" class="wdgk_ajax_url"><img src="'.WP_PLUGIN_DIR.'assets/images/ajax-loader.gif" class="wdgk_loader wdgk_loader_img"><div class="wdgk_error_front"></div></div>';
        }
    }

    public function wdgk_set_button_text_color() {
        $additional_style = wdgk_form_internal_style(); 
        if(isset($additional_style) && !empty($additional_style)) { ?>
            <style>
                <?php _e($additional_style); ?>
            </style>
            <?php
        }
    }

    public function wdgk_add_cart_item_data($cart_item_data, $product_id, $variation_id){
        $pid = "";
        $options = wdgk_get_wc_donation_setting();
        if (isset($options['Product'])) {
            $pid = $options['Product'];
        }
        if (isset($_COOKIE['wdgk_product_price'])) {
            if ($product_id == $pid) {
                $donation_note = json_decode(stripslashes($_COOKIE['wdgk_donation_note']));
                $cart_item_data['donation_price'] = $_COOKIE['wdgk_product_price'];
                if(isset($donation_note) && !empty($donation_note))		$cart_item_data['donation_note'] = implode("<br />", $donation_note);
            }
        }
        return $cart_item_data;
    }
    
    public function wdgk_before_calculate_totals($cart_obj){
    
        $pid = "";
        $options = wdgk_get_wc_donation_setting();
        if (isset($options['Product'])) {
            $pid = $options['Product'];
        }
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        // Iterate through each cart item
        foreach ($cart_obj->get_cart() as $key => $value) {
            $id = $value['data'];
    
            if (isset($value['donation_price']) && $id->get_id() == $pid) {
                $price = $value['donation_price'];
                $value['data']->set_price(($price));
            }
        }
    }

    public function wdgk_filter_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
        if( isset( $cart_item['donation_price'] ) ) {        
            return wc_price(  $cart_item['donation_price'] );    
        }
        return $price_html;
    
    }

    public function wdgk_show_product_discount_order_summary( $total, $cart_item, $cart_item_key ) {
     
        //Get product object
        if( isset(  $cart_item['donation_price']  ) ) {
    
            $total= wc_price($cart_item['donation_price']  * $cart_item['quantity']);
        }
        // Return the html
        return $total;
    }

    public function wdgk_donation_ajax_callback(){
        $product_id = sanitize_text_field($_POST['product_id']);
        $price = sanitize_text_field($_POST['price']);
        $redirect_url = sanitize_text_field($_POST['redirect_url']);
        wdgk_add_donation_product_to_cart($product_id);
        $response = array();
        $response['url'] = $redirect_url;
        $response = json_encode($response);
        _e($response,'woo-donations');
        wp_die();
    }

    public function wdgk_plugin_republic_get_item_data($item_data, $cart_item_data){
        if ( isset($cart_item_data['donation_note'])  && isset($cart_item_data['donation_price']) && !empty($cart_item_data['donation_note']) && !empty($cart_item_data['donation_note'])) {
            $item_data[] = array(
                'key' => __('Description', 'woo-donations'),
                'value' => wp_unslash($cart_item_data['donation_note'])
            );
        }
        return $item_data;
    }

    public function wdgk_plugin_republic_checkout_create_order_line_item($item, $cart_item_key, $values, $order){
        if (isset($values['donation_note'])) {
            $item->add_meta_data(
                __('Description', 'woo-donations'),
                wp_unslash($values['donation_note']),
                true
            );
        }
    }

    public function wdgk_plugin_republic_order_item_name($product_name, $item){
        if (isset($item['donation_note']) && isset($item['donation_price'])) {
    
            $product_name .= sprintf(
                '<ul><li>%s: %s</li></ul>',
                __('Description', 'woo-donations'),
                wp_unslash($item['donation_note'])
            );
        }
        return $product_name;
    }

    public function wdgk_thankyou_change_order_status( $order_id ) {
        $donation_product   = "";
        $options = wdgk_get_wc_donation_setting();

        if (isset($options['Product'])) 		        $donation_product   = $options['Product'];

        $order              =   wc_get_order( $order_id );
        $items              =   $order ->get_items();
        foreach ( $items as $item ) {
            $item_id = $item['product_id'];
            if($donation_product == $item_id) {
                $order->update_meta_data( 'wdgk_donation_order_flag', $item_id );   // set donation order flag 
                $order->save();
            }
        }
    }
}
<?php
global $woocommerce;

$product_id 		= "";
$text 				= "Add Donation";
$note 				= "";
$note_html 			= "";
$donation_price 	= "";
$donation_p_id 	    = "";
$donation_note 		= "";
$product_form 		= false;
$form_title			= "Donation";
$amount_placeholder	= "Ex.100";
$note_placeholder	= "Note";

$options = wdgk_get_wc_donation_setting();
$attr_product_id = (isset($value['product_id']) && !empty($value['product_id'])) ? $value['product_id'] : "";

if(!empty($attr_product_id)) {
    $is_donatable = wdgk_is_donatable($attr_product_id);
    if($is_donatable) {
        $product_form = true;
    }
}

if($product_form) {
    $product_id = $attr_product_id;
    $wdgk_btntext = get_post_meta($product_id, 'wdgk-settings[wdgk_btntext]', true);
    if(isset($wdgk_btntext) && !empty($wdgk_btntext))   $text = $wdgk_btntext;

    $wdgk_add_note = get_post_meta($product_id, 'wdgk-settings[wdgk_add_note]', true);
    if(isset($wdgk_add_note))       $note = $wdgk_add_note;

    $wdgk_title = get_post_meta($product_id, 'wdgk-settings[wdgk_title]', true);
    if(isset($wdgk_title))          $form_title = $wdgk_title;

    $wdgk_amt_place = get_post_meta($product_id, 'wdgk-settings[wdgk_amt_place]', true);
    if(isset($wdgk_amt_place))      $amount_placeholder = $wdgk_amt_place;

    $wdgk_note_place = get_post_meta($product_id, 'wdgk-settings[wdgk_note_place]', true);
    if(isset($wdgk_note_place))     $note_placeholder = $wdgk_note_place;
}else{
    if(isset($options['Product']))      $product_id = $options['Product'];
    if (isset($options['Text']))        $text = $options['Text'];
    if (isset($options['Note']))        $note = $options['Note'];
    if(isset($options['Formtitle']) )   $form_title = $options['Formtitle'];
    if(isset($options['AmtPlaceholder']))   $amount_placeholder = $options['AmtPlaceholder'];
    if(isset($options['Noteplaceholder']))  $note_placeholder = $options['Noteplaceholder'];
}

// When product is not selected as donation
if(empty($product_id)) return;

$product = wc_get_product($product_id);
$cookie_var_key = 'wdgk_variation_product:' . $product_id;
$cookie_variation_id = (isset($_COOKIE[$cookie_var_key]) && !empty($_COOKIE[$cookie_var_key])) ? $_COOKIE[$cookie_var_key] : '';

$has_child = is_a($product, 'WC_Product_Variable') && $product->has_child();
//enqueue woocommerce variation js
if ($has_child) {
    wp_enqueue_script('wc-add-to-cart-variation');

    $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
    $available_variations = $get_variations ? $product->get_available_variations() : false;
    $attributes = $product->get_variation_attributes();
    $attribute_keys = array_keys($attributes);
    $variations_json = wp_json_encode($available_variations);
    $variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);
    $selected_attributes = $product->get_default_attributes();
    $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
}

if(wc()->cart){
    $cart_count = is_object($woocommerce->cart) ? $woocommerce->cart->get_cart_contents_count() : '';
    if ($cart_count != 0) {
        $cartitems = $woocommerce->cart->get_cart();
        if (!empty($cartitems) && isset($cartitems)) {
            foreach ($cartitems as $item => $values) {
                $item_id =  $values['product_id'];
                $donateble_product_id = (!empty($cookie_variation_id)) ? $cookie_variation_id : $product_id;
                
                if(!$product_form) {
                    if ($item_id == $product_id) {
                        $donation_price = isset($_COOKIE['wdgk_product_display_price']) ? $_COOKIE['wdgk_product_display_price'] : $values['donation_price'];
                        if(isset($values['donation_note'])) $donation_note = str_replace("<br />","\n",$values['donation_note']);
                    }
                }
            }
        }
    }
}

if (!empty($product_id) && $note == 'on') {
    $note_html = sprintf( '<textarea id="w3mission" rows="3" cols="20" placeholder="%s" name="donation_note" class="donation_note">%s</textarea>', esc_attr(wp_unslash($note_placeholder)), esc_textarea(wp_unslash($donation_note)) );
}

$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();

$ajax_url= admin_url('admin-ajax.php');
$current_cur = get_woocommerce_currency();
$cur_syambols = get_woocommerce_currency_symbols();

if(!empty($donation_price))	{
    $decimal_separator = wc_get_price_decimal_separator();
    $thousand_separator = wc_get_price_thousand_separator();
    $price_decimals = wc_get_price_decimals();
    $donation_price = number_format($donation_price,$price_decimals,$decimal_separator,$thousand_separator);
}
?>
<?php if ($has_child): ?>
    <form class="variations_form cart wdgk-donation-form" method="post" 
    action="<?php echo esc_url($cart_url); ?>"
    autocomplete="off" enctype='multipart/form-data' data-product_id="<?php echo intval($product_id); ?>"
    data-product_variations="<?php echo esc_attr($variations_attr); ?>">
<?php endif; ?>
<div class="wdgk_donation_content">
    <?php 
    if(isset($form_title) && !empty($form_title)) : ?>
        <h3><?php echo esc_attr__(wp_unslash($form_title),'woo-donations') ?></h3>
        <?php
    endif; ?>

    <div class="wdgk_display_option"> 
        <span><?php echo esc_html($cur_syambols[$current_cur]); ?></span>
        <input type="text" name="donation-price" class="wdgk_donation" placeholder="<?php echo esc_attr(wp_unslash($amount_placeholder)) ?>" value="<?php echo esc_attr($donation_price); ?>" >
    </div>

    <?php
    if ($has_child) :
        $selected = $selected_attributes = array(); ?>
        <input type="hidden" name="variation_id" id="variation_id" value="">
        <?php
        foreach ($attributes as $attribute => $options) :
            $esc_attribute = esc_attr(sanitize_title($attribute));
            ?>
            <div class="variations wdgk_variation wdgk-row">
                <label class="wdgk-variation-heading" for="<?php echo $esc_attribute; ?>">
                    <?php echo wc_attribute_label($attribute, $product); ?>
                    <abbr class="required" title="<?php esc_html_e('required', 'wc-donation-platform'); ?>">*</abbr>
                </label>
                <div>
                    <?php
                    $variation_args = array(
                        'options' => $options,
                        'attribute' => $esc_attribute,
                        'product' => $product
                    );
                    wc_dropdown_variation_attribute_options(
                        $variation_args
                    ); ?>
                </div>
            </div>
            <?php 
        endforeach;
    endif; ?>

    <?php _e($note_html); ?>
    <a href="javascript:void(0)" class="button wdgk_add_donation" data-single-dp="<?php esc_attr_e($product_form == true ? 'true' : 'false') ?>" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-url="<?php echo esc_url($cart_url); ?>">
        <?php echo esc_attr__(wp_unslash($text),'woo-donations'); ?>
    </a>
    <input type="hidden" name="wdgk_product_id" value="" class="wdgk_product_id">
    <input type="hidden" name="wdgk_ajax_url" value="<?php echo esc_url($ajax_url) ?>" class="wdgk_ajax_url">
    <img src="<?php echo esc_url( plugins_url( 'woo-donations/assets/images/ajax-loader.gif' ) ); ?>" class="wdgk_loader wdgk_loader_img">
    <div class="wdgk_error_front"></div>
</div>
<?php if ($has_child): ?></form><?php endif; ?>



    
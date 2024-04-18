<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://geekcodelab.com/
 * @since      1.0.0
 *
 * @package    Woo_Donations
 * @subpackage Woo_Donations/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Donations
 * @subpackage Woo_Donations/admin
 * @author     Geek Code Lab <support@geekcodelab.com>
 */
class Woo_Donations_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 */
        
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-donations-admin.css', array(), $this->version, 'all' );
        if ($hook == 'woocommerce_page_wdgk-donation-page') {
            wp_enqueue_style( $this->plugin_name . '-select2-style', plugin_dir_url( __DIR__ ) . 'assets/css/select2.min.css', array(), $this->version, 'all' );
            wp_enqueue_style( $this->plugin_name . '-admin-style', plugin_dir_url( __DIR__ ) . 'assets/css/wdgk-admin-style.css', array(), $this->version, 'all' );
            wp_enqueue_style( 'wp-color-picker' );
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-donations-admin.js', array( 'jquery' ), $this->version, false );
        if ($hook == 'woocommerce_page_wdgk-donation-page') {
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script( $this->plugin_name . '-select2-script', plugin_dir_url( __DIR__ ) . 'assets/js/select2.min.js', array( 'jquery' ), $this->version, false );
            wp_enqueue_script( $this->plugin_name . '-custom-script', plugin_dir_url( __DIR__ ) . 'assets/js/wdgk-admin-script.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name . '-custom-script', 'wdgkObj', [ 'ajaxurl' => admin_url('admin-ajax.php') ] );
        }
	}

    public function admin_menu_page() {
        add_submenu_page( 'woocommerce', 'Donation', 'Donation', 'manage_woocommerce', 'wdgk-donation-page', array( $this, 'woo_donation_setting' ) );
    }

    public function woo_donation_setting() {
        include( WP_PLUGIN_DIR . '/woo-donations/admin/class-woo-donation-settings.php' );
    }

    public function wdgk_woo_admin_order_items_column( $order_columns ){
        $order_columns['order_products'] = __("Donation","woo-donations");
        return $order_columns;
    }

    public function wdgk_shop_order_custom_column( $column, $order ) {
        if ( 'order_products' !== $column )		return;

        $this->wdgk_get_order_donation_flag($order);
    }

    public function wdgk_order_items_column_cnt( $colname ){
        global $the_order; // the global order object

        if ($colname == 'order_products') {
            $this->wdgk_get_order_donation_flag($the_order);
        }
    }

    public function wdgk_get_order_donation_flag($order) {
        $product = "";
        $options = wdgk_get_wc_donation_setting();
        if (isset($options['Product'])) {
            $product = $options['Product'];
        }
    
        $wdgk_set_order_flag_status = get_option( 'wdgk_set_order_flag_status' );
        if(!$wdgk_set_order_flag_status) {
            $order_items = $order->get_items();
            
            if (!is_wp_error($order_items)) {
                $donation_flag = false;
                foreach ($order_items as $order_item) {
    
                    if ($product == $order_item['product_id']) {
                        $donation_flag = true;
                    }
                }
                if ($donation_flag == true){
                    _e('<span class="dashicons dashicons-yes-alt wdgk_right_icon"></span>');
                } 
                    
            }
        }else{
            $order_flag_meta = $order->get_meta("wdgk_donation_order_flag");
    
            if(isset($order_flag_meta) && !empty($order_flag_meta)) {
                _e('<span class="dashicons dashicons-yes-alt wdgk_right_icon"></span>');
            }
        }
        
    }

    public function wdgk_product_select_ajax_callback() {	
    
        $result = array();
        $search = $_POST['search'];
    
        $search_product_args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1 );
    
        if(is_numeric($search)) {
            $search_product_args['p'] = (int) $search;
        }else{
            $search_product_args['s'] = $search;
        }
        $wdgk_get_page = get_posts( $search_product_args );
    
        foreach ($wdgk_get_page as $wdgk_product) {
            $result[] = array(
                'id' => $wdgk_product->ID,
                'title' => $wdgk_product->post_title .  " ( #" . $wdgk_product->ID . " )"
            );
        }
        echo json_encode($result);
    
        wp_die();
    }

    public function wdgk_block_editor_script() {
        wp_enqueue_style( $this->plugin_name . '-block-style', WP_PLUGIN_DIR . 'assets/css/wdgk-front-style.css', array('wp-edit-blocks'), $this->version );
        wp_enqueue_script( $this->plugin_name . '-block-script', WP_PLUGIN_DIR . 'assets/js/wdgk-block.js', array( 'wp-blocks', 'wp-element' ), $this->version );
    }

    public function wdgk_wp_donation_block() {
        register_block_type( 'woo-donations-block/woo-donations', array(
            'api_version' => 3,
            'editor_script' => 'wdgk-block-script',
            'render_callback' => array( $this, 'wdgk_gutenberg_render_callback' )
        ) );
    }

    public function wdgk_gutenberg_render_callback( $block_attributes, $content ) {
        $donation_form_html = "";
        $additional_style = wdgk_form_internal_style();
    
        if($additional_style != "") {
            $donation_form_html .= '<style>'. esc_html($additional_style) .'</style>';
        }
    
        $donation_form_html .= stripslashes( do_shortcode('[wdgk_donation]') );
    
        return $donation_form_html;
    }

    public function wdgk_product_data_tabs( $tabs ) {
        $wdgk_options = array(
            'label' => __('Donation Form', 'wc-donation-platform'),
            'target' => 'wdgk_donation_form_data',
            'class' => 'show_if_donatable hidden wdgk_donation_options hide_if_external',
            'priority' => 65,
        );
        $tabs[] = $wdgk_options;
        return $tabs;
    }

    public function wdgk_product_data_panel() {
        include WP_PLUGIN_DIR . '/woo-donations/admin/class-product-tab-options.php';
    }

    public function wdgk_process_product_meta($post_id) {
        $product = wc_get_product($post_id);
        $product_settings = [];

        if(isset($_POST['wdgk_add_note']))
            $product_settings['wdgk_add_note'] = sanitize_text_field($_POST['wdgk_add_note']);

        if(isset($_POST['wdgk_btntext']))
            $product_settings['wdgk_btntext'] = sanitize_text_field($_POST['wdgk_btntext']);

        if(isset($_POST['wdgk_title']))
            $product_settings['wdgk_title'] = sanitize_text_field($_POST['wdgk_title']);

        if(isset($_POST['wdgk_amt_place']))
            $product_settings['wdgk_amt_place'] = sanitize_text_field($_POST['wdgk_amt_place']);

        if(isset($_POST['wdgk_note_place']))
            $product_settings['wdgk_note_place'] = sanitize_text_field($_POST['wdgk_note_place']);

        foreach ($product_settings as $key => $value) {
            $product->update_meta_data('wdgk-settings[' . $key . ']', $value);
        }
        $product->save();
    }

    public function wdgk_add_product_type_option($product_type_options) {
        global $post;
        $donation_product = "";
        $options = wdgk_get_wc_donation_setting();
        if (isset($options['Product'])) 		        $donation_product   = $options['Product'];

        if(isset($post->ID) && $post->ID != $donation_product) {
            $product_type_options["donatable"] = [
                "id" => "_donatable",
                "wrapper_class" => "show_if_simple show_if_variable",
                "label" => __('Donation Product', 'woo-donations'),
                "description" => __('This product will only be used for donations if activated', 'woo-donations'),
                "default" => "on",
                "custom_attributes" => array( "het" => "hae" )
            ];
        }

        return $product_type_options;
    }

    public function wdgk_save_post_product($post_id, $product, $update) {
        if (!isset($_POST['_wpnonce'])) {
            return;
        }

        update_post_meta(
            $post_id
            , "_donatable"
            , isset($_POST["_donatable"]) ? "yes" : "no"
        );
        // if(isset($_POST["_donatable"])) {
        //     update_post_meta(
        //         $post_id,
        //         "_sold_individually",
        //         "yes"
        //     ); 
        // }
    }
}
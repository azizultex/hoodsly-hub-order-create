<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://hoodslyhub.com
 * @since      1.0.0
 *
 * @package    HoodslyHub
 * @subpackage HoodslyHub/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    HoodslyHub
 * @subpackage HoodslyHub/admin
 * @author     wppool <info@wppool.dev>
 */
class HoodslyHub_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;


	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version Setting api instance.
	 */
	private $settings_api;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;

		$this->version = $version;
		if ( defined( 'WP_DEBUG' ) ) {
			$this->version = current_time( 'timestamp' ); //for development time only
		}

		$this->settings_api = new HoodslyHub_Settings();
	}//end constructor


	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @return    null    Return early if no settings page is registered.
	 * @since     1.0.0
	 *
	 */
	public function enqueue_styles( $hook ) {
		$page = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';

		global $post_type;

		//register css files
		wp_register_style(
			'select2',
			plugin_dir_url( __FILE__ ) . '../assets/js/select2/css/select2.min.css',
			array(),
			$this->version
		);

		//hoodslyhub admin edit and listing

		wp_register_style(
			'hoodslyhub-admin-styles',
			plugins_url( '../assets/css/HoodslyHub_admin.css', __FILE__ ),
			array(
				'select2',
			),
			HOODSLYHUB_PLUGIN_VERSION
		);

		//hoodslyhub setting
		wp_register_style(
			'hoodslyhub-admin-setting',
			plugins_url( '../assets/css/hoodslyhub-admin-setting.css', __FILE__ ),
			array(
				'wp-color-picker',
				'select2',
			),
			HOODSLYHUB_PLUGIN_VERSION
		);
		if ( $page == 'HoodslyHubsetting' ) {

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'select2' );

			wp_enqueue_style( 'hoodslyhub-admin-setting' );
		}

		if ( $page == 'HoodslyHubsetting' ) {
			wp_register_style(
				'hoodslyhub-branding',
				plugin_dir_url( __FILE__ ) . '../assets/css/hoodslyhub-branding.css',
				array(),
				$this->version
			);
			wp_enqueue_style( 'hoodslyhub-branding' );
		}

	}//end of method enqueue_styles


	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @return    null    Return early if no settings page is registered.
	 * @since     1.0.0
	 *
	 */
	public function enqueue_scripts( $hook ) {
		$page = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';

		global $post_type;

		wp_register_script(
			'select2',
			plugin_dir_url( __FILE__ ) . '../assets/js/select2/js/select2.min.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		//hoodslyhub setting
		wp_register_script(
			'hoodslyhub-admin-setting',
			plugins_url( '../assets/js/hoodslyhub-admin-setting.js', __FILE__ ),
			array(
				'jquery',
				'select2',
				'wp-color-picker',
			),
			HOODSLYHUB_PLUGIN_VERSION
		);

		if ( $page == 'HoodslyHubsetting' ) {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_media();

			$HoodslyHub_admin_setting_arr = array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'hoodslyhub' ),
				'please_select' => esc_html__( 'Please select', 'hoodslyhub' ),
			);
			wp_localize_script( 'hoodslyhub-admin-setting', 'HoodslyHubadminsettingObj', $HoodslyHub_admin_setting_arr );
			wp_enqueue_script( 'hoodslyhub-admin-setting' );
		}

		//header scroll
		wp_register_script(
			'hoodslyhub-scroll',
			plugins_url( '../assets/js/hoodslyhub-scroll.js', __FILE__ ),
			array( 'jquery' ),
			HOODSLYHUB_PLUGIN_VERSION
		);
		if ( $page == 'HoodslyHubsetting' || $page == 'hoodslyhub-help-support' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'hoodslyhub-scroll' );
		}

	}//end method enqueue_scripts

	/**
	 * on admin init initialize setting and handle hoodslyhub type post delete
	 */
	public function admin_init() {

		//init setting api
		$this->settings_api->set_sections( $this->get_setting_sections() );
		$this->settings_api->set_fields( $this->get_setting_fields() );

		//initialize them
		$this->settings_api->admin_init();

	}//end method admin_init


	/**
	 * HoodslyHub Core Global Setting Sections
	 *
	 * @return mixed|void
	 */
	public function get_setting_sections() {
		$sections = array(
			array(
				'id'    => 'AOTHub_global_settings',
				'title' => esc_html__( 'Default API Settings', 'hoodslyhub' ),
			),
		);

		return apply_filters( 'AOTHub_setting_sections', $sections );
	}//end method get_setting_sections

	/**
	 * HoodslyHub Setting Core Fields
	 *
	 * @return mixed|void
	 */
	public function get_setting_fields() {

		$fields = array(
			'AOTHub_global_settings' => apply_filters(
				'AOTHub_global_general_fields',
				array(
					'hub_endpoint'              => array(
						'name'    => 'hub_endpoint',
						'label'   => esc_html__( 'Hoodslyhub End point ', 'hoodslyhub' ),
						'type'    => 'text',
						'default' => 'https://hoodslyhub.com/wp-json/order-data/v1/hub',
					),
					'hub_order_status_endpoint' => array(
						'name'    => 'hub_order_status_endpoint',
						'label'   => esc_html__( 'Hoodslyhub Order Status End point', 'hoodslyhub' ),
						'type'    => 'text',
						'default' => 'https://hoodslyhub.com/wp-json/order-status/v1/hub',
					),
					'hub_order_api_secret' => array(
						'name'    => 'hub_order_api_secret_field',
						'label'   => esc_html__( 'API Endpoint Secret', 'hoodslyhub' ),
						'type'    => 'text',
						'default' => '',
					),
				)
			),
		);

		return apply_filters( 'AOTHub_global_fields', $fields );
	}//end method get_setting_fields

	/**
	 *  add setting page menu
	 */
	public function admin_menu() {
		$setting_page_hook = add_menu_page(
			'Hoodsluyhub Order Manager',
			esc_html__( 'Hub Order Manager', 'hoodslyhub' ),
			'manage_options',
			'HoodslyHubsetting',
			array( $this, 'admin_menu_setting_page' ),
			'',
			3
		);

	}//end method admin_menu

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_setting_page() {
		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . HOODSLYHUB_PLUGIN_BASE_NAME );
		include 'partials/settings-display.php';
	}//end method admin_menu_setting_page


	/**
	 * Test Order for metadata
	 * @since    1.0.0
	 */
	function test_order_data( $order_id ) {
		
		//write_log($api_secret);
		// $order_id                     = intval( 26609 );
		// $order                        = wc_get_order( $order_id );
		// $line_items                   = array();
		// $data                         = $order->get_data();
		// $order_date                   = $order->order_date;
		// $order_status                 = $order->get_status();
		// $order_status                 = wc_get_order_status_name( $order_status );
		// $line_items['order_total']    = $order->get_total();
		// $line_items['total_quantity'] = $order->get_item_count();
		// $product_catSlug              = array();
		// $product_catName              = array();
		// $productName                  = array();
		// $item_Size                    = '';
		// $height                       = '';
		// $tradewinds_sku               = '';

		// $user = $order->get_user();
		// // Get the WP_User roles and capabilities
		// $user_roles   = $user->roles[0];
		// $item_sku_arr = array();
		// foreach ( $order->get_items() as $item_key => $item_values ) {

		// 	$product           = wc_get_product( $item_values->get_product_id() );
		// 	$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
		// 	$product_image_url = $product_image->get_image();
		// 	$pattern           = '/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
		// 	preg_match_all( $pattern, $product_image_url, $matches );
		// 	$product_img_url = $matches[0][0];
		// 	$ups_req_data    = array(
		// 		'woocommerce_dimension_unit' => get_option( 'woocommerce_dimension_unit' ),
		// 		'woocommerce_weight_unit'    => get_option( 'woocommerce_weight_unit' ),
		// 		'weight'                     => $product->get_weight(),
		// 		'width'                      => $product->get_width(),
		// 		'length'                     => $product->get_length(),
		// 		'height'                     => $product->get_height(),
		// 	);

		// 	$item_sku                  = $product->get_sku();
		// 	$item_sku_arr[]            = $item_sku;
		// 	$item_data                 = $item_values->get_data();
		// 	$new_arr                   = array();
		// 	$item_meta_data            = $item_values->get_meta_data();
		// 	$formatted_meta_data       = $item_values->get_formatted_meta_data( '_', true );
		// 	$formatted_meta_data_array = json_decode( json_encode( $formatted_meta_data ), true );
		// 	$reference_for_customer    = '';
		// 	$color                     = '';
		// 	$color_key                 = '';
		// 	$sku                       = '';
		// 	$sku_key                   = '';
		// 	$size                      = '';
		// 	$size_key                  = '';
		// 	$trim_options              = '';
		// 	$trim_options_key          = '';
		// 	$remove_trim               = '';
		// 	$remove_trim_key           = '';
		// 	$molding                   = '';
		// 	$molding_key               = '';
		// 	$increase_depth            = '';
		// 	$increase_depth_key        = '';
		// 	$reduce_height             = '';
		// 	$reduce_height_key         = '';
		// 	$extend_chimney            = '';
		// 	$extend_chimney_key        = '';
		// 	$solid_button              = '';
		// 	$solid_button_key          = '';
		// 	$rush_my_order             = '';
		// 	$rush_my_order_key         = '';
		// 	$tradewinds_sku            = '';
		// 	$stock_quantity            = $product->get_stock_quantity();
		// 	$tradewinds_cat_sku        = get_post_meta( $item_data['variation_id'], '_sku', true );
		// 	$item_Size                 = HoodslyHubHelper::hypemill_product_size( $item_values );
		// 	$terms                     = get_the_terms( $item_data['product_id'], 'product_cat' );
		// 	foreach ( $terms as $term ) {
		// 		// Categories by slug
		// 		$product_catSlug[] = $term->slug;
		// 		$product_catName[] = $term->name;
		// 	}

		// 	foreach ( $formatted_meta_data_array as $value ) {
		// 		$display_value = str_replace(
		// 			array( '<p>', '</p>' ),
		// 			array(
		// 				'',
		// 				'',
		// 			),
		// 			html_entity_decode( $value['display_value'] )
		// 		);
		// 		if ( trim( $display_value ) == 'TradeWinds Select For Pricing' ) {
		// 			$is_tradewinds_selected = 'yes';
		// 		}else{
		// 			$is_tradewinds_selected = 'no';
		// 		}

		// 		if ( $value['display_value'] == 'No Vent' ) {
		// 			$tradewinds_quickship = 'no';
		// 		} else {
		// 			$tradewinds_quickship = $value['display_value'];
		// 		}

		// 		if ( $value['display_key'] == 'Ventilation Options' ) {
		// 			$vent_option_data = $value['display_value'];
		// 		}

		// 		if ( $value['key'] === 'pa_color' ) {
		// 			$color     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$color_key = $value['value'];
		// 		}

		// 		// Get the size of the product
		// 		if ( $value['display_key'] === 'Size' ) {
		// 			$size     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$size_key = $value['value'];
		// 		}

		// 		// Ge the SKU from product
		// 		if ( $value['key'] === 'SKU' ) {
		// 			$sku            = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$sku_key        = $value['value'];
		// 			$tradewinds_sku = explode( '-', $sku );
		// 		}
		// 		// Ge the Removed Trim from product
		// 		if ( $value['display_key'] === 'Trim Options' ) {
		// 			$trim_options     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$trim_options_key = $value['value'];

		// 		}

		// 		// Ge the Removed Trim from product
		// 		$remove_trim_arr = array( 'trim_options_brass_strapping', 'trim_options_walnut_band', 'trim_options_brass_buttons', 'remove_your_trim' );
		// 		if ( in_array( $value['key'], $remove_trim_arr ) ) {
		// 			$remove_trim     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$remove_trim_key = $value['value'];
		// 		}

		// 		// Ge the Crown Molding
		// 		$crown_molding_arr = array( 'brass_crown_molding', 'molding_loose_installed', 'top_strap_steel' );
		// 		if ( in_array( $value['key'], $crown_molding_arr ) ) {
		// 			$molding     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$molding_key = $value['value'];
		// 		}

		// 		// Ge the Increase Depth
		// 		$depth_arr = array( 'curved_depth', 'depth_noncurved', 'vah_19', 'vah_225' );
		// 		if ( in_array( $value['key'], $depth_arr ) ) {
		// 			$increase_depth     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$increase_depth_key = $value['value'];
		// 		}

		// 		// Ge the Reduce height
		// 		if ( $value['key'] === 'reduce_height' ) {
		// 			$reduce_height     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$reduce_height_key = $value['value'];
		// 		}

		// 		// Ge the Extended Chimney
		// 		if ( $value['key'] === 'extend_your_chimney' ) {
		// 			$extend_chimney     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$extend_chimney_key = $value['value'];
		// 		}

		// 		// Ge the Solid Bottom Data
		// 		$solid_arr = array( 'solid_bottom_normal_200', 'solid_bottom_corbels' );
		// 		if ( in_array( $value['key'], $solid_arr ) ) {
		// 			$solid_button     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$solid_button_key = $value['value'];
		// 		}

		// 		// Get the EPO ref for customer
		// 		if ( $value['key'] === 'reference_for_customer' ) {
		// 			$reference_for_customer = $rush_my_order = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 		}
		// 		// Ge the Rush Manufacturing data
		// 		if ( $value['key'] === 'rushed_manufacturing' ) {
		// 			$rush_my_order     = str_replace(
		// 				array( '<p>', '</p>' ),
		// 				array(
		// 					'',
		// 					'',
		// 				),
		// 				html_entity_decode( $value['display_value'] )
		// 			);
		// 			$rush_my_order_key = $value['value'];
		// 		}

		// 		// Ge the height For WRH Condition
		// 		if ( $value['key'] === 'reduce_height' ) {
		// 			$height = $value['value'];
		// 		}// End Condition
		// 	}

		// 	foreach ( $item_data['meta_data'] as $key => $value ) {

		// 		if ( $value->get_data()['key'] == 'pa_color' ) {
		// 			if ( $value->get_data()['value'] == 'custom-color-match' ) {
		// 				$custom_color_match = true;
		// 			} else {
		// 				$custom_color_match = '0';
		// 			}
		// 		}
		// 	}

		// 	$terms            = get_the_terms( $item_data['product_id'], 'product_cat' );
		// 	$product_cat_slug = array();
		// 	$product_cat_name = array();
		// 	foreach ( $terms as $term ) {
		// 		// Categories by slug
		// 		$product_cat_slug[] = $term->slug;
		// 		$product_cat_name[] = $term->name;
		// 	}
		// 	$inc_tax         = true;
		// 	$round           = false; // Not rounded at item level ("true"  for rounding at item level)
		// 	$product_name    = $item_values['name'];
		// 	$product_pattern = '/[\s\S]*?(?=-)/i';
		// 	preg_match_all( $product_pattern, $product_name, $product_matches );
		// 	$productName = trim( $product_matches[0][0] );

		// 	$new_arr['product_id'] = $item_data['product_id'];
		// 	if ( 'RVS' === trim( $tradewinds_sku[0] ) ) {
		// 		$new_arr['tradewinds_sku'] = trim( $tradewinds_sku[1] );
		// 	} else {
		// 		$new_arr['tradewinds_sku'] = trim( $tradewinds_sku[0] );
		// 	}
		// 	$new_arr['tradewinds_quickship'] = $tradewinds_quickship;
		// 	$new_arr['tradewinds_cat_sku']   = $tradewinds_cat_sku;
		// 	$new_arr['vent_option_data']     = $vent_option_data;
		// 	$new_arr['product_img_url']      = $product_img_url;
		// 	$new_arr['product_name']         = $item_data['name'];
		// 	$new_arr['product_cat']          = $product_cat_slug;
		// 	$new_arr['product_cat_name']      = $product_cat_name;
		// 	$new_arr['item_total']           = $order->get_line_total( $item_values, $inc_tax, $round );
		// 	$new_arr['item_total_tax']       = $order->get_line_tax( $item_values );
		// 	$new_arr['item_sku']             = $item_sku;
		// 	$new_arr['variation_id']         = $item_data['variation_id'];
		// 	$new_arr['quantity']             = $item_data['quantity'];
		// 	$new_arr['color']                = array(
		// 		'key'   => $color_key,
		// 		'value' => $color,
		// 	);
		// 	$new_arr['sku']                  = array(
		// 		'key'   => $sku_key,
		// 		'value' => $sku,
		// 	);
		// 	$new_arr['size']                 = array(
		// 		'key'   => $size_key,
		// 		'value' => $size,
		// 	);
		// 	/*$new_arr['vent_option']            = [ 'key' => $vent_option_key, 'value' => $vent_option ];
		// 	$new_arr['z_vent_filter']          = [ 'key' => $zline_filter_key, 'value' => $zline_filter ];
		// 	$new_arr['z_vent_options']         = [ 'key' => $zline_vent_options_key, 'value' => $zline_vent_options ];*/
		// 	$new_arr['trim_options']           = array(
		// 		'key'   => $trim_options_key,
		// 		'value' => $trim_options,
		// 	);
		// 	$new_arr['remove_trim']            = array(
		// 		'key'   => $remove_trim_key,
		// 		'value' => $remove_trim,
		// 	);
		// 	$new_arr['crown_molding']          = array(
		// 		'key'   => $molding_key,
		// 		'value' => $molding,
		// 	);
		// 	$new_arr['increase_depth']         = array(
		// 		'key'   => $increase_depth_key,
		// 		'value' => $increase_depth,
		// 	);
		// 	$new_arr['reduce_height']          = array(
		// 		'key'   => $reduce_height_key,
		// 		'value' => $reduce_height,
		// 	);
		// 	$new_arr['extend_chimney']         = array(
		// 		'key'   => $extend_chimney_key,
		// 		'value' => $extend_chimney,
		// 	);
		// 	$new_arr['solid_button']           = array(
		// 		'key'   => $solid_button_key,
		// 		'value' => $solid_button,
		// 	);
		// 	$new_arr['reference_for_customer'] = $reference_for_customer;
		// 	$new_arr['rush_my_order']          = array(
		// 		'key'   => $rush_my_order_key,
		// 		'value' => $rush_my_order,
		// 	);
		// 	$new_arr['order_meta']             = $formatted_meta_data_array;
		// 	$line_items['line_items'][]        = $new_arr;
		// 	//$productName = $item_data['name'];

		// }
		// foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {

		// 	$shipping_method_total = $item->get_total();
		// 	$shipping_method_id    = $item->get_method_id(); // The method ID
		// 	$shipping_method_title = $item->get_method_title();
		// }
		// $details_data = array(
		// 	'payment_method'       => $data['payment_method'],
		// 	'payment_method_title' => $data['payment_method_title'],
		// 	'customer_note'        => $data['customer_note'],
		// 	'set_paid'             => true,
		// 	'meta_data'            => $data['meta_data'],
		// 	'billing'              => array(
		// 		'first_name' => $data['billing']['first_name'],
		// 		'last_name'  => $data['billing']['last_name'],
		// 		'address_1'  => $data['billing']['address_1'],
		// 		'address_2'  => $data['billing']['address_2'],
		// 		'city'       => $data['billing']['city'],
		// 		'state'      => $data['billing']['state'],
		// 		'postcode'   => $data['billing']['postcode'],
		// 		'country'    => $data['billing']['country'],
		// 		'email'      => $data['billing']['email'],
		// 		'phone'      => $data['billing']['phone'],
		// 	),
		// 	'shipping'             => array(
		// 		'first_name' => $data['shipping']['first_name'],
		// 		'last_name'  => $data['shipping']['last_name'],
		// 		'address_1'  => $data['shipping']['address_1'],
		// 		'address_2'  => $data['shipping']['address_2'],
		// 		'city'       => $data['shipping']['city'],
		// 		'state'      => $data['shipping']['state'],
		// 		'postcode'   => $data['shipping']['postcode'],
		// 		'country'    => $data['shipping']['country'],
		// 	),
		// 	'line_items'           => $line_items,
		// 	'shipping_lines'       => array(
		// 		'method_id'    => $shipping_method_id,
		// 		'method_title' => $shipping_method_title,
		// 		'total'        => $shipping_method_total,
		// 	),
		// );
		// if ( 'RVS' === trim( $tradewinds_sku[0] ) ) {
		// 	$tradewinds_sku_data = trim( $tradewinds_sku[1] );
		// } else {
		// 	$tradewinds_sku_data = trim( $tradewinds_sku[0] );
		// }
		// $this->settings_api = new hoodslyhub_Settings();
		// $hub_endpoint       = $this->settings_api->get_option( 'hub_endpoint', 'AOTHub_global_settings', 'text' );
		// $rest_api_url       = $hub_endpoint;
		// $host               = parse_url( get_site_url(), PHP_URL_HOST );
		// //$domains = explode('.', $host);
		// if ('diamondhoods.com' === $host){
		// 	$orderid = 'USCD-'.intval( $order_id );
		// }else{
		// 	$orderid = intval( $order_id );
		// }
		// $data_string = json_encode(
		// 	array(
		// 		'title'                   => '#' . $order_id . '',
		// 		'order_id'                => $orderid,
		// 		'data'                    => $details_data,
		// 		'content'                 => '#' . $order_id . '<br>' . $data['shipping']['first_name'] . ' ' . $data['billing']['last_name'] . '<br>' . $data['billing']['email'] . '<br>' . $data['billing']['phone'] . '<br>' . $data['shipping']['address_1'] . $data['shipping']['address_2'] . ' ,' . $data['shipping']['city'] . ' ,' . $data['shipping']['state'] . ' ' . $data['shipping']['postcode'] . '',
		// 		'status'                  => 'publish',
		// 		'estimated_shipping_date' => get_post_meta( $order_id, 'estimated_shipping_date', true ),
		// 		'origin'                  => $host,
		// 		'order_date'              => $order_date,
		// 		'meta_data'               => $formatted_meta_data,
		// 		'product_name'            => $productName,
		// 		'product_height'          => $item_Size,
		// 		'reduce_height'           => $height,
		// 		'product_cat'             => $product_catSlug,
		// 		'product_cat_name'        => $product_catName,
		// 		'tradewinds_quickship'    => $tradewinds_quickship,
		// 		'tradewinds_sku'          => $tradewinds_sku_data,
		// 		'product_sku'             => $item_sku,
		// 		'order_status'            => $order_status,
		// 		'custom_color_match'      => $custom_color_match,
		// 		'is_tradewinds_selected'  => $is_tradewinds_selected,
		// 		'stock_quantity'          => $stock_quantity,
		// 		'ups_req_data'            => $ups_req_data,
		// 		'user_roles'              => $user_roles,
		// 		'item_sku_arr'            => $item_sku_arr,
		// 	)
		// );

	}// End test_order_data

	/**
	 * Order from Hoodsly Site To Hoodsly-Hub
	 *
	 * @param $order_id
	 *
	 * @since    1.0.0
	 *
	 */
	public function send_order_data( $order_id ) {
		$order = wc_get_order( $order_id );

		$line_items                   = array();
		$data                         = $order->get_data();
		$order_date                   = $order->order_date;
		$order_status                 = $order->get_status();
		$order_status                 = wc_get_order_status_name( $order_status );
		$line_items['order_total']    = $order->get_total();
		$line_items['total_quantity'] = $order->get_item_count();
		$product_catSlug              = array();
		$product_catName              = array();
		$productName                  = array();
		$item_Size                    = '';
		$height                       = '';
		$tradewinds_sku               = '';
		$host                         = parse_url( get_site_url(), PHP_URL_HOST );


		$user = $order->get_user();
		// Get the WP_User roles and capabilities
		$user_roles   = $user->roles[0];
		$item_sku_arr = array();
		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = '/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];
			$ups_req_data    = array(
				'woocommerce_dimension_unit' => get_option( 'woocommerce_dimension_unit' ),
				'woocommerce_weight_unit'    => get_option( 'woocommerce_weight_unit' ),
				'weight'                     => $product->get_weight(),
				'width'                      => $product->get_width(),
				'length'                     => $product->get_length(),
				'height'                     => $product->get_height(),
			);

			$item_sku                  = $product->get_sku();
			$item_sku_arr[]            = $item_sku;
			$item_data                 = $item_values->get_data();
			$new_arr                   = array();
			$item_meta_data            = $item_values->get_meta_data();
			$formatted_meta_data       = $item_values->get_formatted_meta_data( '_', true );
			$formatted_meta_data_array = json_decode( json_encode( $formatted_meta_data ), true );
			$reference_for_customer    = '';
			$color                     = '';
			$color_key                 = '';
			$sku                       = '';
			$sku_key                   = '';
			$size                      = '';
			$size_key                  = '';
			$trim_options              = '';
			$trim_options_key          = '';
			$remove_trim               = '';
			$remove_trim_key           = '';
			$molding                   = '';
			$molding_key               = '';
			$increase_depth            = '';
			$increase_depth_key        = '';
			$reduce_height             = '';
			$reduce_height_key         = '';
			$extend_chimney            = '';
			$extend_chimney_key        = '';
			$solid_button              = '';
			$solid_button_key          = '';
			$rush_my_order             = '';
			$rush_my_order_key         = '';
			$tradewinds_sku            = '';
			$stock_quantity            = $product->get_stock_quantity();
			$tradewinds_cat_sku        = get_post_meta( $item_data['variation_id'], '_sku', true );
			$item_Size                 = HoodslyHubHelper::hypemill_product_size( $item_values );
			$terms                     = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_catSlug[] = $term->slug;
				$product_catName[] = $term->name;
			}

			foreach ( $formatted_meta_data_array as $value ) {
				$display_value = str_replace(
					array( '<p>', '</p>' ),
					array(
						'',
						'',
					),
					html_entity_decode( $value['display_value'] )
				);
				if ( trim( $display_value ) == 'TradeWinds Select For Pricing' ) {
					$is_tradewinds_selected = 'yes';
				}else{
					$is_tradewinds_selected = 'no';
				}

				if ( $value['display_value'] == 'No Vent' ) {
					$tradewinds_quickship = 'no';
				} else {
					$tradewinds_quickship = $value['display_value'];
				}

				if ( $value['display_key'] == 'Ventilation Options' ) {
					$vent_option_data = $value['display_value'];
				}

				if ( $value['key'] === 'pa_color' ) {
					$color     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$color_key = $value['value'];
				}

				// Get the size of the product
				if ( $value['display_key'] === 'Size' ) {
					$size     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$size_key = $value['value'];
				}

				// Ge the SKU from product
				if ( $value['key'] === 'SKU' ) {
					$sku            = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$sku_key        = $value['value'];
					$tradewinds_sku = explode( '-', $sku );
				}
				// Ge the Removed Trim from product
				if ( $value['display_key'] === 'Trim Options' ) {
					$trim_options     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$trim_options_key = $value['value'];

				}

				// Ge the Removed Trim from product
				$remove_trim_arr = array( 'trim_options_brass_strapping', 'trim_options_walnut_band', 'trim_options_brass_buttons', 'remove_your_trim' );
				if ( in_array( $value['key'], $remove_trim_arr ) ) {
					$remove_trim     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$remove_trim_key = $value['value'];
				}

				// Ge the Crown Molding
				$crown_molding_arr = array( 'brass_crown_molding', 'molding_loose_installed', 'top_strap_steel' );
				if ( in_array( $value['key'], $crown_molding_arr ) ) {
					$molding     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$molding_key = $value['value'];
				}

				// Ge the Increase Depth
				$depth_arr = array( 'curved_depth', 'depth_noncurved', 'vah_19', 'vah_225' );
				if ( in_array( $value['key'], $depth_arr ) ) {
					$increase_depth     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$increase_depth_key = $value['value'];
				}

				// Ge the Reduce height
				if ( $value['key'] === 'reduce_height' ) {
					$reduce_height     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$reduce_height_key = $value['value'];
				}

				// Ge the Extended Chimney
				if ( $value['key'] === 'extend_your_chimney' ) {
					$extend_chimney     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$extend_chimney_key = $value['value'];
				}

				// Ge the Solid Bottom Data
				$solid_arr = array( 'solid_bottom_normal_200', 'solid_bottom_corbels' );
				if ( in_array( $value['key'], $solid_arr ) ) {
					$solid_button     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$solid_button_key = $value['value'];
				}

				// Get the EPO ref for customer
				if ( $value['key'] === 'reference_for_customer' ) {
					$reference_for_customer = $rush_my_order = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
				}
				// Ge the Rush Manufacturing data
				if ( $value['key'] === 'rushed_manufacturing' ) {
					$rush_my_order     = str_replace(
						array( '<p>', '</p>' ),
						array(
							'',
							'',
						),
						html_entity_decode( $value['display_value'] )
					);
					$rush_my_order_key = $value['value'];
				}

				// Ge the height For WRH Condition
				if ( $value['key'] === 'reduce_height' ) {
					$height = $value['value'];
				}// End Condition
			}

			foreach ( $item_data['meta_data'] as $key => $value ) {

				if ( $value->get_data()['key'] == 'pa_color' ) {
					if ( $value->get_data()['value'] == 'custom-color-match' ) {
						$custom_color_match = true;
					} else {
						$custom_color_match = '0';
					}
				}
			}

			$terms            = get_the_terms( $item_data['product_id'], 'product_cat' );
			$product_cat_slug = array();
			$product_cat_name = array();
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_cat_slug[] = $term->slug;
				$product_cat_name[] = $term->name;
			}
			$inc_tax         = true;
			$round           = false; // Not rounded at item level ("true"  for rounding at item level)
			$product_name    = $item_values['name'];
			$product_pattern = '/[\s\S]*?(?=-)/i';
			preg_match_all( $product_pattern, $product_name, $product_matches );


			if ('diamondhoods.com' === $host){
				$productName = trim( $product_name );
			}else{
				$productName = trim( $product_matches[0][0] );
			}

			$new_arr['product_id'] = $item_data['product_id'];
			if ( 'RVS' === trim( $tradewinds_sku[0] ) ) {
				$new_arr['tradewinds_sku'] = trim( $tradewinds_sku[1] );
			} else {
				$new_arr['tradewinds_sku'] = trim( $tradewinds_sku[0] );
			}
			$new_arr['tradewinds_quickship'] = $tradewinds_quickship;
			$new_arr['tradewinds_cat_sku']   = $tradewinds_cat_sku;
			$new_arr['vent_option_data']     = $vent_option_data;
			$new_arr['product_img_url']      = $product_img_url;
			$new_arr['product_name']         = $item_data['name'];
			$new_arr['product_cat']          = $product_cat_slug;
			$new_arr['product_catName']      = $product_cat_name;
			$new_arr['item_total']           = $order->get_line_total( $item_values, $inc_tax, $round );
			$new_arr['item_total_tax']       = $order->get_line_tax( $item_values );
			$new_arr['item_sku']             = $item_sku;
			$new_arr['variation_id']         = $item_data['variation_id'];
			$new_arr['quantity']             = $item_data['quantity'];
			$new_arr['color']                = array(
				'key'   => $color_key,
				'value' => $color,
			);
			$new_arr['sku']                  = array(
				'key'   => $sku_key,
				'value' => $sku,
			);
			$new_arr['size']                 = array(
				'key'   => $size_key,
				'value' => $size,
			);
			/*$new_arr['vent_option']            = [ 'key' => $vent_option_key, 'value' => $vent_option ];
			$new_arr['z_vent_filter']          = [ 'key' => $zline_filter_key, 'value' => $zline_filter ];
			$new_arr['z_vent_options']         = [ 'key' => $zline_vent_options_key, 'value' => $zline_vent_options ];*/
			$new_arr['trim_options']           = array(
				'key'   => $trim_options_key,
				'value' => $trim_options,
			);
			$new_arr['remove_trim']            = array(
				'key'   => $remove_trim_key,
				'value' => $remove_trim,
			);
			$new_arr['crown_molding']          = array(
				'key'   => $molding_key,
				'value' => $molding,
			);
			$new_arr['increase_depth']         = array(
				'key'   => $increase_depth_key,
				'value' => $increase_depth,
			);
			$new_arr['reduce_height']          = array(
				'key'   => $reduce_height_key,
				'value' => $reduce_height,
			);
			$new_arr['extend_chimney']         = array(
				'key'   => $extend_chimney_key,
				'value' => $extend_chimney,
			);
			$new_arr['solid_button']           = array(
				'key'   => $solid_button_key,
				'value' => $solid_button,
			);
			$new_arr['reference_for_customer'] = $reference_for_customer;
			$new_arr['rush_my_order']          = array(
				'key'   => $rush_my_order_key,
				'value' => $rush_my_order,
			);
			//$new_arr['order_meta']             = $formatted_meta_data_array;
			$line_items['line_items'][]        = $new_arr;
			//$productName = $item_data['name'];

		}
		foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {

			$shipping_method_total = $item->get_total();
			$shipping_method_id    = $item->get_method_id(); // The method ID
			$shipping_method_title = $item->get_method_title();
		}
		$details_data = array(
			'payment_method'       => $data['payment_method'],
			'payment_method_title' => $data['payment_method_title'],
			'customer_note'        => $data['customer_note'],
			'set_paid'             => true,
			'meta_data'            => $data['meta_data'],
			'billing'              => array(
				'first_name' => $data['billing']['first_name'],
				'last_name'  => $data['billing']['last_name'],
				'address_1'  => $data['billing']['address_1'],
				'address_2'  => $data['billing']['address_2'],
				'city'       => $data['billing']['city'],
				'state'      => $data['billing']['state'],
				'postcode'   => $data['billing']['postcode'],
				'country'    => $data['billing']['country'],
				'email'      => $data['billing']['email'],
				'phone'      => $data['billing']['phone'],
			),
			'shipping'             => array(
				'first_name' => $data['shipping']['first_name'],
				'last_name'  => $data['shipping']['last_name'],
				'address_1'  => $data['shipping']['address_1'],
				'address_2'  => $data['shipping']['address_2'],
				'city'       => $data['shipping']['city'],
				'state'      => $data['shipping']['state'],
				'postcode'   => $data['shipping']['postcode'],
				'country'    => $data['shipping']['country'],
			),
			'line_items'           => $line_items,
			'shipping_lines'       => array(
				'method_id'    => $shipping_method_id,
				'method_title' => $shipping_method_title,
				'total'        => $shipping_method_total,
			),
		);
		if ( 'RVS' === trim( $tradewinds_sku[0] ) ) {
			$tradewinds_sku_data = trim( $tradewinds_sku[1] );
		} else {
			$tradewinds_sku_data = trim( $tradewinds_sku[0] );
		}
		$this->settings_api = new hoodslyhub_Settings();
		$hub_endpoint       = $this->settings_api->get_option( 'hub_endpoint', 'AOTHub_global_settings', 'text' );
		$rest_api_url       = $hub_endpoint;

		if ('diamondhoods.com' === $host){
			$orderid = 'USCD-'.intval( $order_id );
		}else{
			$orderid = intval( $order_id );
		}
		$data_string = json_encode(
			array(
				'title'                   => '#' . $orderid . '',
				'order_id'                => $orderid,
				'data'                    => $details_data,
				'content'                 => '#' . $order_id . '<br>' . $data['shipping']['first_name'] . ' ' . $data['billing']['last_name'] . '<br>' . $data['billing']['email'] . '<br>' . $data['billing']['phone'] . '<br>' . $data['shipping']['address_1'] . $data['shipping']['address_2'] . ' ,' . $data['shipping']['city'] . ' ,' . $data['shipping']['state'] . ' ' . $data['shipping']['postcode'] . '',
				'status'                  => 'publish',
				'estimated_shipping_date' => get_post_meta( $order_id, 'estimated_shipping_date', true ),
				'origin'                  => $host,
				'order_date'              => $order_date,
				'meta_data'               => $formatted_meta_data,
				'product_name'            => $productName,
				'product_height'          => $item_Size,
				'reduce_height'           => $height,
				'product_cat'             => $product_catSlug,
				'product_cat_name'        => $product_catName,
				'tradewinds_quickship'    => $tradewinds_quickship,
				'tradewinds_sku'          => $tradewinds_sku_data,
				'product_sku'             => $item_sku,
				'order_status'            => $order_status,
				'custom_color_match'      => $custom_color_match,
				'is_tradewinds_selected'  => $is_tradewinds_selected,
				'stock_quantity'          => $stock_quantity,
				'ups_req_data'            => $ups_req_data,
				'user_roles'              => $user_roles,
				'item_sku_arr'            => $item_sku_arr,
			)
		);
		$api_secret = get_option('AOTHub_global_settings');
		$api_signature = base64_encode(hash_hmac('sha256', 'NzdhYjZiOWMwMGIxMjI2', $api_secret['hub_order_api_secret_field']));
		wp_remote_post(
		$rest_api_url,
		array(
			'body' => $data_string,
			'headers' => [
				'content-type' => 'application/json',
				'Api-Signature' => $api_signature,
			]
		)
	);
	}// End send_order_data


	/**
	 *
	 * Trigger order status hook
	 *
	 * @param $order_id
	 */
	public function send_order_status( $order_id ) {
		$order = wc_get_order( $order_id );
		$order_status                 = $order->get_status();
		$order_status                 = wc_get_order_status_name( $order_status );

		$this->settings_api        = new hoodslyhub_Settings();
		$hub_order_status_endpoint = $this->settings_api->get_option( 'hub_order_status_endpoint', 'AOTHub_global_settings', 'text' );
		$rest_api_url              = $hub_order_status_endpoint;

		$data_string = json_encode(
			array(
				'order_id'                => $order_id,
				'order_status'            => $order_status,
			)
		);
		$api_secret = get_option('AOTHub_global_settings');
		$api_signature = base64_encode(hash_hmac('sha256', 'NzdhYjZiOWMwMGIxMjI2', $api_secret['hub_order_api_secret_field']));
		 wp_remote_post(
			$rest_api_url,
			array(
				'body' => $data_string,
				'headers' => [
					'content-type' => 'application/json',
					'Api-Signature' => $api_signature,
				]
			)
		);
	}

}//end class HoodslyHub

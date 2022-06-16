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
		wp_register_style( 'select2', plugin_dir_url( __FILE__ ) . '../assets/js/select2/css/select2.min.css', array(),
			$this->version );

		//hoodslyhub admin edit and listing

		wp_register_style( 'hoodslyhub-admin-styles', plugins_url( '../assets/css/HoodslyHub_admin.css', __FILE__ ), array(
			'select2',
		), HOODSLYHUB_PLUGIN_VERSION );


		//hoodslyhub setting
		wp_register_style( 'hoodslyhub-admin-setting', plugins_url( '../assets/css/hoodslyhub-admin-setting.css', __FILE__ ),
			array(
				'wp-color-picker',
				'select2',
			), HOODSLYHUB_PLUGIN_VERSION );
		if ( $page == 'HoodslyHubsetting' ) {

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'select2' );


			wp_enqueue_style( 'hoodslyhub-admin-setting' );
		}

		if ( $page == 'HoodslyHubsetting' ) {
			wp_register_style( 'hoodslyhub-branding', plugin_dir_url( __FILE__ ) . '../assets/css/hoodslyhub-branding.css',
				array(),
				$this->version );
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

		wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../assets/js/select2/js/select2.min.js',
			array( 'jquery' ), $this->version, true );

		//hoodslyhub setting
		wp_register_script( 'hoodslyhub-admin-setting', plugins_url( '../assets/js/hoodslyhub-admin-setting.js', __FILE__ ),
			array(
				'jquery',
				'select2',
				'wp-color-picker'
			), HOODSLYHUB_PLUGIN_VERSION );

		if ( $page == 'HoodslyHubsetting' ) {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_media();


			$HoodslyHub_admin_setting_arr = array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'hoodslyhub' ),
				'please_select' => esc_html__( 'Please select', 'hoodslyhub' )
			);
			wp_localize_script( 'hoodslyhub-admin-setting', 'HoodslyHubadminsettingObj', $HoodslyHub_admin_setting_arr );
			wp_enqueue_script( 'hoodslyhub-admin-setting' );
		}

		//header scroll
		wp_register_script( 'hoodslyhub-scroll', plugins_url( '../assets/js/hoodslyhub-scroll.js', __FILE__ ), array( 'jquery' ),
			HOODSLYHUB_PLUGIN_VERSION );
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
				'title' => esc_html__( 'Default API Settings', 'hoodslyhub' )
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
			'AOTHub_global_settings' => apply_filters( 'AOTHub_global_general_fields', array(
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
			) ),
		);


		return apply_filters( 'AOTHub_global_fields', $fields );
	}//end method get_setting_fields

	/**
	 *  add setting page menu
	 */
	public function admin_menu() {
		$setting_page_hook = add_menu_page( 'Hoodsluyhub Order Manager', esc_html__( 'Hub Order Manager', 'hoodslyhub' ),
			'manage_options', 'HoodslyHubsetting', [ $this, 'admin_menu_setting_page' ], '', 3 );

	}//end method admin_menu

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu_setting_page() {
		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . HOODSLYHUB_PLUGIN_BASE_NAME );
		include( 'partials/settings-display.php' );
	}//end method admin_menu_setting_page


	/**
	 * Test Order for metadata
	 * @since    1.0.0
	 */
	function test_order_data( $order_id ) {
		$order_id = intval( 26476 );
		$order    = wc_get_order( $order_id );

		$line_items                   = array();
		$data                         = $order->get_data();
		$order_date                   = $order->order_date;
		$order_status                 = $order->get_status();
		$order_status                 = wc_get_order_status_name( $order_status );
		$line_items['order_total']    = $order->get_total();
		$line_items['total_quantity'] = $order->get_item_count();
		$product_catSlug              = [];
		$product_catName              = [];
		$productName                  = [];
		$reduce_height                = '';
		$item_Size                    = '';
		/* $args = array(
			'posts_per_page'     => -1,
			'post_type'          => 'product_variation',
			'suppress_filters'   => true
		);
	
		$posts_array = get_posts( $args );
	
		foreach ( $posts_array as $post_array ) {
			$Cogcost = get_post_meta( $post_array->ID, '_regular_price', true );

		} */

		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];

			$item_sku = $product->get_sku();

			$ups_req_data        = [
				'woocommerce_dimension_unit' => get_option( 'woocommerce_dimension_unit' ),
				'woocommerce_weight_unit'    => get_option( 'woocommerce_weight_unit' ),
				'weight'                     => $product->get_weight(),
				'length'                     => $product->get_length(),
				'width'                      => $product->get_width(),
				'length'                     => $product->get_length(),
				'height'                     => $product->get_height(),
			];
			$item_data           = $item_values->get_data();
			$new_arr             = [];
			$item_meta_data      = $item_values->get_meta_data();
			$formatted_meta_data = $item_values->get_formatted_meta_data( '_', true );

			/* $variations                = $product->get_available_variations();
			$variation_formatted_array = array();
			foreach ( $variations as $variation ) {
				$variation_id  = $variation['variation_id'];
				$variation_obj = new WC_Product_variation( $variation_id );
				$stock         = $variation_obj->get_stock_quantity();

				$new_arr                     = array();
				$new_arr[]['_attributes']    = $variation_obj->get_attributes();
				$new_arr[]['variation_id']   = $variation_id;
				$variation_formatted_array[] = $new_arr;
			}
			//$product = wc_get_product($product_id);
			$variations    = $product->get_available_variations();
			$variations_id = wp_list_pluck( $variations, 'variation_id' ); */


			$formatted_meta_data_array = json_decode( json_encode( $formatted_meta_data ), true );
			$reference_for_customer    = '';
			$sku                       = '';
			$color                     = '';
			$color_key                 = '';
			$size                      = '';
			$size_key                  = '';
			$trim_options              = '';
			$remove_trim               = '';
			$crown_molding             = '';
			$increase_depth            = '';
			$reduce_height             = '';
			$solid_button              = '';
			$rush_my_order             = '';
			$extend_chimney            = '';
			$item_Size                 = HoodslyHubHelper::hypemill_product_size( $item_values );

			$terms              = get_the_terms( $item_data['product_id'], 'product_cat' );
			$tradewinds_cat_sku = get_post_meta( $item_data['variation_id'], '_sku', true );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_catSlug[] = $term->slug;
				$product_catName[] = $term->name;
			}

			foreach ( $formatted_meta_data_array as $value ) {
				$display_value = str_replace( [ '<p>', '</p>' ], [
					'',
					''
				], html_entity_decode( $value['display_value'] ) );
				//write_log($value['display_value']);
				//$is_tradewinds_selected = 'no';
				if ( trim( $display_value ) == 'TradeWinds Select For Pricing' ) {
					$is_tradewinds_selected = 'yes';
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
					$color     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$color_key = $value['value'];
				}
				// Get the EPO ref for customer
				if ( $value['key'] === 'reference_for_customer' ) {
					$reference_for_customer = $value['value'];
				}

				// Get the size of the product
				if ( $value['display_key'] === 'Size' ) {
					$size     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$size_key = $value['value'];
				}

				// Ge the SKU from product

				if ( $value['key'] === 'SKU' ) {
					$sku = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;

					$tradewinds_sku = explode( '-', $sku );

				}
				// Ge the Removed Trim from product
				if ( $value['display_key'] === 'Trim Options' ) {
					$trim_options = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}

				// Ge the Removed Trim from product
				if ( $value['key'] === 'remove_your_trim' ) {
					$remove_trim = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}

				// Ge the Crown Molding
				if ( $value['display_key'] === 'Crown Molding (Optional)' ) {
					$crown_molding = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;

				}

				// Ge the Increase Depth
				if ( $value['display_key'] === 'Increase Depth' ) {
					$increase_depth = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}

				// Ge the Reduce height
				if ( $value['key'] === 'reduce_height' ) {
					$reduce_height = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}

				// Ge the SOlid Button Data
				if ( $value['display_key'] === 'Add A Solid Bottom' ) {
					$solid_button = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}

				// Ge the Rush Manufacturing data
				if ( $value['display_key'] === 'Rush Manufacturing' ) {
					$rush_my_order = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}
				// Ge the Rush Manufacturing data
				if ( $value['display_key'] === 'Extend Your Chimney' ) {
					$extend_chimney = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );;
				}
			}
			write_log( $is_tradewinds_selected );
			foreach ( $item_data['meta_data'] as $key => $value ) {

				if ( $value->get_data()['key'] == 'pa_color' ) {
					if ( $value->get_data()['value'] == 'custom-color-match' ) {
						$custom_color_match = true;
					}
				}
			}

			$terms = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_cat_slug = $term->slug;
			}
			$inc_tax         = true;
			$round           = false; // Not rounded at item level ("true"  for rounding at item level)
			$product_name    = $item_values['name'];
			$product_pattern = "/[\s\S]*?(?=-)/i";
			preg_match_all( $product_pattern, $product_name, $product_matches );
			$productName = trim( $product_matches[0][0] );
			//write_log($productName);
			$new_arr['product_id']             = $item_data['product_id'];
			$new_arr['product_img_url']        = $product_img_url;
			$new_arr['product_name']           = $item_data['name'];
			$new_arr['tradewinds_quickship']   = $tradewinds_quickship;
			$new_arr['product_cat']            = $product_cat_slug;
			$new_arr['sku']                    = $item_sku;
			$new_arr['tradewinds_cat_sku']     = $tradewinds_cat_sku;
			$new_arr['item_total']             = $order->get_line_total( $item_values, $inc_tax, $round );
			$new_arr['item_total_tax']         = $order->get_line_tax( $item_values );
			$new_arr['variation_id']           = $item_data['variation_id'];
			$new_arr['quantity']               = $item_data['quantity'];
			$new_arr['color']                  = [ $color ];
			$new_arr[ $size_key ]              = $size;
			$new_arr['sku']                    = $sku;
			$new_arr['trim_options']           = $trim_options;
			$new_arr['remove_trim']            = $remove_trim;
			$new_arr['crown_molding']          = $crown_molding;
			$new_arr['increase_depth']         = $increase_depth;
			$new_arr['reduce_height']          = $reduce_height;
			$new_arr['solid_button']           = $solid_button;
			$new_arr['rush_my_order']          = $rush_my_order;
			$new_arr['extend_chimney']         = $extend_chimney;
			$new_arr['reference_for_customer'] = $reference_for_customer;
			$new_arr['order_meta']             = $formatted_meta_data_array;
			$line_items['line_items'][]        = $new_arr;
			//$productName = $item_data['name'];
		}


		/* foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {
			$order_item_name             = $item->get_name();
			$order_item_type             = $item->get_type();
			$shipping_method_id          = $item->get_method_id(); // The method ID
			$shipping_method_instance_id = $item->get_instance_id(); // The instance ID
			$shipping_method_total_tax   = $item->get_total_tax();
			$shipping_method_taxes       = $item->get_taxes();

			$shipping_method_total = $item->get_total();
			$shipping_method_id    = $item->get_method_id(); // The method ID
			$shipping_method_title = $item->get_method_title();
		} */
		/* $data = $order->get_data();
		$endpoint = 'https://hoodslyhub.com/wp-json/wc/v3/orders/'; */

		/* $details_data = [
			'payment_method'       => $data['payment_method'],
			'payment_method_title' => $data['payment_method_title'],
			'customer_note'        => $data['customer_note'],
			'set_paid'             => true,
			'meta_data'            => $data['meta_data'],
			'billing'              => [
				'first_name' => $data['billing']['first_name'],
				'last_name'  => $data['billing']['last_name'],
				'address_1'  => $data['billing']['address_1'],
				'address_2'  => $data['billing']['address_2'],
				'city'       => $data['billing']['city'],
				'state'      => $data['billing']['state'],
				'postcode'   => $data['billing']['postcode'],
				'country'    => $data['billing']['country'],
				'email'      => $data['billing']['email'],
				'phone'      => $data['billing']['phone']
			],
			'shipping'             => [
				'first_name' => $data['shipping']['first_name'],
				'last_name'  => $data['shipping']['last_name'],
				'address_1'  => $data['shipping']['address_1'],
				'address_2'  => $data['shipping']['address_2'],
				'city'       => $data['shipping']['city'],
				'state'      => $data['shipping']['state'],
				'postcode'   => $data['shipping']['postcode'],
				'country'    => $data['shipping']['country'],
			],
			'line_items'           => $line_items,
			'shipping_lines'       => [
				'method_id'    => $shipping_method_id,
				'method_title' => $shipping_method_title,
				'total'        => $shipping_method_total
			]
		]; */


		/* if ( defined( 'WP_DEBUG' ) ) {
			$api_url = DEV_ORDER_REST_API;
		} else {
			$api_url = "https://hoodslyhub.com/wp-json/order-data/v1/hub";
		}
		$rest_api_url = $api_url;
		$host         = parse_url( get_site_url(), PHP_URL_HOST ); */
		//$domains = explode('.', $host);
		/* $data_string = json_encode( [
			'title'                   => '#' . $order_id . '',
			'order_id'                => intval( $order_id ),
			'data'                    => $details_data,
			'content'                 => '#' . $order_id . '<br>' . $data['shipping']['first_name'] . ' ' . $data['billing']['last_name'] . '<br>' . $data['billing']['email'] . '<br>' . $data['billing']['phone'] . '<br>' . $data['shipping']['address_1'] . $data['shipping']['address_2'] . ' ,' . $data['shipping']['city'] . ' ,' . $data['shipping']['state'] . ' ' . $data['shipping']['postcode'] . '',
			'status'                  => 'publish',
			'estimated_shipping_date' => get_post_meta( $order_id, 'estimated_shipping_date', true ),
			//'bill_of_landing_id'      => get_post_meta( $order_id, 'bill_of_landing_id', true ),
			'origin'                  => $host,
			'order_date'              => $order_date,
			'meta_data'               => $formatted_meta_data,
			'product_height'          => $item_Size,
			'product_name'            => $productName,
			'reduce_height'           => $reduce_height,
			'product_cat'             => $product_catSlug,
			'product_catName'         => $product_catName,
			'tradewinds_quickship'    => $tradewinds_quickship,
			'product_sku'             => $item_sku,
			'order_status'            => $order_status,
			'custom_color_match'      => $custom_color_match,
			//'order_summery'           => $order_summery,
		] ); */

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
		$product_catSlug              = [];
		$product_catName              = [];
		$productName                  = [];
		$item_Size                    = '';
		$height                       = '';
		$tradewinds_sku               = '';

		$user = $order->get_user();
		// Get the WP_User roles and capabilities
		$user_roles = $user->roles[0];
		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];
			$ups_req_data    = [
				'woocommerce_dimension_unit' => get_option( 'woocommerce_dimension_unit' ),
				'woocommerce_weight_unit'    => get_option( 'woocommerce_weight_unit' ),
				'weight'                     => $product->get_weight(),
				'width'                      => $product->get_width(),
				'length'                     => $product->get_length(),
				'height'                     => $product->get_height(),
			];

			$item_sku                  = $product->get_sku();
			$item_data                 = $item_values->get_data();
			$new_arr                   = [];
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
				$display_value = str_replace( [ '<p>', '</p>' ], [
					'',
					''
				], html_entity_decode( $value['display_value'] ) );
				if ( trim( $display_value ) == 'TradeWinds Select For Pricing' ) {
					$is_tradewinds_selected = 'yes';
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
					$color     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$color_key = $value['value'];
				}

				// Get the size of the product
				if ( $value['display_key'] === 'Size' ) {
					$size     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$size_key = $value['value'];
				}


				// Ge the SKU from product
				if ( $value['key'] === 'SKU' ) {
					$sku            = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$sku_key        = $value['value'];
					$tradewinds_sku = explode( '-', $sku );
				}
				// Ge the Removed Trim from product
				if ( $value['display_key'] === 'Trim Options' ) {
					$trim_options     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$trim_options_key = $value['value'];

				}

				// Ge the Removed Trim from product
				$remove_trim_arr = [ 'trim_options_brass_strapping', 'trim_options_walnut_band', 'trim_options_brass_buttons', 'remove_your_trim' ];
				if ( in_array( $value['key'], $remove_trim_arr ) ) {
					$remove_trim     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$remove_trim_key = $value['value'];
				}

				// Ge the Crown Molding
				$crown_molding_arr = [ 'brass_crown_molding', 'molding_loose_installed', 'top_strap_steel' ];
				if ( in_array( $value['key'], $crown_molding_arr ) ) {
					$molding     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$molding_key = $value['value'];
				}

				// Ge the Increase Depth
				$depth_arr = [ 'curved_depth', 'depth_noncurved', 'vah_19', 'vah_225' ];
				if ( in_array( $value['key'], $depth_arr ) ) {
					$increase_depth     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$increase_depth_key = $value['value'];
				}

				// Ge the Reduce height
				if ( $value['key'] === 'reduce_height' ) {
					$reduce_height     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$reduce_height_key = $value['value'];
				}

				// Ge the Extended Chimney
				if ( $value['key'] === 'extend_your_chimney' ) {
					$extend_chimney     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$extend_chimney_key = $value['value'];
				}

				// Ge the Solid Bottom Data
				$solid_arr = [ 'solid_bottom_normal_200', 'solid_bottom_corbels' ];
				if ( in_array( $value['key'], $solid_arr ) ) {
					$solid_button     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$solid_button_key = $value['value'];
				}

				// Get the EPO ref for customer
				if ( $value['key'] === 'reference_for_customer' ) {
					$reference_for_customer = $rush_my_order = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
				}
				// Ge the Rush Manufacturing data
				if ( $value['key'] === 'rushed_manufacturing' ) {
					$rush_my_order     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
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
			$product_cat_slug = [];
			$product_cat_name = [];
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_cat_slug[] = $term->slug;
				$product_cat_name[] = $term->name;
			}
			$inc_tax         = true;
			$round           = false; // Not rounded at item level ("true"  for rounding at item level)
			$product_name    = $item_values['name'];
			$product_pattern = "/[\s\S]*?(?=-)/i";
			preg_match_all( $product_pattern, $product_name, $product_matches );
			$productName = trim( $product_matches[0][0] );

			$new_arr['product_id']           = $item_data['product_id'];
			$new_arr['tradewinds_sku']       = trim( $tradewinds_sku[0] );
			$new_arr['tradewinds_quickship'] = $tradewinds_quickship;
			$new_arr['tradewinds_cat_sku']   = $tradewinds_cat_sku;
			$new_arr['vent_option_data']     = $vent_option_data;
			$new_arr['product_img_url']      = $product_img_url;
			$new_arr['product_name']         = $item_data['name'];
			$new_arr['product_cat']          = $product_cat_slug;
			$new_arr['product_catName']      = $product_cat_name;
			$new_arr['item_total']           = $order->get_line_total( $item_values, $inc_tax, $round );
			$new_arr['item_total_tax']       = $order->get_line_tax( $item_values );
			$new_arr['variation_id']         = $item_data['variation_id'];
			$new_arr['quantity']             = $item_data['quantity'];
			$new_arr['color']                = [ 'key' => $color_key, 'value' => $color ];
			$new_arr['sku']                  = [ 'key' => $sku_key, 'value' => $sku ];
			$new_arr['size']                 = [ 'key' => $size_key, 'value' => $size ];
			/*$new_arr['vent_option']            = [ 'key' => $vent_option_key, 'value' => $vent_option ];
			$new_arr['z_vent_filter']          = [ 'key' => $zline_filter_key, 'value' => $zline_filter ];
			$new_arr['z_vent_options']         = [ 'key' => $zline_vent_options_key, 'value' => $zline_vent_options ];*/
			$new_arr['trim_options']           = [ 'key' => $trim_options_key, 'value' => $trim_options ];
			$new_arr['remove_trim']            = [ 'key' => $remove_trim_key, 'value' => $remove_trim ];
			$new_arr['crown_molding']          = [ 'key' => $molding_key, 'value' => $molding ];
			$new_arr['increase_depth']         = [ 'key' => $increase_depth_key, 'value' => $increase_depth ];
			$new_arr['reduce_height']          = [ 'key' => $reduce_height_key, 'value' => $reduce_height ];
			$new_arr['extend_chimney']         = [ 'key' => $extend_chimney_key, 'value' => $extend_chimney ];
			$new_arr['solid_button']           = [ 'key' => $solid_button_key, 'value' => $solid_button ];
			$new_arr['reference_for_customer'] = $reference_for_customer;
			$new_arr['rush_my_order']          = [ 'key' => $rush_my_order_key, 'value' => $rush_my_order ];
			$new_arr['order_meta']             = $formatted_meta_data_array;
			$line_items['line_items'][]        = $new_arr;
			//$productName = $item_data['name'];

		}
		foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {

			$shipping_method_total = $item->get_total();
			$shipping_method_id    = $item->get_method_id(); // The method ID
			$shipping_method_title = $item->get_method_title();
		}
		$details_data       = [
			'payment_method'       => $data['payment_method'],
			'payment_method_title' => $data['payment_method_title'],
			'customer_note'        => $data['customer_note'],
			'set_paid'             => true,
			'meta_data'            => $data['meta_data'],
			'billing'              => [
				'first_name' => $data['billing']['first_name'],
				'last_name'  => $data['billing']['last_name'],
				'address_1'  => $data['billing']['address_1'],
				'address_2'  => $data['billing']['address_2'],
				'city'       => $data['billing']['city'],
				'state'      => $data['billing']['state'],
				'postcode'   => $data['billing']['postcode'],
				'country'    => $data['billing']['country'],
				'email'      => $data['billing']['email'],
				'phone'      => $data['billing']['phone']
			],
			'shipping'             => [
				'first_name' => $data['shipping']['first_name'],
				'last_name'  => $data['shipping']['last_name'],
				'address_1'  => $data['shipping']['address_1'],
				'address_2'  => $data['shipping']['address_2'],
				'city'       => $data['shipping']['city'],
				'state'      => $data['shipping']['state'],
				'postcode'   => $data['shipping']['postcode'],
				'country'    => $data['shipping']['country'],
			],
			'line_items'           => $line_items,
			'shipping_lines'       => [
				'method_id'    => $shipping_method_id,
				'method_title' => $shipping_method_title,
				'total'        => $shipping_method_total
			]
		];
		$this->settings_api = new hoodslyhub_Settings();
		$hub_endpoint       = $this->settings_api->get_option( 'hub_endpoint', 'AOTHub_global_settings', 'text' );
		$rest_api_url       = $hub_endpoint;
		$host               = parse_url( get_site_url(), PHP_URL_HOST );
		//$domains = explode('.', $host);
		$data_string = json_encode( [
			'title'                   => '#' . $order_id . '',
			'order_id'                => intval( $order_id ),
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
			'tradewinds_sku'          => trim($tradewinds_sku[0]),
			'product_sku'             => $item_sku,
			'order_status'            => $order_status,
			'custom_color_match'      => $custom_color_match,
			'is_tradewinds_selected'  => $is_tradewinds_selected,
			'stock_quantity'          => $stock_quantity,
			'ups_req_data'            => $ups_req_data,
			'user_roles'              => $user_roles,
		] );

		$data = wp_remote_post( $rest_api_url, array(
			'body' => $data_string
		) );
	}// End send_order_data

	/**
	 *
	 * Trigger order status hook
	 *
	 * @param $order_id
	 */
	public function send_order_status( $order_id ) {
		$order = wc_get_order( $order_id );

		$line_items                   = array();
		$data                         = $order->get_data();
		$order_date                   = $order->order_date;
		$order_status                 = $order->get_status();
		$order_status                 = wc_get_order_status_name( $order_status );
		$line_items['order_total']    = $order->get_total();
		$line_items['total_quantity'] = $order->get_item_count();
		$product_catSlug              = [];
		$product_catNmae              = [];
		$productName                  = [];
		$item_Size                    = '';
		$height                       = '';
		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];

			$item_sku                  = $product->get_sku();
			$item_data                 = $item_values->get_data();
			$new_arr                   = [];
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


			$item_Size = HoodslyHubHelper::hypemill_product_size( $item_values );
			$terms     = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_catSlug[] = $term->slug;
				$product_catNmae[] = $term->name;
			}

			foreach ( $formatted_meta_data_array as $value ) {
				if ( $value['key'] === 'pa_color' ) {
					$color     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$color_key = $value['value'];
				}

				// Get the size of the product
				if ( $value['display_key'] === 'Size' ) {
					$size     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$size_key = $value['value'];
				}


				// Ge the SKU from product
				if ( $value['key'] === 'SKU' ) {
					$sku     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$sku_key = $value['value'];
				}
				// Ge the Removed Trim from product
				if ( $value['display_key'] === 'Trim Options' ) {
					$trim_options     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$trim_options_key = $value['value'];

				}

				// Ge the Removed Trim from product
				$remove_trim_arr = [ 'trim_options_brass_strapping', 'trim_options_walnut_band', 'trim_options_brass_buttons', 'remove_your_trim' ];
				if ( in_array( $value['key'], $remove_trim_arr ) ) {
					$remove_trim     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$remove_trim_key = $value['value'];
				}

				// Ge the Crown Molding
				$crown_molding_arr = [ 'brass_crown_molding', 'molding_loose_installed', 'top_strap_steel' ];
				if ( in_array( $value['key'], $crown_molding_arr ) ) {
					$molding     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$molding_key = $value['value'];
				}

				// Ge the Increase Depth
				$depth_arr = [ 'curved_depth', 'depth_noncurved', 'vah_19', 'vah_225' ];
				if ( in_array( $value['key'], $depth_arr ) ) {
					$increase_depth     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$increase_depth_key = $value['value'];
				}

				// Ge the Reduce height
				if ( $value['key'] === 'reduce_height' ) {
					$reduce_height     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$reduce_height_key = $value['value'];
				}

				// Ge the Extended Chimney
				if ( $value['key'] === 'extend_your_chimney' ) {
					$extend_chimney     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$extend_chimney_key = $value['value'];
				}

				// Ge the Solid Bottom Data
				$solid_arr = [ 'solid_bottom_normal_200', 'solid_bottom_corbels' ];
				if ( in_array( $value['key'], $solid_arr ) ) {
					$solid_button     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
					$solid_button_key = $value['value'];
				}

				// Get the EPO ref for customer
				if ( $value['key'] === 'reference_for_customer' ) {
					$reference_for_customer = $rush_my_order = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
				}
				// Ge the Rush Manufacturing data
				if ( $value['key'] === 'rushed_manufacturing' ) {
					$rush_my_order     = str_replace( [ '<p>', '</p>' ], [
						'',
						''
					], html_entity_decode( $value['display_value'] ) );
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
					}
				}
			}

			$terms = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_cat_slug = $term->slug;
			}
			$inc_tax         = true;
			$round           = false; // Not rounded at item level ("true"  for rounding at item level)
			$product_name    = $item_values['name'];
			$product_pattern = "/[\s\S]*?(?=-)/i";
			preg_match_all( $product_pattern, $product_name, $product_matches );
			$productName[] = trim( $product_matches[0][0] );

			$new_arr['product_id']      = $item_data['product_id'];
			$new_arr['product_img_url'] = $product_img_url;
			$new_arr['product_name']    = $item_data['name'];
			$new_arr['product_cat']     = $product_cat_slug;
			$new_arr['item_total']      = $order->get_line_total( $item_values, $inc_tax, $round );
			$new_arr['item_total_tax']  = $order->get_line_tax( $item_values );
			$new_arr['variation_id']    = $item_data['variation_id'];
			$new_arr['quantity']        = $item_data['quantity'];
			$new_arr['color']           = [ 'key' => $color_key, 'value' => $color ];
			$new_arr['sku']             = [ 'key' => $sku_key, 'value' => $sku ];
			$new_arr['size']            = [ 'key' => $size_key, 'value' => $size ];
			/*$new_arr['vent_option']            = [ 'key' => $vent_option_key, 'value' => $vent_option ];
			$new_arr['z_vent_filter']          = [ 'key' => $zline_filter_key, 'value' => $zline_filter ];
			$new_arr['z_vent_options']         = [ 'key' => $zline_vent_options_key, 'value' => $zline_vent_options ];*/
			$new_arr['trim_options']           = [ 'key' => $trim_options_key, 'value' => $trim_options ];
			$new_arr['remove_trim']            = [ 'key' => $remove_trim_key, 'value' => $remove_trim ];
			$new_arr['crown_molding']          = [ 'key' => $molding_key, 'value' => $molding ];
			$new_arr['increase_depth']         = [ 'key' => $increase_depth_key, 'value' => $increase_depth ];
			$new_arr['reduce_height']          = [ 'key' => $reduce_height_key, 'value' => $reduce_height ];
			$new_arr['extend_chimney']         = [ 'key' => $extend_chimney_key, 'value' => $extend_chimney ];
			$new_arr['solid_button']           = [ 'key' => $solid_button_key, 'value' => $solid_button ];
			$new_arr['reference_for_customer'] = $reference_for_customer;
			$new_arr['rush_my_order']          = [ 'key' => $rush_my_order_key, 'value' => $rush_my_order ];
			$new_arr['order_meta']             = $formatted_meta_data_array;
			$line_items['line_items'][]        = $new_arr;

		}
		foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {

			$shipping_method_total = $item->get_total();
			$shipping_method_id    = $item->get_method_id(); // The method ID
			$shipping_method_title = $item->get_method_title();
		}
		$details_data              = [
			'payment_method'       => $data['payment_method'],
			'payment_method_title' => $data['payment_method_title'],
			'customer_note'        => $data['customer_note'],
			'set_paid'             => true,
			'meta_data'            => $data['meta_data'],
			'billing'              => [
				'first_name' => $data['billing']['first_name'],
				'last_name'  => $data['billing']['last_name'],
				'address_1'  => $data['billing']['address_1'],
				'address_2'  => $data['billing']['address_2'],
				'city'       => $data['billing']['city'],
				'state'      => $data['billing']['state'],
				'postcode'   => $data['billing']['postcode'],
				'country'    => $data['billing']['country'],
				'email'      => $data['billing']['email'],
				'phone'      => $data['billing']['phone']
			],
			'shipping'             => [
				'first_name' => $data['shipping']['first_name'],
				'last_name'  => $data['shipping']['last_name'],
				'address_1'  => $data['shipping']['address_1'],
				'address_2'  => $data['shipping']['address_2'],
				'city'       => $data['shipping']['city'],
				'state'      => $data['shipping']['state'],
				'postcode'   => $data['shipping']['postcode'],
				'country'    => $data['shipping']['country'],
			],
			'line_items'           => $line_items,
			'shipping_lines'       => [
				'method_id'    => $shipping_method_id,
				'method_title' => $shipping_method_title,
				'total'        => $shipping_method_total
			]
		];
		$this->settings_api        = new hoodslyhub_Settings();
		$hub_order_status_endpoint = $this->settings_api->get_option( 'hub_order_status_endpoint', 'AOTHub_global_settings', 'text' );
		$rest_api_url              = $hub_order_status_endpoint;
		$host                      = parse_url( get_site_url(), PHP_URL_HOST );
		//$domains = explode('.', $host);
		$data_string = json_encode( [
			'title'                   => '#' . $order_id . '',
			'order_id'                => intval( $order_id ),
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
			'product_cat_name'        => $product_catNmae,
			'product_sku'             => $item_sku,
			'order_status'            => $order_status,
			'custom_color_match'      => $custom_color_match,
			'shipping_state'          => $data['shipping']['state'],
		] );

		$data = wp_remote_post( $rest_api_url, array(
			'body' => $data_string
		) );
	}

}//end class HoodslyHub

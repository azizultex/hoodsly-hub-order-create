<?php
/**
 * Plugin Name: Order from any Hoodsly Site To Hoodsly-Hub
 * Plugin URI:  https://wppool.dev
 * Description: This plugin will create order to hoodsly hub from any Hoodsly site.
 * Version:     1.0
 * Author:      Saiful Islam
 * Author URI:  https://wppool.dev
 * Text Domain: hoodsly-hub
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class HoodslyHub {
	const VERSION = '1.0.0';

	public function __construct() {
		add_action( 'woocommerce_thankyou', [ $this, 'send_order_data' ], 10, 1 );
		//add_action( 'admin_init', [ $this, 'test_order_data' ] );
	}

	/**
	 * @param $class
	 * @param $object
	 *
	 * @return mixed
	 */
	function casttoclass( $class, $object ) {
		return unserialize( preg_replace( '/^O:\d+:"[^"]++"/', 'O:' . strlen( $class ) . ':"' . $class . '"', serialize( $object ) ) );
	}

	/**
	 * @param $item_values
	 *
	 * @return mixed
	 */
	function hypemill_product_size( $item_values ) {
		$extraProductOptions       = get_option( 'thwepo_custom_sections' )['default'];
		$islandHoodOptions         = get_option( 'thwepo_custom_sections' )['island_wood_hood_sizes'];
		$size_and_ventilation      = $this->casttoclass( 'stdClass', $extraProductOptions );
		$size_and_ventilation_keys = [];
		foreach ( $size_and_ventilation->fields as $key => $value ) {
			if ( $this->casttoclass( 'stdClass', $value )->type === 'select' ) {
				$size_and_ventilation_keys[] = $key;
			}
		}
		$island_hood = [];
		foreach ( $islandHoodOptions->fields as $key => $value ) {
			if ( $value->type === 'select' ) {
				$island_hood[] = $key;
			}
		}
		$size_keys = array_merge( $size_and_ventilation_keys, $island_hood );
		foreach ( $size_keys as $size_key ) {
			$size = $item_values->get_meta( $size_key, true );
			if ( $size ) {
				$size = $size;
				break;
			}
		}
		//preg_match_all('!\d+!', $size, $matches);
		preg_match( '/(?<=_w_).*/', $size, $match );

		//$finalSize = $matches[0][0] ."x".$matches[0][1];
		return $match[0];
	}

	function test_order_data() {
		$order_id = intval( 26502 );
		$order    = wc_get_order( $order_id );

		$line_items                = array();
		$data                      = $order->get_data();
		$order_date                = $order->order_date;
		$order_status              = $order->get_status();
		$order_status              = wc_get_order_status_name( $order_status );
		$line_items['order_total'] = $order->get_total();
		$line_items['total_quantity'] = $order->get_item_count();
		$product_catSlug           = [];
		$productName               = [];
		$reduce_height             = '';
		$item_Size                 = '';

		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];

			$item_sku            = $product->get_sku();
			$item_data           = $item_values->get_data();
			$new_arr             = [];
			$item_meta_data      = $item_values->get_meta_data();
			$formatted_meta_data = $item_values->get_formatted_meta_data( '_', true );

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
//write_log($formatted_meta_data_array);
			$item_Size = $this->hypemill_product_size( $item_values );

			$terms = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_catSlug[] = $term->slug;
			}

			foreach ( $formatted_meta_data_array as $value ) {
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
			$productName[]                     = trim( $product_matches[0][0] );
			$new_arr['product_id']             = $item_data['product_id'];
			$new_arr['product_img_url']        = $product_img_url;
			$new_arr['product_name']           = $item_data['name'];
			$new_arr['product_cat']            = $product_cat_slug;
			$new_arr['sku']                    = $item_sku;
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

		}
		//write_log( $line_items );

		foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {
			/* $order_item_name             = $item->get_name();
			$order_item_type             = $item->get_type();
			$shipping_method_id          = $item->get_method_id(); // The method ID
			$shipping_method_instance_id = $item->get_instance_id(); // The instance ID
			$shipping_method_total_tax   = $item->get_total_tax();
			$shipping_method_taxes       = $item->get_taxes(); */

			$shipping_method_total = $item->get_total();
			$shipping_method_id    = $item->get_method_id(); // The method ID
			$shipping_method_title = $item->get_method_title();
		}
		/* $data = $order->get_data();
		$endpoint = 'https://hoodslyhub.com/wp-json/wc/v3/orders/'; */

		$details_data = [
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


		if ( defined( 'WP_DEBUG' ) ) {
			$api_url = DEV_ORDER_REST_API;
		} else {
			$api_url = "https://hoodslyhub.com/wp-json/order-data/v1/hub";
		}
		$rest_api_url = $api_url;
		$host         = parse_url( get_site_url(), PHP_URL_HOST );
		//$domains = explode('.', $host);
		//write_log( $product_catSlug );
		$data_string = json_encode( [
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
			'product_sku'             => $item_sku,
			'order_status'            => $order_status,
			'custom_color_match'      => $custom_color_match,
			//'order_summery'           => $order_summery,
		] );

	}

	/**
	 * init function for single tone approach
	 *
	 * @return false|HoodslyHub
	 */
	public static function init() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function send_order_data( $order_id ) {
		$order = wc_get_order( $order_id );

		$line_items                = array();
		$data                      = $order->get_data();
		$order_date                = $order->order_date;
		$order_status              = $order->get_status();
		$order_status              = wc_get_order_status_name( $order_status );
		$line_items['order_total'] = $order->get_total();
		$line_items['total_quantity'] = $order->get_item_count();
		$product_catSlug           = [];
		$productName               = [];
		$item_Size                 = '';
		$height                    = '';
		foreach ( $order->get_items() as $item_key => $item_values ) {

			$product           = wc_get_product( $item_values->get_product_id() );
			$product_image     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item_values ), $item_values );
			$product_image_url = $product_image->get_image();
			$pattern           = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
			preg_match_all( $pattern, $product_image_url, $matches );
			$product_img_url = $matches[0][0];

			//write_log($product_img_url);
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


			$item_Size = $this->hypemill_product_size( $item_values );
			$terms     = get_the_terms( $item_data['product_id'], 'product_cat' );
			foreach ( $terms as $term ) {
				// Categories by slug
				$product_catSlug[] = $term->slug;
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
				$remove_trim_arr = [ 'trim_options_brass_strapping', 'trim_options_walnut_band', 'trim_options_brass_buttons','remove_your_trim' ];
				if ( in_array( $value['key'], $remove_trim_arr )) {
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
				$solid_arr = [ 'solid_bottom_normal_200', 'solid_bottom_corbels'];
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
		$details_data = [
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

		if ( defined( 'WP_DEBUG' ) ) {
			$api_url = DEV_ORDER_REST_API;
		} else {
			$api_url = "https://staging.hoodslyhub.com/wp-json/order-data/v1/hub";
		}
		$rest_api_url = $api_url;
		$host         = parse_url( get_site_url(), PHP_URL_HOST );
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
			'product_sku'             => $item_sku,
			'order_status'            => $order_status,
			'custom_color_match'      => $custom_color_match,
		] );


		$data = wp_remote_post( $rest_api_url, array(
			'body' => $data_string
		) );
	}
}

/**
 * initialise the main function
 *
 * @return false|HoodslyHub
 */
function hoodsly_hub() {
	return HoodslyHub::init();
}

// let's start the plugin
hoodsly_hub();
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


if (!defined('ABSPATH')) {
    exit;
}


final class HoodslyHub{
	const VERSION = '1.0.0';

    public function __construct(){
        add_action('woocommerce_thankyou', [$this, 'send_order_data'], 10, 1);
        add_action('admin_init', [$this, 'test_order_data']);
    }

    function test_order_data(){
        $order = wc_get_order(26333);
        $line_items = array();
        $data = $order->get_data();
        $order_date = $order->order_date;
        $order_status  = $order->get_status();
        $status_label = wc_get_order_status_name( $order_status );
        
        //write_log($order);
        $line_items['order_total'] = $order->get_total();
        foreach ( $order->get_items() as  $item_key => $item_values ) {
            write_log($item_values);
            $product = wc_get_product($item_values->get_product_id());

            $product_img_url = wp_get_attachment_url( $product->get_image_id() );
            
            $item_sku = $product->get_sku();
            $item_data = $item_values->get_data();
            $new_arr = [];
            $item_meta_data = $item_values->get_meta_data();
            $formatted_meta_data = $item_values->get_formatted_meta_data( '_', true );

            foreach($item_data['meta_data'] as $key => $value){
                
                if($value->get_data()['key'] == 'pa_color'){
                    if($value->get_data()['value'] == 'custom-color-match'){
                        $custom_color_match = true;
                    }
                }
            }
        

            $terms = get_the_terms( $item_data['product_id'], 'product_cat' );
            foreach ( $terms as $term ) {
                // Categories by slug
                $product_cat_slug= $term->slug;
            }
            $inc_tax = true; 
            $round   = false; // Not rounded at item level ("true"  for rounding at item level)
            $product_name = $item_values['name'];
            $new_arr['product_id'] = $item_data['product_id'];
            $new_arr['product_img_url'] = $product_img_url;
            $new_arr['item_total'] = $order->get_line_total( $item_values, $inc_tax, $round );
            $new_arr['item_total_tax'] = $order->get_line_tax($item_values);
            $new_arr['product_name'] = $item_data['name'];
            $new_arr['variation_id'] = $item_data['variation_id'];
            $new_arr['quantity'] = $item_data['quantity'];
            $new_arr['order_meta'] = $formatted_meta_data;
            $line_items['line_items'][] = $new_arr;
        }
    }

	/**
     * init function for single tone approach
     *
     * @return false|HoodslyHub
	 */
    public static function init(){
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }

    public function send_order_data( $order_id ){
        $order = wc_get_order($order_id);

        $order_history = wc_get_order_notes( array(
            'order_id' => $order_id,
           'orderby'  => 'date_created_gmt',
          ) );
        $order_summery = json_decode(json_encode($order_history), true);

        $line_items = array();
        $data = $order->get_data();
        $order_date = $order->order_date;
        $order_status  = $order->get_status();
        $order_status = wc_get_order_status_name( $order_status );
        $line_items['order_total'] = $order->get_total();
        foreach ( $order->get_items() as  $item_key => $item_values ) {
            //write_log($item_values);
            $product = wc_get_product($item_values->get_product_id());
            $product_img_url = wp_get_attachment_url( $product->get_image_id() );
            $item_sku = $product->get_sku();
            $item_data = $item_values->get_data();
            $new_arr = [];
            $item_meta_data = $item_values->get_meta_data();
            $formatted_meta_data = $item_values->get_formatted_meta_data( '_', true );

            foreach($item_data['meta_data'] as $key => $value){
                
                if($value->get_data()['key'] == 'pa_color'){
                    if($value->get_data()['value'] == 'custom-color-match'){
                        $custom_color_match = true;
                    }
                }
            }

            $terms = get_the_terms( $item_data['product_id'], 'product_cat' );
            foreach ( $terms as $term ) {
                // Categories by slug
                $product_cat_slug= $term->slug;
            }
            $inc_tax = true; 
            $round   = false; // Not rounded at item level ("true"  for rounding at item level)
            $product_name = $item_values['name'];
            $new_arr['product_id'] = $item_data['product_id'];
            $new_arr['product_img_url'] = $product_img_url;
            $new_arr['product_name'] = $item_data['name'];
            $new_arr['item_total'] = $order->get_line_total( $item_values, $inc_tax, $round );
            $new_arr['item_total_tax'] = $order->get_line_tax($item_values);
            $new_arr['variation_id'] = $item_data['variation_id'];
            $new_arr['quantity'] = $item_data['quantity'];
            $new_arr['order_meta'] = $formatted_meta_data;
            $line_items['line_items'][] = $new_arr;
        }
        foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
            /* $order_item_name             = $item->get_name();
            $order_item_type             = $item->get_type();
            $shipping_method_id          = $item->get_method_id(); // The method ID
            $shipping_method_instance_id = $item->get_instance_id(); // The instance ID
            $shipping_method_total_tax   = $item->get_total_tax();
            $shipping_method_taxes       = $item->get_taxes(); */

            $shipping_method_total       = $item->get_total();
            $shipping_method_id          = $item->get_method_id(); // The method ID
            $shipping_method_title       = $item->get_method_title();
        }
        /* $data = $order->get_data();
        $endpoint = 'https://hoodslyhub.com/wp-json/wc/v3/orders/'; */
        $details_data = [
            'payment_method' => $data['payment_method'],
            'payment_method_title' => $data['payment_method_title'],
            'customer_note' => $data['customer_note'],
            'set_paid' => true,
            'meta_data' => $data['meta_data'],
            'billing' => [
                'first_name' => $data['billing']['first_name'],
                'last_name' => $data['billing']['last_name'],
                'address_1' => $data['billing']['address_1'],
                'address_2' => $data['billing']['address_2'],
                'city' => $data['billing']['city'],
                'state' => $data['billing']['state'],
                'postcode' => $data['billing']['postcode'],
                'country' => $data['billing']['country'],
                'email' => $data['billing']['email'],
                'phone' => $data['billing']['phone']
            ],
            'shipping' => [
                'first_name' => $data['shipping']['first_name'],
                'last_name' => $data['shipping']['last_name'],
                'address_1' => $data['shipping']['address_1'],
                'address_2' => $data['shipping']['address_2'],
                'city' => $data['shipping']['city'],
                'state' => $data['shipping']['state'],
                'postcode' => $data['shipping']['postcode'],
                'country' => $data['shipping']['country'],
            ],
            'line_items' => $line_items,
            'shipping_lines' => [
                [
                    'method_id' => $shipping_method_id,
                    'method_title' => $shipping_method_title,
                    'total' => $shipping_method_total
                ]
            ]
        ];


	    if ( defined( 'WP_DEBUG' ) ) {
		    $api_url = DEV_ORDER_REST_API;
	    }else{
		    $api_url = "https://hoodslyhub.com/wp-json/order-data/v1/hub";
	    }
        $rest_api_url = $api_url;
        $host = parse_url(get_site_url(), PHP_URL_HOST);
        //$domains = explode('.', $host);

        $data_string = json_encode([
            'title'    => '#'.$order_id.'',
            'order_id'    => $order_id,
            'data' => $details_data,
            'content'  => '#'.$order_id.'<br>'.$data['shipping']['first_name'].' '.$data['billing']['last_name'].'<br>'.$data['billing']['email'].'<br>'.$data['billing']['phone'].'<br>'. $data['shipping']['address_1'] . $data['shipping']['address_2']. ' ,'. $data['shipping']['city'].' ,'. $data['shipping']['state'].' '.$data['shipping']['postcode'].'',
            'status'   => 'publish',
            'estimated_shipping_date' => get_post_meta($order_id,'estimated_shipping_date', true),
            'origin'   => $host,
            'order_date'   => $order_date,
            'meta_data'   => $formatted_meta_data,
            // 'order_data'   => $line_items,
            'product_name'   => $product_name,
            'product_cat'   => $product_cat_slug,
            'product_sku'   => $item_sku,
            'order_status'   => $order_status,
            'custom_color_match'   => $custom_color_match,
            'order_summery' => $order_summery,
        ]);

        wp_remote_post( $rest_api_url, array(
            'body'    => $data_string
        ) );
    }
}

/**
 * initialise the main function
 *
 * @return false|HoodslyHub
 */
function hoodsly_hub()
{
    return HoodslyHub::init();
}

// let's start the plugin
hoodsly_hub();
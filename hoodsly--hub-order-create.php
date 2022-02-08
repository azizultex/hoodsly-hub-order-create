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
        //add_action('admin_init', [$this, 'test']);
    }

    public function test(){
        $order = wc_get_order(26329);
        //write_log($order);
        $line_items = array();
        $data = $order->get_data();
        // write_log(get_option('thwepo_options_name_title_map'));
        // /write_log(get_post_meta(13922,'thwepo_advanced_settings'));
        $order_date = $order->order_date;
        foreach ( $order->get_items() as  $item_key => $item_values ) {
            $item_data = $item_values->get_data();
            $new_arr = [];

            $item_meta_data = $item_values->get_meta_data();
            $formatted_meta_data = $item_values->get_formatted_meta_data( '_', true );
           
            foreach($item_data['meta_data'] as $key => $value){
                if($value->get_data()['key'] == 'pa_color'){
                    if($value->get_data()['value'] == 'custom-color-match'){
                        $custom_color_match = "true";
                    }
                }
            }
            write_log($custom_color_match);
            
            $terms = get_the_terms( $item_data['product_id'], 'product_cat' );
            foreach ( $terms as $term ) {
                // Categories by slug
                $product_cat_slug= $term->slug;
            }
            $new_arr['product_id'] = $item_data['product_id'];
            $new_arr['variation_id'] = $item_data['variation_id'];
            $new_arr['quantity'] = $item_data['quantity'];
            $line_items[] = $new_arr;
            
        }
        $host = parse_url(get_site_url(), PHP_URL_HOST);
        $domains = explode('.', $host);
        //write_log($data);
        //write_log($formatted_meta_data);
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
        // $details_data = [
        //     'payment_method' => $data['payment_method'],
        //     'payment_method_title' => $data['payment_method_title'],
        //     'customer_note' => $data['customer_note'],
        //     'set_paid' => true,
        //     'meta_data' => $data['meta_data'],
        //     'billing' => [
        //         'first_name' => $data['billing']['first_name'],
        //         'last_name' => $data['billing']['last_name'],
        //         'address_1' => $data['billing']['address_1'],
        //         'address_2' => $data['billing']['address_2'],
        //         'city' => $data['billing']['city'],
        //         'state' => $data['billing']['state'],
        //         'postcode' => $data['billing']['postcode'],
        //         'country' => $data['billing']['country'],
        //         'email' => $data['billing']['email'],
        //         'phone' => $data['billing']['phone']
        //     ],
        //     'shipping' => [
        //         'first_name' => $data['shipping']['first_name'],
        //         'last_name' => $data['shipping']['last_name'],
        //         'address_1' => $data['shipping']['address_1'],
        //         'address_2' => $data['shipping']['address_2'],
        //         'city' => $data['shipping']['city'],
        //         'state' => $data['shipping']['state'],
        //         'postcode' => $data['shipping']['postcode'],
        //         'country' => $data['shipping']['country'],
        //     ],
        //     'line_items' => $line_items,
        //     'shipping_lines' => [
        //         [
        //             'method_id' => $shipping_method_id,
        //             'method_title' => $shipping_method_title,
        //             'total' => $shipping_method_total
        //         ]
        //     ]
        // ];

        /* $rest_api_url = "https://hoodslyhub.com/wp-json/order-data/v1/hub";
        $host = parse_url(get_site_url(), PHP_URL_HOST);
        $domains = explode('.', $host);

        $data_string = json_encode([
            'title'    => '#13922',
            'order_id'    => '13922',
            'data' => $details_data,
            'content'  => '#13922 '.'<br>'.$data['shipping']['first_name'].' '.$data['billing']['last_name'].'<br>'.$data['billing']['email'].'<br>'.$data['billing']['phone'].'<br>'. $data['shipping']['address_1'] . $data['shipping']['address_2']. ' ,'. $data['shipping']['city'].' ,'. $data['shipping']['state'].' '.$data['shipping']['postcode'].'',
            'status'   => 'publish',
            'origin'   => $domains[count($domains)-2],
            'order_date'   => $order_date,
        ]); */

    /* $response = wp_remote_post( $rest_api_url, array(
        'body'    => $data_string
    ) ); */
    //write_log($response);
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
        
        $line_items = array();
        $data = $order->get_data();
        $order_date = $order->order_date;
        $order_status  = $order->get_status();
        foreach ( $order->get_items() as  $item_key => $item_values ) {
            //write_log($item_values);
            $product = wc_get_product($item_values->get_product_id());
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
            $product_name = $item_values['name'];
            $new_arr['product_id'] = $item_data['product_id'];
            $new_arr['variation_id'] = $item_data['variation_id'];
            $new_arr['quantity'] = $item_data['quantity'];
            $line_items[] = $new_arr;
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

        $rest_api_url = "http://localhost/hoodsly_hub/wp-json/order-data/v1/hub";
	    if ( defined( 'WP_DEBUG' ) ) {
		    $api_url = DEV_REST_API;
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
            'product_name'   => $product_name,
            'product_cat'   => $product_cat_slug,
            'product_sku'   => $item_sku,
            'order_status'   => $order_status,
            'custom_color_match'   => $custom_color_match,
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
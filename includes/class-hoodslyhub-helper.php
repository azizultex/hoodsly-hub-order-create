<?php
/**
 * The helper functionality of the plugin.
 *
 * @link       https://codeboxr.com
 * @since      1.0.0
 *
 * @package    HoodslyHub
 * @subpackage HoodslyHub/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Helper functionality of the plugin.
 *
 * lots of micro methods that help get set
 *
 * @package    HoodslyHub
 * @subpackage HoodslyHub/includes
 * @author     wppool <info@wppool.dev>
 */
class HoodslyHubHelper {

	/**
	 * @param $class
	 * @param $object
	 *
	 * @return mixed
	 */
	public static function casttoclass( $class, $object ) {
		return unserialize( preg_replace( '/^O:\d+:"[^"]++"/', 'O:' . strlen( $class ) . ':"' . $class . '"', serialize( $object ) ) );
	}

	/**
	 * @param $item_values
	 *
	 * @return mixed
	 */
	public static function hypemill_product_size( $item_values ) {
		$extraProductOptions       = get_option( 'thwepo_custom_sections' )['default'];
		$islandHoodOptions         = get_option( 'thwepo_custom_sections' )['island_wood_hood_sizes'];
		$size_and_ventilation      = HoodslyHubHelper::casttoclass( 'stdClass', $extraProductOptions );
		$size_and_ventilation_keys = [];
		foreach ( $size_and_ventilation->fields as $key => $value ) {
			if ( HoodslyHubHelper::casttoclass( 'stdClass', $value )->type === 'select' ) {
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

}//end class HoodslyHubHelper
<?php
/**
 *
 * @link              https://wppool.dev
 * @since             1.0.0
 * @package           hoodslyhub
 *
 * @wordpress-plugin
 * Plugin Name: Order from any Hoodsly Site To Hoodsly-Hub
 * Plugin URI:  https://wppool.dev
 * Description: This plugin will create order to hoodsly hub from any Hoodsly site.
 * Version:     1.0.3
 * Author:      Saiful Islam
 * Author URI:  https://wppool.dev
 * Text Domain: hoodslyhub
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//plugin definition specific constants
defined( 'HOODSLYHUB_PLUGIN_NAME' ) or define( 'HOODSLYHUB_PLUGIN_NAME', 'hoodslyhub' );
defined( 'HOODSLYHUB_PLUGIN_VERSION' ) or define( 'HOODSLYHUB_PLUGIN_VERSION', '1.0.3' );
defined( 'HOODSLYHUB_PLUGIN_BASE_NAME' ) or define( 'HOODSLYHUB_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'HOODSLYHUB_PLUGIN_ROOT_PATH' ) or define( 'HOODSLYHUB_PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'HOODSLYHUB_PLUGIN_ROOT_URL' ) or define( 'HOODSLYHUB_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hoodslyhub-activator.php
 */
function activate_hoodslyhub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hoodslyhub-activator.php';
	hoodslyhub_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hoodslyhub-deactivator.php
 */
function deactivate_hoodslyhub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hoodslyhub-deactivator.php';
	hoodslyhub_Deactivator::deactivate();
}

function uninstall_hoodslyhub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hoodslyhub-uninstall.php';
	hoodslyhub_Uninstall::uninstall();
}

register_activation_hook( __FILE__, 'activate_hoodslyhub' );
register_deactivation_hook( __FILE__, 'deactivate_hoodslyhub' );
register_uninstall_hook( __FILE__, 'uninstall_hoodslyhub' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-hoodslyhubOrderCreate.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_hoodslyhubOrderCreate() {

	$plugin = new hoodslyhubOrderCreate();
	$plugin->run();

}

run_hoodslyhubOrderCreate();

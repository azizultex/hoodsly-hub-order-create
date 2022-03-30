<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wppool.dev
 * @since      1.0.0
 *
 * @package    hoodslyhub
 * @subpackage hoodslyhub/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    hoodslyhubapi
 * @subpackage hoodslyhub/includes
 * @author     wppool <info@wppool.dev>
 */
class hoodslyhubOrderCreate {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      hoodslyhub_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->version     = HOODSLYHUB_PLUGIN_VERSION;
		$this->plugin_name = HOODSLYHUB_PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - hoodslyhub_Loader. Orchestrates the hooks of the plugin.
	 * - hoodslyhub_i18n. Defines internationalization functionality.
	 * - hoodslyhub_Admin. Defines all hooks for the admin area.
	 * - hoodslyhub_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoodslyhub-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoodslyhub-i18n.php';

		/**
		 * This class responsible for help methods
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoodslyhub-helper.php';

		/**
		 * This class responsible for plugin setting
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hoodslyhub-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hoodslyhub-admin.php';


		$this->loader = new hoodslyhub_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the hoodslyhub_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new hoodslyhub_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $wp_version;

		$plugin_admin = new hoodslyhub_Admin( $this->get_plugin_name(), $this->get_version() );


		//add js and css in admin end
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//on admin init setting init and hoodslyhub type post tdelete hook
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );

		// add global settings menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu', 11 );

		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'send_order_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'send_order_status', 10, 1 );
		//$this->loader->add_action( 'admin_init', $plugin_admin, 'test_order_data', 10, 1 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    hoodslyhub_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}//end method hoodslyhub

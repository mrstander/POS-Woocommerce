<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/includes
 * @author     Your Name <email@example.com>
 */
class Woocommerce_Jt_Trader_Extender {
    /** @var object single instance of plugin */
    protected static $instance;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();

	}

    /**
     * Load the required dependencies for the Admin facing functionality.
     *
     * Include the following files that make up the plugin:
     *
     * - Woocommerce_Jt_Trader_Admin_Settings. Registers the admin settings and page.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    protected function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
    }

    public function load_hooks() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Jt_Trader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Jt_Trader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Jt_Trader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Jt_Trader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	}

    /**
     * Gets the main loader instance.
     *
     * Ensures only one instance can be loaded.
     *
     * @param $plugin_name
     * @param $version
     * @return static $instance
     * @since 2.4.0
     *
     */
    public static function instance($plugin_name, $version) {

        if ( null === self::$instance ) {
            self::$instance = new self($plugin_name, $version);
        }

        return self::$instance;
    }

}

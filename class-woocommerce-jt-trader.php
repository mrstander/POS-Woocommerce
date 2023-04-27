<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/includes
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
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/includes
 * @author     Your Name <email@example.com>
 */
class Woocommerce_Jt_Trader {


    /** @var object single instance of plugin */
    protected static $instance;

    /** @var string plugin path, without trailing slash */
    private $plugin_path;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_Jt_Trader_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $woocommerce_jt_trader    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
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
		if ( defined( 'WOOCOMMERCE_JT_TRADER_VERSION' ) ) {
			$this->version = WOOCOMMERCE_JT_TRADER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-jt-trader';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Jt_Trader_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Jt_Trader_i18n. Defines internationalization functionality.
	 * - Woocommerce_Jt_Trader_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Jt_Trader_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        /**
         * The class responsible for our interactions with the db
         */
        require_once $this->get_plugin_path() . '/includes/class-woocommerce-jt-trader-model.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $this->get_plugin_path() . '/includes/class-woocommerce-jt-trader-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $this->get_plugin_path() . '/includes/class-woocommerce-jt-trader-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $this->get_plugin_path() . '/admin/class-woocommerce-jt-trader-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $this->get_plugin_path() . '/public/class-woocommerce-jt-trader-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $this->get_plugin_path() . '/includes/class-woocommerce-jt-trader-extender.php';

		$this->loader = new Woocommerce_Jt_Trader_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Jt_Trader_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_Jt_Trader_i18n();

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

		$plugin_admin = new Woocommerce_Jt_Trader_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new Woocommerce_Jt_Trader_Admin_Settings( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_menu' );
        $this->loader->add_action( 'admin_init', $plugin_settings, 'initialize_api_options' );

        if (woocommerce_jt_trader_required_plugins_available(["woocommerce"])) {
            $this->loader->add_action('woocommerce_order_status_processing', $plugin_admin, 'schedule_order_submission');
            $this->loader->add_action('wp_ajax_jt_trader_import', $plugin_admin, 'process_ajax_import');
            $this->loader->add_action('jt_trader_background_product_import', $plugin_admin, 'process_background_product_import');
            $this->loader->add_action('jt_trader_background_promotion_import', $plugin_admin, 'process_background_promotion_import');
            $this->loader->add_action('jt_trader_background_purge_imports', $plugin_admin, 'purge_imports');
            $this->loader->add_action('jt_trader_background_order_submission', $plugin_admin, 'process_order');
        }



    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_Jt_Trader_Public( $this->get_plugin_name(), $this->get_version() );
        $plugin_admin = new Woocommerce_Jt_Trader_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action('wp_ajax_jt_trader_import', $plugin_admin, 'process_ajax_import');


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
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Jt_Trader_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * Gets the plugin's path without a trailing slash.
     *
     * e.g. /path/to/wp-content/plugins/plugin-directory
     *
     * @since 2.0.0
     *
     * @return string
     */
    public function get_plugin_path() {

        if ( null === $this->plugin_path ) {
            $this->plugin_path = untrailingslashit( plugin_dir_path( $this->get_file() ) );
        }

        return $this->plugin_path;
    }

    /**
     * Returns __FILE__.
     *
     * @since 1.5
     *
     * @return string the full path and filename of the plugin file
     */
    public function get_file() {

        return __FILE__;
    }

    /**
     * Gets the main loader instance.
     *
     * Ensures only one instance can be loaded.
     *
     * @since 2.4.0
     *
     * @return \Woocommerce_Jt_Trader
     */
    public static function instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function log($value, $exit=false) {
        echo "<pre>" . print_r($value, true) . "</pre>";
        if ($exit) {
            exit;
        }
    }

    public function get_soap_client($wsdl_file = 'trader') {
        require_once($this->get_plugin_path() . "/lib/import/lib/TraderSoapClient.php");
        return new TraderSoapClient($wsdl_file);

    }

    /**
     * @return Woocommerce_Jt_Trader_Model
     */
    public function get_model() {
        return Woocommerce_Jt_Trader_Model::instance();
    }

}

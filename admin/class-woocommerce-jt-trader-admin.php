<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/admin
 * @author     Your Name <email@example.com>
 */
class Woocommerce_Jt_Trader_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
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
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/class-woocommerce-jt-trader-admin-settings.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) .  'lib/import/class-trader-import.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) .  'lib/import/class-trader-order.php';
    }

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-jt-trader-admin.css', array(), $this->version, 'all' );

		if (isset($_GET["import"])) {
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-jt-trader-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * This function introduces the theme options into the 'Appearance' menu and into a top-level
     * 'WPPB Demo' menu.
     */
    public function setup_menu() {
        add_submenu_page( 'woocommerce',
            'Trader',
            'Trader',
            'manage_options',
            'wc_jt_trader',
            array($this, 'render_page'));
    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     * @param string $active_tab
     */
    public function render_page( $active_tab = '' ) {
        $model = Woocommerce_Jt_Trader_Model::instance();

        if (isset($_GET['import_dump'])) {
            $importer = new TraderImport($model);
            echo "<pre>" . $importer->dumpProducts() . "</pre>";
            exit;
        }

        if (isset($_GET['import_dump_promotions'])) {
            $importer = new TraderImport($model);
            echo "<pre>" . $importer->dumpPromotions() . "</pre>";
            exit;
        }

        if (isset($_GET['enable_background_processing'])) {
            return $this->enable_background_processing();
        }

        if (isset($_GET['purge_imports'])) {
            woocommerce_jt_trader()->log($this->purge_imports(), true);
        }

        if (isset($_GET['import'])) {
            if ($_GET['import'] === "products" || $_GET['import'] === "promotions") {
                return $this->loadImportForm($_GET['import']);
            }
            $importer = new TraderImport($model);
            $import_results = $importer->run($_GET['import']);
        } else {
            if (isset($_GET['retry_order'])) {
                $this->process_order($_GET['retry_order']);
                $_GET["tab"] = "orders";
            }
        }

        if (isset($_GET["details"])) {
            $details = $model->fetchImport($_GET["details"]);
            if (!empty($details) && !empty($details->report)) {
                $details->report = json_decode($details->report);
            }
        } elseif (isset($_GET["order"])) {
            $order_details = $model->fetchOrder($_GET["order"]);
            if (!empty($order_details) && !empty($order_details->report)) {
                $order_details->report = json_decode($order_details->report);
            }
        } else {
            $active_tab = (empty($active_tab) && isset($_GET["tab"]) && !empty($_GET["tab"])) ? $_GET["tab"] : "products";

            if ($active_tab === "products") {
                $imports = $model->fetchImports("product");
            } elseif ($active_tab === "promotions") {
                $imports = $model->fetchImports("promotion");
            } elseif ($active_tab === "orders") {
                $orders = $model->fetchOrders();
            }
        }

        include plugin_dir_path( dirname( __FILE__ ) ) .  'admin/partials/woocommerce-jt-trader-admin-display.php';
    }

    public function process_order($order_id) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }
        $model = Woocommerce_Jt_Trader_Model::instance();
        $processor = new TraderOrder($model);
        $processor->run($order);
    }

    public function convertToTimezone($timestamp, $timezone='Africa/Johannesburg', $format='Y-m-d H:i:s')
    {
        $dt = new DateTime();
        $dt->setTimezone(new DateTimeZone($timezone));
        $dt->setTimestamp($timestamp);
        return $dt->format($format);
    }

    public function loadImportForm($type) {
        $include_path = plugin_dir_path( dirname( __FILE__ ) ) .  "admin/partials/";
        $filename = $type . "-form.php";
        wp_localize_script(
            $this->plugin_name,
            'jt_trader_import_params',
            array(
                'import_nonce'    => wp_create_nonce( 'jt-trader-import' )
            )
        );
        wp_enqueue_script( $this->plugin_name );
        include "$include_path/importer/$filename";
        return true;
    }

    public function process_ajax_import() {
        $model = Woocommerce_Jt_Trader_Model::instance();
        $import_type = $_REQUEST["type"] ? $_REQUEST["type"] : "products";
        $importer = new TraderImport($model);
        $imported = $importer->run($import_type);
        $results = $imported["results"];
        $percent_complete = absint( min( round( ((intval($results->total) - intval($imported["remaining"])) / intval($results->total)) * 100 ), 100 ) );

        wp_send_json_success(
            array(
                'percentage' => $percent_complete,
                'url'        => admin_url( 'admin.php?page=wc_jt_trader&details=' . $results->id ),
                'created'    => $results->num_created,
                'failed'     => $results->num_errors,
                'updated'    => $results->num_updated,
                'skipped'    => $results->num_skipped,
                'remaining'  => intval($imported["remaining"]),
                'total'      => $results->total
            )
        );
    }

    public function process_background_product_import() {
        $imported = $this->process_import("products");
        if (intval($imported["remaining"]) > 0) {
            WC()->queue()->schedule_single(time() + 1, "jt_trader_background_product_import", array(), "jt_trader_background_imports");
        }
    }

    public function process_background_promotion_import() {
        $imported = $this->process_import("promotions");
        if (intval($imported["remaining"]) > 0) {
            WC()->queue()->schedule_single(time() + 1, "jt_trader_background_promotion_import", array(), "jt_trader_background_imports");
        }
    }

    public function schedule_order_submission($order_id) {
        WC()->queue()->schedule_single(time() + 1, "jt_trader_background_order_submission", ["order_id" => $order_id], "jt_trader_background_imports");
    }

    private function process_import($import_type) {
        $model = Woocommerce_Jt_Trader_Model::instance();
        $importer = new TraderImport($model);
        return $importer->run($import_type);
    }

    private function log($title, $data) {
        if ($title) {
            echo "<h3>$title</h3>";
        }
        echo "<pre>" . print_r($data, true) . "</pre>";
    }

    private function cancel_all_processing(){
        WC()->queue()->cancel_all('jt_trader_background_product_import', array(), 'jt_trader_background_imports');
        WC()->queue()->cancel_all('jt_trader_background_promotion_import', array(), 'jt_trader_background_imports');
        WC()->queue()->cancel_all('jt_trader_background_stock_import', array(), 'jt_trader_background_imports');
        // WC()->queue()->cancel_all('jt_trader_background_process_customer');
        // WC()->queue()->cancel_all('jt_trader_background_order_submission');
        echo "CANCELLED";
    }

    private function enable_background_processing(){
        WC()->queue()->schedule_cron(
            time(),
            "0 1 * * *",
            'jt_trader_background_product_import',
            array(),
            'jt_trader_background_imports'
        );

        WC()->queue()->schedule_cron(
            time(),
            "0 13 * * *",
            'jt_trader_background_product_import',
            array(),
            'jt_trader_background_imports'
        );

        WC()->queue()->schedule_cron(
            time(),
            "30 2 * * *",
            'jt_trader_background_promotion_import',
            array(),
            'jt_trader_background_imports'
        );

        WC()->queue()->schedule_cron(
            time(),
            "30 14 * * *",
            'jt_trader_background_promotion_import',
            array(),
            'jt_trader_background_imports'
        );

        WC()->queue()->schedule_cron(
            time(),
            "* * * 0 *",
            'jt_trader_background_purge_imports',
            array(),
            'jt_trader_background_imports'
        );
        echo "ENABLED";
    }

    public function purge_imports() {
        return woocommerce_jt_trader()->get_model()->purgeImports();
    }
}

<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://jumptag.co.za
 * @since             1.0.0
 * @package           Woocommerce_Jt_Trader
 *
 * @wordpress-plugin
 * Plugin Name:       Jumptag WooCommerce Trader
 * Plugin URI:        http://jumptag.co.za
 * Description:       Manages Trader Product Imports and Trader Order creation
 * Version:           1.0.0
 * Author:            Jumptag Web Development
 * Author URI:        http://jumptag.co.za
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-jt-trader
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_JT_TRADER_VERSION', '1.1.0' );

/**
 * Warehouse Store IDs
 * Defined in the Trader "Stores" table,  we use these IDs for numerous checks;
 *  - Determining whether local shipping is available on checkout
 *  - Determining whether products have stock available for shipping
 */
define( 'WOOCOMMERCE_JT_TRADER_WAREHOUSE_IDS', [7 => 'Cape Town', 14 => 'Pretoria', 12 => 'Durban'] );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'class-woocommerce-jt-trader.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-jt-trader-activator.php
 */
function activate_woocommerce_jt_trader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-jt-trader-activator.php';
	Woocommerce_Jt_Trader_Activator::activate(Woocommerce_Jt_Trader_Model::instance());
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-jt-trader-deactivator.php
 */
function deactivate_woocommerce_jt_trader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-jt-trader-deactivator.php';
	Woocommerce_Jt_Trader_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_jt_trader' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_jt_trader' );

function woocommerce_jt_trader_required_plugins_available($required_plugins=[]) {
    if (empty($required_plugins))
        return true;
    $active_plugins = (array) get_option('active_plugins', array());
    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }
    foreach ($required_plugins as $key => $required) {
        $required = (!is_numeric($key)) ? "{$key}/{$required}.php" : "{$required}/{$required}.php";
        if (!in_array($required, $active_plugins) && !array_key_exists($required, $active_plugins))
            return false;
    }
    return true;
}


/**
 * Returns the One True Instance of Woocomerce JT Trader.
 *
 * @since 2.0.0-dev.1
 *
 * @return \Woocommerce_Jt_Trader
 */
function woocommerce_jt_trader() {

    return \Woocommerce_Jt_Trader::instance();
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_jt_trader() {
    woocommerce_jt_trader()->run();
}
run_woocommerce_jt_trader();

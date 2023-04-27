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
class Woocommerce_Jt_Trader_Admin_Settings {

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

	}

    /**
     * Provides default values for the Input Options.
     *
     * @return array
     */
    public function default_input_options() {
        $defaults = array(
            'trader_api_wsdl'		=>	'<xml></xml>'
        );
        return $defaults;
    }

    /**
     * This function provides a simple description for the General Options page.
     *
     * It's called from the 'woocommerce-jt-trader_initialize_theme_options' function by being passed as a parameter
     * in the add_settings_section function.
     */
    public function api_options_callback() {
        $options = get_option('woocommerce_jt_trader_api_options');
        echo '<p>' . __( 'Customise the Trader API configuration options', 'woocommerce-jt-trader' ) . '</p>';
    } // end general_options_callback

    /**
     * Initializes the theme's input example by registering the Sections,
     * Fields, and Settings. This particular group of options is used to demonstration
     * validation and sanitization.
     *
     * This function is registered with the 'admin_init' hook.
     */
    public function initialize_api_options() {
        //delete_option('woocommerce_jt_trader_input_examples');
        if( false == get_option( 'woocommerce_jt_trader_api_options' ) ) {
            $default_array = $this->default_input_options();
            update_option( 'woocommerce_jt_trader_api_options', $default_array );
        } // end if
        add_settings_section(
            'api_options_section',
            __( 'API Configuration', 'woocommerce-jt-trader' ),
            array( $this, 'api_options_callback'),
            'woocommerce_jt_trader_api_options'
        );
        add_settings_field(
            'trader_api_wsdl_input',
            __( 'API WSDL Document', 'woocommerce-jt-trader' ),
            array( $this, 'trader_wsdl_input_callback'),
            'woocommerce_jt_trader_api_options',
            'api_options_section'
        );

        // Finally, we register the fields with WordPress
        register_setting(
            'woocommerce_jt_trader_api_options',
            'woocommerce_jt_trader_api_options'
        );
    }
    public function trader_wsdl_input_callback() {
        $options = get_option( 'woocommerce_jt_trader_api_options' );
        // Render the output
        echo '<textarea id="trader_api_wsdl_input" name="woocommerce_jt_trader_api_options[trader_api_wsdl]" rows="15" cols="150">' . $options['trader_api_wsdl'] . '</textarea>';
    } // end textarea_element_callback


}

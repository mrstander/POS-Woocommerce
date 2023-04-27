<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/includes
 */
class Woocommerce_Jt_Trader_Activator {

    /**
     * @param Woocommerce_Jt_Trader_Model $model
     * @return void
     */
	public static function activate($model) {
        $model->createTables();
    }

}

<?php
/**
 * Tell WordPress we are doing the CRON task.
 *
 * @var bool
 */
define( 'DOING_CRON', true );
// define( 'SHORTINIT', true );

    require_once("class-trader-import.php");
    require_once(ABSPATH . "/wp-content/plugins/woocommerce-jt-trader/includes/class-woocommerce-jt-trader-model.php");
    $model = Woocommerce_Jt_Trader_Model::instance();

    $import = new TraderImport($model, true);
    $import->run("products");
    echo "done";
    exit();

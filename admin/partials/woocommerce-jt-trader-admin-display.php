<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/admin/partials
 */
?>

<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
    <h1 class="wp-heading-inline"><a href="?page=wc_jt_trader">WooCommerce & Trader Integration</a></h1>
    <a href="?page=wc_jt_trader&tab=products&import=products" class="page-title-action">Import Products</a>
    <a href="?page=wc_jt_trader&tab=promotions&import=promotions" class="page-title-action">Import Promotions</a>
    <hr class="wp-header-end">
    <?php settings_errors(); ?>
    <?php
    if (isset($details) && isset($details->report) && !empty($details->report)) {
        include_once("import_details.php");
    } elseif (isset($order_details) && isset($order_details->report) && !empty($order_details->report)) {
        include_once("order_details.php");
    } else {
        include_once("tabs.php");
        $active_tab = (isset($active_tab) && !empty($active_tab)) ? $active_tab : "products";
        switch ($active_tab) {
            case "orders":
                include_once("order_list.php");
                break;
            case "settings":
                include_once("settings.php");
                break;
            default:
                include_once("import_list.php");
                break;
        }
    }
    ?>
</div><!-- /.wrap -->

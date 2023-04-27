<?php
/**
 * Admin View: Product import form
 *
 * @package WooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!-- Create a header in the default WordPress 'wrap' container -->

<?php include("$include_path/header.php"); ?>
    <div class="woocommerce-progress-form-wrapper">
        <form id="jt-trader-importer-promotion" class="wc-progress-form-content woocommerce-importer jt-trader-importer" enctype="multipart/form-data" method="post">
            <header>
                <h2><?php esc_html_e( 'Import promotions from Trader', 'woocommerce' ); ?> <a href="?page=wc_jt_trader&tab=promotions&import=promotions&import_dump_promotions=1" style="font-size: 14px;" target="_blank">[ View Raw Trader Data ]</a></h2>
            </header>
            <div class="jt-trader-importer-form-start">
                <div class="wc-actions">
                    <button type="submit" class="button button-primary button-next button-jt-trader-import" value="Import" name="save_step">Continue</button>
                    <?php wp_nonce_field( 'jt-trader-importer' ); ?>
                </div>
            </div>
            <div class="wc-progress-form-content woocommerce-importer jt-trader-importer-form-progress woocommerce-importer__importing hidden">
                <header>
                    <span class="spinner is-active"></span>
                    <h2>Importing</h2>
                    <p>Your promotions are now being imported, this may take a few minutes...</p>
                    <p><a href="?page=wc_jt_trader&tab=promotions&import=promotions" class="page-title-action">Cancel</a></p>
                </header>
                <section>
                    <progress class="woocommerce-importer-progress jt-trader-importer-progress" max="100" value="0"></progress>
                </section>
            </div>
            <div class="woocommerce-importer-done jt-trader-importer-form-done hidden">
                <header>
                    <span class="spinner is-active"></span>
                    <h2>Import Complete</h2>
                    <ul>
                        <li>Total Imported: <span class="jt-trader-importer-total">0</span></li>
                        <li>New Promotions: <span class="jt-trader-importer-created">0</span></li>
                        <li>Updated Promotions: <span class="jt-trader-importer-updated">0</span></li>
                        <li>Skipped Promotions: <span class="jt-trader-importer-skipped">0</span></li>
                        <li>Failed Promotions: <span class="jt-trader-importer-failed">0</span></li>
                    </ul>
                </header>
                <div class="wc-actions">
                    <button type="button" class="button button-primary button-next button-jt-trader-import-details">View Details</button>
                </div>
            </div>
        </form>
    </div>
<?php include("$include_path/footer.php"); ?>
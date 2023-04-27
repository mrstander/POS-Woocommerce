<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!defined('WC_ABSPATH')) {
    define( 'WC_ABSPATH', ABSPATH . 'wp-content/plugins/woocommerce/' );
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Product_Importer', false ) ) {
    include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'TraderWooCommerceBaseImporter', false ) ) {
    include_once 'TraderWooCommerceBaseImporter.php';
}

/**
 * TraderWooCommerceProductImporter Class.
 */
class TraderWooCommerceProductImporter extends TraderWooCommerceBaseImporter {

    /**
     * Initialize importer.
     *
     * @param array $data   Data to import
     * @param array  $params Arguments for the parser.
     * @param string  $type
     */
    public function __construct( $data, $params = array() ) {
        $this->params = array(
            'mapping' => array(
                "SKU" => "sku",
                "NAME" => "name",
                "PRICE" => "regular_price",
                "STATUS" => "stock_status",
                "CATEGORY" => "category_ids",
                "SOH" => "stock_quantity"
            ), // Column mapping. csv_heading => schema_heading.
            'parse'            => true, // Whether to sanitize and format data.
            'update_existing'  => true, // Whether to update existing items.
            'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
        );

        $this->unprocessed_data   = array_map('str_getcsv', $data);
        $this->import_type = 'product';

        $this->read_data();
    }

    /**
     * Read file.
     */
    protected function read_data() {
        if (!empty($this->unprocessed_data)) {
            $this->categorize_data();
        }
        parent::read_data();
    }

    protected function categorize_data() {
        $this->raw_data = array();
        foreach($this->unprocessed_data as $index => $row) {
            if (!$index) {
                $this->raw_data[] = array("SKU", "NAME", "PRICE", "STATUS", "CATEGORY", "SOH");
            } else {
                $categories = $this->categorize_row($row);
                $this->raw_data[] = array($row[0], ucwords(strtolower($row[1])), $row[2], $row[3], $categories, $row[7]);
            }
        }
    }

    protected function categorize_row($row) {
        $category = ucwords(strtolower($row[4]));
        $category .= (!empty($row[5])) ? " > " . ucwords(strtolower($row[5])) : "";
        return $category. ucwords(strtolower($row[6]));
    }
}
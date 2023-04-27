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
 * TraderWooCommercePromotionImporter Class.
 */
class TraderWooCommercePromotionImporter extends TraderWooCommerceBaseImporter {

    /**
     * Initialize importer.
     *
     * @param array $data   Data to import
     * @param array  $params Arguments for the parser.
     */
    public function __construct( $data, $params = array() ) {
        parent::__construct($data, $params, 'promotion');

        $this->params = array(
            'mapping' => array(
                "SKU"           => "sku",
                "STARTDATE"     => "date_on_sale_from",
                "ENDDATE"       => "date_on_sale_to",
                "PROMO_PRICE"   => "sale_price"
            ), // Column mapping. csv_heading => schema_heading.
            'parse'            => true, // Whether to sanitize and format data.
            'update_existing'  => true, // Whether to update existing items.
            'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
        );

        $this->read_data();
    }

    /**
     * Parse dates from a CSV.
     * Dates requires the format YYYY-MM-DD and time is optional.
     *
     * @param string $value Field value.
     *
     * @return string|null
     */
    public function parse_date_field( $value ) {
        if ( empty( $value ) ) {
            return null;
        }

        $value = strtotime($value);
        $value = date("Y-m-d", $value);

        if ( preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])([ 01-9:]*)$/', $value ) ) {
            // Don't include the time if the field had time in it.
            return current( explode( ' ', $value ) );
        }

        return null;
    }



    protected function build_report_item($item, $key) {

        $id         = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
        $sku        = isset( $item['sku'] ) ? $item['sku'] : '';
        return array(
            'id'  => $id,
            'sku' => esc_attr($sku),
            'promotion' => esc_attr($this->raw_data[$key][5]),
            'from' => $item['date_on_sale_from'],
            'to' => $item['date_on_sale_to'],
            'regular_price' => number_format($this->raw_data[$key][4], 2),
            'sale_price' => number_format($item['sale_price'], 2)
        );
    }
}
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
 * TraderWooCommerceProductImporter Class.
 */
class TraderWooCommerceBaseImporter extends WC_Product_CSV_Importer {

    /**
     * Tracks current row being parsed.
     *
     * @var integer
     */
    protected $parsing_raw_data_index = 0;

    protected $unprocessed_data = [];

    protected $import_type = "";

    /**
     * Initialize importer.
     *
     * @param array $data   Data to import
     * @param array  $params Arguments for the parser.
     * @param string  $type
     */
    public function __construct( $data, $params = array(), $type = "product" ) {
        $this->raw_data   = array_map('str_getcsv', $data);
        $this->import_type = $type;
    }

    /**
     * Read file.
     */
    protected function read_data() {

        $this->raw_keys = array_shift($this->raw_data);


        if ( ! empty( $this->params['mapping'] ) ) {
            $this->set_mapped_keys();
        }

        if ( $this->params['parse'] ) {
            $this->set_parsed_data();
        }
    }

    /**
     * Process importer.
     *
     * If product exists, update, otherwise insert new
     *
     * @return array
     * @throws Exception
     */
    public function import() {
        $this->start_time = time();
        $import_start = microtime(true);
        $index            = 0;
        $update_existing  = $this->params['update_existing'];
        $data             = array(
            'type'     => $this->import_type,
            'imported' => array(),
            'failed'   => array(),
            'updated'  => array(),
            'skipped'  => array(),
        );
        set_time_limit(0);


        foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {
            do_action( 'woocommerce_product_import_before_import', $parsed_data );

            $id         = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
            $sku        = isset( $parsed_data['sku'] ) ? $parsed_data['sku'] : '';
            $id_exists  = false;
            $sku_exists = false;

            if ( $id ) {
                $product   = wc_get_product( $id );
                $id_exists = $product && 'importing' !== $product->get_status();
            }

            if ( $sku ) {
                $id_from_sku = wc_get_product_id_by_sku( $sku );
                $product     = $id_from_sku ? wc_get_product( $id_from_sku ) : false;
                $sku_exists  = $product && 'importing' !== $product->get_status();
            }

            $parsed_row_id = $this->get_parsed_row_data( $parsed_data );
            $report_data = $this->build_report_item($parsed_data, $parsed_data_key);

            if ( ($id_exists || $sku_exists) && !$update_existing ) {
                $data['skipped'][] = $report_data;
                continue;
            }

            /** Skip adding new products if this is a promotion import */
            if (!$id_exists && !$sku_exists && $this->import_type === "promotion" ) {
                $data['skipped'][] = $report_data;
                continue;
            }

            /** Automatically set newly imported products to "draft" status */
            if (!$id_exists && !$sku_exists) {
                $parsed_data['status'] = "draft";
            }

            if ($this->import_type === "promotion" || $this->should_process($parsed_data_key)) {
                $result = $this->process_item( $parsed_data );

                if ( is_wp_error( $result ) ) {
                    $result->add_data( array( 'row' => $parsed_row_id ) );
                    $data['failed'][] = $result;
                } elseif ( $result['updated'] ) {
                    $report_data['id'] = $result['id'];
                    $data['updated'][] = $report_data;
                } else {
                    $report_data['id'] = $result['id'];
                    $data['imported'][] = $report_data;
                }
            } else {
                $report_data['id'] = $parsed_data_key;
                $data['skipped'][] = $report_data;
            }


            $index++;

            if ( $this->params['prevent_timeouts']) {
               set_time_limit(60);
            }
        }
        return $data;
    }

    /**
     * Get a string to identify the row from parsed data.
     *
     * @param array $parsed_data Parsed data.
     *
     * @return string
     */
    protected function get_parsed_row_data( $parsed_data ) {
        $row_data = array();

        foreach($parsed_data as $key => $val) {
            $val = is_array($val) ? implode(", ", $val) : $val;
            $val = is_string($val) ? esc_attr($val) : $val;
            $row_data[] = sprintf( '%s %s', strtoupper($key), $val );
        }

        return implode( ', ', $row_data );
    }

    protected function build_report_item($item, $key) {

        $id         = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
        $sku        = isset( $item['sku'] ) ? $item['sku'] : '';
        return array(
            'id'  => $id,
            'sku' => esc_attr($sku),
            'name' => $item['name'],
            'price' => number_format($item['regular_price'],2),
            'stock' => $item['stock_quantity']
        );
    }

    protected function should_process($key) {
        return true;
//        $row = $this->raw_data[$key];
//        $statuses = [2, 20];
//
//        // return (!in_array($row[3], $statuses) || (in_array($row[3], $statuses) && intval($row[8]) >= 5));
//        return (!in_array($row[3], $statuses));
    }

    public function parse_categories_field( $value ) {
        if ( empty( $value ) ) {
            return array();
        }

        $row_terms  = $this->explode_values( $value );
        $categories = array();

        foreach ( $row_terms as $row_term ) {
            $parent = null;
            $_terms = array_map( 'trim', explode( '>', $row_term ) );
            $total  = count( $_terms );

            foreach ( $_terms as $index => $_term ) {

                $term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );

                if ( is_wp_error( $term ) ) {
                    if ( $term->get_error_code() === 'term_exists' ) {
                        // When term exists, error data should contain existing term id.
                        $term_id = $term->get_error_data();
                    } else {
                        break; // We cannot continue on any other error.
                    }
                } else {
                    // New term.
                    $term_id = $term['term_id'];
                }

                // Only requires assign the last category.
                if ( ( 1 + $index ) === $total ) {
                    $categories[] = $term_id;
                } else {
                    // Store parent to be able to insert or query categories based in parent ID.
                    $parent = $term_id;
                }
            }
        }

        return $categories;
    }
}
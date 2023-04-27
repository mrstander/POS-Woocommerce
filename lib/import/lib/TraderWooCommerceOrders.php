<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WC_ABSPATH')) {
    define('WC_ABSPATH', ABSPATH . 'wp-content/plugins/woocommerce/');
}

/**
 * Include dependencies.
 */
if (!class_exists('WC_Product_Importer', false)) {
    include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
}

/**
 * TraderWooCommerceOrders Class.
 */
class TraderWooCommerceOrders {

    protected $order = null;
    public $request_object = array();

    /**
     * Initialize importer.
     *
     * @param array $order Order data to parse and send
     * @param array $customer Customer data to parse and send
     */
    public function __construct($order) {
        $this->order = $order;
        $this->build_request_object();
    }

    /**
     * Build our SOAP request object
     * @return array
     */
    protected function build_request_object() {
        $this->request_object = array(
            "AutoCreateCustomer" => false,
            "OrderNumber"        => "BABW-" . $this->order->get_order_number(),
            "CustomerRec"        => $this->parse_customer(),
            "Items"              => $this->parse_items()
        );
    }

    /**
     * Parse our WC order into a SOAP compliant array
     * @return array|null
     */
    protected function parse_items() {
        $result = array();

        foreach ($this->order->get_items() as $item) {
            $product  = $item->get_product();
            $result[] = array(
                "ItemCode"      => $product->get_sku(),
                "ColourCode"    => "1",
                "SizeCode"      => "1",
                "Qty"           => floatval($item->get_quantity()),
                "InclSellPrice" => number_format($item->get_total(), 2, ".", "")
            );
        }

        return $result;
    }

    /**
     * Parse our WC customer into a SOAP compliant object
     * @return array|null
     */
    protected function parse_customer() {
        return array(
            "Accountcode"             => $this->order->get_customer_id() . "-TEST",
            "Title"                   => "",
            "Initials"                => "",
            "Surname"                 => $this->order->get_billing_last_name() . " - TEST",
            "ContactName"             => $this->order->get_billing_first_name(),
            "IDNumber"                => "",
            "DateOfBirth"             => "",
            "ResidentialAddressLine1" => $this->order->get_billing_address_1(),
            "ResidentialAddressLine2" => $this->order->get_billing_address_2(),
            "ResidentialSuburb"       => "",
            "ResidentialTown"         => $this->order->get_billing_city(),
            "ResidentialPostalCode"   => $this->order->get_billing_postcode(),
            "PostalAddressLine1"      => "",
            "PostalAddressLine2"      => "",
            "PostalSuburb"            => "",
            "PostalTown"              => "",
            "PostalCode"              => "",
            "MobileNo"                => $this->order->get_billing_phone(),
            "EmailAddress"            => $this->order->get_billing_email(),
            "UserField1"              => "BABW-TEST",
        );
    }
}
<?php
require_once("load-wp.php");
require_once("lib/TraderSoapClient.php");
require_once("lib/TraderWooCommerceProductImporter.php");
require_once("lib/TraderWooCommercePromotionImporter.php");

class TraderImport {
    protected $soapClient;
    protected $importer;
    protected $model;
    private $isCli;

    /**
     * TraderImport constructor.
     * @param Woocommerce_Jt_Trader_Model $model
     * @param false $isCli
     */
    public function __construct($model, $isCli=false) {
        $this->soapClient = new TraderSoapClient();
        $this->model = $model;
        $this->isCli = $isCli;
    }

    public function getProducts() {
        return $this->soapClient->products();
    }

    public function getProductsArray($header=false) {
        $products = array_map('str_getcsv', $this->getProducts());
        if (!$header) {
            array_shift($products);
        }

        return $products;
    }

    public function dumpProducts() {
        try {
        return implode("\n", $this->soapClient->products());
        } catch  (SoapFault $exception) {
            echo $exception->getMessage();
            $this->soapClient->printErrors();
        }
    }

    public function dumpStores() {
        return implode("\n", $this->soapClient->stores());
    }

    public function dumpCustomers() {
        return implode("\n", $this->soapClient->customers());
    }

    public function dumpPromotions() {
        return implode("\n", $this->soapClient->promotions());
    }

    public function getCategories() {
        return $this->soapClient->categories();
    }

    public function getPromotions() {
        return $this->soapClient->promotions();
    }

    public function run($task) {
        switch($task) {
            case "products":
                return $this->runProductImport();
                break;

            case "categories":
                return $this->getCategories();
                break;

            case "promotions":
                return $this->runPromotionImport();
                break;
        }
    }

    public function runProductImport() {
        $process_id = uniqid();
        $type = "product";
        try {
            $this->importer = new TraderWooCommerceProductImporter($this->processQueue($type, $process_id));
            $result = $this->importer->import();
        } catch (\Exception | SoapFault $exception) {
            $result = array("type" => $type, "imported" => 0, "updated" => 0, "skipped" => 0, "failed" => 0, "report" => json_encode($exception));
        }
        $updated = $this->model->updateImport($result);
        $this->model->clearQueuedIds($process_id);

        return ($this->isCli) ? $updated : array("results" => $updated, "remaining" => $this->model->fetchQueueCount("product"));
    }

    public function runPromotionImport() {
        $process_id = uniqid();
        $this->importer = new TraderWooCommercePromotionImporter($this->processQueue("promotion", $process_id));
        try {
            $result = $this->importer->import();
        } catch (\Exception | SoapFault $exception) {
            $result = array("type" => "promotion", "imported" => 0, "updated" => 0, "skipped" => 0, "failed" => 0, "report" => json_encode($exception));
        }
        $updated = $this->model->updateImport($result);
        if ($updated) {
            $this->model->clearQueuedIds($process_id);
        }

        return ($this->isCli) ? $updated : array("results" => $updated, "remaining" => $this->model->fetchQueueCount("promotion"));
    }

    public function populateQueue($type) {
        if ($this->model->isQueueEmpty($type) && !$this->isCurrentlyImporting($type)) {
            $this->setCurrentlyImporting($type);
            $items = ($type === "promotion") ? $this->getPromotions() : $this->getProducts();
            if (!empty($items)) {
                $this->model->setQueueItems($type, $items);
                if ($type === "promotion") {
                    $this->clearPromotions(); // We only get active promotions from Trader. We need to clear everything out first to make sure we synch correctly.
                }
            }
            $this->unsetCurrentlyImporting($type);
        }
    }

    public function processQueue($type, $process_id) {
        $limit = $this->isCli ? null : 150;
        $this->populateQueue($type); // will only add more items to the queue if it's already empty
                $queued_raw = $this->model->fetchQueueItems($type, $process_id, $limit);

        if(empty($queued_raw)) {
            throw new Exception("TraderImport::processQueue - Could not find any products to import");
        }

        return $this->model->normalizeQueueItems($queued_raw);
    }

    public function clearPromotions() {
        $ids = wc_get_product_ids_on_sale();

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $product = wc_get_product( $id );
                $product->set_date_on_sale_to( '' );
                $product->set_date_on_sale_from( '' );
                $product->set_sale_price( '' );
                $product->save();
            }
        }
    }

    public function isCurrentlyImporting($type) {
        return get_transient("wc_jt_trader_importing_$type");
    }

    public function setCurrentlyImporting($type) {
        set_transient("wc_jt_trader_importing_$type", time(), 3600);
    }

    public function unsetCurrentlyImporting($type) {
        delete_transient("wc_jt_trader_importing_$type");
    }
}

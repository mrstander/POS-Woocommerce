<?php

class TraderSoapClient {
    private $client;
    public function __construct($wsdlFile = 'trader') {
        ini_set('default_socket_timeout', 600);
        $wsdl = TRADERABSPATH . "config/$wsdlFile.wsdl";
        if (file_exists($wsdl)) {
            $this->client = new SoapClient($wsdl, array(
                'trace'              => true,
                'keep_alive'         => true,
                'connection_timeout' => 5000,
                'cache_wsdl'         => WSDL_CACHE_NONE,
                'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ));
        } else {
            trigger_error("Unknown wsdl file: $wsdlFile", E_USER_ERROR);
        }
    }

    public function printErrors($error=null) {
        echo "<h3>SOAP Error</h3>";
        echo "<h3>ERROR</h3>";
        echo "<pre>" . print_r($error, true) . "</pre>";
        echo "<h3>REQUEST</h3>";
        echo "<pre>" . print_r($this->client->__getLastRequest(), true) . "</pre>";
        echo "<h3>RESPONSE</h3>";
        echo "<pre>" . print_r($this->client->__getLastResponse(), true) . "</pre>";
        echo "<h3>RESPONSE HEADERS</h3>";
        echo "<pre>" . print_r($this->client->__getLastResponseHeaders(), true) . "</pre>";
    }

    protected function loadSQL($file) {
        return file_get_contents(TRADERABSPATH . "queries/$file-sql.php");
    }

    public function products() {
        return $this->client->CustomQuery($this->loadSQL("products"));
    }

    public function categories() {
        return $this->client->CustomQuery($this->loadSQL("categories"));
    }

    public function promotions() {
        return $this->client->CustomQuery($this->loadSQL("promotions"));
    }

    public function query($sql) {
        try {
            return $this->client->CustomQuery($sql);
        }  catch (\Exception | SoapFault $exception) {
            $this->printErrors($exception);
            exit;
        }
    }

    public function results_to_array($results, $header = false) {
        $rows = array_map('str_getcsv', $results);
        if (!$header) {
            array_shift($rows);
        }

        return $rows;
    }

    public function results_to_single($results) {
        $rows = $this->results_to_array($results);
        return $rows[0];
    }

    public function sendOrder($order){
        /*echo "<pre>";
        print_r($this->client->__getTypes());
        print_r($this->client->__getFunctions());
        print_r($order);
        echo "<pre>";
        die();*/

        return $this->client->AddOrder($order["AutoCreateCustomer"], $order["OrderNumber"], $order["CustomerRec"], $order["Items"]);
    }
}
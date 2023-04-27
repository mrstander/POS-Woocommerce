<?php

require_once("load-wp.php");
require_once("lib/TraderSoapClient.php");
require_once("lib/TraderWooCommerceImporter.php");

$soapClient = new TraderSoapClient();
$products = $soapClient->products();
echo "<pre>";
print_r($products);
echo "</pre>";

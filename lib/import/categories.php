<?php

require_once("load-wp.php");
require_once("lib/TraderSoapClient.php");
require_once("lib/TraderWooCommerceImporter.php");

$soapClient = new TraderSoapClient();
$categories = $soapClient->categories();
echo "<pre>";
print_r($categories);
echo "</pre>";
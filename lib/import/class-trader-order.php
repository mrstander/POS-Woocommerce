<?php
require_once("lib/TraderSoapClient.php");
require_once("lib/TraderWooCommerceOrders.php");

class TraderOrder {
    protected $soapClient;
    protected $order;
    protected $model;

    public function __construct($model) {
        $this->soapClient = new TraderSoapClient('orders');
        $this->model      = $model;
    }

    public function run($order) {
        $this->order = new TraderWooCommerceOrders($order);
        $report = array(
            "order"           => $order->get_data(),
            "request_object"  => $this->order->request_object,
            "trader_response" => ""
        );
        $status = "success";
        try {
            $report["trader_response"] = $this->soapClient->sendOrder($this->order->request_object);
        } catch (\Exception | SoapFault $exception) {
            $report["trader_response"] = $exception;
            $status = "fail";
        }


        return $this->model->saveOrder(array("order_number" => $order->get_order_number(), "status" => $status, "report" => $report));
    }
}

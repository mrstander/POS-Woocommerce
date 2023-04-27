<h2>Order Details for #<?php echo $order_details->order_number; ?></h2>
<h4>Status: <?php echo $order_details->status; ?></h4>
<?php
foreach ($order_details->report as $key => $report) {
        echo "<h4>" . ucwords($key) . "</h4>"; ?>
           <pre><?php echo trim(json_encode($report, JSON_PRETTY_PRINT)); ?></pre>
        <?php
}
<?php $orders = (!empty($orders)) ? $orders : []; ?>
<h3>Processed Orders</h3>
<table class="wp-list-table widefat fixed striped">
    <thead>
    <tr>
        <th>Date</th>
        <th>WC Order Number</th>
        <th>Trader Order Number</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="the-list">
    <?php if (empty($orders)) { ?>
        <tr>
            <td colspan="4">No Orders currently processed</td>
        </tr>
        <?php
    } else {
        foreach ($orders as $order) { ?>
            <?php $report = json_decode($order->report); ?>

            <tr>
                <td><?php echo $this->convertToTimezone($order->created_at); ?></td>
                <td><a href="post.php?post=<?php echo $order->order_number; ?>&action=edit"><?php echo $order->order_number; ?></a></td>
                <td><?php echo ($report && $report->trader_response && $report->trader_response->OrderNo) ? $report->trader_response->OrderNo : ""; ?></td>
                <td><?php echo $order->status; ?></td>
                <td>
                    <a href="?page=wc_jt_trader&order=<?php echo $order->id; ?>"
                       class="page-title-action">Details</a>
                    <?php if ($order->status === "fail"){ ?>
                    <a href="?page=wc_jt_trader&retry_order=<?php echo $order->order_number; ?>"
                       class="page-title-action">Retry</a>
                    <?php } ?>
                </td>
            </tr>
        <?php }
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th>Date</th>
        <th>WC Order Number</th>
        <th>Trader Order Number</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </tfoot>
</table>
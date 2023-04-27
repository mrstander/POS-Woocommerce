<?php
foreach ($details->report as $key => $report) {
    if (is_array($report) && count($report)) {
        echo "<h2>" . ucwords($details->import_type) . " Imports : " . ucwords($key) . "</h2>";

        if ($key !== "failed") { ?>
            <?php $headers = array_keys(get_object_vars($report[0])); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <?php foreach ($headers as $header) { ?>
                        <th><?php echo strtoupper(str_replace("_", " ", $header)); ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <?php foreach ($report as $row) { ?>
                    <tr>
                        <?php foreach ($headers as $row_key) { ?>
                            <td><?php echo $row->{$row_key}; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                <tfoot>
                <tr>
                    <?php foreach ($headers as $header) { ?>
                        <th><?php echo strtoupper(str_replace("_", " ", $header)); ?></th>
                    <?php } ?>
                </tr>
                </tfoot>
            </table>
        <?php }
    }
}
<?php $imports = (!empty($imports)) ? $imports : []; ?>
<h3>Import Results</h3>
<table class="wp-list-table widefat fixed striped">
    <thead>
    <tr>
        <th>Date</th>
        <th>Products Added</th>
        <th>Products Updated</th>
        <th>Products Skipped</th>
        <th>Errors</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="the-list">
    <?php if (empty($imports)) { ?>
        <tr>
            <td colspan="5">No Imports currently completed</td>
        </tr>
        <?php
    } else {
        foreach ($imports as $import) { ?>
            <tr>
                <td><?php echo $this->convertToTimezone($import->created_at); ?></td>
                <td><?php echo $import->num_created; ?></td>
                <td><?php echo $import->num_updated; ?></td>
                <td><?php echo $import->num_skipped; ?></td>
                <td><?php echo $import->num_errors; ?></td>
                <td><a href="?page=wc_jt_trader&details=<?php echo $import->id; ?>"
                       class="page-title-action">Details</a></td>
            </tr>
        <?php }
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th>Date</th>
        <th>Products Added</th>
        <th>Products Updated</th>
        <th>Products Skipped</th>
        <th>Errors</th>
        <th>Actions</th>
    </tr>
    </tfoot>
</table>
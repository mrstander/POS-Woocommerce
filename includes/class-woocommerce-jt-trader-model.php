<?php
/**
 * The Model for our DB interactions
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Jt_Trader
 * @subpackage Woocommerce_Jt_Trader/admin
 * @author     Your Name <email@example.com>
 */
class Woocommerce_Jt_Trader_Model {
    /**
     * The single instance of the class.
     *
     * @var object
     */
    protected static $instance = null;

    protected $db;
    protected $tables;
    protected $queued_ids;
    protected $queue_import_id;

    /**
     * Woocommerce_Jt_Trader_Model constructor.
     */
    protected function __construct(){
        global $wpdb;
        $this->queued_ids = [];
        $this->queue_import_id = null;
        $this->db = $wpdb;
        $this->tables = array(
            "imports" => $this->db->prefix . "wc_jt_trader_imports",
            "orders" => $this->db->prefix . "wc_jt_trader_orders",
            "queue" => $this->db->prefix . "wc_jt_trader_import_queue"
        );
    }

    /**
     * Get class instance.
     *
     * @return Woocommerce_Jt_Trader_Model Instance.
     */
    public static function instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create the DB Schema for our plugin. Called on plugin activation
     */
    public function createTables() {
        $charset_collate = $this->db->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->tables["imports"] . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  created_at varchar(255),
                  import_type varchar(15),
                  num_created smallint(4),
                  num_updated smallint(4),
                  num_skipped smallint(4),
                  num_errors smallint(4),
                  total smallint(4),
                  report LONGTEXT,
                  import_id varchar(30),
                  UNIQUE KEY id (id)
                 ) $charset_collate;";

        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->tables["orders"] . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  order_number mediumint(9) NOT NULL,
                  created_at varchar(255),
                  status varchar(25),
                  report LONGTEXT,
                  UNIQUE KEY id (id),
                  UNIQUE KEY order_number (order_number)
                 ) $charset_collate;";

        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . $this->tables["queue"] . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  import_type varchar(12) NOT NULL,
                  import_id varchar(20) NOT NULL,
                  created_at varchar(255),
                  import_row LONGTEXT,
                  header_row varchar(255),
                  locked varchar(255) DEFAULT NULL,
                  PRIMARY KEY id (id),
                  KEY import_id (import_id)
                  KEY locked (locked)
                 ) $charset_collate;";

        $this->db->query($sql);
    }

    /**
     * Simple SELECT for our product imports
     * @return array|null query result set
     */
    public function fetchImports($type='product') {
        $query = $this->db->prepare("SELECT id, created_at, num_created, num_updated, num_skipped, num_errors FROM " . $this->tables["imports"] . " WHERE import_type = %s ORDER BY created_at DESC LIMIT 100", [$type]);
        return $this->db->get_results($query);
    }

    /**
     * Simple SELECT for a single product import
     * @param integer $id
     * @return array|null query result set
     */
    public function fetchImport($id) {
        $query = $this->db->prepare("SELECT id, created_at, import_type, num_created, num_updated, num_skipped, num_errors, report FROM " . $this->tables["imports"] . " WHERE id=%d", [$id]);
        return $this->db->get_row($query);
    }

    /**
     * Simple SELECT for a single product import
     * @return object|null query result set
     */
    public function fetchQueuedImport() {
        $query = $this->db->prepare("SELECT id, created_at, import_type, num_created, num_updated, num_skipped, num_errors, report, total FROM " . $this->tables["imports"] . " WHERE import_id=%s", [$this->queue_import_id]);
        return $this->db->get_row($query);
    }

    /**
     * Simple SELECT for our processed orders
     * @return array|null query result set
     */
    public function fetchOrders() {
        $query = "SELECT id, created_at, order_number, status, report FROM " . $this->tables["orders"] . " ORDER BY created_at DESC LIMIT 100";
        return $this->db->get_results($query);
    }



    /**
     * Simple SELECT for a single product import
     * @param integer $id
     * @return array|null query result set
     */
    public function fetchOrder($id) {
        $query = $this->db->prepare("SELECT id, created_at, order_number, status, report FROM " . $this->tables["orders"] . " WHERE id=%d", [$id]);
        return $this->db->get_row($query);
    }

    /**
     * Save results of product imports
     * @param array $importData
     * @return bool|int
     */
    public function saveImport($importData) {

        $totals = [
            "imported" => (is_array($importData["imported"])) ? intval(count($importData["imported"])) : 0,
            "updated" => (is_array($importData["updated"])) ? intval(count($importData["updated"])) : 0,
            "skipped" => (is_array($importData["skipped"])) ? intval(count($importData["skipped"])) : 0,
            "failed" => (is_array($importData["failed"])) ? intval(count($importData["failed"])) : 0,
        ];

        $sql = "INSERT INTO " . $this->tables["imports"] . " SET 
        created_at= '" . time() . "', 
        import_type = '" . $importData["type"] . "',
        num_created = " . $totals["imported"].", 
        num_updated=" . $totals["updated"].", 
        num_skipped=" . $totals["skipped"].", 
        num_errors=" . $totals["failed"].", 
        total=". intval($this->fetchQueueCount($importData["type"])) . ",
        report='" . esc_sql(json_encode($importData)) . "'";

        if ($this->queue_import_id) {
            $sql .= ", import_id='" . $this->queue_import_id . "'";
        }

        return $this->db->query($sql);
    }

    private function mergeImportReports($new, $original) {
        if ($new && $original) {
            $new = json_decode(json_encode($new));
            $original = json_decode(json_encode($original));
            $original->updated  = (is_array($original->updated)) ? array_merge($original->updated, $new->updated) : $new->updated;
            $original->imported = (is_array($original->imported)) ? array_merge($original->imported, $new->imported) : $new->imported;
            $original->skipped  = (is_array($original->skipped)) ? array_merge($original->skipped, $new->skipped) : $new->skipped;
            $original->failed   = (is_array($original->failed)) ? array_merge($original->failed, $new->failed) : $new->failed;
        } elseif ($new) {
            $original = json_decode(json_encode($new));
        }
        return $original;
    }

    /**
     * Save results of product imports
     * @param array $importData
     * @return object|bool
     */
    public function updateImport($importData) {
        $row = ($this->queue_import_id) ? $this->fetchQueuedImport() : null;

        if (!$row || !$row->id) {
            $saved = $this->saveImport($importData);
        } else {

            $report = $this->mergeImportReports($importData, json_decode($row->report));
            $totals = [
                "created" => intval(count($report->imported)),
                "updated" => intval(count($report->updated)),
                "skipped" => intval(count($report->skipped)),
                "errors"  => intval(count($report->failed)),
                "total"   => $row->total,
                "report"  => $report
            ];
            $sql    = "UPDATE " . $this->tables["imports"] . " SET 
            num_created = '" . $totals["created"] . "',
            num_updated = '" . $totals["updated"] . "',
            num_skipped = '" . $totals["skipped"] . "',
            num_errors = '" . $totals["errors"] . "',
            report = '" . esc_sql(json_encode($totals["report"])) . "'
            WHERE id=" . $row->id;
            $saved  = $this->db->query($sql);
        }

        return ($saved) ? $this->fetchQueuedImport() : false;
    }

    public function saveOrder($orderData) {
        $sql = "REPLACE INTO " . $this->tables["orders"] . " SET 
        created_at= '" . time() . "', 
        order_number= '". $orderData["order_number"] . "', 
        status= '". $orderData["status"] . "', 
        report='" . esc_sql(json_encode($orderData["report"])) . "'";

        return $this->db->query($sql);

    }

    public function fetchQueueItems($type, $process_id, $limit=100) {
        $this->lockQueueItems($type, $process_id, $limit);
        $sql = $this->db->prepare("SELECT id, import_row, header_row, import_id from " . $this->tables["queue"] . " WHERE locked=%s LIMIT %d", [$process_id, $limit]);
        return $this->db->get_results($sql);
    }

    public function lockQueueItems($type, $process_id, $limit) {
        $sql = $this->db->prepare("UPDATE " . $this->tables["queue"] . " SET locked=%s WHERE import_type=%s AND locked IS NULL LIMIT %d", [$process_id, $type, $limit]);
        $this->db->query($sql);
    }

    public function isQueueEmpty($type) {
        $total = $this->fetchQueueCount($type);
        return (intval($total) <= 0); // return false if we have items in the queue
    }

    public function normalizeQueueItems($data) {
        $result = array();
        if (is_array($data) && !empty($data)) {
            $this->queue_import_id = null;
            foreach($data as $index => $item) {
                if (!$index && $item->header_row) {
                    $result[] = $item->header_row;
                    if ($item->import_id) {
                        $this->queue_import_id = $item->import_id;
                    }
                }
                $result[] = $item->import_row;
            }
        }

        return $result;
    }

    public function setQueueItems($type, $items) {
        if (empty($items) || empty($type)) {
            return false;
        }

        $header = array_shift($items);
        $this->queue_import_id = uniqid();
        $values = array();
        $created_at = time();

        $query = "INSERT INTO " . $this->tables["queue"] . " (import_id, import_type, header_row, import_row, created_at) VALUES ";
        if ($header) {
            foreach($items as $item) {
                $values[] = $this->db->prepare( "(%s,%s,%s,%s,%s)", $this->queue_import_id, $type, $header, $item, $created_at );
            }
            $query .= implode( ",\n", $values );
            $this->db->query($query);
            return true;
        }
        return false;
    }

    public function clearQueuedIds($process_id) {
        if (!empty($process_id)) {
            $sql = $this->db->prepare("DELETE FROM " . $this->tables["queue"] . " WHERE locked=%s", [$process_id]);
            return $this->db->query($sql);
        }
        return false;
    }

    public function fetchQueueCount($type) {
        $query = $this->db->prepare("SELECT COUNT(id) AS total FROM " . $this->tables["queue"] . " WHERE import_type = %s AND locked IS NULL", [$type]);
        $row = $this->db->get_row($query);
        return ($row && isset($row->total)) ? $row->total : null;
    }

    public function purgeImports() {
        $query = $this->db->prepare("DELETE FROM " . $this->tables["imports"] . " WHERE created_at < %s", [strtotime("-30 days")]);
        return $this->db->query($query);
    }
}
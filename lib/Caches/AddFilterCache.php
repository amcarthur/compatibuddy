<?php

namespace Compatibuddy\Caches;

require_once('CacheInterface.php');

use Compatibuddy\Database;


class AddFilterCache implements CacheInterface {

    private $table;
    private $map;
    private $modified;

    public function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . Database::ADD_FILTER_CACHE_TABLE;
        $this->modified = false;
    }

    public function get($key = null) {
        if ($key !== null) {
            return isset($this->map[$key]) ? $this->map[$key] : null;
        }

        return $this->map;
    }

    public function set($key, $value) {
        $this->map[$key] = $value;
        $this->modified = true;
    }

    public function fetch() {
        global $wpdb;

        $this->map = [];
        $results = $wpdb->get_results("SELECT * FROM $this->table", ARRAY_A);
        foreach ($results as $result) {
            $moduleId = $result['module_id'];
            $moduleVersion = $result['module_version'];
            $lastUpdated = $result['last_updated'];
            $data = json_decode($result['data'], true);

            $this->map[$moduleId] = $data;
        }

        $this->modified = false;
    }

    public function commit() {
        global $wpdb;

        if ($this->modified) {
            $values = [];
            $lastUpdated = date("Y-m-d H:i:s");

            foreach($this->map as $moduleId => $module) {
                $values[] = $wpdb->prepare("(%s,%s,%s,%s)",
                    $moduleId, $module['module']['metadata']['Version'],
                    $lastUpdated, json_encode($module['calls']));
            }

            $query = "
INSERT INTO $this->table (module_id, module_version, last_updated, data)
VALUES ";
            $query .= implode(",\n", $values);
            $query .= "
ON DUPLICATE KEY UPDATE
    module_version = VALUES(module_version),
    last_updated = VALUES(last_updated),
    data = VALUES(data)
";
            $wpdb->query($query);

            $this->modified = false;
        }
    }

    public function clear($key = null) {

        $this->map = [];
        $this->modified = false;
    }
}
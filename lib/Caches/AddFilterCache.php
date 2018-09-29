<?php

namespace Compatibuddy\Caches;

require_once('CacheInterface.php');

use Compatibuddy\Database;
use DateTime;
use DateTimeZone;


class AddFilterCache implements CacheInterface {

    protected $table;
    protected $map;

    public function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . Database::ADD_FILTER_CACHE_TABLE;
    }

    public function get($key = null) {
        if ($key !== null) {
            return isset($this->map[$key]) ? $this->map[$key] : null;
        }

        return $this->map;
    }

    public function set($key, $value) {
        $this->map[$key] = $value;
        $this->map[$key]['modified'] = true;
    }

    public function fetch() {
        global $wpdb;

        $this->map = [];
        $results = $wpdb->get_results("SELECT * FROM $this->table", ARRAY_A);
        foreach ($results as $result) {
            $moduleId = $result['module_id'];
            $lastUpdated = $result['last_updated'];
            $data = json_decode($result['data'], true);

            $timezone = get_option('timezone_string');
            if ($timezone) {
                $date = new DateTime($lastUpdated, new DateTimeZone($timezone));
            } else {
                $date = new DateTime($lastUpdated);
            }

            $this->map[$moduleId] = $data;
            $this->map[$moduleId]['lastUpdated'] = $date->format('M jS Y g:i A');
            $this->map[$moduleId]['modified'] = false;
        }
    }

    public function commit() {
        global $wpdb;

        $values = [];
        $lastUpdated = new DateTime('now', new DateTimeZone('UTC'));

        foreach($this->map as $moduleId => $module) {
            if (!isset($module['modified'])) {
                $module['modified'] = true;
            }

            if ($module['modified']) {
                $values[] = $wpdb->prepare('(%s,%s,%s,%s)',
                    $moduleId, $module['module']['metadata']['Version'],
                    $lastUpdated->format('Y-m-d H:i:s'), json_encode($module));
            }
        }

        if (empty($values)) {
            return;
        }

        $query = "
INSERT INTO $this->table (`module_id`, `module_version`, `last_updated`, `data`)
VALUES ";
        $query .= implode(",\n", $values);
        $query .= "
ON DUPLICATE KEY UPDATE
`module_version` = VALUES(`module_version`),
`last_updated` = VALUES(`last_updated`),
`data` = VALUES(`data`)
";
        $wpdb->query($query);
    }

    public function clear($keys = null) {
        global $wpdb;

        $query = "DELETE FROM $this->table";

        if ($keys !== null) {
            if (empty($keys)) {
                return;
            }

            $values = [];
            foreach ($keys as $key) {
                $values[] = $wpdb->prepare('%s', $key);
                unset($this->map[$key]);
            }

            $query .= " WHERE `module_id` IN (" . implode(',', $values) . ")";
        } else {
            $this->map = [];
        }

        $wpdb->query($query);
    }
}
<?php

namespace Compatibuddy;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Database {
    const MODULE_CACHE_TABLE = 'compatibuddy_module_cache';

    /**
     * Disables the constructor.
     */
    private function __construct() {}

    public static function ensureSchema() {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        $createModuleCacheTableSql = sprintf("CREATE TABLE %s (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  module_id varchar(255) NOT NULL,
  module_type varchar(255) NOT NULL,
  module_version varchar(55) NOT NULL,
  last_updated datetime NOT NULL,
  data longtext NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY mkey (module_id)
) %s;", $wpdb->prefix . self::MODULE_CACHE_TABLE, $charsetCollate);

        dbDelta($createModuleCacheTableSql);
    }

    public static function dropSchema() {
        global $wpdb;

        $dropModuleCacheTableSql =
            sprintf("DROP TABLE IF EXISTS %s", $wpdb->prefix . self::MODULE_CACHE_TABLE);

        $wpdb->query($dropModuleCacheTableSql);
    }
}
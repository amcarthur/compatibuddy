<?php

namespace Compatibuddy;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Database {
    const ADD_FILTER_CACHE_TABLE = 'compatibuddy_add_filter_cache';

    /**
     * Disables the constructor.
     */
    private function __construct() {}

    public static function ensureSchema() {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        $createAddFilterCacheTableSql = sprintf("CREATE TABLE %s (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  module_id varchar(255) NOT NULL,
  module_type varchar(255) NOT NULL,
  module_version varchar(55) NOT NULL,
  last_updated datetime NOT NULL,
  data longtext NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY mkey (module_id)
) %s;", $wpdb->prefix . self::ADD_FILTER_CACHE_TABLE, $charsetCollate);

        dbDelta($createAddFilterCacheTableSql);
    }

    public static function dropSchema() {
        global $wpdb;

        $dropAddFilterCacheTableSql =
            sprintf("DROP TABLE IF EXISTS %s", $wpdb->prefix . self::ADD_FILTER_CACHE_TABLE);

        $wpdb->query($dropAddFilterCacheTableSql);
    }
}
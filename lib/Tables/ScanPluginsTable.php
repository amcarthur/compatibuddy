<?php

namespace Compatibuddy\Tables;

use Compatibuddy\Analyzers\HigherPriorityAddFilterAnalyzer;
use Compatibuddy\Routes;
use Compatibuddy\Scanners\AddFilterScanner;
use Compatibuddy\Utilities;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ScanPluginsTable extends \WP_List_Table {
    protected $router;

    function __construct($router) {
        $this->router = $router;

        parent::__construct([
            'singular'=> 'compatibuddy_scan_plugin',
            'plural' => 'compatibuddy_scan_plugins',
            'ajax'   => false
        ]);
    }

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            $this->search_box(__('Search', 'compatibuddy'), 'scan_plugins_search');
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            //echo"Hi, I'm after the table";
        }
    }

    function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'name'=> __('Name', 'compatibuddy'),
            'version'=> __('Version', 'compatibuddy'),
            'author'=> __('Author', 'compatibuddy'),
            'path'=> __('Path', 'compatibuddy'),
            'last_scanned' => __('Last Scanned', 'compatibuddy'),
            'status'=> __('Status', 'compatibuddy')
        ];
    }

    public function get_sortable_columns() {
        return [
            'name'=> ['name', true],
            'version'=> ['version', true],
            'author'=> ['author', true],
            'path'=> ['path', true],
            'last_scanned'=> ['last_scanned', true],
            'status'=> ['status', true]
        ];
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $plugins = Utilities::getPlugins();
        $this->handle_bulk_scan($plugins);

        $addFilterScanner = new AddFilterScanner();
        $addFilterScanResults = $addFilterScanner->scan($plugins, true);

        $searchKey = isset($_REQUEST['s']) ? strtolower(wp_unslash(trim($_REQUEST['s']))) : '';

        $requestUriArgs = [];

        if (isset($_REQUEST['s'])) {
            $requestUriArgs['s'] = $searchKey;
        }

        if (isset($_REQUEST['order'])) {
            $requestUriArgs['order'] = $_REQUEST['order'];
        }

        if (isset($_REQUEST['orderby'])) {
            $requestUriArgs['orderby'] = $_REQUEST['orderby'];
        }

        if (!empty($requestUriArgs)) {
            $_SERVER['REQUEST_URI'] = add_query_arg($requestUriArgs, $_SERVER['REQUEST_URI']);
        }

        $formattedItems = [];
        foreach ($plugins as $basename => $plugin) {
            $inSearch = false;
            if ($searchKey) {
                if (stripos(strtolower($basename), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($plugin['metadata']['Name']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($plugin['metadata']['Author']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($plugin['metadata']['Version']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($plugin['metadata']['Version']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($plugin['metadata']['Author']), $searchKey) !== false) {
                    $inSearch = true;
                }
            }

            $formattedItems[$basename] = [
                'plugin' => $plugin,
                'status' => 2
            ];

            foreach ($addFilterScanResults as $moduleId => $scanResult) {
                if ($searchKey && stripos(strtolower($scanResult['lastUpdated']), $searchKey) !== false) {
                    $inSearch = true;
                }

                if ($searchKey && !$inSearch) {
                    continue;
                }

                if ($basename === $moduleId) {
                    $formattedItems[$basename]['scanResult'] = $scanResult;
                    if ($formattedItems[$basename]['scanResult']['moduleVersion'] !==
                        $plugin['metadata']['Version']) {
                        $formattedItems[$basename]['status'] = 1;
                    } else {
                        $formattedItems[$basename]['status'] = 0;
                    }
                    break;
                }
            }
        }

        // TODO: Optimize sorting to be done by cache

        if (isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
            usort($formattedItems, function ($a, $b) {
                $orderBy = $_REQUEST['orderby'];
                $order = $_REQUEST['order'];
                switch ($orderBy) {
                    case 'name':
                        if ($order === 'asc') {
                            return strcasecmp($a['plugin']['metadata']['Name'], $b['plugin']['metadata']['Name']);
                        }

                        return strcasecmp($b['plugin']['metadata']['Name'], $a['plugin']['metadata']['Name']);
                    case 'version':
                        if ($order === 'asc') {
                            return strcasecmp($a['plugin']['metadata']['Version'], $b['plugin']['metadata']['Version']);
                        }

                        return strcasecmp($b['plugin']['metadata']['Version'], $a['plugin']['metadata']['Version']);
                    case 'author':
                        if ($order === 'asc') {
                            return strcasecmp($a['plugin']['metadata']['Author'], $b['plugin']['metadata']['Author']);
                        }

                        return strcasecmp($b['plugin']['metadata']['Author'], $a['plugin']['metadata']['Author']);
                    case 'path':
                        if ($order === 'asc') {
                            return strcasecmp($a['plugin']['id'], $b['plugin']['id']);
                        }

                        return strcasecmp($b['plugin']['id'], $a['plugin']['id']);
                    case 'status':
                        if ($a['status'] === $b['status']) {
                            return 0;
                        }

                        if ($order === 'asc') {
                            return $a['status'] > $b['status'] ? -1 : 1;
                        }

                        return $a['status'] < $b['status'] ? -1 : 1;

                    case 'last_scanned':
                      
                        $t1 = strtotime($a['scanResult']['lastUpdated']);
                        $t2 = strtotime($b['scanResult']['lastUpdated']);

                        if ($order === 'asc') {
                            return $t1 - $t2;
                        }

                        return $t2 - $t1;
                    default:
                        return 0;
                }
            });
        }
        $this->items = $formattedItems;
    }

    function handle_bulk_scan($plugins) {
        if (!isset($_REQUEST['_wpnonce']) || (!isset($_REQUEST['action']) && !isset($_REQUEST['action2']))) {
            return false;
        }

        $nonce = wp_unslash($_REQUEST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'bulk-compatibuddy_scan_plugins')) {
            return false;
        }

        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : $_REQUEST['action2'];

        $addFilterScanner = new AddFilterScanner();

        if ($action === 'bulk-scan-selected') {
            if (!isset($_REQUEST['plugin_ids']) || empty($_REQUEST['plugin_ids'])) {
                return false;
            }

            $pluginIds = $_REQUEST['plugin_ids'];
            $pluginsToScan = [];
            foreach ($pluginIds as $pluginId) {
                $pluginsToScan[$pluginId] = $plugins[$pluginId];
            }

            return $addFilterScanner->scan($pluginsToScan);
        }

        if ($action === 'bulk-rescan-selected-out-of-date') {
            if (!isset($_REQUEST['plugin_ids']) || empty($_REQUEST['plugin_ids'])) {
                return false;
            }

            $pluginIds = $_REQUEST['plugin_ids'];
            $pluginIdsToClear = [];
            $pluginsToScan = [];
            foreach ($pluginIds as $pluginId) {
                $cached = $addFilterScanner->getCache()->get($pluginId);
                if (!$cached || $plugins[$pluginId]['metadata']['Version'] !== $cached['module']['metadata']['Version']) {
                    $pluginIdsToClear[] = $pluginId;
                    $pluginsToScan[$pluginId] = $plugins[$pluginId];
                }
            }

            $addFilterScanner->getCache()->clear($pluginIdsToClear);
            return $addFilterScanner->scan($pluginsToScan);
        }

        if ($action === 'bulk-rescan-selected') {
            if (!isset($_REQUEST['plugin_ids']) || empty($_REQUEST['plugin_ids'])) {
                return false;
            }

            $pluginIds = $_REQUEST['plugin_ids'];
            $pluginsToScan = [];
            foreach ($pluginIds as $pluginId) {
                $pluginsToScan[$pluginId] = $plugins[$pluginId];
            }

            $addFilterScanner->getCache()->clear($pluginIds);
            return $addFilterScanner->scan($pluginsToScan);
        }

        if ($action === 'bulk-scan-all') {
            return $addFilterScanner->scan($plugins);
        }

        if ($action === 'bulk-rescan-all-out-of-date') {
            $pluginIds = [];
            $pluginsToScan = [];
            foreach ($plugins as $pluginId => $plugin) {
                $cached = $addFilterScanner->getCache()->get($pluginId);
                if (!$cached || $plugin['metadata']['Version'] !== $cached['module']['metadata']['Version']) {
                    $pluginIds[] = $pluginId;
                    $pluginsToScan[$pluginId] = $plugins[$pluginId];
                }
            }

            $addFilterScanner->getCache()->clear($pluginIds);
            return $addFilterScanner->scan($pluginsToScan);
        }

        if ($action === 'bulk-rescan-all') {
            $pluginIds = [];
            foreach ($plugins as $pluginId => $plugin) {
                $pluginIds[] = $pluginId;
            }

            $addFilterScanner->getCache()->clear($pluginIds);
            return $addFilterScanner->scan($plugins);
        }

        return false;
    }

    protected function column_name($item) {
        $row_value = '<strong>' . esc_html($item['plugin']['metadata']['Name']) . '</strong>';
        return $row_value;
    }

    protected function column_version($item) {
        $row_value = esc_html($item['scanResult']['moduleVersion']);
        return $row_value;
    }

    protected function column_author($item) {
        $row_value = esc_html($item['plugin']['metadata']['Author']);
        return $row_value;
    }

    protected function column_path($item) {
        $row_value = esc_html($item['plugin']['id']);
        return $row_value;
    }

    protected function column_last_scanned($item) {
        $row_value = esc_html(isset($item['scanResult']['lastUpdated']) ? $item['scanResult']['lastUpdated'] : 'N/A');
        return $row_value;
    }

    protected function column_status($item) {
        if ($item['status'] === 2) {
            $status = __('Not Scanned', 'compatibuddy');
            $scanLinkText = __('Scan', 'compatibuddy');
            $scanLinkType = 'scan';
            $noticeClass = 'notice-error';
            $iconClass = 'dashicons-dismiss';
        } else if ($item['status'] === 1) {
            $status = __('Out of Date', 'compatibuddy');
            $scanLinkText = __('Rescan', 'compatibuddy');
            $scanLinkType = 'rescan';
            $noticeClass = 'notice-warning';
            $iconClass = 'dashicons-warning';
        } else {
            $status = __('Scanned', 'compatibuddy');
            $scanLinkText = __('Rescan', 'compatibuddy');
            $scanLinkType = 'rescan';
            $noticeClass = 'notice-success';
            $iconClass = 'dashicons-yes';
        }

        $actions['scan'] = '<a href="' .
            add_query_arg([
                'scan-action' => 'scan',
                'type' => $scanLinkType,
                'subject' => urlencode($item['plugin']['id'])
            ], admin_url('admin.php?page=compatibuddy-scan')) .
            '">' . $scanLinkText . '</a>';

        $row_value = '<div class="notice inline notice-alt ' . $noticeClass . '"><span class="dashicons ' . $iconClass . '"></span>&nbsp;<strong>' . $status . '</strong></div>';
        return $row_value . $this->row_actions($actions);
    }

    function get_bulk_actions() {
        $actions = [
            'bulk-scan-selected' => __('Scan selected that have not been scanned'),
            'bulk-rescan-selected-out-of-date' => __('Rescan selected that are out-of-date'),
            'bulk-rescan-selected' => __('Rescan selected'),
            'bulk-scan-all' => __('Scan all that have not been scanned'),
            'bulk-rescan-all-out-of-date' => __('Rescan all that are out-of-date'),
            'bulk-rescan-all' => __('Rescan all')
        ];
        return $actions;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="plugin_ids[]" value="%s" />', $item['plugin']['id']
        );
    }
}
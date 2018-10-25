<?php

namespace Compatibuddy\Tables;

use Compatibuddy\Analyzers\HigherPriorityAddFilterCallsAnalyzer;
use Compatibuddy\Scanners\ModuleScanner;
use Compatibuddy\Utilities;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ScanThemesTable extends \WP_List_Table {

    function __construct() {
        parent::__construct([
            'singular'=> 'compatibuddy_scan_theme',
            'plural' => 'compatibuddy_scan_themes',
            'ajax'   => false
        ]);
    }

    function extra_tablenav($which) {
        if ( $which == "top" ){
            $this->search_box(__('Search', 'compatibuddy'), 'scan_themes_search');
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

        $themes = Utilities::getThemes();
        $this->handle_actions($themes);

        $addFilterScanner = new ModuleScanner();
        $addFilterScanResults = $addFilterScanner->scan($themes, true);

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
        foreach ($themes as $basename => $theme) {
            $inSearch = false;
            if ($searchKey) {
                if (stripos(strtolower($basename), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($theme['metadata']['Name']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($theme['metadata']['Author']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($theme['metadata']['Version']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($theme['metadata']['Version']), $searchKey) !== false) {
                    $inSearch = true;
                } else if (stripos(strtolower($theme['metadata']['Author']), $searchKey) !== false) {
                    $inSearch = true;
                }
            }

            $formattedItems[$basename] = [
                'theme' => $theme,
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
                        $theme['metadata']['Version']) {
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
                            return strcasecmp($a['theme']['metadata']['Name'], $b['theme']['metadata']['Name']);
                        }

                        return strcasecmp($b['theme']['metadata']['Name'], $a['theme']['metadata']['Name']);
                    case 'version':
                        if ($order === 'asc') {
                            return strcasecmp($a['theme']['metadata']['Version'], $b['theme']['metadata']['Version']);
                        }

                        return strcasecmp($b['theme']['metadata']['Version'], $a['theme']['metadata']['Version']);
                    case 'author':
                        if ($order === 'asc') {
                            return strcasecmp($a['theme']['metadata']['Author'], $b['theme']['metadata']['Author']);
                        }

                        return strcasecmp($b['theme']['metadata']['Author'], $a['theme']['metadata']['Author']);
                    case 'path':
                        if ($order === 'asc') {
                            return strcasecmp($a['theme']['id'], $b['theme']['id']);
                        }

                        return strcasecmp($b['theme']['id'], $a['theme']['id']);
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

    function handle_actions($themes) {
        if (!isset($_REQUEST['_wpnonce']) || (!isset($_REQUEST['action']) && !isset($_REQUEST['action2']))) {
            return false;
        }

        $nonce = sanitize_key(wp_unslash($_REQUEST['_wpnonce']));
        if (!wp_verify_nonce($nonce, 'bulk-compatibuddy_scan_themes')) {
            return false;
        }

        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : $_REQUEST['action2'];

        $addFilterScanner = new ModuleScanner();

        if ($action === 'bulk-scan-selected') {
            if (!isset($_REQUEST['themes']) || empty($_REQUEST['themes'])) {
                return false;
            }

            $themeIds = $_REQUEST['themes'];
            $themesToScan = [];
            foreach ($themeIds as $themeId) {
                $themesToScan[$themeId] = $themes[$themeId];
            }

            return $addFilterScanner->scan($themesToScan);
        }

        if ($action === 'bulk-rescan-selected-out-of-date') {
            if (!isset($_REQUEST['themes']) || empty($_REQUEST['themes'])) {
                return false;
            }

            $themeIds = $_REQUEST['themes'];
            $themeIdsToClear = [];
            $themesToScan = [];
            foreach ($themeIds as $themeId) {
                $cached = $addFilterScanner->getCache()->get($themeId);
                if (!$cached || $themes[$themeId]['metadata']['Version'] !== $cached['module']['metadata']['Version']) {
                    $themeIdsToClear[] = $themeId;
                    $themesToScan[$themeId] = $themes[$themeId];
                }
            }

            $addFilterScanner->getCache()->clear($themeIdsToClear);
            return $addFilterScanner->scan($themesToScan);
        }

        if ($action === 'bulk-rescan-selected') {
            if (!isset($_REQUEST['themes']) || empty($_REQUEST['themes'])) {
                return false;
            }

            $themeIds = $_REQUEST['themes'];
            $themesToScan = [];
            foreach ($themeIds as $themeId) {
                $themesToScan[$themeId] = $themes[$themeId];
            }

            $addFilterScanner->getCache()->clear($themeIds);
            return $addFilterScanner->scan($themesToScan);
        }

        if ($action === 'bulk-scan-all') {
            return $addFilterScanner->scan($themes);
        }

        if ($action === 'bulk-rescan-all-out-of-date') {
            $themeIds = [];
            $themesToScan = [];
            foreach ($themes as $themeId => $theme) {
                $cached = $addFilterScanner->getCache()->get($themeId);
                if (!$cached || $theme['metadata']['Version'] !== $cached['module']['metadata']['Version']) {
                    $themeIds[] = $themeId;
                    $themesToScan[$themeId] = $themes[$themeId];
                }
            }

            $addFilterScanner->getCache()->clear($themeIds);
            return $addFilterScanner->scan($themesToScan);
        }

        if ($action === 'bulk-rescan-all') {
            $themeIds = [];
            foreach ($themes as $themeId => $theme) {
                $themeIds[] = $themeId;
            }

            $addFilterScanner->getCache()->clear($themeIds);
            return $addFilterScanner->scan($themes);
        }

        return false;
    }

    protected function column_name($item) {
        if ($item['status'] === 2) {
            $scanLinkText = __('Scan', 'compatibuddy');
        } else if ($item['status'] === 1) {
            $scanLinkText = __('Rescan', 'compatibuddy');
        } else {
            $scanLinkText = __('Rescan', 'compatibuddy');
        }

        $actions['scan'] = '<a href="#" class="compatibuddy-scan-theme-link" data-theme="' . $item['theme']['id'] .'">' . $scanLinkText . '</a>';

        $row_value = '<strong>' . esc_html($item['theme']['metadata']['Name']) . '</strong>';
        return $row_value . $this->row_actions($actions);
    }

    protected function column_version($item) {
        $row_value = esc_html($item['theme']['metadata']['Version']);
        return $row_value;
    }

    protected function column_author($item) {
        $row_value = esc_html($item['theme']['metadata']['Author']);
        return $row_value;
    }

    protected function column_path($item) {
        $row_value = esc_html($item['theme']['absolute_directory']);
        return $row_value;
    }

    protected function column_last_scanned($item) {
        $row_value = esc_html(isset($item['scanResult']['lastUpdated']) ? $item['scanResult']['lastUpdated'] : 'N/A');
        return $row_value;
    }

    protected function column_status($item) {
        if ($item['status'] === 2) {
            $status = __('Not Scanned', 'compatibuddy');
            $noticeClass = 'notice-error';
            $iconClass = 'dashicons-dismiss';
        } else if ($item['status'] === 1) {
            $status = __('Out of Date', 'compatibuddy');
            $noticeClass = 'notice-warning';
            $iconClass = 'dashicons-warning';
        } else {
            $status = __('Scanned', 'compatibuddy');
            $noticeClass = 'notice-success';
            $iconClass = 'dashicons-yes';
        }

        $row_value = '<div class="notice inline notice-alt ' . $noticeClass . '"><span class="dashicons ' . $iconClass . '"></span>&nbsp;<strong>' . $status . '</strong></div>';
        return $row_value;
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
            '<input type="checkbox" name="themes[]" value="%s" />', $item['theme']['id']
        );
    }
}
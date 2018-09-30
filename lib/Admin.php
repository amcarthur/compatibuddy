<?php

namespace Compatibuddy;

use Compatibuddy\Analyzers\DuplicateAddFilterAnalyzer;
use Compatibuddy\Analyzers\HigherPriorityAddFilterAnalyzer;
use Compatibuddy\Caches\AddFilterCache;
use Compatibuddy\Scanners\AddFilterScanner;
use Compatibuddy\Tables\ScanPluginsTable;

/**
 * Defines the WordPress admin page behavior.
 * @package Compatibuddy
 */
class Admin {

    /**
     * @var \League\Plates\Engine
     */
    private $templateEngine;
    /**
     * Initializes the member variables.
     */
    public function __construct() {
        $this->templateEngine = new \League\Plates\Engine(
            Environment::getValue(EnvironmentVariable::TEMPLATES_DIRECTORY));

        $this->templateEngine->addFolder('scan',
            Environment::getValue(EnvironmentVariable::TEMPLATES_DIRECTORY) . '/scan');

        $this->templateEngine->addFolder('analyze',
            Environment::getValue(EnvironmentVariable::TEMPLATES_DIRECTORY) . '/analyze');
    }

    /**
     * Registers the WordPress hooks relevant to the admin page.
     */
    public function setup() {
        add_action('admin_menu', [$this, 'addMenuItems']);
        if (is_admin()) {
            add_action('wp_ajax_compatibuddy_scan', [$this, 'ajax_scan']);
        }
    }

    public function addMenuItems() {
        add_menu_page(
            'Compatibuddy',
            'Compatibuddy',
            'activate_plugins',
            'compatibuddy',
            [$this, 'compatibuddyAction']
        );

        add_submenu_page(
            'compatibuddy',
            'Compatibuddy',
            'Scan',
            'activate_plugins',
            'compatibuddy-scan',
            [$this, 'compatibuddyScanAction']
        );

        add_submenu_page(
            'compatibuddy',
            'Compatibuddy',
            'Analyze',
            'activate_plugins',
            'compatibuddy-analyze',
            [$this, 'compatibuddyAnalyzeAction']
        );

        add_submenu_page(
            'compatibuddy',
            'Compatibuddy',
            'Settings',
            'activate_plugins',
            'compatibuddy-settings',
            [$this, 'compatibuddySettingsAction']
        );
    }

    public function compatibuddyAction() {
        echo 'test1';
        //$this->router->route($this->router->parseRoute());
    }

    public function compatibuddyScanAction() {
        $currentTab = (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'themes') ? 'themes' : 'plugins';

        $tabData = [];
        switch ($currentTab) {
            case 'plugins':
                $tabData['table'] = new ScanPluginsTable($this);
                $tabData['table']->prepare_items();
                break;
            case 'themes':

                break;
            default:
        }

        echo $this->templateEngine->render('scan', [
            'title' => __('Scan', 'compatibuddy'),
            'currentTab' => $currentTab,
            'pluginsUri' => add_query_arg(['tab' => 'plugins'], admin_url('admin.php?page=compatibuddy-scan')),
            'themesUri' => add_query_arg(['tab' => 'themes'], admin_url('admin.php?page=compatibuddy-scan')),
            'tabData' => $tabData
        ]);
    }

    public function compatibuddyAnalyzeAction() {
        $currentTab = (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'higherPriorityFilters')
            ? 'higherPriorityFilters' : 'duplicateFilters';

        $plugins = Utilities::getPlugins();
        $tabData = [];
        switch ($currentTab) {
            case 'duplicateFilters':
                $addFilterScanner = new AddFilterScanner();
                $duplicateFilterAnalyzer = new DuplicateAddFilterAnalyzer();
                $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($addFilterScanner->scan($plugins, true));
                break;
            case 'higherPriorityFilters':
                $tabData['plugins'] = $plugins;

                if (isset($_REQUEST['compatibuddy-higher-priority-filters-subject'])
                    && isset($_REQUEST['_wpnonce'])
                    && wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])),
                        'compatibuddy_analyze_higher_priority_filters_subject_select')) {

                    $subjectId = $_REQUEST['compatibuddy-higher-priority-filters-subject'];
                    if (!isset($plugins[$subjectId])) {
                        // TODO: Display error message
                        break;
                    }
                    $addFilterScanner = new AddFilterScanner();
                    $higherPriorityFilterAnalyzer = new HigherPriorityAddFilterAnalyzer();
                    $analysis = $higherPriorityFilterAnalyzer->analyze($addFilterScanner->scan($plugins, true), $plugins[$subjectId]);
                    if (empty($tabData['analysis'])) {
                        // TODO: Display message
                        break;
                    }
                    $tabData['analysis'] = $analysis;
                }
                break;
            default:
        }

        echo $this->templateEngine->render('analyze', [
            'title' => __('Analyze', 'compatibuddy'),
            'currentTab' => $currentTab,
            'duplicateFiltersUri' => add_query_arg(['tab' => 'duplicateFilters'], admin_url('admin.php?page=compatibuddy-analyze')),
            'higherPriorityFiltersUri' => add_query_arg(['tab' => 'higherPriorityFilters'], admin_url('admin.php?page=compatibuddy-analyze')),
            'tabData' => $tabData
        ]);
    }

    public function compatibuddySettingsAction() {
        echo 'test2';
        //$this->router->route($this->router->parseRoute());
    }

    public function ajax_scan() {
        if (!isset($_REQUEST['_wpnonce'])) {
            wp_die();
        }

        $nonce = sanitize_key(wp_unslash($_REQUEST['_wpnonce']));
        if (!wp_verify_nonce($nonce, 'compatibuddy-ajax')) {
            wp_die();
        }

        $plugins = Utilities::getPlugins();

        if (!isset($plugins[$_REQUEST['plugin']])) {
            wp_die();
        }

        $addFilterScanner = new AddFilterScanner();

        $plugin = $plugins[$_REQUEST['plugin']];
        $cached = $addFilterScanner->getCache()->get($_REQUEST['plugin']);

        if ($cached) {
            $addFilterScanner->getCache()->clear([$_REQUEST['plugin']]);
        }

        $addFilterScanner->scan([$plugin]);
        wp_die();
    }
}
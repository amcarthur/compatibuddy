<?php

namespace Compatibuddy;

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
        echo 'test2';
        //$this->router->route($this->router->parseRoute());
    }

    public function compatibuddySettingsAction() {
        echo 'test2';
        //$this->router->route($this->router->parseRoute());
    }

    public function ajax_scan() {
        if (!isset($_REQUEST['_wpnonce'])) {
            wp_die();
        }

        $nonce = wp_unslash($_REQUEST['_wpnonce']);
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
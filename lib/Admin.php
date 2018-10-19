<?php

namespace Compatibuddy;

use Compatibuddy\Analyzers\DuplicateAddFilterAnalyzer;
use Compatibuddy\Analyzers\HigherPriorityAddFilterAnalyzer;
use Compatibuddy\Caches\AddFilterCache;
use Compatibuddy\Scanners\AddFilterScanner;
use Compatibuddy\Tables\ScanPluginsTable;
use Compatibuddy\Tables\ScanThemesTable;

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
        add_action('admin_init', [$this, 'adminInit']);
        add_action('admin_menu', [$this, 'adminMenu']);
        if (is_admin()) {
            add_action('wp_ajax_compatibuddy_scan_plugin', [$this, 'ajax_scan_plugin']);
            add_action('wp_ajax_compatibuddy_scan_theme', [$this, 'ajax_scan_theme']);
        }
        add_filter('parent_file', [$this, 'testing_func'], 5);
    }

    public function testing_func() {

    }

    public function adminInit() {
        register_setting('compatibuddy_options', 'compatibuddy_options', [$this, 'validateOptions']);
        add_settings_section('general_settings', 'General Settings', [$this, 'renderGeneralSettingsSection'], 'compatibuddy-settings');
        add_settings_field('use_cache', 'Use Cache', [$this, 'renderUseCacheField'], 'compatibuddy-settings', 'general_settings');

        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
            if ($action === 'compatibuddy-filter-export') {
                if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])), 'compatibuddy_filter_export')) {
                    die("Invalid request.");
                }

                $plugins = Utilities::getPlugins();
                $themes = Utilities::getThemes();
                $modules = array_merge($plugins, $themes);
                $addFilterScanner = new AddFilterScanner();
                //$duplicateFilterAnalyzer = new DuplicateAddFilterAnalyzer();
                $scan = $addFilterScanner->scan($modules, true);
                $encoded = json_encode($scan);
                header('Content-disposition: attachment; filename=compatibuddy_export.json');
                header('Content-type: application/json');
                die($encoded);
            }
        }
    }

    public function adminMenu() {
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
            'Dashboard',
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

    public function renderOptionsPage() {
        echo '<div>
<form action="options.php" method="post">';

settings_fields('compatibuddy_options');
do_settings_sections('compatibuddy');
submit_button();
echo '</form></div>';
    }

    public function renderGeneralSettingsSection() {
        echo '<p>General settings for Compatibuddy.</p>';
    }

    public function renderUseCacheField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['use_cache'])) {
            $options = [
                'use_cache' => false
            ];
        }

        echo '<input id="compatibuddy_options_use_cache" name="compatibuddy_options[use_cache]" type="checkbox" value="1" ' . checked(1, $options['use_cache'], false) . ' /> Automatically caches scan results.';
    }

    public function validateOptions($options) {
        return $options;
    }

    public function compatibuddyAction() {
        echo $this->templateEngine->render('dashboard', [
            'title' => __('Dashboard', 'compatibuddy')
        ]);
    }

    public function compatibuddyScanAction() {
        $currentTab = (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'themes') ? 'themes' : 'plugins';

        $tabData = [];
        switch ($currentTab) {
            case 'plugins':
                $tabData['table'] = new ScanPluginsTable();
                $tabData['table']->prepare_items();
                break;
            case 'themes':
                $tabData['table'] = new ScanThemesTable();
                $tabData['table']->prepare_items();
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
            ? 'higherPriorityFilters' : 'filters';

        $plugins = Utilities::getPlugins();
        $themes = Utilities::getThemes();
        $modules = array_merge($plugins, $themes);

        $scan = null;
        if (isset($_FILES['importfile'])
            && isset($_REQUEST['_wpnonce'])
            && wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])), 'compatibuddy-filter-import')) {
            //array_map('unlink', array_filter((array) glob(Environment::getValue(EnvironmentVariable::TMP_DIRECTORY))));
            $contents = file_get_contents($_FILES['importfile']['tmp_name']);

            $import_file_name = uniqid() . '.json';
            move_uploaded_file($_FILES['importfile']['tmp_name'], Environment::getValue(EnvironmentVariable::TMP_DIRECTORY) . '/' . $import_file_name);
            $scan = json_decode($contents, true);
        }

        $tabData = [];
        switch ($currentTab) {
            case 'filters':
                $addFilterScanner = new AddFilterScanner();
                $duplicateFilterAnalyzer = new DuplicateAddFilterAnalyzer();

                $tabData['subjectAnalysisUri'] = add_query_arg(
                    [
                        'tab' => 'filters',
                        'action' => 'analyze-subject'
                    ], admin_url('admin.php?page=compatibuddy-analyze')
                );

                $tabData['plugins'] = $plugins;
                $tabData['themes'] = $themes;

                if (isset($_REQUEST['action'])
                    && $_REQUEST['action'] === 'analyze-subject'
                    && isset($_REQUEST['subject'])
                    && isset($_REQUEST['_wpnonce'])
                    && wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])),
                        'compatibuddy-filter-analyze-subject')) {

                    $subject = $_REQUEST['subject'];
                    $count = 0;
                    $subject = preg_replace('/^plugin\-/', '', $subject, -1, $count);

                    if ($count === 1) {
                        $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true), $plugins[$subject]);
                    } else if ($count === 1) {
                        $subject = preg_replace('/^theme\-/', '', $subject, -1, $count);
                        $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true), $themes[$subject]);
                    } else {
                        $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true));
                    }
                } else {

                    $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true));
                }

                break;
            case 'higherPriorityFilters':

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
                    $analysis = $higherPriorityFilterAnalyzer->analyze($addFilterScanner->scan($modules, true), $plugins[$subjectId]);
                    if (empty($analysis)) {
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
            'filtersUri' => add_query_arg(['tab' => 'filters'], admin_url('admin.php?page=compatibuddy-analyze')),
            'higherPriorityFiltersUri' => add_query_arg(['tab' => 'higherPriorityFilters'], admin_url('admin.php?page=compatibuddy-analyze')),
            'tabData' => $tabData
        ]);
    }

    public function compatibuddySettingsAction() {
        echo $this->templateEngine->render('settings', [
            'title' => __('Settings', 'compatibuddy')
        ]);
    }

    public function ajax_scan_plugin() {
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

    public function ajax_scan_theme() {
        if (!isset($_REQUEST['_wpnonce'])) {
            wp_die();
        }

        $nonce = sanitize_key(wp_unslash($_REQUEST['_wpnonce']));
        if (!wp_verify_nonce($nonce, 'compatibuddy-ajax')) {
            wp_die();
        }

        $themes = Utilities::getThemes();

        if (!isset($themes[$_REQUEST['theme']])) {
            wp_die();
        }

        $addFilterScanner = new AddFilterScanner();

        $theme = $themes[$_REQUEST['theme']];
        $cached = $addFilterScanner->getCache()->get($_REQUEST['theme']);

        if ($cached) {
            $addFilterScanner->getCache()->clear([$_REQUEST['theme']]);
        }

        $addFilterScanner->scan([$theme]);
        wp_die();
    }
}
<?php

namespace Compatibuddy;

use Compatibuddy\Analyzers\AddFilterAnalyzer;
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
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_compatibuddy_report', [$this, 'saveReport'], 10, 3);
        add_action('media_buttons', [$this, 'mediaButtons']);

        if (is_admin()) {
            add_action('wp_ajax_compatibuddy_scan_plugin', [$this, 'ajax_scan_plugin']);
            add_action('wp_ajax_compatibuddy_scan_theme', [$this, 'ajax_scan_theme']);
        }
        add_filter('parent_file', [$this, 'testing_func'], 5);
        add_filter('parent_file', [$this, 'testing_func'], 15);
    }

    public function testing_func() {

    }

    public function adminInit() {
        register_setting('compatibuddy_options', 'compatibuddy_options', [$this, 'validateOptions']);

        add_settings_section('general_settings', 'General Settings', [$this, 'renderGeneralSettingsSection'], 'compatibuddy-settings');
        add_settings_section('scanning_settings', 'Scanning Settings', [$this, 'renderScanningSettingsSection'], 'compatibuddy-settings');
        add_settings_section('analysis_settings', 'Analysis Settings', [$this, 'renderAnalysisSettingsSection'], 'compatibuddy-settings');
        add_settings_section('reporting_settings', 'Reporting Settings', [$this, 'renderReportingSettingsSection'], 'compatibuddy-settings');

        /**
         * Scanning settings
         */
        add_settings_field('scan_add_filter', 'Scan Add Filter', [$this, 'renderScanAddFilterField'], 'compatibuddy-settings', 'scanning_settings');
        add_settings_field('scan_remove_filter', 'Scan Remove Filter', [$this, 'renderScanRemoveFilterField'], 'compatibuddy-settings', 'scanning_settings');
        add_settings_field('scan_remove_all_filters', 'Scan Remove All Filters', [$this, 'renderScanRemoveAllFiltersField'], 'compatibuddy-settings', 'scanning_settings');
        add_settings_field('scan_add_action', 'Scan Add Action', [$this, 'renderScanAddActionField'], 'compatibuddy-settings', 'scanning_settings');
        add_settings_field('scan_remove_action', 'Scan Remove Action', [$this, 'renderScanRemoveActionField'], 'compatibuddy-settings', 'scanning_settings');
        add_settings_field('scan_remove_all_actions', 'Scan Remove All Actions', [$this, 'renderScanRemoveAllActionsField'], 'compatibuddy-settings', 'scanning_settings');

        /**
         * Reporting settings
         */
        add_settings_field('report_automatic', 'Automatic Reports', [$this, 'renderReportAutomaticField'], 'compatibuddy-settings', 'reporting_settings');
        add_settings_field('report_visual', 'Visual', [$this, 'renderReportVisualField'], 'compatibuddy-settings', 'reporting_settings');
        add_settings_field('report_visibility', 'Visibility', [$this, 'renderReportVisibilityField'], 'compatibuddy-settings', 'reporting_settings');
        add_settings_field('report_user_roles', 'Restrict Access to User Roles', [$this, 'renderReportUserRolesField'], 'compatibuddy-settings', 'reporting_settings');

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
            'Reports',
            'activate_plugins',
            'edit.php?post_type=compatibuddy_report'
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

    public function renderGeneralSettingsSection() {
        echo '<p>General settings.</p>';
    }

    public function renderScanningSettingsSection() {
        echo '<p>Scanning settings.</p>';
    }

    public function renderAnalysisSettingsSection() {
        echo '<p>Analysis settings.</p>';
    }

    public function renderReportingSettingsSection() {
        echo '<p>Reporting settings.</p>';
    }

    public function renderScanAddFilterField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_add_filter'])) {
            $options = [
                'scan_add_filter' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_add_filter" name="compatibuddy_options[scan_add_filter]" type="checkbox" value="1" ' . checked(1, $options['scan_add_filter'], false) . ' /> Scan for add_filter calls.';
    }

    public function renderScanRemoveFilterField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_remove_filter'])) {
            $options = [
                'scan_remove_filter' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_remove_filter" name="compatibuddy_options[scan_remove_filter]" type="checkbox" value="1" ' . checked(1, $options['scan_remove_filter'], false) . ' /> Scan for remove_filter calls.';
    }

    public function renderScanRemoveAllFiltersField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_remove_all_filters'])) {
            $options = [
                'scan_remove_all_filters' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_remove_all_filters" name="compatibuddy_options[scan_remove_all_filters]" type="checkbox" value="1" ' . checked(1, $options['scan_remove_all_filters'], false) . ' /> Scan for remove_all_filters calls.';
    }

    public function renderScanAddActionField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_add_action'])) {
            $options = [
                'scan_add_action' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_add_action" name="compatibuddy_options[scan_add_action]" type="checkbox" value="1" ' . checked(1, $options['scan_add_action'], false) . ' /> Scan for add_action calls.';
    }

    public function renderScanRemoveActionField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_remove_action'])) {
            $options = [
                'scan_remove_action' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_remove_action" name="compatibuddy_options[scan_remove_action]" type="checkbox" value="1" ' . checked(1, $options['scan_remove_action'], false) . ' /> Scan for remove_action calls.';
    }

    public function renderScanRemoveAllActionsField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['scan_remove_all_actions'])) {
            $options = [
                'scan_remove_all_actions' => false
            ];
        }

        echo '<input id="compatibuddy_options_scan_remove_all_actions" name="compatibuddy_options[scan_remove_all_actions]" type="checkbox" value="1" ' . checked(1, $options['scan_remove_all_actions'], false) . ' /> Scan for remove_all_actions calls.';
    }

    public function renderReportAutomaticField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['report_automatic'])) {
            $options = [
                'report_automatic' => false
            ];
        }

        echo '<input type="checkbox" id="compatibuddy_options_report_automatic" name="compatibuddy_options[report_automatic]" value="1" '
            . checked(1, $options['report_automatic'], false) . ' /> Enable Automatic Reports';
    }

    public function renderReportVisualField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['report_visual'])) {
            $options = [
                'report_visual' => 'tree'
            ];
        }

        $visuals = [
            'tree' => __('Tree', 'compatibuddy'),
            'pie' => __('Pie Chart', 'compatibuddy'),
            'bar' => __('Bar Graph', 'compatibuddy'),
        ];

        echo '
<select id="compatibuddy_options_report_visual" name="compatibuddy_options[report_visual]">';
        foreach ($visuals as $key => $value) {
            echo '<option value="' . esc_attr($key) . '" ' . ($options['report_visual'] === $key ? 'selected' : '') . '>' . esc_html($value) . '</option>';
        }
        echo '
</select>';
    }

    public function renderReportVisibilityField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['report_visibility'])) {
            $options = [
                'report_visibility' => 'public',
                'report_password' => ''
            ];
        }

        echo '<input type="radio" name="compatibuddy_options[report_visibility]" value="public" ' . ($options['report_visibility'] === 'public' ? 'checked' : '') . ' /> Public<br />';
        echo '<input type="radio" name="compatibuddy_options[report_visibility]" value="private" ' . ($options['report_visibility'] === 'private' ? 'checked' : '') . ' /> Private<br />';
        echo '<input type="radio" name="compatibuddy_options[report_visibility]" value="password" ' . ($options['report_visibility'] === 'password' ? 'checked' : '') . ' /> Password Protected<br />';
        echo '<input type="text" id="compatibuddy_options_report_password" name="compatibuddy_options[report_password]" placeholder="' . __('Password', 'compatibuddy') . '" value="'
            . esc_attr($options['report_password']) . '" />';
    }

    public function renderReportUserRolesField() {
        $options = get_option('compatibuddy_options', []);
        if (!$options || !isset($options['report_user_roles'])) {
            $options = [
                'report_user_roles' => []
            ];
        }

        $roles = get_editable_roles();
        foreach ($roles as $key => $role) {
            echo '<input type="checkbox" name="compatibuddy_options[report_user_roles][]" value="'
                . esc_attr($key) . '" ' . (in_array($key, $options['report_user_roles']) ? 'checked' : '') . ' /> '
                . esc_html(translate_user_role($role['name'])) . '<br />';
        }
    }

    public function validateOptions($options) {
        return $options;
    }

    public function addMetaBoxes() {
        add_meta_box(
            'compatibuddy_report_user_roles',
            __('Restrict Access to User Roles', 'compatibuddy'),
            [$this, 'renderReportUserRolesMetaBox'],
            'compatibuddy_report',
            'side',
            'default'
        );
    }

    public function renderReportUserRolesMetaBox() {
        global $post;

        wp_nonce_field('compatibuddy_save_report', 'compatibuddy_save_report_user_roles_nonce');

        $currentValues = get_post_meta($post->ID, 'compatibuddy_report_user_roles', true);
        $roles = get_editable_roles();
        foreach ($roles as $key => $role) {
            echo '<input type="checkbox" name="compatibuddy_report_user_roles[]" value="'
                . esc_attr($key) . '" ' . (!empty($currentValues) && in_array($key, $currentValues) ? 'checked' : '') . ' /> '
                . esc_html(translate_user_role($role['name'])) . '<br />';
        }
    }

    public function saveReport($post_id, $post, $update) {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if ('auto-draft' === $post->post_status) {
            return;
        }

        if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can( 'edit_post', $post_id)) {
            return;
        }

        if (!isset($_POST['compatibuddy_save_report_user_roles_nonce'])
            || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['compatibuddy_save_report_user_roles_nonce'])),
                'compatibuddy_save_report')) {
            return;
        }

        $newUserRoles = [];
        $roles = get_editable_roles();
        foreach ($roles as $key => $role) {
            if (isset($_POST['compatibuddy_report_user_roles'])
                && in_array($key, $_POST['compatibuddy_report_user_roles'])) {
                $newUserRoles[] = $key;
            }
        }

        update_post_meta($post_id, 'compatibuddy_report_user_roles', $newUserRoles);
    }

    public function mediaButtons() {
        if (get_post_type() === 'compatibuddy_report') {
            echo '<a href="#" id="compatibuddy-report-insert-visual" class="button">Insert Visual</a>';
        }
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
                $duplicateFilterAnalyzer = new AddFilterAnalyzer();

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
                        $tabData['subject'] = $plugins[$subject];
                        $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true), $plugins[$subject]);
                    } else {
                        $subject = preg_replace('/^theme\-/', '', $subject, -1, $count);
                        if ($count === 1) {
                            $tabData['subject'] = $themes[$subject];
                            $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true), $themes[$subject]);
                        } else {
                            $tabData['analysis'] = $duplicateFilterAnalyzer->analyze($scan !== null ? $scan : $addFilterScanner->scan($modules, true));
                        }
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
        if (!wp_verify_nonce($nonce, 'compatibuddy-admin-ajax')) {
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
        if (!wp_verify_nonce($nonce, 'compatibuddy-admin-ajax')) {
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

    public function changeReportVisibilityMetabox() {
        global $post;
        if ($post->post_type !== 'compatibuddy_report')
            return;

        $post->post_password = '';
        $visibility = 'private';
        $visibility_trans = __('Private');
        ?>
        <script type="text/javascript">
            (function($){
                try {
                    $('#post-visibility-display').text('<?php echo $visibility_trans; ?>');
                    $('#hidden-post-visibility').val('<?php echo $visibility; ?>');
                } catch(err){}
            }) (jQuery);
        </script>
        <?php
    }
}
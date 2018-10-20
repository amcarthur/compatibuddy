<?php
/**
 * Copyright (C) 2018 Aidan McArthur
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Compatibuddy;

/**
 * Defines core functionality.
 * @package Compatibuddy
 */
class Core {
    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var Reports
     */
    private $reports;

    /**
     * Initializes member variables.
     */
    public function __construct() {
        $this->admin = new Admin();
        $this->reports = new Reports();
        $this->setup();
    }

    /**
     * Registers WordPress hooks and calls its child setup methods.
     */
    public function setup() {
        register_activation_hook(Environment::getValue(EnvironmentVariable::PLUGIN_FILE), [$this, 'activate']);
        register_deactivation_hook(Environment::getValue(EnvironmentVariable::PLUGIN_FILE), [$this, 'deactivate']);
        register_uninstall_hook(Environment::getValue(EnvironmentVariable::PLUGIN_FILE), [__CLASS__, 'uninstall']);

        add_action('init', [$this, 'init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        $this->admin->setup();
        $this->reports->setup();
    }

    public function activate() {
        Database::ensureSchema();

        $options = get_option('compatibuddy_options');
        if (!$options) {
            add_option('compatibuddy_options', [
                'scan_add_filter' => true,
                'scan_remove_filter' => true,
                'scan_remove_all_filters' => true,
                'scan_add_action' => true,
                'scan_remove_action' => true,
                'scan_remove_all_actions' => true,
                'report_visual' => 'tree',
                'report_user_roles' => [],
                'report_password_protect' => '',
            ]);
        }

        $this->init();

        /**
         * 'publish_posts' => 'publish_compatibuddyreports',
        'edit_posts' => 'edit_compatibuddyreports',
        'edit_others_posts' => 'edit_others_compatibuddyreports',
        'read_private_posts' => 'read_private_compatibuddyreports',
        'edit_post' => 'edit_compatibuddyreport',
        'edit_private_posts' => 'edit_private_compatibuddyreports',
        'edit_published_posts' => 'edit_published_compatibuddyreports',
        'delete_post' => 'delete_compatibuddyreport',
        'delete_posts' => 'delete_compatibuddyreports',
        'create_posts' => 'create_compatibuddyreports',
        'delete_others_posts'  => 'delete_other_compatibuddyreports',
        'delete_published_posts'  => 'delete_published_compatibuddyreports',
        'delete_private_posts'  => 'delete_private_compatibuddyreports',
        'read_post' => 'read_compatibuddyreport',
        'read' => 'read',
         */
        $role = get_role('administrator');
        $role->add_cap('publish_compatibuddy_reports');
        $role->add_cap('edit_compatibuddy_reports');
        $role->add_cap('edit_others_compatibuddy_reports');
        $role->add_cap('edit_private_compatibuddy_reports');
        $role->add_cap('edit_published_compatibuddy_reports');
        $role->add_cap('read_private_compatibuddy_reports');
        $role->add_cap('edit_compatibuddy_report');
        $role->add_cap('edit_compatibuddy_reports');
        $role->add_cap('delete_compatibuddy_report');
        $role->add_cap('delete_compatibuddy_reports');
        $role->add_cap('delete_other_compatibuddy_reports');
        $role->add_cap('delete_private_compatibuddy_reports');
        $role->add_cap('delete_published_compatibuddy_reports');
        $role->add_cap('create_compatibuddy_reports');
        $role->add_cap('read_compatibuddy_report');

        flush_rewrite_rules();
    }

    public function deactivate() {
        $role = get_role('administrator');
        $role->remove_cap('publish_compatibuddy_reports');
        $role->remove_cap('edit_compatibuddy_reports');
        $role->remove_cap('edit_others_compatibuddy_reports');
        $role->remove_cap('edit_private_compatibuddy_reports');
        $role->remove_cap('edit_published_compatibuddy_reports');
        $role->remove_cap('read_private_compatibuddy_reports');
        $role->remove_cap('edit_compatibuddy_report');
        $role->remove_cap('edit_compatibuddy_reports');
        $role->remove_cap('delete_compatibuddy_report');
        $role->remove_cap('delete_compatibuddy_reports');
        $role->remove_cap('delete_other_compatibuddy_reports');
        $role->remove_cap('delete_private_compatibuddy_reports');
        $role->remove_cap('delete_published_compatibuddy_reports');
        $role->remove_cap('create_compatibuddy_reports');
        $role->remove_cap('read_compatibuddy_report');
    }

    public static function uninstall() {
        Database::dropSchema();
        delete_option('compatibuddy_options');
    }

    public function init() {
        register_post_type( 'compatibuddy_report',
            [
                'labels' => [
                    'name' => __('Compatibuddy Reports', 'compatibuddy'),
                    'singular_name' => __('Compatibuddy Report', 'compatibuddy'),
                    'add_new' => __('Add New', 'compatibuddy'),
                    'add_new_item' => __('Add New Report', 'compatibuddy'),
                    'edit_item' => __('Edit Report', 'compatibuddy'),
                    'new_item' => __('New Report', 'compatibuddy'),
                    'view_item' => __('View Report', 'compatibuddy'),
                    'view_items' => __('View Reports', 'compatibuddy'),
                    'search_items' => __('Search Reports', 'compatibuddy'),
                    'not_found' => __('No reports found', 'compatibuddy'),
                    'not_found_in_trash' => __('No reports found in trash', 'compatibuddy'),
                    'all_items' => __('All Reports', 'compatibuddy'),
                    'archives' => __('Report Archives', 'compatibuddy'),
                    'attributes' => __('Report Attributes', 'compatibuddy'),
                    'insert_into_item' => __('Insert into report', 'compatibuddy'),
                    'uploaded_to_this_item' => __('Uploaded to this report', 'compatibuddy')

                ],
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'show_in_nav_menus' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_position' => 5,
                'has_archive' => true,
                'rewrite' => ['slug' => 'compatibuddy-reports'],
                'capability_type' => ['compatibuddy_report', 'compatibuddy_reports'],
                'capabilities' => [
                    'publish_posts' => 'publish_compatibuddy_reports',
                    'edit_posts' => 'edit_compatibuddy_reports',
                    'edit_others_posts' => 'edit_others_compatibuddy_reports',
                    'read_private_posts' => 'read_private_compatibuddy_reports',
                    'edit_post' => 'edit_compatibuddy_report',
                    'edit_private_posts' => 'edit_private_compatibuddy_reports',
                    'edit_published_posts' => 'edit_published_compatibuddy_reports',
                    'delete_post' => 'delete_compatibuddy_report',
                    'delete_posts' => 'delete_compatibuddy_reports',
                    'create_posts' => 'create_compatibuddy_reports',
                    'delete_others_posts'  => 'delete_other_compatibuddy_reports',
                    'delete_published_posts'  => 'delete_published_compatibuddy_reports',
                    'delete_private_posts'  => 'delete_private_compatibuddy_reports',
                    'read_post' => 'read_compatibuddy_report'
                ],
                'map_meta_cap' => true
            ]
        );
    }

    public function enqueue_scripts($hook) {
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        wp_enqueue_style(
            'jstree',
            plugins_url('/assets/js/jstree/themes/default/style' . $suffix . '.css',
                Environment::getValue(EnvironmentVariable::PLUGIN_FILE))
        );

        // TODO: add .min
        wp_enqueue_style(
            'compatibuddy',
            plugins_url('/assets/css/compatibuddy.css',
                Environment::getValue(EnvironmentVariable::PLUGIN_FILE)),
            ['jstree']
        );

        wp_enqueue_script(
            'jstree',
            plugins_url('/assets/js/jstree/jstree' . $suffix . '.js',
                Environment::getValue(EnvironmentVariable::PLUGIN_FILE)),
            ['jquery']
        );

        wp_enqueue_script(
            'Chart',
            plugins_url('/assets/js/Chart' . $suffix . '.js',
                Environment::getValue(EnvironmentVariable::PLUGIN_FILE))
        );

        // TODO: add .min
        wp_enqueue_script(
            'compatibuddy',
            plugins_url('/assets/js/compatibuddy.js',
                Environment::getValue(EnvironmentVariable::PLUGIN_FILE)),
            ['jquery', 'jstree']
        );

        wp_localize_script(
            'compatibuddy',
            'ajax_object',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('compatibuddy-ajax')
            ]
        );
    }
}
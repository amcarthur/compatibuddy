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
        add_filter( 'map_meta_cap', [$this, 'mapMetaCap'], 10, 4 );

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

        $role = get_role('administrator');
        $capabilities = $this->compileReportPostTypeCapabilities('compatibuddy_report', 'compatibuddy_reports');
        foreach ($capabilities as $capability) {
            $role->add_cap($capability);
        }

        flush_rewrite_rules();
    }

    public function deactivate() {
        $role = get_role('administrator');
        $capabilities = $this->compileReportPostTypeCapabilities('compatibuddy_report', 'compatibuddy_reports');
        foreach ($capabilities as $capability) {
            $role->remove_cap($capability);
        }
    }

    public static function uninstall() {
        Database::dropSchema();
        delete_option('compatibuddy_options');
    }

    public function init() {
        register_post_type( 'compatibuddy_report',
            [
                'labels' => [
                    'name' => __('Reports', 'compatibuddy'),
                    'singular_name' => __('Report', 'compatibuddy'),
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
                'capabilities' => $this->compileReportPostTypeCapabilities('compatibuddy_report', 'compatibuddy_reports'),
                'map_meta_cap' => true
            ]
        );
    }

    public function mapMetaCap($caps, $cap, $userId, $args) {
        /* If editing, deleting, or reading a movie, get the post and post type object. */
        if ( 'edit_compatibuddy_report' == $cap || 'delete_compatibuddy_report' == $cap || 'read_compatibuddy_report' == $cap ) {
            $post = get_post( $args[0] );
            $post_type = get_post_type_object( $post->post_type );

            /* Set an empty array for the caps. */
            $caps = array();
        }

        /* If editing a movie, assign the required capability. */
        if ( 'edit_compatibuddy_report' == $cap ) {
            if ( $userId == $post->post_author )
                $caps[] = $post_type->cap->edit_posts;
            else
                $caps[] = $post_type->cap->edit_others_posts;
        }

        /* If deleting a movie, assign the required capability. */
        elseif ( 'delete_compatibuddy_report' == $cap ) {
            if ( $userId == $post->post_author )
                $caps[] = $post_type->cap->delete_posts;
            else
                $caps[] = $post_type->cap->delete_others_posts;
        }

        /* If reading a private movie, assign the required capability. */
        elseif ( 'read_compatibuddy_report' == $cap ) {

            if ( 'private' != $post->post_status )
                $caps[] = 'read';
            elseif ( $userId == $post->post_author )
                $caps[] = 'read';
            else
                $caps[] = $post_type->cap->read_private_posts;
        }
        /* Return the capabilities required by the user. */
        return $caps;
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

    private function compileReportPostTypeCapabilities($singular = 'post', $plural = 'posts') {
        return [
            'edit_post'      => "edit_$singular",
            'read_post'      => "read_$singular",
            'delete_post'        => "delete_$singular",
            'edit_posts'         => "edit_$plural",
            'edit_others_posts'  => "edit_others_$plural",
            'publish_posts'      => "publish_$plural",
            'read_private_posts'     => "read_private_$plural",
            'read'                   => "read",
            'delete_posts'           => "delete_$plural",
            'delete_private_posts'   => "delete_private_$plural",
            'delete_published_posts' => "delete_published_$plural",
            'delete_others_posts'    => "delete_others_$plural",
            'edit_private_posts'     => "edit_private_$plural",
            'edit_published_posts'   => "edit_published_$plural",
            'create_posts'           => "edit_$plural",
        ];
    }
}
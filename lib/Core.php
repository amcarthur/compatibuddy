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
     * Initializes member variables.
     */
    public function __construct() {
        $this->admin = new Admin();
        $this->setup();
    }

    /**
     * Registers WordPress hooks and calls its child setup methods.
     */
    public function setup() {
        register_activation_hook(Environment::getValue(EnvironmentVariable::PLUGIN_FILE), [$this, 'activate']);
        register_uninstall_hook(Environment::getValue(EnvironmentVariable::PLUGIN_FILE), [__CLASS__, 'uninstall']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        $this->admin->setup();
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
    }

    public static function uninstall() {
        Database::dropSchema();
        delete_option('compatibuddy_options');
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
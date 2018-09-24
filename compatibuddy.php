<?php
/**
Plugin Name: CompatiBuddy
Plugin URI: https://compatibuddy.com
Description: Detect possible compatibility issues between plugins and themes.
Version: 0.0.1
Author: Aidan McArthur
Author URI: https://mcarthur.io
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: compatibuddy
 */

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

if( !function_exists('compatibuddy_main') ) {
    /**
     * The entry point for CompatiBuddy.
     */
    function compatibuddy_main() {
        if (!defined('ABSPATH')) {
            return;
        }

        require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/utilities.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/filter-checker.php';

        register_activation_hook(__FILE__, 'compatibuddy_activate');
        add_action('admin_menu', 'compatibuddy_add_menu_items');
        add_filter('bulk_actions-users', 'register_bulk_actions', PHP_INT_MIN);
    }
}

function register_bulk_actions() {

}

if( !function_exists('compatibuddy_activate') ) {
    /**
     * Implements the activation hook for CompatiBuddy. Responsible for creating/updating schema and default options.
     */
    function compatibuddy_activate() {
        // TODO
    }
}

if( !function_exists('compatibuddy_add_menu_items') ) {
    /**
     * Responsible for adding the menu items for CompatiBuddy.
     */
    function compatibuddy_add_menu_items() {
        add_menu_page('CompatiBuddy', 'CompatiBuddy', 'activate_plugins', 'compatibuddy', 'compatibuddy_render_page');
    }
}

if( !function_exists('compatibuddy_render_page') ) {
    /**
     * Implements the page renderer for CompatiBuddy.
     */
    function compatibuddy_render_page() {
        require_once plugin_dir_path( __FILE__ ) . 'inc/dashboard-page.php';
    }
}

compatibuddy_main();

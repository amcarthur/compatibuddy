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

if (!defined('ABSPATH')) die("Forbidden");

require_once 'utilities.php';

function compatibuddy_get_add_filter_function_calls( $plugin, $file ) {

    $function_calls = compatibuddy_get_function_calls( $file, 'add_filter' );
    $formatted_add_filter_calls = array();

    foreach ( $function_calls as $call ) {
        if ( count( $call['args'] ) < 2 ) {
            continue;
        }

        $entry = array(
            'plugin' => $plugin,
            'file' => $file,
            'line' => $call['line'],
            'tag' => $call['args'][0],
            'function_to_add' => $call['args'][1]
        );

        if ( isset($call['args'][2]) ) {
            $entry['priority'] = $call['args'][2];
        }

        if ( isset($call['args'][3]) ) {
            $entry['accepted_args'] = $call['args'][3];
        }

        $formatted_add_filter_calls[$call['args'][0]][] = $entry;
    }

    return $formatted_add_filter_calls;
}

function compatibuddy_get_add_filters_from_plugin( $plugin ) {
    $php_files = compatibuddy_get_php_files_in_directory( $plugin['absolute_directory'] );
    $add_filter_calls = array();

    foreach ( $php_files as $file ) {
        $calls = compatibuddy_get_add_filter_function_calls( $plugin, $file );
        if ( ! $calls ) {
            continue;
        }

        foreach ( $calls as $tag => $call ) {
            $add_filter_calls[$tag][$plugin['id']]['files'][$file] = $call;
        }
    }

    return $add_filter_calls;
}

function compatibuddy_get_add_filter_tree() {
    $all_plugins = compatibuddy_get_plugins();
    $all_filters = array();
    foreach ( $all_plugins as $id => $plugin ) {
        $filters = compatibuddy_get_add_filters_from_plugin( $plugin );
        foreach ( $filters as $tag => $filter ) {
            if ( ! isset( $all_filters[$tag] ) ) {
                $all_filters[$tag] = $filter;
            } else {
                $all_filters[$tag] = array_merge($all_filters[$tag], $filter);
            }
        }
    }

    return $all_filters;
}

function compatibuddy_analyze_add_filter_tree( $add_filter_tree ) {
    // This function is purposefully unoptimized to make it easier to implement further analysis features in the future.

    $possible_incompatibilities = array();

    foreach ( $add_filter_tree as $tag => $add_filter_calls ) {

        if ( count ( $add_filter_calls ) <= 1 ) {
            continue;
        }

        // We have found a tag being filtered in more than one place.
        foreach ( $add_filter_calls as $plugin_id => $add_filter_call ) {
            $possible_incompatibilities[$tag][$plugin_id] = $add_filter_call;
        }
    }

    return $possible_incompatibilities;
}

function compatibuddy_analyze_filter_tree_with_subject ( $filter_tree, $subject ) {

    $possible_incompatibilities = compatibuddy_analyze_add_filter_tree( $filter_tree );

    $filters_overwritten = array();

    foreach ( $possible_incompatibilities as $tag => $modules ) {

        $priorities = array();

        foreach ( $modules as $module_id => $module ) {

            foreach ( $module['files'] as $file => $calls ) {

                foreach ( $calls as $call ) {

                    if ( $module_id === $subject['id'] ) {
                        $priorities['subject'] = $call;
                    }

                    if ( ! isset( $call['priority'] ) ) {
                        $call['priority'] = 10;
                    }

                    if ( $call['priority'] === 'PHP_INT_MAX' ) {
                        $call['priority'] = PHP_INT_MAX;
                    } else if ( $call['priority'] === 'PHP_INT_MIN' ) {
                        $call['priority'] = PHP_INT_MIN;
                    }

                    if ( ! is_numeric( $call['priority'] ) ) {
                        continue;
                    }

                    $call['priority'] = (int)$call['priority'];

                    if ( ! isset( $priorities['blame'] ) ) {
                        $priorities['blame'] = $call;
                    } else if ( $call['priority'] >= $priorities['blame']['priority'] ) {
                        $priorities['blame'] = $call;
                    }
                }
            }
        }

        if ( isset( $priorities['subject'])
            && $priorities['subject']['plugin']['id'] !== $priorities['blame']['plugin']['id']) {
            $filters_overwritten[$tag] = $priorities;
        }
    }

    return $filters_overwritten;
}
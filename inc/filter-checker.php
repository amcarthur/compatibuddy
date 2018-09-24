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

function compatibuddy_find_add_filters_from_plugin($plugin, &$filters ) {

    $php_files = compatibuddy_get_php_files_in_directory( $plugin['absolute_directory'] );
    $pattern = '/add_filter\\s*\\(\\s*(\'|")([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\-]+)\\1\\s*,\\s*(.*?\\)?),?\\s*([0-9]+|PHP_MAX_INT)?\\s*,?\\s*([0-9]+)?\\s*\\)/m';
    //$pattern = "/add_filter\s*\(\s*('|\")([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\-]+)\1\s*,\s*(.*?\)?),?\s*,?\s*([0-9]+|PHP_MAX_INT)?\s*,?\s*([0-9]+)?\s*\)/m";
    foreach ( $php_files as $file ) {
        $results = compatibuddy_search_file( $file, $pattern );

        foreach ( $results as $result ) {

            if ( ! isset( $result[2] ) || ! isset( $result[3] ) ) {
                continue;
            }

            $new_entry = array(
                'plugin' => $plugin,
                'file' => $file,
                'function_to_add' => $result[3]
            );

            if ( isset( $result[4] ) ) {
                $new_entry['priority'] = $result[4];
            }

            if ( isset( $result[5] ) ) {
                $new_entry['accepted_args'] = $result[5];
            }


            $filters[$result[2]][] = $new_entry;
        }
    }
}

function compatibuddy_analyze_filter_tree( $filter_tree, &$possible_incompatibilities ) {
    // This function is purposefully unoptimized to make it easier to implement further analysis features in the future.

    foreach ( $filter_tree as $tag => $filters ) {

        if ( count ( $filters ) === 1 ) {
            continue;
        }

        // We have found a tag being filtered in more than one place.
        foreach ( $filters as $filter ) {
            $possible_incompatibilities[$tag][] = $filter;
        }
    }
}

function compatibuddy_filter_priority_compare($a, $b) {

    if ( ! isset( $a['priority'] ) ) {
        return -1;
    }

    if ( ! isset( $b['priority'] ) ) {
        return 1;
    }

    if ( $a['priority'] === $b['priority'] ) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

function compatibuddy_analyze_filter_tree_with_subject ( $filter_tree, $subject, &$possible_incompatibilities ) {

    $possible_incompatibilities_ref = array();
    compatibuddy_analyze_filter_tree( $filter_tree, $possible_incompatibilities_ref );

    $tag_priorities = array();

    foreach ( $possible_incompatibilities_ref as $tag => $filters ) {

        foreach ( $filters as $filter ) {

            if ( $filter['plugin']['id'] === $subject['id'] ) {
                $tag_priorities[$tag]['subject'] = $filter;
            }

            if ( ! isset( $filter['priority'] ) ) {
                continue;
            }

            if ( ! isset( $tag_priorities[$tag]['current_highest_priority_filter'] ) ) {
                $tag_priorities[$tag]['current_highest_priority_filter'] = $filter;
            } else if ( $filter['priority'] >= $tag_priorities[$tag]['current_highest_priority_filter']['priority'] ) {
                $tag_priorities[$tag]['current_highest_priority_filter'] = $filter;
            }
        }

        if ( isset( $tag_priorities[$tag]['subject'])
            && $tag_priorities[$tag]['subject']['plugin']['id'] !== $tag_priorities[$tag]['current_highest_priority_filter']['plugin']['id']) {
            $possible_incompatibilities[$tag] = $tag_priorities[$tag];
        }
    }
}
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

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter;

function compatibuddy_get_plugins() {

    $all_plugins = get_plugins();
    $plugin_info = array();

    foreach ( $all_plugins as $plugin => $data ) {
        if ( ! validate_file( $plugin ) // $plugin must validate as file
            && '.php' == substr( $plugin, -4 ) // $plugin must end with '.php'
            && file_exists( WP_PLUGIN_DIR . '/' . $plugin ) // $plugin must exist)
            && ( strpos( $plugin , '/') || strpos( $plugin, '\\') )
        ) {

            $plugin_info[$plugin] = array(
                'id' => $plugin,
                'metadata' => $data,
                'absolute_directory' => dirname( WP_PLUGIN_DIR . '/' . $plugin )
            );
        }
    }

    return $plugin_info;
}

function compatibuddy_get_themes() {

    $all_themes = search_theme_directories();
    $theme_dirs = array();
    foreach ( $all_themes as $theme ) {
        $theme_dirs[] = array(
            'theme_file' => $theme['theme_file'],
            'theme_root' => $theme['theme_root'],
            'directory' => $theme['theme_root'] . '/' . dirname($theme['theme_file'])
        );
    }

    return $theme_dirs;
}

function compatibuddy_get_php_files_in_directory( $directory ) {

    $dir_iter = new RecursiveDirectoryIterator( $directory );
    $iter_iter = new RecursiveIteratorIterator( $dir_iter );
    $reg_iter = new RegexIterator( $iter_iter, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );

    $files = array();
    foreach ( $reg_iter as $file ) {
        if ( ! empty( $file ) ) {
            $files[] = $file[0];
        }
    }

    return $files;
}

function compatibuddy_deep_expression_search ( $node, $function_name, &$found_node ) {

    if ( ( $node instanceof FuncCall || $node instanceof StaticCall || $node instanceof MethodCall )
        && $node->name == $function_name ) {
        $found_node = $node;
        return true;
    }

    if ( isset( $node->expr ) ) {
       //return compatibuddy_deep_expression_search( $node->expr, $function_name, $found_node );
    }

    return false;
}

function compatibuddy_func_call_filter( $node, $function_name ) {
    $found_node = null;
    compatibuddy_deep_expression_search( $node, $function_name, $found_node );
    return $found_node;
}

function compatibuddy_get_function_calls( $file, $function_name ) {

    $contents = file_get_contents( $file );

    $prettyPrinter = new PrettyPrinter\Standard;

    $parser = (new ParserFactory)->create( ParserFactory::PREFER_PHP7 );
    $function_calls = array();

    try {
        $calls = array();
        $ast = $parser->parse($contents);
        $nodeFinder = new NodeFinder;
        $calls[] = $nodeFinder->find($ast, function( Node $node ) use( $function_name ) {
            return ($node instanceof FuncCall || $node instanceof StaticCall || $node instanceof MethodCall)
                && ($node->name instanceof PhpParser\Node\Name) && $node->name->toString() == $function_name;
        });

        foreach ( $calls as $expressions ) {
            foreach ( $expressions as $expression ) {
                $args = array();
                foreach ( $expression->args as $arg ) {
                    $args[] = $prettyPrinter->prettyPrint(array($arg));
                }

                $function_calls[] = array(
                    'args' => $args,
                    'line' => $expression->getLine(),
                    'name' => $expression->name
                );
            }
        }

    } catch (Error $error) {
        return false;
    }

    return $function_calls;
}

function compatibuddy_get_incompatibilities_for_plugin( $incompatibilities, $plugin ) {
    $plugin_incompatibilities = array();

    foreach ( $incompatibilities as $tag => $incompatibility ) {
        if ( $incompatibility['subject']['plugin']['id'] === $plugin['id']) {
            $incompatibility['tag'] = $tag;
            $plugin_incompatibilities[] = $incompatibility;
        }
    }

    return $plugin_incompatibilities;
}
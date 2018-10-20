<?php

namespace Compatibuddy;

use \PhpParser\Node;
use \PhpParser\Error;
use \PhpParser\NodeFinder;
use \PhpParser\ParserFactory;
use \PhpParser\PrettyPrinter;
use \PhpParser\Node\Expr\FuncCall;
use \PhpParser\Node\Expr\MethodCall;
use \PhpParser\Node\Expr\StaticCall;

/**
 * Defines common searcher functionality.
 * @package Compatibuddy\Scanners
 */
class Utilities {

    /**
     * Disables the constructor.
     */
    private function __construct() {}

    /**
     * Returns an array of valid WordPress plugins
     * @return array
     */
    public static function getPlugins() {
        $all_plugins = get_plugins();
        $plugin_info = [];

        foreach ($all_plugins as $plugin => $data) {
            if (!validate_file($plugin)
                && '.php' == substr($plugin, -4)
                && file_exists(WP_PLUGIN_DIR . '/' . $plugin)
                && (strpos($plugin , '/') || strpos($plugin, '\\'))
            ) {
                $plugin_info[$plugin] = [
                    'id' => $plugin,
                    'metadata' => $data,
                    'type' => 'plugin',
                    'absolute_directory' => dirname(WP_PLUGIN_DIR . '/' . $plugin)
                ];
            }
        }

        return $plugin_info;
    }

    /**
     * Returns an array of WordPress themes.
     * @return array
     */
    public static function getThemes() {
        $all_themes = wp_get_themes();
        $theme_info = [];

        foreach ($all_themes as $theme => $obj) {
            $theme_info[$theme] = [
                'id' => $theme,
                'metadata' => [
                    'Name' => $obj->get('Name'),
                    'ThemeURI' => $obj->get('ThemeURI'),
                    'Description' => $obj->get('Description'),
                    'Author' => $obj->get('Author'),
                    'AuthorURI' => $obj->get('AuthorURI'),
                    'Version' => $obj->get('Version'),
                    'Template' => $obj->get('Template'),
                    'Status' => $obj->get('Status'),
                    'Tags' => $obj->get('Tags'),
                    'TextDomain' => $obj->get('TextDomain'),
                    'DomainPath' => $obj->get('DomainPath')
                ],
                'type' => 'theme',
                'absolute_directory' => $obj->theme_root . '/' . $theme
            ];
        }

        return $theme_info;
    }

    /**
     * Returns an array of all WordPress plugins and themes combined.
     * @return array
     */
    public static function getModules() {
        return array_merge(self::getPlugins(), self::getThemes());
    }

    /**
     * Returns an array of PHP files in a specified directory.
     * @param string $directory
     * @return array
     */
    public static function getPhpFilesInDirectory($directory) {
        $dir_iter = new \RecursiveDirectoryIterator($directory);
        $iter_iter = new \RecursiveIteratorIterator($dir_iter);
        $reg_iter = new \RegexIterator($iter_iter, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $files = [];
        foreach ($reg_iter as $file) {
            if (isset($file[0])) {
                $files[] = $file[0];
            }
        }

        return $files;
    }

    /**
     * Returns an array of function calls in a specified file matching a specified function name.
     * Returns false if there is an error.
     * @param string $file
     * @param string $functionName
     * @return array|bool
     */
    public static function getFunctionCalls($file, $functionName) {
        $contents = file_get_contents($file);

        $prettyPrinter = new PrettyPrinter\Standard;

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $functionCalls = [];

        try {
            $expressionNodes = [];
            $ast = $parser->parse($contents);
            $nodeFinder = new NodeFinder();
            $expressionNodes[] = $nodeFinder->find($ast, function(Node $node) use($functionName) {
                return ($node instanceof FuncCall || $node instanceof StaticCall || $node instanceof MethodCall)
                    && ($node->name instanceof Node\Name) && $node->name->toString() == $functionName;
            });

            foreach ($expressionNodes as $expressionNode) {
                foreach ($expressionNode as $expression) {
                    $args = [];
                    foreach ($expression->args as $arg) {
                        $args[] = $prettyPrinter->prettyPrint([$arg]);
                    }

                    $functionCalls[] = [
                        'args' => $args,
                        'line' => $expression->getLine(),
                        'name' => $expression->name
                    ];
                }
            }

        } catch (Error $error) {
            return false;
        }

        return $functionCalls;
    }
}
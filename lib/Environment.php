<?php

namespace Compatibuddy;

/**
 * Environment variable keys
 * @package Compatibuddy
 */
class EnvironmentVariable {
    const PLUGIN_ROOT_DIRECTORY = 0;
    const PLUGIN_BASENAME = 1;
    const PLUGIN_FILE_NAME_NO_SUFFIX = 2;
    const PLUGIN_PAGE_ID = 3;
    const PLUGIN_BASE_URI = 4;
    const TEMPLATES_DIRECTORY = 5;
}

/**
 * Provides encapsulation for environment variables.
 * @package Compatibuddy
 */
class Environment {

    /**
     * @var array
     */
    private static $store;

    protected static $filesToInclude = [
        'lib/Router.php',
        'lib/Admin.php',
        'lib/vendor/autoload.php',
        'lib/utilities.php',
        'lib/filter-checker.php'
    ];

    /**
     * Disables the constructor.
     */
    private function __construct() {}

    /**
     * Initializes the variable store array.
     */
    public static function initialize($pluginFile) {
        $pluginRootDir = plugin_dir_path($pluginFile);
        $pluginBaseName = plugin_basename($pluginFile);
        $pluginFileNameNoSuffix = basename($pluginBaseName, '.php');

        self::$store = [
            EnvironmentVariable::PLUGIN_ROOT_DIRECTORY => $pluginRootDir,
            EnvironmentVariable::PLUGIN_BASENAME => $pluginBaseName,
            EnvironmentVariable::PLUGIN_FILE_NAME_NO_SUFFIX => $pluginFileNameNoSuffix,
            EnvironmentVariable::PLUGIN_PAGE_ID =>
                'toplevel_page_' . basename($pluginBaseName, '.php'),
            EnvironmentVariable::PLUGIN_BASE_URI => admin_url('admin.php?page=' . $pluginFileNameNoSuffix),
            EnvironmentVariable::TEMPLATES_DIRECTORY => $pluginRootDir . 'lib/Templates'
        ];
    }

    public static function includeFiles() {
        foreach (self::$filesToInclude as $file) {
            /** @noinspection PhpIncludeInspection */
            require_once(self::getValue(EnvironmentVariable::PLUGIN_ROOT_DIRECTORY) . $file);
        }
    }

    /**
     * Returns the value of the specified environment variable.
     * @param int $variable
     * @return mixed|null
     */
    public static function getValue($variable) {
        if (!array_key_exists($variable, self::$store)) {
            return null;
        }

        return self::$store[$variable];
    }
}
<?php

namespace Compatibuddy;

use Compatibuddy\Analyzers\DuplicateAddFilterAnalyzer;
use Compatibuddy\Analyzers\HigherPriorityAddFilterAnalyzer;
use Compatibuddy\Scanners\AddFilterScanner;
use Compatibuddy\Utilities;

class Routes {
    const DASHBOARD_PAGE = 0;
    const SETTINGS_PAGE = 1;
}

/**
 * Defines the routing and page rendering functionality.
 * @package Compatibuddy
 */
class Router {

    private $routes;
    private $templateEngine;

    /**
     * Initializes member variables and sets up routes.
     */
    public function __construct() {
        $this->routes  = [
            Routes::DASHBOARD_PAGE => [
                'action' => 'dashboard',
                'controller' => [$this, 'dashboardController'],
                'view' => 'Dashboard'
            ],
            Routes::SETTINGS_PAGE => [
                'action' => 'settings',
                'controller' => [$this, 'settingsController'],
                'view' => 'Settings'
            ]
        ];

        $this->templateEngine = new \League\Plates\Engine(
            Environment::getValue(EnvironmentVariable::TEMPLATES_DIRECTORY));
    }

    /**
     * Registers the WordPress hooks relevant to routing behavior.
     */
    public function setup() {
        add_filter('parent_file', [$this, 'filterParentFile']);
    }

    /**
     * Implements the parent_file filter
     * @param string $parentFile
     * @return string
     */
    public function filterParentFile($parentFile) {
        global $current_screen;

        if($current_screen->base !== Environment::getValue(EnvironmentVariable::PLUGIN_PAGE_ID)) {
            return $parentFile;
        }

        global $submenu_file;

        $route = $this->parseRoute();
        if (array_key_exists($route, $this->routes)) {
            $submenu_file = htmlentities(Router::buildUri($route));
        }

        return $parentFile;
    }

    /**
     * If a route is specified, returns the map for that route, otherwise returns the entire route map.
     * @param int $route
     * @return array|null
     */
    public function getRouteMap($route = null) {
        if ($route === null) {
            return $this->routes;
        }

        if (isset($this->routes[$route])) {
            return $this->routes[$route];
        }

        return null;
    }

    /**
     * Attempts to parse a route from an action. If no action is specified, it will look for the action in the request.
     * If no route is suitable for the action, the dashboard is used.
     * @param string $action
     * @return int|null
     */
    public function parseRoute($action = '') {
        if ($action === '') {
            if (isset($_GET['action'])) {
                $action = strtolower(trim($_GET['action']));
            } else {
                return 'dashboard';
            }
        }

        foreach ($this->routes as $route => $map) {
            if ($map['action'] === $action) {
                return $route;
            }
        }

        return 'dashboard';
    }

    /**
     * Builds the URI for a specified route.
     * @param int $route
     * @param array $additionalQueryArguments
     * @return null|string
     */
    public function buildUri($route, $additionalQueryArguments = []) {
        if (!isset($this->routes[$route])) {
            return null;
        }

        $baseUri = Environment::getValue(EnvironmentVariable::PLUGIN_BASE_URI);

        $queryArgs = [
            'action' => $this->routes[$route]['action']
        ];

        if (!empty($additionalQueryArguments)) {
            $queryArgs = array_merge($queryArgs, $additionalQueryArguments);
        }

         return add_query_arg($queryArgs, $baseUri);
    }

    /**
     * Implements the logic for a route, mapping it to its controller, and possibly its view.
     * Returns false if the route was not found, otherwise returns true.
     * @param int $route
     * @return bool
     */
    public function route($route) {
        if (!isset($this->routes[$route])) {
            return false;
        }

        $ret = null;
        if (!isset($this->routes[$route]['view'])) {
            $ret = call_user_func($this->routes[$route]['controller']);
        } else {
            $ret = call_user_func($this->routes[$route]['controller'], $this->routes[$route]['view']);
        }

        if ($ret === null) {
            return true;
        }

        if (is_string($ret)) {
            echo $ret;
        }

        return true;
    }

    /**
     * The dashboard page controller.
     * @param string $view
     * @return string
     */
    public function dashboardController($view) {
        $plugins = Utilities::getPlugins();
        $pluginKeys = array_keys($plugins);
        $addFilterScanner = new AddFilterScanner();
        $addFilterScanResult = $addFilterScanner->scan($plugins);
        //$duplicateAddFilterAnalyzer = new DuplicateAddFilterAnalyzer();
        //$duplicateAddFilterAnalysis = $duplicateAddFilterAnalyzer->analyze($addFilterScanResult, $plugins['compatibuddy/compatibuddy.php']);
        $higherPriorityAddFilterAnalyzer = new HigherPriorityAddFilterAnalyzer();
        $higherPriorityAddFilterAnalysis = $higherPriorityAddFilterAnalyzer->analyze($addFilterScanResult, $plugins['compatibuddy/compatibuddy.php']);

        return $this->templateEngine->render($view, [
            'title' => __('Dashboard', 'compatibuddy'),
            'duplicateAddFilters' => $higherPriorityAddFilterAnalysis
        ]);
    }

    /**
     * The settings page controller.
     * @param string $view
     * @return string
     */
    public function settingsController($view) {
        $x = "test";
        return $this->templateEngine->render($view, ['name' => $x]);
    }
}
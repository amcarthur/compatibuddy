<?php

namespace Compatibuddy;

/**
 * Defines the WordPress admin page behavior.
 * @package Compatibuddy
 */
class Admin {

    /**
     * @var Router
     */
    private $router;

    /**
     * Initializes the member variables.
     * @param Router $router
     */
    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * Registers the WordPress hooks relevant to the admin page.
     */
    public function setup() {
        add_action('admin_menu', [$this, 'addMenuItems']);
    }

    public function addMenuItems() {
        add_menu_page(
            'Compatibuddy',
            'Compatibuddy',
            'activate_plugins',
            'compatibuddy',
            [$this, 'menuPageAction']
        );

        global $submenu;

        $submenu['compatibuddy'][] = [__('Dashboard', 'compatibuddy'),
            'activate_plugins', $this->router->buildUri(Routes::DASHBOARD_PAGE)];
    }

    public function menuPageAction() {
        $this->router->route(Routes::DASHBOARD_PAGE);
    }
}
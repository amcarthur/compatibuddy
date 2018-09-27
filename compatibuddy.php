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

if (!defined('ABSPATH')) die("Forbidden");

require_once('lib/Environment.php');
require_once('lib/Core.php');

use Compatibuddy\Environment;
use Compatibuddy\Core;

Environment::initialize();
Environment::includeFiles();
$core = new Core();
$core->setup();

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

use Compatibuddy\Analyzers\AddFilterAnalyzer;
use Compatibuddy\Scanners\AddFilterScanner;

class Reports {
    public function __construct() {
    }

    public function setup() {
        add_shortcode('compatibuddy_filter_priorities', [$this, 'renderFilterPriorities']);
        add_shortcode('compatibuddy_module', [$this, 'renderModule']);
        //add_shortcode('compatibuddy_module_visual', [$this, 'renderModuleVisual']);
    }

    public function renderFilterPriorities($atts) {
        $a = shortcode_atts([
            'tag' => false,
            'visual' => 'bar',
        ], $atts);

        if ($a['tag'] === false) {
            return '<p>No tag specified.</p>';
        }

        $visual = sanitize_text_field($a['visual']);

        if (!in_array($visual, ['bar', 'line', 'pie'])) {
            return '<p>Invalid visual specified.</p>';
        }

        $modules = Utilities::getModules();
        $filterScanner = new AddFilterScanner();
        $analyzer = new AddFilterAnalyzer();
        $analysis = $analyzer->analyze($filterScanner->scan($modules, true));

        if (empty($analysis)) {
            return '<p>Nothing found for the tag "' . esc_html($a['tag']) . '".</p>';
        }

        $tag = sanitize_text_field($a['tag']);
        if (!isset($analysis[$tag])) {
            if (!strpos($tag, '\'')) {
                $tag = '\'' . $tag . '\'';
                if (!isset($analysis[$tag])) {
                    return '<p>Nothing found for the tag "' . esc_html($a['tag']) . '".</p>';
                }
            } else {
                return '<p>Nothing found for the tag "' . esc_html($a['tag']) . '".</p>';
            }
        }

        $calls = $analysis[$tag];

        $canvasId = 'compatibuddy-filter-report-' . uniqid();

        return '
        <canvas id="' . esc_attr($canvasId) . '"></canvas>
        <script type="text/javascript">                           
            var calls = ' . json_encode($calls) . ';
            var visual = "' . esc_js($visual) . '";
            var datasets = Compatibuddy.compileFilterPriorityDatasets(calls);
            Compatibuddy.createChart(' . json_encode($canvasId) . ', visual, datasets, ' . json_encode(__('Priority', 'compatibuddy')) . ', ' . json_encode($tag) . ');
            
        </script>
';
    }

    public function renderModule($atts) {
        $a = shortcode_atts([
            'module' => false,
            'type' => 'plugin',
            'visual' => 'bar',
        ], $atts);

        if ($a['module'] === false) {
            return '<p>No module specified.</p>';
        }

        $module = sanitize_text_field($a['module']);
        $type = sanitize_text_field($a['type']);
        $visual = sanitize_text_field($a['visual']);

        if (!in_array($type, ['plugin', 'theme'])) {
            return '<p>Invalid module type specified.</p>';
        }

        if (!in_array($visual, ['bar', 'line', 'pie'])) {
            return '<p>Invalid visual specified.</p>';
        }

        $plugins = Utilities::getPlugins();
        $themes = Utilities::getThemes();
        $modules = array_merge($plugins, $themes);

        if ($type === 'plugin') {
            if (!isset($plugins[$module])) {
                return '<p>Plugin not found.</p>';
            }

            $subject = $plugins[$module];
        } else {
            if (!isset($themes[$module])) {
                return '<p>Theme not found.</p>';
            }

            $subject = $themes[$module];
        }

        $filterScanner = new AddFilterScanner();
        $analyzer = new AddFilterAnalyzer();
        $analysis = $analyzer->analyze($filterScanner->scan([$subject], true), $subject);

        if (empty($analysis)) {
            return '<p>Nothing found for the module "' . esc_html($module) . '".</p>';
        }

        $canvasId = 'compatibuddy-filter-report-' . uniqid();

        return '
        <canvas id="' . esc_attr($canvasId) . '"></canvas>
        <script type="text/javascript">                           
            var calls = ' . json_encode($analysis) . ';
            var visual = "' . esc_js($visual) . '";
            var datasets = Compatibuddy.compileModuleDatasets(calls);
            Compatibuddy.createChart(' . json_encode($canvasId) . ', visual, datasets, ' . json_encode(__('Priority', 'compatibuddy')) . ', ' . json_encode($subject['metadata']['Name']) . ');
            
        </script>
';
    }
}
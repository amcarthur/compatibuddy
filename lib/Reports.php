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
        add_shortcode('compatibuddy_filter', [$this, 'renderFilter']);
        add_shortcode('compatibuddy_all_filters', [$this, 'renderAllFilters']);
        add_shortcode('compatibuddy_module', [$this, 'renderModule']);
        add_shortcode('compatibuddy_all_modules', [$this, 'renderAllModule']);
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
            if (!strpos($tag, '\'') && !strpos($tag, '"')) {

                $singleQuotedTag = '\'' . $tag . '\'';

                if (!isset($analysis[$singleQuotedTag])) {

                    $doubleQuotedTag = '"' . $tag . '"';

                    if (!isset($analysis[$doubleQuotedTag])) {
                        return '<p>Nothing found for the tag "' . esc_html($a['tag']) . '".</p>';
                    }

                    $tag = $doubleQuotedTag;
                } else {
                    $tag = $singleQuotedTag;
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
            Compatibuddy.createFilterPrioritiesChart(' . json_encode($canvasId) . ', visual, calls, ' . json_encode(__('Priority', 'compatibuddy')) . ', ' . json_encode($tag) . ');
            
        </script>
';
    }

    public function renderFilter($atts) {

    }

    public function renderAllFilters($atts) {

    }

    public function renderModule($atts) {

    }

    public function renderAllModules($atts) {

    }
}
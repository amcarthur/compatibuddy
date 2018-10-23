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

use Compatibuddy\Analyzers\DuplicateAddFilterAnalyzer;
use Compatibuddy\Scanners\AddFilterScanner;

class Reports {
    public function __construct() {
    }

    public function setup() {
        add_shortcode('compatibuddy_filter_report', [$this, 'renderFilterReport']);
    }

    public function renderFilterReport($atts) {
        $a = shortcode_atts([
            'tag' => false,
            'visual' => 'bar',
        ], $atts);

        if ($a['tag'] === false) {
            return '<p>No tag specified.</p>';
        }

        $modules = Utilities::getModules();
        $filterScanner = new AddFilterScanner();
        $analyzer = new DuplicateAddFilterAnalyzer();
        $analysis = $analyzer->analyze($filterScanner->scan($modules, true));

        if (empty($analysis) || !isset($analysis[$a['tag']])) {
            return '<p>Nothing found for the tag "' . esc_html($a['tag']) . '".</p>';
        }

        $canvasId = 'compatibuddy-filter-report-' . uniqid();

        ob_start(); ?>
        <canvas id="<?php echo $canvasId ?>"></canvas>
        <script type="text/javascript">
            var ctx = document.getElementById('<?php echo $canvasId ?>').getContext('2d');
            var chart = new Chart(ctx, {
                // The type of chart we want to create
                type: 'line',

                // The data for our dataset
                data: {
                    labels: ["January", "February", "March", "April", "May", "June", "July"],
                    datasets: [{
                        label: "My First dataset",
                        backgroundColor: 'rgb(255, 99, 132)',
                        borderColor: 'rgb(255, 99, 132)',
                        data: [0, 10, 5, 2, 20, 30, 45]
                    }]
                },

                // Configuration options go here
                options: {}
            });
        </script>
<?php
        return ob_end_clean();
    }
}
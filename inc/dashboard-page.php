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

$all_plugins = compatibuddy_get_plugins();

if ( isset( $_POST['compatibuddy-detect-issues-all'] ) ) {

    $add_filter_tree = compatibuddy_get_add_filter_tree();
    $possible_incompatibilities = compatibuddy_analyze_filter_tree_with_subject( $add_filter_tree, $all_plugins['compatibuddy/compatibuddy.php'] );
    print('<pre>' . print_r($possible_incompatibilities, true) . '</pre>');
    die();
    /*$possible_incompatibilities = array();
    compatibuddy_analyze_filter_tree_with_subject($filters, $all_plugins['compatibuddy/compatibuddy.php'], $possible_incompatibilities);
    $plugin_incompatibilities['compatibuddy/compatibuddy.php'] = compatibuddy_get_incompatibilities_for_plugin($possible_incompatibilities, $all_plugins['compatibuddy/compatibuddy.php']);
    print('<pre>' . print_r($plugin_incompatibilities, true) . '</pre>');
    die();*/

} else if ( isset( $_POST['compatibuddy-detect-issues-single'] ) ) {

}

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php
        echo esc_html( __( 'CompatiBuddy', 'compatibuddy' ) );
    ?></h1>
    <hr class="wp-header-end">
    <form id="compatibuddy-detect-issues-all" method="post" action="">
        <input type="submit" name="compatibuddy-detect-issues-all" class="button button-primary" value="<?php
            echo esc_attr( __( 'Detect issues with all plugins', 'compatibuddy' ) );
        ?>" />
    </form>
    <form id="compatibuddy-detect-issues-single" method="post" action="">
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th>Problem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $all_plugins as $plugin ) { ?>
                <tr>
                    <td><?php echo esc_html( __( $plugin['metadata']['Name'], 'compatibuddy' ) ) ?></td>
                    <td><?php echo esc_html( __( isset($possible_incompatibilities[$plugin['id']]) ? $plugin_incompatibilities[$plugin['id']][0][0]['tag'] : 'N/A', 'compatibuddy' ) ) ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>

<?php

namespace Compatibuddy\Tables;

use Compatibuddy\Analyzers\HigherPriorityAddFilterAnalyzer;
use Compatibuddy\Scanners\AddFilterScanner;
use Compatibuddy\Utilities;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class DuplicateAddFilterTable extends \WP_List_Table {
    function __construct() {
        parent::__construct( array(
            'singular'=> 'compatibuddy_duplicate_filter',
            'plural' => 'compatibuddy_duplicate_filters',
            'ajax'   => false
        ) );
    }

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            echo"Hello, I'm before the table";
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            echo"Hi, I'm after the table";
        }
    }

    function get_columns() {
        return $columns= array(
            'cb' => '<input type="checkbox" />',
            'col_module_name'=>__('Module'),
            'col_module_version'=>__('Version'),
            'col_file'=>__('File'),
            'col_function_to_add'=>__('Function To Add'),
            'col_priority'=>__('Priority'),
            'col_accepted_args'=>__('Accepted Arguments')
        );
    }

    public function get_sortable_columns() {
        return $sortable = array(
            'col_module_name'=> 'module_id',
            'col_module_version'=> 'module_version',
            'col_file'=> 'file',
            'col_function_to_add'=> 'function_to_add',
            'col_priority'=> 'priority',
            'col_accepted_args'=> 'accepted_args'
        );
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $plugins = Utilities::getPlugins();
        $addFilterScanner = new AddFilterScanner();
        $addFilterScanResult = $addFilterScanner->scan($plugins);
        //$duplicateAddFilterAnalyzer = new DuplicateAddFilterAnalyzer();
        //$duplicateAddFilterAnalysis = $duplicateAddFilterAnalyzer->analyze($addFilterScanResult, $plugins['compatibuddy/compatibuddy.php']);
        $higherPriorityAddFilterAnalyzer = new HigherPriorityAddFilterAnalyzer();
        $higherPriorityAddFilterAnalysis = $higherPriorityAddFilterAnalyzer->analyze($addFilterScanResult, $plugins['compatibuddy/compatibuddy.php']);

        $this->items = $higherPriorityAddFilterAnalysis;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'col_module_name':
                return $item['module']['metadata']['Name'];
            case 'col_module_version':
                return $item['module']['metadata']['Version'];
            case 'col_file':
            case 'col_function_to_add':
            case 'col_priority':
            case 'col_accepted_args':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('Delete')
        );
        return $actions;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
        );
    }
}
<?php

namespace Compatibuddy\Analyzers;

use Compatibuddy\Utilities;

require_once('AnalyzerInterface.php');

class FilterCallsAnalyzer implements AnalyzerInterface {

    private $scanResults;
    private $includedTags;
    private $includedModules;

    public function __construct($scanResults, $includedTags = null, $includedModules = null) {
        $this->scanResults = $scanResults;
        $this->includedTags = $includedTags;
        $this->includedModules = $includedModules;
    }

    public function analyze() {
        //var_dump($this->includedModules);die();
        return Utilities::mapScanResultsToCallTree($this->scanResults, ['add_filter', 'remove_filter', 'remove_all_filters'], $this->includedTags, $this->includedModules);
    }
}
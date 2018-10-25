<?php

namespace Compatibuddy\Analyzers;

use Compatibuddy\Utilities;

require_once('AnalyzerInterface.php');

class AllCallsAnalyzer implements AnalyzerInterface {

    private $scanResults;

    public function __construct($scanResults) {
        $this->scanResults = $scanResults;
    }

    public function analyze() {
        return Utilities::mapScanResultsToCallTree($this->scanResults);
    }
}
<?php

namespace Compatibuddy\Analyzers;

use Compatibuddy\Utilities;

require_once('AnalyzerInterface.php');

class ActionCallsAnalyzer implements AnalyzerInterface {

    private $scanResults;
    private $includedTags;
    private $includedModules;

    public function __construct($scanResults, $includedTags = null, $includedModules = null) {
        $this->scanResults = $scanResults;
        $this->includedTags = $includedTags;
        $this->includedModules = $includedModules;
    }

    public function analyze() {
        return Utilities::mapScanResultsToCallTree($this->scanResults, ['add_action', 'remove_action', 'remove_all_actions'], $this->includedTags, $this->includedModules);
    }
}
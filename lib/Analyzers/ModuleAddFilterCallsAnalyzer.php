<?php

namespace Compatibuddy\Analyzers;

use Compatibuddy\Utilities;

require_once('AnalyzerInterface.php');

class ModuleAddFilterCallsAnalyzer implements AnalyzerInterface {

    private $scanResults;
    private $includedTags;
    private $includedModules;

    public function __construct($scanResults, $includedModules, $includedTags = null) {
        $this->scanResults = $scanResults;
        $this->includedTags = $includedTags;
        $this->includedModules = $includedModules;
    }

    public function analyze() {
        return Utilities::mapScanResultsToModuleCallTree($this->scanResults, ['add_filter'], $this->includedModules, $this->includedTags);
    }
}
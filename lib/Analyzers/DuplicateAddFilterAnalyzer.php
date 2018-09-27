<?php

namespace Compatibuddy\Analyzers;

require_once('AnalyzerInterface.php');

class DuplicateAddFilterAnalyzer implements AnalyzerInterface {

    public function analyze($scanResult, $subject = null) {
        $duplicateFilters = [];

        foreach ($scanResult as $addFilterTag => $modules) {
            if (count($modules) <= 1) {
                continue;
            }

            foreach ($modules as $moduleId => $calls) {
                $duplicateFilters[$addFilterTag][$moduleId] = $calls;
            }
        }

        if ($subject === null) {
            return $duplicateFilters;
        }

        $filteredFilters = array();

        foreach (array_keys($duplicateFilters) as $key) {
            if (array_key_exists($subject['id'], $duplicateFilters[$key])) {
                $filteredFilters[$key] = $duplicateFilters[$key];
            }
        }

        return $filteredFilters;
    }
}
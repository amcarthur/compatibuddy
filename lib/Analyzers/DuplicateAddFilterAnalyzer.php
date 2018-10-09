<?php

namespace Compatibuddy\Analyzers;

require_once('AnalyzerInterface.php');

class DuplicateAddFilterAnalyzer implements AnalyzerInterface {

    public function analyze($scanResults, $subject = null) {
        $flatAddFilterCalls = [];

        foreach ($scanResults as $moduleId => $module) {
            foreach ($module['calls'] as $calls) {

                foreach ($calls as $tag => $call) {
                    if (isset($flatAddFilterCalls[$tag])) {
                        $flatAddFilterCalls[$tag] = array_merge($flatAddFilterCalls[$tag], $call);
                    } else {
                        $flatAddFilterCalls[$tag] = $call;
                    }
                }
            }
        }

        $duplicateAddFilterCalls = array_filter($flatAddFilterCalls, function($calls) use($subject) {
            if (count($calls) < 2) {
                return false;
            }

            if ($subject !== null) {
                $moduleFound = false;
                foreach ($calls as $call) {
                    if ($call['module']['id'] === $subject['id']) {
                        $moduleFound = true;
                    }
                }

                return $moduleFound;
            }

            return true;
        });

        return $duplicateAddFilterCalls;
    }
}
<?php

namespace Compatibuddy\Analyzers;

require_once('AnalyzerInterface.php');

class PrioritizedFilterCallsAnalyzer extends FilterCallsAnalyzer {

    public function __construct($scanResults, $includedTags = null, $includedModules = null) {
        parent::__construct($scanResults, $includedTags, $includedModules);
    }

    public function analyze() {
        $prioritizedAddFilterCalls = [];
        $addFilterCalls = parent::analyze();

        foreach ($addFilterCalls as $tag => $calls) {

            usort($calls, function($a, $b) {
                $aHasPriority = self::tryParsePriority($a);
                $bHasPriority = self::tryParsePriority($b);

                if (!$aHasPriority && !$bHasPriority) {
                    return 0;
                }

                if (!$aHasPriority) {
                    return 1;
                }

                if (!$bHasPriority) {
                    return -1;
                }

                if ($a['priority'] === $b['priority']) {
                    return 0;
                }

                return ($a['priority'] > $b['priority']) ? -1 : 1;
            });

            $prioritizedAddFilterCalls[$tag] = $calls;
        }

        return $prioritizedAddFilterCalls;
    }

    private function tryParsePriority(&$call) {
        if (!isset($call['priority'])) {
            $call['priority'] = 10;
            return true;
        }

        if ($call['priority'] === 'PHP_INT_MAX') {
            $call['priority'] = PHP_INT_MAX;
            return true;
        }

        if ($call['priority'] === 'PHP_INT_MIN') {
            $call['priority'] = PHP_INT_MIN;
            return true;
        }

        if (!is_numeric( $call['priority'])) {
            return false;
        }

        $call['priority'] = (int)$call['priority'];
        return true;
    }
}
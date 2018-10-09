<?php

namespace Compatibuddy\Analyzers;

require_once('AnalyzerInterface.php');

class HigherPriorityAddFilterAnalyzer extends DuplicateAddFilterAnalyzer {

    public function analyze($scanResults, $subject = null) {
        $prioritizedFilters = [];

        if ($subject === null) {
            return $prioritizedFilters;
        }

        $duplicateAddFilters = parent::analyze($scanResults, $subject);

        foreach ($duplicateAddFilters as $tag => $calls) {

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

            foreach ($calls as &$call) {
                if ($call['module']['id'] === $subject['id']) {
                    $call['subject'] = true;
                }
            }

            $prioritizedFilters[$tag] = $calls;
        }

        return $prioritizedFilters;
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
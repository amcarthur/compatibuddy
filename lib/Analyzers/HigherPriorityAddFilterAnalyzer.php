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
            $currentPrioritizedFilters = [
                $tag => []
            ];

            foreach ($calls as $call) {
                if ($call['module']['id'] === $subject['id']) {
                    if (!$this->tryParsePriority($call)) {
                        continue;
                    }

                    $currentPrioritizedFilters[$tag][] = $call;
                }
            }

            if (empty($currentPrioritizedFilters[$tag])) {
                continue;
            }

            foreach ($calls as $call) {
                if ($call['module']['id'] === $subject['id']) {
                    continue;
                }

                if (!$this->tryParsePriority($call)) {
                    continue;
                }


                foreach ($currentPrioritizedFilters[$tag] as &$subjectCall) {
                    if ($call['priority'] > $subjectCall['priority']) {
                        $subjectCall['conflicts'][] = $call;
                    }
                }
            }

            foreach ($currentPrioritizedFilters[$tag] as $currentFilterCalls) {
                if (isset($currentFilterCalls['conflicts'])) {
                    $prioritizedFilters[$tag][] = $currentFilterCalls;
                }
            }
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
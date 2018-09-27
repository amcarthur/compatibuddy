<?php

namespace Compatibuddy\Analyzers;

require_once('AnalyzerInterface.php');

class HigherPriorityAddFilterAnalyzer implements AnalyzerInterface {

    public function analyze($scanResult, $subject) {
        $filtersOverwritten = [];

        foreach ($scanResult as $addFilterTag => $modules) {

            $priorities = [];

            foreach ($modules as $moduleId => $module) {

                foreach ($module['files'] as $file => $calls) {

                    foreach ($calls as $call) {

                        if ($moduleId === $subject['id']) {
                            $priorities['subject'] = $call;
                        }

                        if (!isset($call['priority'])) {
                            $call['priority'] = 10;
                        }

                        if ($call['priority'] === 'PHP_INT_MAX') {
                            $call['priority'] = PHP_INT_MAX;
                        } else if ($call['priority'] === 'PHP_INT_MIN') {
                            $call['priority'] = PHP_INT_MIN;
                        }

                        if (!is_numeric( $call['priority'])) {
                            continue;
                        }

                        $call['priority'] = (int)$call['priority'];

                        if (!isset( $priorities['blame'])) {
                            $priorities['blame'] = $call;
                        } else if ($call['priority'] > $priorities['blame']['priority']) {
                            $priorities['blame'] = $call;
                        }
                    }
                }
            }

            if (isset($priorities['subject'])
                && isset ($priorities['blame'])
                && $priorities['subject']['plugin']['id'] !== $priorities['blame']['plugin']['id']) {
                $filtersOverwritten[$addFilterTag] = $priorities;
            }
        }

        return $filtersOverwritten;
    }
}
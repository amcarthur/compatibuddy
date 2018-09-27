<?php

namespace Compatibuddy\Scanners;

require_once('ScannerInterface.php');

use Compatibuddy\Utilities;

class AddFilterScanner implements ScannerInterface {

    private $filters;

    public function __construct() {
        $this->filters = [];
    }

    public function scan($plugins) {
        foreach ($plugins as $plugin) {
            $phpFiles = Utilities::getPhpFilesInDirectory($plugin['absolute_directory']);

            foreach ($phpFiles as $file) {
                $calls = $this->getAddFilterFunctionCalls($plugin, $file);
                if (!$calls) {
                    continue;
                }

                foreach ($calls as $tag => $call) {
                    if (!isset($this->filters[$tag])) {
                        $this->filters[$tag] = [];
                    }

                    if (!isset($this->filters[$tag][$plugin['id']])) {
                        $this->filters[$tag][$plugin['id']] = [
                            'files' => []
                        ];
                    }

                    if (!isset($this->filters[$tag][$plugin['id']]['files'][$file])) {
                        $this->filters[$tag][$plugin['id']]['files'][$file] = [];
                    }

                    $this->filters[$tag][$plugin['id']]['files'][$file] = array_merge($this->filters[$tag][$plugin['id']]['files'][$file], $call);
                }
            }
        }

        return $this->filters;
    }

    private function getAddFilterFunctionCalls($plugin, $file) {
        $formattedAddFilterCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'add_filter');
        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 2) {
                continue;
            }

            $entry = [
                'plugin' => $plugin,
                'file' => $file,
                'line' => $call['line'],
                'tag' => $call['args'][0],
                'function_to_add' => $call['args'][1]
            ];

            if (isset($call['args'][2])) {
                $entry['priority'] = $call['args'][2];
            }

            if (isset($call['args'][3])) {
                $entry['accepted_args'] = $call['args'][3];
            }

            $formattedAddFilterCalls[$call['args'][0]][] = $entry;
        }

        return $formattedAddFilterCalls;
    }
}
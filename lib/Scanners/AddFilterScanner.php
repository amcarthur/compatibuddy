<?php

namespace Compatibuddy\Scanners;

require_once('ScannerInterface.php');

use Compatibuddy\Utilities;
use Compatibuddy\Caches\AddFilterCache;

class AddFilterScanner implements ScannerInterface {

    private $cache;

    public function __construct() {
        $this->cache = new AddFilterCache();
        $this->cache->fetch();
    }

    public function scan($modules) {
        $filters = [];

        foreach ($modules as $module) {
            $moduleCalls = null;
            $cachedModuleCalls = $this->cache->get($module['id']);
            $usingCached = false;

            if ($cachedModuleCalls !== null) {
                $moduleCalls['calls'] = $cachedModuleCalls;
                $usingCached = true;
            } else {
                $moduleCalls['calls'] = $this->getModuleAddFilterFunctionCalls($module);
            }

            $moduleCalls['module'] = $module;

            foreach ($moduleCalls['calls'] as $tag => $call) {
                $filters[$module['id']][$tag] = $call;
            }

            if (!$usingCached) {
                $this->cache->set($module['id'], $moduleCalls);
            }
        }

        $this->cache->commit();

        return $filters;
    }

    private function getModuleAddFilterFunctionCalls($module) {
        $phpFiles = Utilities::getPhpFilesInDirectory($module['absolute_directory']);

        $moduleCalls = [];

        foreach ($phpFiles as $file) {
            $calls = $this->getAddFilterFunctionCalls($module, $file);
            if (!$calls) {
                continue;
            }

            $moduleCalls = array_merge($moduleCalls, $calls);
        }

        return $moduleCalls;
    }

    private function getAddFilterFunctionCalls($module, $file) {
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
                'module' => $module,
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
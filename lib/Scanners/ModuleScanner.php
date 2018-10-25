<?php

namespace Compatibuddy\Scanners;

require_once('ScannerInterface.php');

use Compatibuddy\Utilities;
use Compatibuddy\Caches\ModuleCache;

class ModuleScanner implements ScannerInterface {

    private $cache;

    public function __construct() {
        $this->cache = new ModuleCache();
        $this->cache->fetch();
    }

    public function scan($modules, $onlyUseCached = false) {
        $scanResults = [];

        foreach ($modules as $module) {
            $moduleCalls = null;
            $cachedModuleCalls = $this->cache->get($module['id']);
            $usingCached = false;

            if ($cachedModuleCalls !== null) {
                $moduleCalls = $cachedModuleCalls;
                $usingCached = true;
                $scanResults[$module['id']]['module'] = $cachedModuleCalls['module'];
                $scanResults[$module['id']]['moduleVersion'] = $cachedModuleCalls['moduleVersion'];
                $scanResults[$module['id']]['moduleType'] = $cachedModuleCalls['moduleType'];
            } else if (!$onlyUseCached) {
                $moduleCalls = $this->getModuleFunctionCalls($module);
            } else {
                continue;
            }

            if (isset($moduleCalls['add_filter_calls'])) {
                foreach ($moduleCalls['add_filter_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['add_filter_calls'][$tag] = $call;
                }
            }

            if (isset($moduleCalls['remove_filter_calls'])) {
                foreach ($moduleCalls['remove_filter_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['remove_filter_calls'][$tag] = $call;
                }
            }

            if (isset($moduleCalls['remove_all_filters_calls'])) {
                foreach ($moduleCalls['remove_all_filters_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['remove_all_filters_calls'][$tag] = $call;
                }
            }

            if (isset($moduleCalls['add_action_calls'])) {
                foreach ($moduleCalls['add_action_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['add_action_calls'][$tag] = $call;
                }
            }

            if (isset($moduleCalls['remove_action_calls'])) {
                foreach ($moduleCalls['remove_action_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['remove_action_calls'][$tag] = $call;
                }
            }

            if (isset($moduleCalls['remove_all_actions_calls'])) {
                foreach ($moduleCalls['remove_all_actions_calls'] as $tag => $call) {
                    $scanResults[$module['id']]['remove_all_actions_calls'][$tag] = $call;
                }
            }

            if (!$usingCached) {
                $moduleCalls['module'] = $module;
                $moduleCalls['moduleVersion'] = $module['metadata']['Version'];
                $moduleCalls['moduleType'] = $module['type'];
                $this->cache->set($module['id'], $moduleCalls);
            }
        }

        $this->cache->commit();
        return $scanResults;
    }

    private function getModuleFunctionCalls($module) {
        $phpFiles = Utilities::getPhpFilesInDirectory($module['absolute_directory']);

        $moduleCalls = [];

        foreach ($phpFiles as $file) {
            $addFilterCalls = $this->getAddFilterFunctionCalls($module, $file);
            $removeFilterCalls = $this->getRemoveFilterFunctionCalls($module, $file);
            $removeAllFiltersCalls = $this->getRemoveAllFiltersFunctionCalls($module, $file);
            $addActionCalls = $this->getAddActionFunctionCalls($module, $file);
            $removeActionCalls = $this->getRemoveActionFunctionCalls($module, $file);
            $removeAllActionsCalls = $this->getRemoveAllActionsFunctionCalls($module, $file);

            if ($addFilterCalls) {
                $moduleCalls['add_filter_calls'][] = $addFilterCalls;
            }

            if ($removeFilterCalls) {
                $moduleCalls['remove_filter_calls'][] = $removeFilterCalls;
            }

            if ($removeAllFiltersCalls) {
                $moduleCalls['remove_all_filters_calls'][] = $removeAllFiltersCalls;
            }

            if ($addActionCalls) {
                $moduleCalls['add_action_calls'][] = $addActionCalls;
            }

            if ($removeActionCalls) {
                $moduleCalls['remove_action_calls'][] = $removeActionCalls;
            }

            if ($removeAllActionsCalls) {
                $moduleCalls['remove_all_actions_calls'][] = $removeAllActionsCalls;
            }
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
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
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

    private function getRemoveFilterFunctionCalls($module, $file) {
        $formattedRemoveFilterCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'remove_filter');

        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 2) {
                continue;
            }

            $entry = [
                'module' => $module,
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
                'line' => $call['line'],
                'tag' => $call['args'][0],
                'function_to_remove' => $call['args'][1]
            ];

            if (isset($call['args'][2])) {
                $entry['priority'] = $call['args'][2];
            }

            $formattedRemoveFilterCalls[$call['args'][0]][] = $entry;
        }

        return $formattedRemoveFilterCalls;
    }

    private function getRemoveAllFiltersFunctionCalls($module, $file) {
        $formattedRemoveAllFiltersCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'remove_all_filters');

        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 1) {
                continue;
            }

            $entry = [
                'module' => $module,
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
                'line' => $call['line'],
                'tag' => $call['args'][0]
            ];

            if (isset($call['args'][1])) {
                $entry['priority'] = $call['args'][1];
            }

            $formattedRemoveAllFiltersCalls[$call['args'][0]][] = $entry;
        }

        return $formattedRemoveAllFiltersCalls;
    }

    private function getAddActionFunctionCalls($module, $file) {
        $formattedAddActionCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'add_action');

        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 2) {
                continue;
            }

            $entry = [
                'module' => $module,
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
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

            $formattedAddActionCalls[$call['args'][0]][] = $entry;
        }

        return $formattedAddActionCalls;
    }

    private function getRemoveActionFunctionCalls($module, $file) {
        $formattedRemoveActionCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'remove_action');

        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 2) {
                continue;
            }

            $entry = [
                'module' => $module,
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
                'line' => $call['line'],
                'tag' => $call['args'][0],
                'function_to_remove' => $call['args'][1]
            ];

            if (isset($call['args'][2])) {
                $entry['priority'] = $call['args'][2];
            }

            $formattedRemoveActionCalls[$call['args'][0]][] = $entry;
        }

        return $formattedRemoveActionCalls;
    }

    private function getRemoveAllActionsFunctionCalls($module, $file) {
        $formattedRemoveAllActionsCalls = [];
        $functionCalls = Utilities::getFunctionCalls($file, 'remove_all_actions');

        if (!$functionCalls) {
            return false;
        }

        foreach ($functionCalls as $call) {
            if (count($call['args']) < 2) {
                continue;
            }

            $entry = [
                'module' => $module,
                'file' => str_replace('\\', '/', str_replace($module['absolute_directory'], basename($module['absolute_directory']), $file)),
                'line' => $call['line'],
                'tag' => $call['args'][0]
            ];

            if (isset($call['args'][1])) {
                $entry['priority'] = $call['args'][1];
            }

            $formattedRemoveAllActionsCalls[$call['args'][0]][] = $entry;
        }

        return $formattedRemoveAllActionsCalls;
    }

    public function getCache() {
        return $this->cache;
    }
}
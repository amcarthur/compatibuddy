<?php

namespace Compatibuddy\Analyzers;

interface AnalyzerInterface {
    public function analyze($scanResults, $subject);
}
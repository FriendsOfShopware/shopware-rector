<?php
declare(strict_types=1);

use Tracy\Debugger;

require_once __DIR__ . '/../vendor/autoload.php';

// silent deprecations, since we test them
error_reporting(E_ALL & ~E_NOTICE | E_DEPRECATED);
// performance boost
gc_disable();
// define some globals

// for dump() function
Debugger::$maxDepth = 2;

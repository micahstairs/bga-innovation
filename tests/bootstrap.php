<?php

use BGAWorkbench\Test\StubProductionEnvironment;

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$vendorDir = __DIR__ . '/../vendor';
require_once $vendorDir . '/autoload.php';
require_once 'Helpers/TestHelpers.php';

StubProductionEnvironment::stub();

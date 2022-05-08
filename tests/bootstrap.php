<?php

use BGAWorkbench\Test\StubProductionEnvironment;

// We're on GitHub Actions, so do this statically since the directory structure there is different
$ci = getenv('CI');
if (!empty($ci)) {
    $gameName = 'innovation';
} else {
    // Otherwise, determine it from the directory pathing structure
    $gamePath = explode(DIRECTORY_SEPARATOR, getcwd());
    $gameName = end($gamePath);
}

define('BGA_GAME_NAME', $gameName);
define('BGA_GAME_CLASS', ucfirst($gameName));

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$vendorDir = __DIR__ . '/../vendor';
require_once $vendorDir . '/autoload.php';
require_once 'Helpers/TestHelpers.php';

StubProductionEnvironment::stub();
require_once "$gameName.game.php";

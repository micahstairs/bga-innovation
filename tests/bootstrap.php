<?php

use BGAWorkbench\Test\StubProductionEnvironment;

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

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

$vendorDir = __DIR__ . '/../vendor';
require_once $vendorDir . '/autoload.php';
require_once 'Helpers/Mocks.php';
require_once 'Helpers/TestHelpers.php';
require_once 'BaseTest.php';
require_once 'Integration/BaseIntegrationTest.php';
require_once 'Integration/Cards/BaseCardIntegrationTest.php';

$cardsDir = __DIR__ . '/../modules/Innovation/Cards';
foreach (glob($cardsDir . '/*.php') as $filename) {
  require $filename;
}
foreach (glob($cardsDir . '/Base/*.php') as $filename) {
  require $filename;
}

StubProductionEnvironment::stub();
require_once "$gameName.game.php";

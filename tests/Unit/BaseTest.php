<?php

namespace Unit;

use BGAWorkbench\Utils;
use BGAWorkbench\Test\StubProductionEnvironment;
use BGAWorkbench\Test\TestHelp;
use BGAWorkbench\Test\TableInstanceBuilder;
use Helpers\TestHelpers;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase {
  use TestHelpers;
  use TestHelp;

  protected function createGameTableInstanceBuilder() : TableInstanceBuilder {
    return $this->gameTableInstanceBuilder()
      ->setPlayersWithIds([66, 77])
      ->overridePlayersPostSetup([
          66 => ['player_color' => 'ff0000'],
          77 => ['player_color' => '00ff00']
      ]);
  }

  /**
   * Return \Innovation class, but as the user-specific name (to allow for easy dev workflow)
   *
   * @return object
   */
  public function getInnovationInstance() {
    $klass = BGA_GAME_CLASS;
    return new $klass();
  }
}

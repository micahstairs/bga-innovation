<?php

namespace Integration;

use BGAWorkbench\Test\TableInstanceBuilder;
use BGAWorkbench\Test\TestHelp;

trait GameSetup {
  use TestHelp;

  protected function createGameTableInstanceBuilder(): TableInstanceBuilder
  {
    return $this->gameTableInstanceBuilder()
      ->setPlayersWithIds([self::getPlayer1(), self::getPlayer2()])
      ->overrideGlobalsPreSetup(parent::getGameOptions());
  }

  protected function getPlayer1(): int {
    return 12345;
  }

  protected function getPlayer2(): int {
    return 67890;
  }
}
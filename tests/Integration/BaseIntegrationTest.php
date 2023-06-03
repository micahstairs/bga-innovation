<?php

namespace Integration;

use BaseTest;

abstract class BaseIntegrationTest extends BaseTest {
  
  protected function getGameOptions(): Array {
    return [
      "game_type" => 1, // non-2v2
      "game_rules" => 1, // 3rd edition
      "artifacts_mode" => 1, // disabled
      "cities_mode" => 1, // disabled
      "echoes_mode" => 1, // disabled
      "unseen_mode" => 1, // disabled
      "extra_achievement_to_win" => 1, // disabled
    ];
  }
}

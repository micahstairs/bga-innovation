<?php

namespace Unit\Innovation\Cards\Base;

use Helpers\FakeGame;
use Innovation\Cards\Base\Card2;
use Unit\BaseTest;

class Card2Test extends BaseTest {
  protected function setUp(): void {
    $this->game = new FakeGame();
    $this->card = new Card2($this->game);
  }

  public function test() {
    // TODO(LATER): Add a test here.
  }
}

<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card2Test extends BaseIntegrationTest
{
  // Writing:
  //   - Draw a [2].

  public function test()
  {
    self::dogma();

    self::assertDogmaComplete();
    self::assertEquals(self::getInitialHandSize() + 1, self::countCards('hand'));
    self::assertEquals(2, self::getMaxAge('hand'));
  }
}

<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card0Test extends BaseIntegrationTest
{
  // Pottery:
  //   - You may return up to three cards from your hand. If you return any cards, draw and score a
  //     card of value equal to the number of cards you return.
  //   - Draw a [1].

  public function test_whenReturnOneCard_drawAndScore1()
  {
    self::meld();
    self::setHandSize(2);

    self::dogma();
    self::selectRandomCard("hand");
    self::pass();

    self::assertDogmaComplete();
    self::assertEquals(2, self::countCards('hand'));
    self::assertEquals(1, self::getScore());
  }

  public function test_whenReturnThreeCards_drawAndScore3()
  {
    self::setHandSize(4);

    self::dogma();
    self::selectRandomCard("hand");
    self::selectRandomCard("hand");
    self::selectRandomCard("hand");

    self::assertDogmaComplete();
    self::assertEquals(2, self::countCards('hand'));
    self::assertEquals(3, self::getScore());
  }

  public function test_whenReturnNoCards_doNotDrawAndScore()
  {
    self::dogma();
    self::pass();

    self::assertDogmaComplete();
    self::assertEquals(self::getInitialHandSize() + 1, self::countCards('hand'));
    self::assertEquals(0, self::getScore());
  }
}

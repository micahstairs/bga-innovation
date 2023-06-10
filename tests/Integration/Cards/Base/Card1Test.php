<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card1Test extends BaseIntegrationTest
{
  // Tools:
  //   - You may return three cards from your hand. If you return three, draw and meld a [3].
  //   - You may return a [3] from your hand. If you do, draw three [1].

  public function test_whenReturnThreeCards_drawAndMeld3()
  {
    self::setHandSize(4);

    self::dogma();
    self::selectRandomCard();
    self::selectRandomCard();
    self::selectRandomCard();
    self::passIfNeeded(); // Do not return a 3

    self::assertDogmaComplete();
    self::assertEquals(1, self::countCards('hand'));
    self::assertEquals(3, self::getMaxAge('board'));
  }

  public function test_whenReturnLessThanThreeCards_doNotDrawAndMeld3()
  {
    self::setHandSize(2);

    self::dogma();
    self::selectRandomCard(); // 2nd card will be chosen automatically since it's the last card in hand
    self::passIfNeeded(); // Do not return a 3

    self::assertDogmaComplete();
    self::assertEquals(0, self::countCards('hand'));
    self::assertEquals(1, self::getMaxAge('board'));
  }

  public function test_whenReturnAgeThree_drawThreeCards()
  {
    self::setHandSize(2);
    $card = self::drawBaseCard(3);

    self::dogma();
    self::pass(); // Do not return three cards
    self::selectCard($card['id']); // Return a 3

    self::assertDogmaComplete();
    self::assertEquals(self::getInitialHandSize() + 3, self::countCards('hand'));
  }
}

<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card3Test extends BaseIntegrationTest
{
  // Archery:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  //   - [4th edition] Junk an available achievement of value [1] or [2].

  public function test_thirdEdition()
  {
    $card = self::drawBaseCard(6, self::getNonActivePlayerId());

    self::dogma();

    self::assertDogmaComplete();
    self::assertCardInLocation($card['id'], "hand");
  }

  public function test_fourthEdition()
  {
    $card = self::drawBaseCard(4, self::getNonActivePlayerId());

    self::dogma();

    self::selectRandomCard(); // Junk random achievement

    self::assertDogmaComplete();
    self::assertCardInLocation($card['id'], "hand");
  }
}

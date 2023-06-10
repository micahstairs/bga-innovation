<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card0Test extends BaseIntegrationTest
{

  public function testReturnOneCard()
  {
    // "You may return up to three cards from your hand. If you return any cards, draw and score a card of value equal to the number of cards you return."
    $launcherId = self::getPlayer1();
    self::meldAndDogma($launcherId, 0);
    self::selectRandomCard($launcherId, "hand");
    self::assertDogmaComplete();
    self::assertEquals(1, self::getScore($launcherId));
  }

  public function testReturnNoCards()
  {
    // "You may return up to three cards from your hand. If you return any cards, draw and score a card of value equal to the number of cards you return."
    $launcherId = self::getPlayer1();
    self::meldAndDogma($launcherId, 0);
    self::pass($launcherId);
    self::assertDogmaComplete();
    self::assertEquals(0, self::getScore($launcherId));
  }
}

<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card0Test extends BaseIntegrationTest
{
  // Pottery:
  //   - You may return up to three cards from your hand. If you return any cards, draw and score a
  //     card of value equal to the number of cards you return.

  public function testReturnOneCard()
  {
    $launcherId = self::getPlayer1();
    self::meld($launcherId, 0);
    $initial_hand_size = self::countCards($launcherId, 'hand');

    self::dogma($launcherId, 0);
    self::selectRandomCard($launcherId, "hand");

    self::assertDogmaComplete();
    self::assertEquals($initial_hand_size, self::countCards($launcherId, 'hand'));
    self::assertEquals(1, self::getScore($launcherId));
  }

  public function testReturnNoCards()
  {
    $launcherId = self::getPlayer1();
    self::meld($launcherId, 0);
    $initial_hand_size = self::countCards($launcherId, 'hand');

    self::dogma($launcherId, 0);
    self::pass($launcherId);

    self::assertDogmaComplete();
    self::assertEquals($initial_hand_size + 1, self::countCards($launcherId, 'hand'));
    self::assertEquals(0, self::getScore($launcherId));
  }
}

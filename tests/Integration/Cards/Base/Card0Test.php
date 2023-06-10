<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class Card0Test extends BaseIntegrationTest
{

  public function testDogma()
  {
    $launcherId = self::getPlayer1();
    self::meldAndDogma($launcherId, 0);
    self::selectRandomCard($launcherId, "hand");
    self::assertDogmaComplete();
  }
}

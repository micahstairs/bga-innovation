<?php

namespace Integration\Cards\Artifacts;

use Integration\Cards\BaseCardIntegrationTest;

class Card178Test extends BaseCardIntegrationTest
{
  // Jedlik's Electromagnetic Self-Rotor (4th edition):
  //   - Draw and score an [8].
  //   - Draw and meld an [8]. If you do, choose a value, and junk all cards in the deck of that value.

  public function test_when8DeckIsEmpty_fourthEdition_artifacts_stillOffersJunkChoice()
  {
    self::junkBaseDeckOfAge(8);

    self::dogma();

    self::assertEquals('selectionMove', self::getCurrentStateName());
    self::assertEquals('choose_value', self::getSpecialChoiceType());
  }
}

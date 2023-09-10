<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

class Card361 extends Card
{

  // Deodorant
  // - 3rd edition:
  //   - ECHO: Draw and meld a [3].
  //   - If you have a top card with a [AUTHORITY], draw and meld a [3]. Otherwise, draw a [4].
  // - 4th edition:
  //   - ECHO: Draw and meld a [3].
  //   - If you have a top card with a [AUTHORITY], draw and meld a [3]. Otherwise, draw a [4].
  //   - If you have a top card with a [INDUSTRY], junk all cards in the [4] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndMeld(3);
    } else if (self::isFirstNonDemand()) {
      $hasAuthority = false;
      $hasIndustry = false;
      foreach (self::getTopCards() as $card) {
        if (self::hasIcon($card, Icons::AUTHORITY)) {
          $hasAuthority = true;
        }
        if (self::hasIcon($card, Icons::INDUSTRY)) {
          $hasIndustry = true;
        }
      }
      if ($hasAuthority) {
        self::drawAndMeld(3);
      } else {
        self::draw(4);
      }
      if ($hasIndustry) {
        self::junkBaseDeck(4);
      }
    }
  }

}
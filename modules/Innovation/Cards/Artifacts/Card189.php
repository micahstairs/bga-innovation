<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card189 extends AbstractCard
{
  // Ocean Liner Titanic (3rd edition):
  //   - Score all bottom cards from your board.
  // Liner Titanic (4th edition):
  //   - Score your bottom card of each color.
  //   - Junk all cards from the deck of lowest value with a card.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      foreach (Colors::ALL as $color) {
        self::score(self::getBottomCardOfColor($color));
      }
    } else if (self::isSecondNonDemand()) {
      $decks = self::getBaseDecks();
      for ($i = 0; $i <= 11; $i++) {
        if ($decks[$i]) {
          self::junkBaseDeck($i);
          break;
        }
      }
    }
  }

}
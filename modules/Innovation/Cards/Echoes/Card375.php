<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card375 extends AbstractCard
{

  // Lightning Rod
  // - 3rd edition:
  //   - ECHO: Draw and tuck a [5].
  //   - I DEMAND you draw and tuck a [5]! Return your top card of the tucked card's color!
  //   - Draw and tuck a [5]. You may return a top card from your board.
  // - 4th edition:
  //   - ECHO: Draw and tuck a [5].
  //   - I DEMAND you draw and tuck a [5]! Return your top card of the tucked card's color!
  //   - Return a top card from your board.
  //   - Junk all cards in the [6] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(5);
    } else if (self::isDemand()) {
      $card = self::drawAndTuck(5);
      self::return(self::getTopCardOfColor($card['color']));
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndTuck(5);
      }
      self::setMaxSteps(1);
    } else {
      self::junkBaseDeck(6);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass' => self::isFirstOrThirdEdition(),
      'location_from' => 'board',
      'return_keyword' => true,
    ];
  }

}
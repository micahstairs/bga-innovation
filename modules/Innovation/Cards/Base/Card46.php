<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card46 extends AbstractCard
{
  // Physics:
  // - 3rd edition:
  //   - Draw three [6] and reveal them. If two or more of the drawn cards are the same color,
  //     return the drawn cards and all cards in your hand. Otherwise, keep them.
  // - 4th edition:
  //   - Draw three [6] and reveal them. If at least two of the drawn cards are the same color,
  //     return all cards in your hand.

  public function initialExecution()
  {
    $cards = [];
    for ($i = 0; $i < 3; $i++) {
      $card = self::drawAndReveal(6);
      $cards[] = $card;
    }
    if (count(self::getUniqueColors($cards)) < 3) {
      self::setMaxSteps(1);
    } else {
      foreach ($cards as $card) {
        self::transferToHand($card);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->return()->all()->fromYourHandOrRevealed();
  }

}
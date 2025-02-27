<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card187 extends AbstractCard
{
  // Battleship Bismarck
  //   - I COMPEL you to draw and reveal an [8]! Return all cards of the drawn card's color from
  //     your board!

  public function getInteractionOptions(): array
  {
    $card = self::drawAndReveal(8);
    self::transferToHand($card);
    return self::youMust()->return()->all()->fromYourStack(self::getColor($card))->build();
  }

}
<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card514 extends AbstractCard
{

  // Taqiyya:
  //   - Choose a color. Transfer all cards of that color on your board into your hand.
  //   - Draw and meld a [3]. If the melded card is a bottom card on your board, score it and any
  //     number of cards of its color in your hand.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      $card = self::drawAndMeld(3);
      if (self::getId(self::getBottomCardOfColor(self::getColor($card))) == self::getId($card)) {
        self::score($card);
        self::setAuxiliaryValue(self::getColor($card));
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return self::youMust()->chooseColor()->build();
    } else {
      return self::youMay()->score()->anyNumber()->withColor(self::getAuxiliaryValue())->fromYourHand()->build();
    }
  }

  public function handleColorChoice(int $color)
  {
    foreach (self::getStack($color) as $card) {
      self::transferToHand($card);
    }
  }

}
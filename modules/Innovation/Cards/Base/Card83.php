<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card83 extends AbstractCard
{
  // Empiricism:
  // - 3rd edition:
  //   - Choose two colors, then draw and reveal a [9]. If it is either of the colors you choose,
  //     meld it and you may splay your cards of that color up.
  //   - If you have twenty or more [CONCEPT] on your board, you win.
  // - 4th edition:
  //   - Choose two colors, then draw and reveal a [9]. If the drawn card is one of those colors,
  //     meld it and splay your cards of that color up, otherwise unsplay that color.
  //   - If you have at least twenty [CONCEPT] on your board, you win.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      if (self::getStandardIconCount(Icons::CONCEPT) >= 20) {
        self::win();
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseTwoColors()->build();
    } else {
      return self::youMay()->splayUp(self::getAuxiliaryValue())->build();
    }
  }

  public function handleTwoColorChoice(int $color1, int $color2)
  {
    self::notifyTwoColorChoice($color1, $color2);
    $card = self::drawAndReveal(9);
    $drawnColor = self::getColor($card);
    $this->notifications->notifyCardColor($drawnColor);
    if ($drawnColor == $color1 || $drawnColor == $color2) {
      self::meld($card);
      if (self::isFourthEdition()) {
        self::splayUp($drawnColor);
      } else {
        self::setAuxiliaryValue($drawnColor); // Remember which color was drawn
        self::setMaxSteps(2);
      }
    } else {
      self::transferToHand($card);
      if (self::isFourthEdition()) {
        self::unsplay($drawnColor);
      }
    }
  }

}
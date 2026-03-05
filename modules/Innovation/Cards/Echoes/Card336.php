<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card336 extends AbstractCard
{

  // Comb
  // - 3rd edition:
  //   - Choose a color, then draw and reveal five [1]s. Keep all cards that match the color
  //     chosen. Return the rest of the drawn cards.
  // - 4th edition:
  //   - Choose a color, then draw and reveal five [1]. Return the drawn cards that do not match
  //     the chosen color. If Comb was foreseen, return all cards of the chosen color from all
  //     boards.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseColor();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->return()->all()->fromYourRevealed();
    } else {
      return self::youMust()->return()->fromAnywhereInStack()->withColor(self::getAuxiliaryValue())->fromAnyPlayer();
    }
  }

  public function handleColorChoice(int $color)
  {
    self::notifyColorChoice($color);
    $revealedCards = [];
    for ($i = 0; $i < 5; $i++) {
      $revealedCards[] = self::drawAndReveal(1);
    }
    foreach ($revealedCards as $card) {
      if (self::getColor($card) == $color) {
        self::transferToHand($card);
      }
    }
    if (self::wasForeseen()) {
      self::setAuxiliaryValue($color);
      self::setMaxSteps(3);
    }
  }

}
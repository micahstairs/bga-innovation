<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card517 extends AbstractCard
{

  // Ninja:
  //   - I demand you return a card of the color of my choice from your hand! 
  //     If you do, transfer the top card of that color from your board to mine!
  //   - You may splay your red cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->chooseColor()->ofMyChoice();
      } else {
        return self::youMust()->return()->withColor(self::getAuxiliaryValue())->fromYourHand();
      }
    } else {
      return self::youMay()->splayRight(Colors::RED);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      if (self::getNumChosen() > 0) {
        self::transferToBoard(self::getTopCardOfColor(self::getAuxiliaryValue()), self::getLauncherId());
      } else {
        self::revealHand();
      }
    }
  }

  public function handleColorChoice(int $color): void
  {
    self::notifyColorChoice($color, self::getLauncherId());
    self::setAuxiliaryValue($color);
  }

}
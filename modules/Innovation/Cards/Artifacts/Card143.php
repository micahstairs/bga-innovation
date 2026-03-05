<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card143 extends AbstractCard
{

  // Necronomicon
  //   - Draw and reveal a [3]. If it is:
  //       Yellow: Return all cards in your hand.
  //       Green: Unsplay all your colors.
  //       Red: Return all cards in your score pile.
  //       Blue: Draw a [9].

  public function initialExecution()
  {
    $card = self::drawAndReveal(3);
    $this->notifications->notifyCardColor(self::getColor($card));
    if (self::isYellow($card)) {
      self::setAuxiliaryValue(Colors::YELLOW);
      self::setMaxSteps(1);
    } else if (self::isGreen($card)) {
      foreach (Colors::ALL as $color) {
        self::unsplay($color);
      }
    } else if (self::isRed($card)) {
      self::setAuxiliaryValue(Colors::RED);
      self::setMaxSteps(1);
    } else if (self::isBlue($card)) {
      self::draw(9);
    }
    self::transferToHand($card);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::getAuxiliaryValue() == Colors::YELLOW) {
      return self::youMust()->return()->all()->fromYourHand();
    } else {
      return self::youMust()->return()->all()->fromYourScore();
    }
  }

}
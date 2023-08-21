<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card512 extends Card
{

  // Secret Police:
  //   - I DEMAND you tuck a card in your hand, then return your top card of its color! If you do,
  //     repeat this effect! Otherwise, draw a [3]!
  //   - You may tuck any number of cards of any one color from your hand.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      // There is no need to pick a color if the hand is empty
      if (self::countCards('hand') > 0) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from' => 'hand',
        'tuck_keyword'  => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass'     => true,
        'choose_color' => true,
      ];
    } else {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function handleSpecialChoice(int $choice)
  {
    self::setMaxSteps(2);
    self::setAuxiliaryValue($choice);
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::getNumChosen() > 0 && self::return(self::getTopCardOfColor(self::getLastSelectedColor()))) {
        self::setNextStep(1);
      } else {
        self::draw(3);
      }
    }
  }

}
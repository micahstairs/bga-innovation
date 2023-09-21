<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card341 extends AbstractCard
{

  // Soap
  // - 3rd edition
  //   - Choose a color. You may tuck any number of cards of that color from your hand. If you tucked
  //     at least three, you may achieve (if eligible) a card from your hand.
  // - 4th edition
  //   - Choose a color. You may tuck any number of cards of that color from your hand. If you tuck
  //     three, you may achieve (if eligible) a card from your hand.

  public function initialExecution()
  {
    if (self::countCards('hand') > 0) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    } else {
      return [
        'can_pass'            => true,
        'location_from'       => 'hand',
        'achieve_if_eligible' => true,
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color);
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() >= 3) {
      self::setMaxSteps(3);
    }
  }

}
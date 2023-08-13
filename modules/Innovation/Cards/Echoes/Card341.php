<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card341 extends Card
{

  // Soap
  //   - Choose a color. You may tuck any number of cards of that color from your hand. If you
  //     tuck three or more, you may achieve (if eligible) a card from your hand.

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
        'can_pass'                        => true,
        'location_from'                   => 'hand',
        'location_to'                     => 'achievements',
        'require_achievement_eligibility' => true,
      ];
    }
  }

  public function handleSpecialChoice(int $color)
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
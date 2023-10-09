<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card385_4E extends AbstractCard
{
  // Bifocals (4th edition):
  //   - ECHO: Return a card from your forecast.
  //   - Draw and foreshadow a [7], and then if Bifocals was foreseen, draw and foreshadow a card
  //     of value equal to the lowest available standard achievement.
  //   - You may splay your green cards right. If you do, splay any color of your cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::drawAndForeshadow(7);
      if (self::wasForeseen()) {
        $value = self::getMinValue(self::getAvailableStandardAchievements());
        self::drawAndForeshadow($value);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::GREEN],
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand() && self::isFirstInteraction() && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
    }
  }

}
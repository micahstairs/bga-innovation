<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card3 extends AbstractCard
{

  // Archery:
  // - 3rd edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  // - 4th edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  //   - Junk an available achievement of value [1] or [2].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::draw(1);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from' => 'hand',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'hand',
        'age'           => self::getMaxValueInLocation('hand'),
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age_min'       => 1,
        'age_max'       => 2,
      ];
    }
  }
}
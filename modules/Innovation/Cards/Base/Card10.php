<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card10 extends AbstractCard
{
  // Domestication
  //   - Meld the lowest card in your hand. Draw a [1].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::HAND,
      'meld_keyword'  => true,
      'age'           => self::getMinValueInLocation(Locations::HAND),
    ];

  }

  public function afterInteraction()
  {
    self::draw(1);
  }

}
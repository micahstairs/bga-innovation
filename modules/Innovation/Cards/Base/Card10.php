<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;

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
      'meld_keyword'  => true,
      'age'           => ValueSelectors::LOWEST,
      'location_from' => Locations::HAND,
    ];

  }

  public function afterInteraction()
  {
    self::draw(1);
  }

}
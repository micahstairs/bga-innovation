<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card164_3E extends AbstractCard
{
  // Almira, Queen of the Castle (3rd edition):
  //   - Meld a card from your hand. Claim an achievement of matching value, ignoring eligibility.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'age'             => self::getLastSelectedFaceUpAge(),
        'achieve_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() === 1) {
      self::setMaxSteps(2);
    }
  }
}

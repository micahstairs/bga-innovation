<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card62_3E extends AbstractCard
{
  // Vaccination (3rd edition):
  //   - I DEMAND you return all the lowest cards in your score pile! If you returned any, draw and
  //     meld a [6]!
  //   - If any card was returned as a result of the demand, draw and meld a [7]!

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::getAuxiliaryValue() === 1) {
        self::drawAndMeld(7);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => Locations::SCORE,
      'return_keyword' => true,
      'age'            => self::getMinValueInLocation(Locations::SCORE),
    ];
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::getNumChosen() > 0) {
      self::drawAndMeld(6);
      self::setAuxiliaryValue(1); // Remember that a card was returned
    }
  }

}
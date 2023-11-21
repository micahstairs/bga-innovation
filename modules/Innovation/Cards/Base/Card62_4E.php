<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card62_4E extends AbstractCard
{
  // Vaccination (4th edition):
  //   - I DEMAND you choose a card in your score pile! Return all the cards from your score pile
  //     of its value! If you return any, draw and meld a [6]!
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
    if (self::isFirstInteraction()) {
      return ['choose_from'  => Locations::SCORE];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'age'            => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(self::getValue($card));
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::drawAndMeld(6);
      self::setAuxiliaryValue(1); // Remember that a card was returned
    }
  }

}
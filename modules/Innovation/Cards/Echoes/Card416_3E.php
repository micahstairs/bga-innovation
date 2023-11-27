<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card416_3E extends AbstractCard
{

  // Laser (3rd edition):
  //   - Return all unclaimed standard achievements. Then, return half (rounded up) of the cards in
  //     your score pile. Draw and meld two [10].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::AVAILABLE_ACHIEVEMENTS,
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'              => ceil(self::countCards(Locations::SCORE) / 2),
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::drawAndMeld(10);
      self::drawAndMeld(10);
    }
  }

}
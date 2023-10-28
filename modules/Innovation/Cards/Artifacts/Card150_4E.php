<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card150_4E extends AbstractCard
{

  // Hunt-Lenox Globe (4th edition):
  //   - If you have fewer than four cards in your hand, return your top card of each non-green
  //     color. Draw a [5] for each card you return. 
  //   - Meld a card from your hand.


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::countCards(Locations::HAND) < 4) {
        return [
          'n'              => 'all',
          'location_from'  => Locations::BOARD,
          'return_keyword' => true,
          'color'          => Colors::NON_GREEN,
        ];
      } else {
        return [];
      }
    } else {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      for ($i = 0; $i < self::getNumChosen(); $i++) {
        self::draw(5);
      }
    }
  }

}
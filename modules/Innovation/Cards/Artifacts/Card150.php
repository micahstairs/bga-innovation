<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card150 extends Card
{

  // Hunt-Lenox Globe
  // - 3rd edition:
  //   - If you have fewer than four cards in your hand, return all non-green top cards from your
  //     board. Draw a [5] for each card returned. Meld a card from your hand.
  // - 4th edition:
  //   - If you have fewer than four cards in your hand, return your top card of each non-green
  //     color. Draw a [5] for each card you return. 
  //   - Meld a card from your hand.


  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
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
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      for ($i = 0; $i < self::getNumChosen(); $i++) {
        self::draw(5);
      }
    }
  }

}
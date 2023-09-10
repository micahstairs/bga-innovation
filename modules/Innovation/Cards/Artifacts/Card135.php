<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card135 extends Card
{

  // Dunhuang Star Chart
  // - 3rd edition:
  //   - Return all cards from your hand. Draw a card of value equal to the number of cards returned.
  // - 4th edition:
  //   - Return all cards from your hand. Draw a card of value equal to the number of cards you return.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => 'hand',
      'return_keyword' => true,
    ];
  }

  public function afterInteraction() {
    self::draw(self::getNumChosen());
  }

}
<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card131 extends Card
{

  // Holy Grail
  //   - Return a card from your hand. Claim an achievement of matching value ignoring eligibility.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return [
        'age' => self::getLastSelectedAge(),
        'achieve_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}
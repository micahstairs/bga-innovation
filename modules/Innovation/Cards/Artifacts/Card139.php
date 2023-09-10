<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card139 extends Card
{

  // Philosopher's Stone
  // - 3rd edition:
  //   - Return a card from your hand. Score a number of cards from your hand equal to the value of
  //     the card returned.
  // - 4th edition:
  //   - Return a card from your hand. Score a number of cards from your hand equal to the value of
  //     the card you return. Junk all cards in the deck of value equal to the total value of the
  //     cards you score.


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'             => self::getLastSelectedAge(),
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $valueReturned = self::getNumChosen() === 1 ? self::getLastSelectedAge() : 0;
      if ($valueReturned > 0) {
        self::setAuxiliaryValue($valueReturned); // Track deck to junk
        self::setMaxSteps(2);
      } else {
        self::junkBaseDeck($valueReturned);
      }
    } else if (self::isSecondInteraction()) {
      self::junkBaseDeck(self::getAuxiliaryValue());
    }
  }

}
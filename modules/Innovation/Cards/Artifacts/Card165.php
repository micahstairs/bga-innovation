<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card165 extends Card
{
  // Kilogram of the Archives
  // - 3rd edition:
  //   - Return a card from your hand. Return a top card from your board. If you returned two cards
  //     and their values sum to ten, draw and score a [10].
  // - 4th edition:
  //   - Return a card from your hand. Return a top card from your board. If you return two cards
  //     and their values sum to ten, draw and score a [10]. Otherwise, junk all cards in the deck
  //     of value equal to the sum.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from'  => Locations::BOARD,
        'return_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $valueReturned = self::getNumChosen() === 1 ? self::getLastSelectedAge() : 0;
      self::setAuxiliaryValue($valueReturned);
    } else {
      $valueReturned = self::getNumChosen() === 1 ? self::getLastSelectedFaceUpAge() : 0;
      $sum = self::getAuxiliaryValue() + $valueReturned;
      self::notifyAll(clienttranslate('The values sum to ${number}'), ['number' => $sum]);
      if ($sum === 10) {
        self::drawAndScore(10);
      } else {
        self::junkBaseDeck($sum);
      }
    }
  }

}
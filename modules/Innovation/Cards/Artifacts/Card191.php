<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card191 extends AbstractCard
{
  // Plush Beweglich Rod Bear (3rd edition):
  //   - Choose a value. Splay up each color with a top card of the chosen value. Return all cards
  //     of the chosen value from all score piles.
  // Plüsch Beweglich Rod Bear (4th edition):
  //   - Choose a value. Splay up each color on your board with a top card of the chosen value.
  //     Return all cards of the chosen value from all score piles.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => self::getSelectableValues(),
      ];
    } else {
      return [
        'n'              => 'all',
        'owner_from'     => 'any player',
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'age'            => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getTopCards() as $card) {
      if ($card['faceup_age'] == $value) {
        self::splayUp($card['color']);
      }
    }
    self::setAuxiliaryValue($value); // Track value to return from score piles
  }

  private function getSelectableValues(): array
  {
    if (self::isFourthEdition()) {
      return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
    }
    foreach (self::getTopCards() as $card) {
      if ($card['id'] == CardIds::BATTLESHIP_YAMATO) {
        return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
      }
    }
    return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
  }

}
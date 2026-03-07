<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
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

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue(self::getSelectableValues());
    } else {
      return self::youMust()->return()->all()->value(self::getAuxiliaryValue())->fromAnyScore();
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getTopCards() as $card) {
      if (self::getFaceupValue($card) == $value) {
        self::splayUp(self::getColor($card));
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
      if (self::getId($card) == CardIds::BATTLESHIP_YAMATO) {
        return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
      }
    }
    return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
  }

}
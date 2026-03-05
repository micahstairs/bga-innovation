<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card422_4E extends AbstractCard
{

  // Wristwatch (4th edition):
  //   - ECHO: Tuck a top card from your board.
  //   - If Wristwatch was foreseen, return all non-bottom cards from your board.
  //   - For each value in ascending order, if you have a bonus on your board of that
  //     value, draw and meld a card of that value.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::wasForeseen()) {
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      $bonuses = array_unique(self::getBonuses());
      sort($bonuses);
      foreach ($bonuses as $value) {
        self::drawAndMeld($value);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMust()->tuck()->fromYourBoard();
    } else {
      $cardIds = [];
      foreach (self::getCardsKeyedByColor(Locations::BOARD) as $stack) {
        foreach ($stack as $card) {
          if (self::getPosition($card) > 0) {
            $cardIds[] = self::getId($card);
          }
        }
      }
      self::setAuxiliaryArray($cardIds);
      return self::youMust()->return()->all()->fromAnywhereInStack()->onlyCardsInAuxiliaryArray();
    }
  }

}
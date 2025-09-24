<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card381_3E extends AbstractCard
{

  // Pressure Cooker (3rd edition):
  //   - Return all cards from your hand. For each top card on your board with a bonus, draw a
  //     card of value equal to that bonus.

  public function initialExecution()
  {
    $bonuses = self::getTopBonuses();
    if (count($bonuses) > 0) {
      self::setAuxiliaryArray($bonuses);
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourHand()->build();
    } else {
      return self::youMust()->chooseValue(self::getAuxiliaryArray())->build();
    }
  }

  public function handleValueChoice($value)
  {
    $remainingValues = self::removeFromAuxiliaryArray($value);
    self::draw($value);
    if ($remainingValues) {
      self::setNextStep(2);
    }
  }

  private function getTopBonuses(): array
  {
    $bonuses = [];
    foreach (self::getTopCards() as $card) {
      $bonus = self::getBonusIcon($card);
      if ($bonus > 0) {
        $bonuses[] = $bonus;
      }
    }
    return $bonuses;
  }

}
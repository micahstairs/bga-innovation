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
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
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

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $bonuses = self::getTopBonuses();
      if (count($bonuses) > 0) {
        self::setAuxiliaryArray($bonuses);
        self::setMaxSteps(2);
      }
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
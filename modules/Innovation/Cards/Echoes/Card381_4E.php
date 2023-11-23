<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card381_4E extends AbstractCard
{

  // Pressure Cooker (4th edition):
  //   - Return all cards from your hand. For each top card of different color on your board with a
  //     bonus, both draw a card and junk an available achievement of value equal to that bonus.

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
    } else if (self::isSecondInteraction()) {
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleValueChoice($value)
  {
    self::removeFromAuxiliaryArray($value);
    self::draw($value);
    self::setAuxiliaryValue($value); // Track which value needs to be junked
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $bonuses = self::getTopBonuses();
      if ($bonuses) {
        self::setAuxiliaryArray($bonuses);
        self::setMaxSteps(3);
      }
    } else if (self::isThirdInteraction()) {
      if (self::getAuxiliaryArray()) {
        self::setNextStep(2);
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
<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Utils\Arrays;

class Card381 extends Card
{

  // Pressure Cooker
  // - 3rd edition:
  //   - Return all cards from your hand. For each top card on your board with a bonus, draw a
  //     card of value equal to that bonus.
  // - 4th edition:
  //   - Return all cards from your hand. For each top card on your board with a bonus, both draw
  //     a card and junk an available achievement of value equal to that bonus.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'n'              => 'all',
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else if (self::getCurrentStep() === 2) {
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'location_to'   => 'junk',
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleSpecialChoice($value)
  {
    $remainingValues = Arrays::removeElement(self::getAuxiliaryArray(), $value);
    self::setAuxiliaryArray($remainingValues);
    self::draw($value);
    if (self::isFirstOrThirdEdition()) {
      if (count($remainingValues) > 0) {
        self::setNextStep(1);
      }
    } else {
      self::setAuxiliaryValue($value); // Track which value needs to be junked
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() === 1) {
      $bonuses = self::getTopBonuses();
      if (count($bonuses) > 0) {
        self::setAuxiliaryArray($bonuses);
        self::setMaxSteps(self::isFirstOrThirdEdition() ? 2 : 3);
      }
    } else if (self::getCurrentStep() === 3) {
      if (count(self::getAuxiliaryArray()) > 0) {
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
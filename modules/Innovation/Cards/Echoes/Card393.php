<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card393 extends Card
{

  // Indian Clubs
  // - 3rd edition
  //   - I DEMAND you return two cards from your score pile!
  //   - For every value of card in your score pile, score a card from your hand of that value.
  // - 4th edition
  //   - I DEMAND you return two cards from your score pile! If Indian Clubs was foreseen, return
  //     all cards from your score pile!
  //   - For every value of card in your score pile, score a card from your hand of that value.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      $nextValue = self::getNextValueToReturn(0);
      if ($nextValue) {
        self::setAuxiliaryValue($nextValue); // Track the value of the last scored value
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'n'              => self::wasForeseen() ? 'all' : 2,
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'score_keyword' => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $lastScoredValue = self::getAuxiliaryValue();
      $nextValue = self::getNextValueToReturn($lastScoredValue);
      if ($nextValue) {
        self::setAuxiliaryValue($nextValue);
        self::setNextStep(1);
      }
    }
  }

  private function getNextValueToReturn(int $lastScoredValue): ?int
  {
    $scorePileCounts = self::countCardsKeyedByValue('score');
    $handCounts = self::countCardsKeyedByValue('hand');
    for ($age = $lastScoredValue + 1; $age <= 11; $age++) {
      if ($scorePileCounts[$age] > 0 && $handCounts[$age] > 0) {
        return $age;
      }
    }
    return null;
  }

}
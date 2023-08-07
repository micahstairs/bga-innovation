<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Utils\Arrays;

class Card413 extends Card
{

  // Crossword
  // - 3rd edition
  //   - For each visible bonus on your board, draw a card of that value.
  // - 4th edition
  //   - For each even bonus on your board, draw a card of that value. If Crossword was foreseen,
  //     transfer the drawn cards to the available achievements.
  //   - For each odd bonus on your board, return the lowest card from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $bonuses = self::isFirstOrThirdEdition() ? self::getBonuses() : self::getEvenBonuses();
      if (count($bonuses) > 0) {
        self::setAuxiliaryArray($bonuses); // Track values to draw
        self::setActionScopedAuxiliaryArray([]); // Track which cards were drawn
        self::setMaxSteps(1);
      }
    } else {
      $oddBonuses = self::getOddBonuses();
      $numCardsToReturn = count($oddBonuses);
      if ($numCardsToReturn > 0) {
        self::setAuxiliaryValue($numCardsToReturn); // Track number of cards to return from hand
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    } else {
      return [
        'location_from'  => 'hand',
        'return_keyword' => true,
        'age' => self::getMinValueInLocation('hand'),
      ];
    }
  }

  public function handleSpecialChoice($value)
  {
    $card = self::draw($value);
    self::setActionScopedAuxiliaryArray(array_merge(self::getActionScopedAuxiliaryArray(), [$card['id']]));
    $remainingValues = Arrays::removeElement(self::getAuxiliaryArray(), $value);
    if (count($remainingValues) > 0) {
      self::setAuxiliaryArray($remainingValues);
      self::setNextStep(1);
    } else if (self::isFourthEdition()) {
      foreach (self::getActionScopedAuxiliaryArray() as $cardId) {
        $this->game->transferCardFromTo(self::getCard($cardId), 0, 'achievements');
      }
    }
  }

  public function handleCardChoice(array $card) {
    $numCardsLeftToReturn = self::getAuxiliaryValue() - 1;
    if ($numCardsLeftToReturn > 0) {
      self::setAuxiliaryValue($numCardsLeftToReturn);
      self::setNextStep(1);
    }
  }

  private function getEvenBonuses(): array {
    return array_filter(self::getBonuses(), 'isEven');
  }

  private function isEven(int $value): bool {
    return $value % 2 === 0;
  }

  private function getOddBonuses(): array {
    return array_filter(self::getBonuses(), 'isOdd');
  }

  private function isOdd(int $value): bool {
    return $value % 2 === 1;
  }

}
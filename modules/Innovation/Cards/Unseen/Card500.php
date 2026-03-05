<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card500 extends AbstractCard
{

  // Counterfeiting
  //   - Score a top card from your board of a value not in your score pile. If you do, repeat this effect.
  //   - You may splay your green or purple cards left.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $cardIds = self::getEligibleCardIds();
      if (count($cardIds) > 0) {
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->score()->onlyCardsInAuxiliaryArray();
    } else {
      return self::youMay()->splayLeft()->withColor([Colors::GREEN, Colors::PURPLE]);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $cardIds = self::getEligibleCardIds();
      if (count($cardIds) > 0) {
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(self::getMaxSteps() + 1);
      }
    }
  }

  private function getEligibleCardIds(): array
  {
    $cardIds = [];
    $scoreCardsByAge = self::countCardsKeyedByValue(Locations::SCORE);
    foreach (self::getTopCards() as $card) {
      for ($age = 1; $age <= 11; $age++) {
        if ($scoreCardsByAge[self::getValue($card)] == 0) {
          $cardIds[] = self::getId($card);
        }
      }
    }
    return $cardIds;
  }

}
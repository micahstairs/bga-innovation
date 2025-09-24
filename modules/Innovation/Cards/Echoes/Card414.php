<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card414 extends AbstractCard
{

  // Television
  //   - ECHO: Draw and meld an [8].
  //   - Choose a value and an opponent. Transfer a card of that value from their score pile to
  //     their board. If they have an achievement of the same value, achieve (if eligible) a card
  //     of that value from their score pile.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndMeld(8);
    } else {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue()->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->choosePlayer(self::getOpponents())->build();
    } else if (self::isThirdInteraction()) {
      $playerId = self::getAuxiliaryValue2();
      return self::youMust()->value(self::getAuxiliaryValue())->fromScore($playerId)->toBoard($playerId)->build();
    } else {
      return self::youMust()->achieveIfEligible()->value(self::getAuxiliaryValue())->fromScore(self::getAuxiliaryValue2())->build();
    }
  }

  public function handleValueChoice($value)
  {
    self::setAuxiliaryValue($value); // Track value chosen
  }

  public function handlePlayerChoice($playerId)
  {
    self::setAuxiliaryValue2($playerId); // Track opponent chosen
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction()) {
      $value = self::getAuxiliaryValue();
      $opponentId = self::getAuxiliaryValue2();
      if (self::countCardsKeyedByValue('achievements', $opponentId)[$value] > 0) {
        self::setMaxSteps(4);
      }
    }
  }

}
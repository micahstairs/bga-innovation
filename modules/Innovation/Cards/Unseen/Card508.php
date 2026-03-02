<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card508 extends AbstractCard
{

  // Red Envelope:
  //   - Choose a value of which you have exactly two or three cards altogether in your hand and
  //     score pile. Transfer those cards to the score pile of the player on your right.
  //   - You may score exactly two or three cards from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $handCards = self::countCardsKeyedByValue(Locations::HAND);
      $scoreCards = self::countCardsKeyedByValue(Locations::SCORE);
      $values = [];
      for ($age = 1; $age <= 11; $age++) {
        $sum = $handCards[$age] + $scoreCards[$age];
        if ($sum == 2 || $sum == 3) {
          $values[] = $age;
        }
      }
      if (count($values) > 0) {
        self::setMaxSteps(1);
        self::setAuxiliaryArray($values);
      }
    } else {
      if (self::countCards('hand') >= 2) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->chooseValue(self::getAuxiliaryArray())->build();
    } else {
      return self::youMay()->score()->minCards(2)->maxCards(3)->fromYourHand()->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $value = self::getAuxiliaryValue();
      $handCards = self::getCardsKeyedByValue('hand');
      $scoreCards = self::getCardsKeyedByValue(Locations::SCORE);
      $playerIdOnRight = $this->game->getActivePlayerIdOnRightOfActingPlayer();
      foreach ($handCards[$value] as $card) {
        self::transferToScorePile($card, $playerIdOnRight);
      }
      foreach ($scoreCards[$value] as $card) {
        self::transferToScorePile($card, $playerIdOnRight);
      }
    }
  }

}
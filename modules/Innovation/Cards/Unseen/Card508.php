<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card508 extends Card
{

  // Red Envelope:
  //   - Choose a value of which you have exactly two or three cards altogether in your hand and
  //     score pile. Transfer those cards to the score pile of the player on your right.
  //   - You may score exactly two or three cards from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $handCards = self::countCardsKeyedByValue('hand');
      $scoreCards = self::countCardsKeyedByValue('score');
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
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    } else {
      return [
        'can_pass'      => true,
        'n_min'         => 2,
        'n_max'         => 3,
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
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
      $scoreCards = self::getCardsKeyedByValue('score');
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
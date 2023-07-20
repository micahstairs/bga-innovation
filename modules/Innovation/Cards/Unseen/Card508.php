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
    if (self::getEffectNumber() === 1) {
      $handCards = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'hand');
      $scoreCards = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'score');
      $values = [];
      for ($age = 1; $age <= 11; $age++) {
        if ($handCards[$age] + $scoreCards[$age] == 2 || $handCards[$age] + $scoreCards[$age] == 3) {
          $values[] = $age;
        }
      }
      if (count($values) > 0) {
        self::setMaxSteps(1);
        $this->game->setAuxiliaryValueFromArray($values);
      }
    } else {
      if ($this->game->countCardsInHand(self::getPlayerId()) >= 2) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'choose_value' => true,
        'age'          => $this->game->getAuxiliaryValueAsArray(),
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

  public function handleSpecialChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 1) {
      $handCards = self::getCardsKeyedByValue('hand');
      $scoreCards = self::getCardsKeyedByValue('score');
      $playerIdOnRight = $this->game->getActivePlayerIdOnRightOfActingPlayer();
      foreach ($handCards[self::getAuxiliaryValue()] as $card) {
        self::transferToScorePile($card, $playerIdOnRight);
      }
      foreach ($scoreCards[self::getAuxiliaryValue()] as $card) {
        self::transferToScorePile($card, $playerIdOnRight);
      }
    }
  }

}
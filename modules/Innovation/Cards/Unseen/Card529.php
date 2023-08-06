<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card529 extends Card
{

  // Buried Treasure:
  //   - Choose an odd value. Transfer all cards of that value from all score piles to the
  //     available achievements. If you transfer four or more cards, draw and safeguard a card
  //     of that value, and score three available standard achievements.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'choose_value' => true,
        'age'          => [1, 3, 5, 7, 9, 11],
      ];
    } else {
      return [
        'n'             => 3,
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'score_keyword' => true,
      ];
    }
  }

  public function handleSpecialChoice(int $value)
  {
    $count = 0;
    foreach ($this->game->getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $playerId) {
      foreach (self::getCardsKeyedByValue('score', $playerId)[$value] as $card) {
          $this->game->transferCardFromTo($card, 0, 'achievements');
          $count++;
      }
    }
    if ($count >= 4) {
      self::drawAndSafeguard($value);
      self::setMaxSteps(2);
    }
  }

}
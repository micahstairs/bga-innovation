<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card529 extends AbstractCard
{

  // Buried Treasure:
  //   - Choose an odd value. Transfer all cards of that value from all score piles to the
  //     available achievements. If you transfer at least four cards, draw and safeguard a card
  //     of that value, and score three available standard achievements.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => [1, 3, 5, 7, 9, 11],
      ];
    } else {
      return [
        'n'             => 3,
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'score_keyword' => true,
      ];
    }
  }

  public function handleValueChoice(int $value)
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
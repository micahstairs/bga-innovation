<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card529 extends AbstractCard
{

  // Buried Treasure:
  //   - Choose an odd value. Transfer all cards of that value from all score piles to the
  //     available achievements. If you transfer at least four cards, draw and safeguard a card
  //     of that value, and score three available standard achievements.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue([1, 3, 5, 7, 9, 11]);
    } else {
      return self::youMust()->score()->exactly(3)->fromAvailableAchievements();
    }
  }

  public function handleValueChoice(int $value)
  {
    $count = 0;
    foreach ($this->game->getActivePlayerIdsInTurnOrderStartingWithCurrentPlayer() as $playerId) {
      foreach (self::getCardsKeyedByValue(Locations::SCORE, $playerId)[$value] as $card) {
        self::transferToAvailableAchievements($card);
        $count++;
      }
    }
    if ($count >= 4) {
      self::drawAndSafeguard($value);
      self::setMaxSteps(2);
    }
  }

}
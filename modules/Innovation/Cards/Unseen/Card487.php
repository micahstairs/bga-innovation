<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card487 extends AbstractCard
{
  // Rumor
  //   - Return a card from your score pile. If you do, draw a card of value one higher than the card you return.
  //   - Transfer a card from your hand to the hand of the player on your left.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'owner_to'      => $this->game->getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer()[0],
        'location_to'   => Locations::HAND,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::draw(self::getValue($card) + 1);
    }
  }

}
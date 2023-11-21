<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card51_3E extends AbstractCard
{
  // Statistics (3rd edition):
  //   - I DEMAND you transfer all the highest cards in your score pile to your hand!
  //   - You may splay your yellow cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach ($this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), Locations::SCORE) as $cardId) {
        self::transferToHand(self::getCard($cardId));
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::RIGHT,
      'color'           => [Colors::YELLOW],
    ];
  }

}
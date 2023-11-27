<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card93_3E extends AbstractCard
{
  // Services (3rd edition):
  //   - I DEMAND you transfer all the highest cards from your score pile to my hand! If you
  //     transferred any cards, then transfer a top card from my board without a [HEALTH]
  //     to your hand!

  public function initialExecution()
  {
    foreach ($this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), Locations::SCORE) as $cardId) {
      self::transferToHand(self::getCard($cardId), self::getLauncherId());
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => Locations::BOARD,
      'owner_to'      => self::getPlayerId(),
      'location_to'   => Locations::HAND,
      'without_icon'  => Icons::HEALTH,
    ];
  }

}
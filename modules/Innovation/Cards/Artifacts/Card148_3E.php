<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card148_3E extends AbstractCard
{

  // Tortugas Galleon (3rd edition):
  //   - I COMPEL you to transfer all the highest cards from your score pile to my score pile! If
  //     you do, transfer a top card on your board of that value to my board!


  public function initialExecution()
  {
    $cardIds = $this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), Locations::SCORE);
    if ($cardIds) {
      foreach ($cardIds as $cardId) {
        self::transferToScorePile(self::getCard($cardId), self::getLauncherId());
      }
      self::setAuxiliaryValue(self::getValue(self::getCard($cardIds[0])));
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location' => Locations::BOARD,
      'owner_to' => self::getLauncherId(),
      'age'      => self::getAuxiliaryValue(),
    ];
  }

}
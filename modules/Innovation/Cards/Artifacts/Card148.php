<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card148 extends Card
{

  // Tortugas Galleon (3rd edition)
  //   - I COMPEL you to transfer all the highest cards from your score pile to my score pile! If
  //     you transfer any, transfered a top card on your board of that value to my board!
  // Galleon Nuestra Señora De Atocha (4th edition)
  //   - I COMPEL you to transfer all the highest cards from your score pile to my score pile! If
  //     you transfer any, transfer a top card on your board of that value to my board!


  public function initialExecution()
  {
    $cardIds = $this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), Locations::SCORE);
    if (count($cardIds)) {
      foreach ($cardIds as $cardId) {
        self::transferToScorePile(self::getCard($cardId), self::getLauncherId());
      }
      self::setAuxiliaryValue(self::getCard($cardIds[0])['age']);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => Locations::BOARD,
      'owner_to'      => self::getLauncherId(),
      'location_to'   => Locations::BOARD,
      'age'           => self::getAuxiliaryValue(),
    ];
  }

}
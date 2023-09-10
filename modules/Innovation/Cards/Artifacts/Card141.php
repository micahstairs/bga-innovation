<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card141 extends Card
{

  // Moylough Belt Shrine
  // - 3rd edition:
  //   - I COMPEL you to reveal all cards in your hand and transfer the card of my choice to my
  //     board!
  // - 4th edition:
  //   - I COMPEL you to reveal all cards in your hand and transfer the card of my choice to my
  //     board! If you do, junk all cards in the deck of the chosen card's value!


  public function initialExecution()
  {
    foreach (self::getCards('hand') as $card) {
      self::reveal($card);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'player_id'     => self::getLauncherId(),
      'owner_from'    => self::getPlayerId(),
      'location_from' => 'revealed',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'board',
    ];
  }

  public function afterInteraction()
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    foreach (self::getCards('revealed') as $card) {
      self::transferToHand($card);
    }
    if (self::isFourthEdition() && self::getNumChosen() === 1) {
      self::junkBaseDeck(self::getLastSelectedAge());
    }
  }

}
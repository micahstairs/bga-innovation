<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card141 extends AbstractCard
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
    foreach (self::getCards(Locations::HAND) as $card) {
      self::reveal($card);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'player_id'     => self::getLauncherId(),
      'owner_from'    => self::getPlayerId(),
      'location_from' => Locations::REVEALED,
      'owner_to'      => self::getLauncherId(),
      'location_to'   => Locations::BOARD,
    ];
  }

  public function afterInteraction()
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    foreach (self::getCards(Locations::REVEALED) as $card) {
      self::transferToHand($card);
    }
    if (self::isFourthEdition() && self::getNumChosen() === 1) {
      self::junkBaseDeck(self::getLastSelectedAge());
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::HAND) > 0;
  }

}
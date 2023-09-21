<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card408 extends AbstractCard
{

  // Parachute
  // - 3rd edition
  //   - I DEMAND you transfer all cards without a [EFFICIENCY] in your hand to my hand!
  // - 4th edition
  //   - I DEMAND you transfer all cards without a [EFFICIENCY] in your hand to my hand!
  //   - If Parachute was foreseen, junk all cards from all boards.

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach (self::getCards('hand') as $card) {
        if (!self::hasIcon($card, Icons::EFFICIENCY)) {
          self::transferToHand($card, self::getLauncherId());
        }
      }
    } else if (self::wasForeseen()) {
      $cards = [];
      foreach (self::getPlayerIds() as $playerId) {
        $cards = array_merge($cards, self::getCards(Locations::BOARD, $playerId));
      }
      if (self::junkCards($cards)) {
        self::notifyPlayer(clienttranslate('${You} junked all cards from all boards.'));
        self::notifyOthers(clienttranslate('${player_name} junked all cards from all boards.'));
      }
    }
  }

}
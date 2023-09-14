<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card408 extends Card
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
        $stacks = self::getCardsKeyedByColor(Locations::BOARD, $playerId);
        foreach ($stacks as $stack) {
          $cards = array_merge($cards, $stack);
        }
      }
      self::junkCards($cards);
      self::notifyPlayer(clienttranslate('${You} junked all cards from all boards.'));
      self::notifyOthers(clienttranslate('${player_name} junked all cards from all boards.'));
    }
  }

}
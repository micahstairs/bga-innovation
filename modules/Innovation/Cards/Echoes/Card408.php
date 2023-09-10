<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;

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
      // TODO(4E): We need a better bulk junking mechanism here.
      foreach (self::getPlayerIds() as $playerId) {
        foreach (self::getCards('board', $playerId) as $card) {
          self::junk($card);
        }
      }
    }
  }

}
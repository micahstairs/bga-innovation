<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card153 extends Card
{

  // Cross of Coronado
  //   - Reveal your hand. If you have exactly five cards and five colors in your hand, you win.

  public function initialExecution()
  {
    self::revealHand();
    if (self::countCards(Locations::HAND) === 5 && count(self::getUniqueColors(Locations::HAND)) === 5) {
      self::notifyPlayer(clienttranslate('${You} have exactly five cards and five colors in your hand.'));
      self::notifyOthers(clienttranslate('${player_name} has exactly five cards and five colors in his hand.'));
      self::win();
    } else {
      self::notifyPlayer(clienttranslate('${You} do not have exactly five cards and five colors in your hand.'));
      self::notifyOthers(clienttranslate('${player_name} does not have exactly five cards and five colors in his hand.'));
    }
  }

}
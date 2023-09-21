<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card453 extends AbstractCard
{

  // Pizza Pacaya
  //   - I COMPEL you to junk all cards from your board! Draw and meld a card of each value in ascending order!

  public function initialExecution()
  {
    if (self::junkCards(self::getCards(Locations::BOARD))) {
      self::notifyPlayer(clienttranslate('${You} junked all cards on your board.'));
      self::notifyOthers(clienttranslate('${player_name} junked all cards on his board.'));
    }

    for ($i = 1; $i <= 11; $i++) {
      self::drawAndMeld($i);
    }
  }

}
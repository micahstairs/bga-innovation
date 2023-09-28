<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card206 extends AbstractCard
{
  // Higgs Boson
  // - 3rd edition:
  //   - Transfer all cards on your board to your score pile.
  // - 4th edition:
  //   - Score all cards on your board.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      self::transferCardsToScorePile(self::getCards(Locations::BOARD));
      self::notifyPlayer(clienttranslate('${You} transferred all cards on your board to your score pile.'));
      self::notifyOthers(clienttranslate('${player_name} transferred all cards on his board to his score pile.'));
    } else {
      self::scoreCards(self::getCards(Locations::BOARD));
      self::notifyPlayer(clienttranslate('${You} scored all cards on your board.'));
      self::notifyOthers(clienttranslate('${player_name} scored all cards on his board.'));
    }
  }

}
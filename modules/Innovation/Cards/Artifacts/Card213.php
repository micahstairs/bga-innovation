<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Locations;

class Card213 extends AbstractCard
{
  // DeLorean DMC-12
  // - 3rd edition:
  //   - If DeLorean DMC-12 is a top card on any board, remove all top cards on all boards and all
  //     cards in all hands from the game.
  // - 4th edition:
  //   - If Delorean DMC-12 is a top card on any board, junk a top card of each color from each
  //     board and all cards in all hands.

  public function initialExecution()
  {
    if ($this->game->isTopBoardCard(self::getCard(CardIds::DELOREAN_DMC_12))) {
      $cards = [];
      foreach (self::getPlayerIds() as $playerId) {
          $cards = array_merge($cards, self::getTopCards($playerId));
          $cards = array_merge($cards, self::getCards(Locations::HAND, $playerId));
      }
      if (self::isFirstOrThirdEdition()) {
        self::removeCards($cards);
        self::notifyAll(clienttranslate('All top cards on all boards and all cards in all hands are removed from the game.'));
      } else {
        self::junkCards($cards);
        self::notifyPlayer(clienttranslate('${You} junk a top card of each color from each board and all cards in all hands.'));
        self::notifyOthers(clienttranslate('${player_name} junks a top card of each color from each board and all cards in all hands.'));
      }
    }
  }

}
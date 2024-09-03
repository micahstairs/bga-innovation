<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card63 extends AbstractCard
{
  // Democracy:
  // - 3rd edition:
  //   - You may return any number of cards from your hand. If you have returned more cards than
  //     any other player due to Democracy so far during this dogma action, draw and score an [8].
  // - 4th edition:
  //   - You may return any number of cards from your hand. If you have returned more cards than
  //     any other player due to Democracy so far during this action, draw and score an [8].

  public function getInteractionOptions(): array
  {
    return self::youMay()->return()->anyNumber()->fromYourHand()->build();
  }

  public function afterInteraction()
  {
    self::drawIfMostCardsReturned();
  }

  public function handleAbortedInteraction()
  {
    self::drawIfMostCardsReturned();
  }

  private function drawIfMostCardsReturned()
  {
    // Increment this player's counter by the number of cards returned
    $this->game->DbQuery(
      $this->game->format(
        "UPDATE player SET democracy_counter = democracy_counter + {n} WHERE player_id = {player_id}",
        ['player_id' => self::getPlayerId(), 'n' => self::getNumChosen()]
      )
    );

    // Compare this player's counter to the highest counter of any other player
    $hasReturnedMostCards = $this->game->getUniqueValueFromDB(
      $this->game->format(
        "SELECT 
              (democracy_counter > 
                  (SELECT MAX(democracy_counter) FROM player WHERE player_id != {player_id})
              ) 
          FROM player 
          WHERE player_id = {player_id}",
        ['player_id' => self::getPlayerId()]
      )
    );

    if ($hasReturnedMostCards) {
      self::drawAndScore(8);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}
<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card519 extends Card
{

  // Blackmail:
  //   - I DEMAND you reveal your hand! Meld a revealed card of my choice! Reveal your score pile!
  //     Self-execute any revealed card of my choice, replacing 'may' with 'must'!

  public function initialExecution()
  {
    foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
      self::reveal($card);
    }
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'player_id'     => self::getLauncherId(),
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'revealed',
        'owner_to'      => self::getPlayerId(),
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'player_id'     => self::getLauncherId(),
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'revealed',
        'owner_to'      => self::getPlayerId(),
        'location_to'   => 'none',
      ];
    }
  }

  public function afterInteraction()
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    if (self::getCurrentStep() === 1) {
      foreach (self::getCards('revealed') as $card) {
        self::transferToHand($card);
      }
      foreach ($this->game->getCardsInScorePile(self::getPlayerId()) as $card) {
        self::reveal($card);
      }
    } else {
      foreach (self::getCards('revealed') as $card) {
        self::transferToScorePile($card);
      }
      // TODO(4E): Since this is occuring during a demand, it can cause the launcher to get a sharing bonus.
      $this->game->selfExecute(self::getLastSelectedCard(), /*replace_may_with_must=*/ true);
    }
  }

}
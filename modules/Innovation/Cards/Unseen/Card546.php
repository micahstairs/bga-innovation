<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card546 extends Card
{

  // Private Eye:
  //   - I DEMAND you reveal your hand! Transfer the card in your hand of my choice to my board!
  //     Draw a [7]!
  //   - Score one of your secrets.
  //   - You may splay your blue cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'hand') as $card) {
        self::reveal($card);
      }
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      // "Transfer the card in your hand of my choice to my board!"
      return [
        'player_id'     => self::getLauncherId(),
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'revealed',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'board',
      ];
    } else if (self::getEffectNumber() == 1) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'score',
        'score_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::BLUE],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      self::draw(7);
      $this->game->gamestate->changeActivePlayer(self::getPlayerId());
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'revealed') as $card) {
        self::putInHand($card);
      }
    }
  }

}
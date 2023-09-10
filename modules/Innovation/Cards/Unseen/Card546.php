<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

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
      foreach (self::getCards('hand') as $card) {
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
    } else if (self::getEffectNumber() === 1) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'score',
        'score_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::BLUE],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      $this->game->gamestate->changeActivePlayer(self::getPlayerId());
      self::draw(7);
      foreach (self::getCards('revealed') as $card) {
        self::transferToHand($card);
      }
    }
  }

}
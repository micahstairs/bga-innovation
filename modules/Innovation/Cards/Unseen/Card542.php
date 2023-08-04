<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card542 extends Card
{

  // Sabotage:
  //   - I DEMAND you draw a [6]! Reveal the cards in your hand! Return the card of my choice from
  //     your hand! Tuck the top card from your board and all cards from your score pile of the
  //     same color as the returned card!

  public function initialExecution()
  {
    self::draw(6);
    foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
      self::reveal($card);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'player_id'     => self::getLauncherId(),
        'location_from' => 'revealed',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'score',
        'tuck_keyword'  => true,
        'color'         => [self::getLastSelectedColor()],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && self::getCurrentStep() === 1) {
      self::tuck(self::getTopCardOfColor(self::getLastSelectedColor()));
      $this->game->gamestate->changeActivePlayer(self::getPlayerId());
      foreach (self::getCards('revealed') as $card) {
        self::transferToHand($card);
      }
    }
  }

}
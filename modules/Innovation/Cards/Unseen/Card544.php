<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card544 extends Card
{

  // Triad:
  //   - If you have three cards in your hand, return a card from your hand and splay the color of
  //     the returned card right, tuck a card from your hand, and score a card from your hand.

  public function initialExecution()
  {
    if (self::countCards('hand') >= 3) {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => 'hand',
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'location_to'   => 'score',
      ];
    }
  }

  public function handleCardChoice(array $cardId)
  {
    if (self::isFirstInteraction()) {
      self::splayRight(self::getLastSelectedColor());
    }
  }

}
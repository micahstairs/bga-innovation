<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card584 extends Card
{

  // Order of the Occult Hand:
  //   - If you have a [3] in your score pile, you lose.
  //   - If you have a [7] in your hand, you win.
  //   - Meld two cards from your hand. Score four cards from your hand. Splay your blue cards up.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      if (self::countCardsKeyedByValue('score')[3] > 0) {
        self::lose();
      }
    } else if (self::getEffectNumber() === 2) {
      if (self::countCardsKeyedByValue('hand')[7] > 0) {
        self::win();
      }
    } else {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n' => 2,
        'location_from' => 'hand',
        'meld_keyword'   => true,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'n' => 4,
        'location_from' => 'hand',
        'score_keyword'   => 'score',
      ];
    } else {
      return [
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::BLUE],
      ];
    }
  }

}
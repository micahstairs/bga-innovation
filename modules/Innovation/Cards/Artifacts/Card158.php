<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card158 extends Card
{
  // Ship of the Line Sussex
  // - 3rd edition:
  //   - If you have no cards in your score pile, choose a color and score all cards of that color
  //     from your board. Otherwise, return all cards from your score pile.
  // - 4th edition:
  //   - If you have no cards in your score pile, choose a color and score all cards of that color
  //     on your board. Otherwise, return all cards from your score pile.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::countCards(Locations::SCORE) === 0) {
      return ['choose_color' => true];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    foreach (array_reverse(self::getStack($color)) as $card) {
      self::score($card);
    }
  }
}
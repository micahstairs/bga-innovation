<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card471 extends Card
{

  // Touchscreen
  //   - Splay each color on your board in a direction according to the number of cards of that
  //     color on your board: two or three, aslant; four or five, up; six or seven, right; eight
  //     or more, left. If you splay five colors, you win.

  public function initialExecution()
  {
    $numColorsSplayed = 0;
    $countsByColor = self::countCardsKeyedByColor('board');
    foreach (Colors::ALL as $color) {
      switch ($countsByColor[$color]) {
        case 0:
        case 1:
          $newDirection = null;
        case 2:
        case 3:
          $newDirection = Directions::ASLANT;
          break;
        case 4:
        case 5:
          $newDirection = Directions::UP;
          break;
        case 6:
        case 7:
          $newDirection = Directions::RIGHT;
          break;
        default:
          $newDirection = Directions::LEFT;
          break;
      }
      if ($newDirection !== null && self::getSplayDirection($color) !== $newDirection) {
        $numColorsSplayed++;
        self::splay($color, $newDirection);
      }
    }
    if ($numColorsSplayed === 5) {
      self::win();
    }
  }

}
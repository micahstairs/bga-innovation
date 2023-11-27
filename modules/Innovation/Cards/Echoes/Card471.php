<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card471 extends AbstractCard
{

  // Touchscreen
  //   - Splay each color on your board in a direction according to the number of cards of that
  //     color on your board: two or three, aslant; four or five, up; six or seven, right; eight
  //     or more, left. If you splay five colors, you win.

  public function initialExecution()
  {
    $numColorsSplayed = 0;
    $countsByColor = self::countCardsKeyedByColor(Locations::BOARD);
    foreach (Colors::ALL as $color) {
      switch ($countsByColor[$color]) {
        case 0:
        case 1:
          $newDirection = null;
          break;
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
      if ($newDirection !== null && self::splay($color, $newDirection)) {
        $numColorsSplayed++;
      }
    }
    $args = ['i18n' => ['n'], 'n' => self::renderNumber($numColorsSplayed)];
    self::notifyPlayer(clienttranslate('${You} splayed ${n} colors.'), $args);
    self::notifyOthers(clienttranslate('${player_name} splayed ${n} colors.'), $args);
    if ($numColorsSplayed === 5) {
      self::win();
    }
  }

}
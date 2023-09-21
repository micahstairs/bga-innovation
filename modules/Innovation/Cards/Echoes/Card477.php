<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card477 extends AbstractCard
{

  // Esports
  //   - For each non-yellow top card on your board, draw and score a card of equal value, in
  //     ascending order. If you do, and Esports was foreseen, you win.

  public function initialExecution()
  {
    $values = [];
    foreach (Colors::NON_YELLOW as $color) {
      $topCard = self::getTopCardOfColor($color);
      if ($topCard) {
        $values[] = $topCard['faceup_age'];
      }
    }
    sort($values);
    foreach ($values as $value) {
      self::drawAndScore($value);
    }

    if ($values && self::wasForeseen()) {
      self::win();
    }
  }

}
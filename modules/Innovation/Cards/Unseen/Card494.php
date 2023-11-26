<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card494 extends AbstractCard
{

  // Symbology:
  //   - If you have at least four each of at least four icons on your board, draw a [4].
  //     Otherwise, if you have at least three each of at least three icons on your board,
  //     draw a [3]. Otherwise, if you have at least two each of at least two icons on your
  //     board, draw a [2].

  public function initialExecution()
  {
    $count2 = 0;
    $count3 = 0;
    $count4 = 0;
    foreach (self::getStandardIconCounts() as $icon => $count) {
      if ($count >= 2) {
        $count2++;
      }
      if ($count >= 3) {
        $count3++;
      }
      if ($count >= 4) {
        $count4++;
      }
    }

    if ($count4 >= 4) {
      self::draw(4);
    } elseif ($count3 >= 3) {
      self::draw(3);
    } elseif ($count2 >= 2) {
      self::draw(2);
    }
  }
}
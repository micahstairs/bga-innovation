<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card594 extends Card
{

  // Metaverse:
  //   - For each splayed color on your board, score its top card. If you score fewer than three
  //     cards, you lose.

  public function initialExecution()
  {
    $numScoredCards = 0;
    for ($color = 0; $color < 5; $color++) {
      $topCard = self::getTopCardOfColor($color);
      if ($topCard !== null && $topCard['splay_direction'] > 0) {
        self::score($topCard);
        $numScoredCards++;
      }
    }
    if ($numScoredCards < 3) {
      self::lose();
    }
  }

}
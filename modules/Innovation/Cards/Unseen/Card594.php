<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card594 extends AbstractCard
{

  // Metaverse:
  //   - For each splayed color on your board, score its top card. If you score fewer than three
  //     cards, you lose.

  public function initialExecution()
  {
    $numScoredCards = 0;
    foreach (self::getTopCards() as $card) {
      if ($card['splay_direction'] > 0) {
        self::score($card);
        $numScoredCards++;
      }
    }
    if ($numScoredCards < 3) {
      self::lose();
    }
  }

}
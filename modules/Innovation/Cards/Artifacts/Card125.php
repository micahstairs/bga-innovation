<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card125 extends Card
{

  // Seikilos Epitaph
  // - 3rd edition:
  //   - Draw and meld a [3]. Meld your bottom card of the drawn card's color. Execute its
  //     non-demand dogma effects. Do not share them.
  // - 4th edition:
  //   - Draw and meld a [3]. Meld your bottom card of the drawn card's color, then self-execute it.

  public function initialExecution()
  {
    $card = self::drawAndMeld(3);
    self::selfExecute(self::meld(self::getBottomCardOfColor($card['color'])));
  }

}
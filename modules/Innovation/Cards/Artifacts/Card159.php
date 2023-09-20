<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card159 extends Card
{
  // Barque-Longue La Belle
  //   - Draw and meld a [5]. If the drawn card is not green, repeat this effect.

  public function initialExecution()
  {
    do {
      $card = self::drawAndMeld(5);
    } while (!self::isGreen($card));
  }

}
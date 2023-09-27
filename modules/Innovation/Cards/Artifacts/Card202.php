<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card202 extends AbstractCard
{
  // Magnavox Odyssey
  //   - Draw and meld two [10]. If they are the same color, you win.

  public function initialExecution()
  {
    $card1 = self::drawAndMeld(10);
    $card2 = self::drawAndMeld(10);

    if ($card1['color'] == $card2['color']) {
      self::notifyAll(clienttranslate('Both melded cards were the same color.'));
      self::win();
    }
  }

}
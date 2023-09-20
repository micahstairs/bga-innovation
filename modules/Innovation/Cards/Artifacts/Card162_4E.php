<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card162_4E extends Card
{
  // The Daily Courant (4th edition):
  //   - Draw, reveal, and return a [6]. Draw and meld a card of value equal to the value of your
  //    top card of the same color as the returned card. Self-execute the melded card.

  public function initialExecution()
  {
    $card = self::drawAndReveal(6);
    $this->notifications->notifyCardColor($card['color']);
    self::return($card);
    $value = self::getTopCardOfColor($card['color'])['faceup_age'];
    self::selfExecute(self::drawAndMeld($value));
  }

}
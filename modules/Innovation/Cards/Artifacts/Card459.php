<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card459 extends Card
{

  // Oculus Quest
  //   - I COMPEL you to transfer all cards on your board to your hand!

  public function initialExecution()
  {
    // TODO(4E): Use bulk transfer.
    foreach (Colors::ALL as $color) {
      foreach (self::getStack($color) as $card) {
        self::transferToHand($card);
      }
    }
  }

}
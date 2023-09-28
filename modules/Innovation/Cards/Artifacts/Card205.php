<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;

class Card205 extends AbstractCard
{
  // Rover Curiosity
  // - 3rd edition:
  //   - Draw and meld an Artifact [10]. Execute the effects of the melded card as if they were on
  //     this card. Do not share them.
  // - 4th edition:
  //   - Draw and meld an Artifact [10], then self-execute it.

  public function initialExecution()
  {
    $card = self::drawAndMeldType(10, CardTypes::ARTIFACTS);
    if (self::isFirstOrThirdEdition()) {
      self::fullyExecute($card);
    } else {
      self::selfExecute($card);
    }
  }

}
<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card85 extends AbstractCard
{
  // Computers:
  // - 3rd edition:
  //   - You may splay your red cards or your green cards up.
  //   - Draw and meld a [10], then execute each of its non-demand effects. Do not share them.
  // - 4th edition:
  //   - You may splay your red or green cards up.
  //   - Draw and meld a [10], then self-execute it.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      $card = self::drawAndMeld(10);
      self::selfExecute($card);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->splayUp([Colors::RED, Colors::GREEN]);
  }

}
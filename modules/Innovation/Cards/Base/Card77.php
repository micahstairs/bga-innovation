<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card77 extends AbstractCard
{
  // Flight:
  //   - If your red cards are splayed up, you may splay any one color of your cards up.
  //   - You may splay your red cards up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() && self::isSplayedUp(Colors::RED)) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayUp();
    } else {
      return self::youMay()->splayUp(Colors::RED);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay(Colors::RED) || (self::isSplayedUp(Colors::RED) && self::canSplay(Colors::NON_RED));
  }

}
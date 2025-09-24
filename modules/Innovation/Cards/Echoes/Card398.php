<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card398 extends AbstractCard
{

  // Rubber
  // - 3rd edition
  //   - ECHO: Draw and tuck two [8].
  //   - Score a top card from your board without a bonus.
  //   - You may splay your red cards up.
  // - 4th edition
  //   - ECHO: Draw and tuck two [8].
  //   - Score a top card from your board without a bonus.
  //   - You may splay your red cards up.
  //   - If Rubber was foreseen, foreshadow a top card on your board.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(8);
      self::drawAndTuck(8);
    } else if (self::isFirstNonDemand() || self::isSecondNonDemand() || self::wasForeseen()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->score()->withoutBonus()->fromYourBoard()->build();
    } else if (self::isSecondNonDemand()) {
      return self::youMay()->splayUp(Colors::RED)->build();
    } else {
      return self::youMust()->foreshadow()->fromYourBoard()->build();
    }
  }

}
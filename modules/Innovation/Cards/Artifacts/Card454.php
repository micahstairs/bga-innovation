<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Icons;

class Card454 extends AbstractCard
{
  // Greenland
  //   - I COMPEL you to return one of your top cards with [EFFICIENCY]! If you do, repeat this effect.
  //   - Return one of your top cards with [PROSPERITY]. If you do, repeat this effect.

  public function getInteractionOptions(): InteractionBuilder
  {
    $icon = self::isCompel() ? Icons::EFFICIENCY : Icons::PROSPERITY;
    return self::youMust()->return()->fromYourBoard()->withIcon($icon);
  }

  public function handleCardChoice(array $card)
  {
    self::setNextStep(1);
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::EFFICIENCY)) {
        return true;
      }
    }
    return false;
  }

}
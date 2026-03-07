<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card128 extends AbstractCard
{

  // Babylonian Chronicles
  //   - I COMPEL you to transfer a top non-red card with [AUTHORITY] from your board to my board!
  //   - Draw and score a [3].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndScore(3);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->non(Colors::RED)->withIcon(Icons::AUTHORITY)->fromYourBoard()->toMine();
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::AUTHORITY) && !self::isRed($card)) {
        return true;
      }
    }
    return false;
  }

}
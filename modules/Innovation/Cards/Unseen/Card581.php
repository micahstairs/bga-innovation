<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card581 extends AbstractCard
{

  // Ride-Hailing:
  //   - You may splay your green cards up.
  //   - Meld a top non-yellow card with [EFFICIENCY] from another player's board. If you do, self-execute it. Otherwise, draw an [11].

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayUp(Colors::GREEN)->build();
    } else {
      return self::youMust()->meld()->non(Colors::YELLOW)->withIcon(Icons::EFFICIENCY)->fromAnyBoard()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      if (self::getNumChosen() === 1) {
        self::selfExecute(self::getLastSelectedCard());
      } else {
        self::draw(11);
      }
    }
  }

}
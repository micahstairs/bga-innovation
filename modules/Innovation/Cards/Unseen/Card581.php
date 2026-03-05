<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card581 extends AbstractCard
{

  // Ride-Hailing:
  //   - You may splay your green cards up.
  //   - Meld a top non-yellow card with [EFFICIENCY] from another player's board. If you do, self-execute it. Otherwise, draw an [11].

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayUp(Colors::GREEN);
    } else {
      return self::youMust()->meld()->non(Colors::YELLOW)->withIcon(Icons::EFFICIENCY)->fromAnyBoard();
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
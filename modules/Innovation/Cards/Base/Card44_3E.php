<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;
use Innovation\Enums\Icons;

class Card44_3E extends AbstractCard
{
  // Reformation (3rd edition):
  //   - You may tuck a card from your hand for every two [HEALTH] on your board.
  //   - You may splay your yellow or purple cards right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::HEALTH), 2);
      return self::youMay()->tuck()->exactly($numCards)->fromYourHand();
    } else {
      return self::youMay()->splayRight([Colors::YELLOW, Colors::PURPLE]);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::getStandardIconCount(Icons::HEALTH) >= 2 && self::hasCards(Locations::HAND)) {
      return true;
    }
    return self::canSplay([Colors::YELLOW, Colors::PURPLE]);
  }

}
<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card44_4E extends AbstractCard
{
  // Reformation (4th edition):
  //   - You may splay your yellow or purple cards right.
  //   - You may tuck a card from your hand for every splayed color on your board.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayRight([Colors::YELLOW, Colors::PURPLE]);
    } else {
      return self::youMay()->tuck()->exactly(self::countSplayedColors())->fromYourHand();
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::canSplay([Colors::YELLOW, Colors::PURPLE])) {
      return true;
    }
    return self::countSplayedColors() > 0 && self::hasCards(Locations::HAND);
  }

}
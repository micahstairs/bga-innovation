<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card64 extends AbstractCard
{
  // Emancipation:
  //   - I DEMAND you transfer a card from your hand to my score pile! If you do, draw a 6!
  //   - You may splay your red or purple cards right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->fromYourHand()->toMyScore();
    } else {
      return self::youMay()->splayRight([Colors::RED, Colors::PURPLE]);
    }
  }

  public function handleCardChoice(array $card)
  {
    self::draw(6);
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay([Colors::RED, Colors::PURPLE]);
  }

}
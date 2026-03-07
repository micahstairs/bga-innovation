<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card530 extends AbstractCard
{

  // Secret History:
  //   - I DEMAND you transfer one of your secrets to my safe!
  //   - If your red and purple cards are splayed right, claim the Mystery achievement. Otherwise,
  //     splay your red or purple cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      if (self::getSplayDirection(Colors::RED) == Directions::RIGHT && self::getSplayDirection(Colors::PURPLE) == Directions::RIGHT) {
        self::claim(CardIds::MYSTERY);
      } else {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->fromYourSafe()->toMySafe();
    } else {
      return self::youMust()->splayRight([Colors::RED, Colors::PURPLE]);
    }
  }

}
<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card1 extends AbstractCard
{

  // Tools:
  //   - You may return three cards from your hand. If you do, draw and meld a [3].
  //   - You may return a [3] from your hand. If you do, draw three [1].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'       => true,
        'return_keyword' => true,
        'n'              => 3,
        'location_from'  => Locations::HAND,
      ];
    } else {
      return [
        'can_pass'       => true,
        'return_keyword' => true,
        'age'            => 3,
        'location_from'  => Locations::HAND,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand() && self::getNumChosen() === 3) {
      self::drawAndMeld(3);
    } else if (self::isSecondNonDemand() && self::getNumChosen() > 0) {
      self::draw(1);
      self::draw(1);
      self::draw(1);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }
}
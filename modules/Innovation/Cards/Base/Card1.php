<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

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
        'n'              => 3,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'       => true,
        'location_from'  => 'hand',
        'return_keyword' => true,
        'age'            => 3,
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
}
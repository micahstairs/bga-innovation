<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Directions;

class Card199 extends AbstractCard
{
  // Philips Compact Cassette
  //   - I COMPEL you to unsplay all splayed colors on your board!
  //   - Splay up two colors on your board.

  public function initialExecution()
  {
    if (self::isCompel()) {
      foreach (Colors::ALL as $color) {
        self::unsplay($color);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'               => 2,
      'splay_direction' => Directions::UP,
    ];
  }

}
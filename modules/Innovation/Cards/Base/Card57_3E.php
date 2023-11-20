<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card57_3E extends AbstractCard
{
  // Industrialization (3rd edition):
  //   - Draw and tuck a [6] for every color on your board with one or more [INDUSTRY].
  //   - You may splay your red or purple cards right.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::INDUSTRY) > 0) {
          self::drawAndTuck(6);
        }
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::RIGHT,
      'color'           => [Colors::RED, Colors::PURPLE],
    ];
  }

}
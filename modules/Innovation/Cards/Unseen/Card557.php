<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card557 extends AbstractCard
{

  // Concealment:
  //   - I DEMAND you tuck all your secrets!
  //   - Safeguard your bottom purple card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::safeguard(self::getBottomCardOfColor(Colors::PURPLE));
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'             => 'all',
      'location_from' => 'safe',
      'tuck_keyword'  => true,
    ];
  }

}
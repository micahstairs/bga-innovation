<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card395 extends AbstractCard
{

  // Photography
  // - 3rd edition
  //   - ECHO: Meld a card from your forecast.
  //   - I DEMAND you take the highest top card from your board into your hand!
  //   - If you have at least three echo effects visible in one color, claim the History achievement.
  // - 4th edition
  //   - ECHO: Meld a card from your forecast.
  //   - I DEMAND you transfer your highest top card to your hand!
  //   - If you have at least three echo effects in one color, claim the History achievement. If you do,
  //     and Photography was foreseen, you win.

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::ECHO_EFFECT) >= 3) {
          if (self::claim(CardIds::HISTORY) && self::wasForeseen()) {
            self::win();
          }
          return;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'forecast',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => 'board',
        'location_to'   => 'hand',
        'age'           => self::getMaxValue(self::getTopCards()),
      ];
    }
  }

}
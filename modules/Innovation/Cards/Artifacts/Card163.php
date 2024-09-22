<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card163 extends AbstractCard
{
  // Sandham Room Cricket Bat
  // - 3rd edition:
  //   - Draw and reveal a [6]. If it is red, claim an achievement, ignoring eligibility.
  // - 4th edition:
  //   - Draw and reveal a [6]. If it is red, claim an available standard achievement, ignoring
  //     eligibility. Otherwise, junk an available standard achievement.

  public function getInteractionOptions(): array
  {
    $card = self::drawAndReveal(6);
    $this->notifications->notifyCardColor(self::getColor($card));
    self::transferToHand($card);
    if (self::isRed($card)) {
      return ['achieve_keyword' => true];
    } else if (self::isFirstOrThirdEdition()) {
      return [];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
      ];
    }
  }
}
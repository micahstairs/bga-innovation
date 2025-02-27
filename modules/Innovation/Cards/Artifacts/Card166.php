<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card166 extends AbstractCard
{
  // Puffing Billy
  // - 3rd edition:
  //   - Return a card from your hand. Draw a card of value equal to the highest number of symbols
  //     of the same type visible in that color on your board. Splay right that color.
  // - 4th edition:
  //   - Tuck a card from your hand. Splay right its color on your board. Draw a card of value
  //     equal to the highest number of icons of the same type in that color on your board.

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMust()->revealAndReturn()->fromYourHand()->build();
    } else {
      return self::youMust()->tuck()->fromYourHand()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFourthEdition()) {
      self::splayRight(self::getColor($card));
    }

    $countsByIcon = self::getAllIconCountsInStack(self::getColor($card));
    $maxCount = $countsByIcon ? max(array_values($countsByIcon)) : 0;
    self::draw($maxCount);

    if (self::isFirstOrThirdEdition()) {
      self::splayRight(self::getColor($card));
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }
}

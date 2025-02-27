<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card120 extends AbstractCard
{

  // Lurgan Canoe
  // - 3rd edition:
  //   - Meld a card from your hand. Score all other cards of the same color from your board. If
  //     you scored at least one card, repeat this effect.
  // - 4th edition:
  //   - Meld a card from your hand. Score all other cards of the same color from your board. If
  //     you score a card, repeat this effect.

  public function getInteractionOptions(): array
  {
    return self::youMust()->meld()->fromYourHand()->build();
  }

  public function handleCardChoice(array $meldedCard)
  {
    foreach (array_reverse(self::getStack(self::getColor($meldedCard))) as $card) {
      if (self::getId($card) != self::getId($meldedCard)) {
        self::score($card);
        self::setNextStep(1);
      }
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}
<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card71 extends AbstractCard
{
  // Refrigeration:
  // - 3rd edition:
  //   - I demand you return half (rounded down) of the cards in your hand!
  //   - You may score a card from your hand.
  // - 4th edition:
  //   - I demand you return all but one of the cards in your hand!
  //   - You may score a card from your hand.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $handCount = self::countCards(Locations::HAND);
      if (self::isFourthEdition()) {
        $numCardsToReturn = $handCount == 0 ? 0 : $handCount - 1;
      } else {
        $numCardsToReturn = $this->game->intDivision($handCount, 2);
      }
      return self::youMust()->return()->exactly($numCardsToReturn)->fromYourHand()->build();
    } else {
      return self::youMay()->score()->fromYourHand()->build();
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}
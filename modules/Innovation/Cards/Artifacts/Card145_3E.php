<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card145_3E extends AbstractCard
{

  // Petition of Right (3rd edition):
  //   - I COMPEL you to transfer a card from your score pile to my score pile for each top card
  //     with a [AUTHORITY] on your board!


  public function getInteractionOptions(): array
  {
    $numTopCardsWithAuthority = 0;
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        $numTopCardsWithAuthority++;
      }
    }
    return self::youMust()->exactly($numTopCardsWithAuthority)->fromYourScore()->toMine()->build();
  }

  public function compelMightBeEffective(): bool
  {
    $hasIcon = false;
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        $hasIcon = true;
        break;
      }
    }

    return $hasIcon && self::hasCards(Locations::HAND);
  }

}
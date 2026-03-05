<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card150_4E extends AbstractCard
{

  // Hunt-Lenox Globe (4th edition):
  //   - If you have fewer than four cards in your hand, return your top card of each non-green
  //     color. Draw a [5] for each card you return. 
  //   - Meld a card from your hand.


  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::countCards(Locations::HAND) < 4) {
        return self::youMust()->return()->all()->non(Colors::GREEN)->fromYourBoard();
      } else {
        return [];
      }
    } else {
      return self::youMust()->meld()->fromYourHand();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      for ($i = 0; $i < self::getNumChosen(); $i++) {
        self::draw(5);
      }
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::hasCards(Locations::HAND)) {
      return true;
    }
    return self::countCards(Locations::HAND) < 4 && count(self::filterByColor(self::getCards(Locations::BOARD), Colors::NON_GREEN)) > 0;
  }

}
<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card150_3E extends AbstractCard
{

  // Hunt-Lenox Globe (3rd edition):
  //   - If you have fewer than four cards in your hand, return all non-green top cards from your
  //     board. Draw a [5] for each card returned. Meld a card from your hand.


  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      if (self::countCards(Locations::HAND) < 4) {
        return self::youMust()->return()->all()->non(Colors::GREEN)->fromYourBoard()->build();
      } else {
        return [];
      }
    } else {
      return self::youMust()->meld()->fromYourHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
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
    return self::countCards(Locations::BOARD) < 4 && count(self::filterByColor(self::getCards(Locations::BOARD), Colors::NON_GREEN)) > 0;
  }

}
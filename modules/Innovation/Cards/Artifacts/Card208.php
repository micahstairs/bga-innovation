<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card208 extends AbstractCard
{
  // Maldives
  //   - I COMPEL you to return all cards in your hand but two! Return all cards in your score pile but two!
  //   - Return all cards in your score pile but four.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      if (self::isFirstInteraction()) {
        $n = self::countCards(Locations::HAND) - 2;
        return self::youMust()->return()->exactly($n)->fromYourHand()->build();
      } else {
        $n = self::countCards(Locations::SCORE) - 2;
        return self::youMust()->return()->exactly($n)->fromYourScore()->build();
      }
    } else {
      $n = self::countCards(Locations::SCORE) - 4;
      return self::youMust()->return()->exactly($n)->fromYourScore()->build();
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::HAND) > 2 || self::countCards(Locations::SCORE) > 2;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::countCards(Locations::SCORE) > 4;
  }

}

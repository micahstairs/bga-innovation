<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card84_4E extends AbstractCard
{
  // Socialism (4th edition):
  //   - You may tuck a top card from your board. If you do, tuck all cards from your hand.
  //   - You may junk an available achievement of value [8], [9], or [10].

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMay()->tuck()->fromYourBoard()->build();
      } else {
        return self::youMust()->tuck()->all()->fromYourHand()->build();
      }
    } else {
      return self::youMay()->junk()->fromAvailableAchievements()->range(8, 10)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    $cardsToJunk = self::filterByValue(self::getCards(Locations::AVAILABLE_ACHIEVEMENTS), [8, 9, 10]);
    return self::hasCards(Locations::BOARD) || count($cardsToJunk) > 0;
  }

}
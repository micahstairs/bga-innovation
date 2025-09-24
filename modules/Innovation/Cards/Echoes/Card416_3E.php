<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card416_3E extends AbstractCard
{

  // Laser (3rd edition):
  //   - Return all unclaimed standard achievements. Then, return half (rounded up) of the cards in
  //     your score pile. Draw and meld two [10].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->all()->fromAvailableAchievements()->build();
    } else {
      $numCards = ceil(self::countCards(Locations::SCORE) / 2);
      return self::youMust()->return()->exactly($numCards)->fromYourScore()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::drawAndMeld(10);
      self::drawAndMeld(10);
    }
  }

}
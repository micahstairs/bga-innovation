<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card449 extends AbstractCard
{

  // Whataboutism:
  //   - I DEMAND you transfer a top card with a demand effect of each color from your board to my
  //     board! If you transfer any cards, exchange all cards in your score pile with all cards in
  //     my score pile!

  public function getInteractionOptions(): array
  {
    return self::youMust()->all()->withDemandEffect()->fromYourBoard()->toMine()->build();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      $launcherScoreCards = self::getCards(Locations::SCORE, self::getLauncherId());
      $playerScoreCards = self::getCards(Locations::SCORE, self::getPlayerId());
      foreach ($launcherScoreCards as $card) {
        self::transferToScorePile($card, self::getPlayerId());
      }
      foreach ($playerScoreCards as $card) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
  }

  public function demandMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasDemandEffect($card)) {
        return true;
      }
    }
    return false;
  }

}
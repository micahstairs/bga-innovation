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

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'                 => 'all',
      'owner_from'        => self::getPlayerId(),
      'location_from'     => Locations::BOARD,
      'owner_to'          => self::getLauncherId(),
      'location_to'       => Locations::BOARD,
      'has_demand_effect' => true,
    ];
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

}
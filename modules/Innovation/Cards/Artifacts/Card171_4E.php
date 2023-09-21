<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card171_4E extends AbstractCard
{
  // Qianlong's Dragon Robe (4th edition):
  //   - I COMPEL you to transfer your top red card to my score pile! Transfer your top green card
  //     to my board! Transfer a yellow card from your score pile to mine! Transfer a purple card
  //     from your score pile to my hand!

  public function initialExecution()
  {
    self::transferToScorePile(self::getTopCardOfColor(Colors::RED), self::getLauncherId());
    self::transferToBoard(self::getTopCardOfColor(Colors::GREEN), self::getLauncherId());
    foreach (self::getCards(Locations::SCORE) as $card) {
      self::reveal($card);
    }
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::REVEALED,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::SCORE,
        'color'         => [Colors::YELLOW],
      ];
    } else {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::REVEALED,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::HAND,
        'color'         => [Colors::PURPLE],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      foreach (self::getCards(Locations::REVEALED) as $card) {
        self::transferToScorePile($card);
      }
    }
  }

}
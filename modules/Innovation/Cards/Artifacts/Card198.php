<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card198 extends AbstractCard
{
  // Velcro Shoes
  // - 3rd edition:
  //   - I COMPEL you to transfer a [9] from your hand to my hand! If you do not, transfer a [9]
  //     from your score pile to my score pile! If you do neither, I win!
  // - 4th edition:
  //   - I COMPEL you to transfer a [9] from your hand to my hand! If you don't, transfer a [9]
  //     from your score pile to my score pile! If you do neither, I win!
  //   - Score your highest top card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      if (self::isFirstInteraction()) {
        return [
          'owner_from'    => self::getPlayerId(),
          'location_from' => Locations::HAND,
          'owner_to'      => self::getLauncherId(),
          'location_to'   => Locations::HAND,
          'age'           => 9,
        ];
      } else {
        return [
          'owner_from'    => self::getPlayerId(),
          'location_from' => Locations::SCORE,
          'owner_to'      => self::getLauncherId(),
          'location_to'   => Locations::SCORE,
          'age'           => 9,
        ];
      }
    } else {
      return [
        'location_from' => Locations::BOARD,
        'age'           => self::getMaxValue(self::getTopCards()),
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() == 0) {
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction() && self::getNumChosen() == 0) {
      self::notifyAll(clienttranslate('Neither transfer took place.'));
      self::win(self::getLauncherId());
    }
  }

}
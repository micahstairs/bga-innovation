<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Locations;

class Card17 extends AbstractCard
{
  // Construction
  //   - I DEMAND you transfer two cards from your hand to my hand! Draw a [2]!
  //   - If you are the only player with five top cards, claim the Empire achievement.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isAvailableAchievement(CardIds::EMPIRE) && self::isOnlyOneWithFiveTopCards()) {
      self::claim(CardIds::EMPIRE);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'          => 2,
      'location'   => Locations::HAND,
      'owner_from' => self::getPlayerId(),
      'owner_to'   => self::getLauncherId(),
    ];
  }

  public function afterInteraction()
  {
    self::draw(2);
  }

  private function isOnlyOneWithFiveTopCards(): bool
  {
    if (count(self::getTopCards()) < 5) {
      return false;
    }

    foreach (self::getOtherPlayerIds() as $otherPlayerId) {
      if (count(self::getTopCards($otherPlayerId)) == 5) {
        return false;
      }
    }

    return true;
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}
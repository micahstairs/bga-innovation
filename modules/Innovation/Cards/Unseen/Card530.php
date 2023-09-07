<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card530 extends Card
{

  // Secret History:
  //   - I DEMAND you transfer one of your secrets to my safe!
  //   - If your red or purple cards are splayed right, claim the Mystery achievement. Otherwise,
  //     splay your red or purple cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      if (self::getSplayDirection(Colors::RED) == Directions::RIGHT || self::getSplayDirection(Colors::PURPLE) == Directions::RIGHT) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), CardIds::MYSTERY);
      } else {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'safe',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'safe',
      ];
    } else {
      return [
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::RED, Colors::PURPLE],
      ];
    }
  }

}
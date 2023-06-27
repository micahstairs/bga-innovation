<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card530 extends Card
{

  // Secret History:
  //   - I DEMAND you transfer one of your secrets to my safe!
  //   - Splay your red or purple cards right. If you don't, claim the Mystery achievement.

  public function initialExecution()
  {
    self::setMaxSteps(1);
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
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::RED, $this->game::PURPLE],
      ];
    }
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::selfExecute(self::getTopCardOfColor($choice));
    self::setAuxiliaryValue($choice);
  }

  public function afterInteraction(): void
  {
    if (self::isNonDemand() && self::getNumChosen() == 0) {
      $this->game->claimSpecialAchievement(self::getPlayerId(), 599);
    }
  }

}
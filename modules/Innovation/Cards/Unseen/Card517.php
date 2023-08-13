<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card517 extends Card
{

  // Ninja:
  //   - I demand you return a card of the color of my choice from your hand! 
  //     If you do, transfer the top card of that color from your board to mine!
  //   - You may splay your red cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'player_id'    => self::getLauncherId(),
          'choose_color' => true,
        ];
      } else {
        return [
          'location_from' => 'hand',
          'location_to'   => 'deck',
          'color'         => [self::getAuxiliaryValue()],
        ];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::RED],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      if (self::getNumChosen() > 0) {
        self::transferToBoard(self::getTopCardOfColor(self::getAuxiliaryValue()), self::getLauncherId());
      } else {
        self::revealHand();
      }
    }
  }

  public function handleSpecialChoice(int $color): void
  {
    $this->notifications->notifyColorChoice($color, self::getLauncherId());
    self::setAuxiliaryValue($color);
  }

}
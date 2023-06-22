<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card517 extends Card
{

  // Ninja:
  //   - I demand you return a card of the color of my choice from your hand! 
  //     If you do, transfer the top card of that color from your board to mine!
  //   - You may splay your red cards right.

  public function initialExecution(ExecutionState $state)
  {
    if (self::isDemand()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::isDemand()) {
      if (self::getCurrentStep() == 1) {
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

  public function afterInteraction(Executionstate $state)
  {
    if (self::isDemand() && self::getNumChosen() > 0) {
      self::transferToBoard(self::getTopCardOfColor(self::getAuxiliaryValue()), self::getLauncherId());
    }
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

}
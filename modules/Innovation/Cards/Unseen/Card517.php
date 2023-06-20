<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card517 extends Card
{

  // Ninja:
  //   - I demand you return a card of the color of my choice from your hand! 
  //     If you do, transfer the top card of that color from your board to mine!
  //   - You may splay your red cards right.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->isDemand()) {
      $state->setMaxSteps(2);
    } else {
      $state->setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->isDemand()) {
      if ($state->getCurrentStep() == 1) {
        return [
          'player_id'    => $state->getLauncherId(),
          'choose_color' => true,
        ];
      } else {
        return [
          'location_from' => 'hand',
          'location_to'   => 'deck',
          'color'         => array($this->game->getAuxiliaryValue()),
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
    if ($state->isDemand() && $state->getNumChosen() > 0) {
      $topCard = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
      if ($topCard !== null) {
        $this->game->transferCardFromTo($topCard, $state->getLauncherId(), 'board');
      }
    }
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $this->game->setAuxiliaryValue($choice);
  }

}
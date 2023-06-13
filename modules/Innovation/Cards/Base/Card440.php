<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card440 extends Card
{

  // Climatology:
  //   - I DEMAND you return two top cards from your board each with the icon of my choice other
  //     than [HEALTH]!
  //   - Return a top card from your board. Return all cards in your score pile of equal or higher
  //     value than the top card.

  public function initialExecution(ExecutionState $state)
  {
    $state->setMaxSteps($state->isDemand() ? 3 : 2);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->isDemand()) {
      if ($state->getCurrentStep() == 1) {
        return [
          'player_id'        => $state->getLauncherId(),
          'choose_icon_type' => true,
          'icon'             => [1, 3, 4, 5, 6, 7],
        ];
      } else {
        return [
          'location_from' => 'board',
          'location_to'   => 'deck',
          'with_icon'     => $this->game->getAuxiliaryValue(),
        ];
      }
    }
    if ($state->getCurrentStep() == 1) {
      return [
        'location_from' => 'board',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'location_from' => 'score',
        'location_to'   => 'deck',
        'age_min'       => $this->game->getAuxiliaryValue(),
        'n'             => 'all',
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isNonDemand() && $state->getCurrentStep() == 1) {
      $minAgeToReturn = 0;
      if ($state->getNumChosen() > 0) {
        $minAgeToReturn = $this->game->innovationGameState->get('age_last_selected');
      }
      $this->game->setAuxiliaryValue($minAgeToReturn);
    }
  }

  public function handleSpecialChoice(Executionstate $state, int $chosenIcon): void
  {
    $this->notifications->motifyIconChoice($chosenIcon, $state->getPlayerId());
    $this->game->setAuxiliaryValue($chosenIcon);
  }
}
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
        // "I demand you return a card of the color of my choice from your hand!"
        return [
          'player_id'     => $state->getPlayerId(),
          'location_from' => 'hand',
          'location_to'   => 'deck',
          
          'color'         => array($this->game->getAuxiliaryValue()),
        ];
      }
    } else {
      // "You may splay your red cards right."
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => array($this->game::RED),
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isDemand()) {
        if ($state->getNumChosen() > 0) { // "If you do"
            // "transfer the top card of that color from your board to mine!"
            $top_card_of_color = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
            if ($top_card_of_color !== null) {
                $this->game->transferCardFromTo($top_card_of_color, $state->getLauncherId(), 'board');
            }
        }
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose a color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color'),
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $this->game->setAuxiliaryValue($choice);
  }

}
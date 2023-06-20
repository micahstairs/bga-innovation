<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card538 extends Card
{

  // Sniping:
  //   - I demand you unsplay the color on your board of my choice! 
  //     Meld your bottom card of that color! 
  //     Transfer your bottom card of that color to my board!

  public function initialExecution(ExecutionState $state)
  {
    if ($state->isDemand()) {
      $state->setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->isDemand()) {
      // "of my choice!"
      if ($state->getCurrentStep() == 1) {
        return [
          'player_id'    => $state->getLauncherId(),
          'choose_color' => true,
        ];
      } 
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isDemand()) {
        // " I demand you unsplay the color on your board"
        $this->game->unsplay($state->getPlayerId(), $state->getPlayerId(), $this->game->getAuxiliaryValue());
        // "Meld your bottom card of that color!"
        $bottom_card = $this->game->getBottomCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
        if ($bottom_card !== null) {
            $this->game->transferCardFromTo($bottom_card, $state->getPlayerId(), 'board');
        }
        // "Transfer your bottom card of that color to my board!"
        $bottom_card = $this->game->getBottomCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
        if ($bottom_card !== null) {
            $this->game->transferCardFromTo($bottom_card, $state->getLauncherId(), 'board');
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
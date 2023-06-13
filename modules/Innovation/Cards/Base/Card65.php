<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card65 extends Card
{

  // Evolution:
  //   - You may choose to either draw and score an [8] and then return a card from your score pile, or draw a card of value one higher than the highest card in your score pile.

  public function initialExecution(ExecutionState $state)
  {
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'can_pass'         => true,
        'choose_yes_or_no' => true,
      ];
    } else {
      return [
        'location_from' => 'score',
        'location_to'   => 'deck'
      ];
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    $player_id = $state->getPlayerId();
    $age_to_score = $this->game->getAgeToDrawIn($player_id, 8);
    $age_to_draw = $this->game->getAgeToDrawIn($player_id, $this->game->getMaxAgeInScore($player_id) + 1);
    $max_age = $this->game->getMaxAge();
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $age_to_score <= $max_age ? clienttranslate('Draw and score a ${age}, then return a card from your score pile') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($age_to_score)
        ],
        [
          'value' => 0,
          'text'  => $age_to_draw <= $max_age ? clienttranslate('Draw a ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($age_to_draw)
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $player_id = $state->getPlayerId();
    if ($choice === 0) {
      $this->game->executeDraw($player_id, $this->game->getMaxAgeInScore($player_id) + 1);
    } else {
      $this->game->executeDrawAndScore($player_id, 8);
      $state->setMaxSteps(2);
    }
  }

}
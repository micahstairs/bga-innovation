<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card533 extends Card
{

  // Pantheism:
  //   - Tuck a card from your hand. If you do, draw and tuck a [4], score all cards on your board
  //     of the color of one of the tucked cards, and splay right the color on your board of the
  //     other tucked card.
  //   - Draw and tuck a [4].

  public function initialExecution(ExecutionState $state)
  {
    if ($state->getEffectNumber() == 1) {
      $state->setMaxSteps(1);
    } else {
      $this->game->executeDrawAndTuck($state->getPlayerId(), 4);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'board',
        'bottom_to'     => true,
      ];
    } else {
      return [
        'choose_color' => true,
        'color'        => $this->game->getAuxiliaryArray(),
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    $tuckedCard1 = $this->game->getCardInfo($cardId);
    $tuckedCard2 = $this->game->executeDrawAndTuck($state->getPlayerId(), 4);
    $this->game->setAuxiliaryArray([$tuckedCard1['color'], $tuckedCard2['color']]);
    $state->setMaxSteps(2);
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose a color and score all cards on your board of that color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color and score all cards on his board of that color'),
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    while (($card = $this->game->getTopCardOnBoard($state->getPlayerId(), $choice)) !== null) {   
      $this->game->scoreCard($card, $state->getPlayerId());
    }
    $colors = $this->game->getAuxiliaryArray();
    $remainingColor = $colors[0] == $choice ? $colors[1] : $colors[0];
    $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), $remainingColor);
  }

}
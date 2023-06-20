<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card537 extends Card
{

  // Red Herring:
  //   - Splay your red cards left, right, or up.
  //   - Draw and tuck a [6]. If the color on your board of the 
  //     card you tuck is splayed in the same direction as your red 
  //     cards, splay that color up.  Otherwise, unsplay that color.

  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        // TODO: third choice needed
        $state->setMaxSteps(1);
        break;
      case 2:
        // "Draw and tuck a [6]."
        $card = $this->game->executeDrawAndTuck($state->getPlayerId(), 6);
        // "If the color on your board of the card you tuck is splayed in the 
        // same direction as your red cards, splay that color up."
        if ($this->game->getCurrentSplayDirection($state->getPlayerId(), $this->game::RED) == 
            $this->game->getCurrentSplayDirection($state->getPlayerId(), $card['color'])) {
          $this->game->splayUp($state->getPlayerId(), $state->getPlayerId(), $card['color']);
        } else {
            // "Otherwise, unsplay that color."
          $this->game->unsplay($state->getPlayerId(), $state->getPlayerId(), $card['color']);
        }
        break;
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
      if ($state->getEffectNumber() == 1) {
        return [
          'choose_yes_or_no' => true,
        ];
      }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Splay red left'),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Splay red right'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    if ($choice === 1) {
      $this->game->splayLeft($state->getPlayerId(), $state->getPlayerId(), 1);
    } else {
      $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 1);
    }
  }
}
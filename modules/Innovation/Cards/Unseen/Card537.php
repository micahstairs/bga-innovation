<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card537 extends Card
{

  // Red Herring:
  //   - Splay your red cards left, right, or up.
  //   - Draw and tuck a [6]. If the color on your board of the card you tuck is splayed in the
  //     same direction as your red  cards, splay that color up. Otherwise, unsplay that color.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->getEffectNumber() == 1) {
      self::setMaxSteps(1);
    } else {
      $card = self::drawAndTuck(6);
      if (
        $this->game->getCurrentSplayDirection($state->getPlayerId(), $this->game::RED) ==
        $this->game->getCurrentSplayDirection($state->getPlayerId(), $card['color'])
      ) {
        $this->game->splayUp($state->getPlayerId(), $state->getPlayerId(), $card['color']);
      } else {
        self::unsplay($card['color']);
      }
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    return ['choose_yes_or_no' => true];
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    // TODO(4E): A third choice is needed.
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
      self::splayLeft($this->game::RED);
    } else {
      self::splayRight($this->game::RED);
    }
  }
}
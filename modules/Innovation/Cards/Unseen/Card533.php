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
    if (self::getEffectNumber() == 1) {
      self::setMaxSteps(1);
    } else {
      self::drawAndTuck(4);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::getCurrentStep() == 1) {
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
    $tuckedCard1 = self::getCard($cardId);
    $tuckedCard2 = self::drawAndTuck(4);
    $this->game->setAuxiliaryArray([$tuckedCard1['color'], $tuckedCard2['color']]);
    self::setMaxSteps(2);
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
    while (($card = self::getTopCardOfColor($choice)) !== null) {   
      self::score($card);
    }
    $colors = $this->game->getAuxiliaryArray();
    $remainingColor = $colors[0] == $choice ? $colors[1] : $colors[0];
    self::splayRight($remainingColor);
  }

}
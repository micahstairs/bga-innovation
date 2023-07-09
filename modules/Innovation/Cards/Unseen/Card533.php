<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card533 extends Card
{

  // Pantheism:
  //   - Tuck a card from your hand. If you do, draw and tuck a [4], score all cards on your board
  //     of the color of one of the tucked cards, and splay right the color on your board of the
  //     other tucked card.
  //   - Draw and tuck a [4].

  public function initialExecution()
  {
    if (self::getEffectNumber() == 1) {
      self::setMaxSteps(1);
    } else {
      self::drawAndTuck(4);
    }
  }

  public function getInteractionOptions(): array
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

  public function handleCardChoice(array $tuckedCard1)
  {
    $tuckedCard2 = self::drawAndTuck(4);
    if ($tuckedCard1['color'] == $tuckedCard2['color']) {
        while (($card = self::getTopCardOfColor($tuckedCard1['color'])) !== null) {   
            self::score($card);
        }
    } else {
        $this->game->setAuxiliaryArray([$tuckedCard1['color'], $tuckedCard2['color']]);
        self::setMaxSteps(2);
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose a color and score all cards on your board of that color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color and score all cards on his board of that color'),
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    while (($card = self::getTopCardOfColor($choice)) !== null) {   
      self::score($card);
    }
    $colors = $this->game->getAuxiliaryArray();
    $remainingColor = $colors[0] == $choice ? $colors[1] : $colors[0];
    self::splayRight($remainingColor);
  }

}
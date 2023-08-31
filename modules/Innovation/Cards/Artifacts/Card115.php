<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card115 extends Card
{

  // Pavlovian Tusk
  //   - Draw three cards of value equal to your top green card. Return one of the drawn cards.
  //     Score one of the drawn cards.

  public function initialExecution()
  {
    $card = self::getTopCardOfColor($this->game::GREEN);
    $value = 0;
    if ($card) {
      $value = $card["faceup_age"];
    }
    $cardIds = [];
    for ($i = 0; $i < 3; $i++) {
      $card = self::draw($value);
      $cardIds[] = $card['id'];
    }
    self::setAuxiliaryArray($cardIds);
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    $keyword = self::isFirstInteraction() ? 'return_keyword' : 'score_keyword';
    return [
      'location_from' => 'hand',
      $keyword => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

}
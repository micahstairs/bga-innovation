<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card567 extends Card
{

  // Iron Curtain:
  //   - Unsplay each splayed color on your board. For each color you unsplay, return your top
  //     card of that color and safeguard an available standard achievement.

  public function initialExecution()
  {
    $colors = [];
    foreach ($this->game->getTopCardsOnBoard(self::getPlayerId()) as $card) {
      if ($card['splay_direction'] > 0) {
        $colors[] = $card['color'];
        self::unsplay($card['color']);
      }
    }
    if (count($colors) > 0) {
      $this->game->setAuxiliaryArray($colors);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'n'             => 'all',
        'location_from' => 'board',
        'location_to'   => 'deck',
        'color'         => $this->game->getAuxiliaryArray(),
      ];
    } else {
      return [
        'n'             => count($this->game->getAuxiliaryArray()),
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
      ];
    }
  }

}
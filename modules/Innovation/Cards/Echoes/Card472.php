<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card472 extends Card
{

  // Drone
  //   - Reveal a card in your hand. If you have fewer than five cards of that color on your board,
  //     splay that color aslant on your board. Otherwise, return the bottom four cards of that
  //     color from all boards. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'hand',
      'location_to'   => 'revealed',
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::transferToHand($card);
    $color = $card['color'];
    if (self::countCardsKeyedByColor('board')[$color] < 5) {
      self::splayAslant($color);
    } else {
      foreach (self::getPlayerIds() as $playerId) {
        foreach (self::getStack($color, $playerId) as $card) {
          if ($card['position'] < 4) {
            self::return($card);
            self::setNextStep(1);
          }
        }
      }
    }
  }

}
<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card458 extends Card
{

  // Jumbo Kingdom
  //   - Choose a color on your board. Junk all cards of that color from all boards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_color' => true,
      'color'        => self::getUniqueColors(Locations::BOARD),
    ];
  }

  public function handleSpecialChoice(int $color)
  {
    foreach (self::getPlayerIds() as $playerId) {
      $stack = self::getCardsKeyedByColor(Locations::BOARD, $playerId)[$color];
      foreach (array_reverse($stack) as $card) {
        self::junk($card);
      }
    }
  }

}
<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card157 extends Card
{
  // - Bill of Rights (3rd edition):
  //   - I COMPEL you to choose a color where you have more visible cards than I do! Transfer all
  //     cards of that color from your board to my board, from the bottom up!
  // - Galley Whydah (4th edition):
  //   - I COMPEL you to choose a color of which there are more visible cards on your board than on
  //     my board! From the bottom up, transfer all cards of that color from my board to my score
  //     pile, then from your board to my board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $colors = [];
    foreach (Colors::ALL as $color) {
      if (self::countVisibleCardsInStack($color, self::getPlayerId()) > self::countVisibleCardsInStack($color, self::getLauncherId())) {
        $colors[] = $color;
      }
    }
    return [
      'choose_color' => true,
      'color' => $colors,
    ];
  }

  public function handleSpecialChoice(int $color) {
    if (self::isFourthEdition()) {
      while ($card = self::getBottomCardOfColor($color, self::getLauncherId())) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
    while ($card = self::getBottomCardOfColor($color, self::getPlayerId())) {
      self::transferToBoard($card, self::getLauncherId());
    }
  }
}
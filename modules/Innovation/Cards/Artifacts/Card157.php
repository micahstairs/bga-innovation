<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card157 extends AbstractCard
{
  // - Bill of Rights (3rd edition):
  //   - I COMPEL you to choose a color where you have more visible cards than I do! Transfer all
  //     cards of that color from your board to my board, from the bottom up!
  // - Galley Whydah (4th edition):
  //   - I COMPEL you to choose a color of which there are more visible cards on your board than on
  //     my board! From the bottom up, transfer all cards of that color from my board to my score
  //     pile, then from your board to my board!

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->chooseColor(self::getEligibleColors());
  }

  public function handleColorChoice(int $color)
  {
    if (self::isFourthEdition()) {
      while ($card = self::getBottomCardOfColor($color, self::getLauncherId())) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
    while ($card = self::getBottomCardOfColor($color, self::getPlayerId())) {
      self::transferToBoard($card, self::getLauncherId());
    }
  }

  private function getEligibleColors(): array
  {
    $colors = [];
    foreach (Colors::ALL as $color) {
      if (self::countVisibleCardsInStack($color, self::getPlayerId()) > self::countVisibleCardsInStack($color, self::getLauncherId())) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

  public function compelMightBeEffective(): bool
  {
    return count($this->getEligibleColors()) > 0;
  }

}
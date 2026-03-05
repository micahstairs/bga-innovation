<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Directions;

class Card538 extends AbstractCard
{

  // Sniping:
  //   - I DEMAND you unsplay the color on your board of my choice! Meld your bottom card of that
  //     color! Transfer your bottom non-top card of that color to my board!

  public function getInteractionOptions(): InteractionBuilder
  {
    $colors = [];
    foreach (self::getTopCards() as $card) {
      $colors[] = self::getColor($card);
    }
    return self::youMust()->chooseColor($colors)->ofMyChoice();
  }

  public function handleColorChoice(int $color): void
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    self::unsplay($color);
    self::meld(self::getBottomCardOfColor($color));
    if (self::getPosition(self::getTopCardOfColor($color)) > 0) {
      self::transferToBoard(self::getBottomCardOfColor($color), self::getLauncherId());
    }
  }

}
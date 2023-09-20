<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

class Card538 extends Card
{

  // Sniping:
  //   - I DEMAND you unsplay the color on your board of my choice! Meld your bottom card of that
  //     color! Transfer your bottom non-top card of that color to my board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $colors = $this->game->getSplayableColorsOnBoard(self::getPlayerId(), Directions::UNSPLAYED);
    return [
      'player_id'    => self::getLauncherId(),
      'choose_color' => true,
      'color'        => $colors,
    ];
  }

  public function handleColorChoice(int $color): void
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    self::unsplay($color);
    self::meld(self::getBottomCardOfColor($color));
    if (self::getTopCardOfColor($color)['position'] > 0) {
      self::transferToBoard(self::getBottomCardOfColor($color), self::getLauncherId());
    }
  }

}
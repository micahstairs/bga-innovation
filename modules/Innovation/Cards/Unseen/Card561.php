<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card561 extends Card
{

  // Jackalope:
  //   - I DEMAND you transfer the highest card on your board without a [EFFICIENCY] to my board!
  //     If you do, unsplay the transferred card's color on your board!
  //   - Unsplay the color on your board with the most visible cards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from' => 'board',
        'owner_from'    => self::getPlayerId(),
        'location_to'   => 'board',
        'owner_to'      => self::getLauncherId(),
        'without_icon'  => Icons::EFFICIENCY,
        'age'           => $this->game->getMaxAgeOnBoardOfColorsWithoutIcon(self::getPlayerId(), Colors::ALL, Icons::EFFICIENCY),
      ];
    } else {
      return [
        'splay_direction' => Directions::UNSPLAYED,
        'color'           => self::getColorsWithMostVisibleCards(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::unsplay($card['color']);
  }

  private function getColorsWithMostVisibleCards(): array
  {
    $mostVisibleCards = 0;
    foreach (Colors::ALL as $color) {
      $numVisibleCards = $this->game->countVisibleCards(self::getPlayerId(), $color);
      if ($numVisibleCards > $mostVisibleCards) {
        $mostVisibleCards = $numVisibleCards;
      }
    }
    $colors = [];
    foreach (Colors::ALL as $color) {
      $numVisibleCards = $this->game->countVisibleCards(self::getPlayerId(), $color);
      // TODO(LATER): Move this optimization to a more central place (if no color has more than
      // one card, then the unsplay is a no-op).
      if ($numVisibleCards > 1 && $numVisibleCards === $mostVisibleCards) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

}
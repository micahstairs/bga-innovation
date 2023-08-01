<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
        'without_icon'  => $this->game::EFFICIENCY,
        'age'           => $this->game->getMaxAgeOnBoardOfColorsWithoutIcon(self::getPlayerId(), [0, 1, 2, 3, 4], $this->game::EFFICIENCY),
      ];
    } else {
      return [
        'splay_direction' => $this->game::UNSPLAYED,
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
    for ($color = 0; $color < 5; $color++) {
      $numVisibleCards = $this->game->countVisibleCards(self::getPlayerId(), $color);
      if ($numVisibleCards > $mostVisibleCards) {
        $mostVisibleCards = $numVisibleCards;
      }
    }
    $colors = [];
    for ($color = 0; $color < 5; $color++) {
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
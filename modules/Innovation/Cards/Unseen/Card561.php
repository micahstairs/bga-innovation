<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card561 extends AbstractCard
{

  // Jackalope:
  //   - I DEMAND you transfer the highest card on your board without [EFFICIENCY] to my board!
  //     If you do, unsplay the transferred card's color on your board!
  //   - Unsplay the color on your board with the most visible cards.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      $value = $this->game->getMaxAgeOnBoardOfColorsWithoutIcon(self::getPlayerId(), Colors::ALL, Icons::EFFICIENCY);
      return self::youMust()->value($value)->withIcon(Icons::EFFICIENCY)->fromYourBoard()->toMine();
    } else {
      return self::youMust()->unsplay(self::getColorsWithMostVisibleCards());
    }
  }

  public function handleCardChoice(array $card)
  {
    self::unsplay(self::getColor($card));
  }

  private function getColorsWithMostVisibleCards(): array
  {
    $mostVisibleCards = 0;
    foreach (Colors::ALL as $color) {
      $numVisibleCards = self::countVisibleCardsInStack($color);
      if ($numVisibleCards > $mostVisibleCards) {
        $mostVisibleCards = $numVisibleCards;
      }
    }
    $colors = [];
    foreach (Colors::ALL as $color) {
      $numVisibleCards = self::countVisibleCardsInStack($color);
      // TODO(LATER): Move this optimization to a more central place (if no color has more than
      // one card, then the unsplay is a no-op).
      if ($numVisibleCards > 1 && $numVisibleCards === $mostVisibleCards) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

}
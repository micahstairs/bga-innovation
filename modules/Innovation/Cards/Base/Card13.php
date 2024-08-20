<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;


class Card13 extends AbstractCard
{
  // Code of Laws:
  //   - You may tuck a card from your hand of the same color as any card on your board. If you do,
  //     you may splay that color of your cards left.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'      => true,
        'tuck_keyword'  => true,
        'location_from' => Locations::HAND,
        'color'         => self::getColorsOnBoard(),
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [self::getLastSelectedColor()],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setMaxSteps(2);
  }

  private function getColorsOnBoard(): array
  {
    $countsByColor = self::countCardsKeyedByColor(Locations::BOARD);
    $colors = 0;
    foreach (Colors::ALL as $color) {
      if (count(self::countCardsKeyedByColor($color)) > 0) {
        $colors++;
      }
    }
    return $countsByColor;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByColor(self::getCards(Locations::HAND), self::getColorsOnBoard())) > 0;
  }

}
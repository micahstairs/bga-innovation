<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;


class Card13 extends AbstractCard
{
  // Code of Laws:
  //   - You may tuck a card from your hand of the same color as any card on your board. If you do,
  //     you may splay that color of your cards left.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->tuck()->fromYourHand()->withColor(self::getColorsOnBoard())->build();
    } else {
      return self::youMay()->splayLeft()->withColor(self::getLastSelectedColor())->build();
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
    if (self::isLauncher()) {
      return count(self::filterByColor(self::getCards(Locations::HAND), self::getColorsOnBoard())) > 0;
    } else {
      return self::hasCards(Locations::HAND) && count(self::getColorsOnBoard()) > 0;
    }
  }

}
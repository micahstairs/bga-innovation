<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card23 extends AbstractCard
{
  // Monotheism:
  // - 3rd edition:
  //   - I DEMAND you transfer a top card on your board of different color from any card on my
  //     board to my score pile! If you do, draw and tuck a [1]!
  //   - Draw and tuck a [1].
  // - 4th edition:
  //   - I DEMAND you transfer a top card on your board of different color from every card on my
  //     board to my score pile! If you do, draw and tuck a [1]!
  //   - Draw and tuck a [1].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndTuck(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->fromYourBoard()->withColor(self::getSelectableColors())->toMyScore()->build();
  }

  private function getSelectableColors(): array
  {
    $playerBoardColors = self::getUniqueColors(Locations::BOARD);
    $launcherBoardColors = self::getUniqueColors(Locations::BOARD, self::getLauncherId());
    return array_diff($playerBoardColors, $launcherBoardColors);
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndTuck(1);
  }

}
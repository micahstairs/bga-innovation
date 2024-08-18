<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card79 extends AbstractCard
{
  // Corporations:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-green card with a [INDUSTRY] from your board to my score
  //     pile! If you do, draw and meld an 8!
  //   - Draw and meld an [8].
  // - 4th edition:
  //   - I DEMAND you transfer a top non-green card with [FACTORY] from your board to my score
  //     pile! If you do, draw and meld an 8!
  //   - Draw and meld an [8]!

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndMeld(8);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'color'         => Colors::NON_GREEN,
      'with_icon'     => Icons::INDUSTRY,
      'owner_from'    => self::getPlayerId(),
      'location_from' => Locations::BOARD,
      'owner_to'      => self::getLauncherId(),
      'location_to'   => Locations::SCORE,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndMeld(8);
  }

  public function demandMightBeEffective(): bool
  {
    $topNonGreenCards = self::filterByColor(self::getTopCards(), Colors::NON_GREEN);
    return count(self::filterByIcon($topNonGreenCards, Icons::INDUSTRY)) > 0;
  }

}
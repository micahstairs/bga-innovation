<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card78 extends AbstractCard
{
  // Mobility:
  // - 3rd edition:
  //   - I demand you transfer the two highest non-red top cards without a [INDUSTRY] from your
  //     board to my score pile! If you transferred any cards, draw an 8.
  // - 4th edition:
  //   - I demand you transfer your two highest non-red top cards without [INDUSTRY] of different
  //     colors to my score pile! If you transfer any cards, draw an 8.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'                    => 2,
      'owner_from'           => self::getPlayerId(),
      'location_from'        => Locations::BOARD,
      'owner_to'             => self::getLauncherId(),
      'location_to'          => Locations::SCORE,
      'color'                => Colors::NON_RED,
      'without_icon'         => Icons::INDUSTRY,
      'age'                  => 'highest',
      'refresh_selection'    => true,
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::draw(8);
    }
  }

  public function demandMightBeEffective(): bool
  {
    $topNonRedCards = self::filterByColor(self::getTopCards(), Colors::NON_RED);
    return count(self::filterWithoutIcon($topNonRedCards, Icons::INDUSTRY)) > 0;
  }

}